<?php
/**
 * Email Notifications
 *
 * Automated email notifications for member lifecycle events:
 * - Welcome emails for new members
 * - Renewal reminders before expiration
 * - Expired membership notices
 *
 * WHY: Reduces manual admin work and ensures timely communication with members.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SocietyPress_Notifications
 *
 * Handles all automated email notifications.
 */
class SocietyPress_Notifications {

	/**
	 * Database object.
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Stores event meta values BEFORE a save, so we can compare old vs new
	 * after the save completes and only email registrants if something actually changed.
	 *
	 * @var array
	 */
	private $old_event_meta = array();

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
	 * WHY: Hooks into member creation for welcome emails and daily cron for renewal reminders.
	 */
	private function init_hooks(): void {
		// Hook into member creation
		add_action( 'societypress_member_created', array( $this, 'send_welcome_email' ) );

		// Daily cron for renewal reminders
		add_action( 'societypress_send_reminders', array( $this, 'schedule_reminder_checks' ) );

		// Event registration confirmation email
		add_action( 'societypress_event_registered', array( $this, 'send_registration_confirmation' ), 10, 4 );

		// Waitlist promotion email — fires when a member is auto-promoted from the waitlist
		add_action( 'societypress_waitlist_promoted', array( $this, 'send_waitlist_promotion_email' ), 10, 3 );

		// Event update notifications — capture old meta BEFORE save, then compare after
		// save_event_meta runs at priority 10, so we compare at priority 99 (after meta is updated)
		add_action( 'pre_post_update', array( $this, 'capture_old_event_meta' ), 10, 1 );
		add_action( 'save_post_sp_event', array( $this, 'notify_event_changes' ), 99, 2 );
	}

	/**
	 * Send welcome email to new member.
	 *
	 * WHY: Automated onboarding improves member experience and reduces admin workload.
	 *
	 * @param int $member_id Member ID.
	 * @return bool Whether email was sent.
	 */
	public function send_welcome_email( int $member_id ): bool {
		$settings = get_option( 'societypress_settings', array() );

		// Check if welcome emails are enabled
		if ( empty( $settings['email_notifications']['welcome_enabled'] ) ) {
			return false;
		}

		// Check if already sent
		if ( $this->has_reminder_been_sent( $member_id, 'welcome' ) ) {
			return false;
		}

		// Get member data
		$member = societypress()->members->get_full( $member_id );
		if ( ! $member || empty( $member['contact']['primary_email'] ) ) {
			return false;
		}

		// Check communication preference
		if ( isset( $member['communication_preference'] ) && 'mail' === $member['communication_preference'] ) {
			return false; // Member prefers postal mail only
		}

		// Get email settings
		$subject = $settings['email_notifications']['welcome_subject'] ?? 'Welcome to {{organization_name}}';
		$message = $settings['email_notifications']['welcome_message'] ?? self::get_default_welcome_template();

		// Replace merge tags
		$subject = $this->replace_merge_tags( $subject, $member );
		$message = $this->replace_merge_tags( $message, $member );

		// Send email
		$sent = $this->send_email(
			$member['contact']['primary_email'],
			$subject,
			$message,
			'welcome'
		);

		// Log if sent
		if ( $sent ) {
			$this->log_email_sent( $member_id, 'welcome', $member['contact']['primary_email'] );
		}

		return $sent;
	}

	/**
	 * Schedule renewal reminder checks (daily cron).
	 *
	 * WHY: Proactive reminders improve renewal rates and member retention.
	 * Batch processing prevents server timeout on large member lists.
	 */
	public function schedule_reminder_checks(): void {
		// Prevent concurrent runs
		if ( get_transient( 'sp_email_batch_running' ) ) {
			return;
		}

		set_transient( 'sp_email_batch_running', true, 5 * MINUTE_IN_SECONDS );

		$settings = get_option( 'societypress_settings', array() );

		// Check if reminders are enabled
		if ( empty( $settings['email_notifications']['reminder_enabled'] ) ) {
			delete_transient( 'sp_email_batch_running' );
			return;
		}

		$days_before = $settings['email_notifications']['reminder_days_before'] ?? array( 30, 14, 7, 1 );

		// Send reminders for each interval
		foreach ( $days_before as $days ) {
			$this->send_renewal_reminders( $days );
		}

		// Check for expired members and send expired notices
		$this->send_expired_notices();

		delete_transient( 'sp_email_batch_running' );
	}

