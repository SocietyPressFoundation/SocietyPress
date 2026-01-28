<?php
/**
 * License Validator
 *
 * Validates license keys and site activations for update server.
 *
 * WHY: Ensures only licensed customers can receive updates.
 */

require_once __DIR__ . '/db.php';

/**
 * Validate license for a given site.
 *
 * WHY: Checks if license key is valid and activated for the requesting site.
 *
 * @param string $license_key License key to validate.
 * @param string $site_url    Site URL requesting update.
 * @return array Result with 'valid' boolean and optional 'message'.
 */
function validate_license( string $license_key, string $site_url ): array {
	if ( empty( $license_key ) || empty( $site_url ) ) {
		return array(
			'valid'   => false,
			'message' => 'License key and site URL are required',
		);
	}

	// Normalize site URL
	$site_url = normalize_site_url( $site_url );

	try {
		$pdo = get_db_connection();

		// Get license
		$stmt = $pdo->prepare( "
			SELECT * FROM licenses
			WHERE license_key = :license_key
			LIMIT 1
		" );
		$stmt->execute( array( ':license_key' => $license_key ) );
		$license = $stmt->fetch();

		if ( ! $license ) {
			return array(
				'valid'   => false,
				'message' => 'Invalid license key',
			);
		}

		// Check license status
		if ( 'active' !== $license->status ) {
			return array(
				'valid'   => false,
				'message' => 'License is ' . $license->status,
			);
		}

		// Check expiration
		if ( $license->expires_at && strtotime( $license->expires_at ) < time() ) {
			// Mark as expired
			$update = $pdo->prepare( "
				UPDATE licenses
				SET status = 'expired'
				WHERE id = :id
			" );
			$update->execute( array( ':id' => $license->id ) );

			return array(
				'valid'   => false,
				'message' => 'License has expired',
			);
		}

		// Check site activation
		$activation = $pdo->prepare( "
			SELECT * FROM license_activations
			WHERE license_id = :license_id
			AND site_url = :site_url
			LIMIT 1
		" );
		$activation->execute( array(
			':license_id' => $license->id,
			':site_url'   => $site_url,
		) );
		$site_activation = $activation->fetch();

		if ( ! $site_activation ) {
			// Check if license has available activation slots
			$active_sites = $pdo->prepare( "
				SELECT COUNT(*) as count FROM license_activations
				WHERE license_id = :license_id
			" );
			$active_sites->execute( array( ':license_id' => $license->id ) );
			$count = $active_sites->fetch()->count;

			if ( $count >= $license->sites_allowed ) {
				return array(
					'valid'   => false,
					'message' => 'License activation limit reached',
				);
			}

			// Auto-activate site (first update check activates)
			$insert = $pdo->prepare( "
				INSERT INTO license_activations (license_id, site_url, activated_at, last_check)
				VALUES (:license_id, :site_url, NOW(), NOW())
			" );
			$insert->execute( array(
				':license_id' => $license->id,
				':site_url'   => $site_url,
			) );
		} else {
			// Update last check time
			$update = $pdo->prepare( "
				UPDATE license_activations
				SET last_check = NOW()
				WHERE id = :id
			" );
			$update->execute( array( ':id' => $site_activation->id ) );
		}

		return array(
			'valid'   => true,
			'license' => $license,
		);
	} catch ( PDOException $e ) {
		error_log( 'License validation error: ' . $e->getMessage() );
		return array(
			'valid'   => false,
			'message' => 'Database error',
		);
	}
}

/**
 * Generate download token.
 *
 * WHY: Creates short-lived token for secure file downloads.
 *
 * @param string $license_key License key.
 * @param string $site_url    Site URL.
 * @param string $version     Plugin version.
 * @return string|false Download token or false on failure.
 */
function generate_download_token( string $license_key, string $site_url, string $version ) {
	try {
		$pdo = get_db_connection();

		// Generate random token
		$token = bin2hex( random_bytes( 32 ) );

		// Insert token (expires in 30 minutes)
		$stmt = $pdo->prepare( "
			INSERT INTO download_tokens (token, license_key, site_url, version, created_at, expires_at)
			VALUES (:token, :license_key, :site_url, :version, NOW(), DATE_ADD(NOW(), INTERVAL 30 MINUTE))
		" );

		$stmt->execute( array(
			':token'       => $token,
			':license_key' => $license_key,
			':site_url'    => normalize_site_url( $site_url ),
			':version'     => $version,
		) );

		return $token;
	} catch ( PDOException $e ) {
		error_log( 'Token generation error: ' . $e->getMessage() );
		return false;
	}
}

/**
 * Validate download token.
 *
 * WHY: Ensures download links are time-limited and single-use.
 *
 * @param string $token       Download token.
 * @param string $license_key License key.
 * @param string $site_url    Site URL.
 * @return bool Whether token is valid.
 */
function validate_download_token( string $token, string $license_key, string $site_url ): bool {
	try {
		$pdo = get_db_connection();

		// Get token
		$stmt = $pdo->prepare( "
			SELECT * FROM download_tokens
			WHERE token = :token
			AND license_key = :license_key
			AND site_url = :site_url
			AND expires_at > NOW()
			AND used_at IS NULL
			LIMIT 1
		" );

		$stmt->execute( array(
			':token'       => $token,
			':license_key' => $license_key,
			':site_url'    => normalize_site_url( $site_url ),
		) );

		$token_record = $stmt->fetch();

		if ( ! $token_record ) {
			return false;
		}

		// Mark token as used
		$update = $pdo->prepare( "
			UPDATE download_tokens
			SET used_at = NOW()
			WHERE id = :id
		" );
		$update->execute( array( ':id' => $token_record->id ) );

		return true;
	} catch ( PDOException $e ) {
		error_log( 'Token validation error: ' . $e->getMessage() );
		return false;
	}
}

/**
 * Normalize site URL for consistent comparison.
 *
 * WHY: Handles trailing slashes, protocols, etc.
 *
 * @param string $url Site URL.
 * @return string Normalized URL.
 */
function normalize_site_url( string $url ): string {
	// Remove protocol
	$url = preg_replace( '#^https?://#i', '', $url );

	// Remove trailing slash
	$url = rtrim( $url, '/' );

	// Remove www
	$url = preg_replace( '#^www\.#i', '', $url );

	return strtolower( $url );
}

/**
 * Clean up expired tokens (run via cron).
 *
 * WHY: Prevent database bloat from old tokens.
 */
function cleanup_expired_tokens(): void {
	try {
		$pdo = get_db_connection();

		$stmt = $pdo->prepare( "
			DELETE FROM download_tokens
			WHERE expires_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
		" );

		$stmt->execute();
	} catch ( PDOException $e ) {
		error_log( 'Token cleanup error: ' . $e->getMessage() );
	}
}
