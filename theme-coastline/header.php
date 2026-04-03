<?php
/**
 * Coastline Child Theme — Header
 *
 * WHY: Overrides the parent header to create a centered masthead layout.
 * The parent theme puts logo left / nav right. Coastline's magazine archetype
 * centers the logo at top with the nav bar below — like a newspaper masthead.
 *
 * This template preserves ALL parent header functionality:
 * - Skip-to-main-content link (WCAG 2.4.1)
 * - Custom logo / child theme fallback logo
 * - Site title and description (controlled by admin toggle)
 * - Hamburger menu for mobile
 * - Primary navigation menu
 * - Social media icons
 * - Search dropdown
 * - User account menu
 *
 * @package Coastline
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
     a WCAG 2.1 Level A requirement (Success Criterion 2.4.1). -->
<a href="#main-content" class="skip-to-main"><?php esc_html_e( 'Skip to main content', 'coastline' ); ?></a>

<div class="site">
    <header class="site-header">

        <div class="header-inner">

            <!-- Site branding: centered logo and/or site title.
                 WHY centered: The magazine archetype uses a centered masthead
                 to give the society's identity maximum visual weight. The CSS
                 in style.css overrides the parent's flexbox to column/center. -->
            <?php
            // WHY we read this setting: The admin can toggle the site title/tagline
            // on or off from Settings > Design. When the logo already includes the
            // society name, showing it again as text is redundant.
            $sp_settings       = get_option( 'societypress_settings', [] );
            $show_header_title = (int) ( $sp_settings['design_show_header_title'] ?? 1 );
            ?>
            <div class="site-branding">
                <?php
                if ( has_custom_logo() ) :
                    // Admin uploaded a logo via Design settings — use it.
                    the_custom_logo();
                else :
                    // WHY: Fall back to a logo file bundled in the child theme.
                    // Checks for logo.svg first, then logo.png in the child theme's
                    // img/ directory. This lets child themes ship with a default logo
                    // that works out of the box.
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
                    // WHY conditional heading: Only the front page should have the
                    // site name as <h1>. On all other pages, the page's own title is
                    // the <h1>. Two <h1> elements breaks heading hierarchy (WCAG 2.4.6).
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

            <!-- Nav area: hamburger + nav links + social + search + user menu.
                 WHY all in one wrapper: Flexbox centers them as a group on
                 desktop. On mobile, the hamburger replaces the nav while search
                 and user menu stay visible. -->
            <div class="header-nav-area">

                <!-- Hamburger toggle — visible on mobile only (CSS hides on desktop). -->
                <button class="sp-hamburger" aria-label="<?php esc_attr_e( 'Toggle navigation menu', 'coastline' ); ?>" aria-expanded="false">
                    <span class="sp-hamburger-bar"></span>
                    <span class="sp-hamburger-bar"></span>
                    <span class="sp-hamburger-bar"></span>
                </button>

                <!-- Primary navigation menu -->
                <?php if ( has_nav_menu( 'primary' ) ) : ?>
                <nav class="main-navigation" aria-label="<?php esc_attr_e( 'Primary navigation', 'coastline' ); ?>">
                    <?php
                    wp_nav_menu( [
                        'theme_location' => 'primary',
                        'container'      => false,
                        'depth'          => 1,
                    ] );
                    ?>
                </nav>
                <?php endif; ?>

                <!-- Social media icons — inline in the nav bar -->
                <?php if ( function_exists( 'sp_social_icons' ) ) { sp_social_icons(); } ?>

                <!-- Site search — click-to-expand dropdown.
                     WHY dropdown: A persistent text input takes up valuable nav
                     space. The magnifying glass icon is universally recognized. -->
                <?php if ( function_exists( 'sp_get_search_page_url' ) ) : ?>
                <div class="sp-header-search-wrap">
                    <button type="button" class="sp-search-toggle" aria-label="<?php esc_attr_e( 'Open search', 'coastline' ); ?>" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </button>
                    <form class="sp-header-search" action="<?php echo esc_url( sp_get_search_page_url() ); ?>" method="get">
                        <input type="text" name="sp_q" placeholder="<?php esc_attr_e( 'Search...', 'coastline' ); ?>" aria-label="<?php esc_attr_e( 'Search the site', 'coastline' ); ?>" autocomplete="off" required minlength="2">
                        <button type="submit" aria-label="<?php esc_attr_e( 'Search', 'coastline' ); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </button>
                    </form>
                </div>
                <?php endif; ?>

                <!-- User account menu (replaces the admin bar) -->
                <?php if ( function_exists( 'sp_user_menu' ) ) { sp_user_menu(); } ?>
            </div>

        </div>
    </header>
