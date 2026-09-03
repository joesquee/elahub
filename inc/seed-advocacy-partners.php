<?php

/**
 * Advocacy Partners page seeder
 *
 * Creates (or re-seeds) the Advocacy Partners page, imports logos from
 * wp-content/uploads/advocacy-partner-source/ into the media library,
 * and populates all ACF repeater data.
 *
 * Trigger: visit /advocacy-partners/?elahub_seed_advocacy=1 (must be logged in as admin)
 *
 * Safe to re-run: existing attachments are re-used by filename; the page is
 * not duplicated if it already exists.
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
    exit;
}

add_action('template_redirect', 'elahub_seed_advocacy_partners_from_url');

function elahub_seed_advocacy_partners_from_url()
{
    if (! isset($_GET['elahub_seed_advocacy']) || '1' !== (string) $_GET['elahub_seed_advocacy']) {
        return;
    }

    if (! current_user_can('manage_options')) {
        wp_die('You do not have permission to run this seed.');
    }

    $result = elahub_do_seed_advocacy_partners();

    $status  = ! empty($result['success']) ? 'Seed complete' : 'Seed failed';
    $message = $result['message'] ?? 'No message returned.';
    $notes   = $result['notes'] ?? [];

    ob_start();
    ?>
    <style>body{font-family:sans-serif;max-width:800px;margin:2rem auto;padding:1rem}</style>
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
    <p><a href="<?php echo esc_url(remove_query_arg('elahub_seed_advocacy')); ?>">View page</a></p>
    <?php
    wp_die(ob_get_clean(), $status, ['response' => 200]);
}

function elahub_do_seed_advocacy_partners()
{
    @set_time_limit(0);   // image metadata generation can be slow for 80 files
    $notes = [];

    if (! function_exists('update_field')) {
        return ['success' => false, 'message' => 'ACF is required.', 'notes' => []];
    }

    /* ── 1. Create or find the page ── */
    $existing = get_page_by_path('advocacy-partners');
    if ($existing) {
        $page_id = $existing->ID;
        $notes[] = "Page already exists (ID {$page_id}) — re-seeding content.";
    } else {
        $page_id = wp_insert_post([
            'post_title'   => 'Advocacy Partners',
            'post_name'    => 'advocacy-partners',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '',
            'page_template' => 'template-advocacy-partners.php',
        ], true);

        if (is_wp_error($page_id)) {
            return [
                'success' => false,
                'message' => 'Failed to create page: ' . $page_id->get_error_message(),
                'notes'   => [],
            ];
        }
        $notes[] = "Page created (ID {$page_id}).";
    }

    /* Ensure correct template is set */
    update_post_meta($page_id, '_wp_page_template', 'template-advocacy-partners.php');

    /* ── 2. Set hero content ── */
    update_field('adv_hero_eyebrow',     'Advocacy & Partnerships', $page_id);
    update_field('adv_hero_icon',        'fa-solid fa-handshake',   $page_id);
    update_field('adv_hero_heading',     'Our Advocacy Partners',   $page_id);
    update_field('adv_hero_description',
        'eLaHub works with a wide range of organisations, communities, and networks who share our commitment to accessible and inclusive learning. These are the partners who help us amplify that message.',
        $page_id
    );
    $notes[] = 'Hero content set.';

    /* ── 3. Import logos and build repeater ── */
    // Supports both a flat layout and the nested ADVOCACY_PARTNER_LOGOS sub-folder
    // that results from extracting the original zip on Hostinger.
    $source_dir = WP_CONTENT_DIR . '/uploads/advocacy-partner-source/';
    if ( ! is_dir( $source_dir ) || count( glob( $source_dir . '*.jpg' ) ) === 0 ) {
        $nested = $source_dir . 'ADVOCACY_PARTNER_LOGOS/';
        if ( is_dir( $nested ) ) {
            $source_dir = $nested;
        }
    }
    $upload_dir = wp_upload_dir();

    if (! is_dir($source_dir)) {
        return [
            'success' => false,
            'message' => 'Source directory not found: ' . $source_dir,
            'notes'   => $notes,
        ];
    }

    /* Require WP attachment helpers */
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    /* Target sub-folder inside wp uploads */
    $target_subdir = 'advocacy-partner-logos';
    $target_dir    = trailingslashit($upload_dir['basedir']) . $target_subdir;
    $target_url    = trailingslashit($upload_dir['baseurl']) . $target_subdir;

    if (! is_dir($target_dir)) {
        wp_mkdir_p($target_dir);
        $notes[] = "Created uploads sub-folder: {$target_subdir}/";
    }

    /* Partner data: [ file, display name, URL ] */
    $partners = [
        [ 'file' => '3 Play Media.jpg', 'name' => '3Play Media', 'url' => 'https://www.3playmedia.com/' ],
        [ 'file' => 'AXS Chat.jpg', 'name' => 'AXS Chat', 'url' => 'https://www.axschat.com/' ],
        [ 'file' => 'Ability Net.jpg', 'name' => 'AbilityNet', 'url' => 'https://abilitynet.org.uk/' ],
        [ 'file' => 'Accessibility Book Club.jpg', 'name' => 'Accessibility Book Club', 'url' => 'https://www.linkedin.com/groups/14367289/' ],
        [ 'file' => 'Accessibility Summer Camp.jpg', 'name' => 'Accessibility Summer Camp', 'url' => 'https://www.accessibilityict.org/' ],
        [ 'file' => 'Association or Talent Development.jpg', 'name' => 'Association for Talent Development', 'url' => 'https://www.td.org' ],
        [ 'file' => 'Atlas Copco.jpg', 'name' => 'Atlas Copco', 'url' => '' ],
        [ 'file' => 'Atos.jpg', 'name' => 'Atos', 'url' => 'https://atos.net/en-gb/united-kingdom' ],
        [ 'file' => 'BATOD.jpg', 'name' => 'BATOD', 'url' => 'https://www.batod.org.uk/' ],
        [ 'file' => 'Build Capable.jpg', 'name' => 'Build Capable', 'url' => 'https://www.buildcapable.com/' ],
        [ 'file' => 'CIPD.jpg', 'name' => 'CIPD', 'url' => 'https://www.cipd.org/uk' ],
        [ 'file' => 'CLO 100.jpg', 'name' => 'CLO 100', 'url' => 'https://clo100.com/' ],
        [ 'file' => 'CPD.jpg', 'name' => 'CPDSO', 'url' => 'https://www.cpdstandards.com/' ],
        [ 'file' => 'Cabinet Office.jpg', 'name' => 'Cabinet Office', 'url' => 'https://www.gov.uk/government/organisations/cabinet-office' ],
        [ 'file' => 'Celebrating Disability.jpg', 'name' => 'Celebrating Disability', 'url' => 'https://celebratingdisability.co.uk/' ],
        [ 'file' => 'Champions of Accessibility Network.jpg', 'name' => 'Champions of Accessibility Network', 'url' => 'https://www.linkedin.com/groups/12499821/' ],
        [ 'file' => 'Charity Learning Consortium.jpg', 'name' => 'Charity Learning Consortium', 'url' => 'https://charitylearning.org/' ],
        [ 'file' => 'Colossyan.jpg', 'name' => 'Colossyan', 'url' => 'https://www.colossyan.com/' ],
        [ 'file' => 'Course Arc.jpg', 'name' => 'CourseArc', 'url' => 'https://www.coursearc.com/' ],
        [ 'file' => 'Design for All Learners.jpg', 'name' => 'Design for All Learners', 'url' => 'https://www.designforallbook.com/' ],
        [ 'file' => 'Do Think Do.jpg', 'name' => 'Do Think Do', 'url' => 'https://dothinkdo.com/' ],
        [ 'file' => 'Domin Know.jpg', 'name' => 'dominKnow', 'url' => 'https://dominknow.com/' ],
        [ 'file' => 'European Diplomats.jpg', 'name' => 'European Diplomats', 'url' => 'https://www.european-diplomats.eu/' ],
        [ 'file' => 'HM Prison & Probation Service.jpg', 'name' => 'HM Prison & Probation Service', 'url' => 'https://www.gov.uk/government/organisations/hm-prison-and-probation-service' ],
        [ 'file' => 'HR Uprising.jpg', 'name' => 'HR Uprising', 'url' => 'https://hruprising.com/' ],
        [ 'file' => 'Hampshire Chronicle.jpg', 'name' => 'Hampshire Chronicle', 'url' => 'https://www.hampshirechronicle.co.uk/' ],
        [ 'file' => 'IAAP.jpg', 'name' => 'IAAP', 'url' => 'https://www.accessibilityassociation.org/' ],
        [ 'file' => 'IDIODC.jpg', 'name' => 'IDIODC', 'url' => 'https://dominknow.com/idiodc' ],
        [ 'file' => 'IDT.jpg', 'name' => 'IDTX', 'url' => 'https://idtips.substack.com/' ],
        [ 'file' => 'If You Ask Betty.jpg', 'name' => 'If You Ask Betty', 'url' => 'https://ifyouaskbetty.com/podcast/' ],
        [ 'file' => 'Inspire Accessibility.jpg', 'name' => 'Inspire Accessibility', 'url' => 'https://inspireaccessibility.com/about-us/' ],
        [ 'file' => 'Intellum.jpg', 'name' => 'Intellum', 'url' => 'https://www.intellum.com/' ],
        [ 'file' => 'Jisc.jpg', 'name' => 'Jisc', 'url' => 'https://www.jisc.ac.uk/' ],
        [ 'file' => 'Kogan Page.jpg', 'name' => 'Kogan Page', 'url' => 'https://www.koganpage.com/' ],
        [ 'file' => 'L&D Talks.jpg', 'name' => 'L&D Talks Brussels', 'url' => 'https://www.bedrijfsopleidingen.be/stimulearning/2_ld_talks.asp' ],
        [ 'file' => 'LCA Spotlight.jpg', 'name' => 'LCA Spotlight', 'url' => 'https://www.lcaspotlight.com/' ],
        [ 'file' => 'LLARN.jpg', 'name' => 'Llarn Podcast', 'url' => 'https://womentalkingaboutlearning.com/' ],
        [ 'file' => 'LPI.jpg', 'name' => 'Learning Performance Institute', 'url' => 'https://www.thelpi.org/' ],
        [ 'file' => 'LT Awards.jpg', 'name' => 'Learning Technologies Awards', 'url' => 'https://www.learningtechnologies.co.uk/learning-tech-awards' ],
        [ 'file' => 'LXDCON.jpg', 'name' => 'LXDCON', 'url' => 'https://lxd.org/lxdcon/' ],
        [ 'file' => 'Larmer Brown.jpg', 'name' => 'Larmer Brown', 'url' => 'https://www.larmerbrown.com/' ],
        [ 'file' => 'Learn Tec.jpg', 'name' => 'LEARNTEC', 'url' => 'https://www.learntec.de/en/' ],
        [ 'file' => 'Learning Guild.jpg', 'name' => 'Learning Guild', 'url' => 'https://www.learningguild.com/' ],
        [ 'file' => 'Learning Hack.jpg', 'name' => 'Learning Hack Podcast', 'url' => 'https://learninghackpodcast.com/' ],
        [ 'file' => 'Learning Network.jpg', 'name' => 'Learning Network', 'url' => 'https://thelearning-network.org/' ],
        [ 'file' => 'Learning News.jpg', 'name' => 'Learning News', 'url' => 'https://learningnews.com/' ],
        [ 'file' => 'Learning Technologies.jpg', 'name' => 'Learning Technologies', 'url' => 'https://www.learningtechnologies.co.uk/' ],
        [ 'file' => 'Metro DC atd.jpg', 'name' => 'Metro DC ATD', 'url' => 'https://dcatd.org/' ],
        [ 'file' => 'Mildon.jpg', 'name' => 'Mildon', 'url' => 'https://www.mildon.co.uk/' ],
        [ 'file' => 'Mindtools Kineo.jpg', 'name' => 'Mindtools Kineo', 'url' => 'https://kineo.com/' ],
        [ 'file' => 'NZATD.jpg', 'name' => 'NZATD', 'url' => 'https://www.nzatd.org.nz/' ],
        [ 'file' => 'NatWest.jpg', 'name' => 'NatWest', 'url' => 'https://www.natwest.com/' ],
        [ 'file' => 'Nvolve.jpg', 'name' => 'NVolve Group', 'url' => 'https://www.nvolvegroup.com/' ],
        [ 'file' => 'Oxfam.jpg', 'name' => 'Oxfam', 'url' => 'https://www.oxfam.org.uk/' ],
        [ 'file' => 'Parallel.jpg', 'name' => 'Parallel Windsor', 'url' => 'https://www.parallellifestyle.com/windsor2026' ],
        [ 'file' => 'Pearson.jpg', 'name' => 'Pearson', 'url' => 'https://www.pearson.com/en-gb.html' ],
        [ 'file' => 'Publishing Accessibility Action Group.jpg', 'name' => 'Publishing Accessibility Action Group (PAAG)', 'url' => 'https://www.paag.uk/' ],
        [ 'file' => 'Purple Beard.jpg', 'name' => 'Purple Beard', 'url' => 'https://purplebeard.co.uk/' ],
        [ 'file' => 'Raive On.jpg', 'name' => 'Raiveon', 'url' => 'https://raiveon.com/video-production/' ],
        [ 'file' => 'Routledge.jpg', 'name' => 'Routledge', 'url' => 'https://www.routledge.com/' ],
        [ 'file' => 'Scope.jpg', 'name' => 'Scope', 'url' => 'https://www.scope.org.uk/' ],
        [ 'file' => 'Squire Patton Boggs.jpg', 'name' => 'Squire Patton Boggs', 'url' => 'https://www.squirepattonboggs.com/' ],
        [ 'file' => 'Stellar Labs.jpg', 'name' => 'Stellar Labs', 'url' => '' ],
        [ 'file' => 'TANK.jpg', 'name' => 'Tank PR', 'url' => 'https://tank.co.uk/' ],
        [ 'file' => 'TLDC.jpg', 'name' => 'TLDC', 'url' => 'https://www.thetldc.com/' ],
        [ 'file' => 'Talk to the Elephant.jpg', 'name' => 'Talk to the Elephant', 'url' => 'https://www.amazon.co.uk/Talk-Elephant-Design-Learning-Behavior/dp/0138073686/' ],
        [ 'file' => 'Tech Access.jpg', 'name' => 'Teach Access', 'url' => 'https://www.teachaccess.org/' ],
        [ 'file' => 'Tech Share Pro.jpg', 'name' => 'Tech Share Pro', 'url' => 'https://abilitynet.org.uk/TechSharePro' ],
        [ 'file' => 'The Method.jpg', 'name' => 'The Method', 'url' => 'https://the-method.com/' ],
        [ 'file' => 'Training Industry.jpg', 'name' => 'Training Industry', 'url' => 'https://trainingindustry.com/' ],
        [ 'file' => 'Training Journal.jpg', 'name' => 'Training Journal (Jo Cook)', 'url' => 'https://www.trainingjournal.com/' ],
        [ 'file' => 'UK Deaf Leadership Summit.jpg', 'name' => 'UK Deaf Leadership Summit', 'url' => 'https://deafleadershipsummit.com/' ],
        [ 'file' => 'University Hosptial Southampton.jpg', 'name' => 'University Hospital Southampton NHS', 'url' => 'https://www.uhs.nhs.uk/' ],
        [ 'file' => 'University of Kent.jpg', 'name' => 'University of Kent', 'url' => 'https://www.kent.ac.uk/' ],
        [ 'file' => 'University of Nottingham.jpg', 'name' => 'University of Nottingham (UON Digital Accessibility Conference)', 'url' => 'https://www.nottingham.ac.uk/' ],
        [ 'file' => 'University of Southampton.jpg', 'name' => 'University of Southampton', 'url' => 'https://www.southampton.ac.uk/' ],
        [ 'file' => 'University of Winchester.jpg', 'name' => 'University of Winchester', 'url' => 'https://www.winchester.ac.uk/' ],
        [ 'file' => 'elb Learning.jpg', 'name' => 'ELB Learning', 'url' => 'https://www.elblearning.com/' ],
        [ 'file' => 'iHasco.jpg', 'name' => 'iHasco', 'url' => 'https://www.ihasco.co.uk/' ],
        [ 'file' => 'ispring.jpg', 'name' => 'iSpring', 'url' => 'https://www.ispringsolutions.com/' ],
    ];

    /* ── 4. Import each logo and build ACF repeater rows ── */
    $repeater_rows = [];
    $imported      = 0;
    $reused        = 0;
    $missing       = 0;

    foreach ($partners as $partner) {
        $source_path = $source_dir . $partner['file'];
        $dest_path   = $target_dir . '/' . $partner['file'];
        $dest_url    = $target_url . '/' . rawurlencode($partner['file']);

        /* Skip if source file missing */
        if (! file_exists($source_path)) {
            $notes[]  = "Missing source file: {$partner['file']} — skipped.";
            $missing++;

            /* Still add to repeater without a logo */
            $repeater_rows[] = [
                'adv_name' => $partner['name'],
                'adv_url'  => $partner['url'],
                'adv_logo' => '',
            ];
            continue;
        }

        /* Check if an attachment already exists for this file */
        $existing_attach = elahub_find_attachment_by_filename($partner['file'], $target_dir);

        if ($existing_attach) {
            $attach_id = $existing_attach;
            $reused++;
        } else {
            /* Copy file to uploads sub-folder */
            if (! copy($source_path, $dest_path)) {
                $notes[]  = "Could not copy {$partner['file']} — skipped.";
                $missing++;
                $repeater_rows[] = [
                    'adv_name' => $partner['name'],
                    'adv_url'  => $partner['url'],
                    'adv_logo' => '',
                ];
                continue;
            }

            /* Determine MIME type */
            $filetype  = wp_check_filetype($partner['file']);
            $mime_type = $filetype['type'] ?: 'image/jpeg';

            /* Insert attachment */
            $attach_data = [
                'post_mime_type' => $mime_type,
                'post_title'     => sanitize_file_name(pathinfo($partner['file'], PATHINFO_FILENAME)),
                'post_content'   => '',
                'post_status'    => 'inherit',
            ];

            $attach_id = wp_insert_attachment($attach_data, $dest_path, $page_id);

            if (is_wp_error($attach_id)) {
                $notes[]  = "wp_insert_attachment failed for {$partner['file']}: " . $attach_id->get_error_message();
                $missing++;
                $repeater_rows[] = [
                    'adv_name' => $partner['name'],
                    'adv_url'  => $partner['url'],
                    'adv_logo' => '',
                ];
                continue;
            }

            /* Generate and store image metadata (sizes, alt, etc.) */
            $attach_meta = wp_generate_attachment_metadata($attach_id, $dest_path);
            wp_update_attachment_metadata($attach_id, $attach_meta);

            /*
             * Alt text: empty — the partner name shown below the logo provides
             * the accessible label in context. Setting it here would cause
             * screen readers to announce it twice on link cards.
             */
            update_post_meta($attach_id, '_wp_attachment_image_alt', '');

            $imported++;
        }

        $repeater_rows[] = [
            'adv_name' => $partner['name'],
            'adv_url'  => $partner['url'],
            'adv_logo' => $attach_id,
        ];
    }

    $notes[] = "Logos: {$imported} imported, {$reused} already existed, {$missing} missing/failed.";

    /* ── 5. Save repeater to ACF ── */
    update_field('adv_items', $repeater_rows, $page_id);
    $notes[] = count($repeater_rows) . ' partner rows written to ACF repeater.';

    return [
        'success' => true,
        'message' => "Advocacy Partners page seeded (ID {$page_id}). Visit: " . get_permalink($page_id),
        'notes'   => $notes,
    ];
}

/**
 * Find an existing WP attachment by filename in a given directory.
 *
 * @param string $filename  Filename (basename only).
 * @param string $directory Absolute path to search in.
 * @return int|null Attachment post ID, or null if not found.
 */
function elahub_find_attachment_by_filename(string $filename, string $directory): ?int
{
    global $wpdb;

    $path_fragment = '%' . $wpdb->esc_like($filename) . '%';

    $result = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta}
             WHERE meta_key = '_wp_attached_file'
               AND meta_value LIKE %s
             LIMIT 1",
            $path_fragment
        )
    );

    return $result ? (int) $result : null;
}
