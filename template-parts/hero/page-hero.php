<?php

/**
 * Page Hero — flexible inner-page hero component
 *
 * @package elahub
 */

$badge_text  = trim((string) (get_field('page_hero_badge_text') ?: ''));
$badge_icon  = trim((string) (get_field('page_hero_badge_icon') ?: 'fa-solid fa-universal-access'));
$heading     = trim((string) (get_field('page_hero_heading') ?: get_the_title()));
$description = trim((string) (get_field('page_hero_description') ?: ''));

$btn1_label   = trim((string) (get_field('page_hero_button_1_label') ?: ''));
$btn1_url     = trim((string) (get_field('page_hero_button_1_url') ?: ''));
$btn1_variant = trim((string) (get_field('page_hero_button_1_variant') ?: 'primary'));
$btn2_label   = trim((string) (get_field('page_hero_button_2_label') ?: ''));
$btn2_url     = trim((string) (get_field('page_hero_button_2_url') ?: ''));
$btn2_variant = trim((string) (get_field('page_hero_button_2_variant') ?: 'secondary'));

$page_icon_source   = trim((string) (get_field('page_icon_source') ?: 'master_icon'));
$page_icon_svg      = trim((string) (get_field('page_icon_svg') ?: ''));
$page_icon_fa       = trim((string) (get_field('page_icon_fa_class') ?: 'fa-solid fa-universal-access'));
$page_icon_uploaded = get_field('page_icon_uploaded_image');

$image_style     = trim((string) (get_field('page_hero_image_style') ?: 'full_image'));
$hero_image      = get_field('page_hero_image');
$cutout_position = trim((string) (get_field('page_hero_cutout_position') ?: 'center'));

$img_src = '';
$img_alt = '';
if (is_array($hero_image) && ! empty($hero_image['url'])) {
	$img_src = $hero_image['url'];
	$img_alt = $hero_image['alt'] ?? '';
}

$page_icon_uploaded_src = '';
if (is_array($page_icon_uploaded) && ! empty($page_icon_uploaded['url'])) {
	$page_icon_uploaded_src = $page_icon_uploaded['url'];
}

$page_icon_master_src = '';
if ($page_icon_svg && function_exists('elahub_get_master_icon_uri')) {
	$page_icon_master_src = elahub_get_master_icon_uri($page_icon_svg);
}

$hero_icon_src = '';
$hero_icon_fa  = '';

if ('master_icon' === $page_icon_source && $page_icon_master_src) {
	$hero_icon_src = $page_icon_master_src;
} elseif ('upload' === $page_icon_source && $page_icon_uploaded_src) {
	$hero_icon_src = $page_icon_uploaded_src;
} elseif ('font_awesome' === $page_icon_source && $page_icon_fa) {
	$hero_icon_fa = $page_icon_fa;
}

$dot_svg = get_template_directory_uri() . '/assets/main-circle-dots.svg';

$cutout_object_position = match ($cutout_position) {
	'left'  => 'object-left',
	'right' => 'object-right',
	default => 'object-center',
};

if ('secondary' === $btn1_variant) {
	$btn1_variant = 'primary';
}

if ('secondary' === $btn2_variant) {
	$btn2_variant = 'primary';
}
?>

