<?php
/**
 * Coastline Child Theme — Functions
 *
 * WHY: This file does three things:
 * 1. On activation, pushes Coastline's color palette and font into the plugin's
 *    design settings so the plugin's override CSS outputs OUR values.
 * 2. Loads the Inter font from Google Fonts (Coastline's clean sans-serif)
 * 3. Enqueues the child stylesheet for additional CSS tweaks beyond the
 *    custom properties (rounded corners, link colors, etc.)
 *
 * @package Coastline
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'COASTLINE_THEME_VERSION', '1.0.0' );

/**
 * Push Coastline's palette into the plugin's design settings on activation.
 *
 * WHY: The SocietyPress plugin outputs a :root {} block AFTER all theme
 *      stylesheets with the saved design settings. If we only set --sp-*
 *      vars in our style.css, the plugin's override stomps them. By writing
 *      our colors into the settings option, the plugin outputs OUR palette
 *      and the Design settings page shows the correct values too.
 */
add_action( 'after_switch_theme', function () {
    $settings = get_option( 'societypress_settings', [] );

    $settings['design_color_primary']       = '#1B3A5C';
    $settings['design_color_primary_hover'] = '#5B9BD5';
    $settings['design_color_accent']        = '#5B9BD5';
    $settings['design_color_header_bg']     = '#1B3A5C';
    $settings['design_color_header_text']   = '#FFFFFF';
    $settings['design_color_footer_bg']     = '#1B3A5C';
    $settings['design_color_footer_text']   = '#FFFFFF';
    $settings['design_font_body']           = 'inter';
    $settings['design_font_heading']        = 'inter';

    update_option( 'societypress_settings', $settings );
} );

add_action( 'wp_enqueue_scripts', function () {

    // Google Fonts — Inter with full weight range
    wp_enqueue_style(
        'coastline-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
        [],
        null
    );

    // Child stylesheet — loads after parent for additional CSS tweaks.
    wp_enqueue_style(
        'coastline-style',
        get_stylesheet_uri(),
        [ 'societypress-style', 'coastline-google-fonts' ],
        COASTLINE_THEME_VERSION
    );
} );
