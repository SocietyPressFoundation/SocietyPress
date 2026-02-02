<?php
/**
 * SocietyPress Public Widgets
 *
 * Block-based widgets for the front end. These appear in the "SocietyPress"
 * category at the top of the block inserter, making them easy for admins to find.
 *
 * WHY: Genealogical societies need widgets showing community activity - new members,
 *      upcoming events, in memoriam notices. These help seniors stay engaged and
 *      informed without digging through multiple pages.
 *
 * @package SocietyPress
 * @since 0.52d
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SocietyPress_Widgets
 *
 * Registers and renders block widgets for public display.
 */
class SocietyPress_Widgets {

	/**
	 * WordPress database object.
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
	 *
	 * WHY: Register all our blocks on init so they appear in the block inserter.
	 */
	private function init_hooks(): void {
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Register all SocietyPress blocks.
	 *
	 * WHY: Using dynamic blocks (server-side rendering) because:
	 *      - No JavaScript build step required
	 *      - Data comes from our database, not post content
	 *      - Easier for non-developers to maintain
	 */
	public function register_blocks(): void {
		// Upcoming Events Widget
		register_block_type( 'societypress/upcoming-events', array(
			'api_version'     => 3,
			'title'           => __( 'Upcoming Events', 'societypress' ),
			'description'     => __( 'Displays upcoming society events with dates and registration links.', 'societypress' ),
			'category'        => 'societypress',
			'icon'            => 'calendar-alt',
			'keywords'        => array( 'events', 'calendar', 'meetings', 'classes' ),
			'supports'        => array(
				'html'   => false,
				'anchor' => true,
			),
			'attributes'      => array(
				'title'     => array(
					'type'    => 'string',
					'default' => __( 'Upcoming Events', 'societypress' ),
				),
				'count'     => array(
					'type'    => 'number',
					'default' => 5,
				),
				'showTime'  => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'showLocation' => array(
					'type'    => 'boolean',
					'default' => false,
				),
			),
			'render_callback' => array( $this, 'render_upcoming_events' ),
			'editor_script'   => null, // We'll add this if needed for settings
		) );

		// New Members Widget
		register_block_type( 'societypress/new-members', array(
			'api_version'     => 3,
			'title'           => __( 'New Members', 'societypress' ),
			'description'     => __( 'Welcomes recently joined members to the society.', 'societypress' ),
			'category'        => 'societypress',
			'icon'            => 'groups',
			'keywords'        => array( 'members', 'welcome', 'new', 'joined' ),
			'supports'        => array(
				'html'   => false,
				'anchor' => true,
			),
			'attributes'      => array(
				'title'    => array(
					'type'    => 'string',
					'default' => __( 'Welcome New Members', 'societypress' ),
				),
				'count'    => array(
					'type'    => 'number',
					'default' => 5,
				),
				'days'     => array(
					'type'    => 'number',
					'default' => 30,
				),
				'showCity' => array(
					'type'    => 'boolean',
					'default' => true,
				),
			),
			'render_callback' => array( $this, 'render_new_members' ),
		) );

		// In Memoriam Widget
		register_block_type( 'societypress/in-memoriam', array(
			'api_version'     => 3,
			'title'           => __( 'In Memoriam', 'societypress' ),
			'description'     => __( 'Honors deceased members with dignity and respect.', 'societypress' ),
			'category'        => 'societypress',
			'icon'            => 'heart',
			'keywords'        => array( 'memorial', 'deceased', 'remembrance', 'tribute' ),
			'supports'        => array(
				'html'   => false,
				'anchor' => true,
			),
			'attributes'      => array(
				'title' => array(
					'type'    => 'string',
					'default' => __( 'In Memoriam', 'societypress' ),
				),
				'count' => array(
					'type'    => 'number',
					'default' => 10,
				),
				'showDates' => array(
					'type'    => 'boolean',
					'default' => true,
				),
			),
			'render_callback' => array( $this, 'render_in_memoriam' ),
		) );

		// Membership Status Widget (shows logged-in user's status)
		register_block_type( 'societypress/membership-status', array(
			'api_version'     => 3,
			'title'           => __( 'My Membership', 'societypress' ),
			'description'     => __( 'Shows the logged-in member their membership status and renewal info.', 'societypress' ),
			'category'        => 'societypress',
			'icon'            => 'id-alt',
			'keywords'        => array( 'membership', 'status', 'renewal', 'expiration' ),
			'supports'        => array(
				'html'   => false,
				'anchor' => true,
			),
			'attributes'      => array(
				'title'            => array(
					'type'    => 'string',
					'default' => __( 'My Membership', 'societypress' ),
				),
				'showRenewalButton' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'daysWarning'       => array(
					'type'    => 'number',
					'default' => 30,
				),
			),
			'render_callback' => array( $this, 'render_membership_status' ),
		) );

		// Quick Links Widget
		register_block_type( 'societypress/quick-links', array(
			'api_version'     => 3,
			'title'           => __( 'Quick Links', 'societypress' ),
			'description'     => __( 'Displays helpful research links for genealogists.', 'societypress' ),
			'category'        => 'societypress',
			'icon'            => 'admin-links',
			'keywords'        => array( 'links', 'resources', 'research', 'websites' ),
			'supports'        => array(
				'html'   => false,
				'anchor' => true,
			),
			'attributes'      => array(
				'title' => array(
					'type'    => 'string',
					'default' => __( 'Research Resources', 'societypress' ),
				),
				'links' => array(
					'type'    => 'string',
					'default' => '',
				),
			),
			'render_callback' => array( $this, 'render_quick_links' ),
		) );

		// Research Tip Widget
		register_block_type( 'societypress/research-tip', array(
			'api_version'     => 3,
			'title'           => __( 'Research Tip', 'societypress' ),
			'description'     => __( 'Shows a rotating genealogy research tip.', 'societypress' ),
			'category'        => 'societypress',
			'icon'            => 'lightbulb',
			'keywords'        => array( 'tip', 'advice', 'research', 'genealogy' ),
			'supports'        => array(
				'html'   => false,
				'anchor' => true,
			),
			'attributes'      => array(
				'title' => array(
					'type'    => 'string',
					'default' => __( 'Research Tip', 'societypress' ),
				),
			),
			'render_callback' => array( $this, 'render_research_tip' ),
		) );

		// Latest Newsletter Widget
		register_block_type( 'societypress/newsletter', array(
			'api_version'     => 3,
			'title'           => __( 'Latest Newsletter', 'societypress' ),
			'description'     => __( 'Highlights the most recent society newsletter.', 'societypress' ),
			'category'        => 'societypress',
			'icon'            => 'media-document',
			'keywords'        => array( 'newsletter', 'publication', 'download', 'pdf' ),
			'supports'        => array(
				'html'   => false,
				'anchor' => true,
			),
			'attributes'      => array(
				'title' => array(
					'type'    => 'string',
					'default' => __( 'Latest Newsletter', 'societypress' ),
				),
			),
			'render_callback' => array( $this, 'render_newsletter' ),
		) );

		// Library Hours Widget
		register_block_type( 'societypress/library-hours', array(
			'api_version'     => 3,
			'title'           => __( 'Library Hours', 'societypress' ),
			'description'     => __( 'Displays research library operating hours.', 'societypress' ),
			'category'        => 'societypress',
			'icon'            => 'clock',
			'keywords'        => array( 'hours', 'library', 'schedule', 'open' ),
			'supports'        => array(
				'html'   => false,
				'anchor' => true,
			),
			'attributes'      => array(
				'title' => array(
					'type'    => 'string',
					'default' => __( 'Library Hours', 'societypress' ),
				),
				'hours' => array(
					'type'    => 'string',
					'default' => '',
				),
				'note' => array(
					'type'    => 'string',
					'default' => '',
				),
			),
			'render_callback' => array( $this, 'render_library_hours' ),
		) );

		// Announcements Widget
		register_block_type( 'societypress/announcements', array(
			'api_version'     => 3,
			'title'           => __( 'Announcements', 'societypress' ),
			'description'     => __( 'Displays recent news and announcements.', 'societypress' ),
			'category'        => 'societypress',
			'icon'            => 'megaphone',
			'keywords'        => array( 'news', 'announcements', 'updates', 'posts' ),
			'supports'        => array(
				'html'   => false,
				'anchor' => true,
			),
			'attributes'      => array(
				'title' => array(
					'type'    => 'string',
					'default' => __( 'News & Announcements', 'societypress' ),
				),
				'count' => array(
					'type'    => 'number',
					'default' => 3,
				),
				'category' => array(
					'type'    => 'string',
					'default' => '',
				),
			),
			'render_callback' => array( $this, 'render_announcements' ),
		) );

		// Volunteer Opportunities Widget
		register_block_type( 'societypress/volunteer', array(
			'api_version'     => 3,
			'title'           => __( 'Volunteer Opportunities', 'societypress' ),
			'description'     => __( 'Lists current volunteer needs and open positions.', 'societypress' ),
			'category'        => 'societypress',
			'icon'            => 'superhero',
			'keywords'        => array( 'volunteer', 'help', 'positions', 'opportunities' ),
			'supports'        => array(
				'html'   => false,
				'anchor' => true,
			),
			'attributes'      => array(
				'title' => array(
					'type'    => 'string',
					'default' => __( 'Volunteer Opportunities', 'societypress' ),
				),
			),
			'render_callback' => array( $this, 'render_volunteer' ),
		) );

		// Contact Info Widget
		register_block_type( 'societypress/contact-info', array(
			'api_version'     => 3,
			'title'           => __( 'Contact Info', 'societypress' ),
			'description'     => __( 'Displays society contact information.', 'societypress' ),
			'category'        => 'societypress',
			'icon'            => 'phone',
			'keywords'        => array( 'contact', 'phone', 'email', 'address' ),
			'supports'        => array(
				'html'   => false,
				'anchor' => true,
			),
			'attributes'      => array(
				'title'       => array(
					'type'    => 'string',
					'default' => __( 'Contact Us', 'societypress' ),
				),
				'showAddress' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'showPhone'   => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'showEmail'   => array(
					'type'    => 'boolean',
					'default' => true,
				),
			),
			'render_callback' => array( $this, 'render_contact_info' ),
		) );

		// Member Count Widget
		register_block_type( 'societypress/member-count', array(
			'api_version'     => 3,
			'title'           => __( 'Member Count', 'societypress' ),
			'description'     => __( 'Shows total active membership count.', 'societypress' ),
			'category'        => 'societypress',
			'icon'            => 'chart-bar',
			'keywords'        => array( 'members', 'count', 'total', 'statistics' ),
			'supports'        => array(
				'html'   => false,
				'anchor' => true,
			),
			'attributes'      => array(
				'title' => array(
					'type'    => 'string',
					'default' => '',
				),
				'showGrowth' => array(
					'type'    => 'boolean',
					'default' => true,
				),
			),
			'render_callback' => array( $this, 'render_member_count' ),
		) );
	}

	/**
	 * Enqueue widget styles.
	 *
	 * WHY: Separate stylesheet for widgets keeps main CSS smaller and
	 *      only loads when widgets are actually used.
	 */
	public function enqueue_styles(): void {
		wp_enqueue_style(
			'societypress-widgets',
			SOCIETYPRESS_URL . 'assets/css/widgets.css',
			array(),
			SOCIETYPRESS_VERSION
		);
	}

	/**
	 * Render Upcoming Events widget.
	 *
	 * WHY: Shows the next few events so members don't miss important meetings.
	 *      Links go directly to event pages where they can register.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	public function render_upcoming_events( array $attributes ): string {
		$title         = $attributes['title'] ?? __( 'Upcoming Events', 'societypress' );
		$count         = absint( $attributes['count'] ?? 5 );
		$show_time     = $attributes['showTime'] ?? true;
		$show_location = $attributes['showLocation'] ?? false;

		// Query upcoming events.
		$events = get_posts( array(
			'post_type'      => 'sp_event',
			'posts_per_page' => $count,
			'post_status'    => 'publish',
			'meta_key'       => '_sp_event_date',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'     => '_sp_event_date',
					'value'   => current_time( 'Y-m-d' ),
					'compare' => '>=',
					'type'    => 'DATE',
				),
			),
		) );

		if ( empty( $events ) ) {
			return $this->render_widget_wrapper(
				$title,
				'<p class="sp-widget-empty">' . esc_html__( 'No upcoming events scheduled.', 'societypress' ) . '</p>',
				'upcoming-events'
			);
		}

		$output = '<ul class="sp-widget-list sp-events-list">';

		foreach ( $events as $event ) {
			$date     = get_post_meta( $event->ID, '_sp_event_date', true );
			$time     = get_post_meta( $event->ID, '_sp_event_time', true );
			$location = get_post_meta( $event->ID, '_sp_event_location', true );

			// Format date nicely (e.g., "Sat, Feb 15").
			$date_formatted = '';
			if ( $date ) {
				$timestamp      = strtotime( $date );
				$date_formatted = date_i18n( 'D, M j', $timestamp );
			}

			$output .= '<li class="sp-widget-item sp-event-item">';
			$output .= '<a href="' . esc_url( get_permalink( $event->ID ) ) . '" class="sp-event-link">';
			$output .= '<span class="sp-event-title">' . esc_html( get_the_title( $event->ID ) ) . '</span>';

			if ( $date_formatted ) {
				$output .= '<span class="sp-event-date">' . esc_html( $date_formatted );
				if ( $show_time && $time ) {
					$output .= ' <span class="sp-event-time">at ' . esc_html( $time ) . '</span>';
				}
				$output .= '</span>';
			}

			if ( $show_location && $location ) {
				$output .= '<span class="sp-event-location">' . esc_html( $location ) . '</span>';
			}

			$output .= '</a>';
			$output .= '</li>';
		}

		$output .= '</ul>';

		// Link to full calendar.
		$archive_link = get_post_type_archive_link( 'sp_event' );
		if ( $archive_link ) {
			$output .= '<p class="sp-widget-footer">';
			$output .= '<a href="' . esc_url( $archive_link ) . '">' . esc_html__( 'View All Events →', 'societypress' ) . '</a>';
			$output .= '</p>';
		}

		return $this->render_widget_wrapper( $title, $output, 'upcoming-events' );
	}

	/**
	 * Render New Members widget.
	 *
	 * WHY: Welcomes new members publicly, helping them feel part of the community.
	 *      Also lets existing members know who's joined recently.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	public function render_new_members( array $attributes ): string {
		$title     = $attributes['title'] ?? __( 'Welcome New Members', 'societypress' );
		$count     = absint( $attributes['count'] ?? 5 );
		$days      = absint( $attributes['days'] ?? 30 );
		$show_city = $attributes['showCity'] ?? true;

		$members_table = SocietyPress::table( 'members' );
		$contact_table = SocietyPress::table( 'member_contact' );

		// Query recent members who joined in the last X days.
		// Only show members who haven't opted out of directory listing.
		$sql = $this->wpdb->prepare(
			"SELECT m.id, m.first_name, m.last_name, m.join_date, c.city
			 FROM {$members_table} m
			 LEFT JOIN {$contact_table} c ON m.id = c.member_id
			 WHERE m.join_date >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
			   AND m.status IN ('active', 'pending')
			   AND (m.directory_opt_out IS NULL OR m.directory_opt_out = 0)
			 ORDER BY m.join_date DESC
			 LIMIT %d",
			$days,
			$count
		);

		$members = $this->wpdb->get_results( $sql );

		if ( empty( $members ) ) {
			return $this->render_widget_wrapper(
				$title,
				'<p class="sp-widget-empty">' . esc_html__( 'No new members recently.', 'societypress' ) . '</p>',
				'new-members'
			);
		}

		$output = '<ul class="sp-widget-list sp-members-list">';

		foreach ( $members as $member ) {
			$output .= '<li class="sp-widget-item sp-member-item">';
			$output .= '<span class="sp-member-name">' . esc_html( $member->first_name . ' ' . $member->last_name ) . '</span>';

			if ( $show_city && ! empty( $member->city ) ) {
				$output .= '<span class="sp-member-city">(' . esc_html( $member->city ) . ')</span>';
			}

			$output .= '</li>';
		}

		$output .= '</ul>';

		return $this->render_widget_wrapper( $title, $output, 'new-members' );
	}

	/**
	 * Render In Memoriam widget.
	 *
	 * WHY: Honors deceased members with dignity. Shows members who have passed away
	 *      so the community can remember and pay respects.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	public function render_in_memoriam( array $attributes ): string {
		$title      = $attributes['title'] ?? __( 'In Memoriam', 'societypress' );
		$count      = absint( $attributes['count'] ?? 10 );
		$show_dates = $attributes['showDates'] ?? true;

		$members_table = SocietyPress::table( 'members' );

		// Query deceased members, most recent first.
		// Uses the 'deceased' status that's already in the system.
		$sql = $this->wpdb->prepare(
			"SELECT id, first_name, last_name, join_date, updated_at
			 FROM {$members_table}
			 WHERE status = 'deceased'
			 ORDER BY updated_at DESC
			 LIMIT %d",
			$count
		);

		$members = $this->wpdb->get_results( $sql );

		if ( empty( $members ) ) {
			// Don't show empty widget - that would be awkward.
			return '';
		}

		$output = '<ul class="sp-widget-list sp-memoriam-list">';

		foreach ( $members as $member ) {
			$output .= '<li class="sp-widget-item sp-memoriam-item">';
			$output .= '<span class="sp-memoriam-name">' . esc_html( $member->first_name . ' ' . $member->last_name ) . '</span>';

			if ( $show_dates && $member->join_date ) {
				// Show membership years (e.g., "Member 2015-2024").
				$join_year = date( 'Y', strtotime( $member->join_date ) );
				$end_year  = date( 'Y', strtotime( $member->updated_at ) );
				$output   .= '<span class="sp-memoriam-years">Member ' . esc_html( $join_year ) . '–' . esc_html( $end_year ) . '</span>';
			}

			$output .= '</li>';
		}

		$output .= '</ul>';

		return $this->render_widget_wrapper( $title, $output, 'in-memoriam' );
	}

	/**
	 * Render Membership Status widget.
	 *
	 * WHY: Shows logged-in members their current status so they know when to renew.
	 *      Reduces "Am I still a member?" questions to admins.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	public function render_membership_status( array $attributes ): string {
		$title              = $attributes['title'] ?? __( 'My Membership', 'societypress' );
		$show_renewal       = $attributes['showRenewalButton'] ?? true;
		$days_warning       = absint( $attributes['daysWarning'] ?? 30 );

		// Must be logged in to see this widget.
		if ( ! is_user_logged_in() ) {
			return $this->render_widget_wrapper(
				$title,
				'<p class="sp-widget-login-required">' .
				sprintf(
					/* translators: %s: login URL */
					__( 'Please <a href="%s">log in</a> to view your membership status.', 'societypress' ),
					esc_url( wp_login_url( get_permalink() ) )
				) .
				'</p>',
				'membership-status'
			);
		}

		// Get current user's member record.
		$user_id   = get_current_user_id();
		$member_id = get_user_meta( $user_id, 'societypress_member_id', true );

		if ( ! $member_id ) {
			return $this->render_widget_wrapper(
				$title,
				'<p class="sp-widget-not-member">' . esc_html__( 'No membership found for your account.', 'societypress' ) . '</p>',
				'membership-status'
			);
		}

		// Get member data.
		$members_table = SocietyPress::table( 'members' );
		$tiers_table   = SocietyPress::table( 'membership_tiers' );

		$member = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT m.*, t.name as tier_name
				 FROM {$members_table} m
				 LEFT JOIN {$tiers_table} t ON m.membership_tier_id = t.id
				 WHERE m.id = %d",
				$member_id
			)
		);

		if ( ! $member ) {
			return $this->render_widget_wrapper(
				$title,
				'<p class="sp-widget-not-member">' . esc_html__( 'Membership record not found.', 'societypress' ) . '</p>',
				'membership-status'
			);
		}

		// Build status display.
		$output = '<div class="sp-membership-info">';

		// Status badge.
		$status_class = 'sp-status-' . sanitize_html_class( $member->status );
		$status_label = ucfirst( $member->status );
		$output      .= '<div class="sp-status-badge ' . esc_attr( $status_class ) . '">' . esc_html( $status_label ) . '</div>';

		// Tier name.
		if ( $member->tier_name ) {
			$output .= '<p class="sp-membership-tier">' . esc_html( $member->tier_name ) . '</p>';
		}

		// Expiration date with warning if soon.
		if ( $member->expiration_date ) {
			$exp_timestamp = strtotime( $member->expiration_date );
			$days_until    = ( $exp_timestamp - time() ) / DAY_IN_SECONDS;
			$exp_formatted = date_i18n( 'F j, Y', $exp_timestamp );

			$exp_class = '';
			$exp_note  = '';

			if ( $member->status === 'expired' || $days_until < 0 ) {
				$exp_class = 'sp-expired';
				$exp_note  = __( '(Expired)', 'societypress' );
			} elseif ( $days_until <= $days_warning ) {
				$exp_class = 'sp-expiring-soon';
				$exp_note  = sprintf(
					/* translators: %d: number of days */
					__( '(%d days remaining)', 'societypress' ),
					ceil( $days_until )
				);
			}

			$output .= '<p class="sp-membership-expires ' . esc_attr( $exp_class ) . '">';
			$output .= esc_html__( 'Expires: ', 'societypress' ) . esc_html( $exp_formatted );
			if ( $exp_note ) {
				$output .= ' <span class="sp-expires-note">' . esc_html( $exp_note ) . '</span>';
			}
			$output .= '</p>';
		}

		// Member since.
		if ( $member->join_date ) {
			$join_formatted = date_i18n( 'F Y', strtotime( $member->join_date ) );
			$output        .= '<p class="sp-membership-since">' . esc_html__( 'Member since: ', 'societypress' ) . esc_html( $join_formatted ) . '</p>';
		}

		// Renewal button if expiring soon or expired.
		if ( $show_renewal && $member->expiration_date ) {
			$days_until = ( strtotime( $member->expiration_date ) - time() ) / DAY_IN_SECONDS;

			if ( $days_until <= $days_warning || $member->status === 'expired' ) {
				// TODO: Link to actual renewal page when payment system is implemented.
				$renewal_url = home_url( '/membership/renew/' );
				$output     .= '<p class="sp-renewal-action">';
				$output     .= '<a href="' . esc_url( $renewal_url ) . '" class="sp-renew-button">';
				$output     .= esc_html__( 'Renew Membership', 'societypress' );
				$output     .= '</a>';
				$output     .= '</p>';
			}
		}

		$output .= '</div>';

		return $this->render_widget_wrapper( $title, $output, 'membership-status' );
	}

	/**
	 * Render Quick Links widget.
	 *
	 * WHY: Genealogists frequently visit the same research sites. Having quick access
	 *      to FamilySearch, Ancestry, FindAGrave, etc. saves time and helps seniors
	 *      who might not remember URLs.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	public function render_quick_links( array $attributes ): string {
		$title = $attributes['title'] ?? __( 'Research Resources', 'societypress' );

		// Default links useful for genealogists.
		// These can be customized via plugin settings in the future.
		$links = array(
			array(
				'name'     => 'FamilySearch',
				'url'      => 'https://www.familysearch.org',
				'icon'     => '🔍',
				'external' => true,
			),
			array(
				'name'     => 'Ancestry',
				'url'      => 'https://www.ancestry.com',
				'icon'     => '🌳',
				'external' => true,
			),
			array(
				'name'     => 'Find A Grave',
				'url'      => 'https://www.findagrave.com',
				'icon'     => '🪦',
				'external' => true,
			),
			array(
				'name'     => 'Newspapers.com',
				'url'      => 'https://www.newspapers.com',
				'icon'     => '📰',
				'external' => true,
			),
			array(
				'name'     => 'Fold3 (Military)',
				'url'      => 'https://www.fold3.com',
				'icon'     => '🎖️',
				'external' => true,
			),
		);

		// Allow filtering of links.
		$links = apply_filters( 'societypress_quick_links', $links );

		if ( empty( $links ) ) {
			return '';
		}

		$output = '<ul class="sp-widget-list sp-quick-links-list">';

		foreach ( $links as $link ) {
			$external_class = ! empty( $link['external'] ) ? ' sp-link-external' : '';
			$target         = ! empty( $link['external'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';

			$output .= '<li class="sp-widget-item">';
			$output .= '<a href="' . esc_url( $link['url'] ) . '" class="sp-quick-link' . esc_attr( $external_class ) . '"' . $target . '>';

			if ( ! empty( $link['icon'] ) ) {
				$output .= '<span class="sp-link-icon">' . esc_html( $link['icon'] ) . '</span>';
			}

			$output .= '<span class="sp-link-text">' . esc_html( $link['name'] ) . '</span>';
			$output .= '</a>';
			$output .= '</li>';
		}

		$output .= '</ul>';

		return $this->render_widget_wrapper( $title, $output, 'quick-links' );
	}

	/**
	 * Render Research Tip widget.
	 *
	 * WHY: Helps members improve their research skills with rotating tips.
	 *      Educational content keeps the site valuable beyond just organizational info.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	public function render_research_tip( array $attributes ): string {
		$title = $attributes['title'] ?? __( 'Research Tip', 'societypress' );

		// Default tips - can be expanded or made editable via settings.
		$tips = array(
			array(
				'tip'    => 'Always cite your sources! Future researchers (and your future self) will thank you.',
				'source' => 'Basic Genealogy Best Practices',
			),
			array(
				'tip'    => 'Check for spelling variations of surnames. Names were often recorded phonetically by census takers.',
				'source' => 'Census Research Tips',
			),
			array(
				'tip'    => 'Don\'t overlook female ancestors. They often hold the key to breaking through brick walls.',
				'source' => 'Research Strategy',
			),
			array(
				'tip'    => 'Join DNA matches to your tree. Even distant cousins can help confirm your research.',
				'source' => 'DNA Research',
			),
			array(
				'tip'    => 'Check obituaries for family relationships. They often list survivors and their locations.',
				'source' => 'Newspaper Research',
			),
			array(
				'tip'    => 'Land records can reveal family relationships, migration patterns, and economic status.',
				'source' => 'Land & Property Records',
			),
			array(
				'tip'    => 'Search for your ancestors in city directories - they can fill gaps between census years.',
				'source' => 'Directory Research',
			),
			array(
				'tip'    => 'Military pension files often contain detailed family information and depositions.',
				'source' => 'Military Records',
			),
			array(
				'tip'    => 'Church records may predate civil vital records. Check parish registers for baptisms, marriages, and burials.',
				'source' => 'Religious Records',
			),
			array(
				'tip'    => 'When you hit a brick wall, work on the collateral lines. Siblings and cousins often provide clues.',
				'source' => 'Problem-Solving Strategies',
			),
		);

		// Allow filtering of tips.
		$tips = apply_filters( 'societypress_research_tips', $tips );

		if ( empty( $tips ) ) {
			return '';
		}

		// Select tip based on day of year for daily rotation.
		$day_of_year = date( 'z' );
		$tip_index   = $day_of_year % count( $tips );
		$tip         = $tips[ $tip_index ];

		$output  = '<div class="sp-tip-content">';
		$output .= '<div class="sp-tip-icon">💡</div>';
		$output .= '<p class="sp-tip-text">' . esc_html( $tip['tip'] ) . '</p>';

		if ( ! empty( $tip['source'] ) ) {
			$output .= '<p class="sp-tip-source">— ' . esc_html( $tip['source'] ) . '</p>';
		}

		$output .= '</div>';

		return $this->render_widget_wrapper( $title, $output, 'research-tip' );
	}

	/**
	 * Render Newsletter widget.
	 *
	 * WHY: Highlights the latest newsletter so members know there's new content.
	 *      Makes it easy to download without navigating to the archive.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	public function render_newsletter( array $attributes ): string {
		$title = $attributes['title'] ?? __( 'Latest Newsletter', 'societypress' );

		// Get the newsletters directory.
		$upload_dir      = wp_upload_dir();
		$newsletters_dir = $upload_dir['basedir'] . '/newsletters';
		$newsletters_url = $upload_dir['baseurl'] . '/newsletters';

		if ( ! is_dir( $newsletters_dir ) ) {
			return '';
		}

		// Find PDF files in the directory.
		$files = glob( $newsletters_dir . '/*.pdf' );

		if ( empty( $files ) ) {
			return $this->render_widget_wrapper(
				$title,
				'<p class="sp-widget-empty">' . esc_html__( 'No newsletters available yet.', 'societypress' ) . '</p>',
				'newsletter'
			);
		}

		// Sort by filename (which includes date) descending.
		rsort( $files );
		$latest = $files[0];

		// Parse filename for display (expects format: YYYY_MM_Month_Newsletter.pdf).
		$filename = basename( $latest, '.pdf' );
		$parts    = explode( '_', $filename );

		$display_name = $filename;
		$display_date = '';

		if ( count( $parts ) >= 3 ) {
			$year  = $parts[0];
			$month = $parts[2] ?? $parts[1];
			$display_name = $month . ' ' . $year . ' Newsletter';
			$display_date = $month . ' ' . $year;
		}

		$file_url = $newsletters_url . '/' . basename( $latest );

		$output  = '<div class="sp-newsletter-current">';
		$output .= '<span class="sp-newsletter-name">' . esc_html( $display_name ) . '</span>';

		if ( $display_date ) {
			$output .= '<span class="sp-newsletter-date">' . esc_html( $display_date ) . '</span>';
		}

		// Only show download link to logged-in users (members only).
		if ( is_user_logged_in() ) {
			$output .= '<a href="' . esc_url( $file_url ) . '" class="sp-newsletter-download" target="_blank">';
			$output .= esc_html__( 'Download PDF', 'societypress' );
			$output .= '</a>';
		} else {
			$output .= '<p class="sp-newsletter-login">';
			$output .= esc_html__( 'Log in to download', 'societypress' );
			$output .= '</p>';
		}

		$output .= '</div>';

		// Link to full archive.
		$archive_page = get_page_by_path( 'newsletters' );
		if ( $archive_page ) {
			$output .= '<p class="sp-widget-footer">';
			$output .= '<a href="' . esc_url( get_permalink( $archive_page->ID ) ) . '">';
			$output .= esc_html__( 'View All Newsletters →', 'societypress' );
			$output .= '</a>';
			$output .= '</p>';
		}

		return $this->render_widget_wrapper( $title, $output, 'newsletter' );
	}

	/**
	 * Render Library Hours widget.
	 *
	 * WHY: Research libraries have specific hours. Showing them prominently
	 *      prevents wasted trips and reduces "are you open?" phone calls.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	public function render_library_hours( array $attributes ): string {
		$title = $attributes['title'] ?? __( 'Library Hours', 'societypress' );
		$note  = $attributes['note'] ?? '';

		// Get hours from settings or use defaults.
		$settings = get_option( 'societypress_settings', array() );

		// Default hours - these should be configurable in settings.
		$hours = array(
			'Monday'    => 'Closed',
			'Tuesday'   => '10:00 AM – 3:00 PM',
			'Wednesday' => '10:00 AM – 3:00 PM',
			'Thursday'  => '10:00 AM – 3:00 PM',
			'Friday'    => '10:00 AM – 3:00 PM',
			'Saturday'  => '10:00 AM – 2:00 PM',
			'Sunday'    => 'Closed',
		);

		// Allow filtering of hours.
		$hours = apply_filters( 'societypress_library_hours', $hours );

		$today = date( 'l' ); // Current day name.

		$output = '<div class="sp-hours-list">';

		foreach ( $hours as $day => $time ) {
			$is_today = ( $day === $today );
			$is_closed = ( strtolower( $time ) === 'closed' );

			$day_class = $is_today ? ' sp-hours-today' : '';
			$time_class = $is_closed ? ' sp-hours-closed' : '';

			$output .= '<div class="sp-hours-day' . esc_attr( $day_class ) . '">';
			$output .= '<span class="sp-hours-day-name">' . esc_html( $day );

			if ( $is_today ) {
				$output .= ' <small>(Today)</small>';
			}

			$output .= '</span>';
			$output .= '<span class="sp-hours-day-time' . esc_attr( $time_class ) . '">' . esc_html( $time ) . '</span>';
			$output .= '</div>';
		}

		$output .= '</div>';

		// Add note if provided.
		$note = $settings['library_hours_note'] ?? $note;
		if ( $note ) {
			$output .= '<p class="sp-hours-note">' . esc_html( $note ) . '</p>';
		}

		return $this->render_widget_wrapper( $title, $output, 'library-hours' );
	}

	/**
	 * Render Announcements widget.
	 *
	 * WHY: Shows recent blog posts/news to keep members informed.
	 *      Drives traffic to important content without requiring navigation.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	public function render_announcements( array $attributes ): string {
		$title    = $attributes['title'] ?? __( 'News & Announcements', 'societypress' );
		$count    = absint( $attributes['count'] ?? 3 );
		$category = $attributes['category'] ?? '';

		$args = array(
			'post_type'      => 'post',
			'posts_per_page' => $count,
			'post_status'    => 'publish',
		);

		// Filter by category if specified.
		if ( $category ) {
			$args['category_name'] = $category;
		}

		$posts = get_posts( $args );

		if ( empty( $posts ) ) {
			return $this->render_widget_wrapper(
				$title,
				'<p class="sp-widget-empty">' . esc_html__( 'No announcements at this time.', 'societypress' ) . '</p>',
				'announcements'
			);
		}

		$output = '<ul class="sp-widget-list sp-announcements-list">';

		foreach ( $posts as $post ) {
			// Check if post has "urgent" or "important" tag.
			$is_urgent    = has_tag( array( 'urgent', 'important' ), $post->ID );
			$urgent_class = $is_urgent ? ' sp-announcement-urgent' : '';

			$output .= '<li class="sp-widget-item sp-announcement-item' . esc_attr( $urgent_class ) . '">';
			$output .= '<a href="' . esc_url( get_permalink( $post->ID ) ) . '" class="sp-announcement-link">';
			$output .= '<span class="sp-announcement-title">' . esc_html( get_the_title( $post->ID ) ) . '</span>';

			// Show excerpt if available.
			$excerpt = wp_trim_words( get_the_excerpt( $post ), 15, '...' );
			if ( $excerpt ) {
				$output .= '<span class="sp-announcement-excerpt">' . esc_html( $excerpt ) . '</span>';
			}

			$output .= '<span class="sp-announcement-date">' . esc_html( get_the_date( 'M j, Y', $post->ID ) ) . '</span>';
			$output .= '</a>';
			$output .= '</li>';
		}

		$output .= '</ul>';

		// Link to blog.
		$blog_url = get_permalink( get_option( 'page_for_posts' ) );
		if ( $blog_url ) {
			$output .= '<p class="sp-widget-footer">';
			$output .= '<a href="' . esc_url( $blog_url ) . '">' . esc_html__( 'View All News →', 'societypress' ) . '</a>';
			$output .= '</p>';
		}

		return $this->render_widget_wrapper( $title, $output, 'announcements' );
	}

	/**
	 * Render Volunteer Opportunities widget.
	 *
	 * WHY: Societies run on volunteers. Making opportunities visible helps
	 *      recruit help and shows members how they can contribute.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	public function render_volunteer( array $attributes ): string {
		$title = $attributes['title'] ?? __( 'Volunteer Opportunities', 'societypress' );

		// Get volunteer opportunities from settings or use defaults.
		// In the future, this could be a custom post type or settings page.
		$opportunities = apply_filters( 'societypress_volunteer_opportunities', array() );

		// Default opportunities if none configured.
		if ( empty( $opportunities ) ) {
			$opportunities = array(
				array(
					'role'        => 'Library Volunteer',
					'description' => 'Help visitors with research at our library',
					'contact'     => '',
				),
				array(
					'role'        => 'Program Committee',
					'description' => 'Help plan educational programs and speakers',
					'contact'     => '',
				),
				array(
					'role'        => 'Hospitality',
					'description' => 'Welcome new members and help at events',
					'contact'     => '',
				),
			);
		}

		$output = '<ul class="sp-widget-list sp-volunteer-list">';

		foreach ( $opportunities as $opp ) {
			$output .= '<li class="sp-widget-item sp-volunteer-item">';
			$output .= '<span class="sp-volunteer-role">' . esc_html( $opp['role'] ) . '</span>';

			if ( ! empty( $opp['description'] ) ) {
				$output .= '<span class="sp-volunteer-desc">' . esc_html( $opp['description'] ) . '</span>';
			}

			if ( ! empty( $opp['contact'] ) ) {
				$output .= '<span class="sp-volunteer-contact">Contact: ' . esc_html( $opp['contact'] ) . '</span>';
			}

			$output .= '</li>';
		}

		$output .= '</ul>';

		return $this->render_widget_wrapper( $title, $output, 'volunteer' );
	}

	/**
	 * Render Contact Info widget.
	 *
	 * WHY: Makes it easy for visitors to find contact information without
	 *      searching. Uses organization settings from the plugin.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	public function render_contact_info( array $attributes ): string {
		$title        = $attributes['title'] ?? __( 'Contact Us', 'societypress' );
		$show_address = $attributes['showAddress'] ?? true;
		$show_phone   = $attributes['showPhone'] ?? true;
		$show_email   = $attributes['showEmail'] ?? true;

		$settings = get_option( 'societypress_settings', array() );

		$output = '<div class="sp-contact-info">';

		// Address.
		if ( $show_address && ! empty( $settings['organization_address'] ) ) {
			$address = nl2br( esc_html( $settings['organization_address'] ) );
			$maps_link = 'https://maps.google.com/?q=' . urlencode( str_replace( "\n", ', ', $settings['organization_address'] ) );

			$output .= '<div class="sp-contact-address">';
			$output .= '<strong>' . esc_html__( 'Address:', 'societypress' ) . '</strong><br>';
			$output .= '<a href="' . esc_url( $maps_link ) . '" target="_blank" rel="noopener noreferrer">';
			$output .= $address;
			$output .= '</a>';
			$output .= '</div>';
		}

		// Phone.
		if ( $show_phone && ! empty( $settings['organization_phone'] ) ) {
			$phone = $settings['organization_phone'];
			$phone_link = 'tel:' . preg_replace( '/[^0-9+]/', '', $phone );

			$output .= '<div class="sp-contact-phone">';
			$output .= '<strong>' . esc_html__( 'Phone:', 'societypress' ) . '</strong> ';
			$output .= '<a href="' . esc_url( $phone_link ) . '">' . esc_html( $phone ) . '</a>';
			$output .= '</div>';
		}

		// Email.
		if ( $show_email && ! empty( $settings['organization_email'] ) ) {
			$email = $settings['organization_email'];

			$output .= '<div class="sp-contact-email">';
			$output .= '<strong>' . esc_html__( 'Email:', 'societypress' ) . '</strong> ';
			$output .= '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
			$output .= '</div>';
		}

		$output .= '</div>';

		return $this->render_widget_wrapper( $title, $output, 'contact-info' );
	}

	/**
	 * Render Member Count widget.
	 *
	 * WHY: Shows social proof and community strength. "Join our 500+ members"
	 *      is more compelling than no number at all.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	public function render_member_count( array $attributes ): string {
		$title       = $attributes['title'] ?? '';
		$show_growth = $attributes['showGrowth'] ?? true;

		$members_table = SocietyPress::table( 'members' );

		// Get active member count.
		$count = (int) $this->wpdb->get_var(
			"SELECT COUNT(*) FROM {$members_table} WHERE status = 'active'"
		);

		// Get new members this month if showing growth.
		$new_this_month = 0;
		if ( $show_growth ) {
			$new_this_month = (int) $this->wpdb->get_var(
				$this->wpdb->prepare(
					"SELECT COUNT(*) FROM {$members_table}
					 WHERE status IN ('active', 'pending')
					   AND join_date >= %s",
					date( 'Y-m-01' )
				)
			);
		}

		$output = '<div class="sp-member-count">';
		$output .= '<div class="sp-count-number">' . number_format( $count ) . '</div>';
		$output .= '<div class="sp-count-label">' . esc_html__( 'Active Members', 'societypress' ) . '</div>';

		if ( $show_growth && $new_this_month > 0 ) {
			$output .= '<div class="sp-count-growth">+' . $new_this_month . ' ' . esc_html__( 'this month', 'societypress' ) . '</div>';
		}

		$output .= '</div>';

		return $this->render_widget_wrapper( $title, $output, 'member-count' );
	}

	/**
	 * Wrap widget content with standard container.
	 *
	 * WHY: Consistent structure across all widgets for styling and accessibility.
	 *
	 * @param string $title   Widget title.
	 * @param string $content Widget content HTML.
	 * @param string $type    Widget type for CSS class.
	 * @return string Complete widget HTML.
	 */
	private function render_widget_wrapper( string $title, string $content, string $type ): string {
		$output  = '<div class="sp-widget sp-widget-' . esc_attr( $type ) . '">';

		if ( $title ) {
			$output .= '<h3 class="sp-widget-title">' . esc_html( $title ) . '</h3>';
		}

		$output .= '<div class="sp-widget-content">';
		$output .= $content;
		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}
}
