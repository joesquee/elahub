<?php

/**
 * Spam protection shared by every eLaHub form
 *
 * Covers the contact form (inc/contact-page-handler.php, posts to admin-post)
 * and the four flexible-content forms (inc/form-handler.php, posts over AJAX):
 * DALC Sign Up, DALC Live Waitlist, Short Courses Waitlist and the Showcase
 * Module Sign Up.
 *
 * Until now the only protection on any of them was a WordPress nonce. A nonce
 * stops a blind cross-site post, but not a bot that loads the page first and
 * reads the nonce off it, which is what has been getting through.
 *
 * Three layers, cheapest first, so a failure of one does not open the door:
 *
 *   1. Honeypot. A field a person never sees and never reaches, because it is
 *      off screen, aria-hidden and out of the tab order. A bot filling every
 *      input hands us a reason to reject it, at no cost to anyone real.
 *
 *   2. Timing. The render time is stamped into the form and signed, so it
 *      cannot be forged. A submission arriving within a few seconds of the page
 *      rendering was not typed by a person. The window is deliberately open at
 *      the top end: with page caching a visitor can be served a page stamped
 *      hours earlier, and they must not be turned away.
 *
 *   3. reCAPTCHA v3, only when a key pair is configured. Invisible and score
 *      based, so there is no puzzle, no image grid and nothing extra for
 *      someone using a keyboard or a screen reader. With no keys set the first
 *      two layers still run.
 *
 * Every rejection returns the same neutral message. Telling a bot which check
 * caught it just helps it past the next one.
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
	exit;
}

const ELAHUB_HP_FIELD    = 'elahub_hp_url';
const ELAHUB_TS_FIELD    = 'elahub_ts';
const ELAHUB_TSIG_FIELD  = 'elahub_tsig';
const ELAHUB_TOKEN_FIELD = 'elahub_recaptcha_token';

/** Submissions faster than this many seconds after render are not human. */
const ELAHUB_MIN_FILL_SECONDS = 3;

/** Outer bound, generous so a cached page served much later still works. */
const ELAHUB_MAX_FILL_SECONDS = 604800; // 7 days.

/* -------------------------------------------------------------------------
 * Settings
 * ---------------------------------------------------------------------- */

/**
 * Resolve a reCAPTCHA key: Theme Settings → Integrations first, then a
 * wp-config constant. Returns '' when neither is set, which disables layer 3.
 */
function elahub_recaptcha_key(string $which): string {

	$acf_field = $which === 'secret' ? 'recaptcha_secret_key' : 'recaptcha_site_key';
	$constant  = $which === 'secret' ? 'ELAHUB_RECAPTCHA_SECRET_KEY' : 'ELAHUB_RECAPTCHA_SITE_KEY';

	if (function_exists('get_field')) {
		$value = get_field($acf_field, 'option');
		if (is_string($value) && trim($value) !== '') {
			return trim($value);
		}
	}

	if (defined($constant) && (string) constant($constant) !== '') {
		return (string) constant($constant);
	}

	return '';
}

/** Is reCAPTCHA usable? Both halves of the pair have to be present. */
function elahub_recaptcha_active(): bool {
	return elahub_recaptcha_key('site') !== '' && elahub_recaptcha_key('secret') !== '';
}

/* -------------------------------------------------------------------------
 * Where the keys are entered: Theme Settings > Integrations
 * ---------------------------------------------------------------------- */

add_action('acf/init', 'elahub_recaptcha_register_settings', 12);

