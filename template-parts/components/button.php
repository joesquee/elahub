<?php

/**
 * Reusable button component
 *
 * @package elahub
 */

$args = wp_parse_args(
	$args ?? [],
	[
		'url'        => '#',
		'label'      => '',
		'variant'    => 'primary',
		'class'      => '',
		'target'     => '',
		'rel'        => '',
		'aria_label' => '',
	]
);

$base_classes = 'group inline-flex items-center justify-center gap-2 rounded-full px-6 py-3 font-normal transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2';

$variant_classes = [
	'primary'   => '!bg-primary-dark !text-white hover:bg-primary',
	'secondary' => 'border border-primary-border bg-white text-text hover:bg-primary-soft',
	'banner'    => 'bg-white text-text hover:bg-surface-alt',
	'white'     => '!bg-white !text-primary-dark hover:!bg-primary-light',
];

$classes = trim($base_classes . ' ' . ($variant_classes[$args['variant']] ?? $variant_classes['primary']) . ' ' . $args['class']);
?>
<a
	href="<?php echo esc_url($args['url']); ?>"
	class="<?php echo esc_attr($classes); ?>"
	<?php if (! empty($args['target'])) : ?>target="<?php echo esc_attr($args['target']); ?>" <?php endif; ?>
	<?php if (! empty($args['rel'])) : ?>rel="<?php echo esc_attr($args['rel']); ?>" <?php endif; ?>
	<?php if (! empty($args['aria_label'])) : ?>aria-label="<?php echo esc_attr($args['aria_label']); ?>" <?php endif; ?>>
	<span class="underline-offset-2 transition-all duration-200 group-hover:underline group-hover:underline-offset-4"><?php echo esc_html($args['label']); ?></span>
</a>