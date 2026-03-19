<?php
/**
 * Heritage Child Theme — Functions
 *
 * WHY: The only job of this file is to enqueue the Google Font that Heritage
 * uses (Merriweather). The parent theme handles everything else. We enqueue
 * the font at priority 5 so it loads before the parent stylesheet references it.
 *
 * @package Heritage
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue the Heritage font (Merriweather) from Google Fonts.
 */
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'heritage-google-fonts',
        'https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,400&display=swap',
        [],
        null
    );
}, 5 );
