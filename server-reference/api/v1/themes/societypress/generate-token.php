<?php
/**
 * Theme Download Token Generator
 *
 * Generates short-lived download tokens for theme downloads.
 *
 * WHY: Separates token generation from update check for security.
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
$license_key = $data['license_key'] ?? '';
$site_url    = $data['site_url'] ?? '';
$version     = $data['version'] ?? '';

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

// Generate token
$token = generate_download_token( $license_key, $site_url, 'theme-' . $version );

if ( ! $token ) {
	http_response_code( 500 );
	echo json_encode( array( 'error' => 'Failed to generate download token' ) );
	exit;
}

// Return token
echo json_encode( array( 'token' => $token ) );
