<?php

/**
 * Template Name: Service Page
 * Template Post Type: page
 *
 * Used for service and inner pages that need the flexible hero
 * (full image, person cutout, or icon variant) + page builder sections.
 *
 * To use: edit any page in WordPress admin → Page Attributes → Template → Service Page
 *
 * @package elahub
 */

get_header();
?>

<main id="primary" class="site-main service-template">

	<?php get_template_part('template-parts/hero/page-hero'); ?>
	<?php get_template_part('template-parts/flexible/render-flexible-sections'); ?>

</main>

<?php
get_footer();
