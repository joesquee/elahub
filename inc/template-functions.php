<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package elahub
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function elahub_body_classes( $classes ) {
	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	// Adds a class of no-sidebar when there is no sidebar present.
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}

	return $classes;
}
add_filter( 'body_class', 'elahub_body_classes' );

/* ==========================================================================
   Clean archive page titles
   Replaces slugified/lowercase terms and strips the " Archives" suffix that
   WordPress appends by default. Hooks into both WordPress native
   (document_title_parts) and Yoast SEO (wpseo_title) so it works regardless
   of which generates the <title> tag.
   ========================================================================== */

/**
 * Return a human-readable page title for known archive/taxonomy contexts,
 * or an empty string if no override is needed.
 */
function elahub_get_clean_archive_title(): string {

	// ── Learning Hub type taxonomy (books, webinars-podcasts, etc.) ──────────
	if ( is_tax( 'learning_hub_type' ) ) {
		$term = get_queried_object();
		$map  = [
			'books-and-publications' => 'Books',
			'guides'                 => 'Accessibility Guides',
			'industry-reports'       => 'Articles & Industry Reports',
			'webinars-podcasts'      => 'Webinars & Podcasts',
		];
		if ( $term instanceof WP_Term ) {
			return $map[ $term->slug ] ?? ucwords( str_replace( '-', ' ', $term->slug ) );
		}
	}

	// ── Learning Hub category taxonomy ───────────────────────────────────────
	if ( is_tax( 'learning_hub_category' ) ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			return $term->name;
		}
	}

	// ── Case studies CPT archive ─────────────────────────────────────────────
	if ( is_post_type_archive( 'case_study' ) ) {
		return 'Case Studies';
	}

	// ── Blog / Accessibility Insights ────────────────────────────────────────
	// Read the editable Theme Settings > Archive Intros heading so the SEO
	// <title> stays in sync with the H1 rendered by home.php.
	if ( is_home() && ! is_front_page() ) {
		return function_exists( 'elahub_get_option_field' )
			? elahub_get_option_field( 'insights_archive_heading', 'Accessibility Insights' )
			: 'Accessibility Insights';
	}

	// ── FAQ category taxonomy ────────────────────────────────────────────────
	if ( is_tax( 'faq_category' ) ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			return $term->name;
		}
	}

	return '';
}

/**
 * Filter for WordPress native title tag (document_title_parts).
 */
function elahub_clean_document_title_parts( array $parts ): array {
	$clean = elahub_get_clean_archive_title();
	if ( $clean ) {
		$parts['title'] = $clean;
	}
	return $parts;
}
add_filter( 'document_title_parts', 'elahub_clean_document_title_parts', 20 );

/**
 * Filter for Yoast SEO title string (wpseo_title).
 * Yoast returns a pre-assembled string, so we replace the title portion
 * before the first separator.
 */
function elahub_clean_yoast_title( string $title ): string {
	$clean = elahub_get_clean_archive_title();
	if ( ! $clean ) {
		return $title;
	}

	$site_name = get_bloginfo( 'name' );

	// Yoast separators to try, most common first
	foreach ( [ ' - ', ' | ', ' · ', ' » ', ' « ', ' — ' ] as $sep ) {
		if ( str_contains( $title, $sep ) ) {
			$parts    = explode( $sep, $title );
			$parts[0] = $clean;
			return implode( $sep, $parts );
		}
	}

	return $clean . ' - ' . $site_name;
}
add_filter( 'wpseo_title', 'elahub_clean_yoast_title', 20 );

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function elahub_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'elahub_pingback_header' );
