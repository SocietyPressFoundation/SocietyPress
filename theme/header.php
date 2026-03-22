<?php
/**
 * Theme Header
 *
 * WHY: This outputs everything from <!DOCTYPE> through the opening of the
 * main content area. Every page on the site includes this file, so it
 * controls the site-wide header, logo, and navigation.
 *
 * @package SocietyPress
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

            <!-- Site branding: logo and/or site title -->
            <?php
            // WHY we read this setting here: The admin can toggle the site
            // title/tagline on or off from Settings → Design. Default is on
            // (1) so existing sites keep their text. When the logo already
            // includes the society name, showing it again as text is redundant.
            $sp_settings      = get_option( 'societypress_settings', [] );
            $show_header_title = (int) ( $sp_settings['design_show_header_title'] ?? 1 );
            ?>
            <div class="site-branding">
                <?php if ( has_custom_logo() ) : ?>
                    <?php the_custom_logo(); ?>
                <?php endif; ?>

                <?php if ( $show_header_title ) : ?>
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
                        <p class="site-description"><?php echo esc_html( $description ); ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <!--
                WHY the nav and user menu are wrapped together: They sit side
                by side in the header — the nav menu on the left, the user
                account dropdown on the right. Wrapping them lets us use
                flexbox to align them as a unit.
            -->
            <div class="header-nav-area">
                <!-- Primary navigation menu -->
                <?php if ( has_nav_menu( 'primary' ) ) : ?>
                <nav class="main-navigation" aria-label="Primary navigation">
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'primary',
                        'container'      => false,
                        'depth'          => 1, // No dropdowns for now — keep it simple
                    ]);
                    ?>
                </nav>
                <?php endif; ?>

                <!-- Social media icons — inline in the header row -->
                <?php if ( function_exists( 'sp_social_icons' ) ) { sp_social_icons(); } ?>

                <!-- Site search — click-to-expand dropdown in the header bar.
                     WHY dropdown: A persistent text input takes up valuable nav
                     space, especially on tablets. The magnifying glass icon is
                     universally recognized as "search." Clicking it reveals the
                     input field; clicking away or pressing Escape closes it.
                     The expanded state is wide enough to type a real query. -->
                <?php if ( function_exists( 'sp_get_search_page_url' ) ) : ?>
                <div class="sp-header-search-wrap">
                    <button type="button" class="sp-search-toggle" aria-label="<?php esc_attr_e( 'Open search', 'societypress' ); ?>" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </button>
                    <form class="sp-header-search" action="<?php echo esc_url( sp_get_search_page_url() ); ?>" method="get">
                        <input type="text" name="sp_q" placeholder="<?php esc_attr_e( 'Search…', 'societypress' ); ?>" autocomplete="off" required minlength="2">
                        <button type="submit" aria-label="<?php esc_attr_e( 'Search', 'societypress' ); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </button>
                    </form>
                </div>
                <?php endif; ?>

                <!-- User account menu (replaces the admin bar) -->
                <?php sp_user_menu(); ?>
            </div>

        </div>
    </header>
