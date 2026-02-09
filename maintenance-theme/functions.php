<?php
/**
 * SocietyPress Maintenance Theme Functions
 *
 * Minimal functions for maintenance mode.
 *
 * @package SocietyPress_Maintenance
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue the theme stylesheet.
 */
function societypress_maintenance_enqueue_styles() {
    wp_enqueue_style(
        'societypress-maintenance-style',
        get_stylesheet_uri(),
        array(),
        '1.0'
    );
}
add_action( 'wp_enqueue_scripts', 'societypress_maintenance_enqueue_styles' );

/**
 * Allow admins to still access the site normally via wp-admin.
 * Non-logged-in users and non-admins see the maintenance page.
 */
function societypress_maintenance_redirect() {
    // Skip if in admin area or login page
    if ( is_admin() || $GLOBALS['pagenow'] === 'wp-login.php' ) {
        return;
    }

    // Allow logged-in admins to browse normally (they'll still see maintenance theme)
    // This is just here if you want to add bypass logic later
}
add_action( 'template_redirect', 'societypress_maintenance_redirect' );

/**
 * Remove admin bar for non-admins on frontend.
 */
function societypress_maintenance_hide_admin_bar() {
    if ( ! current_user_can( 'manage_options' ) ) {
        show_admin_bar( false );
    }
}
add_action( 'after_setup_theme', 'societypress_maintenance_hide_admin_bar' );

/**
 * Set proper HTTP status for maintenance.
 * 503 tells search engines the site is temporarily unavailable.
 */
function societypress_maintenance_status_header() {
    if ( ! is_admin() && ! current_user_can( 'manage_options' ) ) {
        status_header( 503 );
        header( 'Retry-After: 3600' ); // Tell crawlers to check back in 1 hour
    }
}
add_action( 'template_redirect', 'societypress_maintenance_status_header' );
