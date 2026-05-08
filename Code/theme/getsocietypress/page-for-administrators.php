<?php
/**
 * One-Pager: For Society Administrators (page-for-administrators.php)
 *
 * Tight, print-optimized page targeting the person who runs the society's
 * website and membership day-to-day — usually a volunteer, usually
 * non-technical, always overworked.
 *
 * Print CSS in style.css lets the browser "Print to PDF" produce a clean
 * single-page handout. This is the shippable version of the "PDF info
 * sheets" item on the marketing to-do.
 *
 * @package getsocietypress
 * @version 0.04d
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="onepager">
    <div class="container container--narrow">

        <header class="onepager__header">
            <div class="onepager__role-label">For the society administrator</div>
            <h1 class="onepager__title">Stop juggling plugins. Start running your society.</h1>
            <p class="onepager__lede">
                SocietyPress replaces the spreadsheets, generic event plugins,
                awkward directory tools, and makeshift newsletter archive that
                you've been stitching together &mdash; with one integrated
                platform designed specifically for the work you're actually doing.
            </p>
        </header>

        <section class="onepager__section">
            <h2>What you're probably doing today</h2>
            <ul class="onepager__list">
                <li>Maintaining the member roster in a spreadsheet, emailing renewal reminders by hand.</li>
                <li>Publishing monthly meetings with a calendar plugin that doesn't really understand &ldquo;meeting.&rdquo;</li>
                <li>Uploading newsletter PDFs somewhere, then hoping members can find them.</li>
                <li>Chasing volunteer signups through three different email threads.</li>
                <li>Answering the same &ldquo;when does my membership expire?&rdquo; question eleven times a month.</li>
            </ul>
        </section>

        <section class="onepager__section">
            <h2>What SocietyPress does for you</h2>

            <div class="onepager__features">

                <div class="onepager__feature">
                    <h3>Member management that just works</h3>
                    <p>Individual and organizational members in one database. Automatic renewal reminders. A self-service member portal where members update their own info. Import from a CSV &mdash; including the standard 86-column ENS export &mdash; in minutes.</p>
                </div>

                <div class="onepager__feature">
                    <h3>Events built for societies</h3>
                    <p>Monthly meetings, workshops, cemetery walks, conferences. Online registration with capacity limits and waitlists. Stripe and PayPal for ticketed events. iCal export for members' calendars.</p>
                </div>

                <div class="onepager__feature">
                    <h3>Your newsletter archive, searchable</h3>
                    <p>Upload PDFs. Automatic cover thumbnails. Member-only access control. Your whole back catalog, always available, no broken links.</p>
                </div>

                <div class="onepager__feature">
                    <h3>A page builder non-developers can use</h3>
                    <p>21 drag-and-drop widgets (text, images, events lists, member directory, library catalog, volunteer signups). No codes, no templates, no technical knowledge required. Live preview before publishing.</p>
                </div>

                <div class="onepager__feature">
                    <h3>Volunteer and committee management</h3>
                    <p>Post opportunities with shift signups. Log volunteer hours. Track officer positions and terms. Run elections with secure online ballots.</p>
                </div>

                <div class="onepager__feature">
                    <h3>Your data, always</h3>
                    <p>One-click full-site export. Every byte of your society's data in one zip. No vendor lock-in, ever. If the project ever stops, your data keeps working.</p>
                </div>

            </div>
        </section>

        <section class="onepager__section onepager__section--highlighted">
            <h2>The cost</h2>
            <p>
                The software is free forever under the GPL v2 license. Your
                only cost is web hosting &mdash; typically $5&ndash;15 a month
                for a society-sized site. No monthly SaaS fee. No per-member
                charges. No paywall features.
            </p>
        </section>

        <section class="onepager__section">
            <h2>Getting started takes about 30 minutes</h2>
            <ol class="onepager__steps">
                <li>Visit <strong>getsocietypress.org/download/</strong> and grab the installer.</li>
                <li>Upload <code>sp-installer.php</code> to a fresh <a href="https://wordpress.org" target="_blank" rel="noopener">WordPress</a> hosting account and visit it in your browser.</li>
                <li>Answer the 3-step setup wizard (society info, membership tiers, colors).</li>
                <li>Import your members via CSV.</li>
            </ol>
        </section>

        <footer class="onepager__footer">
            <p>
                <strong>See it in action:</strong>
                <a href="https://demo.getsocietypress.org">demo.getsocietypress.org</a>
                (fully functional, 1,100+ sample members, 19,000+ library items)
            </p>
            <p>
                <strong>Download and docs:</strong>
                <a href="https://getsocietypress.org/">getsocietypress.org</a>
            </p>
            <p class="onepager__footer-meta">
                Print this page for your board or fellow volunteers &mdash;
                it's formatted to print cleanly as a single sheet.
            </p>
        </footer>

    </div>
</section>

<?php get_footer(); ?>
