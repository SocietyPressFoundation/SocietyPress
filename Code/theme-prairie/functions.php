<?php
/**
 * Prairie Child Theme — Functions
 *
 * WHY this file does what it does:
 *
 * 1. On activation, pushes Prairie's color palette and font into the plugin's
 *    design settings so the plugin's override CSS outputs OUR values.
 * 2. Loads the Lora font from Google Fonts (Prairie's warm, earthy serif).
 * 3. Enqueues the child stylesheet for layout and visual tweaks.
 * 4. Registers the sidebar widget area and nav menu for the Explorer layout.
 * 5. Enqueues the sidebar toggle script for mobile.
 *
 * ARCHITECTURE NOTE: Prairie uses the "Explorer" layout archetype — a permanent
 * left sidebar with vertical navigation on desktop. This is ideal for content-heavy
 * societies with deep page structures (big library, lots of records, research guides).
 * The sidebar collapses to a toggleable overlay on mobile.
 *
 * @package Prairie
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'PRAIRIE_THEME_VERSION' ) ) {
    define( 'PRAIRIE_THEME_VERSION', '1.1.0' );
}


/* =============================================================================
   THEME ACTIVATION — PUSH PALETTE INTO PLUGIN SETTINGS
   =============================================================================
   WHY: The SocietyPress plugin outputs a :root {} block AFTER all theme
        stylesheets with the saved design settings. If we only set --sp-*
        vars in our style.css, the plugin's override stomps them. By writing
        our colors into the settings option, the plugin outputs OUR palette
        and the Design settings page shows the correct values too.
   ============================================================================ */

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


/* =============================================================================
   ENQUEUE STYLES & SCRIPTS
   ============================================================================ */

add_action( 'wp_enqueue_scripts', function () {

    /* Google Fonts — Lora with italic support for the warm earthy serif feel */
    wp_enqueue_style(
        'prairie-google-fonts',
        'https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap',
        [],
        null
    );

    /* Child stylesheet — loads after parent for layout + visual overrides */
    wp_enqueue_style(
        'prairie-style',
        get_stylesheet_uri(),
        [ 'societypress-style', 'prairie-google-fonts' ],
        PRAIRIE_THEME_VERSION
    );

    /* Sidebar toggle script — vanilla JS for the mobile sidebar slide-in.
       WHY a separate file: This is ~60 lines of logic (toggle, backdrop,
       escape key, focus trap hints) — too much for an inline script tag
       and complex enough to warrant its own file for maintainability. */
    wp_enqueue_script(
        'prairie-sidebar-toggle',
        get_stylesheet_directory_uri() . '/js/sidebar-toggle.js',
        [],
        PRAIRIE_THEME_VERSION,
        true /* Load in footer — DOM must be ready */
    );
} );


/* =============================================================================
   REGISTER NAV MENUS
   =============================================================================
   WHY: The Explorer layout has TWO nav locations:
   1. prairie-sidebar-nav — The vertical navigation in the left sidebar.
      This is the primary way users navigate the site.
   2. prairie-top-nav — A small horizontal nav in the compact header.
      Only 3-4 key links (Home, About, Contact, etc.) for quick access.

   The parent theme's 'primary' menu is still registered by the parent.
   We add our own locations for the Explorer-specific layout.
   ============================================================================ */

add_action( 'after_setup_theme', function () {
    register_nav_menus( [
        'prairie-sidebar-nav' => esc_html__( 'Sidebar Navigation', 'societypress' ),
        'prairie-top-nav'     => esc_html__( 'Header Links', 'societypress' ),
    ] );
} );


/* =============================================================================
   REGISTER WIDGET AREA
   =============================================================================
   WHY: The sidebar can use either a nav menu OR widgets. If the admin assigns
   a menu to the prairie-sidebar-nav location, that takes priority. If not,
   the widget area provides a fallback so they can drag in any widgets they
   want. This flexibility matters for societies that might want a sidebar
   with a mix of nav links, a search widget, and a "recent additions" list.
   ============================================================================ */

add_action( 'widgets_init', function () {
    register_sidebar( [
        'name'          => esc_html__( 'Prairie Sidebar', 'societypress' ),
        'id'            => 'prairie-sidebar',
        'description'   => esc_html__( 'Widgets appear in the left sidebar. Only used if no menu is assigned to the Sidebar Navigation location.', 'societypress' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ] );
} );
