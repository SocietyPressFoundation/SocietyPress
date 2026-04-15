<?php
/**
 * Installation Guide Page Template (page-installation.php)
 *
 * Step-by-step guide for installing SocietyPress on a WordPress site.
 * Written for non-technical genealogical society administrators.
 *
 * Sections:
 * 1. Hero — "Installation Guide"
 * 2. Before You Begin — prerequisites checklist
 * 3. Install the Plugin — step-by-step upload and activation
 * 4. Install the Theme — upload and activate the companion theme
 * 5. First-Time Setup — essential configuration after activation
 * 6. Troubleshooting — common issues and fixes
 * 7. CTA — what to do next
 *
 * @package getsocietypress
 * @version 0.02d
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<!-- ==========================================================================
     1. INSTALLATION GUIDE HERO
     ========================================================================== -->
<section class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <h1 class="page-hero__title">Installation Guide</h1>
            <p class="page-hero__subtitle">
                From download to running site in under 15 minutes.
                No command line, no code — just your WordPress dashboard.
            </p>
        </div>
    </div>
</section>


<!-- ==========================================================================
     2. BEFORE YOU BEGIN
     Prerequisites checklist so nobody hits a wall mid-install.
     ========================================================================== -->
<section class="inst-prereqs section">
    <div class="container">

        <div class="inst-section-header">
            <div class="inst-section-header__icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
            </div>
            <h2>Before You Begin</h2>
            <p>Make sure you have these things in place before starting the installation. If you're not sure about any of them, ask your hosting provider — they deal with this every day.</p>
        </div>

        <div class="inst-checklist">

            <div class="inst-checklist__item">
                <div class="inst-checklist__check" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                <div class="inst-checklist__text">
                    <strong>A working WordPress installation</strong>
                    <span>WordPress 6.0 or higher. If you can log in to your WordPress dashboard, you're good.</span>
                </div>
            </div>

            <div class="inst-checklist__item">
                <div class="inst-checklist__check" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                <div class="inst-checklist__text">
                    <strong>Administrator access</strong>
                    <span>You need to be logged in as an admin — the user role that can install plugins and themes.</span>
                </div>
            </div>

            <div class="inst-checklist__item">
                <div class="inst-checklist__check" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                <div class="inst-checklist__text">
                    <strong>PHP 7.4 or higher</strong>
                    <span>PHP 8.1+ is recommended for best performance. Almost every modern host meets this already.</span>
                </div>
            </div>

            <div class="inst-checklist__item">
                <div class="inst-checklist__check" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                <div class="inst-checklist__text">
                    <strong>The SocietyPress download</strong>
                    <span>Grab it from the <a href="<?php echo esc_url( home_url( '/download/' ) ); ?>">download page</a> if you haven't already. It's a single .zip file containing both the plugin and the theme.</span>
                </div>
            </div>

        </div>

        <div class="inst-note">
            <strong>Not sure about your server specs?</strong>
            Check the <a href="<?php echo esc_url( home_url( '/requirements/' ) ); ?>">full requirements page</a> for details. The short version: if your host runs WordPress, it almost certainly runs SocietyPress.
        </div>

    </div>
</section>


<!-- ==========================================================================
     3. INSTALL THE PLUGIN
     The main event — getting the SocietyPress plugin onto the site.
     ========================================================================== -->
<section class="inst-plugin section">
    <div class="container">

        <div class="inst-section-header">
            <div class="inst-section-header__icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                </svg>
            </div>
            <h2>Step 1: Install the Plugin</h2>
            <p>The plugin is the core of SocietyPress — it handles members, events, dues, the page builder, and everything else. Install it first.</p>
        </div>

        <div class="inst-steps">

            <div class="inst-step">
                <div class="inst-step__number">1</div>
                <div class="inst-step__content">
                    <h3>Unzip the download</h3>
                    <p>Unzip the SocietyPress .zip file you downloaded. Inside you'll find two folders: <code>plugin</code> and <code>theme</code>. Inside the <code>plugin</code> folder is the file <code>societypress.php</code>.</p>
                </div>
            </div>

            <div class="inst-step">
                <div class="inst-step__number">2</div>
                <div class="inst-step__content">
                    <h3>Go to Plugins in WordPress</h3>
                    <p>Log in to your WordPress dashboard and navigate to <strong>Plugins &rarr; Add New Plugin</strong>. At the top of the page, click the <strong>Upload Plugin</strong> button.</p>
                </div>
            </div>

            <div class="inst-step">
                <div class="inst-step__number">3</div>
                <div class="inst-step__content">
                    <h3>Upload and install</h3>
                    <p>Click <strong>Choose File</strong>, then select the <code>societypress.php</code> file from the <code>plugin</code> folder you unzipped. Click <strong>Install Now</strong> and wait for WordPress to finish.</p>
                </div>
            </div>

            <div class="inst-step">
                <div class="inst-step__number">4</div>
                <div class="inst-step__content">
                    <h3>Activate the plugin</h3>
                    <p>After installation completes, click <strong>Activate Plugin</strong>. SocietyPress will automatically create all of its database tables — this takes just a few seconds. You'll see a new <strong>SocietyPress</strong> menu item appear in your dashboard sidebar.</p>
                </div>
            </div>

        </div>

        <div class="inst-note">
            <strong>Upload size limits.</strong>
            If WordPress won't let you upload the file because it's too large, your host has a low PHP upload limit. Contact your hosting provider and ask them to increase <code>upload_max_filesize</code> and <code>post_max_size</code> to at least 10 MB. This is a common, quick fix that any host can do.
        </div>

    </div>
</section>


<!-- ==========================================================================
     4. INSTALL THE THEME
     The companion theme that makes the public-facing pages look right.
     ========================================================================== -->
<section class="inst-theme section">
    <div class="container">

        <div class="inst-section-header">
            <div class="inst-section-header__icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <line x1="3" y1="9" x2="21" y2="9"/>
                    <line x1="9" y1="21" x2="9" y2="9"/>
                </svg>
            </div>
            <h2>Step 2: Install the Theme</h2>
            <p>The SocietyPress theme is purpose-built to display your public-facing pages — events, member directory, homepage, and everything your visitors see. All of its colors, fonts, and layout are controlled from the plugin's design panel.</p>
        </div>

        <div class="inst-steps">

            <div class="inst-step">
                <div class="inst-step__number">1</div>
                <div class="inst-step__content">
                    <h3>Go to Themes in WordPress</h3>
                    <p>Navigate to <strong>Appearance &rarr; Themes</strong> and click the <strong>Add New Theme</strong> button at the top. Then click <strong>Upload Theme</strong>.</p>
                </div>
            </div>

            <div class="inst-step">
                <div class="inst-step__number">2</div>
                <div class="inst-step__content">
                    <h3>Upload the theme folder</h3>
                    <p>The <code>theme</code> folder from your download needs to be zipped first — select the <code>theme</code> folder, compress it into a .zip file, then upload that .zip. Click <strong>Install Now</strong>.</p>
                </div>
            </div>

            <div class="inst-step">
                <div class="inst-step__number">3</div>
                <div class="inst-step__content">
                    <h3>Activate the theme</h3>
                    <p>After installation, click <strong>Activate</strong>. Your site's front end will now use the SocietyPress theme. Don't worry if it looks bare at first — that's normal. It'll come to life once you configure your settings and add content.</p>
                </div>
            </div>

        </div>

        <div class="inst-note">
            <strong>Plugin first, theme second.</strong>
            Always activate the SocietyPress plugin before activating the theme. The theme depends on the plugin for its design settings, page templates, and functionality. Without the plugin active, the theme won't have anything to work with.
        </div>

    </div>
</section>


<!-- ==========================================================================
     5. FIRST-TIME SETUP
     What to do right after both are activated.
     ========================================================================== -->
<section class="inst-setup section">
    <div class="container">

        <div class="inst-section-header">
            <div class="inst-section-header__icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
            </div>
            <h2>Step 3: First-Time Setup</h2>
            <p>SocietyPress is installed. Now tell it about your society.</p>
        </div>

        <div class="inst-setup-grid">

            <div class="inst-setup-card">
                <div class="inst-setup-card__number">A</div>
                <h3>Name your society</h3>
                <p>Open <strong>Settings &rarr; Website</strong> and fill in your society's name. This appears in the header, footer, and throughout your site. Set your society's location and contact email while you're here.</p>
            </div>

            <div class="inst-setup-card">
                <div class="inst-setup-card__number">B</div>
                <h3>Set your brand colors</h3>
                <p>Go to <strong>Appearance &rarr; Design</strong> and pick your primary and accent colors. The live preview shows exactly what your site will look like. Choose fonts, adjust spacing, and make it yours.</p>
            </div>

            <div class="inst-setup-card">
                <div class="inst-setup-card__number">C</div>
                <h3>Create membership plans</h3>
                <p>Navigate to <strong>Members &rarr; Membership Plans</strong> and add the plans your society offers — Individual, Family, Student, Lifetime, whatever you need. Set dues amounts and renewal periods for each.</p>
            </div>

            <div class="inst-setup-card">
                <div class="inst-setup-card__number">D</div>
                <h3>Add your members</h3>
                <p>If you have an existing member list, go to <strong>Members &rarr; Import Members</strong> and upload a CSV file. Or add members one at a time. Either way, SocietyPress handles duplicates and validates the data as it goes.</p>
            </div>

            <div class="inst-setup-card">
                <div class="inst-setup-card__number">E</div>
                <h3>Enable the modules you need</h3>
                <p>Open <strong>Settings &rarr; Modules</strong> and toggle on the features your society will use — events, the store, volunteers, genealogy records, newsletters, and more. Disable what you don't need to keep the dashboard clean.</p>
            </div>

            <div class="inst-setup-card">
                <div class="inst-setup-card__number">F</div>
                <h3>Build your pages</h3>
                <p>Go to <strong>Appearance &rarr; Pages</strong> and use the built-in page builder to create your homepage, about page, and anything else. Drag in widgets — text, images, events, member spotlights — and arrange them how you like.</p>
            </div>

        </div>

        <div class="inst-note">
            <strong>Take your time here.</strong>
            There's no rush. SocietyPress doesn't publish anything until you tell it to. Configure things at your own pace, and use the live preview in the Design panel to see how your changes look before they go live.
        </div>

    </div>
</section>


<!-- ==========================================================================
     6. TROUBLESHOOTING
     Common install issues and how to fix them.
     ========================================================================== -->
<section class="inst-trouble section">
    <div class="container">

        <div class="inst-section-header">
            <div class="inst-section-header__icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <h2>Troubleshooting</h2>
            <p>If something goes sideways during installation, you're probably hitting one of these common issues.</p>
        </div>

        <div class="inst-trouble-list">

            <details class="inst-trouble-item">
                <summary class="inst-trouble-item__question">
                    <span>"The uploaded file exceeds the upload_max_filesize directive"</span>
                    <svg class="inst-trouble-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </summary>
                <div class="inst-trouble-item__answer">
                    <p>Your hosting provider has set a low file upload limit. Contact them and ask to increase <code>upload_max_filesize</code> and <code>post_max_size</code> to at least 10 MB. This is a standard request that any host can handle in minutes.</p>
                </div>
            </details>

            <details class="inst-trouble-item">
                <summary class="inst-trouble-item__question">
                    <span>"Plugin activation failed" or white screen after activation</span>
                    <svg class="inst-trouble-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </summary>
                <div class="inst-trouble-item__answer">
                    <p>This usually means your server is running a PHP version below 7.4. Ask your host to upgrade to PHP 8.1 or higher — it's free and most hosts can switch it with a single click in their control panel. If you're on cPanel, look under <strong>Software &rarr; Select PHP Version</strong>.</p>
                </div>
            </details>

            <details class="inst-trouble-item">
                <summary class="inst-trouble-item__question">
                    <span>The theme looks blank or broken after activation</span>
                    <svg class="inst-trouble-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </summary>
                <div class="inst-trouble-item__answer">
                    <p>Make sure the SocietyPress <strong>plugin</strong> is activated. The theme depends on the plugin for its design system, page templates, and all content. Without the plugin running, the theme has nothing to display. Activate the plugin first, then check your site again.</p>
                </div>
            </details>

            <details class="inst-trouble-item">
                <summary class="inst-trouble-item__question">
                    <span>"Are you sure you want to do this?" error during upload</span>
                    <svg class="inst-trouble-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </summary>
                <div class="inst-trouble-item__answer">
                    <p>A "nonce" is a one-time-use security token that WordPress generates every time you load a page in the dashboard. It proves that the action you're taking (like uploading a plugin) actually came from you, and not from a malicious script trying to hijack your session. Think of it like a receipt number — WordPress gives you one when you start, and checks it when you finish. If the numbers don't match, WordPress blocks the action to protect your site.</p>
                    <p>This error almost always means you left the upload page open too long and the token expired. Log out of WordPress, log back in, and try the upload again. If it keeps happening, clear your browser cookies for the site — sometimes an old cookie holds a stale token.</p>
                </div>
            </details>

            <details class="inst-trouble-item">
                <summary class="inst-trouble-item__question">
                    <span>Database tables weren't created</span>
                    <svg class="inst-trouble-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </summary>
                <div class="inst-trouble-item__answer">
                    <p>SocietyPress creates its tables automatically when the plugin is activated. If they're missing, try deactivating and reactivating the plugin from the <strong>Plugins</strong> page. This re-triggers the table creation process without losing any data.</p>
                </div>
            </details>

            <details class="inst-trouble-item">
                <summary class="inst-trouble-item__question">
                    <span>My existing WordPress content disappeared</span>
                    <svg class="inst-trouble-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </summary>
                <div class="inst-trouble-item__answer">
                    <p>It hasn't. SocietyPress never modifies or deletes your existing WordPress posts, pages, or media. If content seems missing, you likely just need to switch your theme back temporarily to see it. SocietyPress uses its own page system, so standard WordPress pages may not appear in the SocietyPress theme's navigation — but they're still in your database, safe and untouched.</p>
                </div>
            </details>

        </div>

    </div>
</section>


<!-- ==========================================================================
     7. CTA — WHAT'S NEXT
     ========================================================================== -->
<section class="inst-cta">
    <div class="container">
        <div class="inst-cta__content">
            <h2>You're all set.</h2>
            <p>
                SocietyPress is installed and ready to run your society's website.
                Explore the features, set up your first event, or import your member list.
            </p>
            <div class="inst-cta__actions">
                <a href="<?php echo esc_url( home_url( '/features/' ) ); ?>" class="btn btn-primary btn-lg">
                    Explore Features
                </a>
                <a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>" class="btn btn-secondary btn-lg">
                    Back to Documentation
                </a>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
