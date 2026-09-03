<?php

/**
 * Learning Hub archive template
 *
 * Handles:
 *   - /learning-hub/accessible-learning-live/  (learning_hub_type tax archive)
 *   - /learning-hub/industry-reports/
 *   - /learning-hub/webinars-podcasts/
 *   - /learning-hub/books-and-publications/    (no category pills, + author bio)
 *   - /learning-hub-category/{slug}/           (category archive, optional ?lh_type= filter)
 *
 * Cards use accessibility-guide-card.php; external URL is read from the
 * ACF `lh_external_url` field inside that component.
 *
 * @package elahub
 */

get_header();

/* ── Detect context ────────────────────────────────────────────── */

$queried          = get_queried_object();
$is_type_archive  = $queried instanceof WP_Term && 'learning_hub_type' === $queried->taxonomy;
$is_cat_archive   = $queried instanceof WP_Term && 'learning_hub_category' === $queried->taxonomy;
$active_type_slug = '';
$active_cat_slug  = '';

if ( $is_type_archive ) {
	$active_type_slug = $queried->slug;
} elseif ( $is_cat_archive ) {
	$active_cat_slug  = $queried->slug;
	$active_type_slug = sanitize_key( $_GET['lh_type'] ?? '' );
}

/* ── Per-type config ───────────────────────────────────────────── */

$type_config = [
	'accessible-learning-live' => [
		'heading'     => __( 'Accessible Learning Live', 'elahub' ),
		'description' => __( 'A monthly live series featuring conversations with learning and accessibility specialists, focused on real-world challenges, lessons learned, and practical approaches to accessible learning.', 'elahub' ),
		'eyebrow'     => __( 'Learning Hub', 'elahub' ),
		'icon'        => 'fa-solid fa-podcast',
		'show_pills'  => true,
		'show_bio'    => false,
	],
	'industry-reports' => [
		'heading'     => __( 'Articles & Industry Reports', 'elahub' ),
		'description' => __( 'In-depth articles and industry research on accessible learning — practical insights, emerging trends, and evidence-based guidance to inform your work.', 'elahub' ),
		'eyebrow'     => __( 'Learning Hub', 'elahub' ),
		'icon'        => 'fa-solid fa-newspaper',
		'show_pills'  => true,
		'show_bio'    => false,
	],
	'webinars-podcasts' => [
		'heading'     => __( 'Webinars & Podcasts', 'elahub' ),
		'description' => __( 'Watch and listen to expert-led webinars and podcast episodes covering accessibility in learning design, technology, and practice.', 'elahub' ),
		'eyebrow'     => __( 'Learning Hub', 'elahub' ),
		'icon'        => 'fa-solid fa-microphone',
		'show_pills'  => true,
		'show_bio'    => false,
	],
	'books-and-publications' => [
		'heading'     => __( 'Books', 'elahub' ),
		'description' => __( 'Published books by Susi Miller exploring accessible and inclusive learning design.', 'elahub' ),
		'eyebrow'     => __( 'Learning Hub', 'elahub' ),
		'icon'        => 'fa-solid fa-book-open',
		'show_pills'  => false,
		'show_bio'    => true,
	],
];

// If we're on a category archive without a type filter, derive heading from the category
$config = $type_config[ $active_type_slug ] ?? [
	'heading'    => $queried instanceof WP_Term ? $queried->name : __( 'Learning Hub', 'elahub' ),
	'description'=> $queried instanceof WP_Term ? ( $queried->description ?: '' ) : '',
	'eyebrow'    => __( 'Learning Hub', 'elahub' ),
	'icon'       => 'fa-solid fa-book-open',
	'show_pills' => true,
	'show_bio'   => false,
];

/* ── Taxonomy filter pills ─────────────────────────────────────── */

$filter_terms = [];
$archive_url  = '';

if ( $config['show_pills'] && $active_type_slug ) {
	// Get categories that have at least one item of the current type
	$type_term = get_term_by( 'slug', $active_type_slug, 'learning_hub_type' );
	if ( $type_term ) {
		$archive_url = get_term_link( $type_term );
		$archive_url = is_string( $archive_url ) ? $archive_url : '';

		// Get all categories assigned to posts of this type
		$items_of_type = get_posts( [
			'post_type'        => 'learning_hub_item',
			'posts_per_page'   => -1,
			'fields'           => 'ids',
			'no_found_rows'    => true,
			'tax_query'        => [ [
				'taxonomy' => 'learning_hub_type',
				'field'    => 'slug',
				'terms'    => $active_type_slug,
			] ],
		] );

		if ( ! empty( $items_of_type ) ) {
			$filter_terms = wp_get_object_terms( $items_of_type, 'learning_hub_category', [
				'orderby' => 'name',
				'order'   => 'ASC',
			] );
			$filter_terms = is_wp_error( $filter_terms ) ? [] : $filter_terms;
		}
	}
} elseif ( $config['show_pills'] && $active_cat_slug && ! $active_type_slug ) {
	// Category archive without type — show all categories as pills
	$archive_url  = ''; // no sensible "All" link without a type context
}

