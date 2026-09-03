<?php

/**
 * Single blog post — Accessibility Insights
 *
 * Layout (Figma node 1017:65508):
 *   Hero         — back link, h1, meta (author · date · read time), category pill,
 *                  full-width feature image
 *   Two-column   — sticky TOC (+ share links) | body content
 *   Related      — 3 more posts using accessibility-guide-card
 *
 * No summary card, no FAQs — those are guide-only.
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

/* ── Meta ─────────────────────────────────────────────────────── */

$author_name = get_the_author_meta('display_name', get_post_field('post_author', $post_id));
$date        = get_the_date('jS F Y', $post_id);

// Read time: ACF field first, then auto-calculate
$read_time = '';
if (function_exists('get_field')) {
	$read_time = trim((string) (get_field('post_read_time', $post_id) ?: ''));
}
if (! $read_time) {
	$word_count = str_word_count(wp_strip_all_tags(get_the_content()));
	$minutes    = max(1, (int) round($word_count / 200));
	$read_time  = sprintf(_n('%d Minute Read', '%d Minute Read', $minutes, 'elahub'), $minutes);
}

// Category
$cats           = get_the_category($post_id);
$category_label = ! empty($cats) ? $cats[0]->name : '';
$category_url   = ! empty($cats) ? get_category_link($cats[0]->term_id) : '';

// Blog archive URL
$blog_page_id = (int) get_option('page_for_posts');
$archive_url  = $blog_page_id ? get_permalink($blog_page_id) : home_url('/insights/');

// Feature image
$hero_img_src = get_the_post_thumbnail_url($post_id, 'full') ?: '';
$hero_img_id  = get_post_thumbnail_id($post_id);
$hero_img_alt = $hero_img_id ? trim((string) get_post_meta($hero_img_id, '_wp_attachment_image_alt', true)) : '';
if (! $hero_img_alt) {
	$hero_img_alt = get_the_title($post_id);
}

/* ── Body + TOC ───────────────────────────────────────────────── */

$content   = apply_filters('the_content', get_the_content());
$content = elahub_build_toc($content, $toc_items);

/* ── Social share ─────────────────────────────────────────────── */

$youtube_url    = function_exists('get_field') ? trim((string) (get_field('post_youtube_url', $post_id) ?: '')) : '';
$linkedin_share = 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode($permalink);

/* ── Related posts ────────────────────────────────────────────── */

$related_ids = [];

if (! empty($cats)) {
	$related = new WP_Query([
		'post_type'      => 'post',
		'posts_per_page' => 3,
		'post__not_in'   => [$post_id],
		'no_found_rows'  => true,
		'category__in'   => [$cats[0]->term_id],
		'orderby'        => 'date',
		'order'          => 'DESC',
	]);
	if ($related->have_posts()) {
		$related_ids = wp_list_pluck($related->posts, 'ID');
	}
	wp_reset_postdata();
}

