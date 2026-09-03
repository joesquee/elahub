<?php

/**
 * Template Name: DALC Thank You
 * Template Post Type: page
 *
 * Native eLaHub thank-you page for the DALC purchase funnel. Uses the site's
 * real hero pattern (light, left-aligned, eyebrow + heading + button + icon box)
 * and design tokens, with the WooCommerce order summary styled to match.
 *
 * The TalentLMS "Start course" link is order-item meta (tlms_go-to-course)
 * printed by the plugin inside the WooCommerce order table; we also surface it
 * as a hero button.
 *
 * FunnelKit's Thank You step redirects here with ?order_id=&key=.
 *
 * @package elahub
 */

get_header();

/* ── Resolve + verify the order from the query (passed by FunnelKit redirect) ── */
$order       = false;
$first_name  = '';
$course_link = '';

$order_id  = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
$order_key = isset($_GET['key']) ? sanitize_text_field(wp_unslash($_GET['key'])) : '';

if ($order_id && function_exists('wc_get_order')) {
	$maybe = wc_get_order($order_id);
	if ($maybe && $order_key && hash_equals((string) $maybe->get_order_key(), $order_key)) {
		$order      = $maybe;
		$first_name = $maybe->get_billing_first_name();

		foreach ($maybe->get_items() as $item_id => $item) {
			$meta = wc_get_order_item_meta($item_id, 'tlms_go-to-course');
			if (is_array($meta) && ! empty($meta['goto_url'])) {
				$course_link = $meta['goto_url'];
				break;
			}
		}
	}
}

$support_email = get_option('admin_email');
$dot_svg       = get_template_directory_uri() . '/assets/main-circle-dots.svg';

/* ── Editable content (Page editor → “DALC Thank You — page content”) ─────── */
$eyebrow     = trim((string) (get_field('dalc_ty_eyebrow') ?: 'Order confirmed'));
$heading_tpl = trim((string) (get_field('dalc_ty_heading') ?: 'Thank you {first_name} & congratulations!'));
$intro       = trim((string) (get_field('dalc_ty_intro') ?: 'You’re all set to begin the Designing Accessible Learning Content (DALC) Programme. Here’s what happens next.'));
$steps_title = trim((string) (get_field('dalc_ty_steps_title') ?: 'What happens next'));
$support_tpl = trim((string) (get_field('dalc_ty_support') ?: '<p>Any issues or queries? Contact {support_email}.</p>'));

// Heading: swap {first_name}; drop the token cleanly when there’s no name.
$heading = $first_name
	? str_replace('{first_name}', $first_name, $heading_tpl)
	: trim(str_replace(['{first_name} ', '{first_name}'], '', $heading_tpl));

// Support line: swap {support_email} for a mailto link.
$support_link = '<a href="mailto:' . esc_attr($support_email) . '">' . esc_html($support_email) . '</a>';
$support_html = str_replace('{support_email}', $support_link, $support_tpl);

// Steps: use the repeater rows, or fall back to the defaults.
$steps = get_field('dalc_ty_steps');
if (empty($steps) || ! is_array($steps)) {
	$steps = [
		[
			'title' => 'Check your email',
			'text'  => 'You’ll shortly receive your DALC Programme order confirmation and a “Welcome to the eLaHub learning portal” email. These can take up to 10–15 minutes — please check your junk/spam folder if they don’t arrive.',
		],
		[
			'title' => 'Set up your account',
			'text'  => 'Open the “Welcome to the eLaHub learning portal” email for step-by-step instructions to set your password and access the portal.',
		],
		[
			'title' => 'Start learning',
			'text'  => 'Once your account is set up, sign in and begin the DALC Programme at your own pace — or jump straight in with the button above.',
		],
	];
}
?>

<style>
/* ── DALC native thank-you — scoped to the eLaHub design tokens ───────────── */
.dalc-ty__section { padding: 3.5rem 0; }
.dalc-ty__section--tint { background: var(--color-primary-softest); }
.dalc-ty__hero-media { min-height: 14rem; }
@media (min-width: 768px)  { .dalc-ty__hero-media { min-height: 17rem; } }
@media (min-width: 1024px) { .dalc-ty__hero-media { min-height: 21rem; } }
.dalc-ty__title { margin: 0 0 2rem; }
.dalc-ty__cta-note { margin: 0; font-size: .9rem; color: var(--color-icon); }

