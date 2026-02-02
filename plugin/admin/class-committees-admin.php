<?php
/**
 * Committees Admin Interface
 *
 * Admin pages for managing committees and their members.
 *
 * WHY: Societies organize work through committees (Library, Programs, etc.).
 *      This provides an interface to create committees and assign members
 *      with roles (Chair, Co-Chair, Member).
 *
 * @package SocietyPress
 * @since 0.55d
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SocietyPress_Committees_Admin
 *
 * Admin interface for committee management.
 */
class SocietyPress_Committees_Admin {

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
		add_action( 'admin_menu', array( $this, 'register_pages' ), 20 );
		add_action( 'admin_init', array( $this, 'handle_actions' ) );
	}

	/**
	 * Register admin pages.
	 */
	public function register_pages(): void {
		// Hidden pages for editing
		add_submenu_page(
			'',
			__( 'Edit Committee', 'societypress' ),
			__( 'Edit Committee', 'societypress' ),
			'manage_society_members',
			'societypress-committee-edit',
			array( $this, 'render_committee_edit' )
		);

		add_submenu_page(
			'',
			__( 'Committee Members', 'societypress' ),
			__( 'Committee Members', 'societypress' ),
			'manage_society_members',
			'societypress-committee-members',
			array( $this, 'render_committee_members' )
		);

		add_submenu_page(
			'',
			__( 'Add Committee Member', 'societypress' ),
			__( 'Add Committee Member', 'societypress' ),
			'manage_society_members',
			'societypress-committee-add-member',
			array( $this, 'render_add_member' )
		);
	}

	/**
	 * Handle form submissions and actions.
	 */
	public function handle_actions(): void {
		// Only process on our pages
		$page = $_GET['page'] ?? '';
		if ( strpos( $page, 'societypress-committee' ) === false ) {
			return;
		}

		// Save committee
		if ( isset( $_POST['societypress_action'] ) && 'save_committee' === $_POST['societypress_action'] ) {
			$this->save_committee();
		}

		// Delete committee
		if ( isset( $_GET['action'] ) && 'delete_committee' === $_GET['action'] && isset( $_GET['committee'] ) ) {
			$this->delete_committee();
		}

		// Add member
		if ( isset( $_POST['societypress_action'] ) && 'add_committee_member' === $_POST['societypress_action'] ) {
			$this->add_member();
		}

		// Remove member
		if ( isset( $_GET['action'] ) && 'remove_member' === $_GET['action'] && isset( $_GET['member'] ) ) {
			$this->remove_member();
		}

		// Update member role
		if ( isset( $_POST['societypress_action'] ) && 'update_member_role' === $_POST['societypress_action'] ) {
			$this->update_member_role();
		}
	}

	/**
	 * Render the main committees page.
	 */
	public function render_committees_page(): void {
		if ( ! current_user_can( 'manage_society_members' ) ) {
			wp_die( __( 'You do not have permission to access this page.', 'societypress' ) );
		}

		$committees = societypress()->committees->get_all( false ); // Include inactive

		?>
		<div class="wrap societypress-admin societypress-committees-admin">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Committees', 'societypress' ); ?></h1>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-committee-edit' ) ); ?>" class="page-title-action">
				<?php esc_html_e( 'Add Committee', 'societypress' ); ?>
			</a>
			<hr class="wp-header-end">

			<?php $this->render_admin_notices(); ?>

			<?php if ( empty( $committees ) ) : ?>
				<div class="notice notice-info">
					<p><?php esc_html_e( 'No committees defined yet. Add your first committee to get started.', 'societypress' ); ?></p>
				</div>
			<?php else : ?>
				<table class="widefat striped societypress-committees-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Committee', 'societypress' ); ?></th>
							<th><?php esc_html_e( 'Chair(s)', 'societypress' ); ?></th>
							<th><?php esc_html_e( 'Members', 'societypress' ); ?></th>
							<th><?php esc_html_e( 'Type', 'societypress' ); ?></th>
							<th><?php esc_html_e( 'Status', 'societypress' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $committees as $committee ) : ?>
							<?php $this->render_committee_row( $committee ); ?>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render a single committee row.
	 *
	 * @param array $committee Committee data.
	 */
	private function render_committee_row( array $committee ): void {
		$edit_url    = admin_url( 'admin.php?page=societypress-committee-edit&committee_id=' . $committee['id'] );
		$members_url = admin_url( 'admin.php?page=societypress-committee-members&committee_id=' . $committee['id'] );
		$delete_url  = wp_nonce_url(
			admin_url( 'admin.php?page=societypress-committees&action=delete_committee&committee=' . $committee['id'] ),
			'delete_committee_' . $committee['id']
		);

		$chairs       = societypress()->committees->get_chairs( $committee['id'] );
		$member_count = societypress()->committees->get_member_count( $committee['id'] );

		$chair_names = array();
		foreach ( $chairs as $chair ) {
			$chair_names[] = $chair['first_name'] . ' ' . $chair['last_name'];
		}

		?>
		<tr class="<?php echo $committee['is_active'] ? '' : 'sp-inactive'; ?>">
			<td>
				<strong><a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $committee['name'] ); ?></a></strong>
				<br><code><?php echo esc_html( $committee['slug'] ); ?></code>
				<div class="row-actions">
					<span class="edit">
						<a href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit', 'societypress' ); ?></a> |
					</span>
					<span class="members">
						<a href="<?php echo esc_url( $members_url ); ?>"><?php esc_html_e( 'Manage Members', 'societypress' ); ?></a>
						<?php if ( $member_count === 0 ) : ?>
							|
						<?php endif; ?>
					</span>
					<?php if ( $member_count === 0 ) : ?>
						<span class="delete">
							<a href="<?php echo esc_url( $delete_url ); ?>" class="submitdelete"
							   onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this committee?', 'societypress' ) ); ?>');">
								<?php esc_html_e( 'Delete', 'societypress' ); ?>
							</a>
						</span>
					<?php endif; ?>
				</div>
			</td>
			<td>
				<?php if ( ! empty( $chair_names ) ) : ?>
					<?php echo esc_html( implode( ', ', $chair_names ) ); ?>
				<?php else : ?>
					<em class="sp-vacant"><?php esc_html_e( 'No chair assigned', 'societypress' ); ?></em>
				<?php endif; ?>
			</td>
			<td>
				<a href="<?php echo esc_url( $members_url ); ?>">
					<?php echo esc_html( $member_count ); ?>
				</a>
			</td>
			<td>
				<?php echo $committee['is_standing'] ? esc_html__( 'Standing', 'societypress' ) : esc_html__( 'Ad Hoc', 'societypress' ); ?>
			</td>
			<td>
				<?php if ( $committee['is_active'] ) : ?>
					<span class="sp-status-active"><?php esc_html_e( 'Active', 'societypress' ); ?></span>
				<?php else : ?>
					<span class="sp-status-inactive"><?php esc_html_e( 'Inactive', 'societypress' ); ?></span>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render the committee add/edit page.
	 */
	public function render_committee_edit(): void {
		if ( ! current_user_can( 'manage_society_members' ) ) {
			wp_die( __( 'You do not have permission to access this page.', 'societypress' ) );
		}

		$committee_id = isset( $_GET['committee_id'] ) ? absint( $_GET['committee_id'] ) : 0;
		$committee    = null;
		$is_new       = true;

		if ( $committee_id ) {
			$committee = societypress()->committees->get( $committee_id );
			if ( ! $committee ) {
				wp_die( __( 'Committee not found.', 'societypress' ) );
			}
			$is_new = false;
		}

		$page_title = $is_new ? __( 'Add Committee', 'societypress' ) : __( 'Edit Committee', 'societypress' );

		// Get next sort order for new committees
		$next_sort_order = 0;
		if ( $is_new ) {
			$all_committees = societypress()->committees->get_all( false );
			if ( ! empty( $all_committees ) ) {
				$next_sort_order = max( array_column( $all_committees, 'sort_order' ) ) + 10;
			}
		}

		?>
		<div class="wrap societypress-admin societypress-committees-admin">
			<h1><?php echo esc_html( $page_title ); ?></h1>

			<?php $this->render_admin_notices(); ?>

			<form method="post" action="" class="societypress-committee-form">
				<?php wp_nonce_field( 'save_committee', 'sp_committee_nonce' ); ?>
				<input type="hidden" name="societypress_action" value="save_committee">
				<input type="hidden" name="committee_id" value="<?php echo esc_attr( $committee_id ); ?>">

				<table class="form-table">
					<tr>
						<th scope="row"><label for="name"><?php esc_html_e( 'Name', 'societypress' ); ?> <span class="required">*</span></label></th>
						<td>
							<input type="text" id="name" name="name" class="regular-text" required
							       value="<?php echo esc_attr( $committee['name'] ?? '' ); ?>">
							<p class="description"><?php esc_html_e( 'e.g., Library Committee, Membership Committee', 'societypress' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="slug"><?php esc_html_e( 'Slug', 'societypress' ); ?></label></th>
						<td>
							<input type="text" id="slug" name="slug" class="regular-text"
							       value="<?php echo esc_attr( $committee['slug'] ?? '' ); ?>">
							<p class="description"><?php esc_html_e( 'URL-friendly identifier. Leave blank to auto-generate.', 'societypress' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="description"><?php esc_html_e( 'Description', 'societypress' ); ?></label></th>
						<td>
							<textarea id="description" name="description" rows="4" class="large-text"><?php echo esc_textarea( $committee['description'] ?? '' ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Brief description of this committee\'s purpose and responsibilities.', 'societypress' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Committee Type', 'societypress' ); ?></th>
						<td>
							<fieldset>
								<label>
									<input type="radio" name="is_standing" value="1"
										<?php checked( $committee['is_standing'] ?? 1, 1 ); ?>>
									<?php esc_html_e( 'Standing Committee', 'societypress' ); ?>
								</label>
								<p class="description"><?php esc_html_e( 'Permanent committees that operate year-round.', 'societypress' ); ?></p>
								<br>
								<label>
									<input type="radio" name="is_standing" value="0"
										<?php checked( $committee['is_standing'] ?? 1, 0 ); ?>>
									<?php esc_html_e( 'Ad Hoc Committee', 'societypress' ); ?>
								</label>
								<p class="description"><?php esc_html_e( 'Temporary committees formed for a specific purpose.', 'societypress' ); ?></p>
							</fieldset>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="sort_order"><?php esc_html_e( 'Sort Order', 'societypress' ); ?></label></th>
						<td>
							<input type="number" id="sort_order" name="sort_order" class="small-text" min="0"
							       value="<?php echo esc_attr( $committee['sort_order'] ?? $next_sort_order ); ?>">
							<p class="description"><?php esc_html_e( 'Lower numbers display first.', 'societypress' ); ?></p>
						</td>
					</tr>

					<?php if ( ! $is_new ) : ?>
						<tr>
							<th scope="row"><?php esc_html_e( 'Status', 'societypress' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="is_active" value="1"
										<?php checked( $committee['is_active'] ?? 1, 1 ); ?>>
									<?php esc_html_e( 'Active', 'societypress' ); ?>
								</label>
								<p class="description"><?php esc_html_e( 'Inactive committees are hidden from public pages.', 'societypress' ); ?></p>
							</td>
						</tr>
					<?php endif; ?>
				</table>

				<p class="submit">
					<input type="submit" name="submit" class="button button-primary"
					       value="<?php echo $is_new ? esc_attr__( 'Add Committee', 'societypress' ) : esc_attr__( 'Update Committee', 'societypress' ); ?>">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-committees' ) ); ?>" class="button">
						<?php esc_html_e( 'Cancel', 'societypress' ); ?>
					</a>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the committee members page.
	 */
	public function render_committee_members(): void {
		if ( ! current_user_can( 'manage_society_members' ) ) {
			wp_die( __( 'You do not have permission to access this page.', 'societypress' ) );
		}

		$committee_id = isset( $_GET['committee_id'] ) ? absint( $_GET['committee_id'] ) : 0;

		if ( ! $committee_id ) {
			wp_die( __( 'No committee specified.', 'societypress' ) );
		}

		$committee = societypress()->committees->get( $committee_id );
		if ( ! $committee ) {
			wp_die( __( 'Committee not found.', 'societypress' ) );
		}

		$members = societypress()->committees->get_members( $committee_id, false ); // Include past members

		?>
		<div class="wrap societypress-admin societypress-committees-admin">
			<h1>
				<?php
				printf(
					/* translators: %s: committee name */
					esc_html__( '%s - Members', 'societypress' ),
					esc_html( $committee['name'] )
				);
				?>
			</h1>

			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-committees' ) ); ?>" class="button">
					&larr; <?php esc_html_e( 'Back to Committees', 'societypress' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-committee-add-member&committee_id=' . $committee_id ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Add Member', 'societypress' ); ?>
				</a>
			</p>

			<?php $this->render_admin_notices(); ?>

			<?php if ( empty( $members ) ) : ?>
				<div class="notice notice-info">
					<p><?php esc_html_e( 'No members in this committee yet.', 'societypress' ); ?></p>
				</div>
			<?php else : ?>
				<table class="widefat striped societypress-committee-members-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Member', 'societypress' ); ?></th>
							<th><?php esc_html_e( 'Role', 'societypress' ); ?></th>
							<th><?php esc_html_e( 'Joined', 'societypress' ); ?></th>
							<th><?php esc_html_e( 'Status', 'societypress' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'societypress' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $members as $member ) : ?>
							<?php $this->render_member_row( $member, $committee_id ); ?>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render a single committee member row.
	 *
	 * @param array $member       Member data.
	 * @param int   $committee_id Committee ID.
	 */
	private function render_member_row( array $member, int $committee_id ): void {
		$remove_url = wp_nonce_url(
			admin_url( 'admin.php?page=societypress-committee-members&committee_id=' . $committee_id . '&action=remove_member&member=' . $member['member_id'] ),
			'remove_member_' . $member['member_id']
		);

		$role_labels = array(
			'chair'    => __( 'Chair', 'societypress' ),
			'co_chair' => __( 'Co-Chair', 'societypress' ),
			'member'   => __( 'Member', 'societypress' ),
		);

		$is_current = empty( $member['left_date'] );

		?>
		<tr class="<?php echo $is_current ? 'sp-current-member' : 'sp-past-member'; ?>">
			<td>
				<strong><?php echo esc_html( $member['first_name'] . ' ' . $member['last_name'] ); ?></strong>
			</td>
			<td>
				<?php if ( $is_current ) : ?>
					<form method="post" action="" class="sp-inline-form">
						<?php wp_nonce_field( 'update_member_role', 'sp_role_nonce' ); ?>
						<input type="hidden" name="societypress_action" value="update_member_role">
						<input type="hidden" name="committee_id" value="<?php echo esc_attr( $committee_id ); ?>">
						<input type="hidden" name="member_id" value="<?php echo esc_attr( $member['member_id'] ); ?>">
						<select name="role" onchange="this.form.submit()">
							<?php foreach ( $role_labels as $role_value => $role_label ) : ?>
								<option value="<?php echo esc_attr( $role_value ); ?>"
									<?php selected( $member['role'], $role_value ); ?>>
									<?php echo esc_html( $role_label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</form>
				<?php else : ?>
					<?php echo esc_html( $role_labels[ $member['role'] ] ?? $member['role'] ); ?>
				<?php endif; ?>
			</td>
			<td>
				<?php echo esc_html( date_i18n( 'M j, Y', strtotime( $member['joined_date'] ) ) ); ?>
			</td>
			<td>
				<?php if ( $is_current ) : ?>
					<span class="sp-status-active"><?php esc_html_e( 'Current', 'societypress' ); ?></span>
				<?php else : ?>
					<span class="sp-status-inactive">
						<?php
						printf(
							/* translators: %s: date left */
							esc_html__( 'Left %s', 'societypress' ),
							date_i18n( 'M j, Y', strtotime( $member['left_date'] ) )
						);
						?>
					</span>
				<?php endif; ?>
			</td>
			<td>
				<?php if ( $is_current ) : ?>
					<a href="<?php echo esc_url( $remove_url ); ?>" class="button button-small"
					   onclick="return confirm('<?php echo esc_js( __( 'Remove this member from the committee?', 'societypress' ) ); ?>');">
						<?php esc_html_e( 'Remove', 'societypress' ); ?>
					</a>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render the add member page.
	 */
	public function render_add_member(): void {
		if ( ! current_user_can( 'manage_society_members' ) ) {
			wp_die( __( 'You do not have permission to access this page.', 'societypress' ) );
		}

		$committee_id = isset( $_GET['committee_id'] ) ? absint( $_GET['committee_id'] ) : 0;

		if ( ! $committee_id ) {
			wp_die( __( 'No committee specified.', 'societypress' ) );
		}

		$committee = societypress()->committees->get( $committee_id );
		if ( ! $committee ) {
			wp_die( __( 'Committee not found.', 'societypress' ) );
		}

		// Get current committee members to exclude from search
		$committee_members  = societypress()->committees->get_members( $committee_id );
		$current_member_ids = array_column( $committee_members, 'member_id' );

		?>
		<div class="wrap societypress-admin societypress-committees-admin">
			<h1>
				<?php
				printf(
					/* translators: %s: committee name */
					esc_html__( 'Add Member to %s', 'societypress' ),
					esc_html( $committee['name'] )
				);
				?>
			</h1>

			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-committee-members&committee_id=' . $committee_id ) ); ?>" class="button">
					&larr; <?php esc_html_e( 'Back to Committee Members', 'societypress' ); ?>
				</a>
			</p>

			<?php $this->render_admin_notices(); ?>

			<form method="post" action="" class="societypress-add-member-form">
				<?php wp_nonce_field( 'add_committee_member', 'sp_add_member_nonce' ); ?>
				<input type="hidden" name="societypress_action" value="add_committee_member">
				<input type="hidden" name="committee_id" value="<?php echo esc_attr( $committee_id ); ?>">

				<table class="form-table">
					<tr>
						<th scope="row"><label for="member_search"><?php esc_html_e( 'Member', 'societypress' ); ?> <span class="required">*</span></label></th>
						<td>
							<div class="sp-member-search-wrapper">
								<input type="text"
								       id="member_search"
								       class="sp-member-search"
								       placeholder="<?php esc_attr_e( 'Type a name to search...', 'societypress' ); ?>"
								       autocomplete="off"
								       data-hidden-input="member_id"
								       data-results-container="member_search_results"
								       data-min-length="2"
								       data-exclude-ids="<?php echo esc_attr( wp_json_encode( array_map( 'intval', $current_member_ids ) ) ); ?>">
								<input type="hidden" id="member_id" name="member_id" required>
								<div id="member_search_results" class="sp-member-search-results"></div>
							</div>
							<span class="sp-search-hint"><?php esc_html_e( 'Start typing a first or last name to find members.', 'societypress' ); ?></span>
						</td>
					</tr>

						<tr>
							<th scope="row"><label for="role"><?php esc_html_e( 'Role', 'societypress' ); ?></label></th>
							<td>
								<select id="role" name="role">
									<option value="member"><?php esc_html_e( 'Member', 'societypress' ); ?></option>
									<option value="co_chair"><?php esc_html_e( 'Co-Chair', 'societypress' ); ?></option>
									<option value="chair"><?php esc_html_e( 'Chair', 'societypress' ); ?></option>
								</select>
							</td>
						</tr>

						<tr>
							<th scope="row"><label for="joined_date"><?php esc_html_e( 'Joined Date', 'societypress' ); ?></label></th>
							<td>
								<input type="date" id="joined_date" name="joined_date"
								       value="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>">
							</td>
						</tr>

						<tr>
							<th scope="row"><label for="notes"><?php esc_html_e( 'Notes', 'societypress' ); ?></label></th>
							<td>
								<textarea id="notes" name="notes" rows="3" class="large-text"></textarea>
							</td>
						</tr>
					</table>

					<p class="submit">
						<input type="submit" name="submit" class="button button-primary"
						       value="<?php esc_attr_e( 'Add to Committee', 'societypress' ); ?>">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-committee-members&committee_id=' . $committee_id ) ); ?>" class="button">
							<?php esc_html_e( 'Cancel', 'societypress' ); ?>
						</a>
					</p>
				</form>
		</div>
		<?php
	}

	/**
	 * Save a committee.
	 */
	private function save_committee(): void {
		if ( ! wp_verify_nonce( $_POST['sp_committee_nonce'] ?? '', 'save_committee' ) ) {
			wp_die( __( 'Security check failed.', 'societypress' ) );
		}

		if ( ! current_user_can( 'manage_society_members' ) ) {
			wp_die( __( 'You do not have permission to manage committees.', 'societypress' ) );
		}

		$committee_id = absint( $_POST['committee_id'] ?? 0 );

		$data = array(
			'name'        => sanitize_text_field( $_POST['name'] ?? '' ),
			'slug'        => sanitize_title( $_POST['slug'] ?? '' ),
			'description' => sanitize_textarea_field( $_POST['description'] ?? '' ),
			'is_standing' => absint( $_POST['is_standing'] ?? 1 ),
			'sort_order'  => absint( $_POST['sort_order'] ?? 0 ),
		);

		// Only include is_active on edit
		if ( $committee_id ) {
			$data['is_active'] = isset( $_POST['is_active'] ) ? 1 : 0;
		}

		if ( empty( $data['name'] ) ) {
			$this->add_admin_notice( __( 'Name is required.', 'societypress' ), 'error' );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		$committees = societypress()->committees;

		if ( $committee_id ) {
			$result  = $committees->update( $committee_id, $data );
			$message = $result ? __( 'Committee updated.', 'societypress' ) : __( 'Failed to update committee.', 'societypress' );
		} else {
			$result  = $committees->create( $data );
			$message = $result ? __( 'Committee created.', 'societypress' ) : __( 'Failed to create committee.', 'societypress' );
		}

		$this->add_admin_notice( $message, $result ? 'success' : 'error' );
		wp_safe_redirect( admin_url( 'admin.php?page=societypress-committees' ) );
		exit;
	}

	/**
	 * Delete a committee.
	 */
	private function delete_committee(): void {
		$committee_id = absint( $_GET['committee'] ?? 0 );

		if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'delete_committee_' . $committee_id ) ) {
			wp_die( __( 'Security check failed.', 'societypress' ) );
		}

		if ( ! current_user_can( 'manage_society_members' ) ) {
			wp_die( __( 'You do not have permission to delete committees.', 'societypress' ) );
		}

		// Check for members
		$member_count = societypress()->committees->get_member_count( $committee_id );
		if ( $member_count > 0 ) {
			$this->add_admin_notice( __( 'Cannot delete committee with members. Remove all members first.', 'societypress' ), 'error' );
			wp_safe_redirect( admin_url( 'admin.php?page=societypress-committees' ) );
			exit;
		}

		$result = societypress()->committees->delete( $committee_id, true ); // Hard delete

		$this->add_admin_notice(
			$result ? __( 'Committee deleted.', 'societypress' ) : __( 'Failed to delete committee.', 'societypress' ),
			$result ? 'success' : 'error'
		);

		wp_safe_redirect( admin_url( 'admin.php?page=societypress-committees' ) );
		exit;
	}

	/**
	 * Add a member to a committee.
	 */
	private function add_member(): void {
		if ( ! wp_verify_nonce( $_POST['sp_add_member_nonce'] ?? '', 'add_committee_member' ) ) {
			wp_die( __( 'Security check failed.', 'societypress' ) );
		}

		if ( ! current_user_can( 'manage_society_members' ) ) {
			wp_die( __( 'You do not have permission to manage committee members.', 'societypress' ) );
		}

		$committee_id = absint( $_POST['committee_id'] ?? 0 );
		$member_id    = absint( $_POST['member_id'] ?? 0 );
		$role         = sanitize_text_field( $_POST['role'] ?? 'member' );
		$joined_date  = sanitize_text_field( $_POST['joined_date'] ?? '' );
		$notes        = sanitize_textarea_field( $_POST['notes'] ?? '' );

		if ( ! $committee_id || ! $member_id ) {
			$this->add_admin_notice( __( 'Invalid committee or member.', 'societypress' ), 'error' );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		$result = societypress()->committees->add_member( $committee_id, $member_id, $role, $joined_date, $notes );

		if ( is_wp_error( $result ) ) {
			$this->add_admin_notice( $result->get_error_message(), 'error' );
		} else {
			$this->add_admin_notice( __( 'Member added to committee.', 'societypress' ), 'success' );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=societypress-committee-members&committee_id=' . $committee_id ) );
		exit;
	}

	/**
	 * Remove a member from a committee.
	 */
	private function remove_member(): void {
		$member_id    = absint( $_GET['member'] ?? 0 );
		$committee_id = absint( $_GET['committee_id'] ?? 0 );

		if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'remove_member_' . $member_id ) ) {
			wp_die( __( 'Security check failed.', 'societypress' ) );
		}

		if ( ! current_user_can( 'manage_society_members' ) ) {
			wp_die( __( 'You do not have permission to remove committee members.', 'societypress' ) );
		}

		$result = societypress()->committees->remove_member( $committee_id, $member_id );

		$this->add_admin_notice(
			$result ? __( 'Member removed from committee.', 'societypress' ) : __( 'Failed to remove member.', 'societypress' ),
			$result ? 'success' : 'error'
		);

		wp_safe_redirect( admin_url( 'admin.php?page=societypress-committee-members&committee_id=' . $committee_id ) );
		exit;
	}

	/**
	 * Update a member's role.
	 */
	private function update_member_role(): void {
		if ( ! wp_verify_nonce( $_POST['sp_role_nonce'] ?? '', 'update_member_role' ) ) {
			wp_die( __( 'Security check failed.', 'societypress' ) );
		}

		if ( ! current_user_can( 'manage_society_members' ) ) {
			wp_die( __( 'You do not have permission to update member roles.', 'societypress' ) );
		}

		$committee_id = absint( $_POST['committee_id'] ?? 0 );
		$member_id    = absint( $_POST['member_id'] ?? 0 );
		$role         = sanitize_text_field( $_POST['role'] ?? 'member' );

		$result = societypress()->committees->update_member_role( $committee_id, $member_id, $role );

		$this->add_admin_notice(
			$result ? __( 'Role updated.', 'societypress' ) : __( 'Failed to update role.', 'societypress' ),
			$result ? 'success' : 'error'
		);

		wp_safe_redirect( admin_url( 'admin.php?page=societypress-committee-members&committee_id=' . $committee_id ) );
		exit;
	}

	/**
	 * Add an admin notice.
	 *
	 * @param string $message Notice message.
	 * @param string $type    Notice type.
	 */
	private function add_admin_notice( string $message, string $type = 'info' ): void {
		$notices   = get_transient( 'societypress_committees_notices' ) ?: array();
		$notices[] = array(
			'message' => $message,
			'type'    => $type,
		);
		set_transient( 'societypress_committees_notices', $notices, 60 );
	}

	/**
	 * Render admin notices.
	 */
	private function render_admin_notices(): void {
		$notices = get_transient( 'societypress_committees_notices' );
		if ( ! $notices ) {
			return;
		}

		delete_transient( 'societypress_committees_notices' );

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