	/**
	 * Send renewal reminders for members expiring in X days.
	 *
	 * WHY: Timely reminders increase renewal likelihood.
	 *
	 * @param int $days_before Days before expiration.
	 * @return int Number of emails sent.
	 */
	private function send_renewal_reminders( int $days_before ): int {
		$members_table = SocietyPress::table( 'members' );
		$contact_table = SocietyPress::table( 'member_contact' );
		$reminders_table = SocietyPress::table( 'renewal_reminders' );

		$reminder_type = 'renewal_' . $days_before;

		// Find members expiring in X days who haven't received this reminder
		$sql = $this->wpdb->prepare(
			"SELECT m.id, m.first_name, m.last_name, m.expiration_date, c.primary_email
			 FROM {$members_table} m
			 INNER JOIN {$contact_table} c ON m.id = c.member_id
			 LEFT JOIN {$reminders_table} r ON m.id = r.member_id AND r.reminder_type = %s
			 WHERE m.status = 'active'
			   AND m.expiration_date = DATE_ADD(CURDATE(), INTERVAL %d DAY)
			   AND r.id IS NULL
			   AND c.primary_email IS NOT NULL
			   AND c.primary_email != ''
			 LIMIT 100",
			$reminder_type,
			$days_before
		);

		$members = $this->wpdb->get_results( $sql );

		if ( empty( $members ) ) {
			return 0;
		}

		$settings = get_option( 'societypress_settings', array() );
		$subject = $settings['email_notifications']['reminder_subject'] ?? 'Your membership expires in {{days_until_expiration}} days';
		$message = $settings['email_notifications']['reminder_message'] ?? self::get_default_reminder_template();

		$sent_count = 0;

		foreach ( $members as $member_row ) {
			// Get full member data
			$member = societypress()->members->get_full( $member_row->id );
			if ( ! $member ) {
				continue;
			}

			// Check communication preference
			if ( isset( $member['communication_preference'] ) && 'mail' === $member['communication_preference'] ) {
				continue;
			}

			// Replace merge tags
			$member_subject = $this->replace_merge_tags( $subject, $member );
			$member_message = $this->replace_merge_tags( $message, $member );

			// Send email
			$sent = $this->send_email(
				$member_row->primary_email,
				$member_subject,
				$member_message,
				$reminder_type
			);

			if ( $sent ) {
				$this->log_email_sent( $member_row->id, $reminder_type, $member_row->primary_email );
				$sent_count++;
			}
		}

		return $sent_count;
	}

	/**
	 * Send expired membership notices.
	 *
	 * WHY: Prompt expired members to renew.
	 * Only sent once per member, on the day after expiration.
	 *
	 * @return int Number of emails sent.
	 */
	private function send_expired_notices(): int {
		$settings = get_option( 'societypress_settings', array() );

		// Check if expired notices are enabled
		if ( empty( $settings['email_notifications']['expired_enabled'] ) ) {
			return 0;
		}

		$members_table = SocietyPress::table( 'members' );
		$contact_table = SocietyPress::table( 'member_contact' );
		$reminders_table = SocietyPress::table( 'renewal_reminders' );

		// Find members who expired yesterday and haven't received expired notice
		$sql = $this->wpdb->prepare(
			"SELECT m.id, m.first_name, m.last_name, m.expiration_date, c.primary_email
			 FROM {$members_table} m
			 INNER JOIN {$contact_table} c ON m.id = c.member_id
			 LEFT JOIN {$reminders_table} r ON m.id = r.member_id AND r.reminder_type = 'expired'
			 WHERE m.status = 'active'
			   AND m.expiration_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
			   AND r.id IS NULL
			   AND c.primary_email IS NOT NULL
			   AND c.primary_email != ''
			 LIMIT 100"
		);

		$members = $this->wpdb->get_results( $sql );

		if ( empty( $members ) ) {
			return 0;
		}

		$subject = $settings['email_notifications']['expired_subject'] ?? 'Your {{organization_name}} membership has expired';
		$message = $settings['email_notifications']['expired_message'] ?? self::get_default_expired_template();

		$sent_count = 0;

		foreach ( $members as $member_row ) {
			// Get full member data
			$member = societypress()->members->get_full( $member_row->id );
			if ( ! $member ) {
				continue;
			}

			// Check communication preference
			if ( isset( $member['communication_preference'] ) && 'mail' === $member['communication_preference'] ) {
				continue;
			}

			// Replace merge tags
			$member_subject = $this->replace_merge_tags( $subject, $member );
			$member_message = $this->replace_merge_tags( $message, $member );

			// Send email
			$sent = $this->send_email(
				$member_row->primary_email,
				$member_subject,
				$member_message,
				'expired'
			);

			if ( $sent ) {
				$this->log_email_sent( $member_row->id, 'expired', $member_row->primary_email );
				$sent_count++;

				// Update member status to expired
				societypress()->members->update_status( $member_row->id, 'expired' );
			}
		}

		return $sent_count;
	}

