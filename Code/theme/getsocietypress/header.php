<?php
/**
 * Site Header
 *
 * Contains the opening HTML, <head> section, announcement bar, and the
 * sticky navigation with logo, centered links, and right-side actions.
 *
 * The hamburger menu and mobile nav overlay are also here — they're hidden
 * on desktop and revealed via CSS at the 768px breakpoint.
 *
 * @package getsocietypress
 * @version 0.02d
 */

defined( 'ABSPATH' ) || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php bloginfo( 'description' ); ?>">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Skip-to-content link — first focusable element, visually hidden until
     focused via keyboard. Lets screen-reader and keyboard users bypass
     the nav and go straight to the page content. -->
<a class="skip-link" href="#main-content">Skip to main content</a>

<!-- ==========================================================================
     ANNOUNCEMENT BAR
     Pulled from Appearance > Customize > Site Announcement.
     Only renders when the toggle is on AND text exists.
     Dismissible per session via sessionStorage (handled in theme.js).
     ========================================================================== -->
<?php
$announce_enabled = get_theme_mod( 'gsp_announce_enabled', true );
$announce_text    = get_theme_mod( 'gsp_announce_text', '' );

if ( $announce_enabled && ! empty( $announce_text ) ) :
?>
<div class="announce-bar" id="announce-bar">
    <span><?php echo wp_kses_post( $announce_text ); ?></span>
    <button class="announce-bar__dismiss" id="announce-dismiss" aria-label="Dismiss announcement">&times;</button>
</div>
<?php endif; ?>

<!-- ==========================================================================
     SITE HEADER & NAVIGATION
     Sticky nav: logo left, centered links, right-side Download CTA.
     ========================================================================== -->
<header class="site-header" role="banner">
    <div class="site-header__inner">

        <!-- Logo / Site Name -->
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-brand" aria-label="<?php
            /* translators: %s: site name */
            echo esc_attr( sprintf( __( '%s home', 'getsocietypress' ), get_bloginfo( 'name' ) ) );
        ?>">
            <?php if ( has_custom_logo() ) : ?>
                <?php
                /* Output just the <img> tag from the custom logo, no wrapping link */
                $logo_id  = get_theme_mod( 'custom_logo' );
                $logo_img = wp_get_attachment_image( $logo_id, 'full', false, array( 'class' => 'site-brand__logo' ) );
                echo $logo_img;
                ?>
            <?php else : ?>
                <!--
                    Inline SVG logo mark — navy rounded square with gold serif S.
                    Uses CSS custom properties so it stays in sync with the theme palette.
                    If a custom logo is uploaded via Appearance > Customize, this is replaced.
                -->
                <svg class="site-brand__icon" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <rect x="16" y="16" width="480" height="480" rx="68" ry="68" fill="var(--color-accent)"/>
                    <text x="256" y="384" font-family="Georgia, 'Times New Roman', serif" font-size="360" font-weight="700" fill="var(--color-nav-bg)" text-anchor="middle">S</text>
                </svg>
            <?php endif; ?>
            <span class="site-brand__name">Society<span>Press</span></span>
        </a>

        <!-- Primary Navigation — managed from Appearance > Menus -->
        <nav class="primary-nav" role="navigation" aria-label="Primary navigation">
            <?php
            wp_nav_menu( array(
                'theme_location' => 'primary',
                'container'      => false,
                'items_wrap'     => '%3$s',
                'walker'         => new GSP_Nav_Walker(),
                'depth'          => 1,
                'fallback_cb'    => false,
            ) );
            ?>
        </nav>

        <!-- Right-side Action -->
        <div class="nav-actions">
            <a href="<?php echo esc_url( home_url( '/download/' ) ); ?>" class="btn btn-primary btn-sm">Download</a>
        </div>

        <!-- Hamburger Button — visible only on mobile (below 768px) -->
        <button class="hamburger" id="hamburger" aria-label="Toggle navigation" aria-expanded="false">
            <div class="hamburger__lines">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </button>

    </div>
</header>

<!-- ==========================================================================
     MOBILE NAVIGATION OVERLAY
     Full-screen menu revealed when the hamburger is tapped.
     ========================================================================== -->
<nav class="mobile-nav" id="mobile-nav" role="navigation" aria-label="Mobile navigation">
    <?php
    wp_nav_menu( array(
        'theme_location' => 'primary',
        'container'      => false,
        'items_wrap'     => '%3$s',
        'walker'         => new GSP_Nav_Walker(),
        'depth'          => 1,
        'fallback_cb'    => false,
    ) );
    ?>
    <a href="<?php echo esc_url( home_url( '/download/' ) ); ?>" class="btn btn-primary">Download</a>
</nav>

<!-- Main content landmark — wraps all page template output so screen
     readers can jump directly here via the "main" region. Closed in
     footer.php. -->
<main id="main-content" role="main">
