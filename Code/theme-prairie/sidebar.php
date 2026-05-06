<?php
/**
 * Prairie Theme — Left Sidebar
 *
 * WHY: The Explorer layout puts a permanent vertical navigation sidebar on the
 * left side of every page. This gives content-heavy societies a persistent
 * "table of contents" for their site — visitors always know where they are
 * and what else is available, without hunting through dropdown menus.
 *
 * Priority logic:
 * 1. If the admin has assigned a menu to the 'prairie-sidebar-nav' location,
 *    we render that menu. This is the recommended approach — it gives the
 *    admin full control over the sidebar links and their order.
 * 2. If no menu is assigned, we fall back to the 'prairie-sidebar' widget area.
 *    This lets the admin use widgets (search, recent posts, custom HTML, etc.)
 *    if they prefer flexibility over a structured nav.
 * 3. If neither is configured, we show a helpful message on the frontend
 *    (only visible to admins) so they know to set it up.
 *
 * Active page highlighting is handled via CSS using WordPress' built-in
 * .current-menu-item and .current_page_item classes that wp_nav_menu()
 * adds automatically.
 *
 * @package Prairie
 * @since   1.1.0
 */
?>

<!-- Backdrop — the dark overlay behind the sidebar on mobile.
     WHY separate from sidebar: It needs to be a sibling element (not a child)
     so clicking it can close the sidebar without the click event bubbling
     into the sidebar itself. -->
<div class="prairie-sidebar-backdrop" aria-hidden="true"></div>

<aside class="prairie-sidebar" role="complementary" aria-label="<?php esc_attr_e( 'Sidebar navigation', 'prairie' ); ?>">

    <?php if ( has_nav_menu( 'prairie-sidebar-nav' ) ) : ?>

        <!-- Admin has assigned a menu to the Sidebar Navigation location.
             WHY walker is default: WordPress' default walker already outputs
             .current-menu-item, .current_page_item, and .current-menu-ancestor
             classes — our CSS hooks into those for active page highlighting.
             No custom walker needed. -->
        <nav aria-label="<?php esc_attr_e( 'Site navigation', 'prairie' ); ?>">
            <?php
            wp_nav_menu( [
                'theme_location' => 'prairie-sidebar-nav',
                'container'      => false,
                'depth'          => 3, /* Allow up to 3 levels of nesting for deep structures */
            ] );
            ?>
        </nav>

    <?php elseif ( is_active_sidebar( 'prairie-sidebar' ) ) : ?>

        <!-- No nav menu assigned, but widgets are present in the sidebar area -->
        <div class="widget-area">
            <?php dynamic_sidebar( 'prairie-sidebar' ); ?>
        </div>

    <?php elseif ( current_user_can( 'edit_theme_options' ) ) : ?>

        <!-- Nothing configured yet — show a setup hint to admins only.
             WHY admin-only: Regular visitors shouldn't see configuration
             instructions. Admins need to know where to go to set this up. -->
        <div class="widget-area widget-area--admin-hint">
            <p class="widget-title"><?php esc_html_e( 'Sidebar Setup', 'prairie' ); ?></p>
            <p class="widget-area__hint-text">
                <?php
                echo wp_kses(
                    sprintf(
                        /* translators: 1: opening link tag to menu admin, 2: closing link tag */
                        __( 'Assign a menu to the Sidebar Navigation location in %1$sMenus%2$s, or add widgets to the Prairie Sidebar area.', 'prairie' ),
                        '<a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">',
                        '</a>'
                    ),
                    [ 'a' => [ 'href' => [] ] ]
                );
                ?>
            </p>
        </div>

    <?php endif; ?>

</aside>
