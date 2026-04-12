<?php
/**
 * SocietyPress Theme Functions
 *
 * WHY: This is the theme's bootstrap file. It tells WordPress what features
 * we support, loads our stylesheet, and registers the navigation menu
 * and sidebar widget area.
 *
 * IMPORTANT: All functionality — CSS custom properties, Google Fonts loading,
 * admin bar hiding, dashicons, user menu, My Account helpers, form processing,
 * and custom avatar overrides — lives in the SocietyPress PLUGIN, not here.
 * The theme handles presentation only. The plugin handles logic. This keeps
 * child themes clean: they inherit the parent theme's layout and can override
 * templates, while all backend functionality comes from the plugin regardless
 * of which theme is active.
 *
 * @package SocietyPress
 * @since   1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// WHY the defined() guard: Child themes (e.g. the SAGHS rebuild) were
// cloned from the parent template and kept the same
// SOCIETYPRESS_THEME_VERSION define at the top of their functions.php.
// In WordPress, child theme functions.php loads BEFORE the parent's, so
// the child defines the constant first and the parent's unguarded define()
// triggers a PHP warning on every page load ("Constant already defined").
// Wrapping the define in an if-not-defined is the standard defensive
// pattern and keeps the parent theme safe against child theme copies.
// If a child theme wants its own version it should define a
// differently-named constant (e.g. SAGHS_THEME_VERSION).
if ( ! defined( 'SOCIETYPRESS_THEME_VERSION' ) ) {
	define( 'SOCIETYPRESS_THEME_VERSION', '1.0.18' );
}


// ============================================================================
// THEME SETUP
// ============================================================================

/**
 * Set up theme defaults and register support for WordPress features.
 *
 * WHY each feature:
 * - title-tag:       Let WordPress manage the <title> tag (best for SEO)
 * - post-thumbnails:  Enable featured images on posts and pages
 * - html5:           Use modern HTML5 markup for forms, comments, etc.
 * - custom-logo:     Let the site admin upload a logo through the Customizer
 *
 * WHY register_nav_menus here and not in the plugin: Navigation menus are
 * a presentation concern — different themes might want different menu
 * locations. The parent theme defines 'primary', and child themes can
 * add their own via their own functions.php.
 */
add_action( 'after_setup_theme', function () {

    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );

    add_theme_support( 'html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ]);

    add_theme_support( 'custom-logo', [
        'height'      => 100,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
    ]);

    // Register navigation menus
    register_nav_menus([
        'primary' => __( 'Primary Navigation', 'societypress' ),
        'footer'  => __( 'Footer Links', 'societypress' ),
    ]);
});


// ============================================================================
// ENQUEUE STYLES & SCRIPTS
// ============================================================================

/**
 * Load the theme's stylesheet.
 *
 * WHY: We use the theme version number as a cache-buster so browsers
 * always load the latest CSS after a theme update. Without this,
 * users might see stale styles for hours or days.
 *
 * NOTE: When a child theme is active, get_stylesheet_uri() returns the
 * CHILD theme's style.css, not the parent's. The child theme is
 * responsible for enqueuing the parent stylesheet if it needs it
 * (see the SAGHS child theme's functions.php for an example).
 */
add_action( 'wp_enqueue_scripts', function () {
    // WHY get_template_directory_uri: When a child theme is active,
    // get_stylesheet_uri() returns the CHILD theme's style.css — not ours.
    // We must explicitly point to the parent directory so our base styles
    // always load, regardless of which child theme (if any) is active.
    wp_enqueue_style(
        'societypress-style',
        get_template_directory_uri() . '/style.css',
        [],
        SOCIETYPRESS_THEME_VERSION
    );

    // WHY wp_add_inline_style here: The SocietyPress plugin exposes a
    // "Custom CSS" field on the Design settings page (Settings → Appearance
    // → Custom CSS). The admin pastes CSS into that field and it's stored
    // in societypress_settings['design_custom_css']. We attach it to our
    // stylesheet handle via wp_add_inline_style, which prints it in a
    // <style id="societypress-style-inline-css"> tag immediately after the
    // main stylesheet is loaded. Because it comes AFTER the theme CSS in
    // the cascade, it wins specificity ties without requiring !important,
    // which is exactly what an admin expects when they "override" a style.
    //
    // The plugin's sanitizer scrubs </style> and PHP tags on save, so the
    // value here is safe to print inline. We still check function_exists
    // and get_option defensively — the theme has to work if the plugin is
    // deactivated, even if the custom CSS disappears in that case.
    if ( function_exists( 'get_option' ) ) {
        $sp_settings      = get_option( 'societypress_settings', array() );
        $sp_custom_css    = isset( $sp_settings['design_custom_css'] ) ? (string) $sp_settings['design_custom_css'] : '';
        if ( $sp_custom_css !== '' ) {
            wp_add_inline_style( 'societypress-style', $sp_custom_css );
        }
    }

    // WHY theme.js: Handles the hamburger menu toggle on mobile screens.
    // Loaded in the footer (true) so it doesn't block page rendering.
    // Uses get_template_directory_uri() for the same reason as the stylesheet —
    // child themes need the parent's JS to load regardless.
    wp_enqueue_script(
        'societypress-theme',
        get_template_directory_uri() . '/js/theme.js',
        [],
        SOCIETYPRESS_THEME_VERSION,
        true
    );
});


// ============================================================================
// WIDGET AREAS
// ============================================================================

/**
 * Register the sidebar widget area.
 *
 * WHY: A sidebar gives site admins a place to add search, recent posts,
 * categories, and other widgets. It appears on single post pages by default.
 *
 * Child themes can register additional widget areas (like SAGHS's three
 * footer columns) in their own functions.php without conflicting with this.
 */
add_action( 'widgets_init', function () {
    register_sidebar([
        'name'          => 'Sidebar',
        'id'            => 'sidebar-1',
        'description'   => 'Widgets in this area appear on blog posts and archive pages.',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);
});
