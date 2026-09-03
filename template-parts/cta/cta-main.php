<?php

/**
 * CTA Main — image + content with link list on dark bg.
 *
 * Grid: 5 cols image / 7 cols content.
 * All text white. Eyebrow uses primary-light bg.
 * Links render as border-separated list, titles optionally linked.
 *
 * Image corner radii from Figma: tl=16 tr=16 bl=16 br=66px.
 *
 * @package elahub
 */

$badge_text = trim((string) (get_sub_field('badge_text') ?: ''));
$badge_icon = trim((string) (get_sub_field('badge_icon_class') ?: 'fa-solid fa-universal-access'));
$heading    = trim((string) (get_sub_field('heading') ?: ''));
$body       = get_sub_field('body') ?: '';
$image      = get_sub_field('image');
$links_raw  = get_sub_field('links') ?: [];
$btn_label  = trim((string) (get_sub_field('button_label') ?: ''));
$btn_url    = trim((string) (get_sub_field('button_url') ?: ''));
$btn_aria   = trim((string) (get_sub_field('button_aria_label') ?: ''));

$img_src = '';
$img_alt = '';
if (is_array($image)) {
	$img_src = $image['url'] ?? '';
	$img_alt = $image['alt'] ?? '';
} elseif (is_numeric($image) && $image) {
	$img_src = wp_get_attachment_image_url((int) $image, 'large') ?: '';
	$img_alt = get_post_meta((int) $image, '_wp_attachment_image_alt', true) ?: '';
}
?>

<div class="grid grid-cols-1 items-stretch gap-8 px-6 py-6 md:px-8 md:py-10 lg:grid-cols-12 lg:gap-0 lg:px-8 lg:py-16">

	<?php /* Image column — 5/12, stretches to content height */ ?>
	<div class="lg:col-span-5 lg:self-stretch">
		<?php if ($img_src) : ?>
			<div class="relative h-full">
				<img
					src="<?php echo esc_url($img_src); ?>"
					alt="<?php echo esc_attr($img_alt); ?>"
					class="block !h-64 w-full object-cover object-top rounded-2xl lg:rounded-br-[3.667rem] md:!h-80 lg:!h-full lg:min-h-full"
					loading="lazy"
					decoding="async">
			</div>
		<?php endif; ?>
	</div>

	<?php /* Content column — 7/12 */ ?>
	<div class="flex flex-col items-start gap-6 lg:col-span-7 lg:px-12 lg:py-8">

		<?php if ($badge_text) : ?>
			<span class="inline-flex items-center gap-2 rounded-sm bg-primary-light px-3 py-2 text-sm leading-none font-semibold text-text">
				<span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-sm bg-primary-dark text-white" aria-hidden="true">
					<i class="<?php echo esc_attr($badge_icon); ?> text-xs"></i>
				</span>
				<span><?php echo esc_html($badge_text); ?></span>
			</span>
		<?php endif; ?>

		<?php if ($heading) : ?>
			<h2 class="!text-white"><?php echo esc_html($heading); ?></h2>
		<?php endif; ?>

		<?php if ($body) : ?>
			<div class="elahub-feature__body elahub-rich-text elahub-rich-text--invert text-white/90">
				<?php echo wp_kses_post($body); ?>
			</div>
		<?php endif; ?>

		<?php if (! empty($links_raw)) : ?>
			<ul class="m-0 w-full list-none p-0">
				<?php foreach ($links_raw as $i => $link_row) : ?>
					<?php
					$link_title = trim((string) ($link_row['link_title'] ?? ''));
					$link_desc  = trim((string) ($link_row['link_description'] ?? ''));
					$link_url   = trim((string) ($link_row['link_url'] ?? ''));
					if (! $link_title) {
						continue;
					}
					?>
					<li class="py-5 <?php echo $i > 0 ? 'border-t border-white/15' : ''; ?>">
						<div class="flex flex-col gap-2">
							<?php if ($link_url) : ?>
								<h3 class="h4">
									<a
										href="<?php echo esc_url($link_url); ?>"
										class="!text-white">
										<?php echo esc_html($link_title); ?>
									</a>
								</h3>
							<?php else : ?>
								<h3 class="h4 m-0 !text-white"><?php echo esc_html($link_title); ?></h3>
							<?php endif; ?>

							<?php if ($link_desc) : ?>
								<p class="m-0 text-white/80"><?php echo esc_html($link_desc); ?></p>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ($btn_label && $btn_url) : ?>
			<?php
			get_template_part(
				'template-parts/components/button',
				null,
				[
					'url'        => $btn_url,
					'label'      => $btn_label,
					'variant'    => 'white',
					'aria_label' => $btn_aria ?: $btn_label,
				]
			);
			?>
		<?php endif; ?>

	</div>

</div>