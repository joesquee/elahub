<?php

/**
 * Learning Hub CPT
 *
 * Registers the `learning_hub_item` post type and its two taxonomies:
 *   - learning_hub_type     : the section (Accessible Learning Live, Books, etc.)
 *                             Rewrite slug = 'learning-hub' → /learning-hub/{type-slug}/
 *   - learning_hub_category : optional category filter within each type
 *
 * Both taxonomy archives are routed to archive-learning_hub_item.php via
 * template_include so a single template handles all four sections.
 *
 * Requires in functions.php:
 *   require get_template_directory() . '/inc/learning-hub-cpt.php';
 *
 * @package elahub
 */

/* ── Post type ─────────────────────────────────────────────────── */

function elahub_register_learning_hub_cpt()
{
	register_post_type(
		'learning_hub_item',
		[
			'labels' => [
				'name'               => __('Learning Hub Items', 'elahub'),
				'singular_name'      => __('Learning Hub Item', 'elahub'),
				'add_new'            => __('Add New', 'elahub'),
				'add_new_item'       => __('Add New Item', 'elahub'),
				'edit_item'          => __('Edit Item', 'elahub'),
				'all_items'          => __('All Items', 'elahub'),
				'search_items'       => __('Search Items', 'elahub'),
				'not_found'          => __('No items found.', 'elahub'),
				'not_found_in_trash' => __('No items found in Trash.', 'elahub'),
			],
			'public'             => true,
			'show_ui'            => true,
			'show_in_rest'       => true,
			'has_archive'        => false,               // sections live on type taxonomy archives
			'rewrite'            => ['slug' => 'learning-hub-item'],
			'menu_icon'          => 'dashicons-book-alt',
			'supports'           => ['title', 'editor', 'excerpt', 'thumbnail'],
			'publicly_queryable' => true,
			'show_in_menu'       => 'learning-hub-admin', // custom parent menu
		]
	);
}
add_action('init', 'elahub_register_learning_hub_cpt');

/* ── Taxonomies ────────────────────────────────────────────────── */

function elahub_register_learning_hub_taxonomies()
{

	/**
	 * Type taxonomy — determines which Learning Hub section an item belongs to.
	 * Rewrite slug 'learning-hub' gives clean URLs:
	 *   /learning-hub/books-and-publications/
	 *   /learning-hub/accessible-learning-live/
	 */
	register_taxonomy(
		'learning_hub_type',
		['learning_hub_item'],
		[
			'label'        => __('Type', 'elahub'),
			'public'       => true,
			'show_ui'      => true,
			'hierarchical' => false,
			'show_in_rest' => true,
			'rewrite'      => ['slug' => 'learning-hub'],
			'show_in_menu' => false,  // surfaced under custom admin menu instead
		]
	);

	/**
	 * Category taxonomy — optional filter within each type.
	 * e.g. /learning-hub-category/authoring-tools/
	 */
	register_taxonomy(
		'learning_hub_category',
		['learning_hub_item'],
		[
			'label'        => __('Category', 'elahub'),
			'public'       => true,
			'show_ui'      => true,
			'hierarchical' => true,
			'show_in_rest' => true,
			'rewrite'      => ['slug' => 'learning-hub-category'],
			'show_in_menu' => false,
		]
	);
}
add_action('init', 'elahub_register_learning_hub_taxonomies');

/* ── Insights posts-page rewrite ───────────────────────────────── */

/**
 * The Insights "posts page" lives at /learning-hub/accessible-learning-insights/,
 * nested under the Learning Hub overview page.
 *
 * Because the learning_hub_type taxonomy uses 'learning-hub' as its rewrite
 * base, the generic taxonomy rule (learning-hub/{slug}/) would otherwise
 * capture this path first and 404 (there is no 'accessible-learning-insights'
 * type term). These higher-priority ('top') rules resolve the path to the
 * posts page instead, including its pagination.
 *
 * Flush permalinks (Settings > Permalinks > Save) after deploying.
 */
function elahub_insights_posts_page_rewrites()
{
	add_rewrite_rule(
		'^learning-hub/accessible-learning-insights/?$',
		'index.php?pagename=learning-hub/accessible-learning-insights',
		'top'
	);
	add_rewrite_rule(
		'^learning-hub/accessible-learning-insights/page/?([0-9]{1,})/?$',
		'index.php?pagename=learning-hub/accessible-learning-insights&paged=$matches[1]',
		'top'
	);
}
add_action('init', 'elahub_insights_posts_page_rewrites');

/* ── Custom admin menu ─────────────────────────────────────────── */

