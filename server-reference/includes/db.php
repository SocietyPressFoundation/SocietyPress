<?php
/**
 * Database Connection
 *
 * Provides PDO connection for license validation and update serving.
 *
 * WHY: Centralized database access for all update server endpoints.
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
/** Database hostname */
define( 'DB_HOST', 'localhost' );

define( 'DB_NAME', 'charle24_stricklindevelopment' );

/** Database username */
define( 'DB_USER', 'charle24_stricklindevelopment' );

/** Database password */
define( 'DB_PASSWORD', 'JF22Q9b7GnXDnszSFE56' );
define( 'DB_PASS', 'JF22Q9b7GnXDnszSFE56' );



/**
 * Get database connection.
 *
 * WHY: Returns PDO instance with proper error handling.
 *
 * @return PDO Database connection.
 * @throws PDOException If connection fails.
 */
function get_db_connection(): PDO {
	static $pdo = null;

	if ( null === $pdo ) {
		try {
			$dsn = sprintf( 'mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME );

			$pdo = new PDO(
				$dsn,
				DB_USER,
				DB_PASSWORD,
				array(
					PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
					PDO::ATTR_EMULATE_PREPARES   => false,
				)
			);
		} catch ( PDOException $e ) {
			error_log( 'Database connection failed: ' . $e->getMessage() );
			throw $e;
		}
	}

	return $pdo;
}

/**
 * Create database tables if they don't exist.
 *
 * WHY: Initial setup helper for deployment.
 */
function create_tables(): void {
	$pdo = get_db_connection();

	// Licenses table
	$pdo->exec("
		CREATE TABLE IF NOT EXISTS licenses (
			id INT PRIMARY KEY AUTO_INCREMENT,
			license_key VARCHAR(255) NOT NULL UNIQUE,
			email VARCHAR(255) NOT NULL,
			license_type ENUM('site', 'multi', 'lifetime') NOT NULL,
			status ENUM('active', 'expired', 'suspended') NOT NULL,
			sites_allowed INT DEFAULT 1,
			created_at DATETIME NOT NULL,
			expires_at DATETIME,
			INDEX(license_key),
			INDEX(status)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
	");

	// License activations table
	$pdo->exec("
		CREATE TABLE IF NOT EXISTS license_activations (
			id INT PRIMARY KEY AUTO_INCREMENT,
			license_id INT NOT NULL,
			site_url VARCHAR(255) NOT NULL,
			activated_at DATETIME NOT NULL,
			last_check DATETIME,
			FOREIGN KEY (license_id) REFERENCES licenses(id) ON DELETE CASCADE,
			UNIQUE KEY unique_activation (license_id, site_url),
			INDEX(site_url)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
	");

	// Download tokens table
	$pdo->exec("
		CREATE TABLE IF NOT EXISTS download_tokens (
			id INT PRIMARY KEY AUTO_INCREMENT,
			token VARCHAR(64) NOT NULL UNIQUE,
			license_key VARCHAR(255) NOT NULL,
			site_url VARCHAR(255) NOT NULL,
			version VARCHAR(20) NOT NULL,
			created_at DATETIME NOT NULL,
			expires_at DATETIME NOT NULL,
			used_at DATETIME,
			INDEX(token),
			INDEX(expires_at)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
	");
}
