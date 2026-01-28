<?php
/**
 * Test Update Server Endpoints
 *
 * Tests all three endpoints with sample data.
 *
 * Usage:
 *   php test-endpoints.php
 *
 * Or for remote testing:
 *   php test-endpoints.php https://stricklindevelopment.com
 */

// Get base URL from command line or use localhost
$base_url = $argv[1] ?? 'http://localhost';
$base_url = rtrim( $base_url, '/' );

// Test data
$test_license = 'TEST-1234-5678-9012';
$test_site    = 'example.com';

echo "Testing SocietyPress Update Server\n";
echo "===================================\n\n";
echo "Base URL: {$base_url}\n\n";

// Test 1: Update Check
echo "1. Testing Update Check Endpoint...\n";
echo "   URL: {$base_url}/api/v1/plugins/societypress/update-check\n";

$update_check = curl_post(
	"{$base_url}/api/v1/plugins/societypress/update-check",
	array(
		'plugin_slug'     => 'societypress',
		'current_version' => '1.0.0',
		'license_key'     => $test_license,
		'site_url'        => $test_site,
		'wp_version'      => '6.4',
		'php_version'     => '8.2',
	)
);

if ( $update_check['success'] ) {
	echo "   ✓ Success (HTTP {$update_check['code']})\n";
	echo "   Response:\n";
	print_r( $update_check['data'] );
} else {
	echo "   ✗ Failed (HTTP {$update_check['code']})\n";
	echo "   Error: {$update_check['error']}\n";
}
echo "\n";

// Test 2: Plugin Info
echo "2. Testing Plugin Info Endpoint...\n";
echo "   URL: {$base_url}/api/v1/plugins/societypress/info\n";

$plugin_info = curl_post(
	"{$base_url}/api/v1/plugins/societypress/info",
	array(
		'plugin_slug' => 'societypress',
		'license_key' => $test_license,
		'site_url'    => $test_site,
	)
);

if ( $plugin_info['success'] ) {
	echo "   ✓ Success (HTTP {$plugin_info['code']})\n";
	echo "   Plugin: {$plugin_info['data']['name']} v{$plugin_info['data']['version']}\n";
	echo "   Author: {$plugin_info['data']['author']}\n";
} else {
	echo "   ✗ Failed (HTTP {$plugin_info['code']})\n";
	echo "   Error: {$plugin_info['error']}\n";
}
echo "\n";

// Test 3: Download (if we have a token from update check)
if ( $update_check['success'] && ! empty( $update_check['data']['download_url'] ) ) {
	echo "3. Testing Download Endpoint...\n";
	echo "   URL: {$update_check['data']['download_url']}\n";

	// Just check if URL is accessible (don't download full file)
	$download = curl_head( $update_check['data']['download_url'] );

	if ( $download['success'] ) {
		echo "   ✓ Success (HTTP {$download['code']})\n";
		echo "   Content-Type: {$download['content_type']}\n";
		if ( ! empty( $download['content_length'] ) ) {
			echo "   File Size: " . format_bytes( $download['content_length'] ) . "\n";
		}
	} else {
		echo "   ✗ Failed (HTTP {$download['code']})\n";
		echo "   Error: {$download['error']}\n";
	}
} else {
	echo "3. Skipping Download Test (no download URL from update check)\n";
}

echo "\n===================================\n";
echo "Testing Complete\n";

/**
 * Make POST request.
 *
 * @param string $url  URL to request.
 * @param array  $data POST data.
 * @return array Response data.
 */
function curl_post( string $url, array $data ): array {
	$ch = curl_init( $url );

	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_POST, true );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $data ) );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json' ) );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );

	$response = curl_exec( $ch );
	$code     = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	$error    = curl_error( $ch );

	curl_close( $ch );

	if ( false === $response ) {
		return array(
			'success' => false,
			'code'    => 0,
			'error'   => $error,
		);
	}

	$decoded = json_decode( $response, true );

	return array(
		'success' => $code >= 200 && $code < 300,
		'code'    => $code,
		'data'    => $decoded ?: $response,
		'error'   => isset( $decoded['error'] ) ? $decoded['error'] : null,
	);
}

/**
 * Make HEAD request.
 *
 * @param string $url URL to request.
 * @return array Response data.
 */
function curl_head( string $url ): array {
	$ch = curl_init( $url );

	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_NOBODY, true );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );

	curl_exec( $ch );
	$code           = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	$content_type   = curl_getinfo( $ch, CURLINFO_CONTENT_TYPE );
	$content_length = curl_getinfo( $ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD );
	$error          = curl_error( $ch );

	curl_close( $ch );

	return array(
		'success'        => $code >= 200 && $code < 300,
		'code'           => $code,
		'content_type'   => $content_type,
		'content_length' => $content_length,
		'error'          => $error ?: null,
	);
}

/**
 * Format bytes to human-readable size.
 *
 * @param int $bytes Bytes.
 * @return string Formatted size.
 */
function format_bytes( int $bytes ): string {
	$units = array( 'B', 'KB', 'MB', 'GB' );
	$power = $bytes > 0 ? floor( log( $bytes, 1024 ) ) : 0;
	return number_format( $bytes / pow( 1024, $power ), 2, '.', ',' ) . ' ' . $units[ $power ];
}
