<?php

/**
 * Single Case Study template
 *
 * Layout (Figma node 1011:57477):
 *   Hero          — case-study-hero.php (logo or full-image variant)
 *   Two-column    — sticky TOC sidebar + content column
 *     Content     — executive summary card, body (with injected h2 IDs),
 *                   optional quote section
 *   Related       — three most recent case studies
 *
 * @package elahub
 */

get_header();

if (! have_posts()) {
	get_footer();
	return;
}

the_post();

$post_id = get_the_ID();

/* ── Body content + h2 ID injection ─────────────────────────── */

$content = apply_filters('the_content', get_the_content());
$content = elahub_build_toc($content, $toc_items);

/* ── ACF: executive summary ──────────────────────────────────── */

$cs_client      = trim((string) (get_field('cs_client') ?: ''));
$cs_sector      = trim((string) (get_field('cs_sector') ?: ''));
$cs_tool        = trim((string) (get_field('cs_tool') ?: ''));
$cs_recognition = trim((string) (get_field('cs_recognition') ?: ''));
$cs_extra       = get_field('cs_summary_extra') ?: [];  // repeater: label + value
$cs_summary     = trim((string) (get_field('cs_summary_text') ?: ''));

$summary_meta = array_filter([
	$cs_client      ? ['label' => __('Client', 'elahub'),      'value' => $cs_client]      : null,
	$cs_sector      ? ['label' => __('Sector', 'elahub'),      'value' => $cs_sector]      : null,
	$cs_tool        ? ['label' => __('Tool', 'elahub'),        'value' => $cs_tool]        : null,
	$cs_recognition ? ['label' => __('Recognition', 'elahub'), 'value' => $cs_recognition] : null,
]);

if (is_array($cs_extra)) {
	foreach ($cs_extra as $row) {
		$extra_label = trim((string) ($row['label'] ?? ''));
		$extra_value = trim((string) ($row['value'] ?? ''));
		if ($extra_label && $extra_value) {
			$summary_meta[] = ['label' => $extra_label, 'value' => $extra_value];
		}
	}
}

$has_exec_summary = ! empty($summary_meta) || $cs_summary;

/* ── ACF: quote ─────────────────────────────────────────────── */

$quote_enabled  = function_exists('get_field') ? (bool) get_field('cs_quote_enabled') : false;
$quote_text     = trim((string) (get_field('cs_quote_text') ?: ''));
$quote_name     = trim((string) (get_field('cs_quote_name') ?: ''));
$quote_role     = trim((string) (get_field('cs_quote_role') ?: ''));
$quote_company  = trim((string) (get_field('cs_quote_company') ?: ''));
$quote_initials = trim((string) (get_field('cs_quote_initials') ?: ''));
$quote_stars    = max(1, min(5, (int) (get_field('cs_quote_stars') ?: 5)));

if (! $quote_initials && $quote_name) {
	$parts = preg_split('/\s+/', $quote_name);
	$quote_initials = '';
	foreach ($parts as $part) {
		$quote_initials .= strtoupper(substr($part, 0, 1));
	}
	$quote_initials = substr($quote_initials, 0, 2);
}

$has_quote = $quote_enabled && $quote_text;

/* ── Add quote heading to TOC ────────────────────────────────── */

if ($has_quote) {
	$toc_items[] = [
		'id'    => 'cs-quote',
		'label' => __('What they said', 'elahub'),
	];
}

/* ── Related case studies ────────────────────────────────────── */

$related_ids = [];

// Prefer same category
$terms = get_the_terms($post_id, 'case_study_category');
if (! empty($terms) && ! is_wp_error($terms)) {
	$related_query = new WP_Query([
		'post_type'        => 'case_study',
		'posts_per_page'   => 3,
		'post__not_in'     => [$post_id],
		'tax_query'        => [[
			'taxonomy' => 'case_study_category',
			'field'    => 'term_id',
			'terms'    => wp_list_pluck($terms, 'term_id'),
		]],
		'no_found_rows'    => true,
		'orderby'          => 'date',
		'order'            => 'DESC',
	]);
	if ($related_query->have_posts()) {
		$related_ids = wp_list_pluck($related_query->posts, 'ID');
	}
	wp_reset_postdata();
}