/* ── Books author bio fields ───────────────────────────────────── */

// Editable intro: a section's (Type's) own Description field overrides the
// default copy above, so the team can edit it under Learning Hub > Sections.
if ( $is_type_archive && $queried instanceof WP_Term ) {
	$lh_term_desc = trim( wp_strip_all_tags( term_description( $queried->term_id ) ) );
	if ( '' !== $lh_term_desc ) {
		$config['description'] = $lh_term_desc;
	}
}

$show_bio = $config['show_bio'];
$bio_image   = $show_bio ? elahub_get_option_field( 'lh_books_bio_image' ) : null;
$bio_eyebrow = $show_bio ? elahub_get_option_field( 'lh_books_bio_eyebrow', __( 'Accessibility & Learning', 'elahub' ) ) : '';
$bio_heading = $show_bio ? elahub_get_option_field( 'lh_books_bio_heading', __( 'Meet Susi Miller', 'elahub' ) ) : '';
$bio_text    = $show_bio ? elahub_get_option_field( 'lh_books_bio_text', '' ) : '';
$bio_btn_url = $show_bio ? elahub_get_option_field( 'lh_books_bio_button_url', '' ) : '';
$bio_btn_lbl = $show_bio ? elahub_get_option_field( 'lh_books_bio_button_label', __( 'Book a chat', 'elahub' ) ) : '';
?>

<main id="primary" class="site-main">

	<?php /* ── Archive hero ── */ ?>
	<?php
	get_template_part(
		'template-parts/hero/archive-hero',
		null,
		[
			'eyebrow_text'      => $config['eyebrow'],
			'eyebrow_icon'      => $config['icon'],
			'heading'           => $config['heading'],
			'description'       => $config['description'],
			'terms'             => ( $config['show_pills'] && have_posts() ) ? $filter_terms : [],
			'current_term_slug' => $active_cat_slug,
			'archive_url'       => $archive_url,
			'archive_logos'     => true,
		]
	);
	rewind_posts();
	?>

	<?php /* ── Books: Meet Susi — reuses the feature section template part directly ── */ ?>
	<?php if ( $show_bio && $bio_heading ) : ?>
		<?php
		// Convert plain-text paragraphs to HTML for the feature section body field
		$bio_body_html = implode( '', array_map(
			fn( $p ) => '<p>' . nl2br( esc_html( trim( $p ) ) ) . '</p>',
			array_filter( explode( "\n\n", $bio_text ) )
		) );

		get_template_part(
			'template-parts/flexible/feature-section',
			null,
			[
				'variant'    => 'image_left',
				'badge_text' => $bio_eyebrow,
				'badge_icon' => $config['icon'],
				'heading'    => $bio_heading,
				'body'       => $bio_body_html,
				'btn_label'  => $bio_btn_lbl,
				'btn_url'    => $bio_btn_url,
				'image'      => $bio_image,
				'section_id' => 'lh-books-bio-heading',
			]
		);
		?>
		<div class="container"><hr class="!m-0 border-primary-border"></div>
	<?php endif; ?>

	<?php /* ── Card grid ── */ ?>
	<div class="container py-8 md:py-12">

		<?php if ( have_posts() ) : ?>

			<?php if ( $show_bio ) : ?>
				<?php /* Books: section heading above grid */ ?>
				<div class="mb-8 flex flex-col gap-2">
					<h2><?php esc_html_e( 'Books by Susi Miller', 'elahub' ); ?></h2>
				</div>
			<?php endif; ?>

			<div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part(
						'template-parts/components/accessibility-guide-card',
						null,
						[ 'post_id' => get_the_ID() ]
					);
				endwhile;
				?>
			</div>

			<?php
			the_posts_pagination( [
				'mid_size'           => 2,
				'prev_text'          => '<span aria-hidden="true">&larr;</span><span class="sr-only">' . __( 'Previous page', 'elahub' ) . '</span>',
				'next_text'          => '<span class="sr-only">' . __( 'Next page', 'elahub' ) . '</span><span aria-hidden="true">&rarr;</span>',
				'screen_reader_text' => __( 'Learning Hub navigation', 'elahub' ),
				'class'              => 'mt-10 md:mt-12',
			] );
			?>

		<?php else : ?>

			<p class="!mb-0 py-16 text-center">
				<?php esc_html_e( 'No items found.', 'elahub' ); ?>
			</p>

		<?php endif; ?>

	</div>

	<?php /* ── Shared archive content: testimonials + services block ── */ ?>
	<?php get_template_part( 'template-parts/archive/archive-content', null, [ 'position' => 'bottom' ] ); ?>

</main>

<?php get_footer(); ?>
