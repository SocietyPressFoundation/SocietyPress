<?php
/**
 * Coastline Child Theme — Functions
 *
 * WHY: This file does two things:
 * 1. Loads the Inter font from Google Fonts (Coastline's clean sans-serif)
 * 2. Enqueues the child stylesheet so our :root color/font overrides actually
 *    take effect. Without this, WordPress would only load the parent CSS and
 *    Coastline's style.css would never be included in the page.
 *
 * @package Coastline
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'COASTLINE_THEME_VERSION', '1.0.0' );

add_action( 'wp_enqueue_scripts', function () {

    // Google Fonts — Inter with full weight range
    // WHY Inter: The cleanest sans-serif available. Designed specifically for
    // screens, highly legible at all sizes, and feels modern without being trendy.
    wp_enqueue_style(
        'coastline-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
        [],
        null
    );

    // Child stylesheet — loads after parent so our :root overrides win.
    wp_enqueue_style(
        'coastline-style',
        get_stylesheet_uri(),
        [ 'societypress-style', 'coastline-google-fonts' ],
        COASTLINE_THEME_VERSION
    );
} );
