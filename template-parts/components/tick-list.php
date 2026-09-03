<?php

/**
 * Tick list component
 *
 * Reusable tick list used across feature sections and other template parts.
 *
 * @package elahub
 *
 * @param array $args {
 *   @type array  $items  Array of strings — each becomes one tick item.
 * }
 */

$items = $args['items'] ?? [];

if (empty($items)) {
    return;
}
?>

<ul class="elahub-tick-list m-0 list-none p-0">
    <?php foreach ($items as $item) : ?>
        <?php $text = trim((string) $item); ?>
        <?php if ($text) : ?>
            <li class="flex items-start gap-3">
                <span class="elahub-tick-list__icon shrink-0 mt-[0.15em]" aria-hidden="true">
                    <span class="flex h-6 w-6 items-center justify-center rounded-[0.25rem] bg-primary/5">
                        <svg class="h-3 w-3 text-primary" viewBox="0 0 12 12" fill="none" focusable="false">
                            <path d="M2 6L5 9L10 3" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                </span>
                <span><?php echo esc_html($text); ?></span>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>