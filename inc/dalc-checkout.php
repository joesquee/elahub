<?php

/**
 * Native DALC checkout flow.
 *
 * Replaces the FunnelKit funnel for the DALC purchase with the native
 * WooCommerce checkout, rendered inside the eLaHub site (header / nav / footer)
 * with a consistent look and feel.
 *
 *  - "Start Learning Now" → ?start-dalc=1 → empties the cart, adds the DALC
 *    product, and sends the buyer to the themed WooCommerce checkout.
 *  - The old FunnelKit checkout URL auto-redirects into the same flow.
 *  - The checkout gains a programme header (image + title as H1), a testimonials
 *    sidebar, an author note and a support block — all editable in
 *    Theme Settings → DALC Checkout — plus a proper coupon-field label.
 *  - After payment, DALC orders redirect to the native themed thank-you page.
 *
 * The checkout form itself is 100% the live WooCommerce checkout; we only wrap
 * and style it. TalentLMS enrolment + order emails + thank-you all keep working.
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
	exit;
}

/* ─────────────────────────────────────────────────────────────────────────
 * Resolvers
 * ───────────────────────────────────────────────────────────────────────── */

/** Resolve the DALC product ID (filter → option → name search). */
function elahub_dalc_product_id(): int {
	$id = (int) apply_filters('elahub_dalc_product_id', 0);
	if ($id > 0) {
		return $id;
	}
	$opt = (int) get_option('elahub_dalc_product_id');
	if ($opt > 0) {
		return $opt;
	}
	if (function_exists('wc_get_products')) {
		$found = wc_get_products([
			'limit'   => 1,
			'status'  => 'publish',
			'orderby' => 'date',
			'order'   => 'DESC',
			's'       => 'DALC Programme',
		]);
		if (! empty($found)) {
			return $found[0]->get_id();
		}
	}
	return 0;
}

/** The NATIVE checkout URL (wc_get_checkout_url() is filtered by FunnelKit). */
function elahub_native_checkout_url(): string {
	$id  = function_exists('wc_get_page_id') ? wc_get_page_id('checkout') : 0;
	$url = $id > 0 ? get_permalink($id) : '';
	return $url ?: home_url('/checkout/');
}

/** Start-checkout button URL. */
function elahub_dalc_start_url(): string {
	return add_query_arg('start-dalc', '1', elahub_native_checkout_url());
}

/** URL of the page using the DALC thank-you template. */
function elahub_dalc_thankyou_url(): string {
	$pages = get_posts([
		'post_type'      => 'page',
		'posts_per_page' => 1,
		'post_status'    => 'publish',
		'meta_key'       => '_wp_page_template',
		'meta_value'     => 'template-dalc-thank-you.php',
		'fields'         => 'ids',
	]);
	if (! empty($pages)) {
		return get_permalink($pages[0]);
	}
	return home_url('/dalc-thank-you-native/');
}

/** Does an ORDER contain the DALC product (or a TalentLMS-mapped product)? */
function elahub_order_has_dalc($order): bool {
	if (! $order) {
		return false;
	}
	$dalc = elahub_dalc_product_id();
	foreach ($order->get_items() as $item) {
		$pid = $item->get_product_id();
		if ($dalc && $pid === $dalc) {
			return true;
		}
		if (get_post_meta($pid, '_talentlms_course_id', true)) {
			return true;
		}
	}
	return false;
}

/** Does the CART contain the DALC product (or a TalentLMS-mapped product)? */
function elahub_cart_has_dalc(): bool {
	if (! function_exists('WC') || ! WC()->cart) {
		return false;
	}
	$dalc = elahub_dalc_product_id();
	foreach (WC()->cart->get_cart() as $item) {
		$pid = (int) ($item['product_id'] ?? 0);
		if ($pid && ($pid === $dalc || get_post_meta($pid, '_talentlms_course_id', true))) {
			return true;
		}
	}
	return false;
}

/* ─────────────────────────────────────────────────────────────────────────
 * Routing: buttons → native checkout → thank-you
 * ───────────────────────────────────────────────────────────────────────── */

/** Route the old FunnelKit checkout URL into the native flow. */
add_action('template_redirect', 'elahub_dalc_bypass_funnelkit', 1);
function elahub_dalc_bypass_funnelkit(): void {
	if (is_admin() || ! function_exists('WC')) {
		return;
	}
	$uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
	if (strpos($uri, 'dalc-programme-checkout') !== false && strpos($uri, 'start-dalc') === false) {
		wp_safe_redirect(elahub_dalc_start_url());
		exit;
	}
}

