<?php

/**
 * Template for the case_study post-type archive (/case-studies/)
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package elahub
 */

get_header();

/* ── Context ──────────────────────────────────────────────────── */

$terms = get_terms(
	[
		'taxonomy'   => 'case_study_category',
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	]
);

if (is_wp_error($terms)) {
	$terms = [];
}

$archive_url        = get_post_type_archive_link('case_study') ?: home_url('/accessible-learning-case-studies/');
$current_term_slug  = '';

/*
 * Detect if WP is currently filtering by taxonomy.
 * This can happen when this template is used as a fallback, or via
 * pre_get_posts. taxonomy-case_study_category.php handles the true
 * taxonomy archive but sets the same $current_term_slug pattern.
 */
$queried    = get_queried_object();
$is_cs_term = ( $queried instanceof WP_Term && 'case_study_category' === $queried->taxonomy );
if ( $is_cs_term ) {
	$current_term_slug = $queried->slug;
}

/*
 * Editable hero copy.
 * - Category archives (/case-study-category/{slug}/): intro comes from the
 *   category's Description field, falling back to the Case Studies intro.
 * - Main /case-studies/ archive: heading + intro come from Theme Settings > Archive Intros.
 */
$cs_intro_default = __('Explore real-world examples of accessible learning in practice. These case studies highlight how organisations and learning platforms have applied accessibility principles to improve learner experience, meet standards, and create learning that works for everyone.', 'elahub');
$cs_intro_option  = elahub_get_option_field('case_studies_archive_intro', $cs_intro_default);

if ( $is_cs_term ) {
	$cs_term_desc = trim( wp_strip_all_tags( term_description( $queried->term_id ) ) );
	$hero_heading = __('Case Studies', 'elahub');
	$hero_desc    = '' !== $cs_term_desc ? $cs_term_desc : $cs_intro_option;
} else {
	$hero_heading = elahub_get_option_field('case_studies_archive_heading', __('Case Studies', 'elahub'));
	$hero_desc    = $cs_intro_option;
}

/* ── Hero ─────────────────────────────────────────────────────── */
?>

<main id="primary" class="site-main">

	<?php
	get_template_part(
		'template-parts/hero/archive-hero',
		null,
		[
			'eyebrow_text'      => esc_html__('Accessibility & Learning', 'elahub'),
			'eyebrow_icon'      => 'fa-solid fa-universal-access',
			'heading'           => $hero_heading,
			'description'       => $hero_desc,
			'terms'             => $terms,
			'current_term_slug' => $current_term_slug,
			'archive_url'       => $archive_url,
		]
	);
	?>

	<?php /* ── Cards loop ── */ ?>
	<div class="container">

		<?php if (have_posts()) : ?>

			<div class="divide-y divide-primary-border">
				<?php
				while (have_posts()) :
					the_post();
					get_template_part(
						'template-parts/components/case-study-card',
						null,
						['post_id' => get_the_ID()]
					);
				endwhile;
				?>
			</div>

			<?php
			the_posts_pagination(
				[
					'mid_size'           => 2,
					'prev_text'          => '<span aria-hidden="true">&larr;</span><span class="sr-only">' . __('Previous page', 'elahub') . '</span>',
					'next_text'          => '<span class="sr-only">' . __('Next page', 'elahub') . '</span><span aria-hidden="true">&rarr;</span>',
					'before_page_number' => '<span class="sr-only">' . __('Page', 'elahub') . ' </span>',
					'screen_reader_text' => __('Case studies navigation', 'elahub'),
					'class'              => 'mt-10 md:mt-12',
				]
			);
			?>

		<?php else : ?>

			<p class="!mb-0 py-16 text-center"><?php esc_html_e('No case studies found.', 'elahub'); ?></p>

		<?php endif; ?>

	</div>

</main>

<?php get_footer(); ?>