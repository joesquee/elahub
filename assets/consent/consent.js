/**
 * eLaHub cookie consent.
 *
 * Loads in the head, before first paint, so a returning visitor's tags are
 * activated as early as possible and a new visitor never sees a flash of the
 * banner appearing late.
 *
 * The markup is rendered identically for everyone (see class-elahub-consent.php
 * for why), so this file is solely responsible for deciding what the visitor
 * actually sees and what is allowed to run.
 */
(function () {
	'use strict';

	var CFG = window.ELAHUB_CONSENT_CONFIG || {
		cookie: 'elahub_consent',
		version: '1',
		lifetime: 182,
		secure: false
	};

	var CATEGORIES = ['analytics', 'marketing'];

	/* -----------------------------------------------------------------
	 * Stored choice
	 * -------------------------------------------------------------- */

	function readConsent() {
		var match = document.cookie.match(
			new RegExp('(?:^|; )' + CFG.cookie.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '=([^;]*)')
		);
		if (!match) {
			return null;
		}
		try {
			var stored = JSON.parse(decodeURIComponent(match[1]));
			// A category change invalidates the old choice and re-asks.
			if (!stored || stored.v !== CFG.version) {
				return null;
			}
			return stored;
		} catch (e) {
			return null;
		}
	}

	function writeConsent(state) {
		var value = encodeURIComponent(JSON.stringify({
			v: CFG.version,
			analytics: !!state.analytics,
			marketing: !!state.marketing,
			ts: Date.now()
		}));

		var expires = new Date(Date.now() + CFG.lifetime * 864e5).toUTCString();
		var cookie = CFG.cookie + '=' + value +
			'; path=/; expires=' + expires + '; SameSite=Lax';

		if (CFG.secure) {
			cookie += '; Secure';
		}

		document.cookie = cookie;
	}

	/* -----------------------------------------------------------------
	 * Google Consent Mode v2
	 * -------------------------------------------------------------- */

	function gtagSafe() {
		window.dataLayer = window.dataLayer || [];
		if (typeof window.gtag !== 'function') {
			window.gtag = function () {
				window.dataLayer.push(arguments);
			};
		}
		return window.gtag;
	}

	function sendConsentSignal(state) {
		var g = gtagSafe();
		var granted = 'granted';
		var denied = 'denied';

		g('consent', 'update', {
			analytics_storage: state.analytics ? granted : denied,
			ad_storage: state.marketing ? granted : denied,
			ad_user_data: state.marketing ? granted : denied,
			ad_personalization: state.marketing ? granted : denied,
			personalization_storage: state.marketing ? granted : denied
		});

		g('set', 'ads_data_redaction', !state.marketing);

		window.dataLayer.push({
			event: 'elahub_consent_update',
			elahub_consent_analytics: !!state.analytics,
			elahub_consent_marketing: !!state.marketing
		});
	}

	/* -----------------------------------------------------------------
	 * Releasing the blocked tags
	 * -------------------------------------------------------------- */

	function isAllowed(category, state) {
		if (category === 'analytics') {
			return !!state.analytics;
		}
		if (category === 'marketing') {
			return !!state.marketing;
		}
		// The Google tag stack is released once either category is agreed to.
		// What it is then permitted to do is governed by the Consent Mode
		// signals sent above, per category.
		if (category === 'google') {
			return !!state.analytics || !!state.marketing;
		}
		return false;
	}

	/**
	 * A script element that is already in the DOM will not execute just because
	 * its type changed, so each released tag is rebuilt as a fresh element and
	 * swapped in. Document order is preserved, which keeps the gtag loader
	 * ahead of the config calls that depend on it.
	 */
	function releaseTags(state) {
		var blocked = document.querySelectorAll('script[type="text/plain"][data-elahub-consent]');

		Array.prototype.forEach.call(blocked, function (old) {
			if (!isAllowed(old.getAttribute('data-elahub-consent'), state)) {
				return;
			}

			var fresh = document.createElement('script');

			Array.prototype.forEach.call(old.attributes, function (attr) {
				if (attr.name === 'type' || attr.name === 'data-elahub-consent' || attr.name === 'data-cd-src') {
					return;
				}
				fresh.setAttribute(attr.name, attr.value);
			});

			var src = old.getAttribute('data-cd-src');
			if (src) {
				fresh.src = src;
			} else {
				fresh.text = old.textContent;
			}

			old.parentNode.replaceChild(fresh, old);
		});
	}

	function applyConsent(state) {
		sendConsentSignal(state);
		releaseTags(state);
	}

	/* -----------------------------------------------------------------
	 * UI
	 * -------------------------------------------------------------- */

	var ui = {};
	var lastFocused = null;

	function q(name, scope) {
		return (scope || document).querySelector('[data-elahub-consent-' + name + ']');
	}

	function announce(message) {
		if (ui.status) {
			ui.status.textContent = message;
		}
	}

	function showBanner() {
		ui.root.hidden = false;
		ui.banner.hidden = false;
		document.documentElement.classList.add('elahub-consent-active');
	}

	function hideBanner() {
		if (ui.banner) {
			ui.banner.hidden = true;
		}
		document.documentElement.classList.remove('elahub-consent-active');
	}

	function focusableIn(el) {
		return Array.prototype.filter.call(
			el.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])'),
			function (node) {
				return node.offsetParent !== null;
			}
		);
	}

	function trapFocus(event) {
		if (event.key !== 'Tab' || ui.overlay.hidden) {
			return;
		}
		var items = focusableIn(ui.modal);
		if (!items.length) {
			return;
		}
		var first = items[0];
		var last = items[items.length - 1];

		if (event.shiftKey && document.activeElement === first) {
			event.preventDefault();
			last.focus();
		} else if (!event.shiftKey && document.activeElement === last) {
			event.preventDefault();
			first.focus();
		}
	}

	function openModal() {
		lastFocused = document.activeElement;

		var current = readConsent() || { analytics: false, marketing: false };
		CATEGORIES.forEach(function (cat) {
			var toggle = document.querySelector('[data-elahub-consent-toggle="' + cat + '"]');
			if (toggle) {
				toggle.checked = !!current[cat];
			}
		});

		ui.root.hidden = false;
		ui.overlay.hidden = false;
		document.documentElement.classList.add('elahub-consent-modal-open');
		ui.modal.focus();
	}

	function closeModal() {
		ui.overlay.hidden = true;
		document.documentElement.classList.remove('elahub-consent-modal-open');

		if (lastFocused && document.body.contains(lastFocused)) {
			lastFocused.focus();
		}
		lastFocused = null;
	}

	function commit(state, message) {
		writeConsent(state);
		applyConsent(state);
		closeModal();
		hideBanner();
		announce(message);
	}

	function acceptAll() {
		commit({ analytics: true, marketing: true }, 'Optional cookies accepted. Your choice is saved.');
	}

	function rejectAll() {
		commit({ analytics: false, marketing: false }, 'Optional cookies rejected. Your choice is saved.');
	}

	function saveChoices() {
		var state = {};
		CATEGORIES.forEach(function (cat) {
			var toggle = document.querySelector('[data-elahub-consent-toggle="' + cat + '"]');
			state[cat] = !!(toggle && toggle.checked);
		});
		commit(state, 'Your cookie preferences have been saved.');
	}

	function bind() {
		ui.root = document.querySelector('[data-elahub-consent-root]');
		if (!ui.root) {
			return false;
		}

		ui.banner = q('banner');
		ui.overlay = q('overlay');
		ui.modal = q('modal');
		ui.status = q('status');

		Array.prototype.forEach.call(document.querySelectorAll('[data-elahub-consent-accept]'), function (b) {
			b.addEventListener('click', acceptAll);
		});
		Array.prototype.forEach.call(document.querySelectorAll('[data-elahub-consent-reject]'), function (b) {
			b.addEventListener('click', rejectAll);
		});
		Array.prototype.forEach.call(document.querySelectorAll('[data-elahub-consent-open]'), function (b) {
			b.addEventListener('click', openModal);
		});

		var save = q('save');
		if (save) {
			save.addEventListener('click', saveChoices);
		}

		var close = q('close');
		if (close) {
			close.addEventListener('click', closeModal);
		}

		if (ui.overlay) {
			ui.overlay.addEventListener('mousedown', function (e) {
				if (e.target === ui.overlay) {
					closeModal();
				}
			});
		}

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && ui.overlay && !ui.overlay.hidden) {
				closeModal();
			}
			trapFocus(e);
		});

		return true;
	}

	/* -----------------------------------------------------------------
	 * Boot
	 * -------------------------------------------------------------- */

	var stored = readConsent();

	// Released as early as possible so a returning visitor's tags behave
	// exactly as they would on a site with no consent gate at all.
	if (stored) {
		sendConsentSignal(stored);
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', function () {
				releaseTags(stored);
			});
		} else {
			releaseTags(stored);
		}
	}

	function ready() {
		if (!bind()) {
			return;
		}
		if (!readConsent()) {
			showBanner();
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', ready);
	} else {
		ready();
	}

	// Public hook for the footer link and for anything else that needs to
	// reopen the panel.
	window.ELaHubConsent = {
		open: openModal,
		get: readConsent
	};
})();
