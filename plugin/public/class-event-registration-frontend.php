<?php
/**
 * Event Registration Frontend
 *
 * Handles the frontend display and AJAX functionality for event registration.
 * Shows available time slots to members and allows them to register/cancel.
 *
 * WHY: Members need a simple, accessible way to sign up for events from the
 *      single event page. This integrates with the event display template
 *      via WordPress action hooks.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SocietyPress_Event_Registration_Frontend
 *
 * Frontend event registration functionality.
 */
class SocietyPress_Event_Registration_Frontend {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks(): void {
		// Hook into single event display
		add_action( 'sp_event_after_content', array( $this, 'render_registration_section' ), 20 );

		// Enqueue frontend assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// AJAX handlers (logged-in users only)
		add_action( 'wp_ajax_societypress_register_event', array( $this, 'ajax_register' ) );
		add_action( 'wp_ajax_societypress_cancel_registration', array( $this, 'ajax_cancel' ) );
		add_action( 'wp_ajax_societypress_join_waitlist', array( $this, 'ajax_join_waitlist' ) );

		// Also allow non-logged-in users to see the "please log in" message via AJAX
		add_action( 'wp_ajax_nopriv_societypress_register_event', array( $this, 'ajax_not_logged_in' ) );
		add_action( 'wp_ajax_nopriv_societypress_cancel_registration', array( $this, 'ajax_not_logged_in' ) );
		add_action( 'wp_ajax_nopriv_societypress_join_waitlist', array( $this, 'ajax_not_logged_in' ) );
	}

