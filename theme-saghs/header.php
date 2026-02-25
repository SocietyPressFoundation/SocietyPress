<?php
/**
 * SAGHS Child Theme — Header
 *
 * WHY this overrides the parent: SAGHS uses a white header (not dark), a larger
 * logo, 3-level dropdown navigation, and a hamburger menu on mobile. The parent
 * header has depth:1 and no hamburger, so we need a complete replacement.
 *
 * Layout:
 * - White background with burgundy bottom border
 * - Logo (left) + site title/description
 * - Horizontal nav (right) with 3-level dropdowns
 * - Hamburger icon (mobile only)
 * - User account menu from plugin (sp_user_menu)
 *
 * @package SAGHS
 * @since   0.01d
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="site">
    <header class="site-header">
        <div class="header-inner">

            <!-- Site branding: SAGHS logo and/or site title -->
            <div class="site-branding">
                <?php if ( has_custom_logo() ) : ?>
                    <?php the_custom_logo(); ?>
                <?php endif; ?>

                <div>
                    <h1 class="site-title">
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                            <?php bloginfo( 'name' ); ?>
                        </a>
                    </h1>
                    <?php
                    $description = get_bloginfo( 'description', 'display' );
                    if ( $description ) :
                    ?>
                        <p class="site-description"><?php echo $description; ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!--
                WHY the nav area wraps hamburger + nav + user menu: On desktop,
                the nav and user menu sit side by side. On mobile, the hamburger
                replaces the nav, and the user menu stays visible. Wrapping them
                together lets flexbox handle the alignment.
            -->
            <div class="header-nav-area">

                <!-- Hamburger toggle — visible on mobile only (CSS hides on desktop) -->
                <button class="saghs-hamburger" aria-label="Toggle navigation menu" aria-expanded="false">
                    <span class="saghs-hamburger-bar"></span>
                    <span class="saghs-hamburger-bar"></span>
                    <span class="saghs-hamburger-bar"></span>
                </button>

                <!-- Primary navigation with 3-level dropdown support -->
                <?php if ( has_nav_menu( 'primary' ) ) : ?>
                <nav class="main-navigation" aria-label="Primary navigation">
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'primary',
                        'container'      => false,
                        'depth'          => 3, // SAGHS needs 3-level dropdowns
                        'walker'         => new SAGHS_Nav_Walker(),
                    ]);
                    ?>
                </nav>
                <?php endif; ?>

                <!-- User account menu — rendered by the SocietyPress plugin -->
                <?php
                if ( function_exists( 'sp_user_menu' ) ) {
                    sp_user_menu();
                }
                ?>
            </div>

        </div>
    </header>
