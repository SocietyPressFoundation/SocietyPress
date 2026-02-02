/**
 * Volunteer Opportunities Admin JavaScript
 *
 * WHY: Handles dynamic form behavior for the opportunity edit form,
 *      showing/hiding schedule fields based on opportunity type.
 *
 * @package SocietyPress
 * @since 0.54d
 */

(function($) {
    'use strict';

    /**
     * Initialize admin functionality when document is ready.
     */
    $(document).ready(function() {
        initOpportunityTypeToggle();
    });

    /**
     * Toggle schedule fields based on opportunity type.
     *
     * WHY: Different opportunity types need different scheduling fields:
     *      - One-time: Shows date picker
     *      - Recurring: Shows day of week dropdown
     *      - Ongoing: Shows neither (no schedule)
     */
    function initOpportunityTypeToggle() {
        var $typeSelect = $('#opportunity_type');

        if (!$typeSelect.length) {
            return;
        }

        // Initial state
        updateScheduleVisibility($typeSelect.val());

        // Listen for changes
        $typeSelect.on('change', function() {
            updateScheduleVisibility($(this).val());
        });
    }

    /**
     * Update schedule field visibility based on type.
     *
     * @param {string} type The opportunity type.
     */
    function updateScheduleVisibility(type) {
        var $oneTimeRows = $('.sp-schedule-one-time');
        var $recurringRows = $('.sp-schedule-recurring');

        // Hide all first
        $oneTimeRows.removeClass('sp-visible');
        $recurringRows.removeClass('sp-visible');

        // Show relevant rows
        switch (type) {
            case 'one_time':
                $oneTimeRows.addClass('sp-visible');
                break;
            case 'recurring':
                $recurringRows.addClass('sp-visible');
                break;
            case 'ongoing':
                // Neither date nor day_of_week shown
                break;
        }
    }

})(jQuery);