/** ?start-dalc=1 → clean cart, add the DALC product, go to the checkout. */
add_action('template_redirect', 'elahub_dalc_start_checkout');
function elahub_dalc_start_checkout(): void {
	if (empty($_GET['start-dalc']) || is_admin() || ! function_exists('WC')) {
		return;
	}
	$pid = elahub_dalc_product_id();
	if ($pid <= 0 || ! WC()->cart) {
		return;
	}
	WC()->cart->empty_cart();
	WC()->cart->add_to_cart($pid);
	wp_safe_redirect(elahub_native_checkout_url());
	exit;
}

/** After payment, send DALC orders to the themed thank-you page. */
add_filter('woocommerce_get_checkout_order_received_url', 'elahub_dalc_thankyou_redirect', 10, 2);
function elahub_dalc_thankyou_redirect($url, $order) {
	if (elahub_order_has_dalc($order)) {
		return add_query_arg(
			[
				'order_id' => $order->get_id(),
				'key'      => $order->get_order_key(),
			],
			elahub_dalc_thankyou_url()
		);
	}
	return $url;
}

/* ─────────────────────────────────────────────────────────────────────────
 * Editable checkout content (Theme Settings → DALC Checkout)
 * ───────────────────────────────────────────────────────────────────────── */

add_action('acf/init', 'elahub_dalc_checkout_acf', 12);
function elahub_dalc_checkout_acf(): void {
	if (function_exists('acf_add_options_sub_page')) {
		acf_add_options_sub_page([
			'page_title'  => 'DALC Checkout',
			'menu_title'  => 'DALC Checkout',
			'parent_slug' => 'elahub-theme-settings',
			'menu_slug'   => 'elahub-dalc-checkout',
		]);
	}
	if (! function_exists('acf_add_local_field_group')) {
		return;
	}
	acf_add_local_field_group([
		'key'    => 'group_elahub_dalc_checkout',
		'title'  => 'DALC Checkout',
		'fields' => [
			[ 'key' => 'field_dco_tab_head', 'label' => 'Header', 'type' => 'tab' ],
			[ 'key' => 'field_dco_title', 'label' => 'Programme title (H1)', 'name' => 'dco_title', 'type' => 'text', 'default_value' => 'The Designing Accessible Learning Content (DALC) Programme', 'instructions' => 'Shown as the main heading on the checkout. The image comes from the DALC product’s image.' ],
			[ 'key' => 'field_dco_subtitle', 'label' => 'Subtitle', 'name' => 'dco_subtitle', 'type' => 'text', 'default_value' => 'Create digital learning that ticks every box.' ],
			[ 'key' => 'field_dco_tab_side', 'label' => 'Sidebar', 'type' => 'tab' ],
			[ 'key' => 'field_dco_side_heading', 'label' => 'Sidebar heading', 'name' => 'dco_side_heading', 'type' => 'text', 'default_value' => 'What people are saying' ],
			[ 'key' => 'field_dco_testimonials', 'label' => 'Testimonials', 'name' => 'dco_testimonials', 'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Add testimonial', 'instructions' => 'Leave empty to use the defaults. Rendered as the site’s quote card (photo, name, role, organisation, quote, star rating).', 'sub_fields' => [
				[ 'key' => 'field_dco_t_role', 'label' => 'Role / job title', 'name' => 'role', 'type' => 'text' ],
					[ 'key' => 'field_dco_t_org', 'label' => 'Organisation', 'name' => 'organisation', 'type' => 'text' ],
				[ 'key' => 'field_dco_t_quote', 'label' => 'Quote', 'name' => 'quote', 'type' => 'textarea', 'rows' => 3 ],
				[ 'key' => 'field_dco_t_name', 'label' => 'Name', 'name' => 'name', 'type' => 'text' ],
				[ 'key' => 'field_dco_t_photo', 'label' => 'Photo', 'name' => 'photo', 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'thumbnail' ],
					[ 'key' => 'field_dco_t_stars', 'label' => 'Star rating', 'name' => 'stars', 'type' => 'number', 'default_value' => 5, 'min' => 1, 'max' => 5, 'instructions' => '1–5. Defaults to 5.' ],
			] ],
			[ 'key' => 'field_dco_tab_author', 'label' => 'Author + support', 'type' => 'tab' ],
			[ 'key' => 'field_dco_author_name', 'label' => 'Author name', 'name' => 'dco_author_name', 'type' => 'text', 'default_value' => 'Susi Miller' ],
			[ 'key' => 'field_dco_author_photo', 'label' => 'Author photo', 'name' => 'dco_author_photo', 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'thumbnail' ],
			[ 'key' => 'field_dco_author_note', 'label' => 'Author note', 'name' => 'dco_author_note', 'type' => 'textarea', 'rows' => 4, 'default_value' => 'Thank you for being part of the digital learning inclusion revolution and helping to make all learning content accessible and inclusive — as the default.' ],
			[ 'key' => 'field_dco_support', 'label' => 'Support email', 'name' => 'dco_support_email', 'type' => 'text', 'instructions' => 'Leave blank to use the site admin email.' ],
		],
		'location' => [ [ [ 'param' => 'options_page', 'operator' => '==', 'value' => 'elahub-dalc-checkout' ] ] ],
	]);
}

