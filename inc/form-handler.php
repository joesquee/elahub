<?php

/**
 * Sign-up / waitlist form AJAX handler
 *
 * Handles all elahub flexible-content form types (dalc_signup, dalc_live_waitlist,
 * short_courses_waitlist, showcase_module_signup). Validates a unified nonce,
 * sanitises submitted data, and sends an admin notification email.
 *
 * Showcase Module sign-up (form type `showcase_module_signup`) additionally:
 *   1. Emails the person a "thanks" message with the Buildcapable XCL link.
 *   2. Adds them to MailChimp, tagged (default "Showcase sign-up").
 * Both extra steps are best-effort — failures are logged, never shown to the visitor.
 *
 * Showcase settings are editable in the admin at:
 *   Theme Settings → Integrations
 * (or overridable via wp-config constants; see elahub_showcase_setting()).
 *
 * Recipient logic (mirrors contact-page-handler.php):
 *   – Local / dev environments  → joe@squee.design only.
 *   – Production               → wp admin email + any extra addresses supplied
 *                                via the ACF `notification_emails` sub-field.
 *
 * @package elahub
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ── Showcase settings admin page (Theme Settings → Integrations) ─────────────── */

add_action( 'acf/init', 'elahub_showcase_register_settings', 11 );
function elahub_showcase_register_settings(): void {

    if ( function_exists( 'acf_add_options_sub_page' ) ) {
        acf_add_options_sub_page(
            array(
                'page_title'  => 'Integrations',
                'menu_title'  => 'Integrations',
                'parent_slug' => 'elahub-theme-settings',
                'menu_slug'   => 'elahub-integrations',
            )
        );
    }

    if ( function_exists( 'acf_add_local_field_group' ) ) {
        acf_add_local_field_group(
            array(
                'key'      => 'group_elahub_showcase_integration',
                'title'    => 'Showcase Module Sign-up (DALC)',
                'fields'   => array(

                    array(
                        'key'   => 'field_elahub_showcase_tab_email',
                        'label' => 'Welcome email',
                        'type'  => 'tab',
                    ),
                    array(
                        'key'          => 'field_elahub_showcase_buildcapable_url',
                        'label'        => 'Buildcapable form URL',
                        'name'         => 'showcase_buildcapable_url',
                        'type'         => 'url',
                        'instructions' => 'The Buildcapable XCL form link emailed to people who sign up for the free Showcase Module. Leave blank and they will be told their link is coming shortly.',
                    ),
                    array(
                        'key'           => 'field_elahub_showcase_email_subject',
                        'label'         => 'Welcome email subject',
                        'name'          => 'showcase_email_subject',
                        'type'          => 'text',
                        'default_value' => 'Your DALC Showcase Module access',
                        'instructions'  => 'Subject line of the welcome email sent to people who sign up. Leave blank to use the default.',
                    ),
                    array(
                        'key'           => 'field_elahub_showcase_email_body',
                        'label'         => 'Welcome email body',
                        'name'          => 'showcase_email_body',
                        'type'          => 'wysiwyg',
                        'tabs'          => 'all',
                        'toolbar'       => 'basic',
                        'media_upload'  => 0,
                        'default_value' => elahub_showcase_default_email_body(),
                        'instructions'  => 'The body of the welcome email. Merge tags you can use: {first_name}, {launch_button} (styled button to the Buildcapable form), {launch_link} (plain URL), {support_email}, {site_name}. Leave blank to fall back to the default template.',
                    ),
                    array(
                        'key'       => 'field_elahub_showcase_test_send',
                        'label'     => 'Send a test',
                        'type'      => 'message',
                        'esc_html'  => 0,
                        'new_lines' => '',
                        'message'   => '<div id="elahub-showcase-test-slot"></div>',
                    ),

                    array(
                        'key'   => 'field_elahub_showcase_tab_mc',
                        'label' => 'MailChimp',
                        'type'  => 'tab',
                    ),
                    array(
                        'key'          => 'field_elahub_showcase_mc_key',
                        'label'        => 'MailChimp API key (override)',
                        'name'         => 'showcase_mailchimp_api_key',
                        'type'         => 'password',
                        'instructions' => 'Optional override for the MAIL_CHIMP_KEY constant in wp-config. If set, this key is used instead. Leave blank to use the wp-config value (recommended — keeps the key out of the database).',
                    ),
                    array(
                        'key'          => 'field_elahub_showcase_mc_audience',
                        'label'        => 'MailChimp Audience ID',
                        'name'         => 'showcase_mailchimp_audience',
                        'type'         => 'text',
                        'instructions' => 'MailChimp → Audience → Settings → “Audience name and defaults” → Audience ID. Leave blank to skip adding sign-ups to MailChimp.',
                    ),
                    array(
                        'key'           => 'field_elahub_showcase_mc_tag',
                        'label'         => 'MailChimp tag',
                        'name'          => 'showcase_mailchimp_tag',
                        'type'          => 'text',
                        'default_value' => 'Showcase sign-up',
                        'instructions'  => 'Tag applied to Showcase sign-ups in MailChimp.',
                    ),
                ),
                'location' => array(
                    array(
                        array(
                            'param'    => 'options_page',
                            'operator' => '==',
                            'value'    => 'elahub-integrations',
                        ),
                    ),
                ),
            )
        );
    }
}

