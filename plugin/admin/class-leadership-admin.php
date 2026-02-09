<?php
/**
 * Leadership Admin Interface
 *
 * Admin pages for managing positions (President, VP, etc.) and
 * assigning members to those positions.
 *
 * WHY: Societies need to track who holds leadership positions and
 *      when their terms started/ended. This provides a clear interface
 *      for managing that information.
 *
 * @package SocietyPress
 * @since 0.55d
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SocietyPress_Leadership_Admin
 *
 * Admin interface for leadership management.
 */
class SocietyPress_Leadership_Admin {

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
	 * Register hidden admin pages for leadership editing.
	 *
	 * WHY: Edit/detail pages for positions and assignments don't need sidebar
	 *      links — users reach them by clicking actions on the Leadership list.
	 */
	public function register_pages(): void {
		// Add hidden pages for editing
		add_submenu_page(
			'',
			__( 'Edit Position', 'societypress' ),
			__( 'Edit Position', 'societypress' ),
			'manage_society_members',
			'societypress-position-edit',
			array( $this, 'render_position_edit' )
		);

		add_submenu_page(
			'',
			__( 'Position Holders', 'societypress' ),
			__( 'Position Holders', 'societypress' ),
			'manage_society_members',
			'societypress-position-holders',
			array( $this, 'render_position_holders' )
		);

		add_submenu_page(
			'',
			__( 'Assign Position', 'societypress' ),
			__( 'Assign Position', 'societypress' ),
			'manage_society_members',
			'societypress-position-assign',
			array( $this, 'render_position_assign' )
		);
	}

	/**
	 * Handle form submissions and actions.
	 */
	public function handle_actions(): void {
		// Only process on our pages
		$page = $_GET['page'] ?? '';
		if ( strpos( $page, 'societypress-leadership' ) === false && strpos( $page, 'societypress-position' ) === false ) {
			return;
		}

		// Save position
		if ( isset( $_POST['societypress_action'] ) && 'save_position' === $_POST['societypress_action'] ) {
			$this->save_position();
		}

		// Delete position
		if ( isset( $_GET['action'] ) && 'delete_position' === $_GET['action'] && isset( $_GET['position'] ) ) {
			$this->delete_position();
		}

		// Assign holder
		if ( isset( $_POST['societypress_action'] ) && 'assign_holder' === $_POST['societypress_action'] ) {
			$this->assign_holder();
		}

		// Update holder
		if ( isset( $_POST['societypress_action'] ) && 'update_holder' === $_POST['societypress_action'] ) {
			$this->update_holder();
		}

		// Remove holder
		if ( isset( $_GET['action'] ) && 'remove_holder' === $_GET['action'] && isset( $_GET['holder'] ) ) {
			$this->remove_holder();
		}

		// End holder term
		if ( isset( $_GET['action'] ) && 'end_term' === $_GET['action'] && isset( $_GET['holder'] ) ) {
			$this->end_holder_term();
		}
	}

