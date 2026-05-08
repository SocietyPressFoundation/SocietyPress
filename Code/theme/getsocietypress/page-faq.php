<?php
/**
 * FAQ Page Template (page-faq.php)
 *
 * Collapsible Q&A sections grouped by category. Uses vanilla JS
 * accordion behavior — no jQuery, no libraries.
 *
 * Sections:
 * 1. Hero — "Frequently Asked Questions"
 * 2. General — what is it, who is it for, etc.
 * 3. Technical — installation, hosting, updates
 * 4. Licensing — GPL, cost, commercial use
 * 5. Features — module-level "can we..." questions
 * 6. CTA — still have questions?
 *
 * @package getsocietypress
 * @version 0.03d
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<!-- ==========================================================================
     1. FAQ HERO
     ========================================================================== -->
<section class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <h1 class="page-hero__title">Frequently Asked Questions</h1>
            <p class="page-hero__subtitle">
                Answers to common questions about SocietyPress.
            </p>
        </div>
    </div>
</section>


<!-- ==========================================================================
     2. GENERAL QUESTIONS
     ========================================================================== -->
<section class="faq-section section">
    <div class="container container--narrow">

        <h2 class="faq-section__heading">General</h2>

        <div class="faq-group">

            <details class="faq-item" open>
                <summary class="faq-item__question">
                    What is SocietyPress?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        SocietyPress is a free, open-source platform built specifically for genealogical
                        and historical societies. It provides membership management, event publishing,
                        surname databases, a page builder, and a visual design system — all in one package.
                    </p>
                </div>
            </details>

            <details class="faq-item" open>
                <summary class="faq-item__question">
                    Who is SocietyPress for?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        It's designed for the administrators of local genealogical and historical societies —
                        the volunteers who manage the membership roster, organize monthly meetings, and
                        maintain the society's website. The goal is to make their job easier with tools
                        that actually fit how societies work.
                    </p>
                </div>
            </details>

            <details class="faq-item" open>
                <summary class="faq-item__question">
                    How much does it cost?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Nothing. SocietyPress is completely free. There are no paid tiers, no premium
                        features, no subscriptions. The only cost is your own web hosting, which
                        typically runs $5–15/month on a standard shared plan.
                    </p>
                </div>
            </details>

            <details class="faq-item" open>
                <summary class="faq-item__question">
                    Do I need to know how to code?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        No. SocietyPress is designed for non-technical users. Everything from site
                        design to member management happens through point-and-click admin screens.
                        If you can use email and a web browser, you can run SocietyPress.
                    </p>
                </div>
            </details>

            <details class="faq-item" open>
                <summary class="faq-item__question">
                    Can I use SocietyPress for a historical society (not genealogical)?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Absolutely. While the genealogy-specific tools (surname databases, GEDCOM
                        import) are tailored for genealogical societies, the core features —
                        membership management, event publishing, page builder, design system —
                        work for any small membership organization.
                    </p>
                </div>
            </details>

        </div>

    </div>
</section>


<!-- ==========================================================================
     3. TECHNICAL QUESTIONS
     ========================================================================== -->
<section class="faq-section faq-section--alt section">
    <div class="container container--narrow">

        <h2 class="faq-section__heading">Technical</h2>

        <div class="faq-group">

            <details class="faq-item" open>
                <summary class="faq-item__question">
                    What are the server requirements?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        PHP 8.1+, <a href="https://wordpress.org" target="_blank" rel="noopener">WordPress</a> 6.0+, and MySQL 8.0+ (or MariaDB 10.6+). Any
                        current shared-hosting plan that supports <a href="https://wordpress.org" target="_blank" rel="noopener">WordPress</a> will work.
                        See the full
                        <a href="<?php echo esc_url( home_url( '/docs/requirements/' ) ); ?>">requirements page</a>
                        for details.
                    </p>
                </div>
            </details>

            <details class="faq-item" open>
                <summary class="faq-item__question">
                    How do I install SocietyPress?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        The easiest path is the one-click installer: upload
                        <code>sp-installer.php</code> to a fresh <a href="https://wordpress.org" target="_blank" rel="noopener">WordPress</a> site, visit
                        <code>yoursite.com/sp-installer.php</code>, and it handles the
                        rest. If you'd rather do it by hand, download the .zip from the
                        <a href="<?php echo esc_url( home_url( '/download/' ) ); ?>">download page</a>,
                        upload the plugin and each theme folder through your <a href="https://wordpress.org" target="_blank" rel="noopener">WordPress</a>
                        admin, activate the plugin first, then your chosen theme. Either
                        way you'll land on the 3-step setup wizard.
                    </p>
                </div>
            </details>

            <details class="faq-item" open>
                <summary class="faq-item__question">
                    Can I use SocietyPress with my existing <a href="https://wordpress.org" target="_blank" rel="noopener">WordPress</a> theme?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        The SocietyPress plugin and theme are designed to work together. The theme
                        renders pages built with the page builder and responds to the design system
                        settings. Using a different theme means you'd lose those integrations. We
                        recommend using the included theme for the best experience.
                    </p>
                </div>
            </details>

            <details class="faq-item" open>
                <summary class="faq-item__question">
                    How do I migrate my existing member data?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        SocietyPress includes a CSV import tool with smart duplicate detection. Export
                        your existing member list as a CSV file, map the columns to SocietyPress fields,
                        and import. The system automatically detects organizational members based on
                        name patterns.
                    </p>
                </div>
            </details>

            <details class="faq-item" open>
                <summary class="faq-item__question">
                    Will SocietyPress slow down my site?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        No. The entire download is about 8 MB and there are no external
                        API calls, CDN dependencies, or bloated JavaScript libraries.
                        The theme uses vanilla JS &mdash; no jQuery. It's built to be
                        fast on cheap shared hosting.
                    </p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-item__question">
                    How are payments handled?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Stripe and PayPal are both supported out of the box for dues,
                        event registrations, and donations. You connect your own Stripe
                        and/or PayPal accounts from the settings panel &mdash; no
                        middleman takes a cut, and no payment data ever touches the
                        SocietyPress project. Sandbox mode is supported for testing
                        before you go live.
                    </p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-item__question">
                    How is sensitive member data protected?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Sensitive fields (phone numbers, street addresses, dates of
                        birth) are stored encrypted using the same modern security
                        standard used by Signal and WhatsApp &mdash; even someone who
                        gained access to your database wouldn&rsquo;t be able to read
                        them. Every admin screen verifies your identity and permissions
                        before showing or saving anything. Email addresses on public
                        pages are obfuscated to slow down scraping bots.
                    </p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-item__question">
                    Does SocietyPress handle GDPR export and erasure?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Yes. SocietyPress plugs into <a href="https://wordpress.org" target="_blank" rel="noopener">WordPress</a>'s built-in personal-data
                        export and erasure tools. Members, event registrations, library
                        loans, newsletter access, volunteer records, and donations all
                        flow through the standard Tools &gt; Export Personal Data and
                        Tools &gt; Erase Personal Data screens. Donations are
                        <em>pseudonymized</em> on erasure rather than deleted &mdash;
                        the dollar amount and date stay on the books for IRS
                        recordkeeping; the name and contact information are wiped.
                        See the
                        <a href="<?php echo esc_url( home_url( '/docs/modules/?guide=privacy-gdpr' ) ); ?>">Privacy &amp; GDPR guide</a>
                        for the full walkthrough.
                    </p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-item__question">
                    Can I give different volunteers different levels of access?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Yes. SocietyPress ships with 8 role templates (Webmaster,
                        Membership Manager, Treasurer, Event Coordinator, Librarian,
                        Communications Director, Records Manager, Content Editor)
                        across 10 access areas. You can assign a template with one
                        click, or toggle individual permissions per person. The
                        Treasurer can record donations without seeing the membership
                        roster; the Librarian can run the catalog without touching
                        events. See the
                        <a href="<?php echo esc_url( home_url( '/docs/modules/?guide=user-access' ) ); ?>">User Access &amp; Roles guide</a>
                        for the full breakdown.
                    </p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-item__question">
                    Can I back up and restore the entire site?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Yes. Settings &gt; Export &amp; Backup produces a single .zip
                        containing a SQL dump of every SocietyPress table, a decrypted
                        member export, and a README. Every byte your society put in
                        comes back out. Your host's backup system handles the
                        <a href="https://wordpress.org" target="_blank" rel="noopener">WordPress</a> files and database &mdash; SocietyPress doesn't
                        replace that, but it does make sure nothing important is
                        trapped in a proprietary format. The
                        <a href="<?php echo esc_url( home_url( '/docs/modules/?guide=backup-restore' ) ); ?>">Backup &amp; Restore guide</a>
                        walks through the full export and restore flow.
                    </p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-item__question">
                    Will renewal and event emails actually land in inboxes?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Email deliverability is a <a href="https://wordpress.org" target="_blank" rel="noopener">WordPress</a>-level concern, not
                        SocietyPress-specific. For reliable delivery, install a free
                        SMTP plugin (WP Mail SMTP, FluentSMTP) and point it at a
                        transactional email service &mdash; Amazon SES, Postmark, or
                        Mailgun all have free tiers that comfortably cover a society's
                        volume. SocietyPress sends through
                        <code>wp_mail()</code>, so any SMTP plugin works. The
                        <a href="<?php echo esc_url( home_url( '/docs/modules/?guide=email-setup' ) ); ?>">Email Setup guide</a>
                        has step-by-step recipes (15 minutes including coffee).
                    </p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-item__question">
                    I'm moving from EasyNetSites (ENS). How does migration work?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        SocietyPress was designed with ENS societies in mind. Export
                        your members as a CSV from ENS (SocietyPress supports the
                        standard 86-column ENS export format directly), upload it
                        through Members &gt; Import, and the importer maps fields
                        automatically, detects organizational members, and handles
                        duplicates. Events, surname research, and library catalogs
                        all have CSV importers too.
                        See the <a href="<?php echo esc_url( home_url( '/docs/ens-migration/' ) ); ?>">ENS migration guide</a>
                        for the full walkthrough.
                    </p>
                </div>
            </details>

        </div>

    </div>
</section>


<!-- ==========================================================================
     4. LICENSING QUESTIONS
     ========================================================================== -->
<section class="faq-section section">
    <div class="container container--narrow">

        <h2 class="faq-section__heading">Licensing</h2>

        <div class="faq-group">

            <details class="faq-item" open>
                <summary class="faq-item__question">
                    What license is SocietyPress released under?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        The GNU General Public License v2 (GPL v2). This means you can use, modify,
                        and distribute SocietyPress freely. The full license text is included in the
                        download.
                    </p>
                </div>
            </details>

            <details class="faq-item" open>
                <summary class="faq-item__question">
                    Can I modify SocietyPress for my society's needs?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Yes. The GPL license explicitly allows modification. If you have a developer
                        who wants to customize SocietyPress for your society, they're free to do so.
                        The code is yours to inspect and customize as needed.
                    </p>
                </div>
            </details>

            <details class="faq-item" open>
                <summary class="faq-item__question">
                    Will there ever be a paid version?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        For the foreseeable future, no. SocietyPress is free under
                        the GPL v2 and there are no plans to change that. Every
                        release is the full release.
                    </p>
                </div>
            </details>

        </div>

    </div>
</section>


<!-- ==========================================================================
     5. COMMON CONCERNS — direct answers to the doubts boards raise
     ========================================================================== -->
<section class="faq-section faq-section--alt section">
    <div class="container container--narrow">

        <h2 class="faq-section__heading">Common Concerns</h2>

        <div class="faq-group">

            <details class="faq-item">
                <summary class="faq-item__question">
                    SocietyPress is maintained by one person. What happens if something happens to him?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Today, SocietyPress is independently developed. The
                        license is GPL, the code is public, and the long-term
                        plan is to put the project under nonprofit
                        stewardship &mdash; the GitHub organization name
                        (<a href="https://github.com/SocietyPressFoundation" target="_blank" rel="noopener">SocietyPressFoundation</a>)
                        was chosen with that in mind.
                    </p>
                    <p>
                        The protection against bus-factor risk is also baked
                        into the licensing model. Every line of code is public
                        on
                        <a href="https://github.com/SocietyPressFoundation/SocietyPress">GitHub</a>,
                        every release ships the full source, and any developer
                        anywhere can fork the project and continue it. You
                        also have a copy of every release sitting on your own
                        server, which means a working SocietyPress site keeps
                        working whether the project continues or not.
                    </p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-item__question">
                    There's no support contract or phone number. What do we do when something breaks?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Honest answer: there is no paid SLA today. Support
                        happens through the public
                        <a href="/community/">community forums</a>, the
                        <a href="/bug-reports/">bug-report channel</a>, and
                        direct conversation on GitHub. A managed-hosting
                        option with formal support is on the
                        <a href="/roadmap/">roadmap</a> for societies that
                        want a bill instead of a fork. In the meantime, the
                        same GPL license that lets you read the code lets
                        any <a href="https://wordpress.org" target="_blank" rel="noopener">WordPress</a> developer in the world fix a bug for
                        you &mdash; you are never locked in to one vendor's
                        support queue.
                    </p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-item__question">
                    We don't see other societies running SocietyPress yet. Are we taking a risk by being early?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Yes &mdash; and that is the trade. Early adopters get
                        attention from the maintainer that later adopters
                        will not. They get input into priorities. Their bug
                        reports get fixed in days, not quarters. The
                        <a href="https://demo.getsocietypress.org">live demo</a>
                        runs every feature against real settings every day,
                        so what you see is what you get. The risk you are
                        actually weighing is whether the platform you switch
                        to is still going to exist in five years &mdash; and
                        the answer to that question is in the GPL license,
                        not in the customer count.
                    </p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-item__question">
                    Why is SocietyPress still pre-1.0? Is it not ready?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        The version number is conservative on purpose. Every
                        release ships the full feature set with no
                        upgrade-locked tier &mdash; the 1.0 milestone is an
                        internal threshold for "first society in production"
                        rather than a feature gate. The
                        <a href="/changelog/">changelog</a> shows the actual
                        rate of progress, and the
                        <a href="https://demo.getsocietypress.org">demo</a>
                        shows the actual surface area. Don't read 1.0 as a
                        ship date &mdash; read the changelog and the demo.
                    </p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-item__question">
                    SocietyPress isn't a 501(c)(3) yet. Is that a governance risk?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Today the
                        <a href="https://github.com/SocietyPressFoundation">SocietyPressFoundation</a>
                        GitHub organization is owned by the maintainer
                        personally and acts as a holding pen. The explicit
                        plan is to incorporate a 501(c)(3) nonprofit and
                        transfer the project to it &mdash; the URL,
                        repository, and trademarks were all chosen to make
                        that handoff possible without forcing societies to
                        re-migrate or re-link anything. In the meantime, the
                        GPL license means the code is already public-good
                        regardless of who holds the org.
                    </p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-item__question">
                    We don't have a webmaster. Doesn't running <a href="https://wordpress.org" target="_blank" rel="noopener">WordPress</a> mean hosting fees, updates, and security patches?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Yes &mdash; self-hosting is a real responsibility, and
                        we won't pretend otherwise. Most cPanel hosts run $5
                        to $10 a month and handle <a href="https://wordpress.org" target="_blank" rel="noopener">WordPress</a> core updates
                        automatically. SocietyPress itself ships a one-click
                        installer and an in-app update checker so plugin and
                        theme updates are a single button click. For
                        societies who want none of that responsibility, the
                        <a href="/roadmap/">roadmap</a> includes a managed
                        hosting option where we handle every layer for a
                        flat annual fee. The honest framing is: a few
                        dollars a month for full control, or a higher fee
                        for someone-else's-problem &mdash; both are
                        legitimate choices.
                    </p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-item__question">
                    Aren't <a href="https://wordpress.org" target="_blank" rel="noopener">WordPress</a> sites the ones that always get hacked? Isn't open source less secure?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Almost every <a href="https://wordpress.org" target="_blank" rel="noopener">WordPress</a> compromise traces back to one
                        of three things: an outdated installation, a sketchy
                        third-party plugin, or a weak admin password.
                        SocietyPress addresses all three: the in-app update
                        checker keeps the plugin and theme current, the
                        platform is built to ship as a single audited
                        package rather than a kitchen-sink of unrelated
                        plugins, and member data is encrypted at rest with
                        XChaCha20-Poly1305 via libsodium. Open source
                        actually makes security <em>better</em>, not worse:
                        anyone can read every line, and anyone reporting a
                        vulnerability follows the public disclosure policy
                        at <a href="/security-policy/">/security-policy/</a>.
                    </p>
                </div>
            </details>

        </div>

    </div>
</section>


<!-- ==========================================================================
     6. FEATURE QUESTIONS — what can the software actually do
     ========================================================================== -->
<section class="faq-section section">
    <div class="container container--narrow">

        <h2 class="faq-section__heading">Features</h2>

        <div class="faq-group">

            <details class="faq-item">
                <summary class="faq-item__question">
                    Can I run online board elections and bylaw votes?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Yes. The Voting module supports board elections, bylaw
                        amendments, and member surveys, with tier-based eligibility
                        (e.g. only full members vote on bylaws; subscribers don't),
                        configurable voting windows, and a results page the board
                        can release publicly or keep admin-only. One member, one
                        ballot &mdash; duplicates are blocked by default.
                    </p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-item__question">
                    Can we recognize members for First Families, Pioneer Settlers, etc.?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Yes. The Lineage Programs module is built for exactly this.
                        Define any number of programs (First Families of [your
                        county], Mayflower Descendants, Civil War Veterans
                        Descendants &mdash; whatever your society recognizes), let
                        members submit applications through a public form, review
                        them in an admin queue, and approved members appear on a
                        public roster with auto-numbered printable certificates.
                        Optional Stripe application fee.
                    </p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-item__question">
                    Can members submit research help requests to each other?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Yes. The Help Requests module is a public Q&amp;A archive
                        modeled on the duty-librarian system most societies already
                        run informally. Anyone can submit a question (math captcha
                        + email verification keep spam out); members respond with
                        time-tracked answers that automatically log to the
                        volunteer-hours ledger. Questions can be marked resolved,
                        endorsed, or escalated to paid Research Services for cases
                        that need many hours of focused work.
                    </p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-item__question">
                    Can we sell publications, merchandise, or back issues?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Yes. The Store module ships with a real shopping cart,
                        inline Stripe and PayPal checkout (Apple Pay / Google Pay /
                        Link / Venmo all work), inventory tracking, shipping
                        addresses, and refund tools. Products can be physical
                        (mugs, polos, society publications) or digital (PDFs).
                    </p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-item__question">
                    Can members upload photos from society events?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Yes. The Gallery module supports admin-curated albums and
                        an optional member-submission flow (the "Picture Wall").
                        Submissions land in a moderation queue so the webmaster
                        approves photos before they go public.
                    </p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-item__question">
                    Can we publish searchable cemetery, census, or church record databases?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Yes. The Records module lets you build any number of
                        record collections, each with its own custom fields
                        (cemetery transcriptions, census extracts, church
                        registers, obituary indexes &mdash; whatever your
                        society holds). Records can be public or members-only
                        per collection, with full-text search and per-field
                        filtering. CSV import handles bulk loads.
                    </p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-item__question">
                    Can the board see how the society is doing without exporting reports?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Yes. The Insights page collects engagement and use numbers
                        across every active module &mdash; active members,
                        events held, donations raised, volunteer hours logged,
                        records added &mdash; on a single admin/board-only screen.
                        Pick a time window (last 30 / 90 / 365 days, this fiscal
                        year, last fiscal year) and every number updates at once.
                        Each card has a sparkline showing the trend.
                    </p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-item__question">
                    Can we send mass emails to members through the website?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Yes. The Blast Email module sends to all members or to
                        specific groups (membership tier, committee, custom group),
                        with delivery tracking, opt-out management, and template
                        variables for personalization. For volume past a few
                        hundred recipients we recommend pairing it with an SMTP
                        plugin pointed at a transactional service (Amazon SES,
                        Postmark, Mailgun) so deliverability stays clean.
                    </p>
                </div>
            </details>

        </div>

    </div>
</section>


<!-- ==========================================================================
     6. STILL HAVE QUESTIONS?
     ========================================================================== -->
<section class="faq-cta">
    <div class="container">
        <div class="faq-cta__content">
            <h2>Still have questions?</h2>
            <p>
                Reach out through the community or check the documentation for
                more detailed information.
            </p>
            <div class="faq-cta__actions">
                <a href="<?php echo esc_url( home_url( '/community/' ) ); ?>" class="btn btn-primary btn-lg">
                    Join the Community
                </a>
                <a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>" class="btn btn-secondary btn-lg">
                    Read the Docs
                </a>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
