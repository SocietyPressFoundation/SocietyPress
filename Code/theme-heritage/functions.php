<?php
/**
 * Heritage Child Theme — Functions
 *
 * WHY: This file handles four responsibilities:
 * 1. On activation, pushes Heritage's color palette and font into the plugin's
 *    design settings so the plugin's override CSS outputs OUR values.
 * 2. Loads the Merriweather font from Google Fonts (Heritage's warm serif).
 * 3. Enqueues the child stylesheet for layout and visual tweaks.
 * 4. Registers widget areas for the 3-column footer and front page hero,
 *    giving Harold full control over footer content without editing templates.
 *
 * @package Heritage
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'HERITAGE_THEME_VERSION' ) ) {
    define( 'HERITAGE_THEME_VERSION', '1.1.0' );
}

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


/**
 * Enqueue styles and fonts.
 *
 * WHY wp_enqueue_scripts at default priority: Google Fonts must load before
 * the child stylesheet references Merriweather. The dependency array ensures
 * correct load order: Google Fonts -> parent style -> child style.
 */
add_action( 'wp_enqueue_scripts', function () {

    // Google Fonts — Merriweather with italic support for body text
    wp_enqueue_style(
        'heritage-google-fonts',
        'https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,400&display=swap',
        [],
        null
    );

    // Child stylesheet — loads after parent for layout and visual overrides.
    wp_enqueue_style(
        'heritage-style',
        get_stylesheet_uri(),
        [ 'societypress-style', 'heritage-google-fonts' ],
        HERITAGE_THEME_VERSION
    );
} );


/**
 * Register widget areas for the Heritage theme.
 *
 * WHY widgets_init: This is the standard WordPress hook for registering
 * sidebars/widget areas. We register four areas:
 *
 * 1-3. Three footer columns — Harold can drop in Text, Nav Menu, or any
 *      widget. If left empty, footer.php renders sensible defaults.
 * 4.   Front page hero — allows admin-controlled hero content via widgets.
 *      If left empty, front-page.php renders the default hero (site name
 *      + tagline + CTA button).
 */
add_action( 'widgets_init', function () {

    // Footer column 1 — typically "About" / organization description
    register_sidebar( [
        'name'          => esc_html__( 'Footer Column 1', 'societypress' ),
        'id'            => 'heritage-footer-1',
        'description'   => esc_html__( 'First footer column — typically organization description.', 'societypress' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ] );

    // Footer column 2 — typically quick links / navigation
    register_sidebar( [
        'name'          => esc_html__( 'Footer Column 2', 'societypress' ),
        'id'            => 'heritage-footer-2',
        'description'   => esc_html__( 'Second footer column — typically quick links.', 'societypress' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ] );

    // Footer column 3 — typically contact info
    register_sidebar( [
        'name'          => esc_html__( 'Footer Column 3', 'societypress' ),
        'id'            => 'heritage-footer-3',
        'description'   => esc_html__( 'Third footer column — typically contact information.', 'societypress' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ] );

    // Front page hero — optional widget area for admin-controlled hero content
    register_sidebar( [
        'name'          => esc_html__( 'Front Page Hero', 'societypress' ),
        'id'            => 'heritage-hero',
        'description'   => esc_html__( 'Hero section on the front page. Leave empty for the default hero (site name + tagline + button).', 'societypress' ),
        'before_widget' => '<div id="%1$s" class="widget heritage-hero-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="heritage-hero-widget-title">',
        'after_title'   => '</h2>',
    ] );
} );
