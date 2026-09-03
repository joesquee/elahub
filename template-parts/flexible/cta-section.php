<?php

/**
 * CTA Section — dark blue flexible content wrapper
 *
 * Inner layouts via cta_items flexible content:
 *   cta_main   — image + content with link list
 *   cta_quotes — quotes grid with heading + optional button
 *
 * Blue card uses the same mx-6 inset as the hero with matching
 * asymmetric border-radius (tl=16 tr=16 bl=16 br=112px).
 *
 * @package elahub
 */

$dot_svg = get_template_directory_uri() . '/assets/dotsondarkblue.svg';
?>

<section class="relative py-4 lg:py-5">
	<div class="container">
		<div class="relative overflow-hidden rounded-2xl rounded-br-[6.222rem] bg-primary-dark lg:!-mx-8">

			<?php /*
			 * Dot pattern — full-width horizontal band.
			 * Figma: w=2848px on 1680px card = 170% wide, y=748/2065 = 36% from top.
			 * 130% with left:-15% centres it across the card.
			 */ ?>
			<div class="pointer-events-none absolute inset-x-0" style="top:36%; width:130%; left:-15%;" aria-hidden="true">
				<img
					src="<?php echo esc_url($dot_svg); ?>"
					alt=""
					width="2848"
					height="542"
					loading="lazy"
					decoding="async"
					class="block h-auto w-full">
			</div>

			<div class="relative z-10">
				<?php if (have_rows('cta_items')) : ?>
					<?php while (have_rows('cta_items')) : the_row(); ?>
						<?php
						switch (get_row_layout()) {
							case 'cta_main':
								get_template_part('template-parts/cta/cta-main');
								break;
							case 'cta_quotes':
								get_template_part('template-parts/cta/cta-quotes');
								break;
							case 'cta_logo_strip':
								get_template_part('template-parts/cta/cta-logo-strip');
								break;
							case 'cta_icon_features':
								get_template_part('template-parts/cta/cta-icon-features');
								break;
							case 'cta_people_cards':
								get_template_part('template-parts/cta/cta-people-cards');
								break;
						}
						?>
					<?php endwhile; ?>
				<?php endif; ?>
			</div>

		</div>
	</div>
</section>