	/**
	 * Enqueue frontend assets on single event pages.
	 */
	public function enqueue_assets(): void {
		if ( ! is_singular( 'sp_event' ) ) {
			return;
		}

		// Only load if the event has slots
		$event_id = get_the_ID();
		if ( ! $this->event_has_slots( $event_id ) ) {
			return;
		}

		wp_enqueue_style(
			'societypress-event-registration',
			SOCIETYPRESS_URL . 'assets/css/event-registration.css',
			array(),
			SOCIETYPRESS_VERSION
		);

		wp_enqueue_script(
			'societypress-event-registration',
			SOCIETYPRESS_URL . 'assets/js/event-registration.js',
			array( 'jquery' ),
			SOCIETYPRESS_VERSION,
			true
		);

		wp_localize_script(
			'societypress-event-registration',
			'spEventReg',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'sp_event_registration' ),
				'strings' => array(
					'registering'    => __( 'Registering...', 'societypress' ),
					'cancelling'     => __( 'Cancelling...', 'societypress' ),
					'error'          => __( 'An error occurred. Please try again.', 'societypress' ),
					'confirmCancel'  => __( 'Are you sure you want to cancel your registration?', 'societypress' ),
				),
			)
		);
	}

	/**
	 * Check if an event has any active time slots.
	 *
	 * @param int $event_id The event post ID.
	 * @return bool True if event has slots.
	 */
	private function event_has_slots( int $event_id ): bool {
		if ( ! isset( societypress()->event_slots ) ) {
			return false;
		}

		$slots = societypress()->event_slots->get_by_event( $event_id );
		return ! empty( $slots );
	}

	/**
	 * Render the registration section on single event pages.
	 *
	 * WHY: This is the main entry point for the registration UI. It displays
	 *      different content based on the user's state:
	 *      - Not logged in: "Log in to register" link
	 *      - Not a member: "Membership required" message
	 *      - Member: Slots table with register/cancel buttons
	 *
	 * @param int|null $event_id The event post ID. Defaults to current post.
	 */
	public function render_registration_section( ?int $event_id = null ): void {
		if ( $event_id === null ) {
			$event_id = get_the_ID();
		}

		// Check if event has slots
		if ( ! $this->event_has_slots( $event_id ) ) {
			return;
		}

		echo '<div class="sp-event-registration">';
		echo '<h3>' . esc_html__( 'Register for This Event', 'societypress' ) . '</h3>';

		// Check user state and render appropriate content
		if ( ! is_user_logged_in() ) {
			$this->render_login_prompt( $event_id );
		} else {
			$member_id = $this->get_current_member_id();

			if ( ! $member_id ) {
				$this->render_membership_required();
			} else {
				$this->render_slots_table( $event_id, $member_id );
			}
		}

		echo '</div>';
	}

	/**
	 * Render login prompt for non-logged-in users.
	 *
	 * @param int $event_id The event post ID.
	 */
	private function render_login_prompt( int $event_id ): void {
		$login_url = wp_login_url( get_permalink( $event_id ) );
		?>
		<div class="sp-registration-message sp-login-required">
			<p>
				<?php
				printf(
					/* translators: %s: login link */
					esc_html__( 'Please %s to register for this event.', 'societypress' ),
					'<a href="' . esc_url( $login_url ) . '">' . esc_html__( 'log in', 'societypress' ) . '</a>'
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render membership required message.
	 */
	private function render_membership_required(): void {
		?>
		<div class="sp-registration-message sp-membership-required">
			<p>
				<?php esc_html_e( 'Membership is required to register for events. Please contact us for information about joining.', 'societypress' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render the time slots table with registration buttons.
	 *
	 * WHY: Shows all available slots with their times, capacity, and the
	 *      appropriate action button based on the member's registration status.
	 *      If ALL slots are full and member isn't registered, shows a single
	 *      "Join Waitlist" button at the bottom.
	 *
	 * @param int $event_id  The event post ID.
	 * @param int $member_id The member ID.
	 */
	private function render_slots_table( int $event_id, int $member_id ): void {
		$slots = societypress()->event_slots->get_by_event( $event_id );

		if ( empty( $slots ) ) {
			return;
		}

		// Check if member is already registered for any slot OR on event waitlist
		$existing_registration = societypress()->event_registrations->get_member_event_registration( $event_id, $member_id );

		// Check if member is on the event-wide waitlist (slot_id IS NULL)
		$is_on_event_waitlist = $existing_registration && empty( $existing_registration['slot_id'] ) && $existing_registration['status'] === 'waitlist';

		// If on event-wide waitlist, show that status
		if ( $is_on_event_waitlist ) {
			$position = societypress()->event_registrations->get_waitlist_position( $existing_registration['id'] );
			$waitlist_count = societypress()->event_registrations->get_event_waitlist_count( $event_id );
			?>
			<div class="sp-waitlist-status">
				<span class="sp-waitlist-badge">
					<?php
					printf(
						/* translators: %d: position in waitlist */
						esc_html__( 'You are #%d on the waitlist', 'societypress' ),
						$position
					);
					?>
				</span>
				<p class="sp-waitlist-info">
					<?php esc_html_e( 'When a spot opens up, you\'ll be automatically registered and notified.', 'societypress' ); ?>
				</p>
				<button type="button" class="sp-cancel-btn sp-cancel-waitlist" data-registration-id="<?php echo esc_attr( $existing_registration['id'] ); ?>">
					<?php esc_html_e( 'Leave Waitlist', 'societypress' ); ?>
				</button>
			</div>
			<?php
			return;
		}

		// Check if ALL slots are full
		$all_slots_full = true;
		foreach ( $slots as $slot ) {
			if ( societypress()->event_slots->has_capacity( (int) $slot['id'] ) ) {
				$all_slots_full = false;
				break;
			}
		}
		?>
		<table class="sp-slots-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Time', 'societypress' ); ?></th>
					<th><?php esc_html_e( 'Description', 'societypress' ); ?></th>
					<th><?php esc_html_e( 'Availability', 'societypress' ); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $slots as $slot ) : ?>
					<?php $this->render_slot_row( $slot, $member_id, $existing_registration ); ?>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php

		// Show "Join Waitlist" button if ALL slots are full and member isn't registered
		if ( $all_slots_full && ! $existing_registration ) {
			$waitlist_count = societypress()->event_registrations->get_event_waitlist_count( $event_id );
			?>
			<div class="sp-waitlist-section">
				<p class="sp-all-full-message">
					<?php esc_html_e( 'All time slots are currently full.', 'societypress' ); ?>
					<?php if ( $waitlist_count > 0 ) : ?>
						<span class="sp-waitlist-count">
							<?php
							printf(
								/* translators: %d: number of people on waitlist */
								esc_html( _n( '(%d person on waitlist)', '(%d people on waitlist)', $waitlist_count, 'societypress' ) ),
								$waitlist_count
							);
							?>
						</span>
					<?php endif; ?>
				</p>
				<button type="button" class="sp-register-btn sp-join-waitlist-btn" data-event-id="<?php echo esc_attr( $event_id ); ?>">
					<?php esc_html_e( 'Join Waitlist', 'societypress' ); ?>
				</button>
			</div>
			<?php
		}
	}

	/**
	 * Render a single slot row in the registration table.
	 *
	 * @param array      $slot                  Slot data.
	 * @param int        $member_id             The member ID.
	 * @param array|null $existing_registration Existing registration if any.
	 */
	private function render_slot_row( array $slot, int $member_id, ?array $existing_registration ): void {
		$slot_id = (int) $slot['id'];
		$slots_manager = societypress()->event_slots;
		$registrations_manager = societypress()->event_registrations;

		// Get slot status for this member
		$member_status = $registrations_manager->is_member_registered( $slot_id, $member_id );

		// Get capacity info
		$remaining = $slots_manager->get_remaining_capacity( $slot_id );
		$is_unlimited = $remaining === null;
		$is_full = ! $is_unlimited && $remaining <= 0;
		$waitlist_count = $registrations_manager->get_waitlist_count( $slot_id );

		// Format time range
		$time_range = $slots_manager->format_time_range_from_data( $slot );

		// Determine row class
		$row_class = '';
		if ( $member_status === 'confirmed' ) {
			$row_class = 'sp-slot-registered';
		} elseif ( $member_status === 'waitlist' ) {
			$row_class = 'sp-slot-waitlisted';
		} elseif ( $is_full ) {
			$row_class = 'sp-slot-full';
		}
		?>
		<tr class="sp-slot-row <?php echo esc_attr( $row_class ); ?>" data-slot-id="<?php echo esc_attr( $slot_id ); ?>">
			<td class="sp-slot-time">
				<?php echo esc_html( $time_range ); ?>
			</td>
			<td class="sp-slot-description">
				<?php echo esc_html( $slot['description'] ?: '—' ); ?>
			</td>
			<td class="sp-slot-availability">
				<?php $this->render_availability( $remaining, $is_unlimited, $is_full, $waitlist_count ); ?>
			</td>
			<td class="sp-slot-action">
				<?php $this->render_action_button( $slot_id, $member_id, $member_status, $is_full, $existing_registration ); ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render availability status for a slot.
	 *
	 * @param int|null $remaining      Remaining spots (null if unlimited).
	 * @param bool     $is_unlimited   Whether capacity is unlimited.
	 * @param bool     $is_full        Whether slot is full.
	 * @param int      $waitlist_count Number on waitlist.
	 */
	private function render_availability( ?int $remaining, bool $is_unlimited, bool $is_full, int $waitlist_count ): void {
		if ( $is_unlimited ) {
			echo '<span class="sp-availability-open">' . esc_html__( 'Open', 'societypress' ) . '</span>';
		} elseif ( $is_full ) {
			echo '<span class="sp-availability-full">' . esc_html__( 'Full', 'societypress' ) . '</span>';
			if ( $waitlist_count > 0 ) {
				echo ' <span class="sp-waitlist-count">(' . sprintf(
					/* translators: %d: number of people on waitlist */
					esc_html( _n( '%d on waitlist', '%d on waitlist', $waitlist_count, 'societypress' ) ),
					$waitlist_count
				) . ')</span>';
			}
		} else {
			echo '<span class="sp-availability-spots">' . sprintf(
				/* translators: %d: number of spots remaining */
				esc_html( _n( '%d spot left', '%d spots left', $remaining, 'societypress' ) ),
				$remaining
			) . '</span>';
		}
	}

	/**
	 * Render the action button for a slot.
	 *
	 * @param int        $slot_id               The slot ID.
	 * @param int        $member_id             The member ID.
	 * @param string|false $member_status       Member's registration status.
	 * @param bool       $is_full               Whether slot is full.
	 * @param array|null $existing_registration Existing registration for event.
	 */
	private function render_action_button( int $slot_id, int $member_id, $member_status, bool $is_full, ?array $existing_registration ): void {
		// Member is registered for THIS slot
		if ( $member_status === 'confirmed' ) {
			$registration = societypress()->event_registrations->get_registration( $slot_id, $member_id );
			?>
			<span class="sp-registered-badge"><?php esc_html_e( 'Registered', 'societypress' ); ?></span>
			<button type="button" class="sp-cancel-btn" data-registration-id="<?php echo esc_attr( $registration['id'] ); ?>">
				<?php esc_html_e( 'Cancel', 'societypress' ); ?>
			</button>
			<?php
			return;
		}

		// Member is on waitlist for THIS slot
		if ( $member_status === 'waitlist' ) {
			$registration = societypress()->event_registrations->get_registration( $slot_id, $member_id );
			$position = societypress()->event_registrations->get_waitlist_position( $registration['id'] );
			?>
			<span class="sp-waitlist-badge">
				<?php
				printf(
					/* translators: %d: position in waitlist */
					esc_html__( 'Waitlist #%d', 'societypress' ),
					$position
				);
				?>
			</span>
			<button type="button" class="sp-cancel-btn sp-cancel-waitlist" data-registration-id="<?php echo esc_attr( $registration['id'] ); ?>">
				<?php esc_html_e( 'Leave Waitlist', 'societypress' ); ?>
			</button>
			<?php
			return;
		}

		// Member is registered for a DIFFERENT slot of this event
		if ( $existing_registration && (int) $existing_registration['slot_id'] !== $slot_id ) {
			?>
			<span class="sp-other-slot-note">
				<?php esc_html_e( 'One registration per event', 'societypress' ); ?>
			</span>
			<?php
			return;
		}

		// Slot is full - show "Pick another time" if other slots available,
		// otherwise show nothing (the "Join Waitlist" button is shown below the table)
		if ( $is_full ) {
			// Check if any other slots have capacity
			$slot = societypress()->event_slots->get( $slot_id );
			$all_slots = societypress()->event_slots->get_by_event( (int) $slot['event_id'] );
			$any_available = false;

			foreach ( $all_slots as $other_slot ) {
				if ( (int) $other_slot['id'] !== $slot_id && societypress()->event_slots->has_capacity( (int) $other_slot['id'] ) ) {
					$any_available = true;
					break;
				}
			}

			if ( $any_available ) {
				// Other slots available - prompt user to pick one
				?>
				<span class="sp-slot-full-note"><?php esc_html_e( 'Pick another time', 'societypress' ); ?></span>
				<?php
			}
			// If ALL full, don't show anything here - waitlist button is below the table
			return;
		}

		// Slot has capacity - show register button
		?>
		<button type="button" class="sp-register-btn" data-slot-id="<?php echo esc_attr( $slot_id ); ?>">
			<?php esc_html_e( 'Register', 'societypress' ); ?>
		</button>
		<?php
	}

	/**
	 * Get the current user's member ID.
	 *
	 * @return int|false Member ID or false if not a member.
	 */
	private function get_current_member_id() {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$user_id = get_current_user_id();
		$member_id = get_user_meta( $user_id, 'societypress_member_id', true );

		return $member_id ? (int) $member_id : false;
	}

	/**
	 * AJAX handler for event registration.
	 */
	public function ajax_register(): void {
		// Verify nonce with JSON error response instead of die()
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'sp_event_registration' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh the page and try again.', 'societypress' ) ) );
		}
		$slot_id = isset( $_POST['slot_id'] ) ? absint( $_POST['slot_id'] ) : 0;

		if ( ! $slot_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid slot.', 'societypress' ) ) );
		}

		$member_id = $this->get_current_member_id();

		if ( ! $member_id ) {
			wp_send_json_error( array( 'message' => __( 'You must be a member to register.', 'societypress' ) ) );
		}

		// Attempt registration
		$result = societypress()->event_registrations->register( $slot_id, $member_id );

		if ( $result['success'] ) {
			// Get updated slot HTML for the response
			$slot = societypress()->event_slots->get( $slot_id );
			$event_id = $slot['event_id'];
			$existing_registration = societypress()->event_registrations->get_member_event_registration( $event_id, $member_id );

			ob_start();
			$this->render_slot_row( $slot, $member_id, $existing_registration );
			$row_html = ob_get_clean();

			wp_send_json_success( array(
				'message' => $result['message'],
				'status'  => $result['status'],
				'rowHtml' => $row_html,
			) );
		} else {
			wp_send_json_error( array( 'message' => $result['message'] ) );
		}
	}

	/**
	 * AJAX handler for cancelling registration.
	 */
	public function ajax_cancel(): void {
		// Verify nonce with JSON error response instead of die()
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'sp_event_registration' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh the page and try again.', 'societypress' ) ) );
		}

		$registration_id = isset( $_POST['registration_id'] ) ? absint( $_POST['registration_id'] ) : 0;

		if ( ! $registration_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid registration.', 'societypress' ) ) );
		}

		// Verify the registration belongs to the current member
		$registration = societypress()->event_registrations->get( $registration_id );
		$member_id = $this->get_current_member_id();

		if ( ! $registration || (int) $registration['member_id'] !== $member_id ) {
			wp_send_json_error( array( 'message' => __( 'You cannot cancel this registration.', 'societypress' ) ) );
		}

		// Cancel the registration
		$result = societypress()->event_registrations->cancel( $registration_id );

		if ( $result['success'] ) {
			// Get updated slot HTML for the response
			$slot = societypress()->event_slots->get( (int) $registration['slot_id'] );
			$event_id = $slot['event_id'];
			$existing_registration = societypress()->event_registrations->get_member_event_registration( $event_id, $member_id );

			ob_start();
			$this->render_slot_row( $slot, $member_id, $existing_registration );
			$row_html = ob_get_clean();

			wp_send_json_success( array(
				'message' => $result['message'],
				'rowHtml' => $row_html,
			) );
		} else {
			wp_send_json_error( array( 'message' => $result['message'] ) );
		}
	}

	/**
	 * AJAX handler for joining the event-wide waitlist.
	 */
	public function ajax_join_waitlist(): void {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'sp_event_registration' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh the page and try again.', 'societypress' ) ) );
		}

		$event_id = isset( $_POST['event_id'] ) ? absint( $_POST['event_id'] ) : 0;

		if ( ! $event_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid event.', 'societypress' ) ) );
		}

		$member_id = $this->get_current_member_id();

		if ( ! $member_id ) {
			wp_send_json_error( array( 'message' => __( 'You must be a member to join the waitlist.', 'societypress' ) ) );
		}

		// Attempt to join waitlist
		$result = societypress()->event_registrations->join_event_waitlist( $event_id, $member_id );

		if ( $result['success'] ) {
			wp_send_json_success( array(
				'message' => $result['message'],
				'status'  => 'waitlist',
				'reload'  => true, // Tell JS to reload to show updated UI
			) );
		} else {
			wp_send_json_error( array( 'message' => $result['message'] ) );
		}
	}

	/**
	 * AJAX handler for non-logged-in users.
	 */
	public function ajax_not_logged_in(): void {
		wp_send_json_error( array(
			'message'   => __( 'Please log in to continue.', 'societypress' ),
			'loginUrl'  => wp_login_url(),
			'needLogin' => true,
		) );
	}
}
