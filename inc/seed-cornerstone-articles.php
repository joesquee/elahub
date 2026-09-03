<?php
/**
 * Seed Cornerstone Articles (Guides)
 *
 * Creates three long-form cornerstone articles and their related FAQ posts.
 * Trigger with:
 *   /guides/?elahub_seed_cornerstone_articles=1
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
    exit;
}

function elahub_seed_cornerstone_articles_from_url()
{
    if (! isset($_GET['elahub_seed_cornerstone_articles']) || '1' !== (string) $_GET['elahub_seed_cornerstone_articles']) {
        return;
    }

    if (! current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to run this seed.', 'elahub'));
    }

    $results = elahub_seed_cornerstone_articles();

    nocache_headers();
    header('Content-Type: text/html; charset=' . get_bloginfo('charset'));

    echo '<div style="max-width:980px;margin:40px auto;padding:24px;font:16px/1.6 -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif">';
    echo '<h1 style="margin-top:0">Cornerstone article seed results</h1>';
    echo '<ul>';

    foreach ($results as $result) {
        echo '<li><strong>' . esc_html($result['title']) . ':</strong> ' . esc_html($result['message']) . '</li>';

        if (! empty($result['notes'])) {
            echo '<ul>';
            foreach ($result['notes'] as $note) {
                echo '<li>' . esc_html($note) . '</li>';
            }
            echo '</ul>';
        }
    }

    echo '</ul>';
    echo '<p><a href="' . esc_url(home_url('/guides/')) . '">View guides archive</a></p>';
    echo '</div>';
    exit;
}
add_action('init', 'elahub_seed_cornerstone_articles_from_url');

function elahub_seed_cornerstone_articles()
{
    $default_image_id = 12139;

    $guides = [
        [
            'title'         => 'What Accessible Learning Content Actually Means',
            'slug'          => 'what-accessible-learning-content-actually-means',
            'excerpt'       => 'A practical guide to what accessible learning content means, why it matters, and where teams should focus first.',
            'summary'       => "Accessible learning content is often reduced to a short checklist or a compliance task. In practice, it is much broader than that.\n\nThis guide explains what accessible learning content actually means in a learning context, why it matters for organisations and learners, and how teams can start making better decisions without feeling overwhelmed.",
            'last_updated'  => '20260410',
            'read_time'     => '12 min read',
            'category_name' => 'Foundations',
            'category_slug' => 'foundations',
            'content'       => implode("\n\n", [
                '<h2>Why this matters</h2><p>Accessible learning content is not just about avoiding problems. It is about making learning usable, understandable and complete for more people. That includes disabled learners, but it also includes people working in noisy places, people using mobile devices, people with temporary injuries, and people who are simply under pressure.</p><p>When accessibility is treated as part of learning quality, teams often create clearer, more consistent and more effective content overall. That is why accessible learning content should not be seen as a bolt-on. It is part of what good learning looks like.</p>',
                '<h2>Accessible does not mean basic</h2><p>One of the most common myths is that accessible learning content has to be plain, restrictive or less engaging. That is not true. Accessible content can still be rich, branded and interactive. The difference is that it has been designed so more people can perceive it, operate it and understand it.</p><p>Good accessibility work improves the learner experience rather than stripping it back. It asks better questions about structure, clarity, media choices and interaction patterns.</p>',
                '<h2>What teams should look at first</h2><p>Most organisations do not need to solve everything at once. A better approach is to focus on the areas that have the biggest impact on learner access.</p><h3>Structure and layout</h3><p>Clear headings, consistent layout and logical reading order make content easier to follow. They also help screen reader users and anyone scanning the page quickly.</p><h3>Text and instructions</h3><p>Instructions should be specific and easy to understand. Link text, button labels and task wording should tell the learner what to do next without ambiguity.</p><h3>Media and alternatives</h3><p>Videos, audio and images often create barriers when alternatives are missing. Captions, transcripts and meaningful alternative text are not extras. They are often essential.</p>',
                '<h2>Accessibility in a learning context</h2><p>Learning content has its own challenges. Teams are not just publishing information. They are creating experiences that often include activities, assessments, interactions, progress tracking and media. That means accessibility needs to be considered across the full learner journey, not just on a slide-by-slide basis.</p><p>For example, a module may look visually tidy but still create barriers if keyboard users cannot complete an interaction, if instructions are unclear, or if timed tasks create unnecessary pressure.</p>',
                '<h2>What good looks like in practice</h2><p>Good accessible learning content is intentional. It uses structure well. It avoids unnecessary friction. It gives learners equivalent ways to access information and complete tasks. It also reflects the realities of the tools being used, which is why practical decision-making matters.</p><p>Teams do not need perfection on day one. What matters is building a stronger baseline, knowing how to spot common issues, and improving content in a way that can be repeated consistently.</p>',
                '<h2>Where to start as a team</h2><p>Start with awareness, then move into review and action. Choose a small number of real learning assets. Look at them with accessibility in mind. Identify repeated issues. Then decide what should change in templates, processes and team habits.</p><p>This is often where organisations make the biggest progress: not by chasing isolated fixes, but by building a more joined-up approach to how learning content is designed and reviewed.</p>',
            ]),
            'faqs'          => [
                [
                    'question' => 'Is accessible learning content just about disability?',
                    'slug'     => 'faq-accessible-learning-content-disability',
                    'answer'   => '<p>No. Disability is a core part of accessibility, but accessible learning content also supports people in a wide range of real-world situations, including temporary and situational barriers.</p>',
                ],
                [
                    'question' => 'Does accessible content have to be less interactive?',
                    'slug'     => 'faq-accessible-learning-content-interactive',
                    'answer'   => '<p>No. Interactive content can still be accessible. The key is making sure learners can understand, operate and complete the interaction in different ways where needed.</p>',
                ],
                [
                    'question' => 'What should teams improve first?',
                    'slug'     => 'faq-accessible-learning-content-first-steps',
                    'answer'   => '<p>Start with structure, clarity, media alternatives and common interaction barriers. Focus first on changes that improve learner access across multiple pieces of content.</p>',
                ],
            ],
        ],
        [
            'title'         => 'WCAG for eLearning: What Teams Actually Need to Know',
            'slug'          => 'wcag-for-elearning-what-teams-actually-need-to-know',
            'excerpt'       => 'A straightforward guide to how WCAG applies to eLearning and what learning teams should focus on in practice.',
            'summary'       => "WCAG is often referenced in conversations about digital accessibility, but many learning teams are left unsure how it applies to eLearning specifically.\n\nThis guide explains what WCAG is, where it is useful, and how teams can use it in a practical way when creating and reviewing learning content.",
            'last_updated'  => '20260410',
            'read_time'     => '14 min read',
            'category_name' => 'Standards & Practice',
            'category_slug' => 'standards-and-practice',
            'content'       => implode("\n\n", [
                '<h2>What WCAG is for</h2><p>WCAG stands for the Web Content Accessibility Guidelines. It provides an internationally recognised framework for making digital content more accessible. For learning teams, it is useful because it gives a shared reference point for what accessible digital content should support.</p><p>That said, WCAG was not written purely for eLearning. Learning content often includes activities, assessments and tool-specific interactions that need interpretation in context.</p>',
                '<h2>Why teams often find WCAG difficult</h2><p>Many teams first encounter WCAG through legal or compliance conversations. That can make it feel abstract, technical and disconnected from day-to-day content work. The problem is not WCAG itself. The problem is often how it is introduced.</p><p>Teams usually need help translating the guidelines into questions like: What does this mean for a Storyline interaction? What does this mean for a quiz? What does this mean for a video-based module?</p>',
                '<h2>How WCAG applies to learning content</h2><p>WCAG helps teams think about whether content is perceivable, operable, understandable and robust. In a learning context, that means asking whether learners can access the information, use the interface, understand the instructions and complete the required tasks.</p><h3>Perceivable</h3><p>Can learners access the information through more than one sense where needed? Are captions, transcripts, alternative text and clear contrast in place?</p><h3>Operable</h3><p>Can learners use the content with a keyboard? Are focus states visible? Do interactions avoid traps or unnecessary timing barriers?</p><h3>Understandable</h3><p>Are labels, instructions and navigation patterns clear? Does the learner know what is expected and what happens next?</p><h3>Robust</h3><p>Is the content technically reliable enough to work with assistive technology and across different environments?</p>',
                '<h2>What teams should pay particular attention to</h2><p>Some WCAG issues show up repeatedly in learning content. These are often the most useful starting points.</p><ul><li>Meaningful heading structure and consistent reading order</li><li>Clear button labels and instructions</li><li>Captions and transcripts for media</li><li>Keyboard accessibility for interactions</li><li>Focus visibility and logical focus order</li><li>Colour contrast and use of colour alone</li><li>Avoiding inaccessible drag-and-drop or timed interactions without alternatives</li></ul>',
                '<h2>WCAG is not the whole story</h2><p>WCAG is important, but it is not the only thing that matters. Teams also need to think about usability, cognitive load, clarity and the realities of learning design. A module may technically pass many checks and still be hard to use in practice.</p><p>That is why strong accessibility work combines standards knowledge with practical learning design judgement.</p>',
                '<h2>How to use WCAG without overwhelming teams</h2><p>Use WCAG as a framework, not as a wall of jargon. Focus on recurring issues that matter most to learners. Build internal examples. Connect the standard to the tools and content patterns your teams actually use.</p><p>Once teams can see what WCAG looks like in context, it becomes much easier to work with. The goal is not for everyone to become a standards expert. The goal is to make better decisions, more consistently.</p>',
            ]),
            'faqs'          => [
                [
                    'question' => 'Does WCAG apply to eLearning?',
                    'slug'     => 'faq-wcag-apply-to-elearning',
                    'answer'   => '<p>Yes. WCAG is widely used as the core accessibility standard for digital content, including eLearning. It still needs practical interpretation in a learning context.</p>',
                ],
                [
                    'question' => 'Do learning teams need to memorise WCAG?',
                    'slug'     => 'faq-memorise-wcag',
                    'answer'   => '<p>No. Most teams do not need to memorise the guidelines. They need practical ways to apply the most relevant requirements to the content and tools they use.</p>',
                ],
                [
                    'question' => 'Is meeting WCAG enough on its own?',
                    'slug'     => 'faq-wcag-enough',
                    'answer'   => '<p>Not always. WCAG is essential, but accessible learning also depends on clarity, usability and good learning design decisions.</p>',
                ],
            ],
        ],
        [
            'title'         => 'How to Test eLearning for Accessibility Without Getting Lost',
            'slug'          => 'how-to-test-elearning-for-accessibility-without-getting-lost',
            'excerpt'       => 'A practical guide to testing eLearning for accessibility, including what to review first and how to keep the process manageable.',
            'summary'       => "Accessibility testing can feel overwhelming when teams are unsure where to begin or try to test everything at once.\n\nThis guide explains a practical approach to reviewing eLearning for accessibility, including what to look for, how to prioritise, and how to build confidence through real testing rather than assumptions.",
            'last_updated'  => '20260410',
            'read_time'     => '13 min read',
            'category_name' => 'Testing & Review',
            'category_slug' => 'testing-and-review',
            'content'       => implode("\n\n", [
                '<h2>Why testing matters</h2><p>It is easy to assume learning content is accessible because it looks tidy, uses a familiar template or has been through internal review. Accessibility testing helps teams move beyond assumption. It shows what the learner experience is actually like and where the barriers really are.</p><p>Good testing is not about catching people out. It is about making problems visible early enough to improve outcomes.</p>',
                '<h2>Start with a realistic scope</h2><p>One of the biggest mistakes teams make is trying to test everything in one go. A better approach is to choose a representative sample. That might be a module with interactions, a video-based resource, an assessment and a page with downloadable content.</p><p>The aim is to understand the patterns in your content, not just the issues in one asset.</p>',
                '<h2>What to check first</h2><p>Testing becomes much more manageable when you break it into core areas.</p><h3>Navigation and structure</h3><p>Check whether the content has a clear heading structure, logical sequence and understandable navigation. Learners should be able to work out where they are and what to do next.</p><h3>Keyboard access</h3><p>Move through the content without a mouse. Can every interactive element be reached? Is focus visible? Can activities be completed?</p><h3>Media access</h3><p>Review captions, transcripts, alternative text and whether important visual information is available in another form where needed.</p><h3>Instructions and feedback</h3><p>Look closely at task wording, validation messages and quiz feedback. Clear language often makes a significant difference.</p>',
                '<h2>Use assistive technology where possible</h2><p>Automated tools can help, but they will not give a complete picture of learning accessibility. Where possible, teams should include real keyboard testing and at least some checks with assistive technology such as screen readers. This helps surface issues that are easy to miss in visual review alone.</p><p>Even a small amount of hands-on testing can change how teams think about content quality.</p>',
                '<h2>Document issues in a useful way</h2><p>Testing is only helpful if the findings can be acted on. Record what the issue is, where it happens, why it matters and what a practical fix might look like. Avoid overly vague notes. Teams need findings they can turn into decisions and changes.</p><p>It is also worth grouping repeated issues. When the same problem appears several times, it is often a sign that a template, workflow or design habit needs attention.</p>',
                '<h2>Testing should support improvement, not just reporting</h2><p>The best testing processes do not end with a list of faults. They help teams understand patterns, raise capability and improve future content. That is why accessibility testing works best when it is connected to broader quality practice, not treated as a one-off exercise.</p><p>Over time, teams that test well become faster at spotting issues earlier, which reduces rework and improves consistency.</p>',
                '<h2>Keep the process practical</h2><p>You do not need a perfect lab setup to start testing more effectively. Begin with a manageable sample, use a repeatable checklist, include keyboard testing, and review findings in a way that supports action. The goal is steady improvement, better decisions and a clearer picture of learner access.</p>',
            ]),
            'faqs'          => [
                [
                    'question' => 'Can automated tools test eLearning properly on their own?',
                    'slug'     => 'faq-automated-tools-enough-elearning',
                    'answer'   => '<p>No. Automated tools can help identify some issues, but they cannot fully test usability, keyboard flows, learning interactions or the real learner experience.</p>',
                ],
                [
                    'question' => 'What is the best place to start with accessibility testing?',
                    'slug'     => 'faq-best-place-start-testing',
                    'answer'   => '<p>Start with a representative sample of learning content and focus on structure, keyboard access, media alternatives and instruction clarity.</p>',
                ],
                [
                    'question' => 'Should teams test every module in full?',
                    'slug'     => 'faq-test-every-module',
                    'answer'   => '<p>Not always at first. It is often more useful to review a representative set of assets, identify repeated barriers and then improve the patterns behind them.</p>',
                ],
            ],
        ],
    ];

    $results = [];

    foreach ($guides as $guide) {
        $results[] = elahub_upsert_cornerstone_article($guide, $default_image_id);
    }

    return $results;
}

function elahub_upsert_cornerstone_article(array $config, $default_image_id = 0)
{
    $notes = [];

    $existing = get_page_by_path($config['slug'], OBJECT, 'cornerstone_article');

    $post_args = [
        'post_type'    => 'cornerstone_article',
        'post_title'   => $config['title'],
        'post_name'    => $config['slug'],
        'post_excerpt' => $config['excerpt'],
        'post_content' => $config['content'],
        'post_status'  => 'publish',
    ];

    if ($existing instanceof WP_Post) {
        $post_args['ID'] = (int) $existing->ID;
        $post_id = wp_update_post($post_args, true);
        $notes[] = 'Updated existing guide post.';
    } else {
        $post_id = wp_insert_post($post_args, true);
        $notes[] = 'Created new guide post.';
    }

    if (is_wp_error($post_id)) {
        return [
            'title'   => $config['title'],
            'message' => 'Failed to create guide.',
            'notes'   => [$post_id->get_error_message()],
        ];
    }

    $post_id = (int) $post_id;

    update_field('guide_summary_text', $config['summary'], $post_id);
    update_field('guide_last_updated', $config['last_updated'], $post_id);
    update_field('guide_read_time', $config['read_time'], $post_id);
    update_field('guide_youtube_url', '', $post_id);

    $term = term_exists($config['category_slug'], 'guide_category');
    if (! $term) {
        $term = wp_insert_term($config['category_name'], 'guide_category', ['slug' => $config['category_slug']]);
        $notes[] = 'Created guide category term.';
    }

    if (! is_wp_error($term) && ! empty($term['term_id'])) {
        wp_set_object_terms($post_id, [(int) $term['term_id']], 'guide_category', false);
    }

    $faq_ids = [];
    foreach ($config['faqs'] as $faq) {
        $faq_ids[] = elahub_upsert_cornerstone_faq($faq);
    }
    $faq_ids = array_values(array_filter(array_map('intval', $faq_ids)));

    if (! empty($faq_ids)) {
        update_field('guide_faqs', $faq_ids, $post_id);
        $notes[] = 'Attached ' . count($faq_ids) . ' FAQs.';
    }

    if ($default_image_id && get_post($default_image_id)) {
        set_post_thumbnail($post_id, (int) $default_image_id);
        $notes[] = 'Set featured image to attachment ID ' . (int) $default_image_id . '.';
    }

    return [
        'title'   => $config['title'],
        'message' => 'Guide seeded successfully.',
        'notes'   => $notes,
    ];
}

function elahub_upsert_cornerstone_faq(array $faq)
{
    $existing = get_page_by_path($faq['slug'], OBJECT, 'faq');

    $post_args = [
        'post_type'    => 'faq',
        'post_title'   => $faq['question'],
        'post_name'    => $faq['slug'],
        'post_content' => $faq['answer'],
        'post_status'  => 'publish',
    ];

    if ($existing instanceof WP_Post) {
        $post_args['ID'] = (int) $existing->ID;
        $faq_id = wp_update_post($post_args, true);
    } else {
        $faq_id = wp_insert_post($post_args, true);
    }

    if (is_wp_error($faq_id)) {
        return 0;
    }

    return (int) $faq_id;
}
