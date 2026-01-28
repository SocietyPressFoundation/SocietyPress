<?php
/**
 * Public Member Directory
 *
 * Provides searchable, filterable member directory via [societypress_directory] shortcode.
 * Respects privacy settings and only shows members who opt in.
 *
 * WHY: Helps members discover and connect with each other based on research interests.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SocietyPress_Directory
 *
 * Public-facing member directory functionality.
 */
class SocietyPress_Directory {

	/**
	 * Database object.
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks(): void {
		add_action( 'init', array( $this, 'register_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );
		add_action( 'wp_ajax_nopriv_societypress_directory_search', array( $this, 'handle_ajax_search' ) );
		add_action( 'wp_ajax_societypress_directory_search', array( $this, 'handle_ajax_search' ) );
	}

	/**
	 * Register shortcode.
	 */
	public function register_shortcode(): void {
		add_shortcode( 'societypress_directory', array( $this, 'render_directory' ) );
	}

	/**
	 * Enqueue public assets.
	 *
	 * WHY: Only load directory CSS/JS on pages that use the shortcode.
	 */
	public function enqueue_public_assets(): void {
		// Check if shortcode is present
		global $post;
		if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'societypress_directory' ) ) {
			return;
		}

		wp_enqueue_style(
			'societypress-directory',
			SOCIETYPRESS_URL . 'assets/css/directory.css',
			array(),
			SOCIETYPRESS_VERSION
		);

		wp_enqueue_script(
			'societypress-directory',
			SOCIETYPRESS_URL . 'assets/js/directory.js',
			array( 'jquery' ),
			SOCIETYPRESS_VERSION,
			true
		);

		wp_localize_script(
			'societypress-directory',
			'societypressDirectory',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'societypress_directory' ),
			)
		);
	}

	/**
	 * Render directory shortcode.
	 *
	 * WHY: Main entry point for public member directory display.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_directory( $atts ): string {
		$atts = shortcode_atts(
			array(
				'view'         => 'grid',
				'per_page'     => 24,
				'show_search'  => 'true',
				'show_filters' => 'true',
				'tier'         => '',
				'state'        => '',
				'fields'       => 'name,location,tier,surnames',
			),
			$atts,
			'societypress_directory'
		);

		// Sanitize attributes
		$atts['view']         = in_array( $atts['view'], array( 'grid', 'list' ), true ) ? $atts['view'] : 'grid';
		$atts['per_page']     = absint( $atts['per_page'] );
		$atts['show_search']  = filter_var( $atts['show_search'], FILTER_VALIDATE_BOOLEAN );
		$atts['show_filters'] = filter_var( $atts['show_filters'], FILTER_VALIDATE_BOOLEAN );
		$atts['tier']         = sanitize_text_field( $atts['tier'] );
		$atts['state']        = sanitize_text_field( $atts['state'] );
		$atts['fields']       = array_map( 'trim', explode( ',', $atts['fields'] ) );

		// Get current page
		$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

		// Build query args
		$args = array(
			'per_page' => $atts['per_page'],
			'page'     => $paged,
		);

		// Apply filters from URL or shortcode
		if ( ! empty( $atts['tier'] ) ) {
			$args['tier'] = $atts['tier'];
		} elseif ( ! empty( $_GET['tier'] ) ) {
			$args['tier'] = sanitize_text_field( $_GET['tier'] );
		}

		if ( ! empty( $atts['state'] ) ) {
			$args['state'] = $atts['state'];
		} elseif ( ! empty( $_GET['state'] ) ) {
			$args['state'] = sanitize_text_field( $_GET['state'] );
		}

		if ( ! empty( $_GET['search'] ) ) {
			$args['search'] = sanitize_text_field( $_GET['search'] );
		}

		// Get members
		$result = $this->get_directory_members( $args );

		ob_start();
		?>
		<div class="societypress-directory" data-view="<?php echo esc_attr( $atts['view'] ); ?>">
			<?php if ( $atts['show_search'] || $atts['show_filters'] ) : ?>
				<?php echo $this->render_search_form( $atts ); ?>
			<?php endif; ?>

			<?php if ( ! empty( $result['members'] ) ) : ?>
				<div class="sp-directory-count">
					<?php
					printf(
						esc_html__( 'Showing %1$d-%2$d of %3$d members', 'societypress' ),
						absint( ( $paged - 1 ) * $atts['per_page'] + 1 ),
						absint( min( $paged * $atts['per_page'], $result['total'] ) ),
						absint( $result['total'] )
					);
					?>
				</div>

				<?php echo $this->render_member_grid( $result['members'], $atts ); ?>

				<?php if ( $result['total'] > $atts['per_page'] ) : ?>
					<div class="sp-directory-pagination">
						<?php
						echo paginate_links(
							array(
								'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
								'format'    => '?paged=%#%',
								'current'   => max( 1, $paged ),
								'total'     => ceil( $result['total'] / $atts['per_page'] ),
								'prev_text' => __( '&laquo; Previous', 'societypress' ),
								'next_text' => __( 'Next &raquo;', 'societypress' ),
							)
						);
						?>
					</div>
				<?php endif; ?>
			<?php else : ?>
				<p class="sp-directory-empty"><?php esc_html_e( 'No members found.', 'societypress' ); ?></p>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render search and filter form.
	 *
	 * WHY: Allows visitors to find members by name, tier, or location.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	private function render_search_form( array $atts ): string {
		$search = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
		$tier   = isset( $_GET['tier'] ) ? sanitize_text_field( $_GET['tier'] ) : ( $atts['tier'] ?? '' );
		$state  = isset( $_GET['state'] ) ? sanitize_text_field( $_GET['state'] ) : ( $atts['state'] ?? '' );

		// Get available tiers
		$tiers = societypress()->tiers->get_active();

		// Get available states (from members)
		$states = $this->get_available_states();

		ob_start();
		?>
		<form class="sp-directory-search" method="get" action="">
			<div class="sp-search-fields">
				<?php if ( $atts['show_search'] ) : ?>
					<div class="sp-search-field">
						<label for="sp-search-input" class="screen-reader-text"><?php esc_html_e( 'Search members', 'societypress' ); ?></label>
						<input
							type="search"
							id="sp-search-input"
							name="search"
							placeholder="<?php esc_attr_e( 'Search by name or surname...', 'societypress' ); ?>"
							value="<?php echo esc_attr( $search ); ?>"
						>
					</div>
				<?php endif; ?>

				<?php if ( $atts['show_filters'] ) : ?>
					<?php if ( ! empty( $tiers ) ) : ?>
						<div class="sp-filter-field">
							<label for="sp-tier-filter" class="screen-reader-text"><?php esc_html_e( 'Filter by tier', 'societypress' ); ?></label>
							<select id="sp-tier-filter" name="tier">
								<option value=""><?php esc_html_e( 'All Tiers', 'societypress' ); ?></option>
								<?php foreach ( $tiers as $tier_obj ) : ?>
									<option value="<?php echo esc_attr( $tier_obj->slug ); ?>" <?php selected( $tier, $tier_obj->slug ); ?>>
										<?php echo esc_html( $tier_obj->name ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $states ) ) : ?>
						<div class="sp-filter-field">
							<label for="sp-state-filter" class="screen-reader-text"><?php esc_html_e( 'Filter by state', 'societypress' ); ?></label>
							<select id="sp-state-filter" name="state">
								<option value=""><?php esc_html_e( 'All States', 'societypress' ); ?></option>
								<?php foreach ( $states as $state_name ) : ?>
									<option value="<?php echo esc_attr( $state_name ); ?>" <?php selected( $state, $state_name ); ?>>
										<?php echo esc_html( $state_name ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<button type="submit" class="sp-search-submit"><?php esc_html_e( 'Search', 'societypress' ); ?></button>

				<?php if ( ! empty( $search ) || ! empty( $tier ) || ! empty( $state ) ) : ?>
					<a href="<?php echo esc_url( get_permalink() ); ?>" class="sp-clear-filters">
						<?php esc_html_e( 'Clear', 'societypress' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</form>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render member grid/list.
	 *
	 * @param array $members Members array.
	 * @param array $atts    Shortcode attributes.
	 * @return string HTML output.
	 */
	private function render_member_grid( array $members, array $atts ): string {
		ob_start();
		?>
		<div class="sp-directory-grid sp-view-<?php echo esc_attr( $atts['view'] ); ?>">
			<?php foreach ( $members as $member ) : ?>
				<?php echo $this->render_member_card( $member, $atts ); ?>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render individual member card.
	 *
	 * WHY: Displays member info while respecting privacy (email obfuscation, no sensitive data).
	 *
	 * @param object $member Member object.
	 * @param array  $atts   Shortcode attributes.
	 * @return string HTML output.
	 */
	private function render_member_card( object $member, array $atts ): string {
		$fields = $atts['fields'];

		ob_start();
		?>
		<div class="sp-member-card">
			<?php if ( in_array( 'name', $fields, true ) ) : ?>
				<div class="sp-member-name">
					<?php echo esc_html( $member->first_name . ' ' . $member->last_name ); ?>
				</div>
			<?php endif; ?>

			<?php if ( in_array( 'location', $fields, true ) && ! empty( $member->city ) ) : ?>
				<div class="sp-member-location">
					<?php
					$location_parts = array_filter( array( $member->city, $member->state_province ) );
					echo esc_html( implode( ', ', $location_parts ) );
					?>
				</div>
			<?php endif; ?>

			<?php if ( in_array( 'tier', $fields, true ) && ! empty( $member->tier_name ) ) : ?>
				<div class="sp-member-tier">
					<?php echo esc_html( $member->tier_name ); ?>
				</div>
			<?php endif; ?>

			<?php if ( in_array( 'surnames', $fields, true ) && ! empty( $member->surnames ) ) : ?>
				<div class="sp-member-surnames">
					<strong><?php esc_html_e( 'Researching:', 'societypress' ); ?></strong>
					<?php echo esc_html( $member->surnames ); ?>
				</div>
			<?php endif; ?>

			<?php if ( in_array( 'email', $fields, true ) && ! empty( $member->primary_email ) ) : ?>
				<div class="sp-member-contact">
					<a href="mailto:<?php echo esc_attr( $member->primary_email ); ?>">
						<?php echo esc_html( $this->obfuscate_email( $member->primary_email ) ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get directory members with filters.
	 *
	 * WHY: Centralized query logic with caching and privacy controls.
	 * Only shows: directory_visible = 1 AND status = 'active'.
	 *
	 * @param array $args Query arguments.
	 * @return array Array with 'members' and 'total' keys.
	 */
	private function get_directory_members( array $args ): array {
		$defaults = array(
			'per_page' => 24,
			'page'     => 1,
			'search'   => '',
			'tier'     => '',
			'state'    => '',
		);

		$args = wp_parse_args( $args, $defaults );

		// Generate cache key
		$cache_key = 'sp_directory_' . md5( serialize( $args ) );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$members_table = SocietyPress::table( 'members' );
		$contact_table = SocietyPress::table( 'member_contact' );
		$tiers_table   = SocietyPress::table( 'membership_tiers' );

		// Build WHERE clauses
		$where = array(
			'm.directory_visible = 1',
			"m.status = 'active'",
		);

		$where_args = array();

		// Search by name or surname
		if ( ! empty( $args['search'] ) ) {
			$where[]      = '(m.first_name LIKE %s OR m.last_name LIKE %s OR CONCAT(m.first_name, " ", m.last_name) LIKE %s)';
			$search_term  = '%' . $this->wpdb->esc_like( $args['search'] ) . '%';
			$where_args[] = $search_term;
			$where_args[] = $search_term;
			$where_args[] = $search_term;
		}

		// Filter by tier
		if ( ! empty( $args['tier'] ) ) {
			$tier_obj = societypress()->tiers->get_by_slug( $args['tier'] );
			if ( $tier_obj ) {
				$where[]      = 'm.membership_tier_id = %d';
				$where_args[] = $tier_obj->id;
			}
		}

		// Filter by state
		if ( ! empty( $args['state'] ) ) {
			$where[]      = 'c.state_province = %s';
			$where_args[] = $args['state'];
		}

		$where_clause = implode( ' AND ', $where );

		// Get total count
		$count_sql = "SELECT COUNT(DISTINCT m.id)
					  FROM {$members_table} m
					  INNER JOIN {$contact_table} c ON m.id = c.member_id
					  WHERE {$where_clause}";

		if ( ! empty( $where_args ) ) {
			$count_sql = $this->wpdb->prepare( $count_sql, $where_args );
		}

		$total = (int) $this->wpdb->get_var( $count_sql );

		// Get members
		$offset = ( $args['page'] - 1 ) * $args['per_page'];

		$sql = "SELECT m.id, m.first_name, m.last_name,
					   c.primary_email, c.city, c.state_province,
					   t.name as tier_name,
					   GROUP_CONCAT(DISTINCT s.surname ORDER BY s.surname SEPARATOR ', ') as surnames
				FROM {$members_table} m
				INNER JOIN {$contact_table} c ON m.id = c.member_id
				INNER JOIN {$tiers_table} t ON m.membership_tier_id = t.id
				LEFT JOIN " . SocietyPress::table( 'member_surnames' ) . " s ON m.id = s.member_id
				WHERE {$where_clause}
				GROUP BY m.id
				ORDER BY m.last_name ASC, m.first_name ASC
				LIMIT %d OFFSET %d";

		$query_args = array_merge( $where_args, array( $args['per_page'], $offset ) );
		$members    = $this->wpdb->get_results( $this->wpdb->prepare( $sql, $query_args ) );

		$result = array(
			'members' => $members,
			'total'   => $total,
		);

		// Cache for 15 minutes
		set_transient( $cache_key, $result, 15 * MINUTE_IN_SECONDS );

		return $result;
	}

	/**
	 * Get available states from members.
	 *
	 * WHY: Populate state filter dropdown.
	 *
	 * @return array State names.
	 */
	private function get_available_states(): array {
		$cache_key = 'sp_directory_states';
		$states    = get_transient( $cache_key );

		if ( false === $states ) {
			$contact_table = SocietyPress::table( 'member_contact' );
			$members_table = SocietyPress::table( 'members' );

			$sql = "SELECT DISTINCT c.state_province
					FROM {$contact_table} c
					INNER JOIN {$members_table} m ON c.member_id = m.id
					WHERE m.directory_visible = 1
					  AND m.status = 'active'
					  AND c.state_province IS NOT NULL
					  AND c.state_province != ''
					ORDER BY c.state_province ASC";

			$results = $this->wpdb->get_col( $sql );
			$states  = $results ?: array();

			set_transient( $cache_key, $states, HOUR_IN_SECONDS );
		}

		return $states;
	}

	/**
	 * Obfuscate email address.
	 *
	 * WHY: Prevents email harvesting by bots while remaining readable to humans.
	 *
	 * @param string $email Email address.
	 * @return string Obfuscated email.
	 */
	private function obfuscate_email( string $email ): string {
		$parts = explode( '@', $email );
		if ( count( $parts ) !== 2 ) {
			return $email;
		}

		$domain_parts = explode( '.', $parts[1] );
		return $parts[0] . ' [at] ' . implode( ' [dot] ', $domain_parts );
	}

	/**
	 * Handle AJAX directory search.
	 *
	 * WHY: Enables live filtering without page reload.
	 */
	public function handle_ajax_search(): void {
		check_ajax_referer( 'societypress_directory', 'nonce' );

		$args = array(
			'per_page' => isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 24,
			'page'     => isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1,
			'search'   => isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '',
			'tier'     => isset( $_POST['tier'] ) ? sanitize_text_field( $_POST['tier'] ) : '',
			'state'    => isset( $_POST['state'] ) ? sanitize_text_field( $_POST['state'] ) : '',
		);

		$result = $this->get_directory_members( $args );

		wp_send_json_success( $result );
	}
}
