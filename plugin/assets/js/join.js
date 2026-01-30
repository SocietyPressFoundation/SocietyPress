/**
 * SocietyPress Join Form JavaScript
 *
 * Handles conditional display of student verification field,
 * phone number formatting, and PayPal payment integration.
 *
 * @package SocietyPress
 * @since 0.27d
 */

(function($) {
    'use strict';

    /**
     * Join Form Module
     */
    var JoinForm = {

        /**
         * Student tier ID from localized data.
         */
        studentTierId: 0,

        /**
         * Payment settings from localized data.
         */
        paymentMode: 'disabled',
        paypalEnabled: false,
        tierPrices: {},
        organizationName: '',

        /**
         * Tracks whether payment has been completed.
         */
        paymentCompleted: false,

        /**
         * PayPal buttons instance (for re-rendering).
         */
        paypalButtonsInstance: null,

        /**
         * Initialize the module.
         */
        /**
         * US States from localized data.
         */
        usStates: {},

        init: function() {
            this.studentTierId = societypressJoin.studentTierId || 0;
            this.paymentMode = societypressJoin.paymentMode || 'disabled';
            this.paypalEnabled = societypressJoin.paypalEnabled || false;
            this.tierPrices = societypressJoin.tierPrices || {};
            this.organizationName = societypressJoin.organizationName || '';
            this.usStates = societypressJoin.usStates || {};

            this.bindEvents();
            this.initPhoneFormatting();
            this.initStateAutocomplete();
            this.checkInitialTierSelection();

            // Initialize PayPal if enabled
            if (this.paypalEnabled && typeof paypal !== 'undefined') {
                this.initPayPalButtons();
            }
        },

        /**
         * Bind event handlers.
         */
        bindEvents: function() {
            var self = this;

            // Tier selection change
            $(document).on('change', 'input[name="membership_tier_id"]', this.handleTierChange.bind(this));

            // Pay later checkbox
            $(document).on('change', '#sp-pay-later', this.handlePayLaterChange.bind(this));

            // Form submission validation
            $('#sp-join-form').on('submit', function(e) {
                return self.validateFormSubmit(e);
            });
        },

        /**
         * Check initial tier selection on page load.
         *
         * WHY: If form is reloaded after validation error with student tier selected,
         *      we need to show the student fields and update payment info.
         */
        checkInitialTierSelection: function() {
            var $checked = $('input[name="membership_tier_id"]:checked');
            if ($checked.length) {
                var tierId = parseInt($checked.val(), 10);
                this.toggleStudentFields(tierId);
                this.updatePaymentSection(tierId);
            }
        },

        /**
         * Handle tier selection change.
         *
         * @param {Event} e Change event.
         */
        handleTierChange: function(e) {
            var tierId = parseInt($(e.target).val(), 10);
            this.toggleStudentFields(tierId);
            this.updatePaymentSection(tierId);

            // Reset payment status when tier changes
            this.paymentCompleted = false;
            $('#sp-paypal-order-id').val('');
            $('#sp-payment-status').val('');
        },

        /**
         * Show/hide student verification fields.
         *
         * @param {number} selectedTierId Selected tier ID.
         */
        toggleStudentFields: function(selectedTierId) {
            var $studentFields = $('#sp-student-fields');
            var $schoolInput = $studentFields.find('input[name="school_name"]');

            if (selectedTierId === this.studentTierId && this.studentTierId > 0) {
                $studentFields.slideDown(200);
                $schoolInput.prop('required', true);
            } else {
                $studentFields.slideUp(200);
                $schoolInput.prop('required', false).val('');
            }
        },

        /**
         * Update payment section based on selected tier.
         *
         * @param {number} tierId Selected tier ID.
         */
        updatePaymentSection: function(tierId) {
            if (!this.paypalEnabled) {
                return;
            }

            var price = this.tierPrices[tierId] || 0;
            var $paymentSection = $('#sp-payment-section');
            var $paymentSummary = $('#sp-payment-summary');
            var $freeNotice = $('#sp-payment-free-notice');
            var $paypalContainer = $('#sp-paypal-container');
            var $payLaterOption = $('#sp-pay-later-option');

            // Find tier name from the selected radio
            var $selectedTier = $('input[name="membership_tier_id"]:checked');
            var tierName = $selectedTier.siblings('.sp-tier-card').find('.sp-tier-name').text();

            if (price > 0) {
                // Show payment UI
                $('#sp-payment-tier-name').text(tierName);
                $('#sp-payment-amount').text('$' + price.toFixed(2));
                $paymentSummary.show();
                $freeNotice.hide();
                $paypalContainer.show();
                $payLaterOption.show();
            } else {
                // Free tier - hide payment UI
                $paymentSummary.hide();
                $freeNotice.show();
                $paypalContainer.hide();
                $payLaterOption.hide();
            }
        },

        /**
         * Handle pay later checkbox change.
         *
         * @param {Event} e Change event.
         */
        handlePayLaterChange: function(e) {
            var payLater = $(e.target).is(':checked');
            var $paypalContainer = $('#sp-paypal-container');
            var $submitButton = $('#sp-submit-button');

            if (payLater) {
                // Dim PayPal buttons when pay later is selected
                $paypalContainer.addClass('sp-paypal-dimmed');
                $submitButton.prop('disabled', false);
            } else {
                $paypalContainer.removeClass('sp-paypal-dimmed');
            }
        },

        /**
         * Initialize PayPal buttons.
         *
         * WHY: Creates the PayPal Smart Payment Buttons that handle
         *      the complete checkout flow within a popup.
         */
        initPayPalButtons: function() {
            var self = this;
            var $container = $('#sp-paypal-container');

            if (!$container.length) {
                return;
            }

            // Render PayPal buttons
            paypal.Buttons({
                // Style the buttons
                style: {
                    layout: 'vertical',
                    color: 'gold',
                    shape: 'rect',
                    label: 'paypal'
                },

                /**
                 * Create order when button is clicked.
                 *
                 * WHY: Builds the order object with the selected tier's price.
                 */
                createOrder: function(data, actions) {
                    var $selectedTier = $('input[name="membership_tier_id"]:checked');
                    var tierId = $selectedTier.val();
                    var price = self.tierPrices[tierId] || 0;
                    var tierName = $selectedTier.siblings('.sp-tier-card').find('.sp-tier-name').text();

                    if (price <= 0) {
                        // Should not happen - PayPal buttons hidden for free tiers
                        return actions.reject();
                    }

                    return actions.order.create({
                        purchase_units: [{
                            description: self.organizationName + ' - ' + tierName + ' Membership',
                            amount: {
                                currency_code: 'USD',
                                value: price.toFixed(2)
                            }
                        }],
                        application_context: {
                            shipping_preference: 'NO_SHIPPING'
                        }
                    });
                },

                /**
                 * Called when buyer approves the payment.
                 *
                 * WHY: Captures the payment and updates form hidden fields
                 *      so the PHP handler knows payment succeeded.
                 */
                onApprove: function(data, actions) {
                    // Show processing state
                    self.showPaymentProcessing();

                    return actions.order.capture().then(function(orderData) {
                        // Payment successful
                        self.paymentCompleted = true;
                        $('#sp-paypal-order-id').val(orderData.id);
                        $('#sp-payment-status').val('completed');

                        // Update UI to show success
                        self.showPaymentSuccess();

                        // Uncheck pay later if it was checked
                        $('#sp-pay-later').prop('checked', false);
                    });
                },

                /**
                 * Called when buyer cancels the payment.
                 */
                onCancel: function(data) {
                    self.showPaymentMessage(societypressJoin.strings.paymentCancelled, 'warning');
                },

                /**
                 * Called when an error occurs.
                 */
                onError: function(err) {
                    console.error('PayPal error:', err);
                    self.showPaymentMessage(societypressJoin.strings.paymentError, 'error');
                }
            }).render('#sp-paypal-container');
        },

        /**
         * Show payment processing state.
         */
        showPaymentProcessing: function() {
            var $container = $('#sp-paypal-container');
            $container.addClass('sp-paypal-processing');
            $container.find('.sp-payment-overlay').remove();
            $container.append('<div class="sp-payment-overlay"><span>' + societypressJoin.strings.processing + '</span></div>');
        },

        /**
         * Show payment success state.
         */
        showPaymentSuccess: function() {
            var $container = $('#sp-paypal-container');
            var $payLaterOption = $('#sp-pay-later-option');
            var $submitButton = $('#sp-submit-button');

            // Replace PayPal buttons with success message
            $container.html('<div class="sp-payment-success">' +
                '<span class="sp-payment-success-icon">&#10003;</span> ' +
                societypressJoin.strings.paymentSuccess +
            '</div>');

            // Hide pay later option
            $payLaterOption.hide();

            // Enable and highlight submit button
            $submitButton.prop('disabled', false).addClass('sp-submit-ready');
        },

        /**
         * Show payment message.
         *
         * @param {string} message Message to display.
         * @param {string} type    Message type (info, warning, error).
         */
        showPaymentMessage: function(message, type) {
            var $container = $('#sp-paypal-container');
            $container.removeClass('sp-paypal-processing');
            $container.find('.sp-payment-overlay').remove();

            // Insert message above PayPal buttons
            var $existingMsg = $container.prev('.sp-payment-message-inline');
            if ($existingMsg.length) {
                $existingMsg.remove();
            }

            $('<div class="sp-payment-message-inline sp-' + type + '">' + message + '</div>')
                .insertBefore($container);
        },

        /**
         * Validate form before submission.
         *
         * @param {Event} e Submit event.
         * @return {boolean} Whether to allow submission.
         */
        validateFormSubmit: function(e) {
            // If payment is not enabled, allow normal submission
            if (!this.paypalEnabled) {
                return true;
            }

            var $selectedTier = $('input[name="membership_tier_id"]:checked');
            if (!$selectedTier.length) {
                return true; // Let server-side validation handle this
            }

            var tierId = parseInt($selectedTier.val(), 10);
            var price = this.tierPrices[tierId] || 0;

            // If tier is free, allow submission
            if (price <= 0) {
                return true;
            }

            // Check if payment is completed or pay later is selected
            var paymentCompleted = this.paymentCompleted || $('#sp-payment-status').val() === 'completed';
            var payLater = $('#sp-pay-later').is(':checked');

            if (this.paymentMode === 'required') {
                // Payment required - must have completed payment
                if (!paymentCompleted) {
                    e.preventDefault();
                    this.showPaymentMessage(societypressJoin.strings.paymentRequired, 'error');
                    // Scroll to payment section
                    $('html, body').animate({
                        scrollTop: $('#sp-payment-section').offset().top - 50
                    }, 300);
                    return false;
                }
            } else if (this.paymentMode === 'optional') {
                // Payment optional - must have paid OR selected pay later
                if (!paymentCompleted && !payLater) {
                    e.preventDefault();
                    this.showPaymentMessage(societypressJoin.strings.paymentRequired, 'error');
                    $('html, body').animate({
                        scrollTop: $('#sp-payment-section').offset().top - 50
                    }, 300);
                    return false;
                }
            }

            return true;
        },

        /**
         * Initialize phone number formatting.
         *
         * Formats phone numbers as (XXX) XXX-XXXX as the user types.
         */
        initPhoneFormatting: function() {
            var self = this;
            var $phone = $('#sp-phone');

            if (!$phone.length) {
                return;
            }

            // Format on input
            $phone.on('input', function() {
                var cursorPos = this.selectionStart;
                var oldValue = $(this).val();
                var oldLength = oldValue.length;
                var formatted = self.formatPhoneNumber(oldValue);
                var newLength = formatted.length;

                $(this).val(formatted);

                // Adjust cursor position after formatting
                if (newLength > oldLength) {
                    cursorPos += (newLength - oldLength);
                }

                this.setSelectionRange(cursorPos, cursorPos);
            });

            // Strip formatting on form submit (store only digits)
            $phone.closest('form').on('submit', function() {
                var value = $phone.val();
                if (value) {
                    $phone.val(value.replace(/\D/g, ''));
                }
            });
        },

        /**
         * Format a phone number as (XXX) XXX-XXXX.
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
                return '(' + digits.substring(0, 3) + ') ' + digits.substring(3);
            } else if (digits.length <= 10) {
                return '(' + digits.substring(0, 3) + ') ' + digits.substring(3, 6) + '-' + digits.substring(6);
            } else {
                // Truncate to 10 digits and format
                digits = digits.substring(0, 10);
                return '(' + digits.substring(0, 3) + ') ' + digits.substring(3, 6) + '-' + digits.substring(6);
            }
        },

        /**
         * Initialize state autocomplete.
         *
         * WHY: Provides predictive text input for state codes with validation.
         *      Shows suggestions as user types, validates on blur.
         */
        initStateAutocomplete: function() {
            var self = this;
            var $state = $('#sp-state');

            if (!$state.length || !Object.keys(this.usStates).length) {
                return;
            }

            // Create datalist for native autocomplete
            var $datalist = $('<datalist id="sp-state-list"></datalist>');
            $.each(this.usStates, function(code, name) {
                $datalist.append($('<option></option>').val(code).text(name + ' (' + code + ')'));
            });
            $('body').append($datalist);
            $state.attr('list', 'sp-state-list');

            // Force uppercase and validate on input
            $state.on('input', function() {
                var val = $(this).val().toUpperCase();
                $(this).val(val);

                // Clear any previous error
                $(this).removeClass('sp-field-error');
                $(this).siblings('.sp-field-error-msg').remove();
            });

            // Validate on blur
            $state.on('blur', function() {
                var val = $(this).val().trim().toUpperCase();
                $(this).val(val);

                // If empty, that's okay (not required)
                if (!val) {
                    return;
                }

                // Check if valid state code
                if (!self.usStates[val]) {
                    $(this).addClass('sp-field-error');
                    if (!$(this).siblings('.sp-field-error-msg').length) {
                        $('<span class="sp-field-error-msg">' + societypressJoin.strings.invalidState + '</span>')
                            .insertAfter($(this));
                    }
                }
            });

            // Set attributes for better UX
            $state.attr({
                'maxlength': 2,
                'autocomplete': 'address-level1',
                'placeholder': 'TX'
            });
        }
    };

    /**
     * Initialize on document ready.
     */
    $(document).ready(function() {
        JoinForm.init();
    });

})(jQuery);
