<?php

/**
 * eLaHub page seeder
 *
 * Supports DALC Live, DALC Showcase Module, Short Courses, Tailored Training, Digital Accessibility Awareness Workshop, Accessibility Services Overview, Testing & Auditing, Consultancy & Coaching, Speaking & Advocacy, Accessibility Assessments, and About Us page creation + seeding.
 *
 * Put in /inc/seed-page-config.php, require it from functions.php, then visit:
 * /designing-accessible-learning-content-live/?elahub_seed_page=1
 * or
 * /designing-accessible-learning-content-showcase-module/?elahub_seed_page=1
 * /short-courses/?elahub_seed_page=1
 * /tailored-training/?elahub_seed_page=1
 * /digital-accessibility-awareness-workshop/?elahub_seed_page=1
 * /accessible-elearning-services/?elahub_seed_page=1
 * /accessible-elearning-services/elearning-accessibility-testing-and-auditing/?elahub_seed_page=1
 * /consultancy/?elahub_seed_page=1
 * /speaking-and-advocacy/?elahub_seed_page=1
 * /accessibility-assessments/?elahub_seed_page=1
 * /about-us/?elahub_seed_page=1
 * /accessible-elearning-services/?elahub_seed_services_grid=1
 */

if (! defined('ABSPATH')) {
    exit;
}

add_action('template_redirect', 'elahub_seed_single_page_from_url');

