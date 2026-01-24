<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<header class="site-header">
    <div class="container">
        <h1 class="site-title"><a href="<?php echo home_url(); ?>"><?php bloginfo('name'); ?></a></h1>
        <?php
        wp_nav_menu([
            'theme_location' => 'primary',
            'menu_id' => 'primary-menu',
            'container' => 'nav',
            'container_class' => 'primary-menu'
        ]);
        ?>
    </div>
</header>
