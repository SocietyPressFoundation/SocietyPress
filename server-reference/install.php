<?php
/**
 * Installation Script for SocietyPress Update Server
 *
 * Creates database tables and sample license for testing.
 *
 * SECURITY WARNING: Delete this file after running on production server!
 *
 * Usage:
 *   1. Edit includes/db.php with your database credentials
 *   2. Navigate to this file in your browser or run: php install.php
 *   3. Delete this file after successful installation
 */

// Prevent running twice
if ( file_exists( __DIR__ . '/.installed' ) ) {
	die( "Installation already completed. Delete .installed file to run again.\n" );
}

require_once __DIR__ . '/includes/db.php';

echo "<h1>SocietyPress Update Server - Installation</h1>\n";

try {
	// Test database connection
	echo "<p>Testing database connection...</p>\n";
	$pdo = get_db_connection();
	echo "<p style='color: green;'>✓ Database connection successful</p>\n";

	// Create tables
	echo "<p>Creating database tables...</p>\n";
	create_tables();
	echo "<p style='color: green;'>✓ Tables created successfully</p>\n";

	// Create sample license for testing
	echo "<p>Creating test license...</p>\n";

	$stmt = $pdo->prepare( "
		INSERT INTO licenses (license_key, email, license_type, status, sites_allowed, created_at)
		VALUES (:license_key, :email, :license_type, :status, :sites_allowed, NOW())
		ON DUPLICATE KEY UPDATE id=id
	" );

	$test_license = 'TEST-1234-5678-9012';

	$stmt->execute( array(
		':license_key'   => $test_license,
		':email'         => 'test@example.com',
		':license_type'  => 'site',
		':status'        => 'active',
		':sites_allowed' => 1,
	) );

	echo "<p style='color: green;'>✓ Test license created</p>\n";
	echo "<p><strong>Test License Key:</strong> {$test_license}</p>\n";
	echo "<p><strong>Test Email:</strong> test@example.com</p>\n";

	// Check for versions directory
	echo "<p>Checking file structure...</p>\n";

	$versions_dir = __DIR__ . '/versions';
	if ( ! is_dir( $versions_dir ) ) {
		mkdir( $versions_dir, 0755, true );
		echo "<p style='color: green;'>✓ Created versions directory</p>\n";
	} else {
		echo "<p style='color: green;'>✓ Versions directory exists</p>\n";
	}

	$assets_dir = __DIR__ . '/assets';
	if ( ! is_dir( $assets_dir ) ) {
		mkdir( $assets_dir, 0755, true );
		echo "<p style='color: green;'>✓ Created assets directory</p>\n";
	} else {
		echo "<p style='color: green;'>✓ Assets directory exists</p>\n";
	}

	// Mark installation complete
	file_put_contents( __DIR__ . '/.installed', date( 'Y-m-d H:i:s' ) );

	echo "<hr>\n";
	echo "<h2 style='color: green;'>Installation Complete!</h2>\n";
	echo "<h3>Next Steps:</h3>\n";
	echo "<ol>\n";
	echo "  <li>Delete this install.php file for security</li>\n";
	echo "  <li>Upload your plugin ZIP file to: <code>versions/societypress-1.0.0.zip</code></li>\n";
	echo "  <li>Update <code>versions/latest.json</code> with current version info</li>\n";
	echo "  <li>Upload plugin icons and banners to <code>assets/</code> directory</li>\n";
	echo "  <li>Test the endpoints using: <code>php test-endpoints.php</code></li>\n";
	echo "  <li>Update WordPress plugin to use: <code>https://stricklindevelopment.com</code></li>\n";
	echo "</ol>\n";

	echo "<h3>Test the Server:</h3>\n";
	echo "<p>Run this command to test:</p>\n";
	echo "<pre>curl -X POST " . get_base_url() . "/api/v1/plugins/societypress/update-check \\
  -H \"Content-Type: application/json\" \\
  -d '{\"plugin_slug\":\"societypress\",\"current_version\":\"1.0.0\",\"license_key\":\"" . $test_license . "\",\"site_url\":\"localhost\"}'</pre>\n";

} catch ( Exception $e ) {
	echo "<p style='color: red;'>✗ Error: " . htmlspecialchars( $e->getMessage() ) . "</p>\n";
	echo "<p>Check your database credentials in <code>includes/db.php</code></p>\n";
	exit( 1 );
}

/**
 * Get base URL for this installation.
 *
 * @return string Base URL.
 */
function get_base_url(): string {
	$protocol = isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http';
	$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
	$dir      = dirname( $_SERVER['PHP_SELF'] );

	return $protocol . '://' . $host . rtrim( $dir, '/' );
}
