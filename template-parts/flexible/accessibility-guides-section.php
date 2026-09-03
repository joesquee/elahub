<?php

/**
 * Accessibility guides section
 *
 * @package elahub
 */

$use_defaults = (bool) get_sub_field('use_global_accessibility_guides');

$default_badge_text  = elahub_get_option_field('accessibility_guides_default_badge_text', 'Learning Hub');
$default_badge_icon  = elahub_get_option_field('accessibility_guides_default_badge_icon_class', 'fa-solid fa-book-open');
$default_heading     = elahub_get_option_field('accessibility_guides_default_heading', 'Explore our Accessibility Guides');
$default_body        = elahub_get_option_field('accessibility_guides_default_body', 'Discover practical guidance, updates and resources to help you create more accessible learning content.');
$default_source      = elahub_get_option_field('accessibility_guides_default_source', 'latest_posts');
$default_manual      = elahub_get_option_field('accessibility_guides_default_manual_posts');
$default_button_link = elahub_get_option_field('accessibility_guides_default_button_link');

$badge_text   = $use_defaults ? $default_badge_text : trim((string) get_sub_field('accessibility_guides_badge_text'));
$badge_icon   = $use_defaults ? $default_badge_icon : trim((string) get_sub_field('accessibility_guides_badge_icon_class'));
$heading      = $use_defaults ? $default_heading : trim((string) get_sub_field('accessibility_guides_heading'));
$body         = $use_defaults ? $default_body : trim((string) get_sub_field('accessibility_guides_body'));
$source       = $use_defaults ? $default_source : (get_sub_field('accessibility_guides_source') ?: 'latest_posts');
$manual_posts = $use_defaults ? $default_manual : get_sub_field('accessibility_guides_manual_posts');
$button_link  = $use_defaults ? $default_button_link : get_sub_field('accessibility_guides_button_link');

$post_ids = [];

if ('manual' === $source) {
	$post_ids = array_map('intval', is_array($manual_posts) ? $manual_posts : []);
} elseif ('latest_guides' === $source) {
	$post_ids = get_posts([
		'post_type'              => 'cornerstone_article',
		'post_status'            => 'publish',
		'posts_per_page'         => 3,
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'fields'                 => 'ids',
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	]);
} else {
	$post_ids = get_posts([
		'post_type'              => 'post',
		'post_status'            => 'publish',
		'posts_per_page'         => 3,
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'fields'                 => 'ids',
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	]);
}

$post_ids = array_values(array_filter(array_map('intval', $post_ids)));

if (! $heading && ! $body && empty($post_ids)) {
	return;
}

$button_url   = '';
$button_label = '';

if (is_array($button_link)) {
	$button_url   = ! empty($button_link['url']) ? $button_link['url'] : '';
	$button_label = ! empty($button_link['title']) ? $button_link['title'] : '';
}
?>

<section class="py-8 md:py-12 lg:py-16" aria-labelledby="accessibility-guides-heading">
	<div class="container">
		<div class="flex flex-col gap-8">
			<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
				<div class="flex max-w-7xl flex-col gap-4">
					<div class="flex flex-col items-start gap-4">
						<?php
						if ($badge_text) {
							get_template_part(
								'template-parts/components/eyebrow',
								null,
								[
									'text'       => $badge_text,
									'icon_class' => $badge_icon ?: 'fa-solid fa-book-open',
								]
							);
						}
						?>

						<?php if ($heading) : ?>
							<h2 class="!mb-0" id="accessibility-guides-heading">
								<?php echo esc_html($heading); ?>
							</h2>
						<?php endif; ?>
					</div>

					<?php if ($body) : ?>
						<p class="!mb-0 mt-1 max-w-7xl">
							<?php echo esc_html($body); ?>
						</p>
					<?php endif; ?>
				</div>

				<?php if ($button_url && $button_label) : ?>
					<div class="shrink-0 lg:pt-1">
						<?php
						get_template_part(
							'template-parts/components/button',
							null,
							[
								'url'   => $button_url,
								'label' => $button_label,
							]
						);
						?>
					</div>
				<?php endif; ?>
			</div>

			<?php if (! empty($post_ids)) : ?>
				<div class="grid grid-cols-1 gap-8 lg:grid-cols-3 lg:gap-8">
					<?php foreach ($post_ids as $post_id) : ?>
						<?php
						get_template_part(
							'template-parts/components/accessibility-guide-card',
							null,
							['post_id' => $post_id]
						);
						?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>