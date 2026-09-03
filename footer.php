<?php

/**
 * The template for displaying the footer
 *
 * @package elahub
 */

$contact_heading = elahub_get_option_field('footer_contact_heading', 'Contact');
$contact_url     = elahub_get_option_field('footer_contact_url', home_url('/contact-elahub/'));
$contact_items   = elahub_get_footer_contact_items();
$social_links    = elahub_get_footer_social_links();
$made_by_label   = elahub_get_option_field('footer_made_by_label', 'Made by Squee.');
$made_by_url     = elahub_get_option_field('footer_made_by_url', 'https://squee.design');
$footer_svg_src  = get_template_directory_uri() . '/assets/footer.svg';
?>

<footer id="colophon" class="site-footer relative overflow-hidden bg-surface text-text">

	<div class="elahub-footer-bg-svg" aria-hidden="true">
		<img src="<?php echo esc_url($footer_svg_src); ?>" alt="" width="1434" height="1435" loading="lazy" decoding="async">
	</div>

	<div class="container relative z-10 py-10 md:py-12 lg:py-16">

		<?php get_template_part('template-parts/footer/certifications'); ?>

		<?php get_template_part('template-parts/footer/banner'); ?>

		<!-- Nav columns — Figma: 4 equal cols, gap-[16px], headings 21px bold underline -->
		<div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">

			<nav aria-label="<?php esc_attr_e( 'Training &amp; Programmes', 'elahub' ); ?>">
				<!-- Figma: heading is text-[21px] font-bold tracking-[-0.21px] underline -->
				<!-- h2 base styles in input.css are too large — !important needed to override clamp -->
				<h2 class="!mb-2.5 !text-[21px] !font-bold !leading-[1.5] !tracking-[-0.21px]"><a href="<?php echo esc_url(home_url('/accessible-elearning-services/elearning-and-digital-accessibility-training/')); ?>" class="underline underline-offset-2 transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2">Training &amp; Programmes</a></h2>
				<?php
				wp_nav_menu(array(
					'theme_location' => 'footer-training',
					'container'      => false,
					'menu_class'     => 'm-0 flex list-none flex-col gap-2.5 p-0',
					'fallback_cb'    => false,
					'depth'          => 1,
					'link_before'    => '<span class="underline underline-offset-2 transition hover:text-primary-dark">',
					'link_after'     => '</span>',
				));
				?>
			</nav>

			<nav aria-label="<?php esc_attr_e( 'Accessibility Services', 'elahub' ); ?>">
				<h2 class="!mb-2.5 !text-[21px] !font-bold !leading-[1.5] !tracking-[-0.21px]"><a href="<?php echo esc_url(home_url('/accessible-elearning-services/')); ?>" class="underline underline-offset-2 transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2">Accessibility Services</a></h2>
				<?php
				wp_nav_menu(array(
					'theme_location' => 'footer-services',
					'container'      => false,
					'menu_class'     => 'm-0 flex list-none flex-col gap-2.5 p-0',
					'fallback_cb'    => false,
					'depth'          => 1,
					'link_before'    => '<span class="underline underline-offset-2 transition hover:text-primary-dark">',
					'link_after'     => '</span>',
				));
				?>
			</nav>

			<nav aria-label="<?php esc_attr_e( 'Learning Hub', 'elahub' ); ?>">
				<h2 class="!mb-2.5 !text-[21px] !font-bold !leading-[1.5] !tracking-[-0.21px]"><a href="<?php echo esc_url(home_url('/learning-hub/')); ?>" class="underline underline-offset-2 transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2">Learning Hub</a></h2>
				<?php
				wp_nav_menu(array(
					'theme_location' => 'footer-learning-hub',
					'container'      => false,
					'menu_class'     => 'm-0 flex list-none flex-col gap-2.5 p-0',
					'fallback_cb'    => false,
					'depth'          => 1,
					'link_before'    => '<span class="underline underline-offset-2 transition hover:text-primary-dark">',
					'link_after'     => '</span>',
				));
				?>
			</nav>

			<div>
				<h2 class="!mb-2.5 !text-[21px] !font-bold !leading-[1.5] !tracking-[-0.21px]"><a href="<?php echo esc_url($contact_url); ?>" class="underline underline-offset-2 transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2"><?php echo esc_html($contact_heading); ?></a></h2>

				<?php if (! empty($contact_items)) : ?>
					<ul class="m-0 flex list-none flex-col gap-2.5 p-0">
						<?php foreach ($contact_items as $item) :
							$label = trim((string) ($item['label'] ?? ''));
							$value = trim((string) ($item['value'] ?? ''));
							$url   = trim((string) ($item['url'] ?? ''));
							$icon  = trim((string) ($item['icon_class'] ?? ''));
						?>
							<?php if ($label || $value) : ?>
								<li class="flex items-center gap-2.5">
									<?php if ($icon) : ?>
										<i class="<?php echo esc_attr($icon); ?> shrink-0 text-sm" aria-hidden="true"></i>
									<?php endif; ?>
									<?php if ($url) : ?>
										<a href="<?php echo esc_url($url); ?>" class="underline underline-offset-2 transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2"<?php if ($label && $value) : ?> aria-label="<?php echo esc_attr($label . ': ' . $value); ?>"<?php endif; ?>><?php echo esc_html($value ?: $label); ?></a>
									<?php else : ?>
										<span><?php echo esc_html($value ?: $label); ?></span>
									<?php endif; ?>
								</li>
							<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if (! empty($social_links)) : ?>
					<ul class="<?php echo ! empty($contact_items) ? 'mt-2.5' : ''; ?> m-0 flex list-none flex-col gap-2.5 p-0">
						<?php foreach ($social_links as $item) :
							$label = trim((string) ($item['label'] ?? ''));
							$url   = trim((string) ($item['url'] ?? ''));
							$icon  = trim((string) ($item['icon_class'] ?? ''));
						?>
							<?php if ($label && $url) : ?>
								<li class="flex items-center gap-2.5">
									<?php if ($icon) : ?>
										<i class="<?php echo esc_attr($icon); ?> shrink-0 text-sm" aria-hidden="true"></i>
									<?php endif; ?>
									<a href="<?php echo esc_url($url); ?>" class="underline underline-offset-2 transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2" target="_blank" rel="noopener noreferrer"><?php echo esc_html($label); ?></a>
								</li>
							<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>

		<!-- Bottom bar — Figma: border-t border-[#c1d7e2], py-[32px], gap-[16px], text-[18px] -->
		<div class="mt-8 border-t border-primary-light pt-8 md:mt-10">
			<div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

				<!-- Figma: copyright + legal links all inline at same text size, gap-[16px] between groups, gap-[8px] between legal items -->
				<div class="flex flex-wrap items-center gap-x-4 gap-y-1">
					<!-- !mb-0 overrides the p { margin-bottom: 1em } in input.css -->
					<p class="!mb-0">
						&copy; Copyright <?php echo esc_html(wp_date('Y')); ?>. eLaHub Ltd. All rights reserved.
					</p>

					<?php if (has_nav_menu('footer-legal')) : ?>
						<?php
						wp_nav_menu(array(
							'theme_location' => 'footer-legal',
							'container'      => false,
							'menu_class'     => 'm-0 flex list-none flex-wrap items-center gap-x-2 gap-y-1 p-0',
							'fallback_cb'    => false,
							'depth'          => 1,
							'link_before'    => '<span class="underline underline-offset-2 transition hover:text-primary-dark">',
							'link_after'     => '</span>',
						));
						?>
					<?php else : ?>
						<!-- Figma: separators are plain text nodes between link nodes -->
						<ul class="m-0 flex list-none flex-wrap items-center gap-x-2 gap-y-1 p-0">
							<li aria-hidden="true">-</li>
							<li><a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>" class="underline underline-offset-2 transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2">Privacy Policy</a></li>
							<li aria-hidden="true">-</li>
							<li><a href="<?php echo esc_url(home_url('/terms-of-use/')); ?>" class="underline underline-offset-2 transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2">Terms of Use</a></li>
							<li aria-hidden="true">-</li>
							<li><a href="<?php echo esc_url(home_url('/accessibility-statement-3/')); ?>" class="underline underline-offset-2 transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2">Accessibility Statement</a></li>
						</ul>
					<?php endif; ?>
				</div>

				<!-- Figma: "Made by " normal, "Squee" bold, "." bold larger in primary colour -->
				<a
					href="<?php echo esc_url($made_by_url); ?>"
					class="shrink-0 whitespace-nowrap underline underline-offset-2 transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2 md:text-right"
					target="_blank"
					rel="noopener noreferrer">
					<?php
					if (str_contains($made_by_label, 'Squee')) {
						$parts = explode('Squee', $made_by_label, 2);
						echo esc_html($parts[0]);
						echo '<strong class="font-bold">Squee</strong>';
						echo '<strong class="elahub-made-by-dot font-bold">' . esc_html($parts[1]) . '</strong>';
					} else {
						echo esc_html($made_by_label);
					}
					?>
				</a>
			</div>
		</div>
	</div>
</footer>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>

</html>