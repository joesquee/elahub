<?php

/**
 * Archive template for Cornerstone Articles (Guides)
 *
 * URL: /guides/ and /guide-category/{slug}/
 * Uses the shared archive-hero component with guide_category filter pills.
 * Routes taxonomy archives through cornerstone-cpt.php template_include filter.
 *
 * @package elahub
 */

get_header();

/* ── Context ─────────────────────────────────────────────────── */

$queried           = get_queried_object();
$current_term_slug = ($queried instanceof WP_Term && 'guide_category' === $queried->taxonomy) ? $queried->slug : '';
$archive_url       = get_post_type_archive_link('cornerstone_article') ?: home_url('/guides/');

/*
 * Editable hero copy.
 * - Category archives (/guide-category/{slug}/): intro comes from the category's
 *   Description field (Guides > Categories), falling back to the Guides intro.
 * - Main /guides/ archive: heading + intro come from Theme Settings > Archive Intros.
 */
$is_guide_term        = ( $queried instanceof WP_Term && 'guide_category' === $queried->taxonomy );
$guides_intro_default = __('In-depth, practical guides covering the core principles of accessible and inclusive learning design. Written by Susi Miller and the eLaHub team.', 'elahub');
$guides_intro_option  = elahub_get_option_field('guides_archive_intro', $guides_intro_default);

if ( $is_guide_term ) {
	$guide_term_desc = trim( wp_strip_all_tags( term_description( $queried->term_id ) ) );
	$hero_heading    = __('Accessibility Guides', 'elahub');
	$hero_desc       = '' !== $guide_term_desc ? $guide_term_desc : $guides_intro_option;
} else {
	$hero_heading = elahub_get_option_field('guides_archive_heading', __('Accessibility Guides', 'elahub'));
	$hero_desc    = $guides_intro_option;
}

$filter_terms = get_terms([
	'taxonomy'   => 'guide_category',
	'hide_empty' => true,
	'orderby'    => 'name',
	'order'      => 'ASC',
]);

if (is_wp_error($filter_terms)) {
	$filter_terms = [];
}
?>

<main id="primary" class="site-main guides-archive-template">

	<?php
	get_template_part(
		'template-parts/hero/archive-hero',
		null,
		[
			'eyebrow_text'      => __('Learning Hub', 'elahub'),
			'eyebrow_icon'      => 'fa-solid fa-book-open',
			'heading'           => $hero_heading,
			'description'       => $hero_desc,
			'terms'             => $filter_terms,
			'current_term_slug' => $current_term_slug,
			'archive_url'       => $archive_url,
		]
	);
	?>

	<div class="container py-8 md:py-12">
		<?php if (have_posts()) : ?>

			<div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
				<?php
				while (have_posts()) :
					the_post();
					get_template_part(
						'template-parts/components/accessibility-guide-card',
						null,
						['post_id' => get_the_ID()]
					);
				endwhile;
				?>
			</div>

			<?php
			the_posts_pagination([
				'mid_size'           => 2,
				'prev_text'          => '<span aria-hidden="true">&larr;</span><span class="sr-only">' . __('Previous page', 'elahub') . '</span>',
				'next_text'          => '<span class="sr-only">' . __('Next page', 'elahub') . '</span><span aria-hidden="true">&rarr;</span>',
				'screen_reader_text' => __('Guides navigation', 'elahub'),
				'class'              => 'mt-10 md:mt-12',
			]);
			?>

		<?php else : ?>
			<p class="!mb-0 py-16 text-center"><?php esc_html_e('No guides found.', 'elahub'); ?></p>
		<?php endif; ?>
	</div>

</main>

<?php get_footer(); ?>