<?php
/**
 * ENS Migration Guide Page Template (page-ens-migration-guide.php)
 *
 * Renders the canonical ENS migration guide (stored as Markdown in
 * docs/ENS-MIGRATION-GUIDE.md on the server) as a styled HTML
 * page. Lives at /docs/ens-migration-guide/ — linked from the
 * /ens-migration/ landing page's "Read the Full Migration Guide" CTA.
 *
 * The Markdown file stays the canonical source so updates land in
 * one place. This template reads it, renders it through a minimal
 * Markdown-to-HTML converter, and caches the result in a transient.
 *
 * @package getsocietypress
 * @version 0.04d
 */

defined( 'ABSPATH' ) || exit;

// gsp_render_simple_markdown() and gsp_md_inline() now live in functions.php
// so other templates (page-module-docs.php) can use them without pulling
// in this entire file.

get_header();

/*
 * Load the guide. Canonical source on the server:
 *   ~/domains/getsocietypress.org/public_html/sp-docs-source/ENS-MIGRATION-GUIDE.md
 *
 * The directory is intentionally not called "docs/" — WordPress owns
 * the /docs/ URL (it's the Documentation hub page), and a real server
 * directory at that path would shadow the WP route and produce a 403.
 * Cached by gsp_markdown_file_html() until the file or the renderer changes.
 */
// Offset 1: the page hero already carries the <h1>, so the markdown's own
// top-level "# Title" becomes <h2> and the rest shifts down accordingly.
$gsp_guide_html = gsp_markdown_file_html( ABSPATH . '../sp-docs-source/ENS-MIGRATION-GUIDE.md', 1 );
if ( null === $gsp_guide_html ) {
    $gsp_guide_html = '<p><em>The migration guide file is temporarily unavailable. Please try again, or email <a href="mailto:migrations@getsocietypress.org">migrations@getsocietypress.org</a> for a copy.</em></p>';
}
?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <nav class="page-breadcrumbs" aria-label="Breadcrumb">
                <a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>">Documentation</a>
                <span aria-hidden="true">&rsaquo;</span>
                <a href="<?php echo esc_url( home_url( '/docs/ens-migration/' ) ); ?>">Moving from ENS</a>
                <span aria-hidden="true">&rsaquo;</span>
                <span>Full Migration Guide</span>
            </nav>
            <h1 class="page-hero__title">ENS to SocietyPress Migration Guide</h1>
            <p class="page-hero__subtitle">
                A step-by-step walkthrough for society webmasters leaving
                EasyNetSites. No development background required.
            </p>
        </div>
    </div>
</section>

<section class="guide-page section">
    <div class="container container--narrow">

        <article class="guide-page__content">
            <?php
            // Output is already HTML we built; allow standard WP-safe tags.
            echo wp_kses_post( $gsp_guide_html );
            ?>
        </article>

        <div class="guide-page__footer">
            <p>
                <strong>See also:</strong>
                <a href="<?php echo esc_url( home_url( '/docs/ens-migration-walkthrough/' ) ); ?>">A migration, start to finish</a> &middot;
                <a href="<?php echo esc_url( home_url( '/docs/ens-migration/' ) ); ?>">ENS migration overview</a> &middot;
                <a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>">Documentation hub</a> &middot;
                <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Migration help</a>
            </p>
        </div>

    </div>
</section>

<?php get_footer(); ?>
