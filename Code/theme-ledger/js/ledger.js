/**
 * Ledger Child Theme — JavaScript
 *
 * WHY: Handles two interactive behaviors that CSS alone can't cover:
 *
 * 1. Hamburger menu toggle — On mobile (below 768px), the nav is hidden.
 *    Tapping the hamburger button toggles the nav panel open/closed.
 *
 * 2. Mobile submenu toggles — On mobile, dropdown submenus can't use :hover.
 *    Each parent menu item gets a toggle button that shows/hides its submenu
 *    on tap. On desktop these buttons are hidden via CSS; :hover and
 *    :focus-within handle dropdowns instead.
 *
 * No jQuery. No dependencies. Vanilla JS only.
 *
 * @package Ledger
 * @since   1.1.0
 */

(function () {
    'use strict';

    // ========================================================================
    // HAMBURGER MENU TOGGLE
    // ========================================================================

    var hamburger = document.querySelector('.ledger-hamburger');
    var nav       = document.querySelector('.ledger-navigation');

    if (hamburger && nav) {
        hamburger.addEventListener('click', function () {
            var isOpen = nav.classList.toggle('ledger-nav-open');

            // Toggle the is-active class on the hamburger so CSS can animate
            // the three bars into an X shape
            hamburger.classList.toggle('is-active');

            // Update aria-expanded for screen readers — they need to know
            // whether the navigation panel is currently visible or hidden
            hamburger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        // WHY: Close the mobile nav when clicking outside of it. Without this,
        // the nav stays open if the user taps elsewhere on the page, which
        // feels broken on mobile.
        document.addEventListener('click', function (e) {
            if (!nav.classList.contains('ledger-nav-open')) return;
            if (!nav.contains(e.target) && !hamburger.contains(e.target)) {
                nav.classList.remove('ledger-nav-open');
                hamburger.classList.remove('is-active');
                hamburger.setAttribute('aria-expanded', 'false');
            }
        });

        // Close on Escape key — consistent with the search and user menu behavior
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && nav.classList.contains('ledger-nav-open')) {
                nav.classList.remove('ledger-nav-open');
                hamburger.classList.remove('is-active');
                hamburger.setAttribute('aria-expanded', 'false');
                hamburger.focus();
            }
        });
    }


    // ========================================================================
    // MOBILE SUBMENU TOGGLES
    // ========================================================================
    // WHY: On mobile, CSS :hover doesn't work for revealing submenus. Instead,
    // each parent menu item has a toggle button (injected by the walker in
    // functions.php). Tapping it adds/removes .is-open on the parent <li>,
    // which CSS uses to show/hide the .sub-menu.
    //
    // On desktop, these buttons are display:none via CSS — dropdowns use
    // :hover and :focus-within for keyboard accessibility.
    // ========================================================================

    var submenuToggles = document.querySelectorAll('.ledger-submenu-toggle');

    for (var i = 0; i < submenuToggles.length; i++) {
        submenuToggles[i].addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var parentLi = this.closest('.menu-item-has-children');
            if (!parentLi) return;

            var isOpen = parentLi.classList.toggle('is-open');
            this.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

            // WHY: Close sibling submenus when opening a new one. This prevents
            // the nav from getting visually cluttered with multiple open submenus
            // on a small screen.
            var siblings = parentLi.parentNode.children;
            for (var j = 0; j < siblings.length; j++) {
                if (siblings[j] !== parentLi && siblings[j].classList.contains('is-open')) {
                    siblings[j].classList.remove('is-open');
                    var sibToggle = siblings[j].querySelector('.ledger-submenu-toggle');
                    if (sibToggle) {
                        sibToggle.setAttribute('aria-expanded', 'false');
                    }
                }
            }
        });
    }

})();
