<?php

/**
 * eLaHub cookie consent.
 *
 * A first-party consent gate, ported from the Celebrating Disability build.
 * Compliant with UK PECR / UK GDPR and Google Consent Mode v2.
 *
 * Design notes
 * ------------
 * 1. Nothing non-essential runs before the visitor chooses. Known tracking
 *    tags are rewritten server side into inert <script type="text/plain">
 *    so the browser neither fetches nor executes them. The JS activates
 *    them only once consent exists.
 *
 * 2. The HTML is identical for every visitor regardless of consent state,
 *    so the page stays safe to cache. All consent-dependent behaviour is
 *    client side.
 *
 * 3. Consent Mode v2 defaults are emitted inline as early as possible in
 *    the head, ahead of any Google tag, so Google receives a denied signal
 *    rather than no signal at all.
 *
 * WHAT IS DELIBERATELY NOT GATED
 * ------------------------------
 * reCAPTCHA (google.com/recaptcha, gstatic.com) and PayPal (paypal.com)
 * are treated as strictly necessary: reCAPTCHA is fraud prevention on the
 * checkout and PayPal is the payment processor itself. Gating either one
 * breaks the ability to take money. Decided 21 Sep 2026. Do not add them
 * to the pattern lists below. See self::$never_gate.
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
	exit;
}

class ELaHub_Consent
{

	/** Bump when the categories or the cookie shape change. Invalidates stored consent. */
	const VERSION = '1';

	/** Name of the cookie holding the visitor's choice. */
	const COOKIE = 'elahub_consent';

	/** How long a choice is remembered. ICO guidance points at 6 months. */
	const COOKIE_LIFETIME_DAYS = 182;

	/**
	 * Hosts that must never be gated, whatever the patterns below say.
	 * Checked first, so a future pattern cannot accidentally catch payment
	 * or anti-fraud scripts and take the checkout down.
	 */
	private static $never_gate = array(
		'#google\.com/recaptcha#i',
		'#gstatic\.com/recaptcha#i',
		'#\bpaypal\.com#i',
		'#paypalobjects\.com#i',
	);

	/**
	 * Tags we know about, and the consent category each one belongs to.
	 *
	 * "google" means the Google tag stack: released once the visitor has
	 * agreed to analytics or marketing, with the precise behaviour then
	 * governed by the Consent Mode signals sent per category.
	 */
	private static $src_patterns = array(
		'#googletagmanager\.com/(gtag/js|gtm\.js)#i'            => 'google',
		'#google-analytics\.com/(analytics|ga)\.js#i'           => 'google',
		'#googleadservices\.com|googlesyndication\.com#i'       => 'google',
		// Amplitude: sets AMP_* and AMP_MKTG_* on this site.
		'#amplitude\.com|amplitude-analytics\.com#i'            => 'analytics',
		// Automattic / WooCommerce usage tracking: sets tk_ai and tk_qs.
		'#stats\.wp\.com|pixel\.wp\.com#i'                      => 'analytics',
		// WooCommerce order attribution (Sourcebuster): sets the sbjs_* set.
		// Served from our own domain, so matched on path not host.
		'#/sourcebuster|order-attribution#i'                    => 'analytics',
		'#connect\.facebook\.net#i'                             => 'marketing',
	);

	private static $inline_patterns = array(
		// The GTM container snippet builds its own script element in JS, so the
		// tag in the server HTML is inline with no src. It uses w[l].push()
		// rather than dataLayer.push(), so it has to be matched on the host
		// name it assembles or on gtm.start. Missing this is how the container
		// slipped through the first deploy.
		'#googletagmanager\.com|gtm\.start#i'                    => 'google',
		'#gtag\s*\(|dataLayer\s*\.\s*push|GoogleAnalyticsObject#i' => 'google',
		'#amplitude\s*\.\s*(init|getInstance)#i'                   => 'analytics',
		'#\bsbjs\s*\.\s*init|wc_order_attribution#i'               => 'analytics',
		'#fbq\s*\(#i'                                              => 'marketing',
	);

	public static function init()
	{
		// Consent Mode defaults must beat every other head output.
		add_action('wp_head', array(__CLASS__, 'print_consent_mode_defaults'), -9999);

		add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue'));
		add_action('wp_body_open', array(__CLASS__, 'render_banner'), 1);

		add_action('template_redirect', array(__CLASS__, 'start_buffer'), 1);
	}

	/**
	 * Should this request be processed at all?
	 */
	private static function is_front_end()
	{
		if (is_admin() || is_feed() || is_embed()) {
			return false;
		}
		if (defined('DOING_AJAX') && DOING_AJAX) {
			return false;
		}
		if (defined('DOING_CRON') && DOING_CRON) {
			return false;
		}
		if (defined('REST_REQUEST') && REST_REQUEST) {
			return false;
		}
		if (defined('WP_CLI') && WP_CLI) {
			return false;
		}
		return true;
	}

	/* ---------------------------------------------------------------------
	 * Consent Mode v2
	 * ------------------------------------------------------------------ */

	public static function print_consent_mode_defaults()
	{
		if (! self::is_front_end()) {
			return;
		}
?>
		<script data-elahub-consent-core="1">
			window.dataLayer = window.dataLayer || [];

			function gtag() {
				dataLayer.push(arguments);
			}
			gtag('consent', 'default', {
				'ad_storage': 'denied',
				'ad_user_data': 'denied',
				'ad_personalization': 'denied',
				'analytics_storage': 'denied',
				'personalization_storage': 'denied',
				'functionality_storage': 'granted',
				'security_storage': 'granted',
				'wait_for_update': 500
			});
			gtag('set', 'ads_data_redaction', true);
			gtag('set', 'url_passthrough', true);
		</script>
	<?php
	}

	/* ---------------------------------------------------------------------
	 * Script gating
	 * ------------------------------------------------------------------ */

	public static function start_buffer()
	{
		if (! self::is_front_end()) {
			return;
		}
		ob_start(array(__CLASS__, 'gate_tracking_scripts'));
	}

	public static function gate_tracking_scripts($html)
	{
		if ('' === trim((string) $html) || false === stripos($html, '<script')) {
			return $html;
		}

		$html = preg_replace_callback(
			'#<script\b([^>]*)>(.*?)</script>#is',
			array(__CLASS__, 'gate_script_tag'),
			$html
		);

		// A GTM noscript iframe cannot be consented to, because a visitor with
		// JavaScript disabled can never operate the banner. Remove it.
		$html = preg_replace(
			'#<noscript>\s*<iframe[^>]*googletagmanager\.com/ns\.html[^>]*>.*?</iframe>\s*</noscript>#is',
			'',
			$html
		);

		return $html;
	}

	private static function gate_script_tag($m)
	{
		$attrs = $m[1];
		$body  = $m[2];

		// Never touch our own consent scripts.
		if (false !== stripos($attrs, 'data-elahub-consent')) {
			return $m[0];
		}

		// Structured data, templates and already-inert scripts are left alone.
		if (preg_match('#type\s*=\s*["\']([^"\']+)["\']#i', $attrs, $type)) {
			$t = strtolower($type[1]);
			if (false === strpos($t, 'javascript') && 'module' !== $t) {
				return $m[0];
			}
		}

		$has_src = preg_match('#\bsrc\s*=\s*["\']([^"\']+)["\']#i', $attrs, $src_match);

		// Payment and anti-fraud scripts are strictly necessary. Bail before
		// the category patterns get a look at them.
		if ($has_src) {
			foreach (self::$never_gate as $safe) {
				if (preg_match($safe, $src_match[1])) {
					return $m[0];
				}
			}
		}

		$category = null;

		if ($has_src) {
			foreach (self::$src_patterns as $pattern => $cat) {
				if (preg_match($pattern, $src_match[1])) {
					$category = $cat;
					break;
				}
			}
		} else {
			foreach (self::$inline_patterns as $pattern => $cat) {
				if (preg_match($pattern, $body)) {
					$category = $cat;
					break;
				}
			}
		}

		if (null === $category) {
			return $m[0];
		}

		// Move src out of the way and mark the tag inert.
		$new_attrs = preg_replace('#\bsrc\s*=\s*(["\'])([^"\']*)\1#i', 'data-elahub-src=$1$2$1', $attrs);
		$new_attrs = preg_replace('#\btype\s*=\s*(["\'])[^"\']*\1#i', '', $new_attrs);
		$new_attrs = trim($new_attrs);

		return '<script type="text/plain" data-elahub-consent="' . esc_attr($category) . '" '
			. $new_attrs . '>' . $body . '</script>';
	}

	/* ---------------------------------------------------------------------
	 * Assets
	 * ------------------------------------------------------------------ */

	public static function enqueue()
	{
		if (! self::is_front_end()) {
			return;
		}

		$dir = get_template_directory() . '/assets/consent/';
		$uri = get_template_directory_uri() . '/assets/consent/';

		wp_enqueue_style(
			'elahub-consent',
			$uri . 'consent.css',
			array(),
			file_exists($dir . 'consent.css') ? filemtime($dir . 'consent.css') : self::VERSION
		);

		wp_enqueue_script(
			'elahub-consent',
			$uri . 'consent.js',
			array(),
			file_exists($dir . 'consent.js') ? filemtime($dir . 'consent.js') : self::VERSION,
			false // In the head: the gate has to run before first paint.
		);

		wp_add_inline_script(
			'elahub-consent',
			'window.ELAHUB_CONSENT_CONFIG = ' . wp_json_encode(array(
				'cookie'   => self::COOKIE,
				'version'  => self::VERSION,
				'lifetime' => self::COOKIE_LIFETIME_DAYS,
				'secure'   => is_ssl(),
			)) . ';',
			'before'
		);

		// Mark our own script so the gate skips it.
		add_filter('script_loader_tag', function ($tag, $handle) {
			if ('elahub-consent' === $handle) {
				$tag = str_replace('<script ', '<script data-elahub-consent-core="1" ', $tag);
			}
			return $tag;
		}, 10, 2);
	}

	/* ---------------------------------------------------------------------
	 * Markup
	 * ------------------------------------------------------------------ */

	public static function render_banner()
	{
		if (! self::is_front_end()) {
			return;
		}

		$privacy = esc_url(site_url('/privacy-policy/'));
	?>
		<div class="elahub-consent" data-elahub-consent-root hidden>

			<section class="elahub-consent__banner"
				role="region"
				aria-labelledby="elahub-consent-banner-title"
				data-elahub-consent-banner
				hidden>
				<div class="elahub-consent__banner-inner">
					<div class="elahub-consent__banner-text">
						<h2 class="elahub-consent__title" id="elahub-consent-banner-title">Cookies on this site</h2>
						<p>
							We use essential cookies to make this site work, including keeping your
							basket and your course purchase working. We would also like to set optional
							cookies that help us understand how the site is used. We will not set
							optional cookies unless you turn them on. You can change your choice at any
							time. <a href="<?php echo $privacy; ?>">Read our privacy policy</a>.
						</p>
					</div>
					<div class="elahub-consent__actions">
						<button type="button" class="elahub-consent__btn elahub-consent__btn--primary" data-elahub-consent-accept>
							Accept optional cookies
						</button>
						<button type="button" class="elahub-consent__btn elahub-consent__btn--primary" data-elahub-consent-reject>
							Reject optional cookies
						</button>
						<button type="button" class="elahub-consent__btn elahub-consent__btn--link" data-elahub-consent-open>
							Manage preferences
						</button>
					</div>
				</div>
			</section>

			<div class="elahub-consent__overlay" data-elahub-consent-overlay hidden>
				<div class="elahub-consent__modal"
					role="dialog"
					aria-modal="true"
					aria-labelledby="elahub-consent-modal-title"
					aria-describedby="elahub-consent-modal-desc"
					data-elahub-consent-modal
					tabindex="-1">

					<div class="elahub-consent__modal-head">
						<h2 class="elahub-consent__title" id="elahub-consent-modal-title">Cookie preferences</h2>
						<button type="button"
							class="elahub-consent__close"
							data-elahub-consent-close
							aria-label="Close cookie preferences">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>

					<div class="elahub-consent__modal-body">
						<p id="elahub-consent-modal-desc">
							Choose which cookies you are happy for us to use. Your choice is saved for
							six months, and you can come back and change it whenever you like using the
							Cookie preferences link in the footer.
						</p>

						<fieldset class="elahub-consent__group">
							<legend class="elahub-consent__group-title">Essential cookies</legend>
							<p class="elahub-consent__group-desc">
								These keep the site secure, remember your basket during checkout, and
								remember your cookie choice. They also cover the checks that protect
								card payments from fraud. The site cannot work properly without them,
								so they are always on.
							</p>
							<p class="elahub-consent__always">Always active</p>
						</fieldset>

						<fieldset class="elahub-consent__group">
							<legend class="elahub-consent__group-title">Analytics cookies</legend>
							<p class="elahub-consent__group-desc">
								These tell us which pages people visit and how they move around the
								site, so we know what is useful and what needs improving. We use
								Google Analytics and Amplitude for this.
							</p>
							<div class="elahub-consent__choice">
								<input type="checkbox" id="elahub-consent-analytics" data-elahub-consent-toggle="analytics">
								<label for="elahub-consent-analytics">Use analytics cookies</label>
							</div>
						</fieldset>

						<fieldset class="elahub-consent__group">
							<legend class="elahub-consent__group-title">Marketing cookies</legend>
							<p class="elahub-consent__group-desc">
								These let us measure how well our advertising works and how many people
								get in touch after seeing it.
							</p>
							<div class="elahub-consent__choice">
								<input type="checkbox" id="elahub-consent-marketing" data-elahub-consent-toggle="marketing">
								<label for="elahub-consent-marketing">Use marketing cookies</label>
							</div>
						</fieldset>
					</div>

					<div class="elahub-consent__modal-foot">
						<button type="button" class="elahub-consent__btn elahub-consent__btn--primary" data-elahub-consent-save>
							Save my choices
						</button>
						<button type="button" class="elahub-consent__btn elahub-consent__btn--secondary" data-elahub-consent-accept>
							Accept all
						</button>
						<button type="button" class="elahub-consent__btn elahub-consent__btn--secondary" data-elahub-consent-reject>
							Reject all
						</button>
					</div>
				</div>
			</div>

			<p class="elahub-consent__status" role="status" data-elahub-consent-status></p>
		</div>
<?php
	}
}

ELaHub_Consent::init();
