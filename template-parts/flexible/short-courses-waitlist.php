<?php

/**
 * Short Courses Waitlist section
 *
 * @package elahub
 */

$section_badge_text = trim((string) (get_sub_field('badge_text') ?: ''));
$section_badge_icon = trim((string) (get_sub_field('badge_icon_class') ?: ''));
$section_heading    = trim((string) (get_sub_field('heading') ?: ''));
$section_body       = get_sub_field('body') ?: '';
$ticks_label        = trim((string) (get_sub_field('ticks_label') ?: ''));
$tick_items         = get_sub_field('tick_items') ?: [];
$submit_label       = trim((string) (get_sub_field('submit_label') ?: 'Join the Short Courses Waitlist'));
$webhook_url        = trim((string) (get_sub_field('webhook_url') ?: ''));
$success_message    = trim((string) (get_sub_field('success_message') ?: 'Thank you — we\'ll be in touch shortly.'));
$section_id         = trim((string) (get_sub_field('section_id') ?: ''));

$notification_emails_raw = get_sub_field('notification_emails') ?: [];
$notification_emails_csv = implode(',', array_filter(array_map(static function ($row) {
	$e = sanitize_email($row['email'] ?? '');
	return is_email($e) ? $e : '';
}, $notification_emails_raw)));

if (! $section_heading && ! $section_body && empty($tick_items)) {
	return;
}

$form_id = 'elahub-short-courses-waitlist-' . get_the_ID() . '-' . wp_unique_id();
$nonce   = wp_create_nonce('elahub_form_submit');
?>

<section <?php if ($section_id) : ?>id="<?php echo esc_attr($section_id); ?>" <?php endif; ?>class="relative py-8 md:py-12 lg:py-16"<?php if ($section_heading) : ?> aria-labelledby="<?php echo esc_attr($form_id); ?>-heading"<?php endif; ?>>

	<?php get_template_part('template-parts/components/section-bg', null, ['side' => 'right']); ?>

	<div class="container relative z-10">
		<div class="max-w-7xl">
			<div class="flex flex-col gap-8">

				<div class="flex flex-col gap-6">
					<?php if ($section_badge_text) : ?>
						<div class="self-start">
							<?php
							get_template_part(
								'template-parts/components/eyebrow',
								null,
								[
									'text'       => $section_badge_text,
									'icon_class' => $section_badge_icon ?: 'fa-solid fa-universal-access',
								]
							);
							?>
						</div>
					<?php endif; ?>

					<?php if ($section_heading) : ?>
						<h2 class="!mb-0" id="<?php echo esc_attr($form_id); ?>-heading"><?php echo esc_html($section_heading); ?></h2>
					<?php endif; ?>

					<?php if ($section_body) : ?>
						<div class="elahub-feature__body elahub-rich-text">
							<?php echo wp_kses_post($section_body); ?>
						</div>
					<?php endif; ?>

					<?php if ($ticks_label || ! empty($tick_items)) : ?>
						<div class="flex flex-col gap-3">
							<?php if ($ticks_label) : ?>
								<p class="!mb-0 font-bold"><?php echo esc_html($ticks_label); ?></p>
							<?php endif; ?>

							<?php if (! empty($tick_items)) : ?>
								<?php
								get_template_part(
									'template-parts/components/tick-list',
									null,
									['items' => array_values(array_filter(array_map(static function ($item) {
										return trim((string) ($item['text'] ?? ''));
									}, $tick_items)))]
								);
								?>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>

				<hr class="!m-0 !border-t !border-b-0 border-primary-border">

				<div class="elahub-form max-w-4xl">
					<form
						id="<?php echo esc_attr($form_id); ?>"
						class="elahub-form__fields"
						novalidate
						data-webhook="<?php echo esc_url($webhook_url); ?>"
						data-success="<?php echo esc_attr($success_message); ?>">

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
						<input type="hidden" name="_form_label"          value="Short Courses Waitlist">
						<input type="hidden" name="_success_msg"         value="<?php echo esc_attr($success_message); ?>">
						<input type="hidden" name="_notification_emails" value="<?php echo esc_attr($notification_emails_csv); ?>">

						<div class="elahub-form__status" role="alert" aria-live="polite" tabindex="-1" hidden></div>

						<div class="elahub-form__footer pt-2">
							<button type="submit" class="inline-flex items-center justify-center gap-2 rounded-full !bg-primary-dark px-6 py-3 font-normal !text-white transition hover:!bg-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2">
								<?php echo esc_html($submit_label); ?>
							</button>
						</div>

					</form>
				</div>

			</div>
		</div>
	</div>
</section>
