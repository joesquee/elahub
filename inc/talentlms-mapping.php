<?php

/**
 * TalentLMS enrolment: mapping repair + failed-enrolment guard
 *
 * THE FAULT THIS EXISTS FOR
 * -------------------------
 * Buyers of the DALC programme were paying and never being enrolled, with no
 * error anywhere. The cause was found on 21 Sep 2026 in the TalentLMS plugin's
 * own log (TLMS_BASEPATH/errorLog.txt, which is not reachable over HTTP because
 * the site rewrites .txt to the 404 template, so nobody had ever read it):
 *
 *     #September 11th 2026, 21:18: Could not connect to TalentLMS
 *     (https://elahub.talentlms.com/api/v1). Please check your internet
 *     connection and try again.
 *     (Network error: Connection timed out after 30002 milliseconds)
 *
 * Dozens of those, from 11 September onwards and still occurring. The site's
 * outbound connection to the TalentLMS API times out intermittently. The plugin
 * calls the API synchronously during checkout, catches the exception with
 * catch (Exception), writes it to that unreadable file and returns. The order
 * completes, the customer pays, and nothing else happens. There is no retry and
 * nobody is told.
 *
 * WHAT THIS FILE DOES
 * -------------------
 * 1. Mapping repair. The plugin only writes `_talentlms_course_id` when IT
 *    creates the product, and its "Re-Sync" row action is not a repair tool -
 *    it DELETEs the post row and rebuilds the product, which would destroy the
 *    live DALC product, its URL, its SEO and the ID inc/dalc-checkout.php
 *    resolves. So the meta is restored in place from the plugin's own
 *    course/product table. Idempotent; runs once per ELAHUB_TLMS_MAP_VERSION.
 *
 * 2. Enrolment guard. After the plugin's own handlers have run, check whether
 *    every TalentLMS course line on the order came away with `tlms_go-to-course`
 *    item meta - which the plugin writes only after a successful
 *    TalentLMS_Course::addUser(). If any did not, schedule a retry rather than
 *    retrying inline, because the failure mode is a 30-second timeout and an
 *    immediate second attempt would just hit it again. Retries back off
 *    (5, 15, 30, 60 minutes). If they are all exhausted, an order note is added
 *    and an alert is emailed so a human can enrol the customer by hand.
 *
 * Everything is logged to WooCommerce > Status > Logs, source elahub-talentlms.
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
	exit;
}

/** Bump to re-run the mapping repair. */
if (! defined('ELAHUB_TLMS_MAP_VERSION')) {
	define('ELAHUB_TLMS_MAP_VERSION', '2');
}

/** Retry schedule, in seconds after the previous attempt. */
if (! defined('ELAHUB_TLMS_RETRY_DELAYS')) {
	define('ELAHUB_TLMS_RETRY_DELAYS', '300,900,1800,3600');
}

const ELAHUB_TLMS_RETRY_HOOK = 'elahub_tlms_retry_enrolment';

/* -------------------------------------------------------------------------
 * 1. Mapping repair
 * ---------------------------------------------------------------------- */

add_action('admin_init', 'elahub_tlms_repair_product_mapping', 20);

function elahub_tlms_repair_product_mapping(): void {

	if (get_option('elahub_tlms_map_done') === ELAHUB_TLMS_MAP_VERSION) {
		return;
	}

	if (! function_exists('wc_get_product')) {
		return;
	}

	global $wpdb;

	$table  = $wpdb->prefix . 'talentlms_products';
	$exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));

	if ($exists !== $table) {
		elahub_tlms_log('Mapping table ' . $table . ' not found - nothing to repair.');
		update_option('elahub_tlms_map_done', ELAHUB_TLMS_MAP_VERSION, false);
		return;
	}

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name, not user input.
	$rows   = $wpdb->get_results("SELECT product_id, course_id FROM {$table}");
	$report = array();

	foreach ((array) $rows as $row) {
		$product_id = (int) $row->product_id;
		$course_id  = (int) $row->course_id;

		if (! $product_id || ! $course_id) {
			continue;
		}

		$post = get_post($product_id);
		if (! $post || $post->post_type !== 'product' || $post->post_status === 'trash') {
			$report[] = sprintf('course %d -> product %d MISSING (stale row)', $course_id, $product_id);
			continue;
		}

		$current = get_post_meta($product_id, '_talentlms_course_id', true);

		if ((string) $current === (string) $course_id) {
			$report[] = sprintf('course %d -> product %d OK', $course_id, $product_id);
			continue;
		}

		update_post_meta($product_id, '_talentlms_course_id', $course_id);
		$report[] = sprintf(
			'course %d -> product %d "%s": meta was %s, set to %d',
			$course_id,
			$product_id,
			$post->post_title,
			('' === $current || null === $current) ? 'EMPTY' : (string) $current,
			$course_id
		);
	}

	elahub_tlms_log('Mapping repair: ' . (empty($report) ? 'no rows' : implode(' | ', $report)));
	update_option('elahub_tlms_map_done', ELAHUB_TLMS_MAP_VERSION, false);
}