function elahub_learning_hub_admin_menu()
{
	add_menu_page(
		__('Learning Hub', 'elahub'),
		__('Learning Hub', 'elahub'),
		'edit_posts',
		'learning-hub-admin',
		'elahub_learning_hub_admin_page',
		'dashicons-book-alt',
		22
	);

	// Items sub-page (mirrors the CPT list table)
	add_submenu_page(
		'learning-hub-admin',
		__('All Items', 'elahub'),
		__('All Items', 'elahub'),
		'edit_posts',
		'edit.php?post_type=learning_hub_item'
	);

	add_submenu_page(
		'learning-hub-admin',
		__('Add New Item', 'elahub'),
		__('Add New Item', 'elahub'),
		'edit_posts',
		'post-new.php?post_type=learning_hub_item'
	);

	// Type terms sub-page
	add_submenu_page(
		'learning-hub-admin',
		__('Sections (Types)', 'elahub'),
		__('Sections (Types)', 'elahub'),
		'manage_categories',
		'edit-tags.php?taxonomy=learning_hub_type&post_type=learning_hub_item'
	);

	// Category terms sub-page
	add_submenu_page(
		'learning-hub-admin',
		__('Categories', 'elahub'),
		__('Categories', 'elahub'),
		'manage_categories',
		'edit-tags.php?taxonomy=learning_hub_category&post_type=learning_hub_item'
	);

	// Remove the duplicate auto-created submenu item
	remove_submenu_page('learning-hub-admin', 'learning-hub-admin');
}
add_action('admin_menu', 'elahub_learning_hub_admin_menu');

/**
 * Placeholder landing page (the menu entry itself has no real page,
 * it redirects to All Items).
 */
function elahub_learning_hub_admin_page()
{
	wp_safe_redirect(admin_url('edit.php?post_type=learning_hub_item'));
	exit;
}

/**
 * Highlight the Learning Hub menu when editing a learning_hub_item,
 * or when on a taxonomy screen for its taxonomies.
 */
function elahub_learning_hub_menu_highlight($parent_file)
{
	global $current_screen;

	if (
		in_array($current_screen->post_type, ['learning_hub_item'], true)
		|| in_array($current_screen->taxonomy, ['learning_hub_type', 'learning_hub_category'], true)
	) {
		return 'learning-hub-admin';
	}

	return $parent_file;
}
add_filter('parent_file', 'elahub_learning_hub_menu_highlight');

/* ── Template routing ──────────────────────────────────────────── */

/**
 * Route learning_hub_type and learning_hub_category taxonomy archives
 * through a single archive-learning_hub_item.php template.
 */
function elahub_learning_hub_template($template)
{
	if (is_tax('learning_hub_type') || is_tax('learning_hub_category')) {
		$located = locate_template('archive-learning_hub_item.php');
		if ($located) {
			return $located;
		}
	}

	return $template;
}
add_filter('template_include', 'elahub_learning_hub_template');

/* ── Single post redirect ──────────────────────────────────────── */

/**
 * learning_hub_item posts should never display their own page.
 *
 * If the post has an external URL set, redirect there (external link opens
 * in the same request — the browser will follow the 301).
 * If no external URL is set, redirect to the type taxonomy archive so the
 * visitor lands in the right section rather than a bare post template.
 */
function elahub_learning_hub_single_redirect()
{
	if (! is_singular('learning_hub_item')) {
		return;
	}

	$post_id      = get_the_ID();
	$external_url = '';

	if (function_exists('get_field')) {
		$external_url = trim((string) (get_field('lh_external_url', $post_id) ?: ''));
	}

	if ($external_url) {
		wp_redirect($external_url, 301);
		exit;
	}

	// No external URL — redirect to the type archive (or generic fallback)
	$type_terms = get_the_terms($post_id, 'learning_hub_type');
	if (! empty($type_terms) && ! is_wp_error($type_terms)) {
		$archive = get_term_link($type_terms[0]);
		if (is_string($archive)) {
			wp_redirect($archive, 301);
			exit;
		}
	}

	// Last resort — home
	wp_redirect(home_url('/'), 301);
	exit;
}
add_action('template_redirect', 'elahub_learning_hub_single_redirect');

/* ── Pre-get-posts: when on a category archive, restrict to one type ── */

/**
 * If viewing a learning_hub_category archive, also filter by the type
 * passed as a query var (?lh_type=books).  Editors can link category
 * pills as: get_term_link($cat) . '?lh_type=' . $type_slug
 */
function elahub_learning_hub_category_pre_get($query)
{
	if (! $query->is_main_query() || is_admin()) {
		return;
	}

	if (! is_tax('learning_hub_category')) {
		return;
	}

	$type_slug = sanitize_key($_GET['lh_type'] ?? '');

	if (! $type_slug) {
		return;
	}

	$tax_query = (array) ($query->get('tax_query') ?: []);
	$tax_query[] = [
		'taxonomy' => 'learning_hub_type',
		'field'    => 'slug',
		'terms'    => $type_slug,
	];
	$query->set('tax_query', $tax_query);
}
add_action('pre_get_posts', 'elahub_learning_hub_category_pre_get');
