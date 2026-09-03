<?php

/**
 * Feature section — flexible content layout
 *
 * Variants:
 *   image_left        — image left, content right, optional logos row
 *   image_right       — image right, content left, optional tick list
 *   image_left_bg     — light panel on left with contained image + bleeding dots
 *   image_right_bg    — light panel on right with contained image + bleeding dots
 *
 * @package elahub
 */

// Support both page-builder (get_sub_field) and direct $args usage.
$_a = $args ?? [];

$variant = $_a['variant'] ?? (get_sub_field('feature_variant') ?: 'image_left');

$badge_text = isset($_a['badge_text']) ? trim((string) $_a['badge_text']) : trim((string) (get_sub_field('badge_text') ?: ''));
$badge_icon = isset($_a['badge_icon']) ? trim((string) $_a['badge_icon']) : trim((string) (get_sub_field('badge_icon_class') ?: 'fa-solid fa-universal-access'));
$heading    = isset($_a['heading'])    ? trim((string) $_a['heading'])    : trim((string) (get_sub_field('heading') ?: ''));
$body       = $_a['body']  ?? (get_sub_field('body') ?: '');
$btn_label  = isset($_a['btn_label']) ? trim((string) $_a['btn_label'])  : trim((string) (get_sub_field('button_label') ?: ''));
$btn_url    = isset($_a['btn_url'])   ? trim((string) $_a['btn_url'])    : trim((string) (get_sub_field('button_url') ?: ''));
$image      = $_a['image'] ?? get_sub_field('image');

$section_id = isset($_a['section_id']) ? $_a['section_id'] : ( $heading ? 'section-' . sanitize_title($heading) : 'section-' . uniqid() );

// Direct call: pass list items as a plain array of strings via $_a['list_items']
if ( isset( $_a['list_items'] ) ) {
    $show_list  = ! empty( $_a['list_items'] );
    $list_items = array_values( array_filter( array_map( 'strval', (array) $_a['list_items'] ) ) );
} else {
    $show_list      = (bool) get_sub_field('show_list');
    $list_items_raw = $show_list ? (get_sub_field('list_items') ?: []) : [];
    $list_items     = array_values(array_filter(array_map(
        fn($row) => trim((string) ($row['item'] ?? '')),
        is_array($list_items_raw) ? $list_items_raw : []
    )));
}

$show_logos  = ('image_left' === $variant) && (bool) get_sub_field('show_logos');
$logos_raw   = $show_logos ? (get_sub_field('logos') ?: []) : [];
$logo_size   = get_sub_field('feature_logo_size') ?: 'medium';
$feat_sz_map = [
    'small'  => 'max-h-16 lg:max-h-24',
    'medium' => 'max-h-20 lg:max-h-28',
    'large'  => 'max-h-24 lg:max-h-32',
    'xl'     => 'max-h-24 lg:max-h-40',
];
$feat_logo_max_h = $feat_sz_map[$logo_size] ?? $feat_sz_map['medium'];

$img_src = '';
$img_alt = '';
if (is_array($image)) {
    $img_src = $image['url'] ?? '';
    $img_alt = $image['alt'] ?? '';
} elseif (is_numeric($image) && $image) {
    $img_src = wp_get_attachment_image_url((int) $image, 'large') ?: '';
    $img_alt = get_post_meta((int) $image, '_wp_attachment_image_alt', true) ?: '';
}

$has_bg      = in_array($variant, ['image_left_bg', 'image_right_bg'], true);
$img_on_left = in_array($variant, ['image_left', 'image_left_bg'], true);
$dot_svg     = get_template_directory_uri() . '/assets/main-circle-dots.svg';
$bg_side     = $has_bg ? 'right' : ($img_on_left ? 'left' : 'right');
?>

