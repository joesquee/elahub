<?php

/**
 * The template for displaying all pages
 *
 * @package elahub
 */

get_header();

while ( have_posts() ) :
	the_post();
?>

<main id="primary" class="site-main">

	<div class="container py-12 md:py-16">

		<h1><?php the_title(); ?></h1>

		<hr class="my-8 border-primary-border/50 md:my-10">

		<div class="elahub-content max-w-7xl">
			<?php the_content(); ?>
		</div>

	</div>

	<?php if ( get_edit_post_link() ) : ?>
		<div class="container pb-8">
			<?php edit_post_link(
				sprintf(
					/* translators: %s: post title */
					wp_kses( __( 'Edit <span class="screen-reader-text">%s</span>', 'elahub' ), [ 'span' => [ 'class' => [] ] ] ),
					get_the_title()
				)
			); ?>
		</div>
	<?php endif; ?>

</main>

<?php
	if ( comments_open() || get_comments_number() ) :
		comments_template();
	endif;

endwhile;

get_footer();
