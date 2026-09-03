<?php

/**
 * CTA People Cards — team / associates card grid inside the dark blue CTA section.
 *
 * @package elahub
 */

$heading    = trim((string) (get_sub_field('heading') ?: ''));
$body       = get_sub_field('body') ?: '';
$badge_text = trim((string) (get_sub_field('badge_text') ?: ''));
$badge_icon = trim((string) (get_sub_field('badge_icon_class') ?: ''));
$btn_label  = trim((string) (get_sub_field('button_label') ?: ''));
$btn_url    = trim((string) (get_sub_field('button_url') ?: ''));
$btn_aria   = trim((string) (get_sub_field('button_aria_label') ?: ''));
$columns    = (int) (get_sub_field('columns') ?: 3);
$cards      = get_sub_field('cards') ?: [];

if (empty($cards) && ! $heading && ! $body) {
	return;
}

$grid_classes = [
	2 => 'grid-cols-1 md:grid-cols-2',
	3 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
	4 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
];

$grid_class = $grid_classes[$columns] ?? $grid_classes[3];
?>

<div class="border-t border-white/10 px-6 py-8 md:px-8 md:py-12 lg:px-8 lg:py-16">

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
				<div class="self-start">
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
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if (! empty($cards)) : ?>
		<div class="grid <?php echo esc_attr($grid_class); ?> gap-6">
			<?php foreach ($cards as $card) : ?>
				<?php
				get_template_part(
					'template-parts/components/cta-person-card',
					null,
					[
						'image'       => $card['image'] ?? null,
						'name'        => $card['name'] ?? '',
						'role'        => $card['role'] ?? '',
						'description' => $card['description'] ?? '',
					]
				);
				?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

</div>
