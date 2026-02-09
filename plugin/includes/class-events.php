<?php
/**
 * Events Management
 *
 * Registers and manages the Events custom post type.
 * Handles event meta fields (date, time, location, registration).
 *
 * WHY: Societies need to manage and display classes, meetings, workshops, and special events.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SocietyPress_Events
 *
 * Manages events custom post type and metadata.
 */
class SocietyPress_Events {

	/**
	 * Meta field prefix.
	 *
	 * WHY: Prefixing prevents conflicts with other plugins.
	 *
	 * @var string
	 */
	private const META_PREFIX = 'sp_event_';

	/**
	 * Constructor.
	 *
	 * WHY: Registers hooks for CPT and meta boxes.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_taxonomy' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_sp_event', array( $this, 'save_event_meta' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'add_duplicate_link' ), 10, 2 );
		add_action( 'admin_action_duplicate_event', array( $this, 'duplicate_event' ) );

		// iCal export: serves .ics download when ?sp_ical={event_id} is in the URL
		add_action( 'template_redirect', array( $this, 'handle_ical_download' ) );
	}

	/**
	 * Register Events custom post type.
	 *
	 * WHY: Events are distinct from standard posts/pages and need custom fields.
	 */
	public function register_post_type(): void {
		$labels = array(
			'name'                  => _x( 'Events', 'Post type general name', 'societypress' ),
			'singular_name'         => _x( 'Event', 'Post type singular name', 'societypress' ),
			'menu_name'             => _x( 'Events', 'Admin Menu text', 'societypress' ),
			'name_admin_bar'        => _x( 'Event', 'Add New on Toolbar', 'societypress' ),
			'add_new'               => __( 'Add New', 'societypress' ),
			'add_new_item'          => __( 'Add New Event', 'societypress' ),
			'new_item'              => __( 'New Event', 'societypress' ),
			'edit_item'             => __( 'Edit Event', 'societypress' ),
			'view_item'             => __( 'View Event', 'societypress' ),
			'all_items'             => __( 'All Events', 'societypress' ),
			'search_items'          => __( 'Search Events', 'societypress' ),
			'parent_item_colon'     => __( 'Parent Events:', 'societypress' ),
			'not_found'             => __( 'No events found.', 'societypress' ),
			'not_found_in_trash'    => __( 'No events found in Trash.', 'societypress' ),
			'featured_image'        => _x( 'Event Image', 'Overrides the "Featured Image" phrase', 'societypress' ),
			'set_featured_image'    => _x( 'Set event image', 'Overrides the "Set featured image" phrase', 'societypress' ),
			'remove_featured_image' => _x( 'Remove event image', 'Overrides the "Remove featured image" phrase', 'societypress' ),
			'use_featured_image'    => _x( 'Use as event image', 'Overrides the "Use as featured image" phrase', 'societypress' ),
			'archives'              => _x( 'Event archives', 'The post type archive label', 'societypress' ),
			'insert_into_item'      => _x( 'Insert into event', 'Overrides the "Insert into post" phrase', 'societypress' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this event', 'Overrides the "Uploaded to this post" phrase', 'societypress' ),
			'filter_items_list'     => _x( 'Filter events list', 'Screen reader text', 'societypress' ),
			'items_list_navigation' => _x( 'Events list navigation', 'Screen reader text', 'societypress' ),
			'items_list'            => _x( 'Events list', 'Screen reader text', 'societypress' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'events' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 32,
			'menu_icon'          => 'dashicons-calendar-alt',
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'show_in_rest'       => true,
		);

		register_post_type( 'sp_event', $args );
	}

	/**
	 * Register Event Categories taxonomy.
	 *
	 * WHY: Allows categorizing events (Classes, Meetings, Workshops, Special Events).
	 */
	public function register_taxonomy(): void {
		$labels = array(
			'name'              => _x( 'Event Categories', 'taxonomy general name', 'societypress' ),
			'singular_name'     => _x( 'Event Category', 'taxonomy singular name', 'societypress' ),
			'search_items'      => __( 'Search Event Categories', 'societypress' ),
			'all_items'         => __( 'All Event Categories', 'societypress' ),
			'parent_item'       => __( 'Parent Event Category', 'societypress' ),
			'parent_item_colon' => __( 'Parent Event Category:', 'societypress' ),
			'edit_item'         => __( 'Edit Event Category', 'societypress' ),
			'update_item'       => __( 'Update Event Category', 'societypress' ),
			'add_new_item'      => __( 'Add New Event Category', 'societypress' ),
			'new_item_name'     => __( 'New Event Category Name', 'societypress' ),
			'menu_name'         => __( 'Event Categories', 'societypress' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'event-category' ),
			'show_in_rest'      => true,
		);

		register_taxonomy( 'sp_event_category', array( 'sp_event' ), $args );

		// Create default categories if they don't exist
		$this->create_default_categories();
	}

	/**
	 * Create default event categories.
	 *
	 * WHY: Provides standard categories: Classes, Meetings, Workshops, Special Events.
	 */
	private function create_default_categories(): void {
		$defaults = array( 'Classes', 'Meetings', 'Workshops', 'Special Events' );

		foreach ( $defaults as $name ) {
			if ( ! term_exists( $name, 'sp_event_category' ) ) {
				wp_insert_term( $name, 'sp_event_category' );
			}
		}
	}

	/**
	 * Add meta boxes for event details.
	 *
	 * WHY: Admin needs fields to enter event date, time, location, etc.
	 */
	public function add_meta_boxes(): void {
		add_meta_box(
			'sp_event_details',
			__( 'Event Details', 'societypress' ),
			array( $this, 'render_event_details_meta_box' ),
			'sp_event',
			'normal',
			'high'
		);

		// Add time slots meta box for registration-enabled events
		add_meta_box(
			'sp_event_slots',
			__( 'Registration Time Slots', 'societypress' ),
			array( $this, 'render_event_slots_meta_box' ),
			'sp_event',
			'normal',
			'default'
		);
	}

	/**
	 * Render event details meta box.
	 *
	 * WHY: Provides UI for entering event metadata.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_event_details_meta_box( $post ): void {
		// Add nonce for security
		wp_nonce_field( 'sp_event_meta_box', 'sp_event_meta_box_nonce' );

		// Get existing values (with defaults for new events)
		$date              = get_post_meta( $post->ID, self::META_PREFIX . 'date', true );
		$time              = get_post_meta( $post->ID, self::META_PREFIX . 'time', true );
		$end_time          = get_post_meta( $post->ID, self::META_PREFIX . 'end_time', true );
		$location          = get_post_meta( $post->ID, self::META_PREFIX . 'location', true );
		$address           = get_post_meta( $post->ID, self::META_PREFIX . 'address', true );
		$instructors       = get_post_meta( $post->ID, self::META_PREFIX . 'instructors', true );
		$registration_url  = get_post_meta( $post->ID, self::META_PREFIX . 'registration_url', true );
		$registration_req  = get_post_meta( $post->ID, self::META_PREFIX . 'registration_required', true );
		$notice_only       = get_post_meta( $post->ID, self::META_PREFIX . 'notice_only', true );
		$recurring         = get_post_meta( $post->ID, self::META_PREFIX . 'recurring', true );
		$recurring_end     = get_post_meta( $post->ID, self::META_PREFIX . 'recurring_end', true );
		$recurring_day     = get_post_meta( $post->ID, self::META_PREFIX . 'recurring_day', true );
		$recurring_week    = get_post_meta( $post->ID, self::META_PREFIX . 'recurring_week', true );

		// Set defaults for new events from Organization settings.
		// WHY: Each organization should see their own address as the default,
		//      not a hardcoded example. Pull from SocietyPress > Settings > Organization.
		if ( 'auto-draft' === $post->post_status || empty( $location ) ) {
			$location = SocietyPress_Admin::get_setting( 'organization_name', '' );
		}
		if ( 'auto-draft' === $post->post_status || empty( $address ) ) {
			$address = SocietyPress_Admin::get_setting( 'organization_address', '' );
		}

		?>
		<table class="form-table">
			<tr>
				<th><label for="sp_event_date"><?php esc_html_e( 'Event Date', 'societypress' ); ?></label></th>
				<td>
					<input type="date" id="sp_event_date" name="sp_event_date" value="<?php echo esc_attr( $date ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Date of the first event (or only event if not recurring)', 'societypress' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="sp_event_recurring"><?php esc_html_e( 'Recurring Event', 'societypress' ); ?></label></th>
				<td>
					<select id="sp_event_recurring" name="sp_event_recurring" class="regular-text">
						<option value="" <?php selected( $recurring, '' ); ?>><?php esc_html_e( 'Does not repeat', 'societypress' ); ?></option>
						<option value="weekly" <?php selected( $recurring, 'weekly' ); ?>><?php esc_html_e( 'Weekly', 'societypress' ); ?></option>
						<option value="monthly" <?php selected( $recurring, 'monthly' ); ?>><?php esc_html_e( 'Monthly (same day of week)', 'societypress' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Whether this event repeats', 'societypress' ); ?></p>
				</td>
			</tr>
			<tr id="sp_recurring_monthly_options" style="<?php echo ( 'monthly' === $recurring ) ? '' : 'display:none;'; ?>">
				<th><label for="sp_event_recurring_week"><?php esc_html_e( 'Which Week', 'societypress' ); ?></label></th>
				<td>
					<select id="sp_event_recurring_week" name="sp_event_recurring_week" class="regular-text">
						<option value="1" <?php selected( $recurring_week, '1' ); ?>><?php esc_html_e( '1st', 'societypress' ); ?></option>
						<option value="2" <?php selected( $recurring_week, '2' ); ?>><?php esc_html_e( '2nd', 'societypress' ); ?></option>
						<option value="3" <?php selected( $recurring_week, '3' ); ?>><?php esc_html_e( '3rd', 'societypress' ); ?></option>
						<option value="4" <?php selected( $recurring_week, '4' ); ?>><?php esc_html_e( '4th', 'societypress' ); ?></option>
						<option value="last" <?php selected( $recurring_week, 'last' ); ?>><?php esc_html_e( 'Last', 'societypress' ); ?></option>
					</select>
					<select id="sp_event_recurring_day" name="sp_event_recurring_day" class="regular-text">
						<option value="0" <?php selected( $recurring_day, '0' ); ?>><?php esc_html_e( 'Sunday', 'societypress' ); ?></option>
						<option value="1" <?php selected( $recurring_day, '1' ); ?>><?php esc_html_e( 'Monday', 'societypress' ); ?></option>
						<option value="2" <?php selected( $recurring_day, '2' ); ?>><?php esc_html_e( 'Tuesday', 'societypress' ); ?></option>
						<option value="3" <?php selected( $recurring_day, '3' ); ?>><?php esc_html_e( 'Wednesday', 'societypress' ); ?></option>
						<option value="4" <?php selected( $recurring_day, '4' ); ?>><?php esc_html_e( 'Thursday', 'societypress' ); ?></option>
						<option value="5" <?php selected( $recurring_day, '5' ); ?>><?php esc_html_e( 'Friday', 'societypress' ); ?></option>
						<option value="6" <?php selected( $recurring_day, '6' ); ?>><?php esc_html_e( 'Saturday', 'societypress' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Example: 2nd Tuesday of every month', 'societypress' ); ?></p>
				</td>
			</tr>
			<tr id="sp_recurring_end_date" style="<?php echo ( ! empty( $recurring ) ) ? '' : 'display:none;'; ?>">
				<th><label for="sp_event_recurring_end"><?php esc_html_e( 'Repeat Until', 'societypress' ); ?></label></th>
				<td>
					<input type="date" id="sp_event_recurring_end" name="sp_event_recurring_end" value="<?php echo esc_attr( $recurring_end ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Last date this event will occur (leave blank for no end date)', 'societypress' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="sp_event_time"><?php esc_html_e( 'Start Time', 'societypress' ); ?></label></th>
				<td>
					<input type="time" id="sp_event_time" name="sp_event_time" value="<?php echo esc_attr( $time ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Event start time', 'societypress' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="sp_event_end_time"><?php esc_html_e( 'End Time', 'societypress' ); ?></label></th>
				<td>
					<input type="time" id="sp_event_end_time" name="sp_event_end_time" value="<?php echo esc_attr( $end_time ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Event end time (optional)', 'societypress' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="sp_event_location"><?php esc_html_e( 'Location', 'societypress' ); ?></label></th>
				<td>
					<input type="text" id="sp_event_location" name="sp_event_location" value="<?php echo esc_attr( $location ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Venue name', 'societypress' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="sp_event_address"><?php esc_html_e( 'Address', 'societypress' ); ?></label></th>
				<td>
					<textarea id="sp_event_address" name="sp_event_address" rows="3" class="large-text"><?php echo esc_textarea( $address ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Full address of the venue', 'societypress' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="sp_event_instructors"><?php esc_html_e( 'Instructor(s)', 'societypress' ); ?></label></th>
				<td>
					<input type="text" id="sp_event_instructors" name="sp_event_instructors" value="<?php echo esc_attr( $instructors ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Name(s) of instructor(s) for this event', 'societypress' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="sp_event_registration_url"><?php esc_html_e( 'Registration URL', 'societypress' ); ?></label></th>
				<td>
					<input type="url" id="sp_event_registration_url" name="sp_event_registration_url" value="<?php echo esc_url( $registration_url ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'External registration link (optional)', 'societypress' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="sp_event_registration_required"><?php esc_html_e( 'Registration Required', 'societypress' ); ?></label></th>
				<td>
					<label>
						<input type="checkbox" id="sp_event_registration_required" name="sp_event_registration_required" value="1" <?php checked( $registration_req, '1' ); ?>>
						<?php esc_html_e( 'Registration is required for this event', 'societypress' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th><label for="sp_event_notice_only"><?php esc_html_e( 'Notice Only', 'societypress' ); ?></label></th>
				<td>
					<label>
						<input type="checkbox" id="sp_event_notice_only" name="sp_event_notice_only" value="1" <?php checked( $notice_only, '1' ); ?>>
						<?php esc_html_e( 'Calendar notice only — no detail page', 'societypress' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Use for closures, reminders, and other simple notices that don\'t need their own page.', 'societypress' ); ?></p>
				</td>
			</tr>
		</table>
		<script>
		(function($) {
			$(document).ready(function() {
				$('#sp_event_recurring').on('change', function() {
					var value = $(this).val();
					if (value === 'monthly') {
						$('#sp_recurring_monthly_options').show();
						$('#sp_recurring_end_date').show();
					} else if (value === 'weekly') {
						$('#sp_recurring_monthly_options').hide();
						$('#sp_recurring_end_date').show();
					} else {
						$('#sp_recurring_monthly_options').hide();
						$('#sp_recurring_end_date').hide();
					}
				});
			});
		})(jQuery);
		</script>
		<?php
	}

	/**
	 * Save event meta data.
	 *
	 * WHY: Persists event details when admin saves the post.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_event_meta( $post_id, $post ): void {
		// Check nonce
		if ( ! isset( $_POST['sp_event_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['sp_event_meta_box_nonce'], 'sp_event_meta_box' ) ) {
			return;
		}

		// Check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save each field
		$fields = array(
			'date'                  => 'sanitize_text_field',
			'time'                  => 'sanitize_text_field',
			'end_time'              => 'sanitize_text_field',
			'location'              => 'sanitize_text_field',
			'address'               => 'sanitize_textarea_field',
			'instructors'           => 'sanitize_text_field',
			'registration_url'      => 'esc_url_raw',
			'registration_required' => 'absint',
			'notice_only'           => 'absint',
			'recurring'             => 'sanitize_text_field',
			'recurring_end'         => 'sanitize_text_field',
			'recurring_day'         => 'sanitize_text_field',
			'recurring_week'        => 'sanitize_text_field',
		);

		foreach ( $fields as $field => $sanitize_callback ) {
			$meta_key = self::META_PREFIX . $field;
			$post_key = 'sp_event_' . $field;

			if ( isset( $_POST[ $post_key ] ) ) {
				$value = call_user_func( $sanitize_callback, $_POST[ $post_key ] );
				update_post_meta( $post_id, $meta_key, $value );
			} else {
				// Checkbox fields need special handling (unchecked = not in $_POST)
				if ( in_array( $field, array( 'registration_required', 'notice_only' ), true ) ) {
					update_post_meta( $post_id, $meta_key, 0 );
				}
			}
		}

		// Save event time slots
		$this->save_event_slots( $post_id );
	}

	/**
	 * Save event time slots from the meta box.
	 *
	 * WHY: Handles the repeatable slot rows, creating/updating/removing slots
	 *      based on what the admin submits in the form.
	 *
	 * @param int $post_id The event post ID.
	 */
	private function save_event_slots( int $post_id ): void {
		// Check if slots data was submitted
		if ( ! isset( $_POST['sp_event_slots'] ) || ! is_array( $_POST['sp_event_slots'] ) ) {
			return;
		}

		// Ensure event_slots class exists
		if ( ! isset( societypress()->event_slots ) ) {
			return;
		}

		$slots_data = array();

		foreach ( $_POST['sp_event_slots'] as $slot ) {
			// Skip completely empty rows
			if ( empty( $slot['start_time'] ) && empty( $slot['end_time'] ) ) {
				continue;
			}

			$slots_data[] = array(
				'id'          => isset( $slot['id'] ) ? absint( $slot['id'] ) : 0,
				'start_time'  => sanitize_text_field( $slot['start_time'] ?? '' ),
				'end_time'    => sanitize_text_field( $slot['end_time'] ?? '' ),
				'capacity'    => isset( $slot['capacity'] ) && $slot['capacity'] !== '' ? absint( $slot['capacity'] ) : null,
				'description' => sanitize_text_field( $slot['description'] ?? '' ),
			);
		}

		societypress()->event_slots->save_event_slots( $post_id, $slots_data );
	}

	/**
	 * Render the event time slots meta box.
	 *
	 * WHY: Provides a repeatable row interface for adding multiple time slots
	 *      to an event, each with start time, end time, capacity, and description.
	 *      Members can then register for specific slots.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_event_slots_meta_box( $post ): void {
		// Get existing slots if any
		$slots = array();
		if ( isset( societypress()->event_slots ) && $post->post_status !== 'auto-draft' ) {
			$slots = societypress()->event_slots->get_by_event( $post->ID, false );
		}

		// Ensure at least one empty row for new events
		if ( empty( $slots ) ) {
			$slots = array(
				array(
					'id'          => '',
					'start_time'  => '',
					'end_time'    => '',
					'capacity'    => '',
					'description' => '',
					'is_active'   => 1,
				),
			);
		}
		?>
		<div id="sp-event-slots-container">
			<p class="description" style="margin-bottom: 15px;">
				<?php esc_html_e( 'Add time slots if you want members to register for specific times. Leave empty if no registration is needed or if the event has a single time.', 'societypress' ); ?>
			</p>

			<table class="widefat sp-event-slots-table" id="sp-event-slots-table">
				<thead>
					<tr>
						<th style="width: 130px;"><?php esc_html_e( 'Start Time', 'societypress' ); ?></th>
						<th style="width: 130px;"><?php esc_html_e( 'End Time', 'societypress' ); ?></th>
						<th style="width: 100px;"><?php esc_html_e( 'Capacity', 'societypress' ); ?></th>
						<th><?php esc_html_e( 'Description', 'societypress' ); ?></th>
						<th style="width: 120px;"><?php esc_html_e( 'Registrations', 'societypress' ); ?></th>
						<th style="width: 50px;"></th>
					</tr>
				</thead>
				<tbody id="sp-event-slots-body">
					<?php foreach ( $slots as $index => $slot ) : ?>
						<?php $this->render_slot_row( $index, $slot ); ?>
					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="6">
							<button type="button" class="button" id="sp-add-slot-row">
								<?php esc_html_e( '+ Add Time Slot', 'societypress' ); ?>
							</button>
						</td>
					</tr>
				</tfoot>
			</table>
		</div>

		<!-- Template for new rows (used by JavaScript) -->
		<script type="text/template" id="sp-slot-row-template">
			<?php $this->render_slot_row( '{{INDEX}}', array( 'id' => '', 'start_time' => '', 'end_time' => '', 'capacity' => '', 'description' => '', 'is_active' => 1 ) ); ?>
		</script>
		<?php
	}

	/**
	 * Render a single slot row for the meta box.
	 *
	 * WHY: Reusable row template used both for existing slots and as a
	 *      JavaScript template for adding new rows.
	 *
	 * @param int|string $index Row index (or '{{INDEX}}' for template).
	 * @param array      $slot  Slot data.
	 */
	private function render_slot_row( $index, array $slot ): void {
		$is_active = isset( $slot['is_active'] ) ? (bool) $slot['is_active'] : true;
		$row_class = $is_active ? '' : 'sp-slot-inactive';

		// Get registration count for existing slots
		$registration_count = 0;
		$slot_id = isset( $slot['id'] ) ? (int) $slot['id'] : 0;
		if ( $slot_id && isset( societypress()->event_slots ) ) {
			$registration_count = societypress()->event_slots->get_registration_count( $slot_id );
		}
		?>
		<tr class="sp-slot-row <?php echo esc_attr( $row_class ); ?>" data-index="<?php echo esc_attr( $index ); ?>">
			<td>
				<input type="hidden" name="sp_event_slots[<?php echo esc_attr( $index ); ?>][id]" value="<?php echo esc_attr( $slot['id'] ?? '' ); ?>">
				<input type="time" name="sp_event_slots[<?php echo esc_attr( $index ); ?>][start_time]" value="<?php echo esc_attr( isset( $slot['start_time'] ) ? substr( $slot['start_time'], 0, 5 ) : '' ); ?>" class="sp-slot-start-time">
			</td>
			<td>
				<input type="time" name="sp_event_slots[<?php echo esc_attr( $index ); ?>][end_time]" value="<?php echo esc_attr( isset( $slot['end_time'] ) ? substr( $slot['end_time'], 0, 5 ) : '' ); ?>" class="sp-slot-end-time">
			</td>
			<td>
				<input type="number" name="sp_event_slots[<?php echo esc_attr( $index ); ?>][capacity]" value="<?php echo esc_attr( $slot['capacity'] ?? '' ); ?>" min="0" placeholder="<?php esc_attr_e( 'Unlimited', 'societypress' ); ?>" class="sp-slot-capacity" style="width: 90px;">
			</td>
			<td>
				<input type="text" name="sp_event_slots[<?php echo esc_attr( $index ); ?>][description]" value="<?php echo esc_attr( $slot['description'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'e.g., Morning Session', 'societypress' ); ?>" class="sp-slot-description" style="width: 100%;">
			</td>
			<td class="sp-slot-registrations">
				<?php if ( $slot_id ) : ?>
					<span class="sp-registration-count"><?php echo esc_html( $registration_count ); ?></span>
					<?php if ( $registration_count > 0 ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-event-registrations&slot_id=' . $slot_id ) ); ?>" class="sp-view-registrations" title="<?php esc_attr_e( 'View registrations', 'societypress' ); ?>">
							<?php esc_html_e( 'View', 'societypress' ); ?>
						</a>
					<?php endif; ?>
				<?php else : ?>
					<span class="description"><?php esc_html_e( 'Save to view', 'societypress' ); ?></span>
				<?php endif; ?>
			</td>
			<td>
				<button type="button" class="button sp-remove-slot-row" title="<?php esc_attr_e( 'Remove slot', 'societypress' ); ?>">&times;</button>
			</td>
		</tr>
		<?php
	}

	/**
	 * Get event date.
	 *
	 * WHY: Helper method for theme to retrieve event date.
	 *
	 * @param int $post_id Post ID.
	 * @return string Event date.
	 */
	public static function get_event_date( int $post_id ): string {
		return get_post_meta( $post_id, self::META_PREFIX . 'date', true );
	}

	/**
	 * Get event time.
	 *
	 * WHY: Helper method for theme to retrieve event time.
	 *
	 * @param int $post_id Post ID.
	 * @return string Event time.
	 */
	public static function get_event_time( int $post_id ): string {
		return get_post_meta( $post_id, self::META_PREFIX . 'time', true );
	}

	/**
	 * Get event end time.
	 *
	 * WHY: Helper method for theme to retrieve event end time.
	 *
	 * @param int $post_id Post ID.
	 * @return string Event end time.
	 */
	public static function get_event_end_time( int $post_id ): string {
		return get_post_meta( $post_id, self::META_PREFIX . 'end_time', true );
	}

	/**
	 * Get event location.
	 *
	 * WHY: Helper method for theme to retrieve event location.
	 *
	 * @param int $post_id Post ID.
	 * @return string Event location.
	 */
	public static function get_event_location( int $post_id ): string {
		return get_post_meta( $post_id, self::META_PREFIX . 'location', true );
	}

	/**
	 * Get event address.
	 *
	 * WHY: Helper method for theme to retrieve event address.
	 *
	 * @param int $post_id Post ID.
	 * @return string Event address.
	 */
	public static function get_event_address( int $post_id ): string {
		return get_post_meta( $post_id, self::META_PREFIX . 'address', true );
	}

	/**
	 * Get event instructors.
	 *
	 * WHY: Helper method for theme to retrieve event instructor(s).
	 *
	 * @param int $post_id Post ID.
	 * @return string Event instructors.
	 */
	public static function get_event_instructors( int $post_id ): string {
		return get_post_meta( $post_id, self::META_PREFIX . 'instructors', true );
	}

	/**
	 * Get event registration URL.
	 *
	 * WHY: Helper method for theme to retrieve registration URL.
	 *
	 * @param int $post_id Post ID.
	 * @return string Registration URL.
	 */
	public static function get_event_registration_url( int $post_id ): string {
		return get_post_meta( $post_id, self::META_PREFIX . 'registration_url', true );
	}

	/**
	 * Check if registration is required.
	 *
	 * WHY: Helper method for theme to check registration requirement.
	 *
	 * @param int $post_id Post ID.
	 * @return bool True if registration required.
	 */
	public static function is_registration_required( int $post_id ): bool {
		return (bool) get_post_meta( $post_id, self::META_PREFIX . 'registration_required', true );
	}

	/**
	 * Check if event is notice-only (no detail page).
	 *
	 * WHY: Notice-only events (closures, reminders) appear on calendars
	 *      and listings but shouldn't link to their own detail page.
	 *      The theme uses this to decide whether to link the event title.
	 *
	 * @param int $post_id Post ID.
	 * @return bool True if notice-only.
	 */
	public static function is_notice_only( int $post_id ): bool {
		return (bool) get_post_meta( $post_id, self::META_PREFIX . 'notice_only', true );
	}

	/**
	 * Get formatted event datetime.
	 *
	 * WHY: Provides formatted date/time string for display.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $format  PHP date format.
	 * @return string Formatted datetime.
	 */
	public static function get_formatted_datetime( int $post_id, string $format = 'F j, Y g:i A' ): string {
		$date = self::get_event_date( $post_id );
		$time = self::get_event_time( $post_id );

		if ( empty( $date ) ) {
			return '';
		}

		$datetime_string = $date;
		if ( ! empty( $time ) ) {
			$datetime_string .= ' ' . $time;
		}

		$timestamp = strtotime( $datetime_string );
		return $timestamp ? date( $format, $timestamp ) : '';
	}

	/**
	 * Check if event is recurring.
	 *
	 * WHY: Helper method to determine if event repeats.
	 *
	 * @param int $post_id Post ID.
	 * @return bool True if recurring.
	 */
	public static function is_recurring( int $post_id ): bool {
		$recurring = get_post_meta( $post_id, self::META_PREFIX . 'recurring', true );
		return ! empty( $recurring );
	}

	/**
	 * Get recurring event description.
	 *
	 * WHY: Provides human-readable description of recurrence pattern.
	 *
	 * @param int $post_id Post ID.
	 * @return string Recurrence description.
	 */
	public static function get_recurring_description( int $post_id ): string {
		$recurring = get_post_meta( $post_id, self::META_PREFIX . 'recurring', true );

		if ( empty( $recurring ) ) {
			return '';
		}

		if ( 'weekly' === $recurring ) {
			return __( 'Repeats weekly', 'societypress' );
		}

		if ( 'monthly' === $recurring ) {
			$week = get_post_meta( $post_id, self::META_PREFIX . 'recurring_week', true );
			$day = get_post_meta( $post_id, self::META_PREFIX . 'recurring_day', true );

			$days = array(
				'0' => __( 'Sunday', 'societypress' ),
				'1' => __( 'Monday', 'societypress' ),
				'2' => __( 'Tuesday', 'societypress' ),
				'3' => __( 'Wednesday', 'societypress' ),
				'4' => __( 'Thursday', 'societypress' ),
				'5' => __( 'Friday', 'societypress' ),
				'6' => __( 'Saturday', 'societypress' ),
			);

			$weeks = array(
				'1'    => __( '1st', 'societypress' ),
				'2'    => __( '2nd', 'societypress' ),
				'3'    => __( '3rd', 'societypress' ),
				'4'    => __( '4th', 'societypress' ),
				'last' => __( 'Last', 'societypress' ),
			);

			$week_label = isset( $weeks[ $week ] ) ? $weeks[ $week ] : '';
			$day_label = isset( $days[ $day ] ) ? $days[ $day ] : '';

			if ( $week_label && $day_label ) {
				/* translators: 1: week (1st, 2nd, etc), 2: day of week */
				return sprintf( __( 'Repeats %1$s %2$s of every month', 'societypress' ), $week_label, $day_label );
			}
		}

		return '';
	}

	/**
	 * Calculate next occurrence of recurring event.
	 *
	 * WHY: Determines the next date this event will occur.
	 *
	 * @param int $post_id Post ID.
	 * @return string|false Next occurrence date (Y-m-d format) or false.
	 */
	public static function get_next_occurrence( int $post_id ) {
		$recurring = get_post_meta( $post_id, self::META_PREFIX . 'recurring', true );
		$start_date = get_post_meta( $post_id, self::META_PREFIX . 'date', true );
		$end_date = get_post_meta( $post_id, self::META_PREFIX . 'recurring_end', true );

		if ( empty( $recurring ) || empty( $start_date ) ) {
			return false;
		}

		$today = date( 'Y-m-d' );
		$start_timestamp = strtotime( $start_date );
		$end_timestamp = $end_date ? strtotime( $end_date ) : false;

		// If start date is in future, return it
		if ( $start_date >= $today ) {
			return $start_date;
		}

		if ( 'weekly' === $recurring ) {
			$current = strtotime( $start_date );
			while ( date( 'Y-m-d', $current ) < $today ) {
				$current = strtotime( '+1 week', $current );
			}

			if ( $end_timestamp && $current > $end_timestamp ) {
				return false;
			}

			return date( 'Y-m-d', $current );
		}

		if ( 'monthly' === $recurring ) {
			$week = get_post_meta( $post_id, self::META_PREFIX . 'recurring_week', true );
			$day = get_post_meta( $post_id, self::META_PREFIX . 'recurring_day', true );

			$current_month = strtotime( 'first day of this month' );
			$next_occurrence = self::calculate_monthly_occurrence( $current_month, $week, $day );

			// If this month's occurrence has passed, get next month's
			if ( date( 'Y-m-d', $next_occurrence ) < $today ) {
				$next_month = strtotime( 'first day of next month' );
				$next_occurrence = self::calculate_monthly_occurrence( $next_month, $week, $day );
			}

			if ( $end_timestamp && $next_occurrence > $end_timestamp ) {
				return false;
			}

			return date( 'Y-m-d', $next_occurrence );
		}

		return false;
	}

	/**
	 * Calculate specific occurrence in a month.
	 *
	 * WHY: Helper to find nth occurrence of a weekday in a month.
	 *
	 * @param int    $month_timestamp First day of month timestamp.
	 * @param string $week Which occurrence (1, 2, 3, 4, last).
	 * @param string $day Day of week (0-6).
	 * @return int Timestamp of the occurrence.
	 */
	private static function calculate_monthly_occurrence( int $month_timestamp, string $week, string $day ): int {
		$month_year = date( 'Y-m', $month_timestamp );

		if ( 'last' === $week ) {
			return strtotime( "last " . date( 'l', strtotime( "Sunday + $day days" ) ) . " of $month_year" );
		}

		$ordinal = array( '1' => 'first', '2' => 'second', '3' => 'third', '4' => 'fourth' );
		$week_text = isset( $ordinal[ $week ] ) ? $ordinal[ $week ] : 'first';
		$day_name = date( 'l', strtotime( "Sunday + $day days" ) );

		return strtotime( "$week_text $day_name of $month_year" );
	}

	/**
	 * Add duplicate link to event row actions.
	 *
	 * WHY: Allows quick duplication of events with all metadata.
	 *
	 * @param array   $actions Row actions array.
	 * @param WP_Post $post    Current post object.
	 * @return array Modified actions array.
	 */
	public function add_duplicate_link( array $actions, WP_Post $post ): array {
		// Only add for sp_event post type
		if ( 'sp_event' !== $post->post_type ) {
			return $actions;
		}

		// Check user capability
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return $actions;
		}

		// Create duplicate URL with nonce
		$duplicate_url = wp_nonce_url(
			admin_url( 'admin.php?action=duplicate_event&post=' . $post->ID ),
			'duplicate_event_' . $post->ID
		);

		// Add duplicate link after edit
		$new_actions = array();
		foreach ( $actions as $key => $action ) {
			$new_actions[ $key ] = $action;
			if ( 'edit' === $key ) {
				$new_actions['duplicate'] = '<a href="' . esc_url( $duplicate_url ) . '">' . __( 'Duplicate', 'societypress' ) . '</a>';
			}
		}

		return $new_actions;
	}

	/**
	 * Duplicate an event with all metadata.
	 *
	 * WHY: Creates a copy of an event including all custom fields and taxonomy terms.
	 */
	public function duplicate_event(): void {
		// Check if post ID is provided
		if ( ! isset( $_GET['post'] ) ) {
			wp_die( esc_html__( 'No event to duplicate.', 'societypress' ) );
		}

		$post_id = absint( $_GET['post'] );

		// Verify nonce
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'duplicate_event_' . $post_id ) ) {
			wp_die( esc_html__( 'Security check failed.', 'societypress' ) );
		}

		// Get original post
		$original_post = get_post( $post_id );

		if ( ! $original_post || 'sp_event' !== $original_post->post_type ) {
			wp_die( esc_html__( 'Invalid event.', 'societypress' ) );
		}

		// Check user capability
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'You do not have permission to duplicate this event.', 'societypress' ) );
		}

		// Create duplicate post
		$new_post_args = array(
			'post_title'     => $original_post->post_title . ' (Copy)',
			'post_content'   => $original_post->post_content,
			'post_excerpt'   => $original_post->post_excerpt,
			'post_status'    => 'draft',
			'post_type'      => $original_post->post_type,
			'post_author'    => get_current_user_id(),
			'menu_order'     => $original_post->menu_order,
			'comment_status' => $original_post->comment_status,
			'ping_status'    => $original_post->ping_status,
		);

		$new_post_id = wp_insert_post( $new_post_args );

		if ( is_wp_error( $new_post_id ) ) {
			wp_die( esc_html__( 'Failed to duplicate event.', 'societypress' ) );
		}

		// Copy all post meta
		$meta_keys = array(
			self::META_PREFIX . 'date',
			self::META_PREFIX . 'time',
			self::META_PREFIX . 'end_time',
			self::META_PREFIX . 'location',
			self::META_PREFIX . 'address',
			self::META_PREFIX . 'instructors',
			self::META_PREFIX . 'registration_url',
			self::META_PREFIX . 'registration_required',
			self::META_PREFIX . 'notice_only',
			self::META_PREFIX . 'recurring',
			self::META_PREFIX . 'recurring_end',
			self::META_PREFIX . 'recurring_day',
			self::META_PREFIX . 'recurring_week',
		);

		foreach ( $meta_keys as $meta_key ) {
			$meta_value = get_post_meta( $post_id, $meta_key, true );
			if ( $meta_value ) {
				update_post_meta( $new_post_id, $meta_key, $meta_value );
			}
		}

		// Copy taxonomy terms
		$taxonomies = get_object_taxonomies( $original_post->post_type );
		foreach ( $taxonomies as $taxonomy ) {
			$terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				wp_set_object_terms( $new_post_id, $terms, $taxonomy );
			}
		}

		// Copy featured image
		$thumbnail_id = get_post_thumbnail_id( $post_id );
		if ( $thumbnail_id ) {
			set_post_thumbnail( $new_post_id, $thumbnail_id );
		}

		// Redirect to edit new post
		wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
		exit;
	}

	/**
	 * Handle iCal (.ics) download request.
	 *
	 * WHY: Members often want to add society events directly to their personal
	 *      calendars (Google Calendar, Apple Calendar, Outlook). An iCal download
	 *      link makes this a one-click operation instead of manual entry.
	 *
	 * Triggered by: ?sp_ical={event_id}
	 */
	public function handle_ical_download(): void {
		if ( empty( $_GET['sp_ical'] ) ) {
			return;
		}

		$event_id = absint( $_GET['sp_ical'] );
		$event    = get_post( $event_id );

		// Validate this is a real, published event
		if ( ! $event || 'sp_event' !== $event->post_type || 'publish' !== $event->post_status ) {
			wp_die( esc_html__( 'Event not found.', 'societypress' ), 404 );
		}

		$event_date = self::get_event_date( $event_id );
		$event_time = self::get_event_time( $event_id );
		$end_time   = self::get_event_end_time( $event_id );
		$location   = self::get_event_location( $event_id );
		$address    = self::get_event_address( $event_id );

		// Build the full location string (venue + address)
		$full_location = $location;
		if ( $address ) {
			$full_location .= $location ? ', ' : '';
			// Flatten multi-line address into a single line for iCal
			$full_location .= str_replace( array( "\r\n", "\r", "\n" ), ', ', $address );
		}

		// Build DTSTART and DTEND in iCal format
		// If we have a date and time, use date-time format. Otherwise, use all-day format.
		if ( $event_date && $event_time ) {
			$dt_start = gmdate( 'Ymd\THis', strtotime( $event_date . ' ' . $event_time ) );
			if ( $end_time ) {
				$dt_end = gmdate( 'Ymd\THis', strtotime( $event_date . ' ' . $end_time ) );
			} else {
				// Default to 1 hour duration if no end time
				$dt_end = gmdate( 'Ymd\THis', strtotime( $event_date . ' ' . $event_time . ' +1 hour' ) );
			}
			$dtstart_line = 'DTSTART:' . $dt_start;
			$dtend_line   = 'DTEND:' . $dt_end;
		} elseif ( $event_date ) {
			// All-day event (VALUE=DATE means no time component)
			$dtstart_line = 'DTSTART;VALUE=DATE:' . gmdate( 'Ymd', strtotime( $event_date ) );
			$dtend_line   = 'DTEND;VALUE=DATE:' . gmdate( 'Ymd', strtotime( $event_date . ' +1 day' ) );
		} else {
			wp_die( esc_html__( 'This event does not have a date set.', 'societypress' ), 400 );
		}

		// Build a plain-text description from the event content
		$description = wp_strip_all_tags( $event->post_content );
		$description = str_replace( array( "\r\n", "\r" ), "\n", $description );

		// Generate a unique ID for the event (required by RFC 5545)
		$uid = $event_id . '-' . strtotime( $event->post_date ) . '@' . wp_parse_url( home_url(), PHP_URL_HOST );

		// Build the .ics content (RFC 5545 format)
		$ics  = "BEGIN:VCALENDAR\r\n";
		$ics .= "VERSION:2.0\r\n";
		$ics .= "PRODID:-//SocietyPress//Events//EN\r\n";
		$ics .= "CALSCALE:GREGORIAN\r\n";
		$ics .= "METHOD:PUBLISH\r\n";
		$ics .= "BEGIN:VEVENT\r\n";
		$ics .= 'UID:' . $uid . "\r\n";
		$ics .= 'DTSTAMP:' . gmdate( 'Ymd\THis\Z' ) . "\r\n";
		$ics .= $dtstart_line . "\r\n";
		$ics .= $dtend_line . "\r\n";
		$ics .= 'SUMMARY:' . self::ical_escape( $event->post_title ) . "\r\n";

		if ( $full_location ) {
			$ics .= 'LOCATION:' . self::ical_escape( $full_location ) . "\r\n";
		}

		if ( $description ) {
			$ics .= 'DESCRIPTION:' . self::ical_escape( $description ) . "\r\n";
		}

		$ics .= 'URL:' . get_permalink( $event_id ) . "\r\n";
		$ics .= "END:VEVENT\r\n";
		$ics .= "END:VCALENDAR\r\n";

		// Generate a clean filename from the event title
		$filename = sanitize_file_name( $event->post_title ) . '.ics';

		// Send headers and output the file
		header( 'Content-Type: text/calendar; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		echo $ics; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Raw iCal format, not HTML
		exit;
	}

	/**
	 * Escape a string for iCal format (RFC 5545).
	 *
	 * WHY: iCal has specific escaping rules — commas, semicolons, backslashes,
	 *      and newlines all need to be escaped or they'll break the parser
	 *      in calendar apps.
	 *
	 * @param string $text Text to escape.
	 * @return string Escaped text.
	 */
	private static function ical_escape( string $text ): string {
		$text = str_replace( '\\', '\\\\', $text );
		$text = str_replace( ',', '\\,', $text );
		$text = str_replace( ';', '\\;', $text );
		$text = str_replace( "\n", '\\n', $text );
		return $text;
	}

	/**
	 * Get the iCal download URL for an event.
	 *
	 * WHY: Provides a clean helper for templates and widgets to generate the download link.
	 *
	 * @param int $event_id The event post ID.
	 * @return string The iCal download URL.
	 */
	public static function get_ical_url( int $event_id ): string {
		return add_query_arg( 'sp_ical', $event_id, home_url( '/' ) );
	}
}
