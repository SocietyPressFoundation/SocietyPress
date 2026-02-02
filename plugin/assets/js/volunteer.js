/**
 * Volunteer Opportunities Frontend JavaScript
 *
 * WHY: Handles AJAX signup/cancel actions and client-side filtering.
 *      Provides immediate feedback for actions without full page reload.
 *      Designed for accessibility and older browsers.
 *
 * @package SocietyPress
 * @since 0.54d
 */

(function($) {
    'use strict';

    /**
     * Initialize volunteer functionality when document is ready.
     */
    $(document).ready(function() {
        initFilters();
        initSignupButtons();
        initCancelButtons();
        initPortalCancelButtons();
    });

    /**
     * Initialize client-side filtering of opportunity cards.
     *
     * WHY: Instant filtering without server round-trip improves UX,
     *      especially for members with slower connections.
     */
    function initFilters() {
        var $filters = $('.sp-volunteer-filter');
        var $cards = $('.sp-volunteer-card');

        if (!$filters.length || !$cards.length) {
            return;
        }

        $filters.on('change', function() {
            var committeeFilter = $('#sp-volunteer-filter-committee').val();
            var typeFilter = $('#sp-volunteer-filter-type').val();

            $cards.each(function() {
                var $card = $(this);
                var cardCommittee = $card.data('committee') || '';
                var cardType = $card.data('type') || '';

                var showByCommittee = !committeeFilter || cardCommittee === committeeFilter;
                var showByType = !typeFilter || cardType === typeFilter;

                if (showByCommittee && showByType) {
                    $card.show();
                } else {
                    $card.hide();
                }
            });

            // Show message if no results
            var visibleCount = $cards.filter(':visible').length;
            var $emptyMessage = $('.sp-volunteer-filter-empty');

            if (visibleCount === 0) {
                if (!$emptyMessage.length) {
                    $('<div class="sp-volunteer-filter-empty sp-volunteer-empty"><p>' +
                        (societypressVolunteer.strings.no_results || 'No opportunities match your filters.') +
                        '</p></div>').insertAfter('.sp-volunteer-list');
                }
                $emptyMessage.show();
            } else {
                $emptyMessage.hide();
            }
        });
    }

    /**
     * Initialize signup button handlers.
     *
     * WHY: AJAX submission provides immediate feedback and keeps the page
     *      context intact (scroll position, filter state).
     */
    function initSignupButtons() {
        $(document).on('click', '.sp-btn-signup, .sp-btn-waitlist', function(e) {
            e.preventDefault();

            var $button = $(this);
            var opportunityId = $button.data('opportunity-id');

            if (!opportunityId || $button.hasClass('sp-btn-loading')) {
                return;
            }

            // Show loading state
            var originalText = $button.text();
            $button.addClass('sp-btn-loading')
                   .text(societypressVolunteer.strings.signing_up || 'Signing up...')
                   .prop('disabled', true);

            $.ajax({
                url: societypressVolunteer.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'societypress_volunteer_signup',
                    nonce: societypressVolunteer.nonce,
                    opportunity_id: opportunityId
                },
                success: function(response) {
                    if (response.success) {
                        // Replace button with status display
                        var statusClass = response.data.status === 'confirmed' ? 'sp-status-confirmed' : 'sp-status-waitlist';
                        var statusLabel = response.data.status === 'confirmed' ? 'Confirmed' : 'Waitlist';

                        var $signedUp = $('<div class="sp-volunteer-signed-up">' +
                            '<span class="sp-signup-status ' + statusClass + '">' + statusLabel + '</span>' +
                            '<button type="button" class="sp-volunteer-btn sp-btn-cancel" data-signup-id="' +
                            response.data.signup_id + '">Cancel</button>' +
                            '</div>');

                        $button.replaceWith($signedUp);

                        // Update capacity display if present
                        var $card = $signedUp.closest('.sp-volunteer-card');
                        updateCapacityDisplay($card);

                        // Show success message
                        showMessage(response.data.message, 'success', $card);
                    } else {
                        // Show error
                        $button.removeClass('sp-btn-loading')
                               .text(originalText)
                               .prop('disabled', false);
                        showMessage(response.data.message || societypressVolunteer.strings.error, 'error', $button.closest('.sp-volunteer-card'));
                    }
                },
                error: function() {
                    $button.removeClass('sp-btn-loading')
                           .text(originalText)
                           .prop('disabled', false);
                    showMessage(societypressVolunteer.strings.error, 'error', $button.closest('.sp-volunteer-card'));
                }
            });
        });
    }

    /**
     * Initialize cancel button handlers on opportunity cards.
     */
    function initCancelButtons() {
        $(document).on('click', '.sp-volunteer-card .sp-btn-cancel', function(e) {
            e.preventDefault();

            var $button = $(this);
            var signupId = $button.data('signup-id');

            if (!signupId || $button.hasClass('sp-btn-loading')) {
                return;
            }

            // Confirm cancellation
            if (!confirm(societypressVolunteer.strings.confirm_cancel || 'Are you sure you want to cancel your signup?')) {
                return;
            }

            // Show loading state
            var originalText = $button.text();
            $button.addClass('sp-btn-loading')
                   .text(societypressVolunteer.strings.cancelling || 'Cancelling...')
                   .prop('disabled', true);

            var $card = $button.closest('.sp-volunteer-card');
            var opportunityId = $card.data('opportunity-id');

            $.ajax({
                url: societypressVolunteer.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'societypress_volunteer_cancel',
                    nonce: societypressVolunteer.nonce,
                    signup_id: signupId
                },
                success: function(response) {
                    if (response.success) {
                        // Replace status with signup button
                        var $signedUp = $button.closest('.sp-volunteer-signed-up');
                        var $newButton = $('<button type="button" class="sp-volunteer-btn sp-btn-signup" ' +
                            'data-opportunity-id="' + opportunityId + '">Sign Up</button>');

                        $signedUp.replaceWith($newButton);

                        // Update capacity display
                        updateCapacityDisplay($card);

                        // Show success message
                        showMessage(response.data.message, 'success', $card);
                    } else {
                        $button.removeClass('sp-btn-loading')
                               .text(originalText)
                               .prop('disabled', false);
                        showMessage(response.data.message || societypressVolunteer.strings.error, 'error', $card);
                    }
                },
                error: function() {
                    $button.removeClass('sp-btn-loading')
                           .text(originalText)
                           .prop('disabled', false);
                    showMessage(societypressVolunteer.strings.error, 'error', $card);
                }
            });
        });
    }

    /**
     * Initialize cancel buttons in the member portal volunteer widget.
     *
     * WHY: Uses the portal's existing AJAX infrastructure for consistency.
     */
    function initPortalCancelButtons() {
        $(document).on('click', '.sp-my-volunteer .sp-btn-cancel', function(e) {
            e.preventDefault();

            var $button = $(this);
            var signupId = $button.data('signup-id');

            if (!signupId || $button.hasClass('sp-btn-loading')) {
                return;
            }

            // Confirm cancellation
            if (!confirm(societypressVolunteer.strings.confirm_cancel || 'Are you sure you want to cancel?')) {
                return;
            }

            var originalText = $button.text();
            var $item = $button.closest('.sp-commitment-item');

            $button.addClass('sp-btn-loading')
                   .text(societypressVolunteer.strings.cancelling || 'Cancelling...')
                   .prop('disabled', true);

            $.ajax({
                url: societypressVolunteer.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'societypress_volunteer_cancel',
                    nonce: societypressVolunteer.nonce,
                    signup_id: signupId
                },
                success: function(response) {
                    if (response.success) {
                        // Fade out and remove the item
                        $item.fadeOut(300, function() {
                            $(this).remove();

                            // Check if list is empty
                            var $list = $('.sp-volunteer-commitments-list');
                            if ($list.children().length === 0) {
                                $list.html('<li class="sp-no-commitments">No upcoming volunteer commitments.</li>');
                            }
                        });
                    } else {
                        $button.removeClass('sp-btn-loading')
                               .text(originalText)
                               .prop('disabled', false);
                        alert(response.data.message || societypressVolunteer.strings.error);
                    }
                },
                error: function() {
                    $button.removeClass('sp-btn-loading')
                           .text(originalText)
                           .prop('disabled', false);
                    alert(societypressVolunteer.strings.error);
                }
            });
        });
    }

    /**
     * Update the capacity display on a card after signup/cancel.
     *
     * WHY: Keeps the UI in sync without requiring a page refresh.
     *      Note: This is a simple implementation - more complex scenarios
     *      might need a full refresh from server.
     *
     * @param {jQuery} $card The opportunity card element.
     */
    function updateCapacityDisplay($card) {
        // For now, just add a subtle visual indicator that something changed
        // A full refresh would be needed to get accurate counts from server
        $card.addClass('sp-updated');
        setTimeout(function() {
            $card.removeClass('sp-updated');
        }, 1000);
    }

    /**
     * Show a temporary message to the user.
     *
     * @param {string} message The message text.
     * @param {string} type    'success' or 'error'.
     * @param {jQuery} $context Element to show message near.
     */
    function showMessage(message, type, $context) {
        // Remove any existing messages
        $('.sp-volunteer-message').remove();

        var $message = $('<div class="sp-volunteer-message sp-message-' + type + '">' +
            '<span>' + message + '</span>' +
            '</div>');

        // Add styling
        $message.css({
            position: 'absolute',
            top: '50%',
            left: '50%',
            transform: 'translate(-50%, -50%)',
            padding: '0.75rem 1.25rem',
            borderRadius: '6px',
            fontSize: '0.95rem',
            fontWeight: '600',
            zIndex: '100',
            backgroundColor: type === 'success' ? '#d4edda' : '#f8d7da',
            color: type === 'success' ? '#155724' : '#721c24',
            border: '1px solid ' + (type === 'success' ? '#c3e6cb' : '#f5c6cb'),
            boxShadow: '0 4px 12px rgba(0,0,0,0.15)'
        });

        // Position relative to context
        if ($context && $context.length) {
            $context.css('position', 'relative');
            $context.append($message);
        } else {
            $('body').append($message);
        }

        // Auto-remove after delay
        setTimeout(function() {
            $message.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }

})(jQuery);
