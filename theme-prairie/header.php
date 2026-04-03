<?php
/**
 * Prairie Theme — Compact Header
 *
 * WHY this overrides the parent header: The Explorer layout puts the primary
 * navigation in a left sidebar, so the header only needs to be a slim bar
 * with branding, a few utility links, and the user menu. The parent's
 * header is designed around a full horizontal nav — too much chrome for
 * a sidebar-driven layout.
 *
 * Structure:
 * - Skip link (WCAG 2.4.1)
 * - Compact header bar: logo left, utility links center, search + user menu right
 * - Mobile sidebar toggle button (hamburger)
 *
 * @package Prairie
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
     page content without tabbing through the header and sidebar nav.
     WCAG 2.1 Level A requirement (Success Criterion 2.4.1). -->
<a href="#main-content" class="prairie-skip-link"><?php esc_html_e( 'Skip to main content', 'prairie' ); ?></a>

<div class="site">

    <header class="prairie-header">
        <div class="prairie-header-inner">

            <!-- Sidebar toggle — visible on mobile only.
                 WHY: On mobile the left sidebar hides off-screen. This button
                 slides it in as an overlay panel. Uses a different class than
                 the parent's .sp-hamburger to avoid JS/CSS conflicts. -->
            <button class="prairie-sidebar-toggle" aria-label="<?php esc_attr_e( 'Toggle sidebar navigation', 'prairie' ); ?>" aria-expanded="false">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>

            <!-- Site branding: logo and/or site title.
                 WHY compact: The Explorer layout saves space for the sidebar,
                 so we keep the logo small and skip the tagline. -->
            <?php
            $sp_settings       = get_option( 'societypress_settings', [] );
            $show_header_title = (int) ( $sp_settings['design_show_header_title'] ?? 1 );
            ?>
            <div class="site-branding">
                <?php
                if ( has_custom_logo() ) :
                    the_custom_logo();
                else :
                    /* Fall back to a logo file bundled in the child theme.
                       Checks for logo.svg first, then logo.png — same pattern
                       as the parent theme so child themes are consistent. */
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
                <?php
                /* WHY conditional heading: Only the front page gets <h1> for
                   the site name. On other pages, the page title is the <h1>.
                   Two <h1> elements break heading hierarchy (WCAG 2.4.6). */
                $title_tag = is_front_page() ? 'h1' : 'p';
                ?>
                <<?php echo $title_tag; ?> class="site-title">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                        <?php bloginfo( 'name' ); ?>
                    </a>
                </<?php echo $title_tag; ?>>
                <?php endif; ?>
            </div>

            <!-- Header navigation area: utility links + search + user menu -->
            <div class="prairie-header-nav">

                <!-- Minimal horizontal nav — only a few key links.
                     WHY: The sidebar has the full navigation. The header just
                     needs quick-access links (Home, About, Contact, Login)
                     that users expect to find in a top bar. -->
                <?php if ( has_nav_menu( 'prairie-top-nav' ) ) : ?>
                <nav class="prairie-top-nav-wrap" aria-label="<?php esc_attr_e( 'Header navigation', 'prairie' ); ?>">
                    <?php
                    wp_nav_menu( [
                        'theme_location' => 'prairie-top-nav',
                        'container'      => false,
                        'menu_class'     => 'prairie-top-nav',
                        'depth'          => 1, /* No dropdowns — keep it flat */
                    ] );
                    ?>
                </nav>
                <?php endif; ?>

                <!-- Site search — click-to-expand dropdown.
                     WHY dropdown: A persistent search input takes up valuable
                     header space. The magnifying glass icon is universally
                     recognized. Click to expand, click away to close. -->
                <?php if ( function_exists( 'sp_get_search_page_url' ) ) : ?>
                <div class="sp-header-search-wrap">
                    <button type="button" class="sp-search-toggle" aria-label="<?php esc_attr_e( 'Open search', 'prairie' ); ?>" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </button>
                    <form class="sp-header-search" action="<?php echo esc_url( sp_get_search_page_url() ); ?>" method="get">
                        <input type="text" name="sp_q" placeholder="<?php esc_attr_e( 'Search…', 'prairie' ); ?>" aria-label="<?php esc_attr_e( 'Search the site', 'prairie' ); ?>" autocomplete="off" required minlength="2">
                        <button type="submit" aria-label="<?php esc_attr_e( 'Search', 'prairie' ); ?>">
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
