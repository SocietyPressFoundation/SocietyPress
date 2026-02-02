<?php
/**
 * Volunteer Opportunities Admin Interface
 *
 * Handles admin pages for creating, editing, and managing volunteer
 * opportunities and signups. Committee chairs can manage their own
 * committee's opportunities, while admins can manage all.
 *
 * WHY: Provides a clean interface for posting volunteer needs and
 *      tracking who signed up. Keeps volunteer coordination simple.
 *
 * @package SocietyPress
 * @since 0.54d
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SocietyPress_Volunteer_Admin
 *
 * Admin interface for volunteer opportunities.
 */
class SocietyPress_Volunteer_Admin {

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
		add_action( 'admin_menu', array( $this, 'add_menus' ) );
		add_action( 'admin_init', array( $this, 'handle_actions' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Add admin menu items.
	 *
	 * WHY: Creates a "Volunteer" submenu under SocietyPress for managing
	 *      volunteer opportunities. Uses lower capability check to allow
	 *      committee chairs to access (fine-grained permissions handled in render).
	 */
	public function add_menus(): void {
		// Main volunteer opportunities list
		add_submenu_page(
			'societypress',
			__( 'Volunteer Opportunities', 'societypress' ),
			__( 'Volunteer', 'societypress' ),
			'read', // Low capability - we check permissions in the render methods
			'societypress-volunteer',
			array( $this, 'render_opportunities_page' )
		);

		// Hidden pages (accessible via URL only)
		// Add/Edit opportunity
		add_submenu_page(
			'',
			__( 'Edit Opportunity', 'societypress' ),
			__( 'Edit Opportunity', 'societypress' ),
			'read',
			'societypress-volunteer-edit',
			array( $this, 'render_opportunity_edit' )
		);

		// View signups for an opportunity
		add_submenu_page(
			'',
			__( 'Volunteer Signups', 'societypress' ),
			__( 'Volunteer Signups', 'societypress' ),
			'read',
			'societypress-volunteer-signups',
			array( $this, 'render_signups_page' )
		);
	}

	/**
	 * Enqueue admin assets for volunteer pages.
	 *
	 * @param string|null $hook Current admin page hook.
	 */
	public function enqueue_assets( ?string $hook ): void {
		// Only load on volunteer admin pages
		if ( null === $hook || strpos( $hook, 'societypress-volunteer' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'societypress-volunteer-admin',
			SOCIETYPRESS_URL . 'assets/css/volunteer-admin.css',
			array(),
			SOCIETYPRESS_VERSION
		);

		wp_enqueue_script(
			'societypress-volunteer-admin',
			SOCIETYPRESS_URL . 'assets/js/volunteer-admin.js',
			array( 'jquery' ),
			SOCIETYPRESS_VERSION,
			true
		);

		wp_localize_script(
			'societypress-volunteer-admin',
			'societypressVolunteerAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'societypress_volunteer_admin' ),
				'strings' => array(
					'confirmDelete'   => __( 'Are you sure you want to delete this opportunity? This cannot be undone.', 'societypress' ),
					'confirmClose'    => __( 'Are you sure you want to close this opportunity? Members will no longer be able to sign up.', 'societypress' ),
					'confirmCancel'   => __( 'Are you sure you want to cancel this signup?', 'societypress' ),
					'confirmComplete' => __( 'Mark this signup as completed?', 'societypress' ),
					'confirmNoShow'   => __( 'Mark this member as a no-show?', 'societypress' ),
				),
			)
		);
	}

	/**
	 * Handle form submissions and actions.
	 */
	public function handle_actions(): void {
		// Save opportunity
		if ( isset( $_POST['societypress_action'] ) && 'save_volunteer_opportunity' === $_POST['societypress_action'] ) {
			$this->save_opportunity();
		}

		// Delete opportunity
		if ( isset( $_GET['action'] ) && 'delete_opportunity' === $_GET['action'] && isset( $_GET['opportunity'] ) ) {
			$this->delete_opportunity();
		}

		// Close opportunity
		if ( isset( $_GET['action'] ) && 'close_opportunity' === $_GET['action'] && isset( $_GET['opportunity'] ) ) {
			$this->close_opportunity();
		}

		// Reopen opportunity
		if ( isset( $_GET['action'] ) && 'reopen_opportunity' === $_GET['action'] && isset( $_GET['opportunity'] ) ) {
			$this->reopen_opportunity();
		}

		// Signup actions
		if ( isset( $_GET['action'] ) && isset( $_GET['signup'] ) ) {
			$action = sanitize_text_field( $_GET['action'] );
			if ( in_array( $action, array( 'cancel_signup', 'complete_signup', 'noshow_signup' ), true ) ) {
				$this->handle_signup_action( $action );
			}
		}

		// Admin add signup
		if ( isset( $_POST['societypress_action'] ) && 'admin_add_signup' === $_POST['societypress_action'] ) {
			$this->admin_add_signup();
		}

		// Update signup hours
		if ( isset( $_POST['societypress_action'] ) && 'update_signup_hours' === $_POST['societypress_action'] ) {
			$this->update_signup_hours();
		}
	}

	/**
	 * Check if current user can manage a specific opportunity.
	 *
	 * WHY: Admins can manage all opportunities. Committee chairs can only
	 *      manage opportunities for their committee. Regular users cannot
	 *      manage any opportunities.
	 *
	 * @param int|null $committee_id Committee ID (null for society-wide).
	 * @return bool True if user can manage.
	 */
	private function can_manage_opportunity( ?int $committee_id ): bool {
		// Admins can manage all
		if ( current_user_can( 'manage_society_members' ) ) {
			return true;
		}

		// Must be a member to be a committee chair
		$member_id = $this->get_current_member_id();
		if ( ! $member_id ) {
			return false;
		}

		// Society-wide opportunities require admin
		if ( null === $committee_id ) {
			return false;
		}

		// Check if user is a chair/co-chair of this committee
		return societypress()->committees->is_chair( $committee_id, $member_id );
	}

	/**
	 * Check if current user can manage any opportunities.
	 *
	 * WHY: Used to determine if user should see the volunteer admin page at all.
	 *
	 * @return bool True if user can manage at least one committee's opportunities.
	 */
	private function can_manage_any_opportunities(): bool {
		// Admins can manage all
		if ( current_user_can( 'manage_society_members' ) ) {
			return true;
		}

		// Check if user is chair of any committee
		$member_id = $this->get_current_member_id();
		if ( ! $member_id ) {
			return false;
		}

		$chaired_committees = societypress()->committees->get_committees_where_chair( $member_id );
		return ! empty( $chaired_committees );
	}

	/**
	 * Get committees the current user can post opportunities for.
	 *
	 * WHY: Admins see all committees plus a "Society-wide" option.
	 *      Committee chairs only see their committee(s).
	 *
	 * @return array Array of committees with 'id' and 'name'.
	 */
	private function get_manageable_committees(): array {
		$committees = array();

		// Admins can post society-wide opportunities
		if ( current_user_can( 'manage_society_members' ) ) {
			$committees[] = array(
				'id'   => '',
				'name' => __( '— Society-wide —', 'societypress' ),
			);

			// Get all active committees
			$all_committees = societypress()->committees->get_all( true );
			foreach ( $all_committees as $committee ) {
				$committees[] = array(
					'id'   => $committee['id'],
					'name' => $committee['name'],
				);
			}
		} else {
			// Committee chairs only see their committees
			$member_id = $this->get_current_member_id();
			if ( $member_id ) {
				$chaired = societypress()->committees->get_committees_where_chair( $member_id );
				foreach ( $chaired as $committee ) {
					$committees[] = array(
						'id'   => $committee['id'],
						'name' => $committee['name'],
					);
				}
			}
		}

		return $committees;
	}

	/**
	 * Get current user's member ID.
	 *
	 * @return int|null Member ID or null if not a member.
	 */
	private function get_current_member_id(): ?int {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return null;
		}

		$member = societypress()->members->get_by_user_id( $user_id );
		return $member ? (int) $member->id : null;
	}

	/**
	 * Render the volunteer opportunities list page.
	 */
	public function render_opportunities_page(): void {
		// Check permissions
		if ( ! $this->can_manage_any_opportunities() ) {
			wp_die( __( 'You do not have permission to manage volunteer opportunities.', 'societypress' ) );
		}

		// Get filters
		$status_filter = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
		$committee_filter = isset( $_GET['committee'] ) ? sanitize_text_field( $_GET['committee'] ) : '';

		// Build query filters
		$filters = array();
		if ( $status_filter ) {
			$filters['status'] = $status_filter;
		}
		if ( $committee_filter === 'society' ) {
			$filters['committee_id'] = null;
		} elseif ( $committee_filter ) {
			$filters['committee_id'] = absint( $committee_filter );
		}

		// Non-admins only see their committee's opportunities
		if ( ! current_user_can( 'manage_society_members' ) ) {
			$member_id = $this->get_current_member_id();
			$chaired = societypress()->committees->get_committees_where_chair( $member_id );
			$committee_ids = wp_list_pluck( $chaired, 'id' );
			if ( empty( $committee_ids ) ) {
				$committee_ids = array( -1 ); // No results
			}
			$filters['committee_ids'] = $committee_ids;
		}

		$opportunities = societypress()->volunteer_opportunities->get_all( $filters );

		// Get committees for filter dropdown
		$committees = $this->get_manageable_committees();

		?>
		<div class="wrap societypress-admin societypress-volunteer-admin">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Volunteer Opportunities', 'societypress' ); ?></h1>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-volunteer-edit' ) ); ?>" class="page-title-action">
				<?php esc_html_e( 'Add New', 'societypress' ); ?>
			</a>
			<hr class="wp-header-end">

