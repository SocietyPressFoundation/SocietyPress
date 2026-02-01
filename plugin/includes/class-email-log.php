<?php
/**
 * Email Log Management
 *
 * Logs all outgoing emails for admin review and debugging. Tracks sent,
 * blocked (dev mode), and failed emails so admins can verify what
 * communications are going out to members.
 *
 * WHY: Genealogical societies need visibility into member communications.
 *      Admins can verify emails are correct before enabling production
 *      sending, troubleshoot delivery issues, and maintain records.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SocietyPress_Email_Log
 *
 * Manages email logging and retrieval.
 */
class SocietyPress_Email_Log {

	/**
	 * WordPress database object.
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Table name.
	 *
	 * @var string
	 */
	private string $table;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->table = SocietyPress::table( 'email_log' );
	}

	/**
	 * Log an email.
	 *
	 * WHY: Records all email attempts for admin review. Called from the
	 *      wp_mail filter to capture emails before they're sent.
	 *
	 * @param string      $recipient   Email recipient(s).
	 * @param string      $subject     Email subject.
	 * @param string      $body        Email body content.
	 * @param string      $headers     Email headers.
	 * @param string      $status      Status: sent, blocked, failed, pending.
	 * @param string|null $error       Error message if failed.
	 * @param int|null    $member_id   Associated member ID if known.
	 * @param string|null $email_type  Type of email (welcome, reminder, etc.).
	 * @return int|false The log entry ID or false on failure.
	 */
	public function log(
		string $recipient,
		string $subject,
		string $body,
		string $headers = '',
		string $status = 'pending',
		?string $error = null,
		?int $member_id = null,
		?string $email_type = null
	) {
		$data = array(
			'recipient'     => $recipient,
			'subject'       => $subject,
			'body'          => $body,
			'headers'       => $headers,
			'status'        => $status,
			'error_message' => $error,
			'member_id'     => $member_id,
			'email_type'    => $email_type,
			'created_at'    => current_time( 'mysql' ),
		);

		if ( $status === 'sent' ) {
			$data['sent_at'] = current_time( 'mysql' );
		}

		$result = $this->wpdb->insert(
			$this->table,
			$data,
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s' )
		);

		if ( $result === false ) {
			return false;
		}

		return (int) $this->wpdb->insert_id;
	}

	/**
	 * Update an email log entry status.
	 *
	 * WHY: After logging a pending email, we update its status based on
	 *      whether sending succeeded or failed.
	 *
	 * @param int         $log_id The log entry ID.
	 * @param string      $status New status.
	 * @param string|null $error  Error message if failed.
	 * @return bool True on success.
	 */
	public function update_status( int $log_id, string $status, ?string $error = null ): bool {
		$data = array( 'status' => $status );
		$formats = array( '%s' );

		if ( $status === 'sent' ) {
			$data['sent_at'] = current_time( 'mysql' );
			$formats[] = '%s';
		}

		if ( $error !== null ) {
			$data['error_message'] = $error;
			$formats[] = '%s';
		}

		$result = $this->wpdb->update(
			$this->table,
			$data,
			array( 'id' => $log_id ),
			$formats,
			array( '%d' )
		);

		return $result !== false;
	}

	/**
	 * Get a single log entry.
	 *
	 * @param int $log_id The log entry ID.
	 * @return array|null Log data or null.
	 */
	public function get( int $log_id ): ?array {
		$row = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE id = %d",
				$log_id
			),
			ARRAY_A
		);

		return $row ?: null;
	}

	/**
	 * Get paginated email log entries.
	 *
	 * WHY: Admin page needs paginated list of emails with filtering.
	 *
	 * @param array $args Query arguments.
	 * @return array Array with 'items' and 'total' keys.
	 */
	public function get_list( array $args = array() ): array {
		$defaults = array(
			'status'     => '',
			'email_type' => '',
			'search'     => '',
			'per_page'   => 20,
			'page'       => 1,
			'orderby'    => 'created_at',
			'order'      => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		// Build WHERE clause
		$where = array( '1=1' );
		$values = array();

		if ( ! empty( $args['status'] ) ) {
			$where[] = 'status = %s';
			$values[] = $args['status'];
		}

		if ( ! empty( $args['email_type'] ) ) {
			$where[] = 'email_type = %s';
			$values[] = $args['email_type'];
		}

		if ( ! empty( $args['search'] ) ) {
			$where[] = '(recipient LIKE %s OR subject LIKE %s)';
			$search = '%' . $this->wpdb->esc_like( $args['search'] ) . '%';
			$values[] = $search;
			$values[] = $search;
		}

		$where_sql = implode( ' AND ', $where );

		// Sanitize orderby
		$allowed_orderby = array( 'id', 'recipient', 'subject', 'status', 'created_at', 'sent_at' );
		$orderby = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at';
		$order = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

		// Get total count
		$count_sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$where_sql}";
		if ( ! empty( $values ) ) {
			$count_sql = $this->wpdb->prepare( $count_sql, ...$values );
		}
		$total = (int) $this->wpdb->get_var( $count_sql );

		// Get items
		$offset = ( $args['page'] - 1 ) * $args['per_page'];
		$items_sql = "SELECT * FROM {$this->table} WHERE {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
		$values[] = $args['per_page'];
		$values[] = $offset;

		$items = $this->wpdb->get_results(
			$this->wpdb->prepare( $items_sql, ...$values ),
			ARRAY_A
		);

		return array(
			'items' => $items ?: array(),
			'total' => $total,
		);
	}

	/**
	 * Get recent emails for dashboard widget.
	 *
	 * @param int $limit Number of entries to return.
	 * @return array Recent email log entries.
	 */
	public function get_recent( int $limit = 10 ): array {
		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table}
				 ORDER BY created_at DESC
				 LIMIT %d",
				$limit
			),
			ARRAY_A
		);

		return $results ?: array();
	}

	/**
	 * Get email statistics.
	 *
	 * WHY: Dashboard overview of email activity.
	 *
	 * @param int $days Number of days to look back.
	 * @return array Statistics array.
	 */
	public function get_stats( int $days = 30 ): array {
		$since = date( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		$stats = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT status, COUNT(*) as count
				 FROM {$this->table}
				 WHERE created_at >= %s
				 GROUP BY status",
				$since
			),
			ARRAY_A
		);

		$result = array(
			'sent'    => 0,
			'blocked' => 0,
			'failed'  => 0,
			'pending' => 0,
			'total'   => 0,
		);

		foreach ( $stats as $row ) {
			$result[ $row['status'] ] = (int) $row['count'];
			$result['total'] += (int) $row['count'];
		}

		return $result;
	}

	/**
	 * Delete old log entries.
	 *
	 * WHY: Prevents the log table from growing indefinitely.
	 *      Called periodically via cron or manually.
	 *
	 * @param int $days Delete entries older than this many days.
	 * @return int Number of entries deleted.
	 */
	public function cleanup( int $days = 90 ): int {
		$cutoff = date( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		$result = $this->wpdb->query(
			$this->wpdb->prepare(
				"DELETE FROM {$this->table} WHERE created_at < %s",
				$cutoff
			)
		);

		return $result !== false ? $result : 0;
	}

	/**
	 * Get distinct email types for filtering.
	 *
	 * @return array List of email types.
	 */
	public function get_email_types(): array {
		$results = $this->wpdb->get_col(
			"SELECT DISTINCT email_type FROM {$this->table}
			 WHERE email_type IS NOT NULL AND email_type != ''
			 ORDER BY email_type ASC"
		);

		return $results ?: array();
	}
}
