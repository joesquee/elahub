<?php

/**
 * Footer certifications strip
 *
 * @package elahub
 */

$heading = elahub_get_option_field('footer_certifications_heading', 'Award-winning professional excellence');
$items   = elahub_get_footer_certifications();

// Destination for the heading. An explicit footer_certifications_url in Site
// Settings wins; otherwise it resolves to the Awards page.
$heading_url = trim((string) elahub_get_option_field(
	'footer_certifications_url',
	elahub_get_awards_page_url()
));

if (empty($items)) {
	return;
}
?>

<section class="pb-8 md:pb-10">
	<div class="flex flex-col items-center gap-6">
		<!-- h2 clamp in input.css is too large — !important overrides it.
		     The Figma design had this underlined. The underline is back now the
		     heading links to the Awards page, which is what it was always meant
		     to signal. Without an Awards page it falls back to plain text rather
		     than an underline that goes nowhere. -->
		<h2 id="footer-certifications-heading"
			class="!mb-0 !text-[16px] !font-bold !leading-[1.8] !tracking-[-0.16px] !text-center">
			<?php if ($heading_url) : ?>
				<a href="<?php echo esc_url($heading_url); ?>"
					class="underline underline-offset-2 decoration-text/40 hover:decoration-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2 rounded-sm">
					<?php echo esc_html($heading); ?>
				</a>
			<?php else : ?>
				<?php echo esc_html($heading); ?>
			<?php endif; ?>
		</h2>

		<!-- Figma: flex wrap, gap-[66px] between logos, logos ~113px tall. Smaller gap + icon on mobile. -->
		<div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-4 md:gap-x-12 lg:gap-x-16 lg:gap-y-5" role="list">
			<?php foreach ($items as $item) :
				$image   = $item['image'] ?? null;
				$alt     = trim((string) ($item['alt_text'] ?? ''));
				$url     = trim((string) ($item['url'] ?? ''));
				$img_alt = $alt ?: ($image['alt'] ?? '');
			?>
				<?php if (! empty($image['url'])) : ?>
					<div role="listitem">
						<?php if ($url) : ?>
							<a href="<?php echo esc_url($url); ?>" class="inline-flex items-center justify-center rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr($img_alt); ?>">
							<?php else : ?>
								<div class="inline-flex items-center justify-center">
								<?php endif; ?>
								<img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($img_alt); ?>" class="block h-auto max-h-14 w-auto md:max-h-20 lg:max-h-28" loading="lazy" decoding="async">
								<?php if ($url) : ?>
							</a>
						<?php else : ?>
					</div>
				<?php endif; ?>
		</div>
	<?php endif; ?>
<?php endforeach; ?>
	</div>
	</div>
</section>