<?php

/**
 * Cornerstone Articles CPT
 *
 * @package elahub
 */

function elahub_register_cornerstone_articles_cpt() {
	$labels = [
		// Drives the archive's <title>, which is public-facing — hence
		// "Accessibility Guides" rather than the internal "Cornerstone Articles".
		'name'               => __( 'Accessibility Guides', 'elahub' ),
		'singular_name'      => __( 'Accessibility Guide', 'elahub' ),
		'menu_name'          => __( 'Guides', 'elahub' ),
		'name_admin_bar'     => __( 'Guide', 'elahub' ),
		'add_new'            => __( 'Add New', 'elahub' ),
		'add_new_item'       => __( 'Add New Guide', 'elahub' ),
		'edit_item'          => __( 'Edit Guide', 'elahub' ),
		'new_item'           => __( 'New Guide', 'elahub' ),
		'view_item'          => __( 'View Guide', 'elahub' ),
		'all_items'          => __( 'All Guides', 'elahub' ),
		'search_items'       => __( 'Search Guides', 'elahub' ),
		'not_found'          => __( 'No guides found.', 'elahub' ),
		'not_found_in_trash' => __( 'No guides found in Trash.', 'elahub' ),
	];

	register_post_type(
		'cornerstone_article',
		[
			'labels'             => $labels,
			'public'             => true,
			'show_in_rest'       => true,
			'has_archive'        => 'guides',
			'rewrite'            => [ 'slug' => 'guides' ],
			'menu_icon'          => 'dashicons-media-document',
			'supports'           => [ 'title', 'editor', 'excerpt', 'thumbnail' ],
			'menu_position'      => 21,
			'publicly_queryable' => true,
		]
	);

	register_taxonomy(
		'guide_category',
		[ 'cornerstone_article' ],
		[
			'label'        => __( 'Guide Categories', 'elahub' ),
			'public'       => true,
			'hierarchical' => true,
			'show_in_rest' => true,
			'rewrite'      => [ 'slug' => 'guide-category' ],
		]
	);
}
add_action( 'init', 'elahub_register_cornerstone_articles_cpt' );

/**
 * Route guide_category taxonomy archives through archive-cornerstone_article.php.
 */
function elahub_cornerstone_taxonomy_template( $template ) {
	if ( is_tax( 'guide_category' ) ) {
		$located = locate_template( 'archive-cornerstone_article.php' );
		if ( $located ) {
			return $located;
		}
	}

	return $template;
}
add_filter( 'template_include', 'elahub_cornerstone_taxonomy_template' );

/**
 * Estimated read time helper for guides.
 * Returns e.g. "8 Minute Read". Stored as ACF field for flexibility,
 * but this function can auto-calculate as a fallback.
 *
 * @param int $post_id
 * @return string
 */
function elahub_guide_read_time( $post_id ) {
	// Try ACF field first
	if ( function_exists( 'get_field' ) ) {
		$stored = trim( (string) ( get_field( 'guide_read_time', $post_id ) ?: '' ) );
		if ( $stored ) {
			return $stored;
		}
	}

	// Auto-calculate from post content
	$content    = get_post_field( 'post_content', $post_id );
	$word_count = str_word_count( wp_strip_all_tags( $content ) );
	$minutes    = max( 1, (int) round( $word_count / 200 ) );

	return sprintf(
		/* translators: %d = number of minutes */
		_n( '%d Minute Read', '%d Minute Read', $minutes, 'elahub' ),
		$minutes
	);
}
