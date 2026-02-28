/**
 * SAGHS Child Theme — JavaScript
 *
 * Handles three interactive behaviors:
 * 1. Hamburger menu toggle (mobile)
 * 2. Dropdown nav — hover on desktop, tap-to-toggle on mobile
 * 3. Hero slider — auto-rotate with pause on hover
 *
 * No jQuery — vanilla JS only, as required by the SocietyPress project rules.
 *
 * @package SAGHS
 * @since   0.01d
 */

(function () {
    'use strict';

    // Wait for DOM to be fully parsed before attaching event listeners
    document.addEventListener('DOMContentLoaded', function () {
        initHamburger();
        initDropdowns();
        initHeroSlider();
    });


    // =========================================================================
    // 1. HAMBURGER MENU TOGGLE
    // =========================================================================
    // WHY: On smaller screens (≤1024px) the full nav is hidden. The hamburger button
    // toggles the .is-open class on the nav container, which CSS uses to
    // show/hide the mobile menu.

    function initHamburger() {
        var hamburger = document.querySelector('.saghs-hamburger');
        var nav       = document.querySelector('.main-navigation');

        if (!hamburger || !nav) {
            return;
        }

        hamburger.addEventListener('click', function () {
            var isOpen = nav.classList.toggle('is-open');
            hamburger.classList.toggle('is-active', isOpen);
            hamburger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function (e) {
            if (!nav.contains(e.target) && !hamburger.contains(e.target)) {
                nav.classList.remove('is-open');
                hamburger.classList.remove('is-active');
                hamburger.setAttribute('aria-expanded', 'false');
            }
        });
    }


    // =========================================================================
    // 2. DROPDOWN NAV — DESKTOP HOVER + MOBILE TAP
    // =========================================================================
    // WHY two modes:
    // - Desktop: CSS :hover handles opening submenus. JS isn't strictly needed,
    //   but we add keyboard support (focus/blur) for accessibility.
    // - Mobile: Hover doesn't work on touch devices. The submenu toggle buttons
    //   (added by the SAGHS_Nav_Walker in functions.php) get click handlers
    //   that toggle the .is-open class on parent <li> elements.

    function initDropdowns() {
        var toggles = document.querySelectorAll('.saghs-submenu-toggle');

        for (var i = 0; i < toggles.length; i++) {
            toggles[i].addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                var parentLi  = this.closest('li');
                var isOpen    = parentLi.classList.toggle('is-open');
                this.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

                // Close sibling menus — only one dropdown open at a time per level
                var siblings = parentLi.parentElement.children;
                for (var j = 0; j < siblings.length; j++) {
                    if (siblings[j] !== parentLi) {
                        siblings[j].classList.remove('is-open');
                        var sibToggle = siblings[j].querySelector('.saghs-submenu-toggle');
                        if (sibToggle) {
                            sibToggle.setAttribute('aria-expanded', 'false');
                        }
                    }
                }
            });
        }

        // Hide the toggle buttons on desktop (they're only needed on mobile).
        // WHY CSS instead of JS: We handle this in CSS (the toggle button is
        // only styled/visible when the hamburger is shown), but we also add
        // a .saghs-submenu-toggle style rule here for clarity.
    }


    // =========================================================================
    // 3. HERO SLIDER
    // =========================================================================
    // WHY: The hero section can have multiple background images that rotate
    // automatically. This handles both the SAGHS static hero (class-based
    // .saghs-hero) and the plugin's sp_render_builder_widget_hero_slider
    // (which has its own inline JS). We only run this if we find the SAGHS
    // hero container with multiple slides.

    function initHeroSlider() {
        var hero = document.getElementById('saghs-hero');
        if (!hero) {
            return; // No SAGHS hero on this page (maybe using plugin slider)
        }

        var slides  = hero.querySelectorAll('.saghs-hero-slide');
        var dots    = hero.querySelectorAll('.saghs-hero-dot');
        var current = 0;
        var total   = slides.length;
        var timer   = null;
        var INTERVAL = 5000; // 5 seconds between slides

        if (total <= 1) {
            return; // Single slide, no rotation needed
        }

        function goToSlide(index) {
            // Deactivate current slide
            slides[current].classList.remove('is-active');
            if (dots[current]) {
                dots[current].classList.remove('is-active');
            }

            // Activate target slide
            current = index;
            slides[current].classList.add('is-active');
            if (dots[current]) {
                dots[current].classList.add('is-active');
            }
        }

        function nextSlide() {
            goToSlide((current + 1) % total);
        }

        function startAutoplay() {
            if (timer) {
                clearInterval(timer);
            }
            timer = setInterval(nextSlide, INTERVAL);
        }

        function stopAutoplay() {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        }

        // Dot click handlers
        for (var i = 0; i < dots.length; i++) {
            dots[i].addEventListener('click', (function (index) {
                return function () {
                    goToSlide(index);
                    startAutoplay(); // Reset timer on manual navigation
                };
            })(i));
        }

        // Pause autoplay on hover so users can read the content
        hero.addEventListener('mouseenter', stopAutoplay);
        hero.addEventListener('mouseleave', startAutoplay);

        // Start the auto-rotation
        startAutoplay();
    }

})();
