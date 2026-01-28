/**
 * SocietyPress Public Directory JavaScript
 *
 * Handles AJAX search and filtering for member directory.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Directory AJAX search handler.
     */
    const DirectorySearch = {
        init: function() {
            this.cacheDom();
            this.bindEvents();
        },

        cacheDom: function() {
            this.$form = $('.sp-directory-search');
            this.$searchInput = $('#sp-search-input');
            this.$tierFilter = $('#sp-tier-filter');
            this.$stateFilter = $('#sp-state-filter');
            this.$grid = $('.sp-directory-grid');
        },

        bindEvents: function() {
            // Debounce search input
            let searchTimeout;
            this.$searchInput.on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    DirectorySearch.performSearch();
                }, 300);
            });

            // Filter changes
            this.$tierFilter.on('change', () => this.performSearch());
            this.$stateFilter.on('change', () => this.performSearch());
        },

        performSearch: function() {
            const data = {
                action: 'societypress_directory_search',
                nonce: societypressDirectory.nonce,
                search: this.$searchInput.val(),
                tier: this.$tierFilter.val(),
                state: this.$stateFilter.val(),
                per_page: 24,
                page: 1
            };

            $.post(societypressDirectory.ajaxUrl, data, function(response) {
                if (response.success && response.data.members) {
                    DirectorySearch.updateGrid(response.data.members);
                }
            });
        },

        updateGrid: function(members) {
            // TODO: Implement grid update with new members
            // This would rebuild the member cards dynamically
            console.log('Updating grid with', members.length, 'members');
        }
    };

    // Initialize on document ready
    $(function() {
        if ($('.societypress-directory').length) {
            DirectorySearch.init();
        }
    });

})(jQuery);
