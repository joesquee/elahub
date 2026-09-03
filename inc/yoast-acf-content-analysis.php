<?php

/**
 * Feed ACF content to Yoast SEO's content analysis.
 *
 * Yoast normally only reads post_content. On this site most of the actual page
 * copy lives in ACF fields (hero, flexible content blocks, etc.), so Yoast's
 * readability + keyphrase analysis on service pages is misleading.
 *
 * This filter walks every ACF field on the current post, extracts the text,
 * and appends it to the content Yoast analyses. It is a belt-and-braces
 * companion to the "ACF Content Analysis for Yoast SEO" plugin — the plugin
 * handles standard ACF field types reliably, this function makes sure deeply
 * nested flexible-content layouts aren't missed either.
 *
 * Why "walk all fields" instead of hand-coding each block?
 *   - Zero maintenance: new layouts/fields are picked up automatically.
 *   - Safe in admin context: we never render template parts (which could
 *     depend on front-end globals); we only read field values.
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Hook into Yoast's pre-analysis filter. Runs only when Yoast is analysing
 * a post (admin edit screen / REST endpoint), so it has zero front-end cost.
 *
 * Filter signature (Yoast SEO): apply_filters( 'wpseo_pre_analysis_post_content', $content, $post )
 */
add_filter('wpseo_pre_analysis_post_content', 'elahub_yoast_append_acf_content', 10, 2);

function elahub_yoast_append_acf_content($content, $post = null)
{
    if (! function_exists('get_fields')) {
        return $content;
    }

    if (! $post instanceof WP_Post) {
        $post = get_post();
    }

    if (! $post instanceof WP_Post) {
        return $content;
    }

    $acf_text = elahub_yoast_collect_acf_text($post->ID);

    if ('' === $acf_text) {
        return $content;
    }

    // Wrap in a hidden marker so Yoast can analyse it but we know where it
    // came from if anyone inspects the analysed string.
    return $content . "\n\n<!-- elahub-acf-content -->\n" . $acf_text;
}

/**
 * Return every readable string in the post's ACF tree, joined with blank
 * lines so Yoast counts headings/paragraphs sensibly.
 */
function elahub_yoast_collect_acf_text(int $post_id): string
{
    $fields = get_fields($post_id);

    if (! is_array($fields) || empty($fields)) {
        return '';
    }

    $chunks = [];
    elahub_yoast_walk_acf_value($fields, $chunks, 0);

    if (empty($chunks)) {
        return '';
    }

    // De-dupe while preserving order — repeater rows often share boilerplate.
    $chunks = array_values(array_unique($chunks));

    return implode("\n\n", $chunks);
}

/**
 * Recursively walk an ACF value, pushing every text-bearing string into $out.
 *
 * @param mixed $value
 * @param array $out
 * @param int   $depth Guard against pathological nesting.
 */
function elahub_yoast_walk_acf_value($value, array &$out, int $depth): void
{
    if ($depth > 20) {
        return;
    }

    if (is_array($value)) {
        foreach ($value as $item) {
            elahub_yoast_walk_acf_value($item, $out, $depth + 1);
        }
        return;
    }

    if ($value instanceof WP_Post) {
        // Linked post — pull its title + excerpt only, don't recurse into
        // its ACF (would explode for relationship-heavy data models).
        $title = trim((string) $value->post_title);
        $exc   = trim((string) $value->post_excerpt);
        if ('' !== $title) {
            $out[] = $title;
        }
        if ('' !== $exc) {
            $out[] = $exc;
        }
        return;
    }

    if (! is_string($value)) {
        return;
    }

    $value = trim($value);
    if ('' === $value) {
        return;
    }

    // Skip values that obviously aren't human-readable copy. These add noise
    // to Yoast's analysis without adding meaning.
    if (preg_match('#^https?://#i', $value)) {
        return;                                 // URL
    }
    if (preg_match('#^/[^\s]*$#', $value)) {
        return;                                 // path
    }
    if (preg_match('/^#?[0-9a-f]{3,8}$/i', $value)) {
        return;                                 // hex colour
    }
    if (preg_match('#^fa-[a-z0-9-]+( fa-[a-z0-9-]+)*$#i', $value)) {
        return;                                 // Font Awesome class
    }
    // Slug / id-looking values with no spaces and reasonable length.
    if (strlen($value) < 60 && ! preg_match('/\s/', $value) && preg_match('#^[a-z0-9_./-]+$#i', $value)) {
        return;
    }

    // Strip HTML (WYSIWYG, textarea-with-html) down to plain text for Yoast.
    $stripped = wp_strip_all_tags($value);
    $stripped = trim(preg_replace('/\s+/', ' ', $stripped));

    if ('' === $stripped) {
        return;
    }

    $out[] = $stripped;
}
