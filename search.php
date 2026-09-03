<?php
/**
 * Search results
 *
 * @package elahub
 */

get_header();

$query   = get_search_query();
$count   = $GLOBALS['wp_query']->found_posts;
?>

<main id="primary" class="site-main">

	<?php /* ── Header ── */ ?>
	<section class="relative border-b border-primary-border/50 py-10 md:py-14">

		<?php get_template_part( 'template-parts/components/section-bg', null, [ 'side' => 'right' ] ); ?>

		<div class="container relative z-10 flex flex-col gap-4">
			<?php if ( $query ) : ?>
				<p class="!mb-0 text-sm font-medium uppercase tracking-widest text-primary-dark">
					<?php
					printf(
						/* translators: %s: search query */
						esc_html( _n( '%d result for', '%d results for', $count, 'elahub' ) ),
						(int) $count
					);
					?>
				</p>
				<h1 class="!mt-0">&#8220;<?php echo esc_html( $query ); ?>&#8221;</h1>
			<?php else : ?>
				<h1><?php esc_html_e( 'Search', 'elahub' ); ?></h1>
			<?php endif; ?>

			<?php get_search_form(); ?>
		</div>
	</section>

	<?php /* ── Results ── */ ?>
	<section class="relative py-10 md:py-14">
		<div class="container relative z-10">

			<?php if ( have_posts() ) : ?>

				<div class="flex flex-col divide-y divide-primary-border/40">
					<?php while ( have_posts() ) : the_post(); ?>

						<article <?php post_class( 'py-8 first:pt-0' ); ?> aria-labelledby="search-result-<?php the_ID(); ?>">
							<div class="flex flex-col gap-2">

								<?php /* Post type badge */ ?>
								<p class="!mb-0 text-sm font-medium uppercase tracking-widest text-primary-dark">
									<?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ?? get_post_type() ); ?>
								</p>

								<?php /* Title */ ?>
								<h2 id="search-result-<?php the_ID(); ?>" class="h4 !mb-0">
									<a href="<?php the_permalink(); ?>" class="underline underline-offset-2 transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2">
										<?php the_title(); ?>
									</a>
								</h2>

								<?php /* Excerpt */ ?>
								<?php if ( has_excerpt() || get_the_excerpt() ) : ?>
									<p class="!mb-0 text-text/70 line-clamp-3"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
								<?php endif; ?>

								<?php /* URL breadcrumb */ ?>
								<p class="!mb-0 text-sm text-text/50">
									<?php echo esc_html( str_replace( [ 'https://', 'http://' ], '', get_permalink() ) ); ?>
								</p>

							</div>
						</article>

					<?php endwhile; ?>
				</div>

				<?php /* Pagination */ ?>
				<nav class="mt-10 border-t border-primary-border pt-8" aria-label="<?php esc_attr_e( 'Search results pages', 'elahub' ); ?>">
					<?php the_posts_pagination( [
						'mid_size'  => 2,
						'prev_text' => '<i class="fa-solid fa-arrow-left" aria-hidden="true"></i> ' . esc_html__( 'Previous', 'elahub' ),
						'next_text' => esc_html__( 'Next', 'elahub' ) . ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>',
						'class'     => 'flex flex-wrap items-center gap-2',
					] ); ?>
				</nav>

			<?php else : ?>

				<?php /* No results */ ?>
				<div class="flex flex-col gap-6 lg:max-w-xl">
					<p class="!mb-0 text-text/70">
						<?php
						printf(
							/* translators: %s: search query */
							esc_html__( 'No results found for &#8220;%s&#8221;. Try a different search term, or browse our services below.', 'elahub' ),
							esc_html( $query )
						);
						?>
					</p>

					<div class="flex flex-wrap gap-3">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>"
							class="inline-flex items-center justify-center rounded-full bg-primary-dark px-6 py-3 font-normal text-white no-underline transition hover:bg-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2">
							<?php esc_html_e( 'Go to homepage', 'elahub' ); ?>
						</a>
						<a href="<?php echo esc_url( home_url( '/contact-elahub/' ) ); ?>"
							class="inline-flex items-center justify-center rounded-full border border-primary-border bg-surface px-6 py-3 font-normal text-text no-underline transition hover:bg-primary-softest focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2">
							<?php esc_html_e( 'Contact us', 'elahub' ); ?>
						</a>
					</div>
				</div>

			<?php endif; ?>

		</div>
	</section>

</main>

<?php get_footer(); ?>