/**
 * Resolve a Showcase setting.
 * Precedence: 1) admin field (Theme Settings → Integrations), 2) wp-config
 * constant (dev override), 3) default.
 */
function elahub_showcase_setting( string $acf_field, string $constant, string $default = '' ): string {
    if ( function_exists( 'get_field' ) ) {
        $v = get_field( $acf_field, 'option' );
        if ( is_string( $v ) && trim( $v ) !== '' ) {
            return trim( $v );
        }
    }
    if ( defined( $constant ) && (string) constant( $constant ) !== '' ) {
        return (string) constant( $constant );
    }
    return $default;
}

/* ── MailChimp key status notice (Theme Settings → Integrations) ─────────────── */
/**
 * Show, on the Integrations screen only, whether a usable MailChimp key is in
 * play and where it came from. Never prints the key.
 *
 * Why this exists: the override field is an ACF `password` type, so it renders
 * masked. On 21 Sep 2026 it was found to contain a 13-character string with no
 * hyphen in it - almost certainly a password pasted into the wrong box. Because
 * the override takes precedence over MAIL_CHIMP_KEY in wp-config, that silently
 * disabled every MailChimp sync from 11 September: elahub_showcase_mailchimp_upsert()
 * cannot parse a data centre from a key with no "-", so it returns before making
 * any API call. The forms kept saying "Thank you!" and nobody could see a thing
 * was wrong. A masked field that quietly overrides a working value needs to say
 * what it is doing.
 */
add_action( 'admin_notices', 'elahub_mailchimp_key_status_notice' );
function elahub_mailchimp_key_status_notice(): void {

    if ( ( $_GET['page'] ?? '' ) !== 'elahub-integrations' ) {
        return;
    }

    $override = function_exists( 'get_field' ) ? (string) get_field( 'showcase_mailchimp_api_key', 'option' ) : '';
    $override = trim( $override );
    $from_wpconfig = defined( 'MAIL_CHIMP_KEY' ) && (string) constant( 'MAIL_CHIMP_KEY' ) !== '';

    $key    = $override !== '' ? $override : ( $from_wpconfig ? (string) constant( 'MAIL_CHIMP_KEY' ) : '' );
    $source = $override !== '' ? 'the override field below' : ( $from_wpconfig ? 'MAIL_CHIMP_KEY in wp-config' : '' );

    if ( $key === '' ) {
        printf(
            '<div class="notice notice-error"><p><strong>MailChimp is not connected.</strong> No API key is set — neither in the override field below nor as %s in wp-config. Sign-ups will not reach MailChimp.</p></div>',
            '<code>MAIL_CHIMP_KEY</code>'
        );
        return;
    }

    // A MailChimp key is 32 hex characters, a hyphen, then the data centre.
    $well_formed = (bool) preg_match( '/^[0-9a-f]{32}-[a-z]{2}\d+$/i', $key );
    $dc          = strpos( $key, '-' ) !== false ? substr( strrchr( $key, '-' ), 1 ) : '';

    if ( ! $well_formed ) {
        printf(
            '<div class="notice notice-error"><p><strong>MailChimp key looks wrong — sign-ups are not reaching MailChimp.</strong> The key in use comes from %1$s and is %2$d characters%3$s. A MailChimp key is 36 characters: 32 hex digits, a hyphen, then the data centre (for this account, <code>us3</code>). %4$s</p></div>',
            esc_html( $source ),
            strlen( $key ),
            $dc === '' ? ' with no hyphen in it, so no data centre can be read from it' : '',
            $override !== ''
                ? 'Clear the override field below to fall back to <code>MAIL_CHIMP_KEY</code> in wp-config, or paste a valid key.'
                : 'Correct <code>MAIL_CHIMP_KEY</code> in wp-config.'
        );
        return;
    }

    printf(
        '<div class="notice notice-success"><p><strong>MailChimp key OK.</strong> Using the key from %s (data centre <code>%s</code>).</p></div>',
        esc_html( $source ),
        esc_html( $dc )
    );
}

