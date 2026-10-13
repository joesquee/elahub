<?php

/**
 * Google Tag Manager.
 *
 * GA4 (G-YZ1NQGCLV0) is loaded directly by the theme, below. It is NOT in
 * GTM: on 13 Oct 2026 the published GTM-K98MDMTK container was found to be
 * empty (no tags, triggers or rules), so the site loaded GTM but never sent
 * anything to GA4. Squee has no access to that GTM account.
 *
 * The container is still loaded so anything eLaHub adds to it later fires.
 * If GA4 is ever configured inside GTM, set ELAHUB_GA4_ID to '' or the
 * property will count every page view twice.
 *
 * Both tags are gated by the cookie banner (inc/consent) and respect the
 * Consent Mode v2 defaults it prints.
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
	exit;
}

if (! defined('ELAHUB_GTM_ID')) {
	define('ELAHUB_GTM_ID', 'GTM-K98MDMTK');
}

if (! defined('ELAHUB_GA4_ID')) {
	define('ELAHUB_GA4_ID', 'G-YZ1NQGCLV0');
}

/**
 * Container snippet, as high in <head> as the theme can put it.
 *
 * Priority 1 because GTM's own guidance is "as high as possible" — tags that
 * measure page load get less accurate the later the container initialises.
 */
function elahub_gtm_head()
{
	$id = ELAHUB_GTM_ID;

	if (empty($id)) {
		return;
	}
	?>
	<!-- Google Tag Manager -->
	<script>
		(function(w, d, s, l, i) {
			w[l] = w[l] || [];
			w[l].push({
				'gtm.start': new Date().getTime(),
				event: 'gtm.js'
			});
			var f = d.getElementsByTagName(s)[0],
				j = d.createElement(s),
				dl = l != 'dataLayer' ? '&l=' + l : '';
			j.async = true;
			j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
			f.parentNode.insertBefore(j, f);
		})(window, document, 'script', 'dataLayer', <?php echo wp_json_encode($id); ?>);
	</script>
	<!-- End Google Tag Manager -->
	<?php
}
add_action('wp_head', 'elahub_gtm_head', 1);

/**
 * GA4 Google tag, straight after the GTM container.
 *
 * The consent gate rewrites both script tags to inert text/plain until the
 * visitor agrees, then releases them in document order.
 */
function elahub_ga4_head()
{
	$id = ELAHUB_GA4_ID;

	if (empty($id)) {
		return;
	}
	?>
	<!-- Google tag (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($id); ?>"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag() {
			dataLayer.push(arguments);
		}
		gtag('js', new Date());
		gtag('config', <?php echo wp_json_encode($id); ?>);
	</script>
	<!-- End Google tag -->
	<?php
}
add_action('wp_head', 'elahub_ga4_head', 2);

/**
 * The no-JavaScript fallback, immediately after <body>.
 *
 * Google ships this iframe with no accessible name, which trips WCAG 4.1.2 in
 * an audit even though the frame is hidden. The title attribute is our
 * addition and changes nothing about how the tag behaves.
 */
function elahub_gtm_body()
{
	$id = ELAHUB_GTM_ID;

	if (empty($id)) {
		return;
	}
	?>
	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr($id); ?>"
			height="0" width="0" style="display:none;visibility:hidden"
			title="<?php esc_attr_e('Google Tag Manager', 'elahub'); ?>"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->
	<?php
}
add_action('wp_body_open', 'elahub_gtm_body', 1);
