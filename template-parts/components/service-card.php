<?php

/**
 * Service card component
 *
 * @package elahub
 */

$args = wp_parse_args(
    $args ?? [],
    [
        'title'                       => '',
        'url'                         => '',
        'description'                 => '',
        'description_is_html'         => false,
        'icon_type'                   => 'page_icon',
        'fa_icon_class'               => '',
        'uploaded_icon_src'           => '',
        'icon_card_size_class'        => 'h-24 w-24 md:h-32 md:w-32 xl:h-40 xl:w-40',
        'icon_card_padding_class'     => 'p-5 md:p-7 xl:p-8',
        'icon_size_class'             => 'text-4xl md:text-5xl xl:text-6xl',
        'icon_image_max_height_class' => 'max-h-10 md:max-h-12 xl:max-h-16',
    ]
);

if (! $args['title'] || ! $args['url']) {
    return;
}
?>

<div class="flex flex-col items-start gap-4">
    <div class="flex <?php echo esc_attr($args['icon_card_size_class']); ?> shrink-0 items-center justify-center rounded-3xl bg-primary/5 <?php echo esc_attr($args['icon_card_padding_class']); ?>">
        <?php if (in_array($args['icon_type'], ['upload', 'page_icon'], true) && $args['uploaded_icon_src']) : ?>
            <img
                src="<?php echo esc_url($args['uploaded_icon_src']); ?>"
                alt=""
                aria-hidden="true"
                class="h-auto w-auto max-w-full object-contain <?php echo esc_attr($args['icon_image_max_height_class']); ?>"
                loading="lazy"
                decoding="async">
        <?php elseif ($args['fa_icon_class']) : ?>
            <i
                class="<?php echo esc_attr(trim($args['fa_icon_class'] . ' ' . $args['icon_size_class'] . ' text-primary')); ?>"
                aria-hidden="true"></i>
        <?php endif; ?>
    </div>

    <div class="flex flex-col gap-3">
        <h3 class="h4">
            <a href="<?php echo esc_url($args['url']); ?>" class="focus-visible:rounded-sm">
                <?php echo esc_html($args['title']); ?>
            </a>
        </h3>

        <?php if ($args['description']) : ?>
            <div class="elahub-feature__body elahub-rich-text">
                <?php if (! empty($args['description_is_html'])) : ?>
                    <?php echo wp_kses_post($args['description']); ?>
                <?php else : ?>
                    <p class="mb-0"><?php echo esc_html($args['description']); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>