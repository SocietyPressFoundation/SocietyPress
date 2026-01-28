<?php
/**
 * Theme Download Endpoint
 *
 * Serves theme ZIP files with license authentication.
 *
 * WHY: WordPress downloads theme updates from here after token validation.
 */

require_once __DIR__ . '/../../../../includes/license-validator.php';

// Only accept GET requests
if ( 'GET' !== $_SERVER['REQUEST_METHOD'] ) {
	http_response_code( 405 );
	header( 'Content-Type: application/json' );
	echo json_encode( array( 'error' => 'Method not allowed' ) );
	exit;
}

// Get query parameters
$license_key = $_GET['license'] ?? '';
$site_url    = $_GET['site'] ?? '';
$token       = $_GET['token'] ?? '';

// Validate required parameters
if ( empty( $license_key ) || empty( $site_url ) || empty( $token ) ) {
	http_response_code( 400 );
	header( 'Content-Type: application/json' );
	echo json_encode( array( 'error' => 'Missing required parameters' ) );
	exit;
}

// Validate download token
if ( ! validate_download_token( $token, $license_key, $site_url ) ) {
	http_response_code( 403 );
	header( 'Content-Type: application/json' );
	echo json_encode( array( 'error' => 'Invalid or expired download token' ) );
	exit;
}

// Get latest version file
$version_info = get_latest_theme_version();

if ( ! $version_info ) {
	http_response_code( 500 );
	header( 'Content-Type: application/json' );
	echo json_encode( array( 'error' => 'Failed to retrieve version information' ) );
	exit;
}

// Build file path
$file_path = __DIR__ . '/../../../../versions/themes/societypress-' . $version_info['version'] . '.zip';

// Check if file exists
if ( ! file_exists( $file_path ) ) {
	http_response_code( 404 );
	header( 'Content-Type: application/json' );
	echo json_encode( array(
		'error'   => 'Theme file not found',
		'version' => $version_info['version'],
	) );
	error_log( 'SocietyPress Theme download: File not found - ' . $file_path );
	exit;
}

// Validate file is readable
if ( ! is_readable( $file_path ) ) {
	http_response_code( 500 );
	header( 'Content-Type: application/json' );
	echo json_encode( array( 'error' => 'Theme file is not readable' ) );
	error_log( 'SocietyPress Theme download: File not readable - ' . $file_path );
	exit;
}

// Get file info
$file_name = basename( $file_path );
$file_size = filesize( $file_path );

// Log download
error_log( sprintf(
	'SocietyPress Theme download: %s by %s from %s',
	$version_info['version'],
	$license_key,
	$site_url
) );

// Set headers for file download
header( 'Content-Type: application/zip' );
header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );
header( 'Content-Length: ' . $file_size );
header( 'Cache-Control: no-cache, must-revalidate' );
header( 'Expires: 0' );
header( 'Pragma: public' );

// Disable output buffering for large files
if ( ob_get_level() ) {
	ob_end_clean();
}

// Stream file in chunks
$chunk_size = 8192; // 8KB chunks
$handle     = fopen( $file_path, 'rb' );

if ( false === $handle ) {
	http_response_code( 500 );
	error_log( 'SocietyPress Theme download: Failed to open file - ' . $file_path );
	exit;
}

while ( ! feof( $handle ) ) {
	echo fread( $handle, $chunk_size );
	flush();
}

fclose( $handle );
exit;

/**
 * Get latest theme version information.
 *
 * @return array|false Version info or false on failure.
 */
function get_latest_theme_version() {
	$versions_file = __DIR__ . '/../../../../versions/themes/societypress-latest.json';

	if ( ! file_exists( $versions_file ) ) {
		// Fallback to hardcoded version
		return array(
			'version'     => '1.0.0',
			'released_at' => '2026-01-25',
		);
	}

	$content = file_get_contents( $versions_file );
	$data    = json_decode( $content, true );

	return $data ?: false;
}
