<?php

/**
 * Template Name: Comparison Page
 * Template Post Type: page
 *
 * Used for pricing/comparison pages — hero with decorative glows
 * + configurable grid of pricing cards, then flexible page builder sections.
 *
 * @package elahub
 */

get_header();

$badge_text  = trim((string) (get_field('comparison_badge_text') ?: ''));
$badge_icon  = trim((string) (get_field('comparison_badge_icon') ?: 'fa-solid fa-universal-access'));
$heading     = trim((string) (get_field('comparison_heading') ?: get_the_title()));
$description = trim((string) (get_field('comparison_description') ?: ''));
$columns     = (int) (get_field('comparison_columns') ?: 2);
$cards       = get_field('comparison_cards') ?: [];

$dot_svg = get_template_directory_uri() . '/assets/main-circle-dots.svg';

$grid_classes = [
	1 => 'grid-cols-1',
	2 => 'grid-cols-1 lg:grid-cols-2',
	3 => 'grid-cols-1 lg:grid-cols-3',
	4 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
];
$grid_class = $grid_classes[$columns] ?? $grid_classes[2];
?>

<main id="primary" class="site-main">

	<?php /* ── Hero ── */ ?>
	<section class="relative overflow-visible pb-0 pt-10 md:pt-14 lg:pt-16" aria-labelledby="comparison-hero-heading">

		<?php /* Decorative glows — responsive, using the same approach as page-hero */ ?>
		<div class="pointer-events-none absolute inset-x-0 top-0 z-0 overflow-visible lg:hidden" aria-hidden="true">
			<div style="position:absolute; width:30rem; height:30rem; right:-9rem; top:-9rem; border-radius:9999px; background:rgba(0,85,125,0.22); filter:blur(5.5rem);"></div>
			<div style="position:absolute; width:23rem; height:23rem; left:-7rem; top:10rem; border-radius:9999px; background:rgba(0,85,125,0.12); filter:blur(5rem);"></div>
			<div style="position:absolute; width:18rem; height:18rem; left:-8rem; bottom:-5rem; border-radius:9999px; background:rgba(0,85,125,0.12); filter:blur(5.25rem);"></div>
			<img
				src="<?php echo esc_url($dot_svg); ?>"
				alt=""
				width="985"
				height="986"
				loading="lazy"
				decoding="async"
				style="position:absolute; width:32rem; right:-10rem; top:-34rem; max-width:none; opacity:0.24; transform:scale(1.2);">
		</div>

		<div class="pointer-events-none absolute inset-0 z-0 hidden lg:block" aria-hidden="true">
			<div style="position:absolute; width:41.0556rem; height:41.0556rem; left:-12rem; bottom:-10rem; border-radius:9999px; background:rgba(0,85,125,0.12); filter:blur(9rem);"></div>
			<div style="position:absolute; width:52rem; height:52rem; right:20rem; top:-17rem; border-radius:9999px; background:rgba(0,85,125,0.08); filter:blur(10rem);"></div>
			<img
				src="<?php echo esc_url($dot_svg); ?>"
				alt=""
				width="985"
				height="986"
				loading="lazy"
				decoding="async"
				style="position:absolute; width:52rem; right:-15rem; top:-50rem; max-width:none; opacity:0.24; transform:scale(1.2);">
		</div>

		<div class="container relative z-10">
			<div class="max-w-4xl flex flex-col gap-4 pb-10 md:pb-14 lg:pb-16">

				<?php if ($badge_text) : ?>
					<div class="self-start">
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
					</div>
				<?php endif; ?>

				<?php if ($heading) : ?>
					<h1 id="comparison-hero-heading"><?php echo esc_html($heading); ?></h1>
				<?php endif; ?>

				<?php if ($description) : ?>
					<p class="!mb-0"><?php echo nl2br(esc_html($description)); ?></p>
				<?php endif; ?>

			</div>
		</div>

	</section>

	<?php /* ── Pricing cards ── */ ?>
	<?php if (! empty($cards)) : ?>
		<section class="pb-10 md:pb-14 lg:pb-16">
			<div class="container">
				<div class="grid <?php echo esc_attr($grid_class); ?> gap-4 lg:gap-8 items-start">
					<?php foreach ($cards as $card) :
						get_template_part(
							'template-parts/components/pricing-card',
							null,
							['card' => $card]
						);
					endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php /* ── Page builder sections ── */ ?>
	<?php get_template_part('template-parts/flexible/render-flexible-sections'); ?>

</main>

<?php
get_footer();
