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
        init: function() {
            this.cacheDom();
            this.bindEvents();
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
        }
    };

    // Initialize on document ready
    $(function() {
        if ($('#sp-portal-profile-form').length) {
            PortalForm.init();
        }
    });

})(jQuery);
