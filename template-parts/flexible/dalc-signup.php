<?php

/**
 * DALC Sign Up — content block + native HTML form.
 *
 * Layout: two-col at desktop — content+form (2fr) | image (1fr).
 * Submitted via JS to the WordPress AJAX handler which sends an admin
 * email notification. Webhook URL can additionally forward to an
 * external CRM / automation (Zapier, Make, HubSpot, etc.)
 *
 * Figma (node 921:17044).
 *
 * @package elahub
 */

$badge_text   = trim((string) (get_sub_field('badge_text') ?: ''));
$badge_icon   = trim((string) (get_sub_field('badge_icon_class') ?: ''));
$heading      = trim((string) (get_sub_field('heading') ?: ''));
$body         = get_sub_field('body') ?: '';
$tick_items   = get_sub_field('tick_items') ?: [];
$image        = get_sub_field('image');
$form_heading = trim((string) (get_sub_field('form_heading') ?: ''));
$submit_label = trim((string) (get_sub_field('submit_label') ?: 'Submit'));
$webhook_url  = trim((string) (get_sub_field('webhook_url') ?: ''));
$success_msg  = trim((string) (get_sub_field('success_message') ?: 'Thank you — we\'ll be in touch shortly.'));

$img_src = '';
$img_alt = '';
if (is_array($image) && ! empty($image['url'])) {
	$img_src = $image['url'];
	$img_alt = $image['alt'] ?? '';
}

if (! $heading) {
	return;
}

$notification_emails_raw = get_sub_field('notification_emails') ?: [];
$notification_emails_csv = implode(',', array_filter(array_map(static function ($row) {
	$e = sanitize_email($row['email'] ?? '');
	return is_email($e) ? $e : '';
}, $notification_emails_raw)));

$form_id = 'elahub-form-' . get_the_ID() . '-' . wp_unique_id();
$nonce   = wp_create_nonce('elahub_form_submit');
?>

<section class="relative py-8 md:py-12 lg:py-16"<?php if ($heading) : ?> aria-labelledby="<?php echo esc_attr($form_id); ?>-heading"<?php endif; ?>>

	<?php get_template_part('template-parts/components/section-bg', null, ['side' => 'right']); ?>

	<div class="container relative z-10">
		<div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] lg:gap-16">

			<div class="flex flex-col gap-8">

				<?php /* Content: badge → heading → body → mobile image → ticks */ ?>
				<div class="flex flex-col gap-6">

					<?php if ($badge_text) : ?>
						<div class="self-start">
							<?php
							get_template_part(
								'template-parts/components/eyebrow',
								null,
								[
									'text'       => $badge_text,
									'icon_class' => $badge_icon ?: 'fa-solid fa-universal-access',
								]
							);
							?>
						</div>
					<?php endif; ?>

					<?php if ($heading) : ?>
						<h2 class="!mb-0" id="<?php echo esc_attr($form_id); ?>-heading"><?php echo esc_html($heading); ?></h2>
					<?php endif; ?>

					<?php if ($body) : ?>
						<div class="elahub-feature__body elahub-rich-text">
							<?php echo wp_kses_post($body); ?>
						</div>
					<?php endif; ?>

					<?php /* Image — mobile only */ ?>
					<?php if ($img_src) : ?>
						<div class="overflow-hidden rounded-2xl rounded-br-[4rem] lg:hidden">
							<img
								src="<?php echo esc_url($img_src); ?>"
								alt="<?php echo esc_attr($img_alt); ?>"
								class="block h-64 w-full object-cover object-center sm:h-80"
								loading="lazy"
								decoding="async">
						</div>
					<?php endif; ?>

					<?php if (! empty($tick_items)) : ?>
						<?php
						get_template_part(
							'template-parts/components/tick-list',
							null,
							['items' => array_column($tick_items, 'text')]
						);
						?>
					<?php endif; ?>

				</div>

				<hr class="!m-0 !border-t !border-b-0 border-primary-border">

				<?php /* Native form */ ?>
				<div class="elahub-form">

					<?php if ($form_heading) : ?>
						<h3 class="h4 !mb-0"><?php echo esc_html($form_heading); ?></h3>
					<?php endif; ?>

					<form
						id="<?php echo esc_attr($form_id); ?>"
						class="elahub-form__fields"
						novalidate
						data-webhook="<?php echo esc_url($webhook_url); ?>"
						data-success="<?php echo esc_attr($success_msg); ?>">

						<div class="elahub-form__field">
							<label for="<?php echo esc_attr($form_id . '-full-name'); ?>" class="elahub-form__label">Full Name</label>
							<input type="text" id="<?php echo esc_attr($form_id . '-full-name'); ?>" name="full_name" class="elahub-form__input" autocomplete="name" required aria-required="true">
						</div>

						<div class="elahub-form__field">
							<label for="<?php echo esc_attr($form_id . '-work-email'); ?>" class="elahub-form__label">Work email</label>
							<input type="email" id="<?php echo esc_attr($form_id . '-work-email'); ?>" name="work_email" class="elahub-form__input" autocomplete="email" required aria-required="true">
						</div>

						<div class="elahub-form__field">
							<label for="<?php echo esc_attr($form_id . '-organisation'); ?>" class="elahub-form__label">Organisation</label>
							<input type="text" id="<?php echo esc_attr($form_id . '-organisation'); ?>" name="organisation" class="elahub-form__input" autocomplete="organization" required aria-required="true">
						</div>

						<div class="elahub-form__field">
							<label for="<?php echo esc_attr($form_id . '-role-job-title'); ?>" class="elahub-form__label">Role / job title</label>
							<input type="text" id="<?php echo esc_attr($form_id . '-role-job-title'); ?>" name="role_job_title" class="elahub-form__input" autocomplete="organization-title" required aria-required="true">
						</div>

						<input type="hidden" name="_nonce"               value="<?php echo esc_attr($nonce); ?>">
						<input type="hidden" name="_form_label"          value="DALC Sign Up">
						<input type="hidden" name="_success_msg"         value="<?php echo esc_attr($success_msg); ?>">
						<input type="hidden" name="_notification_emails" value="<?php echo esc_attr($notification_emails_csv); ?>">

						<div class="elahub-form__status" role="alert" aria-live="polite" tabindex="-1" hidden></div>

						<div class="elahub-form__footer pt-2">
							<button
								type="submit"
								class="inline-flex items-center justify-center gap-2 rounded-full !bg-primary-dark px-6 py-3 font-normal !text-white transition hover:!bg-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2">
								<?php echo esc_html($submit_label); ?>
							</button>
						</div>

					</form>
				</div>

			</div>

			<?php /* Right: image — desktop only, sticky */ ?>
			<?php if ($img_src) : ?>
				<div class="hidden lg:block">
					<div class="sticky top-8 overflow-hidden rounded-2xl rounded-br-[4rem]">
						<img
							src="<?php echo esc_url($img_src); ?>"
							alt="<?php echo esc_attr($img_alt); ?>"
							class="block h-auto w-full object-cover"
							loading="lazy"
							decoding="async">
					</div>
				</div>
			<?php endif; ?>

		</div>
	</div>
</section>
