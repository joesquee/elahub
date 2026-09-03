<?php

/**
 * Single Cornerstone Article (Guide) template
 *
 * Layout (Figma node 1017:66317):
 *   Hero         — guide-hero.php (full-width image, back link, meta, pill)
 *   Two-column   — sticky TOC sidebar (+ share links) | content column
 *     Content    — guide summary card, body (h2 IDs injected for TOC),
 *                  FAQ section (queried from FAQ CPT by category slug)
 *   Banner       — contact banner (global defaults)
 *   Related      — 3 more guides using accessibility-guide-card
 *
 * @package elahub
 */

get_header();

if (! have_posts()) {
	get_footer();
	return;
}

the_post();

$post_id   = get_the_ID();
$permalink = get_permalink($post_id);

/* ── Body content + h2 ID injection ────────────────────────── */

$content   = apply_filters('the_content', get_the_content());
$content = elahub_build_toc($content, $toc_items);

/* ── ACF: guide summary ─────────────────────────────────────── */

$guide_summary = trim((string) (get_field('guide_summary_text') ?: ''));

/* ── ACF: FAQ section ───────────────────────────────────────── */

$faq_ids   = get_field('guide_faqs') ?: []; // relationship field returns array of IDs
$faq_ids   = array_filter(array_map('intval', (array) $faq_ids));

$faq_query = null;
$has_faqs  = false;

if (! empty($faq_ids)) {
	$faq_query = new WP_Query([
		'post_type'              => 'faq',
		'post_status'            => 'publish',
		'posts_per_page'         => count($faq_ids),
		'post__in'               => $faq_ids,
		'orderby'                => 'post__in',  // preserve selection order
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	]);
	$has_faqs = $faq_query->have_posts();
}

// Add FAQ to TOC if present
if ($has_faqs) {
	$toc_items[] = [
		'id'    => 'guide-faq',
		'label' => __('Frequently Asked Questions', 'elahub'),
	];
}

/* ── ACF: social share ──────────────────────────────────────── */

$youtube_url       = trim((string) (get_field('guide_youtube_url') ?: ''));
$linkedin_share    = 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode($permalink);

/* ── Related guides ─────────────────────────────────────────── */

$related_ids = [];

$terms = get_the_terms($post_id, 'guide_category');
if (! empty($terms) && ! is_wp_error($terms)) {
	$related_query = new WP_Query([
		'post_type'      => 'cornerstone_article',
		'posts_per_page' => 3,
		'post__not_in'   => [$post_id],
		'no_found_rows'  => true,
		'tax_query'      => [[
			'taxonomy' => 'guide_category',
			'field'    => 'term_id',
			'terms'    => wp_list_pluck($terms, 'term_id'),
		]],
		'orderby'        => 'date',
		'order'          => 'DESC',
	]);
	if ($related_query->have_posts()) {
		$related_ids = wp_list_pluck($related_query->posts, 'ID');
	}
	wp_reset_postdata();
}

// Backfill with latest guides
if (count($related_ids) < 3) {
	$fallback = new WP_Query([
		'post_type'      => 'cornerstone_article',
		'posts_per_page' => 3 - count($related_ids),
		'post__not_in'   => array_merge([$post_id], $related_ids),
		'no_found_rows'  => true,
		'orderby'        => 'date',
		'order'          => 'DESC',
	]);
	if ($fallback->have_posts()) {
		$related_ids = array_merge($related_ids, wp_list_pluck($fallback->posts, 'ID'));
	}
	wp_reset_postdata();
}

/* ── Contact banner defaults ────────────────────────────────── */

