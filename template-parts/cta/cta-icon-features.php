<?php

/**
 * CTA Icon Features — icon card grid inside the dark blue CTA section.
 *
 * Figma (node 942:8869):
 *   Section padding: pt-8 pb-16 px-16 (32/66/66px)
 *   Grid gap: gap-8 (32px between cards)
 *   Card: icon box 128px (≈h-28 w-28 at 18px base), bg rgba(230,246,255),
 *         rounded-2xl; title h3.h4 text-white/95; body text-base text-white/80
 *   Internal card gap: gap-4 (16px)
 *   Columns: configurable — 2, 3 (default), or 4 on desktop
 *
 * Icon sources (mirrors page hero pattern):
 *   master_icon  → SVG from assets/eLaHub_Master_Icons/
 *   upload       → ACF image field
 *   font_awesome → FA class string
 *
 * Responsive: desktop cols → tablet 2 → mobile 1 (never 3 on tablet)
 *
 * @package elahub
 */

$heading     = trim((string) (get_sub_field('heading') ?: ''));
$body        = get_sub_field('body') ?: '';
$badge_text  = trim((string) (get_sub_field('badge_text') ?: ''));
$badge_icon  = trim((string) (get_sub_field('badge_icon_class') ?: ''));
$btn_label   = trim((string) (get_sub_field('button_label') ?: ''));
$btn_url     = trim((string) (get_sub_field('button_url') ?: ''));
$btn_aria    = trim((string) (get_sub_field('button_aria_label') ?: ''));
$columns     = (int) (get_sub_field('columns') ?: 3);
$items       = get_sub_field('items') ?: [];

if (empty($items)) {
	return;
}

// Map column count to Tailwind grid classes — 2 or 3 on desktop, never 3 on tablet
$grid_classes = [
	2 => 'grid-cols-1 lg:grid-cols-2',
	3 => 'grid-cols-1 lg:grid-cols-3',
	4 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
];
$grid_class = $grid_classes[$columns] ?? $grid_classes[3];

$icon_dir_uri = get_template_directory_uri() . '/assets/eLaHub_Master_Icons/';
?>

<div class="border-t border-white/10 px-6 md:px-8 lg:px-8 py-8 md:py-12 lg:py-16">

	<?php /* Optional header row */ ?>
	<?php if ($heading || $badge_text || ($btn_label && $btn_url)) : ?>
		<div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between lg:gap-12">
			<div class="flex flex-col gap-4">
				<?php if ($badge_text) : ?>
					<span class="inline-flex items-center gap-2 self-start rounded-sm bg-primary-light px-3 py-2 text-sm font-semibold leading-none text-text">
						<?php if ($badge_icon) : ?>
							<span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-sm bg-primary-dark text-white" aria-hidden="true">
								<i class="<?php echo esc_attr($badge_icon); ?> text-xs"></i>
							</span>
						<?php endif; ?>
						<span><?php echo esc_html($badge_text); ?></span>
					</span>
				<?php endif; ?>

				<?php if ($heading) : ?>
					<h2 class="!text-white"><?php echo esc_html($heading); ?></h2>
				<?php endif; ?>

				<?php if ($body) : ?>
					<div class="elahub-rich-text elahub-rich-text--invert text-white/80">
						<?php echo wp_kses_post($body); ?>
					</div>
				<?php endif; ?>
			</div>

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
	<?php endif; ?>

	<?php /* Feature grid */ ?>
	<div class="grid <?php echo esc_attr($grid_class); ?> gap-8">
		<?php foreach ($items as $item) :
			$item_title  = trim((string) ($item['title'] ?? ''));
			$item_body   = trim((string) ($item['description'] ?? ''));
			$icon_source = $item['icon_source'] ?? 'font_awesome';
			$icon_svg    = $item['icon_svg'] ?? '';
			$icon_upload = $item['icon_upload'] ?? null;
			$icon_fa     = trim((string) ($item['icon_fa_class'] ?? ''));

			if (! $item_title && ! $item_body) {
				continue;
			}
		?>
			<div class="flex flex-col gap-4">

				<?php /* Icon box — 128px in Figma, h-28 w-28 at 18px base ≈ 126px */ ?>
				<div
					class="flex h-28 w-28 shrink-0 items-center justify-center rounded-2xl"
					style="background-color: rgba(230,246,255,1);"
					aria-hidden="true">

					<?php if ('master_icon' === $icon_source && $icon_svg) : ?>
						<img
							src="<?php echo esc_url($icon_dir_uri . $icon_svg); ?>"
							alt=""
							class="block h-auto !max-h-16 w-auto"
							loading="lazy"
							decoding="async">

					<?php elseif ('upload' === $icon_source && is_array($icon_upload) && ! empty($icon_upload['url'])) : ?>
						<img
							src="<?php echo esc_url($icon_upload['url']); ?>"
							alt=""
							class="block h-auto !max-h-16 w-auto"
							loading="lazy"
							decoding="async">

					<?php elseif ('font_awesome' === $icon_source && $icon_fa) : ?>
						<i class="<?php echo esc_attr($icon_fa); ?> !text-4xl !text-primary-dark"></i>

					<?php endif; ?>
				</div>

				<?php /* Title — h3 visually styled as h4 */ ?>
				<?php if ($item_title) : ?>
					<h3 class="h4 !text-white/95"><?php echo esc_html($item_title); ?></h3>
				<?php endif; ?>

				<?php /* Body */ ?>
				<?php if ($item_body) : ?>
					<p class="!m-0 !text-base !text-white/80"><?php echo esc_html($item_body); ?></p>
				<?php endif; ?>

			</div>
		<?php endforeach; ?>
	</div>

</div>