	/**
	 * Render the main leadership page.
	 *
	 * WHY: This is called by the main admin class's placeholder page
	 *      but we'll override it to show real content.
	 */
	public function render_leadership_page(): void {
		if ( ! current_user_can( 'manage_society_members' ) ) {
			wp_die( __( 'You do not have permission to access this page.', 'societypress' ) );
		}

		$leadership = societypress()->leadership;
		$positions  = $leadership->get_all_positions();
		$counts     = $leadership->get_holder_counts();

		// Get current holders for each position
		$current_holders = array();
		foreach ( $positions as $position ) {
			$current_holders[ $position['id'] ] = $leadership->get_current_holder( $position['id'] );
		}

		?>
		<div class="wrap societypress-admin societypress-leadership-admin">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Leadership', 'societypress' ); ?></h1>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-position-edit' ) ); ?>" class="page-title-action">
				<?php esc_html_e( 'Add Position', 'societypress' ); ?>
			</a>
			<hr class="wp-header-end">

			<?php $this->render_admin_notices(); ?>

			<?php if ( empty( $positions ) ) : ?>
				<div class="notice notice-info">
					<p><?php esc_html_e( 'No leadership positions defined yet. Add your first position to get started.', 'societypress' ); ?></p>
				</div>
			<?php else : ?>
				<table class="widefat striped societypress-positions-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Position', 'societypress' ); ?></th>
							<th><?php esc_html_e( 'Current Holder', 'societypress' ); ?></th>
							<th><?php esc_html_e( 'Term Started', 'societypress' ); ?></th>
							<th><?php esc_html_e( 'Type', 'societypress' ); ?></th>
							<th><?php esc_html_e( 'History', 'societypress' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $positions as $position ) : ?>
							<?php $this->render_position_row( $position, $current_holders[ $position['id'] ] ?? null, $counts[ $position['id'] ] ?? 0 ); ?>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render a single position row.
	 *
	 * @param array      $position       Position data.
	 * @param array|null $current_holder Current holder data or null.
	 * @param int        $holder_count   Total holder count.
	 */
	private function render_position_row( array $position, ?array $current_holder, int $holder_count ): void {
		$edit_url    = admin_url( 'admin.php?page=societypress-position-edit&position_id=' . $position['id'] );
		$holders_url = admin_url( 'admin.php?page=societypress-position-holders&position_id=' . $position['id'] );
		$assign_url  = admin_url( 'admin.php?page=societypress-position-assign&position_id=' . $position['id'] );
		$delete_url  = wp_nonce_url(
			admin_url( 'admin.php?page=societypress-leadership&action=delete_position&position=' . $position['id'] ),
			'delete_position_' . $position['id']
		);

		$types = array();
		if ( $position['is_board_member'] ) {
			$types[] = __( 'Board', 'societypress' );
		}
		if ( $position['is_officer'] ) {
			$types[] = __( 'Officer', 'societypress' );
		}

		?>
		<tr>
			<td>
				<strong><a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $position['title'] ); ?></a></strong>
				<br><code><?php echo esc_html( $position['slug'] ); ?></code>
				<div class="row-actions">
					<span class="edit">
						<a href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit', 'societypress' ); ?></a> |
					</span>
					<span class="assign">
						<a href="<?php echo esc_url( $assign_url ); ?>"><?php esc_html_e( 'Assign Member', 'societypress' ); ?></a> |
					</span>
					<span class="history">
						<a href="<?php echo esc_url( $holders_url ); ?>"><?php esc_html_e( 'View History', 'societypress' ); ?></a>
						<?php if ( $holder_count === 0 ) : ?>
							|
						<?php endif; ?>
					</span>
					<?php if ( $holder_count === 0 ) : ?>
						<span class="delete">
							<a href="<?php echo esc_url( $delete_url ); ?>" class="submitdelete"
							   onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this position?', 'societypress' ) ); ?>');">
								<?php esc_html_e( 'Delete', 'societypress' ); ?>
							</a>
						</span>
					<?php endif; ?>
				</div>
			</td>
			<td>
				<?php if ( $current_holder ) : ?>
					<strong><?php echo esc_html( $current_holder['first_name'] . ' ' . $current_holder['last_name'] ); ?></strong>
				<?php else : ?>
					<em class="sp-vacant"><?php esc_html_e( 'Vacant', 'societypress' ); ?></em>
				<?php endif; ?>
			</td>
			<td>
				<?php if ( $current_holder ) : ?>
					<?php echo esc_html( date_i18n( 'M j, Y', strtotime( $current_holder['term_start'] ) ) ); ?>
				<?php else : ?>
					—
				<?php endif; ?>
			</td>
			<td>
				<?php echo esc_html( implode( ', ', $types ) ?: '—' ); ?>
			</td>
			<td>
				<?php if ( $holder_count > 0 ) : ?>
					<a href="<?php echo esc_url( $holders_url ); ?>">
						<?php
						printf(
							/* translators: %d: number of holders */
							esc_html( _n( '%d holder', '%d holders', $holder_count, 'societypress' ) ),
							$holder_count
						);
						?>
					</a>
				<?php else : ?>
					<?php esc_html_e( 'None', 'societypress' ); ?>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render the position add/edit page.
	 */
	public function render_position_edit(): void {
		if ( ! current_user_can( 'manage_society_members' ) ) {
			wp_die( __( 'You do not have permission to access this page.', 'societypress' ) );
		}

		$position_id = isset( $_GET['position_id'] ) ? absint( $_GET['position_id'] ) : 0;
		$position    = null;
		$is_new      = true;

		if ( $position_id ) {
			$position = societypress()->leadership->get_position( $position_id );
			if ( ! $position ) {
				wp_die( __( 'Position not found.', 'societypress' ) );
			}
			$is_new = false;
		}

		$page_title = $is_new ? __( 'Add Position', 'societypress' ) : __( 'Edit Position', 'societypress' );

		// Get next sort order for new positions
		$next_sort_order = 0;
		if ( $is_new ) {
			$positions = societypress()->leadership->get_all_positions();
			if ( ! empty( $positions ) ) {
				$next_sort_order = max( array_column( $positions, 'sort_order' ) ) + 10;
			}
		}

		?>
		<div class="wrap societypress-admin societypress-leadership-admin">
			<h1><?php echo esc_html( $page_title ); ?></h1>

			<?php $this->render_admin_notices(); ?>

			<form method="post" action="" class="societypress-position-form">
				<?php wp_nonce_field( 'save_position', 'sp_position_nonce' ); ?>
				<input type="hidden" name="societypress_action" value="save_position">
				<input type="hidden" name="position_id" value="<?php echo esc_attr( $position_id ); ?>">

				<table class="form-table">
					<tr>
						<th scope="row"><label for="title"><?php esc_html_e( 'Title', 'societypress' ); ?> <span class="required">*</span></label></th>
						<td>
							<input type="text" id="title" name="title" class="regular-text" required
							       value="<?php echo esc_attr( $position['title'] ?? '' ); ?>">
							<p class="description"><?php esc_html_e( 'e.g., President, Vice President, Treasurer', 'societypress' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="slug"><?php esc_html_e( 'Slug', 'societypress' ); ?></label></th>
						<td>
							<input type="text" id="slug" name="slug" class="regular-text"
							       value="<?php echo esc_attr( $position['slug'] ?? '' ); ?>">
							<p class="description"><?php esc_html_e( 'URL-friendly identifier. Leave blank to auto-generate.', 'societypress' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="description"><?php esc_html_e( 'Description', 'societypress' ); ?></label></th>
						<td>
							<textarea id="description" name="description" rows="4" class="large-text"><?php echo esc_textarea( $position['description'] ?? '' ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Optional description of this position\'s responsibilities.', 'societypress' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Position Type', 'societypress' ); ?></th>
						<td>
							<fieldset>
								<label>
									<input type="checkbox" name="is_board_member" value="1"
										<?php checked( $position['is_board_member'] ?? 0, 1 ); ?>>
									<?php esc_html_e( 'Board of Directors', 'societypress' ); ?>
								</label>
								<br>
								<label>
									<input type="checkbox" name="is_officer" value="1"
										<?php checked( $position['is_officer'] ?? 0, 1 ); ?>>
									<?php esc_html_e( 'Officer', 'societypress' ); ?>
								</label>
								<p class="description"><?php esc_html_e( 'Check all that apply. Used for filtering and display grouping.', 'societypress' ); ?></p>
							</fieldset>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="sort_order"><?php esc_html_e( 'Sort Order', 'societypress' ); ?></label></th>
						<td>
							<input type="number" id="sort_order" name="sort_order" class="small-text" min="0"
							       value="<?php echo esc_attr( $position['sort_order'] ?? $next_sort_order ); ?>">
							<p class="description"><?php esc_html_e( 'Lower numbers display first.', 'societypress' ); ?></p>
						</td>
					</tr>
				</table>

				<p class="submit">
					<input type="submit" name="submit" class="button button-primary"
					       value="<?php echo $is_new ? esc_attr__( 'Add Position', 'societypress' ) : esc_attr__( 'Update Position', 'societypress' ); ?>">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-leadership' ) ); ?>" class="button">
						<?php esc_html_e( 'Cancel', 'societypress' ); ?>
					</a>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the position holders history page.
	 */
	public function render_position_holders(): void {
		if ( ! current_user_can( 'manage_society_members' ) ) {
			wp_die( __( 'You do not have permission to access this page.', 'societypress' ) );
		}

		$position_id = isset( $_GET['position_id'] ) ? absint( $_GET['position_id'] ) : 0;

		if ( ! $position_id ) {
			wp_die( __( 'No position specified.', 'societypress' ) );
		}

		$position = societypress()->leadership->get_position( $position_id );
		if ( ! $position ) {
			wp_die( __( 'Position not found.', 'societypress' ) );
		}

		$holders = societypress()->leadership->get_position_holders( $position_id );

		?>
		<div class="wrap societypress-admin societypress-leadership-admin">
			<h1>
				<?php
				printf(
					/* translators: %s: position title */
					esc_html__( '%s - Position History', 'societypress' ),
					esc_html( $position['title'] )
				);
				?>
			</h1>

			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-leadership' ) ); ?>" class="button">
					&larr; <?php esc_html_e( 'Back to Leadership', 'societypress' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-position-assign&position_id=' . $position_id ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Assign New Holder', 'societypress' ); ?>
				</a>
			</p>

			<?php $this->render_admin_notices(); ?>

			<?php if ( empty( $holders ) ) : ?>
				<div class="notice notice-info">
					<p><?php esc_html_e( 'No one has held this position yet.', 'societypress' ); ?></p>
				</div>
			<?php else : ?>
				<table class="widefat striped societypress-holders-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Member', 'societypress' ); ?></th>
							<th><?php esc_html_e( 'Term', 'societypress' ); ?></th>
							<th><?php esc_html_e( 'Status', 'societypress' ); ?></th>
							<th><?php esc_html_e( 'Notes', 'societypress' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'societypress' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $holders as $holder ) : ?>
							<?php $this->render_holder_row( $holder, $position_id ); ?>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render a single holder row.
	 *
	 * @param array $holder      Holder data.
	 * @param int   $position_id Position ID.
	 */
	private function render_holder_row( array $holder, int $position_id ): void {
		$remove_url = wp_nonce_url(
			admin_url( 'admin.php?page=societypress-position-holders&position_id=' . $position_id . '&action=remove_holder&holder=' . $holder['id'] ),
			'remove_holder_' . $holder['id']
		);
		$end_term_url = wp_nonce_url(
			admin_url( 'admin.php?page=societypress-position-holders&position_id=' . $position_id . '&action=end_term&holder=' . $holder['id'] ),
			'end_term_' . $holder['id']
		);

		$term = societypress()->leadership->format_term( $holder );

		?>
		<tr class="<?php echo $holder['is_current'] ? 'sp-current-holder' : ''; ?>">
			<td>
				<strong><?php echo esc_html( $holder['first_name'] . ' ' . $holder['last_name'] ); ?></strong>
			</td>
			<td><?php echo esc_html( $term ); ?></td>
			<td>
				<?php if ( $holder['is_current'] ) : ?>
					<span class="sp-status-current"><?php esc_html_e( 'Current', 'societypress' ); ?></span>
				<?php else : ?>
					<span class="sp-status-past"><?php esc_html_e( 'Past', 'societypress' ); ?></span>
				<?php endif; ?>
			</td>
			<td><?php echo esc_html( $holder['notes'] ?: '—' ); ?></td>
			<td>
				<?php if ( $holder['is_current'] ) : ?>
					<a href="<?php echo esc_url( $end_term_url ); ?>" class="button button-small"
					   onclick="return confirm('<?php echo esc_js( __( 'End this member\'s term? They will be marked as a past holder.', 'societypress' ) ); ?>');">
						<?php esc_html_e( 'End Term', 'societypress' ); ?>
					</a>
				<?php endif; ?>
				<a href="<?php echo esc_url( $remove_url ); ?>" class="button button-small button-link-delete"
				   onclick="return confirm('<?php echo esc_js( __( 'Remove this holder record? This cannot be undone.', 'societypress' ) ); ?>');">
					<?php esc_html_e( 'Remove', 'societypress' ); ?>
				</a>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render the assign position page.
	 */
	public function render_position_assign(): void {
		if ( ! current_user_can( 'manage_society_members' ) ) {
			wp_die( __( 'You do not have permission to access this page.', 'societypress' ) );
		}

		$position_id = isset( $_GET['position_id'] ) ? absint( $_GET['position_id'] ) : 0;

		if ( ! $position_id ) {
			wp_die( __( 'No position specified.', 'societypress' ) );
		}

		$position = societypress()->leadership->get_position( $position_id );
		if ( ! $position ) {
			wp_die( __( 'Position not found.', 'societypress' ) );
		}

		// Get current holder if any
		$current_holder = societypress()->leadership->get_current_holder( $position_id );

		?>
		<div class="wrap societypress-admin societypress-leadership-admin">
			<h1>
				<?php
				printf(
					/* translators: %s: position title */
					esc_html__( 'Assign %s', 'societypress' ),
					esc_html( $position['title'] )
				);
				?>
			</h1>

			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-leadership' ) ); ?>" class="button">
					&larr; <?php esc_html_e( 'Back to Leadership', 'societypress' ); ?>
				</a>
			</p>

			<?php $this->render_admin_notices(); ?>

			<?php if ( $current_holder ) : ?>
				<div class="notice notice-warning">
					<p>
						<?php
						printf(
							/* translators: %s: current holder name */
							esc_html__( 'This position is currently held by %s. Assigning a new holder will end their term.', 'societypress' ),
							'<strong>' . esc_html( $current_holder['first_name'] . ' ' . $current_holder['last_name'] ) . '</strong>'
						);
						?>
					</p>
				</div>
			<?php endif; ?>

			<form method="post" action="" class="societypress-assign-form">
				<?php wp_nonce_field( 'assign_holder', 'sp_assign_nonce' ); ?>
				<input type="hidden" name="societypress_action" value="assign_holder">
				<input type="hidden" name="position_id" value="<?php echo esc_attr( $position_id ); ?>">

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
								       data-min-length="2">
								<input type="hidden" id="member_id" name="member_id" required>
								<div id="member_search_results" class="sp-member-search-results"></div>
							</div>
							<span class="sp-search-hint"><?php esc_html_e( 'Start typing a first or last name to find members.', 'societypress' ); ?></span>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="term_start"><?php esc_html_e( 'Term Start', 'societypress' ); ?> <span class="required">*</span></label></th>
						<td>
							<input type="date" id="term_start" name="term_start" required
							       value="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>">
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="term_end"><?php esc_html_e( 'Term End', 'societypress' ); ?></label></th>
						<td>
							<input type="date" id="term_end" name="term_end">
							<p class="description"><?php esc_html_e( 'Leave blank if term is ongoing.', 'societypress' ); ?></p>
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
					       value="<?php esc_attr_e( 'Assign Position', 'societypress' ); ?>">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-leadership' ) ); ?>" class="button">
						<?php esc_html_e( 'Cancel', 'societypress' ); ?>
					</a>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Save a position.
	 */
	private function save_position(): void {
		if ( ! wp_verify_nonce( $_POST['sp_position_nonce'] ?? '', 'save_position' ) ) {
			wp_die( __( 'Security check failed.', 'societypress' ) );
		}

		if ( ! current_user_can( 'manage_society_members' ) ) {
			wp_die( __( 'You do not have permission to manage positions.', 'societypress' ) );
		}

		$position_id = absint( $_POST['position_id'] ?? 0 );

		$data = array(
			'title'           => sanitize_text_field( $_POST['title'] ?? '' ),
			'slug'            => sanitize_title( $_POST['slug'] ?? '' ),
			'description'     => sanitize_textarea_field( $_POST['description'] ?? '' ),
			'is_board_member' => isset( $_POST['is_board_member'] ) ? 1 : 0,
			'is_officer'      => isset( $_POST['is_officer'] ) ? 1 : 0,
			'sort_order'      => absint( $_POST['sort_order'] ?? 0 ),
		);

		if ( empty( $data['title'] ) ) {
			$this->add_admin_notice( __( 'Title is required.', 'societypress' ), 'error' );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		$leadership = societypress()->leadership;

		if ( $position_id ) {
			$result  = $leadership->update_position( $position_id, $data );
			$message = $result ? __( 'Position updated.', 'societypress' ) : __( 'Failed to update position.', 'societypress' );
		} else {
			$result  = $leadership->create_position( $data );
			$message = $result ? __( 'Position created.', 'societypress' ) : __( 'Failed to create position.', 'societypress' );
		}

		$this->add_admin_notice( $message, $result ? 'success' : 'error' );
		wp_safe_redirect( admin_url( 'admin.php?page=societypress-leadership' ) );
		exit;
	}

	/**
	 * Delete a position.
	 */
	private function delete_position(): void {
		$position_id = absint( $_GET['position'] ?? 0 );

		if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'delete_position_' . $position_id ) ) {
			wp_die( __( 'Security check failed.', 'societypress' ) );
		}

		if ( ! current_user_can( 'manage_society_members' ) ) {
			wp_die( __( 'You do not have permission to delete positions.', 'societypress' ) );
		}

		$result = societypress()->leadership->delete_position( $position_id );

		if ( is_wp_error( $result ) ) {
			$this->add_admin_notice( $result->get_error_message(), 'error' );
		} else {
			$this->add_admin_notice( __( 'Position deleted.', 'societypress' ), 'success' );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=societypress-leadership' ) );
		exit;
	}

	/**
	 * Assign a holder to a position.
	 */
	private function assign_holder(): void {
		if ( ! wp_verify_nonce( $_POST['sp_assign_nonce'] ?? '', 'assign_holder' ) ) {
			wp_die( __( 'Security check failed.', 'societypress' ) );
		}

		if ( ! current_user_can( 'manage_society_members' ) ) {
			wp_die( __( 'You do not have permission to assign positions.', 'societypress' ) );
		}

		$position_id = absint( $_POST['position_id'] ?? 0 );

		$data = array(
			'position_id' => $position_id,
			'member_id'   => absint( $_POST['member_id'] ?? 0 ),
			'term_start'  => sanitize_text_field( $_POST['term_start'] ?? '' ),
			'term_end'    => ! empty( $_POST['term_end'] ) ? sanitize_text_field( $_POST['term_end'] ) : null,
			'is_current'  => empty( $_POST['term_end'] ) ? 1 : 0,
			'notes'       => sanitize_textarea_field( $_POST['notes'] ?? '' ),
		);

		$result = societypress()->leadership->assign_holder( $data );

		if ( is_wp_error( $result ) ) {
			$this->add_admin_notice( $result->get_error_message(), 'error' );
			wp_safe_redirect( wp_get_referer() );
		} else {
			$this->add_admin_notice( __( 'Position assigned successfully.', 'societypress' ), 'success' );
			wp_safe_redirect( admin_url( 'admin.php?page=societypress-leadership' ) );
		}
		exit;
	}

	/**
	 * Remove a holder record.
	 */
	private function remove_holder(): void {
		$holder_id   = absint( $_GET['holder'] ?? 0 );
		$position_id = absint( $_GET['position_id'] ?? 0 );

		if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'remove_holder_' . $holder_id ) ) {
			wp_die( __( 'Security check failed.', 'societypress' ) );
		}

		if ( ! current_user_can( 'manage_society_members' ) ) {
			wp_die( __( 'You do not have permission to remove holders.', 'societypress' ) );
		}

		$result = societypress()->leadership->remove_holder( $holder_id );

		$this->add_admin_notice(
			$result ? __( 'Holder removed.', 'societypress' ) : __( 'Failed to remove holder.', 'societypress' ),
			$result ? 'success' : 'error'
		);

		wp_safe_redirect( admin_url( 'admin.php?page=societypress-position-holders&position_id=' . $position_id ) );
		exit;
	}

	/**
	 * End a current holder's term.
	 */
	private function end_holder_term(): void {
		$holder_id   = absint( $_GET['holder'] ?? 0 );
		$position_id = absint( $_GET['position_id'] ?? 0 );

		if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'end_term_' . $holder_id ) ) {
			wp_die( __( 'Security check failed.', 'societypress' ) );
		}

		if ( ! current_user_can( 'manage_society_members' ) ) {
			wp_die( __( 'You do not have permission to end terms.', 'societypress' ) );
		}

		$result = societypress()->leadership->update_holder( $holder_id, array(
			'is_current' => 0,
			'term_end'   => current_time( 'Y-m-d' ),
		) );

		$this->add_admin_notice(
			$result ? __( 'Term ended.', 'societypress' ) : __( 'Failed to end term.', 'societypress' ),
			$result ? 'success' : 'error'
		);

		wp_safe_redirect( admin_url( 'admin.php?page=societypress-position-holders&position_id=' . $position_id ) );
		exit;
	}

	/**
	 * Add an admin notice.
	 *
	 * @param string $message Notice message.
	 * @param string $type    Notice type.
	 */
	private function add_admin_notice( string $message, string $type = 'info' ): void {
		$notices   = get_transient( 'societypress_leadership_notices' ) ?: array();
		$notices[] = array(
			'message' => $message,
			'type'    => $type,
		);
		set_transient( 'societypress_leadership_notices', $notices, 60 );
	}

	/**
	 * Render admin notices.
	 */
	private function render_admin_notices(): void {
		$notices = get_transient( 'societypress_leadership_notices' );
		if ( ! $notices ) {
			return;
		}

		delete_transient( 'societypress_leadership_notices' );

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
