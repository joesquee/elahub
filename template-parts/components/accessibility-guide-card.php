<?php

/**
 * Accessibility guide card
 *
 * Works for cornerstone_article, post, and learning_hub_item post types.
 * If the post has an `lh_external_url` ACF field set, that URL is used
 * instead of the WordPress permalink, and the link opens in a new tab.
 *
 * @package elahub
 *
 * @param array $args {
 *   @type int $post_id  The post ID to render.
 * }
 */

$post_id = isset($args['post_id']) ? (int) $args['post_id'] : 0;

if (! $post_id) {
	return;
}

$post_type = get_post_type($post_id);
$title     = get_the_title($post_id);
$permalink = get_permalink($post_id);

if (! $title || ! $permalink) {
	return;
}

/* ── External URL override (learning_hub_item) ── */

$external_url   = '';
$open_new_tab   = false;

if (function_exists('get_field') && 'learning_hub_item' === $post_type) {
	$external_url = trim((string) (get_field('lh_external_url', $post_id) ?: ''));
	$open_new_tab = ! empty($external_url) || (bool) get_field('lh_open_new_tab', $post_id);
}

$card_url    = $external_url ?: $permalink;
$link_target = $open_new_tab ? '_blank' : '_self';
$link_rel    = $open_new_tab ? 'noopener noreferrer' : '';

/* ── Thumbnail ── */

$image_html = get_the_post_thumbnail(
	$post_id,
	'large',
	[
		'class'    => 'block !h-full !w-full object-cover',
		'loading'  => 'lazy',
		'decoding' => 'async',
	]
);

/* ── Category label ── */

$category_label = '';

if ('cornerstone_article' === $post_type) {
	$terms = get_the_terms($post_id, 'guide_category');
	if (! empty($terms) && ! is_wp_error($terms)) {
		$category_label = $terms[0]->name;
	}
} elseif ('learning_hub_item' === $post_type) {
	// Show the learning_hub_category term if set; fall back to type
	$terms = get_the_terms($post_id, 'learning_hub_category');
	if (! empty($terms) && ! is_wp_error($terms)) {
		$category_label = $terms[0]->name;
	} else {
		$type_terms = get_the_terms($post_id, 'learning_hub_type');
		if (! empty($type_terms) && ! is_wp_error($type_terms)) {
			$category_label = $type_terms[0]->name;
		}
	}
} elseif ('post' === $post_type) {
	$terms = get_the_terms($post_id, 'category');
	if (! empty($terms) && ! is_wp_error($terms)) {
		$category_label = $terms[0]->name;
	}
}

if (! $category_label) {
	$post_type_object = get_post_type_object($post_type);
	$category_label   = $post_type_object && ! empty($post_type_object->labels->singular_name)
		? $post_type_object->labels->singular_name
		: 'Article';
}

/* ── Excerpt ── */

$excerpt = get_post_field('post_excerpt', $post_id);

if (! $excerpt) {
	$content = get_post_field('post_content', $post_id);
	$content = strip_shortcodes((string) $content);
	$content = wp_strip_all_tags($content);
	$content = preg_replace('#https?://\S+#', '', $content);
	$content = preg_replace('/\[[^\]]*\]/', '', (string) $content);
	$content = preg_replace('/\s+/', ' ', (string) $content);
	$excerpt = trim((string) $content);
}

$excerpt = trim(wp_strip_all_tags((string) $excerpt));
$excerpt = preg_replace('/\[\.\.\.\]|\[\…\]/', '', (string) $excerpt);
$excerpt = preg_replace('/\s+/', ' ', (string) $excerpt);
$excerpt = trim((string) $excerpt);

if ($excerpt) {
	$excerpt = wp_trim_words($excerpt, 19, '…');
}

if (! $excerpt) {
	$excerpt = __('Practical guidance to help you build more accessible learning content.', 'elahub');
}
?>

<div class="flex h-full flex-col gap-8">

	<div class="block overflow-hidden rounded-2xl rounded-br-[4rem]">

		<?php if ($image_html) : ?>
			<div class="h-72 overflow-hidden rounded-2xl rounded-br-[4rem] bg-surface-alt md:h-80 xl:h-80">
				<?php echo $image_html; ?>
			</div>
		<?php else : ?>
			<div class="flex h-72 items-center justify-center rounded-2xl bg-primary-soft md:h-80 xl:h-80">
				<div class="flex h-24 w-24 items-center justify-center rounded-2xl bg-white shadow-[0_20px_60px_rgba(16,24,40,0.14)]">
					<i class="fa-solid fa-book-open text-[21px] text-primary-dark" aria-hidden="true"></i>
				</div>
				<span class="sr-only"><?php echo esc_html($title); ?></span>
			</div>
		<?php endif; ?>

	</div>

	<div class="flex flex-col items-start gap-4">

		<?php
		get_template_part(
			'template-parts/components/category-pill',
			null,
			['label' => $category_label]
		);
		?>

		<h3 class="h4 !mb-0">
			<a
				href="<?php echo esc_url($card_url); ?>"
				class="underline underline-offset-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2"
				<?php if ($open_new_tab) : ?>target="<?php echo esc_attr($link_target); ?>" rel="<?php echo esc_attr($link_rel); ?>" <?php endif; ?>>
				<?php echo esc_html($title); ?>
			</a>
		</h3>

		<p class="!mb-0">
			<?php echo esc_html($excerpt); ?>
		</p>

	</div>

</div>