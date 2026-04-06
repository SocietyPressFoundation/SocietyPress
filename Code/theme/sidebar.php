<?php
/**
 * Sidebar Template
 *
 * WHY: Outputs the sidebar widget area. If no widgets have been added
 * by the site admin, nothing is displayed — we don't show an empty box.
 *
 * @package SocietyPress
 */

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
    return; // No widgets? Don't output anything.
}
?>

<aside class="widget-area" role="complementary">
    <?php dynamic_sidebar( 'sidebar-1' ); ?>
</aside>
