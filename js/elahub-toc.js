/**
 * Sticky table of contents — smooth scroll + active section highlighting.
 *
 * Shared by single-cornerstone_article.php, single-case_study.php and
 * single.php, which each render a TOC of the same shape. Links opt in with
 * data-elahub-toc-link and point at their heading via data-target.
 *
 * @package elahub
 */
(function () {
	'use strict';

	function init() {
		var links = Array.prototype.slice.call(
			document.querySelectorAll('[data-elahub-toc-link]')
		);

		if (!links.length) {
			return;
		}

		var targets = links
			.map(function (link) {
				return document.getElementById(link.dataset.target);
			})
			.filter(Boolean);

		if (!targets.length) {
			return;
		}

		// Headings carry scroll-margin-top: 8rem so anchor jumps clear the
		// header. Read it back from the root font size so the observer band and
		// the CSS can never drift apart.
		var offset = 8 * parseFloat(
			window.getComputedStyle(document.documentElement).fontSize
		);

		function setActive(id) {
			links.forEach(function (link) {
				var isActive = link.dataset.target === id;

				link.classList.toggle('!font-semibold', isActive);

				if (isActive) {
					link.setAttribute('aria-current', 'true');
				} else {
					link.removeAttribute('aria-current');
				}
			});
		}

		var onScreen = [];

		var observer = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					var index = onScreen.indexOf(entry.target);

					if (entry.isIntersecting && index === -1) {
						onScreen.push(entry.target);
					} else if (!entry.isIntersecting && index !== -1) {
						onScreen.splice(index, 1);
					}
				});

				// Highlight the highest section currently in the band. Marking
				// whichever entry fired last gets it wrong when scrolling up,
				// because the section being re-entered is below the one already
				// on screen.
				if (onScreen.length) {
					var highest = onScreen.slice().sort(function (a, b) {
						return a.getBoundingClientRect().top -
							b.getBoundingClientRect().top;
					})[0];

					setActive(highest.id);
					return;
				}

				// Nothing in the band — a section taller than the viewport is
				// filling it. Fall back to the last heading scrolled past.
				var current = null;

				targets.forEach(function (element) {
					if (element.getBoundingClientRect().top <= offset) {
						current = element;
					}
				});

				if (current) {
					setActive(current.id);
				}
			},
			{
				rootMargin: '-' + Math.round(offset) + 'px 0px -55% 0px',
				threshold: 0
			}
		);

		targets.forEach(function (element) {
			observer.observe(element);
		});

		links.forEach(function (link) {
			link.addEventListener('click', function (event) {
				var target = document.getElementById(link.dataset.target);

				// No target: leave the browser to do whatever the href says
				// rather than swallowing the click.
				if (!target) {
					return;
				}

				event.preventDefault();

				// scrollIntoView honours the heading's scroll-margin-top, so the
				// landing position stays defined in one place, in CSS.
				target.scrollIntoView({ behavior: 'smooth', block: 'start' });

				// Keep the URL shareable and move focus for keyboard users.
				window.history.replaceState(null, '', '#' + link.dataset.target);

				if (!target.hasAttribute('tabindex')) {
					target.setAttribute('tabindex', '-1');
				}

				target.focus({ preventScroll: true });

				setActive(link.dataset.target);
			});
		});
	}

	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
}());
