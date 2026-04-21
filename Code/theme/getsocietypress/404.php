<?php
/**
 * 404 Page Template (404.php)
 *
 * Friendly, specific not-found page. The big "most people were looking
 * for..." list covers the most common mistyped URLs and mis-remembered
 * slugs so visitors don't bounce.
 *
 * @package getsocietypress
 * @version 0.04d
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <div class="error-404__code">404</div>
            <h1 class="page-hero__title">Page not found</h1>
            <p class="page-hero__subtitle">
                That URL doesn't exist on getsocietypress.org &mdash; at
                least not anymore. Search or pick a destination below.
            </p>

            <!-- Search form — a real tool, not just decoration. Preserves
                 the attempted URL as a hint seed if the router exposed it. -->
            <form class="error-404__search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                <label for="error-404-s" class="screen-reader-text">Search the site</label>
                <input
                    type="search"
                    id="error-404-s"
                    name="s"
                    placeholder="Search the site&hellip;"
                    value="<?php echo esc_attr( get_search_query() ); ?>"
                    class="error-404__search-input"
                    autofocus
                >
                <button type="submit" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    Search
                </button>
            </form>
        </div>
    </div>
</section>

<section class="error-404 section">
    <div class="container container--narrow">
        <h2 class="error-404__heading">Or jump to one of these</h2>

        <div class="error-404__grid">

            <a class="error-404__card" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <h3>Home</h3>
                <p>The overview &mdash; start here if this is your first visit.</p>
            </a>

            <a class="error-404__card" href="<?php echo esc_url( home_url( '/download/' ) ); ?>">
                <h3>Download</h3>
                <p>Latest release of the SocietyPress plugin and themes.</p>
            </a>

            <a class="error-404__card" href="<?php echo esc_url( home_url( '/docs/installation/' ) ); ?>">
                <h3>Installation Guide</h3>
                <p>Step-by-step setup for a fresh install.</p>
            </a>

            <a class="error-404__card" href="<?php echo esc_url( home_url( '/docs/faq/' ) ); ?>">
                <h3>FAQ</h3>
                <p>Quick answers to the most common questions.</p>
            </a>

            <a class="error-404__card" href="<?php echo esc_url( home_url( '/docs/' ) ); ?>">
                <h3>Documentation</h3>
                <p>Guides for every part of the platform.</p>
            </a>

            <a class="error-404__card" href="<?php echo esc_url( home_url( '/docs/ens-migration/' ) ); ?>">
                <h3>Moving from ENS</h3>
                <p>Migration guide for EasyNetSites societies.</p>
            </a>

            <a class="error-404__card" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
                <h3>Contact</h3>
                <p>All the ways to reach us, routed by topic.</p>
            </a>

            <a class="error-404__card" href="<?php echo esc_url( home_url( '/sitemap/' ) ); ?>">
                <h3>Full Sitemap</h3>
                <p>Every page on the site, grouped by purpose.</p>
            </a>

        </div>

        <p class="error-404__footnote">
            Still lost? <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Email us</a>
            with the URL you were trying to reach. If it's a link from
            somewhere else on the site, we'd like to know about the broken
            link.
        </p>

    </div>
</section>

<?php get_footer(); ?>