/* -------------------------------------------------------------------------
 * 2. Enrolment guard
 * ---------------------------------------------------------------------- */

// The plugin's own handlers sit at priority 10 on both hooks.
add_action('woocommerce_payment_complete', 'elahub_tlms_guard_order', 900, 1);
add_action('woocommerce_order_status_completed', 'elahub_tlms_guard_order', 900, 1);
add_action(ELAHUB_TLMS_RETRY_HOOK, 'elahub_tlms_retry_enrolment', 10, 2);

/**
 * Did every TalentLMS course line on this order get its "start course" link?
 *
 * @return array{course_items:int, missing:int[]}
 */
function elahub_tlms_enrolment_state(int $order_id): array {

	$state = array('course_items' => 0, 'missing' => array());
	$order = wc_get_order($order_id);

	if (! $order) {
		return $state;
	}

	foreach ($order->get_items() as $item_id => $item) {
		$product_id = (int) $item->get_product_id();

		if (! get_post_meta($product_id, '_talentlms_course_id', true)) {
			continue;
		}

		$state['course_items']++;

		$goto = wc_get_order_item_meta($item_id, 'tlms_go-to-course');
		if (empty($goto)) {
			$state['missing'][] = (int) $item_id;
		}
	}

	return $state;
}

/**
 * Runs after the plugin's handler. Schedules a retry if anything did not enrol.
 */
function elahub_tlms_guard_order($order_id): void {

	$order_id = (int) $order_id;
	$state    = elahub_tlms_enrolment_state($order_id);

	if ($state['course_items'] === 0) {
		return; // Not a course order - nothing to guard.
	}

	if (empty($state['missing'])) {
		elahub_tlms_log(sprintf('Order %d: enrolled OK (%d course item(s)).', $order_id, $state['course_items']));
		return;
	}

	elahub_tlms_log(sprintf(
		'Order %d: %d of %d course item(s) NOT enrolled - scheduling retry 1.',
		$order_id,
		count($state['missing']),
		$state['course_items']
	));

	elahub_tlms_schedule_retry($order_id, 1);
}

/**
 * Queue attempt N, backing off per ELAHUB_TLMS_RETRY_DELAYS.
 */
function elahub_tlms_schedule_retry(int $order_id, int $attempt): void {

	$delays = array_map('intval', explode(',', ELAHUB_TLMS_RETRY_DELAYS));

	if ($attempt < 1 || $attempt > count($delays)) {
		return;
	}

	$delay = $delays[$attempt - 1];
	$args  = array($order_id, $attempt);

	if (function_exists('as_schedule_single_action')) {
		if (function_exists('as_has_scheduled_action')
			&& as_has_scheduled_action(ELAHUB_TLMS_RETRY_HOOK, $args, 'elahub')
		) {
			return;
		}
		as_schedule_single_action(time() + $delay, ELAHUB_TLMS_RETRY_HOOK, $args, 'elahub');
		return;
	}

	// Action Scheduler ships with WooCommerce, but fall back rather than fail.
	if (! wp_next_scheduled(ELAHUB_TLMS_RETRY_HOOK, $args)) {
		wp_schedule_single_event(time() + $delay, ELAHUB_TLMS_RETRY_HOOK, $args);
	}
}

/**
 * Re-run the plugin's own enrolment for this order, then check and reschedule.
 */
