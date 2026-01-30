<?php
/**
 * The sidebar containing the main widget area
 *
 * WHY: Provides widget-ready sidebar for posts and pages.
 *
 * @package SocietyPress
 * @since 1.01d
 */

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}
?>

<aside id="secondary" class="widget-area sidebar">
	<?php dynamic_sidebar( 'sidebar-1' ); ?>
</aside><!-- #secondary -->
