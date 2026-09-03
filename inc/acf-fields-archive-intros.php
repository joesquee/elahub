<?php

/**
 * ACF local field group: Archive Intros
 *
 * Lets the team edit the heading and intro text at the top of the post-type
 * archive landing pages that have no taxonomy term behind them:
 *   - /guides/        (Guides archive)
 *   - /case-studies/  (Case Studies archive)
 *   - the Insights blog landing (template-insights.php)
 *
 * Managed under: Theme Settings > Archive Intros.
 * Templates read these via elahub_get_option_field(), falling back to the
 * built-in default copy when a field is left blank.
 *
 * Per-category, per-tag and per-section intros are edited on the term itself
 * (its Description box), not here.
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
	exit;
}

add_action('acf/init', 'elahub_register_archive_intros_fields');

function elahub_register_archive_intros_fields()
{
	if (! function_exists('acf_add_local_field_group')) {
		return;
	}

	acf_add_local_field_group([
		'key'    => 'group_archive_intros',
		'title'  => 'Archive Intros',
		'fields' => [
			/* ── Guides ── */
			[
				'key'           => 'field_guides_archive_heading',
				'label'         => 'Guides — heading',
				'name'          => 'guides_archive_heading',
				'type'          => 'text',
				'instructions'  => 'Heading at the top of the main /guides/ page.',
				'default_value' => 'Accessibility Guides',
			],
			[
				'key'          => 'field_guides_archive_intro',
				'label'        => 'Guides — intro text',
				'name'         => 'guides_archive_intro',
				'type'         => 'textarea',
				'rows'         => 3,
				'instructions' => 'Intro paragraph on the main /guides/ page.',
			],
			/* ── Case Studies ── */
			[
				'key'           => 'field_case_studies_archive_heading',
				'label'         => 'Case Studies — heading',
				'name'          => 'case_studies_archive_heading',
				'type'          => 'text',
				'instructions'  => 'Heading at the top of the main /case-studies/ page.',
				'default_value' => 'Case Studies',
			],
			[
				'key'          => 'field_case_studies_archive_intro',
				'label'        => 'Case Studies — intro text',
				'name'         => 'case_studies_archive_intro',
				'type'         => 'textarea',
				'rows'         => 3,
				'instructions' => 'Intro paragraph on the main /case-studies/ page.',
			],
			/* ── Insights (blog landing) ── */
			[
				'key'           => 'field_insights_archive_heading',
				'label'         => 'Insights — heading',
				'name'          => 'insights_archive_heading',
				'type'          => 'text',
				'instructions'  => 'Heading at the top of the Insights blog landing page.',
				'default_value' => 'Accessibility Insights',
			],
			[
				'key'          => 'field_insights_archive_intro',
				'label'        => 'Insights — intro text',
				'name'         => 'insights_archive_intro',
				'type'         => 'textarea',
				'rows'         => 3,
				'instructions' => 'Intro paragraph on the Insights blog landing page. Also used as the fallback intro on individual category pages that have no Description set.',
			],
		],
		'location' => [
			[
				[
					'param'    => 'options_page',
					'operator' => '==',
					'value'    => 'elahub-archive-intros',
				],
			],
		],
		'menu_order'            => 0,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
	]);
}