	/**
	 * Send an email via wp_mail().
	 *
	 * WHY: Centralized email sending ensures consistent headers and error handling.
	 *
	 * @param string $to      Recipient email.
	 * @param string $subject Email subject.
	 * @param string $message Email message (HTML).
	 * @param string $type    Email type (for logging).
	 * @return bool Whether email was sent.
	 */
	private function send_email( string $to, string $subject, string $message, string $type ): bool {
		// Validate email
		if ( ! is_email( $to ) ) {
			return false;
		}

		$settings = get_option( 'societypress_settings', array() );

		$from_name  = $settings['email_notifications']['from_name'] ?? get_bloginfo( 'name' );
		$from_email = $settings['email_notifications']['from_email'] ?? get_option( 'admin_email' );
		$reply_to   = $settings['email_notifications']['reply_to'] ?? $from_email;

		// Prepare headers
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . $from_name . ' <' . $from_email . '>',
			'Reply-To: ' . $reply_to,
		);

		// Wrap message in HTML template
		$html_message = $this->wrap_email_template( $message, $subject );

		// Send via WordPress
		$sent = wp_mail( $to, $subject, $html_message, $headers );

		// Log errors
		if ( ! $sent ) {
			error_log( sprintf( 'SocietyPress: Failed to send %s email to %s', $type, $to ) );
		}

