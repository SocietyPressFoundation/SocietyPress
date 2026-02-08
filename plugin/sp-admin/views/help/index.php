<?php
/**
 * SocietyPress Admin - Help & Documentation View
 *
 * This page provides clear, friendly documentation for volunteers who may not
 * be technically inclined. The goal is to answer common questions and help
 * them feel confident using the system. Remember: our target users are
 * 80-year-old volunteers who might be intimidated by technology.
 *
 * @package SocietyPress
 * @since 0.59
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Determine which help section to show (if deep-linking)
$section = $router->get_param( 'section', '' );
?>

<header class="sp-admin-page-header">
    <h1 class="sp-admin-page-title">Help & Documentation</h1>
</header>

<!-- Quick Start Guide -->
<div class="sp-card" style="margin-bottom: var(--sp-spacing-lg); border-left: 4px solid var(--sp-primary);">
    <div class="sp-card-header">
        <h2 class="sp-card-title">👋 Welcome to SocietyPress Admin</h2>
    </div>
    <div style="padding: var(--sp-spacing-lg);">
        <p style="font-size: 16px; line-height: 1.6; margin-bottom: var(--sp-spacing-md);">
            This is your simplified control panel for managing your genealogical society's website.
            Everything you need is organized in the menu on the left side of your screen.
        </p>
        <p style="color: var(--sp-gray-600);">
            <strong>Don't worry about breaking anything!</strong> Most actions can be undone, and
            we've designed this system to prevent accidental mistakes.
        </p>
    </div>
</div>

<!-- Help Topics in Accordion Style -->
<div class="sp-help-sections">

    <!-- Members -->
    <div class="sp-card" style="margin-bottom: var(--sp-spacing-md);" id="help-members">
        <div class="sp-card-header" style="cursor: pointer;" onclick="this.parentElement.classList.toggle('sp-help-expanded')">
            <h2 class="sp-card-title">👥 Managing Members</h2>
            <span style="float: right; color: var(--sp-gray-400);">Click to expand</span>
        </div>
        <div class="sp-help-content" style="padding: var(--sp-spacing-lg); display: none;">
            <h3 style="margin-bottom: var(--sp-spacing-sm);">Adding a New Member</h3>
            <ol style="margin-bottom: var(--sp-spacing-lg); padding-left: var(--sp-spacing-lg);">
                <li>Click <strong>Members</strong> in the left menu</li>
                <li>Click the <strong>+ Add New Member</strong> button</li>
                <li>Fill in the member's information (at minimum: first name, last name, email)</li>
                <li>Select their membership tier</li>
                <li>Click <strong>Create Member</strong></li>
            </ol>

            <h3 style="margin-bottom: var(--sp-spacing-sm);">Finding a Member</h3>
            <ol style="margin-bottom: var(--sp-spacing-lg); padding-left: var(--sp-spacing-lg);">
                <li>Go to <strong>Members</strong></li>
                <li>Use the search box at the top - you can search by name or email</li>
                <li>Or use the filters to show only Active, Expired, or other status members</li>
            </ol>

            <h3 style="margin-bottom: var(--sp-spacing-sm);">Updating Member Information</h3>
            <ol style="padding-left: var(--sp-spacing-lg);">
                <li>Find the member using search</li>
                <li>Click on their name to open their profile</li>
                <li>Click the <strong>Edit</strong> button</li>
                <li>Make your changes and click <strong>Save Changes</strong></li>
            </ol>
        </div>
    </div>

    <!-- Events -->
    <div class="sp-card" style="margin-bottom: var(--sp-spacing-md);" id="help-events">
        <div class="sp-card-header" style="cursor: pointer;" onclick="this.parentElement.classList.toggle('sp-help-expanded')">
            <h2 class="sp-card-title">📅 Managing Events</h2>
            <span style="float: right; color: var(--sp-gray-400);">Click to expand</span>
        </div>
        <div class="sp-help-content" style="padding: var(--sp-spacing-lg); display: none;">
            <h3 style="margin-bottom: var(--sp-spacing-sm);">Creating an Event</h3>
            <ol style="margin-bottom: var(--sp-spacing-lg); padding-left: var(--sp-spacing-lg);">
                <li>Click <strong>Events</strong> in the left menu</li>
                <li>Click <strong>+ Add New Event</strong></li>
                <li>Fill in the event details: title, date, time, location</li>
                <li>Add a description of what the event is about</li>
                <li>If there's a fee, enter the cost</li>
                <li>Click <strong>Create Event</strong></li>
            </ol>

            <h3 style="margin-bottom: var(--sp-spacing-sm);">Viewing Who's Registered</h3>
            <ol style="padding-left: var(--sp-spacing-lg);">
                <li>Go to <strong>Events</strong></li>
                <li>Click on the event name</li>
                <li>Scroll down to see the list of registered attendees</li>
            </ol>
        </div>
    </div>

    <!-- Transactions -->
    <div class="sp-card" style="margin-bottom: var(--sp-spacing-md);" id="help-transactions">
        <div class="sp-card-header" style="cursor: pointer;" onclick="this.parentElement.classList.toggle('sp-help-expanded')">
            <h2 class="sp-card-title">💰 Recording Payments</h2>
            <span style="float: right; color: var(--sp-gray-400);">Click to expand</span>
        </div>
        <div class="sp-help-content" style="padding: var(--sp-spacing-lg); display: none;">
            <h3 style="margin-bottom: var(--sp-spacing-sm);">Recording a Payment</h3>
            <p style="margin-bottom: var(--sp-spacing-md); color: var(--sp-gray-600);">
                When a member pays their dues (by check, cash, or other offline method), you'll need to record it:
            </p>
            <ol style="margin-bottom: var(--sp-spacing-lg); padding-left: var(--sp-spacing-lg);">
                <li>Click <strong>Transactions</strong> in the left menu</li>
                <li>Click <strong>+ Record Payment</strong></li>
                <li>Select the member from the dropdown</li>
                <li>Enter the amount paid</li>
                <li>Choose the payment method (Check, Cash, etc.)</li>
                <li>If it's for membership dues, check the box to update their membership expiration</li>
                <li>Click <strong>Record Payment</strong></li>
            </ol>

            <h3 style="margin-bottom: var(--sp-spacing-sm);">Viewing Transaction History</h3>
            <ol style="padding-left: var(--sp-spacing-lg);">
                <li>Go to <strong>Transactions</strong></li>
                <li>Use the year dropdown to see different years</li>
                <li>The summary at the top shows totals for invoiced, paid, and outstanding amounts</li>
            </ol>
        </div>
    </div>

    <!-- Groups -->
    <div class="sp-card" style="margin-bottom: var(--sp-spacing-md);" id="help-groups">
        <div class="sp-card-header" style="cursor: pointer;" onclick="this.parentElement.classList.toggle('sp-help-expanded')">
            <h2 class="sp-card-title">👥 Managing Groups</h2>
            <span style="float: right; color: var(--sp-gray-400);">Click to expand</span>
        </div>
        <div class="sp-help-content" style="padding: var(--sp-spacing-lg); display: none;">
            <p style="margin-bottom: var(--sp-spacing-md); color: var(--sp-gray-600);">
                Groups help you organize members into committees, interest groups, or email lists.
            </p>

            <h3 style="margin-bottom: var(--sp-spacing-sm);">Creating a Group</h3>
            <ol style="margin-bottom: var(--sp-spacing-lg); padding-left: var(--sp-spacing-lg);">
                <li>Click <strong>Groups</strong> in the left menu</li>
                <li>Click <strong>+ Add New Group</strong></li>
                <li>Give the group a name and description</li>
                <li>Check "Enable blast email" if you want to send emails to this group</li>
                <li>Click <strong>Create Group</strong></li>
            </ol>

            <h3 style="margin-bottom: var(--sp-spacing-sm);">Adding Members to a Group</h3>
            <ol style="padding-left: var(--sp-spacing-lg);">
                <li>Go to <strong>Groups</strong> and click on the group name</li>
                <li>Use the dropdown to select a member</li>
                <li>Click <strong>Add</strong></li>
            </ol>
        </div>
    </div>

    <!-- Leadership -->
    <div class="sp-card" style="margin-bottom: var(--sp-spacing-md);" id="help-leadership">
        <div class="sp-card-header" style="cursor: pointer;" onclick="this.parentElement.classList.toggle('sp-help-expanded')">
            <h2 class="sp-card-title">🏛️ Managing Leadership Positions</h2>
            <span style="float: right; color: var(--sp-gray-400);">Click to expand</span>
        </div>
        <div class="sp-help-content" style="padding: var(--sp-spacing-lg); display: none;">
            <p style="margin-bottom: var(--sp-spacing-md); color: var(--sp-gray-600);">
                Track your society's officers, board members, and committee chairs.
            </p>

            <h3 style="margin-bottom: var(--sp-spacing-sm);">Adding a Position</h3>
            <ol style="margin-bottom: var(--sp-spacing-lg); padding-left: var(--sp-spacing-lg);">
                <li>Click <strong>Leadership</strong> in the left menu</li>
                <li>Click <strong>+ Add Position</strong></li>
                <li>Enter the position title (e.g., "President", "Newsletter Editor")</li>
                <li>Check the boxes for Board Member or Officer as appropriate</li>
                <li>Click <strong>Create Position</strong></li>
            </ol>

            <h3 style="margin-bottom: var(--sp-spacing-sm);">Assigning Someone to a Position</h3>
            <ol style="padding-left: var(--sp-spacing-lg);">
                <li>Click on the position in the Leadership list</li>
                <li>Click <strong>Edit</strong></li>
                <li>In the "Current Holder" dropdown, select the member</li>
                <li>Click <strong>Save Changes</strong></li>
            </ol>
            <p style="margin-top: var(--sp-spacing-md); color: var(--sp-gray-600);">
                <em>Note: When you change who holds a position, the system automatically records the end date for the previous holder.</em>
            </p>
        </div>
    </div>

    <!-- Newsletters -->
    <div class="sp-card" style="margin-bottom: var(--sp-spacing-md);" id="help-newsletters">
        <div class="sp-card-header" style="cursor: pointer;" onclick="this.parentElement.classList.toggle('sp-help-expanded')">
            <h2 class="sp-card-title">📰 Managing Newsletters</h2>
            <span style="float: right; color: var(--sp-gray-400);">Click to expand</span>
        </div>
        <div class="sp-help-content" style="padding: var(--sp-spacing-lg); display: none;">
            <h3 style="margin-bottom: var(--sp-spacing-sm);">Uploading a Newsletter</h3>
            <ol style="margin-bottom: var(--sp-spacing-lg); padding-left: var(--sp-spacing-lg);">
                <li>Click <strong>Newsletters</strong> in the left menu</li>
                <li>In the upload form at the top, enter the filename</li>
                <li>Use the format: <code>YYYY_MM_Month_Newsletter.pdf</code> (e.g., 2026_01_January_Newsletter.pdf)</li>
                <li>Click <strong>Choose File</strong> and select your PDF</li>
                <li>Click <strong>Upload</strong></li>
            </ol>
            <p style="color: var(--sp-gray-600);">
                <em>Newsletters are automatically organized by year based on the filename.</em>
            </p>
        </div>
    </div>

    <!-- Pages -->
    <div class="sp-card" style="margin-bottom: var(--sp-spacing-md);" id="help-pages">
        <div class="sp-card-header" style="cursor: pointer;" onclick="this.parentElement.classList.toggle('sp-help-expanded')">
            <h2 class="sp-card-title">📄 Editing Website Pages</h2>
            <span style="float: right; color: var(--sp-gray-400);">Click to expand</span>
        </div>
        <div class="sp-help-content" style="padding: var(--sp-spacing-lg); display: none;">
            <h3 style="margin-bottom: var(--sp-spacing-sm);">Editing a Page</h3>
            <ol style="margin-bottom: var(--sp-spacing-lg); padding-left: var(--sp-spacing-lg);">
                <li>Click <strong>Pages</strong> in the left menu</li>
                <li>Find the page you want to edit</li>
                <li>Click on the page title</li>
                <li>Click <strong>Edit Page</strong></li>
                <li>Make your changes using the text editor</li>
                <li>Click <strong>Save Changes</strong></li>
            </ol>

            <h3 style="margin-bottom: var(--sp-spacing-sm);">Understanding Page Status</h3>
            <ul style="padding-left: var(--sp-spacing-lg);">
                <li><strong>Published</strong> - The page is live and visible to everyone</li>
                <li><strong>Draft</strong> - The page is saved but not visible to the public</li>
            </ul>
        </div>
    </div>

</div>

<!-- Need More Help? -->
<div class="sp-card" style="margin-top: var(--sp-spacing-xl); background: var(--sp-gray-50);">
    <div class="sp-card-header">
        <h2 class="sp-card-title">Need More Help?</h2>
    </div>
    <div style="padding: var(--sp-spacing-lg);">
        <p style="margin-bottom: var(--sp-spacing-md);">
            If you can't find what you're looking for, here are some options:
        </p>
        <ul style="padding-left: var(--sp-spacing-lg); line-height: 2;">
            <li>Contact your society's website administrator</li>
            <li>Visit the SocietyPress documentation at <a href="https://getsocietypress.org/docs" target="_blank">getsocietypress.org/docs</a></li>
            <li>Email support at <a href="mailto:support@getsocietypress.org">support@getsocietypress.org</a></li>
        </ul>
    </div>
</div>

<!-- JavaScript for accordion behavior -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers to expand/collapse help sections
    document.querySelectorAll('.sp-card .sp-card-header').forEach(function(header) {
        const content = header.parentElement.querySelector('.sp-help-content');
        if (content) {
            header.addEventListener('click', function() {
                const isVisible = content.style.display !== 'none';
                content.style.display = isVisible ? 'none' : 'block';
                const hint = header.querySelector('span');
                if (hint) {
                    hint.textContent = isVisible ? 'Click to expand' : 'Click to collapse';
                }
            });
        }
    });

    // If there's a hash in the URL, expand that section
    if (window.location.hash) {
        const section = document.querySelector(window.location.hash);
        if (section) {
            const content = section.querySelector('.sp-help-content');
            if (content) {
                content.style.display = 'block';
                section.scrollIntoView({ behavior: 'smooth' });
            }
        }
    }
});
</script>

<style>
/* Additional styles for the help page */
.sp-help-content h3 {
    color: var(--sp-gray-700);
    font-size: 15px;
    margin-top: var(--sp-spacing-lg);
}
.sp-help-content h3:first-child {
    margin-top: 0;
}
.sp-help-content ol, .sp-help-content ul {
    line-height: 1.8;
}
.sp-help-content code {
    background: var(--sp-gray-100);
    padding: 2px 6px;
    border-radius: 3px;
    font-family: monospace;
}
</style>
