<?php

/**
 * Showcase Module Signup section
 *
 * @package elahub
 */

$section_badge_text = trim((string) (get_sub_field('badge_text') ?: ''));
$section_badge_icon = trim((string) (get_sub_field('badge_icon_class') ?: ''));
$section_heading    = trim((string) (get_sub_field('heading') ?: ''));
$section_body       = get_sub_field('body') ?: '';
$numbered_items     = get_sub_field('numbered_items') ?: [];
$submit_label       = trim((string) (get_sub_field('submit_label') ?: 'Access the Showcase Module'));
$webhook_url        = trim((string) (get_sub_field('webhook_url') ?: ''));
$success_message    = trim((string) (get_sub_field('success_message') ?: 'Thank you — check your inbox for your Showcase Module access link.'));
$section_id         = trim((string) (get_sub_field('section_id') ?: ''));

$notification_emails_raw = get_sub_field('notification_emails') ?: [];
$notification_emails_csv = implode(',', array_filter(array_map(static function ($row) {
	$e = sanitize_email($row['email'] ?? '');
	return is_email($e) ? $e : '';
}, $notification_emails_raw)));

if (! $section_heading && ! $section_body && empty($numbered_items)) {
	return;
}

$form_id = 'elahub-showcase-module-signup-' . get_the_ID() . '-' . wp_unique_id();
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

					<?php if (! empty($numbered_items)) : ?>
						<div class="mt-2">
							<?php
							get_template_part(
								'template-parts/components/numbered-list',
								null,
								['items' => $numbered_items]
							);
							?>
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
							<label for="<?php echo esc_attr($form_id . '-first-name'); ?>" class="elahub-form__label">First name</label>
							<input type="text" id="<?php echo esc_attr($form_id . '-first-name'); ?>" name="first_name" class="elahub-form__input" autocomplete="given-name" required aria-required="true">
						</div>

						<div class="elahub-form__field">
							<label for="<?php echo esc_attr($form_id . '-last-name'); ?>" class="elahub-form__label">Last name</label>
							<input type="text" id="<?php echo esc_attr($form_id . '-last-name'); ?>" name="last_name" class="elahub-form__input" autocomplete="family-name" required aria-required="true">
						</div>

						<div class="elahub-form__field">
							<label for="<?php echo esc_attr($form_id . '-work-email'); ?>" class="elahub-form__label">Work email</label>
							<input type="email" id="<?php echo esc_attr($form_id . '-work-email'); ?>" name="work_email" class="elahub-form__input" autocomplete="email" required aria-required="true">
						</div>

						<div class="elahub-form__field">
							<label for="<?php echo esc_attr($form_id . '-where-heard'); ?>" class="elahub-form__label">Where did you hear about us?</label>
							<select id="<?php echo esc_attr($form_id . '-where-heard'); ?>" name="where_heard" class="elahub-form__input elahub-form__select" required aria-required="true">
								<option value="">Please select…</option>
								<option value="Search engine (Google, Bing, etc.)">Search engine (Google, Bing, etc.)</option>
								<option value="LinkedIn">LinkedIn</option>
								<option value="Other social media">Other social media</option>
								<option value="Recommendation or colleague">Recommendation or colleague</option>
								<option value="Conference or event">Conference or event</option>
								<option value="Susi Miller's book (Designing Accessible Learning Content)">Susi Miller&rsquo;s book (Designing Accessible Learning Content)</option>
								<option value="Newsletter or email">Newsletter or email</option>
								<option value="Other">Other</option>
							</select>
						</div>

						<div class="elahub-form__field">
							<label for="<?php echo esc_attr($form_id . '-role-job-title'); ?>" class="elahub-form__label">Which of the following best describes your role?</label>
							<select id="<?php echo esc_attr($form_id . '-role-job-title'); ?>" name="role_job_title" class="elahub-form__input elahub-form__select" autocomplete="organization-title" required aria-required="true">
								<option value="">Please select…</option>
								<option value="Learning Development manager">Learning Development manager</option>
								<option value="Learning Developer">Learning Developer</option>
								<option value="Content Author">Content Author</option>
								<option value="Instructional Designer">Instructional Designer</option>
								<option value="Other">Other</option>
							</select>
						</div>

						<div class="elahub-form__field">
							<label for="<?php echo esc_attr($form_id . '-organisation-size'); ?>" class="elahub-form__label">Number of people in your organisation</label>
							<select id="<?php echo esc_attr($form_id . '-organisation-size'); ?>" name="organisation_size" class="elahub-form__input elahub-form__select" required aria-required="true">
								<option value="">Please select…</option>
								<option value="less than 10">less than 10</option>
								<option value="less than 100">less than 100</option>
								<option value="less than 1000">less than 1000</option>
								<option value="1000 +">1000 +</option>
							</select>
						</div>

						<div class="elahub-form__field">
							<label for="<?php echo esc_attr($form_id . '-industry'); ?>" class="elahub-form__label">Which best describes your industry?</label>
							<select id="<?php echo esc_attr($form_id . '-industry'); ?>" name="industry" class="elahub-form__input elahub-form__select" required aria-required="true">
								<option value="">Please select…</option>
								<option value="Business Consultancy">Business Consultancy</option>
								<option value="Education">Education</option>
								<option value="Engineering">Engineering</option>
								<option value="Financial">Financial</option>
								<option value="Government">Government</option>
								<option value="Health Provider">Health Provider</option>
								<option value="Learning Provider">Learning Provider</option>
								<option value="Non-Profit">Non-Profit</option>
								<option value="Pharmaceutical">Pharmaceutical</option>
								<option value="Retail">Retail</option>
								<option value="Technology">Technology</option>
								<option value="Other">Other</option>
							</select>
						</div>

						<input type="hidden" name="_nonce"               value="<?php echo esc_attr($nonce); ?>">
						<input type="hidden" name="_form_label"          value="DALC Showcase Module Sign Up">
						<input type="hidden" name="_form_type"           value="showcase_module_signup">
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
