<?php

/**
 * Template Name: Insights (Blog Archive)
 * Template Post Type: page
 *
 * Assign this template to the "Insights" page in the WordPress admin.
 * Uses WP_Query to fetch standard posts, shows category filter pills
 * via archive-hero, and renders cards using accessibility-guide-card.
 *
 * Category pill links go to /category/{slug}/ which is handled by category.php.
 *
 * @package elahub
 */

get_header();

/* ── Category filter pills ─────────────────────────────────────── */

$archive_url  = get_permalink();
$filter_terms = get_categories( [
	'hide_empty' => true,
	'orderby'    => 'name',
	'order'      => 'ASC',
] );

if ( is_wp_error( $filter_terms ) ) {
	$filter_terms = [];
}

/* ── Posts query ───────────────────────────────────────────────── */

$paged = max( 1, get_query_var( 'paged' ) );

$posts_query = new WP_Query( [
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => get_option( 'posts_per_page', 9 ),
	'paged'               => $paged,
	'ignore_sticky_posts' => true,
	'orderby'             => 'date',
	'order'               => 'DESC',
] );
?>

<main id="primary" class="site-main">

	<?php
	get_template_part(
		'template-parts/hero/archive-hero',
		null,
		[
			'eyebrow_text'      => __( 'Learning Hub', 'elahub' ),
			'eyebrow_icon'      => 'fa-solid fa-pen-nib',
			'heading'           => elahub_get_option_field( 'insights_archive_heading', __( 'Accessibility Insights', 'elahub' ) ),
			'description'       => elahub_get_option_field( 'insights_archive_intro', __( 'Short articles and reflections on accessibility, learning quality, and industry change. Practical perspectives on what\'s working, what\'s not, and where learning design needs to improve.', 'elahub' ) ),
			'terms'             => $filter_terms,
			'current_term_slug' => '',  // always "All" on this page
			'archive_url'       => $archive_url,
			'archive_logos'     => true,
		]
	);
	?>

	<div class="container py-8 md:py-12">

		<?php if ( $posts_query->have_posts() ) : ?>

			<div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
				<?php
				while ( $posts_query->have_posts() ) :
					$posts_query->the_post();
					get_template_part(
						'template-parts/components/accessibility-guide-card',
						null,
						[ 'post_id' => get_the_ID() ]
					);
				endwhile;
				wp_reset_postdata();
				?>
			</div>

			<?php
			// Pagination for a custom WP_Query on a page template needs a little help
			$big = 999999999;
			echo paginate_links( [
				'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format'    => '?paged=%#%',
				'current'   => $paged,
				'total'     => $posts_query->max_num_pages,
				'prev_text' => '<span aria-hidden="true">&larr;</span><span class="sr-only">' . __( 'Previous page', 'elahub' ) . '</span>',
				'next_text' => '<span class="sr-only">' . __( 'Next page', 'elahub' ) . '</span><span aria-hidden="true">&rarr;</span>',
				'type'      => 'plain',
				'class'     => 'mt-10 md:mt-12',
			] );
			?>

		<?php else : ?>

			<p class="!mb-0 py-16 text-center">
				<?php esc_html_e( 'No articles found.', 'elahub' ); ?>
			</p>

		<?php endif; ?>

	</div>

	<?php /* ── Shared archive content: services block ── */ ?>
	<?php get_template_part( 'template-parts/archive/archive-content', null, [ 'position' => 'bottom' ] ); ?>

</main>

<?php get_footer(); ?>
