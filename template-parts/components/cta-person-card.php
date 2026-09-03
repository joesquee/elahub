<?php

/**
 * CTA Person Card
 *
 * Dark card used inside the CTA section for team / associate profile grids.
 *
 * @package elahub
 *
 * @param array $args {
 *   @type array|string|int $image       ACF image array / ID / URL.
 *   @type string           $name        Person name.
 *   @type string           $role        Role / job title.
 *   @type string           $description Short bio / description.
 * }
 */

$args        = $args ?? [];
$image       = $args['image'] ?? null;
$name        = trim((string) ($args['name'] ?? ''));
$role        = trim((string) ($args['role'] ?? ''));
$description = trim((string) ($args['description'] ?? ''));

if (! $name && ! $role && ! $description && empty($image)) {
	return;
}

$image_url = '';
$image_alt = $name;

if (is_array($image) && ! empty($image['url'])) {
	$image_url = $image['url'];
	$image_alt = $image['alt'] ?: $name;
} elseif (is_numeric($image) && $image) {
	$image_url = wp_get_attachment_image_url((int) $image, 'large') ?: '';
	$image_alt = get_post_meta((int) $image, '_wp_attachment_image_alt', true) ?: $name;
} elseif (is_string($image) && filter_var($image, FILTER_VALIDATE_URL)) {
	$image_url = $image;
}
?>

<div class="flex h-full flex-col gap-4 rounded-lg border border-white/10 bg-white/5 px-4 py-4 md:px-5 md:py-5">
	<?php if ($image_url) : ?>
		<div class="overflow-hidden rounded-lg bg-white/5">
			<img
				src="<?php echo esc_url($image_url); ?>"
				alt="<?php echo esc_attr($image_alt); ?>"
				class="block aspect-[4/3] w-full object-cover object-center"
				loading="lazy"
				decoding="async">
		</div>
	<?php endif; ?>

	<div class="flex flex-col gap-2">
		<?php if ($name) : ?>
			<h3 class="h4 !mb-0 !text-white"><?php echo esc_html($name); ?></h3>
		<?php endif; ?>

		<?php if ($role) : ?>
			<p class="!mb-0 text-sm font-semibold text-white/90"><?php echo esc_html($role); ?></p>
		<?php endif; ?>

		<?php if ($description) : ?>
			<p class="!mb-0 text-sm text-white/80"><?php echo esc_html($description); ?></p>
		<?php endif; ?>
	</div>
</div>
