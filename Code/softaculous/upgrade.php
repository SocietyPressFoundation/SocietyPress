<?php
/**
 * SocietyPress — Softaculous Upgrade Script
 *
 * WHY: Called by Softaculous after extracting updated files during an upgrade.
 * SocietyPress handles its own database migrations via dbDelta on plugin
 * activation, so all we need to do here is trigger a plugin reactivation
 * cycle so the new code runs its migration checks.
 *
 * The heavy lifting (new tables, new columns, settings migrations) is all
 * inside sp_create_tables() in the plugin — this just makes sure it fires.
 */


function __upgrade() {
    global $__settings, $error;

    $path = $__settings['softpath'];

    // Load WordPress
    if ( ! defined( 'ABSPATH' ) ) {
        define( 'ABSPATH', $path . '/' );
    }

    require_once $path . '/wp-load.php';
    require_once $path . '/wp-admin/includes/plugin.php';

    // Find the SocietyPress plugin file
    $plugin_file = '';
    if ( file_exists( $path . '/wp-content/plugins/societypress/societypress.php' ) ) {
        $plugin_file = 'societypress/societypress.php';
    } elseif ( file_exists( $path . '/wp-content/plugins/societypress.php' ) ) {
        $plugin_file = 'societypress.php';
    }

    if ( empty( $plugin_file ) ) {
        $error[] = 'SocietyPress plugin file not found after upgrade.';
        return false;
    }

    // Deactivate and reactivate to trigger dbDelta migrations
    deactivate_plugins( $plugin_file );
    activate_plugin( $plugin_file );

    // Flush rewrite rules in case new page templates were added
    flush_rewrite_rules();

    return true;
}
