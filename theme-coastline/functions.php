<?php
/**
 * Coastline Child Theme — Functions
 *
 * WHY this file exists: It handles three concerns for the Coastline magazine
 * layout theme:
 *
 * 1. On activation, pushes Coastline's color palette and font into the
 *    plugin's design settings so the plugin's override CSS outputs OUR values.
 * 2. Loads the Inter font from Google Fonts (Coastline's clean sans-serif).
 * 3. Enqueues the child stylesheet for layout and visual overrides.
 * 4. Registers the sidebar and footer widget areas that make the magazine
 *    layout functional — admins populate these from Appearance > Widgets.
 *
 * @package Coastline
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'COASTLINE_THEME_VERSION', '1.1.0' );


/* ============================================================================
   THEME ACTIVATION — PUSH PALETTE INTO PLUGIN SETTINGS
   ============================================================================ */

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


/* ============================================================================
   ENQUEUE STYLES
   ============================================================================ */

add_action( 'wp_enqueue_scripts', function () {

    // Google Fonts — Inter with full weight range for the clean sans-serif look.
    wp_enqueue_style(
        'coastline-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
        [],
        null
    );

    // Child stylesheet — loads after parent for layout and visual overrides.
    // WHY the dependency chain: parent style must load first so our CSS
    // overrides land on top. Google Fonts must load first so Inter is
    // available when the browser renders our CSS.
    wp_enqueue_style(
        'coastline-style',
        get_stylesheet_uri(),
        [ 'societypress-style', 'coastline-google-fonts' ],
        COASTLINE_THEME_VERSION
    );
} );


/* ============================================================================
   WIDGET AREAS — SIDEBAR + FOOTER COLUMNS
   WHY: The magazine layout needs three widget areas that the parent theme
   doesn't provide:
   - coastline-sidebar: The right sidebar on every page (events, newsletters,
     quick links, calendar, etc.)
   - coastline-footer-1 / coastline-footer-2: Two footer columns for org
     info and navigation links.
   These are registered here so the admin can populate them from the Widgets
   screen without touching any code.
   ============================================================================ */

add_action( 'widgets_init', function () {

    register_sidebar( [
        'name'          => esc_html__( 'Magazine Sidebar', 'coastline' ),
        'id'            => 'coastline-sidebar',
        'description'   => esc_html__( 'Widgets in this area appear in the right sidebar on all pages. Great for upcoming events, newsletter covers, quick links, and calendars.', 'coastline' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ] );

    register_sidebar( [
        'name'          => esc_html__( 'Footer Column 1', 'coastline' ),
        'id'            => 'coastline-footer-1',
        'description'   => esc_html__( 'First footer column. Typically used for organization info, address, or an about blurb.', 'coastline' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ] );

    register_sidebar( [
        'name'          => esc_html__( 'Footer Column 2', 'coastline' ),
        'id'            => 'coastline-footer-2',
        'description'   => esc_html__( 'Second footer column. Typically used for quick links, social media, or contact info.', 'coastline' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ] );
} );
