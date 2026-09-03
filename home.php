<?php

/**
 * Blog archive — Accessibility Insights
 *
 * WordPress uses this template when a page is set as the "Posts page"
 * in Settings → Reading. The template cannot be changed in the admin —
 * this file is how you control the output.
 *
 * Category archives use category.php which shares the same layout.
 *
 * @package elahub
 */

get_header();

/* ── Context ─────────────────────────────────────────────────── */

// On is_home(), get_queried_object() returns the WP_Post for the posts page.
// No term is active — "All" pill is selected.
$current_term_slug = '';

// "All" pill URL — permalink of the posts page itself
$blog_page_id = (int) get_option('page_for_posts');
$archive_url  = $blog_page_id ? get_permalink($blog_page_id) : home_url('/insights/');

$filter_terms = get_categories([
	'hide_empty' => true,
	'orderby'    => 'name',
	'order'      => 'ASC',
]);

if (is_wp_error($filter_terms)) {
	$filter_terms = [];
}
?>

<main id="primary" class="site-main">

	<?php
	get_template_part(
		'template-parts/hero/archive-hero',
		null,
		[
			'eyebrow_text'      => __('Learning Hub', 'elahub'),
			'eyebrow_icon'      => 'fa-solid fa-pen-nib',
			'heading'           => elahub_get_option_field('insights_archive_heading', __('Accessibility Insights', 'elahub')),
			'description'       => elahub_get_option_field('insights_archive_intro', __('Short articles and reflections on accessibility, learning quality, and industry change. Practical perspectives on what\'s working, what\'s not, and where learning design needs to improve.', 'elahub')),
			'terms'             => $filter_terms,
			'current_term_slug' => $current_term_slug,
			'archive_url'       => $archive_url,
			'archive_logos'     => true,
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

			<div class="mt-10 md:mt-12">
				<?php
				the_posts_pagination([
					'mid_size'           => 2,
					'prev_text'          => '<span aria-hidden="true">&larr;</span><span class="sr-only">' . __('Previous page', 'elahub') . '</span>',
					'next_text'          => '<span class="sr-only">' . __('Next page', 'elahub') . '</span><span aria-hidden="true">&rarr;</span>',
					'screen_reader_text' => __('Insights navigation', 'elahub'),
				]);
				?>
			</div>

		<?php else : ?>

			<p class="!mb-0 py-16 text-center">
				<?php esc_html_e('No articles found.', 'elahub'); ?>
			</p>

		<?php endif; ?>

	</div>

	<?php /* ── Shared archive content: services block ── */ ?>
	<?php get_template_part( 'template-parts/archive/archive-content', null, [ 'position' => 'bottom' ] ); ?>

</main>

<?php get_footer(); ?>