<?php

/**
 * SVG uploads for administrators.
 *
 * Award and partner logos arrive as SVG, and WordPress rejects the type by
 * default. This allows it for administrators only, which is the same approach
 * used on the other Squee-built sites.
 *
 * SVG is an executable format: a file can carry script. Restricting the
 * capability to administrators means an untrusted author or contributor account
 * cannot introduce one. Only upload SVGs from a source you trust, i.e. the
 * client's own brand files or a designer's export.
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Add SVG to the allowed upload types for administrators.
 *
 * @param array $mimes Allowed mime types.
 * @return array
 */
function elahub_allow_svg_uploads($mimes)
{
	if (! current_user_can('manage_options')) {
		return $mimes;
	}

	$mimes['svg']  = 'image/svg+xml';
	$mimes['svgz'] = 'image/svg+xml';

	return $mimes;
}
add_filter('upload_mimes', 'elahub_allow_svg_uploads');

/**
 * WordPress also runs its own extension/type check on the real file, which
 * fails SVGs even when the mime is allowed. Correct the result for SVG uploads
 * by administrators.
 *
 * @param array  $data     File data (ext, type, proper_filename).
 * @param string $file     Full path to the file.
 * @param string $filename The name of the file.
 * @param array  $mimes    Allowed mime types.
 * @return array
 */
function elahub_fix_svg_filetype_check($data, $file, $filename, $mimes)
{
	if (! current_user_can('manage_options')) {
		return $data;
	}

	if (! empty($data['ext']) && ! empty($data['type'])) {
		return $data;
	}

	$check = wp_check_filetype($filename, $mimes);

	if ('svg' === $check['ext'] || 'svgz' === $check['ext']) {
		$data['ext']  = $check['ext'];
		$data['type'] = 'image/svg+xml';
	}

	return $data;
}
add_filter('wp_check_filetype_and_ext', 'elahub_fix_svg_filetype_check', 10, 4);

/**
 * Give SVGs a sensible width and height in the admin, so the Media Library grid
 * and ACF image fields do not render them at full container width.
 */
function elahub_svg_admin_thumbnail_css()
{
	echo '<style>img[src$=".svg"], img[src$=".svgz"] { max-width: 100%; height: auto; }</style>';
}
add_action('admin_head', 'elahub_svg_admin_thumbnail_css');
