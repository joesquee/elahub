<?php

/**
 * Google Tag Manager.
 *
 * The container carries everything: GA4 (G-YZ1NQGCLV0) and the LinkedIn
 * Insight Tag are both configured inside GTM rather than hardcoded here, which
 * is why the measurement ID never appears in the page source. Adding tags is a
 * GTM job, not a theme job — nothing below should need editing for a new tag.
 *
 * Carried over from the previous elahub.net build, which fired the same
 * container. Without this the new site collects nothing at all.
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
	exit;
}

if (! defined('ELAHUB_GTM_ID')) {
	define('ELAHUB_GTM_ID', 'GTM-K98MDMTK');
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
