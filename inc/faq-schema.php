<?php

/**
 * FAQPage structured data — Sally's site query #2.
 *
 * The bespoke FAQ blocks are built from the `faq` CPT rather than a plugin's
 * FAQ block, so nothing was emitting FAQPage markup and Yoast's inspection tool
 * reported none. This collects the FAQs actually shown on the current page and
 * adds a FAQPage node.
 *
 * When Yoast is active the node is injected into its @graph, so its own
 * inspector and Google's Rich Results Test both see it. Without Yoast, a
 * standalone JSON-LD block is printed instead.
 *
 * Worth knowing: Google withdrew FAQ rich results for most sites in 2023, so
 * this is unlikely to change how the pages look in search. The markup is still
 * valid, still parsed, and is what Yoast's tool is asking for.
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Build a FAQ list from a WP_Query-style set of `faq` post IDs.
 *
 * @param int[] $ids FAQ post IDs, in display order.
 * @return array<int, array{question: string, answer: string}>
 */
function elahub_faq_items_from_ids(array $ids): array
{
	$items = [];

	foreach ($ids as $id) {
		$post = get_post($id);

		if (! $post || 'faq' !== $post->post_type || 'publish' !== $post->post_status) {
			continue;
		}

		$question = trim(wp_strip_all_tags(get_the_title($post)));
		$answer   = trim(wp_strip_all_tags(strip_shortcodes($post->post_content)));

		if ('' === $question || '' === $answer) {
			continue;
		}

		$items[] = [
			'question' => $question,
			'answer'   => $answer,
		];
	}

	return $items;
}

/**
 * Resolve the FAQ IDs a `faq_section` row would render.
 *
 * Mirrors the selection logic in template-parts/flexible/faq-section.php.
 *
 * @param string   $source     all | manual | by_category
 * @param string   $cat_slug   faq_category slug when $source is by_category.
 * @param int[]    $manual_ids Explicit IDs when $source is manual.
 * @param int      $limit      0 for no limit.
 * @return int[]
 */
function elahub_faq_ids_for_source(string $source, string $cat_slug, array $manual_ids, int $limit): array
{
	$query_args = [
		'post_type'              => 'faq',
		'post_status'            => 'publish',
		'posts_per_page'         => $limit > 0 ? $limit : -1,
		'orderby'                => 'menu_order title',
		'order'                  => 'ASC',
		'fields'                 => 'ids',
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	];

	if ('manual' === $source && ! empty($manual_ids)) {
		$query_args['post__in'] = $manual_ids;
		$query_args['orderby']  = 'post__in';
	} elseif ('by_category' === $source && $cat_slug) {
		$query_args['tax_query'] = [
			[
				'taxonomy' => 'faq_category',
				'field'    => 'slug',
				'terms'    => $cat_slug,
			],
		];
	}

	return array_map('intval', get_posts($query_args));
}

/**
 * Every FAQ shown on the current singular view, in page order.
 *
 * @return array<int, array{question: string, answer: string}>
 */
function elahub_collect_page_faqs(): array
{
	if (! is_singular() || ! function_exists('get_field')) {
		return [];
	}

	$post_id = get_queried_object_id();

	if (! $post_id) {
		return [];
	}

	$ids = [];

	// Guides attach FAQs through a relationship field.
	if ('cornerstone_article' === get_post_type($post_id)) {
		$guide_faqs = get_field('guide_faqs', $post_id) ?: [];

		foreach ((array) $guide_faqs as $entry) {
			$ids[] = is_object($entry) ? (int) $entry->ID : (int) $entry;
		}
	}

	// Pages built with the page builder can carry one or more faq_section rows.
	if (have_rows('page_builder', $post_id)) {
		while (have_rows('page_builder', $post_id)) {
			the_row();

			if ('faq_section' !== get_row_layout()) {
				continue;
			}

			$use_global = (bool) get_sub_field('use_global_faq');

			if ($use_global) {
				$source     = elahub_get_option_field('faq_default_source', 'all');
				$cat_slug   = (string) elahub_get_option_field('faq_default_category_slug', '');
				$manual_raw = elahub_get_option_field('faq_default_manual_posts', []);
			} else {
				$source     = get_sub_field('faq_source') ?: 'all';
				$cat_slug   = trim((string) (get_sub_field('faq_category_slug') ?: ''));
				$manual_raw = get_sub_field('faq_manual_posts');
			}

			$manual_ids = [];

			foreach ((array) $manual_raw as $entry) {
				if (is_object($entry)) {
					$manual_ids[] = (int) $entry->ID;
				} elseif (is_array($entry) && isset($entry['ID'])) {
					$manual_ids[] = (int) $entry['ID'];
				} elseif ($entry) {
					$manual_ids[] = (int) $entry;
				}
			}

			$ids = array_merge(
				$ids,
				elahub_faq_ids_for_source(
					(string) $source,
					$cat_slug,
					$manual_ids,
					(int) (get_sub_field('faq_limit') ?: 0)
				)
			);
		}
	}

	$ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

	return elahub_faq_items_from_ids($ids);
}

/**
 * Shape the collected FAQs as a schema.org FAQPage node.
 *
 * @param array<int, array{question: string, answer: string}> $items
 * @param string                                              $id
 * @return array<string, mixed>
 */
function elahub_faq_schema_node(array $items, string $id): array
{
	$entities = [];

	foreach ($items as $index => $item) {
		$entities[] = [
			'@type'          => 'Question',
			'@id'            => $id . '-q' . ( $index + 1 ),
			'position'       => $index + 1,
			'name'           => $item['question'],
			'acceptedAnswer' => [
				'@type' => 'Answer',
				'text'  => $item['answer'],
			],
		];
	}

	return [
		'@type'      => 'FAQPage',
		'@id'        => $id,
		'mainEntity' => $entities,
	];
}

/* ── Yoast: add the node to its graph ─────────────────────────────── */

add_filter('wpseo_schema_graph', 'elahub_add_faq_schema_to_yoast_graph', 11, 2);

/**
 * @param array<int, array<string, mixed>> $graph
 * @param mixed                            $context Yoast meta tags context.
 * @return array<int, array<string, mixed>>
 */
function elahub_add_faq_schema_to_yoast_graph($graph, $context)
{
	if (! is_array($graph)) {
		return $graph;
	}

	$items = elahub_collect_page_faqs();

	if (empty($items)) {
		return $graph;
	}

	$permalink = is_object($context) && ! empty($context->canonical)
		? $context->canonical
		: get_permalink(get_queried_object_id());

	$graph[] = elahub_faq_schema_node($items, $permalink . '#faq');

	return $graph;
}

/* ── No Yoast: print a standalone block ───────────────────────────── */

add_action('wp_head', 'elahub_print_standalone_faq_schema', 20);

function elahub_print_standalone_faq_schema(): void
{
	if (defined('WPSEO_VERSION')) {
		return; // Already in Yoast's graph.
	}

	$items = elahub_collect_page_faqs();

	if (empty($items)) {
		return;
	}

	$node = elahub_faq_schema_node($items, get_permalink(get_queried_object_id()) . '#faq');
	$node = array_merge(['@context' => 'https://schema.org'], $node);

	echo "\n" . '<script type="application/ld+json">'
		. wp_json_encode($node, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
		. '</script>' . "\n";
}