			<?php $this->render_admin_notices(); ?>

			<!-- Filters -->
			<div class="tablenav top">
				<form method="get" class="alignleft actions">
					<input type="hidden" name="page" value="societypress-volunteer">

					<select name="status">
						<option value=""><?php esc_html_e( 'All Statuses', 'societypress' ); ?></option>
						<option value="open" <?php selected( $status_filter, 'open' ); ?>><?php esc_html_e( 'Open', 'societypress' ); ?></option>
						<option value="filled" <?php selected( $status_filter, 'filled' ); ?>><?php esc_html_e( 'Filled', 'societypress' ); ?></option>
						<option value="closed" <?php selected( $status_filter, 'closed' ); ?>><?php esc_html_e( 'Closed', 'societypress' ); ?></option>
						<option value="cancelled" <?php selected( $status_filter, 'cancelled' ); ?>><?php esc_html_e( 'Cancelled', 'societypress' ); ?></option>
					</select>

					<?php if ( count( $committees ) > 1 ) : ?>
						<select name="committee">
							<option value=""><?php esc_html_e( 'All Committees', 'societypress' ); ?></option>
							<?php foreach ( $committees as $committee ) : ?>
								<option value="<?php echo esc_attr( $committee['id'] ?: 'society' ); ?>" <?php selected( $committee_filter, $committee['id'] ?: 'society' ); ?>>
									<?php echo esc_html( $committee['name'] ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					<?php endif; ?>

					<input type="submit" class="button" value="<?php esc_attr_e( 'Filter', 'societypress' ); ?>">
				</form>
			</div>

			<!-- Opportunities Table -->
			<table class="widefat striped societypress-opportunities-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Title', 'societypress' ); ?></th>
						<th><?php esc_html_e( 'Committee', 'societypress' ); ?></th>
						<th><?php esc_html_e( 'Type', 'societypress' ); ?></th>
						<th><?php esc_html_e( 'When', 'societypress' ); ?></th>
						<th><?php esc_html_e( 'Signups', 'societypress' ); ?></th>
						<th><?php esc_html_e( 'Status', 'societypress' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $opportunities ) ) : ?>
						<tr>
							<td colspan="6"><?php esc_html_e( 'No volunteer opportunities found.', 'societypress' ); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ( $opportunities as $opportunity ) : ?>
							<?php $this->render_opportunity_row( $opportunity ); ?>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render a single opportunity row in the list table.
	 *
	 * @param array $opportunity Opportunity data.
	 */
	private function render_opportunity_row( array $opportunity ): void {
		$edit_url = admin_url( 'admin.php?page=societypress-volunteer-edit&opportunity_id=' . $opportunity['id'] );
		$signups_url = admin_url( 'admin.php?page=societypress-volunteer-signups&opportunity_id=' . $opportunity['id'] );
		$delete_url = wp_nonce_url(
			admin_url( 'admin.php?page=societypress-volunteer&action=delete_opportunity&opportunity=' . $opportunity['id'] ),
			'delete_opportunity_' . $opportunity['id']
		);
		$close_url = wp_nonce_url(
			admin_url( 'admin.php?page=societypress-volunteer&action=close_opportunity&opportunity=' . $opportunity['id'] ),
			'close_opportunity_' . $opportunity['id']
		);
		$reopen_url = wp_nonce_url(
			admin_url( 'admin.php?page=societypress-volunteer&action=reopen_opportunity&opportunity=' . $opportunity['id'] ),
			'reopen_opportunity_' . $opportunity['id']
		);

		$signup_count = societypress()->volunteer_opportunities->get_signup_count( $opportunity['id'] );
		$capacity = $opportunity['capacity'];
		$type_label = societypress()->volunteer_opportunities->get_type_label( $opportunity['opportunity_type'] );
		$status_label = societypress()->volunteer_opportunities->get_status_label( $opportunity['status'] );
		$schedule = societypress()->volunteer_opportunities->format_schedule( $opportunity );

		?>
		<tr>
			<td>
				<strong><a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $opportunity['title'] ); ?></a></strong>
				<div class="row-actions">
					<span class="edit">
						<a href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit', 'societypress' ); ?></a> |
					</span>
					<span class="view">
						<a href="<?php echo esc_url( $signups_url ); ?>"><?php esc_html_e( 'View Signups', 'societypress' ); ?></a> |
					</span>
					<?php if ( $opportunity['status'] === 'open' || $opportunity['status'] === 'filled' ) : ?>
						<span class="close">
							<a href="<?php echo esc_url( $close_url ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to close this opportunity?', 'societypress' ) ); ?>');">
								<?php esc_html_e( 'Close', 'societypress' ); ?>
							</a> |
						</span>
					<?php elseif ( $opportunity['status'] === 'closed' ) : ?>
						<span class="reopen">
							<a href="<?php echo esc_url( $reopen_url ); ?>"><?php esc_html_e( 'Reopen', 'societypress' ); ?></a> |
						</span>
					<?php endif; ?>
					<?php if ( $signup_count === 0 ) : ?>
						<span class="delete">
							<a href="<?php echo esc_url( $delete_url ); ?>" class="submitdelete" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this opportunity?', 'societypress' ) ); ?>');">
								<?php esc_html_e( 'Delete', 'societypress' ); ?>
							</a>
						</span>
					<?php else : ?>
						<span class="delete" title="<?php esc_attr_e( 'Cannot delete opportunity with signups', 'societypress' ); ?>">
							<?php esc_html_e( 'Delete', 'societypress' ); ?>
						</span>
					<?php endif; ?>
				</div>
			</td>
			<td>
				<?php if ( $opportunity['committee_name'] ) : ?>
					<?php echo esc_html( $opportunity['committee_name'] ); ?>
				<?php else : ?>
					<em><?php esc_html_e( 'Society-wide', 'societypress' ); ?></em>
				<?php endif; ?>
			</td>
			<td><?php echo esc_html( $type_label ); ?></td>
			<td><?php echo esc_html( $schedule ); ?></td>
			<td>
				<a href="<?php echo esc_url( $signups_url ); ?>">
					<?php
					if ( $capacity ) {
						printf( '%d / %d', $signup_count, $capacity );
					} else {
						echo esc_html( $signup_count );
					}
					?>
				</a>
			</td>
			<td>
				<span class="societypress-status societypress-status-<?php echo esc_attr( $opportunity['status'] ); ?>">
					<?php echo esc_html( $status_label ); ?>
				</span>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render the opportunity add/edit page.
	 */
	public function render_opportunity_edit(): void {
		// Check base permissions
		if ( ! $this->can_manage_any_opportunities() ) {
			wp_die( __( 'You do not have permission to manage volunteer opportunities.', 'societypress' ) );
		}

		$opportunity_id = isset( $_GET['opportunity_id'] ) ? absint( $_GET['opportunity_id'] ) : 0;
		$opportunity = null;
		$is_new = true;

		if ( $opportunity_id ) {
			$opportunity = societypress()->volunteer_opportunities->get( $opportunity_id );
			if ( ! $opportunity ) {
				wp_die( __( 'Opportunity not found.', 'societypress' ) );
			}

			// Check permission for this specific opportunity
			$committee_id = $opportunity['committee_id'] ? (int) $opportunity['committee_id'] : null;
			if ( ! $this->can_manage_opportunity( $committee_id ) ) {
				wp_die( __( 'You do not have permission to edit this opportunity.', 'societypress' ) );
			}

			$is_new = false;
		}

		$page_title = $is_new ? __( 'Add New Volunteer Opportunity', 'societypress' ) : __( 'Edit Volunteer Opportunity', 'societypress' );
		$committees = $this->get_manageable_committees();

		// Get members list for contact person dropdown (limit 0 = no limit)
		$members = societypress()->members->get_members( array( 'status' => 'active', 'limit' => 0 ) );
		$current_member_id = $this->get_current_member_id();

		?>
		<div class="wrap societypress-admin societypress-volunteer-admin">
			<h1><?php echo esc_html( $page_title ); ?></h1>

			<?php $this->render_admin_notices(); ?>

			<form method="post" action="" class="societypress-opportunity-form">
				<?php wp_nonce_field( 'save_volunteer_opportunity', 'sp_opportunity_nonce' ); ?>
				<input type="hidden" name="societypress_action" value="save_volunteer_opportunity">
				<input type="hidden" name="opportunity_id" value="<?php echo esc_attr( $opportunity_id ); ?>">

				<table class="form-table">
					<tr>
						<th scope="row"><label for="title"><?php esc_html_e( 'Title', 'societypress' ); ?> <span class="required">*</span></label></th>
						<td>
							<input type="text" id="title" name="title" class="regular-text" required
							       value="<?php echo esc_attr( $opportunity['title'] ?? '' ); ?>">
							<p class="description"><?php esc_html_e( 'A clear, descriptive title for the volunteer position.', 'societypress' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="committee_id"><?php esc_html_e( 'Committee', 'societypress' ); ?></label></th>
						<td>
							<?php if ( count( $committees ) === 1 ) : ?>
								<input type="hidden" name="committee_id" value="<?php echo esc_attr( $committees[0]['id'] ); ?>">
								<strong><?php echo esc_html( $committees[0]['name'] ); ?></strong>
							<?php else : ?>
								<select id="committee_id" name="committee_id">
									<?php foreach ( $committees as $committee ) : ?>
										<option value="<?php echo esc_attr( $committee['id'] ); ?>"
											<?php selected( $opportunity['committee_id'] ?? '', $committee['id'] ); ?>>
											<?php echo esc_html( $committee['name'] ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							<?php endif; ?>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="description"><?php esc_html_e( 'Description', 'societypress' ); ?></label></th>
						<td>
							<?php
							wp_editor(
								$opportunity['description'] ?? '',
								'description',
								array(
									'textarea_name' => 'description',
									'textarea_rows' => 8,
									'media_buttons' => false,
									'teeny'         => true,
								)
							);
							?>
							<p class="description"><?php esc_html_e( 'Detailed description of the volunteer role, responsibilities, and any requirements.', 'societypress' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="location"><?php esc_html_e( 'Location', 'societypress' ); ?></label></th>
						<td>
							<input type="text" id="location" name="location" class="regular-text"
							       value="<?php echo esc_attr( $opportunity['location'] ?? '' ); ?>">
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="opportunity_type"><?php esc_html_e( 'Type', 'societypress' ); ?> <span class="required">*</span></label></th>
						<td>
							<select id="opportunity_type" name="opportunity_type" required>
								<option value="one_time" <?php selected( $opportunity['opportunity_type'] ?? 'one_time', 'one_time' ); ?>>
									<?php esc_html_e( 'One-time', 'societypress' ); ?>
								</option>
								<option value="recurring" <?php selected( $opportunity['opportunity_type'] ?? '', 'recurring' ); ?>>
									<?php esc_html_e( 'Recurring (weekly)', 'societypress' ); ?>
								</option>
								<option value="ongoing" <?php selected( $opportunity['opportunity_type'] ?? '', 'ongoing' ); ?>>
									<?php esc_html_e( 'Ongoing', 'societypress' ); ?>
								</option>
							</select>
						</td>
					</tr>

					<tr class="sp-schedule-row sp-schedule-one-time">
						<th scope="row"><label for="date"><?php esc_html_e( 'Date', 'societypress' ); ?></label></th>
						<td>
							<input type="date" id="date" name="date"
							       value="<?php echo esc_attr( $opportunity['date'] ?? '' ); ?>">
						</td>
					</tr>

					<tr class="sp-schedule-row sp-schedule-recurring">
						<th scope="row"><label for="day_of_week"><?php esc_html_e( 'Day of Week', 'societypress' ); ?></label></th>
						<td>
							<select id="day_of_week" name="day_of_week">
								<option value=""><?php esc_html_e( 'Select day...', 'societypress' ); ?></option>
								<option value="0" <?php selected( $opportunity['day_of_week'] ?? '', '0' ); ?>><?php esc_html_e( 'Sunday', 'societypress' ); ?></option>
								<option value="1" <?php selected( $opportunity['day_of_week'] ?? '', '1' ); ?>><?php esc_html_e( 'Monday', 'societypress' ); ?></option>
								<option value="2" <?php selected( $opportunity['day_of_week'] ?? '', '2' ); ?>><?php esc_html_e( 'Tuesday', 'societypress' ); ?></option>
								<option value="3" <?php selected( $opportunity['day_of_week'] ?? '', '3' ); ?>><?php esc_html_e( 'Wednesday', 'societypress' ); ?></option>
								<option value="4" <?php selected( $opportunity['day_of_week'] ?? '', '4' ); ?>><?php esc_html_e( 'Thursday', 'societypress' ); ?></option>
								<option value="5" <?php selected( $opportunity['day_of_week'] ?? '', '5' ); ?>><?php esc_html_e( 'Friday', 'societypress' ); ?></option>
								<option value="6" <?php selected( $opportunity['day_of_week'] ?? '', '6' ); ?>><?php esc_html_e( 'Saturday', 'societypress' ); ?></option>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="start_time"><?php esc_html_e( 'Time', 'societypress' ); ?></label></th>
						<td>
							<input type="time" id="start_time" name="start_time"
							       value="<?php echo esc_attr( $opportunity['start_time'] ?? '' ); ?>">
							<span class="sp-time-separator"><?php esc_html_e( 'to', 'societypress' ); ?></span>
							<input type="time" id="end_time" name="end_time"
							       value="<?php echo esc_attr( $opportunity['end_time'] ?? '' ); ?>">
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="capacity"><?php esc_html_e( 'Volunteers Needed', 'societypress' ); ?></label></th>
						<td>
							<input type="number" id="capacity" name="capacity" min="0" class="small-text"
							       value="<?php echo esc_attr( $opportunity['capacity'] ?? '' ); ?>">
							<p class="description"><?php esc_html_e( 'Leave blank for unlimited. When full, additional signups go to waitlist.', 'societypress' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="skills_needed"><?php esc_html_e( 'Skills / Requirements', 'societypress' ); ?></label></th>
						<td>
							<textarea id="skills_needed" name="skills_needed" rows="3" class="large-text"><?php echo esc_textarea( $opportunity['skills_needed'] ?? '' ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Any special skills or requirements for this position.', 'societypress' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="contact_member_id"><?php esc_html_e( 'Contact Person', 'societypress' ); ?></label></th>
						<td>
							<select id="contact_member_id" name="contact_member_id">
								<option value=""><?php esc_html_e( '— Default (Committee Chair) —', 'societypress' ); ?></option>
								<?php foreach ( $members as $member ) : ?>
									<option value="<?php echo esc_attr( $member->id ); ?>"
										<?php selected( $opportunity['contact_member_id'] ?? $current_member_id, $member->id ); ?>>
										<?php echo esc_html( $member->first_name . ' ' . $member->last_name ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'Who should members contact with questions?', 'societypress' ); ?></p>
						</td>
					</tr>

					<?php if ( ! $is_new ) : ?>
						<tr>
							<th scope="row"><label for="status"><?php esc_html_e( 'Status', 'societypress' ); ?></label></th>
							<td>
								<select id="status" name="status">
									<option value="open" <?php selected( $opportunity['status'], 'open' ); ?>><?php esc_html_e( 'Open', 'societypress' ); ?></option>
									<option value="filled" <?php selected( $opportunity['status'], 'filled' ); ?>><?php esc_html_e( 'Filled', 'societypress' ); ?></option>
									<option value="closed" <?php selected( $opportunity['status'], 'closed' ); ?>><?php esc_html_e( 'Closed', 'societypress' ); ?></option>
									<option value="cancelled" <?php selected( $opportunity['status'], 'cancelled' ); ?>><?php esc_html_e( 'Cancelled', 'societypress' ); ?></option>
								</select>
							</td>
						</tr>

						<tr>
							<th scope="row"><label for="is_active"><?php esc_html_e( 'Active', 'societypress' ); ?></label></th>
							<td>
								<label>
									<input type="checkbox" id="is_active" name="is_active" value="1"
										<?php checked( $opportunity['is_active'], 1 ); ?>>
									<?php esc_html_e( 'Show on public volunteer page', 'societypress' ); ?>
								</label>
							</td>
						</tr>
					<?php endif; ?>
				</table>

				<p class="submit">
					<input type="submit" name="submit" class="button button-primary" value="<?php echo $is_new ? esc_attr__( 'Create Opportunity', 'societypress' ) : esc_attr__( 'Update Opportunity', 'societypress' ); ?>">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-volunteer' ) ); ?>" class="button">
						<?php esc_html_e( 'Cancel', 'societypress' ); ?>
					</a>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the signups page for a specific opportunity.
	 */
	public function render_signups_page(): void {
		$opportunity_id = isset( $_GET['opportunity_id'] ) ? absint( $_GET['opportunity_id'] ) : 0;

		if ( ! $opportunity_id ) {
			wp_die( __( 'No opportunity specified.', 'societypress' ) );
		}

		$opportunity = societypress()->volunteer_opportunities->get( $opportunity_id );
		if ( ! $opportunity ) {
			wp_die( __( 'Opportunity not found.', 'societypress' ) );
		}

		// Check permission
		$committee_id = $opportunity['committee_id'] ? (int) $opportunity['committee_id'] : null;
		if ( ! $this->can_manage_opportunity( $committee_id ) ) {
			wp_die( __( 'You do not have permission to view signups for this opportunity.', 'societypress' ) );
		}

		$signups = societypress()->volunteer_signups->get_opportunity_signups( $opportunity_id );

		// Get all active members for admin add dropdown (limit 0 = no limit)
		$members = societypress()->members->get_members( array( 'status' => 'active', 'limit' => 0 ) );

		// Filter out members who already signed up
		$signed_up_member_ids = wp_list_pluck( $signups, 'member_id' );
		$available_members = array_filter( $members, function( $m ) use ( $signed_up_member_ids ) {
			return ! in_array( $m->id, $signed_up_member_ids, true );
		} );

		?>
		<div class="wrap societypress-admin societypress-volunteer-admin">
			<h1>
				<?php esc_html_e( 'Volunteer Signups', 'societypress' ); ?>:
				<?php echo esc_html( $opportunity['title'] ); ?>
			</h1>

			<p class="sp-opportunity-meta">
				<?php if ( $opportunity['committee_name'] ) : ?>
					<strong><?php esc_html_e( 'Committee:', 'societypress' ); ?></strong>
					<?php echo esc_html( $opportunity['committee_name'] ); ?> •
				<?php endif; ?>
				<strong><?php esc_html_e( 'Type:', 'societypress' ); ?></strong>
				<?php echo esc_html( societypress()->volunteer_opportunities->get_type_label( $opportunity['opportunity_type'] ) ); ?> •
				<strong><?php esc_html_e( 'Status:', 'societypress' ); ?></strong>
				<?php echo esc_html( societypress()->volunteer_opportunities->get_status_label( $opportunity['status'] ) ); ?>
				<?php if ( $opportunity['capacity'] ) : ?>
					•
					<strong><?php esc_html_e( 'Capacity:', 'societypress' ); ?></strong>
					<?php printf( '%d / %d', count( $signups ), $opportunity['capacity'] ); ?>
				<?php endif; ?>
			</p>

			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-volunteer' ) ); ?>" class="button">
					&larr; <?php esc_html_e( 'Back to Opportunities', 'societypress' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-volunteer-edit&opportunity_id=' . $opportunity_id ) ); ?>" class="button">
					<?php esc_html_e( 'Edit Opportunity', 'societypress' ); ?>
				</a>
			</p>

			<?php $this->render_admin_notices(); ?>

			<!-- Admin Add Member Form -->
			<?php if ( ! empty( $available_members ) && in_array( $opportunity['status'], array( 'open', 'filled' ), true ) ) : ?>
				<div class="sp-admin-add-signup">
					<h3><?php esc_html_e( 'Add Volunteer', 'societypress' ); ?></h3>
					<form method="post" action="">
						<?php wp_nonce_field( 'admin_add_signup', 'sp_add_signup_nonce' ); ?>
						<input type="hidden" name="societypress_action" value="admin_add_signup">
						<input type="hidden" name="opportunity_id" value="<?php echo esc_attr( $opportunity_id ); ?>">

						<select name="member_id" required>
							<option value=""><?php esc_html_e( 'Select member...', 'societypress' ); ?></option>
							<?php foreach ( $available_members as $member ) : ?>
								<option value="<?php echo esc_attr( $member->id ); ?>">
									<?php echo esc_html( $member->first_name . ' ' . $member->last_name ); ?>
								</option>
							<?php endforeach; ?>
						</select>

						<label>
							<input type="checkbox" name="bypass_capacity" value="1">
							<?php esc_html_e( 'Bypass capacity limit', 'societypress' ); ?>
						</label>

						<input type="submit" class="button" value="<?php esc_attr_e( 'Add Signup', 'societypress' ); ?>">
					</form>
				</div>
			<?php endif; ?>

			<!-- Signups Table -->
			<table class="widefat striped societypress-signups-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Member', 'societypress' ); ?></th>
						<th><?php esc_html_e( 'Status', 'societypress' ); ?></th>
						<th><?php esc_html_e( 'Signed Up', 'societypress' ); ?></th>
						<th><?php esc_html_e( 'Hours', 'societypress' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'societypress' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $signups ) ) : ?>
						<tr>
							<td colspan="5"><?php esc_html_e( 'No signups yet.', 'societypress' ); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ( $signups as $signup ) : ?>
							<?php $this->render_signup_row( $signup, $opportunity_id ); ?>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render a single signup row.
	 *
	 * @param array $signup         Signup data.
	 * @param int   $opportunity_id Opportunity ID.
	 */
	private function render_signup_row( array $signup, int $opportunity_id ): void {
		$cancel_url = wp_nonce_url(
			admin_url( 'admin.php?page=societypress-volunteer-signups&opportunity_id=' . $opportunity_id . '&action=cancel_signup&signup=' . $signup['id'] ),
			'cancel_signup_' . $signup['id']
		);
		$complete_url = wp_nonce_url(
			admin_url( 'admin.php?page=societypress-volunteer-signups&opportunity_id=' . $opportunity_id . '&action=complete_signup&signup=' . $signup['id'] ),
			'complete_signup_' . $signup['id']
		);
		$noshow_url = wp_nonce_url(
			admin_url( 'admin.php?page=societypress-volunteer-signups&opportunity_id=' . $opportunity_id . '&action=noshow_signup&signup=' . $signup['id'] ),
			'noshow_signup_' . $signup['id']
		);

		$status_labels = array(
			'confirmed' => __( 'Confirmed', 'societypress' ),
			'waitlist'  => __( 'Waitlist', 'societypress' ),
			'completed' => __( 'Completed', 'societypress' ),
			'cancelled' => __( 'Cancelled', 'societypress' ),
			'no_show'   => __( 'No Show', 'societypress' ),
		);

		?>
		<tr>
			<td>
				<strong><?php echo esc_html( $signup['first_name'] . ' ' . $signup['last_name'] ); ?></strong>
				<?php if ( ! empty( $signup['registered_by_name'] ) ) : ?>
					<br><small><?php printf( esc_html__( 'Added by %s', 'societypress' ), esc_html( $signup['registered_by_name'] ) ); ?></small>
				<?php endif; ?>
			</td>
			<td>
				<span class="societypress-status societypress-status-<?php echo esc_attr( $signup['status'] ); ?>">
					<?php echo esc_html( $status_labels[ $signup['status'] ] ?? $signup['status'] ); ?>
				</span>
				<?php if ( $signup['status'] === 'waitlist' ) : ?>
					<?php
					$position = societypress()->volunteer_signups->get_waitlist_position( $signup['id'] );
					?>
					<small>(#<?php echo esc_html( $position ); ?>)</small>
				<?php endif; ?>
			</td>
			<td>
				<?php echo esc_html( date_i18n( 'M j, Y g:i a', strtotime( $signup['signed_up_at'] ) ) ); ?>
			</td>
			<td>
				<?php if ( $signup['status'] === 'completed' ) : ?>
					<?php if ( $signup['hours_logged'] ) : ?>
						<?php echo esc_html( number_format( $signup['hours_logged'], 1 ) ); ?>
					<?php else : ?>
						<form method="post" action="" class="sp-inline-form">
							<?php wp_nonce_field( 'update_signup_hours', 'sp_hours_nonce' ); ?>
							<input type="hidden" name="societypress_action" value="update_signup_hours">
							<input type="hidden" name="signup_id" value="<?php echo esc_attr( $signup['id'] ); ?>">
							<input type="hidden" name="opportunity_id" value="<?php echo esc_attr( $opportunity_id ); ?>">
							<input type="number" name="hours" step="0.5" min="0" max="24" class="small-text" placeholder="0">
							<button type="submit" class="button button-small"><?php esc_html_e( 'Log', 'societypress' ); ?></button>
						</form>
					<?php endif; ?>
				<?php else : ?>
					—
				<?php endif; ?>
			</td>
			<td>
				<?php if ( $signup['status'] === 'confirmed' || $signup['status'] === 'waitlist' ) : ?>
					<a href="<?php echo esc_url( $complete_url ); ?>" class="button button-small"
					   onclick="return confirm('<?php echo esc_js( __( 'Mark as completed?', 'societypress' ) ); ?>');">
						<?php esc_html_e( 'Complete', 'societypress' ); ?>
					</a>
					<a href="<?php echo esc_url( $noshow_url ); ?>" class="button button-small"
					   onclick="return confirm('<?php echo esc_js( __( 'Mark as no-show?', 'societypress' ) ); ?>');">
						<?php esc_html_e( 'No Show', 'societypress' ); ?>
					</a>
					<a href="<?php echo esc_url( $cancel_url ); ?>" class="button button-small button-link-delete"
					   onclick="return confirm('<?php echo esc_js( __( 'Cancel this signup?', 'societypress' ) ); ?>');">
						<?php esc_html_e( 'Cancel', 'societypress' ); ?>
					</a>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save an opportunity (create or update).
	 */
	private function save_opportunity(): void {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['sp_opportunity_nonce'] ?? '', 'save_volunteer_opportunity' ) ) {
			wp_die( __( 'Security check failed.', 'societypress' ) );
		}

		$opportunity_id = absint( $_POST['opportunity_id'] ?? 0 );
		$committee_id = ! empty( $_POST['committee_id'] ) ? absint( $_POST['committee_id'] ) : null;

		// Check permission
		if ( ! $this->can_manage_opportunity( $committee_id ) ) {
			wp_die( __( 'You do not have permission to manage opportunities for this committee.', 'societypress' ) );
		}

		// If editing, also check permission for the existing opportunity
		if ( $opportunity_id ) {
			$existing = societypress()->volunteer_opportunities->get( $opportunity_id );
			if ( ! $existing ) {
				wp_die( __( 'Opportunity not found.', 'societypress' ) );
			}
			$existing_committee_id = $existing['committee_id'] ? (int) $existing['committee_id'] : null;
			if ( ! $this->can_manage_opportunity( $existing_committee_id ) ) {
				wp_die( __( 'You do not have permission to edit this opportunity.', 'societypress' ) );
			}
		}

		// Collect data
		$data = array(
			'committee_id'      => $committee_id,
			'title'             => sanitize_text_field( $_POST['title'] ?? '' ),
			'description'       => wp_kses_post( $_POST['description'] ?? '' ),
			'location'          => sanitize_text_field( $_POST['location'] ?? '' ),
			'opportunity_type'  => sanitize_text_field( $_POST['opportunity_type'] ?? 'one_time' ),
			'date'              => ! empty( $_POST['date'] ) ? sanitize_text_field( $_POST['date'] ) : null,
			'day_of_week'       => isset( $_POST['day_of_week'] ) && $_POST['day_of_week'] !== '' ? absint( $_POST['day_of_week'] ) : null,
			'start_time'        => ! empty( $_POST['start_time'] ) ? sanitize_text_field( $_POST['start_time'] ) : null,
			'end_time'          => ! empty( $_POST['end_time'] ) ? sanitize_text_field( $_POST['end_time'] ) : null,
			'capacity'          => ! empty( $_POST['capacity'] ) ? absint( $_POST['capacity'] ) : null,
			'skills_needed'     => sanitize_textarea_field( $_POST['skills_needed'] ?? '' ),
			'contact_member_id' => ! empty( $_POST['contact_member_id'] ) ? absint( $_POST['contact_member_id'] ) : null,
		);

		// Status and active flag (only on edit)
		if ( $opportunity_id ) {
			$data['status'] = sanitize_text_field( $_POST['status'] ?? 'open' );
			$data['is_active'] = isset( $_POST['is_active'] ) ? 1 : 0;
		} else {
			// New opportunity - set posted_by
			$data['posted_by'] = $this->get_current_member_id() ?: get_current_user_id();
		}

		// Validate required fields
		if ( empty( $data['title'] ) ) {
			$this->add_admin_notice( __( 'Title is required.', 'societypress' ), 'error' );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		// Create or update
		if ( $opportunity_id ) {
			$result = societypress()->volunteer_opportunities->update( $opportunity_id, $data );
			$message = 'opportunity_updated';
		} else {
			$result = societypress()->volunteer_opportunities->create( $data );
			$opportunity_id = $result;
			$message = 'opportunity_created';
		}

		if ( ! $result ) {
			$this->add_admin_notice( __( 'Failed to save opportunity.', 'societypress' ), 'error' );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		// Success - redirect to list
		$this->add_admin_notice(
			$message === 'opportunity_created'
				? __( 'Opportunity created successfully.', 'societypress' )
				: __( 'Opportunity updated successfully.', 'societypress' ),
			'success'
		);
		wp_safe_redirect( admin_url( 'admin.php?page=societypress-volunteer&message=' . $message ) );
		exit;
	}

	/**
	 * Delete an opportunity.
	 */
	private function delete_opportunity(): void {
		$opportunity_id = absint( $_GET['opportunity'] ?? 0 );

		if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'delete_opportunity_' . $opportunity_id ) ) {
			wp_die( __( 'Security check failed.', 'societypress' ) );
		}

		$opportunity = societypress()->volunteer_opportunities->get( $opportunity_id );
		if ( ! $opportunity ) {
			wp_die( __( 'Opportunity not found.', 'societypress' ) );
		}

		$committee_id = $opportunity['committee_id'] ? (int) $opportunity['committee_id'] : null;
		if ( ! $this->can_manage_opportunity( $committee_id ) ) {
			wp_die( __( 'You do not have permission to delete this opportunity.', 'societypress' ) );
		}

		// Check for existing signups
		$signup_count = societypress()->volunteer_opportunities->get_signup_count( $opportunity_id );
		if ( $signup_count > 0 ) {
			$this->add_admin_notice( __( 'Cannot delete opportunity with existing signups. Close it instead.', 'societypress' ), 'error' );
			wp_safe_redirect( admin_url( 'admin.php?page=societypress-volunteer' ) );
			exit;
		}

		$result = societypress()->volunteer_opportunities->delete( $opportunity_id, false ); // Hard delete

		if ( $result ) {
			$this->add_admin_notice( __( 'Opportunity deleted.', 'societypress' ), 'success' );
		} else {
			$this->add_admin_notice( __( 'Failed to delete opportunity.', 'societypress' ), 'error' );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=societypress-volunteer' ) );
		exit;
	}

	/**
	 * Close an opportunity.
	 */
	private function close_opportunity(): void {
		$opportunity_id = absint( $_GET['opportunity'] ?? 0 );

		if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'close_opportunity_' . $opportunity_id ) ) {
			wp_die( __( 'Security check failed.', 'societypress' ) );
		}

		$opportunity = societypress()->volunteer_opportunities->get( $opportunity_id );
		if ( ! $opportunity ) {
			wp_die( __( 'Opportunity not found.', 'societypress' ) );
		}

		$committee_id = $opportunity['committee_id'] ? (int) $opportunity['committee_id'] : null;
		if ( ! $this->can_manage_opportunity( $committee_id ) ) {
			wp_die( __( 'You do not have permission to close this opportunity.', 'societypress' ) );
		}

		$result = societypress()->volunteer_opportunities->update( $opportunity_id, array( 'status' => 'closed' ) );

		if ( $result ) {
			$this->add_admin_notice( __( 'Opportunity closed.', 'societypress' ), 'success' );
		} else {
			$this->add_admin_notice( __( 'Failed to close opportunity.', 'societypress' ), 'error' );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=societypress-volunteer' ) );
		exit;
	}

	/**
	 * Reopen a closed opportunity.
	 */
	private function reopen_opportunity(): void {
		$opportunity_id = absint( $_GET['opportunity'] ?? 0 );

		if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'reopen_opportunity_' . $opportunity_id ) ) {
			wp_die( __( 'Security check failed.', 'societypress' ) );
		}

		$opportunity = societypress()->volunteer_opportunities->get( $opportunity_id );
		if ( ! $opportunity ) {
			wp_die( __( 'Opportunity not found.', 'societypress' ) );
		}

		$committee_id = $opportunity['committee_id'] ? (int) $opportunity['committee_id'] : null;
		if ( ! $this->can_manage_opportunity( $committee_id ) ) {
			wp_die( __( 'You do not have permission to reopen this opportunity.', 'societypress' ) );
		}

		$result = societypress()->volunteer_opportunities->update( $opportunity_id, array( 'status' => 'open' ) );

		if ( $result ) {
			$this->add_admin_notice( __( 'Opportunity reopened.', 'societypress' ), 'success' );
		} else {
			$this->add_admin_notice( __( 'Failed to reopen opportunity.', 'societypress' ), 'error' );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=societypress-volunteer' ) );
		exit;
	}

	/**
	 * Handle signup actions (cancel, complete, no-show).
	 *
	 * @param string $action Action to perform.
	 */
	private function handle_signup_action( string $action ): void {
		$signup_id = absint( $_GET['signup'] ?? 0 );
		$opportunity_id = absint( $_GET['opportunity_id'] ?? 0 );

		if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', $action . '_' . $signup_id ) ) {
			wp_die( __( 'Security check failed.', 'societypress' ) );
		}

		// Get opportunity to check permission
		$opportunity = societypress()->volunteer_opportunities->get( $opportunity_id );
		if ( ! $opportunity ) {
			wp_die( __( 'Opportunity not found.', 'societypress' ) );
		}

		$committee_id = $opportunity['committee_id'] ? (int) $opportunity['committee_id'] : null;
		if ( ! $this->can_manage_opportunity( $committee_id ) ) {
			wp_die( __( 'You do not have permission to manage signups for this opportunity.', 'societypress' ) );
		}

		$signups = societypress()->volunteer_signups;

		switch ( $action ) {
			case 'cancel_signup':
				$result = $signups->cancel( $signup_id, 'admin' );
				$message = $result ? __( 'Signup cancelled.', 'societypress' ) : __( 'Failed to cancel signup.', 'societypress' );
				break;

			case 'complete_signup':
				$result = $signups->complete( $signup_id );
				$message = $result ? __( 'Signup marked as completed.', 'societypress' ) : __( 'Failed to complete signup.', 'societypress' );
				break;

			case 'noshow_signup':
				$result = $signups->mark_no_show( $signup_id );
				$message = $result ? __( 'Signup marked as no-show.', 'societypress' ) : __( 'Failed to mark as no-show.', 'societypress' );
				break;

			default:
				$result = false;
				$message = __( 'Unknown action.', 'societypress' );
		}

		$this->add_admin_notice( $message, $result ? 'success' : 'error' );
		wp_safe_redirect( admin_url( 'admin.php?page=societypress-volunteer-signups&opportunity_id=' . $opportunity_id ) );
		exit;
	}

	/**
	 * Admin add a member signup.
	 */
	private function admin_add_signup(): void {
		if ( ! wp_verify_nonce( $_POST['sp_add_signup_nonce'] ?? '', 'admin_add_signup' ) ) {
			wp_die( __( 'Security check failed.', 'societypress' ) );
		}

		$opportunity_id = absint( $_POST['opportunity_id'] ?? 0 );
		$member_id = absint( $_POST['member_id'] ?? 0 );
		$bypass_capacity = isset( $_POST['bypass_capacity'] ) && $_POST['bypass_capacity'] === '1';

		if ( ! $opportunity_id || ! $member_id ) {
			$this->add_admin_notice( __( 'Invalid opportunity or member.', 'societypress' ), 'error' );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		// Get opportunity to check permission
		$opportunity = societypress()->volunteer_opportunities->get( $opportunity_id );
		if ( ! $opportunity ) {
			wp_die( __( 'Opportunity not found.', 'societypress' ) );
		}

		$committee_id = $opportunity['committee_id'] ? (int) $opportunity['committee_id'] : null;
		if ( ! $this->can_manage_opportunity( $committee_id ) ) {
			wp_die( __( 'You do not have permission to add signups to this opportunity.', 'societypress' ) );
		}

		// Get current admin member ID for registered_by
		$registered_by = $this->get_current_member_id();

		$result = societypress()->volunteer_signups->signup(
			$opportunity_id,
			$member_id,
			$registered_by,
			$bypass_capacity
		);

		if ( is_wp_error( $result ) ) {
			$this->add_admin_notice( $result->get_error_message(), 'error' );
		} else {
			$this->add_admin_notice( __( 'Member signed up successfully.', 'societypress' ), 'success' );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=societypress-volunteer-signups&opportunity_id=' . $opportunity_id ) );
		exit;
	}

	/**
	 * Update signup hours.
	 */
	private function update_signup_hours(): void {
		if ( ! wp_verify_nonce( $_POST['sp_hours_nonce'] ?? '', 'update_signup_hours' ) ) {
			wp_die( __( 'Security check failed.', 'societypress' ) );
		}

		$signup_id = absint( $_POST['signup_id'] ?? 0 );
		$opportunity_id = absint( $_POST['opportunity_id'] ?? 0 );
		$hours = floatval( $_POST['hours'] ?? 0 );

		if ( ! $signup_id || ! $opportunity_id ) {
			$this->add_admin_notice( __( 'Invalid signup.', 'societypress' ), 'error' );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		// Get opportunity to check permission
		$opportunity = societypress()->volunteer_opportunities->get( $opportunity_id );
		if ( ! $opportunity ) {
			wp_die( __( 'Opportunity not found.', 'societypress' ) );
		}

		$committee_id = $opportunity['committee_id'] ? (int) $opportunity['committee_id'] : null;
		if ( ! $this->can_manage_opportunity( $committee_id ) ) {
			wp_die( __( 'You do not have permission to update hours for this opportunity.', 'societypress' ) );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'sp_volunteer_signups';

		$result = $wpdb->update(
			$table,
			array( 'hours_logged' => $hours ),
			array( 'id' => $signup_id ),
			array( '%f' ),
			array( '%d' )
		);

		if ( false !== $result ) {
			$this->add_admin_notice( __( 'Hours logged successfully.', 'societypress' ), 'success' );
		} else {
			$this->add_admin_notice( __( 'Failed to log hours.', 'societypress' ), 'error' );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=societypress-volunteer-signups&opportunity_id=' . $opportunity_id ) );
		exit;
	}

	/**
	 * Add an admin notice to be displayed.
	 *
	 * @param string $message Notice message.
	 * @param string $type    Notice type (success, error, warning, info).
	 */
	private function add_admin_notice( string $message, string $type = 'info' ): void {
		$notices = get_transient( 'societypress_volunteer_admin_notices' ) ?: array();
		$notices[] = array(
			'message' => $message,
			'type'    => $type,
		);
		set_transient( 'societypress_volunteer_admin_notices', $notices, 60 );
	}

	/**
	 * Render admin notices.
	 */
	private function render_admin_notices(): void {
		$notices = get_transient( 'societypress_volunteer_admin_notices' );
		if ( ! $notices ) {
			return;
		}

		delete_transient( 'societypress_volunteer_admin_notices' );

		foreach ( $notices as $notice ) {
			$type = in_array( $notice['type'], array( 'success', 'error', 'warning', 'info' ), true ) ? $notice['type'] : 'info';
			printf(
				'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
				esc_attr( $type ),
				esc_html( $notice['message'] )
			);
		}
	}
}
