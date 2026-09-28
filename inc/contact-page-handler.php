<?php

/**
 * Contact page form handler
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
	exit;
}

add_action('admin_post_nopriv_elahub_submit_contact_form', 'elahub_handle_contact_form_submission');
add_action('admin_post_elahub_submit_contact_form', 'elahub_handle_contact_form_submission');

function elahub_handle_contact_form_submission()
{
	$page_id = isset($_POST['page_id']) ? (int) $_POST['page_id'] : 0;

	if (! $page_id || 'page' !== get_post_type($page_id)) {
		wp_safe_redirect(home_url('/contact/?contact_status=error&contact_message=' . rawurlencode('Sorry, that page could not be found.')));
		exit;
	}

	if (! isset($_POST['elahub_contact_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['elahub_contact_nonce'])), 'elahub_contact_form_' . $page_id)) {
		wp_safe_redirect(get_permalink($page_id) . '?contact_status=error&contact_message=' . rawurlencode('Sorry, your form session expired. Please try again.'));
		exit;
	}

	$full_name    = sanitize_text_field(wp_unslash($_POST['full_name'] ?? ''));
	$email        = sanitize_email(wp_unslash($_POST['email_address'] ?? ''));
	$organisation = sanitize_text_field(wp_unslash($_POST['organisation'] ?? ''));
	$interest     = sanitize_text_field(wp_unslash($_POST['interest'] ?? ''));
	$heard_about  = sanitize_text_field(wp_unslash($_POST['heard_about'] ?? ''));
	// Strip tags from the message. It is the one field that was passed through
	// untouched, which is why spam arrived as a wall of raw link markup.
	$message      = trim(wp_strip_all_tags((string) wp_unslash($_POST['message'] ?? '')));

	// Honeypot, timing and (when keys are set) reCAPTCHA. A blocked submission
	// gets the same wording as a validation failure so a bot learns nothing.
	if (function_exists('elahub_form_spam_check')) {
		$spam = elahub_form_spam_check($_POST, 'contact');
		if ($spam !== true) {
			elahub_form_spam_log('Contact form blocked: ' . $spam);
			wp_safe_redirect(get_permalink($page_id) . '?contact_status=error&contact_message=' . rawurlencode('Sorry, we could not send your enquiry. Please try again.'));
			exit;
		}
	}

	if (! $full_name || ! $email || ! is_email($email) || ! $heard_about || ! $message) {
		wp_safe_redirect(get_permalink($page_id) . '?contact_status=error&contact_message=' . rawurlencode('Please complete the required fields before submitting the form.'));
		exit;
	}

	$is_local = in_array(wp_get_environment_type(), ['local', 'development'], true)
		|| in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', 'localhost:8888', '127.0.0.1:8888'], true);

	if ($is_local) {
		$recipients = ['joe@squee.design'];
	} else {
		// Deliberately NOT seeded with the site admin address. That made the
		// admin a silent recipient of every enquiry from every form, which is
		// how a client's inbound mail ended up in our inbox. Admin is only used
		// as a last resort below, when a page has no addresses configured, so
		// nothing is lost quietly.
		$recipients   = [];
		$extra_emails = get_field('contact_notification_emails', $page_id);

		if (is_array($extra_emails)) {
			foreach ($extra_emails as $row) {
				$maybe_email = '';

				if (is_array($row)) {
					$maybe_email = sanitize_email((string) ($row['email'] ?? ''));
				} elseif (is_string($row)) {
					$maybe_email = sanitize_email($row);
				}

				if ($maybe_email && is_email($maybe_email)) {
					$recipients[] = $maybe_email;
				}
			}
		}

		$recipients = array_values(array_unique(array_filter($recipients)));

		if (! $recipients) {
			$recipients = [get_option('admin_email')];
			if (function_exists('elahub_form_spam_log')) {
				elahub_form_spam_log('Contact form on page ' . $page_id . ' has no notification addresses set, falling back to the site admin.');
			}
		}
	}

	$subject = sprintf('New contact enquiry from %s', $full_name);
	$body    = [];
	$body[]  = 'A new contact enquiry has been submitted.';
	$body[]  = '';
	$body[]  = 'Full Name: ' . $full_name;
	$body[]  = 'Email Address: ' . $email;
	$body[]  = 'Organisation: ' . ($organisation ?: 'Not provided');
	$body[]  = 'Interested In: ' . ($interest ?: 'Not provided');
	$body[]  = 'Heard About eLaHub Via: ' . ($heard_about ?: 'Not provided');
	$body[]  = '';
	$body[]  = 'Message:';
	$body[]  = $message;
	$body[]  = '';
	$body[]  = 'Submitted from: ' . get_permalink($page_id);

	$headers = [
		'Content-Type: text/plain; charset=UTF-8',
		'Reply-To: ' . $full_name . ' <' . $email . '>',
	];

	$sent = wp_mail($recipients, $subject, implode("\n", $body), $headers);

	if (! $sent) {
		wp_safe_redirect(get_permalink($page_id) . '?contact_status=error&contact_message=' . rawurlencode('Sorry, there was a problem sending your enquiry. Please try again.'));
		exit;
	}

	wp_safe_redirect(get_permalink($page_id) . '?contact_status=success');
	exit;
}
