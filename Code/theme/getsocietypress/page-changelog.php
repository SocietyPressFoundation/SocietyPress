<?php
/**
 * Changelog Page Template (page-changelog.php)
 *
 * Renders the canonical CHANGELOG.md from the repo (synced via the
 * marketing deploy to sp-docs-source/CHANGELOG.md). Single source of
 * truth — every release ships with a changelog entry; this template
 * just surfaces them on the marketing site.
 *
 * @package getsocietypress
 * @version 0.04d
 */

defined( 'ABSPATH' ) || exit;

get_header();

$gsp_changelog_path = ABSPATH . '../sp-docs-source/CHANGELOG.md';
$gsp_changelog_html = get_transient( 'gsp_changelog_html_v2' );

if ( false === $gsp_changelog_html ) {
    if ( is_readable( $gsp_changelog_path ) ) {
        $raw = file_get_contents( $gsp_changelog_path );
        // Strip the document-level H1 ("# Changelog") since the page hero shows it.
        $raw = preg_replace( '/^# .+?\n+/', '', $raw, 1 );
        $gsp_changelog_html = gsp_render_simple_markdown( $raw, 0 );
    } else {
        $gsp_changelog_html = '<p><em>The changelog is temporarily unavailable. The full release history is on <a href="https://github.com/SocietyPressFoundation/SocietyPress/blob/main/CHANGELOG.md">GitHub</a>.</em></p>';
    }
    set_transient( 'gsp_changelog_html_v2', $gsp_changelog_html, HOUR_IN_SECONDS );
}
?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <nav class="page-breadcrumbs" aria-label="Breadcrumb">
                <a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>">Documentation</a>
                <span aria-hidden="true">&rsaquo;</span>
                <span>Changelog</span>
            </nav>
            <h1 class="page-hero__title">Changelog</h1>
            <p class="page-hero__subtitle">
                Every release of SocietyPress, what changed, and why. The
                <strong>[Unreleased]</strong> block at the top is what's landed
                in the development branch since the last tagged release.
            </p>
        </div>
    </div>
</section>

<section class="guide-page section">
    <div class="container container--narrow">
        <article class="guide-page__content md-article">
            <?php echo wp_kses_post( $gsp_changelog_html ); ?>
        </article>

        <div class="guide-page__footer">
            <p>
                <strong>See also:</strong>
                <a href="<?php echo esc_url( home_url( '/docs/roadmap/' ) ); ?>">What's coming next</a> &middot;
                <a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>">Documentation hub</a> &middot;
                <a href="https://github.com/SocietyPressFoundation/SocietyPress/releases">GitHub Releases</a>
            </p>
        </div>
    </div>
</section>

<?php get_footer(); ?>
