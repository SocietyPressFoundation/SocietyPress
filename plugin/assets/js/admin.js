/**
 * SocietyPress Admin JavaScript
 *
 * Handles interactive features in the WordPress admin.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * SocietyPress Admin Module
     */
    var SocietyPressAdmin = {

        /**
         * Initialize the module.
         */
        init: function() {
            this.bindEvents();
            this.initDateFields();
        },

        /**
         * Bind event handlers.
         */
        bindEvents: function() {
            // Confirm delete actions
            $(document).on('click', '.societypress-delete-member', this.confirmDelete);

            // Tier selection auto-updates expiration
            $(document).on('change', '#membership_tier_id', this.updateExpiration);

            // Form submission feedback
            $(document).on('submit', '.societypress-member-form', this.handleFormSubmit);
        },

        /**
         * Initialize date fields with proper defaults.
         */
        initDateFields: function() {
            // Set join date to today if empty on new member form
            var $joinDate = $('#join_date');
            if ($joinDate.length && !$joinDate.val()) {
                var today = new Date().toISOString().split('T')[0];
                $joinDate.val(today);
            }
        },

        /**
         * Confirm delete action.
         *
         * @param {Event} e Click event.
         * @return {boolean} Whether to proceed.
         */
        confirmDelete: function(e) {
            var message = societypressAdmin.strings.confirmDelete ||
                          'Are you sure you want to delete this member?';

            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
            return true;
        },

        /**
         * Update expiration date based on tier selection.
         */
        updateExpiration: function() {
            // This could be enhanced to calculate expiration based on tier duration
            // For now, it's a placeholder for future AJAX functionality
        },

        /**
         * Handle form submission with feedback.
         *
         * @param {Event} e Submit event.
         */
        handleFormSubmit: function(e) {
            var $form = $(this);
            var $submit = $form.find('input[type="submit"]');

            // Disable submit button to prevent double submission
            $submit.prop('disabled', true);
            $submit.val(societypressAdmin.strings.saving || 'Saving...');
        }
    };

    /**
     * Initialize on document ready.
     */
    $(document).ready(function() {
        SocietyPressAdmin.init();
    });

})(jQuery);