/* ── Mail failure diagnostics ────────────────────────────────────────────────── */
// wp_mail() only returns true/false; the actual reason (SMTP refusal, send
// limit, misconfiguration) is in the wp_mail_failed hook. Log it so mail
// problems are diagnosable from the server error log.
add_action( 'wp_mail_failed', static function ( $error ) {
    if ( is_wp_error( $error ) ) {
        error_log( '[elahub] wp_mail failed: ' . $error->get_error_message() );
    }
} );

/* ── Form submission handler ─────────────────────────────────────────────────── */

add_action( 'wp_ajax_nopriv_elahub_form_submit', 'elahub_handle_form_submit' );
add_action( 'wp_ajax_elahub_form_submit',        'elahub_handle_form_submit' );

function elahub_handle_form_submit(): void {

    // ── 1. Nonce ──────────────────────────────────────────────────────────────
    $nonce = isset( $_POST['_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ) : '';

    if ( ! wp_verify_nonce( $nonce, 'elahub_form_submit' ) ) {
        wp_send_json_error( [
            'message' => 'Your session has expired. Please refresh the page and try again.',
        ] );
    }

    // ── 2. Meta fields (not sent as email body rows) ──────────────────────────
    $form_label  = sanitize_text_field( wp_unslash( $_POST['_form_label']  ?? '' ) );
    $form_type   = sanitize_key( wp_unslash( $_POST['_form_type'] ?? '' ) );
    $success_msg = sanitize_text_field( wp_unslash( $_POST['_success_msg'] ?? 'Thank you — we’ll be in touch shortly.' ) );

    // ── 3. Collect user-submitted fields ──────────────────────────────────────
    $reserved = [ '_nonce', 'action', '_form_label', '_form_type', '_success_msg', '_notification_emails' ];

    $fields = [];
    foreach ( $_POST as $raw_key => $raw_val ) {
        if ( in_array( $raw_key, $reserved, true ) ) {
            continue;
        }
        // Convert snake_case / kebab-case to a readable label
        $label = ucwords( str_replace( [ '_', '-' ], ' ', $raw_key ) );
        $fields[ $label ] = sanitize_text_field( wp_unslash( (string) $raw_val ) );
    }

    if ( empty( $fields ) ) {
        wp_send_json_error( [ 'message' => 'No form data received. Please try again.' ] );
    }

    // ── 3b. Required fields (server-side) ─────────────────────────────────────
    // The Showcase form is fully compulsory (client requirement, Jul 2026); the
    // browser enforces the `required` attributes, this is the belt-and-braces.
    if ( 'showcase_module_signup' === $form_type ) {
        $required = [
            'first_name'        => 'First name',
            'last_name'         => 'Last name',
            'work_email'        => 'Work email',
            'where_heard'       => 'Where did you hear about us?',
            'role_job_title'    => 'Your role',
            'organisation_size' => 'Number of people in your organisation',
            'industry'          => 'Your industry',
        ];
        $missing = [];
        foreach ( $required as $req_key => $req_label ) {
            if ( '' === trim( sanitize_text_field( wp_unslash( (string) ( $_POST[ $req_key ] ?? '' ) ) ) ) ) {
                $missing[] = $req_label;
            }
        }
        if ( $missing ) {
            wp_send_json_error( [ 'message' => 'Please complete: ' . implode( ', ', $missing ) . '.' ] );
        }
    }

    // ── 4. Recipients ─────────────────────────────────────────────────────────
    $is_local = in_array(
        $_SERVER['HTTP_HOST'] ?? '',
        [ 'localhost', '127.0.0.1', 'localhost:8888', '127.0.0.1:8888' ],
        true
    ) || in_array( wp_get_environment_type(), [ 'local', 'development' ], true );

    if ( $is_local ) {
        $recipients = [ 'joe@squee.design' ];
    } else {
        $recipients = [ get_option( 'admin_email' ) ];

        $extra_raw = sanitize_text_field( wp_unslash( $_POST['_notification_emails'] ?? '' ) );
        foreach ( explode( ',', $extra_raw ) as $candidate ) {
            $candidate = sanitize_email( trim( $candidate ) );
            if ( $candidate && is_email( $candidate ) ) {
                $recipients[] = $candidate;
            }
        }

        $recipients = array_values( array_unique( array_filter( $recipients ) ) );
    }

    // ── 5. Email body ─────────────────────────────────────────────────────────
    $subject = sprintf(
        'New %s submission',
        $form_label ?: 'sign-up form'
    );

    $body   = [];
    $body[] = sprintf( 'A new "%s" submission has been received.', $form_label ?: 'sign-up form' );
    $body[] = '';

    foreach ( $fields as $label => $value ) {
        $body[] = sprintf( '%s: %s', $label, $value !== '' ? $value : '—' );
    }

    $body[] = '';
    $body[] = 'Submitted from: ' . ( isset( $_SERVER['HTTP_REFERER'] )
        ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) )
        : home_url() );

    // ── 6. Headers (Reply-To from submitted email, if any) ────────────────────
    $headers = [ 'Content-Type: text/plain; charset=UTF-8' ];

    $reply_email = '';
    foreach ( $_POST as $key => $val ) {
        if ( str_contains( strtolower( $key ), 'email' ) ) {
            $candidate = sanitize_email( wp_unslash( (string) $val ) );
            if ( is_email( $candidate ) ) {
                $reply_email = $candidate;
                break;
            }
        }
    }

    if ( $reply_email ) {
        $reply_name = sanitize_text_field(
            wp_unslash( $_POST['first_name'] ?? $_POST['full_name'] ?? $_POST['name'] ?? '' )
        );
        $headers[] = $reply_name
            ? 'Reply-To: ' . $reply_name . ' <' . $reply_email . '>'
            : 'Reply-To: ' . $reply_email;
    }

    // ── 7. Send admin notification (best effort) ──────────────────────────────
    // This email is for the site owners, not the visitor. If it fails (e.g. the
    // host's hourly send limit), the sign-up itself has still been processed —
    // MailChimp and the welcome email below run regardless — so log it and
    // carry on rather than showing the visitor an error.
    $sent = wp_mail( $recipients, $subject, implode( "\n", $body ), $headers );
    if ( ! $sent ) {
        error_log( '[elahub] Admin notification email failed for "' . ( $form_label ?: 'sign-up form' ) . '" submission (visitor still gets success).' );
    }

    // ── 8. Follow-ups (best effort) ───────────────────────────────────────────
    // Showcase gets its welcome email + MailChimp; every other form (DALC Sign
    // Up, DALC Live Waitlist, Short Courses Waitlist, …) is added to MailChimp
    // tagged with its form label.
    if ( 'showcase_module_signup' === $form_type ) {
        elahub_showcase_after_submit();
    } else {
        elahub_generic_mailchimp_after_submit( $form_label );
    }

    wp_send_json_success( [ 'message' => $success_msg ] );
}

