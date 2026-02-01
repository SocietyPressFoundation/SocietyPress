/**
 * SocietyPress Event Registration Frontend
 *
 * Handles AJAX registration and cancellation for event time slots.
 * Designed for accessibility with clear feedback and keyboard support.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Event Registration Module
     */
    var SPEventRegistration = {

        /**
         * Initialize the module.
         */
        init: function() {
            this.$container = $('.sp-event-registration');

            if (!this.$container.length) {
                return;
            }

            this.bindEvents();
        },

        /**
         * Bind event handlers.
         */
        bindEvents: function() {
            var self = this;

            // Register button click (for specific slot)
            this.$container.on('click', '.sp-register-btn:not(.sp-join-waitlist-btn)', function(e) {
                e.preventDefault();
                self.handleRegister($(this));
            });

            // Join waitlist button click (event-wide waitlist)
            this.$container.on('click', '.sp-join-waitlist-btn', function(e) {
                e.preventDefault();
                self.handleJoinWaitlist($(this));
            });

            // Cancel button click
            this.$container.on('click', '.sp-cancel-btn', function(e) {
                e.preventDefault();
                self.handleCancel($(this));
            });
        },

        /**
         * Handle registration button click.
         *
         * @param {jQuery} $button The clicked button.
         */
        handleRegister: function($button) {
            var self = this;
            var slotId = $button.data('slot-id');
            var $row = $button.closest('.sp-slot-row');
            var originalText = $button.text();

            // Disable button and show loading state
            $button.prop('disabled', true)
                   .text(spEventReg.strings.registering)
                   .addClass('sp-loading');

            // Send AJAX request
            $.ajax({
                url: spEventReg.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'societypress_register_event',
                    nonce: spEventReg.nonce,
                    slot_id: slotId
                },
                success: function(response) {
                    if (response.success) {
                        // Replace the row with updated HTML
                        var $newRow = $(response.data.rowHtml);
                        $row.replaceWith($newRow);

                        // Show success message
                        self.showMessage(response.data.message, 'success');

                        // If they were added to waitlist, the status will indicate that
                        if (response.data.status === 'waitlist') {
                            self.showMessage(response.data.message, 'info');
                        }

                        // Update other rows to show "Registered for X" note
                        self.refreshOtherRows(slotId);
                    } else {
                        self.handleError($button, originalText, response.data.message);
                    }
                },
                error: function() {
                    self.handleError($button, originalText, spEventReg.strings.error);
                }
            });
        },

        /**
         * Handle join waitlist button click (event-wide waitlist).
         *
         * @param {jQuery} $button The clicked button.
         */
        handleJoinWaitlist: function($button) {
            var self = this;
            var eventId = $button.data('event-id');
            var originalText = $button.text();

            // Disable button and show loading state
            $button.prop('disabled', true)
                   .text(spEventReg.strings.registering)
                   .addClass('sp-loading');

            // Send AJAX request
            $.ajax({
                url: spEventReg.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'societypress_join_waitlist',
                    nonce: spEventReg.nonce,
                    event_id: eventId
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        self.showMessage(response.data.message, 'success');

                        // Reload page to show updated waitlist status
                        if (response.data.reload) {
                            self.refreshPage();
                        }
                    } else {
                        self.handleError($button, originalText, response.data.message);
                    }
                },
                error: function() {
                    self.handleError($button, originalText, spEventReg.strings.error);
                }
            });
        },

        /**
         * Handle cancel button click.
         * Works for both slot-specific cancellation and event-wide waitlist cancellation.
         *
         * @param {jQuery} $button The clicked button.
         */
        handleCancel: function($button) {
            var self = this;
            var registrationId = $button.data('registration-id');
            var $row = $button.closest('.sp-slot-row');
            var $waitlistStatus = $button.closest('.sp-waitlist-status');
            var originalText = $button.text();

            // Confirm cancellation
            if (!confirm(spEventReg.strings.confirmCancel)) {
                return;
            }

            // Disable button and show loading state
            $button.prop('disabled', true)
                   .text(spEventReg.strings.cancelling)
                   .addClass('sp-loading');

            // Send AJAX request
            $.ajax({
                url: spEventReg.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'societypress_cancel_registration',
                    nonce: spEventReg.nonce,
                    registration_id: registrationId
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        self.showMessage(response.data.message, 'success');

                        // If cancelling from slot row, update the row
                        if ($row.length && response.data.rowHtml) {
                            var $newRow = $(response.data.rowHtml);
                            $row.replaceWith($newRow);
                        }

                        // Refresh page to get updated state
                        self.refreshPage();
                    } else {
                        self.handleError($button, originalText, response.data.message);
                    }
                },
                error: function() {
                    self.handleError($button, originalText, spEventReg.strings.error);
                }
            });
        },

        /**
         * Handle AJAX error.
         *
         * @param {jQuery} $button      The button that was clicked.
         * @param {string} originalText Original button text to restore.
         * @param {string} message      Error message to display.
         */
        handleError: function($button, originalText, message) {
            $button.prop('disabled', false)
                   .text(originalText)
                   .removeClass('sp-loading');

            this.showMessage(message, 'error');
        },

        /**
         * Show a feedback message.
         *
         * @param {string} message The message to show.
         * @param {string} type    Message type: success, error, info.
         */
        showMessage: function(message, type) {
            var $existing = this.$container.find('.sp-registration-feedback');
            $existing.remove();

            var $message = $('<div class="sp-registration-feedback sp-feedback-' + type + '">' +
                             '<p>' + this.escapeHtml(message) + '</p>' +
                             '</div>');

            // Insert after the heading
            this.$container.find('h3').first().after($message);

            // Auto-hide after 5 seconds for success messages
            if (type === 'success') {
                setTimeout(function() {
                    $message.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 5000);
            }

            // Scroll message into view if needed
            if ($message.length && $message[0].scrollIntoView) {
                $message[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        },

        /**
         * Escape HTML entities for safe output.
         *
         * @param {string} text Text to escape.
         * @return {string} Escaped text.
         */
        escapeHtml: function(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        /**
         * Refresh other rows after registration.
         * Updates the "Registered for X" notes on other slots.
         *
         * @param {number} registeredSlotId The slot ID that was just registered.
         */
        refreshOtherRows: function(registeredSlotId) {
            // For simplicity, reload the page to get fresh state
            // This ensures all rows show consistent data
            this.refreshPage();
        },

        /**
         * Refresh the page to get updated state.
         * Uses a short delay to show the success message first.
         */
        refreshPage: function() {
            setTimeout(function() {
                window.location.reload();
            }, 1500);
        }
    };

    /**
     * Initialize on document ready.
     */
    $(document).ready(function() {
        SPEventRegistration.init();
    });

})(jQuery);
