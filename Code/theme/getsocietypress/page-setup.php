<?php
/**
 * First-Time Setup Guide Page Template (page-setup.php)
 *
 * Visual, screenshot-heavy walkthrough for new SocietyPress admins.
 * Each section pairs a screenshot with step-by-step instructions
 * so users can match what they see on screen to the guide.
 *
 * Sections:
 * 1. Hero — "First-Time Setup"
 * 2. The Dashboard — what you see after activation
 * 3. Society Settings — name, location, contact info
 * 4. Design System — colors, fonts, logo
 * 5. Membership Plans — creating your plan structure
 * 6. Adding Members — manual entry and CSV import
 * 7. Modules — enabling and disabling features
 * 8. Page Builder — building your first page
 * 9. Going Live — publishing your site
 * 10. CTA — next steps
 *
 * @package getsocietypress
 * @version 0.02d
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<!-- ==========================================================================
     1. FIRST-TIME SETUP HERO
     ========================================================================== -->
<section class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <h1 class="page-hero__title">First-Time Setup</h1>
            <p class="page-hero__subtitle">
                A visual walkthrough of everything you'll do after installing
                SocietyPress — with screenshots of every step.
            </p>
        </div>
    </div>
</section>


<!-- ==========================================================================
     2. THE DASHBOARD
     First thing they see after activation — orient them.
     ========================================================================== -->
<section class="setup-section section">
    <div class="container">

        <div class="setup-step">
            <div class="setup-step__text">
                <div class="setup-step__badge">Overview</div>
                <h2>The SocietyPress Dashboard</h2>
                <p>After activating the plugin, you'll see a new <strong>SocietyPress</strong> menu in your WordPress sidebar. This is your home base — everything you need to manage your society lives here.</p>
                <p>The dashboard gives you an at-a-glance summary: how many members you have, upcoming events, recent activity, and quick links to the areas you'll use most. Don't worry about all the options right now — we'll walk through each one.</p>
            </div>
            <div class="setup-step__screenshot">
                <div class="setup-screenshot" aria-label="Screenshot: SocietyPress dashboard after first activation">
                    <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/setup/dashboard.jpg"
                         alt="The SocietyPress dashboard showing member count, upcoming events, and sidebar navigation"
                         loading="lazy">
                </div>
                <p class="setup-screenshot__caption">The SocietyPress dashboard — your home base for managing everything.</p>
            </div>
        </div>

    </div>
</section>


<!-- ==========================================================================
     3. SOCIETY SETTINGS
     Name, location, contact email, timezone.
     ========================================================================== -->
<section class="setup-section setup-section--alt section">
    <div class="container">

        <div class="setup-step setup-step--reverse">
            <div class="setup-step__text">
                <div class="setup-step__badge">Step 1</div>
                <h2>Name Your Society</h2>
                <p>Open <strong>Settings &rarr; Website</strong>. The first thing to fill in is your society's name — this appears in the header, footer, emails, and anywhere your society is referenced.</p>
                <p>While you're here, set your:</p>
                <ul class="setup-step__list">
                    <li><strong>Society location</strong> — city, state, or region</li>
                    <li><strong>Contact email</strong> — where member inquiries go</li>
                    <li><strong>Timezone</strong> — so events display the right times</li>
                    <li><strong>Founded year</strong> — displayed on your public site</li>
                </ul>
                <p>Hit <strong>Save Settings</strong> when you're done. You can change any of this later.</p>
            </div>
            <div class="setup-step__screenshot">
                <div class="setup-screenshot" aria-label="Screenshot: Society settings page with name and contact fields">
                    <div class="setup-screenshot__placeholder">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <span>Screenshot: Settings page — society name, location, contact email, timezone</span>
                    </div>
                    <!-- Replace placeholder div above with:
                         <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/setup/settings-society.png"
                              alt="SocietyPress Settings page showing society name, location, contact email, and timezone fields"
                              width="800" height="500" loading="lazy"> -->
                </div>
                <p class="setup-screenshot__caption">Settings &rarr; Website — tell SocietyPress who you are.</p>
            </div>
        </div>

    </div>
</section>


<!-- ==========================================================================
     4. DESIGN SYSTEM
     Colors, fonts, logo, live preview.
     ========================================================================== -->
<section class="setup-section section">
    <div class="container">

        <div class="setup-step">
            <div class="setup-step__text">
                <div class="setup-step__badge">Step 2</div>
                <h2>Set Your Brand Colors</h2>
                <p>Navigate to <strong>Appearance &rarr; Design</strong>. This is where you make the site look like <em>your</em> society, not a generic template.</p>
                <p>Pick your <strong>primary color</strong> (used for headers, buttons, and navigation) and your <strong>accent color</strong> (used for highlights, links, and call-to-action elements). The live preview on the right side of the screen updates in real time as you adjust.</p>
                <ul class="setup-step__list">
                    <li><strong>Primary &amp; accent colors</strong> — color pickers, no hex codes needed</li>
                    <li><strong>Fonts</strong> — choose a heading font and a body font</li>
                    <li><strong>Logo</strong> — upload your society's logo</li>
                    <li><strong>Layout</strong> — content width, spacing, border radius</li>
                </ul>
                <p>Nothing goes live until you click <strong>Save</strong>. Experiment freely.</p>
            </div>
            <div class="setup-step__screenshot">
                <div class="setup-screenshot" aria-label="Screenshot: Design panel with color pickers and live preview">
                    <div class="setup-screenshot__placeholder">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <span>Screenshot: Design panel — color pickers, font selectors, live preview</span>
                    </div>
                    <!-- Replace placeholder div above with:
                         <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/setup/design-colors.png"
                              alt="SocietyPress Design panel showing color pickers for primary and accent colors with a live preview of the site"
                              width="800" height="500" loading="lazy"> -->
                </div>
                <p class="setup-screenshot__caption">The Design panel — pick colors and watch the preview update live.</p>
            </div>
        </div>

    </div>
</section>


<!-- ==========================================================================
     5. MEMBERSHIP TIERS
     Setting up the tier structure before adding members.
     ========================================================================== -->
<section class="setup-section setup-section--alt section">
    <div class="container">

        <div class="setup-step setup-step--reverse">
            <div class="setup-step__text">
                <div class="setup-step__badge">Step 3</div>
                <h2>Create Membership Plans</h2>
                <p>Go to <strong>Members &rarr; Membership Plans</strong>. Before you add members, you need at least one plan for them to belong to.</p>
                <p>Most societies have a few standard plans. Set up the ones that match your organization:</p>
                <ul class="setup-step__list">
                    <li><strong>Plan name</strong> — Individual, Family, Student, Lifetime, etc.</li>
                    <li><strong>Dues amount</strong> — annual cost (or $0 for honorary/lifetime)</li>
                    <li><strong>Renewal period</strong> — annual, biennial, lifetime, or custom</li>
                    <li><strong>Description</strong> — what's included (shown to members)</li>
                </ul>
                <p>You can add, edit, or remove plans at any time. Members won't lose their records if you reorganize later.</p>
            </div>
            <div class="setup-step__screenshot">
                <div class="setup-screenshot" aria-label="Screenshot: Membership tiers list showing Individual, Family, and Lifetime tiers">
                    <div class="setup-screenshot__placeholder">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <span>Screenshot: Membership Plans page — plan list with names, dues amounts, and renewal periods</span>
                    </div>
                    <!-- Replace placeholder div above with:
                         <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/setup/membership-plans.png"
                              alt="SocietyPress Membership Plans page showing a list of plans with names, dues amounts, and renewal periods"
                              width="800" height="500" loading="lazy"> -->
                </div>
                <p class="setup-screenshot__caption">Membership Plans — define what your society offers.</p>
            </div>
        </div>

    </div>
</section>


<!-- ==========================================================================
     6. ADDING MEMBERS
     Manual entry and CSV import.
     ========================================================================== -->
<section class="setup-section section">
    <div class="container">

        <div class="setup-step">
            <div class="setup-step__text">
                <div class="setup-step__badge">Step 4</div>
                <h2>Add Your Members</h2>
                <p>Open the <strong>Members</strong> menu. You have two ways to get your members into the system:</p>

                <h3 class="setup-step__subhead">Option A: Import from a spreadsheet</h3>
                <p>If you have an existing member list in Excel or Google Sheets, export it as a CSV file and use <strong>Members &rarr; Import Members</strong>. Map your spreadsheet columns to SocietyPress fields (name, email, tier, join date, etc.) and the importer handles the rest. It catches duplicates and flags any rows that need attention.</p>

                <h3 class="setup-step__subhead">Option B: Add members manually</h3>
                <p>Click <strong>Add New Member</strong> to enter members one at a time. Fill in their name, contact info, select a membership tier, and save. Good for small societies or adding new members as they join.</p>
            </div>
            <div class="setup-step__screenshot">
                <div class="setup-screenshot" aria-label="Screenshot: Member import screen with CSV column mapping">
                    <div class="setup-screenshot__placeholder">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <span>Screenshot: CSV import screen — file upload, column mapping, preview of imported data</span>
                    </div>
                    <!-- Replace placeholder div above with:
                         <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/setup/member-import.png"
                              alt="SocietyPress CSV import screen showing file upload area and column mapping dropdowns"
                              width="800" height="500" loading="lazy"> -->
                </div>
                <p class="setup-screenshot__caption">CSV import — map your spreadsheet columns and let SocietyPress do the work.</p>
            </div>
        </div>

        <!-- Second screenshot for this section: the member list after import -->
        <div class="setup-step setup-step--reverse setup-step--continuation">
            <div class="setup-step__text">
                <p>After importing or adding members, you'll see them all in the member list. From here you can search, filter by tier or status, edit individual records, or export everything back to a spreadsheet.</p>
            </div>
            <div class="setup-step__screenshot">
                <div class="setup-screenshot" aria-label="Screenshot: Member list showing imported members with search and filter controls">
                    <div class="setup-screenshot__placeholder">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <span>Screenshot: Member list — table of members with name, email, tier, status, and join date columns</span>
                    </div>
                    <!-- Replace placeholder div above with:
                         <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/setup/member-list.png"
                              alt="SocietyPress member list showing a table of members with search bar and tier filter dropdown"
                              width="800" height="500" loading="lazy"> -->
                </div>
                <p class="setup-screenshot__caption">Your member list — searchable, filterable, and exportable.</p>
            </div>
        </div>

    </div>
</section>


<!-- ==========================================================================
     7. MODULES
     Turning features on and off.
     ========================================================================== -->
<section class="setup-section setup-section--alt section">
    <div class="container">

        <div class="setup-step setup-step--reverse">
            <div class="setup-step__text">
                <div class="setup-step__badge">Step 5</div>
                <h2>Enable Your Modules</h2>
                <p>Go to <strong>Settings &rarr; Modules</strong>. SocietyPress has a lot of features, but not every society needs all of them. This page lets you toggle each module on or off.</p>
                <p>Modules include:</p>
                <ul class="setup-step__list">
                    <li><strong>Events</strong> — calendar, event management, RSVPs</li>
                    <li><strong>Store</strong> — sell books, publications, and merchandise</li>
                    <li><strong>Volunteers</strong> — track volunteer roles and hours</li>
                    <li><strong>Genealogy Records</strong> — searchable record database</li>
                    <li><strong>Newsletters</strong> — manage and archive past newsletters</li>
                    <li><strong>Donations</strong> — accept and track donations</li>
                    <li><strong>Resources</strong> — curated link library for members</li>
                </ul>
                <p>Disabled modules disappear from the sidebar entirely — less clutter for you, and less confusion for anyone else who helps manage the site.</p>
            </div>
            <div class="setup-step__screenshot">
                <div class="setup-screenshot" aria-label="Screenshot: Modules settings page showing toggle switches for each feature">
                    <div class="setup-screenshot__placeholder">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <span>Screenshot: Modules page — toggle switches for Events, Store, Volunteers, Records, Newsletters, Donations, Resources</span>
                    </div>
                    <!-- Replace placeholder div above with:
                         <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/setup/modules.png"
                              alt="SocietyPress Modules settings page showing toggle switches for Events, Store, Volunteers, Genealogy Records, Newsletters, Donations, and Resources"
                              width="800" height="500" loading="lazy"> -->
                </div>
                <p class="setup-screenshot__caption">Modules — only enable what your society actually uses.</p>
            </div>
        </div>

    </div>
</section>


<!-- ==========================================================================
     8. PAGE BUILDER
     Building the first page.
     ========================================================================== -->
<section class="setup-section section">
    <div class="container">

        <div class="setup-step">
            <div class="setup-step__text">
                <div class="setup-step__badge">Step 6</div>
                <h2>Build Your Pages</h2>
                <p>Go to <strong>Appearance &rarr; Pages</strong>. The page builder is how you create everything your visitors see — your homepage, about page, contact page, and any others you need.</p>
                <p>Each page is built from <strong>widgets</strong> — self-contained content blocks that you stack in whatever order you want:</p>
                <ul class="setup-step__list">
                    <li><strong>Text</strong> — rich text with headings, lists, and links</li>
                    <li><strong>Image</strong> — photos and graphics with captions</li>
                    <li><strong>Events</strong> — automatically shows upcoming events</li>
                    <li><strong>Members</strong> — member spotlight or directory embed</li>
                    <li><strong>Call to Action</strong> — buttons and highlighted sections</li>
                    <li><strong>Columns</strong> — side-by-side layout for any content</li>
                </ul>
                <p>Drag widgets to reorder them. Click any widget to edit its content. The page shows exactly what your visitors will see.</p>
            </div>
            <div class="setup-step__screenshot">
                <div class="setup-screenshot" aria-label="Screenshot: Page builder with widgets arranged on a homepage">
                    <div class="setup-screenshot__placeholder">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <span>Screenshot: Page builder — widget list on left, page preview on right, drag handles on each widget</span>
                    </div>
                    <!-- Replace placeholder div above with:
                         <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/setup/page-builder.png"
                              alt="SocietyPress page builder showing a homepage under construction with text, image, and events widgets stacked vertically"
                              width="800" height="500" loading="lazy"> -->
                </div>
                <p class="setup-screenshot__caption">The page builder — drag, drop, and edit. No code required.</p>
            </div>
        </div>

    </div>
</section>


<!-- ==========================================================================
     9. GOING LIVE
     Final checks before publishing.
     ========================================================================== -->
<section class="setup-section setup-section--alt section">
    <div class="container">

        <div class="setup-step setup-step--reverse">
            <div class="setup-step__text">
                <div class="setup-step__badge">Step 7</div>
                <h2>Go Live</h2>
                <p>Your society's name is set, your colors look right, your tiers are defined, your members are imported, and your pages are built. Time to open the doors.</p>
                <p>SocietyPress doesn't publish anything until you say so. When you're ready:</p>
                <ul class="setup-step__list">
                    <li><strong>Review your homepage</strong> — click "View Site" in the admin bar to see what visitors will see</li>
                    <li><strong>Check your navigation</strong> — make sure the pages you want in your menu are there</li>
                    <li><strong>Test on your phone</strong> — pull up the site on a mobile device to make sure it looks good</li>
                    <li><strong>Share the link</strong> — send your URL to a few board members for a final gut check</li>
                </ul>
                <p>When everything looks right, you're done. Your society's website is live.</p>
            </div>
            <div class="setup-step__screenshot">
                <div class="setup-screenshot" aria-label="Screenshot: A finished society homepage as seen by visitors">
                    <div class="setup-screenshot__placeholder">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <span>Screenshot: Finished society homepage — header with logo, hero section, upcoming events, footer</span>
                    </div>
                    <!-- Replace placeholder div above with:
                         <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/setup/live-site.png"
                              alt="A finished SocietyPress society homepage showing the header with society logo, a hero section, upcoming events widget, and footer"
                              width="800" height="500" loading="lazy"> -->
                </div>
                <p class="setup-screenshot__caption">A finished SocietyPress site — ready for members and visitors.</p>
            </div>
        </div>

    </div>
</section>


<!-- ==========================================================================
     10. CTA — NEXT STEPS
     ========================================================================== -->
<section class="setup-cta">
    <div class="container">
        <div class="setup-cta__content">
            <h2>You're up and running.</h2>
            <p>
                Your site is configured and ready to serve your society.
                Explore the full documentation to learn about events, the store,
                genealogy records, and everything else SocietyPress can do.
            </p>
            <div class="setup-cta__actions">
                <a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>" class="btn btn-primary btn-lg">
                    Full Documentation
                </a>
                <a href="<?php echo esc_url( home_url( '/features/' ) ); ?>" class="btn btn-secondary btn-lg">
                    Explore Features
                </a>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