/** Option getter with a default. */
function elahub_dco(string $name, string $default = ''): string {
	$v = function_exists('get_field') ? get_field($name, 'option') : '';
	return (is_string($v) && trim($v) !== '') ? trim($v) : $default;
}

/** Testimonials (ACF rows, else defaults). */
function elahub_dco_testimonials(): array {
	$t = function_exists('get_field') ? get_field('dco_testimonials', 'option') : null;
	if (! empty($t) && is_array($t)) {
		return $t;
	}
	return [
		[ 'name' => 'Leah Holroyd', 'role' => 'Learning & Development Lead', 'organisation' => '', 'quote' => 'I couldn’t recommend this programme more highly. It really brought Susi’s book to life and made it much easier to apply practically.', 'photo' => content_url('/uploads/2023/12/Leah.jpg'), 'stars' => 5 ],
		[ 'name' => 'Kirsty Wolf', 'role' => 'Accessibility Specialist', 'organisation' => '', 'quote' => 'As a screen reader user, it’s rare that I can access everything in an online course. This programme worked seamlessly and was a great learner experience.', 'photo' => content_url('/uploads/2023/12/Kirsty-300x300.jpg'), 'stars' => 5 ],
	];
}

/* ─────────────────────────────────────────────────────────────────────────
 * Checkout layout: programme header (main col) + testimonials sidebar
 * ───────────────────────────────────────────────────────────────────────── */

/** Body class so CSS can scope to the enhanced DALC checkout only. */
add_filter('body_class', 'elahub_dalc_checkout_body_class');
function elahub_dalc_checkout_body_class($classes) {
	if (function_exists('is_checkout') && is_checkout() && ! is_wc_endpoint_url('order-received') && elahub_cart_has_dalc()) {
		$classes[] = 'dalc-checkout-enhanced';
	}
	return $classes;
}

/** Open the two-column grid + programme header, before the checkout form. */
add_action('woocommerce_before_checkout_form', 'elahub_dalc_checkout_open', 5);
function elahub_dalc_checkout_open($checkout): void {
	if (! elahub_cart_has_dalc()) {
		return;
	}
	$title    = esc_html(elahub_dco('dco_title', 'The Designing Accessible Learning Content (DALC) Programme'));
	$subtitle = esc_html(elahub_dco('dco_subtitle', 'Create digital learning that ticks every box.'));
	$pid      = elahub_dalc_product_id();
	$img      = $pid ? get_the_post_thumbnail_url($pid, 'medium') : '';

	echo '<div class="dalc-co-grid"><div class="dalc-co-main">';
	echo '<div class="dalc-co-header">';
	if ($img) {
		echo '<div class="dalc-co-cover"><img src="' . esc_url($img) . '" alt="" loading="eager"></div>';
	}
	echo '<div class="dalc-co-header-text"><h1 class="dalc-co-title">' . $title . '</h1>';
	echo '<p class="dalc-co-subtitle">' . $subtitle . '</p></div>';
	echo '</div>';
}

