<?php

/**
 * Footer banner
 *
 * @package elahub
 */

$heading      = elahub_get_option_field('footer_banner_heading', 'Talk it through with us');
$copy         = elahub_get_option_field('footer_banner_copy', '');
$button_label = elahub_get_option_field('footer_banner_button_text', 'Book a Call');
$button_url   = elahub_get_option_field('footer_banner_button_url', home_url('/contact-elahub/'));
$image        = elahub_get_option_field('footer_banner_image');

if (! empty($image['url'])) {
	$image_src = $image['url'];
	$image_alt = ! empty($image['alt']) ? $image['alt'] : 'Susi Miller, eLaHub founder';
} else {
	$image_src = get_template_directory_uri() . '/assets/60d190757070e6daff13879907e4c1f24c5743ef.png';
	$image_alt = 'Susi Miller, eLaHub founder';
}

$banner_svg_src = get_template_directory_uri() . '/assets/banner.svg';
?>

<section class="pb-8 md:pb-10">
	<div class="elahub-footer-banner relative overflow-hidden rounded-2xl rounded-bl-[4rem] rounded-br-[4rem] mx-2 sm:mx-0"
		style="background: linear-gradient(90deg, #014563 0%, #026b9a 69.2%);">

		<!-- Dot pattern: absolutely positioned, lg+ only -->
		<div class="elahub-footer-banner__pattern" aria-hidden="true">
			<img src="<?php echo esc_url($banner_svg_src); ?>" alt="" width="789" height="790" loading="lazy" decoding="async">
		</div>

		<!--
			Text column. Figma: px-[33px] py-[66px] on the banner.
			lg = 1280px (input.css). Right padding at lg leaves room for Susi (280px + 2rem offset + gap).
		-->
		<div class="relative z-10 flex flex-col gap-4 px-8 py-8 md:px-10 md:py-10 lg:px-12 lg:py-12 lg:pr-80 2xl:pr-[28rem]">

			<!-- Figma: 43.95px bold leading-[1.47]. We use h2 for semantics so must override input.css clamp. -->
			<h2 id="footer-banner-heading" class="!text-[2.44rem] !font-bold !leading-[1.47] !tracking-[-0.04em] !text-white">
				<?php echo esc_html($heading); ?>
			</h2>

			<?php if ($copy) : ?>
				<!-- Figma: text-[18px] = base size, white, normal weight -->
				<p class="!mb-0 text-white">
					<?php echo nl2br(esc_html($copy)); ?>
				</p>
			<?php endif; ?>

			<div>
				<!-- Figma: rounded-[47px] bg-[#c1d7e2] px-[24px] py-[10px] -->
				<a
					href="<?php echo esc_url($button_url); ?>"
					class="inline-flex items-center justify-center rounded-full bg-primary-light px-6 py-2.5 text-text no-underline transition hover:bg-[#a8c4d4] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-primary-dark"
					aria-label="<?php echo esc_attr($button_label); ?>">
					<?php echo esc_html($button_label); ?>
				</a>
			</div>
		</div>

		<!-- Susi: absolutely positioned bottom-right. CSS controls show/hide at lg (1280px). -->
		<div class="elahub-footer-banner__person">
			<img
				src="<?php echo esc_url($image_src); ?>"
				alt="<?php echo esc_attr($image_alt); ?>"
				width="380"
				height="380"
				loading="lazy"
				decoding="async">
		</div>
	</div>
</section>