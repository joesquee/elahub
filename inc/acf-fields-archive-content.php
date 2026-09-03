<?php

/**
 * ACF local field group: Archive Content
 *
 * Sally's site query #10 — advocacy logos at the top and the services block at
 * the bottom of every Learning Hub page — plus #12, testimonials on the Books
 * and Publications section.
 *
 * Two levels:
 *   Theme Settings > Archive Content   global defaults for every Learning Hub
 *                                      archive (the overview and every type)
 *   Each Learning Hub Type term        per-section override, so Books can show
 *                                      testimonials without podcasts doing so
 *
 * Per-section toggles are three-way: inherit (use the global setting), on, off.
 * Content left blank falls back to the global defaults already managed under
 * Theme Settings > Global Content, so nothing has to be entered twice.
 *
 * Rendered by template-parts/archive/archive-content.php.
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
	exit;
}

add_action('acf/init', 'elahub_register_archive_content_fields');

function elahub_register_archive_content_fields()
{
	if (! function_exists('acf_add_local_field_group')) {
		return;
	}

	/* ── Global defaults ─────────────────────────────────────────── */

	acf_add_local_field_group([
		'key'    => 'group_elahub_archive_content',
		'title'  => 'Archive Content',
		'fields' => [
			[
				'key'          => 'field_elahub_archive_top_tab',
				'label'        => 'Top of page',
				'type'         => 'tab',
				'placement'    => 'top',
			],
			[
				'key'           => 'field_elahub_archive_show_logos',
				'label'         => 'Show advocacy logos',
				'name'          => 'archive_show_advocacy_logos',
				'type'          => 'true_false',
				'instructions'  => 'Adds the advocacy logo strip just below the hero on every Learning Hub page. Logos and heading are managed under Theme Settings &rsaquo; Global Content.',
				'default_value' => 1,
				'ui'            => 1,
				'ui_on_text'    => 'Shown',
				'ui_off_text'   => 'Hidden',
			],
			[
				'key'          => 'field_elahub_archive_logos_heading',
				'label'        => 'Advocacy logos heading',
				'name'         => 'archive_advocacy_heading',
				'type'         => 'text',
				'instructions' => 'Leave blank to use the default advocacy heading.',
				'conditional_logic' => [[[
					'field'    => 'field_elahub_archive_show_logos',
					'operator' => '==',
					'value'    => '1',
				]]],
			],

			[
				'key'          => 'field_elahub_archive_bottom_tab',
				'label'        => 'Bottom of page',
				'type'         => 'tab',
				'placement'    => 'top',
			],
			[
				'key'           => 'field_elahub_archive_show_services',
				'label'         => 'Show services block',
				'name'          => 'archive_show_services_block',
				'type'          => 'true_false',
				'instructions'  => 'Adds the services grid at the bottom of every Learning Hub page. The service cards themselves are managed under Theme Settings &rsaquo; Global Content.',
				'default_value' => 1,
				'ui'            => 1,
				'ui_on_text'    => 'Shown',
				'ui_off_text'   => 'Hidden',
			],
			[
				'key'               => 'field_elahub_archive_services_heading',
				'label'             => 'Services block heading',
				'name'              => 'archive_services_heading',
				'type'              => 'text',
				'instructions'      => 'Shown above the service cards.',
				'default_value'     => 'Explore our accessibility services',
				'conditional_logic' => [[[
					'field'    => 'field_elahub_archive_show_services',
					'operator' => '==',
					'value'    => '1',
				]]],
			],
			[
				'key'               => 'field_elahub_archive_services_body',
				'label'             => 'Services block intro',
				'name'              => 'archive_services_body',
				'type'              => 'wysiwyg',
				'tabs'              => 'visual',
				'media_upload'      => 0,
				'toolbar'           => 'basic',
				'conditional_logic' => [[[
					'field'    => 'field_elahub_archive_show_services',
					'operator' => '==',
					'value'    => '1',
				]]],
			],
		],
		'location'    => [[[
			'param'    => 'options_page',
			'operator' => '==',
			'value'    => 'elahub-archive-content',
		]]],
		'menu_order'  => 0,
		'description' => 'Shared content shown at the top and bottom of the Learning Hub pages.',
	]);

	/* ── Per-section overrides ───────────────────────────────────── */

	$inherit_choices = [
		''    => 'Use the global setting',
		'on'  => 'Show on this section',
		'off' => 'Hide on this section',
	];

	acf_add_local_field_group([
		'key'    => 'group_elahub_archive_content_term',
		'title'  => 'Archive Content — this section',
		'fields' => [
			[
				'key'           => 'field_elahub_term_show_logos',
				'label'         => 'Advocacy logos',
				'name'          => 'archive_show_advocacy_logos',
				'type'          => 'select',
				'instructions'  => 'Overrides Theme Settings &rsaquo; Archive Content for this section only.',
				'choices'       => $inherit_choices,
				'default_value' => '',
				'allow_null'    => 0,
			],
			[
				'key'           => 'field_elahub_term_show_services',
				'label'         => 'Services block',
				'name'          => 'archive_show_services_block',
				'type'          => 'select',
				'instructions'  => 'Overrides Theme Settings &rsaquo; Archive Content for this section only.',
				'choices'       => $inherit_choices,
				'default_value' => '',
				'allow_null'    => 0,
			],
			[
				'key'           => 'field_elahub_term_show_testimonials',
				'label'         => 'Testimonials',
				'name'          => 'archive_show_testimonials',
				'type'          => 'true_false',
				'instructions'  => 'Adds a testimonials section above the services block on this section only. Used on Books and Publications.',
				'default_value' => 0,
				'ui'            => 1,
				'ui_on_text'    => 'Shown',
				'ui_off_text'   => 'Hidden',
			],
			[
				'key'               => 'field_elahub_term_testimonials_heading',
				'label'             => 'Testimonials heading',
				'name'              => 'archive_testimonials_heading',
				'type'              => 'text',
				'instructions'      => 'Leave blank to use the global testimonials heading.',
				'conditional_logic' => [[[
					'field'    => 'field_elahub_term_show_testimonials',
					'operator' => '==',
					'value'    => '1',
				]]],
			],
			[
				'key'               => 'field_elahub_term_testimonials_quotes',
				'label'             => 'Testimonials',
				'name'              => 'archive_testimonials_quotes',
				'type'              => 'repeater',
				'instructions'      => 'Leave empty to reuse the global testimonials managed under Theme Settings &rsaquo; Global Content.',
				'layout'            => 'row',
				'button_label'      => 'Add testimonial',
				'conditional_logic' => [[[
					'field'    => 'field_elahub_term_show_testimonials',
					'operator' => '==',
					'value'    => '1',
				]]],
				'sub_fields'        => [
					[
						'key'   => 'field_elahub_term_quote',
						'label' => 'Quote',
						'name'  => 'quote',
						'type'  => 'textarea',
						'rows'  => 3,
					],
					[
						'key'     => 'field_elahub_term_quote_author',
						'label'   => 'Author name',
						'name'    => 'author_name',
						'type'    => 'text',
						'wrapper' => ['width' => '33'],
					],
					[
						'key'     => 'field_elahub_term_quote_role',
						'label'   => 'Author role',
						'name'    => 'author_role',
						'type'    => 'text',
						'wrapper' => ['width' => '33'],
					],
					[
						'key'     => 'field_elahub_term_quote_org',
						'label'   => 'Organisation',
						'name'    => 'organisation',
						'type'    => 'text',
						'wrapper' => ['width' => '34'],
					],
					[
						'key'           => 'field_elahub_term_quote_stars',
						'label'         => 'Stars',
						'name'          => 'stars',
						'type'          => 'number',
						'min'           => 0,
						'max'           => 5,
						'default_value' => 5,
						'wrapper'       => ['width' => '33'],
					],
					[
						'key'           => 'field_elahub_term_quote_avatar',
						'label'         => 'Avatar',
						'name'          => 'avatar',
						'type'          => 'image',
						'return_format' => 'array',
						'preview_size'  => 'thumbnail',
						'wrapper'       => ['width' => '67'],
					],
				],
			],
		],
		'location'    => [[[
			'param'    => 'taxonomy',
			'operator' => '==',
			'value'    => 'learning_hub_type',
		]]],
		'menu_order'  => 5,
		'description' => 'Per-section overrides for the shared Learning Hub archive content.',
	]);
}
