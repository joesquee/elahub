<?php

/**
 * Category pill component
 *
 * Shared between case study cards, accessibility guide cards, and any
 * other card type that needs a taxonomy/category badge.
 *
 * @package elahub
 *
 * @param array $args {
 *   @type string $label  The text label to display.
 *   @type string $class  Additional CSS classes.
 * }
 */

$args  = wp_parse_args(
	$args ?? [],
	[
		'label' => '',
		'class' => '',
	]
);

$label = trim((string) $args['label']);

if (! $label) {
	return;
}

$classes = trim(
	'inline-flex items-center justify-center rounded-full border border-primary-border bg-primary-soft px-4 py-1 ' . $args['class']
);
?>
<div class="<?php echo esc_attr($classes); ?>">
	<span class="!text-[16px] !font-bold !leading-[1.8] !tracking-[-0.01em] text-primary-dark">
		<?php echo esc_html($label); ?>
	</span>
</div>
