<?php

/**
 * Services grid section — flexible content layout
 *
 * @package elahub
 */

// Support both page-builder (get_sub_field) and direct $args usage.
$_a = $args ?? [];

$badge_text       = isset($_a['badge_text']) ? trim((string) $_a['badge_text']) : trim((string) (get_sub_field('badge_text') ?: ''));
$badge_icon       = isset($_a['badge_icon']) ? trim((string) $_a['badge_icon']) : trim((string) (get_sub_field('badge_icon_class') ?: 'fa-solid fa-universal-access'));
$heading          = isset($_a['heading'])    ? trim((string) $_a['heading'])    : trim((string) (get_sub_field('heading') ?: ''));
$body             = $_a['body']        ?? (get_sub_field('body') ?: '');
$button_link      = $_a['button_link'] ?? get_sub_field('button_link');
$use_global_items = isset($_a['use_global_items']) ? (bool) $_a['use_global_items'] : (bool) get_sub_field('use_global_service_items');
$columns          = (string) ($_a['columns'] ?? (get_sub_field('columns') ?: '4'));
// Global default list always uses 4 columns unless editor has explicitly chosen 2
if ($use_global_items && in_array($columns, ['3', '4'], true)) {
    $columns = '4';
}
$icon_height      = (int) ($_a['icon_height'] ?? (get_sub_field('icon_height') ?: 100));
$icon_height      = max(40, min(160, $icon_height));
$items_raw        = $_a['items'] ?? ($use_global_items ? (get_field('services_grid_default_items', 'option') ?: []) : (get_sub_field('items') ?: []));
$current_page_id  = get_queried_object_id();

$grid_cols_class = match ($columns) {
    '2'     => 'xl:grid-cols-2',
    '3'     => 'xl:grid-cols-3',
    default => 'xl:grid-cols-4',
};

$card_size_class    = 'h-24 w-24 md:h-32 md:w-32 xl:h-40 xl:w-40';
$card_padding_class = 'p-5 md:p-7 xl:p-8';

if ($icon_height <= 56) {
    $icon_size_class             = 'text-3xl md:text-4xl xl:text-5xl';
    $icon_image_max_height_class = 'max-h-8 md:max-h-10 xl:max-h-12';
} elseif ($icon_height <= 88) {
    $icon_size_class             = 'text-4xl md:text-5xl xl:text-6xl';
    $icon_image_max_height_class = 'max-h-10 md:max-h-12 xl:max-h-16';
} else {
    $icon_size_class             = 'text-5xl md:text-6xl xl:text-7xl';
    $icon_image_max_height_class = 'max-h-12 md:max-h-16 xl:max-h-20';
}

$items = [];

foreach ($items_raw as $row) {
    $page_id = isset($row['page']) ? (int) $row['page'] : 0;

    if (! $page_id) {
        continue;
    }

    if ($use_global_items && $current_page_id && $page_id === $current_page_id) {
        continue;
    }

    $title = trim((string) ($row['heading_override'] ?? ''));

    if (! $title) {
        $title = get_the_title($page_id) ?: '';
    }

    $url = get_permalink($page_id) ?: '';

    $description_override = $row['description'] ?? '';
    $description          = '';
    $description_is_html  = false;

    if (is_string($description_override)) {
        $description_override = trim($description_override);
    }

    if (! empty($description_override)) {
        $description         = wp_kses_post($description_override);
        $description_is_html = true;
    }

    if (! $description) {
        $excerpt = trim((string) get_the_excerpt($page_id));

        if ($excerpt) {
            $description = $excerpt;
        } else {
            $summary_source = get_post_field('post_content', $page_id) ?: '';
            $description    = wp_strip_all_tags($summary_source);
            $description    = wp_trim_words($description, 28, '…');
        }
    }

    $icon_type         = $use_global_items ? 'page_icon' : trim((string) ($row['icon_type'] ?? 'page_icon'));
    $fa_icon_class     = trim((string) ($row['font_awesome_icon_class'] ?? ''));
    $uploaded_icon     = $row['uploaded_icon'] ?? null;
    $uploaded_icon_src = '';

    if (is_array($uploaded_icon)) {
        $uploaded_icon_src = $uploaded_icon['url'] ?? '';
    }

    if ('page_icon' === $icon_type && function_exists('elahub_get_page_icon_from_page')) {
        $page_icon = elahub_get_page_icon_from_page($page_id);
        if (! empty($page_icon['uri'])) {
            $uploaded_icon_src = $page_icon['uri'];
        }
    }

    if ('page_icon' === $icon_type && ! $uploaded_icon_src && $fa_icon_class) {
        $icon_type = 'font_awesome';
    }

    if (! $title || ! $url) {
        continue;
    }

    $items[] = [
        'title'                       => $title,
        'url'                         => $url,
        'description'                 => $description,
        'description_is_html'         => $description_is_html,
        'icon_type'                   => $icon_type,
        'fa_icon_class'               => $fa_icon_class,
        'uploaded_icon_src'           => $uploaded_icon_src,
        'icon_card_size_class'        => $card_size_class,
        'icon_card_padding_class'     => $card_padding_class,
        'icon_size_class'             => $icon_size_class,
        'icon_image_max_height_class' => $icon_image_max_height_class,
    ];
}
?>

<?php if ($heading || $body || ! empty($items)) : ?>
    <section class="relative py-8 md:py-12 lg:py-16" aria-labelledby="services-grid-heading">

        <?php get_template_part('template-parts/components/section-bg', null, ['side' => 'right']); ?>

        <div class="container relative z-10">

            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between lg:gap-12">
                <div class="flex flex-col items-start gap-4 lg:max-w-7xl">
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
                        <h2 id="services-grid-heading"><?php echo esc_html($heading); ?></h2>
                    <?php endif; ?>

                    <?php if ($body) : ?>
                        <div class="elahub-feature__body elahub-rich-text">
                            <?php echo wp_kses_post($body); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (is_array($button_link) && ! empty($button_link['url']) && ! empty($button_link['title'])) : ?>
                    <div class="shrink-0">
                        <?php
                        get_template_part(
                            'template-parts/components/button',
                            null,
                            [
                                'url'   => $button_link['url'],
                                'label' => $button_link['title'],
                            ]
                        );
                        ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (! empty($items)) : ?>
                <div class="mt-10 grid grid-cols-1 gap-x-8 gap-y-10 md:grid-cols-2 lg:mt-14 <?php echo esc_attr($grid_cols_class); ?>">
                    <?php foreach ($items as $item) : ?>
                        <?php
                        get_template_part(
                            'template-parts/components/service-card',
                            null,
                            $item
                        );
                        ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </section>
<?php endif; ?>