<?php

/**
 * The header for our theme
 *
 * @package elahub
 */

$logo_filename = 'eLaHub-Logo-Master-Logo (1) 1.svg';
$logo_src      = get_template_directory_uri() . '/assets/' . rawurlencode($logo_filename);
?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>

	<div id="page" class="site min-h-screen bg-surface text-text">
		<a class="elahub-skip-link" href="#primary">
			<?php esc_html_e('Skip to content', 'elahub'); ?>
		</a>

		<header id="masthead" class="site-header relative z-[100] bg-transparent">
			<div class="container">
				<div class="header-row flex py-3 items-center justify-between gap-6">
					<div class="site-branding shrink-0">
						<a href="<?php echo esc_url(home_url('/')); ?>" class="inline-flex items-center" rel="home" aria-label="<?php bloginfo('name'); ?>">
							<img
								src="<?php echo esc_url($logo_src); ?>"
								alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
								class="h-auto w-44 min-w-44">
						</a>
					</div>

					<nav id="site-navigation" class="main-navigation hidden nav:flex nav:items-center nav:gap-10" aria-label="<?php esc_attr_e('Primary', 'elahub'); ?>">
						<?php
						wp_nav_menu(
							[
								'theme_location' => 'menu-1',
								'container'      => false,
								'menu_id'        => 'primary-menu',
								'menu_class'     => 'nav-menu m-0 flex list-none items-center gap-8 p-0 xl:gap-10',
								'fallback_cb'    => false,
								'walker'         => new ELaHub_Nav_Walker(),
							]
						);
						?>

						<?php
						get_template_part(
							'template-parts/components/button',
							null,
							[
								'url'        => home_url('/contact-elahub/'),
								'label'      => 'Contact eLaHub',
								'variant'    => 'primary',
								'class'      => 'shrink-0',
								'aria_label' => 'Contact eLaHub',
							]
						);
						?>
					</nav>

					<div class="flex items-center nav:hidden">
						<button
							id="elahub-mobile-toggle"
							type="button"
							class="flex min-h-6 min-w-6 items-center justify-center p-2 text-primary-dark transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2"
							aria-controls="elahub-mobile-panel"
							aria-expanded="false"
							aria-label="<?php esc_attr_e('Open menu', 'elahub'); ?>">
							<svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
								<path d="M4 7H20" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
								<path d="M4 12H20" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
								<path d="M4 17H20" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
							</svg>
						</button>
					</div>
				</div>
			</div>
		</header>

		<div id="elahub-desktop-overlay" class="hidden fixed inset-x-0 bottom-0 z-[80] bg-black/25" aria-hidden="true"></div>
		<div id="elahub-mobile-overlay" class="hidden fixed inset-0 z-[110] bg-black/35" aria-hidden="true"></div>

		<aside
			id="elahub-mobile-panel"
			class="fixed inset-0 z-[120] w-screen translate-x-full overflow-y-auto bg-white shadow-2xl transition-transform duration-200 ease-out nav:hidden"
			aria-hidden="true"
			aria-label="<?php esc_attr_e('Mobile menu', 'elahub'); ?>">
			<div class="flex min-h-full flex-col">
				<div class="flex items-center justify-between gap-4 border-b border-primary-border px-5 py-5">
					<a href="<?php echo esc_url(home_url('/')); ?>" class="inline-flex items-center" rel="home" aria-label="<?php bloginfo('name'); ?>">
						<img src="<?php echo esc_url($logo_src); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" class="h-auto w-[9.5rem]" width="171" height="51">
					</a>

					<button
						type="button"
						class="flex min-h-6 min-w-6 items-center justify-center p-2 text-primary-dark transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2"
						data-elahub-mobile-close
						aria-label="<?php esc_attr_e('Close menu', 'elahub'); ?>">
						<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
							<path d="M6 6L18 18" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
							<path d="M18 6L6 18" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
						</svg>
					</button>
				</div>

				<div class="flex-1 px-5 py-6">
					<nav aria-label="<?php esc_attr_e('Mobile primary', 'elahub'); ?>">
						<?php
						wp_nav_menu(
							[
								'theme_location' => 'menu-1',
								'container'      => false,
								'menu_class'     => 'm-0 flex list-none flex-col p-0',
								'fallback_cb'    => false,
								'walker'         => new ELaHub_Mobile_Nav_Walker(),
							]
						);
						?>
					</nav>
				</div>

				<div class="border-t border-primary-border px-5 py-5">
					<?php
					get_template_part(
						'template-parts/components/button',
						null,
						[
							'url'        => home_url('/contact/'),
							'label'      => 'Contact eLaHub',
							'variant'    => 'primary',
							'class'      => 'w-full justify-center',
							'aria_label' => 'Contact eLaHub',
						]
					);
					?>
				</div>
			</div>
		</aside>