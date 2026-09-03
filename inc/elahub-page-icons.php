<?php

/**
 * eLaHub page icon helpers
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Return available master SVG icons from the theme folder.
 */
function elahub_get_master_icon_choices(): array
{
    $choices   = [];
    $icon_dir  = trailingslashit(get_template_directory()) . 'assets/eLaHub_Master_Icons/';
    $icon_uri  = trailingslashit(get_template_directory_uri()) . 'assets/eLaHub_Master_Icons/';

    if (! is_dir($icon_dir)) {
        return $choices;
    }

    $files = glob($icon_dir . '*.svg');

    if (! $files) {
        return $choices;
    }

    natcasesort($files);

    foreach ($files as $file_path) {
        $filename = basename($file_path);
        $label    = str_replace(['.svg', '_'], ['', ' '], $filename);
        $choices[$filename] = $label;
    }

    return $choices;
}

/**
 * Return full URI for a selected master icon filename.
 */
function elahub_get_master_icon_uri(string $filename = ''): string
{
    $filename = trim($filename);

    if (! $filename) {
        return '';
    }

    $filename = basename($filename);

    return trailingslashit(get_template_directory_uri()) . 'assets/eLaHub_Master_Icons/' . $filename;
}

/**
 * Populate Page Icon SVG choices dynamically.
 */
add_filter('acf/load_field/name=page_icon_svg', function ($field) {
    $field['choices'] = elahub_get_master_icon_choices();
    return $field;
});

/**
 * Optional helper for getting a page icon from a linked page.
 */
function elahub_get_page_icon_from_page(int $page_id): array
{
    $filename = '';

    if ($page_id > 0) {
        $filename = (string) get_field('page_icon_svg', $page_id);
    }

    return [
        'filename' => $filename,
        'uri'      => $filename ? elahub_get_master_icon_uri($filename) : '',
    ];
}
