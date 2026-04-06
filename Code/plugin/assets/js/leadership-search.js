/**
 * SocietyPress — Leadership & Committees Search
 *
 * WHY: With hundreds or thousands of members, admins need a fast way
 * to find specific people when assigning officer or committee roles.
 * This script powers two features:
 *
 *   1. Top-of-page member search — type a name, see matching members
 *      with their existing leadership roles, and click to assign them
 *      directly to an officer or committee position.
 *
 *   2. Form dropdown filters — text inputs above the Member <select>
 *      in the Add Officer and Add Committee forms that narrow the
 *      dropdown to matching names as you type.
 *
 * Member data is passed from PHP via wp_localize_script as spLeadershipData.
 */
(function () {
    'use strict';

    /* Bail if the localized data object wasn't passed by PHP */
    if (typeof spLeadershipData === 'undefined') return;

    var members = spLeadershipData.members || [];

    /* ================================================================
       MEMBER DROPDOWN FILTER
       WHY: The Member <select> can contain 1,000+ entries. Typing
       in the filter input rebuilds the dropdown to show only matching
       names, making it fast to find anyone without scrolling.
       ================================================================ */

    function setupSelectFilter(filterId, selectId) {
        var filterInput = document.getElementById(filterId);
        var select      = document.getElementById(selectId);
        if (!filterInput || !select) return;

        /* Snapshot all <option> elements on first load so we can
           rebuild the list each time the filter text changes */
        var allOptions = [];
        for (var i = 0; i < select.options.length; i++) {
            allOptions.push({
                value:    select.options[i].value,
                text:     select.options[i].text,
                selected: select.options[i].selected
            });
        }

        filterInput.addEventListener('input', function () {
            var term       = this.value.toLowerCase().trim();
            var currentVal = select.value;

            /* Clear and rebuild */
            select.innerHTML = '';

            for (var j = 0; j < allOptions.length; j++) {
                var opt = allOptions[j];

                /* Always keep the blank placeholder option */
                if (!opt.value) {
                    var placeholder = document.createElement('option');
                    placeholder.value = '';
                    placeholder.textContent = opt.text;
                    select.appendChild(placeholder);
                    continue;
                }

                /* Show if matches or no filter active */
                if (!term || opt.text.toLowerCase().indexOf(term) !== -1) {
                    var el = document.createElement('option');
                    el.value       = opt.value;
                    el.textContent = opt.text;
                    if (opt.value === currentVal) el.selected = true;
                    select.appendChild(el);
                }
            }
        });
    }

    setupSelectFilter('sp-filter-officer-member',    'officer_user_id');
    setupSelectFilter('sp-filter-committee-member',  'committee_user_id');

    /* ================================================================
       TOP-OF-PAGE MEMBER SEARCH
       WHY: Lets admins search all active members by name, see any
       existing roles at a glance, and click to assign them as
       officer or committee member — opening the right form with
       the member pre-selected.
       ================================================================ */

    var searchInput = document.getElementById('sp-leadership-search');
    var resultsBox  = document.getElementById('sp-leadership-search-results');

    if (!searchInput || !resultsBox) return;

    searchInput.addEventListener('input', function () {
        var term = this.value.toLowerCase().trim();

        if (!term || term.length < 2) {
            resultsBox.style.display = 'none';
            resultsBox.innerHTML     = '';
            return;
        }

        var matches = [];
        for (var i = 0; i < members.length; i++) {
            if (members[i].name.toLowerCase().indexOf(term) !== -1) {
                matches.push(members[i]);
            }
        }

        if (matches.length === 0) {
            resultsBox.innerHTML     = '<div style="padding:10px 12px;color:#787c82;font-style:italic;">' + spLeadershipData.i18n.noResults + '</div>';
            resultsBox.style.display = 'block';
            return;
        }

        var html = '';
        for (var k = 0; k < matches.length; k++) {
            var m        = matches[k];
            var safeName = m.name.replace(/</g, '&lt;');

            html += '<div style="padding:8px 12px;border-bottom:1px solid #e5e7eb;">';
            html += '<div style="display:flex;justify-content:space-between;align-items:center;">';
            html += '<span style="font-weight:600;color:#1d2327;">' + safeName + '</span>';
            html += '<span style="font-size:12px;white-space:nowrap;">';
            html += '<a href="#" class="sp-assign-officer" data-uid="' + m.id + '" style="color:#2271b1;text-decoration:none;margin-right:10px;">' + spLeadershipData.i18n.addOfficer + '</a>';
            html += '<a href="#" class="sp-assign-committee" data-uid="' + m.id + '" style="color:#2271b1;text-decoration:none;">' + spLeadershipData.i18n.addCommittee + '</a>';
            html += '</span></div>';

            if (m.roles && m.roles.length > 0) {
                html += '<div style="font-size:12px;color:#00a32a;margin-top:2px;">';
                html += m.roles.join(', ').replace(/</g, '&lt;');
                html += '</div>';
            }
            html += '</div>';
        }

        resultsBox.innerHTML     = html;
        resultsBox.style.display = 'block';
    });

    /* Handle clicks on "Add as Officer" / "Add to Committee" links */
    resultsBox.addEventListener('click', function (e) {
        var target = e.target;
        /* Walk up to find the link if the click hit a child element */
        while (target && target !== resultsBox) {
            if (target.classList && (target.classList.contains('sp-assign-officer') || target.classList.contains('sp-assign-committee'))) {
                break;
            }
            target = target.parentNode;
        }
        if (!target || target === resultsBox) return;
        e.preventDefault();

        var uid       = target.getAttribute('data-uid');
        var isOfficer = target.classList.contains('sp-assign-officer');
        var formId    = isOfficer ? 'sp-officer-form'            : 'sp-committee-form';
        var selectId  = isOfficer ? 'officer_user_id'            : 'committee_user_id';
        var filterId  = isOfficer ? 'sp-filter-officer-member'   : 'sp-filter-committee-member';
        var form      = document.getElementById(formId);
        var select    = document.getElementById(selectId);

        if (form && select) {
            /* Clear the dropdown filter so all options are visible,
               then set the selected member */
            var fi = document.getElementById(filterId);
            if (fi) {
                fi.value = '';
                fi.dispatchEvent(new Event('input'));
            }
            select.value = uid;

            /* Open the form and scroll to it */
            form.style.display = 'block';
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        /* Close search results and clear the search box */
        resultsBox.style.display = 'none';
        searchInput.value        = '';
    });

    /* Close results when clicking outside */
    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
            resultsBox.style.display = 'none';
        }
    });

})();
