<?php
/**
 * SocietyPress — Softaculous File Index
 *
 * WHY: Softaculous uses this during uninstallation to know which files and
 * directories belong to the application. Without it, uninstall would either
 * delete everything (including user uploads) or nothing. This lists only
 * the core application files — user content (uploads, backups) is preserved.
 */

$filelist = array(

    // WordPress core directories
    'wp-admin',
    'wp-includes',

    // WordPress core files
    'index.php',
    'license.txt',
    'readme.html',
    'wp-activate.php',
    'wp-blog-header.php',
    'wp-comments-post.php',
    'wp-config.php',
    'wp-config-sample.php',
    'wp-cron.php',
    'wp-links-opml.php',
    'wp-load.php',
    'wp-login.php',
    'wp-mail.php',
    'wp-settings.php',
    'wp-signup.php',
    'wp-trackback.php',
    'xmlrpc.php',
    '.htaccess',

    // SocietyPress plugin
    'wp-content/plugins/societypress',

    // SocietyPress themes
    'wp-content/themes/societypress',

    // WordPress default content (safe to remove on uninstall)
    'wp-content/plugins/akismet',
    'wp-content/plugins/hello.php',
    'wp-content/themes/twentytwentyfive',
    'wp-content/themes/twentytwentyfour',
    'wp-content/themes/twentytwentythree',

);
