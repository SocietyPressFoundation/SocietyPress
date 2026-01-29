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
            this.initPhoneFormatting();
            this.initEmailValidation();
            this.initExpirationCalculator();
            this.initPhotoUploader();
        },

        /**
         * Bind event handlers.
         */
        bindEvents: function() {
            // Confirm delete actions
            $(document).on('click', '.societypress-delete-member', this.confirmDelete);

            // Tier selection and join date changes auto-update expiration
            $(document).on('change', '#membership_tier_id', this.updateExpiration);
            $(document).on('change', '#join_date', this.updateExpiration);

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
         * Initialize phone number formatting.
         *
         * Formats phone numbers as (XXX) XXX-XXXX as the user types.
         */
        initPhoneFormatting: function() {
            var self = this;

            // Target all phone input fields
            var phoneFields = [
                '#cell_phone',
                '#home_phone',
                '#work_phone'
            ];

            phoneFields.forEach(function(fieldId) {
                var $field = $(fieldId);
                if ($field.length) {
                    // Format on page load if field has a value
                    if ($field.val()) {
                        $field.val(self.formatPhoneNumber($field.val()));
                    }

                    // Format as user types
                    $field.on('input', function() {
                        var cursorPos = this.selectionStart;
                        var oldValue = $(this).val();
                        var oldLength = oldValue.length;
                        var formatted = self.formatPhoneNumber(oldValue);
                        var newLength = formatted.length;

                        $(this).val(formatted);

                        // Adjust cursor position after formatting
                        // If characters were added (formatting), move cursor forward
                        if (newLength > oldLength) {
                            cursorPos += (newLength - oldLength);
                        }

                        // Set cursor position
                        this.setSelectionRange(cursorPos, cursorPos);
                    });

                    // Strip formatting on form submit (store only digits)
                    $field.closest('form').on('submit', function() {
                        var value = $field.val();
                        if (value) {
                            $field.val(value.replace(/\D/g, ''));
                        }
                    });
                }
            });
        },

        /**
         * Format a phone number as (XXX) XXX-XXXX.
         *
         * Takes a string with any characters and returns formatted phone number.
         * Only formats if 10 digits are present.
         *
         * @param {string} value Raw input value.
         * @return {string} Formatted phone number.
         */
        formatPhoneNumber: function(value) {
            if (!value) {
                return '';
            }

            // Strip all non-digits
            var digits = value.replace(/\D/g, '');

            // Don't format if less than 4 digits
            if (digits.length < 4) {
                return digits;
            }

            // Format based on number of digits
            if (digits.length <= 6) {
                // (XXX) XXX
                return '(' + digits.substring(0, 3) + ') ' + digits.substring(3);
            } else if (digits.length <= 10) {
                // (XXX) XXX-XXXX
                return '(' + digits.substring(0, 3) + ') ' + digits.substring(3, 6) + '-' + digits.substring(6);
            } else {
                // Truncate to 10 digits and format
                digits = digits.substring(0, 10);
                return '(' + digits.substring(0, 3) + ') ' + digits.substring(3, 6) + '-' + digits.substring(6);
            }
        },

        /**
         * Initialize email validation.
         *
         * Validates email addresses on blur and provides feedback.
         */
        initEmailValidation: function() {
            var self = this;
            var $emailField = $('#primary_email');

            if ($emailField.length) {
                // Validate on blur (when user leaves the field)
                $emailField.on('blur', function() {
                    var email = $(this).val().trim();
                    var $feedback = $(this).siblings('.email-validation-feedback');

                    // Remove existing feedback
                    $feedback.remove();

                    // Skip validation if field is empty (HTML5 required will handle it)
                    if (email === '') {
                        $(this).removeClass('invalid-email valid-email');
                        return;
                    }

                    // Validate email format
                    if (self.isValidEmail(email)) {
                        $(this).removeClass('invalid-email').addClass('valid-email');
                        $(this).after('<span class="email-validation-feedback valid" style="color: #46b450; margin-left: 5px;">✓ Valid email</span>');
                    } else {
                        $(this).removeClass('valid-email').addClass('invalid-email');
                        $(this).after('<span class="email-validation-feedback invalid" style="color: #dc3232; margin-left: 5px;">✗ Invalid email format</span>');
                    }
                });

                // Clear feedback on input
                $emailField.on('input', function() {
                    $(this).removeClass('invalid-email valid-email');
                    $(this).siblings('.email-validation-feedback').remove();
                });

                // Prevent form submission if email is invalid
                $emailField.closest('form').on('submit', function(e) {
                    var email = $emailField.val().trim();
                    if (email !== '' && !self.isValidEmail(email)) {
                        e.preventDefault();
                        $emailField.focus();
                        alert('Please enter a valid email address.');
                        return false;
                    }
                });
            }
        },

        /**
         * Validate email format.
         *
         * Uses comprehensive regex pattern for email validation.
         *
         * @param {string} email Email address to validate.
         * @return {boolean} True if valid email format.
         */
        isValidEmail: function(email) {
            // RFC 5322 compliant email validation pattern
            var pattern = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
            return pattern.test(email);
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
         * Initialize expiration date calculator.
         *
         * Adds helper text to the expiration field explaining auto-calculation.
         */
        initExpirationCalculator: function() {
            var $expirationField = $('#expiration_date');
            if (!$expirationField.length) {
                return;
            }

            var model = societypressAdmin.expirationModel || 'calendar_year';
            var helpText = '';

            if (model === 'calendar_year') {
                helpText = 'Auto-calculated: December 31 of join year. Can be edited if needed.';
            } else {
                helpText = 'Auto-calculated: Join date + tier duration. Can be edited if needed.';
            }

            // Add help text if it doesn't exist
            if (!$expirationField.next('.description').length) {
                $expirationField.after('<p class="description">' + helpText + '</p>');
            }
        },

        /**
         * Update expiration date based on tier selection and join date.
         *
         * Calculates expiration based on the configured model:
         * - Calendar Year: December 31 of the join year
         * - Anniversary: Join date + tier duration in months
         */
        updateExpiration: function() {
            var $expirationField = $('#expiration_date');
            var $joinDateField = $('#join_date');
            var $tierField = $('#membership_tier_id');

            // Exit if fields don't exist or join date is empty
            if (!$expirationField.length || !$joinDateField.length || !$tierField.length) {
                return;
            }

            var joinDate = $joinDateField.val();
            if (!joinDate) {
                return;
            }

            var tierId = $tierField.val();
            var model = societypressAdmin.expirationModel || 'calendar_year';
            var expirationDate = '';

            if (model === 'calendar_year') {
                // Calendar Year: December 31 of join year
                var joinYear = new Date(joinDate).getFullYear();
                expirationDate = joinYear + '-12-31';
            } else if (model === 'anniversary') {
                // Anniversary: Join date + tier duration
                if (tierId && societypressAdmin.tiers && societypressAdmin.tiers[tierId]) {
                    var tier = societypressAdmin.tiers[tierId];
                    var durationMonths = parseInt(tier.duration_months) || 12;

                    var date = new Date(joinDate);
                    date.setMonth(date.getMonth() + durationMonths);

                    // Format as YYYY-MM-DD
                    var year = date.getFullYear();
                    var month = String(date.getMonth() + 1).padStart(2, '0');
                    var day = String(date.getDate()).padStart(2, '0');
                    expirationDate = year + '-' + month + '-' + day;
                }
            }

            // Update the expiration field if we calculated a date
            if (expirationDate) {
                $expirationField.val(expirationDate);

                // Add visual feedback
                $expirationField.css('background-color', '#ffffcc');
                setTimeout(function() {
                    $expirationField.css('background-color', '');
                }, 1000);
            }
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
        },

        /**
         * Initialize member photo uploader.
         *
         * Uses WordPress media uploader for selecting/uploading member photos.
         * Enforces 1MB max file size. Photos display as circles via CSS.
         */
        initPhotoUploader: function() {
            var $uploadBtn = $('#sp-member-photo-upload');
            var $removeBtn = $('#sp-member-photo-remove');
            var $preview = $('#sp-member-photo-preview');
            var $previewImg = $('#sp-member-photo-img');
            var $photoIdField = $('#photo_id');

            // Exit if upload button doesn't exist (not on member edit page)
            if (!$uploadBtn.length) {
                return;
            }

            var mediaFrame;

            // Handle upload button click
            $uploadBtn.on('click', function(e) {
                e.preventDefault();

                // If the media frame already exists, reopen it
                if (mediaFrame) {
                    mediaFrame.open();
                    return;
                }

                // Create the media frame
                mediaFrame = wp.media({
                    title: societypressAdmin.strings.photoUploadTitle || 'Select Member Photo',
                    button: {
                        text: societypressAdmin.strings.photoUploadButton || 'Use this photo'
                    },
                    library: {
                        type: 'image'
                    },
                    multiple: false
                });

                // When an image is selected, run a callback
                mediaFrame.on('select', function() {
                    var attachment = mediaFrame.state().get('selection').first().toJSON();

                    // Check file size (1MB = 1048576 bytes)
                    if (attachment.filesizeInBytes && attachment.filesizeInBytes > 1048576) {
                        alert(societypressAdmin.strings.photoTooLarge || 'Photo must be less than 1MB.');
                        return;
                    }

                    // Get the thumbnail URL if available, otherwise use full
                    var imageUrl = attachment.sizes && attachment.sizes.thumbnail
                        ? attachment.sizes.thumbnail.url
                        : attachment.url;

                    // Update the preview
                    $previewImg.attr('src', imageUrl);
                    $preview.show();
                    $photoIdField.val(attachment.id);
                    $uploadBtn.text(societypressAdmin.strings.photoChangeButton || 'Change Photo');
                });

                // Open the media frame
                mediaFrame.open();
            });

            // Handle remove button click
            $removeBtn.on('click', function(e) {
                e.preventDefault();
                $preview.hide();
                $previewImg.attr('src', '');
                $photoIdField.val('');
                $uploadBtn.text(societypressAdmin.strings.photoUploadButtonText || 'Upload Photo');
            });
        }
    };

    /**
     * Initialize on document ready.
     */
    $(document).ready(function() {
        SocietyPressAdmin.init();
    });

})(jQuery);