if (count($related_ids) < 3) {
	$fallback = new WP_Query([
		'post_type'      => 'post',
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

/* ── Contact banner ───────────────────────────────────────────── */

$banner_heading   = elahub_get_option_field('contact_banner_default_heading', __('Talk it through with us', 'elahub'));
$banner_copy      = elahub_get_option_field('contact_banner_default_copy', '');
$banner_btn_label = elahub_get_option_field('contact_banner_default_button_label', __('Book a Call', 'elahub'));
$banner_btn_url   = elahub_get_option_field('contact_banner_default_button_url', home_url('/contact-elahub/'));
$banner_image     = elahub_get_option_field('contact_banner_default_image');
$banner_img_src   = ! empty($banner_image['url']) ? $banner_image['url'] : get_template_directory_uri() . '/assets/60d190757070e6daff13879907e4c1f24c5743ef.png';
$banner_img_alt   = ! empty($banner_image['alt']) ? $banner_image['alt'] : 'Susi Miller, eLaHub founder';
$banner_svg_src   = get_template_directory_uri() . '/assets/banner.svg';

$dot_svg = get_template_directory_uri() . '/assets/main-circle-dots.svg';
?>

<main id="primary" class="site-main">

	<?php /* ── Hero ── */ ?>
	<div class="relative overflow-visible pb-0 pt-10 md:pt-14 lg:pt-16">

		<div class="pointer-events-none absolute inset-x-0 top-0 z-0 overflow-visible lg:hidden" aria-hidden="true">
			<div style="position:absolute; width:30rem; height:30rem; right:-9rem; top:-9rem; border-radius:9999px; background:rgba(0,85,125,0.22); filter:blur(5.5rem);"></div>
			<div style="position:absolute; width:23rem; height:23rem; left:-7rem; top:10rem; border-radius:9999px; background:rgba(0,85,125,0.12); filter:blur(5rem);"></div>
			<img src="<?php echo esc_url($dot_svg); ?>" alt="" aria-hidden="true" width="985" height="986" loading="lazy" decoding="async"
				style="position:absolute; width:32rem; right:-10rem; top:-34rem; max-width:none; opacity:0.24; transform:scale(1.2);">
		</div>

		<div class="pointer-events-none absolute inset-0 z-0 hidden lg:block" aria-hidden="true">
			<div style="position:absolute; width:41.0556rem; height:41.0556rem; left:-12rem; bottom:-10rem; border-radius:9999px; background:rgba(0,85,125,0.12); filter:blur(9rem);"></div>
			<div style="position:absolute; width:52rem; height:52rem; right:20rem; top:-17rem; border-radius:9999px; background:rgba(0,85,125,0.08); filter:blur(10rem);"></div>
			<img src="<?php echo esc_url($dot_svg); ?>" alt="" aria-hidden="true" width="985" height="986" loading="lazy" decoding="async"
				style="position:absolute; width:52rem; right:-15rem; top:-50rem; max-width:none; opacity:0.24; transform:scale(1.2);">
		</div>

		<div class="container relative z-10 flex flex-col gap-8">

			<div class="flex flex-col items-start gap-4">

				<a
					href="<?php echo esc_url($archive_url); ?>"
					class="inline-flex items-center gap-2 text-text underline underline-offset-[0.18em] transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2">
					<svg class="h-4 w-4 shrink-0" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false">
						<path d="M10 13L5 8L10 3" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
					<?php esc_html_e('Back to Insights', 'elahub'); ?>
				</a>

				<h1><?php the_title(); ?></h1>

				<?php /* Meta row: By Author · Date · Read Time */ ?>
				<p class="!mb-0 flex flex-wrap items-center gap-3 !text-sm text-text/70">
					<?php if ($author_name) : ?>
						<span><?php echo esc_html(sprintf(__('By %s', 'elahub'), $author_name)); ?></span>
						<span class="inline-block h-1.5 w-1.5 rounded-full bg-text/40" aria-hidden="true"></span>
					<?php endif; ?>
					<?php if ($date) : ?>
						<span><?php echo esc_html($date); ?></span>
					<?php endif; ?>
					<?php if ($read_time) : ?>
						<span class="inline-block h-1.5 w-1.5 rounded-full bg-text/40" aria-hidden="true"></span>
						<span><?php echo esc_html($read_time); ?></span>
					<?php endif; ?>
				</p>

				<?php if ($category_label) : ?>
					<div class="self-start">
						<?php
						get_template_part(
							'template-parts/components/category-pill',
							null,
							['label' => $category_label]
						);
						?>
					</div>
				<?php endif; ?>

			</div>

			<?php if ($hero_img_src) : ?>
				<div class="h-72 overflow-hidden rounded-2xl md:h-[24.667rem] lg:h-[27.778rem]">
					<img
						src="<?php echo esc_url($hero_img_src); ?>"
						alt="<?php echo esc_attr($hero_img_alt); ?>"
						class="!h-full w-full object-cover"
						loading="eager"
						decoding="async">
				</div>
			<?php endif; ?>

		</div>

		<hr class="!m-0 mt-8 border-t border-primary-border">

	</div>

	<?php /* ── Two-column ── */ ?>
	<div class="container grid grid-cols-1 items-start gap-8 py-8 md:py-12 lg:grid-cols-4 lg:gap-12">

		<?php /* Sticky TOC + Share */ ?>
		<?php if (! empty($toc_items)) : ?>
			<aside class="hidden lg:col-span-1 lg:block">
				<div class="lg:sticky lg:top-14">
					<div class="rounded-lg rounded-br-[3.667rem] border border-primary-border bg-white p-6 shadow-[0px_4px_40px_0px_rgba(160,160,160,0.25)]">

						<h2 class="!mb-0 !text-lg !font-semibold"><?php esc_html_e('Table Of Contents', 'elahub'); ?></h2>

						<ul class="mt-6 m-0 flex list-none flex-col gap-2 p-0">
							<?php foreach ($toc_items as $item) : ?>
								<li>
									<a
										href="#<?php echo esc_attr($item['id']); ?>"
										class="post-toc-link block text-primary underline underline-offset-[0.18em] break-words transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2"
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
									<i class="fa-brands fa-linkedin-in w-[18px] text-center" aria-hidden="true"></i>
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

		<?php /* Body */ ?>
		<div class="<?php echo ! empty($toc_items) ? 'lg:col-span-3' : 'lg:col-span-4'; ?>">
			<article class="elahub-cs-content elahub-rich-content">
				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput 
				?>
			</article>
		</div>

	</div>

	<?php /* ── Contact banner ── */ ?>
	<?php if ($banner_heading) : ?>
		<section aria-labelledby="post-banner-heading" class="py-4 md:py-6 lg:py-8">
			<div class="container">
				<div class="relative overflow-hidden rounded-2xl rounded-bl-[4rem] rounded-br-[4rem]"
					style="background: linear-gradient(90deg, #014563 0%, #026b9a 69.2%);">
					<div class="elahub-footer-banner__pattern" aria-hidden="true">
						<img src="<?php echo esc_url($banner_svg_src); ?>" alt="" width="789" height="790" loading="lazy" decoding="async">
					</div>
					<div class="relative z-10 flex flex-col gap-4 px-8 py-8 md:px-10 md:py-10 lg:px-12 lg:py-12 lg:pr-80 2xl:pr-[28rem]">
						<h2 id="post-banner-heading" class="!text-white"><?php echo esc_html($banner_heading); ?></h2>
						<?php if ($banner_copy) : ?>
							<p class="!mb-0 text-white"><?php echo nl2br(esc_html($banner_copy)); ?></p>
						<?php endif; ?>
						<?php if ($banner_btn_label && $banner_btn_url) : ?>
							<div>
								<a href="<?php echo esc_url($banner_btn_url); ?>"
									class="inline-flex items-center justify-center rounded-full bg-primary-light px-6 py-2.5 text-text no-underline transition hover:bg-[#a8c4d4] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-primary-dark">
									<?php echo esc_html($banner_btn_label); ?>
								</a>
							</div>
						<?php endif; ?>
					</div>
					<div class="elahub-footer-banner__person">
						<img src="<?php echo esc_url($banner_img_src); ?>" alt="<?php echo esc_attr($banner_img_alt); ?>" width="380" height="380" loading="lazy" decoding="async">
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php /* ── Related posts ── */ ?>
	<?php if (! empty($related_ids)) : ?>
		<section class="py-10 md:py-14" aria-labelledby="post-related-heading">
			<div class="container">
				<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between lg:gap-12">
					<div class="flex flex-col gap-3">
						<h2 id="post-related-heading"><?php esc_html_e('More Accessibility Insights', 'elahub'); ?></h2>
						<p class="!mb-0"><?php esc_html_e('Short articles and reflections on accessibility, learning quality, and industry change.', 'elahub'); ?></p>
					</div>
					<div class="shrink-0">
						<?php
						get_template_part(
							'template-parts/components/button',
							null,
							[
								'url'   => $archive_url,
								'label' => __('View All Insights', 'elahub'),
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

<?php get_footer(); ?>