<?php

/**
 * Case study hero
 *
 * Two variants driven by the cs_hero_type ACF field:
 *   logo       — client logo centred on a blue-tinted panel (Figma node 1011:57717)
 *   full_image — image fills the panel edge-to-edge with object-cover
 *
 * Called from single-case_study.php — all data read directly from the
 * current post context (no $args needed).
 *
 * @package elahub
 */

$post_id     = get_the_ID();
$title       = get_the_title();
$hero_type   = trim((string) (get_field('cs_hero_type') ?: 'logo'));
$image       = get_field('case_study_cover_image');   // returns array
$description = trim((string) (get_field('cs_description') ?: get_the_excerpt()));
$archive_url = get_post_type_archive_link('case_study') ?: home_url('/accessible-learning-case-studies/');

$terms       = get_the_terms($post_id, 'case_study_category');
$term_labels = [];
if (! empty($terms) && ! is_wp_error($terms)) {
	foreach ($terms as $term) {
		$term_labels[] = $term->name;
	}
}

$dot_svg = get_template_directory_uri() . '/assets/main-circle-dots.svg';

// Panel class changes per variant
$panel_base    = 'overflow-hidden rounded-tl-[3.667rem] rounded-br-[3.667rem] rounded-tr-lg rounded-bl-lg';
$panel_logo    = $panel_base . ' flex items-center justify-center p-10 md:p-12';
$panel_full    = $panel_base;
$panel_classes = ('full_image' === $hero_type) ? $panel_full : $panel_logo;
?>

<div class="relative overflow-visible !pb-0 pt-10 md:pt-14 lg:pt-16">

	<?php /* ── Decorative glows (mobile) ── */ ?>
	<div class="pointer-events-none absolute inset-x-0 top-0 z-0 overflow-visible lg:hidden" aria-hidden="true">
		<div style="position:absolute; width:30rem; height:30rem; right:-9rem; top:-9rem; border-radius:9999px; background:rgba(0,85,125,0.22); filter:blur(5.5rem);"></div>
		<div style="position:absolute; width:23rem; height:23rem; left:-7rem; top:10rem; border-radius:9999px; background:rgba(0,85,125,0.12); filter:blur(5rem);"></div>
		<img
			src="<?php echo esc_url($dot_svg); ?>"
			alt=""
			aria-hidden="true"
			width="985"
			height="986"
			loading="lazy"
			decoding="async"
			style="position:absolute; width:32rem; right:-10rem; top:-34rem; max-width:none; opacity:0.24; transform:scale(1.2);">
	</div>

	<?php /* ── Decorative glows (desktop) ── */ ?>
	<div class="pointer-events-none absolute inset-0 z-0 hidden lg:block" aria-hidden="true">
		<div style="position:absolute; width:41.0556rem; height:41.0556rem; left:-12rem; bottom:-10rem; border-radius:9999px; background:rgba(0,85,125,0.12); filter:blur(9rem);"></div>
		<div style="position:absolute; width:52rem; height:52rem; right:20rem; top:-17rem; border-radius:9999px; background:rgba(0,85,125,0.08); filter:blur(10rem);"></div>
		<img
			src="<?php echo esc_url($dot_svg); ?>"
			alt=""
			aria-hidden="true"
			width="985"
			height="986"
			loading="lazy"
			decoding="async"
			style="position:absolute; width:52rem; right:-15rem; top:-50rem; max-width:none; opacity:0.24; transform:scale(1.2);">
	</div>

	<div class="container relative z-10">
		<div class="flex flex-col gap-8 lg:grid lg:grid-cols-[1fr_42%] lg:items-center lg:gap-16">

			<?php /* ── Left: text content ── */ ?>
			<div class="flex flex-col items-start gap-4">

				<a
					href="<?php echo esc_url($archive_url); ?>"
					class="inline-flex items-center gap-2 text-text underline underline-offset-[0.18em] hover:text-primary-dark transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2">
					<svg class="h-4 w-4 shrink-0" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false">
						<path d="M10 13L5 8L10 3" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
					<?php esc_html_e('Back to Case Studies', 'elahub'); ?>
				</a>

				<h1><?php echo esc_html($title); ?></h1>

				<?php if ($description) : ?>
					<p class="!mb-0"><?php echo nl2br(esc_html($description)); ?></p>
				<?php endif; ?>

				<?php if (! empty($term_labels)) : ?>
					<div class="flex flex-wrap items-center gap-2 self-start">
						<?php foreach ($term_labels as $label) : ?>
							<?php
							get_template_part(
								'template-parts/components/category-pill',
								null,
								['label' => $label]
							);
							?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

			</div>

			<?php /* ── Right: image / logo panel ── */ ?>
			<?php if (! empty($image['url'])) : ?>
				<div
					class="<?php echo esc_attr($panel_classes); ?> min-h-[16rem] lg:self-stretch lg:min-h-[28rem]"
					<?php if ('full_image' !== $hero_type) : ?>
					style="background:rgba(0,85,125,0.07);"
					<?php endif; ?>>
					<img
						src="<?php echo esc_url($image['url']); ?>"
						alt="<?php echo esc_attr($image['alt'] ?? ''); ?>"
						class="<?php echo 'full_image' === $hero_type ? 'h-full w-full object-cover' : 'h-auto w-full max-h-40 object-contain'; ?>"
						loading="eager"
						decoding="async"
						<?php if (! empty($image['width'])) : ?>width="<?php echo esc_attr($image['width']); ?>" <?php endif; ?>
						<?php if (! empty($image['height'])) : ?>height="<?php echo esc_attr($image['height']); ?>" <?php endif; ?>>
				</div>
			<?php elseif ('logo' === $hero_type) : ?>
				<?php /* Placeholder panel when no image is set */ ?>
				<div
					class="<?php echo esc_attr($panel_base); ?> flex min-h-[16rem] items-center justify-center lg:self-stretch lg:min-h-[28rem]"
					style="background:rgba(0,85,125,0.07);" aria-hidden="true">
					<i class="fa-solid fa-building text-[4rem] text-primary/20"></i>
				</div>
			<?php endif; ?>

		</div>
	</div>

</div>