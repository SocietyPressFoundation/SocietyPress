/**
 * SocietyPress Parent Theme — JavaScript
 *
 * Handles the hamburger menu toggle for mobile screens (below 768px).
 * On desktop the hamburger is hidden via CSS and the nav renders normally.
 *
 * No jQuery — vanilla JS only, as required by the SocietyPress project rules.
 *
 * @package SocietyPress
 * @since   1.0.5
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initHamburger();
    });


    // =========================================================================
    // HAMBURGER MENU TOGGLE
    // =========================================================================
    // WHY: On screens below 768px the full horizontal nav won't fit. CSS hides
    // the nav and shows a hamburger button instead. This JS toggles the
    // sp-nav-open class on .main-navigation, which CSS uses to display the
    // mobile dropdown panel. We also animate the hamburger bars into an X
    // by toggling is-active on the button itself.

    function initHamburger() {
        var hamburger = document.querySelector('.sp-hamburger');
        var nav       = document.querySelector('.main-navigation');

        if (!hamburger || !nav) {
            return;
        }

        // Toggle the mobile nav panel open/closed
        hamburger.addEventListener('click', function () {
            var isOpen = nav.classList.toggle('sp-nav-open');
            hamburger.classList.toggle('is-active', isOpen);
            hamburger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        // Close on Escape key — standard UX pattern for dismissible panels
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && nav.classList.contains('sp-nav-open')) {
                nav.classList.remove('sp-nav-open');
                hamburger.classList.remove('is-active');
                hamburger.setAttribute('aria-expanded', 'false');
            }
        });

        // Close when clicking a nav link — smooth mobile UX so the panel
        // dismisses immediately after the user makes a selection
        nav.addEventListener('click', function (e) {
            if (e.target.tagName === 'A') {
                nav.classList.remove('sp-nav-open');
                hamburger.classList.remove('is-active');
                hamburger.setAttribute('aria-expanded', 'false');
            }
        });

        // Close when clicking outside the nav and hamburger button
        document.addEventListener('click', function (e) {
            if (!nav.contains(e.target) && !hamburger.contains(e.target)) {
                nav.classList.remove('sp-nav-open');
                hamburger.classList.remove('is-active');
                hamburger.setAttribute('aria-expanded', 'false');
            }
        });
    }

})();