function elahub_recaptcha_register_settings(): void {

	if (! function_exists('acf_add_local_field_group')) {
		return;
	}

	acf_add_local_field_group(array(
		'key'      => 'group_elahub_recaptcha',
		'title'    => 'Form spam protection',
		'fields'   => array(
			array(
				'key'       => 'field_elahub_recaptcha_intro',
				'label'     => '',
				'type'      => 'message',
				'message'   => 'Every form on the site already uses a hidden honeypot field and a timing check, which need no setup. Adding a reCAPTCHA v3 key pair below turns on a third layer: an invisible score-based check that asks the visitor to do nothing, so it adds no step for anyone using a keyboard or a screen reader. Leave these blank and the first two layers still apply.',
				'esc_html'  => 0,
				'new_lines' => 'wpautop',
			),
			array(
				'key'          => 'field_elahub_recaptcha_site_key',
				'label'        => 'reCAPTCHA v3 site key',
				'name'         => 'recaptcha_site_key',
				'type'         => 'text',
				'instructions' => 'The public half of the pair. It appears in the page source, so it is not a secret. Must be a v3 key registered against this domain.',
			),
			array(
				'key'          => 'field_elahub_recaptcha_secret_key',
				'label'        => 'reCAPTCHA v3 secret key',
				'name'         => 'recaptcha_secret_key',
				'type'         => 'password',
				'instructions' => 'The private half. Never leaves the server. Can also be set as ELAHUB_RECAPTCHA_SECRET_KEY in wp-config, which keeps it out of the database.',
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'options_page',
					'operator' => '==',
					'value'    => 'elahub-integrations',
				),
			),
		),
	));
}

/**
 * Say on the Integrations screen which layers are actually live.
 *
 * A masked key field that silently does nothing is exactly how the Mailchimp
 * sync went unnoticed for ten days, so this screen states its own status.
 */
add_action('admin_notices', 'elahub_recaptcha_status_notice');

function elahub_recaptcha_status_notice(): void {

	if (($_GET['page'] ?? '') !== 'elahub-integrations') {
		return;
	}

	if (elahub_recaptcha_active()) {
		echo '<div class="notice notice-success"><p><strong>Form spam protection:</strong> honeypot, timing check and reCAPTCHA v3 are all active.</p></div>';
		return;
	}

	$half = elahub_recaptcha_key('site') !== '' || elahub_recaptcha_key('secret') !== '';

	printf(
		'<div class="notice notice-%1$s"><p><strong>Form spam protection:</strong> honeypot and timing check are active. %2$s</p></div>',
		$half ? 'warning' : 'info',
		$half
			? 'reCAPTCHA is <strong>not</strong> running because only one half of the key pair is filled in.'
			: 'reCAPTCHA is off. Add a v3 key pair below to turn it on.'
	);
}

/* -------------------------------------------------------------------------
 * Form-side markup
 * ---------------------------------------------------------------------- */

/**
 * Print the hidden guard fields. Called once inside every form.
 *
 * The honeypot is positioned off screen rather than display:none, because some
 * bots skip anything explicitly hidden. aria-hidden and tabindex="-1" keep it
 * away from assistive technology and the keyboard, and the label exists for
 * anyone who reaches it by an unexpected route.
 */
function elahub_form_spam_fields(): void {

	$ts  = (string) time();
	$sig = elahub_form_sign_timestamp($ts);

	// Tell the footer a guarded form is on this page.
	$GLOBALS['elahub_form_on_page'] = true;

	?>
	<div style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
		<label for="<?php echo esc_attr(ELAHUB_HP_FIELD); ?>">Leave this field empty</label>
		<input
			type="text"
			id="<?php echo esc_attr(ELAHUB_HP_FIELD); ?>"
			name="<?php echo esc_attr(ELAHUB_HP_FIELD); ?>"
			value=""
			tabindex="-1"
			autocomplete="off">
	</div>
	<input type="hidden" name="<?php echo esc_attr(ELAHUB_TS_FIELD); ?>" value="<?php echo esc_attr($ts); ?>">
	<input type="hidden" name="<?php echo esc_attr(ELAHUB_TSIG_FIELD); ?>" value="<?php echo esc_attr($sig); ?>">
	<?php if (elahub_recaptcha_active()) : ?>
		<input type="hidden" name="<?php echo esc_attr(ELAHUB_TOKEN_FIELD); ?>" value="">
	<?php endif; ?>
	<?php
}

