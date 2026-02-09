<?php
/**
 * Dashboard Widgets
 *
 * Displays membership statistics and alerts on the WordPress dashboard.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SocietyPress_Dashboard_Widgets
 *
 * Manages dashboard widgets for membership overview.
 */
class SocietyPress_Dashboard_Widgets {

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
		add_action( 'wp_dashboard_setup', array( $this, 'register_widgets' ) );
	}

	/**
	 * Register dashboard widgets.
	 */
	public function register_widgets(): void {
		// Only show widgets to users with permission
		if ( ! current_user_can( 'manage_society_members' ) ) {
			return;
		}

		$settings = get_option( 'societypress_settings', array() );

		// Check if widgets are enabled
		if ( isset( $settings['dashboard_widgets_enabled'] ) && ! $settings['dashboard_widgets_enabled'] ) {
			return;
		}

		wp_add_dashboard_widget(
			'societypress_expiring_members',
			__( 'Memberships Expiring Soon', 'societypress' ),
			array( $this, 'render_expiring_members_widget' ),
			null,
			null,
			'normal',
			'high'
		);

		wp_add_dashboard_widget(
			'societypress_recent_signups',
			__( 'Recent New Members', 'societypress' ),
			array( $this, 'render_recent_signups_widget' ),
			null,
			null,
			'side',
			'default'
		);

		wp_add_dashboard_widget(
			'societypress_quick_stats',
			__( 'Membership Overview', 'societypress' ),
			array( $this, 'render_quick_stats_widget' ),
			null,
			null,
			'side',
			'high'
		);
	}

	/**
	 * Render expiring members widget.
	 *
	 * Shows members whose membership will expire soon.
	 * WHY: Proactive membership management - admins can contact members before expiration.
	 */
	public function render_expiring_members_widget(): void {
		$settings = get_option( 'societypress_settings', array() );
		$days     = $settings['dashboard_expiring_days'] ?? 30;
		$members  = $this->get_expiring_members( $days );

		if ( empty( $members ) ) {
			echo '<p>' . esc_html__( 'No memberships expiring in the next 30 days.', 'societypress' ) . '</p>';
			echo '<p class="sp-widget-footer"><a href="' . esc_url( admin_url( 'admin.php?page=societypress-members' ) ) . '">' . esc_html__( 'View All Members →', 'societypress' ) . '</a></p>';
			return;
		}

		echo '<table class="widefat sp-widget-table">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__( 'Member', 'societypress' ) . '</th>';
		echo '<th>' . esc_html__( 'Tier', 'societypress' ) . '</th>';
		echo '<th>' . esc_html__( 'Expires', 'societypress' ) . '</th>';
		echo '</tr></thead>';
		echo '<tbody>';

		foreach ( $members as $member ) {
			$edit_url    = admin_url( 'admin.php?page=societypress-member-edit&member_id=' . $member->id );
			$days_until  = ( strtotime( $member->expiration_date ) - time() ) / DAY_IN_SECONDS;
			$row_class   = $days_until <= 7 ? 'sp-urgent' : 'sp-warning';

			echo '<tr class="' . esc_attr( $row_class ) . '">';
			echo '<td><a href="' . esc_url( $edit_url ) . '"><strong>' . esc_html( $member->first_name . ' ' . $member->last_name ) . '</strong></a></td>';
			echo '<td>' . esc_html( $member->tier_name ) . '</td>';
			echo '<td>' . esc_html( date_i18n( 'M j', strtotime( $member->expiration_date ) ) ) . ' <span class="sp-days">(' . absint( ceil( $days_until ) ) . 'd)</span></td>';
			echo '</tr>';
		}

		echo '</tbody></table>';

		$filter_url = admin_url( 'admin.php?page=societypress-members&status=active' );
		echo '<p class="sp-widget-footer"><a href="' . esc_url( $filter_url ) . '">' . esc_html__( 'View All Expiring Members →', 'societypress' ) . '</a></p>';
	}

	/**
	 * Render recent signups widget.
	 *
	 * Shows recently joined members.
	 * WHY: Track growth and identify new members who may need welcome outreach.
	 */
	public function render_recent_signups_widget(): void {
		$settings = get_option( 'societypress_settings', array() );
		$days     = $settings['dashboard_recent_days'] ?? 30;
		$members  = $this->get_recent_signups( $days );

		if ( empty( $members ) ) {
			echo '<p>' . esc_html__( 'No new members in the last 30 days.', 'societypress' ) . '</p>';
			return;
		}

		echo '<table class="widefat sp-widget-table sp-recent-table">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__( 'Member', 'societypress' ) . '</th>';
		echo '<th>' . esc_html__( 'Tier', 'societypress' ) . '</th>';
		echo '<th>' . esc_html__( 'Joined', 'societypress' ) . '</th>';
		echo '</tr></thead>';
		echo '<tbody>';

		foreach ( $members as $member ) {
			$edit_url = admin_url( 'admin.php?page=societypress-member-edit&member_id=' . $member->id );

			echo '<tr>';
			echo '<td><a href="' . esc_url( $edit_url ) . '"><strong>' . esc_html( $member->first_name . ' ' . $member->last_name ) . '</strong></a></td>';
			echo '<td>' . esc_html( $member->tier_name ) . '</td>';
			echo '<td>' . esc_html( date_i18n( 'M j', strtotime( $member->join_date ) ) ) . '</td>';
			echo '</tr>';
		}

		echo '</tbody></table>';

		$all_url = admin_url( 'admin.php?page=societypress-members' );
		echo '<p class="sp-widget-footer"><a href="' . esc_url( $all_url ) . '">' . esc_html__( 'View All Members →', 'societypress' ) . '</a></p>';
	}

	/**
	 * Render quick stats widget.
	 *
	 * Shows membership counts by status.
	 * WHY: At-a-glance overview of membership health without navigating away from dashboard.
	 */
	public function render_quick_stats_widget(): void {
		$stats = $this->get_member_stats();

		?>
		<div class="sp-quick-stats">
			<div class="sp-stat">
				<span class="sp-stat-number"><?php echo absint( $stats['total'] ); ?></span>
				<span class="sp-stat-label"><?php esc_html_e( 'Total Members', 'societypress' ); ?></span>
			</div>
			<div class="sp-stat sp-stat-active">
				<span class="sp-stat-number"><?php echo absint( $stats['active'] ); ?></span>
				<span class="sp-stat-label"><?php esc_html_e( 'Active', 'societypress' ); ?></span>
			</div>
			<div class="sp-stat sp-stat-expired">
				<span class="sp-stat-number"><?php echo absint( $stats['expired'] ); ?></span>
				<span class="sp-stat-label"><?php esc_html_e( 'Expired', 'societypress' ); ?></span>
			</div>
			<div class="sp-stat sp-stat-pending">
				<span class="sp-stat-number"><?php echo absint( $stats['pending'] ); ?></span>
				<span class="sp-stat-label"><?php esc_html_e( 'Pending', 'societypress' ); ?></span>
			</div>
		</div>

		<p class="sp-widget-footer">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress' ) ); ?>">
				<?php esc_html_e( 'View Dashboard →', 'societypress' ); ?>
			</a>
		</p>
		<?php
	}

	/**
	 * Get members expiring soon.
	 *
	 * WHY: Uses transient cache to avoid repeated database queries on every dashboard load.
	 * Cache duration: 1 hour.
	 *
	 * @param int $days Number of days ahead to check.
	 * @return array
	 */
	public function get_expiring_members( int $days = 30 ): array {
		$cache_key = 'sp_widget_expiring_' . $days;
		$members   = get_transient( $cache_key );

		if ( false === $members ) {
			$members_table = SocietyPress::table( 'members' );
			$tiers_table   = SocietyPress::table( 'membership_tiers' );

			$sql = $this->wpdb->prepare(
				"SELECT m.id, m.first_name, m.last_name, m.expiration_date,
						t.name as tier_name
				 FROM {$members_table} m
				 INNER JOIN {$tiers_table} t ON m.membership_tier_id = t.id
				 WHERE m.status = 'active'
				   AND m.expiration_date IS NOT NULL
				   AND m.expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL %d DAY)
				 ORDER BY m.expiration_date ASC
				 LIMIT 10",
				$days
			);

			$members = $this->wpdb->get_results( $sql );
			set_transient( $cache_key, $members, HOUR_IN_SECONDS );
		}

		return $members;
	}

	/**
	 * Get recently joined members.
	 *
	 * WHY: Uses transient cache for performance.
	 * Cache duration: 1 hour.
	 *
	 * @param int $days Number of days back to check.
	 * @return array
	 */
	public function get_recent_signups( int $days = 30 ): array {
		$cache_key = 'sp_widget_recent_' . $days;
		$members   = get_transient( $cache_key );

		if ( false === $members ) {
			$members_table = SocietyPress::table( 'members' );
			$tiers_table   = SocietyPress::table( 'membership_tiers' );

			$sql = $this->wpdb->prepare(
				"SELECT m.id, m.first_name, m.last_name, m.join_date,
						t.name as tier_name
				 FROM {$members_table} m
				 INNER JOIN {$tiers_table} t ON m.membership_tier_id = t.id
				 WHERE m.join_date >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
				 ORDER BY m.join_date DESC
				 LIMIT 10",
				$days
			);

			$members = $this->wpdb->get_results( $sql );
			set_transient( $cache_key, $members, HOUR_IN_SECONDS );
		}

		return $members;
	}

	/**
	 * Get member statistics by status.
	 *
	 * WHY: Single query with GROUP BY is more efficient than multiple COUNT queries.
	 * Uses transient cache for 1 hour.
	 *
	 * @return array
	 */
	public function get_member_stats(): array {
		$cache_key = 'sp_widget_stats';
		$stats     = get_transient( $cache_key );

		if ( false === $stats ) {
			$members_table = SocietyPress::table( 'members' );

			// Get counts by status
			$results = $this->wpdb->get_results(
				"SELECT status, COUNT(*) as count
				 FROM {$members_table}
				 GROUP BY status"
			);

			$stats = array(
				'total'     => 0,
				'active'    => 0,
				'expired'   => 0,
				'pending'   => 0,
				'cancelled' => 0,
				'deceased'  => 0,
			);

			foreach ( $results as $row ) {
				$stats[ $row->status ] = (int) $row->count;
				$stats['total']       += (int) $row->count;
			}

			set_transient( $cache_key, $stats, HOUR_IN_SECONDS );
		}

		return $stats;
	}
}
