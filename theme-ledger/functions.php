<?php
/**
 * Ledger Child Theme — Functions
 *
 * WHY: This file does two things:
 * 1. Loads Source Sans 3 from Google Fonts (Ledger's formal, professional sans-serif)
 * 2. Enqueues the child stylesheet so our :root color/font overrides actually
 *    take effect. Without this, WordPress would only load the parent CSS and
 *    Ledger's style.css would never be included in the page.
 *
 * @package Ledger
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'LEDGER_THEME_VERSION', '1.0.0' );

add_action( 'wp_enqueue_scripts', function () {

    // Google Fonts — Source Sans 3 with italic support
    // WHY Source Sans 3: A professional sans-serif designed by Paul Hunt for Adobe.
    // Clean and authoritative without being cold. Excellent readability at all sizes
    // with a slightly humanist character that softens the formal palette.
    wp_enqueue_style(
        'ledger-google-fonts',
        'https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,300;0,400;0,600;0,700;1,400&display=swap',
        [],
        null
    );

    // Child stylesheet — loads after parent so our :root overrides win.
    wp_enqueue_style(
        'ledger-style',
        get_stylesheet_uri(),
        [ 'societypress-style', 'ledger-google-fonts' ],
        LEDGER_THEME_VERSION
    );
} );
