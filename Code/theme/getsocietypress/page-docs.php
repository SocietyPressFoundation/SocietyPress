<?php
/**
 * Documentation Page Template (page-docs.php)
 *
 * Documentation hub. Organized as a grid of topic cards. Each card gives
 * enough signal for a new admin to find their bearings, plus direct links
 * to the deeper guides where they exist. As long-form articles come online
 * the bracketed "coming" placeholders get swapped for real links.
 *
 * Sections:
 * 1. Hero — "Documentation"
 * 2. Quick start bar — the three links most visitors need right now
 * 3. Doc category grid — one card per major topic
 * 4. CTA — can't find what you need?
 *
 * @package getsocietypress
 * @version 0.03d
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<!-- ==========================================================================
     1. DOCS HERO
     ========================================================================== -->
<section class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <h1 class="page-hero__title">Documentation</h1>
            <p class="page-hero__subtitle">
                Guides, references, and tutorials for setting up and running
                SocietyPress for your society.
            </p>
        </div>
    </div>
</section>


<!-- ==========================================================================
     2. QUICK START BAR
     The three links most visitors need right now. Promoted above the full
     topic grid so nobody has to scan for them.
     ========================================================================== -->
<section class="docs-quick">
    <div class="container">
        <div class="docs-quick__grid">

            <a class="docs-quick__link" href="<?php echo esc_url( home_url( '/docs/installation/' ) ); ?>">
                <div class="docs-quick__link-title">Installation</div>
                <p>Get SocietyPress onto your server in about 30 minutes.</p>
            </a>

            <a class="docs-quick__link" href="<?php echo esc_url( home_url( '/docs/modules/' ) ); ?>">
                <div class="docs-quick__link-title">Module Guides</div>
                <p>One plain-English guide per module &mdash; what it does, how to set it up, and what to do when something doesn&rsquo;t look right.</p>
            </a>

            <a class="docs-quick__link" href="<?php echo esc_url( home_url( '/docs/ens-migration/' ) ); ?>">
                <div class="docs-quick__link-title">ENS Migration</div>
                <p>Coming from EasyNetSites? Start here.</p>
            </a>

        </div>
    </div>
</section>


<!-- ==========================================================================
     3. DOCUMENTATION CATEGORIES
     Each card now carries enough content to be genuinely useful on its own,
     with inline bullets that answer the "what will I actually learn here"
     question. Deep-dive article links are added as those pages come online.
     ========================================================================== -->
<section class="docs-grid section">
    <div class="container">

        <div class="docs-cards">

            <!-- Getting Started -->
            <div class="docs-card">
                <div class="docs-card__icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="13 17 18 12 13 7"/>
                        <polyline points="6 17 11 12 6 7"/>
                    </svg>
                </div>
                <h3>Getting Started</h3>
                <p>Everything you need for a clean install and a first usable site.</p>
                <ul class="docs-card__summary">
                    <li>Install the plugin and theme &mdash; via one-click installer or manual upload</li>
                    <li>Run the 3-step setup wizard: society info, membership tiers, appearance</li>
                    <li>Pick a child theme and tune your colors</li>
                    <li>Publish your homepage and add your first event</li>
                </ul>
                <ul class="docs-card__links">
                    <li><a href="<?php echo esc_url( home_url( '/docs/installation/' ) ); ?>">Installation Guide</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/docs/setup/' ) ); ?>">First-Time Setup</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/docs/requirements/' ) ); ?>">System Requirements</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/docs/troubleshooting/' ) ); ?>">Troubleshooting</a></li>
                </ul>
            </div>

            <!-- Member Management -->
            <div class="docs-card">
                <div class="docs-card__icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h3>Member Management</h3>
                <p>The core of every society website &mdash; rosters, dues, directories.</p>
                <ul class="docs-card__summary">
                    <li>Add members individually from <strong>Members &gt; Add New</strong></li>
                    <li>Bulk-import from CSV; duplicates detected by email</li>
                    <li>Configure membership tiers with different dues, terms, and benefits</li>
                    <li>Support both individual and organizational (corporate) members</li>
                    <li>Control per-field privacy in the public member directory</li>
                </ul>
                <ul class="docs-card__links">
                    <li><a href="<?php echo esc_url( home_url( '/docs/ens-migration/' ) ); ?>">CSV Import from ENS</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/docs/faq/' ) ); ?>">Role templates &amp; access areas (FAQ)</a></li>
                </ul>
            </div>

            <!-- Events -->
            <div class="docs-card">
                <div class="docs-card__icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
                <h3>Events &amp; Calendar</h3>
                <p>From monthly meetings to ticketed workshops and cemetery walks.</p>
                <ul class="docs-card__summary">
                    <li>Create events under <strong>Events &gt; Add New</strong></li>
                    <li>Organize with color-coded categories</li>
                    <li>Accept online registrations, with capacity limits and waitlists</li>
                    <li>Take payments via Stripe or PayPal for ticketed events</li>
                    <li>Publish on a browsable calendar or upcoming-events list widget</li>
                    <li>Export to iCal for members' personal calendars</li>
                </ul>
            </div>

            <!-- Page Builder -->
            <div class="docs-card">
                <div class="docs-card__icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <line x1="3" y1="9" x2="21" y2="9"/>
                        <line x1="9" y1="21" x2="9" y2="9"/>
                    </svg>
                </div>
                <h3>Page Builder</h3>
                <p>21 drag-and-drop widgets for every public page.</p>
                <ul class="docs-card__summary">
                    <li>Edit any page and click <strong>Add Widget</strong> to insert content blocks</li>
                    <li>Widgets include text, image, gallery, hero slider, events list, calendar, member directory, newsletter archive, library catalog, donation form, volunteer opportunities, resource links, and more</li>
                    <li>Reorder widgets by drag and drop; each widget has its own settings panel</li>
                    <li>Works with every child theme and the design system</li>
                </ul>
            </div>

            <!-- Design System -->
            <div class="docs-card">
                <div class="docs-card__icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                    </svg>
                </div>
                <h3>Design System</h3>
                <p>Colors, fonts, and layout &mdash; controlled from one panel.</p>
                <ul class="docs-card__summary">
                    <li>Seven color pickers for full palette control (primary, accent, text, background, headings, nav, footer)</li>
                    <li>Font family and size for body copy, headings, and nav separately</li>
                    <li>Live preview iframe &mdash; see every change before publishing</li>
                    <li>Use the color extractor to pull a palette from your current site</li>
                    <li>Export and import designs between sites as single files</li>
                </ul>
                <ul class="docs-card__links">
                    <li><a href="<?php echo esc_url( home_url( '/showcase/' ) ); ?>">Child theme gallery</a></li>
                </ul>
            </div>

            <!-- Genealogy Tools -->
            <div class="docs-card">
                <div class="docs-card__icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </div>
                <h3>Genealogy Tools</h3>
                <p>Surname databases, record collections, GEDCOM and GENRECORD.</p>
                <ul class="docs-card__summary">
                    <li>Create record collections &mdash; cemetery indexes, census extractions, marriage registers</li>
                    <li>Import from CSV or from <a href="https://genrecord.org">GENRECORD</a>-formatted files</li>
                    <li>Export anything as GENRECORD for interchange with other societies and software</li>
                    <li>Import and export family trees as GEDCOM 5.5 or 7.0</li>
                    <li>Members register the surnames they're researching; visiting researchers find connections</li>
                    <li>Per-field privacy controls for living individuals</li>
                </ul>
            </div>

            <!-- Library & Newsletters -->
            <div class="docs-card">
                <div class="docs-card__icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                    </svg>
                </div>
                <h3>Library &amp; Newsletters</h3>
                <p>Your society's collection and back issues, always available.</p>
                <ul class="docs-card__summary">
                    <li>OPAC-style catalog with call numbers, shelf locations, and circulation</li>
                    <li>Cover images pulled automatically from Open Library</li>
                    <li>Newsletter archive with automatic cover thumbnails from uploaded PDFs</li>
                    <li>Download access restricted to current members</li>
                    <li>Both surfaces have public search widgets for the homepage</li>
                </ul>
            </div>

            <!-- Donations & Store -->
            <div class="docs-card">
                <div class="docs-card__icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                </div>
                <h3>Donations &amp; Store</h3>
                <p>Accept support from members and sell society merchandise.</p>
                <ul class="docs-card__summary">
                    <li>Donation forms with preset amounts, recurring options, and campaign tracking</li>
                    <li>Stripe and PayPal both supported &mdash; connect your own accounts, no middleman</li>
                    <li>Donation ledger with exports for your treasurer</li>
                    <li>Store catalog for polo shirts, lapel pins, printed publications, and other merchandise</li>
                    <li>Native checkout: inline Stripe Payment Element (card, Apple Pay, Google Pay, Link) and PayPal Smart Buttons (PayPal, Venmo)</li>
                </ul>
            </div>

            <!-- Governance & Volunteers -->
            <div class="docs-card">
                <div class="docs-card__icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                    </svg>
                </div>
                <h3>Governance &amp; Volunteers</h3>
                <p>Committees, officers, volunteer hours, and online balloting.</p>
                <ul class="docs-card__summary">
                    <li>Create committees and assign officers with title, term, and bio</li>
                    <li>Post volunteer opportunities with shift signups and capacity limits</li>
                    <li>Log volunteer hours and run annual reports for recognition or grants</li>
                    <li>Run board elections with secure, authenticated online ballots and audit trails</li>
                </ul>
            </div>

            <!-- Insights -->
            <div class="docs-card">
                <div class="docs-card__icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="20" x2="18" y2="10"/>
                        <line x1="12" y1="20" x2="12" y2="4"/>
                        <line x1="6" y1="20" x2="6" y2="14"/>
                    </svg>
                </div>
                <h3>Insights</h3>
                <p>"How alive is our society right now?" — answered, on one screen.</p>
                <ul class="docs-card__summary">
                    <li>Headline number per enabled module: active members, events held, donations raised, volunteer hours, records added, and more</li>
                    <li>Sparkline trend on every card &mdash; up-and-to-the-right tells the story without reading anything</li>
                    <li>Time-window dropdown: rolling 30 / 90 / 365 days, this fiscal year, last fiscal year</li>
                    <li>Admin/board-only &mdash; grant the <strong>Reports</strong> access area to a treasurer or membership chair without giving full admin rights</li>
                </ul>
                <ul class="docs-card__links">
                    <li><a href="<?php echo esc_url( home_url( '/docs/modules/?guide=insights' ) ); ?>">Insights guide</a></li>
                </ul>
            </div>

        </div>

    </div>
</section>


<!-- ==========================================================================
     4. CAN'T FIND WHAT YOU NEED?
     ========================================================================== -->
<section class="docs-cta">
    <div class="container">
        <div class="docs-cta__content">
            <h2>Can't find what you need?</h2>
            <p>
                If something looks missing or wrong, tell us &mdash; the
                docs evolve as fast as the software does. Try the demo,
                check the FAQ, or reach out through the community.
            </p>
            <div class="docs-cta__actions">
                <a href="<?php echo esc_url( home_url( '/docs/faq/' ) ); ?>" class="btn btn-primary btn-lg">
                    Check the FAQ
                </a>
                <a href="https://demo.getsocietypress.org" class="btn btn-outline btn-lg" target="_blank" rel="noopener">
                    Try the Demo
                </a>
                <a href="<?php echo esc_url( home_url( '/community/' ) ); ?>" class="btn btn-secondary btn-lg">
                    Ask the Community
                </a>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
