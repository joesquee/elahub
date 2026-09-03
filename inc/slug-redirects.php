<?php

/**
 * Legacy slug redirects (301)
 *
 * After the June 2026 SEO slug changes, old URLs are permanently redirected
 * to their new locations so existing inbound links and search rankings carry
 * over. Keep this file until the old URLs have fully dropped out of search
 * indexes (then it can be removed).
 *
 *   OLD                                  NEW
 *   /case-studies/...                ->  /accessible-learning-case-studies/...
 *   /learning-hub/books/...          ->  /learning-hub/books-and-publications/...
 *   /learning-hub/articles-reports/. ->  /learning-hub/industry-reports/...
 *   /blog/                           ->  /learning-hub/accessible-learning-insights/
 *
 * The first three are prefix redirects (individual posts/items move with the
 * base). /blog/ is the Insights "posts page" landing, redirected exactly —
 * WordPress' built-in old-slug redirect does not fire for the posts page.
 *
 * Works on any install location (subdirectory or root) because the WordPress
 * home path is stripped before matching and home_url() re-adds it on output.
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Resolve the request path relative to the WordPress home, with a single
 * leading slash and no trailing slash (root returns '/').
 */
function elahub_relative_request_path(): string
{
	$req = (string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
	$req = '/' . trim($req, '/');

	$home_path = trim((string) wp_parse_url(home_url('/'), PHP_URL_PATH), '/');

	if ('' !== $home_path) {
		$base = '/' . $home_path;
		if (0 === strpos($req, $base . '/')) {
			$req = substr($req, strlen($base));
		} elseif ($req === $base) {
			$req = '/';
		}
	}

	return '' === $req ? '/' : $req;
}

function elahub_legacy_slug_redirects()
{
	if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX) || (defined('REST_REQUEST') && REST_REQUEST)) {
		return;
	}

	$path  = elahub_relative_request_path();           // e.g. /case-studies/foo
	$match = '/' === $path ? '/' : rtrim($path, '/') . '/';

	$query = (string) ($_SERVER['QUERY_STRING'] ?? '');

	// ── Prefix redirects: everything under the old base moves to the new base ──
	$prefix_map = [
		'/case-studies/'                  => '/accessible-learning-case-studies/',
		'/learning-hub/books/'            => '/learning-hub/books-and-publications/',
		'/learning-hub/articles-reports/' => '/learning-hub/industry-reports/',
	];

	foreach ($prefix_map as $old => $new) {
		if (0 === strpos($match, $old)) {
			$rest   = substr($match, strlen($old));   // remainder after the old base
			$target = home_url($new . $rest);
			if ('' !== $query) {
				$target .= '?' . $query;
			}
			wp_safe_redirect($target, 301);
			exit;
		}
	}

	// ── Exact redirect: the Insights posts-page landing ──
	if ('/blog/' === $match) {
		$target = home_url('/learning-hub/accessible-learning-insights/');
		if ('' !== $query) {
			$target .= '?' . $query;
		}
		wp_safe_redirect($target, 301);
		exit;
	}
}
add_action('template_redirect', 'elahub_legacy_slug_redirects', 1);
