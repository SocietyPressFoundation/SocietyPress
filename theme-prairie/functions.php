<?php
/**
 * Prairie Child Theme — Functions
 *
 * WHY: This file does three things:
 * 1. On activation, pushes Prairie's color palette and font into the plugin's
 *    design settings so the plugin's override CSS outputs OUR values.
 * 2. Loads the Lora font from Google Fonts (Prairie's warm, earthy serif)
 * 3. Enqueues the child stylesheet for additional CSS tweaks beyond the
 *    custom properties (link colors, divider styling, etc.)
 *
 * @package Prairie
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PRAIRIE_THEME_VERSION', '1.0.0' );

/**
 * Push Prairie's palette into the plugin's design settings on activation.
 *
 * WHY: The SocietyPress plugin outputs a :root {} block AFTER all theme
 *      stylesheets with the saved design settings. If we only set --sp-*
 *      vars in our style.css, the plugin's override stomps them. By writing
 *      our colors into the settings option, the plugin outputs OUR palette
 *      and the Design settings page shows the correct values too.
 */
add_action( 'after_switch_theme', function () {
    $settings = get_option( 'societypress_settings', [] );

    $settings['design_color_primary']       = '#2D5016';
    $settings['design_color_primary_hover'] = '#7A9A5E';
    $settings['design_color_accent']        = '#C4A265';
    $settings['design_color_header_bg']     = '#2D5016';
    $settings['design_color_header_text']   = '#FAF7F2';
    $settings['design_color_footer_bg']     = '#2D5016';
    $settings['design_color_footer_text']   = '#FAF7F2';
    $settings['design_font_body']           = 'lora';
    $settings['design_font_heading']        = 'lora';

    update_option( 'societypress_settings', $settings );
} );

add_action( 'wp_enqueue_scripts', function () {

    // Google Fonts — Lora with italic support
    wp_enqueue_style(
        'prairie-google-fonts',
        'https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap',
        [],
        null
    );

    // Child stylesheet — loads after parent for additional CSS tweaks.
    wp_enqueue_style(
        'prairie-style',
        get_stylesheet_uri(),
        [ 'societypress-style', 'prairie-google-fonts' ],
        PRAIRIE_THEME_VERSION
    );
} );
