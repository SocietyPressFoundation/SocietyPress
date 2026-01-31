<?php
/**
 * Event Slots Management
 *
 * Handles CRUD operations for event time slots, allowing events to have
 * multiple sessions (e.g., 10-11 AM, 11-12 PM, 12-1 PM) that members can
 * register for individually with capacity tracking.
 *
 * WHY: Many society events (especially classes and workshops) need to offer
 *      multiple time slots on the same day. This lets members choose which
 *      slot works best for their schedule while allowing capacity limits.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SocietyPress_Event_Slots
 *
 * Manages event time slots.
 */
class SocietyPress_Event_Slots {

	/**
	 * WordPress database object.
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Slots table name.
	 *
	 * @var string
	 */
	private string $table;

	/**
	 * Registrations table name (for capacity checks).
	 *
	 * @var string
	 */
	private string $registrations_table;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->table = SocietyPress::table( 'event_slots' );
		$this->registrations_table = SocietyPress::table( 'event_registrations' );
	}

	/**
	 * Create a new event time slot.
	 *
	 * WHY: Allows admins to add time slots to events from the event edit screen.
	 *
	 * @param int   $event_id The event post ID.
	 * @param array $data     Slot data: start_time, end_time, capacity, description.
	 * @return int|false The new slot ID on success, false on failure.
	 */
	public function create( int $event_id, array $data ) {
		// Validate required fields
		if ( empty( $data['start_time'] ) || empty( $data['end_time'] ) ) {
			return false;
		}

		// Get the next sort order for this event
		$max_order = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT MAX(sort_order) FROM {$this->table} WHERE event_id = %d",
				$event_id
			)
		);
		$sort_order = $max_order !== null ? (int) $max_order + 1 : 0;

		// Prepare data for insertion
		$insert_data = array(
			'event_id'    => $event_id,
			'start_time'  => $this->sanitize_time( $data['start_time'] ),
			'end_time'    => $this->sanitize_time( $data['end_time'] ),
			'capacity'    => isset( $data['capacity'] ) && $data['capacity'] !== '' ? absint( $data['capacity'] ) : null,
			'description' => isset( $data['description'] ) ? sanitize_text_field( $data['description'] ) : null,
			'sort_order'  => isset( $data['sort_order'] ) ? absint( $data['sort_order'] ) : $sort_order,
			'is_active'   => isset( $data['is_active'] ) ? absint( $data['is_active'] ) : 1,
		);

		$format = array( '%d', '%s', '%s', '%d', '%s', '%d', '%d' );

		// Handle NULL capacity (unlimited)
		if ( $insert_data['capacity'] === null ) {
			$format[3] = null;
		}

		$result = $this->wpdb->insert( $this->table, $insert_data, $format );

		if ( $result === false ) {
			return false;
		}

		return (int) $this->wpdb->insert_id;
	}

	/**
	 * Update an existing slot.
	 *
	 * WHY: Allows admins to modify slot times, capacity, or description.
	 *
	 * @param int   $slot_id The slot ID.
	 * @param array $data    Updated data.
	 * @return bool True on success, false on failure.
	 */
	public function update( int $slot_id, array $data ): bool {
		$update_data = array();
		$format = array();

		if ( isset( $data['start_time'] ) ) {
			$update_data['start_time'] = $this->sanitize_time( $data['start_time'] );
			$format[] = '%s';
		}

		if ( isset( $data['end_time'] ) ) {
			$update_data['end_time'] = $this->sanitize_time( $data['end_time'] );
			$format[] = '%s';
		}

		if ( array_key_exists( 'capacity', $data ) ) {
			// Allow setting capacity to NULL (unlimited) by passing empty string or null
			$update_data['capacity'] = ( $data['capacity'] === '' || $data['capacity'] === null ) ? null : absint( $data['capacity'] );
			$format[] = $update_data['capacity'] === null ? null : '%d';
		}

		if ( isset( $data['description'] ) ) {
			$update_data['description'] = sanitize_text_field( $data['description'] );
			$format[] = '%s';
		}

		if ( isset( $data['sort_order'] ) ) {
			$update_data['sort_order'] = absint( $data['sort_order'] );
			$format[] = '%d';
		}

		if ( isset( $data['is_active'] ) ) {
			$update_data['is_active'] = absint( $data['is_active'] );
			$format[] = '%d';
		}

		if ( empty( $update_data ) ) {
			return true; // Nothing to update
		}

		$result = $this->wpdb->update(
			$this->table,
			$update_data,
			array( 'id' => $slot_id ),
			$format,
			array( '%d' )
		);

		return $result !== false;
	}

	/**
	 * Delete a slot.
	 *
	 * WHY: Allows removing slots that are no longer needed.
	 * Note: Consider soft-delete (is_active = 0) to preserve registration history.
	 *
	 * @param int  $slot_id   The slot ID.
	 * @param bool $soft_delete Whether to soft delete (set is_active = 0) instead of hard delete.
	 * @return bool True on success, false on failure.
	 */
	public function delete( int $slot_id, bool $soft_delete = true ): bool {
		if ( $soft_delete ) {
			return $this->update( $slot_id, array( 'is_active' => 0 ) );
		}

		// Hard delete - also removes registrations
		$this->wpdb->delete(
			$this->registrations_table,
			array( 'slot_id' => $slot_id ),
			array( '%d' )
		);

		$result = $this->wpdb->delete(
			$this->table,
			array( 'id' => $slot_id ),
			array( '%d' )
		);

		return $result !== false;
	}

	/**
	 * Get a single slot by ID.
	 *
	 * @param int $slot_id The slot ID.
	 * @return array|null Slot data or null if not found.
	 */
	public function get( int $slot_id ): ?array {
		$row = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE id = %d",
				$slot_id
			),
			ARRAY_A
		);

		return $row ?: null;
	}

	/**
	 * Get all slots for an event.
	 *
	 * WHY: Used to display available time slots on the event page
	 *      and in the admin meta box.
	 *
	 * @param int  $event_id    The event post ID.
	 * @param bool $active_only Whether to only return active slots.
	 * @return array Array of slot data.
	 */
	public function get_by_event( int $event_id, bool $active_only = true ): array {
		$where_active = $active_only ? 'AND is_active = 1' : '';

		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table}
				 WHERE event_id = %d {$where_active}
				 ORDER BY sort_order ASC, start_time ASC",
				$event_id
			),
			ARRAY_A
		);

		return $results ?: array();
	}

	/**
	 * Get the number of confirmed registrations for a slot.
	 *
	 * WHY: Used for capacity checking before allowing new registrations.
	 *
	 * @param int $slot_id The slot ID.
	 * @return int Number of confirmed registrations.
	 */
	public function get_registration_count( int $slot_id ): int {
		$count = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->registrations_table}
				 WHERE slot_id = %d AND status = 'confirmed'",
				$slot_id
			)
		);

		return (int) $count;
	}

	/**
	 * Get the remaining capacity for a slot.
	 *
	 * WHY: Shows members how many spots are left and determines
	 *      whether they can register or must join the waitlist.
	 *
	 * @param int $slot_id The slot ID.
	 * @return int|null Remaining spots, or null if unlimited.
	 */
	public function get_remaining_capacity( int $slot_id ): ?int {
		$slot = $this->get( $slot_id );

		if ( ! $slot ) {
			return 0;
		}

		// NULL capacity means unlimited
		if ( $slot['capacity'] === null ) {
			return null;
		}

		$registered = $this->get_registration_count( $slot_id );
		$remaining = (int) $slot['capacity'] - $registered;

		return max( 0, $remaining );
	}

	/**
	 * Check if a slot has available capacity.
	 *
	 * WHY: Quick check before attempting registration.
	 *
	 * @param int $slot_id The slot ID.
	 * @return bool True if slot has capacity (or is unlimited).
	 */
	public function has_capacity( int $slot_id ): bool {
		$remaining = $this->get_remaining_capacity( $slot_id );

		// NULL means unlimited
		if ( $remaining === null ) {
			return true;
		}

		return $remaining > 0;
	}

	/**
	 * Format a slot's time range for display.
	 *
	 * WHY: Provides a human-readable time range like "10:00 AM - 11:00 AM"
	 *      for display on the frontend and in admin.
	 *
	 * @param int    $slot_id The slot ID.
	 * @param string $format  PHP time format. Default 'g:i A'.
	 * @return string Formatted time range, or empty if slot not found.
	 */
	public function format_time_range( int $slot_id, string $format = 'g:i A' ): string {
		$slot = $this->get( $slot_id );

		if ( ! $slot ) {
			return '';
		}

		return $this->format_time_range_from_data( $slot, $format );
	}

	/**
	 * Format a time range from slot data array.
	 *
	 * WHY: Allows formatting without an extra database query when you already
	 *      have the slot data (e.g., in a loop).
	 *
	 * @param array  $slot   Slot data with start_time and end_time.
	 * @param string $format PHP time format. Default 'g:i A'.
	 * @return string Formatted time range.
	 */
	public function format_time_range_from_data( array $slot, string $format = 'g:i A' ): string {
		if ( empty( $slot['start_time'] ) || empty( $slot['end_time'] ) ) {
			return '';
		}

		$start = date( $format, strtotime( $slot['start_time'] ) );
		$end = date( $format, strtotime( $slot['end_time'] ) );

		return $start . ' – ' . $end;
	}

	/**
	 * Sanitize a time string to HH:MM:SS format.
	 *
	 * WHY: Ensures consistent time format storage regardless of input format.
	 *
	 * @param string $time Time string in various formats.
	 * @return string Time in H:i:s format.
	 */
	private function sanitize_time( string $time ): string {
		// Try to parse the time and format it consistently
		$timestamp = strtotime( $time );

		if ( $timestamp === false ) {
			return '00:00:00';
		}

		return date( 'H:i:s', $timestamp );
	}

	/**
	 * Delete all slots for an event.
	 *
	 * WHY: Used when an event is deleted to clean up associated slots.
	 *
	 * @param int  $event_id    The event post ID.
	 * @param bool $soft_delete Whether to soft delete instead of hard delete.
	 * @return bool True on success.
	 */
	public function delete_by_event( int $event_id, bool $soft_delete = true ): bool {
		if ( $soft_delete ) {
			$result = $this->wpdb->update(
				$this->table,
				array( 'is_active' => 0 ),
				array( 'event_id' => $event_id ),
				array( '%d' ),
				array( '%d' )
			);
			return $result !== false;
		}

		// Get all slot IDs for the event
		$slot_ids = $this->wpdb->get_col(
			$this->wpdb->prepare(
				"SELECT id FROM {$this->table} WHERE event_id = %d",
				$event_id
			)
		);

		// Delete registrations for each slot
		if ( ! empty( $slot_ids ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $slot_ids ), '%d' ) );
			$this->wpdb->query(
				$this->wpdb->prepare(
					"DELETE FROM {$this->registrations_table} WHERE slot_id IN ({$placeholders})",
					$slot_ids
				)
			);
		}

		// Delete slots
		$result = $this->wpdb->delete(
			$this->table,
			array( 'event_id' => $event_id ),
			array( '%d' )
		);

		return $result !== false;
	}

	/**
	 * Save all slots for an event from form submission.
	 *
	 * WHY: Handles the repeatable slot rows from the admin meta box,
	 *      creating new slots, updating existing ones, and removing deleted ones.
	 *
	 * @param int   $event_id  The event post ID.
	 * @param array $slots_data Array of slot data from the form.
	 * @return bool True on success.
	 */
	public function save_event_slots( int $event_id, array $slots_data ): bool {
		// Get existing slot IDs for this event
		$existing_slots = $this->get_by_event( $event_id, false );
		$existing_ids = array_column( $existing_slots, 'id' );
		$submitted_ids = array();

		// Process each submitted slot
		foreach ( $slots_data as $index => $slot_data ) {
			// Skip empty rows
			if ( empty( $slot_data['start_time'] ) && empty( $slot_data['end_time'] ) ) {
				continue;
			}

			$slot_data['sort_order'] = $index;
			$slot_data['is_active'] = 1;

			if ( ! empty( $slot_data['id'] ) && in_array( (int) $slot_data['id'], $existing_ids, true ) ) {
				// Update existing slot
				$this->update( (int) $slot_data['id'], $slot_data );
				$submitted_ids[] = (int) $slot_data['id'];
			} else {
				// Create new slot
				$new_id = $this->create( $event_id, $slot_data );
				if ( $new_id ) {
					$submitted_ids[] = $new_id;
				}
			}
		}

		// Soft-delete slots that were removed (not in submitted IDs)
		$removed_ids = array_diff( $existing_ids, $submitted_ids );
		foreach ( $removed_ids as $removed_id ) {
			$this->delete( $removed_id, true );
		}

		return true;
	}
}