		return $sent;
	}

	/**
	 * Wrap email content in HTML template.
	 *
	 * WHY: Consistent email styling improves member experience and brand recognition.
	 *
	 * @param string $content Email content.
	 * @param string $subject Email subject.
	 * @return string HTML email.
	 */
	private function wrap_email_template( string $content, string $subject ): string {
		$settings = get_option( 'societypress_settings', array() );
		$org_name = $settings['organization_name'] ?? get_bloginfo( 'name' );

		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<style>
				body {
					font-family: Arial, sans-serif;
					font-size: 18px;
					line-height: 1.6;
					color: #333;
					background: #f4f4f4;
					margin: 0;
					padding: 0;
				}
				.email-wrapper {
					max-width: 600px;
					margin: 0 auto;
					background: #ffffff;
					padding: 20px;
				}
				.email-header {
					border-bottom: 3px solid #0073aa;
					padding-bottom: 20px;
					margin-bottom: 20px;
				}
				.email-header h1 {
					margin: 0;
					font-size: 24px;
					color: #0073aa;
				}
				.email-content {
					padding: 0 10px;
				}
				.email-content h2 {
					font-size: 20px;
					color: #333;
					margin-top: 0;
				}
				.email-content p {
					margin-bottom: 15px;
				}
				.button {
					background: #0073aa;
					color: white !important;
					padding: 15px 30px;
					text-decoration: none;
					border-radius: 5px;
					display: inline-block;
					font-size: 18px;
					font-weight: 600;
					min-height: 60px;
					min-width: 200px;
					line-height: 30px;
					text-align: center;
				}
				.button:hover {
					background: #005a87;
				}
				.email-footer {
					border-top: 1px solid #ddd;
					padding-top: 20px;
					margin-top: 30px;
					font-size: 14px;
					color: #666;
				}
				ul, ol {
					margin: 15px 0;
					padding-left: 25px;
				}
				li {
					margin-bottom: 8px;
				}
			</style>
		</head>
		<body>
			<div class="email-wrapper">
				<div class="email-header">
					<h1><?php echo esc_html( $org_name ); ?></h1>
				</div>
				<div class="email-content">
					<?php echo wp_kses_post( wpautop( $content ) ); ?>
				</div>
				<div class="email-footer">
					<p><?php echo esc_html( $org_name ); ?></p>
				</div>
			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	/**
	 * Replace merge tags in content.
	 *
	 * WHY: Personalized emails improve engagement and member experience.
	 *
	 * @param string $content Content with merge tags.
	 * @param array  $member  Member data.
	 * @return string Content with replaced tags.
	 */
	private function replace_merge_tags( string $content, array $member ): string {
		$settings = get_option( 'societypress_settings', array() );

		// Calculate days until expiration
		$days_until = 0;
		if ( ! empty( $member['expiration_date'] ) ) {
			$days_until = max( 0, ceil( ( strtotime( $member['expiration_date'] ) - time() ) / DAY_IN_SECONDS ) );
		}

		// Get portal URL if configured
		$portal_url = '';
		if ( ! empty( $settings['portal_page_id'] ) ) {
			$portal_url = get_permalink( $settings['portal_page_id'] );
		}

		// Merge tags mapping
		$tags = array(
			'{{first_name}}'             => $member['first_name'] ?? '',
			'{{last_name}}'              => $member['last_name'] ?? '',
			'{{full_name}}'              => ( $member['first_name'] ?? '' ) . ' ' . ( $member['last_name'] ?? '' ),
			'{{email}}'                  => $member['contact']['primary_email'] ?? '',
			'{{tier}}'                   => $member['tier']['name'] ?? '',
			'{{status}}'                 => ucfirst( $member['status'] ?? '' ),
			'{{join_date}}'              => ! empty( $member['join_date'] ) ? date_i18n( 'F j, Y', strtotime( $member['join_date'] ) ) : '',
			'{{expiration_date}}'        => ! empty( $member['expiration_date'] ) ? date_i18n( 'F j, Y', strtotime( $member['expiration_date'] ) ) : '',
			'{{days_until_expiration}}'  => $days_until,
			'{{days}}'                   => $days_until, // Alias
			'{{organization_name}}'      => $settings['organization_name'] ?? get_bloginfo( 'name' ),
			'{{portal_url}}'             => $portal_url,
			'{{admin_email}}'            => $settings['admin_email'] ?? get_option( 'admin_email' ),
		);

		// Replace all tags
		return str_replace( array_keys( $tags ), array_values( $tags ), $content );
	}

	/**
	 * Log email sent to database.
	 *
	 * WHY: Prevents duplicate emails via UNIQUE constraint on (member_id, reminder_type).
	 *
	 * @param int    $member_id Member ID.
	 * @param string $type      Reminder type.
	 * @param string $email     Email address sent to.
	 * @return bool Success.
	 */
	private function log_email_sent( int $member_id, string $type, string $email ): bool {
		$table = SocietyPress::table( 'renewal_reminders' );

		$result = $this->wpdb->insert(
			$table,
			array(
				'member_id'      => $member_id,
				'reminder_type'  => $type,
				'email_sent_to'  => $email,
				'sent_at'        => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s' )
		);

		return false !== $result;
	}

	/**
	 * Check if reminder has been sent.
	 *
	 * WHY: Prevents duplicate emails (UNIQUE constraint also handles this at DB level).
	 *
	 * @param int    $member_id Member ID.
	 * @param string $type      Reminder type.
	 * @return bool Whether reminder was sent.
	 */
	private function has_reminder_been_sent( int $member_id, string $type ): bool {
		$table = SocietyPress::table( 'renewal_reminders' );

		$count = $this->wpdb->get_var( $this->wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE member_id = %d AND reminder_type = %s",
			$member_id,
			$type
		) );

		return $count > 0;
	}

	/**
	 * Get default welcome email template.
	 *
	 * WHY: Public and static so the Settings page can display these same
	 *      defaults in the textarea fields, giving admins a starting point
	 *      to customize rather than staring at an empty box.
	 *
	 * @return string HTML template.
	 */
	public static function get_default_welcome_template(): string {
		return '<h2>Welcome to {{organization_name}}, {{first_name}}!</h2>

<p>We\'re excited to have you as a member. Your <strong>{{tier}}</strong> membership is now active.</p>

<p><strong>Membership Details:</strong></p>
<ul>
	<li>Status: {{status}}</li>
	<li>Joined: {{join_date}}</li>
	<li>Expires: {{expiration_date}}</li>
</ul>

<p>You now have access to your member portal where you can update your profile and connect with other members.</p>

<p style="text-align: center;">
	<a href="{{portal_url}}" class="button">Access Your Portal</a>
</p>

<p>Questions? Reply to this email or contact us at {{admin_email}}.</p>

<p>Best regards,<br>{{organization_name}}</p>';
	}

	/**
	 * Get default renewal reminder template.
	 *
	 * @return string HTML template.
	 */
	public static function get_default_reminder_template(): string {
		return '<h2>Time to Renew Your Membership</h2>

<p>Hi {{first_name}},</p>

<p>Your {{organization_name}} membership expires in <strong>{{days_until_expiration}} days</strong> on {{expiration_date}}.</p>

<p>Renew today to keep your benefits:</p>
<ul>
	<li>Access to member directory</li>
	<li>Member portal access</li>
	<li>Newsletter and updates</li>
	<li>Research resources</li>
</ul>

<p>To renew your membership, please contact us at {{admin_email}}.</p>

<p>Thank you for being a valued member!</p>

<p>Best regards,<br>{{organization_name}}</p>';
	}

	/**
	 * Get default expired notice template.
	 *
	 * @return string HTML template.
	 */
	public static function get_default_expired_template(): string {
		return '<h2>Your Membership Has Expired</h2>

<p>Hi {{first_name}},</p>

<p>Your {{organization_name}} membership expired on {{expiration_date}}.</p>

<p>We\'d love to have you back! Renew your membership to regain access to:</p>
<ul>
	<li>Member directory</li>
	<li>Member portal</li>
	<li>Newsletter and updates</li>
	<li>Research resources</li>
</ul>

<p>To renew, please contact us at {{admin_email}}.</p>

<p>We hope to see you back soon!</p>

<p>Best regards,<br>{{organization_name}}</p>';
	}

	/**
	 * Send event registration confirmation email.
	 *
	 * WHY: Members should get immediate confirmation when they register for
	 *      an event, with all the details they need (event name, date, time, location).
	 *      Only sends for confirmed registrations — waitlist additions don't get this email.
	 *
	 * @param int    $registration_id The registration ID.
	 * @param int    $member_id       The member ID.
	 * @param int    $slot_id         The slot ID they registered for.
	 * @param string $status          Registration status ('confirmed' or 'waitlist').
	 */
	public function send_registration_confirmation( int $registration_id, int $member_id, int $slot_id, string $status ): void {
		// Only send confirmation for confirmed registrations, not waitlist entries
		if ( 'confirmed' !== $status ) {
			return;
		}

		// Get member data
		$member = societypress()->members->get_full( $member_id );
		if ( ! $member || empty( $member['contact']['primary_email'] ) ) {
			return;
		}

		// Check communication preference
		if ( isset( $member['communication_preference'] ) && 'mail' === $member['communication_preference'] ) {
			return;
		}

		// Get slot and event details for the email body
		$slot = societypress()->event_slots->get( $slot_id );
		if ( ! $slot ) {
			return;
		}

		$event_id   = (int) $slot['event_id'];
		$event_title = get_the_title( $event_id );
		$event_date  = SocietyPress_Events::get_event_date( $event_id );
		$event_loc   = SocietyPress_Events::get_event_location( $event_id );
		$time_range  = societypress()->event_slots->format_time_range_from_data( $slot );

		// Build the email using merge tags for the member parts, and direct
		// substitution for the event-specific parts
		$subject = sprintf(
			/* translators: %s: event title */
			__( 'Registration Confirmed: %s', 'societypress' ),
			$event_title
		);

		$message = self::get_default_registration_confirmation_template();

		// Replace event-specific placeholders
		$event_replacements = array(
			'{{event_title}}'    => $event_title,
			'{{event_date}}'     => $event_date ? date_i18n( 'l, F j, Y', strtotime( $event_date ) ) : '',
			'{{event_time}}'     => $time_range,
			'{{event_location}}' => $event_loc ?: __( 'See event page for details', 'societypress' ),
			'{{event_url}}'      => get_permalink( $event_id ),
		);
		$message = str_replace( array_keys( $event_replacements ), array_values( $event_replacements ), $message );

		// Replace standard member merge tags
		$message = $this->replace_merge_tags( $message, $member );

		$this->send_email(
			$member['contact']['primary_email'],
			$subject,
			$message,
			'event_registration'
		);
	}

	/**
	 * Send waitlist promotion email.
	 *
	 * WHY: When a member is auto-promoted from the waitlist because someone
	 *      cancelled, they need to know they now have a confirmed spot —
	 *      otherwise they might miss the event thinking they're still waiting.
	 *
	 * @param int $registration_id The registration ID.
	 * @param int $member_id       The member ID.
	 * @param int $slot_id         The slot ID they were promoted to.
	 */
	public function send_waitlist_promotion_email( int $registration_id, int $member_id, int $slot_id ): void {
		// Get member data
		$member = societypress()->members->get_full( $member_id );
		if ( ! $member || empty( $member['contact']['primary_email'] ) ) {
			return;
		}

		// Check communication preference
		if ( isset( $member['communication_preference'] ) && 'mail' === $member['communication_preference'] ) {
			return;
		}

		// Get slot and event details
		$slot = societypress()->event_slots->get( $slot_id );
		if ( ! $slot ) {
			return;
		}

		$event_id    = (int) $slot['event_id'];
		$event_title = get_the_title( $event_id );
		$event_date  = SocietyPress_Events::get_event_date( $event_id );
		$event_loc   = SocietyPress_Events::get_event_location( $event_id );
		$time_range  = societypress()->event_slots->format_time_range_from_data( $slot );

		$subject = sprintf(
			/* translators: %s: event title */
			__( 'A spot opened up: %s', 'societypress' ),
			$event_title
		);

		$message = self::get_default_waitlist_promotion_template();

		// Replace event-specific placeholders
		$event_replacements = array(
			'{{event_title}}'    => $event_title,
			'{{event_date}}'     => $event_date ? date_i18n( 'l, F j, Y', strtotime( $event_date ) ) : '',
			'{{event_time}}'     => $time_range,
			'{{event_location}}' => $event_loc ?: __( 'See event page for details', 'societypress' ),
			'{{event_url}}'      => get_permalink( $event_id ),
		);
		$message = str_replace( array_keys( $event_replacements ), array_values( $event_replacements ), $message );

		// Replace standard member merge tags
		$message = $this->replace_merge_tags( $message, $member );

		$this->send_email(
			$member['contact']['primary_email'],
			$subject,
			$message,
			'waitlist_promoted'
		);
	}

	/**
	 * Get default registration confirmation email template.
	 *
	 * @return string HTML template.
	 */
	public static function get_default_registration_confirmation_template(): string {
		return '<h2>You\'re Registered!</h2>

<p>Hi {{first_name}},</p>

<p>Your registration for <strong>{{event_title}}</strong> has been confirmed.</p>

<p><strong>Event Details:</strong></p>
<ul>
	<li><strong>Date:</strong> {{event_date}}</li>
	<li><strong>Time:</strong> {{event_time}}</li>
	<li><strong>Location:</strong> {{event_location}}</li>
</ul>

<p style="text-align: center;">
	<a href="{{event_url}}" class="button">View Event Details</a>
</p>

<p>If you need to cancel, you can do so from the event page or your member portal.</p>

<p>See you there!<br>{{organization_name}}</p>';
	}

	/**
	 * Get default waitlist promotion email template.
	 *
	 * @return string HTML template.
	 */
	public static function get_default_waitlist_promotion_template(): string {
		return '<h2>Great News — A Spot Opened Up!</h2>

<p>Hi {{first_name}},</p>

<p>A spot has opened up for <strong>{{event_title}}</strong> and you\'ve been automatically moved from the waitlist to a confirmed registration.</p>

<p><strong>Your Event Details:</strong></p>
<ul>
	<li><strong>Date:</strong> {{event_date}}</li>
	<li><strong>Time:</strong> {{event_time}}</li>
	<li><strong>Location:</strong> {{event_location}}</li>
</ul>

<p style="text-align: center;">
	<a href="{{event_url}}" class="button">View Event Details</a>
</p>

<p>If you can no longer attend, please cancel from the event page so someone else can take the spot.</p>

<p>See you there!<br>{{organization_name}}</p>';
	}

	/**
	 * Capture event meta values BEFORE the post is updated.
	 *
	 * WHY: We need the "before" snapshot to compare against the "after" values.
	 *      WordPress fires pre_post_update before any save_post hooks, so the
	 *      meta in the database still has the old values at this point.
	 *      We only bother capturing for sp_event posts to avoid unnecessary work.
	 *
	 * @param int $post_id Post ID about to be updated.
	 */
	public function capture_old_event_meta( int $post_id ): void {
		// Only capture for events
		if ( 'sp_event' !== get_post_type( $post_id ) ) {
			return;
		}

		// Store the current (soon-to-be-old) values for the fields we care about
		$this->old_event_meta[ $post_id ] = array(
			'date'     => get_post_meta( $post_id, 'sp_event_date', true ),
			'time'     => get_post_meta( $post_id, 'sp_event_time', true ),
			'end_time' => get_post_meta( $post_id, 'sp_event_end_time', true ),
			'location' => get_post_meta( $post_id, 'sp_event_location', true ),
			'address'  => get_post_meta( $post_id, 'sp_event_address', true ),
		);
	}

	/**
	 * Compare old vs new event meta and notify registered members of changes.
	 *
	 * WHY: If an admin changes when or where an event takes place, every member
	 *      who registered needs to know — otherwise they'll show up at the wrong
	 *      time or place. Only fires for published events with actual registrations,
	 *      and only when date, time, or location actually changed.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function notify_event_changes( int $post_id, WP_Post $post ): void {
		// Only notify for published events (drafts don't matter)
		if ( 'publish' !== $post->post_status ) {
			return;
		}

		// Need old meta to compare — if we don't have it, this is a new event
		if ( empty( $this->old_event_meta[ $post_id ] ) ) {
			return;
		}

		// Avoid sending during autosave or quick edit
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$old = $this->old_event_meta[ $post_id ];

		// Get the new (just-saved) meta values
		$new = array(
			'date'     => get_post_meta( $post_id, 'sp_event_date', true ),
			'time'     => get_post_meta( $post_id, 'sp_event_time', true ),
			'end_time' => get_post_meta( $post_id, 'sp_event_end_time', true ),
			'location' => get_post_meta( $post_id, 'sp_event_location', true ),
			'address'  => get_post_meta( $post_id, 'sp_event_address', true ),
		);

		// Build a list of what actually changed — human-readable labels
		$changes = array();
		if ( $old['date'] !== $new['date'] ) {
			$old_formatted = $old['date'] ? date_i18n( 'l, F j, Y', strtotime( $old['date'] ) ) : __( '(none)', 'societypress' );
			$new_formatted = $new['date'] ? date_i18n( 'l, F j, Y', strtotime( $new['date'] ) ) : __( '(none)', 'societypress' );
			$changes[] = sprintf( __( 'Date changed from %1$s to %2$s', 'societypress' ), $old_formatted, $new_formatted );
		}
		if ( $old['time'] !== $new['time'] || $old['end_time'] !== $new['end_time'] ) {
			$old_time = $old['time'] ? date_i18n( 'g:i A', strtotime( $old['time'] ) ) : '';
			$new_time = $new['time'] ? date_i18n( 'g:i A', strtotime( $new['time'] ) ) : '';
			if ( $old['end_time'] ) {
				$old_time .= ' – ' . date_i18n( 'g:i A', strtotime( $old['end_time'] ) );
			}
			if ( $new['end_time'] ) {
				$new_time .= ' – ' . date_i18n( 'g:i A', strtotime( $new['end_time'] ) );
			}
			$changes[] = sprintf( __( 'Time changed from %1$s to %2$s', 'societypress' ), $old_time ?: __( '(none)', 'societypress' ), $new_time ?: __( '(none)', 'societypress' ) );
		}
		if ( $old['location'] !== $new['location'] ) {
			$changes[] = sprintf( __( 'Location changed from "%1$s" to "%2$s"', 'societypress' ), $old['location'] ?: __( '(none)', 'societypress' ), $new['location'] ?: __( '(none)', 'societypress' ) );
		}
		if ( $old['address'] !== $new['address'] ) {
			$changes[] = __( 'Address has been updated', 'societypress' );
		}

		// Nothing meaningful changed — no need to email anyone
		if ( empty( $changes ) ) {
			return;
		}

		// Find all confirmed registrants for this event (across all slots)
		// JOIN: registrations → slots → members → contact to get emails in one query
		$registrations_table = SocietyPress::table( 'event_registrations' );
		$slots_table         = SocietyPress::table( 'event_slots' );
		$members_table       = SocietyPress::table( 'members' );
		$contact_table       = SocietyPress::table( 'member_contact' );

		$registrants = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT DISTINCT r.member_id, m.first_name, m.last_name, mc.primary_email,
				        m.communication_preference
				 FROM {$registrations_table} r
				 INNER JOIN {$slots_table} s ON r.slot_id = s.id
				 INNER JOIN {$members_table} m ON r.member_id = m.id
				 LEFT JOIN {$contact_table} mc ON m.id = mc.member_id
				 WHERE s.event_id = %d
				   AND r.status = 'confirmed'
				   AND mc.primary_email IS NOT NULL
				   AND mc.primary_email != ''",
				$post_id
			),
			ARRAY_A
		);

		// No registrants — nothing to do
		if ( empty( $registrants ) ) {
			return;
		}

		// Build the email content
		$event_title = get_the_title( $post_id );
		$subject     = sprintf(
			/* translators: %s: event title */
			__( 'Event Update: %s', 'societypress' ),
			$event_title
		);

		$template = self::get_default_event_update_template();

		// Build the changes list as HTML
		$changes_html = '<ul>';
		foreach ( $changes as $change ) {
			$changes_html .= '<li>' . esc_html( $change ) . '</li>';
		}
		$changes_html .= '</ul>';

		// Event-specific replacements (same pattern as registration confirmation)
		$event_replacements = array(
			'{{event_title}}'    => $event_title,
			'{{event_date}}'     => $new['date'] ? date_i18n( 'l, F j, Y', strtotime( $new['date'] ) ) : '',
			'{{event_time}}'     => $new['time'] ? date_i18n( 'g:i A', strtotime( $new['time'] ) ) : '',
			'{{event_location}}' => $new['location'] ?: __( 'See event page for details', 'societypress' ),
			'{{event_url}}'      => get_permalink( $post_id ),
			'{{changes_list}}'   => $changes_html,
		);

		// Send to each registrant individually (for personalized merge tags)
		foreach ( $registrants as $registrant ) {
			// Respect communication preference
			if ( ! empty( $registrant['communication_preference'] ) && 'mail' === $registrant['communication_preference'] ) {
				continue;
			}

			// Get full member data for merge tag replacement
			$member = societypress()->members->get_full( (int) $registrant['member_id'] );
			if ( ! $member ) {
				continue;
			}

			// Start with the template, replace event tags, then member tags
			$message = str_replace( array_keys( $event_replacements ), array_values( $event_replacements ), $template );
			$message = $this->replace_merge_tags( $message, $member );

			$this->send_email(
				$registrant['primary_email'],
				$subject,
				$message,
				'event_update'
			);
		}

		// Clean up captured meta now that we're done
		unset( $this->old_event_meta[ $post_id ] );
	}

	/**
	 * Get default event update email template.
	 *
	 * WHY: When an event's date, time, or location changes, registered members
	 *      need clear communication about exactly what changed and what the
	 *      new details are. The {{changes_list}} tag is replaced with a
	 *      bullet list of specific changes.
	 *
	 * @return string HTML template.
	 */
	public static function get_default_event_update_template(): string {
		return '<h2>Event Update: {{event_title}}</h2>

<p>Hi {{first_name}},</p>

<p>An event you\'re registered for has been updated. Here\'s what changed:</p>

{{changes_list}}

<p><strong>Updated Event Details:</strong></p>
<ul>
	<li><strong>Date:</strong> {{event_date}}</li>
	<li><strong>Time:</strong> {{event_time}}</li>
	<li><strong>Location:</strong> {{event_location}}</li>
</ul>

<p style="text-align: center;">
	<a href="{{event_url}}" class="button">View Event Details</a>
</p>

<p>If this change means you can no longer attend, you can cancel your registration from the event page or your member portal.</p>

<p>Thank you for your understanding,<br>{{organization_name}}</p>';
	}
}
