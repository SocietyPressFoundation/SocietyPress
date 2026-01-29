<?php
/**
 * User Manager
 *
 * Handles WordPress user account creation and management for members.
 *
 * @package SocietyPress
 * @since 0.23d
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SocietyPress_User_Manager
 *
 * Creates and manages WordPress user accounts for members.
 */
class SocietyPress_User_Manager {

	/**
	 * Member role name.
	 */
	const MEMBER_ROLE = 'sp_member';

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Register the custom member role on plugin activation
		add_action( 'init', array( $this, 'register_member_role' ) );
	}

	/**
	 * Register custom member role.
	 *
	 * Creates a custom WordPress role for society members with
	 * appropriate capabilities for portal access.
	 *
	 * @since 0.23d
	 */
	public function register_member_role(): void {
		// Only add role if it doesn't exist
		if ( ! get_role( self::MEMBER_ROLE ) ) {
			add_role(
				self::MEMBER_ROLE,
				__( 'Society Member', 'societypress' ),
				array(
					'read'                    => true, // View published content
					'edit_posts'              => false,
					'delete_posts'            => false,
					'upload_files'            => false,
					'sp_access_member_portal' => true, // Custom capability for portal access
				)
			);
		}
	}

	/**
	 * Create or link WordPress user for a member.
	 *
	 * If a user with the email already exists, links the member to that user.
	 * Otherwise creates a new WordPress user account.
	 *
	 * @since 0.23d
	 *
	 * @param int    $member_id Member ID.
	 * @param string $email     Email address.
	 * @param string $first_name First name.
	 * @param string $last_name  Last name.
	 * @return int|WP_Error User ID on success, WP_Error on failure.
	 */
	public function create_or_link_user( int $member_id, string $email, string $first_name, string $last_name ) {
		// Check if user already exists by email
		$existing_user = get_user_by( 'email', $email );

		if ( $existing_user ) {
			// Link to existing user
			$this->link_member_to_user( $member_id, $existing_user->ID );
			return $existing_user->ID;
		}

		// Create new user
		return $this->create_user( $member_id, $email, $first_name, $last_name );
	}

	/**
	 * Create WordPress user account.
	 *
	 * Creates a new WordPress user with auto-generated password.
	 * Does NOT send welcome email during development.
	 *
	 * @since 0.23d
	 *
	 * @param int    $member_id  Member ID.
	 * @param string $email      Email address.
	 * @param string $first_name First name.
	 * @param string $last_name  Last name.
	 * @return int|WP_Error User ID on success, WP_Error on failure.
	 */
	private function create_user( int $member_id, string $email, string $first_name, string $last_name ) {
		// Generate username from email
		$username = $this->generate_username( $email );

		// Generate secure random password
		$password = wp_generate_password( 16, true, true );

		// Prepare user data
		$user_data = array(
			'user_login'      => $username,
			'user_email'      => $email,
			'user_pass'       => $password,
			'first_name'      => $first_name,
			'last_name'       => $last_name,
			'display_name'    => $first_name . ' ' . $last_name,
			'role'            => self::MEMBER_ROLE,
			'show_admin_bar_front' => false, // Hide admin bar for members
		);

		// Create the user
		$user_id = wp_insert_user( $user_data );

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		// Link member to user
		$this->link_member_to_user( $member_id, $user_id );

		// Store member ID in user meta for reverse lookup
		update_user_meta( $user_id, 'sp_member_id', $member_id );

		// TODO: Send welcome email when ready for production
		// For now, just generate the password reset link but don't send
		// $reset_key = get_password_reset_key( $user_id );

		return $user_id;
	}

	/**
	 * Generate unique username from email.
	 *
	 * Uses email address as username. If already taken, appends number.
	 *
	 * @since 0.23d
	 *
	 * @param string $email Email address.
	 * @return string Unique username.
	 */
	private function generate_username( string $email ): string {
		$username = sanitize_user( $email, true );

		// If username already exists, append number
		if ( username_exists( $username ) ) {
			$i = 1;
			$new_username = $username;
			while ( username_exists( $new_username ) ) {
				$new_username = $username . $i;
				$i++;
			}
			$username = $new_username;
		}

		return $username;
	}

	/**
	 * Link member record to WordPress user.
	 *
	 * Updates the member's user_id field in the database.
	 *
	 * @since 0.23d
	 *
	 * @param int $member_id Member ID.
	 * @param int $user_id   WordPress user ID. Pass 0 or null to unlink.
	 * @return bool True on success, false on failure.
	 */
	public function link_member_to_user( int $member_id, int $user_id ): bool {
		global $wpdb;

		$table = $wpdb->prefix . 'sp_members';

		// Use NULL for unlinking (user_id = 0 means unlink)
		$user_id_value  = $user_id > 0 ? $user_id : null;
		$user_id_format = $user_id > 0 ? '%d' : null;

		$result = $wpdb->update(
			$table,
			array( 'user_id' => $user_id_value ),
			array( 'id' => $member_id ),
			array( $user_id_format ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Get member ID from WordPress user ID.
	 *
	 * @since 0.23d
	 *
	 * @param int $user_id WordPress user ID.
	 * @return int|null Member ID or null if not found.
	 */
	public function get_member_id_by_user( int $user_id ): ?int {
		$member_id = get_user_meta( $user_id, 'sp_member_id', true );
		return $member_id ? (int) $member_id : null;
	}

	/**
	 * Check if member has linked WordPress user.
	 *
	 * @since 0.23d
	 *
	 * @param int $member_id Member ID.
	 * @return bool True if member has user account, false otherwise.
	 */
	public function member_has_user( int $member_id ): bool {
		global $wpdb;

		$table = $wpdb->prefix . 'sp_members';

		$user_id = $wpdb->get_var(
			$wpdb->prepare( "SELECT user_id FROM {$table} WHERE id = %d", $member_id )
		);

		return ! empty( $user_id );
	}

	/**
	 * Bulk create user accounts for members without accounts.
	 *
	 * @since 0.23d
	 *
	 * @param array $member_ids Array of member IDs.
	 * @return array Results with 'created' count and 'errors' array.
	 */
	public function bulk_create_users( array $member_ids ): array {
		$members = societypress()->members;
		$results = array(
			'created' => 0,
			'linked'  => 0,
			'skipped' => 0,
			'errors'  => array(),
		);

		foreach ( $member_ids as $member_id ) {
			// Skip if already has user
			if ( $this->member_has_user( $member_id ) ) {
				$results['skipped']++;
				continue;
			}

			// Get member data
			$member = $members->get( $member_id );
			$contact = $members->get_contact( $member_id );

			if ( ! $member || ! $contact ) {
				$results['errors'][] = sprintf(
					/* translators: %d: member ID */
					__( 'Member #%d: Could not load member data.', 'societypress' ),
					$member_id
				);
				continue;
			}

			// Create or link user
			$user_id = $this->create_or_link_user(
				$member_id,
				$contact->primary_email,
				$member->first_name,
				$member->last_name
			);

			if ( is_wp_error( $user_id ) ) {
				$results['errors'][] = sprintf(
					/* translators: %1$d: member ID, %2$s: error message */
					__( 'Member #%1$d: %2$s', 'societypress' ),
					$member_id,
					$user_id->get_error_message()
				);
			} else {
				// Check if we linked to existing or created new
				if ( get_user_by( 'email', $contact->primary_email ) ) {
					$results['linked']++;
				} else {
					$results['created']++;
				}
			}
		}

		return $results;
	}
}
