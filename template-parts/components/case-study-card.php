<?php

/**
 * Case study card component — horizontal layout
 *
 * Figma ref: node 894:11242 (ValuesCard)
 *   Image:   297×301px → w-[16.5rem] h-[16.722rem]
 *            border-radius: tl=8 tr=8 bl=8 br=33px → rounded-lg rounded-br-[1.833rem]
 *   Gap img↔content: 33px → gap-[1.833rem]
 *   Content padding-right: 66px → pr-[3.667rem]
 *   Title: h3 SemiBold 31px, underlined
 *   Body:  18px regular
 *   Category pill: bg-primary-soft border-primary-border rounded-full
 *
 * @package elahub
 *
 * @param array $args {
 *   @type int $post_id  The case_study post ID.
 * }
 */

$post_id = isset($args['post_id']) ? (int) $args['post_id'] : 0;

if (! $post_id) {
	return;
}

$title     = get_the_title($post_id);
$permalink = get_permalink($post_id);

if (! $title || ! $permalink) {
	return;
}

/* ---- Thumbnail ---- */
$thumbnail_html = get_the_post_thumbnail(
	$post_id,
	'medium',
	[
		'class'    => 'block h-full w-full object-cover',
		'loading'  => 'lazy',
		'decoding' => 'async',
	]
);

/* ---- Category labels (show all categories, not just the primary) ---- */
$category_labels = [];
$terms           = get_the_terms($post_id, 'case_study_category');
if (! empty($terms) && ! is_wp_error($terms)) {
	foreach ($terms as $term) {
		$category_labels[] = $term->name;
	}
}
if (empty($category_labels)) {
	$category_labels[] = 'Case Study';
}

/* ---- Excerpt / description ---- */
$excerpt = trim(get_post_field('post_excerpt', $post_id));

if (! $excerpt) {
	$content = get_post_field('post_content', $post_id);
	$content = strip_shortcodes((string) $content);
	$content = wp_strip_all_tags($content);
	$content = preg_replace('/\s+/', ' ', $content);
	$excerpt = trim($content);
}

$excerpt = wp_trim_words($excerpt, 30, '…');
?>

<div class="elahub-case-study-card flex flex-col gap-[1.833rem] py-[1.833rem] lg:flex-row lg:items-center">

	<?php /* ---- Image ---- */ ?>
	<div class="block shrink-0">
		<div class="h-[16.722rem] w-full overflow-hidden rounded-lg rounded-br-[1.833rem] bg-primary-soft lg:w-[16.5rem]">
			<?php if ($thumbnail_html) : ?>
				<?php echo $thumbnail_html; ?>
			<?php else : ?>
				<div class="flex h-full w-full items-center justify-center">
					<i class="fa-solid fa-building text-[3rem] text-primary/30" aria-hidden="true"></i>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php /* ---- Content ---- */ ?>
	<div class="flex flex-1 flex-col items-start gap-4">

		<h3 class="!mb-0">
			<a
				href="<?php echo esc_url($permalink); ?>"
				class="">
				<?php echo esc_html($title); ?>
			</a>
		</h3>

		<?php if ($excerpt) : ?>
			<p class="!mb-0">
				<?php echo esc_html($excerpt); ?>
			</p>
		<?php endif; ?>

		<div class="flex flex-wrap items-center gap-2">
			<?php foreach ($category_labels as $label) : ?>
				<?php
				get_template_part(
					'template-parts/components/category-pill',
					null,
					['label' => $label]
				);
				?>
			<?php endforeach; ?>
		</div>

	</div>

</div>