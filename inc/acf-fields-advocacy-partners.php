<?php

/**
 * ACF local field group: Advocacy Partners page template
 *
 * Registers fields for template-advocacy-partners.php so they appear
 * in the WP admin and can be read via get_field() / update_field().
 *
 * Field keys are stable — do not change them after seeding, or existing
 * post-meta references will break.
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
    exit;
}

add_action('acf/init', 'elahub_register_advocacy_partners_fields');

function elahub_register_advocacy_partners_fields()
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key'    => 'group_advocacy_partners',
        'title'  => 'Advocacy Partners Page',
        'fields' => [
            [
                'key'           => 'field_adv_hero_eyebrow',
                'label'         => 'Hero eyebrow text',
                'name'          => 'adv_hero_eyebrow',
                'type'          => 'text',
                'default_value' => 'Advocacy & Partnerships',
            ],
            [
                'key'           => 'field_adv_hero_icon',
                'label'         => 'Hero icon (Font Awesome class)',
                'name'          => 'adv_hero_icon',
                'type'          => 'text',
                'default_value' => 'fa-solid fa-handshake',
            ],
            [
                'key'   => 'field_adv_hero_heading',
                'label' => 'Hero heading',
                'name'  => 'adv_hero_heading',
                'type'  => 'text',
            ],
            [
                'key'   => 'field_adv_hero_description',
                'label' => 'Hero description',
                'name'  => 'adv_hero_description',
                'type'  => 'textarea',
                'rows'  => 3,
            ],
            [
                'key'   => 'field_adv_intro_text',
                'label' => 'Intro text (optional)',
                'name'  => 'adv_intro_text',
                'type'  => 'textarea',
                'rows'  => 3,
            ],
            [
                'key'        => 'field_adv_items',
                'label'      => 'Advocacy partners',
                'name'       => 'adv_items',
                'type'       => 'repeater',
                'layout'     => 'table',
                'button_label' => 'Add partner',
                'sub_fields' => [
                    [
                        'key'      => 'field_adv_name',
                        'label'    => 'Name',
                        'name'     => 'adv_name',
                        'type'     => 'text',
                        'required' => 1,
                    ],
                    [
                        'key'   => 'field_adv_url',
                        'label' => 'Website URL',
                        'name'  => 'adv_url',
                        'type'  => 'url',
                    ],
                    [
                        'key'           => 'field_adv_logo',
                        'label'         => 'Logo',
                        'name'          => 'adv_logo',
                        'type'          => 'image',
                        'return_format' => 'array',
                        'preview_size'  => 'thumbnail',
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'page_template',
                    'operator' => '==',
                    'value'    => 'template-advocacy-partners.php',
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
