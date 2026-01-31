/**
 * SocietyPress Member Portal JavaScript
 *
 * Handles AJAX auto-save and form interactions for member portal.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Portal form handler.
     */
    const PortalForm = {
        usStates: {},

        init: function() {
            this.usStates = societypressPortal.usStates || {};
            this.cacheDom();
            this.bindEvents();
            this.initStateAutocomplete();
        },

        cacheDom: function() {
            this.$form = $('#sp-portal-profile-form');
            this.$fields = $('.sp-portal-field');
            this.$saveBtn = $('.sp-save-profile-btn');
            this.$status = $('.sp-save-status');
        },

        bindEvents: function() {
            // Auto-save on blur
            this.$fields.on('blur', function() {
                PortalForm.autoSaveField($(this));
            });

            // Form submission
            this.$form.on('submit', function(e) {
                e.preventDefault();
                PortalForm.saveProfile();
            });
        },

        autoSaveField: function($field) {
            const fieldName = $field.data('field');
            let fieldValue = $field.val();

            // Handle checkboxes
            if ($field.is(':checkbox')) {
                fieldValue = $field.is(':checked') ? '1' : '0';
            }

            this.showStatus('saving');

            $.post({
                url: societypressPortal.ajaxUrl,
                data: {
                    action: 'societypress_portal_save_field',
                    nonce: societypressPortal.nonce,
                    field: fieldName,
                    value: fieldValue
                },
                success: (response) => {
                    if (response.success) {
                        this.showStatus('success');
                    } else {
                        this.showStatus('error', response.data.message);
                    }
                },
                error: () => {
                    this.showStatus('error');
                }
            });
        },

        saveProfile: function() {
            this.$saveBtn.prop('disabled', true);
            this.showStatus('saving');

            const formData = this.$form.serialize();

            $.post({
                url: societypressPortal.ajaxUrl,
                data: formData + '&action=societypress_portal_update_profile&nonce=' + societypressPortal.nonce,
                success: (response) => {
                    this.$saveBtn.prop('disabled', false);

                    if (response.success) {
                        this.showStatus('success', response.data.message);
                    } else {
                        this.showStatus('error', response.data.message);
                    }
                },
                error: () => {
                    this.$saveBtn.prop('disabled', false);
                    this.showStatus('error');
                }
            });
        },

        showStatus: function(type, message) {
            this.$status.removeClass('success error');

            switch (type) {
                case 'saving':
                    this.$status.text(societypressPortal.strings.saving);
                    break;
                case 'success':
                    this.$status.addClass('success').text(message || societypressPortal.strings.saved);
                    setTimeout(() => this.$status.text(''), 3000);
                    break;
                case 'error':
                    this.$status.addClass('error').text(message || societypressPortal.strings.error);
                    break;
                default:
                    this.$status.text('');
            }
        },

        /**
         * Initialize state autocomplete.
         *
         * WHY: Provides predictive text input for state codes with validation.
         */
        initStateAutocomplete: function() {
            const self = this;
            const $state = $('#sp-state');

            if (!$state.length || !Object.keys(this.usStates).length) {
                return;
            }

            // Create datalist for native autocomplete
            const $datalist = $('<datalist id="sp-state-list"></datalist>');
            $.each(this.usStates, function(code, name) {
                $datalist.append($('<option></option>').val(code).text(name + ' (' + code + ')'));
            });
            $('body').append($datalist);
            $state.attr('list', 'sp-state-list');

            // Force uppercase on input
            $state.on('input', function() {
                const val = $(this).val().toUpperCase();
                $(this).val(val);

                // Clear any previous error
                $(this).removeClass('sp-field-error');
                $(this).siblings('.sp-field-error-msg').remove();
            });

            // Validate on blur (before auto-save triggers)
            $state.on('blur', function() {
                const val = $(this).val().trim().toUpperCase();
                $(this).val(val);

                // If empty, that's okay
                if (!val) {
                    return;
                }

                // Check if valid state code
                if (!self.usStates[val]) {
                    $(this).addClass('sp-field-error');
                    if (!$(this).siblings('.sp-field-error-msg').length) {
                        $('<span class="sp-field-error-msg">' + societypressPortal.strings.invalidState + '</span>')
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
     * Event registration cancellation handler.
     */
    const EventCancellation = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            // Cancel registration button in My Events widget
            $(document).on('click', '.sp-cancel-registration-btn', function(e) {
                e.preventDefault();
                EventCancellation.cancelRegistration($(this));
            });
        },

        cancelRegistration: function($btn) {
            const registrationId = $btn.data('registration-id');
            const eventTitle = $btn.data('event-title');
            const originalText = $btn.text();

            // Confirm cancellation
            const confirmMsg = societypressPortal.strings.confirmCancelEvent ||
                'Are you sure you want to cancel your registration for "' + eventTitle + '"?';

            if (!confirm(confirmMsg)) {
                return;
            }

            // Disable button
            $btn.prop('disabled', true).text(societypressPortal.strings.cancelling || 'Cancelling...');

            $.post({
                url: societypressPortal.ajaxUrl,
                data: {
                    action: 'societypress_portal_cancel_registration',
                    nonce: societypressPortal.nonce,
                    registration_id: registrationId
                },
                success: function(response) {
                    if (response.success) {
                        // Remove the event item from the list
                        const $item = $btn.closest('.sp-event-item');
                        $item.fadeOut(300, function() {
                            $(this).remove();

                            // Check if there are no more upcoming events
                            const $upcomingList = $('.sp-upcoming-events .sp-events-list');
                            if ($upcomingList.length && $upcomingList.children().length === 0) {
                                $upcomingList.closest('.sp-upcoming-events')
                                    .html('<p class="sp-no-events">' +
                                          (societypressPortal.strings.noUpcoming || 'No upcoming event registrations.') +
                                          '</p>');
                            }
                        });

                        // Show success message
                        EventCancellation.showMessage(response.data.message, 'success');
                    } else {
                        $btn.prop('disabled', false).text(originalText);
                        EventCancellation.showMessage(response.data.message, 'error');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).text(originalText);
                    EventCancellation.showMessage(societypressPortal.strings.error, 'error');
                }
            });
        },

        showMessage: function(message, type) {
            const $widget = $('.sp-my-events');
            const $existing = $widget.find('.sp-portal-feedback');
            $existing.remove();

            const $msg = $('<div class="sp-portal-feedback sp-feedback-' + type + '">' +
                          '<p>' + message + '</p></div>');
            $widget.find('h3').after($msg);

            if (type === 'success') {
                setTimeout(function() {
                    $msg.fadeOut(300, function() { $(this).remove(); });
                }, 4000);
            }
        }
    };

    // Initialize on document ready
    $(function() {
        if ($('#sp-portal-profile-form').length) {
            PortalForm.init();
        }

        if ($('.sp-my-events').length) {
            EventCancellation.init();
        }
    });

})(jQuery);
