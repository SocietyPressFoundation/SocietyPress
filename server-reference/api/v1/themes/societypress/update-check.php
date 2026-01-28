<?php
/**
 * Theme Update Check Endpoint
 *
 * Checks if a newer version of SocietyPress theme is available.
 *
 * WHY: WordPress calls this endpoint to check for theme updates.
 */

header( 'Content-Type: application/json' );

// Only accept POST requests
if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
	http_response_code( 405 );
	echo json_encode( array( 'error' => 'Method not allowed' ) );
	exit;
}

require_once __DIR__ . '/../../../../includes/license-validator.php';

// Get request data
$input = file_get_contents( 'php://input' );
$data  = json_decode( $input, true );

if ( ! $data ) {
	http_response_code( 400 );
	echo json_encode( array( 'error' => 'Invalid JSON' ) );
	exit;
}

// Extract required fields
$theme_slug      = $data['theme_slug'] ?? '';
$current_version = $data['current_version'] ?? '';
$license_key     = $data['license_key'] ?? '';
$site_url        = $data['site_url'] ?? '';

// Validate theme slug
if ( 'societypress' !== $theme_slug ) {
	http_response_code( 404 );
	echo json_encode( array( 'error' => 'Theme not found' ) );
	exit;
}

// Validate license
$validation = validate_license( $license_key, $site_url );

if ( ! $validation['valid'] ) {
	http_response_code( 403 );
	echo json_encode( array(
		'error'   => 'License validation failed',
		'message' => $validation['message'] ?? 'Invalid license',
	) );
	exit;
}

// Get latest version info
$latest_version = get_latest_theme_version();

if ( ! $latest_version ) {
	http_response_code( 500 );
	echo json_encode( array( 'error' => 'Failed to retrieve version information' ) );
	exit;
}

// Return update info
$response = array(
	'version'      => $latest_version['version'],
	'homepage'     => 'https://charlesstricklin.com/societypress',
	'requires'     => '6.0',
	'requires_php' => '8.0',
	'tested'       => '6.4',
);

echo json_encode( $response );

/**
 * Get latest theme version information.
 *
 * WHY: Retrieves current version metadata.
 * In production, load from database or JSON file.
 *
 * @return array|false Version info or false on failure.
 */
function get_latest_theme_version() {
	// Path to version metadata file
	$versions_file = __DIR__ . '/../../../../versions/themes/societypress-latest.json';

	if ( ! file_exists( $versions_file ) ) {
		// Fallback to hardcoded version
		return array(
			'version'     => '1.0.0',
			'released_at' => '2026-01-25',
			'changelog'   => 'Initial release',
		);
	}

	$content = file_get_contents( $versions_file );
	$data    = json_decode( $content, true );

	if ( ! $data || empty( $data['version'] ) ) {
		return false;
	}

	return $data;
}