/**
 * Showcase Module follow-up: welcome email + MailChimp. Never throws.
 */
function elahub_showcase_after_submit(): void {
    $email = '';
    foreach ( $_POST as $key => $val ) {
        if ( str_contains( strtolower( (string) $key ), 'email' ) ) {
            $candidate = sanitize_email( wp_unslash( (string) $val ) );
            if ( is_email( $candidate ) ) { $email = $candidate; break; }
        }
    }
    if ( ! $email ) {
        return;
    }

    $first = sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) );
    $last  = sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) );

    elahub_showcase_send_welcome_email( $email, $first );

    if ( elahub_showcase_setting( 'showcase_mailchimp_audience', 'ELAHUB_SHOWCASE_MAILCHIMP_AUDIENCE' ) ) {
        // Map the Showcase form fields to the audience's merge tags.
        //   first_name        → FNAME    (text, required)
        //   last_name         → LNAME    (text, required)
        //   organisation_size → MMERGE6  (dropdown)
        //   industry          → MMERGE7  (dropdown)
        //   role_job_title    → MMERGE8  (dropdown: role)
        //   where_heard       → MMERGE12 (text: "Where did you hear about us?")
        $merge = [
            'FNAME' => $first,
            'LNAME' => $last,
        ];

        $size     = sanitize_text_field( wp_unslash( $_POST['organisation_size'] ?? '' ) );
        $industry = sanitize_text_field( wp_unslash( $_POST['industry'] ?? '' ) );
        $role     = sanitize_text_field( wp_unslash( $_POST['role_job_title'] ?? '' ) );
        $heard    = sanitize_text_field( wp_unslash( $_POST['where_heard'] ?? '' ) );

        if ( '' !== $size )     { $merge['MMERGE6']  = $size; }
        if ( '' !== $industry ) { $merge['MMERGE7']  = $industry; }
        if ( '' !== $role )     { $merge['MMERGE8']  = $role; }
        if ( '' !== $heard )    { $merge['MMERGE12'] = $heard; }

        elahub_showcase_mailchimp_upsert( $email, $merge );
    }
}

