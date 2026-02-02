/**
 * Member Search Autocomplete
 *
 * Provides an AJAX-based search field for finding members by name.
 * Used in Leadership and Committees admin pages to replace long dropdowns
 * that would be unwieldy for organizations with many members.
 *
 * WHY: Organizations may have hundreds of members. Loading all of them
 *      into a dropdown is slow and hard to navigate. This search field
 *      lets admins type a few characters of a name and see matching results
 *      instantly, making member selection much faster.
 *
 * @package SocietyPress
 * @since 0.55d
 */

(function($) {
    'use strict';

    /**
     * Initialize member search fields.
     *
     * Looks for any input with the class 'sp-member-search' and sets up
     * the autocomplete behavior.
     */
    function initMemberSearch() {
        $('.sp-member-search').each(function() {
            var $input = $(this);
            var $hiddenInput = $('#' + $input.data('hidden-input'));
            var $results = $('#' + $input.data('results-container'));
            var excludeIds = $input.data('exclude-ids') || [];
            var minLength = parseInt($input.data('min-length'), 10) || 2;
            var debounceTimer = null;

            // Don't reinitialize if already set up
            if ($input.data('sp-initialized')) {
                return;
            }
            $input.data('sp-initialized', true);

            /**
             * Perform the AJAX search.
             *
             * @param {string} term The search term entered by the user.
             */
            function doSearch(term) {
                if (term.length < minLength) {
                    hideResults();
                    return;
                }

                $results.html('<div class="sp-search-loading">' + societypressAdmin.strings.searching + '</div>').show();

                $.ajax({
                    url: societypressAdmin.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'societypress_search_members',
                        nonce: societypressAdmin.nonce,
                        search: term,
                        exclude: excludeIds
                    },
                    success: function(response) {
                        if (response.success && response.data.length > 0) {
                            showResults(response.data);
                        } else {
                            $results.html('<div class="sp-search-no-results">' + societypressAdmin.strings.noMembersFound + '</div>').show();
                        }
                    },
                    error: function() {
                        $results.html('<div class="sp-search-error">' + societypressAdmin.strings.searchError + '</div>').show();
                    }
                });
            }

            /**
             * Display search results in the dropdown.
             *
             * @param {Array} members Array of member objects from the server.
             */
            function showResults(members) {
                var html = '<ul class="sp-search-results-list">';
                members.forEach(function(member) {
                    html += '<li class="sp-search-result" data-id="' + member.id + '" data-name="' + escapeHtml(member.name) + '">';
                    html += '<span class="sp-result-name">' + escapeHtml(member.name) + '</span>';
                    if (member.status && member.status !== 'active') {
                        html += ' <span class="sp-result-status sp-result-status-' + member.status + '">(' + member.status + ')</span>';
                    }
                    html += '</li>';
                });
                html += '</ul>';
                $results.html(html).show();
            }

            /**
             * Hide the results dropdown.
             */
            function hideResults() {
                $results.hide().empty();
            }

            /**
             * Escape HTML to prevent XSS.
             *
             * @param {string} str The string to escape.
             * @return {string} The escaped string.
             */
            function escapeHtml(str) {
                var div = document.createElement('div');
                div.appendChild(document.createTextNode(str));
                return div.innerHTML;
            }

            /**
             * Select a member from the results.
             *
             * @param {number} id   The member's ID.
             * @param {string} name The member's name.
             */
            function selectMember(id, name) {
                $input.val(name);
                $hiddenInput.val(id);
                hideResults();
                $input.trigger('sp-member-selected', [id, name]);
            }

            /**
             * Clear the selection.
             */
            function clearSelection() {
                $input.val('');
                $hiddenInput.val('');
                $input.trigger('sp-member-cleared');
            }

            // --- Event Handlers ---

            // Search on input with debounce
            // WHY: We wait 300ms after the user stops typing before searching.
            //      This prevents hammering the server with requests on every keystroke.
            $input.on('input', function() {
                clearTimeout(debounceTimer);
                var term = $(this).val().trim();

                // Clear hidden input when user types (they're changing the selection)
                if ($hiddenInput.val()) {
                    $hiddenInput.val('');
                }

                debounceTimer = setTimeout(function() {
                    doSearch(term);
                }, 300);
            });

            // Handle click on result
            $results.on('click', '.sp-search-result', function() {
                var $this = $(this);
                selectMember($this.data('id'), $this.data('name'));
            });

            // Keyboard navigation
            $input.on('keydown', function(e) {
                var $items = $results.find('.sp-search-result');
                var $highlighted = $items.filter('.sp-highlighted');
                var index = $items.index($highlighted);

                switch (e.which) {
                    case 40: // Down arrow
                        e.preventDefault();
                        if ($results.is(':visible')) {
                            $items.removeClass('sp-highlighted');
                            if (index < $items.length - 1) {
                                $items.eq(index + 1).addClass('sp-highlighted');
                            } else {
                                $items.first().addClass('sp-highlighted');
                            }
                        }
                        break;

                    case 38: // Up arrow
                        e.preventDefault();
                        if ($results.is(':visible')) {
                            $items.removeClass('sp-highlighted');
                            if (index > 0) {
                                $items.eq(index - 1).addClass('sp-highlighted');
                            } else {
                                $items.last().addClass('sp-highlighted');
                            }
                        }
                        break;

                    case 13: // Enter
                        e.preventDefault();
                        if ($highlighted.length) {
                            selectMember($highlighted.data('id'), $highlighted.data('name'));
                        }
                        break;

                    case 27: // Escape
                        hideResults();
                        break;
                }
            });

            // Close dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.sp-member-search-wrapper').length) {
                    hideResults();
                }
            });

            // Focus shows results again if there's a search term
            $input.on('focus', function() {
                var term = $(this).val().trim();
                if (term.length >= minLength && !$hiddenInput.val()) {
                    doSearch(term);
                }
            });
        });
    }

    // Initialize on document ready
    $(document).ready(function() {
        initMemberSearch();
    });

    // Expose for external use (e.g., reinitializing after AJAX content load)
    window.spInitMemberSearch = initMemberSearch;

})(jQuery);
