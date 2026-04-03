/**
 * Prairie Theme — Sidebar Toggle
 *
 * WHY: The Explorer layout has a permanent left sidebar on desktop, but on
 * mobile (below 768px) the sidebar hides off-screen and needs a toggle
 * button to reveal it. This script handles:
 *
 * 1. Toggle button click — slides sidebar in/out
 * 2. Backdrop click — close sidebar when tapping outside it
 * 3. Escape key — close sidebar (standard dismissible panel UX)
 * 4. Nav link click — close sidebar after selection (smooth mobile UX)
 *
 * No jQuery — vanilla JS only, per project rules.
 *
 * @package Prairie
 * @since   1.1.0
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var toggle   = document.querySelector('.prairie-sidebar-toggle');
        var sidebar  = document.querySelector('.prairie-sidebar');
        var backdrop = document.querySelector('.prairie-sidebar-backdrop');

        /* Nothing to do if the sidebar elements aren't present */
        if (!toggle || !sidebar) {
            return;
        }

        /**
         * Open the sidebar overlay on mobile.
         * WHY we also set body overflow: Prevents background scrolling while
         * the overlay is open, which is disorienting on mobile.
         */
        function openSidebar() {
            sidebar.classList.add('is-open');
            toggle.classList.add('is-active');
            toggle.setAttribute('aria-expanded', 'true');

            if (backdrop) {
                backdrop.classList.add('is-visible');
            }

            document.body.style.overflow = 'hidden';

            /* Move focus into the sidebar for keyboard users.
               WHY: When the sidebar opens, keyboard focus should move into it
               so the user can immediately Tab through the nav links. */
            var firstLink = sidebar.querySelector('a');
            if (firstLink) {
                firstLink.focus();
            }
        }

        /**
         * Close the sidebar overlay.
         */
        function closeSidebar() {
            sidebar.classList.remove('is-open');
            toggle.classList.remove('is-active');
            toggle.setAttribute('aria-expanded', 'false');

            if (backdrop) {
                backdrop.classList.remove('is-visible');
            }

            document.body.style.overflow = '';
        }

        /* Toggle button click — open or close the sidebar */
        toggle.addEventListener('click', function () {
            if (sidebar.classList.contains('is-open')) {
                closeSidebar();
                toggle.focus(); /* Return focus to toggle after closing */
            } else {
                openSidebar();
            }
        });

        /* Backdrop click — close sidebar when tapping the dark overlay.
           WHY: This is the standard "tap outside to dismiss" pattern that
           mobile users expect from slide-in panels. */
        if (backdrop) {
            backdrop.addEventListener('click', function () {
                closeSidebar();
                toggle.focus();
            });
        }

        /* Escape key — close sidebar (consistent with other SocietyPress
           dismissible panels like the search dropdown and user menu) */
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && sidebar.classList.contains('is-open')) {
                closeSidebar();
                toggle.focus();
            }
        });

        /* Close when clicking a nav link inside the sidebar.
           WHY: On mobile, after the user picks a page from the sidebar,
           the sidebar should dismiss so they see the new page loading.
           Without this, the sidebar stays open and covers the content. */
        sidebar.addEventListener('click', function (e) {
            if (e.target.tagName === 'A') {
                closeSidebar();
            }
        });
    });

})();
