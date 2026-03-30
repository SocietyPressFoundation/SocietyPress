<?php
/**
 * Parlor Child Theme — Functions
 *
 * WHY: This file does three things:
 * 1. On activation, pushes Parlor's color palette and font into the plugin's
 *    design settings so the plugin's override CSS outputs OUR values.
 * 2. Loads EB Garamond from Google Fonts (Parlor's elegant serif)
 * 3. Enqueues the child stylesheet for additional CSS tweaks beyond the
 *    custom properties (link colors, heading weight, etc.)
 *
 * @package Parlor
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PARLOR_THEME_VERSION', '1.0.0' );

/**
 * Push Parlor's palette into the plugin's design settings on activation.
 *
 * WHY: The SocietyPress plugin outputs a :root {} block AFTER all theme
 *      stylesheets with the saved design settings. If we only set --sp-*
 *      vars in our style.css, the plugin's override stomps them. By writing
 *      our colors into the settings option, the plugin outputs OUR palette
 *      and the Design settings page shows the correct values too.
 */
add_action( 'after_switch_theme', function () {
    $settings = get_option( 'societypress_settings', [] );

    $settings['design_color_primary']       = '#3C1053';
    $settings['design_color_primary_hover'] = '#B76E79';
    $settings['design_color_accent']        = '#B76E79';
    $settings['design_color_header_bg']     = '#3C1053';
    $settings['design_color_header_text']   = '#FFF8F0';
    $settings['design_color_footer_bg']     = '#3C1053';
    $settings['design_color_footer_text']   = '#FFF8F0';
    $settings['design_font_body']           = 'garamond';
    $settings['design_font_heading']        = 'garamond';

    update_option( 'societypress_settings', $settings );
} );

add_action( 'wp_enqueue_scripts', function () {

    // Google Fonts — EB Garamond with italic support
    wp_enqueue_style(
        'parlor-google-fonts',
        'https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap',
        [],
        null
    );

    // Child stylesheet — loads after parent for additional CSS tweaks.
    wp_enqueue_style(
        'parlor-style',
        get_stylesheet_uri(),
        [ 'societypress-style', 'parlor-google-fonts' ],
        PARLOR_THEME_VERSION
    );
} );
