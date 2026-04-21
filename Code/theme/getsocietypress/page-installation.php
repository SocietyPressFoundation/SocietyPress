<?php
/**
 * Installation Guide Page Template (page-installation.php)
 *
 * Step-by-step guide for installing SocietyPress. Installer-first: the
 * sp-installer.php flow is the primary path and reduces the whole job
 * to "upload one file, fill out one form." Manual upload is kept as a
 * collapsed advanced fallback.
 *
 * Sections:
 * 1. Hero — "Installation Guide"
 * 2. Before You Begin — prerequisites for the installer
 * 3. Step 1 — Upload sp-installer.php
 * 4. Step 2 — Run the installer in your browser
 * 5. Step 3 — Land in your dashboard
 * 6. What to Do Next — post-install customization
 * 7. Troubleshooting — installer-era issues
 * 8. Advanced — manual install fallback (collapsed)
 * 9. CTA — what to do next
 *
 * @package getsocietypress
 * @version 0.03d
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
                Upload one file, fill out one form, and SocietyPress is running.
                No command line, no WordPress to install first &mdash; the
                installer handles all of it.
            </p>
        </div>
    </div>
</section>


<!-- ==========================================================================
     2. BEFORE YOU BEGIN
     What you need in place before the installer can do its job.
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
            <p>A short checklist. If anything here is unfamiliar, your hosting provider can walk you through it &mdash; these are the kinds of things they deal with every day.</p>
        </div>

        <div class="inst-checklist">

            <div class="inst-checklist__item">
                <div class="inst-checklist__check" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                <div class="inst-checklist__text">
                    <strong>An empty directory on your web host</strong>
                    <span>Either a brand-new domain, or an empty subfolder like <code>yoursite.com/society/</code>. The installer won't overwrite an existing WordPress install.</span>
                </div>
            </div>

            <div class="inst-checklist__item">
                <div class="inst-checklist__check" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                <div class="inst-checklist__text">
                    <strong>A MySQL or MariaDB database</strong>
                    <span>A database name, username, and password. Most cPanel hosts let you create one from the <strong>MySQL Databases</strong> tool in a minute or two.</span>
                </div>
            </div>

            <div class="inst-checklist__item">
                <div class="inst-checklist__check" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                <div class="inst-checklist__text">
                    <strong>PHP 8.0 or higher</strong>
                    <span>PHP 8.1+ is recommended. Almost every current host already meets this. If yours doesn't, the installer will tell you up front and stop safely.</span>
                </div>
            </div>

            <div class="inst-checklist__item">
                <div class="inst-checklist__check" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                <div class="inst-checklist__text">
                    <strong>A way to upload one file</strong>
                    <span>cPanel File Manager, SFTP, or your host's built-in file uploader. You only need to upload one small file &mdash; <code>sp-installer.php</code> &mdash; and the installer does the rest.</span>
                </div>
            </div>

        </div>

        <div class="inst-note">
            <strong>Not sure about your server specs?</strong>
            Check the <a href="<?php echo esc_url( home_url( '/docs/requirements/' ) ); ?>">full requirements page</a> for details. The short version: if your host runs WordPress, it almost certainly runs SocietyPress.
        </div>

    </div>
</section>


<!-- ==========================================================================
     3. STEP 1 — UPLOAD sp-installer.php
     ========================================================================== -->
<section class="inst-plugin section">
    <div class="container">

        <div class="inst-section-header">
            <div class="inst-section-header__icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
            </div>
            <h2>Step 1: Upload <code>sp-installer.php</code></h2>
            <p>A single, small PHP file &mdash; the entire installer in one piece. You upload it once and it takes care of everything else.</p>
        </div>

        <div class="inst-steps">

            <div class="inst-step">
                <div class="inst-step__number">1</div>
                <div class="inst-step__content">
                    <h3>Download the installer</h3>
                    <p>Grab <code>sp-installer.php</code> from the <a href="<?php echo esc_url( home_url( '/download/' ) ); ?>">download page</a>. It's under 100 KB. Save it somewhere easy to find on your computer.</p>
                </div>
            </div>

            <div class="inst-step">
                <div class="inst-step__number">2</div>
                <div class="inst-step__content">
                    <h3>Open your host's file manager</h3>
                    <p>Log in to your hosting control panel and open the file manager (on most cPanel hosts it's called <strong>File Manager</strong>). If you prefer SFTP, your usual FTP client works just as well &mdash; connect with the credentials your host provided.</p>
                </div>
            </div>

            <div class="inst-step">
                <div class="inst-step__number">3</div>
                <div class="inst-step__content">
                    <h3>Navigate to the folder where you want SocietyPress to live</h3>
                    <p>For a brand-new domain, that's usually the folder called <code>public_html</code> or <code>www</code>. For a subfolder installation, navigate into the subfolder (creating it first if needed).</p>
                </div>
            </div>

            <div class="inst-step">
                <div class="inst-step__number">4</div>
                <div class="inst-step__content">
                    <h3>Upload <code>sp-installer.php</code></h3>
                    <p>Click <strong>Upload</strong> (or drag the file onto the window, depending on your host's interface) and pick <code>sp-installer.php</code> from your computer. That's it &mdash; one file, one upload.</p>
                </div>
            </div>

        </div>

        <div class="inst-note">
            <strong>You do not need to install WordPress first.</strong>
            The installer downloads WordPress and SocietyPress together. If you've already installed WordPress in this folder, the installer will detect that and stop so it doesn't overwrite anything. In that case, use the manual install instructions at the bottom of this page.
        </div>

    </div>
</section>


<!-- ==========================================================================
     4. STEP 2 — RUN THE INSTALLER
     ========================================================================== -->
<section class="inst-theme section">
    <div class="container">

        <div class="inst-section-header">
            <div class="inst-section-header__icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polygon points="10 8 16 12 10 16 10 8"/>
                </svg>
            </div>
            <h2>Step 2: Run the Installer in Your Browser</h2>
            <p>Open the installer's URL and fill out one form. The installer checks your server, downloads everything, sets up the database, and activates SocietyPress &mdash; all while you watch a progress screen.</p>
        </div>

        <div class="inst-steps">

            <div class="inst-step">
                <div class="inst-step__number">1</div>
                <div class="inst-step__content">
                    <h3>Visit <code>yoursite.com/sp-installer.php</code></h3>
                    <p>Open a new browser tab and type your website's address followed by <code>/sp-installer.php</code> &mdash; for example, <code>https://mysociety.org/sp-installer.php</code>. If you uploaded to a subfolder, include that: <code>https://mysociety.org/society/sp-installer.php</code>.</p>
                </div>
            </div>

            <div class="inst-step">
                <div class="inst-step__number">2</div>
                <div class="inst-step__content">
                    <h3>Fill out the installer form</h3>
                    <p>One screen, a handful of fields, grouped into three short sections:</p>
                    <ul>
                        <li><strong>Database</strong> &mdash; the database name, username, and password you created with your host.</li>
                        <li><strong>Site &amp; admin account</strong> &mdash; your site title, and the email, username, and password for your WordPress administrator account.</li>
                        <li><strong>Your society</strong> &mdash; the name of your society and a contact email. This pre-fills the setup wizard so you don't have to do it again later.</li>
                    </ul>
                </div>
            </div>

            <div class="inst-step">
                <div class="inst-step__number">3</div>
                <div class="inst-step__content">
                    <h3>Click <strong>Install SocietyPress</strong></h3>
                    <p>The installer runs through several automated steps: verifying your server, downloading the latest WordPress, downloading the SocietyPress bundle, writing the configuration file, setting up the database, activating the plugin, activating the theme, and saving your society details. You'll see progress as it goes. It usually takes one to two minutes on a decent host.</p>
                </div>
            </div>

        </div>

        <div class="inst-note">
            <strong>The installer cleans up after itself.</strong>
            As soon as the install finishes, <code>sp-installer.php</code> deletes itself from your server. Leaving an installer around after use is a security risk, so SocietyPress removes it automatically.
        </div>

    </div>
</section>


<!-- ==========================================================================
     5. STEP 3 — LAND IN YOUR DASHBOARD
     ========================================================================== -->
<section class="inst-setup section">
    <div class="container">

        <div class="inst-section-header">
            <div class="inst-section-header__icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <h2>Step 3: You're Already Signed In</h2>
            <p>When the installer finishes, it logs you in to your new WordPress dashboard automatically and drops you at the SocietyPress home screen. No logging in separately, no setup wizard to step through &mdash; the information you gave the installer has already been saved.</p>
        </div>

        <div class="inst-note">
            <strong>Bookmark your dashboard.</strong>
            The admin login URL for your new site is <code>yoursite.com/wp-admin</code>. That's where you'll go from now on whenever you want to sign in.
        </div>

    </div>
</section>


<!-- ==========================================================================
     6. WHAT TO DO NEXT
     First customization actions after the installer has handed over a running
     site. These are not required — the site works out of the box.
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
            <h2>What to Do Next</h2>
            <p>SocietyPress is live and functional the moment the installer finishes. These are the first customization steps most societies take, in whatever order makes sense for you.</p>
        </div>

        <div class="inst-setup-grid">

            <div class="inst-setup-card">
                <div class="inst-setup-card__number">A</div>
                <h3>Pick your colors and fonts</h3>
                <p>Go to <strong>Appearance &rarr; Design</strong> and choose your primary and accent colors. A live preview shows your site updating in real time. Set fonts, adjust spacing, and make it yours.</p>
            </div>

            <div class="inst-setup-card">
                <div class="inst-setup-card__number">B</div>
                <h3>Create your membership plans</h3>
                <p>Navigate to <strong>Members &rarr; Membership Plans</strong> and add the plans your society offers &mdash; Individual, Family, Student, Lifetime, whatever you need. Set dues amounts and renewal periods for each.</p>
            </div>

            <div class="inst-setup-card">
                <div class="inst-setup-card__number">C</div>
                <h3>Import or add your members</h3>
                <p>If you have an existing member list, go to <strong>Members &rarr; Import Members</strong> and upload a CSV. Coming from EasyNetSites? See the <a href="<?php echo esc_url( home_url( '/docs/ens-migration/' ) ); ?>">ENS migration guide</a>. Or add members one at a time &mdash; either way, SocietyPress validates and de-duplicates as it goes.</p>
            </div>

            <div class="inst-setup-card">
                <div class="inst-setup-card__number">D</div>
                <h3>Enable the modules you need</h3>
                <p>Open <strong>Settings &rarr; Modules</strong> and toggle on the features your society will use &mdash; events, the store, volunteers, genealogy records, newsletters, and more. Leave the others off to keep your dashboard clean.</p>
            </div>

            <div class="inst-setup-card">
                <div class="inst-setup-card__number">E</div>
                <h3>Build your pages</h3>
                <p>Go to <strong>Appearance &rarr; Pages</strong> and use the built-in page builder to shape your homepage, about page, and anything else. Drag widgets onto the page &mdash; text, images, event lists, member spotlights &mdash; and arrange them how you like.</p>
            </div>

            <div class="inst-setup-card">
                <div class="inst-setup-card__number">F</div>
                <h3>Post your first event</h3>
                <p>Open <strong>Events &rarr; Add New</strong> and create a meeting, workshop, or cemetery walk. Set categories, capacity, and registration options. Your calendar and upcoming-events widgets pick it up automatically.</p>
            </div>

        </div>

        <div class="inst-note">
            <strong>Take your time.</strong>
            SocietyPress doesn't publish anything to visitors until you tell it to. Configure things at your own pace, and use the live preview in the Design panel to see how your changes look before they go live.
        </div>

    </div>
</section>


<!-- ==========================================================================
     7. TROUBLESHOOTING
     Installer-era problems. The old manual-upload issues live in the
     Advanced section at the bottom.
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
            <p>If something goes sideways, you're probably hitting one of these common issues.</p>
        </div>

        <div class="inst-trouble-list">

            <details class="inst-trouble-item">
                <summary class="inst-trouble-item__question">
                    <span>"PHP version too old"</span>
                    <svg class="inst-trouble-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </summary>
                <div class="inst-trouble-item__answer">
                    <p>The installer requires PHP 8.0 or newer. Most cPanel hosts let you change the PHP version from a tool called <strong>MultiPHP Manager</strong> or <strong>PHP Selector</strong> &mdash; pick PHP 8.1 or 8.2 for your domain, save, and reload the installer. If you can't find it, your host's support team can flip the switch in under a minute.</p>
                </div>
            </details>

            <details class="inst-trouble-item">
                <summary class="inst-trouble-item__question">
                    <span>"WordPress is already installed"</span>
                    <svg class="inst-trouble-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </summary>
                <div class="inst-trouble-item__answer">
                    <p>The installer refuses to run in a directory that already contains <code>wp-config.php</code> or <code>wp-includes/</code>. This is intentional &mdash; overwriting an existing install could destroy data. Either install into a different folder, or if you really want a fresh install here, remove the existing files first. If you want to add SocietyPress to an existing WordPress site, use the <strong>Advanced: Manual Install</strong> section at the bottom of this page.</p>
                </div>
            </details>

            <details class="inst-trouble-item">
                <summary class="inst-trouble-item__question">
                    <span>"Couldn't download the SocietyPress bundle" or "Couldn't download WordPress"</span>
                    <svg class="inst-trouble-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </summary>
                <div class="inst-trouble-item__answer">
                    <p>Your host is blocking outbound connections. The installer needs to reach <code>wordpress.org</code> and <code>getsocietypress.org</code> to fetch the files it installs. This is rare on hosts that run WordPress at all, but if it happens, ask your host to allow outbound HTTPS to those two domains &mdash; or use the Advanced manual install instead.</p>
                </div>
            </details>

            <details class="inst-trouble-item">
                <summary class="inst-trouble-item__question">
                    <span>"Could not connect to database" or database errors</span>
                    <svg class="inst-trouble-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </summary>
                <div class="inst-trouble-item__answer">
                    <p>Double-check the database name, username, and password you entered in the installer form &mdash; they must exactly match what you created in your host's database tool. On most cPanel hosts the database and user names are prefixed with your account name (for example, <code>myhost_sp</code> rather than just <code>sp</code>), so copy them from the control panel rather than typing them. Also confirm that the user has been granted all privileges on the database.</p>
                </div>
            </details>

            <details class="inst-trouble-item">
                <summary class="inst-trouble-item__question">
                    <span>Installer page is blank or stops partway through</span>
                    <svg class="inst-trouble-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </summary>
                <div class="inst-trouble-item__answer">
                    <p>This usually means the PHP process ran out of memory or hit the host's time limit. Ask your host to raise <code>memory_limit</code> to at least 128 MB and <code>max_execution_time</code> to at least 120 seconds. Both are one-line changes any support agent can make for you. After they bump the limits, reload the installer and try again.</p>
                </div>
            </details>

            <details class="inst-trouble-item">
                <summary class="inst-trouble-item__question">
                    <span>The theme looks blank or broken after install</span>
                    <svg class="inst-trouble-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </summary>
                <div class="inst-trouble-item__answer">
                    <p>Check that the SocietyPress plugin shows as <strong>Active</strong> under <strong>Plugins</strong>. The theme depends on the plugin for its design system, page templates, and content. If the plugin isn't active, the theme has nothing to work with. Activate it, reload the front page, and you should be back in business.</p>
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
                    <p>SocietyPress creates its tables automatically when the plugin is activated. If any are missing, deactivate and reactivate the plugin from the <strong>Plugins</strong> page &mdash; this re-triggers the table-creation process without losing any data.</p>
                </div>
            </details>

        </div>

    </div>
</section>


<!-- ==========================================================================
     8. ADVANCED — MANUAL INSTALL FALLBACK
     Collapsed by default. For users who already have WordPress, can't run
     the installer for some host-specific reason, or just want to upload
     the files themselves.
     ========================================================================== -->
<section class="inst-trouble section">
    <div class="container">

        <div class="inst-section-header">
            <div class="inst-section-header__icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                </svg>
            </div>
            <h2>Advanced: Manual Install</h2>
            <p>For users who already have WordPress running and want to add SocietyPress by hand, or who can't use the installer for host-specific reasons. The one-click installer is simpler for nearly everyone &mdash; only come here if you have a reason to.</p>
        </div>

        <div class="inst-trouble-list">

            <details class="inst-trouble-item">
                <summary class="inst-trouble-item__question">
                    <span>Install the plugin and theme manually</span>
                    <svg class="inst-trouble-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </summary>
                <div class="inst-trouble-item__answer">
                    <p><strong>Step 1 &mdash; Download the full .zip.</strong> From the <a href="<?php echo esc_url( home_url( '/download/' ) ); ?>">download page</a>, grab the full platform .zip (the secondary option next to the installer). Unzip it on your computer. You'll see a <code>societypress/</code> folder (the plugin) and several theme folders (<code>societypress/</code> parent theme plus the child themes).</p>

                    <p><strong>Step 2 &mdash; Install the plugin.</strong> In your WordPress dashboard, go to <strong>Plugins &rarr; Add New Plugin</strong>, click <strong>Upload Plugin</strong>, and upload the <code>societypress/</code> plugin folder (you'll need to zip just that folder first). Click <strong>Install Now</strong>, then <strong>Activate Plugin</strong>. SocietyPress creates its database tables automatically on activation.</p>

                    <p><strong>Step 3 &mdash; Install the parent theme.</strong> Go to <strong>Appearance &rarr; Themes &rarr; Add New Theme &rarr; Upload Theme</strong>. Upload the <code>societypress/</code> parent theme folder (zip it first). Click <strong>Install Now</strong>, then <strong>Activate</strong>.</p>

                    <p><strong>Step 4 (optional) &mdash; Install a child theme.</strong> If you want to use Heritage, Coastline, Prairie, Ledger, or Parlor, upload its folder the same way and activate it in place of the parent theme. The parent theme must remain installed either way.</p>

                    <p><strong>Step 5 &mdash; Run the setup wizard.</strong> Click the new <strong>SocietyPress</strong> menu in your dashboard sidebar. The setup wizard launches automatically on first visit and walks you through your society's name, membership tiers, and brand colors.</p>
                </div>
            </details>

            <details class="inst-trouble-item">
                <summary class="inst-trouble-item__question">
                    <span>"The uploaded file exceeds the upload_max_filesize directive"</span>
                    <svg class="inst-trouble-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </summary>
                <div class="inst-trouble-item__answer">
                    <p>Your hosting provider has set a low file-upload limit. Contact them and ask to increase <code>upload_max_filesize</code> and <code>post_max_size</code> to at least 10 MB. This is a standard request any host can handle in minutes.</p>
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
                    <p>A "nonce" is a one-time-use security token that WordPress generates every time you load a page in the dashboard. It proves that the action you're taking (like uploading a plugin) actually came from you, and not from a malicious script. This error almost always means you left the upload page open too long and the token expired. Log out of WordPress, log back in, and try again. If it keeps happening, clear your browser cookies for the site.</p>
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
                    <p>It hasn't. SocietyPress never modifies or deletes your existing WordPress posts, pages, or media. If content seems missing, it's just that the SocietyPress theme uses its own page system, so standard WordPress pages may not appear in the theme's navigation &mdash; but they're still in your database, safe and untouched. Switch your theme back temporarily if you need to see them.</p>
                </div>
            </details>

        </div>

    </div>
</section>


<!-- ==========================================================================
     9. CTA — WHAT'S NEXT
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