/**
 * MailChimp follow-up for every non-Showcase form (waitlists + sign-ups).
 *
 * Adds the contact to the audience tagged with the form's label (e.g.
 * "DALC Live Waitlist"). Handles the waitlist field shape: a single full_name
 * (split into first/last), free-text role_job_title → Job Title (MMERGE9),
 * organisation → Company Name (MMERGE10). Never throws.
 */
function elahub_generic_mailchimp_after_submit( string $form_label ): void {
    if ( ! elahub_showcase_setting( 'showcase_mailchimp_audience', 'ELAHUB_SHOWCASE_MAILCHIMP_AUDIENCE' ) ) {
        return;
    }

    $email = '';
    foreach ( $_POST as $key => $val ) {
        if ( str_contains( strtolower( (string) $key ), 'email' ) && ! str_starts_with( (string) $key, '_' ) ) {
            $candidate = sanitize_email( wp_unslash( (string) $val ) );
            if ( is_email( $candidate ) ) { $email = $candidate; break; }
        }
    }
    if ( ! $email ) {
        return;
    }

    // Name: prefer explicit first/last fields, else split full_name.
    $first = sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) );
    $last  = sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) );
    if ( '' === $first && '' === $last ) {
        $full = trim( sanitize_text_field( wp_unslash( $_POST['full_name'] ?? '' ) ) );
        if ( '' !== $full ) {
            $parts = preg_split( '/\s+/', $full );
            $first = array_shift( $parts );
            $last  = implode( ' ', $parts );
        }
    }

    $merge = [
        'FNAME' => $first,
        'LNAME' => $last,
    ];

    // Free-text extras → free-text merge fields.
    $role = sanitize_text_field( wp_unslash( $_POST['role_job_title'] ?? '' ) );
    $org  = sanitize_text_field( wp_unslash( $_POST['organisation'] ?? $_POST['company'] ?? '' ) );
    if ( '' !== $role ) { $merge['MMERGE9']  = $role; }
    if ( '' !== $org )  { $merge['MMERGE10'] = $org; }

    $tag = $form_label !== '' ? $form_label : 'Website sign-up';

    elahub_showcase_mailchimp_upsert( $email, $merge, $tag );
}

/**
 * The built-in default Showcase welcome-email body (merge-tag markup).
 * Used as the ACF field default and as the fallback when the admin body is blank.
 */
function elahub_showcase_default_email_body(): string {
    return
          '<p>Hi {first_name},</p>'
        . '<p>Thanks for signing up for the free Showcase Module from the Designing Accessible Learning Content (DALC) Programme. This gives you a genuine feel for the structure and practical approach of the full programme.</p>'
        . '<p>You can launch the Showcase course here:</p>'
        . '<p>{launch_button}</p>'
        . '<p>Or copy this link into your browser:<br>{launch_link}</p>'
        . '<p>The Showcase Module is delivered using BuildCapable, an authoring tool for accessible learning content.</p>'
        . '<p>If you have any questions, just reply to this email or contact us at {support_email}.</p>'
        . '<p>Best wishes,<br>The {site_name} team</p>';
}

