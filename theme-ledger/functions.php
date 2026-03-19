<?php
/**
 * Ledger Child Theme — Functions
 *
 * WHY: Enqueue Source Sans 3 from Google Fonts. A professional, authoritative
 * sans-serif that pairs with Ledger's formal, archival personality.
 *
 * @package Ledger
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'ledger-google-fonts',
        'https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,300;0,400;0,600;0,700;1,400&display=swap',
        [],
        null
    );
}, 5 );
