<?php

/**
 * Shared Learning Hub archive content — site query #10 (and #12).
 *
 * Renders the advocacy logo strip above the listing and, below it, optional
 * testimonials followed by the services block. Editable under
 * Theme Settings > Archive Content, with per-section overrides on each
 * Learning Hub Type term.
 *
 * Usage:
 *   get_template_part( 'template-parts/archive/archive-content', null, [ 'position' => 'top' ] );
 *   get_template_part( 'template-parts/archive/archive-content', null, [ 'position' => 'bottom' ] );
 *
 * @package elahub
 */

$position = ($args['position'] ?? 'top') === 'bottom' ? 'bottom' : 'top';

// The Learning Hub overview has no term behind it, so it takes the globals.
$term    = is_tax('learning_hub_type') ? get_queried_object() : null;
$term_id = ($term && ! is_wp_error($term)) ? 'learning_hub_type_' . $term->term_id : null;

/**
 * Resolve a three-way per-section toggle against the global default.
 * '' (or a missing term) means inherit.
 */
$resolve = static function ($field, $global_default) use ($term_id) {
	if ($term_id) {
		$override = get_field($field, $term_id);

		if ('on' === $override) {
			return true;
		}
		if ('off' === $override) {
			return false;
		}
	}

	$global = elahub_get_option_field($field, null);

	return null === $global ? $global_default : (bool) $global;
};

if ('top' === $position) {

	// Item 10 is global. Books is the exception — it carries the testimonials
	// section instead (item 12).
	$logo_strip_off = apply_filters(
		'elahub_archive_hide_logo_strip_sections',
		['books-and-publications']
	);

	$on_excluded_section = $term && ! is_wp_error($term)
		&& in_array($term->slug, $logo_strip_off, true)
		&& '' === get_term_meta($term->term_id, 'archive_show_advocacy_logos', true);

	if ($on_excluded_section || ! $resolve('archive_show_advocacy_logos', true)) {
		return;
	}

	$defaults = elahub_get_logo_strip_defaults('advocacy');
	$heading  = trim((string) elahub_get_option_field('archive_advocacy_heading', ''));

	if (empty($defaults['logos'])) {
		return;
	}
	?>
	<div class="pb-8">
		<div>
		<?php
		get_template_part(
			'template-parts/flexible/logo-strip',
			null,
			[
				'use_defaults' => true,
				'default_set'  => 'advocacy',
				'heading'      => $heading ?: ($defaults['heading'] ?? ''),
				'section_id'   => 'lh-advocacy-logos',
				'align'        => 'center',
				'logo_size'    => 'medium',
			]
		);
			?>
		</div>
	</div>
	<?php
	return;
}

/* ── Bottom: testimonials (per section), then the services block ── */

/**
 * Sections that show testimonials before anyone touches the toggle. Books and
 * Publications is the one Sally asked for (site query #12); saving the term
 * once overrides this either way.
 */
$testimonial_defaults = apply_filters(
	'elahub_archive_testimonial_default_sections',
	['books-and-publications']
);

$show_testimonials = false;

if ($term && ! is_wp_error($term)) {
	$saved = get_term_meta($term->term_id, 'archive_show_testimonials', true);

	$show_testimonials = ('' === $saved)
		? in_array($term->slug, $testimonial_defaults, true)
		: (bool) $saved;
}

if ($show_testimonials) {

	$term_quotes  = get_field('archive_testimonials_quotes', $term_id) ?: [];
	$term_heading = trim((string) (get_field('archive_testimonials_heading', $term_id) ?: ''));

	$testimonial_args = ['use_global' => true];

	if (! empty($term_quotes)) {
		$testimonial_args['quotes'] = $term_quotes;
	}
	if ($term_heading) {
		$testimonial_args['heading'] = $term_heading;
	}

	get_template_part('template-parts/flexible/testimonials-section', null, $testimonial_args);
}

if (! $resolve('archive_show_services_block', true)) {
	return;
}

$services_heading = trim((string) elahub_get_option_field('archive_services_heading', __('Explore our accessibility services', 'elahub')));
$services_body    = elahub_get_option_field('archive_services_body', '');

get_template_part(
	'template-parts/flexible/services-grid',
	null,
	array_filter(
		[
			'use_global_items' => true,
			'heading'          => $services_heading,
			'body'             => $services_body,
		],
		static fn($value) => '' !== $value && null !== $value
	)
);
