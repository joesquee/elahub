<?php

/**
 * Template Name: Advocacy Partners
 * Template Post Type: page
 *
 * eLaHub advocacy partners showcase page.
 *
 * Layout:
 *   Hero (eyebrow, h1, description + glows + hr)
 *   Optional intro text block
 *   Responsive logo/name grid from ACF repeater
 *
 * Accessibility notes:
 *   - When a card is a link, the logo img uses alt="" (decorative) because the
 *     visible partner name text already labels the link destination.
 *   - When a card has no URL, the logo img uses alt="[name] logo" so it conveys
 *     meaning without surrounding link context.
 *   - External links include an sr-only "(opens in new tab)" notice.
 *
 * @package elahub
 */

get_header();

$dot_svg = get_template_directory_uri() . '/assets/main-circle-dots.svg';

/* ── ACF fields ── */
$hero_eyebrow     = trim((string) (get_field('adv_hero_eyebrow') ?: __('Advocacy & Partnerships', 'elahub')));
$hero_icon        = trim((string) (get_field('adv_hero_icon') ?: 'fa-solid fa-handshake'));
$hero_heading     = trim((string) (get_field('adv_hero_heading') ?: get_the_title()));
$hero_description = trim((string) (get_field('adv_hero_description') ?: ''));
$intro_text       = trim((string) (get_field('adv_intro_text') ?: ''));
$partners         = get_field('adv_items') ?: [];

/* ── Sort alphabetically by name ── */
usort($partners, fn($a, $b) => strcasecmp($a['adv_name'] ?? '', $b['adv_name'] ?? ''));
?>