/**
 * Send the Showcase Module welcome email to the visitor.
 *
 * Subject and body are editable in Theme Settings → Integrations. The body
 * supports merge tags: {first_name}, {launch_button}, {launch_link},
 * {support_email}, {site_name}. A blank body falls back to the default template.
 */
function elahub_showcase_send_welcome_email( string $email, string $first_name ): bool {
    $link      = elahub_showcase_setting( 'showcase_buildcapable_url', 'ELAHUB_SHOWCASE_BUILDCAPABLE_URL', '' );
    $site_name = get_bloginfo( 'name' );

    // Send from the same authenticated address WooCommerce uses for its order
    // emails, so this message passes SPF/DKIM/DMARC and lands in the inbox rather
    // than spam. Falls back to the site admin email if WooCommerce isn't set.
    $from_email = get_option( 'woocommerce_email_from_address' );
    $from_name  = get_option( 'woocommerce_email_from_name' );
    if ( ! $from_email || ! is_email( $from_email ) ) {
        $from_email = get_option( 'admin_email' );
    }
    if ( ! is_string( $from_name ) || '' === trim( (string) $from_name ) ) {
        $from_name = $site_name;
    }
    $support = $from_email;

    // Subject (configurable, with fallback).
    $subject = elahub_showcase_setting( 'showcase_email_subject', 'ELAHUB_SHOWCASE_EMAIL_SUBJECT', 'Your DALC Showcase Module access' );

    // Body (configurable WYSIWYG); fall back to the built-in default when blank.
    $body_html = '';
    if ( function_exists( 'get_field' ) ) {
        $v = get_field( 'showcase_email_body', 'option' );
        if ( is_string( $v ) && trim( $v ) !== '' ) {
            $body_html = $v;
        }
    }
    if ( '' === $body_html ) {
        $body_html = elahub_showcase_default_email_body();
    }

    // Merge-tag values.
    $greeting = $first_name !== '' ? $first_name : 'there';

    if ( $link ) {
        $button = '<a href="' . esc_url( $link ) . '" style="display:inline-block;padding:12px 22px;background:#0b3d4f;color:#ffffff;text-decoration:none;border-radius:9999px;">Launch the Showcase Module</a>';
        $raw    = '<a href="' . esc_url( $link ) . '">' . esc_html( $link ) . '</a>';
    } else {
        $button = 'Your access link will follow shortly.';
        $raw    = '';
    }

    $body_html = strtr( $body_html, [
        '{first_name}'    => esc_html( $greeting ),
        '{launch_button}' => $button,
        '{launch_link}'   => $raw,
        '{support_email}' => '<a href="mailto:' . esc_attr( $support ) . '">' . esc_html( $support ) . '</a>',
        '{site_name}'     => esc_html( $site_name ),
    ] );

    $html = '<div style="font-family:Arial,Helvetica,sans-serif;font-size:16px;color:#1a1a1a;line-height:1.5;">' . $body_html . '</div>';

    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $from_name . ' <' . $from_email . '>',
        'Reply-To: ' . $from_email,
    ];

    $ok = wp_mail( $email, $subject, $html, $headers );
    if ( ! $ok ) {
        error_log( '[elahub] Showcase welcome email failed to send to ' . $email );
    }
    return $ok;
}

/**
 * Add / update the contact in MailChimp and tag them. Logs and swallows failures.
 */
