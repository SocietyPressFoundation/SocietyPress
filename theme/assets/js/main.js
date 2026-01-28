/**
 * SocietyPress Theme Main JavaScript
 *
 * WHY: Handles interactive features like mobile menu, accessibility enhancements,
 * and dynamic UI behaviors.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

(function() {
	'use strict';

	/**
	 * Mobile menu toggle functionality.
	 *
	 * WHY: Provides accessible mobile navigation without page reloads.
	 */
	function initMobileMenu() {
		const toggle = document.querySelector('.mobile-menu-toggle');
		const nav = document.querySelector('.main-navigation');

		if (!toggle || !nav) {
			return;
		}

		toggle.addEventListener('click', function() {
			const expanded = this.getAttribute('aria-expanded') === 'true';
			this.setAttribute('aria-expanded', !expanded);
			nav.classList.toggle('toggled');
			document.body.classList.toggle('mobile-menu-open');
		});

		// Close menu when clicking outside
		document.addEventListener('click', function(event) {
			const isClickInside = nav.contains(event.target) || toggle.contains(event.target);
			if (!isClickInside && nav.classList.contains('toggled')) {
				toggle.setAttribute('aria-expanded', 'false');
				nav.classList.remove('toggled');
				document.body.classList.remove('mobile-menu-open');
			}
		});

		// Close menu on escape key
		document.addEventListener('keydown', function(event) {
			if (event.key === 'Escape' && nav.classList.contains('toggled')) {
				toggle.setAttribute('aria-expanded', 'false');
				nav.classList.remove('toggled');
				document.body.classList.remove('mobile-menu-open');
				toggle.focus();
			}
		});
	}

	/**
	 * Dropdown menu functionality.
	 *
	 * WHY: Handles desktop hover and mobile click for submenu display.
	 */
	function initDropdownMenus() {
		const menuItems = document.querySelectorAll('.main-navigation .menu-item-has-children, .utility-navigation .menu-item-has-children');

		if (menuItems.length === 0) {
			return;
		}

		// Desktop: Add focus class for keyboard navigation (all menus)
		menuItems.forEach(item => {
			const link = item.querySelector('a');

			link.addEventListener('focus', function() {
				item.classList.add('focus');
			});

			link.addEventListener('blur', function() {
				item.classList.remove('focus');
			});
		});

		// Get only main navigation items for mobile handling
		const mobileMenuItems = document.querySelectorAll('.main-navigation .menu-item-has-children');

		// Mobile: Toggle submenus on click (only for main navigation)
		function handleMobileDropdowns() {
			if (window.innerWidth > 768) {
				// Desktop - remove mobile event listeners
				mobileMenuItems.forEach(item => {
					item.classList.remove('open');
					const link = item.querySelector('a');
					// Remove any existing click handlers by cloning
					const newLink = link.cloneNode(true);
					link.parentNode.replaceChild(newLink, link);
				});
			} else {
				// Mobile - add click handlers
				mobileMenuItems.forEach(item => {
					const link = item.querySelector('a');

					link.addEventListener('click', function(event) {
						// If this item has a submenu
						if (item.classList.contains('menu-item-has-children')) {
							event.preventDefault();

							// Close other open submenus at the same level
							const siblings = Array.from(item.parentElement.children);
							siblings.forEach(sibling => {
								if (sibling !== item && sibling.classList.contains('menu-item-has-children')) {
									sibling.classList.remove('open');
								}
							});

							// Toggle this submenu
							item.classList.toggle('open');
						}
					});
				});
			}
		}

		// Run on load
		handleMobileDropdowns();

		// Run on resize
		let resizeTimer;
		window.addEventListener('resize', function() {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(handleMobileDropdowns, 250);
		});
	}

	/**
	 * Menu search toggle.
	 *
	 * WHY: Provides search functionality in the navigation menu.
	 */
	function initMenuSearch() {
		const searchToggle = document.querySelector('.search-toggle');
		const searchDropdown = document.querySelector('.search-dropdown');

		if (!searchToggle || !searchDropdown) {
			return;
		}

		searchToggle.addEventListener('click', function() {
			const expanded = this.getAttribute('aria-expanded') === 'true';
			this.setAttribute('aria-expanded', !expanded);
			searchDropdown.classList.toggle('active');

			// Focus search input when opened
			if (!expanded) {
				const searchInput = searchDropdown.querySelector('.search-field');
				if (searchInput) {
					setTimeout(() => searchInput.focus(), 100);
				}
			}
		});

		// Close search when clicking outside
		document.addEventListener('click', function(event) {
			const isClickInside = searchToggle.contains(event.target) || searchDropdown.contains(event.target);
			if (!isClickInside && searchDropdown.classList.contains('active')) {
				searchToggle.setAttribute('aria-expanded', 'false');
				searchDropdown.classList.remove('active');
			}
		});

		// Close on escape key
		document.addEventListener('keydown', function(event) {
			if (event.key === 'Escape' && searchDropdown.classList.contains('active')) {
				searchToggle.setAttribute('aria-expanded', 'false');
				searchDropdown.classList.remove('active');
				searchToggle.focus();
			}
		});
	}

	/**
	 * Smooth scroll for anchor links.
	 *
	 * WHY: Improves UX for same-page navigation with smooth scrolling.
	 */
	function initSmoothScroll() {
		const links = document.querySelectorAll('a[href^="#"]');

		links.forEach(link => {
			link.addEventListener('click', function(event) {
				const href = this.getAttribute('href');

				// Skip if it's just '#'
				if (href === '#') {
					return;
				}

				const target = document.querySelector(href);
				if (target) {
					event.preventDefault();
					target.scrollIntoView({
						behavior: 'smooth',
						block: 'start'
					});

					// Update focus for accessibility
					target.focus();
					if (document.activeElement !== target) {
						target.setAttribute('tabindex', '-1');
						target.focus();
					}
				}
			});
		});
	}

	/**
	 * Event category filtering.
	 *
	 * WHY: Allows users to filter events by category without page reload.
	 */
	function initEventFilters() {
		const filters = document.querySelectorAll('.category-filter');
		const events = document.querySelectorAll('.event-card');

		if (filters.length === 0 || events.length === 0) {
			return;
		}

		filters.forEach(filter => {
			filter.addEventListener('click', function() {
				const category = this.getAttribute('data-category');

				// Update active state
				filters.forEach(f => f.classList.remove('active'));
				this.classList.add('active');

				// Filter events
				events.forEach(event => {
					if (category === 'all') {
						event.style.display = '';
						// Fade in animation
						event.style.opacity = '0';
						setTimeout(() => {
							event.style.transition = 'opacity 300ms ease-in-out';
							event.style.opacity = '1';
						}, 10);
					} else {
						const eventCategories = event.getAttribute('data-categories');
						if (eventCategories && eventCategories.includes(category)) {
							event.style.display = '';
							event.style.opacity = '0';
							setTimeout(() => {
								event.style.transition = 'opacity 300ms ease-in-out';
								event.style.opacity = '1';
							}, 10);
						} else {
							event.style.transition = 'opacity 300ms ease-in-out';
							event.style.opacity = '0';
							setTimeout(() => {
								event.style.display = 'none';
							}, 300);
						}
					}
				});

				// Announce to screen readers
				const visibleCount = Array.from(events).filter(e => e.style.display !== 'none').length;
				announceToScreenReader(`Showing ${visibleCount} event${visibleCount !== 1 ? 's' : ''}`);
			});
		});
	}

	/**
	 * Announce message to screen readers.
	 *
	 * WHY: Provides feedback to screen reader users when content updates dynamically.
	 *
	 * @param {string} message - The message to announce.
	 */
	function announceToScreenReader(message) {
		const announcement = document.createElement('div');
		announcement.setAttribute('role', 'status');
		announcement.setAttribute('aria-live', 'polite');
		announcement.className = 'screen-reader-text';
		announcement.textContent = message;
		document.body.appendChild(announcement);

		setTimeout(() => {
			document.body.removeChild(announcement);
		}, 1000);
	}

	/**
	 * Add 'no-js' to 'js' class swap.
	 *
	 * WHY: Allows CSS to provide fallbacks for non-JavaScript environments.
	 */
	function initJSClass() {
		document.documentElement.classList.remove('no-js');
		document.documentElement.classList.add('js');
	}

	/**
	 * Sticky header on scroll.
	 *
	 * WHY: Keeps navigation accessible as user scrolls down the page.
	 */
	function initStickyHeader() {
		const header = document.querySelector('.site-header');
		if (!header) {
			return;
		}

		// Sticky header enabled but no visual changes on scroll
		// WHY: Keeps header accessible without adding shadows/borders
	}

	/**
	 * Back to top button.
	 *
	 * WHY: Improves UX on long pages by providing quick navigation to top.
	 */
	function initBackToTop() {
		// Only show on longer pages
		if (document.body.scrollHeight < 2000) {
			return;
		}

		const button = document.createElement('button');
		button.className = 'back-to-top';
		button.setAttribute('aria-label', 'Back to top');
		button.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M7.41 15.41L12 10.83l4.59 4.58L18 14l-6-6-6 6z"/></svg>';
		document.body.appendChild(button);

		// Show/hide button based on scroll position
		window.addEventListener('scroll', function() {
			if (window.pageYOffset > 300) {
				button.classList.add('visible');
			} else {
				button.classList.remove('visible');
			}
		});

		// Scroll to top on click
		button.addEventListener('click', function() {
			window.scrollTo({
				top: 0,
				behavior: 'smooth'
			});
		});
	}

	/**
	 * Initialize hero slider.
	 *
	 * WHY: Creates an engaging, auto-playing carousel for featured content on homepage.
	 */
	function initHeroSlider() {
		// Only initialize if Swiper library is loaded and slider exists
		if (typeof Swiper === 'undefined' || !document.querySelector('.hero-swiper')) {
			return;
		}

		const heroSwiper = new Swiper('.hero-swiper', {
			// Basic configuration
			loop: true,
			autoplay: {
				delay: 5000,
				disableOnInteraction: false,
			},
			speed: 600,
			effect: 'fade',
			fadeEffect: {
				crossFade: true
			},

			// Navigation
			navigation: {
				nextEl: '.swiper-button-next',
				prevEl: '.swiper-button-prev',
			},

			// Pagination
			pagination: {
				el: '.swiper-pagination',
				clickable: true,
				renderBullet: function (index, className) {
					return '<span class="' + className + '" role="button" aria-label="Go to slide ' + (index + 1) + '"></span>';
				},
			},

			// Accessibility
			a11y: {
				prevSlideMessage: 'Previous slide',
				nextSlideMessage: 'Next slide',
				paginationBulletMessage: 'Go to slide {{index}}',
			},

			// Keyboard control
			keyboard: {
				enabled: true,
				onlyInViewport: true,
			},
		});
	}

	/**
	 * Initialize all functionality on DOM ready.
	 */
	function init() {
		initJSClass();
		initMobileMenu();
		initDropdownMenus();
		initMenuSearch();
		initSmoothScroll();
		initEventFilters();
		initStickyHeader();
		initBackToTop();
		initHeroSlider();
	}

	// Run on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

})();