/**
 * Sign a value that travels through the browser and must come back unaltered.
 *
 * Used for the render timestamp, and for the notification address list and form
 * label. Those two were previously plain hidden inputs, so anyone could edit
 * them in devtools and have the site mail an address of their choosing, or put
 * their own words in the subject line. Signing them keeps the existing shape of
 * the forms while making them tamper-evident.
 */
function elahub_form_sign(string $value): string {
	return hash_hmac('sha256', $value, wp_salt('elahub_form_spam'));
}

/** Is this value exactly what we put in the page? */
function elahub_form_signature_valid(string $value, string $signature): bool {
	return $signature !== '' && hash_equals(elahub_form_sign($value), $signature);
}

/** Sign the render timestamp so the timing check cannot simply be edited. */
function elahub_form_sign_timestamp(string $ts): string {
	return elahub_form_sign($ts);
}

/** Print a hidden input alongside its signature. */
function elahub_form_signed_field(string $name, string $value): void {
	printf(
		'<input type="hidden" name="%1$s" value="%2$s"><input type="hidden" name="%1$s_sig" value="%3$s">',
		esc_attr($name),
		esc_attr($value),
		esc_attr(elahub_form_sign($value))
	);
}

/**
 * Read a signed value back out of a submission.
 *
 * Returns '' when the signature is missing or does not match, so the caller
 * falls back to its own default rather than trusting whatever was posted.
 */
function elahub_form_signed_value(array $post, string $name): string {
	$value = (string) ($post[$name] ?? '');
	$sig   = (string) ($post[$name . '_sig'] ?? '');

	if ($value === '') {
		return '';
	}

	if (! elahub_form_signature_valid($value, $sig)) {
		elahub_form_spam_log('Ignoring tampered "' . $name . '" on a form submission.');
		return '';
	}

	return $value;
}

/* -------------------------------------------------------------------------
 * Server-side check
 * ---------------------------------------------------------------------- */

/**
 * Run all three layers against a submission.
 *
 * @param array  $post   Usually $_POST.
 * @param string $action reCAPTCHA action name, for the score lookup.
 * @return true|string   true when clean, otherwise a short internal reason.
 */
function elahub_form_spam_check(array $post, string $action = 'submit') {

	// 1. Honeypot.
	if (trim((string) ($post[ELAHUB_HP_FIELD] ?? '')) !== '') {
		return 'honeypot filled';
	}

	// 2. Timing.
	$ts  = (string) ($post[ELAHUB_TS_FIELD] ?? '');
	$sig = (string) ($post[ELAHUB_TSIG_FIELD] ?? '');

	if ($ts === '' || $sig === '' || ! hash_equals(elahub_form_sign_timestamp($ts), $sig)) {
		return 'timestamp missing or not signed by us';
	}

	$age = time() - (int) $ts;

	if ($age < ELAHUB_MIN_FILL_SECONDS) {
		return sprintf('submitted %ds after render', max(0, $age));
	}

	if ($age > ELAHUB_MAX_FILL_SECONDS) {
		return 'form older than the replay window';
	}

	// 3. reCAPTCHA, only when configured.
	if (elahub_recaptcha_active()) {
		$verdict = elahub_verify_recaptcha((string) ($post[ELAHUB_TOKEN_FIELD] ?? ''), $action);
		if ($verdict !== true) {
			return $verdict;
		}
	}

	return true;
}

/**
 * Ask Google to score the token.
 *
 * A network failure deliberately passes. If Google is unreachable the other two
 * layers still apply, and turning away real enquiries because a third party is
 * down is the worse outcome.
 *
 * @return true|string
 */
