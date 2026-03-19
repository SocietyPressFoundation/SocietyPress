<?php
/**
 * Coastline Child Theme — Functions
 *
 * WHY: Enqueue the Inter font from Google Fonts. Inter is the default for
 * Coastline's clean, modern look. Loads at priority 5 to be available
 * before the parent stylesheet references it.
 *
 * @package Coastline
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'coastline-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
        [],
        null
    );
}, 5 );
