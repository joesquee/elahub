<?php

/**
 * elahub functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package elahub
 */

if (! defined('_S_VERSION')) {
	define('_S_VERSION', '1.0.0');
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function elahub_setup()
{
	load_theme_textdomain('elahub', get_template_directory() . '/languages');

	add_theme_support('automatic-feed-links');
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');

	register_nav_menus(
		array(
			'menu-1'                  => esc_html__('Primary', 'elahub'),
			'footer-training'         => esc_html__('Footer - Training & Programmes', 'elahub'),
			'footer-services'         => esc_html__('Footer - Accessibility Services', 'elahub'),
			'footer-learning-hub'     => esc_html__('Footer - Learning Hub', 'elahub'),
			'footer-legal'            => esc_html__('Footer - Legal', 'elahub'),
		)
	);

	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	add_theme_support(
		'custom-background',
		apply_filters(
			'elahub_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	add_theme_support('customize-selective-refresh-widgets');

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
}
add_action('after_setup_theme', 'elahub_setup');

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * @global int $content_width
 */
function elahub_content_width()
{
	$GLOBALS['content_width'] = apply_filters('elahub_content_width', 640);
}
add_action('after_setup_theme', 'elahub_content_width', 0);

/**
 * Register widget area.
 */
function elahub_widgets_init()
{
	register_sidebar(
		array(
			'name'          => esc_html__('Sidebar', 'elahub'),
			'id'            => 'sidebar-1',
			'description'   => esc_html__('Add widgets here.', 'elahub'),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action('widgets_init', 'elahub_widgets_init');

/**
 * Enqueue scripts and styles.
 */
function elahub_scripts()
{
	wp_enqueue_style('elahub-style', get_stylesheet_uri(), array(), _S_VERSION);
	wp_style_add_data('elahub-style', 'rtl', 'replace');

	wp_enqueue_style(
		'font-awesome',
		'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css',
		array(),
		'6.7.2'
	);

	$output_css_path = get_template_directory() . '/output.css';
	$output_css_uri  = get_template_directory_uri() . '/output.css';
	$output_css_ver  = file_exists($output_css_path) ? filemtime($output_css_path) : _S_VERSION;

	wp_enqueue_style(
		'elahub-output',
		$output_css_uri,
		array('elahub-style'),
		$output_css_ver
	);

	wp_enqueue_script('elahub-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true);

	$form_js_path = get_template_directory() . '/js/elahub-form.js';
	if ( file_exists( $form_js_path ) ) {
		wp_enqueue_script(
			'elahub-form',
			get_template_directory_uri() . '/js/elahub-form.js',
			array(),
			filemtime( $form_js_path ),
			true
		);
		wp_localize_script( 'elahub-form', 'elahubForm', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		) );
	}

	$desktop_nav_path = get_template_directory() . '/js/elahub-nav-desktop.js';
	$mobile_nav_path  = get_template_directory() . '/js/elahub-nav-mobile.js';

	wp_enqueue_script(
		'elahub-nav-desktop',
		get_template_directory_uri() . '/js/elahub-nav-desktop.js',
		array(),
		file_exists($desktop_nav_path) ? filemtime($desktop_nav_path) : _S_VERSION,
		true
	);

	wp_enqueue_script(
		'elahub-nav-mobile',
		get_template_directory_uri() . '/js/elahub-nav-mobile.js',
		array(),
		file_exists($mobile_nav_path) ? filemtime($mobile_nav_path) : _S_VERSION,
		true
	);

	// Sticky table of contents on the templates that render one.
	if (is_singular(array('post', 'cornerstone_article', 'case_study'))) {
		$toc_js_path = get_template_directory() . '/js/elahub-toc.js';

		wp_enqueue_script(
			'elahub-toc',
			get_template_directory_uri() . '/js/elahub-toc.js',
			array(),
			file_exists($toc_js_path) ? filemtime($toc_js_path) : _S_VERSION,
			true
		);
	}

	if (is_singular() && comments_open() && get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}
}
add_action('wp_enqueue_scripts', 'elahub_scripts');

/**
 * Footer option helpers.
 */
function elahub_get_option_field($field_name, $default = '')
{
	if (function_exists('get_field')) {
		$value = get_field($field_name, 'option');

		if (null !== $value && '' !== $value && array() !== $value) {
			return $value;
		}
	}

	return $default;
}

function elahub_get_footer_contact_items()
{
	$items = elahub_get_option_field('footer_contact_items', array());

	return is_array($items) ? $items : array();
}

function elahub_get_footer_social_links()
{
	$items = elahub_get_option_field('footer_social_links', array());

	return is_array($items) ? $items : array();
}

function elahub_get_footer_certifications()
{
	$items = elahub_get_option_field('footer_certifications', array());

	return is_array($items) ? $items : array();
}


/**
 * Logo strip option helpers.
 *
 * Pass 'advocacy' to fetch the Advocacy Logos Defaults set (managed under
 * Global Content > Advocacy Logos Defaults). Any other value (or omitted)
 * returns the standard Logo Strip Defaults set.
 */
/**
 * Find a published page by its page template, whatever its slug or parent.
 *
 * Pages here have been renamed and re-parented more than once (the advocacy
 * partners page alone has been both /advocacy-partners/ and
 * /elahub-advocacy-partners/), so matching on the template is far more durable
 * than matching on a path.
 *
 * @param string $template Template file name, e.g. 'template-advocacy-partners.php'.
 * @return string Permalink, or '' if no page uses that template.
 */
function elahub_get_page_url_by_template($template)
{
	$cache_key = 'elahub_page_url_tpl_' . md5((string) $template);
	$cached    = wp_cache_get($cache_key, 'elahub');

	if (false !== $cached) {
		return (string) $cached;
	}

	$pages = get_posts(array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'meta_key'       => '_wp_page_template',
		'meta_value'     => $template,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	));

	$url = $pages ? (string) get_permalink($pages[0]) : '';

	wp_cache_set($cache_key, $url, 'elahub');

	return $url;
}

/**
 * URL of the advocacy partners page.
 *
 * Used as the fallback destination for the advocacy logo strip heading, so the
 * heading above the partner logos links through to the partners themselves
 * without anyone having to set an option first. An explicit
 * advocacy_logos_default_url in Site Settings still wins.
 *
 * @return string
 */
function elahub_get_advocacy_partners_url()
{
	return elahub_get_page_url_by_template('template-advocacy-partners.php');
}

/**
 * URL of the Awards page.
 *
 * Matched on the slug rather than the full path, because the page sits under
 * /about-us/ and could be re-parented again.
 *
 * @return string
 */
function elahub_get_awards_page_url()
{
	$cached = wp_cache_get('elahub_awards_url', 'elahub');

	if (false !== $cached) {
		return (string) $cached;
	}

	$pages = get_posts(array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'name'           => 'awards',
		'fields'         => 'ids',
		'no_found_rows'  => true,
	));

	$url = $pages ? (string) get_permalink($pages[0]) : '';

	wp_cache_set('elahub_awards_url', $url, 'elahub');

	return $url;
}

function elahub_get_logo_strip_defaults($set = 'standard')
{
	if ('advocacy' === $set) {
		return array(
			'heading' => elahub_get_option_field('advocacy_logos_default_heading', 'In partnership with'),
			'logos'   => elahub_get_option_field('advocacy_logos_default_logos', array()),
			'url'     => elahub_get_option_field(
				'advocacy_logos_default_url',
				elahub_get_advocacy_partners_url()
			),
		);
	}

	return array(
		'heading' => elahub_get_option_field('logo_strip_default_heading', 'Chosen by over 145 organisations committed to inclusion'),
		'logos'   => elahub_get_option_field('logo_strip_default_logos', array()),
		'url'     => elahub_get_option_field('logo_strip_default_url', ''),
	);
}

/**
 * ACF options pages.
 */
function elahub_register_acf_options_pages()
{
	if (! function_exists('acf_add_options_page')) {
		return;
	}

	$parent = acf_add_options_page(
		array(
			'page_title' => 'Theme Settings',
			'menu_title' => 'Theme Settings',
			'menu_slug'  => 'elahub-theme-settings',
			'capability' => 'edit_posts',
			'redirect'   => true,
			'position'   => 61,
			'icon_url'   => 'dashicons-admin-generic',
		)
	);

	if (! $parent) {
		return;
	}

	acf_add_options_sub_page(
		array(
			'page_title'  => 'Footer Settings',
			'menu_title'  => 'Footer',
			'parent_slug' => $parent['menu_slug'],
		)
	);

	acf_add_options_sub_page(
		array(
			'page_title'  => 'Header Settings',
			'menu_title'  => 'Header',
			'parent_slug' => $parent['menu_slug'],
		)
	);

	acf_add_options_sub_page(
		array(
			'page_title'  => 'Global Content',
			'menu_title'  => 'Global Content',
			'parent_slug' => $parent['menu_slug'],
		)
	);

	acf_add_options_sub_page(
		array(
			'page_title'  => 'Archive Intros',
			'menu_title'  => 'Archive Intros',
			'parent_slug' => $parent['menu_slug'],
			'menu_slug'   => 'elahub-archive-intros',
		)
	);

	acf_add_options_sub_page(
		array(
			'page_title'  => 'Archive Content',
			'menu_title'  => 'Archive Content',
			'parent_slug' => $parent['menu_slug'],
			'menu_slug'   => 'elahub-archive-content',
		)
	);
}
add_action('acf/init', 'elahub_register_acf_options_pages');

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if (defined('JETPACK__VERSION')) {
	require get_template_directory() . '/inc/jetpack.php';
}

require_once get_template_directory() . '/inc/class-elahub-nav-walker.php';
require_once get_template_directory() . '/inc/class-elahub-mobile-nav-walker.php';
require get_template_directory() . '/inc/cornerstone-cpt.php';
require get_template_directory() . '/inc/faq-cpt.php';

require get_template_directory() . '/inc/case-study-cpt.php';

// Seed script — comment out after running once.
require_once get_template_directory() . '/inc/elahub-page-icons.php';
require_once get_template_directory() . '/inc/seed-page-config.php';
require_once get_template_directory() . '/inc/seed-cornerstone-articles.php';
require_once get_template_directory() . '/inc/acf-fields-advocacy-partners.php';
require_once get_template_directory() . '/inc/acf-fields-archive-content.php';
require_once get_template_directory() . '/inc/faq-schema.php';
require_once get_template_directory() . '/inc/acf-fields-hero.php';
require_once get_template_directory() . '/inc/acf-fields-archive-intros.php';
require_once get_template_directory() . '/inc/seed-advocacy-partners.php';
require_once get_template_directory() . '/inc/seed-advocacy-logo-strip.php';
require_once get_template_directory() . '/inc/yoast-acf-content-analysis.php';
require get_template_directory() . '/inc/learning-hub-cpt.php';
require get_template_directory() . '/inc/contact-page-handler.php';
require get_template_directory() . '/inc/form-handler.php';
require get_template_directory() . '/inc/acf-fields-thank-you.php';
require get_template_directory() . '/inc/dalc-checkout.php';
require get_template_directory() . '/inc/slug-redirects.php';
require get_template_directory() . '/inc/svg-uploads.php';
require get_template_directory() . '/inc/analytics.php';

/**
 * Theme self-deploy endpoint used by the GitHub Actions pipeline.
 *
 * Guarded, not a bare require. A deploy copies files one at a time and
 * functions.php sorts before inc/, so on the very first HTTPS deploy this
 * file lands a few milliseconds before inc/deploy.php does. A bare require
 * would fatal the site for those milliseconds.
 */
if (file_exists(get_template_directory() . '/inc/deploy.php')) {
	require get_template_directory() . '/inc/deploy.php';
}

/**
 * Cookie consent: banner, preference panel and the gate that stops tracking
 * scripts running before the visitor has chosen. reCAPTCHA and PayPal are
 * treated as strictly necessary and are never gated.
 */
if (file_exists(get_template_directory() . '/inc/consent/class-elahub-consent.php')) {
	require get_template_directory() . '/inc/consent/class-elahub-consent.php';
}