function elahub_seed_single_page_from_url()
{
    $run_page_seed          = isset($_GET['elahub_seed_page']) && '1' === (string) $_GET['elahub_seed_page'];
    $run_services_grid_seed = isset($_GET['elahub_seed_services_grid']) && '1' === (string) $_GET['elahub_seed_services_grid'];

    if (! $run_page_seed && ! $run_services_grid_seed) {
        return;
    }

    if (! current_user_can('manage_options')) {
        wp_die('You do not have permission to run this seed.');
    }

    $home_url = home_url('/');

    if (
        false === strpos($home_url, 'localhost') &&
        false === strpos($home_url, '.local') &&
        false === strpos($home_url, '.test')
    ) {
        wp_die('This seed is intended for local development only.');
    }

    if (! function_exists('update_field')) {
        wp_die('ACF is required to run this seed.');
    }

    if ($run_services_grid_seed) {
        $result = elahub_seed_services_grid_defaults();
    } else {
        $request_path = trim((string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');

        if (false !== strpos($request_path, 'designing-accessible-learning-content-showcase-module')) {
            $result = elahub_seed_showcase_module_page();
        } elseif (false !== strpos($request_path, 'short-courses')) {
            $result = elahub_seed_short_courses_page();
        } elseif (false !== strpos($request_path, 'tailored-training')) {
            $result = elahub_seed_tailored_training_page();
        } elseif (false !== strpos($request_path, 'digital-accessibility-awareness-workshop')) {
            $result = elahub_seed_digital_accessibility_awareness_workshop_page();
        } elseif (false !== strpos($request_path, 'accessible-elearning-services/elearning-accessibility-testing-and-auditing')) {
            $result = elahub_seed_testing_and_auditing_page();
        } elseif (false !== strpos($request_path, 'consultancy')) {
            $result = elahub_seed_consultancy_page();
        } elseif (false !== strpos($request_path, 'accessible-elearning-services')) {
            $result = elahub_seed_accessibility_services_overview_page();
        } elseif (false !== strpos($request_path, 'speaking-and-advocacy')) {
            $result = elahub_seed_speaking_and_advocacy_page();
        } elseif (false !== strpos($request_path, 'accessibility-assessments')) {
            $result = elahub_seed_accessibility_assessments_page();
        } elseif (false !== strpos($request_path, 'about-us')) {
            $result = elahub_seed_about_us_page();
        } else {
            $result = elahub_seed_dalc_live_page();
        }
    }

    $status  = ! empty($result['success']) ? 'Seed complete' : 'Seed failed';
    $message = ! empty($result['message']) ? $result['message'] : 'No message returned.';
    $notes   = ! empty($result['notes']) && is_array($result['notes']) ? $result['notes'] : [];

    ob_start();
?>
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

    <p><a href="<?php echo esc_url(remove_query_arg('elahub_seed_page')); ?>">Back to page</a></p>
<?php
    wp_die(ob_get_clean(), $status, ['response' => 200]);
}

function elahub_seed_digital_accessibility_awareness_workshop_page()
{
    $notes = [];

    $config = [
        'post_title'   => 'Digital Accessibility Awareness Workshop',
        'post_slug'    => 'digital-accessibility-awareness-workshop',
        'post_status'  => 'publish',
        'template'     => 'template-service-page.php',

        'page_icon_source' => 'master_icon',
        'page_icon_svg'    => 'Digital_Accessibility_Awareness_Workshop.svg',

        'page_hero' => [
            'page_hero_badge_text'       => 'Accessibility & Learning',
            'page_hero_badge_icon'       => 'fa-solid fa-universal-access',
            'page_hero_heading'          => 'Digital Accessibility Awareness Workshop',
            'page_hero_description'      => 'A practical, instructor-led workshop that introduces digital accessibility and challenges common myths around disability and access needs. Designed to help teams build shared understanding, with tailored examples based on your own digital content.',
            'page_hero_button_1_label'   => 'Enquire about this workshop',
            'page_hero_button_1_url'     => home_url('/contact-elahub/'),
            'page_hero_button_1_variant' => 'primary',
            'page_hero_button_2_label'   => '',
            'page_hero_button_2_url'     => '',
            'page_hero_button_2_variant' => 'primary',
            'page_hero_image_style'      => 'icon',
        ],

        'page_builder' => [
            [
                'acf_fc_layout'          => 'logo_strip',
                'section_id'             => '',
                'use_default_logo_strip' => 1,
            ],
            [
                'acf_fc_layout'    => 'feature_section',
                'feature_variant'  => 'image_left_bg',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Designed for teams across your organisation',
                'body'             => '<p>This virtual, instructor-led workshop is designed for organisations looking to build a shared understanding of digital accessibility across their teams. It introduces the benefits of accessibility, challenges common myths around disability and access needs, and uses tailored examples from your own digital content to keep the session practical and relevant.</p>',
                'image'            => 12139,
                'show_list'        => 1,
                'list_items'       => [
                    ['item' => 'Suitable for all staff involved in creating, reviewing, or sharing digital content'],
                    ['item' => 'Encourages collaboration and empathy through real-world examples'],
                    ['item' => 'Focuses on small, practical “micro-commitments” teams can apply straight away'],
                ],
                'button_label'     => 'Enquire about this workshop',
                'button_url'       => home_url('/contact-elahub/'),
            ],
            [
                'acf_fc_layout'    => 'feature_section',
                'feature_variant'  => 'image_right_bg',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Why Organisations Choose eLaHub',
                'body'             => '<p>eLaHub provides accessibility services designed specifically for learning content. Our work combines deep accessibility expertise with a learning practitioner’s perspective, so outputs are not just technical findings, but clear, practical guidance teams can use to improve real learning materials.</p><p>Whether you need a structured assessment, detailed auditing, or targeted consultancy support, you’ll receive findings that explain what is happening, why it matters to learners, and what to change to improve accessibility and usability across your content.</p>',
                'image'            => 10720,
                'show_list'        => 1,
                'list_items'       => [
                    ['item' => 'Accessibility services designed for learning content, not generic websites'],
                    ['item' => 'Clear findings that explain what’s happening and why it matters'],
                    ['item' => 'Practical guidance with patterns teams can apply again and again'],
                    ['item' => 'Options to suit different requirements, timeframes, and levels of depth'],
                    ['item' => 'Output designed to support decisions across learning, accessibility, and standards roles'],
                ],
                'button_label'     => 'Start Learning Now',
                'button_url'       => home_url('/designing-accessible-learning-content-programme/'),
            ],
            [
                'acf_fc_layout' => 'cta_section',
                'cta_items'     => [
                    [
                        'acf_fc_layout'     => 'cta_icon_features',
                        'badge_text'        => 'Accessibility & Learning',
                        'badge_icon_class'  => 'fa-solid fa-universal-access',
                        'heading'           => 'What the workshop covers',
                        'body'              => '<p>This workshop is designed to help organisations build a shared understanding of digital accessibility across teams, not just specialists. It combines practical guidance with real examples, and includes tailored references to your own digital content so the learning feels immediately relevant.</p>',
                        'button_label'      => 'Enquire about this workshop',
                        'button_url'        => home_url('/contact-elahub/'),
                        'button_aria_label' => 'Enquire about this workshop',
                        'columns'           => '3',
                        'items'             => [
                            [
                                'title'         => 'Interactive, instructor-led session',
                                'description'   => 'Delivered live by Susi Miller, with time for discussion and questions.',
                                'icon_source'   => 'font_awesome',
                                'icon_fa_class' => 'fa-solid fa-person-chalkboard',
                            ],
                            [
                                'title'         => 'Covers the benefits of accessibility',
                                'description'   => 'A clear look at why accessibility matters across day-to-day digital work.',
                                'icon_source'   => 'font_awesome',
                                'icon_fa_class' => 'fa-solid fa-circle-check',
                            ],
                            [
                                'title'         => 'Challenges common myths',
                                'description'   => 'Tackles misconceptions around disability and access needs.',
                                'icon_source'   => 'font_awesome',
                                'icon_fa_class' => 'fa-solid fa-comments',
                            ],
                            [
                                'title'         => 'Explores real-world access needs',
                                'description'   => 'Looks at permanent, temporary, and situational barriers people face.',
                                'icon_source'   => 'font_awesome',
                                'icon_fa_class' => 'fa-solid fa-earth-europe',
                            ],
                            [
                                'title'         => 'Micro-commitments teams can use',
                                'description'   => 'Small changes everyone can apply across digital content straight away.',
                                'icon_source'   => 'font_awesome',
                                'icon_fa_class' => 'fa-solid fa-list-check',
                            ],
                            [
                                'title'         => 'Tailored examples from your content',
                                'description'   => 'Includes a mini review of your content to make examples bespoke.',
                                'icon_source'   => 'font_awesome',
                                'icon_fa_class' => 'fa-solid fa-wand-magic-sparkles',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'acf_fc_layout'  => 'faq_section',
                'use_global_faq' => 1,
                'faq_limit'      => 0,
            ],
            [
                'acf_fc_layout' => 'case_studies_section',
                'badge_text'    => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'       => 'Organisations We’ve Delivered For',
                'body'          => '<p>We deliver tailored training to organisations across the public, private, and education sectors. The examples below illustrate the types of bespoke training delivered, with content and format adapted to organisational context and priorities.</p>',
                'button_label'  => 'See Our Case Studies',
                'button_url'    => home_url('/accessible-learning-case-studies/'),
            ],
            [
                'acf_fc_layout'           => 'testimonials_section',
                'use_global_testimonials' => 1,
            ],
        ],
    ];

    $page = get_page_by_path($config['post_slug'], OBJECT, 'page');

    if (! $page instanceof WP_Post) {
        $page_id = wp_insert_post(
            [
                'post_type'    => 'page',
                'post_title'   => $config['post_title'],
                'post_name'    => $config['post_slug'],
                'post_status'  => $config['post_status'],
                'post_content' => '',
            ],
            true
        );

        if (is_wp_error($page_id)) {
            return [
                'success' => false,
                'message' => 'The page could not be created.',
                'notes'   => ['WordPress returned: ' . $page_id->get_error_message()],
            ];
        }

        $notes[] = 'Created page ID ' . $page_id . '.';
    } else {
        $page_id = (int) $page->ID;

        wp_update_post(
            [
                'ID'          => $page_id,
                'post_title'  => $config['post_title'],
                'post_name'   => $config['post_slug'],
                'post_status' => $config['post_status'],
            ]
        );

        $notes[] = 'Updated existing page ID ' . $page_id . '.';
    }

    update_post_meta($page_id, '_wp_page_template', $config['template']);

    update_field('page_icon_source', $config['page_icon_source'], $page_id);
    update_field('page_icon_svg', $config['page_icon_svg'], $page_id);

    foreach ($config['page_hero'] as $field_name => $value) {
        update_field($field_name, $value, $page_id);
    }

    update_field('page_builder', $config['page_builder'], $page_id);

    $notes[] = 'Template set to template-service-page.php.';
    $notes[] = 'Page icon set to Digital_Accessibility_Awareness_Workshop.svg.';
    $notes[] = 'Page Builder rows written: ' . count($config['page_builder']) . '.';

    return [
        'success' => true,
        'message' => 'Digital Accessibility Awareness Workshop page has been created and seeded.',
        'notes'   => $notes,
    ];
}

function elahub_seed_accessibility_services_overview_page()
{
    $notes = [];

    $config = [
        'post_title'   => 'Accessibility Services for Learning Content',
        'post_slug'    => 'accessible-elearning-services',
        'post_status'  => 'publish',
        'template'     => 'template-service-page.php',

        'page_icon_source' => 'master_icon',
        'page_icon_svg'    => 'Accessibility_Services.svg',

        'page_hero' => [
            'page_hero_badge_text'       => 'Accessibility & Learning',
            'page_hero_badge_icon'       => 'fa-solid fa-universal-access',
            'page_hero_heading'          => 'Accessibility Services for Learning Content',
            'page_hero_description'      => 'Practical accessibility services designed to support organisations creating and delivering learning content. We work with teams to review, assess, and improve accessibility across learning materials, using clear standards-led approaches and practical testing methods. Explore our services below or book a chat to discuss what support is needed.',
            'page_hero_button_1_label'   => 'Book a chat',
            'page_hero_button_1_url'     => home_url('/contact-elahub/'),
            'page_hero_button_1_variant' => 'primary',
            'page_hero_button_2_label'   => '',
            'page_hero_button_2_url'     => '',
            'page_hero_button_2_variant' => 'primary',
            'page_hero_image_style'      => 'icon',
        ],

        'page_builder' => [
            [
                'acf_fc_layout'          => 'logo_strip',
                'section_id'             => '',
                'use_default_logo_strip' => 1,
            ],
            [
                'acf_fc_layout'    => 'feature_section',
                'feature_variant'  => 'image_left',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Support for Organisations That Need Practical Accessibility Assurance',
                'body'             => '<p>Our Accessibility Services are designed for organisations creating and delivering learning content who need practical support to review, assess, and improve accessibility. Services are scoped around your content, platforms, and context, and are delivered using clear standards-led approaches and practical testing methods.</p><p>This service area is focused on evaluation and support. If you are looking to build internal capability through courses and programmes, explore our Training and Programmes section.</p>',
                'image'            => 12139,
                'show_list'        => 0,
                'button_label'     => 'Book a call',
                'button_url'       => home_url('/contact-elahub/'),
            ],
            [
                'acf_fc_layout'    => 'services_grid',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Explore Our Services',
                'body'             => '<p>A range of services to support organisations creating learning content, from structured assessments to auditing, testing, and targeted consultancy.</p>',
                'button_link'      => [
                    'title'  => 'Book a Call',
                    'url'    => home_url('/contact-elahub/'),
                    'target' => '',
                ],
                'columns'          => '3',
                'icon_height'      => 100,
                'items'            => [
                    [
                        'page'             => 7942,
                        'heading_override' => 'Testing and Auditing',
                        'description'      => '<p>Manual auditing and practical testing of learning content using standards-led approaches and assistive technology. Designed to identify barriers, document issues clearly, and support remediation planning.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 12720,
                        'heading_override' => 'Accessibility Assessments',
                        'description'      => '<p>Structured assessments designed to help organisations understand the accessibility of learning content and prioritise next steps. Includes eLa1000 at launch, with further assessment options available over time.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 2947,
                        'heading_override' => 'Consultancy and Coaching',
                        'description'      => '<p>Understanding WCAG requirements as they apply to learning content. Focuses on what teams need to know and how standards translate into practice.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 7946,
                        'heading_override' => 'Speaking and Advocacy',
                        'description'      => '<p>Talks, keynotes, and sessions designed to support organisations building understanding of accessibility and inclusive learning. Available for events, internal programmes, and learning communities.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 12720,
                        'heading_override' => 'Accessible LMS Support',
                        'description'      => '<p>Support for organisations reviewing learning platforms and LMS accessibility considerations. Includes guidance and tools designed to help teams make informed decisions.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 2949,
                        'heading_override' => 'Training & Programmes',
                        'description'      => '<p>Manual auditing and practical testing of learning content using standards-led approaches and assistive technology. Designed to identify barriers, document issues clearly, and support remediation planning.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                ],
            ],
            [
                'acf_fc_layout'    => 'feature_section',
                'feature_variant'  => 'image_right_bg',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Why Organisations Choose eLaHub',
                'body'             => '<p>eLaHub provides accessibility services designed specifically for learning content. Our work combines deep accessibility expertise with a learning practitioner’s perspective, so outputs are not just technical findings, but clear, practical guidance teams can use to improve real learning materials.</p><p>Whether you need a structured assessment, detailed auditing, or targeted consultancy support, you’ll receive findings that explain what is happening, why it matters to learners, and what to change to improve accessibility and usability across your content.</p>',
                'image'            => 10720,
                'show_list'        => 1,
                'list_items'       => [
                    ['item' => 'Accessibility services designed for learning content, not generic websites'],
                    ['item' => 'Clear findings that explain what’s happening and why it matters'],
                    ['item' => 'Practical guidance with patterns teams can apply again and again'],
                    ['item' => 'Options to suit different requirements, timeframes, and levels of depth'],
                    ['item' => 'Output designed to support decisions across learning, accessibility, and standards roles'],
                ],
                'button_label'     => 'Start Learning Now',
                'button_url'       => home_url('/designing-accessible-learning-content-programme/'),
            ],
            [
                'acf_fc_layout'  => 'faq_section',
                'use_global_faq' => 1,
                'faq_limit'      => 0,
            ],
            [
                'acf_fc_layout'           => 'testimonials_section',
                'use_global_testimonials' => 1,
            ],
        ],
    ];

    $page = get_page_by_path($config['post_slug'], OBJECT, 'page');

    if (! $page instanceof WP_Post) {
        $page_id = wp_insert_post(
            [
                'post_type'    => 'page',
                'post_title'   => $config['post_title'],
                'post_name'    => $config['post_slug'],
                'post_status'  => $config['post_status'],
                'post_content' => '',
            ],
            true
        );

        if (is_wp_error($page_id)) {
            return [
                'success' => false,
                'message' => 'The page could not be created.',
                'notes'   => ['WordPress returned: ' . $page_id->get_error_message()],
            ];
        }

        $notes[] = 'Created page ID ' . $page_id . '.';
    } else {
        $page_id = (int) $page->ID;

        wp_update_post(
            [
                'ID'          => $page_id,
                'post_title'  => $config['post_title'],
                'post_name'   => $config['post_slug'],
                'post_status' => $config['post_status'],
            ]
        );

        $notes[] = 'Updated existing page ID ' . $page_id . '.';
    }

    update_post_meta($page_id, '_wp_page_template', $config['template']);

    update_field('page_icon_source', $config['page_icon_source'], $page_id);
    update_field('page_icon_svg', $config['page_icon_svg'], $page_id);

    foreach ($config['page_hero'] as $field_name => $value) {
        update_field($field_name, $value, $page_id);
    }

    update_field('page_builder', $config['page_builder'], $page_id);

    $notes[] = 'Template set to template-service-page.php.';
    $notes[] = 'Page icon set to Accessibility_Services.svg.';
    $notes[] = 'Page Builder rows written: ' . count($config['page_builder']) . '.';

    return [
        'success' => true,
        'message' => 'Accessibility Services Overview page has been created and seeded.',
        'notes'   => $notes,
    ];
}

function elahub_seed_about_us_page()
{
    $notes = [];

    $team_cards = [
        [
            'name'        => 'Jayne Davids',
            'role'        => 'Accessible Multimedia Specialist',
            'description' => 'Jayne supports eLaHub’s work around accessible multimedia, including video, audio, and screen-based content. With extensive experience in training and development, she helps ensure learning resources are accessible, engaging, and technically sound.',
            'image'       => 12139,
        ],
        [
            'name'        => 'Jayne Davids',
            'role'        => 'Accessible Multimedia Specialist',
            'description' => 'Jayne supports eLaHub’s work around accessible multimedia, including video, audio, and screen-based content. With extensive experience in training and development, she helps ensure learning resources are accessible, engaging, and technically sound.',
            'image'       => 12139,
        ],
        [
            'name'        => 'Jayne Davids',
            'role'        => 'Accessible Multimedia Specialist',
            'description' => 'Jayne supports eLaHub’s work around accessible multimedia, including video, audio, and screen-based content. With extensive experience in training and development, she helps ensure learning resources are accessible, engaging, and technically sound.',
            'image'       => 12139,
        ],
        [
            'name'        => 'Jayne Davids',
            'role'        => 'Accessible Multimedia Specialist',
            'description' => 'Jayne supports eLaHub’s work around accessible multimedia, including video, audio, and screen-based content. With extensive experience in training and development, she helps ensure learning resources are accessible, engaging, and technically sound.',
            'image'       => 12139,
        ],
        [
            'name'        => 'Jayne Davids',
            'role'        => 'Accessible Multimedia Specialist',
            'description' => 'Jayne supports eLaHub’s work around accessible multimedia, including video, audio, and screen-based content. With extensive experience in training and development, she helps ensure learning resources are accessible, engaging, and technically sound.',
            'image'       => 12139,
        ],
        [
            'name'        => 'Jayne Davids',
            'role'        => 'Accessible Multimedia Specialist',
            'description' => 'Jayne supports eLaHub’s work around accessible multimedia, including video, audio, and screen-based content. With extensive experience in training and development, she helps ensure learning resources are accessible, engaging, and technically sound.',
            'image'       => 12139,
        ],
    ];

    $partner_cards = [
        [
            'name'        => 'Jayne Davids',
            'role'        => 'Accessible Multimedia Specialist',
            'description' => 'Jayne supports eLaHub’s work around accessible multimedia, including video, audio, and screen-based content. With extensive experience in training and development, she helps ensure learning resources are accessible, engaging, and technically sound.',
            'image'       => 12139,
        ],
        [
            'name'        => 'Jayne Davids',
            'role'        => 'Accessible Multimedia Specialist',
            'description' => 'Jayne supports eLaHub’s work around accessible multimedia, including video, audio, and screen-based content. With extensive experience in training and development, she helps ensure learning resources are accessible, engaging, and technically sound.',
            'image'       => 12139,
        ],
        [
            'name'        => 'Jayne Davids',
            'role'        => 'Accessible Multimedia Specialist',
            'description' => 'Jayne supports eLaHub’s work around accessible multimedia, including video, audio, and screen-based content. With extensive experience in training and development, she helps ensure learning resources are accessible, engaging, and technically sound.',
            'image'       => 12139,
        ],
        [
            'name'        => 'Jayne Davids',
            'role'        => 'Accessible Multimedia Specialist',
            'description' => 'Jayne supports eLaHub’s work around accessible multimedia, including video, audio, and screen-based content. With extensive experience in training and development, she helps ensure learning resources are accessible, engaging, and technically sound.',
            'image'       => 12139,
        ],
        [
            'name'        => 'Jayne Davids',
            'role'        => 'Accessible Multimedia Specialist',
            'description' => 'Jayne supports eLaHub’s work around accessible multimedia, including video, audio, and screen-based content. With extensive experience in training and development, she helps ensure learning resources are accessible, engaging, and technically sound.',
            'image'       => 12139,
        ],
        [
            'name'        => 'Jayne Davids',
            'role'        => 'Accessible Multimedia Specialist',
            'description' => 'Jayne supports eLaHub’s work around accessible multimedia, including video, audio, and screen-based content. With extensive experience in training and development, she helps ensure learning resources are accessible, engaging, and technically sound.',
            'image'       => 12139,
        ],
    ];

    $config = [
        'post_title'   => 'About Us',
        'post_slug'    => 'about-us',
        'post_status'  => 'publish',
        'template'     => 'template-service-page.php',

        'page_icon_source' => 'master_icon',
        'page_icon_svg'    => 'About_Us.svg',

        'page_hero' => [
            'page_hero_badge_text'       => 'Accessibility & Learning',
            'page_hero_badge_icon'       => 'fa-solid fa-universal-access',
            'page_hero_heading'          => 'Meet eLaHub',
            'page_hero_description'      => 'We help organisations create learning content that works for everyone. Through testing, training, and practical support, we help teams improve accessibility and learning quality across real tools, real constraints, and real learner needs.',
            'page_hero_button_1_label'   => 'Book a chat',
            'page_hero_button_1_url'     => home_url('/contact-elahub/'),
            'page_hero_button_1_variant' => 'primary',
            'page_hero_button_2_label'   => '',
            'page_hero_button_2_url'     => '',
            'page_hero_button_2_variant' => 'primary',
            'page_hero_image_style'      => 'icon',
        ],

        'page_builder' => [
            [
                'acf_fc_layout'          => 'logo_strip',
                'section_id'             => '',
                'use_default_logo_strip' => 1,
            ],
            [
                'acf_fc_layout'    => 'feature_section',
                'feature_variant'  => 'image_left',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Why eLaHub exists',
                'body'             => '<p>eLaHub was created to close a gap we see again and again in learning accessibility: plenty of standards, but not enough practical support for the people actually designing and delivering learning content. Learning teams are often expected to “make it accessible” while working within real constraints — tight timelines, legacy content, limited authoring tools, and competing priorities. When accessibility isn’t clearly understood or embedded early, barriers creep in and learning quickly becomes harder to use and less effective.</p><p>Our focus is on learning content and the learning experience. We help organisations identify what’s getting in the way — whether that’s inaccessible interactions, unclear structure and instructions, missing media alternatives, or design decisions that increase cognitive load. These issues don’t just affect disabled learners; they affect comprehension, confidence, and engagement for everyone. When learning is difficult to access or navigate, the learning outcome is at risk.</p><p>eLaHub exists to make accessibility practical and achievable. Our work is evidence led, grounded in real audit and testing experience, and focused on clear, realistic next steps. We support teams to improve accessibility in ways that fit how learning is actually built, while helping organisations develop stronger foundations for accessible learning over the long term.</p>',
                'image'            => 12139,
                'show_list'        => 0,
                'button_label'     => 'Book a call',
                'button_url'       => home_url('/contact-elahub/'),
            ],
            [
                'acf_fc_layout'    => 'services_grid',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'What We Do',
                'body'             => '<p>eLaHub supports organisations to create learning content that works for everyone. We combine practical accessibility expertise with real learning design experience — helping teams improve the accessibility, usability, and effectiveness of learning across a wide range of tools and formats.</p>',
                'button_link'      => [
                    'title'  => 'View All Training & Programmes',
                    'url'    => home_url('/training-and-programmes/'),
                    'target' => '',
                ],
                'columns'          => '4',
                'icon_height'      => 100,
                'items'            => [
                    [
                        'page'             => 12494,
                        'heading_override' => 'DALC Programme',
                        'description'      => '<p>The gold standard in practical eLearning accessibility training. Over 100 bite sized lessons created by industry expert Susi Miller, showing learning practitioners how to design, develop, test and fix learning content using the latest WCAG 2.2 standards. Try 9 lessons first in the free DALC Showcase Module.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 7942,
                        'heading_override' => 'Accessibility Audits',
                        'description'      => '<p>A unique service for organisations that need to audit and improve existing learning content. We identify accessibility issues and provide clear, practical guidance to improve usability, effectiveness and learner experience.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 12720,
                        'heading_override' => 'Assessments',
                        'description'      => '<p>A structured eLearning accessibility assessment to benchmark where you are today and highlight the next steps needed to improve accessibility across your learning content and approach.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 12675,
                        'heading_override' => 'Tailored Training',
                        'description'      => '<p>Industry leading training delivered by Susi Miller, shaped around your organisation, your content and your tools. Designed to help teams build capability and apply accessibility in the real world.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                ],
            ],
            [
                'acf_fc_layout'    => 'feature_section',
                'feature_variant'  => 'image_right',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Meet Susi Miller',
                'body'             => '<p>Susi Miller is internationally recognised as one of the leading voices in eLearning accessibility. With over 30 years’ experience in learning and development, she has spent her career showing that accessibility is not just compliance, it is the foundation of great learning.</p><p>Susi is the author of Designing Accessible Learning Content, and her work has helped shape how organisations approach inclusive design across workplace learning, higher education, and professional training. In 2025, she was named the Learning Performance Institute’s Learning Professional of the Year (Gold Winner) for her contributions to accessibility.</p><p>She is also the creator of the DALC Programme, an award winning programme built through years of research and real world auditing. eLaHub’s work has supported 235+ organisations across 32 countries and trained 1,500+ delegates, combining practical expertise with training that helps teams build accessible learning content with confidence.</p>',
                'image'            => 12139,
                'show_list'        => 0,
                'button_label'     => 'Book a chat',
                'button_url'       => home_url('/contact-elahub/'),
            ],
            [
                'acf_fc_layout' => 'cta_section',
                'cta_items'     => [
                    [
                        'acf_fc_layout'     => 'cta_people_cards',
                        'badge_text'        => 'Accessibility & Learning',
                        'badge_icon_class'  => 'fa-solid fa-universal-access',
                        'heading'           => 'Meet the team',
                        'body'              => '<p>eLaHub is supported by a small core team and a wider network of specialists who bring deep experience in learning design, accessibility testing, and inclusive practice. Together, we combine technical expertise, lived experience, and practical delivery to support accessible learning in the real world.</p>',
                        'button_label'      => 'Book a chat',
                        'button_url'        => home_url('/contact-elahub/'),
                        'button_aria_label' => 'Book a chat',
                        'columns'           => '3',
                        'cards'             => $team_cards,
                    ],
                    [
                        'acf_fc_layout'     => 'cta_people_cards',
                        'badge_text'        => 'Accessibility & Learning',
                        'badge_icon_class'  => 'fa-solid fa-universal-access',
                        'heading'           => 'Partners & Associates',
                        'body'              => '<p>Alongside our core team, eLaHub works with a trusted collective of partners and associates. Each brings specialist expertise, lived experience, or sector knowledge that strengthens our work and helps organisations create more inclusive learning experiences.</p>',
                        'button_label'      => 'Book a chat',
                        'button_url'        => home_url('/contact-elahub/'),
                        'button_aria_label' => 'Book a chat',
                        'columns'           => '3',
                        'cards'             => $partner_cards,
                    ],
                ],
            ],
            [
                'acf_fc_layout'    => 'icon_features',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'A practical approach to accessible learning',
                'body'             => '<p>Accessibility only makes a difference when it’s applied in the real world — across real tools, real teams, and real constraints. Our approach is evidence led, learner focused, and designed to help organisations make meaningful improvements that last.</p>',
                'button_link'      => [
                    'title'  => 'Contact eLaHub',
                    'url'    => home_url('/contact-elahub/'),
                    'target' => '',
                ],
                'columns'          => '3',
                'items'            => [
                    [
                        'title'         => 'Evidence-led',
                        'description'   => 'We base our guidance on standards, testing, and what we see repeatedly in real learning content.',
                        'icon_source'   => 'font_awesome',
                        'icon_fa_class' => 'fa-solid fa-chart-line',
                    ],
                    [
                        'title'         => 'Practical and achievable',
                        'description'   => 'Clear next steps that teams can apply without needing to rebuild everything from scratch.',
                        'icon_source'   => 'font_awesome',
                        'icon_fa_class' => 'fa-solid fa-screwdriver-wrench',
                    ],
                    [
                        'title'         => 'Learner-first',
                        'description'   => 'We focus on how learning feels and functions for people with diverse access needs — not just what looks correct on paper.',
                        'icon_source'   => 'font_awesome',
                        'icon_fa_class' => 'fa-solid fa-user-group',
                    ],
                    [
                        'title'         => 'Inclusive by design',
                        'description'   => 'Accessibility is built in early through structure, language, interaction choices, and media alternatives.',
                        'icon_source'   => 'font_awesome',
                        'icon_fa_class' => 'fa-solid fa-compass-drafting',
                    ],
                    [
                        'title'         => 'Collaborative',
                        'description'   => 'We work alongside your team to support understanding, confidence, and long term capability.',
                        'icon_source'   => 'font_awesome',
                        'icon_fa_class' => 'fa-solid fa-people-arrows',
                    ],
                    [
                        'title'         => 'Continuous improvement',
                        'description'   => 'Accessibility isn’t a one off project. We help organisations improve iteratively as content, tools, and learner needs evolve.',
                        'icon_source'   => 'font_awesome',
                        'icon_fa_class' => 'fa-solid fa-arrows-rotate',
                    ],
                ],
            ],
            [
                'acf_fc_layout'           => 'testimonials_section',
                'use_global_testimonials' => 1,
            ],
        ],
    ];

    $page = get_page_by_path('about-us', OBJECT, 'page');

    if (! $page instanceof WP_Post) {
        $page = get_page_by_path($config['post_slug'], OBJECT, 'page');
    }

    if (! $page instanceof WP_Post) {
        $page_id = wp_insert_post(
            [
                'post_type'    => 'page',
                'post_title'   => $config['post_title'],
                'post_name'    => $config['post_slug'],
                'post_status'  => $config['post_status'],
                'post_content' => '',
            ],
            true
        );

        if (is_wp_error($page_id)) {
            return [
                'success' => false,
                'message' => 'The page could not be created.',
                'notes'   => ['WordPress returned: ' . $page_id->get_error_message()],
            ];
        }

        $notes[] = 'Created page ID ' . $page_id . '.';
    } else {
        $page_id = (int) $page->ID;

        wp_update_post(
            [
                'ID'          => $page_id,
                'post_title'  => $config['post_title'],
                'post_name'   => 'about-us',
                'post_status' => $config['post_status'],
            ]
        );

        $notes[] = 'Updated existing page ID ' . $page_id . '.';
    }

    update_post_meta($page_id, '_wp_page_template', $config['template']);

    update_field('page_icon_source', $config['page_icon_source'], $page_id);
    update_field('page_icon_svg', $config['page_icon_svg'], $page_id);

    foreach ($config['page_hero'] as $field_name => $value) {
        update_field($field_name, $value, $page_id);
    }

    update_field('page_builder', $config['page_builder'], $page_id);

    $notes[] = 'Template set to template-service-page.php.';
    $notes[] = 'Page icon set to About_Us.svg.';
    $notes[] = 'Page Builder rows written: ' . count($config['page_builder']) . '.';

    return [
        'success' => true,
        'message' => 'About Us page has been created and seeded.',
        'notes'   => $notes,
    ];
}

function elahub_seed_services_grid_defaults()
{
    $notes = [];

    $page_ids = [
        'overview'             => elahub_get_page_id_by_path('accessible-elearning-services'),
        'awareness'            => elahub_get_page_id_by_path('digital-accessibility-awareness-workshop'),
        'tailored_training'    => elahub_get_page_id_by_path('tailored-training'),
        'testing'              => elahub_get_page_id_by_path('accessible-elearning-services/elearning-accessibility-testing-and-auditing'),
        'consultancy'          => elahub_get_page_id_by_path('consultancy'),
        'training_programmes'  => elahub_get_page_id_by_path('accessible-elearning-services/elearning-and-digital-accessibility-training'),
        'speaking'             => elahub_get_page_id_by_path('speaking-and-advocacy'),
        'assessments'          => elahub_get_page_id_by_path('accessibility-assessments'),
    ];

    $global_items = array_values(array_filter([
        [
            'page'             => $page_ids['testing'],
            'heading_override' => 'Testing & Auditing',
            'description'      => '<p>Manual auditing and practical testing of learning content using standards led approaches and assistive technology. Designed to identify barriers, document issues clearly, and support remediation planning.</p>',
            'icon_type'        => 'page_icon',
        ],
        [
            'page'             => $page_ids['assessments'],
            'heading_override' => 'Accessibility Assessments',
            'description'      => '<p>Structured assessments designed to help organisations understand the accessibility of learning content and prioritise next steps. Includes eLa1000 at launch, with further assessment options available over time.</p>',
            'icon_type'        => 'page_icon',
        ],
        [
            'page'             => $page_ids['consultancy'],
            'heading_override' => 'Consultancy and Coaching',
            'description'      => '<p>Understanding WCAG requirements as they apply to learning content. Focuses on what teams need to know and how standards translate into practice.</p>',
            'icon_type'        => 'page_icon',
        ],
        [
            'page'             => $page_ids['speaking'],
            'heading_override' => 'Speaking and Advocacy',
            'description'      => '<p>Talks, keynotes, and sessions designed to support organisations building understanding of accessibility and inclusive learning. Available for events, internal programmes, and learning communities.</p>',
            'icon_type'        => 'page_icon',
        ],
        [
            'page'             => $page_ids['training_programmes'],
            'heading_override' => 'Training & Programmes',
            'description'      => '<p>Courses and programmes designed to build practical capability in accessible learning content, from foundational awareness through to in-depth training.</p>',
            'icon_type'        => 'page_icon',
        ],
    ], static function ($item) {
        return ! empty($item['page']);
    }));

    update_field('services_grid_default_items', $global_items, 'option');
    $notes[] = 'Updated Services Grid defaults in Theme Settings > Global Content.';

    $manual_training_programmes_items = array_values(array_filter([
        [
            'page'             => $page_ids['tailored_training'],
            'heading_override' => 'Tailored Training',
            'description'      => '<p>Industry-leading training delivered by Susi Miller, shaped around your organisation, your content and your tools. Designed to help teams build capability and apply accessibility in the real world.</p>',
            'icon_type'        => 'page_icon',
        ],
        [
            'page'             => $page_ids['awareness'],
            'heading_override' => 'Digital Accessibility Awareness Raising Workshop',
            'description'      => '<p>A practical, instructor-led workshop that introduces digital accessibility and challenges common myths around disability and access needs. Designed to help teams build shared understanding, with tailored examples based on your own digital content.</p>',
            'icon_type'        => 'page_icon',
        ],
        [
            'page'             => $page_ids['overview'],
            'heading_override' => 'Accessibility Services',
            'description'      => '<p>Practical accessibility services designed to support organisations creating and delivering learning content. We work with teams to review, assess, and improve accessibility across learning materials, using clear standards-led approaches and practical testing methods.</p>',
            'icon_type'        => 'page_icon',
        ],
    ], static function ($item) {
        return ! empty($item['page']);
    }));

    $manual_overview_items = array_values(array_filter([
        [
            'page'             => $page_ids['testing'],
            'heading_override' => 'Testing & Auditing',
            'description'      => '<p>Manual auditing and practical testing of learning content using standards led approaches and assistive technology. Designed to identify barriers, document issues clearly, and support remediation planning.</p>',
            'icon_type'        => 'page_icon',
        ],
        [
            'page'             => $page_ids['assessments'],
            'heading_override' => 'Accessibility Assessments',
            'description'      => '<p>Structured assessments designed to help organisations understand the accessibility of learning content and prioritise next steps. Includes eLa1000 at launch, with further assessment options available over time.</p>',
            'icon_type'        => 'page_icon',
        ],
        [
            'page'             => $page_ids['consultancy'],
            'heading_override' => 'Consultancy and Coaching',
            'description'      => '<p>Understanding WCAG requirements as they apply to learning content. Focuses on what teams need to know and how standards translate into practice.</p>',
            'icon_type'        => 'page_icon',
        ],
        [
            'page'             => $page_ids['speaking'],
            'heading_override' => 'Speaking and Advocacy',
            'description'      => '<p>Talks, keynotes, and sessions designed to support organisations building understanding of accessibility and inclusive learning. Available for events, internal programmes, and learning communities.</p>',
            'icon_type'        => 'page_icon',
        ],
        [
            'page'             => $page_ids['training_programmes'],
            'heading_override' => 'Training & Programmes',
            'description'      => '<p>Courses and programmes designed to build practical capability in accessible learning content, from foundational awareness through to in-depth training.</p>',
            'icon_type'        => 'page_icon',
        ],
    ], static function ($item) {
        return ! empty($item['page']);
    }));

    $pages_to_update = [
        'accessible-elearning-services/elearning-and-digital-accessibility-training' => [
            'use_global_service_items' => 0,
            'items'                    => $manual_training_programmes_items,
        ],
        'accessible-elearning-services' => [
            'use_global_service_items' => 0,
            'items'                    => $manual_overview_items,
        ],
        'accessible-elearning-services/elearning-accessibility-testing-and-auditing' => [
            'use_global_service_items' => 1,
            'items'                    => [],
        ],
        'consultancy' => [
            'use_global_service_items' => 1,
            'items'                    => [],
        ],
        'speaking-and-advocacy' => [
            'use_global_service_items' => 1,
            'items'                    => [],
        ],
        'accessibility-assessments' => [
            'use_global_service_items' => 1,
            'items'                    => [],
        ],
    ];

    foreach ($pages_to_update as $path => $page_config) {
        $page_id = elahub_get_page_id_by_path($path);

        if (! $page_id) {
            $notes[] = 'Skipped ' . $path . ' — page not found.';
            continue;
        }

        $page_builder = get_field('page_builder', $page_id);

        if (! is_array($page_builder) || empty($page_builder)) {
            $notes[] = 'Skipped ' . $path . ' — no page builder rows found.';
            continue;
        }

        $updated = false;

        foreach ($page_builder as &$row) {
            if (($row['acf_fc_layout'] ?? '') !== 'services_grid') {
                continue;
            }

            $row['use_global_service_items'] = $page_config['use_global_service_items'];
            $row['items']                    = $page_config['items'];
            // Global list always uses 4-column layout
            if ($page_config['use_global_service_items']) {
                $row['columns'] = '4';
            }
            $updated                         = true;
        }
        unset($row);

        if (! $updated) {
            $notes[] = 'Skipped ' . $path . ' — no Services Grid row found.';
            continue;
        }

        update_field('page_builder', $page_builder, $page_id);
        $notes[] = 'Updated Services Grid on ' . $path . '.';
    }

    return [
        'success' => true,
        'message' => 'Services Grid defaults and page-level overrides have been updated.',
        'notes'   => $notes,
    ];
}

function elahub_get_page_id_by_path($path)
{
    $page = get_page_by_path($path, OBJECT, 'page');

    return $page instanceof WP_Post ? (int) $page->ID : 0;
}

function elahub_seed_dalc_live_page()
{
    $notes = [];

    $config = [
        'post_title'   => 'Designing Accessible Learning Content – Live',
        'post_slug'    => 'designing-accessible-learning-content-live',
        'post_status'  => 'publish',
        'template'     => 'template-service-page.php',

        'page_icon_source' => 'master_icon',
        'page_icon_svg'    => 'DALC_Programme_Live.svg',

        'page_hero' => [
            'page_hero_badge_text'       => 'Accessibility & Learning',
            'page_hero_badge_icon'       => 'fa-solid fa-universal-access',
            'page_hero_heading'          => 'Designing Accessible Learning Content – Live',
            'page_hero_description'      => 'A moderated, cohort-based delivery of the award-winning DALC Programme, designed for organisations that want structured learning, expert support, and practical application at a higher level.',
            'page_hero_button_1_label'   => 'Join the DALC Live Waitlist',
            'page_hero_button_1_url'     => home_url('/designing-accessible-learning-content-live/#get-updates-on-dalc-live'),
            'page_hero_button_1_variant' => 'primary',
            'page_hero_button_2_label'   => 'Discuss DALC Live',
            'page_hero_button_2_url'     => home_url('/contact-elahub/'),
            'page_hero_button_2_variant' => 'primary',
            'page_hero_image_style'      => 'icon',
        ],

        'page_builder' => [
            [
                'acf_fc_layout'           => 'logo_strip',
                'section_id'              => '',
                'use_default_logo_strip'  => 1,
            ],
            [
                'acf_fc_layout'     => 'feature_section',
                'feature_variant'   => 'image_left_bg',
                'badge_text'        => 'Accessibility & Learning',
                'badge_icon_class'  => 'fa-solid fa-universal-access',
                'heading'           => 'What to expect from DALC Live',
                'body'              => '<p>The DALC Programme was created by Susi Miller after years of supporting learning practitioners who wanted to make their learning content accessible, but kept running into the same problem: plenty of standards, not enough practical guidance.</p><p>DALC closes that gap with clear, step-by-step support that shows you what good looks like, how to test properly, and how to fix issues in a way that improves the learning experience for everyone. DALC Live delivers that same programme in a moderated cohort format, so teams can apply it together with structure, support, and accountability.</p><p><strong>What’s included (DALC Live):</strong></p>',
                'image'             => 10720,
                'show_list'         => 1,
                'list_items'        => [
                    ['item' => 'Moderated delivery of the full DALC Programme in a cohort format'],
                    ['item' => 'Limited places to keep the experience high quality and well supported'],
                    ['item' => 'Monthly follow-up workshop for each module to review progress, answer questions, and troubleshoot real challenges'],
                    ['item' => 'Optional one-to-one coaching session with Susi Miller for tailored support and direction'],
                    ['item' => 'Signed copy of the Designing Accessible Learning Content book included for participants'],
                ],
                'button_label'      => 'Join the DALC Live Waitlist',
                'button_url'        => home_url('/designing-accessible-learning-content-live/#get-updates-on-dalc-live'),
            ],
            [
                'acf_fc_layout'    => 'dalc_live_waitlist',
                'section_id'       => 'get-updates-on-dalc-live',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Get updates on DALC Live',
                'body'             => '<p>DALC Live is launching in 2026 with limited places per cohort. Register your interest and we’ll share dates, cohort details, and next steps as soon as they’re confirmed.</p>',
                'ticks_label'      => 'What happens next',
                'tick_items'       => [
                    ['text' => 'You’ll receive an email when cohort dates and places are released'],
                    ['text' => 'We’ll ask a couple of quick questions to make sure DALC Live is the right fit'],
                    ['text' => 'No commitment, just updates'],
                ],
                'submit_label'        => 'Join the DALC Live Waitlist',
                'webhook_url'         => '',
                'success_message'     => 'Thank you — we\'ll be in touch shortly.',
                'notification_emails' => [],
            ],
            [
                'acf_fc_layout'    => 'faq_section',
                'use_global_faq'   => 1,
                'faq_limit'        => 0,
            ],
            [
                'acf_fc_layout'              => 'testimonials_section',
                'use_global_testimonials'    => 1,
            ],
            [
                'acf_fc_layout'                => 'contact_banner',
                'use_global_contact_banner'    => 1,
            ],
        ],
    ];

    $page = get_page_by_path($config['post_slug'], OBJECT, 'page');

    if (! $page instanceof WP_Post) {
        $page_id = wp_insert_post(
            [
                'post_type'    => 'page',
                'post_title'   => $config['post_title'],
                'post_name'    => $config['post_slug'],
                'post_status'  => $config['post_status'],
                'post_content' => '',
            ],
            true
        );

        if (is_wp_error($page_id)) {
            return [
                'success' => false,
                'message' => 'The page could not be created.',
                'notes'   => ['WordPress returned: ' . $page_id->get_error_message()],
            ];
        }

        $notes[] = 'Created page ID ' . $page_id . '.';
    } else {
        $page_id = (int) $page->ID;

        wp_update_post(
            [
                'ID'          => $page_id,
                'post_title'  => $config['post_title'],
                'post_name'   => $config['post_slug'],
                'post_status' => $config['post_status'],
            ]
        );

        $notes[] = 'Updated existing page ID ' . $page_id . '.';
    }

    update_post_meta($page_id, '_wp_page_template', $config['template']);

    update_field('page_icon_source', $config['page_icon_source'], $page_id);
    update_field('page_icon_svg', $config['page_icon_svg'], $page_id);

    foreach ($config['page_hero'] as $field_name => $value) {
        update_field($field_name, $value, $page_id);
    }

    update_field('page_builder', $config['page_builder'], $page_id);

    $notes[] = 'Template set to template-service-page.php.';
    $notes[] = 'Page icon set to DALC_Programme_Live.svg.';
    $notes[] = 'Page Builder rows written: ' . count($config['page_builder']) . '.';

    return [
        'success' => true,
        'message' => 'DALC Live page has been created and seeded.',
        'notes'   => $notes,
    ];
}

function elahub_seed_testing_and_auditing_page()
{
    $notes = [];

    $parent = get_page_by_path('accessible-elearning-services', OBJECT, 'page');
    $parent_id = $parent instanceof WP_Post ? (int) $parent->ID : 0;

    $config = [
        'post_title'   => 'Accessibility Testing and Auditing for Learning Content',
        'post_slug'    => 'elearning-accessibility-testing-and-auditing',
        'post_path'    => 'accessible-elearning-services/elearning-accessibility-testing-and-auditing',
        'post_parent'  => $parent_id,
        'post_status'  => 'publish',
        'template'     => 'template-service-page.php',

        'page_hero' => [
            'page_hero_badge_text'       => 'Accessibility & Learning',
            'page_hero_badge_icon'       => 'fa-solid fa-universal-access',
            'page_hero_heading'          => 'Accessibility Testing and Auditing for Learning Content',
            'page_hero_description'      => 'eLaHub provides eLearning accessibility testing and auditing services for organisations looking to review and remediate learning content. Our accessibility expertise and learning and development experience allows us to identify accessibility issues and provide clear, practical guidance to improve usability, effectiveness, and learner experience.',
            'page_hero_button_1_label'   => 'Book a chat',
            'page_hero_button_1_url'     => home_url('/contact-elahub/'),
            'page_hero_button_1_variant' => 'primary',
            'page_hero_button_2_label'   => '',
            'page_hero_button_2_url'     => '',
            'page_hero_button_2_variant' => 'primary',
            'page_hero_image_style'      => 'icon',
        ],

        'page_builder' => [
            [
                'acf_fc_layout'          => 'logo_strip',
                'section_id'             => '',
                'use_default_logo_strip' => 1,
            ],
            [
                'acf_fc_layout'    => 'feature_section',
                'feature_variant'  => 'image_left',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Accessibility Audits Designed for Learning Content',
                'body'             => '<p>eLaHub audits learning content with a learning practitioner’s eye, backed by deep accessibility expertise. This means the output is not just a list of technical issues. You receive clear guidance on what is happening, why it matters to learners, and what to change to improve accessibility and usability across your learning content.</p><p>We offer a range of auditing options to suit different requirements, from a quick snapshot through to a full technical audit. Wherever possible, reports include remediation guidance or workaround solutions, alongside recommendations to support more effective and inclusive learning experiences.</p>',
                'image'            => 12139,
                'show_list'        => 1,
                'list_items'       => [
                    ['item' => 'Audits designed specifically for learning content'],
                    ['item' => 'Clear, prioritised findings (what to address first, and why)'],
                    ['item' => 'Guidance to support remediation, including patterns teams can apply again'],
                    ['item' => 'Options to suit different budgets, timeframes, and levels of depth'],
                ],
                'button_label'     => 'Book a call',
                'button_url'       => home_url('/contact-elahub/'),
            ],
            [
                'acf_fc_layout'    => 'purchase_cards',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Audit options',
                'description'      => 'Our targeted audit options allow organisations to choose the level of depth that best fits their requirements.',
                'button_link'      => [
                    'title'  => 'Book a Call',
                    'url'    => home_url('/contact-elahub/'),
                    'target' => '',
                ],
                'columns'          => '3',
                'cards'            => [
                    [
                        'title'               => 'Snapshot audits',
                        'description'         => 'A cost effective option for organisations looking to gauge the accessibility of learning resources without a full auditing service.',
                        'list_items'          => [
                            ['text' => 'Tests against WCAG 2.2 Level A and AA requirements'],
                            ['text' => 'Identifies a maximum of 10 issues'],
                            ['text' => 'Issues focus on WCAG violations'],
                            ['text' => 'Includes accessibility and usability recommendations'],
                        ],
                        'button_1_label'      => 'Enquire about Snapshot audits',
                        'button_1_url'        => home_url('/contact-elahub/'),
                        'button_1_aria_label' => 'Enquire about Snapshot audits',
                    ],
                    [
                        'title'               => 'Overview audits',
                        'description'         => 'A comprehensive audit report detailing all WCAG 2.2 Level A and AA violations, with practical recommendations to support remediation.',
                        'list_items'          => [
                            ['text' => 'Reports all WCAG 2.2 Level A and AA violations'],
                            ['text' => 'Includes accessibility and usability recommendations'],
                            ['text' => 'Provides tool specific suggested solutions where relevant'],
                            ['text' => 'References authoring tool conformance reports for tool level requirements'],
                        ],
                        'button_1_label'      => 'Enquire about Overview audits',
                        'button_1_url'        => home_url('/contact-elahub/'),
                        'button_1_aria_label' => 'Enquire about Overview audits',
                    ],
                    [
                        'title'               => 'Full technical audits',
                        'description'         => 'Includes everything in an Overview audit, with additional testing for coding and mobile conformance requirements where tool conformance reporting is not available or additional assurance is required.',
                        'list_items'          => [
                            ['text' => 'Includes all features of an Overview audit'],
                            ['text' => 'Tests coding and mobile conformance WCAG requirements directly'],
                            ['text' => 'Useful where an authoring tool conformance report is not available'],
                            ['text' => 'Often used where additional WCAG assurance is required'],
                        ],
                        'button_1_label'      => 'Enquire about Full technical audits',
                        'button_1_url'        => home_url('/contact-elahub/'),
                        'button_1_aria_label' => 'Enquire about Full technical audits',
                    ],
                ],
            ],
            [
                'acf_fc_layout'    => 'feature_section',
                'feature_variant'  => 'image_right',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'The eLaHub Active Auditing Approach',
                'body'             => '<p>Susi Miller developed the eLaHub Active Auditing approach out of frustration with the way learning teams often have to rely on accessibility experts who do not have learning and development or authoring tool experience to provide auditing services. Too often this results in technical reports that focus heavily on issues practitioners have little control over.</p><p>At eLaHub, auditing is approached through the lens of learning practice as well as accessibility. This ensures reports are detailed, practical, and focused on improving accessibility while supporting the integrity of learning experiences and interactions.</p><p>Wherever possible, we identify remediation or workaround solutions. Reports also include guidance to support more effective, usable learning experiences, helping teams build confidence in improving existing materials and producing more accessible learning content in the future.</p>',
                'image'            => 12139,
                'show_list'        => 1,
                'list_items'       => [
                    ['item' => 'Auditing carried out by specialists with learning and development backgrounds'],
                    ['item' => 'Assistive technology testing supported by lived experience of disability'],
                    ['item' => 'Findings written as practical guidance, with clear examples and context'],
                    ['item' => 'Remediation and workaround suggestions included where possible'],
                    ['item' => 'Recommendations designed to be reusable across future learning content'],
                ],
                'button_label'     => 'Book a Call',
                'button_url'       => home_url('/contact-elahub/'),
            ],
            [
                'acf_fc_layout'           => 'case_studies_section',
                'use_global_case_studies' => 1,
            ],
            [
                'acf_fc_layout'           => 'testimonials_section',
                'use_global_testimonials' => 1,
            ],
            [
                'acf_fc_layout'    => 'feature_section',
                'feature_variant'  => 'image_right_bg',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Why Organisations Choose eLaHub',
                'body'             => '<p>eLaHub provides accessibility services designed specifically for learning content. Our work combines deep accessibility expertise with a learning practitioner’s perspective, so outputs are not just technical findings, but clear, practical guidance teams can use to improve real learning materials.</p><p>Whether you need a structured assessment, detailed auditing, or targeted consultancy support, you’ll receive findings that explain what is happening, why it matters to learners, and what to change to improve accessibility and usability across your content.</p>',
                'image'            => 10720,
                'show_list'        => 1,
                'list_items'       => [
                    ['item' => 'Accessibility services designed for learning content, not generic websites'],
                    ['item' => 'Clear findings that explain what’s happening and why it matters'],
                    ['item' => 'Practical guidance with patterns teams can apply again and again'],
                    ['item' => 'Options to suit different requirements, timeframes, and levels of depth'],
                    ['item' => 'Output designed to support decisions across learning, accessibility, and standards roles'],
                ],
                'button_label'     => 'Start Learning Now',
                'button_url'       => home_url('/designing-accessible-learning-content-programme/'),
            ],
            [
                'acf_fc_layout'  => 'faq_section',
                'use_global_faq' => 1,
                'faq_limit'      => 0,
            ],
            [
                'acf_fc_layout'    => 'services_grid',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Explore More Services',
                'body'             => '<p>A range of services to support organisations creating learning content, from structured assessments to auditing, testing, and targeted consultancy.</p>',
                'button_link'      => [
                    'title'  => 'Book a Call',
                    'url'    => home_url('/contact-elahub/'),
                    'target' => '',
                ],
                'columns'          => '3',
                'icon_height'      => 100,
                'items'            => [
                    [
                        'page'             => 2949,
                        'heading_override' => 'Training & Programmes',
                        'description'      => '<p>Manual auditing and practical testing of learning content using standards led approaches and assistive technology. Designed to identify barriers, document issues clearly, and support remediation planning.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 12720,
                        'heading_override' => 'Accessibility Assessments',
                        'description'      => '<p>Structured assessments designed to help organisations understand the accessibility of learning content and prioritise next steps. Includes eLa1000 at launch, with further assessment options available over time.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 2947,
                        'heading_override' => 'Consultancy and Coaching',
                        'description'      => '<p>Understanding WCAG requirements as they apply to learning content. Focuses on what teams need to know and how standards translate into practice.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 7946,
                        'heading_override' => 'Speaking and Advocacy',
                        'description'      => '<p>Talks, keynotes, and sessions designed to support organisations building understanding of accessibility and inclusive learning. Available for events, internal programmes, and learning communities.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 12720,
                        'heading_override' => 'Accessible LMS Support',
                        'description'      => '<p>Support for organisations reviewing learning platforms and LMS accessibility considerations. Includes guidance and tools designed to help teams make informed decisions.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                ],
            ],
        ],
    ];

    $page = get_page_by_path($config['post_path'], OBJECT, 'page');

    if (! $page instanceof WP_Post) {
        $page_id = wp_insert_post(
            [
                'post_type'    => 'page',
                'post_title'   => $config['post_title'],
                'post_name'    => $config['post_slug'],
                'post_parent'  => $config['post_parent'],
                'post_status'  => $config['post_status'],
                'post_content' => '',
            ],
            true
        );

        if (is_wp_error($page_id)) {
            return [
                'success' => false,
                'message' => 'The page could not be created.',
                'notes'   => ['WordPress returned: ' . $page_id->get_error_message()],
            ];
        }

        $notes[] = 'Created page ID ' . $page_id . '.';
    } else {
        $page_id = (int) $page->ID;

        wp_update_post(
            [
                'ID'          => $page_id,
                'post_title'  => $config['post_title'],
                'post_name'   => $config['post_slug'],
                'post_parent' => $config['post_parent'],
                'post_status' => $config['post_status'],
            ]
        );

        $notes[] = 'Updated existing page ID ' . $page_id . '.';
    }

    update_post_meta($page_id, '_wp_page_template', $config['template']);

    foreach ($config['page_hero'] as $field_name => $value) {
        update_field($field_name, $value, $page_id);
    }

    update_field('page_builder', $config['page_builder'], $page_id);

    $notes[] = 'Template set to template-service-page.php.';
    $notes[] = 'Page Builder rows written: ' . count($config['page_builder']) . '.';

    return [
        'success' => true,
        'message' => 'Testing and Auditing page has been created and seeded.',
        'notes'   => $notes,
    ];
}

function elahub_seed_consultancy_page()
{
    $notes = [];

    $config = [
        'post_title'   => 'Consultancy for accessible learning content',
        'post_slug'    => 'consultancy',
        'post_status'  => 'publish',
        'template'     => 'template-service-page.php',

        'page_icon_source' => 'master_icon',
        'page_icon_svg'    => 'Consultancy_Coaching.svg',

        'page_hero' => [
            'page_hero_badge_text'       => 'Accessibility & Learning',
            'page_hero_badge_icon'       => 'fa-solid fa-universal-access',
            'page_hero_heading'          => 'Consultancy for accessible learning content',
            'page_hero_description'      => 'Our consultancy service supports organisations who are committed to improving the accessibility of their learning content. We create bespoke packages that cater to specific organisational needs and can also provide consultancy support as part of our eLearning accessibility transformation programme.',
            'page_hero_button_1_label'   => 'Book a chat',
            'page_hero_button_1_url'     => home_url('/contact-elahub/'),
            'page_hero_button_1_variant' => 'primary',
            'page_hero_button_2_label'   => '',
            'page_hero_button_2_url'     => '',
            'page_hero_button_2_variant' => 'primary',
            'page_hero_image_style'      => 'full_image',
            'page_hero_image'            => 12139,
        ],

        'page_builder' => [
            [
                'acf_fc_layout'          => 'logo_strip',
                'section_id'             => '',
                'use_default_logo_strip' => 1,
            ],
            [
                'acf_fc_layout'    => 'feature_section',
                'feature_variant'  => 'image_left',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Consultancy that supports real change',
                'body'             => '<p>Our consultancy service supports organisations who are committed to improving the accessibility of their learning content. We create bespoke packages that cater to specific organisational needs and provide practical support at every stage of the journey.</p><p>Consultancy and coaching are also offered as a standard component of our eLearning accessibility transformation programme, helping organisations move beyond awareness and training towards meaningful, embedded change.</p>',
                'image'            => 12139,
                'show_list'        => 1,
                'list_items'       => [
                    ['item' => 'Tailored consultancy aligned to your organisation, learning tools, and teams'],
                    ['item' => 'Practical guidance that supports real implementation, not just compliance'],
                    ['item' => 'Flexible support that can evolve as your accessibility maturity grows'],
                ],
                'button_label'     => 'Book a call',
                'button_url'       => home_url('/contact-elahub/'),
            ],
            [
                'acf_fc_layout'    => 'icon_features',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'How we support your organisation',
                'body'             => '<p>The range of services we offer at eLaHub means we are well placed to provide high quality and effective consultancy for your eLearning accessibility needs. Our Essential, Premium and Enterprise solutions make it easy to choose the level of support you need, and we can also create fully bespoke packages.</p>',
                'button_link'      => [
                    'title'  => 'Start learning now',
                    'url'    => home_url('/designing-accessible-learning-content-programme/'),
                    'target' => '',
                ],
                'columns'          => '4',
                'items'            => [
                    [
                        'title'         => 'Joined-up support',
                        'description'   => 'Because training, testing and auditing sit alongside consultancy, support can be shaped around what you actually need, not a one-size-fits-all approach.',
                        'icon_source'   => 'font_awesome',
                        'icon_fa_class' => 'fa-solid fa-link',
                    ],
                    [
                        'title'         => 'Clear levels of support',
                        'description'   => 'Essential, Premium and Enterprise solutions give a simple starting point, with flexibility to adapt as priorities change.',
                        'icon_source'   => 'font_awesome',
                        'icon_fa_class' => 'fa-solid fa-layer-group',
                    ],
                    [
                        'title'         => 'Bespoke by default',
                        'description'   => 'Support can be tailored to organisational goals, tools, content types, and internal capability, from quick guidance through to longer term change.',
                        'icon_source'   => 'font_awesome',
                        'icon_fa_class' => 'fa-solid fa-sliders',
                    ],
                    [
                        'title'         => 'Practical and actionable',
                        'description'   => 'Recommendations are designed to be used by real teams, helping you move from intent to implementation with clear next steps.',
                        'icon_source'   => 'font_awesome',
                        'icon_fa_class' => 'fa-solid fa-list-check',
                    ],
                ],
            ],
            [
                'acf_fc_layout' => 'case_studies_section',
                'badge_text'    => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'       => 'Organisations We’ve Delivered For',
                'body'          => '<p>We deliver tailored training to organisations across the public, private, and education sectors. The examples below illustrate the types of bespoke training delivered, with content and format adapted to organisational context and priorities.</p>',
                'button_label'  => 'See Our Case Studies',
                'button_url'    => home_url('/accessible-learning-case-studies/'),
            ],
            [
                'acf_fc_layout'           => 'testimonials_section',
                'use_global_testimonials' => 1,
            ],
            [
                'acf_fc_layout'  => 'faq_section',
                'use_global_faq' => 1,
                'faq_limit'      => 0,
            ],
            [
                'acf_fc_layout'    => 'services_grid',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Explore More Services',
                'body'             => '<p>A range of services to support organisations creating learning content, from structured assessments to auditing, testing, and targeted consultancy.</p>',
                'button_link'      => [
                    'title'  => 'Book a Call',
                    'url'    => home_url('/contact-elahub/'),
                    'target' => '',
                ],
                'columns'          => '3',
                'icon_height'      => 100,
                'items'            => [
                    [
                        'page'             => 2949,
                        'heading_override' => 'Training & Programmes',
                        'description'      => '<p>Manual auditing and practical testing of learning content using standards led approaches and assistive technology. Designed to identify barriers, document issues clearly, and support remediation planning.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 12720,
                        'heading_override' => 'Accessibility Assessments',
                        'description'      => '<p>Structured assessments designed to help organisations understand the accessibility of learning content and prioritise next steps. Includes eLa1000 at launch, with further assessment options available over time.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 2947,
                        'heading_override' => 'Consultancy and Coaching',
                        'description'      => '<p>Understanding WCAG requirements as they apply to learning content. Focuses on what teams need to know and how standards translate into practice.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 7946,
                        'heading_override' => 'Speaking and Advocacy',
                        'description'      => '<p>Talks, keynotes, and sessions designed to support organisations building understanding of accessibility and inclusive learning. Available for events, internal programmes, and learning communities.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 12720,
                        'heading_override' => 'Accessible LMS Support',
                        'description'      => '<p>Support for organisations reviewing learning platforms and LMS accessibility considerations. Includes guidance and tools designed to help teams make informed decisions.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                ],
            ],
        ],
    ];

    $page = get_page_by_path($config['post_slug'], OBJECT, 'page');

    if (! $page instanceof WP_Post) {
        $page_id = wp_insert_post(
            [
                'post_type'    => 'page',
                'post_title'   => $config['post_title'],
                'post_name'    => $config['post_slug'],
                'post_status'  => $config['post_status'],
                'post_content' => '',
            ],
            true
        );

        if (is_wp_error($page_id)) {
            return [
                'success' => false,
                'message' => 'The page could not be created.',
                'notes'   => ['WordPress returned: ' . $page_id->get_error_message()],
            ];
        }

        $notes[] = 'Created page ID ' . $page_id . '.';
    } else {
        $page_id = (int) $page->ID;

        wp_update_post(
            [
                'ID'          => $page_id,
                'post_title'  => $config['post_title'],
                'post_name'   => $config['post_slug'],
                'post_status' => $config['post_status'],
            ]
        );

        $notes[] = 'Updated existing page ID ' . $page_id . '.';
    }

    update_post_meta($page_id, '_wp_page_template', $config['template']);

    update_field('page_icon_source', $config['page_icon_source'], $page_id);
    update_field('page_icon_svg', $config['page_icon_svg'], $page_id);

    foreach ($config['page_hero'] as $field_name => $value) {
        update_field($field_name, $value, $page_id);
    }

    update_field('page_builder', $config['page_builder'], $page_id);

    $notes[] = 'Template set to template-service-page.php.';
    $notes[] = 'Page icon set to Consultancy_Coaching.svg.';
    $notes[] = 'Page Builder rows written: ' . count($config['page_builder']) . '.';

    return [
        'success' => true,
        'message' => 'Consultancy page has been created and seeded.',
        'notes'   => $notes,
    ];
}

function elahub_seed_speaking_and_advocacy_page()
{
    $notes = [];

    $config = [
        'post_title'   => 'Speaking and advocacy',
        'post_slug'    => 'speaking-and-advocacy',
        'post_status'  => 'publish',
        'template'     => 'template-service-page.php',

        'page_icon_source' => 'master_icon',
        'page_icon_svg'    => 'Speaking_Advocacy.svg',

        'page_hero' => [
            'page_hero_badge_text'       => 'Accessibility & Learning',
            'page_hero_badge_icon'       => 'fa-solid fa-universal-access',
            'page_hero_heading'          => 'Speaking and advocacy',
            'page_hero_description'      => 'Susi Miller is an industry-leading expert on eLearning accessibility and a passionate advocate for digital accessibility. She is a keynote speaker and regularly delivers webinars, podcasts, panel discussions and articles for L&D and accessibility events and publications.',
            'page_hero_button_1_label'   => 'Book a chat',
            'page_hero_button_1_url'     => home_url('/contact-elahub/'),
            'page_hero_button_1_variant' => 'primary',
            'page_hero_button_2_label'   => '',
            'page_hero_button_2_url'     => '',
            'page_hero_button_2_variant' => 'primary',
            'page_hero_image_style'      => 'icon',
        ],

        'page_builder' => [
            [
                'acf_fc_layout'          => 'logo_strip',
                'section_id'             => '',
                'use_default_logo_strip' => 1,
            ],
            [
                'acf_fc_layout'    => 'feature_section',
                'feature_variant'  => 'image_left',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Meet Susi Miller',
                'body'             => '<p>Susi Miller is internationally recognised as one of the leading voices in eLearning accessibility. With over 30 years’ experience in learning and development, she has spent her career showing that accessibility is not just compliance, it is the foundation of great learning.</p><p>Through eLaHub, her work has supported 235+ organisations across 32 countries and trained 1,500+ delegates, helping teams build accessible and inclusive learning content with confidence.</p>',
                'image'            => 12139,
                'show_list'        => 0,
                'button_label'     => 'Book a chat',
                'button_url'       => home_url('/contact-elahub/'),
            ],
            [
                'acf_fc_layout'    => 'purchase_cards',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Speaking and advocacy services',
                'description'      => 'We contribute to the L&D and accessibility communities through a range of speaking, webinar and written activities, sharing practical insight into eLearning accessibility and inclusive learning design.',
                'button_link'      => [
                    'title'  => 'Book a Call',
                    'url'    => home_url('/contact-elahub/'),
                    'target' => '',
                ],
                'columns'          => '3',
                'cards'            => [
                    [
                        'title'               => 'Speaking engagements',
                        'description'         => 'Susi is regularly invited to speak at conferences and events as an advocate for eLearning accessibility, bringing clarity, context and real-world insight to diverse audiences.',
                        'list_items'          => [
                            ['text' => 'Conference keynotes'],
                            ['text' => 'Panel discussions'],
                            ['text' => 'Q&A sessions'],
                            ['text' => 'Interviews'],
                        ],
                        'button_1_label'      => 'Enquire about speaking',
                        'button_1_url'        => home_url('/contact-elahub/'),
                        'button_1_aria_label' => 'Enquire about speaking',
                    ],
                    [
                        'title'               => 'Webinars and presentations',
                        'description'         => 'Interactive webinars and presentations that explore the practical and human impact of accessibility in learning, grounded in over 30 years’ experience as a trainer.',
                        'list_items'          => [
                            ['text' => 'What new accessibility regulations mean for the L&D industry'],
                            ['text' => 'Accessibility for trainers and facilitators'],
                            ['text' => 'Busting the top 10 eLearning accessibility myths'],
                            ['text' => 'Rethinking eLearning accessibility in further and higher education'],
                        ],
                        'button_1_label'      => 'Enquire about webinars',
                        'button_1_url'        => home_url('/contact-elahub/'),
                        'button_1_aria_label' => 'Enquire about webinars',
                    ],
                    [
                        'title'               => 'Articles & thought leadership',
                        'description'         => 'For organisations working towards embedded, organisation-wide accessibility practices.',
                        'list_items'          => [
                            ['text' => 'Is accessible learning content better for everyone?'],
                            ['text' => 'How accessible eLearning promotes disability equality in the workplace'],
                            ['text' => 'How new digital accessibility regulations impact the eLearning sector'],
                            ['text' => 'Why we need more than tips’ on eLearning accessibility'],
                        ],
                        'button_1_label'      => 'Enquire about contributions',
                        'button_1_url'        => home_url('/contact-elahub/'),
                        'button_1_aria_label' => 'Enquire about contributions',
                    ],
                ],
            ],
            [
                'acf_fc_layout'    => 'feature_section',
                'feature_variant'  => 'image_left_bg',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Sharing practical eLearning accessibility expertise',
                'body'             => '<p>Our mission at eLaHub is to make learning content accessible as the default so that no one is unnecessarily excluded or denied their potential to succeed. This makes us passionate advocates for eLearning accessibility and is reflected in the speaking, webinars and articles we contribute to both the L&D and accessibility communities.</p>',
                'image'            => 12139,
                'show_list'        => 1,
                'list_items'       => [
                    ['item' => 'Human-centred sessions that bring accessibility to life for real learning teams'],
                    ['item' => 'Practical takeaways audiences can apply across tools and content types'],
                    ['item' => 'Clear explanations of what good looks like and how to get there'],
                    ['item' => 'A balanced view of accessibility, inclusion, and real-world constraints'],
                    ['item' => 'Sessions tailored to your event, audience, and goals'],
                ],
                'button_label'     => 'Book a Call',
                'button_url'       => home_url('/contact-elahub/'),
            ],
            [
                'acf_fc_layout'           => 'testimonials_section',
                'use_global_testimonials' => 1,
            ],
            [
                'acf_fc_layout'  => 'faq_section',
                'use_global_faq' => 1,
                'faq_limit'      => 0,
            ],
            [
                'acf_fc_layout'    => 'services_grid',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Explore More Services',
                'body'             => '<p>A range of services to support organisations creating learning content, from structured assessments to auditing, testing, and targeted consultancy.</p>',
                'button_link'      => [
                    'title'  => 'Book a Call',
                    'url'    => home_url('/contact-elahub/'),
                    'target' => '',
                ],
                'columns'          => '3',
                'icon_height'      => 100,
                'items'            => [
                    [
                        'page'             => 2949,
                        'heading_override' => 'Training & Programmes',
                        'description'      => '<p>Manual auditing and practical testing of learning content using standards-led approaches and assistive technology. Designed to identify barriers, document issues clearly, and support remediation planning.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 12720,
                        'heading_override' => 'Accessibility Assessments',
                        'description'      => '<p>Structured assessments designed to help organisations understand the accessibility of learning content and prioritise next steps. Includes eLa1000 at launch, with further assessment options available over time.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 2947,
                        'heading_override' => 'Consultancy and Coaching',
                        'description'      => '<p>Understanding WCAG requirements as they apply to learning content. Focuses on what teams need to know and how standards translate into practice.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 7942,
                        'heading_override' => 'Testing and Auditing',
                        'description'      => '<p>Manual auditing and practical testing of learning content using standards-led approaches and assistive technology. Designed to identify barriers, document issues clearly, and support remediation planning.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 12720,
                        'heading_override' => 'Accessible LMS Support',
                        'description'      => '<p>Support for organisations reviewing learning platforms and LMS accessibility considerations. Includes guidance and tools designed to help teams make informed decisions.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                ],
            ],
        ],
    ];

    $page = get_page_by_path($config['post_slug'], OBJECT, 'page');

    if (! $page instanceof WP_Post) {
        $page_id = wp_insert_post(
            [
                'post_type'    => 'page',
                'post_title'   => $config['post_title'],
                'post_name'    => $config['post_slug'],
                'post_status'  => $config['post_status'],
                'post_content' => '',
            ],
            true
        );

        if (is_wp_error($page_id)) {
            return [
                'success' => false,
                'message' => 'The page could not be created.',
                'notes'   => ['WordPress returned: ' . $page_id->get_error_message()],
            ];
        }

        $notes[] = 'Created page ID ' . $page_id . '.';
    } else {
        $page_id = (int) $page->ID;

        wp_update_post(
            [
                'ID'          => $page_id,
                'post_title'  => $config['post_title'],
                'post_name'   => $config['post_slug'],
                'post_status' => $config['post_status'],
            ]
        );

        $notes[] = 'Updated existing page ID ' . $page_id . '.';
    }

    update_post_meta($page_id, '_wp_page_template', $config['template']);

    update_field('page_icon_source', $config['page_icon_source'], $page_id);
    update_field('page_icon_svg', $config['page_icon_svg'], $page_id);

    foreach ($config['page_hero'] as $field_name => $value) {
        update_field($field_name, $value, $page_id);
    }

    update_field('page_builder', $config['page_builder'], $page_id);

    $notes[] = 'Template set to template-service-page.php.';
    $notes[] = 'Page icon set to Speaking_Advocacy.svg.';
    $notes[] = 'Page Builder rows written: ' . count($config['page_builder']) . '.';

    return [
        'success' => true,
        'message' => 'Speaking and advocacy page has been created and seeded.',
        'notes'   => $notes,
    ];
}

function elahub_seed_accessibility_assessments_page()
{
    $notes = [];

    $config = [
        'post_title'   => 'Accessibility Assessments',
        'post_slug'    => 'accessibility-assessments',
        'post_status'  => 'publish',
        'template'     => 'template-service-page.php',

        'page_icon_source' => 'master_icon',
        'page_icon_svg'    => 'Assessments.svg',

        'page_hero' => [
            'page_hero_badge_text'       => 'Accessibility & Learning',
            'page_hero_badge_icon'       => 'fa-solid fa-universal-access',
            'page_hero_heading'          => 'Accessibility Assessments',
            'page_hero_description'      => 'Structured assessments designed to help organisations creating and delivering learning content understand accessibility, benchmark maturity, and prioritise practical next steps.',
            'page_hero_button_1_label'   => 'Take the eLa1000 Assessment',
            'page_hero_button_1_url'     => home_url('/ela1000/'),
            'page_hero_button_1_variant' => 'primary',
            'page_hero_button_2_label'   => '',
            'page_hero_button_2_url'     => '',
            'page_hero_button_2_variant' => 'primary',
            'page_hero_image_style'      => 'icon',
        ],

        'page_builder' => [
            [
                'acf_fc_layout'          => 'logo_strip',
                'section_id'             => '',
                'use_default_logo_strip' => 1,
            ],
            [
                'acf_fc_layout'    => 'feature_section',
                'feature_variant'  => 'image_left',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'The eLa1000 Accessibility Assessment',
                'body'             => '<p>Welcome to the eLa1000 Accessibility Assessment project, led by Susi Miller – industry-leading eLearning accessibility expert, LPI Learning Professional of the Year 2025, and author of Designing Accessible Learning Content.</p><p>Taking part is straightforward. Set aside around 10–15 minutes to answer 60 targeted questions focused on learning content, learner experience, and organisational approach. In return, you’ll receive a customised eLa1000 Accessibility Assessment Report — a free, detailed assessment with tailored feedback, practical guidance, and useful resources to support improvements in accessibility, inclusivity, and strategic maturity.</p><p>This ground-breaking initiative is designed to support 1,000 organisations to evaluate and improve the accessibility of their learning content, while helping to benchmark accessibility across the Learning and Development industry. By taking part, you contribute to gathering vital evidence that helps identify systemic issues and drive meaningful, measurable change across the sector.</p>',
                'image'            => 12139,
                'show_list'        => 0,
                'button_label'     => 'Take the eLa1000 Assessment',
                'button_url'       => home_url('/ela1000/'),
            ],
            [
                'acf_fc_layout' => 'cta_section',
                'cta_items'     => [
                    [
                        'acf_fc_layout'     => 'cta_icon_features',
                        'badge_text'        => 'Accessibility & Learning',
                        'badge_icon_class'  => 'fa-solid fa-universal-access',
                        'heading'           => 'Why this assessment is unique',
                        'body'              => '<p>Unlike many assessments that oversimplify accessibility into a handful of tips, eLa1000 is designed to give organisations a meaningful benchmark. The questions are focused entirely on learning content, learner experience, and the real factors that improve accessibility and usability for everyone.</p>',
                        'button_label'      => 'Take the eLa1000 Assessment',
                        'button_url'        => home_url('/ela1000/'),
                        'button_aria_label' => 'Take the eLa1000 Assessment',
                        'columns'           => '3',
                        'items'             => [
                            [
                                'title'         => 'Evidence-based',
                                'description'   => 'Built on insights from hundreds of hours of eLaHub audits. Focused on the issues that most often undermine learning content accessibility.',
                                'icon_source'   => 'font_awesome',
                                'icon_fa_class' => 'fa-solid fa-chart-line',
                            ],
                            [
                                'title'         => 'Aligned with international standards',
                                'description'   => 'Grounded in WCAG 2.2 (Level A and AA), the international standard increasingly used across sectors and countries.',
                                'icon_source'   => 'font_awesome',
                                'icon_fa_class' => 'fa-solid fa-globe',
                            ],
                            [
                                'title'         => 'Designed for learning content',
                                'description'   => 'Focused on learning experiences, interactions, and authoring-tool realities — not a generic website checklist repurposed for L&D.',
                                'icon_source'   => 'font_awesome',
                                'icon_fa_class' => 'fa-solid fa-book-open-reader',
                            ],
                            [
                                'title'         => 'Goes beyond standards',
                                'description'   => 'Includes expert recommendations from real-world audit work, helping teams improve usability and inclusivity, not just pass requirements.',
                                'icon_source'   => 'font_awesome',
                                'icon_fa_class' => 'fa-solid fa-lightbulb',
                            ],
                            [
                                'title'         => 'Covers diverse access needs',
                                'description'   => 'Addresses vision, hearing, motor, and cognitive access needs, including common barriers for neurodivergent learners.',
                                'icon_source'   => 'font_awesome',
                                'icon_fa_class' => 'fa-solid fa-people-group',
                            ],
                            [
                                'title'         => 'Strategic, not just content-level',
                                'description'   => 'Looks at maturity, policies, and ways of working as well as content, helping organisations understand where they are and what to prioritise next.',
                                'icon_source'   => 'font_awesome',
                                'icon_fa_class' => 'fa-solid fa-compass-drafting',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'acf_fc_layout'    => 'feature_section',
                'feature_variant'  => 'image_right_bg',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Unlock your organisation’s eLa1000 accessibility insights',
                'body'             => '<p>The eLa1000 Accessibility Assessment Report doesn’t just assess your current accessibility. It equips you with practical guidance, resources, and a clear benchmark you can use to drive meaningful improvement.</p>',
                'image'            => 12139,
                'show_list'        => 1,
                'list_items'       => [
                    ['item' => 'An accurate benchmark: Assess your current maturity level and identify clear next steps.'],
                    ['item' => 'Targeted recommendations: Receive advice and resources tailored to your areas of weakness, including guidance aligned with WCAG 2.2.'],
                    ['item' => 'Enhanced engagement: Give teams a shared, motivating way to take ownership of accessibility improvements.'],
                    ['item' => 'Convenient access: Receive your report via email in an online format, with a shareable PDF version for key stakeholders.'],
                ],
                'button_label'     => 'Take the eLa1000 Assessment',
                'button_url'       => home_url('/ela1000/'),
            ],
            [
                'acf_fc_layout'    => 'icon_features',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'eLa1000 assessment categories',
                'body'             => '<p>You’ll be scored across six categories based on the eLa (eLearning accessibility) Framework. The framework was designed specifically for learning practitioners and underpins Susi Miller’s book Designing Accessible Learning Content and the accompanying Designing Accessible Learning Content Programme.</p>',
                'button_label'     => 'Take the eLa1000 Assessment',
                'button_url'       => home_url('/ela1000/'),
                'columns'          => '3',
                'items'            => [
                    [
                        'title'         => 'Resource design and tool settings',
                        'description'   => 'Ensure eLearning resources are accessible from the outset by designing with access needs in mind and using the accessibility functionality available within authoring tools.',
                        'icon_source'   => 'font_awesome',
                        'icon_fa_class' => 'fa-solid fa-sliders',
                    ],
                    [
                        'title'         => 'Text, information, instructions, and images',
                        'description'   => 'Ensure written and visual content is clear, consistent, and accessible, enabling all learners to understand and engage regardless of access needs.',
                        'icon_source'   => 'font_awesome',
                        'icon_fa_class' => 'fa-solid fa-file-lines',
                    ],
                    [
                        'title'         => 'Interactive items and assessments',
                        'description'   => 'Ensure all learners can take part in activities and assessments, with equal opportunities to demonstrate learning and achieve successful outcomes.',
                        'icon_source'   => 'font_awesome',
                        'icon_fa_class' => 'fa-solid fa-list-check',
                    ],
                    [
                        'title'         => 'Audio and video content',
                        'description'   => 'Ensure multimedia content is accessible and provides equivalent experiences for all learners, including appropriate alternatives such as captions, transcripts, and audio descriptions.',
                        'icon_source'   => 'font_awesome',
                        'icon_fa_class' => 'fa-solid fa-circle-play',
                    ],
                    [
                        'title'         => 'Moving content, keyboard, timing, and global considerations',
                        'description'   => 'Ensure learners are not distracted by unnecessary motion, that all functionality is usable via keyboard, and that time-based interactions do not disadvantage anyone.',
                        'icon_source'   => 'font_awesome',
                        'icon_fa_class' => 'fa-solid fa-keyboard',
                    ],
                    [
                        'title'         => 'Mobile, code, and strategy',
                        'description'   => 'Ensure learning content works across devices, is technically robust, and is supported by a clear accessibility strategy that drives consistent, long-term improvement.',
                        'icon_source'   => 'font_awesome',
                        'icon_fa_class' => 'fa-solid fa-mobile-screen-button',
                    ],
                ],
            ],
            [
                'acf_fc_layout'    => 'feature_section',
                'feature_variant'  => 'image_left',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Why accessible learning content matters',
                'body'             => '<p>Accessible and inclusive learning content doesn’t just remove barriers. It improves the effectiveness, reach, and longevity of learning for everyone, helping organisations deliver learning that works across roles, contexts, and changing needs.</p>',
                'image'            => 12139,
                'show_list'        => 1,
                'list_items'       => [
                    ['item' => 'Supports diverse needs and contexts: Accessible learning works across different abilities, environments, devices, and situations, making learning more usable and impactful for a broader audience.'],
                    ['item' => 'Reduces risk and supports compliance: Meeting global accessibility requirements helps organisations manage legal and reputational risk while embedding inclusive practice.'],
                    ['item' => 'Strengthens DEI commitments: Accessible learning content reinforces Diversity, Equity, and Inclusion values by ensuring all learners can participate fully.'],
                    ['item' => 'Encourages better learning design: Designing for accessibility often leads to clearer structure, stronger interaction, and more effective learning experiences.'],
                    ['item' => 'Future-proofs learning investment: Accessible content adapts more easily to evolving learner needs, technologies, and delivery models.'],
                ],
                'button_label'     => 'Take the eLa1000 Assessment',
                'button_url'       => home_url('/ela1000/'),
            ],
            [
                'acf_fc_layout'    => 'feature_section',
                'feature_variant'  => 'image_right_bg',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-leaf',
                'heading'          => 'Our sustainability commitment',
                'body'             => '<p>eLaHub is committed to reducing environmental impact alongside improving access to learning. For every purchase of the DALC Programme, a tree is planted to support global reforestation efforts.</p><p>We partner with Ecologi to offset carbon emissions, including those generated through digital services and AI technologies. Since 2024, this commitment has helped fund over 120 trees, supporting long-term environmental impact alongside inclusive learning.</p>',
                'image'            => 12139,
                'show_list'        => 0,
                'button_label'     => 'Start learning now',
                'button_url'       => home_url('/designing-accessible-learning-content-programme/'),
            ],
            [
                'acf_fc_layout'           => 'testimonials_section',
                'use_global_testimonials' => 1,
            ],
            [
                'acf_fc_layout'  => 'faq_section',
                'use_global_faq' => 1,
                'faq_limit'      => 0,
            ],
            [
                'acf_fc_layout'    => 'services_grid',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Explore More Services',
                'body'             => '<p>A range of services to support organisations creating learning content, from structured assessments to auditing, testing, and targeted consultancy.</p>',
                'button_link'      => [
                    'title'  => 'Book a Call',
                    'url'    => home_url('/contact-elahub/'),
                    'target' => '',
                ],
                'columns'          => '3',
                'icon_height'      => 100,
                'items'            => [
                    [
                        'page'             => 2949,
                        'heading_override' => 'Training & Programmes',
                        'description'      => '<p>Manual auditing and practical testing of learning content using standards-led approaches and assistive technology. Designed to identify barriers, document issues clearly, and support remediation planning.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 7942,
                        'heading_override' => 'Testing and Auditing',
                        'description'      => '<p>Manual auditing and practical testing of learning content using standards-led approaches and assistive technology. Designed to identify barriers, document issues clearly, and support remediation planning.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 2947,
                        'heading_override' => 'Consultancy and Coaching',
                        'description'      => '<p>Understanding WCAG requirements as they apply to learning content. Focuses on what teams need to know and how standards translate into practice.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 7946,
                        'heading_override' => 'Speaking and Advocacy',
                        'description'      => '<p>Talks, keynotes, and sessions designed to support organisations building understanding of accessibility and inclusive learning. Available for events, internal programmes, and learning communities.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 12720,
                        'heading_override' => 'Accessible LMS Support',
                        'description'      => '<p>Support for organisations reviewing learning platforms and LMS accessibility considerations. Includes guidance and tools designed to help teams make informed decisions.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                ],
            ],
        ],
    ];

    $page = get_page_by_path($config['post_slug'], OBJECT, 'page');

    if (! $page instanceof WP_Post) {
        $page_id = wp_insert_post(
            [
                'post_type'    => 'page',
                'post_title'   => $config['post_title'],
                'post_name'    => $config['post_slug'],
                'post_status'  => $config['post_status'],
                'post_content' => '',
            ],
            true
        );

        if (is_wp_error($page_id)) {
            return [
                'success' => false,
                'message' => 'The page could not be created.',
                'notes'   => ['WordPress returned: ' . $page_id->get_error_message()],
            ];
        }

        $notes[] = 'Created page ID ' . $page_id . '.';
    } else {
        $page_id = (int) $page->ID;

        wp_update_post(
            [
                'ID'          => $page_id,
                'post_title'  => $config['post_title'],
                'post_name'   => $config['post_slug'],
                'post_status' => $config['post_status'],
            ]
        );

        $notes[] = 'Updated existing page ID ' . $page_id . '.';
    }

    update_post_meta($page_id, '_wp_page_template', $config['template']);

    update_field('page_icon_source', $config['page_icon_source'], $page_id);
    update_field('page_icon_svg', $config['page_icon_svg'], $page_id);

    foreach ($config['page_hero'] as $field_name => $value) {
        update_field($field_name, $value, $page_id);
    }

    update_field('page_builder', $config['page_builder'], $page_id);

    $notes[] = 'Template set to template-service-page.php.';
    $notes[] = 'Page icon set to Assessments.svg.';
    $notes[] = 'Page Builder rows written: ' . count($config['page_builder']) . '.';

    return [
        'success' => true,
        'message' => 'Accessibility Assessments page has been created and seeded.',
        'notes'   => $notes,
    ];
}

function elahub_seed_showcase_module_page()
{
    $notes = [];

    $config = [
        'post_title'   => 'DALC Programme – Showcase Module',
        'post_slug'    => 'designing-accessible-learning-content-showcase-module',
        'post_status'  => 'publish',
        'template'     => 'template-service-page.php',

        'page_icon_source' => 'master_icon',
        'page_icon_svg'    => 'DALC_Showcase_Module.svg',

        'page_hero' => [
            'page_hero_badge_text'       => 'Accessibility & Learning',
            'page_hero_badge_icon'       => 'fa-solid fa-universal-access',
            'page_hero_heading'          => 'DALC Programme – Showcase Module',
            'page_hero_description'      => 'Explore a free Showcase Module from the Designing Accessible Learning Content (DALC) Programme. This short taster gives learning practitioners a genuine feel for the structure, quality, and practical approach of the full programme before committing.',
            'page_hero_button_1_label'   => 'Access the free Showcase Module',
            'page_hero_button_1_url'     => home_url('/designing-accessible-learning-content-showcase-module/#access-the-showcase-module'),
            'page_hero_button_1_variant' => 'primary',
            'page_hero_button_2_label'   => '',
            'page_hero_button_2_url'     => '',
            'page_hero_button_2_variant' => 'primary',
            'page_hero_image_style'      => 'icon',
        ],

        'page_builder' => [
            [
                'acf_fc_layout' => 'cta_section',
                'cta_items'     => [
                    [
                        'acf_fc_layout'     => 'cta_icon_features',
                        'badge_text'        => 'Accessibility & Learning',
                        'badge_icon_class'  => 'fa-solid fa-universal-access',
                        'heading'           => 'What you’ll learn in the Showcase Module',
                        'body'              => '<p>The Showcase Module gives you a practical introduction to how DALC approaches accessible learning design. Rather than listing standards, it focuses on helping you understand what good looks like, how to spot issues, and how to make meaningful improvements to real learning content.</p>',
                        'button_label'      => 'Access the free Showcase Module',
                        'button_url'        => home_url('/designing-accessible-learning-content-showcase-module/#access-the-showcase-module'),
                        'button_aria_label' => 'Access the free Showcase Module',
                        'columns'           => '3',
                        'items'             => [
                            [
                                'title'         => 'Why accessibility matters in learning',
                                'description'   => 'Understand accessibility as part of good learning design, not just compliance. Learn how accessibility affects real learners and why getting it right improves clarity, usability, and learning outcomes for everyone.',
                                'icon_source'   => 'font_awesome',
                                'icon_fa_class' => 'fa-solid fa-universal-access',
                            ],
                            [
                                'title'         => 'Designing for assistive technology',
                                'description'   => 'Learn how learners use assistive technologies and what that means for the way learning content is designed, structured, and built.',
                                'icon_source'   => 'font_awesome',
                                'icon_fa_class' => 'fa-solid fa-laptop',
                            ],
                            [
                                'title'         => 'Writing inclusive content and using images well',
                                'description'   => 'Explore how text, images, and alternative text can either support or block access. Learn what inclusive images look like and how to write alternative text that actually helps learners.',
                                'icon_source'   => 'font_awesome',
                                'icon_fa_class' => 'fa-solid fa-image',
                            ],
                            [
                                'title'         => 'Testing learning content properly',
                                'description'   => 'Get a practical introduction to accessibility testing for learning content, including what to check, how to approach testing, and how to avoid common mistakes.',
                                'icon_source'   => 'font_awesome',
                                'icon_fa_class' => 'fa-solid fa-magnifying-glass',
                            ],
                            [
                                'title'         => 'Understanding what good looks like',
                                'description'   => 'See real examples of accessible learning content in practice, so you can recognise quality and make informed decisions when reviewing or approving content.',
                                'icon_source'   => 'font_awesome',
                                'icon_fa_class' => 'fa-solid fa-circle-check',
                            ],
                            [
                                'title'         => 'Knowing your next steps',
                                'description'   => 'Finish the Showcase Module with a clear understanding of how the full DALC Programme is structured and what you’d work through next if you continue.',
                                'icon_source'   => 'font_awesome',
                                'icon_fa_class' => 'fa-solid fa-arrow-right',
                            ],
                        ],
                    ],
                    [
                        'acf_fc_layout'     => 'cta_quotes',
                        'heading'           => 'What organisations say about building capability with the DALC Programme',
                        'button_label'      => 'See all testimonials',
                        'button_url'        => home_url('/testimonials/'),
                        'button_aria_label' => 'See all testimonials',
                        'quote_source'      => 'organisations',
                    ],
                    [
                        'acf_fc_layout'     => 'cta_quotes',
                        'heading'           => 'What learning practitioners say about the DALC Programme',
                        'button_label'      => 'See all testimonials',
                        'button_url'        => home_url('/testimonials/'),
                        'button_aria_label' => 'See all testimonials',
                        'quote_source'      => 'practitioners',
                    ],
                    [
                        'acf_fc_layout'               => 'cta_logo_strip',
                        'use_default_dark_logo_strip' => 1,
                    ],
                ],
            ],
            [
                'acf_fc_layout'     => 'feature_section',
                'feature_variant'   => 'image_left_bg',
                'badge_text'        => 'Accessibility & Learning',
                'badge_icon_class'  => 'fa-solid fa-universal-access',
                'heading'           => 'Turning Accessibility Standards Into Practical Action',
                'body'              => '<p>The DALC Programme was created by Susi Miller after years of supporting learning practitioners who wanted to make their learning content accessible, but kept running into the same problem: plenty of standards, not enough practical guidance.</p><p>DALC closes that gap with clear, step-by-step support that shows you what good looks like, how to test properly, and how to fix issues in a way that improves the learning experience for everyone.</p>',
                'image'             => 10720,
                'show_list'         => 1,
                'list_items'        => [
                    ['item' => 'Because WCAG can be hard to apply to learning content without examples, context, and a practical framework.'],
                    ['item' => 'Because most training explains what the standards say, but not how to confidently test, fix, and improve real resources.'],
                    ['item' => 'Because accessibility is about learners, not just compliance, and getting it right improves clarity, usability, and outcomes for everyone.'],
                    ['item' => 'Because learning teams need something that works with real tools, real constraints, and real organisational pressure.'],
                ],
                'button_label'      => 'Access the free Showcase Module',
                'button_url'        => home_url('/designing-accessible-learning-content-showcase-module/#access-the-showcase-module'),
            ],
            [
                'acf_fc_layout'    => 'showcase_module_signup',
                'section_id'       => 'access-the-showcase-module',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'How to access the Showcase Module',
                'body'             => '<p>Getting started is quick and straightforward. Once you sign up, you’ll receive everything you need by email.</p>',
                'numbered_items'   => [
                    [
                        'title' => 'Sign up using the form on this page',
                        'text'  => 'Complete the short sign up form and submit your details.',
                    ],
                    [
                        'title' => 'Check your email for access details',
                        'text'  => 'You’ll receive an email with your login information and a link to access the Showcase Module.',
                    ],
                    [
                        'title' => 'Start learning straight away',
                        'text'  => 'Log in and work through the Showcase Module at your own pace. You’ll have 14 days of access to explore the content.',
                    ],
                ],
                'submit_label'        => 'Access the free Showcase Module',
                'webhook_url'         => '',
                'success_message'     => 'Thank you — we\'ll be in touch shortly.',
                'notification_emails' => [],
            ],
            [
                'acf_fc_layout'  => 'faq_section',
                'use_global_faq' => 1,
                'faq_limit'      => 0,
            ],
            [
                'acf_fc_layout'           => 'testimonials_section',
                'use_global_testimonials' => 1,
            ],
        ],
    ];

    $page = get_page_by_path($config['post_slug'], OBJECT, 'page');

    if (! $page instanceof WP_Post) {
        $page_id = wp_insert_post(
            [
                'post_type'    => 'page',
                'post_title'   => $config['post_title'],
                'post_name'    => $config['post_slug'],
                'post_status'  => $config['post_status'],
                'post_content' => '',
            ],
            true
        );

        if (is_wp_error($page_id)) {
            return [
                'success' => false,
                'message' => 'The page could not be created.',
                'notes'   => ['WordPress returned: ' . $page_id->get_error_message()],
            ];
        }

        $notes[] = 'Created page ID ' . $page_id . '.';
    } else {
        $page_id = (int) $page->ID;

        wp_update_post(
            [
                'ID'          => $page_id,
                'post_title'  => $config['post_title'],
                'post_name'   => $config['post_slug'],
                'post_status' => $config['post_status'],
            ]
        );

        $notes[] = 'Updated existing page ID ' . $page_id . '.';
    }

    update_post_meta($page_id, '_wp_page_template', $config['template']);

    update_field('page_icon_source', $config['page_icon_source'], $page_id);
    update_field('page_icon_svg', $config['page_icon_svg'], $page_id);

    foreach ($config['page_hero'] as $field_name => $value) {
        update_field($field_name, $value, $page_id);
    }

    update_field('page_builder', $config['page_builder'], $page_id);

    $notes[] = 'Template set to template-service-page.php.';
    $notes[] = 'Page icon set to DALC_Showcase_Module.svg.';
    $notes[] = 'Page Builder rows written: ' . count($config['page_builder']) . '.';

    return [
        'success' => true,
        'message' => 'DALC Showcase Module page has been created and seeded.',
        'notes'   => $notes,
    ];
}

function elahub_seed_short_courses_page()
{
    $notes = [];

    $config = [
        'post_title'   => 'Short Courses',
        'post_slug'    => 'short-courses',
        'post_status'  => 'publish',
        'template'     => 'template-service-page.php',

        'page_icon_source' => 'master_icon',
        'page_icon_svg'    => 'Short_Courses.svg',

        'page_hero' => [
            'page_hero_badge_text'       => 'Accessibility & Learning',
            'page_hero_badge_icon'       => 'fa-solid fa-universal-access',
            'page_hero_heading'          => 'Short Courses',
            'page_hero_description'      => 'Practical, focused courses designed to help learning teams improve accessibility in specific areas. Short Courses are launching in 2026. Join the waitlist to get updates on upcoming topics and early access when places are released.',
            'page_hero_button_1_label'   => 'Join the Short Courses Waitlist',
            'page_hero_button_1_url'     => home_url('/short-courses/#get-updates-on-short-courses'),
            'page_hero_button_1_variant' => 'primary',
            'page_hero_button_2_label'   => 'Discuss short courses',
            'page_hero_button_2_url'     => home_url('/contact-elahub/'),
            'page_hero_button_2_variant' => 'primary',
            'page_hero_image_style'      => 'icon',
        ],

        'page_builder' => [
            [
                'acf_fc_layout'          => 'logo_strip',
                'section_id'             => '',
                'use_default_logo_strip' => 1,
            ],
            [
                'acf_fc_layout'    => 'feature_section',
                'feature_variant'  => 'image_left_bg',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'What to expect from Short Courses',
                'body'             => '<p>Short Courses are focused, practical training options designed to help learning teams improve accessibility in a specific area without committing to the full DALC Programme. Each course is built around real examples, clear guidance, and actions you can apply immediately to your learning content.</p><p><strong>What you can expect</strong></p>',
                'image'            => 12139,
                'show_list'        => 1,
                'list_items'       => [
                    ['item' => 'Focused topics that go deep on one area of accessible learning design at a time'],
                    ['item' => 'Practical, example led learning that shows what good looks like and how to apply it'],
                    ['item' => 'Clear outcomes so you know what you’ll be able to do by the end'],
                    ['item' => 'Built for real constraints: tools, timelines, stakeholders, and organisational pressures'],
                    ['item' => 'High quality delivery designed for professional learning teams (not generic compliance training)'],
                ],
                'button_label'     => 'Join the Short Courses Waitlist',
                'button_url'       => home_url('/short-courses/#get-updates-on-short-courses'),
            ],
            [
                'acf_fc_layout'    => 'short_courses_waitlist',
                'section_id'       => 'get-updates-on-short-courses',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Get updates on Short Courses',
                'body'             => '<p>Short Courses are launching in 2026. Register your interest and we’ll share upcoming topics, formats, and dates as soon as they’re confirmed.</p>',
                'ticks_label'      => 'What happens next',
                'tick_items'       => [
                    ['text' => 'We’ll email you when new short courses are announced'],
                    ['text' => 'You’ll get early access to dates and availability when places are released'],
                    ['text' => 'No commitment, just updates'],
                ],
                'submit_label'        => 'Join the Short Courses Waitlist',
                'webhook_url'         => '',
                'success_message'     => 'Thank you — we\'ll be in touch shortly.',
                'notification_emails' => [],
            ],
            [
                'acf_fc_layout'           => 'testimonials_section',
                'use_global_testimonials' => 1,
            ],
            [
                'acf_fc_layout'    => 'services_grid',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Explore Our Services',
                'body'             => '<p>A range of services to support organisations creating learning content, from structured assessments to auditing, testing, and targeted consultancy.</p>',
                'button_link'      => [
                    'title'  => 'Book a Call',
                    'url'    => home_url('/contact-elahub/'),
                    'target' => '',
                ],
                'columns'          => '3',
                'icon_height'      => 100,
                'items'            => [
                    [
                        'page'             => 7942,
                        'heading_override' => 'Testing and Auditing',
                        'description'      => '<p>Manual auditing and practical testing of learning content using standards led approaches and assistive technology. Designed to identify barriers, document issues clearly, and support remediation planning.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 12720,
                        'heading_override' => 'Accessibility Assessments',
                        'description'      => '<p>Structured assessments designed to help organisations understand the accessibility of learning content and prioritise next steps. Includes eLa1000 at launch, with further assessment options available over time.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 2947,
                        'heading_override' => 'Consultancy and Coaching',
                        'description'      => '<p>Understanding WCAG requirements as they apply to learning content. Focuses on what teams need to know and how standards translate into practice.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 7946,
                        'heading_override' => 'Speaking and Advocacy',
                        'description'      => '<p>Talks, keynotes, and sessions designed to support organisations building understanding of accessibility and inclusive learning. Available for events, internal programmes, and learning communities.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 2949,
                        'heading_override' => 'Training & Programmes',
                        'description'      => '<p>Structured training and programmes designed to help learning teams build capability in accessible learning design. Includes the DALC Programme, short courses, workshops, and tailored training options.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                ],
            ],
        ],
    ];

    $page = get_page_by_path($config['post_slug'], OBJECT, 'page');

    if (! $page instanceof WP_Post) {
        $page_id = wp_insert_post(
            [
                'post_type'    => 'page',
                'post_title'   => $config['post_title'],
                'post_name'    => $config['post_slug'],
                'post_status'  => $config['post_status'],
                'post_content' => '',
            ],
            true
        );

        if (is_wp_error($page_id)) {
            return [
                'success' => false,
                'message' => 'The page could not be created.',
                'notes'   => ['WordPress returned: ' . $page_id->get_error_message()],
            ];
        }

        $notes[] = 'Created page ID ' . $page_id . '.';
    } else {
        $page_id = (int) $page->ID;

        wp_update_post(
            [
                'ID'          => $page_id,
                'post_title'  => $config['post_title'],
                'post_name'   => $config['post_slug'],
                'post_status' => $config['post_status'],
            ]
        );

        $notes[] = 'Updated existing page ID ' . $page_id . '.';
    }

    update_post_meta($page_id, '_wp_page_template', $config['template']);

    update_field('page_icon_source', $config['page_icon_source'], $page_id);
    update_field('page_icon_svg', $config['page_icon_svg'], $page_id);

    foreach ($config['page_hero'] as $field_name => $value) {
        update_field($field_name, $value, $page_id);
    }

    update_field('page_builder', $config['page_builder'], $page_id);

    $notes[] = 'Template set to template-service-page.php.';
    $notes[] = 'Page icon set to Short_Courses.svg.';
    $notes[] = 'Page Builder rows written: ' . count($config['page_builder']) . '.';

    return [
        'success' => true,
        'message' => 'Short Courses page has been created and seeded.',
        'notes'   => $notes,
    ];
}

function elahub_seed_tailored_training_page()
{
    $notes = [];

    $config = [
        'post_title'   => 'Tailored Training for Accessible Learning',
        'post_slug'    => 'tailored-training',
        'post_status'  => 'publish',
        'template'     => 'template-service-page.php',

        'page_icon_source' => 'master_icon',
        'page_icon_svg'    => 'Tailored_Training.svg',

        'page_hero' => [
            'page_hero_badge_text'       => 'Accessibility & Learning',
            'page_hero_badge_icon'       => 'fa-solid fa-universal-access',
            'page_hero_heading'          => 'Tailored Training for Accessible Learning',
            'page_hero_description'      => 'Bespoke training sessions designed around your organisation, your tools, and the learning content you create. Tailored delivery ensures training is relevant, practical, and aligned with your teams and priorities.',
            'page_hero_button_1_label'   => 'Book a chat',
            'page_hero_button_1_url'     => home_url('/contact-elahub/'),
            'page_hero_button_1_variant' => 'primary',
            'page_hero_button_2_label'   => '',
            'page_hero_button_2_url'     => '',
            'page_hero_button_2_variant' => 'primary',
            'page_hero_image_style'      => 'icon',
        ],

        'page_builder' => [
            [
                'acf_fc_layout'          => 'logo_strip',
                'section_id'             => '',
                'use_default_logo_strip' => 1,
            ],
            [
                'acf_fc_layout' => 'cta_section',
                'cta_items'     => [
                    [
                        'acf_fc_layout'     => 'cta_icon_features',
                        'badge_text'        => 'Accessibility & Learning',
                        'badge_icon_class'  => 'fa-solid fa-universal-access',
                        'heading'           => 'What We Can Cover',
                        'body'              => '<p>Tailored Training is designed around your organisation to reflect your tools, content, and ways of working. In practice, this often means focusing on specific areas of learning accessibility relevant to your teams, such as:</p>',
                        'button_label'      => 'Book a chat',
                        'button_url'        => home_url('/contact-elahub/'),
                        'button_aria_label' => 'Book a chat',
                        'columns'           => '3',
                        'items'             => [
                            [
                                'title'         => 'Accessible learning content',
                                'description'   => 'How to design and structure learning content so it is usable by more people. Covers layout, interaction, and learner experience considerations.',
                                'icon_source'   => 'font_awesome',
                                'icon_fa_class' => 'fa-solid fa-universal-access',
                            ],
                            [
                                'title'         => 'Authoring tools and platforms',
                                'description'   => 'Creating accessible learning content within common authoring tools and platforms. Includes practical guidance on limitations, settings, and workarounds.',
                                'icon_source'   => 'font_awesome',
                                'icon_fa_class' => 'fa-solid fa-laptop',
                            ],
                            [
                                'title'         => 'Accessibility standards in a learning context',
                                'description'   => 'Understanding WCAG requirements as they apply to learning content. Focuses on what teams need to know and how standards translate into practice.',
                                'icon_source'   => 'font_awesome',
                                'icon_fa_class' => 'fa-solid fa-scale-balanced',
                            ],
                            [
                                'title'         => 'Testing learning content for accessibility',
                                'description'   => 'How to review and test learning content using practical checks and assistive technology. Covers what to test, how to record issues, and where to focus effort.',
                                'icon_source'   => 'font_awesome',
                                'icon_fa_class' => 'fa-solid fa-magnifying-glass',
                            ],
                            [
                                'title'         => 'Inclusive media and interaction',
                                'description'   => 'Designing accessible video, audio, visuals, and interactive elements. Includes captions, transcripts, alternative text, and accessible interaction patterns.',
                                'icon_source'   => 'font_awesome',
                                'icon_fa_class' => 'fa-solid fa-photo-film',
                            ],
                            [
                                'title'         => 'Accessibility awareness for teams',
                                'description'   => 'Building shared understanding of disability, access needs, and common barriers in digital learning. Suitable for mixed roles and experience levels.',
                                'icon_source'   => 'font_awesome',
                                'icon_fa_class' => 'fa-solid fa-people-group',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'acf_fc_layout'    => 'feature_section',
                'feature_variant'  => 'image_left',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Training delivery approaches',
                'body'             => '<p>Tailored Training can be delivered in a range of formats depending on your organisation, teams, and priorities. Delivery options are discussed during scoping to ensure training fits your context, time constraints, and preferred ways of working.</p>',
                'image'            => 12139,
                'show_list'        => 1,
                'list_items'       => [
                    ['item' => 'Virtual instructor led training, delivered live to remote or distributed teams'],
                    ['item' => 'In person training, delivered on site where appropriate'],
                    ['item' => 'Single sessions or multi session programmes, depending on depth and scope'],
                    ['item' => 'Sessions for mixed roles or specific teams, such as designers, authors, or reviewers'],
                    ['item' => 'Follow up or reinforcement sessions, to support implementation after initial delivery'],
                ],
                'button_label'     => 'Book a Call',
                'button_url'       => home_url('/contact-elahub/'),
            ],
            [
                'acf_fc_layout' => 'case_studies_section',
                'badge_text'    => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'       => 'Organisations We’ve Delivered For',
                'body'          => '<p>We deliver tailored training to organisations across the public, private, and education sectors. The examples below illustrate the types of bespoke training delivered, with content and format adapted to organisational context and priorities.</p>',
                'button_label'  => 'See Our Case Studies',
                'button_url'    => home_url('/accessible-learning-case-studies/'),
            ],
            [
                'acf_fc_layout'           => 'testimonials_section',
                'use_global_testimonials' => 1,
            ],
            [
                'acf_fc_layout'  => 'faq_section',
                'use_global_faq' => 1,
                'faq_limit'      => 0,
            ],
            [
                'acf_fc_layout'    => 'services_grid',
                'badge_text'       => 'Accessibility & Learning',
                'badge_icon_class' => 'fa-solid fa-universal-access',
                'heading'          => 'Explore Our Services',
                'body'             => '<p>A range of services to support organisations creating learning content, from structured assessments to auditing, testing, and targeted consultancy.</p>',
                'button_link'      => [
                    'title'  => 'Book a Call',
                    'url'    => home_url('/contact-elahub/'),
                    'target' => '',
                ],
                'columns'          => '3',
                'icon_height'      => 100,
                'items'            => [
                    [
                        'page'             => 7942,
                        'heading_override' => 'Testing and Auditing',
                        'description'      => '<p>Manual auditing and practical testing of learning content using standards led approaches and assistive technology. Designed to identify barriers, document issues clearly, and support remediation planning.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 12720,
                        'heading_override' => 'Accessibility Assessments',
                        'description'      => '<p>Structured assessments designed to help organisations understand the accessibility of learning content and prioritise next steps. Includes eLa1000 at launch, with further assessment options available over time.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 2947,
                        'heading_override' => 'Consultancy and Coaching',
                        'description'      => '<p>Understanding WCAG requirements as they apply to learning content. Focuses on what teams need to know and how standards translate into practice.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 7946,
                        'heading_override' => 'Speaking and Advocacy',
                        'description'      => '<p>Talks, keynotes, and sessions designed to support organisations building understanding of accessibility and inclusive learning. Available for events, internal programmes, and learning communities.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                    [
                        'page'             => 2949,
                        'heading_override' => 'Training & Programmes',
                        'description'      => '<p>Structured training and programmes designed to help learning teams build capability in accessible learning design. Includes the DALC Programme, short courses, workshops, and tailored training options.</p>',
                        'icon_type'        => 'page_icon',
                    ],
                ],
            ],
        ],
    ];

    $page = get_page_by_path($config['post_slug'], OBJECT, 'page');

    if (! $page instanceof WP_Post) {
        $page_id = wp_insert_post(
            [
                'post_type'    => 'page',
                'post_title'   => $config['post_title'],
                'post_name'    => $config['post_slug'],
                'post_status'  => $config['post_status'],
                'post_content' => '',
            ],
            true
        );

        if (is_wp_error($page_id)) {
            return [
                'success' => false,
                'message' => 'The page could not be created.',
                'notes'   => ['WordPress returned: ' . $page_id->get_error_message()],
            ];
        }

        $notes[] = 'Created page ID ' . $page_id . '.';
    } else {
        $page_id = (int) $page->ID;

        wp_update_post(
            [
                'ID'          => $page_id,
                'post_title'  => $config['post_title'],
                'post_name'   => $config['post_slug'],
                'post_status' => $config['post_status'],
            ]
        );

        $notes[] = 'Updated existing page ID ' . $page_id . '.';
    }

    update_post_meta($page_id, '_wp_page_template', $config['template']);

    update_field('page_icon_source', $config['page_icon_source'], $page_id);
    update_field('page_icon_svg', $config['page_icon_svg'], $page_id);

    foreach ($config['page_hero'] as $field_name => $value) {
        update_field($field_name, $value, $page_id);
    }

    update_field('page_builder', $config['page_builder'], $page_id);

    $notes[] = 'Template set to template-service-page.php.';
    $notes[] = 'Page icon set to Tailored_Training.svg.';
    $notes[] = 'Page Builder rows written: ' . count($config['page_builder']) . '.';

    return [
        'success' => true,
        'message' => 'Tailored Training page has been created and seeded.',
        'notes'   => $notes,
    ];
}
