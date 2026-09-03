<?php

/**
 * Numbered list component
 *
 * Args:
 * - items: repeater rows with title and text
 *
 * @package elahub
 */

$args = wp_parse_args(
	$args ?? [],
	[
		'items' => [],
	]
);

$items = is_array($args['items']) ? $args['items'] : [];

if (empty($items)) {
	return;
}
?>

<div class="space-y-4">
	<?php
	$index = 1;
	foreach ($items as $item) :
		$title = isset($item['title']) ? trim((string) $item['title']) : '';
		$text  = isset($item['text']) ? trim((string) $item['text']) : '';

		if (!$title && !$text) {
			continue;
		}
	?>
		<div class="flex items-start gap-4">
			<div class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-soft font-bold text-primary-dark">
				<?php echo esc_html($index); ?>
			</div>
			<div class="min-w-0">
				<?php if ($title) : ?>
					<p class="!font-semibold text-text !mb-0"><?php echo esc_html($title); ?></p>
				<?php endif; ?>
				<?php if ($text) : ?>
					<p class="!mt-1 text-text"><?php echo esc_html($text); ?></p>
				<?php endif; ?>
			</div>
		</div>
	<?php
		$index++;
	endforeach;
	?>
</div>