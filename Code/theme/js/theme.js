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

    // Shared so the hamburger can collapse any open drop down menus when the
    // whole panel closes — otherwise a menu reopens still showing whatever was
    // expanded the last time, which reads as the panel having a memory.
    var closeAllSubMenus = function () {};

    document.addEventListener('DOMContentLoaded', function () {
        initSubMenus();
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

        function closePanel() {
            nav.classList.remove('sp-nav-open');
            hamburger.classList.remove('is-active');
            hamburger.setAttribute('aria-expanded', 'false');
            closeAllSubMenus();
        }

        // Toggle the mobile nav panel open/closed
        hamburger.addEventListener('click', function () {
            var isOpen = nav.classList.toggle('sp-nav-open');
            hamburger.classList.toggle('is-active', isOpen);
            hamburger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            if (!isOpen) {
                closeAllSubMenus();
            }
        });

        // Close on Escape key — standard UX pattern for dismissible panels
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && nav.classList.contains('sp-nav-open')) {
                closePanel();
            }
        });

        // Close when clicking a nav link — smooth mobile UX so the panel
        // dismisses immediately after the user makes a selection.
        //
        // WHY this checks for a real destination first: the drop down carets
        // are buttons, not links, and a placeholder parent whose address is
        // "#" only opens its own drop down. Treating either as a selection
        // tore the panel shut around the menu the tap had just opened, which
        // is what made nested items impossible to reach on a phone.
        nav.addEventListener('click', function (e) {
            var link = e.target.closest ? e.target.closest('a') : null;

            if (!link || !nav.contains(link)) {
                return;
            }

            var href = link.getAttribute('href');
            if (!href || href === '#') {
                return;
            }

            closePanel();
        });

        // Close when clicking outside the nav and hamburger button
        document.addEventListener('click', function (e) {
            if (!nav.contains(e.target) && !hamburger.contains(e.target)) {
                closePanel();
            }
        });
    }


    // =========================================================================
    // SUB-MENU DROPDOWNS
    // =========================================================================
    // WHY a separate caret button rather than overloading the parent link:
    // a menu item like "Resources" is two things at once — a page of its own
    // and the lid on three more pages. Overloading one tap to mean both left
    // the page unreachable on a phone, because opening the drop down had to
    // cancel the navigation to do it. Splitting them gives each job its own
    // thing to tap: the name goes to the page, the caret opens the menu.
    //
    // On desktop the caret is hidden and CSS :hover still reveals sub-menus,
    // so nothing about the desktop nav changes.

    function initSubMenus() {
        var parentItems = document.querySelectorAll('.main-navigation li.menu-item-has-children');

        if (!parentItems.length) {
            return;
        }

        var strings = window.SocietyPressTheme || {};
        var labelTemplate = strings.submenuLabel || 'Show the drop down menu for %s';

        function setExpanded(item, expanded) {
            var toggle = item.querySelector(':scope > .sp-submenu-toggle');
            if (toggle) {
                toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            }
        }

        function openItem(item) {
            var isOpen = item.classList.toggle('sp-submenu-open');
            setExpanded(item, isOpen);

            // Close other open sub-menus — but NOT this item's own ancestors,
            // or opening a level-2 flyout would collapse the level-1 menu
            // containing it. other.contains(item) is true only for ancestors,
            // so we skip exactly those.
            parentItems.forEach(function (other) {
                if (other !== item && !other.contains(item)) {
                    other.classList.remove('sp-submenu-open');
                    setExpanded(other, false);
                }
            });
        }

        closeAllSubMenus = function () {
            parentItems.forEach(function (item) {
                item.classList.remove('sp-submenu-open');
                setExpanded(item, false);
            });
        };

        parentItems.forEach(function (item) {
            var link = item.querySelector(':scope > a');
            var subMenu = item.querySelector(':scope > .sub-menu');
            if (!link || !subMenu) {
                return;
            }

            // The link no longer owns the drop down, so aria-expanded belongs
            // on the caret instead. Strip any left over from an earlier render.
            link.removeAttribute('aria-expanded');

            var toggle = document.createElement('button');
            toggle.type = 'button';
            toggle.className = 'sp-submenu-toggle';
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute(
                'aria-label',
                labelTemplate.replace('%s', link.textContent.trim())
            );
            toggle.innerHTML =
                '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" ' +
                'viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" ' +
                'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" ' +
                'focusable="false"><polyline points="6 9 12 15 18 9"/></svg>';

            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                openItem(item);
            });

            link.insertAdjacentElement('afterend', toggle);

            // A parent whose address is only "#" has nowhere to send anyone,
            // so the name itself keeps working as a way to open the menu.
            link.addEventListener('click', function (e) {
                var href = link.getAttribute('href');
                if (!href || href === '#') {
                    e.preventDefault();
                    openItem(item);
                }
            });
        });

        // Close sub-menus when clicking outside
        document.addEventListener('click', function (e) {
            var nav = document.querySelector('.main-navigation');
            if (nav && !nav.contains(e.target)) {
                closeAllSubMenus();
            }
        });

        // Close sub-menus on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeAllSubMenus();
            }
        });
    }

})();
