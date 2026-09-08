<?php

/**
 * Template Name: Contact Page
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
	exit;
}

get_header();

the_post();

$page_id = get_the_ID();

$badge_text        = trim((string) (get_field('contact_badge_text') ?: 'Accessibility & Learning'));
$badge_icon        = trim((string) (get_field('contact_badge_icon_class') ?: 'fa-solid fa-universal-access'));
$heading           = trim((string) (get_field('contact_heading') ?: get_the_title()));
$intro             = trim((string) (get_field('contact_intro') ?: 'We aim to respond within two working days. If your enquiry relates to an existing booking or support request, please include as much detail as you can so we can help quickly.'));
$email_heading     = trim((string) (get_field('contact_email_heading') ?: 'Email'));
$email_address     = trim((string) (get_field('contact_email_address') ?: get_option('admin_email')));
$chat_heading      = trim((string) (get_field('contact_chat_heading') ?: 'Book a chat'));
$chat_copy         = get_field('contact_chat_copy');
$chat_url          = trim((string) (get_field('contact_chat_url') ?: home_url('/contact-elahub/')));
$form_heading      = trim((string) (get_field('contact_form_heading') ?: 'Send an enquiry'));
$form_submit_label = trim((string) (get_field('contact_form_submit_label') ?: 'Submit Enquiry'));
$success_message   = trim((string) (get_field('contact_success_message') ?: 'Thanks for getting in touch. We\'ll come back to you shortly.'));
$form_panel_title  = trim((string) (get_field('contact_form_panel_title') ?: ''));

if (! $chat_copy) {
	$chat_copy = 'Prefer to talk it through? <a href="' . esc_url($chat_url) . '">Book a short call</a> and we\'ll discuss what you need and the best next step.';
}

$form_fields = [
	[
		'label'        => 'Full Name',
		'name'         => 'full_name',
		'type'         => 'text',
		'autocomplete' => 'name',
		'required'     => true,
	],
	[
		'label'        => 'Email Address',
		'name'         => 'email_address',
		'type'         => 'email',
		'autocomplete' => 'email',
		'required'     => true,
	],
	[
		'label'        => 'Organisation',
		'name'         => 'organisation',
		'type'         => 'text',
		'autocomplete' => 'organization',
		'required'     => false,
	],
	[
		'label'        => 'What are you interested in?',
		'name'         => 'interest',
		'type'         => 'select',
		'autocomplete' => 'off',
		'required'     => false,
		'options'      => [
			''                                 => 'Select an option',
			'General enquiry'                  => 'General enquiry',
			'DALC Programme'                   => 'DALC Programme',
			'Tailored training'                => 'Tailored training',
			'Accessibility testing and auditing' => 'Accessibility testing and auditing',
			'Accessibility assessments'        => 'Accessibility assessments',
			'Speaking and advocacy'            => 'Speaking and advocacy',
			'Something else'                   => 'Something else',
		],
	],
	[
		// Options and label match the form on the current elahub.net site, where
		// this question is also required.
		'label'        => 'How did you hear about eLaHub?',
		'name'         => 'heard_about',
		'type'         => 'select',
		'autocomplete' => 'off',
		'required'     => true,
		'options'      => [
			''                => 'Select an option',
			'Webinar/event'   => 'Webinar/event',
			'LinkedIn Ad'     => 'LinkedIn Ad',
			'LinkedIn Post'   => 'LinkedIn Post',
			'Web Search'      => 'Web Search',
			'Word of mouth'   => 'Word of mouth',
			'Other'           => 'Other',
		],
	],
	[
		'label'        => 'Your Message',
		'name'         => 'message',
		'type'         => 'textarea',
		'autocomplete' => 'off',
		'required'     => true,
	],
];

$form_action             = admin_url('admin-post.php');
$form_status             = isset($_GET['contact_status']) ? sanitize_key((string) $_GET['contact_status']) : '';
$form_notice             = '';
$form_error              = '';
$should_focus_form_status = false;

if ('success' === $form_status) {
	$form_notice              = $success_message;
	$should_focus_form_status = true;
} elseif ('error' === $form_status) {
	$form_error               = trim((string) ($_GET['contact_message'] ?? 'Sorry, there was a problem sending your enquiry. Please try again.'));
	$should_focus_form_status = true;
}
?>

<main id="primary" class="site-main contact-page-template">
	<section class="relative overflow-visible py-8 md:py-12 lg:py-16" aria-labelledby="contact-page-heading">
		<div class="pointer-events-none absolute inset-x-0 top-0 z-0 overflow-visible lg:hidden" aria-hidden="true">
			<div style="position:absolute; width:30rem; height:30rem; right:-9rem; top:-5rem; border-radius:9999px; background:rgba(0,85,125,0.22); filter:blur(5.5rem);"></div>
			<div style="position:absolute; width:23rem; height:23rem; left:-7rem; top:12rem; border-radius:9999px; background:rgba(0,85,125,0.12); filter:blur(5rem);"></div>
			<img
				src="<?php echo esc_url(get_template_directory_uri() . '/assets/main-circle-dots.svg'); ?>"
				alt=""
				width="985"
				height="986"
				loading="lazy"
				decoding="async"
				style="position:absolute; width:32rem; right:-10rem; top:-34rem; max-width:none; opacity:0.24; transform:scale(1.2);">
		</div>

		<div class="pointer-events-none absolute inset-0 z-0 hidden lg:block" aria-hidden="true">
			<div style="position:absolute; width:41.0556rem; height:41.0556rem; left:-12rem; bottom:-10rem; border-radius:9999px; background:rgba(0,85,125,0.12); filter:blur(9rem);"></div>
			<div style="position:absolute; width:52rem; height:52rem; right:20rem; top:-17rem; border-radius:9999px; background:rgba(0,85,125,0.08); filter:blur(10rem);"></div>
			<img
				src="<?php echo esc_url(get_template_directory_uri() . '/assets/main-circle-dots.svg'); ?>"
				alt=""
				aria-hidden="true"
				width="985"
				height="986"
				loading="lazy"
				decoding="async"
				style="position:absolute; width:52rem; right:-15rem; top:-50rem; max-width:none; opacity:0.24; transform:scale(1.2);">
		</div>

		<div class="container relative z-10">
			<div class="grid gap-8 lg:grid-cols-12 lg:items-start lg:gap-10 xl:gap-16">
				<div class="flex flex-col items-start gap-6 lg:col-span-5 lg:pt-4 xl:pr-8">
					<?php
					get_template_part(
						'template-parts/components/eyebrow',
						null,
						[
							'text'       => $badge_text,
							'icon_class' => $badge_icon,
						]
					);
					?>

					<h1 id="contact-page-heading" class="!mb-0"><?php echo esc_html($heading); ?></h1>

					<?php if ($intro) : ?>
						<p class="!mb-0 max-w-xl"><?php echo esc_html($intro); ?></p>
					<?php endif; ?>

					<div class="flex flex-col gap-6 pt-1">
						<div class="flex flex-col gap-2">
							<?php if ($email_heading) : ?>
								<h2 class="!mb-0 !text-lg"><?php echo esc_html($email_heading); ?></h2>
							<?php endif; ?>

							<?php if ($email_address) : ?>
								<p class="!mb-0">For general enquiries: <a href="mailto:<?php echo esc_attr(antispambot($email_address)); ?>" class="text-primary underline underline-offset-2 transition-all duration-200 hover:underline-offset-4 hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2"><?php echo esc_html(antispambot($email_address)); ?></a></p>
							<?php endif; ?>
						</div>

						<div class="flex flex-col gap-2">
							<?php if ($chat_heading) : ?>
								<h2 class="!mb-0 !text-lg"><?php echo esc_html($chat_heading); ?></h2>
							<?php endif; ?>

							<?php if ($chat_copy) : ?>
								<div class="elahub-feature__body elahub-rich-text max-w-lg">
									<?php echo wp_kses_post(wpautop($chat_copy)); ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<div class="relative lg:col-span-7 lg:pl-2 xl:pl-4">
					<div class="pointer-events-none absolute inset-0 z-0 hidden overflow-visible lg:block" aria-hidden="true">
						<div style="position:absolute; width:183.9721%; height:183.9721%; left:-1.4817%; top:-68.7129%; border-radius:9999px; background:rgba(0,85,125,0.16); filter:blur(10.5rem);"></div>
						<div style="position:absolute; width:128.7456%; height:128.7456%; left:-12.8919%; top:-13.4653%; border-radius:9999px; background:rgba(0,85,125,0.11); filter:blur(8.5rem);"></div>
						<div style="position:absolute; width:128.7456%; height:128.7456%; left:42.6829%; top:-18.4158%; border-radius:9999px; background:rgba(0,85,125,0.08); filter:blur(8rem);"></div>
						<img
							src="<?php echo esc_url(get_template_directory_uri() . '/assets/main-circle-dots.svg'); ?>"
							alt=""
							width="985"
							height="986"
							loading="lazy"
							decoding="async"
							style="display:none;">
					</div>

					<div class="relative z-20 overflow-hidden rounded-2xl bg-white/85 px-6 py-6 shadow-sm sm:px-8 sm:py-8 lg:rounded-tl-[4rem] lg:rounded-br-[4rem] lg:px-8 lg:py-8 xl:px-10 xl:py-10">
						<?php if ($form_panel_title) : ?>
							<h2 class="!mb-6 !text-lg"><?php echo esc_html($form_panel_title); ?></h2>
						<?php endif; ?>

						<?php if ($form_notice) : ?>
							<div id="contact-form-status" class="mb-6 rounded-lg border border-primary-dark/20 bg-white/70 px-4 py-3 text-sm text-text" role="status" aria-live="polite" tabindex="-1">
								<?php echo esc_html($form_notice); ?>
							</div>
						<?php endif; ?>

						<?php if ($form_error) : ?>
							<div id="contact-form-status" class="mb-6 rounded-lg border border-red-700/20 bg-white/70 px-4 py-3 text-sm text-text" role="alert" tabindex="-1">
								<?php echo esc_html($form_error); ?>
							</div>
						<?php endif; ?>

						<form action="<?php echo esc_url($form_action); ?>" method="post" class="elahub-form__fields">
							<input type="hidden" name="action" value="elahub_submit_contact_form">
							<input type="hidden" name="page_id" value="<?php echo esc_attr((string) $page_id); ?>">
							<?php wp_nonce_field('elahub_contact_form_' . $page_id, 'elahub_contact_nonce'); ?>

							<?php foreach ($form_fields as $field) : ?>
								<?php $field_id = 'elahub-contact-' . $field['name']; ?>

								<div class="elahub-form__field">
									<label for="<?php echo esc_attr($field_id); ?>" class="elahub-form__label">
										<?php echo esc_html($field['label']); ?>
									</label>

									<?php if ('textarea' === $field['type']) : ?>
										<textarea
											id="<?php echo esc_attr($field_id); ?>"
											name="<?php echo esc_attr($field['name']); ?>"
											class="elahub-form__input elahub-form__textarea"
											rows="6"
											autocomplete="<?php echo esc_attr($field['autocomplete']); ?>"
											<?php if ($field['required']) : ?>required aria-required="true" <?php endif; ?>><?php echo isset($_POST[$field['name']]) ? esc_textarea(wp_unslash((string) $_POST[$field['name']])) : ''; ?></textarea>
									<?php elseif ('select' === $field['type']) : ?>
										<?php $current_value = isset($_POST[$field['name']]) ? sanitize_text_field(wp_unslash((string) $_POST[$field['name']])) : ''; ?>
										<select
											id="<?php echo esc_attr($field_id); ?>"
											name="<?php echo esc_attr($field['name']); ?>"
											class="elahub-form__input"
											autocomplete="<?php echo esc_attr($field['autocomplete']); ?>"
											<?php if ($field['required']) : ?>required aria-required="true" <?php endif; ?>>
											<?php foreach (($field['options'] ?? []) as $option_value => $option_label) : ?>
												<option value="<?php echo esc_attr($option_value); ?>" <?php selected($current_value, (string) $option_value); ?>><?php echo esc_html($option_label); ?></option>
											<?php endforeach; ?>
										</select>
									<?php else : ?>
										<input
											type="<?php echo esc_attr($field['type']); ?>"
											id="<?php echo esc_attr($field_id); ?>"
											name="<?php echo esc_attr($field['name']); ?>"
											class="elahub-form__input"
											autocomplete="<?php echo esc_attr($field['autocomplete']); ?>"
											value="<?php echo isset($_POST[$field['name']]) ? esc_attr(wp_unslash((string) $_POST[$field['name']])) : ''; ?>"
											<?php if ($field['required']) : ?>required aria-required="true" <?php endif; ?>>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>

							<div class="elahub-form__footer pt-2">
								<button
									type="submit"
									class="inline-flex items-center justify-center gap-2 rounded-full !bg-primary-dark px-6 py-3 font-normal !text-white transition hover:!bg-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2">
									<?php echo esc_html($form_submit_label); ?>
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</section>

	<?php get_template_part('template-parts/sections/section-awards-strip'); ?>
	<?php get_template_part('template-parts/sections/contact-banner-section'); ?>
</main>

<?php if ($should_focus_form_status) : ?>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			var formStatus = document.getElementById('contact-form-status');

			if (formStatus) {
				formStatus.focus();
			}
		});
	</script>
<?php endif; ?>

<?php
get_footer();