function elahub_showcase_mailchimp_upsert( string $email, array $merge_fields, string $tag = '' ): void {
    // Key precedence: admin override (Theme Settings → Integrations) → MAIL_CHIMP_KEY (wp-config).
    $key = elahub_showcase_setting( 'showcase_mailchimp_api_key', 'MAIL_CHIMP_KEY' );
    if ( '' === $key ) {
        error_log( '[elahub] Showcase MailChimp skipped: no API key (set Theme Settings → Integrations, or MAIL_CHIMP_KEY in wp-config).' );
        return;
    }

    $dc  = substr( strrchr( $key, '-' ), 1 );
    if ( ! $dc ) {
        error_log( '[elahub] Showcase MailChimp skipped: could not parse data-centre from key.' );
        return;
    }

    $audience = elahub_showcase_setting( 'showcase_mailchimp_audience', 'ELAHUB_SHOWCASE_MAILCHIMP_AUDIENCE' );
    if ( '' === $tag ) {
        $tag = elahub_showcase_setting( 'showcase_mailchimp_tag', 'ELAHUB_SHOWCASE_MAILCHIMP_TAG', 'Showcase sign-up' );
    }
    $hash     = md5( strtolower( $email ) );
    $auth     = 'Basic ' . base64_encode( 'anystring:' . $key );
    $base     = 'https://' . $dc . '.api.mailchimp.com/3.0';
    $endpoint = $base . '/lists/' . rawurlencode( $audience ) . '/members/' . $hash;

    $put = static function ( array $merge ) use ( $endpoint, $auth, $email ) {
        return wp_remote_request( $endpoint, [
            'method'  => 'PUT',
            'timeout' => 15,
            'headers' => [ 'Authorization' => $auth, 'Content-Type' => 'application/json' ],
            'body'    => wp_json_encode( [
                'email_address' => $email,
                'status_if_new' => 'subscribed',
                'merge_fields'  => (object) $merge,
            ] ),
        ] );
    };

    $res  = $put( $merge_fields );
    $code = is_wp_error( $res ) ? 0 : wp_remote_retrieve_response_code( $res );

    // A 400 usually means a merge value MailChimp won't accept (e.g. a dropdown
    // option that no longer matches). Don't let that lose the sign-up — retry
    // with just the required name fields so the person is still subscribed.
    if ( 400 === $code && count( $merge_fields ) > 2 ) {
        error_log( '[elahub] Showcase MailChimp 400 with full merge fields; retrying with name only. Response: ' . wp_remote_retrieve_body( $res ) );
        $res  = $put( [
            'FNAME' => $merge_fields['FNAME'] ?? '',
            'LNAME' => $merge_fields['LNAME'] ?? '',
        ] );
        $code = is_wp_error( $res ) ? 0 : wp_remote_retrieve_response_code( $res );
    }

    if ( is_wp_error( $res ) ) {
        error_log( '[elahub] Showcase MailChimp member upsert error: ' . $res->get_error_message() );
        return;
    }
    if ( $code < 200 || $code >= 300 ) {
        error_log( '[elahub] Showcase MailChimp member upsert HTTP ' . $code . ': ' . wp_remote_retrieve_body( $res ) );
        return;
    }

    // A sign-up is fresh consent, so contacts who already exist in the audience
    // but are archived (or previously unsubscribed / never confirmed) must end
    // up subscribed — the upsert above updates their data but does NOT change
    // their status, so set it explicitly here.
    $member = json_decode( wp_remote_retrieve_body( $res ), true );
    $status = is_array( $member ) ? (string) ( $member['status'] ?? '' ) : '';
    if ( in_array( $status, [ 'archived', 'unsubscribed', 'pending' ], true ) ) {
        $set_status = static function ( string $new_status ) use ( $endpoint, $auth ) {
            return wp_remote_request( $endpoint, [
                'method'  => 'PATCH',
                'timeout' => 15,
                'headers' => [ 'Authorization' => $auth, 'Content-Type' => 'application/json' ],
                'body'    => wp_json_encode( [ 'status' => $new_status ] ),
            ] );
        };

        $sub  = $set_status( 'subscribed' );
        $scode = is_wp_error( $sub ) ? 0 : wp_remote_retrieve_response_code( $sub );

        // MailChimp refuses a straight re-subscribe for some contacts who opted
        // out themselves ("compliance state"). In that case set them to pending,
        // which emails them a one-click confirm link instead of failing silently.
        if ( 400 === $scode && str_contains( strtolower( (string) wp_remote_retrieve_body( $sub ) ), 'compliance' ) ) {
            error_log( '[elahub] MailChimp re-subscribe for ' . $email . ' blocked (compliance state); retrying as pending (confirmation email).' );
            $sub   = $set_status( 'pending' );
            $scode = is_wp_error( $sub ) ? 0 : wp_remote_retrieve_response_code( $sub );
        }

        if ( is_wp_error( $sub ) ) {
            error_log( '[elahub] MailChimp re-subscribe error for ' . $email . ': ' . $sub->get_error_message() );
        } elseif ( $scode < 200 || $scode >= 300 ) {
            error_log( '[elahub] MailChimp re-subscribe HTTP ' . $scode . ' for ' . $email . ' (was ' . $status . '): ' . wp_remote_retrieve_body( $sub ) );
        } else {
            error_log( '[elahub] MailChimp re-subscribed ' . $email . ' (was ' . $status . ').' );
        }
    }

    $tag_res = wp_remote_post( $base . '/lists/' . rawurlencode( $audience ) . '/members/' . $hash . '/tags', [
        'timeout' => 15,
        'headers' => [ 'Authorization' => $auth, 'Content-Type' => 'application/json' ],
        'body'    => wp_json_encode( [ 'tags' => [ [ 'name' => $tag, 'status' => 'active' ] ] ] ),
    ] );
    if ( is_wp_error( $tag_res ) ) {
        error_log( '[elahub] Showcase MailChimp tag error: ' . $tag_res->get_error_message() );
    }
}