<section class="relative overflow-visible pb-6 pt-6 md:pt-8 lg:pt-12 xl:pt-16">
	<div class="pointer-events-none absolute inset-x-0 top-0 z-0 overflow-visible lg:hidden" aria-hidden="true">
		<div style="position:absolute; width:30rem; height:30rem; right:-9rem; top:-5rem; border-radius:9999px; background:rgba(0,85,125,0.22); filter:blur(5.5rem);"></div>
		<div style="position:absolute; width:23rem; height:23rem; left:-7rem; top:12rem; border-radius:9999px; background:rgba(0,85,125,0.12); filter:blur(5rem);"></div>
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
			aria-hidden="true"
			width="985"
			height="986"
			loading="lazy"
			decoding="async"
			style="position:absolute; width:52rem; right:-15rem; top:-50rem; max-width:none; opacity:0.24; transform:scale(1.2);">
	</div>

	<div class="container relative z-10">
		<div class="grid gap-8 lg:grid-cols-12 lg:items-center lg:gap-10 xl:gap-16">

			<div class="flex flex-col items-start gap-5 lg:col-span-7 lg:gap-6 xl:pr-4">
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
					<h1 id="page-hero-heading"><?php echo esc_html($heading); ?></h1>
				<?php endif; ?>

				<?php if ($description) : ?>
					<p class="!mb-0 max-w-3xl"><?php echo nl2br(esc_html($description)); ?></p>
				<?php endif; ?>

				<?php if (($btn1_label && $btn1_url) || ($btn2_label && $btn2_url)) : ?>
					<div class="flex flex-wrap items-center gap-3 pt-1">
						<?php if ($btn1_label && $btn1_url) : ?>
							<?php
							get_template_part(
								'template-parts/components/button',
								null,
								[
									'url'     => $btn1_url,
									'label'   => $btn1_label,
									'variant' => $btn1_variant,
									'class'   => 'whitespace-nowrap',
								]
							);
							?>
						<?php endif; ?>

						<?php if ($btn2_label && $btn2_url) : ?>
							<?php
							get_template_part(
								'template-parts/components/button',
								null,
								[
									'url'     => $btn2_url,
									'label'   => $btn2_label,
									'variant' => $btn2_variant,
									'class'   => 'whitespace-nowrap',
								]
							);
							?>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php /* At md (768px) the grid is still single-column — add horizontal padding so the image
			         box sits off the edges at medium viewports. Remove it at lg when the 12-col grid
			         places the column naturally. */ ?>
			<div class="relative lg:col-span-5 lg:px-0 lg:pl-2 xl:pl-4">
				<div class="pointer-events-none absolute inset-0 z-0 hidden overflow-visible lg:block" aria-hidden="true">
					<div style="position:absolute; width:183.9721%; height:183.9721%; left:-1.4817%; top:-68.7129%; border-radius:9999px; background:rgba(0,85,125,0.16); filter:blur(10.5rem);"></div>
					<div style="position:absolute; width:128.7456%; height:128.7456%; left:-12.8919%; top:-13.4653%; border-radius:9999px; background:rgba(0,85,125,0.11); filter:blur(8.5rem);"></div>
					<div style="position:absolute; width:128.7456%; height:128.7456%; left:42.6829%; top:-18.4158%; border-radius:9999px; background:rgba(0,85,125,0.08); filter:blur(8rem);"></div>
					<img
						src="<?php echo esc_url($dot_svg); ?>"
						alt=""
						width="985"
						height="986"
						loading="lazy"
						decoding="async"
						style="display:none;">
				</div>

				<div class="relative z-20 min-h-60 w-full overflow-hidden rounded-2xl bg-primary-soft sm:min-h-80 md:min-h-80 lg:min-h-[28rem] lg:rounded-tl-[4rem] lg:rounded-br-[4rem] xl:min-h-[31rem]">
					<?php if ('full_image' === $image_style && $img_src) : ?>
						<img
							src="<?php echo esc_url($img_src); ?>"
							alt="<?php echo esc_attr($img_alt); ?>"
							class="absolute inset-0 !h-full w-full object-cover object-center"
							loading="eager"
							decoding="async"
							fetchpriority="high">

					<?php elseif ('cutout' === $image_style && $img_src) : ?>
						<img
							src="<?php echo esc_url($img_src); ?>"
							alt="<?php echo esc_attr($img_alt); ?>"
							class="absolute inset-0 h-full w-full object-contain object-bottom <?php echo esc_attr($cutout_object_position); ?>"
							loading="eager"
							decoding="async"
							fetchpriority="high">

					<?php elseif ('icon' === $image_style) : ?>
						<div class="absolute inset-0 flex items-center justify-center p-8 md:p-10 lg:p-8 xl:p-10">
							<?php if ($hero_icon_src) : ?>
								<img
									src="<?php echo esc_url($hero_icon_src); ?>"
									alt=""
									aria-hidden="true"
									class="w-48 sm:w-56 lg:w-60 xl:w-64"
									loading="lazy"
									decoding="async">
							<?php elseif ($hero_icon_fa) : ?>
								<i class="<?php echo esc_attr($hero_icon_fa); ?> text-6xl text-primary-dark sm:text-7xl lg:text-8xl" aria-hidden="true"></i>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>

		</div>
	</div>
</section>