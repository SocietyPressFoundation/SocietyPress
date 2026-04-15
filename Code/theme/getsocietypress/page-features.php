<?php
/**
 * Features Page Template (page-features.php)
 *
 * The platform overview page for getsocietypress.org. Showcases what
 * SocietyPress can do with a hero, capability grid, detailed feature
 * sections, technical requirements, open-source commitment, showcase,
 * and a final CTA.
 *
 * Sections:
 * 1. Hero — "Everything your society needs. Nothing it doesn't."
 * 2. Built for Preservationists — 6 capability icon cards with descriptions
 * 3. Core Feature — Comprehensive Member Management
 * 4. Core Feature — Event Engine & Calendar
 * 5. Specialized Tool — Advanced Genealogy Tools
 * 6. Built-In Tool — Page Builder & Design System
 * 7. Always Free & Open Source — GPL commitment
 * 8. Final CTA — "Ready to modernize your society?"
 *
 * @package getsocietypress
 * @version 0.02d
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<!-- ==========================================================================
     1. FEATURES HERO
     Dark navy background matching the homepage hero. Announces the platform
     with headline, stats, and two CTAs.
     ========================================================================== -->
<section class="feat-hero">
    <div class="container">
        <div class="feat-hero__content">

            <!-- Version badge -->
            <div class="hero__badge">v<?php echo esc_html( gsp_get_sp_version() ); ?> Released</div>

            <h1 class="feat-hero__title">
                Everything your society needs.<br>
                <span>Nothing it doesn't.</span>
            </h1>

            <p class="feat-hero__subtitle">
                The only open-source membership platform purposefully built for genealogical
                and historical societies. Manage members, events, and family trees in one place.
            </p>

            <!-- Stats — module and widget counts.
                 Numbers here are the single source of truth for every page that
                 cites them (homepage, download, requirements). Update this block
                 and the homepage demo-section__features list together. -->
            <div class="feat-hero__stats">
                <div class="feat-hero__stat">
                    <span class="feat-hero__stat-number">14</span>
                    <span class="feat-hero__stat-label">Feature Modules</span>
                </div>
                <div class="feat-hero__stat">
                    <span class="feat-hero__stat-number">21</span>
                    <span class="feat-hero__stat-label">Page Builder Widgets</span>
                </div>
                <div class="feat-hero__stat">
                    <span class="feat-hero__stat-number">5</span>
                    <span class="feat-hero__stat-label">Child Themes Included</span>
                </div>
            </div>

            <div class="feat-hero__actions">
                <a href="<?php echo esc_url( home_url( '/download/' ) ); ?>" class="btn btn-primary btn-lg">
                    Download Now
                </a>
                <a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>" class="btn btn-secondary btn-lg">
                    Installation Guide
                </a>
            </div>

        </div>
    </div>
</section>


<!-- ==========================================================================
     2. BUILT FOR PRESERVATIONISTS
     Six capability cards showing the breadth of SocietyPress at a glance.
     Each card has an icon, title, and short description.
     ========================================================================== -->
<section class="feat-capabilities section">
    <div class="container">

        <div class="section-header">
            <h2>Built for Preservationists</h2>
            <p>
                SocietyPress was created to solve the specific problems of local history groups.
                Stop wrestling with generic plugins and start using tools designed for your mission.
            </p>
        </div>

        <div class="feat-cap-grid">

            <!-- Members & Dues -->
            <div class="feat-cap-card">
                <div class="feat-cap-card__icon" aria-hidden="true">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h3>Members &amp; Dues</h3>
                <p>Track individuals and organizations, manage tiers, automate renewals, and export rosters.</p>
            </div>

            <!-- Event Engine -->
            <div class="feat-cap-card">
                <div class="feat-cap-card__icon" aria-hidden="true">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
                <h3>Event Engine</h3>
                <p>Publish meetings, workshops, and conferences with categories, locations, and date ranges.</p>
            </div>

            <!-- Publications -->
            <div class="feat-cap-card">
                <div class="feat-cap-card__icon" aria-hidden="true">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                    </svg>
                </div>
                <h3>Publications</h3>
                <p>Distribute newsletters, journals, and research bulletins to members via the page builder.</p>
            </div>

            <!-- Genealogy -->
            <div class="feat-cap-card">
                <div class="feat-cap-card__icon" aria-hidden="true">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </div>
                <h3>Genealogy</h3>
                <p>Genealogical record collections, GENRECORD and GEDCOM import/export, surname research databases, and member privacy controls.</p>
            </div>

            <!-- Fundraising -->
            <div class="feat-cap-card">
                <div class="feat-cap-card__icon" aria-hidden="true">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                </div>
                <h3>Fundraising</h3>
                <p>Track donations, run campaigns, and generate contribution reports for your board and donors.</p>
            </div>

            <!-- Site Settings -->
            <div class="feat-cap-card">
                <div class="feat-cap-card__icon" aria-hidden="true">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                    </svg>
                </div>
                <h3>Site Settings</h3>
                <p>Customize colors, fonts, and branding from a visual design panel. No code required.</p>
            </div>

        </div>

    </div>
</section>


<!-- ==========================================================================
     3. CORE FEATURE — COMPREHENSIVE MEMBER MANAGEMENT
     Two-column layout: text/bullets on the left, image on the right.
     ========================================================================== -->
<section class="feat-detail section">
    <div class="container">

        <div class="feat-detail__label">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Core Feature
        </div>

        <div class="feat-detail__grid">

            <!-- Left column: text content -->
            <div class="feat-detail__text">
                <h2>Comprehensive Member Management</h2>

                <p>
                    Move beyond spreadsheets. Track active memberships, automate
                    renewal reminders via email, and export custom rosters for your
                    print journal. The system handles grace periods and tiered
                    membership levels automatically.
                </p>

                <ul class="feat-detail__checks">
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                        Automated renewal email sequences
                    </li>
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                        Print-ready PDF roster generation
                    </li>
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                        Family &amp; individual membership tiers
                    </li>
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                        Individual &amp; organizational member types
                    </li>
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                        CSV import with smart duplicate detection
                    </li>
                </ul>

                <!-- Callout box -->
                <div class="feat-detail__callout">
                    <strong>Did you know?</strong>
                    SocietyPress supports both individual and organizational members
                    in the same database. Organizations are auto-detected during CSV
                    import based on the name field, so migrating from your existing
                    spreadsheet takes minutes, not hours.
                </div>
            </div>

            <!-- Right column: image placeholder -->
            <div class="feat-detail__image">
                <img src="/cms/wp-content/uploads/members-screenshot.jpg" alt="SocietyPress member management interface showing a list of members with names, emails, phone numbers, plans, and statuses" style="width:100%; border-radius:8px; box-shadow: 0 4px 20px rgba(0,0,0,0.12);">
            </div>

        </div>

    </div>
</section>


<!-- ==========================================================================
     4. CORE FEATURE — EVENT ENGINE & CALENDAR
     Reversed two-column layout: image on left, text on right.
     ========================================================================== -->
<section class="feat-detail feat-detail--alt section">
    <div class="container">

        <div class="feat-detail__label feat-detail__label--accent">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            Core Feature
        </div>

        <div class="feat-detail__grid feat-detail__grid--reverse">

            <!-- Left column: image placeholder -->
            <div class="feat-detail__image">
                <img src="/cms/wp-content/uploads/events-screenshot.jpg" alt="SocietyPress event management showing upcoming meetings, workshops, cemetery walks, and book sales with categories, locations, and registration counts" style="width:100%; border-radius:8px; box-shadow: 0 4px 20px rgba(0,0,0,0.12);">
            </div>

            <!-- Right column: text content -->
            <div class="feat-detail__text">
                <h2>Event Engine &amp; Calendar</h2>

                <p>
                    Your society runs on events — monthly meetings, guest lectures,
                    cemetery walks, research workshops. SocietyPress gives you a
                    purpose-built event system with categories, date ranges, locations,
                    and a public-facing calendar your members can actually find.
                </p>

                <ul class="feat-detail__checks">
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                        Event categories with color coding
                    </li>
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                        Multi-day and recurring event support
                    </li>
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                        Location details with map-ready addresses
                    </li>
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                        Admin list table with bulk management
                    </li>
                </ul>

                <div class="feat-detail__callout">
                    <strong>Built for real societies</strong>
                    Most event plugins are designed for conferences or ticketed shows.
                    SocietyPress events are designed for the way genealogical societies
                    actually work — recurring monthly meetings, free public lectures, and
                    members-only workshops.
                </div>
            </div>

        </div>

    </div>
</section>


<!-- ==========================================================================
     5. SPECIALIZED TOOL — ADVANCED GENEALOGY TOOLS
     Standard two-column layout: text on left, image on right.
     ========================================================================== -->
<section class="feat-detail section">
    <div class="container">

        <div class="feat-detail__label">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            Specialized Tool
        </div>

        <div class="feat-detail__grid">

            <!-- Left column: text content -->
            <div class="feat-detail__text">
                <h2>Genealogical Records &amp; Research Tools</h2>

                <p>
                    Publish your society's transcribed record collections &mdash;
                    cemetery indexes, census extractions, marriage registers, obituary
                    abstracts &mdash; and make them searchable by the public or members
                    only. Members can register the surnames they're researching so
                    visiting researchers can find connections.
                </p>

                <ul class="feat-detail__checks">
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                        <a href="https://genrecord.org">GENRECORD 1.0</a> import &amp; export &mdash; the new open standard for sharing genealogical source records between societies, archives, and software. SocietyPress is the first platform to support it natively.
                    </li>
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                        GEDCOM 5.5 and 7.0 import &amp; export for family tree data
                    </li>
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                        Flexible record collections with custom fields per type
                    </li>
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                        Full-text search across all collections
                    </li>
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                        Public surname research database with county, state, and date range
                    </li>
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                        Members-only directory with per-field privacy controls
                    </li>
                </ul>

                <!-- Data format callout -->
                <div class="feat-detail__callout">
                    <strong>Two Standards, One Platform</strong>
                    GENRECORD handles source records &mdash; the cemetery transcriptions,
                    marriage indexes, and census extracts that societies spend years creating.
                    GEDCOM handles family trees &mdash; individuals, families, relationships.
                    SocietyPress supports both, so your society's data is portable no matter
                    what form it takes.
                </div>
            </div>

            <!-- Right column: image placeholder -->
            <div class="feat-detail__image">
                <img src="/cms/wp-content/uploads/records-screenshot.jpg" alt="SocietyPress record collections showing cemetery indexes, census transcriptions, military records, church records, and naturalization records with record counts and export buttons" style="width:100%; border-radius:8px; box-shadow: 0 4px 20px rgba(0,0,0,0.12);">
            </div>

        </div>

    </div>
</section>


<!-- ==========================================================================
     6. BUILT-IN TOOL — PAGE BUILDER & DESIGN SYSTEM
     Reversed layout: image left, text right.
     ========================================================================== -->
<section class="feat-detail feat-detail--alt section">
    <div class="container">

        <div class="feat-detail__label feat-detail__label--accent">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <line x1="3" y1="9" x2="21" y2="9"/>
                <line x1="9" y1="21" x2="9" y2="9"/>
            </svg>
            Built-In Tool
        </div>

        <div class="feat-detail__grid feat-detail__grid--reverse">

            <!-- Left column: image placeholder -->
            <div class="feat-detail__image">
                <img src="/cms/wp-content/uploads/pagebuilder-screenshot.jpg" alt="SocietyPress page builder showing a hero slider widget with overlay text controls, image selection, and slide configuration" style="width:100%; border-radius:8px; box-shadow: 0 4px 20px rgba(0,0,0,0.12);">
            </div>

            <!-- Right column: text content -->
            <div class="feat-detail__text">
                <h2>Page Builder &amp; Design System</h2>

                <p>
                    Every page on your site is built with a drag-and-drop widget system
                    that doesn't require any coding knowledge. Add text blocks, image
                    galleries, event listings, member directories, and more — all from
                    the admin panel. Your site's colors, fonts, and sizing are controlled
                    from a centralized design panel with live preview.
                </p>

                <ul class="feat-detail__checks">
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                        21 widget types (text, image, events, directory, and more)
                    </li>
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                        7 color pickers for full palette control
                    </li>
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                        Font, size, and width controls per section
                    </li>
                    <li>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                        Live preview iframe — see changes before publishing
                    </li>
                </ul>

                <div class="feat-detail__callout">
                    <strong>No themes to buy, no code to write</strong>
                    Your society's administrator controls the entire look and feel of
                    the site from one settings page. Change your primary color, update
                    your font, adjust content width — it all updates instantly across
                    every page.
                </div>
            </div>

        </div>

    </div>
</section>


<!-- ==========================================================================
     7. ALWAYS FREE & OPEN SOURCE
     GPL commitment section with centered text and support CTA.
     ========================================================================== -->
<section class="feat-opensource section">
    <div class="container">
        <div class="feat-opensource__content">

            <!-- Heart icon -->
            <div class="feat-opensource__icon" aria-hidden="true">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
            </div>

            <h2>Always Free &amp; Open Source</h2>

            <p>
                I built SocietyPress because I was watching small historical societies struggling to
                pay $100+/month for SaaS platforms that were overkill for their needs. Our history
                belongs to everyone, and the tools to preserve it should be accessible.
            </p>

            <p>
                This project is released under the GPLv2 license. It is free to download, free to modify,
                and free to use forever. There are no hidden fees or "Pro" versions.
            </p>

            <p>
                If this software helps your organization, please consider supporting continued
                development.
            </p>

            <div class="feat-opensource__actions">
                <a href="<?php echo esc_url( home_url( '/donate/' ) ); ?>" class="btn btn-primary btn-lg">
                    Support the Project
                </a>
            </div>

        </div>
    </div>
</section>


<!-- ==========================================================================
     9. FINAL CTA
     "Ready to modernize your society?" — two buttons.
     ========================================================================== -->
<section class="feat-final-cta">
    <div class="container">
        <div class="feat-final-cta__content">

            <h2>Ready to modernize your society?</h2>
            <p>
                Get started today with our comprehensive documentation. Download the
                software and follow our step-by-step self-installation guide.
            </p>

            <div class="feat-final-cta__actions">
                <a href="<?php echo esc_url( home_url( '/download/' ) ); ?>" class="btn btn-primary btn-lg">
                    Download Free v<?php echo esc_html( gsp_get_sp_version() ); ?>
                </a>
                <a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>" class="btn btn-secondary btn-lg">
                    View Installation Guide
                </a>
            </div>

        </div>
    </div>
</section>

<?php get_footer(); ?>
