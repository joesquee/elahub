<?php

/**
 * Pricing card component
 *
 * Used on comparison/pricing pages. Renders a bordered card with:
 * title (underlined h3.h4), price (large), description, optional
 * divider + list label + tick list, and up to two configurable buttons.
 *
 * Figma (node 983:23222):
 *   Card:     border-primary-border, bg-primary-softest
 *             rounded-lg (8px) + rounded-br-[3.667rem] (66px)
 *             px-6 py-12, gap-4 between elements
 *   Title:    h3.h4 with underline
 *   Price:    h1-scale, semibold, tracking tight
 *   Divider:  border-primary-border
 *   List lbl: text-base font-bold
 *   Tick list: existing component
 *   Buttons:  button component, primary variant
 *             pt-4 above button row
 *
 * @package elahub
 *
 * @param array $args {
 *   @type array $card  The ACF repeater row data.
 * }
 */

$card          = $args['card'] ?? [];
$title         = trim((string) ($card['title'] ?? ''));
$price         = trim((string) ($card['price'] ?? ''));
$description   = trim((string) ($card['description'] ?? ''));
$show_divider  = ! empty($card['show_divider']);
$list_label    = trim((string) ($card['list_label'] ?? ''));
$list_items    = $card['list_items'] ?? [];
$btn1_label    = trim((string) ($card['button_1_label'] ?? ''));
$btn1_url      = trim((string) ($card['button_1_url'] ?? ''));
$btn1_aria     = trim((string) ($card['button_1_aria_label'] ?? ''));
$btn2_label    = trim((string) ($card['button_2_label'] ?? ''));
$btn2_url      = trim((string) ($card['button_2_url'] ?? ''));
$btn2_aria     = trim((string) ($card['button_2_aria_label'] ?? ''));

if (! $title && ! $price) {
	return;
}

$tick_texts = array_filter(array_map(
	fn($row) => trim((string) ($row['text'] ?? '')),
	(array) $list_items
));
?>

<div class="flex h-full flex-col gap-4 rounded-lg rounded-br-[3.667rem] border border-primary-border bg-primary-softest px-6 py-12">

	<?php /* Title — h3 styled as h4, underlined per Figma */ ?>
	<?php if ($title) : ?>
		<h3 class="h4 !mb-0 underline underline-offset-2"><?php echo esc_html($title); ?></h3>
	<?php endif; ?>

	<?php /* Price — large display text */ ?>
	<?php if ($price) : ?>
		<p class="!mb-0 h1"><?php echo esc_html($price); ?></p>
	<?php endif; ?>

	<?php /* Description */ ?>
	<?php if ($description) : ?>
		<p class="!mb-0"><?php echo esc_html($description); ?></p>
	<?php endif; ?>

	<?php /* Optional divider + list */ ?>
	<?php if ($show_divider) : ?>
		<hr class="!m-0 !border-t !border-b-0 border-primary-border">
	<?php endif; ?>

	<?php if ($list_label) : ?>
		<p class="!mb-0 !font-bold"><?php echo esc_html($list_label); ?></p>
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

	<?php /* Buttons — mt-auto pushes them to the bottom of the card */ ?>
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