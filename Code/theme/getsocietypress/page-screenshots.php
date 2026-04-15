<?php
/**
 * Screenshot Gallery Page Template (page-screenshots.php)
 *
 * Organized by module — gives potential users a visual tour of
 * SocietyPress's admin and public surfaces without them having to
 * log into the demo.
 *
 * Add screenshots to the $gsp_screenshots array below. Each module
 * renders as a section, each screenshot as a captioned figure.
 *
 * To keep the file manageable, screenshots live in
 * /cms/wp-content/uploads/screenshots/ — upload via the Media Library
 * and reference by filename.
 *
 * @package getsocietypress
 * @version 0.04d
 */

defined( 'ABSPATH' ) || exit;

get_header();

/*
 * Screenshot catalog. Each module is a group; each screenshot has
 * 'file', 'caption', and optional 'context' (admin/public/mobile).
 * Empty inner arrays are fine — those modules render their header
 * with a "no screenshots yet" note, not blank space.
 *
 * Filenames are resolved against wp-content/uploads/screenshots/.
 */
$gsp_screenshots = array(

    array(
        'slug'        => 'dashboard',
        'name'        => 'Dashboard',
        'description' => 'The landing screen a society admin sees after logging in. Stat cards, upcoming events, expiring memberships, recent signups.',
        'shots'       => array(),
    ),

    array(
        'slug'        => 'members',
        'name'        => 'Member Management',
        'description' => 'Member list, directory, CSV import, tier management, and the self-service member portal.',
        'shots'       => array(),
    ),

    array(
        'slug'        => 'events',
        'name'        => 'Events &amp; Calendar',
        'description' => 'Event creation, category management, online registration, waitlists, and the public calendar view.',
        'shots'       => array(),
    ),

    array(
        'slug'        => 'records',
        'name'        => 'Genealogical Records',
        'description' => 'Record collections, GENRECORD and GEDCOM import/export, surname databases, full-text search.',
        'shots'       => array(),
    ),

    array(
        'slug'        => 'library',
        'name'        => 'Library',
        'description' => 'OPAC-style catalog, call numbers and shelf locations, patron-facing search, Open Library cover images.',
        'shots'       => array(),
    ),

    array(
        'slug'        => 'pagebuilder',
        'name'        => 'Page Builder',
        'description' => '21 drag-and-drop widgets. The design system with live preview. Every public page is built this way.',
        'shots'       => array(),
    ),

    array(
        'slug'        => 'donations',
        'name'        => 'Donations &amp; Store',
        'description' => 'Donation forms, campaign tracking, store catalog, and the Stripe/PayPal integration flow.',
        'shots'       => array(),
    ),

    array(
        'slug'        => 'themes',
        'name'        => 'Child Themes',
        'description' => 'The five bundled child themes — Heritage, Coastline, Prairie, Ledger, Parlor — and the theme picker.',
        'shots'       => array(),
    ),

);

// Count total shots to drive the overall empty state.
$total_shots = 0;
foreach ( $gsp_screenshots as $group ) {
    $total_shots += count( $group['shots'] );
}
?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <h1 class="page-hero__title">Screenshots</h1>
            <p class="page-hero__subtitle">
                A visual tour of SocietyPress &mdash; admin and public
                surfaces, organized by module.
            </p>
        </div>
    </div>
</section>

<?php if ( $total_shots === 0 ) : ?>

<!-- Overall empty state — renders until the first screenshots land -->
<section class="screenshots-empty section">
    <div class="container container--narrow">

        <div class="screenshots-empty__content">

            <div class="screenshots-empty__icon" aria-hidden="true">
                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                </svg>
            </div>

            <h2>Being assembled</h2>

            <p>
                We'd rather show you a working product than a picture of
                one. The live demo at
                <a href="https://demo.getsocietypress.org">demo.getsocietypress.org</a>
                is fully functional &mdash; log in, click around, see it
                all for yourself.
            </p>

            <p>
                Curated screenshots will land here as the documentation
                site fills out. In the meantime, the sections below
                describe what each module looks like and what it does.
            </p>

            <div class="screenshots-empty__actions">
                <a href="https://demo.getsocietypress.org" class="btn btn-primary btn-lg" target="_blank" rel="noopener">
                    Visit the Demo
                </a>
                <a href="<?php echo esc_url( home_url( '/features/' ) ); ?>" class="btn btn-secondary btn-lg">
                    Read the Feature Tour
                </a>
            </div>

        </div>

    </div>
</section>

<?php endif; ?>

<!-- Module sections — render whether or not shots exist -->
<section class="screenshots-modules section">
    <div class="container">

        <?php foreach ( $gsp_screenshots as $group ) : ?>
            <div class="screenshots-module" id="<?php echo esc_attr( $group['slug'] ); ?>">

                <header class="screenshots-module__header">
                    <h2 class="screenshots-module__name"><?php echo wp_kses_post( $group['name'] ); ?></h2>
                    <p class="screenshots-module__description"><?php echo wp_kses_post( $group['description'] ); ?></p>
                </header>

                <?php if ( ! empty( $group['shots'] ) ) : ?>
                    <div class="screenshots-module__grid">
                        <?php foreach ( $group['shots'] as $shot ) :
                            $src = content_url( '/uploads/screenshots/' . $shot['file'] );
                        ?>
                            <figure class="screenshot-card">
                                <a href="<?php echo esc_url( $src ); ?>" target="_blank" rel="noopener">
                                    <img src="<?php echo esc_url( $src ); ?>" alt="<?php echo esc_attr( $shot['caption'] ); ?>" loading="lazy">
                                </a>
                                <figcaption>
                                    <?php echo esc_html( $shot['caption'] ); ?>
                                    <?php if ( ! empty( $shot['context'] ) ) : ?>
                                        <span class="screenshot-card__context"><?php echo esc_html( $shot['context'] ); ?></span>
                                    <?php endif; ?>
                                </figcaption>
                            </figure>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p class="screenshots-module__pending">
                        <em>Screenshots pending. In the meantime,
                        <a href="https://demo.getsocietypress.org" target="_blank" rel="noopener">see this module live on the demo</a>.</em>
                    </p>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>

    </div>
</section>

<?php get_footer(); ?>
