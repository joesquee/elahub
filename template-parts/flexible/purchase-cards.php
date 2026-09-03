<?php

/**
 * Purchase Cards section — flexible content layout
 *
 * Section heading + configurable grid of cards, each with title,
 * description, tick list and one or two buttons.
 *
 * Figma (node 983:42807):
 *   Section:  eyebrow + h2 + description (left), optional button (right)
 *   Card:     border-primary-border, bg-primary-softest
 *             rounded-lg (8px) + rounded-br-[3.667rem] (66px)
 *             px-6 py-12, gap-4 between elements
 *   Title:    h3 (31px semibold, no underline)
 *   Body:     regular text
 *   Tick list: existing tick-list component
 *   Buttons:  mt-auto pt-4 above button row, primary variant
 *
 * @package elahub
 */

$badge_text  = trim((string) (get_sub_field('badge_text') ?: ''));
$badge_icon  = trim((string) (get_sub_field('badge_icon_class') ?: 'fa-solid fa-universal-access'));
$heading     = trim((string) (get_sub_field('heading') ?: ''));
$description = trim((string) (get_sub_field('description') ?: ''));
$button_link = get_sub_field('button_link');
$columns     = (string) (get_sub_field('columns') ?: '3');
$cards       = get_sub_field('cards') ?: [];

$grid_classes = [
	'1' => 'grid-cols-1',
	'2' => 'grid-cols-1 lg:grid-cols-2',
	'3' => 'grid-cols-1 lg:grid-cols-3',
	'4' => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
];
$grid_class = $grid_classes[$columns] ?? $grid_classes['3'];
?>

<?php if ($heading || ! empty($cards)) : ?>
	<?php $purchase_heading_id = 'purchase-cards-heading-' . wp_unique_id(); ?>
	<section class="relative py-8 md:py-12 lg:py-16"<?php if ($heading) : ?> aria-labelledby="<?php echo esc_attr($purchase_heading_id); ?>"<?php endif; ?>>

		<?php get_template_part('template-parts/components/section-bg', null, ['side' => 'right']); ?>

		<div class="container relative z-10">

			<div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between lg:gap-12">
				<div class="flex flex-col items-start gap-4">

					<?php if ($badge_text) : ?>
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
					<?php endif; ?>

					<?php if ($heading) : ?>
						<h2 id="<?php echo esc_attr($purchase_heading_id); ?>"><?php echo esc_html($heading); ?></h2>
					<?php endif; ?>

					<?php if ($description) : ?>
						<p class="!mb-0"><?php echo nl2br(esc_html($description)); ?></p>
					<?php endif; ?>
				</div>

				<?php if (is_array($button_link) && ! empty($button_link['url']) && ! empty($button_link['title'])) : ?>
					<div class="shrink-0">
						<?php
						get_template_part(
							'template-parts/components/button',
							null,
							[
								'url'   => $button_link['url'],
								'label' => $button_link['title'],
							]
						);
						?>
					</div>
				<?php endif; ?>
			</div>

			<?php if (! empty($cards)) : ?>
				<div class="mt-10 grid <?php echo esc_attr($grid_class); ?> items-start gap-4 lg:mt-14 lg:gap-6">
					<?php foreach ($cards as $card) :
						$title      = trim((string) ($card['title'] ?? ''));
						$desc       = trim((string) ($card['description'] ?? ''));
						$list_items = $card['list_items'] ?? [];
						$btn1_label = trim((string) ($card['button_1_label'] ?? ''));
						$btn1_url   = trim((string) ($card['button_1_url'] ?? ''));
						$btn1_aria  = trim((string) ($card['button_1_aria_label'] ?? ''));
						$btn2_label = trim((string) ($card['button_2_label'] ?? ''));
						$btn2_url   = trim((string) ($card['button_2_url'] ?? ''));
						$btn2_aria  = trim((string) ($card['button_2_aria_label'] ?? ''));

						$tick_texts = array_filter(array_map(
							fn($row) => trim((string) ($row['text'] ?? '')),
							(array) $list_items
						));

						if (! $title && ! $desc) {
							continue;
						}
					?>
						<div class="flex h-full flex-col gap-4 rounded-lg rounded-br-[3.667rem] border border-primary-border bg-primary-softest px-6 py-12">

							<?php if ($title) : ?>
								<h3 class="!mb-0"><?php echo esc_html($title); ?></h3>
							<?php endif; ?>

							<?php if ($desc) : ?>
								<p class="!mb-0"><?php echo nl2br(esc_html($desc)); ?></p>
							<?php endif; ?>

							<?php if (! empty($tick_texts)) : ?>
								<?php
								get_template_part(
									'template-parts/components/tick-list',
									null,
									['items' => array_values($tick_texts)]
								);
								?>
							<?php endif; ?>

							<?php if ($btn1_label && $btn1_url) : ?>
								<div class="mt-auto flex flex-wrap items-center gap-3 pt-4">
									<?php
									get_template_part(
										'template-parts/components/button',
										null,
										[
											'url'        => $btn1_url,
											'label'      => $btn1_label,
											'variant'    => 'primary',
											'aria_label' => $btn1_aria ?: $btn1_label,
										]
									);
									?>

									<?php if ($btn2_label && $btn2_url) : ?>
										<?php
										get_template_part(
											'template-parts/components/button',
											null,
											[
												'url'        => $btn2_url,
												'label'      => $btn2_label,
												'variant'    => 'primary',
												'aria_label' => $btn2_aria ?: $btn2_label,
											]
										);
										?>
									<?php endif; ?>
								</div>
							<?php endif; ?>

						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

		</div>
	</section>
<?php endif; ?>
