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
            this.initSelectAll();
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

            // Confirm "Delete ALL Members" bulk action - extra safety
            $(document).on('submit', '#societypress-members-form', this.confirmBulkDelete);
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
         * Initialize "Select All Across Pages" functionality.
         *
         * Shows a banner when all items on the current page are selected,
         * allowing the user to select all members across all pages.
         */
        initSelectAll: function() {
            var self = this;
            var $form = $('#societypress-members-form');
            var $banner = $('#societypress-select-all-banner');
            var $selectAllLink = $('#societypress-select-all-link');
            var $clearSelectionLink = $('#societypress-clear-selection-link');
            var $selectAllHidden = $('#societypress_select_all');
            var $headerCheckbox = $form.find('thead input[type="checkbox"]');
            var $rowCheckboxes = $form.find('tbody input[name="member[]"]');
            var $pageMsg = $('#societypress-select-all-page-msg');
            var $allMsg = $('#societypress-all-selected-msg');

            // Exit if no banner exists (single page of results)
            if (!$banner.length) {
                return;
            }

            // Track whether all items across all pages are selected
            var allSelected = false;

            /**
             * Check if all checkboxes on the current page are selected.
             */
            function areAllPageItemsSelected() {
                if ($rowCheckboxes.length === 0) {
                    return false;
                }
                return $rowCheckboxes.filter(':checked').length === $rowCheckboxes.length;
            }

            /**
             * Update the banner visibility and state.
             */
            function updateBanner() {
                if (areAllPageItemsSelected() && $rowCheckboxes.length > 0) {
                    $banner.show();
                    if (allSelected) {
                        $pageMsg.hide();
                        $allMsg.show();
                    } else {
                        $pageMsg.show();
                        $allMsg.hide();
                    }
                } else {
                    $banner.hide();
                    allSelected = false;
                    $selectAllHidden.val('0');
                }
            }

            // Watch for checkbox changes
            $form.on('change', 'input[type="checkbox"]', function() {
                // If any individual checkbox is unchecked, clear "all selected" state
                if (!$(this).is(':checked')) {
                    allSelected = false;
                    $selectAllHidden.val('0');
                }
                updateBanner();
            });

            // "Select all X members" link clicked
            $selectAllLink.on('click', function(e) {
                e.preventDefault();
                allSelected = true;
                $selectAllHidden.val('1');
                updateBanner();
            });

            // "Clear selection" link clicked
            $clearSelectionLink.on('click', function(e) {
                e.preventDefault();
                allSelected = false;
                $selectAllHidden.val('0');
                // Uncheck the header checkbox which will uncheck all row checkboxes
                $headerCheckbox.prop('checked', false).trigger('change');
                $rowCheckboxes.prop('checked', false);
                updateBanner();
            });

            // Initial state check
            updateBanner();
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
         * Confirm "Clear Database" bulk action.
         *
         * @param {Event} e Submit event.
         * @return {boolean} Whether to proceed.
         */
        confirmBulkDelete: function(e) {
            var $form = $(this);
            var action = $form.find('select[name="action"]').val();
            var action2 = $form.find('select[name="action2"]').val();

            // Check if "Clear Database" is selected
            if (action === 'delete_all' || action2 === 'delete_all') {
                var confirmMsg = societypressAdmin.strings.confirmDeleteAll ||
                                 'WARNING: This will PERMANENTLY DELETE ALL MEMBERS. Are you absolutely sure?';
                if (!confirm(confirmMsg)) {
                    e.preventDefault();
                    return false;
                }
            }

            // Check if deleting with "Select All" active
            var $selectAllHidden = $('#societypress_select_all');
            if ($selectAllHidden.val() === '1' && (action === 'delete' || action2 === 'delete')) {
                var confirmMsg2 = societypressAdmin.strings.confirmDeleteAllSelected ||
                                  'You are about to delete ALL members across all pages. Are you sure?';
                if (!confirm(confirmMsg2)) {
                    e.preventDefault();
                    return false;
                }
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
