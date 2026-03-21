<?php
/**
 * Prairie Child Theme — Functions
 *
 * WHY: This file does two things:
 * 1. Loads the Lora font from Google Fonts (Prairie's warm, earthy serif)
 * 2. Enqueues the child stylesheet so our :root color/font overrides actually
 *    take effect. Without this, WordPress would only load the parent CSS and
 *    Prairie's style.css would never be included in the page.
 *
 * @package Prairie
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PRAIRIE_THEME_VERSION', '1.0.0' );

add_action( 'wp_enqueue_scripts', function () {

    // Google Fonts — Lora with italic support
    // WHY Lora: A contemporary serif that balances elegance with readability.
    // Warm character fits the earthy palette. Works beautifully for body text
    // at larger sizes — important for our older demographic.
    wp_enqueue_style(
        'prairie-google-fonts',
        'https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap',
        [],
        null
    );

    // Child stylesheet — loads after parent so our :root overrides win.
    wp_enqueue_style(
        'prairie-style',
        get_stylesheet_uri(),
        [ 'societypress-style', 'prairie-google-fonts' ],
        PRAIRIE_THEME_VERSION
    );
} );