/** Close the main column, render the sidebar, close the grid. */
add_action('woocommerce_after_checkout_form', 'elahub_dalc_checkout_close', 20);
function elahub_dalc_checkout_close($checkout): void {
	if (! elahub_cart_has_dalc()) {
		return;
	}
	$heading = esc_html(elahub_dco('dco_side_heading', 'What people are saying'));
	$a_name  = esc_html(elahub_dco('dco_author_name', 'Susi Miller'));
	$a_photo = elahub_dco('dco_author_photo');
	if (! $a_photo) {
		$a_photo = content_url('/uploads/2023/10/Susi-Miller-ELN-300-x300.jpg');
	}
	$a_note  = esc_html(elahub_dco('dco_author_note', 'Thank you for being part of the digital learning inclusion revolution and helping to make all learning content accessible and inclusive — as the default.'));
	$support = elahub_dco('dco_support_email', (string) get_option('admin_email'));

	echo '</div>'; // .dalc-co-main

	echo '<aside class="dalc-co-side" aria-label="What people are saying">';
	echo '<h2 class="dalc-co-side-heading">' . $heading . '</h2><span class="dalc-co-rule"></span>';

	foreach (elahub_dco_testimonials() as $t) {
		$photo = isset($t['photo']) && is_string($t['photo']) ? $t['photo'] : '';
		$name  = $t['name'] ?? '';
		$role  = $t['role'] ?? '';
		$org   = $t['organisation'] ?? '';
		$stars = isset($t['stars']) ? max(1, min(5, (int) $t['stars'])) : 5;

		echo '<figure class="dalc-co-quote">';

		echo '<div class="dalc-co-quote-head">';
		if ($photo) {
			echo '<img class="dalc-co-avatar" src="' . esc_url($photo) . '" alt="" loading="lazy">';
		} elseif ($name) {
			$initials = '';
			foreach (preg_split('/\s+/', trim($name)) as $part) {
				if ($part !== '') { $initials .= mb_substr($part, 0, 1); }
			}
			echo '<span class="dalc-co-avatar dalc-co-avatar--initials" aria-hidden="true">' . esc_html(mb_strtoupper(mb_substr($initials, 0, 2))) . '</span>';
		}
		if ($name || $role) {
			echo '<div class="dalc-co-quote-meta">';
			if ($name) {
				echo '<figcaption class="dalc-co-name">' . esc_html($name) . '</figcaption>';
			}
			if ($role) {
				echo '<p class="dalc-co-role">' . esc_html($role) . '</p>';
			}
			echo '</div>';
		}
		echo '</div>';

		echo '<blockquote>' . esc_html($t['quote'] ?? '') . '</blockquote>';

		if ($org) {
			echo '<p class="dalc-co-org">' . esc_html($org) . '</p>';
		}

		echo '<div class="dalc-co-stars" role="img" aria-label="' . esc_attr(sprintf('%d out of 5 stars', $stars)) . '">';
		for ($i = 1; $i <= 5; $i++) {
			$cls = $i <= $stars ? 'dalc-co-star is-on' : 'dalc-co-star';
			echo '<i class="fa-solid fa-star ' . $cls . '" aria-hidden="true"></i>';
		}
		echo '</div>';

		echo '</figure>';
	}

	echo '<div class="dalc-co-author">';
	echo $a_photo ? '<img class="dalc-co-avatar" src="' . esc_url($a_photo) . '" alt="">' : '<span class="dalc-co-avatar"></span>';
	echo '<h3>A note from the author</h3><p>' . $a_note . '</p><div class="dalc-co-sig">' . $a_name . '</div>';
	echo '</div>';

	if ($support) {
		echo '<div class="dalc-co-queries">If you have any queries email us at:<br><a href="mailto:' . esc_attr($support) . '">' . esc_html($support) . '</a></div>';
	}

	echo '</aside></div>'; // .dalc-co-side + .dalc-co-grid
}

/* ─────────────────────────────────────────────────────────────────────────
 * Brand styling + coupon label
 * ───────────────────────────────────────────────────────────────────────── */

