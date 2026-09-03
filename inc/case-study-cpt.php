<?php

/**
 * Case Study CPT and taxonomy
 *
 * Register in functions.php:
 *   require get_template_directory() . '/inc/case-study-cpt.php';
 *
 * @package elahub
 */

function elahub_register_case_study_cpt()
{
	register_post_type(
		'case_study',
		[
			'labels' => [
				'name'               => __('Case Studies', 'elahub'),
				'singular_name'      => __('Case Study', 'elahub'),
				'menu_name'          => __('Case Studies', 'elahub'),
				'add_new'            => __('Add New', 'elahub'),
				'add_new_item'       => __('Add New Case Study', 'elahub'),
				'edit_item'          => __('Edit Case Study', 'elahub'),
				'new_item'           => __('New Case Study', 'elahub'),
				'all_items'          => __('All Case Studies', 'elahub'),
				'search_items'       => __('Search Case Studies', 'elahub'),
				'not_found'          => __('No case studies found.', 'elahub'),
				'not_found_in_trash' => __('No case studies found in Trash.', 'elahub'),
			],
			'public'             => true,
			'show_ui'            => true,
			'show_in_rest'       => true,
			'has_archive'        => 'accessible-learning-case-studies',
			'rewrite'            => ['slug' => 'accessible-learning-case-studies'],
			'menu_icon'          => 'dashicons-portfolio',
			'supports'           => ['title', 'editor', 'excerpt', 'thumbnail'],
			'menu_position'      => 23,
			'publicly_queryable' => true,
		]
	);

	register_taxonomy(
		'case_study_category',
		['case_study'],
		[
			'label'        => __('Case Study Categories', 'elahub'),
			'public'       => true,
			'show_ui'      => true,
			'hierarchical' => true,
			'show_in_rest' => true,
			'rewrite'      => ['slug' => 'case-study-category'],
		]
	);
}
add_action('init', 'elahub_register_case_study_cpt');

/**
 * Route case_study_category taxonomy archives through archive-case_study.php.
 *
 * This avoids needing a separate taxonomy-case_study_category.php template.
 * archive-case_study.php already detects the queried WP_Term via
 * get_queried_object() and highlights the correct filter pill.
 */
function elahub_case_study_taxonomy_template($template)
{
	if (is_tax('case_study_category')) {
		$located = locate_template('archive-case_study.php');

		if ($located) {
			return $located;
		}
	}

	return $template;
}
add_filter('template_include', 'elahub_case_study_taxonomy_template');
