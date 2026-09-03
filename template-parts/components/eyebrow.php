<?php

/**
 * Reusable eyebrow component
 *
 * @package elahub
 */

$args = wp_parse_args(
    $args ?? [],
    [
        'text'       => '',
        'icon_class' => 'fa-solid fa-universal-access',
        'class'      => '',
    ]
);

if (! $args['text']) {
    return;
}

$classes = trim(
    'inline-flex items-center gap-2 rounded-sm bg-primary-soft px-3 py-2 !text-sm !leading-none !font-semibold !text-text ' . $args['class']
);
?>
<span class="<?php echo esc_attr($classes); ?>">
    <span
        class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-sm !bg-primary-dark !text-white"
        aria-hidden="true">
        <i class="<?php echo esc_attr($args['icon_class']); ?> !text-sm"></i>
    </span>
    <span><?php echo esc_html($args['text']); ?></span>
</span>