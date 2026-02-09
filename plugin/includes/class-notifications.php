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
}
