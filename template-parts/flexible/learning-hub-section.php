<?php

/**
 * Learning Hub Section — flexible content layout
 *
 * Shows a heading/description/button row + 3-column grid of Learning Hub
 * items filtered by a single learning_hub_type term.
 *
 * Used for "Featured Webinars and Podcasts" and "Featured Articles &
 * Industry Reports" sections (Figma nodes 996:47504, 996:47564) on the
 * Speaking & Advocacy page (and any other page that needs it).
 *
 * ACF layout name: learning_hub_section
 *
 * @package elahub
 */

$badge_text  = trim( (string) ( get_sub_field( 'lhs_badge_text' ) ?: '' ) );
$badge_icon  = trim( (string) ( get_sub_field( 'lhs_badge_icon_class' ) ?: 'fa-solid fa-universal-access' ) );
$heading     = trim( (string) ( get_sub_field( 'lhs_heading' ) ?: '' ) );
$description = trim( (string) ( get_sub_field( 'lhs_description' ) ?: '' ) );
$button_link = get_sub_field( 'lhs_button_link' );
$type_slug   = trim( (string) ( get_sub_field( 'lhs_type_slug' ) ?: '' ) );
$count       = max( 1, (int) ( get_sub_field( 'lhs_count' ) ?: 3 ) );
$show_hr     = (bool) get_sub_field( 'lhs_show_divider' );

if ( ! $heading && ! $type_slug ) {
	return;
}

/* ── Query items ── */

$post_ids = [];

if ( $type_slug ) {
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
}

$btn_url   = '';
$btn_label = '';

if ( is_array( $button_link ) ) {
	$btn_url   = ! empty( $button_link['url'] ) ? $button_link['url'] : '';
	$btn_label = ! empty( $button_link['title'] ) ? $button_link['title'] : '';
}
?>

<?php if ( $show_hr ) : ?>
	<div class="container"><hr class="!m-0 border-t border-primary-border"></div>
<?php endif; ?>

<?php $lh_heading_id = 'learning-hub-heading-' . wp_unique_id(); ?>
<section class="py-10 md:py-14"<?php if ( $heading ) : ?> aria-labelledby="<?php echo esc_attr($lh_heading_id); ?>"<?php endif; ?>>
	<div class="container">

		<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between lg:gap-16">

			<div class="flex flex-col items-start gap-4">

				<?php if ( $badge_text ) : ?>
					<div class="self-start">
						<?php
						get_template_part(
							'template-parts/components/eyebrow',
							null,
							[
								'text'       => $badge_text,
								'icon_class' => $badge_icon,
							]
						);
						?>
					</div>
				<?php endif; ?>

				<?php if ( $heading ) : ?>
					<h2 class="!mb-0" id="<?php echo esc_attr($lh_heading_id); ?>"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( $description ) : ?>
					<p class="!mb-0"><?php echo nl2br( esc_html( $description ) ); ?></p>
				<?php endif; ?>

			</div>

			<?php if ( $btn_url && $btn_label ) : ?>
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