/* Steps — signature eLaHub card */
.dalc-ty__steps { display: flex; flex-wrap: wrap; gap: 1.5rem; }
.dalc-ty__step {
	flex: 1 1 240px;
	background: var(--color-primary-softest);
	border: 1px solid var(--color-primary-border);
	border-radius: .5rem;
	border-bottom-right-radius: 3.667rem;
	padding: 2rem 1.75rem 2.25rem;
}
.dalc-ty__step-num {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 2.5rem;
	height: 2.5rem;
	border-radius: 9999px;
	background: var(--color-primary-soft);
	color: var(--color-primary-dark);
	font-weight: 700;
	margin-bottom: 1.1rem;
}
.dalc-ty__step h3 { font-size: var(--text-h4-size); margin: 0 0 .5rem; color: var(--color-primary-dark); }
.dalc-ty__step p { margin: 0; color: var(--color-text); }

/* Order card + WooCommerce table override */
.dalc-ty__order-card {
	background: #fff;
	border: 1px solid var(--color-primary-border);
	border-radius: .5rem;
	border-bottom-right-radius: 3.667rem;
	padding: 2.5rem;
}
.dalc-ty__order-meta { color: var(--color-primary); font-weight: 600; margin: 0 0 2rem; }
.dalc-ty__order-card .woocommerce-order-details__title { display: none; }
.dalc-ty__order-card .woocommerce-column__title {
	font-size: var(--text-h4-size);
	font-weight: 600;
	color: var(--color-primary-dark);
	margin: 0 0 1rem;
}
.dalc-ty__order-card table.order_details {
	width: 100%;
	border-collapse: collapse;
	margin: 0;
	font-size: 1rem;
}
.dalc-ty__order-card table.order_details th,
.dalc-ty__order-card table.order_details td {
	text-align: left;
	padding: .9rem 0;
	border-bottom: 1px solid var(--color-primary-border);
	vertical-align: top;
}
.dalc-ty__order-card table.order_details thead th { color: var(--color-primary-dark); font-weight: 600; }
.dalc-ty__order-card table.order_details .product-total,
.dalc-ty__order-card table.order_details tfoot td { text-align: right; white-space: nowrap; }
.dalc-ty__order-card table.order_details tfoot th { font-weight: 600; }
.dalc-ty__order-card table.order_details tfoot tr:last-child th,
.dalc-ty__order-card table.order_details tfoot tr:last-child td {
	font-size: 1.15rem;
	color: var(--color-primary-dark);
	font-weight: 700;
	border-bottom: none;
	padding-top: 1.1rem;
}
.dalc-ty__order-card td.product-name a[href*="getaccesstocourse"],
.dalc-ty__order-card td.product-name a[href*="talentlms"] {
	display: inline-block;
	margin-top: .65rem;
	padding: .45rem 1.1rem;
	background: var(--color-primary);
	color: #fff !important;
	border-radius: 9999px;
	font-size: .9rem;
	font-weight: 600;
	text-decoration: none;
}
.dalc-ty__order-card .woocommerce-customer-details {
	margin-top: 2rem;
	padding-top: 1.75rem;
	border-top: 1px solid var(--color-primary-border);
}
.dalc-ty__order-card .woocommerce-customer-details address {
	font-style: normal;
	border: 0;
	padding: 0;
	line-height: 1.7;
	color: var(--color-text);
}

.dalc-ty__support { padding: 2.5rem 0 3.5rem; }
.dalc-ty__support p { margin: 0; color: var(--color-text); }

/* Accessibility — inline text links must be underlined (buttons/pills excepted) */
.dalc-ty__support a,
.dalc-ty__order-card .woocommerce-customer-details a,
.dalc-ty__order-card table.order_details a:not([href*="getaccesstocourse"]):not([href*="talentlms"]) {
	text-decoration: underline;
}
</style>

