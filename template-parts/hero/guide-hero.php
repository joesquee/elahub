<?php

/**
 * Guide hero
 *
 * Hero for single cornerstone_article pages (Figma node 1027:71482).
 *
 * Layout:
 *   - Back link + h1 + meta row (last updated · read time) + category pill
 *   - Full-width feature image below, rounded-tl-[3.667rem] rounded-br-[3.667rem]
 *
 * All data read from the current post context — no $args needed.
 *
 * @package elahub
 */

$post_id      = get_the_ID();
$title        = get_the_title();
$archive_url  = get_post_type_archive_link( 'cornerstone_article' ) ?: home_url( '/guides/' );

// ACF meta
$last_updated = trim( (string) ( get_field( 'guide_last_updated' ) ?: '' ) );
$read_time    = trim( (string) ( get_field( 'guide_read_time' ) ?: '' ) );

// Format date if it came back as Y-m-d from date_picker
if ( $last_updated && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $last_updated ) ) {
	$last_updated = date_i18n( 'jS F Y', strtotime( $last_updated ) );
}

// Category pill from guide_category taxonomy
$terms      = get_the_terms( $post_id, 'guide_category' );
$term_label = ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';

// Featured image (post thumbnail used as the hero image)
$hero_img_id  = get_post_thumbnail_id( $post_id );
$hero_img_src = $hero_img_id ? wp_get_attachment_image_url( $hero_img_id, 'full' ) : '';
$hero_img_alt = $hero_img_id ? trim( (string) get_post_meta( $hero_img_id, '_wp_attachment_image_alt', true ) ) : '';
if ( ! $hero_img_alt ) {
	$hero_img_alt = $title;
}

$dot_svg = get_template_directory_uri() . '/assets/main-circle-dots.svg';
?>

<div class="relative overflow-visible pb-0 pt-10 md:pt-14 lg:pt-16">

	<?php /* ── Decorative glows (mobile) ── */ ?>
	<div class="pointer-events-none absolute inset-x-0 top-0 z-0 overflow-visible lg:hidden" aria-hidden="true">
		<div style="position:absolute; width:30rem; height:30rem; right:-9rem; top:-9rem; border-radius:9999px; background:rgba(0,85,125,0.22); filter:blur(5.5rem);"></div>
		<div style="position:absolute; width:23rem; height:23rem; left:-7rem; top:10rem; border-radius:9999px; background:rgba(0,85,125,0.12); filter:blur(5rem);"></div>
		<img src="<?php echo esc_url( $dot_svg ); ?>" alt="" aria-hidden="true" width="985" height="986" loading="lazy" decoding="async"
			style="position:absolute; width:32rem; right:-10rem; top:-34rem; max-width:none; opacity:0.24; transform:scale(1.2);">
	</div>

	<?php /* ── Decorative glows (desktop) ── */ ?>
	<div class="pointer-events-none absolute inset-0 z-0 hidden lg:block" aria-hidden="true">
		<div style="position:absolute; width:41.0556rem; height:41.0556rem; left:-12rem; bottom:-10rem; border-radius:9999px; background:rgba(0,85,125,0.12); filter:blur(9rem);"></div>
		<div style="position:absolute; width:52rem; height:52rem; right:20rem; top:-17rem; border-radius:9999px; background:rgba(0,85,125,0.08); filter:blur(10rem);"></div>
		<img src="<?php echo esc_url( $dot_svg ); ?>" alt="" aria-hidden="true" width="985" height="986" loading="lazy" decoding="async"
			style="position:absolute; width:52rem; right:-15rem; top:-50rem; max-width:none; opacity:0.24; transform:scale(1.2);">
	</div>

	<div class="container relative z-10 flex flex-col gap-8">

		<?php /* ── Text block ── */ ?>
		<div class="flex flex-col items-start gap-4">

			<a
				href="<?php echo esc_url( $archive_url ); ?>"
				class="inline-flex items-center gap-2 text-text underline underline-offset-[0.18em] transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2">
				<svg class="h-4 w-4 shrink-0" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false">
					<path d="M10 13L5 8L10 3" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<?php esc_html_e( 'Back to Guides', 'elahub' ); ?>
			</a>

			<h1><?php echo esc_html( $title ); ?></h1>

			<?php if ( $last_updated || $read_time ) : ?>
				<p class="!mb-0 flex flex-wrap items-center gap-3 !text-sm text-text/70">
					<?php if ( $last_updated ) : ?>
						<span><?php echo esc_html( sprintf( __( 'Last Updated: %s', 'elahub' ), $last_updated ) ); ?></span>
					<?php endif; ?>
					<?php if ( $last_updated && $read_time ) : ?>
						<span class="inline-block h-1.5 w-1.5 rounded-full bg-text/40" aria-hidden="true"></span>
					<?php endif; ?>
					<?php if ( $read_time ) : ?>
						<span><?php echo esc_html( $read_time ); ?></span>
					<?php endif; ?>
				</p>
			<?php endif; ?>

			<?php if ( $term_label ) : ?>
				<div class="self-start">
					<?php
					get_template_part(
						'template-parts/components/category-pill',
						null,
						[ 'label' => $term_label ]
					);
					?>
				</div>
			<?php endif; ?>

		</div>

		<?php /* ── Feature image ── */ ?>
		<?php if ( $hero_img_src ) : ?>
			<div class="h-72 overflow-hidden rounded-tl-[3.667rem] rounded-br-[3.667rem] rounded-tr-lg rounded-bl-lg md:h-96 lg:h-[33.667rem]">
				<img
					src="<?php echo esc_url( $hero_img_src ); ?>"
					alt="<?php echo esc_attr( $hero_img_alt ); ?>"
					class="h-full w-full object-cover"
					loading="eager"
					decoding="async">
			</div>
		<?php endif; ?>

	</div>

	<hr class="!m-0 mt-8 border-t border-primary-border">

</div>
