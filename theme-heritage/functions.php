<?php
/**
 * Heritage Child Theme — Functions
 *
 * WHY: This file does three things:
 * 1. On activation, pushes Heritage's color palette and font into the plugin's
 *    design settings so the plugin's override CSS outputs OUR values.
 * 2. Loads the Merriweather font from Google Fonts (Heritage's warm serif)
 * 3. Enqueues the child stylesheet for additional CSS tweaks beyond the
 *    custom properties (link colors, divider styling, etc.)
 *
 * @package Heritage
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'HERITAGE_THEME_VERSION', '1.0.0' );

/**
 * Push Heritage's palette into the plugin's design settings on activation.
 *
 * WHY: The SocietyPress plugin outputs a :root {} block AFTER all theme
 *      stylesheets with the saved design settings. If we only set --sp-*
 *      vars in our style.css, the plugin's override stomps them. By writing
 *      our colors into the settings option, the plugin outputs OUR palette
 *      and the Design settings page shows the correct values too.
 */
add_action( 'after_switch_theme', function () {
    $settings = get_option( 'societypress_settings', [] );

    $settings['design_color_primary']       = '#3E2723';
    $settings['design_color_primary_hover'] = '#B8860B';
    $settings['design_color_accent']        = '#B8860B';
    $settings['design_color_header_bg']     = '#3E2723';
    $settings['design_color_header_text']   = '#FDF6EC';
    $settings['design_color_footer_bg']     = '#3E2723';
    $settings['design_color_footer_text']   = '#FDF6EC';
    $settings['design_font_body']           = 'merriweather';
    $settings['design_font_heading']        = 'merriweather';

    update_option( 'societypress_settings', $settings );
} );

add_action( 'wp_enqueue_scripts', function () {

    // Google Fonts — Merriweather with italic support for body text
    wp_enqueue_style(
        'heritage-google-fonts',
        'https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,400&display=swap',
        [],
        null
    );

    // Child stylesheet — loads after parent for additional CSS tweaks.
    wp_enqueue_style(
        'heritage-style',
        get_stylesheet_uri(),
        [ 'societypress-style', 'heritage-google-fonts' ],
        HERITAGE_THEME_VERSION
    );
} );
