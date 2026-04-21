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
 * 5. CTA — still have questions?
 *
 * @package getsocietypress
 * @version 0.02d
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
                        PHP 8.1+, WordPress 6.0+, and MySQL 8.0+ (or MariaDB 10.6+). Any
                        current shared-hosting plan that supports WordPress will work.
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
                        <code>sp-installer.php</code> to a fresh WordPress site, visit
                        <code>yoursite.com/sp-installer.php</code>, and it handles the
                        rest. If you'd rather do it by hand, download the .zip from the
                        <a href="<?php echo esc_url( home_url( '/download/' ) ); ?>">download page</a>,
                        upload the plugin and each theme folder through your WordPress
                        admin, activate the plugin first, then your chosen theme. Either
                        way you'll land on the 3-step setup wizard.
                    </p>
                </div>
            </details>

            <details class="faq-item" open>
                <summary class="faq-item__question">
                    Can I use SocietyPress with my existing WordPress theme?
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
                        birth) are encrypted at rest using XChaCha20-Poly1305 via
                        libsodium &mdash; the same modern cipher used by Signal and
                        WireGuard. Every admin page and AJAX endpoint verifies a nonce
                        and capability check. Email addresses on public pages are
                        obfuscated against scraping bots.
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
                        Yes. SocietyPress plugs into WordPress's built-in personal-data
                        export and erasure tools. Members, event registrations, library
                        loans, newsletter access, and volunteer records all flow through
                        the standard Tools &gt; Export Personal Data and Tools &gt;
                        Erase Personal Data screens. Donations coverage is on the
                        roadmap.
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
                        Yes. SocietyPress ships with 8 role templates (President,
                        Membership Chair, Events Coordinator, Librarian, Editor, etc.)
                        across 10 access areas. You can assign a template with one
                        click, or toggle individual permissions per person. The
                        Membership Chair can manage members without seeing the
                        donation ledger; the Librarian can run the catalog without
                        touching events.
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
                        WordPress files and database &mdash; SocietyPress doesn't
                        replace that, but it does make sure nothing important is
                        trapped in a proprietary format.
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
                        Email deliverability is a WordPress-level concern, not
                        SocietyPress-specific. For reliable delivery, install a free
                        SMTP plugin (WP Mail SMTP, FluentSMTP) and point it at a
                        transactional email service &mdash; Amazon SES, Postmark, or
                        Mailgun all have free tiers that comfortably cover a society's
                        volume. SocietyPress sends through
                        <code>wp_mail()</code>, so any SMTP plugin works.
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
                        standard 73-field ENS export format directly), upload it
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
                        No. SocietyPress will always be free. There are no plans for premium tiers,
                        paid add-ons, or "Pro" versions. The project is sustained by voluntary
                        donations from societies that find it useful.
                    </p>
                </div>
            </details>

        </div>

    </div>
</section>


<!-- ==========================================================================
     5. STILL HAVE QUESTIONS?
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
