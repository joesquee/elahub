<?php

/**
 * Archive hero component
 *
 * Reusable hero used at the top of CPT archive and taxonomy archive pages.
 * Renders: eyebrow badge → h1 → description → optional taxonomy filter pills → hr.
 * Decorative glows and dot-pattern SVG sit top-right (Figma node 1017:63093).
 *
 * @package elahub
 *
 * @param array $args {
 *   @type string     $eyebrow_text      Eyebrow badge text. Default ''.
 *   @type string     $eyebrow_icon      Font Awesome class. Default 'fa-solid fa-universal-access'.
 *   @type string     $heading           h1 text. Default ''.
 *   @type string     $description       Intro paragraph. Default ''.
 *   @type WP_Term[]  $terms             Taxonomy terms for filter pills. Default [].
 *   @type string     $current_term_slug Active term slug; '' means "All". Default ''.
 *   @type string     $archive_url       URL for the "All" pill. Default ''.
 *   @type string     $filter_label      Accessible label for the filter nav. Default 'Filter by category'.
 *   @type bool       $archive_logos     Render the shared advocacy logo strip under the heading. Default false.
 * }
 */

$args = wp_parse_args(
	$args ?? [],
	[
		'eyebrow_text'      => '',
		'eyebrow_icon'      => 'fa-solid fa-universal-access',
		'heading'           => '',
		'description'       => '',
		'terms'             => [],
		'current_term_slug' => '',
		'archive_url'       => '',
		'filter_label'      => __('Filter by category', 'elahub'),
		'archive_logos'     => false,
	]
);

$has_filters = ! empty($args['terms']) && ! empty($args['archive_url']);
$dot_svg     = get_template_directory_uri() . '/assets/main-circle-dots.svg';

// Active pill: filled background + muted border.
// Inactive pill: border only, normal weight (not bold).
$pill_base    = 'inline-flex items-center justify-center rounded-full border px-4 py-1.5 !text-sm !leading-snug !text-primary-dark transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2';
$pill_active  = 'bg-primary-soft border-surface-alt !font-bold';
$pill_default = 'border-text/25 !font-normal underline underline-offset-2 hover:bg-primary-soft';
?>

<div class="relative overflow-visible pb-0 pt-10 md:pt-14 lg:pt-16">

	<?php /* ── Decorative glows (mobile) ── */ ?>
	<div class="pointer-events-none absolute inset-x-0 top-0 z-0 overflow-visible lg:hidden" aria-hidden="true">
		<div style="position:absolute; width:30rem; height:30rem; right:-9rem; top:-9rem; border-radius:9999px; background:rgba(0,85,125,0.22); filter:blur(5.5rem);"></div>
		<div style="position:absolute; width:23rem; height:23rem; left:-7rem; top:10rem; border-radius:9999px; background:rgba(0,85,125,0.12); filter:blur(5rem);"></div>
		<div style="position:absolute; width:18rem; height:18rem; left:-8rem; bottom:-5rem; border-radius:9999px; background:rgba(0,85,125,0.12); filter:blur(5.25rem);"></div>
		<img
			src="<?php echo esc_url($dot_svg); ?>"
			alt=""
			aria-hidden="true"
			width="985"
			height="986"
			loading="lazy"
			decoding="async"
			style="position:absolute; width:32rem; right:-10rem; top:-34rem; max-width:none; opacity:0.24; transform:scale(1.2);">
	</div>

	<?php /* ── Decorative glows (desktop) ── */ ?>
	<div class="pointer-events-none absolute inset-0 z-0 hidden lg:block" aria-hidden="true">
		<div style="position:absolute; width:41.0556rem; height:41.0556rem; left:-12rem; bottom:-10rem; border-radius:9999px; background:rgba(0,85,125,0.12); filter:blur(9rem);"></div>
		<div style="position:absolute; width:52rem; height:52rem; right:20rem; top:-17rem; border-radius:9999px; background:rgba(0,85,125,0.08); filter:blur(10rem);"></div>
		<img
			src="<?php echo esc_url($dot_svg); ?>"
			alt=""
			aria-hidden="true"
			width="985"
			height="986"
			loading="lazy"
			decoding="async"
			style="position:absolute; width:52rem; right:-15rem; top:-50rem; max-width:none; opacity:0.24; transform:scale(1.2);">
	</div>

	<div class="container relative z-10">

		<?php /* ── Heading block ── */ ?>
		<div class="max-w-4xl flex flex-col gap-4 pb-8">

			<?php if ($args['eyebrow_text']) : ?>
				<div class="self-start">
					<?php
					get_template_part(
						'template-parts/components/eyebrow',
						null,
						[
							'text'       => $args['eyebrow_text'],
							'icon_class' => $args['eyebrow_icon'],
						]
					);
					?>
				</div>
			<?php endif; ?>

			<?php if ($args['heading']) : ?>
				<h1><?php echo esc_html($args['heading']); ?></h1>
			<?php endif; ?>

			<?php if ($args['description']) : ?>
				<p class="!mb-0"><?php echo nl2br(esc_html($args['description'])); ?></p>
			<?php endif; ?>

		</div>

		<?php /* ── Advocacy logos (Learning Hub) — above the pills so the
		         filters stay next to the results they filter ── */ ?>
		<?php if ($args['archive_logos']) : ?>
			<?php get_template_part('template-parts/archive/archive-content', null, ['position' => 'top']); ?>
		<?php endif; ?>

		<?php /* ── Filter pills ── */ ?>
		<?php if ($has_filters) : ?>
			<nav class="pb-8" aria-label="<?php echo esc_attr($args['filter_label']); ?>">
				<ul class="m-0 flex list-none flex-wrap gap-3 p-0">

					<li>
						<a
							href="<?php echo esc_url($args['archive_url']); ?>"
							class="<?php echo esc_attr($pill_base . ' ' . ('' === $args['current_term_slug'] ? $pill_active : $pill_default)); ?>"
							<?php if ('' === $args['current_term_slug']) : ?>aria-current="page" <?php endif; ?>>
							<?php esc_html_e('All', 'elahub'); ?>
						</a>
					</li>

					<?php foreach ($args['terms'] as $term) :
						if (! ($term instanceof WP_Term)) {
							continue;
						}
						$term_url  = get_term_link($term);
						$is_active = $term->slug === $args['current_term_slug'];
					?>
						<li>
							<a
								href="<?php echo esc_url(is_string($term_url) ? $term_url : ''); ?>"
								class="<?php echo esc_attr($pill_base . ' ' . ($is_active ? $pill_active : $pill_default)); ?>"
								<?php if ($is_active) : ?>aria-current="page" <?php endif; ?>>
								<?php echo esc_html($term->name); ?>
							</a>
						</li>
					<?php endforeach; ?>

				</ul>
			</nav>
		<?php endif; ?>

		<?php /* ── Divider ── */ ?>
		<hr class="!m-0 border-t border-primary-border">

	</div>

</div>