<?php
/**
 * Plugin Information Endpoint
 *
 * Provides detailed plugin information for WordPress update popup.
 *
 * WHY: WordPress "View details" link needs full plugin description, changelog, etc.
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
$plugin_slug = $data['plugin_slug'] ?? '';
$license_key = $data['license_key'] ?? '';
$site_url    = $data['site_url'] ?? '';

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

// Get version info
$version = get_version_info();

if ( ! $version ) {
	http_response_code( 500 );
	echo json_encode( array( 'error' => 'Failed to retrieve plugin information' ) );
	exit;
}

// Generate download token
$token = generate_download_token( $license_key, $site_url, $version['version'] );

if ( ! $token ) {
	http_response_code( 500 );
	echo json_encode( array( 'error' => 'Failed to generate download token' ) );
	exit;
}

// Build download URL
$download_url = sprintf(
	'https://stricklindevelopment.com/api/v1/plugins/societypress/download?license=%s&site=%s&token=%s',
	urlencode( $license_key ),
	urlencode( $site_url ),
	urlencode( $token )
);

// Return full plugin info (WordPress format)
$response = (object) array(
	'name'          => 'SocietyPress',
	'slug'          => 'societypress',
	'version'       => $version['version'],
	'author'        => '<a href="https://charlesstricklin.com">Charles Stricklin</a>',
	'homepage'      => 'https://charlesstricklin.com/societypress',
	'requires'      => '6.0',
	'requires_php'  => '8.0',
	'tested'        => '6.4',
	'last_updated'  => $version['released_at'] ?? date( 'Y-m-d H:i:s' ),
	'sections'      => array(
		'description'  => get_description(),
		'changelog'    => get_changelog( $version ),
		'installation' => get_installation_instructions(),
		'faq'          => get_faq(),
	),
	'download_link' => $download_url,
	'icons'         => array(
		'1x' => 'https://stricklindevelopment.com/assets/societypress/icon-128x128.png',
		'2x' => 'https://stricklindevelopment.com/assets/societypress/icon-256x256.png',
	),
	'banners'       => array(
		'low'  => 'https://stricklindevelopment.com/assets/societypress/banner-772x250.png',
		'high' => 'https://stricklindevelopment.com/assets/societypress/banner-1544x500.png',
	),
);

echo json_encode( $response );

/**
 * Get version information.
 *
 * @return array|false Version info or false on failure.
 */
function get_version_info() {
	$versions_file = __DIR__ . '/../../../../versions/latest.json';

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
 * Get plugin description HTML.
 *
 * @return string Description HTML.
 */
function get_description(): string {
	return '<h3>Membership Management for Genealogical &amp; Historical Societies</h3>
<p>SocietyPress is a comprehensive membership management solution designed specifically for genealogical societies, historical societies, and heritage organizations.</p>

<h4>Key Features</h4>
<ul>
	<li><strong>Member Management</strong> - Full CRUD operations with custom genealogical fields</li>
	<li><strong>Membership Tiers</strong> - Individual, Family, Student, Lifetime, Institutional</li>
	<li><strong>CSV Import/Export</strong> - Intelligent field mapping supports Wild Apricot and other systems</li>
	<li><strong>Member Portal</strong> - Self-service interface for members to update their info</li>
	<li><strong>Public Directory</strong> - Searchable, filterable member directory with privacy controls</li>
	<li><strong>Committee Management</strong> - Track positions and committee assignments</li>
	<li><strong>Automated Notifications</strong> - Renewal reminders, welcome emails, expiration notices</li>
	<li><strong>Security</strong> - AES-256-GCM encryption for sensitive data</li>
</ul>

<h4>Built for Volunteers</h4>
<p>Designed for the volunteers who actually run these organizations. Senior-friendly UI, no subscriptions required, your data stays on your server.</p>';
}

/**
 * Get changelog HTML.
 *
 * @param array $version Version info.
 * @return string Changelog HTML.
 */
function get_changelog( array $version ): string {
	// In production, this should be stored in a separate file or database
	$changelog = '<h4>' . esc_html( $version['version'] ) . '</h4>';
	$changelog .= '<ul>';
	$changelog .= '<li>' . esc_html( $version['changelog'] ?? 'No changelog available' ) . '</li>';
	$changelog .= '</ul>';

	return $changelog;
}

/**
 * Get installation instructions HTML.
 *
 * @return string Installation HTML.
 */
function get_installation_instructions(): string {
	return '<ol>
	<li>Ensure your WordPress site meets the requirements (WordPress 6.0+, PHP 8.0+)</li>
	<li>Click "Update Now" to automatically install the update</li>
	<li>After updating, visit Settings > SocietyPress to verify your license is still active</li>
	<li>Review the changelog for any new features or breaking changes</li>
</ol>';
}

/**
 * Get FAQ HTML.
 *
 * @return string FAQ HTML.
 */
function get_faq(): string {
	return '<h4>Will my data be preserved during the update?</h4>
<p>Yes. Updates preserve all your member data, settings, and configurations. Always back up your database before updating, as with any WordPress plugin.</p>

<h4>Do I need to reactivate my license after updating?</h4>
<p>No. Your license remains active after updates. The plugin will automatically verify your license after the update completes.</p>

<h4>Can I roll back to a previous version?</h4>
<p>Yes. Download previous versions from your account at <a href="https://charlesstricklin.com/account">charlesstricklin.com/account</a>.</p>

<h4>What if the update fails?</h4>
<p>WordPress keeps a backup of the previous version during updates. If an update fails, WordPress will automatically restore the previous version. Contact support if you need assistance.</p>';
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
