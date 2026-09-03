/**
 * eLaHub sign-up / waitlist form handler
 *
 * Intercepts every `.elahub-form__fields` submit event on the page, posts the
 * data to the WordPress AJAX endpoint, then shows the success or error message
 * and moves keyboard focus to it — satisfying WCAG 2.1 SC 3.3.1 (error
 * identification) and making the feedback perceivable for screen-reader users.
 *
 * The status element already carries `role="alert"` and `aria-live="polite"`
 * in the PHP markup, so changing its text is announced automatically. Calling
 * `.focus()` additionally ensures keyboard-only users know where to look.
 *
 * @package elahub
 */
( function () {
    'use strict';

    /* ── Bootstrap ─────────────────────────────────────────────────────────── */

    document.addEventListener( 'DOMContentLoaded', function () {
        document.querySelectorAll( '.elahub-form__fields' ).forEach( function ( form ) {
            // Only intercept AJAX-backed forms — those include a _nonce hidden
            // input. Forms that submit via admin-post.php (e.g. the contact page)
            // use a different nonce field and must not be caught here.
            if ( form.querySelector( '[name="_nonce"]' ) ) {
                form.addEventListener( 'submit', handleSubmit );
            }
        } );
    } );

    /* ── Submit handler ─────────────────────────────────────────────────────── */

    function handleSubmit( e ) {
        e.preventDefault();

        const form     = e.currentTarget;
        const statusEl = form.querySelector( '.elahub-form__status' );
        const submitBtn = form.querySelector( '[type="submit"]' );

        // Clear any previous feedback.
        if ( statusEl ) {
            statusEl.hidden      = true;
            statusEl.textContent = '';
            statusEl.removeAttribute( 'data-state' );
        }

        // Disable the submit button and signal busyness to assistive tech.
        setButtonBusy( submitBtn, true );

        // Build the payload — FormData captures every <input>, <select>, and
        // <textarea>, including the hidden meta fields added in PHP.
        const payload = new FormData( form );
        payload.set( 'action', 'elahub_form_submit' );

        const ajaxUrl = window.elahubForm
            ? window.elahubForm.ajaxUrl
            : '/wp-admin/admin-ajax.php';

        fetch( ajaxUrl, {
            method:      'POST',
            body:        payload,
            credentials: 'same-origin',
        } )
            .then( function ( res ) {
                if ( ! res.ok ) throw new Error( 'Network response was not OK.' );
                return res.json();
            } )
            .then( function ( json ) {
                const msg = json.data && json.data.message ? json.data.message : '';

                if ( json.success ) {
                    showStatus( form, statusEl, msg, 'success' );
                    // Leave the button disabled — the form is done.
                } else {
                    showStatus( form, statusEl, msg || 'Sorry, there was a problem. Please try again.', 'error' );
                    setButtonBusy( submitBtn, false );
                }
            } )
            .catch( function () {
                showStatus(
                    form,
                    statusEl,
                    'Sorry, something went wrong. Please check your connection and try again.',
                    'error'
                );
                setButtonBusy( submitBtn, false );
            } );
    }

    /* ── Helpers ────────────────────────────────────────────────────────────── */

    /**
     * Show the status message and move focus to it.
     *
     * On success, the form fields and submit footer are hidden so only the
     * confirmation message remains — prevents confusing double-submission.
     *
     * @param {HTMLFormElement}  form
     * @param {HTMLElement|null} statusEl
     * @param {string}           message
     * @param {'success'|'error'} state
     */
    function showStatus( form, statusEl, message, state ) {
        if ( ! statusEl ) return;

        statusEl.textContent = message;
        statusEl.setAttribute( 'data-state', state );
        statusEl.hidden = false;

        // tabindex="-1" lets us call .focus() on a non-interactive element without
        // making it part of the natural tab order.
        if ( ! statusEl.hasAttribute( 'tabindex' ) ) {
            statusEl.setAttribute( 'tabindex', '-1' );
        }

        // Move focus so keyboard and screen-reader users immediately encounter
        // the feedback after submission.
        statusEl.focus();

        if ( state === 'success' ) {
            // Collapse the fields — the form is complete.
            form.querySelectorAll( '.elahub-form__field, .elahub-form__footer' ).forEach( function ( el ) {
                el.hidden = true;
            } );
        }
    }

    /**
     * Enable or disable the submit button and its aria-busy state, with a
     * visible loading spinner + "Submitting…" label while the request runs.
     *
     * Accessibility notes:
     *  - The spinner itself is aria-hidden; the state is conveyed by the
     *    visible label change ("Submitting…"), which screen readers announce,
     *    plus aria-busy for assistive tech that uses it.
     *  - The button keeps its original width (min-width lock) so the layout
     *    doesn't jump when the label changes.
     *  - prefers-reduced-motion: the rotation is disabled; the label change
     *    alone conveys the state.
     *
     * @param {HTMLButtonElement|null} btn
     * @param {boolean}                busy
     */
    function setButtonBusy( btn, busy ) {
        if ( ! btn ) return;
        ensureSpinnerCss();
        btn.disabled = busy;
        if ( busy ) {
            btn.setAttribute( 'aria-busy', 'true' );
            if ( ! btn.dataset.originalLabel ) {
                btn.dataset.originalLabel = btn.innerHTML;
            }
            // Lock the width so the shorter "Submitting…" label doesn't shrink it.
            btn.style.minWidth = btn.offsetWidth + 'px';
            btn.innerHTML =
                '<span class="elahub-form__spinner" aria-hidden="true"></span>Submitting…';
        } else {
            btn.removeAttribute( 'aria-busy' );
            if ( btn.dataset.originalLabel ) {
                btn.innerHTML = btn.dataset.originalLabel;
                delete btn.dataset.originalLabel;
            }
            btn.style.minWidth = '';
        }
    }

    /**
     * Inject the spinner styles once. Kept in JS so the spinner works on any
     * page that loads this script without touching the (purged) theme CSS.
     */
    function ensureSpinnerCss() {
        if ( document.getElementById( 'elahub-form-spinner-css' ) ) return;
        const style = document.createElement( 'style' );
        style.id          = 'elahub-form-spinner-css';
        style.textContent =
            '.elahub-form__spinner{display:inline-block;width:1em;height:1em;margin-right:.55em;vertical-align:-0.125em;border:2px solid currentColor;border-top-color:transparent;border-radius:50%;animation:elahub-form-spin .7s linear infinite}' +
            '@keyframes elahub-form-spin{to{transform:rotate(360deg)}}' +
            '@media (prefers-reduced-motion: reduce){.elahub-form__spinner{animation:none}}';
        document.head.appendChild( style );
    }
} )();
