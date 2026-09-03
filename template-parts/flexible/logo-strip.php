<?php

/**
 * Flexible logo strip
 *
 * @package elahub
 */

// Support both page-builder (get_sub_field) and direct $args usage.
$_a = $args ?? [];

$use_defaults = $_a['use_defaults'] ?? get_sub_field('use_default_logo_strip');
$default_set  = $_a['default_set'] ?? (get_sub_field('logo_strip_default_set') ?: 'standard');
$defaults     = elahub_get_logo_strip_defaults($default_set);

$heading = $_a['heading'] ?? ($use_defaults
	? ($defaults['heading'] ?? '')
	: get_sub_field('logo_strip_heading'));

$logos = $_a['logos'] ?? ($use_defaults
	? ($defaults['logos'] ?? array())
	: get_sub_field('logo_strip_logos'));

$section_url = isset($_a['url'])
	? trim((string) $_a['url'])
	: ($use_defaults
		? trim((string) ($defaults['url'] ?? ''))
		: trim((string) (get_sub_field('logo_strip_url') ?: '')));

$section_id = $_a['section_id'] ?? get_sub_field('section_id');

// Logo size: small | medium (default) | large | xl
$logo_size      = $_a['logo_size'] ?? (get_sub_field('logo_size') ?: 'medium');

// Alignment: 'center' (default) or 'left' when the strip sits inside a
// left-aligned block such as the archive hero.
$align         = ($_a['align'] ?? 'center') === 'left' ? 'left' : 'center';
$align_items   = 'left' === $align ? 'items-start' : 'items-center';
$align_heading = 'left' === $align ? '!text-left' : '!text-center';
$align_list    = 'left' === $align ? 'justify-start' : 'justify-center';
$size_map       = [
	'small'  => [ 'li' => 'w-20 md:w-24 lg:w-28',   'img' => 'max-h-6 md:max-h-8 lg:max-h-10',   'wrap_h' => 'h-6 md:h-8 lg:h-10'   ],
	'medium' => [ 'li' => 'w-28 md:w-32 lg:w-40',   'img' => 'max-h-8 md:max-h-10 lg:max-h-14',  'wrap_h' => 'h-8 md:h-10 lg:h-14'  ],
	'large'  => [ 'li' => 'w-32 md:w-40 lg:w-48',   'img' => 'max-h-10 md:max-h-14 lg:max-h-20', 'wrap_h' => 'h-10 md:h-14 lg:h-20' ],
	'xl'     => [ 'li' => 'w-40 md:w-48 lg:w-56',   'img' => 'max-h-14 md:max-h-20 lg:max-h-24', 'wrap_h' => 'h-14 md:h-20 lg:h-24' ],
];
$sz = $size_map[$logo_size] ?? $size_map['medium'];
$heading_id = $section_id ? $section_id . '-heading' : 'logo-strip-' . wp_unique_id() . '-heading';

if (empty($logos) || ! is_array($logos)) {
	return;
}
?>

<section
	<?php if ($section_id) : ?>
	id="<?php echo esc_attr($section_id); ?>"
	<?php endif; ?>>
	<div class="flex flex-col <?php echo esc_attr($align_items); ?> gap-8 px-6 lg:px-0">

		<?php if ($heading) : ?>
			<h2
				id="<?php echo esc_attr($heading_id); ?>"
				class="!mb-0 !text-base <?php echo esc_attr($align_heading); ?>">
				<?php if ($section_url) : ?>
					<a
						href="<?php echo esc_url($section_url); ?>"
						class="underline underline-offset-2 decoration-text/40 hover:decoration-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2 rounded-sm">
						<?php echo esc_html($heading); ?>
					</a>
				<?php else : ?>
					<span><?php echo esc_html($heading); ?></span>
				<?php endif; ?>
			</h2>
		<?php endif; ?>

		<ul class="flex max-w-7xl flex-wrap items-center <?php echo esc_attr($align_list); ?> gap-x-5 gap-y-5 md:gap-x-6 md:gap-y-6 lg:gap-x-8 lg:gap-y-8 p-0" role="list">
			<?php foreach ($logos as $item) :
				$image = $item['logo'] ?? null;
				$alt   = trim((string) ($item['logo_alt'] ?? ''));
				$url   = trim((string) ($item['logo_url'] ?? ''));

				if (empty($image['url'])) {
					continue;
				}

				$img_alt = $alt ?: ($image['alt'] ?? '');
			?>
				<li class="flex <?php echo esc_attr($sz['li']); ?> items-center justify-center">
					<?php if ($url) : ?>
						<a
							href="<?php echo esc_url($url); ?>"
							class="flex <?php echo esc_attr($sz['wrap_h']); ?> w-full items-center justify-center rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2"
							target="_blank"
							rel="noopener noreferrer"
							aria-label="<?php echo esc_attr($img_alt ?: 'Visit organisation website'); ?>">
						<?php else : ?>
							<div class="flex <?php echo esc_attr($sz['wrap_h']); ?> w-full items-center justify-center">
							<?php endif; ?>

							<img
								src="<?php echo esc_url($image['url']); ?>"
								alt="<?php echo esc_attr($img_alt); ?>"
								class="block <?php echo esc_attr($sz['img']); ?> w-auto object-contain"
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
</section>