$banner_heading   = elahub_get_option_field('contact_banner_default_heading', __('Talk it through with us', 'elahub'));
$banner_copy      = elahub_get_option_field('contact_banner_default_copy', '');
$banner_btn_label = elahub_get_option_field('contact_banner_default_button_label', __('Book a Call', 'elahub'));
$banner_btn_url   = elahub_get_option_field('contact_banner_default_button_url', home_url('/contact-elahub/'));
$banner_image     = elahub_get_option_field('contact_banner_default_image');
$banner_img_src   = ! empty($banner_image['url']) ? $banner_image['url'] : get_template_directory_uri() . '/assets/60d190757070e6daff13879907e4c1f24c5743ef.png';
$banner_img_alt   = ! empty($banner_image['alt']) ? $banner_image['alt'] : 'Susi Miller, eLaHub founder';
$banner_svg_src   = get_template_directory_uri() . '/assets/banner.svg';

$section_uid = 'guide-faq-' . $post_id;
?>

<main id="primary" class="site-main">

	<?php /* ── Hero ── */ ?>
	<?php get_template_part('template-parts/hero/guide-hero'); ?>

	<?php /* ── Two-column content ── */ ?>
	<div class="container grid grid-cols-1 items-start gap-8 py-8 md:py-12 lg:grid-cols-4 lg:gap-12">

		<?php /* ── Sticky sidebar: TOC + Share ── */ ?>
		<?php if (! empty($toc_items)) : ?>
			<aside class="hidden lg:col-span-1 h-full lg:block">
				<div class="lg:sticky lg:top-14">
					<div class="rounded-lg rounded-br-[3.667rem] border border-primary-border bg-white p-6 shadow-[0px_4px_40px_0px_rgba(160,160,160,0.25)]">

						<h2 class="!mb-0 !text-lg !font-semibold"><?php esc_html_e('Table Of Contents', 'elahub'); ?></h2>

						<ul class="mt-6 m-0 flex list-none flex-col gap-2 p-0">
							<?php foreach ($toc_items as $item) : ?>
								<li>
									<a
										href="#<?php echo esc_attr($item['id']); ?>"
										class="guide-toc-link block text-primary underline underline-offset-[0.18em] break-words transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2"
										data-elahub-toc-link
											data-target="<?php echo esc_attr($item['id']); ?>">
										<?php echo esc_html($item['label']); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>

						<hr class="!my-6 border-t border-primary-border">

						<p class="!mb-3 !font-bold !text-lg"><?php esc_html_e('Share On', 'elahub'); ?></p>

						<ul class="m-0 flex list-none flex-col gap-3 p-0">
							<li>
								<a
									href="<?php echo esc_url($linkedin_share); ?>"
									target="_blank"
									rel="noopener noreferrer"
									class="inline-flex items-center gap-2 text-primary underline underline-offset-[0.18em] transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2">
									<i class="fa-brands fa-linkedin" aria-hidden="true"></i>
									<?php esc_html_e('LinkedIn', 'elahub'); ?>
								</a>
							</li>
							<?php if ($youtube_url) : ?>
								<li>
									<a
										href="<?php echo esc_url($youtube_url); ?>"
										target="_blank"
										rel="noopener noreferrer"
										class="inline-flex items-center gap-2 text-primary underline underline-offset-[0.18em] transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2">
										<i class="fa-brands fa-youtube w-[18px] text-center" aria-hidden="true"></i>
										<?php esc_html_e('YouTube', 'elahub'); ?>
									</a>
								</li>
							<?php endif; ?>
						</ul>

					</div>
				</div>
			</aside>
		<?php endif; ?>

		<?php /* ── Main content column ── */ ?>
		<div class="flex flex-col gap-8 <?php echo ! empty($toc_items) ? 'lg:col-span-3' : 'lg:col-span-4'; ?>">

			<?php /* Guide summary card */ ?>
			<?php if ($guide_summary) : ?>
				<div class="rounded-lg rounded-br-[3.667rem] border border-primary-border p-6">
					<h2 class="!mb-0 h3"><?php esc_html_e('Guide Summary', 'elahub'); ?></h2>
					<div class="mt-6 flex flex-col gap-4">
						<?php
						$paras = array_filter(array_map('trim', explode("\n\n", $guide_summary)));
						foreach ($paras as $para) :
						?>
							<p class="!mb-0"><?php echo nl2br(esc_html($para)); ?></p>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php /* Body */ ?>
			<article class="elahub-cs-content elahub-rich-content">
				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput 
				?>
			</article>

			<?php /* FAQ accordion */ ?>
			<?php if ($has_faqs) : ?>
				<div id="guide-faq" class="flex scroll-mt-32 flex-col gap-6">
					<h2 class="!mb-0"><?php esc_html_e('Frequently Asked Questions', 'elahub'); ?></h2>

					<div
						class="flex flex-col gap-4"
						role="list"
						data-elahub-faq="<?php echo esc_attr($section_uid); ?>">

						<?php
						$faq_index = 0;
						while ($faq_query->have_posts()) :
							$faq_query->the_post();
							$item_id  = $section_uid . '-item-' . $faq_index;
							$panel_id = $item_id . '-panel';
							$is_open  = 0 === $faq_index;
						?>
							<div
								class="rounded-lg border border-primary-border bg-primary-softest focus-within:outline-none focus-within:ring-2 focus-within:ring-primary-dark focus-within:ring-offset-2"
								role="listitem">

								<button
									type="button"
									class="elahub-faq__trigger flex w-full cursor-pointer items-center gap-8 px-[1.625rem] py-4 text-left focus:outline-none"
									aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
									aria-controls="<?php echo esc_attr($panel_id); ?>">
									<span class="elahub-faq__question flex-1 !text-base !font-bold !leading-[1.8] text-text transition-all <?php echo $is_open ? 'underline underline-offset-2' : ''; ?>">
										<?php the_title(); ?>
									</span>
									<span
										class="elahub-faq__icon flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-light transition-transform duration-300 <?php echo $is_open ? 'rotate-180' : ''; ?>"
										aria-hidden="true">
										<i class="fa-solid fa-chevron-down !text-xs text-primary-dark"></i>
									</span>
								</button>

								<div
									id="<?php echo esc_attr($panel_id); ?>"
									class="elahub-faq__panel overflow-hidden transition-[max-height] duration-500 ease-in-out"
									style="max-height: <?php echo $is_open ? '2000px' : '0px'; ?>;"
									aria-hidden="<?php echo $is_open ? 'false' : 'true'; ?>">
									<div class="elahub-rich-text px-[1.625rem] pb-5 pt-1 !text-[16px] !leading-[1.8] text-text/80">
										<?php echo wp_kses_post(get_the_content()); ?>
									</div>
								</div>

							</div>
						<?php
							$faq_index++;
						endwhile;
						wp_reset_postdata();
						?>

					</div>
				</div>
			<?php endif; ?>

		</div>
	</div>

	<?php /* ── Contact banner ── */ ?>
	<?php if ($banner_heading) : ?>
		<section aria-labelledby="guide-banner-heading" class="py-4 md:py-6 lg:py-8">
			<div class="container">
				<div class="relative overflow-hidden rounded-2xl rounded-bl-[4rem] rounded-br-[4rem]"
					style="background: linear-gradient(90deg, #014563 0%, #026b9a 69.2%);">

					<div class="elahub-footer-banner__pattern" aria-hidden="true">
						<img src="<?php echo esc_url($banner_svg_src); ?>" alt="" width="789" height="790" loading="lazy" decoding="async">
					</div>

					<div class="relative z-10 flex flex-col gap-4 px-8 py-8 md:px-10 md:py-10 lg:px-12 lg:py-12 lg:pr-80 2xl:pr-[28rem]">
						<h2 id="guide-banner-heading" class="!text-white"><?php echo esc_html($banner_heading); ?></h2>

						<?php if ($banner_copy) : ?>
							<p class="!mb-0 text-white"><?php echo nl2br(esc_html($banner_copy)); ?></p>
						<?php endif; ?>

						<?php if ($banner_btn_label && $banner_btn_url) : ?>
							<div>
								<a
									href="<?php echo esc_url($banner_btn_url); ?>"
									class="inline-flex items-center justify-center rounded-full bg-primary-light px-6 py-2.5 text-text no-underline transition hover:bg-[#a8c4d4] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-primary-dark">
									<?php echo esc_html($banner_btn_label); ?>
								</a>
							</div>
						<?php endif; ?>
					</div>

					<div class="elahub-footer-banner__person">
						<img
							src="<?php echo esc_url($banner_img_src); ?>"
							alt="<?php echo esc_attr($banner_img_alt); ?>"
							width="380" height="380" loading="lazy" decoding="async">
					</div>

				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php /* ── Related guides ── */ ?>
	<?php if (! empty($related_ids)) : ?>
		<section class="py-10 md:py-14" aria-labelledby="guide-related-heading">
			<div class="container">

				<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between lg:gap-12">
					<div class="flex flex-col gap-3">
						<h2 id="guide-related-heading"><?php esc_html_e('More Accessibility Guides', 'elahub'); ?></h2>
						<p class="!mb-0"><?php esc_html_e('In-depth, practical guides covering the core principles of accessible and inclusive learning design.', 'elahub'); ?></p>
					</div>
					<div class="shrink-0">
						<?php
						get_template_part(
							'template-parts/components/button',
							null,
							[
								'url'   => get_post_type_archive_link('cornerstone_article') ?: home_url('/guides/'),
								'label' => __('View All Guides', 'elahub'),
							]
						);
						?>
					</div>
				</div>

				<div class="mt-10 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
					<?php foreach ($related_ids as $rel_id) : ?>
						<?php
						get_template_part(
							'template-parts/components/accessibility-guide-card',
							null,
							['post_id' => $rel_id]
						);
						?>
					<?php endforeach; ?>
				</div>

			</div>
		</section>
	<?php endif; ?>

