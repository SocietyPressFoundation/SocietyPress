<?php
/**
 * Parlor Child Theme — Functions
 *
 * WHY: This file does two things:
 * 1. Loads EB Garamond from Google Fonts (Parlor's elegant serif)
 * 2. Enqueues the child stylesheet so our :root color/font overrides actually
 *    take effect. Without this, WordPress would only load the parent CSS and
 *    Parlor's style.css would never be included in the page.
 *
 * @package Parlor
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'PARLOR_THEME_VERSION' ) ) {
    define( 'PARLOR_THEME_VERSION', '1.1.89' );
}

/**
 * Push Parlor's palette into the SocietyPress design system on activation.
 *
 * WHY: The SP plugin reads `societypress_settings` for its design tokens
 * (admin Design panel, plugin-rendered widgets like donation forms and
 * member directory cards). Without this hook, switching to Parlor leaves
 * those tokens at whatever the previous theme set them to, so plugin
 * surfaces wouldn't match the front-end. Every other SP child theme has
 * this same hook with its own palette.
 */
add_action( 'after_switch_theme', function () {
    // Read the option directly, not the plugin's sp_settings() wrapper: the
    // plugin may be inactive when a theme is switched, so its functions can't
    // be assumed to exist here.
    $settings = get_option( 'societypress_settings', [] );

    $settings['design_color_primary']       = '#3C1053';
    $settings['design_color_primary_hover'] = '#9A4F5A';
    $settings['design_color_accent']        = '#9A4F5A';
    $settings['design_color_header_bg']     = '#3C1053';
    $settings['design_color_header_text']   = '#FFF8F0';
    $settings['design_color_footer_bg']     = '#3C1053';
    $settings['design_color_footer_text']   = '#FFF8F0';
    $settings['design_font_body']           = 'eb-garamond';
    $settings['design_font_heading']        = 'eb-garamond';

    update_option( 'societypress_settings', $settings );
} );

add_action( 'wp_enqueue_scripts', function () {

    // Google Fonts — EB Garamond with italic support
    // WHY EB Garamond: An elegant, old-style serif that evokes fine printing
    // and literary tradition. The slight calligraphic character feels refined
    // without sacrificing readability at body sizes.
    wp_enqueue_style(
        'parlor-google-fonts',
        'https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap',
        [],
        null
    );

    // Child stylesheet — loads after parent so our :root overrides win.
    wp_enqueue_style(
        'parlor-style',
        get_stylesheet_uri(),
        [ 'societypress-style', 'parlor-google-fonts' ],
        PARLOR_THEME_VERSION
    );
} );

/**
 * Register widget areas for the Parlor theme.
 *
 * WHY: footer.php references parlor-footer-1 and parlor-footer-2; sidebar.php
 * and page.php reference parlor-sidebar. Without register_sidebar() calls for
 * these IDs, WordPress never marks them active, so is_active_sidebar() always
 * returns false and admins cannot add widgets to any of them. Every other SP
 * child theme has this block — Parlor was missing it.
 */
add_action( 'widgets_init', function () {

    // Sidebar — displayed on interior pages when widgets are present
    register_sidebar( [
        'name'          => esc_html__( 'Parlor Sidebar', 'parlor' ),
        'id'            => 'parlor-sidebar',
        'description'   => esc_html__( 'Sidebar widget area — displayed on interior pages when widgets are added.', 'parlor' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ] );

    // Footer column 1 — typically organization description or about text
    register_sidebar( [
        'name'          => esc_html__( 'Footer Column 1', 'parlor' ),
        'id'            => 'parlor-footer-1',
        'description'   => esc_html__( 'First footer column — typically organization description.', 'parlor' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ] );

    // Footer column 2 — typically quick links or navigation
    register_sidebar( [
        'name'          => esc_html__( 'Footer Column 2', 'parlor' ),
        'id'            => 'parlor-footer-2',
        'description'   => esc_html__( 'Second footer column — typically quick links or contact information.', 'parlor' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ] );
} );
