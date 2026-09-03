<?php

/**
 * Tag archive
 *
 * Handles /tag/{slug}/ URLs for standard blog posts (Insights).
 * Mirrors category.php / the Insights page layout so tag archives match the
 * rest of the site instead of falling back to the unstyled archive.php.
 *
 * The current tag name is shown as the heading, and the tag filter pills let
 * visitors jump to related tags, with the current tag marked active. The
 * "All" pill links back to the Insights listing.
 *
 * @package elahub
 */

get_header();

/* ── Context ───────────────────────────────────────────────────── */

$queried   = get_queried_object(); // WP_Term for the current tag
$tag_name  = $queried instanceof WP_Term ? $queried->name : single_tag_title('', false);
$tag_slug  = $queried instanceof WP_Term ? $queried->slug : '';

$tag_desc = $queried instanceof WP_Term ? trim(wp_strip_all_tags(term_description($queried->term_id))) : '';
if ('' === $tag_desc) {
	/* translators: %s = tag name */
	$tag_desc = sprintf(__('Articles and resources tagged %s.', 'elahub'), $tag_name);
}

// "All" pill links back to the Insights page template.
$insights_page = get_pages(['meta_key' => '_wp_page_template', 'meta_value' => 'template-insights.php']);
$archive_url   = ! empty($insights_page) ? get_permalink($insights_page[0]->ID) : home_url('/blog/');

$filter_terms = get_terms([
	'taxonomy'   => 'post_tag',
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
			'eyebrow_text'      => __('Insights', 'elahub'),
			'eyebrow_icon'      => 'fa-solid fa-pen-nib',
			'heading'           => $tag_name,
			'description'       => $tag_desc,
			'terms'             => $filter_terms,
			'current_term_slug' => $tag_slug, // highlights the current tag pill
			'archive_url'       => $archive_url,
			'filter_label'      => __('Filter by tag', 'elahub'),
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
				'screen_reader_text' => __('Insights navigation', 'elahub'),
				'class'              => 'mt-10 md:mt-12',
			]);
			?>

		<?php else : ?>

			<p class="!mb-0 py-16 text-center">
				<?php esc_html_e('No articles found with this tag.', 'elahub'); ?>
			</p>

		<?php endif; ?>

	</div>

</main>

<?php get_footer(); ?>
