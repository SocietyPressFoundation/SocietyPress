<?php
/**
 * Leadership Management
 *
 * Handles positions (President, Vice President, etc.) and who holds them.
 * Tracks current and historical position holders with term dates.
 *
 * WHY: Societies need to display their board of directors and officers,
 *      and track the history of who served in what roles.
 *
 * @package SocietyPress
 * @since 0.55d
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SocietyPress_Leadership
 *
 * CRUD operations for positions and position holders.
 */
class SocietyPress_Leadership {

	/**
	 * Database object.
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Positions table name.
	 *
	 * @var string
	 */
	private string $positions_table;

	/**
	 * Position holders table name.
	 *
	 * @var string
	 */
	private string $holders_table;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb            = $wpdb;
		$this->positions_table = $wpdb->prefix . 'sp_positions';
		$this->holders_table   = $wpdb->prefix . 'sp_position_holders';
	}

	// =========================================================================
	// POSITIONS CRUD
	// =========================================================================

	/**
	 * Create a new position.
	 *
	 * @param array $data Position data.
	 * @return int|false Position ID on success, false on failure.
	 */
	public function create_position( array $data ) {
		$defaults = array(
			'slug'            => '',
			'title'           => '',
			'description'     => '',
			'is_board_member' => 0,
			'is_officer'      => 0,
			'sort_order'      => 0,
		);

		$data = wp_parse_args( $data, $defaults );

		// Generate slug if not provided
		if ( empty( $data['slug'] ) && ! empty( $data['title'] ) ) {
			$data['slug'] = $this->generate_unique_slug( $data['title'] );
		}

		$result = $this->wpdb->insert(
			$this->positions_table,
			array(
				'slug'            => sanitize_title( $data['slug'] ),
				'title'           => sanitize_text_field( $data['title'] ),
				'description'     => sanitize_textarea_field( $data['description'] ),
				'is_board_member' => absint( $data['is_board_member'] ),
				'is_officer'      => absint( $data['is_officer'] ),
				'sort_order'      => absint( $data['sort_order'] ),
			),
			array( '%s', '%s', '%s', '%d', '%d', '%d' )
		);

		if ( false === $result ) {
			return false;
		}

		$position_id = $this->wpdb->insert_id;

		/**
		 * Fires after a position is created.
		 *
		 * @param int   $position_id Position ID.
		 * @param array $data        Position data.
		 */
		do_action( 'societypress_position_created', $position_id, $data );

		return $position_id;
	}

	/**
	 * Update an existing position.
	 *
	 * @param int   $position_id Position ID.
	 * @param array $data        Data to update.
	 * @return bool True on success, false on failure.
	 */
	public function update_position( int $position_id, array $data ): bool {
		$update_data = array();
		$formats     = array();

		$allowed_fields = array(
			'slug'            => '%s',
			'title'           => '%s',
			'description'     => '%s',
			'is_board_member' => '%d',
			'is_officer'      => '%d',
			'sort_order'      => '%d',
		);

		foreach ( $allowed_fields as $field => $format ) {
			if ( isset( $data[ $field ] ) ) {
				if ( $field === 'slug' ) {
					$update_data[ $field ] = sanitize_title( $data[ $field ] );
				} elseif ( $field === 'title' ) {
					$update_data[ $field ] = sanitize_text_field( $data[ $field ] );
				} elseif ( $field === 'description' ) {
					$update_data[ $field ] = sanitize_textarea_field( $data[ $field ] );
				} else {
					$update_data[ $field ] = absint( $data[ $field ] );
				}
				$formats[] = $format;
			}
		}

		if ( empty( $update_data ) ) {
			return false;
		}

		$result = $this->wpdb->update(
			$this->positions_table,
			$update_data,
			array( 'id' => $position_id ),
			$formats,
			array( '%d' )
		);

		if ( false !== $result ) {
			/**
			 * Fires after a position is updated.
			 *
			 * @param int   $position_id Position ID.
			 * @param array $data        Updated data.
			 */
			do_action( 'societypress_position_updated', $position_id, $data );
		}

		return false !== $result;
	}

	/**
	 * Delete a position.
	 *
	 * WHY: Only allows deletion if no holders exist for this position.
	 *      Historical data is important to preserve.
	 *
	 * @param int $position_id Position ID.
	 * @return bool|WP_Error True on success, WP_Error if holders exist.
	 */
	public function delete_position( int $position_id ) {
		// Check for existing holders
		$holder_count = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->holders_table} WHERE position_id = %d",
				$position_id
			)
		);

		if ( $holder_count > 0 ) {
			return new WP_Error(
				'has_holders',
				__( 'Cannot delete position with existing holders. Remove all holders first.', 'societypress' )
			);
		}

		$result = $this->wpdb->delete(
			$this->positions_table,
			array( 'id' => $position_id ),
			array( '%d' )
		);

		if ( $result ) {
			/**
			 * Fires after a position is deleted.
			 *
			 * @param int $position_id Position ID.
			 */
			do_action( 'societypress_position_deleted', $position_id );
		}

		return (bool) $result;
	}

	/**
	 * Get a single position by ID.
	 *
	 * @param int $position_id Position ID.
	 * @return array|null Position data or null if not found.
	 */
	public function get_position( int $position_id ): ?array {
		$row = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->positions_table} WHERE id = %d",
				$position_id
			),
			ARRAY_A
		);

		return $row ?: null;
	}

	/**
	 * Get a position by slug.
	 *
	 * @param string $slug Position slug.
	 * @return array|null Position data or null if not found.
	 */
	public function get_position_by_slug( string $slug ): ?array {
		$row = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->positions_table} WHERE slug = %s",
				$slug
			),
			ARRAY_A
		);

		return $row ?: null;
	}

	/**
	 * Get all positions.
	 *
	 * @param array $filters Optional filters.
	 * @return array Array of positions.
	 */
	public function get_all_positions( array $filters = array() ): array {
		$where  = array( '1=1' );
		$values = array();

		if ( isset( $filters['is_board_member'] ) ) {
			$where[]  = 'is_board_member = %d';
			$values[] = absint( $filters['is_board_member'] );
		}

		if ( isset( $filters['is_officer'] ) ) {
			$where[]  = 'is_officer = %d';
			$values[] = absint( $filters['is_officer'] );
		}

		$where_clause = implode( ' AND ', $where );
		$order_by     = 'sort_order ASC, title ASC';

		$sql = "SELECT * FROM {$this->positions_table} WHERE {$where_clause} ORDER BY {$order_by}";

		if ( ! empty( $values ) ) {
			$sql = $this->wpdb->prepare( $sql, $values );
		}

		return $this->wpdb->get_results( $sql, ARRAY_A ) ?: array();
	}

	/**
	 * Get board member positions only.
	 *
	 * @return array Array of board member positions.
	 */
	public function get_board_positions(): array {
		return $this->get_all_positions( array( 'is_board_member' => 1 ) );
	}

	/**
	 * Get officer positions only.
	 *
	 * @return array Array of officer positions.
	 */
	public function get_officer_positions(): array {
		return $this->get_all_positions( array( 'is_officer' => 1 ) );
	}

	/**
	 * Generate a unique slug from a title.
	 *
	 * @param string $title Position title.
	 * @return string Unique slug.
	 */
	private function generate_unique_slug( string $title ): string {
		$base_slug = sanitize_title( $title );
		$slug      = $base_slug;
		$counter   = 1;

		while ( $this->get_position_by_slug( $slug ) ) {
			$slug = $base_slug . '-' . $counter;
			$counter++;
		}

		return $slug;
	}

	// =========================================================================
	// POSITION HOLDERS CRUD
	// =========================================================================

	/**
	 * Assign a member to a position.
	 *
	 * @param array $data Holder data.
	 * @return int|WP_Error Holder ID on success, WP_Error on failure.
	 */
	public function assign_holder( array $data ) {
		$defaults = array(
			'position_id' => 0,
			'member_id'   => 0,
			'term_start'  => current_time( 'Y-m-d' ),
			'term_end'    => null,
			'is_current'  => 1,
			'notes'       => '',
		);

		$data = wp_parse_args( $data, $defaults );

		// Validate required fields
		if ( empty( $data['position_id'] ) || empty( $data['member_id'] ) ) {
			return new WP_Error( 'missing_data', __( 'Position ID and Member ID are required.', 'societypress' ) );
		}

		// Check position exists
		if ( ! $this->get_position( $data['position_id'] ) ) {
			return new WP_Error( 'invalid_position', __( 'Position not found.', 'societypress' ) );
		}

		// Check member exists
		$member = societypress()->members->get( $data['member_id'] );
		if ( ! $member ) {
			return new WP_Error( 'invalid_member', __( 'Member not found.', 'societypress' ) );
		}

		// If marking as current, end any existing current holder's term
		if ( $data['is_current'] ) {
			$this->end_current_holder_term( $data['position_id'], $data['term_start'] );
		}

		$result = $this->wpdb->insert(
			$this->holders_table,
			array(
				'position_id' => absint( $data['position_id'] ),
				'member_id'   => absint( $data['member_id'] ),
				'term_start'  => sanitize_text_field( $data['term_start'] ),
				'term_end'    => ! empty( $data['term_end'] ) ? sanitize_text_field( $data['term_end'] ) : null,
				'is_current'  => absint( $data['is_current'] ),
				'notes'       => sanitize_textarea_field( $data['notes'] ),
			),
			array( '%d', '%d', '%s', '%s', '%d', '%s' )
		);

		if ( false === $result ) {
			return new WP_Error( 'db_error', __( 'Failed to assign position holder.', 'societypress' ) );
		}

		$holder_id = $this->wpdb->insert_id;

		/**
		 * Fires after a position holder is assigned.
		 *
		 * @param int   $holder_id Holder ID.
		 * @param array $data      Holder data.
		 */
		do_action( 'societypress_position_holder_assigned', $holder_id, $data );

		return $holder_id;
	}

	/**
	 * Update a position holder record.
	 *
	 * @param int   $holder_id Holder ID.
	 * @param array $data      Data to update.
	 * @return bool True on success, false on failure.
	 */
	public function update_holder( int $holder_id, array $data ): bool {
		$update_data = array();
		$formats     = array();

		$allowed_fields = array(
			'term_start' => '%s',
			'term_end'   => '%s',
			'is_current' => '%d',
			'notes'      => '%s',
		);

		foreach ( $allowed_fields as $field => $format ) {
			if ( array_key_exists( $field, $data ) ) {
				if ( $field === 'term_end' && empty( $data[ $field ] ) ) {
					$update_data[ $field ] = null;
				} elseif ( in_array( $field, array( 'term_start', 'term_end' ), true ) ) {
					$update_data[ $field ] = sanitize_text_field( $data[ $field ] );
				} elseif ( $field === 'notes' ) {
					$update_data[ $field ] = sanitize_textarea_field( $data[ $field ] );
				} else {
					$update_data[ $field ] = absint( $data[ $field ] );
				}
				$formats[] = $format;
			}
		}

		if ( empty( $update_data ) ) {
			return false;
		}

		$result = $this->wpdb->update(
			$this->holders_table,
			$update_data,
			array( 'id' => $holder_id ),
			$formats,
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Remove a position holder record.
	 *
	 * @param int $holder_id Holder ID.
	 * @return bool True on success, false on failure.
	 */
	public function remove_holder( int $holder_id ): bool {
		$result = $this->wpdb->delete(
			$this->holders_table,
			array( 'id' => $holder_id ),
			array( '%d' )
		);

		if ( $result ) {
			/**
			 * Fires after a position holder is removed.
			 *
			 * @param int $holder_id Holder ID.
			 */
			do_action( 'societypress_position_holder_removed', $holder_id );
		}

		return (bool) $result;
	}

	/**
	 * End the current holder's term for a position.
	 *
	 * WHY: When assigning a new current holder, the previous holder's
	 *      term should be ended automatically.
	 *
	 * @param int    $position_id Position ID.
	 * @param string $end_date    Term end date.
	 * @return bool True if updated, false otherwise.
	 */
	public function end_current_holder_term( int $position_id, string $end_date ): bool {
		// Calculate day before new term starts
		$end_date_obj = new DateTime( $end_date );
		$end_date_obj->modify( '-1 day' );
		$term_end = $end_date_obj->format( 'Y-m-d' );

		$result = $this->wpdb->update(
			$this->holders_table,
			array(
				'is_current' => 0,
				'term_end'   => $term_end,
			),
			array(
				'position_id' => $position_id,
				'is_current'  => 1,
			),
			array( '%d', '%s' ),
			array( '%d', '%d' )
		);

		return false !== $result;
	}

	/**
	 * Get a single holder record.
	 *
	 * @param int $holder_id Holder ID.
	 * @return array|null Holder data or null if not found.
	 */
	public function get_holder( int $holder_id ): ?array {
		$row = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT h.*, p.title as position_title, p.slug as position_slug,
				        m.first_name, m.last_name
				 FROM {$this->holders_table} h
				 JOIN {$this->positions_table} p ON h.position_id = p.id
				 JOIN {$this->wpdb->prefix}sp_members m ON h.member_id = m.id
				 WHERE h.id = %d",
				$holder_id
			),
			ARRAY_A
		);

		return $row ?: null;
	}

	/**
	 * Get the current holder for a position.
	 *
	 * @param int $position_id Position ID.
	 * @return array|null Holder data with member info, or null if vacant.
	 */
	public function get_current_holder( int $position_id ): ?array {
		$row = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT h.*, m.first_name, m.last_name, m.id as member_id
				 FROM {$this->holders_table} h
				 JOIN {$this->wpdb->prefix}sp_members m ON h.member_id = m.id
				 WHERE h.position_id = %d AND h.is_current = 1
				 LIMIT 1",
				$position_id
			),
			ARRAY_A
		);

		return $row ?: null;
	}

	/**
	 * Get all holders for a position (current and historical).
	 *
	 * @param int $position_id Position ID.
	 * @return array Array of holders.
	 */
	public function get_position_holders( int $position_id ): array {
		return $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT h.*, m.first_name, m.last_name
				 FROM {$this->holders_table} h
				 JOIN {$this->wpdb->prefix}sp_members m ON h.member_id = m.id
				 WHERE h.position_id = %d
				 ORDER BY h.is_current DESC, h.term_start DESC",
				$position_id
			),
			ARRAY_A
		) ?: array();
	}

	/**
	 * Get all positions a member holds or has held.
	 *
	 * @param int  $member_id    Member ID.
	 * @param bool $current_only Only return current positions.
	 * @return array Array of positions with holder data.
	 */
	public function get_member_positions( int $member_id, bool $current_only = false ): array {
		$where = $current_only ? 'AND h.is_current = 1' : '';

		return $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT h.*, p.title as position_title, p.slug as position_slug,
				        p.is_board_member, p.is_officer
				 FROM {$this->holders_table} h
				 JOIN {$this->positions_table} p ON h.position_id = p.id
				 WHERE h.member_id = %d {$where}
				 ORDER BY h.is_current DESC, h.term_start DESC",
				$member_id
			),
			ARRAY_A
		) ?: array();
	}

	/**
	 * Get all current leadership (positions with their current holders).
	 *
	 * WHY: Used for displaying the board/officers on the frontend.
	 *      Returns positions in sort order, with holder info if filled.
	 *
	 * @param array $filters Optional filters (is_board_member, is_officer).
	 * @return array Array of positions with current holder info.
	 */
	public function get_current_leadership( array $filters = array() ): array {
		$where  = array( '1=1' );
		$values = array();

		if ( isset( $filters['is_board_member'] ) ) {
			$where[]  = 'p.is_board_member = %d';
			$values[] = absint( $filters['is_board_member'] );
		}

		if ( isset( $filters['is_officer'] ) ) {
			$where[]  = 'p.is_officer = %d';
			$values[] = absint( $filters['is_officer'] );
		}

		$where_clause = implode( ' AND ', $where );

		$sql = "SELECT p.*,
		               h.id as holder_id, h.member_id, h.term_start, h.term_end, h.notes,
		               m.first_name, m.last_name
		        FROM {$this->positions_table} p
		        LEFT JOIN {$this->holders_table} h ON p.id = h.position_id AND h.is_current = 1
		        LEFT JOIN {$this->wpdb->prefix}sp_members m ON h.member_id = m.id
		        WHERE {$where_clause}
		        ORDER BY p.sort_order ASC, p.title ASC";

		if ( ! empty( $values ) ) {
			$sql = $this->wpdb->prepare( $sql, $values );
		}

		return $this->wpdb->get_results( $sql, ARRAY_A ) ?: array();
	}

	/**
	 * Get current board of directors.
	 *
	 * @return array Array of board positions with holders.
	 */
	public function get_board_of_directors(): array {
		return $this->get_current_leadership( array( 'is_board_member' => 1 ) );
	}

	/**
	 * Get current officers.
	 *
	 * @return array Array of officer positions with holders.
	 */
	public function get_officers(): array {
		return $this->get_current_leadership( array( 'is_officer' => 1 ) );
	}

	/**
	 * Check if a member is currently on the board.
	 *
	 * @param int $member_id Member ID.
	 * @return bool True if member is a current board member.
	 */
	public function is_board_member( int $member_id ): bool {
		$count = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*)
				 FROM {$this->holders_table} h
				 JOIN {$this->positions_table} p ON h.position_id = p.id
				 WHERE h.member_id = %d AND h.is_current = 1 AND p.is_board_member = 1",
				$member_id
			)
		);

		return $count > 0;
	}

	/**
	 * Check if a member is currently an officer.
	 *
	 * @param int $member_id Member ID.
	 * @return bool True if member is a current officer.
	 */
	public function is_officer( int $member_id ): bool {
		$count = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*)
				 FROM {$this->holders_table} h
				 JOIN {$this->positions_table} p ON h.position_id = p.id
				 WHERE h.member_id = %d AND h.is_current = 1 AND p.is_officer = 1",
				$member_id
			)
		);

		return $count > 0;
	}

	/**
	 * Get holder counts for all positions.
	 *
	 * WHY: Used in admin list to show how many holders each position has.
	 *
	 * @return array Associative array of position_id => count.
	 */
	public function get_holder_counts(): array {
		$results = $this->wpdb->get_results(
			"SELECT position_id, COUNT(*) as count
			 FROM {$this->holders_table}
			 GROUP BY position_id",
			ARRAY_A
		);

		$counts = array();
		foreach ( $results as $row ) {
			$counts[ $row['position_id'] ] = (int) $row['count'];
		}

		return $counts;
	}

	/**
	 * Format a holder's term for display.
	 *
	 * @param array $holder Holder data.
	 * @return string Formatted term string.
	 */
	public function format_term( array $holder ): string {
		$start = date_i18n( 'M Y', strtotime( $holder['term_start'] ) );

		if ( $holder['is_current'] ) {
			/* translators: %s: term start date */
			return sprintf( __( '%s - Present', 'societypress' ), $start );
		}

		if ( ! empty( $holder['term_end'] ) ) {
			$end = date_i18n( 'M Y', strtotime( $holder['term_end'] ) );
			/* translators: %1$s: term start date, %2$s: term end date */
			return sprintf( __( '%1$s - %2$s', 'societypress' ), $start, $end );
		}

		return $start;
	}
}
