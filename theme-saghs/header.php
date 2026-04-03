<?php
/**
 * the society Child Theme — Header
 *
 * WHY this overrides the parent: the society uses a white header (not dark), a larger
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
 * @package the society
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

<a class="skip-to-main" href="#main-content"><?php esc_html_e( 'Skip to main content', 'societypress' ); ?></a>

<div class="site">
    <header class="site-header">

        <div class="header-inner">

            <!-- Social media icons — large, brand-colored, top-right of header.
                 WHY here: They sit in the vertical space above the nav row, using
                 the height the logo provides. Absolutely positioned so they don't
                 affect the logo/nav flexbox layout. -->
            <?php if ( function_exists( 'sp_social_icons' ) ) { sp_social_icons(); } ?>

            <!-- Site branding: the society logo and/or site title -->
            <?php
            // WHY we read this setting here: The admin can toggle the site
            // title/tagline on or off from Settings → Design. Default is on
            // (1) so existing sites keep their text. When the logo already
            // includes the society name, showing it again as text is redundant.
            $sp_settings       = get_option( 'societypress_settings', [] );
            $show_header_title = (int) ( $sp_settings['design_show_header_title'] ?? 1 );
            ?>
            <div class="site-branding">
                <?php if ( has_custom_logo() ) : ?>
                    <?php the_custom_logo(); ?>
                <?php endif; ?>

                <?php if ( $show_header_title ) : ?>
                <?php
                // WHY: Only the front page gets <h1> for the site title. Interior
                // pages use <p> so the page title can be the sole <h1> — proper
                // document structure for screen readers and SEO.
                $title_tag = is_front_page() ? 'h1' : 'p';
                ?>
                <div>
                    <<?php echo $title_tag; ?> class="site-title">
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                            <?php bloginfo( 'name' ); ?>
                        </a>
                    </<?php echo $title_tag; ?>>
                    <?php
                    $description = get_bloginfo( 'description', 'display' );
                    if ( $description ) :
                    ?>
                        <p class="site-description"><?php echo esc_html( $description ); ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <!--
                WHY the nav area wraps hamburger + nav + user menu: On desktop,
                the nav and user menu sit side by side. On mobile, the hamburger
                replaces the nav, and the user menu stays visible. Wrapping them
                together lets flexbox handle the alignment.
            -->
            <div class="header-nav-area">

                <!-- Hamburger toggle — visible on mobile only (CSS hides on desktop) -->
                <button class="society-hamburger" aria-label="<?php esc_attr_e( 'Toggle navigation menu', 'society' ); ?>" aria-expanded="false">
                    <span class="society-hamburger-bar"></span>
                    <span class="society-hamburger-bar"></span>
                    <span class="society-hamburger-bar"></span>
                </button>

                <!-- Primary navigation with 3-level dropdown support -->
                <?php if ( has_nav_menu( 'primary' ) ) : ?>
                <nav class="main-navigation" aria-label="Primary navigation">
                    <?php
                    // WHY the search form is inside the nav on mobile: When the
                    // hamburger opens the nav panel, we want the search field at
                    // the top of that panel. On desktop, this form is hidden via
                    // CSS (.sp-header-search-mobile) and the inline version shows
                    // instead. This avoids duplicating the form in JS.
                    if ( function_exists( 'sp_get_search_page_url' ) ) : ?>
                    <form class="sp-header-search sp-header-search-mobile" action="<?php echo esc_url( sp_get_search_page_url() ); ?>" method="get">
                        <input type="text" name="sp_q" placeholder="<?php esc_attr_e( 'Search…', 'societypress' ); ?>" aria-label="<?php esc_attr_e( 'Search the site', 'societypress' ); ?>" autocomplete="off" required minlength="2">
                        <button type="submit" aria-label="<?php esc_attr_e( 'Search', 'societypress' ); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </button>
                    </form>
                    <?php endif; ?>

                    <?php
                    wp_nav_menu([
                        'theme_location' => 'primary',
                        'container'      => false,
                        'depth'          => 3, // the society needs 3-level dropdowns
                        'walker'         => new the society_Nav_Walker(),
                    ]);
                    ?>
                </nav>
                <?php endif; ?>

                <!-- Site search — desktop only (inline in nav bar).
                     WHY a separate form: On desktop, the search field sits inline
                     with the nav and user menu. On mobile, the hamburger panel
                     has its own copy above the nav links. CSS toggles visibility. -->
                <?php if ( function_exists( 'sp_get_search_page_url' ) ) : ?>
                <form class="sp-header-search sp-header-search-desktop" action="<?php echo esc_url( sp_get_search_page_url() ); ?>" method="get">
                    <input type="text" name="sp_q" placeholder="<?php esc_attr_e( 'Search…', 'societypress' ); ?>" aria-label="<?php esc_attr_e( 'Search the site', 'societypress' ); ?>" autocomplete="off" required minlength="2">
                    <button type="submit" aria-label="<?php esc_attr_e( 'Search', 'societypress' ); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </button>
                </form>
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