<main id="primary" class="site-main dalc-ty">

	<!-- Hero (mirrors template-parts/hero/page-hero.php) -->
	<section class="relative overflow-visible pb-6 pt-6 md:pt-8 lg:pt-12 xl:pt-16">

		<div class="pointer-events-none absolute inset-0 z-0 hidden lg:block" aria-hidden="true">
			<div style="position:absolute; width:41rem; height:41rem; left:-12rem; bottom:-10rem; border-radius:9999px; background:rgba(0,85,125,0.12); filter:blur(9rem);"></div>
			<div style="position:absolute; width:52rem; height:52rem; right:20rem; top:-17rem; border-radius:9999px; background:rgba(0,85,125,0.08); filter:blur(10rem);"></div>
			<img src="<?php echo esc_url($dot_svg); ?>" alt="" aria-hidden="true" width="985" height="986" loading="lazy" decoding="async"
				style="position:absolute; width:52rem; right:-15rem; top:-50rem; max-width:none; opacity:0.24; transform:scale(1.2);">
		</div>

		<div class="container relative z-10">
			<div class="grid gap-8 lg:grid-cols-12 lg:items-center lg:gap-10 xl:gap-16">

				<div class="flex flex-col items-start gap-5 lg:col-span-7 lg:gap-6 xl:pr-4">
					<?php
					get_template_part('template-parts/components/eyebrow', null, [
						'text'       => $eyebrow,
						'icon_class' => 'fa-solid fa-circle-check',
					]);
					?>

					<h1 id="page-hero-heading"><?php echo esc_html($heading); ?></h1>

					<p class="!mb-0 max-w-3xl"><?php echo nl2br(esc_html(wp_strip_all_tags($intro))); ?></p>

					<?php if ($course_link) : ?>
						<div class="flex flex-wrap items-center gap-3 pt-1">
							<?php
							get_template_part('template-parts/components/button', null, [
								'url'     => $course_link,
								'label'   => 'Start the course',
								'variant' => 'primary',
								'class'   => 'whitespace-nowrap',
							]);
							?>
						</div>
						<p class="dalc-ty__cta-note">You’ll need to set your password first (see step 2 below).</p>
					<?php endif; ?>
				</div>

				<div class="relative lg:col-span-5 lg:px-0 lg:pl-2 xl:pl-4">
					<div class="dalc-ty__hero-media relative z-20 w-full overflow-hidden rounded-2xl bg-primary-soft lg:rounded-tl-[4rem] lg:rounded-br-[4rem]">
						<?php if (has_post_thumbnail()) : ?>
							<?php the_post_thumbnail('large', [
								'class'         => 'absolute inset-0 !h-full w-full object-cover object-center',
								'loading'       => 'eager',
								'decoding'      => 'async',
								'fetchpriority' => 'high',
							]); ?>
						<?php else : ?>
							<div class="absolute inset-0 flex items-center justify-center p-8">
								<i class="fa-solid fa-graduation-cap text-6xl text-primary-dark sm:text-7xl lg:text-8xl" aria-hidden="true"></i>
							</div>
						<?php endif; ?>
					</div>
				</div>

			</div>
		</div>
	</section>

	<!-- Next steps -->
	<section class="dalc-ty__section">
		<div class="container">
			<h2 class="dalc-ty__title"><?php echo esc_html($steps_title); ?></h2>
			<div class="dalc-ty__steps">
				<?php $n = 1; foreach ($steps as $step) : ?>
					<div class="dalc-ty__step">
						<span class="dalc-ty__step-num"><?php echo esc_html($n); ?></span>
						<h3><?php echo esc_html($step['title']); ?></h3>
						<p><?php echo esc_html($step['text']); ?></p>
					</div>
				<?php $n++; endforeach; ?>
			</div>
		</div>
	</section>

	<!-- Order details -->
	<?php if ($order && function_exists('woocommerce_order_details_table')) : ?>
		<section class="dalc-ty__section dalc-ty__section--tint">
			<div class="container">
				<div style="max-width:52rem;">
					<h2 class="dalc-ty__title" style="margin-bottom:.5rem;">Your order</h2>
					<p class="dalc-ty__order-meta">Order #<?php echo esc_html($order->get_order_number()); ?></p>

					<div class="dalc-ty__order-card">
						<?php woocommerce_order_details_table($order->get_id()); ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- Support -->
	<section class="dalc-ty__support">
		<div class="container elahub-rich-text">
			<?php echo wp_kses_post($support_html); ?>
		</div>
	</section>

</main>

<?php
get_footer();