/* ── "Send a test" for the Showcase welcome email (admin only) ────────────────── */

add_action( 'wp_ajax_elahub_showcase_test_email', 'elahub_showcase_handle_test_email' );
function elahub_showcase_handle_test_email(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Not permitted.' ] );
    }
    check_ajax_referer( 'elahub_showcase_test', 'nonce' );

    $email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
    if ( ! is_email( $email ) ) {
        wp_send_json_error( [ 'message' => 'Please enter a valid email address.' ] );
    }

    $name = wp_get_current_user()->first_name;
    $name = $name !== '' ? $name : 'there';

    if ( elahub_showcase_send_welcome_email( $email, $name ) ) {
        wp_send_json_success( [ 'message' => 'Test email sent to ' . $email ] );
    }
    wp_send_json_error( [ 'message' => 'WordPress could not send the email (check the mail / SMTP setup).' ] );
}

add_action( 'admin_footer', 'elahub_showcase_test_email_ui' );
function elahub_showcase_test_email_ui(): void {
    if ( ! is_admin() || ( $_GET['page'] ?? '' ) !== 'elahub-integrations' ) {
        return;
    }
    $nonce = wp_create_nonce( 'elahub_showcase_test' );
    $ajax  = admin_url( 'admin-ajax.php' );
    ?>
    <script>
    (function(){
        var slot = document.getElementById('elahub-showcase-test-slot');
        if ( ! slot ) { return; }
        slot.innerHTML =
            '<p class="description" style="margin:0 0 8px;">Sends the welcome email above to a test address (using your first name as the sample). Save any changes first — the test uses the last-saved wording.</p>' +
            '<input type="email" id="elahub-showcase-test-email" placeholder="you@example.com" class="regular-text" style="max-width:320px;" /> ' +
            '<button type="button" class="button button-secondary" id="elahub-showcase-test-btn">Send test email</button> ' +
            '<span id="elahub-showcase-test-result" style="margin-left:8px;font-weight:600;"></span>';

        var btn = document.getElementById('elahub-showcase-test-btn');
        var out = document.getElementById('elahub-showcase-test-result');
        btn.addEventListener('click', function(){
            var email = (document.getElementById('elahub-showcase-test-email') || {}).value || '';
            out.style.color = '#555';
            out.textContent = 'Sending…';
            var params = new URLSearchParams();
            params.append('action', 'elahub_showcase_test_email');
            params.append('nonce', '<?php echo esc_js( $nonce ); ?>');
            params.append('email', email);
            fetch('<?php echo esc_url_raw( $ajax ); ?>', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            })
            .then(function(r){ return r.json(); })
            .then(function(j){
                var msg = (j && j.data && j.data.message) ? j.data.message : (j && j.success ? 'Sent' : 'Failed');
                out.textContent = msg;
                out.style.color = (j && j.success) ? '#1a7f37' : '#b32d2e';
            })
            .catch(function(){ out.textContent = 'Request failed.'; out.style.color = '#b32d2e'; });
        });
    })();
    </script>
    <?php
}
