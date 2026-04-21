<?php
/**
 * Requirements Page Template (page-requirements.php)
 *
 * Tells potential users exactly what they need to run SocietyPress
 * on their own server. Server environment specs, database notes,
 * capacity reality, and hosting recommendations.
 *
 * Sections:
 * 1. Hero — "Requirements"
 * 2. Plain-English Summary — for non-technical readers (Harold)
 * 3. Server Environment — PHP, WordPress, MySQL, memory, disk, SSL
 * 4. Database — what SocietyPress creates in your database
 * 5. How Big a Society Can It Handle — honest capacity numbers
 * 6. Recommended Hosting — shared hosting is fine, specifics
 * 7. Final CTA — "Ready to install?"
 *
 * @package getsocietypress
 * @version 0.03d
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<!-- ==========================================================================
     1. REQUIREMENTS HERO
     ========================================================================== -->
<section class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <h1 class="page-hero__title">Requirements</h1>
            <p class="page-hero__subtitle">
                What you need to run SocietyPress on your own server.
                Short answer: not much.
            </p>
        </div>
    </div>
</section>


<!-- ==========================================================================
     2. PLAIN-ENGLISH SUMMARY
     Harold test: if a non-technical volunteer reads just this block, they
     should walk away knowing whether SocietyPress will work for them.
     Tech specs follow below for the admin who actually needs them.
     ========================================================================== -->
<section class="req-plain section">
    <div class="container container--narrow">

        <div class="req-plain__content">

            <h2>The short version</h2>

            <p class="req-plain__lede">
                If your society already has a WordPress website, SocietyPress will
                almost certainly work on it. If you're starting fresh, any reputable
                shared-hosting plan will do the job.
            </p>

            <ul class="req-plain__checks">
                <li>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                    A web hosting account that offers WordPress &mdash; $5&ndash;15 a month at most hosts
                </li>
                <li>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                    A domain name (yoursociety.org) &mdash; typically $12&ndash;20 a year
                </li>
                <li>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                    A current version of WordPress (6.0 or newer &mdash; most hosts install the latest for you)
                </li>
                <li>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                    About 30 minutes and a cup of coffee to run the installer and answer the setup questions
                </li>
            </ul>

            <p class="req-plain__note">
                You do <strong>not</strong> need: a developer, a credit card, a
                SaaS subscription, a special server, or any technical training.
                The plugin handles its own database setup. The installer handles
                the file uploads. The setup wizard handles the configuration.
            </p>

        </div>

    </div>
</section>


<!-- ==========================================================================
     3. SERVER ENVIRONMENT
     The nuts and bolts — PHP version, WP version, database, memory, etc.
     ========================================================================== -->
<section class="req-env section">
    <div class="container">

        <div class="req-section-header">
            <div class="req-section-header__icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="2" width="20" height="8" rx="2" ry="2"/>
                    <rect x="2" y="14" width="20" height="8" rx="2" ry="2"/>
                    <line x1="6" y1="6" x2="6.01" y2="6"/>
                    <line x1="6" y1="18" x2="6.01" y2="18"/>
                </svg>
            </div>
            <h2>Server Environment</h2>
            <p>
                The technical specs your hosting provider needs to meet. If you're
                not sure what any of this means, send this table to your host's
                support team &mdash; they'll know whether their servers qualify.
            </p>
        </div>

        <div class="req-table-wrap">
            <table class="req-table">
                <thead>
                    <tr>
                        <th>Requirement</th>
                        <th>Minimum</th>
                        <th>Recommended</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>PHP (the language WordPress is written in)</td>
                        <td>8.1</td>
                        <td><strong>8.3 or newer</strong></td>
                    </tr>
                    <tr>
                        <td>WordPress</td>
                        <td>6.0</td>
                        <td><strong>Latest stable release</strong></td>
                    </tr>
                    <tr>
                        <td>Database</td>
                        <td>MySQL 8.0 / MariaDB 10.6</td>
                        <td><strong>MySQL 8.4+ / MariaDB 11+</strong></td>
                    </tr>
                    <tr>
                        <td>PHP Memory (how much RAM PHP can use per page load)</td>
                        <td>128 MB</td>
                        <td><strong>256 MB</strong></td>
                    </tr>
                    <tr>
                        <td>Disk Space</td>
                        <td colspan="2"><strong>About 8 MB for the download;</strong> a running society site usually sits under 500 MB including uploaded PDFs and images</td>
                    </tr>
                    <tr>
                        <td>SSL / HTTPS</td>
                        <td colspan="2"><strong>Strongly recommended.</strong> Most hosts provide free SSL via Let's Encrypt &mdash; ask support to enable it if it's not already on</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="req-note">
            <strong>No special extensions required.</strong>
            SocietyPress uses only standard PHP functions and WordPress APIs, plus
            <code>libsodium</code> (bundled with PHP 7.2+) for encrypting member data
            at rest. If your WordPress installation works, SocietyPress will work.
        </div>

    </div>
</section>


<!-- ==========================================================================
     4. DATABASE
     Non-technical description of what SocietyPress creates in the database,
     with a representative sample of tables rather than pretending there are
     only 8.
     ========================================================================== -->
<section class="req-schema section">
    <div class="container">

        <div class="req-section-header">
            <div class="req-section-header__icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <ellipse cx="12" cy="5" rx="9" ry="3"/>
                    <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/>
                    <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
                </svg>
            </div>
            <h2>Database</h2>
            <p>
                SocietyPress creates its own tables alongside your existing WordPress
                data &mdash; never inside it. Roughly 60 tables across 14 feature
                modules, all prefixed so they're easy to identify and back up.
            </p>
        </div>

        <div class="req-table-wrap">
            <table class="req-table req-table--schema">
                <thead>
                    <tr>
                        <th>Area</th>
                        <th>What it stores</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>sp_members*</code></td>
                        <td>Member records, membership tiers, dues, renewals, directory privacy settings</td>
                    </tr>
                    <tr>
                        <td><code>sp_events*</code></td>
                        <td>Events, categories, registrations, waitlists, ticket tiers</td>
                    </tr>
                    <tr>
                        <td><code>sp_records*</code></td>
                        <td>Genealogical record collections, surname databases, research queries</td>
                    </tr>
                    <tr>
                        <td><code>sp_library*</code></td>
                        <td>Library catalog, circulation, loans, holds</td>
                    </tr>
                    <tr>
                        <td><code>sp_newsletters*</code></td>
                        <td>Newsletter archive PDFs, covers, member-only access control</td>
                    </tr>
                    <tr>
                        <td><code>sp_donations*</code></td>
                        <td>Donation ledger, campaigns, recurring gifts, Stripe/PayPal transactions</td>
                    </tr>
                    <tr>
                        <td><code>sp_committees*</code></td>
                        <td>Committees, officer positions, volunteer assignments and hours</td>
                    </tr>
                    <tr>
                        <td><code>sp_store*</code></td>
                        <td>Store products, orders, and inventory (frontend display today, full checkout in progress)</td>
                    </tr>
                    <tr>
                        <td><code>sp_gallery*</code></td>
                        <td>Photos and videos, nested folders, YouTube embeds</td>
                    </tr>
                    <tr>
                        <td><code>sp_votes*</code></td>
                        <td>Ballot elections, questions, responses, audit trail</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="req-note">
            <strong>Clean uninstall, clean backups.</strong>
            Every SocietyPress table uses the <code>sp_</code> prefix under your
            WordPress prefix. Your database backup picks them up automatically, and
            if you ever remove SocietyPress the uninstaller cleans up after itself.
        </div>

    </div>
</section>


<!-- ==========================================================================
     5. CAPACITY — HOW BIG A SOCIETY CAN IT HANDLE?
     Replaces the earlier "under 1,000 members" claim, which is far below
     what SocietyPress actually handles. Numbers here mirror the live demo.
     ========================================================================== -->
<section class="req-capacity section">
    <div class="container">

        <div class="req-section-header">
            <div class="req-section-header__icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 20V10"/>
                    <path d="M12 20V4"/>
                    <path d="M6 20v-6"/>
                </svg>
            </div>
            <h2>How big a society can it handle?</h2>
            <p>
                The live demo site runs on a basic shared host and carries numbers
                larger than most local societies will ever reach. What you see below
                is not a theoretical ceiling &mdash; it's what's actually running in
                production right now.
            </p>
        </div>

        <div class="req-capacity__grid">
            <div class="req-capacity__stat">
                <span class="req-capacity__stat-number">1,100+</span>
                <span class="req-capacity__stat-label">members with full profiles</span>
            </div>
            <div class="req-capacity__stat">
                <span class="req-capacity__stat-number">6,500+</span>
                <span class="req-capacity__stat-label">genealogical records indexed and searchable</span>
            </div>
            <div class="req-capacity__stat">
                <span class="req-capacity__stat-number">19,000+</span>
                <span class="req-capacity__stat-label">library catalog items</span>
            </div>
            <div class="req-capacity__stat">
                <span class="req-capacity__stat-number">14</span>
                <span class="req-capacity__stat-label">feature modules running simultaneously</span>
            </div>
        </div>

        <div class="req-note">
            <strong>Comfortable range.</strong>
            For context: most local genealogical societies have 50&ndash;500 members.
            Regional and state-level societies run 1,000&ndash;5,000. SocietyPress
            handles all of that on standard shared hosting without breaking a sweat.
            If you're running a 10,000+ member national organization, talk to your
            host about a managed WordPress plan &mdash; not because the software
            can't scale, but because any WordPress site at that size benefits from
            dedicated resources.
        </div>

    </div>
</section>


<!-- ==========================================================================
     6. RECOMMENDED HOSTING
     Practical advice for non-technical society admins.
     ========================================================================== -->
<section class="req-hosting section">
    <div class="container">

        <div class="req-section-header">
            <div class="req-section-header__icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
            </div>
            <h2>Hosting</h2>
            <p>You don't need a dedicated server. Standard shared hosting works great.</p>
        </div>

        <div class="req-hosting-grid">

            <div class="req-hosting-card">
                <h3>Shared Hosting</h3>
                <p>
                    Any standard shared-hosting plan that supports WordPress will run
                    SocietyPress without issues. "Shared" means your site shares a
                    server with other small websites &mdash; it's how most society
                    websites are hosted, and it's plenty of power for a society
                    website. Look for any plan that advertises WordPress support.
                </p>
                <div class="req-hosting-card__price">
                    Typically $5&ndash;15/month
                </div>
            </div>

            <div class="req-hosting-card">
                <h3>Managed WordPress</h3>
                <p>
                    Hosts like SiteGround, Cloudways, Kinsta, or WP Engine offer
                    managed WordPress plans with automatic updates, daily backups,
                    staging environments, and better performance. A good choice for
                    larger societies or any society whose admins want less
                    hands-on maintenance.
                </p>
                <div class="req-hosting-card__price">
                    Typically $10&ndash;30/month
                </div>
            </div>

            <div class="req-hosting-card">
                <h3>VPS / Dedicated</h3>
                <p>
                    Only necessary for very large societies with heavy traffic or
                    complex integrations. Most societies will never need this level
                    of infrastructure &mdash; and if yours does, you'll probably
                    already have an IT person who'll handle the setup.
                </p>
                <div class="req-hosting-card__price">
                    Typically $20&ndash;80/month
                </div>
            </div>

        </div>

        <div class="req-note">
            <strong>No vendor lock-in.</strong>
            SocietyPress runs on any standard WordPress host. If you're unhappy with
            your provider, you can export your entire site and migrate to another
            provider at any time. See <a href="<?php echo esc_url( home_url( '/features/' ) ); ?>">Your Data Is Yours</a>.
        </div>

    </div>
</section>


<!-- ==========================================================================
     7. FINAL CTA
     ========================================================================== -->
<section class="req-cta">
    <div class="container">
        <div class="req-cta__content">
            <h2>Ready to install?</h2>
            <p>
                Download SocietyPress and follow the step-by-step installation guide.
                Most societies are up and running in under 30 minutes.
            </p>
            <div class="req-cta__actions">
                <a href="<?php echo esc_url( home_url( '/download/' ) ); ?>" class="btn btn-primary btn-lg">
                    Download SocietyPress
                </a>
                <a href="<?php echo esc_url( home_url( '/docs/installation/' ) ); ?>" class="btn btn-secondary btn-lg">
                    Installation Guide
                </a>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