function elahub_tlms_retry_enrolment($order_id, $attempt): void {

	$order_id = (int) $order_id;
	$attempt  = (int) $attempt;

	// It may have been fixed by hand, or by an earlier attempt, since queueing.
	$state = elahub_tlms_enrolment_state($order_id);

	if ($state['course_items'] === 0 || empty($state['missing'])) {
		elahub_tlms_log(sprintf('Order %d: already enrolled by attempt %d - nothing to do.', $order_id, $attempt));
		return;
	}

	if (! class_exists('\TalentlmsIntegration\Utils')) {
		elahub_tlms_log(sprintf('Order %d: TalentLMS plugin not loaded - cannot retry.', $order_id));
		return;
	}

	try {
		\TalentlmsIntegration\Utils::tlms_enrollUserToCoursesByOrderId($order_id);
	} catch (\Throwable $e) {
		// The plugin swallows Exceptions itself; this catches Errors too, so a
		// failure inside it can never take the queue runner down with it.
		elahub_tlms_log(sprintf(
			'Order %d: retry %d threw %s: %s',
			$order_id,
			$attempt,
			get_class($e),
			$e->getMessage()
		));
	}

	$state = elahub_tlms_enrolment_state($order_id);

	if (empty($state['missing'])) {
		elahub_tlms_log(sprintf('Order %d: enrolled on retry %d.', $order_id, $attempt));

		$order = wc_get_order($order_id);
		if ($order) {
			$order->add_order_note(sprintf(
				'TalentLMS enrolment succeeded on automatic retry %d.',
				$attempt
			));
		}
		return;
	}

	$delays = array_map('intval', explode(',', ELAHUB_TLMS_RETRY_DELAYS));

	if ($attempt < count($delays)) {
		elahub_tlms_log(sprintf('Order %d: retry %d failed - scheduling retry %d.', $order_id, $attempt, $attempt + 1));
		elahub_tlms_schedule_retry($order_id, $attempt + 1);
		return;
	}

	elahub_tlms_alert_failure($order_id, $attempt);
}

/**
 * All retries exhausted. Make it impossible to miss: order note + email.
 */
function elahub_tlms_alert_failure(int $order_id, int $attempt): void {

	elahub_tlms_log(sprintf('Order %d: enrolment STILL failing after %d retries - alerting.', $order_id, $attempt));

	$order = wc_get_order($order_id);
	$email = $order ? $order->get_billing_email() : '(unknown)';

	if ($order) {
		$order->add_order_note(sprintf(
			'TalentLMS enrolment FAILED after %d automatic retries. This customer has paid and is NOT enrolled - please add them to the course manually in TalentLMS.',
			$attempt
		));
	}

	$to = array_filter(array_unique(array_map(
		'sanitize_email',
		/**
		 * Who to tell when a paid customer could not be enrolled.
		 *
		 * @param string[] $recipients
		 */
		apply_filters('elahub_tlms_alert_recipients', array(get_option('admin_email')))
	)));

	if (empty($to)) {
		return;
	}

	$subject = sprintf('[%s] TalentLMS enrolment failed for order #%d', get_bloginfo('name'), $order_id);

	$body = sprintf(
		"A customer has paid but could NOT be enrolled in TalentLMS.\n\n"
		. "Order: #%d\n"
		. "Customer email: %s\n"
		. "Attempts: %d automatic retries, all failed\n"
		. "Order admin: %s\n\n"
		. "Please add them to the course manually in TalentLMS.\n\n"
		. "The usual cause is the site's connection to the TalentLMS API timing out.\n"
		. "Details are in WooCommerce > Status > Logs, source elahub-talentlms, and in\n"
		. "the TalentLMS plugin's own wp-content/plugins/talentlms/errorLog.txt.\n",
		$order_id,
		$email,
		$attempt,
		admin_url('post.php?post=' . $order_id . '&action=edit')
	);

	wp_mail($to, $subject, $body);
}

/**
 * Write a line to WooCommerce > Status > Logs (source: elahub-talentlms).
 */
function elahub_tlms_log(string $message): void {
	if (function_exists('wc_get_logger')) {
		wc_get_logger()->info($message, array('source' => 'elahub-talentlms'));
	}
}
