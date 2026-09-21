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
	define('ELAHUB_TLMS_MAP_VERSION', '2');
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


/* ===========================================================================
 * TEMPORARY TRACER - remove once TalentLMS enrolment is proven working.
 *
 * The mapping above is confirmed correct (product 11320 -> course 136), so the
 * plugin's gate passes and Utils::tlms_enrollUserToCoursesByOrderId() IS being
 * reached - yet no user or enrolment ever appears in TalentLMS and the plugin
 * writes nothing to its own errorLog.txt.
 *
 * The plugin swallows every API failure with catch (Exception) and then calls
 * Utils::tlms_recordLog(), which does:
 *
 *     $fp = fopen( TLMS_BASEPATH . '/errorLog.txt', 'a' );   // false if not writable
 *     fputs( $fp, $logOutput );                              // TypeError on PHP 8
 *
 * fputs() on `false` throws a TypeError, which is an Error, not an Exception -
 * so nothing catches it and the request dies. That would explain the total
 * silence: no user, no enrolment, no log, no notice.
 *
 * This tracer brackets the plugin's own handlers (they run at priority 10) and
 * records the state going in and whether control ever came out, plus any fatal
 * caught at shutdown. Read it in WooCommerce > Status > Logs, source
 * elahub-talentlms.
 * ======================================================================== */

add_action('woocommerce_payment_complete', 'elahub_tlms_trace_in', 1, 1);
add_action('woocommerce_payment_complete', 'elahub_tlms_trace_out', 999, 1);
add_action('woocommerce_order_status_completed', 'elahub_tlms_trace_in', 1, 1);
add_action('woocommerce_order_status_completed', 'elahub_tlms_trace_out', 999, 1);

/**
 * Record everything the plugin is about to decide on, before its handler runs.
 */
function elahub_tlms_trace_in($order_id): void {

	$hook = current_action();
	$bits = array('hook=' . $hook, 'order=' . (int) $order_id);

	$bits[] = 'setting=' . var_export(get_option('tlms-enroll-user-to-courses'), true);
	$bits[] = 'woo_active=' . var_export(get_option('tlms-woocommerce-active'), true);

	$plugin_dir = WP_PLUGIN_DIR . '/talentlms';
	$bits[] = 'plugin_dir_writable=' . var_export(is_writable($plugin_dir), true);
	$bits[] = 'errorLog_exists=' . var_export(file_exists($plugin_dir . '/errorLog.txt'), true);

	if (class_exists('\TalentlmsIntegration\Utils')) {
		try {
			$bits[] = 'hasCourseItem=' . var_export(
				\TalentlmsIntegration\Utils::tlms_orderHasTalentLMSCourseItem((int) $order_id),
				true
			);
			$bits[] = 'completedInPast=' . var_export(
				\TalentlmsIntegration\Utils::tlms_isOrderCompletedInPast((int) $order_id),
				true
			);
			$user = \TalentlmsIntegration\Utils::tlms_getUserByOrder(wc_get_order($order_id));
			$bits[] = 'enrol_email=' . (string) ($user->user_email ?? '?');
		} catch (\Throwable $e) {
			$bits[] = 'probe_threw=' . get_class($e) . ': ' . $e->getMessage();
		}
	} else {
		$bits[] = 'Utils_class=MISSING (plugin not loaded)';
	}

	elahub_tlms_log('TRACE IN  | ' . implode(' | ', $bits));

	// Catch a fatal that kills the request inside the plugin's handler.
	register_shutdown_function(static function () use ($hook, $order_id) {
		$last = error_get_last();
		if ($last && in_array($last['type'], array(E_ERROR, E_PARSE, E_COMPILE_ERROR, E_CORE_ERROR), true)) {
			elahub_tlms_log(sprintf(
				'TRACE FATAL | hook=%s | order=%d | %s in %s:%d',
				$hook,
				(int) $order_id,
				$last['message'],
				$last['file'],
				$last['line']
			));
		}
	});
}

/**
 * Runs only if the plugin's handler returned without fataling.
 */
function elahub_tlms_trace_out($order_id): void {

	$bits = array('hook=' . current_action(), 'order=' . (int) $order_id);

	$order = wc_get_order($order_id);
	$links = array();
	if ($order) {
		foreach ($order->get_items() as $item_id => $item) {
			$meta = wc_get_order_item_meta($item_id, 'tlms_go-to-course');
			$links[] = $item_id . '=' . (empty($meta) ? 'none' : 'SET');
		}
	}
	$bits[] = 'goto_course_meta: ' . (empty($links) ? 'no items' : implode(',', $links));

	$plugin_dir = WP_PLUGIN_DIR . '/talentlms';
	$bits[] = 'errorLog_exists_now=' . var_export(file_exists($plugin_dir . '/errorLog.txt'), true);

	elahub_tlms_log('TRACE OUT | ' . implode(' | ', $bits));
}


/* ===========================================================================
 * TEMPORARY - one-shot dump of the TalentLMS plugin's own errorLog.txt.
 *
 * The plugin catches every API failure with catch (Exception) and writes the
 * message to TLMS_BASEPATH/errorLog.txt, which is not reachable over HTTP (the
 * site rewrites .txt to the 404 template), so it has never been read. It is the
 * only record of why enrolment was failing before 21 Sep 2026. Dump the tail of
 * it into WooCommerce > Status > Logs once, then remove this block.
 * ======================================================================== */

add_action('admin_init', 'elahub_tlms_dump_plugin_error_log', 21);

function elahub_tlms_dump_plugin_error_log(): void {

	if (get_option('elahub_tlms_errorlog_dumped') === '1') {
		return;
	}

	$file = WP_PLUGIN_DIR . '/talentlms/errorLog.txt';

	if (! file_exists($file) || ! is_readable($file)) {
		elahub_tlms_log('errorLog.txt not readable at ' . $file);
		update_option('elahub_tlms_errorlog_dumped', '1', false);
		return;
	}

	$size = (int) filesize($file);
	$keep = 6000;
	$body = '';

	$fh = fopen($file, 'r');
	if (is_resource($fh)) {
		if ($size > $keep) {
			fseek($fh, -$keep, SEEK_END);
		}
		$body = (string) stream_get_contents($fh);
		fclose($fh);
	}

	elahub_tlms_log(sprintf(
		'errorLog.txt (%d bytes, last %d shown):%s%s',
		$size,
		strlen($body),
		PHP_EOL,
		$body
	));

	update_option('elahub_tlms_errorlog_dumped', '1', false);
}
