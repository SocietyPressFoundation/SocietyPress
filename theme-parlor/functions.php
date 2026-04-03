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


// ============================================================================
// WIDGET AREAS
// WHY: Parlor's "Traditional" layout archetype uses an optional right sidebar
// and a 2-column footer. Registering these widget areas lets the admin control
// what goes in each area from Appearance -> Widgets without editing code.
// ============================================================================

add_action( 'widgets_init', function () {

    // Sidebar — appears on pages and the front page ONLY if widgets are added.
    // WHY optional: Many small societies don't need a sidebar. If it's empty,
    // the page templates detect it and render full-width. Zero config needed.
    register_sidebar([
        'name'          => __( 'Sidebar', 'parlor' ),
        'id'            => 'parlor-sidebar',
        'description'   => __( 'Widgets here appear in the right sidebar on pages and the homepage. Leave empty for a full-width layout.', 'parlor' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

    // Footer column 1 — organization info (left side).
    // WHY widget area instead of hardcoded: Different societies will want
    // different info here — some want address and hours, some want a mission
    // statement, some want a logo. Widget area gives them full control.
    register_sidebar([
        'name'          => __( 'Footer — Organization Info', 'parlor' ),
        'id'            => 'parlor-footer-1',
        'description'   => __( 'Left column of the footer. Ideal for your organization name, description, and contact details.', 'parlor' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

    // Footer column 2 — useful links (right side).
    // WHY: A Navigation Menu widget dropped in here gives the admin a quick
    // way to add footer links without editing code. A Custom HTML widget
    // works too if they want something fancier.
    register_sidebar([
        'name'          => __( 'Footer — Quick Links', 'parlor' ),
        'id'            => 'parlor-footer-2',
        'description'   => __( 'Right column of the footer. Ideal for a navigation menu or list of useful links.', 'parlor' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);
} );


// ============================================================================
// REGISTER FOOTER MENU LOCATION
// WHY: The footer template can use a "footer" nav menu as a fallback when the
// parlor-footer-2 widget area is empty. This gives the admin a second easy
// option: create a menu in Appearance -> Menus and assign it to "Footer."
// ============================================================================

add_action( 'after_setup_theme', function () {
    register_nav_menus([
        'footer' => __( 'Footer Navigation', 'parlor' ),
    ]);
} );
