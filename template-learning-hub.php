<?php

/**
 * Template Name: Learning Hub
 * Template Post Type: page
 *
 * Learning Hub overview page (Figma node 1016:59437).
 *
 * Layout:
 *   Hero (eyebrow + h1 + description + glows + hr)
 *   For each ACF section row:
 *     <hr> + heading / description / "View all" button
 *     3-column card grid (source determined per row)
 *
 * @package elahub
 */

get_header();

$dot_svg  = get_template_directory_uri() . '/assets/main-circle-dots.svg';
$sections = get_field( 'lh_sections' ) ?: [];

// Hero text — falls back to page title/content
$hero_heading     = trim( (string) ( get_field( 'lh_hero_heading' ) ?: get_the_title() ) );
$hero_description = trim( (string) ( get_field( 'lh_hero_description' ) ?: '' ) );
$hero_eyebrow     = trim( (string) ( get_field( 'lh_hero_eyebrow' ) ?: __( 'Accessibility & Learning', 'elahub' ) ) );
$hero_icon        = trim( (string) ( get_field( 'lh_hero_icon' ) ?: 'fa-solid fa-universal-access' ) );
?>

<main id="primary" class="site-main">

	<?php /* ── Hero ── */ ?>
	<div class="relative overflow-visible pb-0 pt-10 md:pt-14 lg:pt-16">

		<div class="pointer-events-none absolute inset-x-0 top-0 z-0 overflow-visible lg:hidden" aria-hidden="true">
			<div style="position:absolute; width:30rem; height:30rem; right:-9rem; top:-9rem; border-radius:9999px; background:rgba(0,85,125,0.22); filter:blur(5.5rem);"></div>
			<div style="position:absolute; width:23rem; height:23rem; left:-7rem; top:10rem; border-radius:9999px; background:rgba(0,85,125,0.12); filter:blur(5rem);"></div>
			<img src="<?php echo esc_url( $dot_svg ); ?>" alt="" aria-hidden="true" width="985" height="986" loading="lazy" decoding="async"
				style="position:absolute; width:32rem; right:-10rem; top:-34rem; max-width:none; opacity:0.24; transform:scale(1.2);">
		</div>

		<div class="pointer-events-none absolute inset-0 z-0 hidden lg:block" aria-hidden="true">
			<div style="position:absolute; width:41.0556rem; height:41.0556rem; left:-12rem; bottom:-10rem; border-radius:9999px; background:rgba(0,85,125,0.12); filter:blur(9rem);"></div>
			<div style="position:absolute; width:52rem; height:52rem; right:20rem; top:-17rem; border-radius:9999px; background:rgba(0,85,125,0.08); filter:blur(10rem);"></div>
			<img src="<?php echo esc_url( $dot_svg ); ?>" alt="" aria-hidden="true" width="985" height="986" loading="lazy" decoding="async"
				style="position:absolute; width:52rem; right:-15rem; top:-50rem; max-width:none; opacity:0.24; transform:scale(1.2);">
		</div>

		<div class="container relative z-10">
			<div class="flex max-w-4xl flex-col items-start gap-4 pb-8">

				<div class="self-start">
					<?php
					get_template_part(
						'template-parts/components/eyebrow',
						null,
						[
							'text'       => $hero_eyebrow,
							'icon_class' => $hero_icon,
						]
					);
					?>
				</div>

				<?php if ( $hero_heading ) : ?>
					<h1><?php echo esc_html( $hero_heading ); ?></h1>
				<?php endif; ?>

				<?php if ( $hero_description ) : ?>
					<p class="!mb-0"><?php echo nl2br( esc_html( $hero_description ) ); ?></p>
				<?php endif; ?>

			</div>

			<?php /* ── Shared archive content: advocacy logos ── */ ?>
			<?php get_template_part( 'template-parts/archive/archive-content', null, [ 'position' => 'top' ] ); ?>

			<hr class="!m-0 border-t border-primary-border">
		</div>

	</div>

	<?php /* ── Content sections ── */ ?>

	<?php if ( ! empty( $sections ) ) : ?>
		<?php foreach ( $sections as $section ) :

			$heading     = trim( (string) ( $section['lh_section_heading'] ?? '' ) );
			$description = trim( (string) ( $section['lh_section_description'] ?? '' ) );
			$btn_label   = trim( (string) ( $section['lh_section_button_label'] ?? '' ) );
			$btn_url     = trim( (string) ( $section['lh_section_button_url'] ?? '' ) );
			$source      = $section['lh_section_source'] ?? 'cornerstone_articles';
			$type_slug   = trim( (string) ( $section['lh_section_lh_type_slug'] ?? '' ) );
			$count       = max( 1, (int) ( $section['lh_section_count'] ?: 3 ) );

			/* ── Query posts for this section ── */
			$post_ids = [];

			if ( 'cornerstone_articles' === $source ) {

				$post_ids = get_posts( [
					'post_type'              => 'cornerstone_article',
					'post_status'            => 'publish',
					'posts_per_page'         => $count,
					'fields'                 => 'ids',
					'orderby'                => 'date',
					'order'                  => 'DESC',
					'no_found_rows'          => true,
					'ignore_sticky_posts'    => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				] );

			} elseif ( 'learning_hub_type' === $source && $type_slug ) {

				$post_ids = get_posts( [
					'post_type'              => 'learning_hub_item',
					'post_status'            => 'publish',
					'posts_per_page'         => $count,
					'fields'                 => 'ids',
					'orderby'                => 'date',
					'order'                  => 'DESC',
					'no_found_rows'          => true,
					'ignore_sticky_posts'    => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
					'tax_query'              => [ [
						'taxonomy' => 'learning_hub_type',
						'field'    => 'slug',
						'terms'    => $type_slug,
					] ],
				] );

			} elseif ( 'blog_posts' === $source ) {

				$post_ids = get_posts( [
					'post_type'              => 'post',
					'post_status'            => 'publish',
					'posts_per_page'         => $count,
					'fields'                 => 'ids',
					'orderby'                => 'date',
					'order'                  => 'DESC',
					'no_found_rows'          => true,
					'ignore_sticky_posts'    => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				] );

			}

			if ( ! $heading && empty( $post_ids ) ) {
				continue;
			}

			$section_id = 'lh-section-' . wp_unique_id();
		?>

			<section<?php if ( $heading ) : ?> aria-labelledby="<?php echo esc_attr( $section_id ); ?>"<?php endif; ?>>
			<div class="container py-10 md:py-14">

				<?php /* Section header row */ ?>
				<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between lg:gap-16">

					<div class="flex flex-col gap-4">
						<?php if ( $heading ) : ?>
							<h2 class="!mb-0" id="<?php echo esc_attr( $section_id ); ?>"><?php echo esc_html( $heading ); ?></h2>
						<?php endif; ?>
						<?php if ( $description ) : ?>
							<p class="!mb-0"><?php echo nl2br( esc_html( $description ) ); ?></p>
						<?php endif; ?>
					</div>

					<?php if ( $btn_label && $btn_url ) : ?>
						<div class="shrink-0">
							<?php
							get_template_part(
								'template-parts/components/button',
								null,
								[
									'url'   => $btn_url,
									'label' => $btn_label,
								]
							);
							?>
						</div>
					<?php endif; ?>

				</div>

				<?php /* Card grid */ ?>
				<?php if ( ! empty( $post_ids ) ) : ?>
					<div class="mt-10 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
						<?php foreach ( $post_ids as $post_id ) : ?>
							<?php
							get_template_part(
								'template-parts/components/accessibility-guide-card',
								null,
								[ 'post_id' => (int) $post_id ]
							);
							?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

			</div>
			</section>

			<div class="container"><hr class="!m-0 border-t border-primary-border"></div>

		<?php endforeach; ?>
	<?php endif; ?>

	<?php /* ── Shared archive content: testimonials + services block ── */ ?>
	<?php get_template_part( 'template-parts/archive/archive-content', null, [ 'position' => 'bottom' ] ); ?>

</main>

<?php get_footer(); ?>
