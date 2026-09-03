<?php

/**
 * Testimonials Section — flexible content layout
 *
 * ACF layout name: testimonials_section
 *
 * Sub-fields:
 *   use_global_testimonials   true_false
 *   testimonials_badge_text   text
 *   testimonials_badge_icon   text
 *   testimonials_heading      text
 *   testimonials_body         textarea
 *   testimonials_button_label text
 *   testimonials_button_url   url
 *   testimonials_quotes       repeater
 *     ↳ quote                 textarea
 *     ↳ author_name           text
 *     ↳ author_role           text
 *     ↳ organisation          text
 *     ↳ avatar                image
 *     ↳ stars                 number (1–5)
 *
 * Global defaults (options → Global Content):
 *   testimonials_default_badge_text, testimonials_default_badge_icon,
 *   testimonials_default_heading, testimonials_default_body,
 *   testimonials_default_button_label, testimonials_default_button_url,
 *   testimonials_default_quotes (repeater, same sub-fields)
 *
 * @package elahub
 */

// Support both page-builder (get_sub_field) and direct $args usage.
$_a = $args ?? [];

$use_global = isset($_a['use_global']) ? (bool) $_a['use_global'] : (bool) get_sub_field('use_global_testimonials');

$default_badge_text   = elahub_get_option_field('testimonials_default_badge_text', 'What people say');
$default_badge_icon   = elahub_get_option_field('testimonials_default_badge_icon', 'fa-solid fa-star');
$default_heading      = elahub_get_option_field('testimonials_default_heading', 'Trusted by organisations committed to inclusive learning');
$default_body         = elahub_get_option_field('testimonials_default_body', '');
$default_button_label = elahub_get_option_field('testimonials_default_button_label', '');
$default_button_url   = elahub_get_option_field('testimonials_default_button_url', '');
$default_quotes       = elahub_get_option_field('testimonials_default_quotes', []);

$badge_text   = isset($_a['badge_text'])   ? trim((string) $_a['badge_text'])   : ($use_global ? $default_badge_text   : trim((string) (get_sub_field('testimonials_badge_text') ?: '')));
$badge_icon   = isset($_a['badge_icon'])   ? trim((string) $_a['badge_icon'])   : ($use_global ? $default_badge_icon   : trim((string) (get_sub_field('testimonials_badge_icon') ?: 'fa-solid fa-star')));
$heading      = isset($_a['heading'])      ? trim((string) $_a['heading'])      : ($use_global ? $default_heading      : trim((string) (get_sub_field('testimonials_heading') ?: '')));
$body         = isset($_a['body'])         ? trim((string) $_a['body'])         : ($use_global ? $default_body         : trim((string) (get_sub_field('testimonials_body') ?: '')));
$button_label = isset($_a['button_label']) ? trim((string) $_a['button_label']) : ($use_global ? $default_button_label : trim((string) (get_sub_field('testimonials_button_label') ?: '')));
$button_url   = isset($_a['button_url'])   ? trim((string) $_a['button_url'])   : ($use_global ? $default_button_url   : trim((string) (get_sub_field('testimonials_button_url') ?: '')));
$quotes_raw   = $_a['quotes'] ?? ($use_global ? $default_quotes : (get_sub_field('testimonials_quotes') ?: []));

/* ---- Normalise quotes ---- */
$quotes = [];
foreach ((array) $quotes_raw as $row) {
	$q = trim((string) ($row['quote'] ?? ''));
	if (! $q) {
		continue;
	}
	$quotes[] = [
		'quote'        => $q,
		'author_name'  => trim((string) ($row['author_name'] ?? '')),
		'author_role'  => trim((string) ($row['author_role'] ?? '')),
		'organisation' => trim((string) ($row['organisation'] ?? '')),
		'avatar'       => $row['avatar'] ?? null,
		'stars'        => max(0, min(5, (int) ($row['stars'] ?? 5))),
	];
}

if (! $heading && empty($quotes)) {
	return;
}
?>

<section class="relative py-12 md:py-16 lg:py-20" aria-labelledby="testimonials-heading">

	<?php get_template_part('template-parts/components/section-bg', null, ['side' => 'right', 'show_dots' => true]); ?>

	<div class="container relative z-10">
		<div class="flex flex-col gap-10">

			<?php /* ---- Header ---- */ ?>
			<div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between lg:gap-10">

				<div class="flex flex-col items-start gap-4 lg:max-w-7xl">
					<?php if ($badge_text) : ?>
						<?php
						get_template_part(
							'template-parts/components/eyebrow',
							null,
							['text' => $badge_text, 'icon_class' => $badge_icon]
						);
						?>
					<?php endif; ?>

					<?php if ($heading) : ?>
						<h2 class="!mb-0" id="testimonials-heading"><?php echo esc_html($heading); ?></h2>
					<?php endif; ?>

					<?php if ($body) : ?>
						<p class="!mb-0"><?php echo nl2br(esc_html($body)); ?></p>
					<?php endif; ?>
				</div>

				<?php if ($button_label && $button_url) : ?>
					<div class="shrink-0 lg:pt-2">
						<?php
						get_template_part(
							'template-parts/components/button',
							null,
							['url' => $button_url, 'label' => $button_label]
						);
						?>
					</div>
				<?php endif; ?>

			</div>

			<?php /* ---- Masonry grid — 3 columns at lg, 2 at md, 1 at mobile ---- */ ?>
			<?php if (! empty($quotes)) : ?>
				<div class="columns-1 gap-4 md:columns-2 lg:columns-3">
					<?php foreach ($quotes as $q) : ?>
						<div class="mb-4 break-inside-avoid">
							<?php
							get_template_part(
								'template-parts/components/quote-card',
								null,
								[
									'variant'      => 'light',
									'quote'        => $q['quote'],
									'author_name'  => $q['author_name'],
									'author_role'  => $q['author_role'],
									'organisation' => $q['organisation'],
									'avatar'       => $q['avatar'],
									'stars'        => $q['stars'],
								]
							);
							?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

		</div>
	</div>
</section>