</main>

<?php /* ── FAQ accordion JS (same pattern as faq-section.php) ── */ ?>
<?php if ($has_faqs) : ?>
	<script>
		(function() {
			var container = document.querySelector('[data-elahub-faq="<?php echo esc_js($section_uid); ?>"]');
			if (!container) return;
			var triggers = Array.from(container.querySelectorAll('.elahub-faq__trigger'));
			triggers.forEach(function(trigger) {
				trigger.addEventListener('click', function() {
					var isOpen = trigger.getAttribute('aria-expanded') === 'true';
					var panel = document.getElementById(trigger.getAttribute('aria-controls'));
					var icon = trigger.querySelector('.elahub-faq__icon');
					var q = trigger.querySelector('.elahub-faq__question');
					trigger.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
					if (panel) {
						panel.style.maxHeight = isOpen ? '0px' : panel.scrollHeight + 'px';
						panel.setAttribute('aria-hidden', isOpen ? 'true' : 'false');
					}
					if (icon) {
						icon.classList.toggle('rotate-180', !isOpen);
					}
					if (q) {
						q.classList.toggle('underline', !isOpen);
						q.classList.toggle('underline-offset-2', !isOpen);
					}
				});
				trigger.addEventListener('keydown', function(e) {
					var idx = triggers.indexOf(trigger);
					if (e.key === 'ArrowDown') {
						e.preventDefault();
						(triggers[idx + 1] || triggers[0]).focus();
					}
					if (e.key === 'ArrowUp') {
						e.preventDefault();
						(triggers[idx - 1] || triggers[triggers.length - 1]).focus();
					}
					if (e.key === 'Home') {
						e.preventDefault();
						triggers[0].focus();
					}
					if (e.key === 'End') {
						e.preventDefault();
						triggers[triggers.length - 1].focus();
					}
				});
			});
		}());
	</script>
<?php endif; ?>

<?php get_footer(); ?>