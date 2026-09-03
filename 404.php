<?php
/**
 * 404 — Page not found
 *
 * @package elahub
 */

get_header();
?>

<main id="primary" class="site-main">

	<section class="relative py-16 md:py-24 lg:py-32">

		<div class="container relative z-10">
			<div class="flex flex-col gap-8 lg:max-w-2xl">

				<div class="flex flex-col gap-4">
					<h1><?php esc_html_e( 'Page not found', 'elahub' ); ?></h1>
					<p class="!mb-0 text-text/70">
						<?php esc_html_e( "Sorry — the page you're looking for doesn't exist or may have moved. Try searching for what you need, or head back to our homepage.", 'elahub' ); ?>
					</p>
				</div>

				<?php get_search_form(); ?>

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

				<nav aria-label="<?php esc_attr_e( 'Helpful links', 'elahub' ); ?>" class="border-t border-primary-border pt-8">
					<p class="!mb-3 font-bold"><?php esc_html_e( 'You might be looking for:', 'elahub' ); ?></p>
					<ul class="m-0 flex list-none flex-col gap-2 p-0">
						<li><a href="<?php echo esc_url( home_url( '/accessible-elearning-services/elearning-and-digital-accessibility-training/' ) ); ?>" class="underline underline-offset-2 transition hover:text-primary-dark"><?php esc_html_e( 'Training &amp; Programmes', 'elahub' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/accessible-elearning-services/' ) ); ?>" class="underline underline-offset-2 transition hover:text-primary-dark"><?php esc_html_e( 'Accessibility Services', 'elahub' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/designing-accessible-learning-content-programme/' ) ); ?>" class="underline underline-offset-2 transition hover:text-primary-dark"><?php esc_html_e( 'DALC Programme', 'elahub' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/accessible-learning-case-studies/' ) ); ?>" class="underline underline-offset-2 transition hover:text-primary-dark"><?php esc_html_e( 'Case Studies', 'elahub' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>" class="underline underline-offset-2 transition hover:text-primary-dark"><?php esc_html_e( 'About Us', 'elahub' ); ?></a></li>
					</ul>
				</nav>

			</div>
		</div>

	</section>

</main>

<?php get_footer(); ?>
