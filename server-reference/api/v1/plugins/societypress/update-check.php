<?php
/**
 * Update Check Endpoint
 *
 * Checks if a newer version of SocietyPress is available.
 *
 * WHY: WordPress calls this endpoint to check for plugin updates.
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
$plugin_slug     = $data['plugin_slug'] ?? '';
$current_version = $data['current_version'] ?? '';
$license_key     = $data['license_key'] ?? '';
$site_url        = $data['site_url'] ?? '';

// Validate plugin slug
if ( 'societypress' !== $plugin_slug ) {
	http_response_code( 404 );
	echo json_encode( array( 'error' => 'Plugin not found' ) );
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
// In production, this should be stored in database or config file
$latest_version = get_latest_version();

if ( ! $latest_version ) {
	http_response_code( 500 );
	echo json_encode( array( 'error' => 'Failed to retrieve version information' ) );
	exit;
}

// Generate download token
$token = generate_download_token( $license_key, $site_url, $latest_version['version'] );

if ( ! $token ) {
	http_response_code( 500 );
	echo json_encode( array( 'error' => 'Failed to generate download token' ) );
	exit;
}

// Build download URL with authentication
$download_url = sprintf(
	'https://stricklindevelopment.com/api/v1/plugins/societypress/download?license=%s&site=%s&token=%s',
	urlencode( $license_key ),
	urlencode( $site_url ),
	urlencode( $token )
);

// Return update info
$response = array(
	'version'      => $latest_version['version'],
	'download_url' => $download_url,
	'homepage'     => 'https://charlesstricklin.com/societypress',
	'requires'     => '6.0',
	'requires_php' => '8.0',
	'tested'       => '6.4',
	'icons'        => array(
		'1x' => 'https://stricklindevelopment.com/assets/societypress/icon-128x128.png',
		'2x' => 'https://stricklindevelopment.com/assets/societypress/icon-256x256.png',
	),
	'banners'      => array(
		'low'  => 'https://stricklindevelopment.com/assets/societypress/banner-772x250.png',
		'high' => 'https://stricklindevelopment.com/assets/societypress/banner-1544x500.png',
	),
);

echo json_encode( $response );

/**
 * Get latest version information.
 *
 * WHY: Retrieves current version metadata.
 * In production, load from database or JSON file.
 *
 * @return array|false Version info or false on failure.
 */
function get_latest_version() {
	// Path to version metadata file
	$versions_file = __DIR__ . '/../../../../versions/latest.json';

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
