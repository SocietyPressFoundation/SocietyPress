<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?php bloginfo('name'); ?> - Under Maintenance</title>
    <?php wp_head(); ?>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-icon">🔧</div>
        <h1>We're Making Improvements</h1>
        <p class="maintenance-message">
            Our website is currently undergoing scheduled maintenance.
            We're working hard to bring you a better experience and will be back shortly.
        </p>
        <p class="maintenance-note">
            Thank you for your patience!
        </p>
    </div>
    <?php wp_footer(); ?>
</body>
</html>
