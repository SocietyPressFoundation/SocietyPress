<?php
/**
 * Showcase Page Template (page-showcase.php)
 *
 * Two audiences on one page:
 *   (1) Potential users who want to see what SocietyPress looks like in the
 *       wild — the 5 bundled child themes stand in until real society sites
 *       are live to feature.
 *   (2) Existing societies running SocietyPress who want to be featured
 *       once the real showcase lights up.
 *
 * Sections:
 * 1. Hero — "Showcase"
 * 2. Child Theme Gallery — 5 bundled looks, all included in the download
 * 3. Society Sites (coming soon) — invitation to be featured
 *
 * @package getsocietypress
 * @version 0.03d
 */

defined( 'ABSPATH' ) || exit;

get_header();

/*
 * Child theme catalog. Order matters: Heritage is the default and leads.
 * Each entry's "palette" array is rendered as swatches so visitors can see
 * the color story at a glance without waiting for screenshot renders.
 */
$gsp_child_themes = array(
    array(
        'slug'        => 'heritage',
        'name'        => 'Heritage',
        'tagline'     => 'Classic, dignified, archival.',
        'description' => 'A warm cream backdrop with navy type and a serif display face. Designed for societies that want the feel of a well-kept historical journal. The default recommendation for most genealogical societies.',
        'palette'     => array( '#FDF8EE', '#1F2E4A', '#8B6B3D', '#6B3E1F', '#2D2D2D' ),
        'font'        => 'Playfair Display + Lora',
    ),
    array(
        'slug'        => 'coastline',
        'name'        => 'Coastline',
        'tagline'     => 'Airy, open, welcoming.',
        'description' => 'Cool blues, generous whitespace, and a clean sans-serif. A modern look for coastal, maritime, or waterway-focused societies — or any group that wants their site to feel approachable and contemporary.',
        'palette'     => array( '#F4F8FB', '#1D4E6B', '#4A90B8', '#E8B547', '#2D2D2D' ),
        'font'        => 'Inter + Source Sans Pro',
    ),
    array(
        'slug'        => 'prairie',
        'name'        => 'Prairie',
        'tagline'     => 'Earthy, rooted, regional.',
        'description' => 'Warm greens and tans evoking open country. A natural fit for plains, prairie, agricultural, or rural-heritage societies. Comfortable and unpretentious.',
        'palette'     => array( '#F5F1E8', '#3A5C3F', '#A67C52', '#8B5A3C', '#2D2D2D' ),
        'font'        => 'Merriweather + Open Sans',
    ),
    array(
        'slug'        => 'ledger',
        'name'        => 'Ledger',
        'tagline'     => 'Editorial, disciplined, archival.',
        'description' => 'A newspaper-of-record feel — structured columns, precise typography, the look of a scholarly journal. Suited to societies that publish quarterlies or maintain serious research archives.',
        'palette'     => array( '#FAFAF7', '#1A1A1A', '#8B0000', '#C49B6C', '#4A4A4A' ),
        'font'        => 'Lora + Inter',
    ),
    array(
        'slug'        => 'parlor',
        'name'        => 'Parlor',
        'tagline'     => 'Stately, traditional, formal.',
        'description' => 'Deep plum and gold on cream, with an old-world serif. Fits state societies, lineage societies (DAR, SAR, Colonial Dames), and historical societies with a formal membership tradition.',
        'palette'     => array( '#F7F2EA', '#4A2C3E', '#B8935F', '#6B2C3F', '#2D2D2D' ),
        'font'        => 'Cormorant Garamond + Raleway',
    ),
);
?>

<!-- ==========================================================================
     1. SHOWCASE HERO
     ========================================================================== -->
<section class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <h1 class="page-hero__title">Showcase</h1>
            <p class="page-hero__subtitle">
                Five ready-to-use looks included in every download &mdash;
                plus real society sites, as they come online.
            </p>
        </div>
    </div>
</section>


<!-- ==========================================================================
     2. CHILD THEME GALLERY
     Each card shows the theme's name, palette, typography pairing, and a
     short description of who it suits. No real screenshots yet; palette
     chips carry the design story until screenshots exist.
     ========================================================================== -->
<section class="showcase-themes section">
    <div class="container">

        <div class="section-header">
            <h2>Five Looks. One Download.</h2>
            <p>
                Every SocietyPress download ships with five child themes, each
                with a distinct palette and typography pairing. Pick one from the
                themes panel and activate &mdash; no code, no separate purchase,
                no extra install. Prefer your own colors? Tune any of them from
                the design panel, or start with the base SocietyPress theme and
                build from scratch.
            </p>
        </div>

        <div class="showcase-themes__grid">

            <?php foreach ( $gsp_child_themes as $theme ) : ?>
                <div class="showcase-theme-card">

                    <!-- Palette preview — visual identity at a glance -->
                    <div class="showcase-theme-card__palette" aria-hidden="true">
                        <?php foreach ( $theme['palette'] as $color ) : ?>
                            <span class="showcase-theme-card__swatch" style="background-color: <?php echo esc_attr( $color ); ?>;"></span>
                        <?php endforeach; ?>
                    </div>

                    <div class="showcase-theme-card__body">
                        <h3 class="showcase-theme-card__name"><?php echo esc_html( $theme['name'] ); ?></h3>
                        <p class="showcase-theme-card__tagline"><?php echo esc_html( $theme['tagline'] ); ?></p>
                        <p class="showcase-theme-card__description"><?php echo esc_html( $theme['description'] ); ?></p>

                        <dl class="showcase-theme-card__meta">
                            <dt>Typography</dt>
                            <dd><?php echo esc_html( $theme['font'] ); ?></dd>
                        </dl>
                    </div>

                </div>
            <?php endforeach; ?>

        </div>

        <div class="showcase-themes__footnote">
            <p>
                Can't decide? Start with Heritage &mdash; it's the most broadly
                useful of the five, and you can switch themes at any time without
                losing content.
            </p>
        </div>

    </div>
</section>


<!-- ==========================================================================
     3. SOCIETY SITES — coming soon, with a clear path to be featured
     ========================================================================== -->
<section class="showcase-sites section">
    <div class="container container--narrow">

        <div class="showcase-sites__content">

            <div class="showcase-sites__icon" aria-hidden="true">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                    <line x1="8" y1="21" x2="16" y2="21"/>
                    <line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
            </div>

            <h2>Society Sites, Coming Soon</h2>

            <p>
                Real societies are spinning up SocietyPress installations right now.
                As they go live, their sites will appear here &mdash; complete with
                a short write-up of how they're using the platform.
            </p>

            <p>
                If your society is running SocietyPress and you'd like to be featured,
                get in touch. We love seeing what societies build.
            </p>

            <div class="showcase-sites__actions">
                <a href="<?php echo esc_url( home_url( '/community/' ) ); ?>" class="btn btn-primary btn-lg">
                    Submit Your Site
                </a>
                <a href="<?php echo esc_url( home_url( '/download/' ) ); ?>" class="btn btn-secondary btn-lg">
                    Download SocietyPress
                </a>
            </div>
        </div>

    </div>
</section>

<?php get_footer(); ?>
