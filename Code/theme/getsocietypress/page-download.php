<?php
/**
 * Download Page Template (page-download.php)
 *
 * Opens with the one decision every visitor has to make: run SocietyPress
 * yourself (free), or have us host it (paid service). Only then does it get
 * into the download itself.
 *
 * WHY the fork comes first: people arriving here are deciding how to get
 * started. A non-technical board member who downloads a PHP file and then
 * discovers they also need a web host has had a bad first hour. Showing both
 * paths side by side — with "the software is free either way" above them —
 * lets them pick before they invest any effort.
 *
 * Sections:
 * 1. Hero — "Two ways to run SocietyPress" (Option A / Option B cards)
 * 2. Who does what? — shared comparison table (template-parts/hosting-choice.php)
 * 3. Option A: Download — installer + full bundle
 * 4. What's Included — plugin + theme description
 * 5. Quick Start — installer-first, with manual upload as fallback
 * 6. Requirements reminder
 *
 * @package getsocietypress
 * @version 0.04d
 */

defined( 'ABSPATH' ) || exit;

get_header();

$sp_ver           = gsp_get_sp_version();
$sp_download_url  = gsp_get_download_url();
$sp_installer_url = gsp_get_installer_url();
$sp_from_price    = gsp_get_hosting_from_price();
?>

<!-- ==========================================================================
     1. TWO WAYS HERO
     Two equal cards. Neither is "recommended" — the right answer depends on
     whether the society has someone comfortable running a website, and we
     say exactly that under each card.
     ========================================================================== -->
<section class="dl-hero host-fork">
    <div class="container">
        <div class="dl-hero__content">

            <h1 class="dl-hero__title">Two ways to run SocietyPress</h1>

            <p class="dl-hero__subtitle">
                The software is free either way. The only question is who takes care of the website.
            </p>

            <div class="host-fork__choices">

                <!-- Option A: self-host -->
                <div class="host-fork__card">
                    <span class="host-fork__label">Option A</span>
                    <h2 class="host-fork__title">Host it yourself</h2>
                    <p class="host-fork__price">Free</p>
                    <p class="host-fork__price-note">plus whatever your web host charges, usually $5&ndash;15 a month</p>
                    <ul class="host-fork__list">
                        <li>You pick a web host and pay them</li>
                        <li>You upload the installer and run it</li>
                        <li>You install updates and keep backups</li>
                        <li>Help comes from the docs and forums</li>
                    </ul>
                    <p class="host-fork__fit"><strong>Best if</strong> someone in your society is comfortable running a website.</p>
                    <a href="#download" class="btn btn-outline btn-xl host-fork__btn">Download free</a>
                </div>

                <!-- Option B: hosted -->
                <div class="host-fork__card host-fork__card--hosted">
                    <span class="host-fork__label">Option B</span>
                    <h2 class="host-fork__title">We host it for you</h2>
                    <p class="host-fork__price">From $<?php echo esc_html( $sp_from_price ); ?> a month</p>
                    <p class="host-fork__price-note">hosting, updates, backups and support included</p>
                    <ul class="host-fork__list">
                        <li>No web host to find or pay</li>
                        <li>We install and set it up</li>
                        <li>We install updates and keep daily backups</li>
                        <li>You email us when you need help</li>
                    </ul>
                    <p class="host-fork__fit"><strong>Best if</strong> you&rsquo;d rather never think about servers.</p>
                    <a href="<?php echo esc_url( home_url( '/hosting/' ) ); ?>" class="btn btn-primary btn-xl host-fork__btn">See hosting plans</a>
                </div>

            </div>

            <p class="dl-hero__meta dl-hero__meta--footer">
                <a href="#compare">Not sure? Compare them side by side &darr;</a>
            </p>

        </div>
    </div>
</section>


<!-- ==========================================================================
     2. WHO DOES WHAT? — shared with the Hosting page
     ========================================================================== -->
<?php get_template_part( 'template-parts/hosting-choice' ); ?>


