<?php

/**
 * Hero section template part
 *
 * @package elahub
 */

$current_page_id = get_queried_object_id();

if (is_front_page() && (int) get_option('page_on_front') > 0) {
	$current_page_id = (int) get_option('page_on_front');
}

if (! function_exists('elahub_hero_field')) {
	function elahub_hero_field($name, $default = '', $page_id = 0)
	{
		if (! function_exists('get_field')) {
			return $default;
		}

		if ($page_id) {
			$value = get_field($name, $page_id);

			if (null !== $value && '' !== $value && [] !== $value) {
				return $value;
			}
		}

		$value = get_field($name);

		if (null !== $value && '' !== $value && [] !== $value) {
			return $value;
		}

		return $default;
	}
}

if (! function_exists('elahub_get_image_data')) {
	function elahub_get_image_data($image)
	{
		if (is_array($image)) {
			$image_id  = isset($image['ID']) ? (int) $image['ID'] : 0;
			$image_src = ! empty($image['url']) ? $image['url'] : ($image_id ? wp_get_attachment_image_url($image_id, 'full') : '');
			$image_alt = ! empty($image['alt']) ? $image['alt'] : ($image_id ? get_post_meta($image_id, '_wp_attachment_image_alt', true) : '');

			return [
				'src' => $image_src,
				'alt' => $image_alt,
			];
		}

		if (is_numeric($image)) {
			$image_id = (int) $image;

			return [
				'src' => wp_get_attachment_image_url($image_id, 'full'),
				'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true),
			];
		}

		if (is_string($image) && '' !== trim($image)) {
			return [
				'src' => $image,
				'alt' => '',
			];
		}

		return [
			'src' => '',
			'alt' => '',
		];
	}
}

$badge_text   = elahub_hero_field('hero_badge_text', 'Accessibility & Learning', $current_page_id);
$heading      = elahub_hero_field('hero_heading', 'Accessible and inclusive learning content', $current_page_id);
$body         = elahub_hero_field('hero_body', 'We help organisations design, build and improve learning content that works for everyone, including people with a wide range of disabilities and access needs.', $current_page_id);
$btn_label    = elahub_hero_field('hero_button_label', 'Explore Our Training', $current_page_id);
$btn_url      = elahub_hero_field('hero_button_url', home_url('/training-and-programmes/'), $current_page_id);
$btn2_label   = elahub_hero_field('hero_button_2_label', 'Explore Our Services', $current_page_id);
$btn2_url     = elahub_hero_field('hero_button_2_url', home_url('/accessible-elearning-services/'), $current_page_id);
$btn2_variant = elahub_hero_field('hero_button_2_variant', 'primary', $current_page_id);
$image        = elahub_hero_field('hero_image', [], $current_page_id);
$stat_1_val   = elahub_hero_field('hero_stat_1_value', '97%', $current_page_id);
$stat_1_label = elahub_hero_field('hero_stat_1_label', 'Confident in delivering accessible and inclusive learning', $current_page_id);
$stat_2_val   = elahub_hero_field('hero_stat_2_value', '82%', $current_page_id);
$stat_2_label = elahub_hero_field('hero_stat_2_label', 'Say the programme supports legal compliance with accessibility standards', $current_page_id);
$stat_3_val   = elahub_hero_field('hero_stat_3_value', '100%', $current_page_id);
$stat_3_label = elahub_hero_field('hero_stat_3_label', 'Report improved ability to create accessible learning content', $current_page_id);
$stat_note    = elahub_hero_field('hero_stat_footnote', '*Based on post-completion DALC survey feedback.', $current_page_id);

$left_svg  = get_template_directory_uri() . '/assets/hero-left.svg';
$right_svg = get_template_directory_uri() . '/assets/hero-right.svg';

$image_data = elahub_get_image_data($image);
$img_src    = ! empty($image_data['src']) ? $image_data['src'] : '';
$img_alt    = ! empty($image_data['alt']) ? $image_data['alt'] : 'Susi Miller';
?>

