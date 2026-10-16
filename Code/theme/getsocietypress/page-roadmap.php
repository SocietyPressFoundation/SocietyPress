<?php
/**
 * Public Roadmap Page Template (page-roadmap.php)
 *
 * Renders the canonical ROADMAP.md from the repo (synced via the
 * marketing deploy to sp-docs-source/ROADMAP.md). The repo's roadmap
 * is the source of truth — this template surfaces it on the marketing
 * site without drift.
 *
 * @package getsocietypress
 * @version 0.05d
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Strip the document-level H1 ("# SocietyPress Roadmap") since the page
// hero shows it.
$gsp_roadmap_html = gsp_markdown_file_html( ABSPATH . '../sp-docs-source/ROADMAP.md', 0, true );
if ( null === $gsp_roadmap_html ) {
    $gsp_roadmap_html = '<p><em>The roadmap is temporarily unavailable. The current state is on <a href="https://github.com/SocietyPressFoundation/SocietyPress/blob/main/ROADMAP.md">GitHub</a>.</em></p>';
}
?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <nav class="page-breadcrumbs" aria-label="Breadcrumb">
                <a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>">Documentation</a>
                <span aria-hidden="true">&rsaquo;</span>
                <span>Roadmap</span>
            </nav>
            <h1 class="page-hero__title">Roadmap</h1>
            <p class="page-hero__subtitle">
                What we're working on, what's next, what's on the back burner.
                Public, organized by theme rather than by date — solo-project
                delivery dates are notoriously unreliable.
            </p>
        </div>
    </div>
</section>

<section class="guide-page section">
    <div class="container container--narrow">
        <article class="guide-page__content md-article">
            <?php echo wp_kses_post( $gsp_roadmap_html ); ?>
        </article>

        <div class="guide-page__footer">
            <p>
                <strong>See also:</strong>
                <a href="<?php echo esc_url( home_url( '/docs/changelog/' ) ); ?>">Changelog</a> &middot;
                <a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>">Documentation hub</a> &middot;
                <a href="https://github.com/SocietyPressFoundation/SocietyPress/issues">GitHub Issues</a>
            </p>
        </div>
    </div>
</section>

<?php get_footer(); ?>
