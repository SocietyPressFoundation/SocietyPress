<?php
/**
 * Check Installation Status
 *
 * Verifies database tables and test license were created.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/includes/db.php';

echo "<h1>Installation Status Check</h1>\n";

try {
	$pdo = get_db_connection();
	echo "<p style='color: green;'>✓ Database connected</p>\n";

	// Check tables exist
	$tables = array('licenses', 'license_activations', 'download_tokens');

	echo "<h3>Database Tables:</h3>\n";
	echo "<ul>\n";

	foreach ($tables as $table) {
		$stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
		$exists = $stmt->rowCount() > 0;

		if ($exists) {
			// Count records
			$count = $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
			echo "<li style='color: green;'>✓ {$table} ({$count} records)</li>\n";
		} else {
			echo "<li style='color: red;'>✗ {$table} - MISSING</li>\n";
		}
	}
	echo "</ul>\n";

	// Check for test license
	echo "<h3>Test License:</h3>\n";
	$stmt = $pdo->query("SELECT * FROM licenses WHERE license_key = 'TEST-1234-5678-9012'");
	$license = $stmt->fetch();

	if ($license) {
		echo "<p style='color: green;'>✓ Test license found</p>\n";
		echo "<table border='1' cellpadding='5'>\n";
		echo "<tr><th>License Key</th><td>{$license->license_key}</td></tr>\n";
		echo "<tr><th>Email</th><td>{$license->email}</td></tr>\n";
		echo "<tr><th>Status</th><td>{$license->status}</td></tr>\n";
		echo "<tr><th>Type</th><td>{$license->license_type}</td></tr>\n";
		echo "</table>\n";
	} else {
		echo "<p style='color: orange;'>⚠ Test license not found (may have been deleted)</p>\n";
	}

	echo "<hr>\n";
	echo "<h3>Next Steps:</h3>\n";
	echo "<ol>\n";
	echo "<li>Upload plugin ZIP: <code>versions/societypress-1.0.0.zip</code></li>\n";
	echo "<li>Upload theme ZIP: <code>versions/themes/societypress-1.0.0.zip</code></li>\n";
	echo "<li>Test endpoints: <code>php test-endpoints.php https://stricklindevelopment.com</code></li>\n";
	echo "</ol>\n";

} catch (Exception $e) {
	echo "<p style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}
