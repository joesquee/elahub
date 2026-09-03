<?php

/**
 * Icon Features section — light background variant
 *
 * Figma (node 946:9686):
 *   Section padding: py-24 (112px ≈ 6rem)
 *   Header row: badge → heading → body → button (left) + button (right)
 *   Card: icon box 128px bg-primary/5 rounded-2xl, gap-4 between elements
 *   Grid gap: gap-x-8 gap-y-12 (32px cols, 48px rows)
 *   Columns: 2, 3 (default), or 4 on desktop
 *   Icon box bg: rgba(0,85,125,0.05) ≈ bg-primary/5
 *
 * Icon sources mirror page hero pattern:
 *   master_icon  → SVG from assets/eLaHub_Master_Icons/
 *   upload       → ACF image field
 *   font_awesome → FA class string
 *
 * @package elahub
 */

$badge_text = trim((string) (get_sub_field('badge_text') ?: ''));
$badge_icon = trim((string) (get_sub_field('badge_icon_class') ?: ''));
$heading    = trim((string) (get_sub_field('heading') ?: ''));
$body       = get_sub_field('body') ?: '';
$btn_link   = get_sub_field('button_link');
$columns    = (int) (get_sub_field('columns') ?: 3);
$items      = get_sub_field('items') ?: [];

if (empty($items)) {
	return;
}

// Grid column classes — never 3 on tablet
$grid_classes = [
	2 => 'grid-cols-1 lg:grid-cols-2',
	3 => 'grid-cols-1 lg:grid-cols-3',
	4 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
];
$grid_class = $grid_classes[$columns] ?? $grid_classes[3];

$icon_dir_uri = get_template_directory_uri() . '/assets/eLaHub_Master_Icons/';

$has_button = is_array($btn_link) && ! empty($btn_link['url']) && ! empty($btn_link['title']);
?>

<?php $icon_features_heading_id = 'icon-features-heading-' . wp_unique_id(); ?>
<section class="relative py-8 md:py-12 lg:py-16"<?php if ($heading) : ?> aria-labelledby="<?php echo esc_attr($icon_features_heading_id); ?>"<?php endif; ?>>

	<?php get_template_part('template-parts/components/section-bg', null, ['side' => 'right']); ?>

	<div class="container relative z-10">

		<?php /* Header row */ ?>
		<?php if ($heading || $badge_text || $has_button) : ?>
			<div class="mb-10 flex flex-col gap-6 lg:mb-14 lg:flex-row lg:items-start lg:justify-between lg:gap-12">

				<div class="flex flex-col items-start gap-4">
					<?php if ($badge_text) : ?>
						<?php
						get_template_part(
							'template-parts/components/eyebrow',
							null,
							[
								'text'       => $badge_text,
								'icon_class' => $badge_icon ?: 'fa-solid fa-universal-access',
							]
						);
						?>
					<?php endif; ?>

					<?php if ($heading) : ?>
						<h2 class="!mb-0" id="<?php echo esc_attr($icon_features_heading_id); ?>"><?php echo esc_html($heading); ?></h2>
					<?php endif; ?>

					<?php if ($body) : ?>
						<div class="elahub-feature__body elahub-rich-text">
							<?php echo wp_kses_post($body); ?>
						</div>
					<?php endif; ?>
				</div>

				<?php if ($has_button) : ?>
					<div class="shrink-0">
						<?php
						get_template_part(
							'template-parts/components/button',
							null,
							[
								'url'   => $btn_link['url'],
								'label' => $btn_link['title'],
							]
						);
						?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php /* Feature grid */ ?>
		<div class="grid <?php echo esc_attr($grid_class); ?> gap-x-8 gap-y-12">
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

					<?php /* Icon box — 128px Figma → h-28 w-28, bg-primary/5 */ ?>
					<div
						class="flex h-28 w-28 shrink-0 items-center justify-center rounded-2xl bg-primary/5"
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

						<?php elseif ($icon_fa) : ?>
							<i class="<?php echo esc_attr($icon_fa); ?> !text-4xl !text-primary" aria-hidden="true"></i>

						<?php endif; ?>
					</div>

					<?php /* Title — h3 visually styled as h4 per input.css */ ?>
					<?php if ($item_title) : ?>
						<h3 class="h4 !mb-0"><?php echo esc_html($item_title); ?></h3>
					<?php endif; ?>

					<?php /* Body */ ?>
					<?php if ($item_body) : ?>
						<p class="!mb-0"><?php echo esc_html($item_body); ?></p>
					<?php endif; ?>

				</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>