// Backfill
if (count($related_ids) < 3) {
	$fallback = new WP_Query([
		'post_type'      => 'case_study',
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
?>

<main id="primary" class="site-main case-study-template">

	<?php /* ── Hero ── */ ?>
	<?php get_template_part('template-parts/hero/case-study-hero'); ?>

	<?php /* ── Two-column content ── */ ?>
	<div class="container grid grid-cols-1 items-start gap-8 py-8 md:py-12 lg:grid-cols-4 lg:gap-12">

		<?php /* ── Sticky TOC ── */ ?>
		<?php if (! empty($toc_items)) : ?>
			<aside class="relative hidden h-full lg:col-span-1 lg:block">
				<div class="lg:sticky lg:top-14">
					<div class="rounded-lg rounded-br-[3.667rem] border border-primary-border bg-white p-6 shadow-[0px_4px_40px_0px_rgba(160,160,160,0.25)]">
						<h2 class="!mb-0 !text-lg !font-semibold"><?php esc_html_e('Table Of Contents', 'elahub'); ?></h2>

						<ul class="mt-6 m-0 flex list-none flex-col gap-2 p-0">
							<?php foreach ($toc_items as $item) : ?>
								<li>
									<a
										href="#<?php echo esc_attr($item['id']); ?>"
										class="cs-toc-link block text-primary underline underline-offset-[0.18em] break-words transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2"
										data-elahub-toc-link
											data-target="<?php echo esc_attr($item['id']); ?>">
										<?php echo esc_html($item['label']); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</aside>
		<?php endif; ?>

		<?php /* ── Main content column ── */ ?>
		<div class="flex flex-col gap-8 <?php echo ! empty($toc_items) ? 'lg:col-span-3' : 'lg:col-span-4'; ?>">

			<?php /* Executive summary card */ ?>
			<?php if ($has_exec_summary) : ?>
				<section class="elahub-cs-summary rounded-lg rounded-br-[3.667rem] border border-primary-border bg-white p-6 shadow-[0px_4px_40px_0px_rgba(160,160,160,0.12)]" aria-labelledby="cs-executive-summary-heading">
					<h2 id="cs-executive-summary-heading" class="!mb-0 h3"><?php esc_html_e('Executive summary', 'elahub'); ?></h2>

					<div class="elahub-cs-summary__content mt-6 flex flex-col gap-6">

						<?php if (! empty($summary_meta)) : ?>
							<dl class="m-0 flex flex-col gap-2">
								<?php foreach ($summary_meta as $meta) : ?>
									<div class="flex flex-wrap gap-1">
										<dt class="!font-bold"><?php echo esc_html($meta['label']); ?>:</dt>
										<dd class="m-0"><?php echo esc_html($meta['value']); ?></dd>
									</div>
								<?php endforeach; ?>
							</dl>
						<?php endif; ?>

						<?php if ($cs_summary) : ?>
							<div class="elahub-cs-summary__body">
								<div class="elahub-rich-text"><?php echo wp_kses_post($cs_summary); ?></div>
							</div>
						<?php endif; ?>

					</div>
				</section>
			<?php endif; ?>

			<?php /* Body content */ ?>
			<article class="elahub-cs-content elahub-rich-content">
				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput 
				?>
			</article>

			<?php /* Quote section — uses the shared quote-card component (light variant) */ ?>
			<?php if ($has_quote) : ?>
				<div id="cs-quote" class="flex scroll-mt-32 flex-col gap-4">
					<h2 class="!mb-0"><?php esc_html_e('What they said', 'elahub'); ?></h2>
					<?php
					get_template_part(
						'template-parts/components/quote-card',
						null,
						[
							'variant'      => 'light',
							'quote'        => $quote_text,
							'author_name'  => $quote_name,
							'author_role'  => $quote_role,
							'organisation' => $quote_company,
							'stars'        => $quote_stars,
						]
					);
					?>
				</div>
			<?php endif; ?>

		</div>
	</div>

	<?php /* ── Related case studies ── */ ?>
	<?php if (! empty($related_ids)) : ?>
		<section class="border-primary-border py-10 md:py-14" aria-labelledby="cs-related-heading">
			<div class="container">

				<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between lg:gap-12">
					<div class="flex flex-col gap-3">
						<h2 id="cs-related-heading"><?php esc_html_e('Explore more case studies', 'elahub'); ?></h2>
						<p class="!mb-0"><?php esc_html_e('More real-world examples of accessible learning in practice.', 'elahub'); ?></p>
					</div>
					<div class="shrink-0">
						<?php
						get_template_part(
							'template-parts/components/button',
							null,
							[
								'url'   => get_post_type_archive_link('case_study') ?: home_url('/case-studies/'),
								'label' => __('View All Case Studies', 'elahub'),
							]
						);
						?>
					</div>
				</div>

				<div class="mt-4 divide-y divide-primary-border">
					<?php foreach ($related_ids as $rel_id) : ?>
						<?php
						get_template_part(
							'template-parts/components/case-study-card',
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