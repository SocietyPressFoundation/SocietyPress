<?php
/**
 * ENS Migration Walkthrough Page Template (page-ens-migration-walkthrough.php)
 *
 * Renders docs/ENS-MIGRATION-WALKTHROUGH.md — one complete ENS import, run
 * on a clean install, with its real numbers and screenshots — at
 * /docs/ens-migration-walkthrough/. The guide says what to do; this page
 * shows what happens when somebody does it.
 *
 * The Markdown stays the canonical source, synced to the server by
 * `./scripts/deploy.sh marketing`, so the page and the GitHub copy never
 * disagree. Screenshots live in images/walkthrough/ in this theme.
 *
 * @package getsocietypress
 */

defined( 'ABSPATH' ) || exit;

get_header();

// The hero carries the <h1>, so the doc's own "# Title" is dropped and its
// "##" sections render as the page's <h2>s.
$gsp_walkthrough_html = gsp_markdown_file_html( ABSPATH . '../sp-docs-source/ENS-MIGRATION-WALKTHROUGH.md', 0, true );
if ( null === $gsp_walkthrough_html ) {
    $gsp_walkthrough_html = '<p><em>The walkthrough is temporarily unavailable. It is also on <a href="https://github.com/SocietyPressFoundation/SocietyPress/blob/main/docs/ENS-MIGRATION-WALKTHROUGH.md">GitHub</a>.</em></p>';
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
                <span>A Migration, Start to Finish</span>
            </nav>
            <h1 class="page-hero__title">A Migration, Start to Finish</h1>
            <p class="page-hero__subtitle">
                One complete EasyNetSites import on a clean SocietyPress site,
                with the real numbers and every screen along the way.
            </p>
        </div>
    </div>
</section>

<section class="guide-page section">
    <div class="container container--narrow">

        <article class="guide-page__content">
            <?php
            // Output is already HTML we built; allow standard WP-safe tags.
            echo wp_kses_post( $gsp_walkthrough_html );
            ?>
        </article>

        <div class="guide-page__footer">
            <p>
                <strong>See also:</strong>
                <a href="<?php echo esc_url( home_url( '/docs/ens-migration-guide/' ) ); ?>">Full migration guide</a> &middot;
                <a href="<?php echo esc_url( home_url( '/docs/ens-migration/' ) ); ?>">ENS migration overview</a> &middot;
                <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Migration help</a>
            </p>
        </div>

    </div>
</section>

<?php get_footer(); ?>
