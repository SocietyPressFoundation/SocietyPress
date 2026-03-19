<?php
/**
 * Prairie Child Theme — Functions
 *
 * WHY: Enqueue the Lora font from Google Fonts. Lora is a warm, readable serif
 * that fits Prairie's earthy, approachable personality.
 *
 * @package Prairie
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'prairie-google-fonts',
        'https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap',
        [],
        null
    );
}, 5 );