<!-- ==========================================================================
     3. OPTION A: DOWNLOAD
     Two equal paths side-by-side: the installer (recommended for most) and
     the full bundle (for anyone who'd rather upload files by hand).
     ========================================================================== -->
<section class="dl-hero dl-hero--download" id="download">
    <div class="container">
        <div class="dl-hero__content">

            <p class="host-fork__label">Option A &middot; Host it yourself</p>

            <h2 class="dl-hero__title">Download SocietyPress</h2>

            <p class="dl-hero__subtitle">
                Free. Open source. No account required. You&rsquo;ll need your own
                <a href="<?php echo esc_url( home_url( '/docs/requirements/' ) ); ?>">web host</a> &mdash;
                or <a href="<?php echo esc_url( home_url( '/hosting/' ) ); ?>">let us host it for you</a>.
            </p>

            <div class="dl-hero__version">
                <span class="dl-hero__version-number">v<?php echo esc_html( $sp_ver ); ?></span>
                <span class="dl-hero__version-label">Latest Release</span>
            </div>

            <!-- Two-choice grid -->
            <div class="dl-hero__choices">

                <!-- Installer (recommended) -->
                <div class="dl-hero__box dl-hero__box--primary">
                    <h2 class="dl-hero__box-title">One-Click Installer</h2>
                    <p class="dl-hero__box-blurb">
                        Recommended. Upload one small file to your site and point
                        your browser at it. The installer handles the rest.
                    </p>

                    <a href="<?php echo esc_url( $sp_installer_url ); ?>" class="btn btn-primary btn-xl dl-hero__btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Download sp-installer.php
                    </a>

                    <div class="dl-hero__meta">
                        <span>Single PHP file</span>
                        <span>&middot;</span>
                        <span>Under 100 KB</span>
                    </div>
                </div>

                <!-- Full bundle -->
                <div class="dl-hero__box dl-hero__box--secondary">
                    <h2 class="dl-hero__box-title">Full Platform (.zip)</h2>
                    <p class="dl-hero__box-blurb">
                        The complete bundle: plugin and all six themes. For manual
                        uploads via SFTP or your host's file manager.
                    </p>

                    <a href="<?php echo esc_url( $sp_download_url ); ?>" class="btn btn-outline btn-xl dl-hero__btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Download .zip
                    </a>

                    <div class="dl-hero__meta">
                        <span>Plugin + 6 themes</span>
                        <span>&middot;</span>
                        <span>About 8 MB</span>
                    </div>
                </div>

            </div>

            <p class="dl-hero__meta dl-hero__meta--footer">
                <span>GPL v2 License</span>
                <span>&middot;</span>
                <a href="<?php echo esc_url( home_url( '/changelog/' ) ); ?>">What's new in v<?php echo esc_html( $sp_ver ); ?> &rarr;</a>
            </p>

        </div>
    </div>
</section>


<!-- ==========================================================================
     4. WHAT'S INCLUDED
     Two cards: the plugin and the themes.
     ========================================================================== -->
<section class="dl-included section">
    <div class="container">

        <div class="section-header">
            <h2>What's in the Download</h2>
            <p>Everything you need to get your society's website running.</p>
        </div>

        <div class="dl-included__grid">

            <!-- Plugin card -->
            <div class="dl-included__card">
                <div class="dl-included__card-icon" aria-hidden="true">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                    </svg>
                </div>
                <h3>SocietyPress Plugin</h3>
                <p>
                    The core of the platform. Manages members, events, dues, the
                    research library, genealogical records, donations, newsletters,
                    the page builder, and the design system. Install it like any
                    other <a href="https://wordpress.org" target="_blank" rel="noopener">WordPress</a> plugin.
                </p>
                <ul class="dl-included__list">
                    <li>18 toggleable feature modules</li>
                    <li>Page builder with 40 widget types</li>
                    <li>Design system with live preview</li>
                    <li>Member directory with privacy controls</li>
                    <li>CSV, GEDCOM, and <a href="https://genrecord.org" target="_blank" rel="noopener">GENRECORD</a> import &amp; export</li>
                    <li>Full-site export in one click</li>
                </ul>
            </div>

            <!-- Themes card -->
            <div class="dl-included__card">
                <div class="dl-included__card-icon" aria-hidden="true">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <line x1="3" y1="9" x2="21" y2="9"/>
                        <line x1="9" y1="21" x2="9" y2="9"/>
                    </svg>
                </div>
                <h3>SocietyPress Theme + 5 Child Themes</h3>
                <p>
                    A clean, responsive parent theme and five ready-to-use child
                    themes, each with its own color palette and typography. Every
                    style is controlled from the plugin's design panel &mdash; no
                    code required.
                </p>
                <ul class="dl-included__list">
                    <li><strong>Heritage</strong> &mdash; classic serif, rich browns and antique gold</li>
                    <li><strong>Coastline</strong> &mdash; airy blues, sans-serif</li>
                    <li><strong>Prairie</strong> &mdash; earthy greens and tans</li>
                    <li><strong>Ledger</strong> &mdash; editorial, archival feel</li>
                    <li><strong>Parlor</strong> &mdash; stately and traditional</li>
                </ul>
            </div>

        </div>

    </div>
</section>


<!-- ==========================================================================
     5. QUICK START
     Installer-first. The one-click installer is the recommended path; the
     manual plugin/theme upload stays as a fallback for anyone who wants it.
     ========================================================================== -->
<section class="dl-quickstart section">
    <div class="container">

        <div class="section-header">
            <h2>Quick Start</h2>
            <p>From zero to a running society site in a few minutes.</p>
        </div>

        <div class="dl-steps">

            <div class="dl-step">
                <div class="dl-step__number">1</div>
                <div class="dl-step__content">
                    <h3>Point your browser at the installer</h3>
                    <p>
                        Upload <code>sp-installer.php</code> to a fresh <a href="https://wordpress.org" target="_blank" rel="noopener">WordPress</a> site, then
                        visit <code>yoursite.com/sp-installer.php</code>. The installer pulls
                        this .zip, sets up the plugin and theme, and drops you at the setup
                        wizard &mdash; no SSH, no FTP, no cPanel gymnastics.
                    </p>
                    <p class="dl-step__note">
                        <a href="<?php echo esc_url( home_url( '/docs/installation/' ) ); ?>">Full installer guide &rarr;</a>
                    </p>
                </div>
            </div>

            <div class="dl-step">
                <div class="dl-step__number">2</div>
                <div class="dl-step__content">
                    <h3>Run the setup wizard</h3>
                    <p>
                        Three short steps: your society's name and contact details, your
                        membership tiers, and your colors and logo. You can change everything
                        later &mdash; this just gets you to a usable site fast.
                    </p>
                </div>
            </div>

            <div class="dl-step">
                <div class="dl-step__number">3</div>
                <div class="dl-step__content">
                    <h3>Import your members (optional)</h3>
                    <p>
                        If you're coming from a spreadsheet, ENS, or another platform, use
                        the CSV importer under <strong>Members &gt; Import</strong>.
                        Organizational members are auto-detected, and duplicate detection
                        keeps a second run from creating copies.
                    </p>
                </div>
            </div>

            <div class="dl-step">
                <div class="dl-step__number">4</div>
                <div class="dl-step__content">
                    <h3>Pick a theme and go live</h3>
                    <p>
                        Activate Heritage, Coastline, Prairie, Ledger, or Parlor &mdash;
                        or stay on the parent theme and tune colors from the design panel.
                        Publish your homepage, add your upcoming meetings, and you're done.
                    </p>
                </div>
            </div>

        </div>

        <div class="dl-quickstart__cta">
            <a href="<?php echo esc_url( home_url( '/docs/installation/' ) ); ?>" class="btn btn-outline btn-lg">
                Full Installation Guide &rarr;
            </a>
        </div>

        <!-- Manual-upload fallback — discreet, for people who want it -->
        <div class="dl-manual">
            <h3>Prefer to upload manually?</h3>
            <p>
                Unzip the download. Upload the <code>societypress</code> folder to
                <code>wp-content/plugins/</code> and each theme folder to
                <code>wp-content/themes/</code> via your host's file manager or SFTP.
                Activate the plugin first, then your chosen theme. The plugin creates
                its database tables on activation and you'll land on the setup wizard.
            </p>
        </div>

    </div>
</section>


<!-- ==========================================================================
     6. REQUIREMENTS REMINDER
     Light nudge to check requirements before installing.
     ========================================================================== -->
<section class="dl-req-reminder section">
    <div class="container">
        <div class="dl-req-reminder__content">
            <h3>Before you install</h3>
            <p>
                Make sure your hosting environment meets the
                <a href="<?php echo esc_url( home_url( '/docs/requirements/' ) ); ?>">minimum requirements</a>.
                Short version: PHP 8.1+, <a href="https://wordpress.org" target="_blank" rel="noopener">WordPress</a> 6.0+, and MySQL 8.0+ (or MariaDB 10.6+).
                If your host runs a current version of <a href="https://wordpress.org" target="_blank" rel="noopener">WordPress</a>, you're almost certainly fine.
            </p>
        </div>
    </div>
</section>

<?php get_footer(); ?>