<?php $feature_heading_id = 'feature-heading-' . wp_unique_id(); ?>
<section id="<?php echo esc_attr($section_id); ?>" class="elahub-feature relative py-8 md:py-12 lg:py-16"<?php if ($heading) : ?> aria-labelledby="<?php echo esc_attr($feature_heading_id); ?>"<?php endif; ?>>

    <?php
    get_template_part(
        'template-parts/components/section-bg',
        null,
        [
            'side'      => $bg_side,
            'show_dots' => ! $has_bg,
        ]
    );
    ?>

    <div class="container relative z-10">
        <div class="grid grid-cols-1 items-stretch gap-8 lg:grid-cols-12 lg:gap-0">

            <?php /* ---- Image column ---- */ ?>
            <div class="lg:col-span-5 <?php echo ! $img_on_left ? 'lg:order-last' : ''; ?>">
                <?php if ($img_src) : ?>
                    <?php if ($has_bg) : ?>
                        <div class="relative h-full lg:!min-h-196">
                            <div class="relative flex !h-64 items-center justify-center overflow-hidden rounded-2xl lg:rounded-br-[3.222rem] lg:rounded-br-[6.222rem] bg-primary-soft md:!h-[26rem] lg:!h-full lg:min-h-full">
                                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                                    <img
                                        src="<?php echo esc_url($dot_svg); ?>"
                                        alt=""
                                        class="absolute left-1/2 top-1/2 max-w-none opacity-20"
                                        loading="lazy"
                                        decoding="async"
                                        style="width: 54.75rem; transform: translate(-50%, -50%);">
                                </div>

                                <img
                                    src="<?php echo esc_url($img_src); ?>"
                                    alt="<?php echo esc_attr($img_alt); ?>"
                                    class="relative z-10 block h-auto max-h-full w-4/5 max-w-xs object-contain px-5 py-5 md:max-w-sm md:px-6 md:py-6 lg:max-w-md lg:px-8 lg:py-8 xl:max-w-xl xl:px-10 xl:py-10"
                                    loading="lazy"
                                    decoding="async">
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="relative h-full lg:!min-h-196">
                            <img
                                src="<?php echo esc_url($img_src); ?>"
                                alt="<?php echo esc_attr($img_alt); ?>"
                                class="rounded-xl md:rounded-2xl rounded-bl-4xl lg:rounded-bl-[6.222rem] relative z-10 block !h-64 w-full object-cover object-top md:!h-80 lg:!h-full lg:min-h-full"
                                loading="lazy"
                                decoding="async">
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <?php /* ---- Content column ---- */ ?>
            <div class="flex flex-col items-start justify-center gap-4 lg:col-span-7 <?php echo $img_on_left ? 'lg:pl-12' : 'lg:pr-12'; ?> <?php echo $has_bg ? 'lg:py-12' : 'lg:py-10'; ?>">

                <?php if ($badge_text) : ?>
                    <?php
                    get_template_part(
                        'template-parts/components/eyebrow',
                        null,
                        [
                            'text'       => $badge_text,
                            'icon_class' => $badge_icon,
                        ]
                    );
                    ?>
                <?php endif; ?>

                <?php if ($heading) : ?>
                    <h2 id="<?php echo esc_attr($feature_heading_id); ?>"><?php echo esc_html($heading); ?></h2>
                <?php endif; ?>

                <?php if ($body) : ?>
                    <div class="elahub-feature__body elahub-rich-text">
                        <?php echo wp_kses_post($body); ?>
                    </div>
                <?php endif; ?>

                <?php if ($show_list && ! empty($list_items)) : ?>
                    <?php
                    get_template_part(
                        'template-parts/components/tick-list',
                        null,
                        ['items' => $list_items]
                    );
                    ?>
                <?php endif; ?>

                <?php if ($btn_label && $btn_url) : ?>
                    <?php
                    get_template_part(
                        'template-parts/components/button',
                        null,
                        [
                            'url'   => $btn_url,
                            'label' => $btn_label,
                        ]
                    );
                    ?>
                <?php endif; ?>

                <?php if ($show_logos && ! empty($logos_raw)) : ?>
                    <div class="flex flex-wrap items-center gap-x-8 gap-y-4">
                        <?php foreach ($logos_raw as $logo_row) : ?>
                            <?php
                            $logo_img = $logo_row['logo_image'] ?? null;
                            $logo_alt = trim((string) ($logo_row['logo_alt_text'] ?? ''));
                            $logo_url = trim((string) ($logo_row['logo_link_url'] ?? ''));
                            $logo_src = '';
                            if (is_array($logo_img)) {
                                $logo_src = $logo_img['url'] ?? '';
                                if (! $logo_alt) {
                                    $logo_alt = $logo_img['alt'] ?? '';
                                }
                            }
                            ?>
                            <?php if ($logo_src) : ?>
                                <?php if ($logo_url) : ?>
                                    <a href="<?php echo esc_url($logo_url); ?>" class="inline-flex items-center" target="_blank" rel="noopener noreferrer">
                                    <?php endif; ?>
                                    <img
                                        src="<?php echo esc_url($logo_src); ?>"
                                        alt="<?php echo esc_attr($logo_alt); ?>"
                                        class="block h-auto <?php echo esc_attr($feat_logo_max_h); ?> w-auto"
                                        loading="lazy"
                                        decoding="async">
                                    <?php if ($logo_url) : ?>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>

        </div>
    </div>
</section>