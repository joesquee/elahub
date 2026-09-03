<?php

/**
 * Shared flexible section renderer
 *
 * @package elahub
 */

if (! have_rows('page_builder')) {
	return;
}

while (have_rows('page_builder')) :
	the_row();

	switch (get_row_layout()) {
		case 'logo_strip':
			get_template_part('template-parts/flexible/logo-strip');
			break;

		case 'feature_section':
			get_template_part('template-parts/flexible/feature-section');
			break;

		case 'services_grid':
			get_template_part('template-parts/flexible/services-grid');
			break;

		case 'icon_features':
			get_template_part('template-parts/flexible/icon-features');
			break;

		case 'cta_section':
			get_template_part('template-parts/flexible/cta-section');
			break;

		case 'contact_banner':
			get_template_part('template-parts/flexible/contact-banner-section');
			break;

		case 'learning_hub_section':
			get_template_part('template-parts/flexible/learning-hub-section');
			break;

		case 'purchase_cards':
			get_template_part('template-parts/flexible/purchase-cards');
			break;

		case 'dalc_signup':
			get_template_part('template-parts/flexible/dalc-signup');
			break;

		case 'dalc_live_waitlist':
			get_template_part('template-parts/flexible/dalc-live-waitlist');
			break;

		case 'short_courses_waitlist':
			get_template_part('template-parts/flexible/short-courses-waitlist');
			break;

		case 'showcase_module_signup':
			get_template_part('template-parts/flexible/showcase-module-signup');
			break;

		case 'accessibility_guides':
			get_template_part('template-parts/flexible/accessibility-guides-section');
			break;

		case 'faq_section':
			get_template_part('template-parts/flexible/faq-section');
			break;

		case 'case_studies_section':
			get_template_part('template-parts/flexible/case-studies-section');
			break;

		case 'testimonials_section':
			get_template_part('template-parts/flexible/testimonials-section');
			break;
	}
endwhile;