<main id="primary" class="site-main">

    <?php /* ── Hero ── */ ?>
    <div class="relative overflow-visible pb-0 pt-10 md:pt-14 lg:pt-16">

        <div class="pointer-events-none absolute inset-x-0 top-0 z-0 overflow-visible lg:hidden" aria-hidden="true">
            <div style="position:absolute;width:30rem;height:30rem;right:-9rem;top:-9rem;border-radius:9999px;background:rgba(0,85,125,0.22);filter:blur(5.5rem);"></div>
            <div style="position:absolute;width:23rem;height:23rem;left:-7rem;top:10rem;border-radius:9999px;background:rgba(0,85,125,0.12);filter:blur(5rem);"></div>
            <img src="<?php echo esc_url($dot_svg); ?>" alt="" aria-hidden="true" width="985" height="986" loading="lazy" decoding="async"
                style="position:absolute; width:32rem; right:-10rem; top:-34rem; max-width:none; opacity:0.24; transform:scale(1.2);">
        </div>

        <div class="pointer-events-none absolute inset-0 z-0 hidden lg:block" aria-hidden="true">
            <div style="position:absolute;width:41rem;height:41rem;left:-12rem;bottom:-10rem;border-radius:9999px;background:rgba(0,85,125,0.12);filter:blur(9rem);"></div>
            <img src="<?php echo esc_url($dot_svg); ?>" alt="" aria-hidden="true" width="985" height="986" loading="lazy" decoding="async"
                style="position:absolute; width:52rem; right:-15rem; top:-50rem; max-width:none; opacity:0.24; transform:scale(1.2);">
        </div>

        <div class="container relative z-10 pb-10 md:pb-14">
            <div class="flex max-w-3xl flex-col items-start gap-4">

                <div class="self-start">
                    <?php
                    get_template_part(
                        'template-parts/components/eyebrow',
                        null,
                        [
                            'text'       => $hero_eyebrow,
                            'icon_class' => $hero_icon,
                        ]
                    );
                    ?>
                </div>

                <h1><?php echo esc_html($hero_heading); ?></h1>

                <?php if ($hero_description) : ?>
                    <p class="!mb-0"><?php echo nl2br(esc_html($hero_description)); ?></p>
                <?php endif; ?>

            </div>
        </div>

        <div class="container"><hr class="!m-0 border-t border-primary-border"></div>

    </div>

    <?php /* ── Intro text (optional) ── */ ?>
    <?php if ($intro_text) : ?>
        <div class="container py-8 md:py-10">
            <div class="max-w-3xl">
                <p class="!mb-0"><?php echo nl2br(esc_html($intro_text)); ?></p>
            </div>
        </div>
        <div class="container"><hr class="!m-0 border-t border-primary-border"></div>
    <?php endif; ?>

    <?php /* ── Partner grid ── */ ?>
    <?php if (! empty($partners)) : ?>
        <div class="container py-10 md:py-14">

            <p class="!mb-8 !text-sm text-text/50">
                <?php
                echo esc_html(sprintf(
                    /* translators: %d = number of advocacy partners */
                    _n('%d advocacy partner', '%d advocacy partners', count($partners), 'elahub'),
                    count($partners)
                ));
                ?>
            </p>

            <ul class="m-0 grid list-none grid-cols-2 gap-5 p-0 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5">

                <?php foreach ($partners as $partner) :
                    $name = trim((string) ($partner['adv_name'] ?? ''));
                    $url  = trim((string) ($partner['adv_url'] ?? ''));
                    $logo = $partner['adv_logo'] ?? null;

                    if (! $name) {
                        continue;
                    }

                    $logo_src = is_array($logo) ? ($logo['url'] ?? '') : '';

                    /*
                     * Accessibility: when the card is a link, the visible partner name
                     * already labels the link destination, so the logo is decorative → alt="".
                     * When the card has no link, the logo needs to convey meaning → alt="[name] logo".
                     */
                    $is_link  = (bool) $url;
                    $logo_alt = $is_link ? '' : $name . ' logo';

                    $tag   = $is_link ? 'a' : 'div';
                    $attrs = $is_link
                        ? sprintf(
                            'href="%s" target="_blank" rel="noopener noreferrer"',
                            esc_url($url)
                        )
                        : '';
                ?>
                    <li>
                        <<?php echo $tag; ?> <?php echo $attrs; ?>
                            class="group flex h-full flex-col items-center gap-3 overflow-hidden rounded-2xl border border-primary-border bg-white p-4 text-center transition<?php echo $is_link ? ' hover:border-primary hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2' : ''; ?>">

                            <?php if ($logo_src) : ?>
                                <div class="flex h-24 w-full flex-shrink-0 items-center justify-center sm:h-28 lg:h-32">
                                    <img
                                        src="<?php echo esc_url($logo_src); ?>"
                                        alt="<?php echo esc_attr($logo_alt); ?>"
                                        class="max-h-full max-w-full object-contain"
                                        loading="lazy"
                                        decoding="async">
                                </div>
                            <?php else : ?>
                                <div class="flex h-24 w-full flex-shrink-0 items-center justify-center rounded-xl bg-primary-soft sm:h-28 lg:h-32" aria-hidden="true">
                                    <span class="text-2xl font-bold text-primary-dark"><?php echo esc_html(strtoupper(substr($name, 0, 2))); ?></span>
                                </div>
                            <?php endif; ?>

                            <p class="!mb-0 text-sm font-semibold leading-snug text-text<?php echo $is_link ? ' underline decoration-text/30 underline-offset-2 group-hover:decoration-primary' : ''; ?>">
                                <?php echo esc_html($name); ?>
                                <?php if ($is_link) : ?>
                                    <span class="sr-only"><?php esc_html_e('(opens in new tab)', 'elahub'); ?></span>
                                <?php endif; ?>
                            </p>

                        </<?php echo $tag; ?>>
                    </li>

                <?php endforeach; ?>

            </ul>

        </div>
    <?php else : ?>
        <div class="container py-16 text-center">
            <p class="!mb-0 text-text/50"><?php esc_html_e('No advocacy partners listed yet.', 'elahub'); ?></p>
        </div>
    <?php endif; ?>

</main>

<?php get_footer(); ?>
