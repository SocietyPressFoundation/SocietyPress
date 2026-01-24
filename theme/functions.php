<?php
function societypress_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    register_nav_menus([
        'primary' => 'Primary Menu',
    ]);
}
add_action('after_setup_theme', 'societypress_theme_setup');

function societypress_scripts() {
    wp_enqueue_style('societypress-style', get_stylesheet_uri());
}
add_action('wp_enqueue_scripts', 'societypress_scripts');
