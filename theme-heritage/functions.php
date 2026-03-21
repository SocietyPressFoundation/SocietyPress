<?php
/**
 * Heritage Child Theme — Functions
 *
 * WHY: This file does two things:
 * 1. Loads the Merriweather font from Google Fonts (Heritage's warm serif)
 * 2. Enqueues the child stylesheet so our :root color/font overrides actually
 *    take effect. Without this, WordPress would only load the parent CSS and
 *    Heritage's style.css would never be included in the page.
 *
 * NOTE: The parent theme's functions.php enqueues the parent stylesheet
 * ('societypress-style'). We declare it as a dependency of our child stylesheet
 * so WordPress guarantees the load order: Google Font → Parent → Child.
 *
 * @package Heritage
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'HERITAGE_THEME_VERSION', '1.0.0' );

add_action( 'wp_enqueue_scripts', function () {

    // Google Fonts — Merriweather with italic support for body text
    // WHY Merriweather: A warm, readable serif that says "library" without
    // feeling stuffy. Excellent at body sizes for older readers.
    wp_enqueue_style(
        'heritage-google-fonts',
        'https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,400&display=swap',
        [],
        null
    );

    // Child stylesheet — loads after parent so our :root overrides win.
    // WHY the dependency chain: WordPress uses the deps array to determine
    // output order in the HTML. By depending on both 'societypress-style'
    // (parent) and 'heritage-google-fonts', we guarantee the font is
    // available before the stylesheet references it, and the parent's
    // base layout loads before our overrides.
    wp_enqueue_style(
        'heritage-style',
        get_stylesheet_uri(),
        [ 'societypress-style', 'heritage-google-fonts' ],
        HERITAGE_THEME_VERSION
    );
} );
