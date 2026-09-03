<?php

/**
 * FAQ CPT and taxonomy
 *
 * Register in functions.php:
 *   require get_template_directory() . '/inc/faq-cpt.php';
 *
 * @package elahub
 */

function elahub_register_faq_cpt()
{
	register_post_type(
		'faq',
		[
			'labels'        => [
				'name'               => __('FAQs', 'elahub'),
				'singular_name'      => __('FAQ', 'elahub'),
				'menu_name'          => __('FAQs', 'elahub'),
				'add_new'            => __('Add New', 'elahub'),
				'add_new_item'       => __('Add New FAQ', 'elahub'),
				'edit_item'          => __('Edit FAQ', 'elahub'),
				'new_item'           => __('New FAQ', 'elahub'),
				'all_items'          => __('All FAQs', 'elahub'),
				'search_items'       => __('Search FAQs', 'elahub'),
				'not_found'          => __('No FAQs found.', 'elahub'),
				'not_found_in_trash' => __('No FAQs found in Trash.', 'elahub'),
			],
			'public'             => false,
			'show_ui'            => true,
			'show_in_rest'       => true,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-editor-help',
			'supports'           => ['title', 'editor'],
			'menu_position'      => 22,
			'publicly_queryable' => false,
			'has_archive'        => false,
		]
	);

	register_taxonomy(
		'faq_category',
		['faq'],
		[
			'label'        => __('FAQ Categories', 'elahub'),
			'public'       => false,
			'show_ui'      => true,
			'hierarchical' => true,
			'show_in_rest' => true,
			'rewrite'      => false,
		]
	);
}
add_action('init', 'elahub_register_faq_cpt');
