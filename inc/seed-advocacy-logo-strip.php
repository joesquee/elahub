<?php

/**
 * Speaking & Advocacy — gray logo strip seeder
 *
 * Reads logos from the theme folder:
 *     wp-content/themes/elahub/assets/seeds/advocacy-logos-gray/
 *
 * Imports each one into the media library under wp-content/uploads/advocacy-logos-gray/,
 * then adds (or refreshes) a Logo Strip section on the Speaking & Advocacy page
 * with use_default_logo_strip = false so it shows this gray set instead of the
 * global default.
 *
 * Trigger:
 *     /speaking-and-advocacy/?elahub_seed_advocacy_logo_strip=1
 *
 * Safe to re-run. Existing attachments are re-used by filename; the section is
 * not duplicated if it already exists — its logos array is replaced.
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
    exit;
}

add_action('template_redirect', 'elahub_seed_advocacy_logo_strip_from_url');

function elahub_seed_advocacy_logo_strip_from_url()
{
    if (! isset($_GET['elahub_seed_advocacy_logo_strip']) || '1' !== (string) $_GET['elahub_seed_advocacy_logo_strip']) {
        return;
    }

    if (! current_user_can('manage_options')) {
        wp_die('You do not have permission to run this seed.');
    }

    $result = elahub_do_seed_advocacy_logo_strip();

    $status  = ! empty($result['success']) ? 'Seed complete' : 'Seed failed';
    $message = $result['message'] ?? 'No message returned.';
    $notes   = $result['notes'] ?? [];

    ob_start();
    ?>
    <style>body{font-family:sans-serif;max-width:820px;margin:2rem auto;padding:1rem}li{margin:.25rem 0}</style>
    <h1><?php echo esc_html($status); ?></h1>
    <p><?php echo esc_html($message); ?></p>
    <?php if ($notes) : ?>
        <h2>Notes</h2>
        <ul>
            <?php foreach ($notes as $note) : ?>
                <li><?php echo esc_html($note); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <p><a href="<?php echo esc_url(remove_query_arg('elahub_seed_advocacy_logo_strip')); ?>">View page</a></p>
    <?php
    wp_die(ob_get_clean(), $status, ['response' => 200]);
}

function elahub_do_seed_advocacy_logo_strip()
{
    @set_time_limit(0);
    $notes = [];

    if (! function_exists('update_field')) {
        return ['success' => false, 'message' => 'ACF is required.', 'notes' => []];
    }

    /* ── 1. Find the Speaking & Advocacy page ── */
    $page = get_page_by_path('speaking-and-advocacy');
    if (! $page) {
        return [
            'success' => false,
            'message' => 'Speaking & Advocacy page not found at slug "speaking-and-advocacy".',
            'notes'   => $notes,
        ];
    }
    $page_id = $page->ID;
    $notes[] = "Found Speaking & Advocacy page (ID {$page_id}).";

    /* ── 2. Locate source folder + collect image files ── */
    $source_dir = trailingslashit(get_template_directory()) . 'assets/seeds/advocacy-logos-gray/';

    if (! is_dir($source_dir)) {
        return [
            'success' => false,
            'message' => 'Source folder missing: ' . $source_dir,
            'notes'   => $notes,
        ];
    }

    $files = [];
    foreach (glob($source_dir . '*') as $path) {
        if (! is_file($path)) {
            continue;
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'svg', 'webp'], true)) {
            continue;
        }
        $files[] = $path;
    }

    sort($files, SORT_NATURAL | SORT_FLAG_CASE);

    if (empty($files)) {
        return [
            'success' => false,
            'message' => 'No images found in ' . $source_dir,
            'notes'   => $notes,
        ];
    }

    $notes[] = 'Found ' . count($files) . ' source images.';

    /* ── 3. Prepare uploads target ── */
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $upload_dir    = wp_upload_dir();
    $target_subdir = 'advocacy-logos-gray';
    $target_dir    = trailingslashit($upload_dir['basedir']) . $target_subdir;

    if (! is_dir($target_dir)) {
        wp_mkdir_p($target_dir);
        $notes[] = "Created uploads sub-folder: {$target_subdir}/";
    }

    /* ── 4. Import each image, build the logos array ── */
    $logos    = [];
    $imported = 0;
    $reused   = 0;
    $failed   = 0;

    foreach ($files as $source_path) {
        $basename  = basename($source_path);
        $dest_path = $target_dir . '/' . $basename;

        $existing = elahub_find_attachment_by_filename($basename, $target_dir);

        if ($existing) {
            $attach_id = $existing;
            $reused++;
        } else {
            if (! copy($source_path, $dest_path)) {
                $notes[] = "Could not copy {$basename} — skipped.";
                $failed++;
                continue;
            }

            $filetype = wp_check_filetype($basename);
            $mime     = $filetype['type'] ?: 'image/jpeg';

            $attach_id = wp_insert_attachment([
                'post_mime_type' => $mime,
                'post_title'     => sanitize_file_name(pathinfo($basename, PATHINFO_FILENAME)),
                'post_content'   => '',
                'post_status'    => 'inherit',
            ], $dest_path, $page_id);

            if (is_wp_error($attach_id)) {
                $notes[] = "wp_insert_attachment failed for {$basename}: " . $attach_id->get_error_message();
                $failed++;
                continue;
            }

            $meta = wp_generate_attachment_metadata($attach_id, $dest_path);
            wp_update_attachment_metadata($attach_id, $meta);

            /*
             * Alt text intentionally empty — partner name is conveyed by the link
             * aria-label (or visible label, if you ever add one). Setting alt
             * here would duplicate the announcement for screen readers.
             */
            update_post_meta($attach_id, '_wp_attachment_image_alt', '');

            $imported++;
        }

        $logos[] = [
            'logo'     => $attach_id,
            'logo_alt' => elahub_advocacy_logo_name_from_filename($basename),
            'logo_url' => '',
        ];
    }

    $notes[] = "Logos: {$imported} imported, {$reused} re-used, {$failed} failed.";

    if (empty($logos)) {
        return [
            'success' => false,
            'message' => 'No logos were imported.',
            'notes'   => $notes,
        ];
    }

    /* ── 5a. Populate the global Advocacy Logos Defaults ──
     * The logos live as a Global Content default so Susi can manage them
     * in one place and any page-builder Logo Strip section can opt in to
     * "use default" + "Advocacy" set. */
    update_field('advocacy_logos_default_heading', 'In partnership with', 'option');
    update_field('advocacy_logos_default_logos', $logos, 'option');
    $notes[] = 'Advocacy Logos Defaults updated globally (Global Content > Advocacy Logos Defaults).';

    /* ── 5b. Insert or refresh the logo-strip section on the page ──
     * Use the new "default set" mechanism so editing the global Advocacy
     * defaults updates every page that opts in, including this one. */
    $blocks = get_field('page_builder', $page_id);
    if (! is_array($blocks)) {
        $blocks = [];
    }

    $section_data = [
        'acf_fc_layout'          => 'logo_strip',
        'use_default_logo_strip' => true,
        'logo_strip_default_set' => 'advocacy',
        'logo_size'              => 'medium',
        'section_id'             => 'advocacy-partners-logos',
    ];

    /* Re-use the existing section by stable section_id, otherwise append */
    $found = false;
    foreach ($blocks as $i => $row) {
        if (
            ($row['acf_fc_layout'] ?? '') === 'logo_strip'
            && ($row['section_id']  ?? '') === 'advocacy-partners-logos'
        ) {
            $blocks[$i] = $section_data;
            $found      = true;
            $notes[]    = "Refreshed existing logo strip section at position {$i}.";
            break;
        }
    }

    if (! $found) {
        $blocks[] = $section_data;
        $notes[]  = 'Appended new logo strip section to the page builder.';
    }

    update_field('page_builder', $blocks, $page_id);

    return [
        'success' => true,
        'message' => 'Speaking & Advocacy logo strip seeded. Visit: ' . get_permalink($page_id),
        'notes'   => $notes,
    ];
}

/**
 * Pretty-print a logo filename as a display name.
 * "Boston_Consulting-Group.jpg" → "Boston Consulting Group"
 */
function elahub_advocacy_logo_name_from_filename(string $basename): string
{
    $name = pathinfo($basename, PATHINFO_FILENAME);
    $name = str_replace(['_', '-'], ' ', $name);
    $name = preg_replace('/\s+/', ' ', $name);
    return trim($name);
}
