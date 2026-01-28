<?php
/**
 * Theme Information Endpoint
 *
 * Provides detailed theme information for WordPress update popup.
 *
 * WHY: WordPress "View details" link needs full theme description, changelog, etc.
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
$theme_slug  = $data['theme_slug'] ?? '';
$license_key = $data['license_key'] ?? '';
$site_url    = $data['site_url'] ?? '';

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

// Get version info
$version = get_theme_version_info();

if ( ! $version ) {
	http_response_code( 500 );
	echo json_encode( array( 'error' => 'Failed to retrieve theme information' ) );
	exit;
}

// Return full theme info (WordPress format)
$response = (object) array(
	'name'         => 'SocietyPress',
	'slug'         => 'societypress',
	'version'      => $version['version'],
	'author'       => 'Charles Stricklin',
	'homepage'     => 'https://charlesstricklin.com/societypress',
	'requires'     => '6.0',
	'requires_php' => '8.0',
	'tested'       => '6.4',
	'last_updated' => $version['released_at'] ?? date( 'Y-m-d H:i:s' ),
	'sections'     => array(
		'description' => get_theme_description(),
		'changelog'   => get_theme_changelog( $version ),
	),
);

echo json_encode( $response );

/**
 * Get theme version information.
 *
 * @return array|false Version info or false on failure.
 */
function get_theme_version_info() {
	$versions_file = __DIR__ . '/../../../../versions/themes/societypress-latest.json';

	if ( ! file_exists( $versions_file ) ) {
		return array(
			'version'     => '1.0.0',
			'released_at' => '2026-01-25 10:00:00',
			'changelog'   => 'Initial release',
		);
	}

	$content = file_get_contents( $versions_file );
	$data    = json_decode( $content, true );

	return $data ?: false;
}

/**
 * Get theme description HTML.
 *
 * @return string Description HTML.
 */
function get_theme_description(): string {
	return '<h3>Custom Theme for Genealogical &amp; Historical Societies</h3>
<p>SocietyPress is a clean, professional WordPress theme designed specifically to complement the SocietyPress membership management plugin.</p>

<h4>Features</h4>
<ul>
	<li><strong>Senior-Friendly Design</strong> - Large text, high contrast, easy navigation</li>
	<li><strong>Membership Integration</strong> - Seamless integration with SocietyPress plugin features</li>
	<li><strong>Responsive Layout</strong> - Works on all devices</li>
	<li><strong>Clean Typography</strong> - Easy to read for all age groups</li>
	<li><strong>Professional Appearance</strong> - Suitable for established organizations</li>
</ul>

<h4>Requirements</h4>
<p>This theme requires the SocietyPress plugin to be installed and activated.</p>';
}

/**
 * Get theme changelog HTML.
 *
 * @param array $version Version info.
 * @return string Changelog HTML.
 */
function get_theme_changelog( array $version ): string {
	// In production, this should be stored in a separate file or database
	$changelog = '<h4>' . esc_html( $version['version'] ) . '</h4>';
	$changelog .= '<ul>';
	$changelog .= '<li>' . esc_html( $version['changelog'] ?? 'No changelog available' ) . '</li>';
	$changelog .= '</ul>';

	return $changelog;
}

/**
 * Escape HTML for output.
 *
 * @param string $text Text to escape.
 * @return string Escaped text.
 */
function esc_html( string $text ): string {
	return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
}
