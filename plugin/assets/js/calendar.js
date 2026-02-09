/**
 * SocietyPress Event Calendar Navigation
 *
 * Handles month-to-month navigation via AJAX so the page doesn't
 * fully reload when clicking Previous/Next. Keeps the experience
 * smooth, especially on slower connections.
 *
 * @package SocietyPress
 * @since 0.62d
 */
(function () {
    'use strict';

    /**
     * Initialize calendar navigation once the DOM is ready.
     *
     * WHY: We attach click listeners to the nav buttons via event delegation
     *      on the wrapper element. This way, when the calendar HTML is replaced
     *      after AJAX, the listeners still work without re-binding.
     */
    function init() {
        var wrapper = document.getElementById('sp-calendar-wrapper');
        if (!wrapper) {
            return;
        }

        wrapper.addEventListener('click', function (e) {
            var button = e.target.closest('.sp-cal-nav');
            if (!button) {
                return;
            }

            e.preventDefault();

            var year  = button.getAttribute('data-year');
            var month = button.getAttribute('data-month');

            if (!year || !month) {
                return;
            }

            navigateToMonth(wrapper, year, month);
        });
    }

    /**
     * Navigate to a different month via AJAX.
     *
     * WHY: Replaces just the calendar HTML instead of reloading the whole
     *      page. Adds a loading class for visual feedback while waiting.
     *
     * @param {HTMLElement} wrapper The #sp-calendar-wrapper element.
     * @param {string}      year    Target year.
     * @param {string}      month   Target month (1-12).
     */
    function navigateToMonth(wrapper, year, month) {
        // Show loading state
        wrapper.classList.add('sp-loading');

        var formData = new FormData();
        formData.append('action', 'societypress_calendar_navigate');
        formData.append('nonce', spCalendar.nonce);
        formData.append('year', year);
        formData.append('month', month);

        fetch(spCalendar.ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data.success && data.data.html) {
                    wrapper.innerHTML = data.data.html;
                    // Scroll the calendar into view if it's off-screen
                    wrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
                wrapper.classList.remove('sp-loading');
            })
            .catch(function () {
                wrapper.classList.remove('sp-loading');
            });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
