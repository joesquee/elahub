<?php

/**
 * Front page template
 *
 * @package elahub
 */

get_header();
?>

<main id="primary" class="site-main">
    <?php get_template_part('template-parts/hero/hero-section'); ?>
    <?php get_template_part('template-parts/flexible/render-flexible-sections'); ?>
</main>

<?php
get_footer();
