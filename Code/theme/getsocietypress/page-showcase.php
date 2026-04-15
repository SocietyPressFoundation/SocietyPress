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
        'tagline'     => 'Warm, traditional, scholarly.',
        'description' => 'Rich browns, soft cream, and antique gold — inspired by old library stacks and leather-bound journals. Classic single-column layout with generous whitespace. The default recommendation for genealogical societies that want to feel established and scholarly.',
        /* Real values pulled from heritage/style.css (--heritage-*) */
        'palette'     => array( '#FDF6EC', '#3E2723', '#B8860B', '#D4C5A9', '#8B7355' ),
        'font'        => 'Merriweather (serif)',
    ),
    array(
        'slug'        => 'coastline',
        'name'        => 'Coastline',
        'tagline'     => 'Clean, modern, magazine-style.',
        'description' => 'Navy and white with soft sky-blue accents. Magazine-style layout with a right sidebar on desktop that collapses beneath content on mobile. Ideal for active societies with a lot happening — multiple monthly events, an ongoing record project, a busy newsletter.',
        /* Real values from coastline/style.css (--coast-*) */
        'palette'     => array( '#FFFFFF', '#1B3A5C', '#5B9BD5', '#EFF6FC', '#F5F0EB' ),
        'font'        => 'Inter (sans-serif)',
    ),
    array(
        'slug'        => 'prairie',
        'name'        => 'Prairie',
        'tagline'     => 'Earthy, rooted, research-heavy.',
        'description' => 'Forest greens, warm wheat, and clay accents. Explorer layout with a permanent left sidebar navigation — built for content-heavy societies with deep page structures. Big libraries, lots of records, multi-level research guides.',
        /* Real values from prairie/style.css (--prairie-*) */
        'palette'     => array( '#E8DCC8', '#2D5016', '#7A9A5E', '#C4A265', '#5C4033' ),
        'font'        => 'Lora (serif)',
    ),
    array(
        'slug'        => 'ledger',
        'name'        => 'Ledger',
        'tagline'     => 'Formal, archival, authoritative.',
        'description' => 'Charcoal and ivory with a deep burgundy accent — evokes courthouses, ledger books, and official records. Dashboard-style theme with card-based layouts and clean, professional spacing. For societies that want to project authority and permanence.',
        /* Real values from ledger/style.css (--ledger-*) */
        'palette'     => array( '#F8F5F0', '#2C2C2C', '#7B2D3B', '#D4D0CB', '#808080' ),
        'font'        => 'Source Sans 3 (sans-serif)',
    ),
    array(
        'slug'        => 'parlor',
        'name'        => 'Parlor',
        'tagline'     => 'Elegant, refined, ENS-familiar.',
        'description' => 'Deep plum, warm ivory, and rose-gold accents. Traditional layout — centered banner, horizontal nav, optional right sidebar, "Next Meeting" callout at the top of the homepage. Designed so ENS migrants immediately feel at home.',
        /* Real values from parlor/style.css (--parlor-*) */
        'palette'     => array( '#FFF8F0', '#3C1053', '#B76E79', '#E8C4C4', '#8B6F8B' ),
        'font'        => 'EB Garamond (serif)',
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
