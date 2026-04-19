<?php
/**
 * Changelog Page Template (page-changelog.php)
 *
 * User-facing release notes. Hand-written from the engineering WORKLOG.md,
 * translated into language a society administrator (not a developer) will
 * actually find useful. If you're a developer looking for the full technical
 * worklog, it lives in the repo's Documentation/WORKLOG.md.
 *
 * Structure: most recent release at the top. Each release is:
 *   - Version number + date
 *   - One-sentence "what's the story of this release"
 *   - Grouped bullets: What's new / What's fixed / What's changed
 *
 * Update: add the newest release entry at the top of $gsp_releases below.
 *
 * @package getsocietypress
 * @version 0.03d
 */

defined( 'ABSPATH' ) || exit;

get_header();

/*
 * Release catalog. Newest first. Each entry has a short story paragraph and
 * three optional lists. Keep entries in society-admin language — not dev
 * language. "Your member data is encrypted" beats "XChaCha20-Poly1305 via
 * libsodium" for this audience.
 */
$gsp_releases = array(

    array(
        'version' => '1.0.19',
        'date'    => 'April 14, 2026',
        'story'   => 'A big release centered on tightening everything: a full security pass on the installer, an architectural split between library items and store merchandise, first-class Committees, full-site PWA support, and a pile of internationalization cleanup.',
        'new'     => array(
            'Committees are now a first-class module &mdash; create committees, assign officers, track assignments, and publish a committee roster on your public site.',
            'Store merchandise is now separate from library items. Your &ldquo;polo shirts and lapel pins&rdquo; inventory lives in a dedicated Store Products admin with stock tracking.',
            'Full-site Progressive Web App (PWA) support &mdash; members can &ldquo;install&rdquo; your society site to their phone home screens, with your logo and theme colors.',
            'One-click full-site export at Settings &rarr; Export &amp; Backup. Every byte of your society data, zipped and downloaded.',
        ),
        'fixed'   => array(
            'Calendar widget rendered non-current months narrowly on standalone event pages. Both render paths now share the same width fix.',
            'Parent theme version number was drifting between the style.css header and the PHP constant. Synced.',
        ),
        'changed' => array(
            'Installer hardened against nine separate security findings (zip-slip, session leakage, password races, DB-host injection, and more). No action needed on your end &mdash; if you ran the installer before, you&rsquo;re fine.',
            'About 50 more admin screens now translate cleanly into languages other than English. If your society runs in French, Spanish, or German, more of SocietyPress will feel native.',
        ),
    ),

    array(
        'version' => '1.0.9',
        'date'    => 'April 9, 2026',
        'story'   => 'Design Export/Import lets you save and share your site\'s visual identity as a single file. Plus developer-mode support and several UX polish passes.',
        'new'     => array(
            'Design Export/Import &mdash; pack up your entire color palette, typography, and layout settings into a single file, and apply it to another site with one click. Great for societies running multiple sub-sites.',
            'Developer Mode &mdash; a hidden toggle for advanced users who want more console output and debug affordances.',
            'Pre-built navigation menus ship with the plugin, so a fresh install has a sensible menu from minute one.',
        ),
        'fixed'   => array(
            'ENS-related terminology cleanup throughout the admin &mdash; more consistent references, less drift.',
        ),
    ),

    array(
        'version' => '1.0.1',
        'date'    => 'April 2, 2026',
        'story'   => 'First stable 1.0 release. The plugin moved from 0.x beta territory to production-ready, with the full feature set locked in.',
        'new'     => array(
            '14 feature modules &mdash; Members, Events, Library, Newsletters, Resources, Committees, Volunteers, Donations, Store, Records, Photos &amp; Videos, Blast Email, Help Requests, Voting.',
            '21 page builder widgets &mdash; drag-and-drop layout for every public page.',
            '5 child themes &mdash; Heritage, Coastline, Prairie, Ledger, and Parlor, each with a distinct palette and typography pairing.',
            '3-step Setup Wizard for new installs.',
            'XChaCha20-Poly1305 encryption for sensitive member fields at rest.',
            'GDPR-compliant personal-data export and erasure via WordPress\'s built-in tools.',
        ),
    ),

    array(
        'version' => '0.46d',
        'date'    => 'March 27, 2026',
        'story'   => 'Late-beta polish: final widget types, full i18n pass, and the Demo site reseeding infrastructure that lets anyone reset demo.getsocietypress.org to a clean slate.',
    ),

    array(
        'version' => '0.27d',
        'date'    => 'March 3, 2026',
        'story'   => 'Mid-beta: the plugin crossed 50,000 lines, and every major module (Members, Events, Records, Library, Newsletters, Donations) had reached functional parity with its commercial counterparts.',
    ),

);
?>

<!-- ==========================================================================
     1. CHANGELOG HERO
     ========================================================================== -->
<section class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <h1 class="page-hero__title">Changelog</h1>
            <p class="page-hero__subtitle">
                What's new in each SocietyPress release, written for society
                administrators &mdash; not developers.
            </p>
        </div>
    </div>
</section>


<!-- ==========================================================================
     2. CURRENT RELEASE CALLOUT
     Pulled from the same helper every download link uses.
     ========================================================================== -->
<section class="changelog-current">
    <div class="container">
        <div class="changelog-current__box">
            <div class="changelog-current__label">Current release</div>
            <div class="changelog-current__version">v<?php echo esc_html( gsp_get_sp_version() ); ?></div>
            <a href="<?php echo esc_url( home_url( '/download/' ) ); ?>" class="btn btn-primary">Download</a>
        </div>
    </div>
</section>


<!-- ==========================================================================
     3. RELEASE LIST
     Newest first. Each release has a story, then up to three grouped lists.
     ========================================================================== -->
<section class="changelog-list section">
    <div class="container container--narrow">

        <?php foreach ( $gsp_releases as $release ) : ?>
            <article class="changelog-entry">

                <header class="changelog-entry__header">
                    <h2 class="changelog-entry__version">v<?php echo esc_html( $release['version'] ); ?></h2>
                    <div class="changelog-entry__date"><?php echo esc_html( $release['date'] ); ?></div>
                </header>

                <p class="changelog-entry__story"><?php echo esc_html( $release['story'] ); ?></p>

                <?php if ( ! empty( $release['new'] ) ) : ?>
                    <div class="changelog-entry__section">
                        <h3 class="changelog-entry__section-heading changelog-entry__section-heading--new">What's new</h3>
                        <ul>
                            <?php foreach ( $release['new'] as $item ) : ?>
                                <li><?php echo wp_kses_post( $item ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $release['fixed'] ) ) : ?>
                    <div class="changelog-entry__section">
                        <h3 class="changelog-entry__section-heading changelog-entry__section-heading--fixed">What's fixed</h3>
                        <ul>
                            <?php foreach ( $release['fixed'] as $item ) : ?>
                                <li><?php echo wp_kses_post( $item ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $release['changed'] ) ) : ?>
                    <div class="changelog-entry__section">
                        <h3 class="changelog-entry__section-heading changelog-entry__section-heading--changed">What's changed</h3>
                        <ul>
                            <?php foreach ( $release['changed'] as $item ) : ?>
                                <li><?php echo wp_kses_post( $item ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

            </article>
        <?php endforeach; ?>

        <div class="changelog-list__footnote">
            <p>
                Looking for the full technical log? Every commit since day one
                is public on
                <a href="https://github.com/SocietyPressFoundation/SocietyPress/commits/main">GitHub</a>,
                and the engineering worklog ships in the download under
                <code>Documentation/WORKLOG.md</code>.
            </p>
        </div>

    </div>
</section>

<?php get_footer(); ?>
