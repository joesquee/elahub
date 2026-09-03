<?php

/**
 * Contact banner section
 *
 * Flexible content layout with Global Content defaults and optional per-section overrides.
 *
 * @package elahub
 */

$use_global = (bool) get_sub_field('use_global_contact_banner');

$default_heading      = elahub_get_option_field('contact_banner_default_heading', 'Talk it through with us');
$default_copy         = elahub_get_option_field('contact_banner_default_copy', 'If you’d like to talk through your learning content and what “good” could look like for accessibility, you can book a short call and we’ll use it to understand what you’re working with, where the main barriers might be, and what a practical next step could be.');
$default_button_label = elahub_get_option_field('contact_banner_default_button_label', 'Book a Call');
$default_button_url   = elahub_get_option_field('contact_banner_default_button_url', home_url('/contact-elahub/'));
$default_image        = elahub_get_option_field('contact_banner_default_image');

$heading      = $use_global ? $default_heading : trim((string) (get_sub_field('contact_banner_heading') ?: ''));
$copy         = $use_global ? $default_copy : trim((string) (get_sub_field('contact_banner_copy') ?: ''));
$button_label = $use_global ? $default_button_label : trim((string) (get_sub_field('contact_banner_button_label') ?: ''));
$button_url   = $use_global ? $default_button_url : trim((string) (get_sub_field('contact_banner_button_url') ?: ''));
$image        = $use_global ? $default_image : get_sub_field('contact_banner_image');

if (! empty($image['url'])) {
	$image_src = $image['url'];
	$image_alt = ! empty($image['alt']) ? $image['alt'] : 'Susi Miller, eLaHub founder';
} else {
	$image_src = get_template_directory_uri() . '/assets/60d190757070e6daff13879907e4c1f24c5743ef.png';
	$image_alt = 'Susi Miller, eLaHub founder';
}

$banner_svg_src = get_template_directory_uri() . '/assets/banner.svg';

if (! $heading && ! $copy && ! $button_label) {
	return;
}
?>

<section class="py-4 md:py-6 lg:py-8">
	<div class="container">
		<div class="elahub-footer-banner relative overflow-hidden rounded-2xl rounded-bl-[4rem] rounded-br-[4rem]"
			style="background: linear-gradient(90deg, #014563 0%, #026b9a 69.2%);">

			<div class="elahub-footer-banner__pattern" aria-hidden="true">
				<img src="<?php echo esc_url($banner_svg_src); ?>" alt="" width="789" height="790" loading="lazy" decoding="async">
			</div>

			<div class="relative z-10 flex flex-col gap-4 px-8 py-8 md:px-10 md:py-10 lg:px-12 lg:py-12 lg:pr-80 2xl:pr-[28rem]">
				<?php if ($heading) : ?>
					<h2 id="contact-banner-heading" class="!text-white">
						<?php echo esc_html($heading); ?>
					</h2>
				<?php endif; ?>

				<?php if ($copy) : ?>
					<p class="!mb-0 text-white">
						<?php echo nl2br(esc_html($copy)); ?>
					</p>
				<?php endif; ?>

				<?php if ($button_label && $button_url) : ?>
					<div>
						<a
							href="<?php echo esc_url($button_url); ?>"
							class="inline-flex items-center justify-center rounded-full bg-primary-light px-6 py-2.5 text-text no-underline transition hover:bg-[#a8c4d4] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-primary-dark"
							aria-label="<?php echo esc_attr($button_label); ?>">
							<?php echo esc_html($button_label); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>

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
	</div>
</section>