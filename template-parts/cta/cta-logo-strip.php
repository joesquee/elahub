<?php

/**
 * CTA Logo Strip — white logos on dark blue background.
 *
 * @package elahub
 */

$use_defaults = (bool) get_sub_field('use_default_dark_logo_strip');

if ($use_defaults) {
	$heading     = function_exists('get_field')
		? trim((string) (get_field('dark_logo_strip_heading', 'option') ?: ''))
		: '';
	$logos       = function_exists('get_field')
		? (get_field('dark_logo_strip_logos', 'option') ?: [])
		: [];
	$section_url = function_exists('get_field')
		? trim((string) (get_field('dark_logo_strip_url', 'option') ?: ''))
		: '';
} else {
	$heading     = trim((string) (get_sub_field('heading') ?: ''));
	$logos       = get_sub_field('logos') ?: [];
	$section_url = trim((string) (get_sub_field('dark_logo_strip_url') ?: ''));
}

if (empty($logos) || ! is_array($logos)) {
	return;
}
?>

<div class="px-6 pt-8 pb-16 md:px-8">
	<div class="flex flex-col items-center gap-6">

		<?php if ($heading) : ?>
			<p class="!m-0 !font-normal !text-center !text-white">
				<?php if ($section_url) : ?>
					<a
						href="<?php echo esc_url($section_url); ?>"
						class="underline underline-offset-2 decoration-white/40 hover:decoration-white text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-primary-dark rounded-sm">
						<?php echo esc_html($heading); ?>
					</a>
				<?php else : ?>
					<?php echo esc_html($heading); ?>
				<?php endif; ?>
			</p>
		<?php endif; ?>

		<ul class="m-0 flex max-w-7xl list-none flex-wrap items-center justify-center gap-x-5 gap-y-5 md:gap-x-6 md:gap-y-6 lg:gap-x-8 lg:gap-y-8 p-0">
			<?php foreach ($logos as $item) :
				$image = $item['logo'] ?? null;
				$alt   = trim((string) ($item['logo_alt'] ?? ''));
				$url   = trim((string) ($item['logo_url'] ?? ''));

				if (empty($image['url'])) {
					continue;
				}

				$img_alt = $alt ?: ($image['alt'] ?? '');
			?>
				<li class="flex w-28 md:w-32 lg:w-40 items-center justify-center">
					<?php if ($url) : ?>
						<a
							href="<?php echo esc_url($url); ?>"
							class="flex h-8 md:h-10 lg:h-14 w-full items-center justify-center rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-primary-dark"
							target="_blank"
							rel="noopener noreferrer"
							aria-label="<?php echo esc_attr($img_alt ?: 'Visit organisation website'); ?>">
						<?php else : ?>
							<div class="flex h-8 md:h-10 lg:h-14 w-full items-center justify-center">
							<?php endif; ?>

							<img
								src="<?php echo esc_url($image['url']); ?>"
								alt="<?php echo esc_attr($img_alt); ?>"
								class="block max-h-8 md:max-h-10 lg:max-h-14 w-auto object-contain opacity-90"
								loading="lazy"
								decoding="async">

							<?php if ($url) : ?>
						</a>
					<?php else : ?>
	</div>
<?php endif; ?>
</li>
<?php endforeach; ?>
</ul>

</div>
</div>