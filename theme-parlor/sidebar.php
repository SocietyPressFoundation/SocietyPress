<?php
/**
 * Parlor Child Theme — Sidebar
 *
 * WHY: Traditional organizational websites often have a right sidebar for
 * quick-access items — upcoming events, recent news, member login, search,
 * etc. This sidebar is OPTIONAL: if no widgets are added by the admin, the
 * page templates detect it with is_active_sidebar() and render full-width
 * instead. This means zero configuration is needed for a clean look, but
 * the sidebar is there if the admin wants it.
 *
 * @package Parlor
 * @since   1.0.0
 */

if ( ! is_active_sidebar( 'parlor-sidebar' ) ) {
    return;
}
?>

<aside class="parlor-sidebar" role="complementary" aria-label="<?php esc_attr_e( 'Sidebar', 'parlor' ); ?>">
    <?php dynamic_sidebar( 'parlor-sidebar' ); ?>
</aside>
