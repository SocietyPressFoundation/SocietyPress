<?php
/**
 * Ledger Child Theme — Functions
 *
 * WHY: This file does three things:
 * 1. On activation, pushes Ledger's color palette and font into the plugin's
 *    design settings so the plugin's override CSS outputs OUR values.
 * 2. Loads Source Sans 3 from Google Fonts (Ledger's formal sans-serif)
 * 3. Enqueues the child stylesheet for additional CSS tweaks beyond the
 *    custom properties (letter spacing, divider styling, etc.)
 *
 * @package Ledger
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'LEDGER_THEME_VERSION', '1.0.0' );

/**
 * Push Ledger's palette into the plugin's design settings on activation.
 *
 * WHY: The SocietyPress plugin outputs a :root {} block AFTER all theme
 *      stylesheets with the saved design settings. If we only set --sp-*
 *      vars in our style.css, the plugin's override stomps them. By writing
 *      our colors into the settings option, the plugin outputs OUR palette
 *      and the Design settings page shows the correct values too.
 */
add_action( 'after_switch_theme', function () {
    $settings = get_option( 'societypress_settings', [] );

    $settings['design_color_primary']       = '#2C2C2C';
    $settings['design_color_primary_hover'] = '#7B2D3B';
    $settings['design_color_accent']        = '#7B2D3B';
    $settings['design_color_header_bg']     = '#2C2C2C';
    $settings['design_color_header_text']   = '#F8F5F0';
    $settings['design_color_footer_bg']     = '#2C2C2C';
    $settings['design_color_footer_text']   = '#F8F5F0';
    $settings['design_font_body']           = 'source-sans';
    $settings['design_font_heading']        = 'source-sans';

    update_option( 'societypress_settings', $settings );
} );

add_action( 'wp_enqueue_scripts', function () {

    // Google Fonts — Source Sans 3 with italic support
    wp_enqueue_style(
        'ledger-google-fonts',
        'https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,300;0,400;0,600;0,700;1,400&display=swap',
        [],
        null
    );

    // Child stylesheet — loads after parent for additional CSS tweaks.
    wp_enqueue_style(
        'ledger-style',
        get_stylesheet_uri(),
        [ 'societypress-style', 'ledger-google-fonts' ],
        LEDGER_THEME_VERSION
    );
} );