<section class="pb-4 lg:pb-5">
	<div class="container container--flush-until-md">
		<div class="relative overflow-hidden rounded-2xl bg-surface-alt lg:!-mx-8 lg:px-8 lg:rounded-br-[6.222rem]">

			<div class="elahub-hero__glow elahub-hero__glow--tr hidden lg:block" aria-hidden="true"></div>
			<div class="elahub-hero__glow elahub-hero__glow--br hidden lg:block" aria-hidden="true"></div>
			<div class="elahub-hero__glow elahub-hero__glow--tl hidden lg:block" aria-hidden="true"></div>
			<div class="elahub-hero__ellipse elahub-hero__ellipse--3" aria-hidden="true"></div>
			<div class="elahub-hero__ellipse elahub-hero__ellipse--2" aria-hidden="true"></div>
			<div class="elahub-hero__ellipse elahub-hero__ellipse--4" aria-hidden="true"></div>

			<div class="elahub-hero__dots-left !opacity-10 lg:!opacity-40" aria-hidden="true">
				<img src="<?php echo esc_url($left_svg); ?>" alt="" width="1091" height="1092" loading="eager" decoding="async">
			</div>

			<div class="elahub-hero__dots-right" aria-hidden="true">
				<img src="<?php echo esc_url($right_svg); ?>" alt="" width="1213" height="1214" loading="eager" decoding="async">
			</div>

			<div class="relative z-10 flex flex-col lg:grid lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)] xl:grid-cols-2 lg:items-stretch lg:gap-6">
				<div class="px-6 pt-10 pb-6 md:px-6 md:pt-6 md:pb-8 lg:flex lg:flex-col lg:justify-center lg:px-0 lg:!pt-46 lg:pb-12 xl:!pt-52 xl:pb-16 items-start">
					<?php
					get_template_part(
						'template-parts/components/eyebrow',
						null,
						[
							'text'       => $badge_text,
							'icon_class' => 'fa-solid fa-universal-access',
						]
					);
					?>

					<h1 id="hero-heading" class="!mt-4">
						<?php echo esc_html($heading); ?>
					</h1>

					<?php if ($body) : ?>
						<p class="m-0 !mt-4 max-w-7xl">
							<?php echo nl2br(esc_html($body)); ?>
						</p>
					<?php endif; ?>

					<?php if (($btn_label && $btn_url) || ($btn2_label && $btn2_url)) : ?>
						<div class="mt-2 flex flex-wrap items-center gap-3">
							<?php if ($btn_label && $btn_url) : ?>
								<?php
								get_template_part(
									'template-parts/components/button',
									null,
									[
										'url'     => $btn_url,
										'label'   => $btn_label,
										'variant' => 'primary',
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

					<?php if ($img_src) : ?>
						<div class="mt-6 w-full lg:hidden">
							<img
								src="<?php echo esc_url($img_src); ?>"
								alt="<?php echo esc_attr($img_alt); ?>"
								width="700"
								height="889"
								loading="eager"
								decoding="async"
								fetchpriority="high"
								class="block w-full rounded-2xl object-cover h-64 md:!h-90 lg:rounded-br-[5.25rem]">
						</div>
					<?php endif; ?>

					<div class="!mt-10 lg:!mt-36 hidden lg:block" aria-label="<?php esc_attr_e('Programme outcomes', 'elahub'); ?>">
						<div class="flex flex-col gap-6 md:flex-row md:gap-3">
							<div class="flex-1 md:border-r md:border-[#cacaca] md:pr-3">
								<p class="!m-0 !text-4xl font-bold"><?php echo esc_html($stat_1_val); ?></p>
								<p class="!mb-0 !mt-3 !text-sm"><?php echo esc_html($stat_1_label); ?></p>
							</div>

							<div class="flex-1 md:border-r md:border-[#cacaca] md:pr-3">
								<p class="!m-0 !text-4xl font-bold"><?php echo esc_html($stat_2_val); ?></p>
								<p class="!mb-0 !mt-3 !text-sm"><?php echo esc_html($stat_2_label); ?></p>
							</div>

							<div class="flex-1">
								<p class="!m-0 !text-4xl font-bold"><?php echo esc_html($stat_3_val); ?></p>
								<p class="!mb-0 !mt-3 !text-sm"><?php echo esc_html($stat_3_label); ?></p>
							</div>
						</div>

						<?php if ($stat_note) : ?>
							<p class="m-0 !mt-6 !text-xs">
								<?php echo esc_html($stat_note); ?>
							</p>
						<?php endif; ?>
					</div>
				</div>

				<?php if ($img_src) : ?>
					<div class="hidden px-8 pb-8 md:px-10 md:pb-10 lg:block lg:self-stretch lg:px-0 lg:py-8 !rounded-br-[5.25rem]">
						<img
							src="<?php echo esc_url($img_src); ?>"
							alt="<?php echo esc_attr($img_alt); ?>"
							width="700"
							height="889"
							loading="eager"
							decoding="async"
							fetchpriority="high"
							class="block !h-full min-h-[24rem] w-full object-cover object-center rounded-2xl lg:rounded-l-none lg:rounded-r-2xl !rounded-br-[5.25rem]">
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>