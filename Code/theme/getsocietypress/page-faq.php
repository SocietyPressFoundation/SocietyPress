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
                        PHP 7.4+, WordPress 6.0+, and MySQL 5.7+ (or MariaDB 10.3+). Any standard
                        shared hosting plan that supports WordPress will work. See the full
                        <a href="<?php echo esc_url( home_url( '/requirements/' ) ); ?>">requirements page</a>
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
                        Download the .zip file from the <a href="<?php echo esc_url( home_url( '/download/' ) ); ?>">download page</a>,
                        then upload the plugin and theme through your WordPress admin panel. Activate the plugin
                        first, then the theme. The database tables are created automatically. The whole process
                        takes under 15 minutes.
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
                        No. The entire plugin and theme combined are under 5 MB. There are no external
                        API calls, no CDN dependencies, and no bloated JavaScript libraries. The theme
                        uses vanilla JS — no jQuery. It's built to be fast on cheap shared hosting.
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
