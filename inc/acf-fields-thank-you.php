<?php

/**
 * ACF fields for the DALC Thank You page template.
 *
 * Lets Sally/Joe edit the thank-you page copy (hero, steps, support line)
 * from the page editor. All fields are optional — the template falls back to
 * sensible defaults when they’re blank. Shows only on pages using the
 * "DALC Thank You" template (template-dalc-thank-you.php).
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
	exit;
}

add_action('acf/init', 'elahub_register_dalc_thankyou_fields');
function elahub_register_dalc_thankyou_fields(): void {

	if (! function_exists('acf_add_local_field_group')) {
		return;
	}

	acf_add_local_field_group([
		'key'    => 'group_elahub_dalc_thankyou',
		'title'  => 'DALC Thank You — page content',
		'fields' => [
			[
				'key'   => 'field_dalc_ty_tab_hero',
				'label' => 'Hero',
				'type'  => 'tab',
			],
			[
				'key'           => 'field_dalc_ty_eyebrow',
				'label'         => 'Hero eyebrow',
				'name'          => 'dalc_ty_eyebrow',
				'type'          => 'text',
				'default_value' => 'Order confirmed',
				'instructions'  => 'Small label above the heading. The hero image is set via the page’s Featured image.',
			],
			[
				'key'           => 'field_dalc_ty_heading',
				'label'         => 'Hero heading',
				'name'          => 'dalc_ty_heading',
				'type'          => 'text',
				'default_value' => 'Thank you {first_name} & congratulations!',
				'instructions'  => 'Use {first_name} to insert the buyer’s first name.',
			],
			[
				'key'           => 'field_dalc_ty_intro',
				'label'         => 'Hero intro',
				'name'          => 'dalc_ty_intro',
				'type'          => 'textarea',
				'rows'          => 3,
				'new_lines'     => 'br',
				'default_value' => 'You’re all set to begin the Designing Accessible Learning Content (DALC) Programme. Here’s what happens next.',
			],
			[
				'key'   => 'field_dalc_ty_tab_steps',
				'label' => 'Next steps',
				'type'  => 'tab',
			],
			[
				'key'           => 'field_dalc_ty_steps_title',
				'label'         => 'Steps heading',
				'name'          => 'dalc_ty_steps_title',
				'type'          => 'text',
				'default_value' => 'What happens next',
			],
			[
				'key'          => 'field_dalc_ty_steps',
				'label'        => 'Steps',
				'name'         => 'dalc_ty_steps',
				'type'         => 'repeater',
				'layout'       => 'block',
				'button_label' => 'Add step',
				'instructions' => 'Leave empty to use the default three steps.',
				'sub_fields'   => [
					[
						'key'   => 'field_dalc_ty_step_title',
						'label' => 'Title',
						'name'  => 'title',
						'type'  => 'text',
					],
					[
						'key'   => 'field_dalc_ty_step_text',
						'label' => 'Text',
						'name'  => 'text',
						'type'  => 'textarea',
						'rows'  => 3,
					],
				],
			],
			[
				'key'   => 'field_dalc_ty_tab_support',
				'label' => 'Support',
				'type'  => 'tab',
			],
			[
				'key'           => 'field_dalc_ty_support',
				'label'         => 'Support line',
				'name'          => 'dalc_ty_support',
				'type'          => 'wysiwyg',
				'tabs'          => 'visual',
				'toolbar'       => 'basic',
				'media_upload'  => 0,
				'default_value' => '<p>Any issues or queries? Contact {support_email}.</p>',
				'instructions'  => 'Use {support_email} to insert the support email address as a link.',
			],
		],
		'location' => [
			[
				[
					'param'    => 'page_template',
					'operator' => '==',
					'value'    => 'template-dalc-thank-you.php',
				],
			],
		],
	]);
}
