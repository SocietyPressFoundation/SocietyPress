<?php
/**
 * Event Registrations Management
 *
 * Handles member registrations for event time slots, including confirmed
 * registrations, cancellations, and waitlist management with automatic
 * promotion when spots open up.
 *
 * WHY: Societies need to track who's signed up for which events and slots,
 *      manage capacity limits, and automatically handle waitlists when
 *      registrations are cancelled.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SocietyPress_Event_Registrations
 *
 * Manages event registrations.
 */
class SocietyPress_Event_Registrations {

	/**
	 * WordPress database object.
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Registrations table name.
	 *
	 * @var string
	 */
	private string $table;

	/**
	 * Slots table name.
	 *
	 * @var string
	 */
	private string $slots_table;

	/**
	 * Members table name.
	 *
	 * @var string
	 */
	private string $members_table;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->table = SocietyPress::table( 'event_registrations' );
		$this->slots_table = SocietyPress::table( 'event_slots' );
		$this->members_table = SocietyPress::table( 'members' );
	}

	/**
	 * Register a member for an event slot.
	 *
	 * WHY: The main entry point for event registration. Handles capacity
	 *      checking and automatically puts members on waitlist if slot is full.
	 *
	 * @param int      $slot_id       The slot ID.
	 * @param int      $member_id     The member ID.
	 * @param int|null $registered_by Admin user ID if manual registration, null if self-registration.
	 * @param string   $notes         Optional admin notes.
	 * @return array Result with 'success', 'status' (confirmed/waitlist), and 'message'.
	 */
	public function register( int $slot_id, int $member_id, ?int $registered_by = null, string $notes = '' ): array {
		// Check if already registered
		$existing = $this->get_registration( $slot_id, $member_id );
		if ( $existing ) {
			if ( $existing['status'] === 'cancelled' ) {
				// Re-register cancelled registration
				return $this->reactivate_registration( $existing['id'], $registered_by, $notes );
			}
			return array(
				'success' => false,
				'status'  => $existing['status'],
				'message' => __( 'You are already registered for this time slot.', 'societypress' ),
			);
		}

		// Check slot exists and is active
		$slots = societypress()->event_slots;
		$slot = $slots->get( $slot_id );
		if ( ! $slot || ! $slot['is_active'] ) {
			return array(
				'success' => false,
				'status'  => null,
				'message' => __( 'This time slot is not available.', 'societypress' ),
			);
		}

		// Check capacity
		$has_capacity = $slots->has_capacity( $slot_id );
		$status = $has_capacity ? 'confirmed' : 'waitlist';

		// Insert registration (event_id from slot for the unique constraint)
		$insert_data = array(
			'event_id'      => (int) $slot['event_id'],
			'slot_id'       => $slot_id,
			'member_id'     => $member_id,
			'status'        => $status,
			'registered_at' => current_time( 'mysql' ),
			'registered_by' => $registered_by,
			'notes'         => $notes ? sanitize_textarea_field( $notes ) : null,
		);

		$result = $this->wpdb->insert(
			$this->table,
			$insert_data,
			array( '%d', '%d', '%d', '%s', '%s', '%d', '%s' )
		);

		if ( $result === false ) {
			return array(
				'success' => false,
				'status'  => null,
				'message' => __( 'Registration failed. Please try again.', 'societypress' ),
			);
		}

		$message = $status === 'confirmed'
			? __( 'You have been registered for this time slot.', 'societypress' )
			: __( 'This slot is full. You have been added to the waitlist.', 'societypress' );

		return array(
			'success'         => true,
			'status'          => $status,
			'registration_id' => (int) $this->wpdb->insert_id,
			'message'         => $message,
		);
	}

	/**
	 * Reactivate a cancelled registration.
	 *
	 * WHY: When a member who previously cancelled wants to register again,
	 *      we update the existing record rather than creating a duplicate.
	 *
	 * @param int      $registration_id The registration ID.
	 * @param int|null $registered_by   Admin user ID if manual.
	 * @param string   $notes           Optional notes.
	 * @return array Result array.
	 */
	private function reactivate_registration( int $registration_id, ?int $registered_by, string $notes ): array {
		$registration = $this->get( $registration_id );
		if ( ! $registration ) {
			return array(
				'success' => false,
				'status'  => null,
				'message' => __( 'Registration not found.', 'societypress' ),
			);
		}

		// Check capacity for the slot
		$slots = societypress()->event_slots;
		$has_capacity = $slots->has_capacity( $registration['slot_id'] );
		$status = $has_capacity ? 'confirmed' : 'waitlist';

		$update_data = array(
			'status'        => $status,
			'registered_at' => current_time( 'mysql' ),
			'registered_by' => $registered_by,
			'cancelled_at'  => null,
		);

		if ( $notes ) {
			$update_data['notes'] = sanitize_textarea_field( $notes );
		}

		$this->wpdb->update(
			$this->table,
			$update_data,
			array( 'id' => $registration_id ),
			array( '%s', '%s', '%d', null, '%s' ),
			array( '%d' )
		);

		$message = $status === 'confirmed'
			? __( 'You have been registered for this time slot.', 'societypress' )
			: __( 'This slot is full. You have been added to the waitlist.', 'societypress' );

		return array(
			'success'         => true,
			'status'          => $status,
			'registration_id' => $registration_id,
			'message'         => $message,
		);
	}

	/**
	 * Cancel a registration.
	 *
	 * WHY: Allows members to cancel their registration, freeing up the spot
	 *      for others. Automatically promotes the next waitlisted person.
	 *
	 * @param int    $registration_id The registration ID.
	 * @param string $reason          Optional cancellation reason/notes.
	 * @return array Result array.
	 */
	public function cancel( int $registration_id, string $reason = '' ): array {
		$registration = $this->get( $registration_id );

		if ( ! $registration ) {
			return array(
				'success' => false,
				'message' => __( 'Registration not found.', 'societypress' ),
			);
		}

		if ( $registration['status'] === 'cancelled' ) {
			return array(
				'success' => false,
				'message' => __( 'This registration was already cancelled.', 'societypress' ),
			);
		}

		$was_confirmed = $registration['status'] === 'confirmed';
		$slot_id = $registration['slot_id'];

		// Update registration to cancelled
		$notes = $registration['notes'];
		if ( $reason ) {
			$notes = $notes ? $notes . "\n\nCancellation: " . $reason : "Cancellation: " . $reason;
		}

		$this->wpdb->update(
			$this->table,
			array(
				'status'       => 'cancelled',
				'cancelled_at' => current_time( 'mysql' ),
				'notes'        => $notes,
			),
			array( 'id' => $registration_id ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);

		// If this was a confirmed registration, promote from waitlist
		if ( $was_confirmed ) {
			$this->process_waitlist( $slot_id );
		}

		return array(
			'success' => true,
			'message' => __( 'Your registration has been cancelled.', 'societypress' ),
		);
	}

	/**
	 * Join the event-wide waitlist.
	 *
	 * WHY: When ALL slots for an event are full, members can join a single
	 *      event-wide waitlist. When ANY slot opens up, the first person
	 *      on the waitlist gets that slot automatically.
	 *
	 * @param int      $event_id      The event post ID.
	 * @param int      $member_id     The member ID.
	 * @param int|null $registered_by Admin user ID if manual, null if self.
	 * @return array Result with 'success', 'status', and 'message'.
	 */
	public function join_event_waitlist( int $event_id, int $member_id, ?int $registered_by = null ): array {
		// Check if already registered for a slot or on waitlist
		$existing = $this->get_member_event_registration( $event_id, $member_id );
		if ( $existing ) {
			return array(
				'success' => false,
				'status'  => $existing['status'],
				'message' => __( 'You are already registered or on the waitlist for this event.', 'societypress' ),
			);
		}

		// Check if there are any open slots - if so, they should register for one
		$slots = societypress()->event_slots->get_by_event( $event_id );
		foreach ( $slots as $slot ) {
			if ( societypress()->event_slots->has_capacity( $slot['id'] ) ) {
				return array(
					'success' => false,
					'status'  => null,
					'message' => __( 'There are still open slots available. Please register for one.', 'societypress' ),
				);
			}
		}

		// Insert waitlist entry with slot_id = NULL (event-wide waitlist)
		$result = $this->wpdb->insert(
			$this->table,
			array(
				'event_id'      => $event_id,
				'slot_id'       => null,
				'member_id'     => $member_id,
				'status'        => 'waitlist',
				'registered_at' => current_time( 'mysql' ),
				'registered_by' => $registered_by,
			),
			array( '%d', null, '%d', '%s', '%s', '%d' )
		);

		if ( $result === false ) {
			return array(
				'success' => false,
				'status'  => null,
				'message' => __( 'Failed to join waitlist. Please try again.', 'societypress' ),
			);
		}

		return array(
			'success'         => true,
			'status'          => 'waitlist',
			'registration_id' => (int) $this->wpdb->insert_id,
			'message'         => __( 'You have been added to the waitlist. We\'ll notify you when a spot opens up.', 'societypress' ),
		);
	}

	/**
	 * Process the waitlist when a slot opens.
	 *
	 * WHY: When a spot opens up (cancellation), automatically promote the
	 *      first person on the EVENT-WIDE waitlist to that slot. This keeps
	 *      things fair (first-come, first-served) and reduces admin work.
	 *
	 * @param int $slot_id The slot ID that opened up.
	 * @return int|false The promoted registration ID, or false if no waitlist.
	 */
	public function process_waitlist( int $slot_id ) {
		// Check if there's capacity
		$slots = societypress()->event_slots;
		if ( ! $slots->has_capacity( $slot_id ) ) {
			return false;
		}

		// Get the event_id for this slot
		$slot = $slots->get( $slot_id );
		if ( ! $slot ) {
			return false;
		}
		$event_id = (int) $slot['event_id'];

		// Get the first waitlisted registration for this EVENT (slot_id IS NULL = event-wide waitlist)
		$waitlisted = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table}
				 WHERE event_id = %d AND slot_id IS NULL AND status = 'waitlist'
				 ORDER BY registered_at ASC
				 LIMIT 1",
				$event_id
			),
			ARRAY_A
		);

		if ( ! $waitlisted ) {
			return false;
		}

		// Promote to confirmed and assign the slot
		$this->wpdb->update(
			$this->table,
			array(
				'status'  => 'confirmed',
				'slot_id' => $slot_id,
			),
			array( 'id' => $waitlisted['id'] ),
			array( '%s', '%d' ),
			array( '%d' )
		);

		// Fire hook for email notification
		do_action( 'societypress_waitlist_promoted', $waitlisted['id'], $waitlisted['member_id'], $slot_id );

		return (int) $waitlisted['id'];
	}

	/**
	 * Get a single registration by ID.
	 *
	 * @param int $registration_id The registration ID.
	 * @return array|null Registration data or null.
	 */
	public function get( int $registration_id ): ?array {
		$row = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE id = %d",
				$registration_id
			),
			ARRAY_A
		);

		return $row ?: null;
	}

	/**
	 * Get a member's registration for a specific slot.
	 *
	 * WHY: Check if a member is already registered for a slot.
	 *
	 * @param int $slot_id   The slot ID.
	 * @param int $member_id The member ID.
	 * @return array|null Registration data or null.
	 */
	public function get_registration( int $slot_id, int $member_id ): ?array {
		$row = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table}
				 WHERE slot_id = %d AND member_id = %d",
				$slot_id,
				$member_id
			),
			ARRAY_A
		);

		return $row ?: null;
	}

	/**
	 * Check if a member is registered for a slot (any status except cancelled).
	 *
	 * WHY: Quick check for frontend display logic.
	 *
	 * @param int $slot_id   The slot ID.
	 * @param int $member_id The member ID.
	 * @return string|false The status ('confirmed' or 'waitlist'), or false if not registered.
	 */
	public function is_member_registered( int $slot_id, int $member_id ) {
		$registration = $this->get_registration( $slot_id, $member_id );

		if ( ! $registration || $registration['status'] === 'cancelled' ) {
			return false;
		}

		return $registration['status'];
	}

	/**
	 * Get all registrations for a slot.
	 *
	 * WHY: Admin view to see who's registered for a specific time slot.
	 *
	 * @param int    $slot_id The slot ID.
	 * @param string $status  Optional. Filter by status (confirmed, cancelled, waitlist).
	 * @return array Array of registrations with member data.
	 */
	public function get_by_slot( int $slot_id, string $status = '' ): array {
		$where_status = $status ? $this->wpdb->prepare( 'AND r.status = %s', $status ) : '';

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT r.*, m.first_name, m.last_name, mc.primary_email
				 FROM {$this->table} r
				 LEFT JOIN {$this->members_table} m ON r.member_id = m.id
				 LEFT JOIN " . SocietyPress::table( 'member_contact' ) . " mc ON m.id = mc.member_id
				 WHERE r.slot_id = %d {$where_status}
				 ORDER BY r.status ASC, r.registered_at ASC",
				$slot_id
			),
			ARRAY_A
		);

		return $results ?: array();
	}

	/**
	 * Get a member's upcoming event registrations.
	 *
	 * WHY: Shows members their upcoming events in the portal dashboard.
	 *      Includes event details, slot time, and registration status.
	 *
	 * @param int $member_id The member ID.
	 * @return array Array of registrations with event and slot data.
	 */
	public function get_member_upcoming( int $member_id ): array {
		$today = date( 'Y-m-d' );

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT r.*, s.event_id, s.start_time, s.end_time, s.description as slot_description,
				        p.post_title as event_title,
				        pm.meta_value as event_date
				 FROM {$this->table} r
				 INNER JOIN {$this->slots_table} s ON r.slot_id = s.id
				 INNER JOIN {$this->wpdb->posts} p ON s.event_id = p.ID
				 LEFT JOIN {$this->wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'sp_event_date'
				 WHERE r.member_id = %d
				   AND r.status IN ('confirmed', 'waitlist')
				   AND s.is_active = 1
				   AND pm.meta_value >= %s
				 ORDER BY pm.meta_value ASC, s.start_time ASC",
				$member_id,
				$today
			),
			ARRAY_A
		);

		return $results ?: array();
	}

	/**
	 * Get a member's past event registrations.
	 *
	 * WHY: Shows members their event history in the portal dashboard.
	 *
	 * @param int $member_id The member ID.
	 * @param int $limit     Maximum number of records to return.
	 * @return array Array of past registrations.
	 */
	public function get_member_past( int $member_id, int $limit = 20 ): array {
		$today = date( 'Y-m-d' );

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT r.*, s.event_id, s.start_time, s.end_time, s.description as slot_description,
				        p.post_title as event_title,
				        pm.meta_value as event_date
				 FROM {$this->table} r
				 INNER JOIN {$this->slots_table} s ON r.slot_id = s.id
				 INNER JOIN {$this->wpdb->posts} p ON s.event_id = p.ID
				 LEFT JOIN {$this->wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'sp_event_date'
				 WHERE r.member_id = %d
				   AND r.status = 'confirmed'
				   AND pm.meta_value < %s
				 ORDER BY pm.meta_value DESC, s.start_time DESC
				 LIMIT %d",
				$member_id,
				$today,
				$limit
			),
			ARRAY_A
		);

		return $results ?: array();
	}

	/**
	 * Get a member's registration for an event (any slot or waitlist).
	 *
	 * WHY: Quick check if member is registered for any slot of an event,
	 *      OR if they're on the event-wide waitlist (slot_id = NULL).
	 *
	 * @param int $event_id  The event post ID.
	 * @param int $member_id The member ID.
	 * @return array|null Registration data or null.
	 */
	public function get_member_event_registration( int $event_id, int $member_id ): ?array {
		// First check for slot-based registration
		$row = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT r.*, s.start_time, s.end_time, s.description as slot_description
				 FROM {$this->table} r
				 INNER JOIN {$this->slots_table} s ON r.slot_id = s.id
				 WHERE s.event_id = %d
				   AND r.member_id = %d
				   AND r.status IN ('confirmed', 'waitlist')
				   AND s.is_active = 1
				 LIMIT 1",
				$event_id,
				$member_id
			),
			ARRAY_A
		);

		if ( $row ) {
			return $row;
		}

		// Check for event-wide waitlist entry (slot_id IS NULL)
		$waitlist_row = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table}
				 WHERE event_id = %d
				   AND member_id = %d
				   AND slot_id IS NULL
				   AND status = 'waitlist'
				 LIMIT 1",
				$event_id,
				$member_id
			),
			ARRAY_A
		);

		return $waitlist_row ?: null;
	}

	/**
	 * Get waitlist position for a registration.
	 *
	 * WHY: Shows members where they are in the event-wide waitlist queue.
	 *
	 * @param int $registration_id The registration ID.
	 * @return int|false Position (1-based) or false if not waitlisted.
	 */
	public function get_waitlist_position( int $registration_id ) {
		$registration = $this->get( $registration_id );

		if ( ! $registration || $registration['status'] !== 'waitlist' ) {
			return false;
		}

		// Count how many are ahead in the EVENT-WIDE waitlist
		$position = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) + 1
				 FROM {$this->table}
				 WHERE event_id = %d
				   AND slot_id IS NULL
				   AND status = 'waitlist'
				   AND registered_at < %s",
				$registration['event_id'],
				$registration['registered_at']
			)
		);

		return (int) $position;
	}

	/**
	 * Get the count of waitlisted members for an event.
	 *
	 * WHY: Shows how many people are waiting for any slot to open.
	 *
	 * @param int $event_id The event post ID.
	 * @return int Waitlist count.
	 */
	public function get_event_waitlist_count( int $event_id ): int {
		$count = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->table}
				 WHERE event_id = %d AND slot_id IS NULL AND status = 'waitlist'",
				$event_id
			)
		);

		return (int) $count;
	}

	/**
	 * Get the count of waitlisted members for a slot.
	 *
	 * @deprecated Use get_event_waitlist_count() instead for event-wide waitlist.
	 * @param int $slot_id The slot ID.
	 * @return int Waitlist count.
	 */
	public function get_waitlist_count( int $slot_id ): int {
		$count = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->table}
				 WHERE slot_id = %d AND status = 'waitlist'",
				$slot_id
			)
		);

		return (int) $count;
	}

	/**
	 * Admin override: Force register a member (bypass capacity).
	 *
	 * WHY: Admins may need to add someone to a full event for special circumstances.
	 *
	 * @param int    $slot_id       The slot ID.
	 * @param int    $member_id     The member ID.
	 * @param int    $registered_by Admin user ID.
	 * @param string $notes         Admin notes explaining override.
	 * @return array Result array.
	 */
	public function admin_register( int $slot_id, int $member_id, int $registered_by, string $notes = '' ): array {
		// Check if already registered
		$existing = $this->get_registration( $slot_id, $member_id );
		if ( $existing && $existing['status'] !== 'cancelled' ) {
			return array(
				'success' => false,
				'message' => __( 'Member is already registered for this slot.', 'societypress' ),
			);
		}

		$admin_notes = $notes ?: __( 'Admin override registration', 'societypress' );

		if ( $existing && $existing['status'] === 'cancelled' ) {
			// Reactivate
			$this->wpdb->update(
				$this->table,
				array(
					'status'        => 'confirmed',
					'registered_at' => current_time( 'mysql' ),
					'registered_by' => $registered_by,
					'cancelled_at'  => null,
					'notes'         => $admin_notes,
				),
				array( 'id' => $existing['id'] ),
				array( '%s', '%s', '%d', null, '%s' ),
				array( '%d' )
			);
			$registration_id = $existing['id'];
		} else {
			// New registration
			$this->wpdb->insert(
				$this->table,
				array(
					'slot_id'       => $slot_id,
					'member_id'     => $member_id,
					'status'        => 'confirmed',
					'registered_at' => current_time( 'mysql' ),
					'registered_by' => $registered_by,
					'notes'         => $admin_notes,
				),
				array( '%d', '%d', '%s', '%s', '%d', '%s' )
			);
			$registration_id = (int) $this->wpdb->insert_id;
		}

		return array(
			'success'         => true,
			'registration_id' => $registration_id,
			'message'         => __( 'Member has been registered.', 'societypress' ),
		);
	}

	/**
	 * Delete all registrations for a member.
	 *
	 * WHY: Used when a member is deleted from the system.
	 *
	 * @param int $member_id The member ID.
	 * @return bool True on success.
	 */
	public function delete_by_member( int $member_id ): bool {
		$result = $this->wpdb->delete(
			$this->table,
			array( 'member_id' => $member_id ),
			array( '%d' )
		);

		return $result !== false;
	}
}
