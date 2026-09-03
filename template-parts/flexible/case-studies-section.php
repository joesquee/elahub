<?php

/**
 * Case Studies Section — flexible content layout
 *
 * ACF layout name: case_studies_section
 *
 * Sub-fields:
 *   use_global_case_studies     true_false
 *   cs_badge_text               text
 *   cs_badge_icon_class         text
 *   cs_heading                  text
 *   cs_body                     textarea
 *   cs_button_label             text
 *   cs_button_url               url
 *   cs_source                   select  all | by_category | manual
 *   cs_category_slug            text    (when source = by_category)
 *   cs_manual_posts             relationship (when source = manual)
 *   cs_limit                    number
 *
 * Global defaults (options → Global Content):
 *   cs_default_badge_text, cs_default_badge_icon_class,
 *   cs_default_heading, cs_default_body,
 *   cs_default_button_label, cs_default_button_url,
 *   cs_default_source, cs_default_category_slug, cs_default_manual_posts
 *
 * @package elahub
 */

$use_global = (bool) get_sub_field('use_global_case_studies');

$default_badge_text   = elahub_get_option_field('cs_default_badge_text', 'Case Studies');
$default_badge_icon   = elahub_get_option_field('cs_default_badge_icon_class', 'fa-solid fa-building-columns');
$default_heading      = elahub_get_option_field('cs_default_heading', "Organisations We've Delivered For");
$default_body         = elahub_get_option_field('cs_default_body', '');
$default_button_label = elahub_get_option_field('cs_default_button_label', '');
$default_button_url   = elahub_get_option_field('cs_default_button_url', '');
$default_source       = elahub_get_option_field('cs_default_source', 'all');
$default_cat_slug     = elahub_get_option_field('cs_default_category_slug', '');
$default_manual       = elahub_get_option_field('cs_default_manual_posts', []);

$badge_text   = $use_global ? $default_badge_text   : trim((string) (get_sub_field('cs_badge_text') ?: ''));
$badge_icon   = $use_global ? $default_badge_icon   : trim((string) (get_sub_field('cs_badge_icon_class') ?: 'fa-solid fa-building-columns'));
$heading      = $use_global ? $default_heading      : trim((string) (get_sub_field('cs_heading') ?: ''));
$body         = $use_global ? $default_body         : trim((string) (get_sub_field('cs_body') ?: ''));
$button_label = $use_global ? $default_button_label : trim((string) (get_sub_field('cs_button_label') ?: ''));
$button_url   = $use_global ? $default_button_url   : trim((string) (get_sub_field('cs_button_url') ?: ''));
$source       = $use_global ? $default_source       : (get_sub_field('cs_source') ?: 'all');
$cat_slug     = $use_global ? $default_cat_slug     : trim((string) (get_sub_field('cs_category_slug') ?: ''));
$manual_ids   = $use_global ? $default_manual       : get_sub_field('cs_manual_posts');
$limit        = (int) (get_sub_field('cs_limit') ?: 0);

$query_args = [
	'post_type'              => 'case_study',
	'post_status'            => 'publish',
	'posts_per_page'         => $limit > 0 ? $limit : 3,
	'orderby'                => 'menu_order date',
	'order'                  => 'ASC',
	'ignore_sticky_posts'    => true,
	'no_found_rows'          => true,
	'update_post_meta_cache' => false,
	'update_post_term_cache' => false,
];

if ('manual' === $source && ! empty($manual_ids)) {
	$ids                    = array_map('intval', is_array($manual_ids) ? $manual_ids : [$manual_ids]);
	$query_args['post__in'] = $ids;
	$query_args['orderby']  = 'post__in';
} elseif ('by_category' === $source && $cat_slug) {
	$query_args['tax_query'] = [
		[
			'taxonomy' => 'case_study_category',
			'field'    => 'slug',
			'terms'    => $cat_slug,
		],
	];
}

$cs_query = new WP_Query($query_args);

if (! $heading && ! $cs_query->have_posts()) {
	return;
}
?>

<section class="relative py-8 md:py-12 lg:py-16" aria-labelledby="case-studies-heading">

	<?php get_template_part('template-parts/components/section-bg', null, ['side' => 'right', 'show_dots' => true]); ?>

	<div class="container relative z-10">
		<div class="flex flex-col gap-8 lg:gap-10">

			<?php /* ---- Header ---- */ ?>
			<div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between lg:gap-10">

				<div class="flex flex-col items-start gap-4 lg:max-w-7xl">
					<?php if ($badge_text) : ?>
						<?php
						get_template_part(
							'template-parts/components/eyebrow',
							null,
							['text' => $badge_text, 'icon_class' => $badge_icon]
						);
						?>
					<?php endif; ?>

					<?php if ($heading) : ?>
						<h2 class="!mb-0" id="case-studies-heading"><?php echo esc_html($heading); ?></h2>
					<?php endif; ?>

					<?php if ($body) : ?>
						<p class="!mb-0"><?php echo nl2br(esc_html($body)); ?></p>
					<?php endif; ?>
				</div>

				<?php if ($button_label && $button_url) : ?>
					<div class="shrink-0 lg:pt-2">
						<?php
						get_template_part(
							'template-parts/components/button',
							null,
							['url' => $button_url, 'label' => $button_label]
						);
						?>
					</div>
				<?php endif; ?>

			</div>

			<?php /* ---- Cards — separated by horizontal rules ---- */ ?>
			<?php if ($cs_query->have_posts()) : ?>
				<div class="divide-y divide-primary-border">
					<?php while ($cs_query->have_posts()) : $cs_query->the_post(); ?>
						<?php
						get_template_part(
							'template-parts/components/case-study-card',
							null,
							['post_id' => get_the_ID()]
						);
						?>
					<?php endwhile;
					wp_reset_postdata(); ?>
				</div>
			<?php endif; ?>

		</div>
	</div>
</section>