<?php

/**
 * TalentLMS <-> WooCommerce product mapping repair + diagnostics
 *
 * WHY THIS EXISTS
 * ---------------
 * The TalentLMS plugin enrols a buyer by way of two hooks - `woocommerce_payment_complete`
 * (setting: "Upon order submission") and `woocommerce_order_status_completed`
 * (setting: "Upon order completion"). BOTH of them pass through the same gate:
 *
 *     Utils::tlms_orderHasTalentLMSCourseItem( $order_id )
 *         -> get_post_meta( $item['product_id'], '_talentlms_course_id' )
 *
 * If the purchased product carries no `_talentlms_course_id` post meta, that gate
 * returns false and the enrolment function is never reached. No API call is made, no
 * exception is thrown, nothing is written to the plugin's errorLog.txt and no fatal is
 * logged. The order completes, the customer pays, and they are silently never enrolled.
 *
 * The plugin only ever writes that meta inside `Utils::tlms_addProduct()` - i.e. when IT
 * creates the WooCommerce product from the Integrations screen. There is no UI to map an
 * existing product to a course, and the "Re-Sync" row action is NOT a repair tool: it
 * runs `DELETE FROM {prefix}posts WHERE ID = <product_id>` and builds a fresh product,
 * which would destroy the live DALC product (its URL, SEO, page links and the ID the
 * theme resolves in inc/dalc-checkout.php). So the mapping is repaired here, in place.
 *
 * WHAT IT DOES
 * ------------
 * The plugin keeps its own course -> product mapping in `{prefix}talentlms_products`.
 * That table is authoritative - it is what ticks the boxes on the Integrations screen.
 * For every row in it, if the product still exists and is missing (or has an empty)
 * `_talentlms_course_id`, the meta is restored from that row. Nothing is hardcoded and
 * nothing is created or deleted.
 *
 * It runs once per ELAHUB_TLMS_MAP_VERSION, on admin page loads only, and records what
 * it found either way to WooCommerce > Status > Logs (source: elahub-talentlms).
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Bump this to re-run the repair after a future change.
 */
if (! defined('ELAHUB_TLMS_MAP_VERSION')) {
	define('ELAHUB_TLMS_MAP_VERSION', '1');
}

add_action('admin_init', 'elahub_tlms_repair_product_mapping', 20);

/**
 * Restore `_talentlms_course_id` on any product the TalentLMS plugin has mapped.
 */
function elahub_tlms_repair_product_mapping(): void {

	if (get_option('elahub_tlms_map_done') === ELAHUB_TLMS_MAP_VERSION) {
		return;
	}

	// Do not run a half-booted repair: WooCommerce must be up.
	if (! function_exists('wc_get_product')) {
		return;
	}

	global $wpdb;

	$table = $wpdb->prefix . 'talentlms_products';

	$exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
	if ($exists !== $table) {
		elahub_tlms_log('Mapping table ' . $table . ' not found - nothing to repair.');
		update_option('elahub_tlms_map_done', ELAHUB_TLMS_MAP_VERSION, false);
		return;
	}

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name, not user input.
	$rows = $wpdb->get_results("SELECT product_id, course_id FROM {$table}");

	if (empty($rows)) {
		elahub_tlms_log('Mapping table is empty - no course is linked to a product.');
		update_option('elahub_tlms_map_done', ELAHUB_TLMS_MAP_VERSION, false);
		return;
	}

	$report = array();

	foreach ($rows as $row) {
		$product_id = (int) $row->product_id;
		$course_id  = (int) $row->course_id;

		if (! $product_id || ! $course_id) {
			$report[] = sprintf('row skipped (product %d / course %d)', $product_id, $course_id);
			continue;
		}

		$post = get_post($product_id);
		if (! $post || $post->post_type !== 'product' || $post->post_status === 'trash') {
			$report[] = sprintf(
				'course %d -> product %d MISSING (deleted or binned) - mapping is stale',
				$course_id,
				$product_id
			);
			continue;
		}

		$current = get_post_meta($product_id, '_talentlms_course_id', true);

		if ((string) $current === (string) $course_id) {
			$report[] = sprintf(
				'course %d -> product %d "%s": meta already correct (%s)',
				$course_id,
				$product_id,
				$post->post_title,
				$current
			);
			continue;
		}

		$was = ('' === $current || null === $current) ? 'EMPTY' : (string) $current;
		update_post_meta($product_id, '_talentlms_course_id', $course_id);

		$report[] = sprintf(
			'course %d -> product %d "%s": meta was %s, set to %d',
			$course_id,
			$product_id,
			$post->post_title,
			$was,
			$course_id
		);
	}

	elahub_tlms_log('TalentLMS mapping repair: ' . implode(' | ', $report));

	update_option('elahub_tlms_map_done', ELAHUB_TLMS_MAP_VERSION, false);
}

/**
 * Write a line to WooCommerce > Status > Logs (source: elahub-talentlms).
 */
function elahub_tlms_log(string $message): void {
	if (function_exists('wc_get_logger')) {
		wc_get_logger()->info($message, array('source' => 'elahub-talentlms'));
	}
}
