<?php
/**
 * Debug Plugin Activation
 *
 * Upload this to your WordPress root and visit it in browser to check for errors.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>SocietyPress Debug</h1>\n";

// Check PHP version
echo "<h2>PHP Version</h2>\n";
echo "<p>Current: " . PHP_VERSION . "</p>\n";
echo "<p>Required: 8.0+</p>\n";
if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    echo "<p style='color: red;'>ERROR: PHP version too old!</p>\n";
}

// Check if plugin file exists
$plugin_file = __DIR__ . '/wp-content/plugins/societypress/societypress.php';
echo "<h2>Plugin File</h2>\n";
echo "<p>Looking for: {$plugin_file}</p>\n";
if (file_exists($plugin_file)) {
    echo "<p style='color: green;'>✓ File exists</p>\n";

    // Try to load it
    echo "<h2>Loading Plugin</h2>\n";
    try {
        define('ABSPATH', __DIR__ . '/');

        // Mock WordPress functions that plugin might call
        if (!function_exists('plugin_dir_path')) {
            function plugin_dir_path($file) { return dirname($file) . '/'; }
        }
        if (!function_exists('plugin_dir_url')) {
            function plugin_dir_url($file) { return 'http://example.com/wp-content/plugins/societypress/'; }
        }
        if (!function_exists('plugin_basename')) {
            function plugin_basename($file) { return 'societypress/societypress.php'; }
        }
        if (!function_exists('register_activation_hook')) {
            function register_activation_hook($file, $callback) { }
        }
        if (!function_exists('register_deactivation_hook')) {
            function register_deactivation_hook($file, $callback) { }
        }
        if (!function_exists('add_action')) {
            function add_action($hook, $callback) { }
        }
        if (!function_exists('add_filter')) {
            function add_filter($hook, $callback) { }
        }
        if (!function_exists('is_admin')) {
            function is_admin() { return false; }
        }

        require_once $plugin_file;

        echo "<p style='color: green;'>✓ Plugin loaded without errors!</p>\n";
        echo "<p>Defined constants:</p>\n";
        echo "<ul>\n";
        if (defined('SOCIETYPRESS_VERSION')) echo "<li>SOCIETYPRESS_VERSION: " . SOCIETYPRESS_VERSION . "</li>\n";
        if (defined('SOCIETYPRESS_PATH')) echo "<li>SOCIETYPRESS_PATH: " . SOCIETYPRESS_PATH . "</li>\n";
        if (defined('SOCIETYPRESS_URL')) echo "<li>SOCIETYPRESS_URL: " . SOCIETYPRESS_URL . "</li>\n";
        echo "</ul>\n";

    } catch (Throwable $e) {
        echo "<pre style='color: red; background: #fee; padding: 10px; border: 2px solid red;'>";
        echo "ERROR: " . $e->getMessage() . "\n\n";
        echo "File: " . $e->getFile() . "\n";
        echo "Line: " . $e->getLine() . "\n\n";
        echo "Stack trace:\n" . $e->getTraceAsString();
        echo "</pre>";
    }
} else {
    echo "<p style='color: red;'>✗ File not found!</p>\n";
    echo "<p>Check that plugin is uploaded to correct location.</p>\n";
}

echo "\n<hr>\n";
echo "<p><strong>Next steps:</strong></p>\n";
echo "<ol>\n";
echo "<li>If you see errors above, send them to me</li>\n";
echo "<li>Check WordPress error log at: wp-content/debug.log</li>\n";
echo "<li>Check server error logs in cPanel or hosting control panel</li>\n";
echo "</ol>\n";
