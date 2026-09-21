<?php

/**
 * TEMPORARY - TalentLMS connectivity probe
 *
 * The TalentLMS plugin's own errorLog.txt shows repeated 30-second connection
 * timeouts from this server to https://elahub.talentlms.com/api/v1, starting
 * 11 Sep 2026 and still occurring on 21 Sep. When one lands during checkout the
 * buyer pays and is never enrolled. inc/talentlms-mapping.php now retries and
 * alerts, but that treats the symptom; the cause is outbound connectivity and
 * proving it needs numbers, not anecdotes.
 *
 * So: probe the portal every 15 minutes and record latency or failure to
 * WooCommerce > Status > Logs, source elahub-talentlms-probe. Run it for 24
 * hours, then read off how many attempts failed and when - which is the
 * evidence a host or TalentLMS support ticket needs.
 *
 * It uses no API key. The failures are connection-level (TCP/TLS), so a plain
 * request to the portal exercises the same path without touching credentials.
 *
 * SELF-EXPIRING: stops on its own ELAHUB_TLMS_PROBE_HOURS after first run.
 * Delete this file and its require in functions.php once the question is
 * settled.
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
	exit;
}

if (! defined('ELAHUB_TLMS_PROBE_HOURS')) {
	define('ELAHUB_TLMS_PROBE_HOURS', 24);
}

const ELAHUB_TLMS_PROBE_HOOK = 'elahub_tlms_probe';

add_action('init', 'elahub_tlms_probe_boot');
add_action(ELAHUB_TLMS_PROBE_HOOK, 'elahub_tlms_probe_run');

/**
 * Start the schedule on first load and remember when to stop.
 */
function elahub_tlms_probe_boot(): void {

	if (! function_exists('as_schedule_recurring_action')) {
		return;
	}

	$until = (int) get_option('elahub_tlms_probe_until');

	if (! $until) {
		$until = time() + (ELAHUB_TLMS_PROBE_HOURS * HOUR_IN_SECONDS);
		update_option('elahub_tlms_probe_until', $until, false);
	}

	if (time() > $until) {
		if (function_exists('as_unschedule_all_actions')) {
			as_unschedule_all_actions(ELAHUB_TLMS_PROBE_HOOK);
		}
		return;
	}

	if (function_exists('as_has_scheduled_action')
		&& ! as_has_scheduled_action(ELAHUB_TLMS_PROBE_HOOK, array(), 'elahub')
	) {
		as_schedule_recurring_action(time() + 60, 15 * MINUTE_IN_SECONDS, ELAHUB_TLMS_PROBE_HOOK, array(), 'elahub');
	}
}

/**
 * One probe: time a request to the portal and record the outcome.
 */
function elahub_tlms_probe_run(): void {

	$until = (int) get_option('elahub_tlms_probe_until');
	if ($until && time() > $until) {
		if (function_exists('as_unschedule_all_actions')) {
			as_unschedule_all_actions(ELAHUB_TLMS_PROBE_HOOK);
		}
		elahub_tlms_probe_log('Probe window elapsed - stopping.');
		return;
	}

	$domain = get_option('tlms-domain');
	if (empty($domain)) {
		return;
	}

	$url   = 'https://' . $domain . '/';
	$start = microtime(true);

	$response = wp_remote_get($url, array(
		'timeout'     => 30,
		'redirection' => 2,
		'sslverify'   => true,
		'user-agent'  => 'eLaHub connectivity probe',
	));

	$ms = (int) round((microtime(true) - $start) * 1000);

	if (is_wp_error($response)) {
		elahub_tlms_probe_log(sprintf(
			'FAIL after %dms - %s: %s',
			$ms,
			$response->get_error_code(),
			$response->get_error_message()
		));
		return;
	}

	elahub_tlms_probe_log(sprintf(
		'ok %d in %dms',
		(int) wp_remote_retrieve_response_code($response),
		$ms
	));
}

function elahub_tlms_probe_log(string $message): void {
	if (function_exists('wc_get_logger')) {
		wc_get_logger()->info($message, array('source' => 'elahub-talentlms-probe'));
	}
}