function elahub_verify_recaptcha(string $token, string $action) {

	if ($token === '') {
		return 'no reCAPTCHA token';
	}

	$response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
		'timeout' => 10,
		'body'    => array(
			'secret'   => elahub_recaptcha_key('secret'),
			'response' => $token,
			'remoteip' => elahub_client_ip(),
		),
	));

	if (is_wp_error($response)) {
		elahub_form_spam_log('reCAPTCHA unreachable, allowing: ' . $response->get_error_message());
		return true;
	}

	$body = json_decode((string) wp_remote_retrieve_body($response), true);

	if (! is_array($body)) {
		elahub_form_spam_log('reCAPTCHA returned something unreadable, allowing.');
		return true;
	}

	if (empty($body['success'])) {
		$codes = implode(', ', (array) ($body['error-codes'] ?? array()));

		// A key problem is ours, not the visitor's, so let them through and
		// make the misconfiguration visible in the log instead.
		$our_fault = array('invalid-input-secret', 'missing-input-secret', 'bad-request');
		foreach ($our_fault as $code) {
			if (strpos($codes, $code) !== false) {
				elahub_form_spam_log('reCAPTCHA key problem (' . $codes . '), allowing. Check Theme Settings > Integrations.');
				return true;
			}
		}

		return 'reCAPTCHA rejected the token (' . $codes . ')';
	}

	/**
	 * Score below which a submission is treated as automated.
	 *
	 * @param float  $threshold
	 * @param string $action
	 */
	$threshold = (float) apply_filters('elahub_recaptcha_threshold', 0.5, $action);
	$score     = isset($body['score']) ? (float) $body['score'] : 1.0;

	if ($score < $threshold) {
		return sprintf('reCAPTCHA score %.2f below %.2f', $score, $threshold);
	}

	return true;
}

/** Best-effort client IP, used only as a reCAPTCHA hint. */
function elahub_client_ip(): string {
	$ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
	return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
}

/** One line per blocked submission, readable in WooCommerce > Status > Logs. */
function elahub_form_spam_log(string $message): void {
	error_log('[elahub] ' . $message);
	if (function_exists('wc_get_logger')) {
		wc_get_logger()->info($message, array('source' => 'elahub-form-spam'));
	}
}

/* -------------------------------------------------------------------------
 * reCAPTCHA script, only on pages that actually carry a form
 * ---------------------------------------------------------------------- */

add_action('wp_footer', 'elahub_recaptcha_footer_script', 5);

function elahub_recaptcha_footer_script(): void {

	if (empty($GLOBALS['elahub_form_on_page']) || ! elahub_recaptcha_active()) {
		return;
	}

	$site_key = elahub_recaptcha_key('site');

	// The consent gate treats google.com/recaptcha as strictly necessary and
	// never defers it, so this runs before a visitor has answered the banner.
	?>
	<script src="https://www.google.com/recaptcha/api.js?render=<?php echo rawurlencode($site_key); ?>"></script>
	<script>
	(function () {
		var SITE_KEY = <?php echo wp_json_encode($site_key); ?>;
		var FIELD    = <?php echo wp_json_encode(ELAHUB_TOKEN_FIELD); ?>;

		/**
		 * Resolve a fresh token. Tokens expire after two minutes, so one is
		 * fetched per submission rather than at page load.
		 */
		window.elahubRecaptchaToken = function (action) {
			return new Promise(function (resolve) {
				if (typeof grecaptcha === 'undefined' || !grecaptcha.ready) {
					resolve('');
					return;
				}
				grecaptcha.ready(function () {
					grecaptcha.execute(SITE_KEY, { action: action || 'submit' })
						.then(resolve)
						.catch(function () { resolve(''); });
				});
			});
		};

		// The contact form posts normally rather than over AJAX, so hold the
		// submit, fetch a token, then let it go through.
		document.addEventListener('DOMContentLoaded', function () {
			document.querySelectorAll('form.elahub-form__fields').forEach(function (form) {
				if (form.querySelector('[name="_nonce"]')) {
					return; // AJAX form, handled in elahub-form.js.
				}
				form.addEventListener('submit', function (e) {
					var input = form.querySelector('[name="' + FIELD + '"]');
					if (!input || input.value) {
						return; // Nothing to fill, or already tokenised.
					}
					e.preventDefault();
					window.elahubRecaptchaToken('contact').then(function (token) {
						input.value = token;
						form.submit();
					});
				});
			});
		});
	})();
	</script>
	<?php
}
