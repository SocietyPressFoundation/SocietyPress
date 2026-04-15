<?php
/**
 * Parlor Child Theme — Functions
 *
 * WHY: This file does two things:
 * 1. Loads EB Garamond from Google Fonts (Parlor's elegant serif)
 * 2. Enqueues the child stylesheet so our :root color/font overrides actually
 *    take effect. Without this, WordPress would only load the parent CSS and
 *    Parlor's style.css would never be included in the page.
 *
 * @package Parlor
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'PARLOR_THEME_VERSION' ) ) {
    define( 'PARLOR_THEME_VERSION', '1.0.0' );
}

add_action( 'wp_enqueue_scripts', function () {

    // Google Fonts — EB Garamond with italic support
    // WHY EB Garamond: An elegant, old-style serif that evokes fine printing
    // and literary tradition. The slight calligraphic character feels refined
    // without sacrificing readability at body sizes.
    wp_enqueue_style(
        'parlor-google-fonts',
        'https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap',
        [],
        null
    );

    // Child stylesheet — loads after parent so our :root overrides win.
    wp_enqueue_style(
        'parlor-style',
        get_stylesheet_uri(),
        [ 'societypress-style', 'parlor-google-fonts' ],
        PARLOR_THEME_VERSION
    );
} );
