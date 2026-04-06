<?php
/**
 * Ledger Child Theme — Header
 *
 * WHY this overrides the parent: Ledger uses a full-width sticky horizontal
 * nav with 2-level dropdown support — the "dashboard" layout pattern. The
 * parent header uses depth:1 navigation and a different layout structure.
 * This is a complete replacement that provides:
 *
 * - Sticky charcoal top bar stretching full viewport width
 * - Logo + site title on the left
 * - Horizontal nav with dropdown submenus + search + user menu on the right
 * - Hamburger menu on mobile with collapsible submenu toggles
 * - Skip link for keyboard/screen reader accessibility (WCAG 2.4.1)
 *
 * @package Ledger
 * @since   1.1.0
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

<!-- Skip link: lets keyboard and screen reader users jump straight to the
     page content without tabbing through the entire header and nav. This is
     a WCAG 2.1 Level A requirement (Success Criterion 2.4.1). The link is
     visually hidden until it receives focus via Tab. -->
<a href="#main-content" class="skip-to-main"><?php esc_html_e( 'Skip to main content', 'ledger' ); ?></a>

<div class="site">
    <header class="ledger-header">

        <div class="ledger-header-inner">

            <!-- Site branding: logo and/or site title -->
            <?php
            // WHY we read this setting here: The admin can toggle the site
            // title/tagline on or off from Settings -> Design. Default is on
            // (1) so existing sites keep their text. When the logo already
            // includes the society name, showing it again as text is redundant.
            $sp_settings       = get_option( 'societypress_settings', [] );
            $show_header_title = (int) ( $sp_settings['design_show_header_title'] ?? 1 );
            ?>
            <div class="site-branding">
                <?php
                if ( has_custom_logo() ) :
                    the_custom_logo();
                else :
                    // WHY: Fall back to a logo file bundled in the child theme.
                    // This lets child themes ship with a default logo that works
                    // out of the box, without requiring Harold to upload anything.
                    $child_logo = '';
                    foreach ( [ 'img/logo.svg', 'img/logo.png' ] as $logo_file ) {
                        if ( file_exists( get_stylesheet_directory() . '/' . $logo_file ) ) {
                            $child_logo = get_stylesheet_directory_uri() . '/' . $logo_file;
                            break;
                        }
                    }
                    if ( $child_logo ) :
                ?>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="custom-logo-link" rel="home">
                        <img src="<?php echo esc_url( $child_logo ); ?>" class="custom-logo" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
                    </a>
                <?php
                    endif;
                endif;
                ?>

                <?php if ( $show_header_title ) : ?>
                <div>
                    <?php
                    // WHY conditional heading: Only the front page should have
                    // the site name as <h1>. On all other pages, the page's own
                    // title is the <h1>. Having two <h1> elements breaks the
                    // heading hierarchy for screen reader users (WCAG 2.4.6).
                    $title_tag = is_front_page() ? 'h1' : 'p';
                    ?>
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
                WHY the nav area wraps hamburger + nav + search + user menu: On
                desktop, the nav and user menu sit side by side, pushed right via
                margin-left:auto. On mobile, the hamburger replaces the inline
                nav, and search + user menu stay visible.
            -->
            <div class="ledger-nav-area">

                <!-- Hamburger toggle — visible on mobile only (CSS hides on desktop).
                     WHY three bars: The three-bar hamburger icon is universally
                     recognized as "menu." JS toggles ledger-nav-open on the nav and
                     is-active on this button, which CSS uses to animate the bars
                     into an X and reveal the mobile nav panel. -->
                <button class="ledger-hamburger" aria-label="<?php esc_attr_e( 'Toggle navigation menu', 'ledger' ); ?>" aria-expanded="false">
                    <span class="ledger-hamburger-bar"></span>
                    <span class="ledger-hamburger-bar"></span>
                    <span class="ledger-hamburger-bar"></span>
                </button>

                <!-- Primary navigation menu with 2-level dropdown support -->
                <?php if ( has_nav_menu( 'primary' ) ) : ?>
                <nav class="ledger-navigation" aria-label="<?php esc_attr_e( 'Primary navigation', 'ledger' ); ?>">
                    <?php
                    wp_nav_menu( [
                        'theme_location' => 'primary',
                        'container'      => false,
                        'depth'          => 2,
                        'walker'         => new Ledger_Nav_Walker(),
                    ] );
                    ?>
                </nav>
                <?php endif; ?>

                <!-- Site search — click-to-expand dropdown in the header bar.
                     WHY dropdown: A persistent text input takes up valuable nav
                     space, especially on tablets. The magnifying glass icon is
                     universally recognized as "search." Clicking it reveals the
                     input field; clicking away or pressing Escape closes it. -->
                <?php if ( function_exists( 'sp_get_search_page_url' ) ) : ?>
                <div class="sp-header-search-wrap">
                    <button type="button" class="sp-search-toggle" aria-label="<?php esc_attr_e( 'Open search', 'ledger' ); ?>" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </button>
                    <form class="sp-header-search" action="<?php echo esc_url( sp_get_search_page_url() ); ?>" method="get">
                        <input type="text" name="sp_q" placeholder="<?php esc_attr_e( 'Search&hellip;', 'ledger' ); ?>" aria-label="<?php esc_attr_e( 'Search the site', 'ledger' ); ?>" autocomplete="off" required minlength="2">
                        <button type="submit" aria-label="<?php esc_attr_e( 'Search', 'ledger' ); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </button>
                    </form>
                </div>
                <?php endif; ?>

                <!-- User account menu (replaces the admin bar) -->
                <?php
                if ( function_exists( 'sp_user_menu' ) ) {
                    sp_user_menu();
                }
                ?>
            </div>

        </div>
    </header>