add_action('wp_head', 'elahub_dalc_checkout_styles', 20);
function elahub_dalc_checkout_styles(): void {
	if (! function_exists('is_checkout') || ! is_checkout() || is_wc_endpoint_url('order-received')) {
		return;
	}
	?>
	<style id="elahub-dalc-checkout-css">
		/* On the DALC checkout, hide the default page title + rule (the programme
		   header provides the H1 instead). */
		.dalc-checkout-enhanced .site-main > .container > h1,
		.dalc-checkout-enhanced .site-main > .container > hr { display: none; }

		/* Centre the checkout within the site container (the page template wraps
		   content in max-w-7xl aligned left; centre it so it matches the rest of
		   the site). */
		.dalc-checkout-enhanced .elahub-content { margin-left: auto; margin-right: auto; }

		/* Two-column layout: form + testimonials sidebar */
		.dalc-co-grid { display: grid; grid-template-columns: 1.55fr .95fr; gap: 2rem; align-items: start; }
		@media (max-width: 900px) { .dalc-co-grid { grid-template-columns: 1fr; } }

		/* Programme header */
		.dalc-co-header { display: flex; gap: 1.25rem; align-items: flex-start; margin-bottom: 2rem; }
		.dalc-co-cover { flex: 0 0 96px; width: 96px; height: 96px; border-radius: .5rem; overflow: hidden; background: var(--color-primary-soft); }
		.dalc-co-cover img { width: 100%; height: 100%; object-fit: cover; }
		.dalc-checkout-enhanced .dalc-co-title { font-size: 1.9rem !important; margin: 0 0 .5rem !important; line-height: 1.18 !important; letter-spacing: -.01em; }
		.dalc-co-subtitle { margin: 0; color: #55636b; }

		/* Force the WooCommerce form into a single column inside the main col */
		.dalc-checkout-enhanced .col2-set .col-1,
		.dalc-checkout-enhanced .col2-set .col-2 { float: none; width: 100%; padding: 0; margin-bottom: 1.5rem; }

		/* Form fields */
		.woocommerce-checkout .form-row label { font-weight: 600; color: var(--color-text); }
		.woocommerce form .form-row input.input-text,
		.woocommerce form .form-row textarea,
		.woocommerce-checkout select,
		.woocommerce-checkout .select2-container .select2-selection--single {
			border: 1px solid var(--color-primary-border) !important;
			border-radius: .5rem !important;
			padding: .7rem .9rem !important;
			min-height: 3rem;
			font-size: 1rem;
			background: #fff;
		}
		.woocommerce-checkout .select2-container--default .select2-selection--single .select2-selection__arrow { height: 3rem; }
		.woocommerce form .form-row input.input-text:focus,
		.woocommerce-checkout select:focus {
			outline: 2px solid var(--color-focus-ring);
			outline-offset: 1px;
			border-color: var(--color-primary) !important;
		}
		.woocommerce-checkout h3,
		.woocommerce-checkout #order_review_heading {
			color: var(--color-primary-dark);
			font-size: var(--text-h4-size);
			margin: 0 0 1.25rem;
		}

		/* Order review + payment */
		.woocommerce-checkout #order_review {
			background: var(--color-primary-softest);
			border: 1px solid var(--color-primary-border);
			border-radius: .5rem;
			border-bottom-right-radius: 2.5rem;
			padding: 1.75rem;
		}
		.woocommerce-checkout table.shop_table { border: 0; border-collapse: collapse; width: 100%; }
		.woocommerce-checkout table.shop_table th,
		.woocommerce-checkout table.shop_table td { border-bottom: 1px solid var(--color-primary-border); padding: .8rem 0; text-align: left; }
		.woocommerce-checkout table.shop_table td.product-total,
		.woocommerce-checkout table.shop_table .amount { text-align: right; white-space: nowrap; }
		.woocommerce-checkout table.shop_table .order-total th,
		.woocommerce-checkout table.shop_table .order-total td { color: var(--color-primary-dark); font-size: 1.15rem; font-weight: 700; border-bottom: 0; padding-top: 1rem; }

		/* Place order → eLaHub pill */
		.woocommerce #payment #place_order,
		.woocommerce-checkout #place_order {
			background: var(--color-primary-dark) !important; color: #fff !important; border: 0 !important;
			border-radius: 9999px !important; padding: .95rem 2.25rem !important;
			font-weight: 600 !important; font-size: 1.05rem !important; width: 100%; transition: background .2s ease;
		}
		.woocommerce #payment #place_order:hover,
		.woocommerce-checkout #place_order:hover { background: var(--color-primary) !important; }

		/* Coupon field label (added via JS below) */
		.elahub-coupon-label { display: block; font-weight: 600; font-size: .9rem; margin-bottom: .35rem; }

		/* Links + notices → brand + underlined (accessibility), checkout content only */
		.woocommerce-checkout .woocommerce a,
		.woocommerce-checkout form.checkout a { color: var(--color-primary); text-decoration: underline; }
		.woocommerce-info, .woocommerce-message, .woocommerce-error { border-top-color: var(--color-primary) !important; border-radius: .5rem; }
		.woocommerce-info::before, .woocommerce-message::before { color: var(--color-primary) !important; }

		/* Testimonials sidebar */
		.dalc-co-side { background: var(--color-primary-dark); color: #fff; border-radius: .75rem; border-bottom-right-radius: 2.5rem; padding: 2rem 1.75rem; }
		.dalc-co-side-heading { text-align: left; color: #fff; font-size: 1.2rem; margin: 0 0 .5rem; }
		.dalc-co-rule { display: block; height: 2px; width: 60px; background: rgba(255,255,255,.35); margin: .5rem 0 1.75rem; }

		/* Quote card — matches the site's dark quote-card component */
		.dalc-co-quote {
			display: flex; flex-direction: column; gap: 1.25rem; text-align: left;
			background: #0d526d; border: 1px solid rgba(255,255,255,.1);
			border-radius: .5rem; border-bottom-right-radius: 33px;
			padding: 1.75rem 1.5rem; margin: 0 0 1.25rem;
		}
		.dalc-co-quote-head { display: flex; align-items: center; gap: 1rem; }
		.dalc-co-avatar { display: block; width: 64px; height: 64px; flex: 0 0 64px; border-radius: 9999px; object-fit: cover; margin: 0; }
		.dalc-co-avatar--initials { display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,.15); color: #fff; font-weight: 700; font-size: 1.25rem; }
		.dalc-co-quote-meta { min-width: 0; }
		.dalc-co-name { margin: 0; font-size: 1.25rem; font-weight: 600; color: #fff; line-height: 1.35; }
		.dalc-co-role { margin: .15rem 0 0; font-size: 1rem; font-weight: 600; color: rgba(255,255,255,.9); }
		.dalc-co-quote blockquote { color: rgba(255,255,255,.85); font-size: 1rem; line-height: 1.8; margin: 0; border: 0; padding: 0; quotes: "“" "”"; }
		.dalc-co-quote blockquote::before { content: open-quote; } .dalc-co-quote blockquote::after { content: close-quote; }
		.dalc-co-org { margin: 0; font-size: 1rem; font-weight: 700; color: rgba(255,255,255,.9); }
		.dalc-co-stars { display: flex; gap: .35rem; }
		.dalc-co-stars .dalc-co-star { font-size: 1.15rem; color: rgba(255,255,255,.2); }
		.dalc-co-stars .dalc-co-star.is-on { color: var(--color-primary-light); }

		/* Author note card (kept white to stand apart from the quote cards) */
		.dalc-co-author { background: #fff; color: var(--color-text); border-radius: .5rem; border-bottom-right-radius: 33px; padding: 1.5rem; text-align: left; margin-top: .25rem; }
		.dalc-co-author .dalc-co-avatar { width: 80px; height: 80px; flex: none; background: var(--color-primary-soft); margin: 0 0 1rem; }
		.dalc-co-author h3 { margin: 0 0 .5rem; font-weight: 600; color: var(--color-primary-dark); }
		.dalc-co-author p { margin: 0; }
		.dalc-co-sig { font-size: 1.4rem; margin-top: 1rem; color: var(--color-primary-dark); }
		.dalc-co-queries { background: #fff; color: var(--color-text); border-radius: .5rem; padding: 1.25rem; text-align: left; margin-top: 1.25rem; font-weight: 600; }
		.dalc-co-queries a { color: var(--color-primary); }
	</style>
	<script id="elahub-dalc-checkout-js">
	document.addEventListener('DOMContentLoaded', function () {
		// Accessibility: give the coupon input a proper label.
		var c = document.getElementById('coupon_code');
		if (c && ! c.dataset.elahubLabelled) {
			c.dataset.elahubLabelled = '1';
			var l = document.createElement('label');
			l.className = 'elahub-coupon-label';
			l.setAttribute('for', 'coupon_code');
			l.textContent = 'Coupon code';
			c.parentNode.insertBefore(l, c);
		}
	});
	</script>
	<?php
}
