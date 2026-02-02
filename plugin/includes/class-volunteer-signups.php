<?php
/**
 * Volunteer Signups Management
 *
 * Handles member sign-ups for volunteer opportunities, including waitlist
 * management, completion tracking, and hours logging.
 *
 * WHY: Follows the event_registrations pattern for consistency. Tracks volunteer
 *      hours for recognition programs and provides automatic waitlist promotion
 *      when spots open up.
 *
 * @package SocietyPress
 * @since 0.54d
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SocietyPress_Volunteer_Signups
 *
 * Manages volunteer signups and waitlist.
 */
class SocietyPress_Volunteer_Signups {

    /**
     * WordPress database object.
     *
     * @var wpdb
     */
    private $wpdb;

    /**
     * Signups table name.
     *
     * @var string
     */
    private string $table;

    /**
     * Opportunities table name.
     *
     * @var string
     */
    private string $opportunities_table;

    /**
     * Members table name.
     *
     * @var string
     */
    private string $members_table;

    /**
     * Member contact table name.
     *
     * @var string
     */
    private string $contact_table;

    /**
     * Valid signup statuses.
     *
     * WHY: Tracks the full lifecycle of a volunteer commitment.
     *      - confirmed: Actively signed up
     *      - waitlist: Waiting for a spot to open
     *      - completed: Finished the volunteer work
     *      - cancelled: Withdrew from commitment
     *      - no_show: Didn't show up when expected
     */
    public const STATUSES = array( 'confirmed', 'waitlist', 'completed', 'cancelled', 'no_show' );

    /**
     * Constructor.
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb                = $wpdb;
        $this->table               = SocietyPress::table( 'volunteer_signups' );
        $this->opportunities_table = SocietyPress::table( 'volunteer_opportunities' );
        $this->members_table       = SocietyPress::table( 'members' );
        $this->contact_table       = SocietyPress::table( 'member_contact' );
    }

    /**
     * Sign up a member for a volunteer opportunity.
     *
     * WHY: Main entry point for volunteer registration. Handles capacity
     *      checking and automatically puts members on waitlist if full.
     *
     * @param int      $opportunity_id Opportunity ID.
     * @param int      $member_id      Member ID.
     * @param int|null $registered_by  Admin member ID if manual, null if self.
     * @param string   $notes          Optional notes.
     * @return array Result with 'success', 'status', and 'message'.
     */
    public function signup( int $opportunity_id, int $member_id, ?int $registered_by = null, string $notes = '' ): array {
        // Check if already signed up
        $existing = $this->get_signup( $opportunity_id, $member_id );
        if ( $existing ) {
            if ( $existing['status'] === 'cancelled' ) {
                // Re-signup cancelled registration
                return $this->reactivate_signup( $existing['id'], $registered_by, $notes );
            }
            return array(
                'success' => false,
                'status'  => $existing['status'],
                'message' => __( 'You are already signed up for this opportunity.', 'societypress' ),
            );
        }

        // Check opportunity exists and is open
        $opportunities = societypress()->volunteer_opportunities;
        $opportunity   = $opportunities->get( $opportunity_id );

        if ( ! $opportunity || ! $opportunity['is_active'] ) {
            return array(
                'success' => false,
                'status'  => null,
                'message' => __( 'This volunteer opportunity is not available.', 'societypress' ),
            );
        }

        if ( ! in_array( $opportunity['status'], array( 'open', 'filled' ), true ) ) {
            return array(
                'success' => false,
                'status'  => null,
                'message' => __( 'This opportunity is no longer accepting signups.', 'societypress' ),
            );
        }

        // Check capacity
        $has_capacity = $opportunities->has_capacity( $opportunity_id );
        $status       = $has_capacity ? 'confirmed' : 'waitlist';

        // Insert signup
        $insert_data = array(
            'opportunity_id' => $opportunity_id,
            'member_id'      => $member_id,
            'status'         => $status,
            'signed_up_at'   => current_time( 'mysql' ),
            'confirmed_at'   => $status === 'confirmed' ? current_time( 'mysql' ) : null,
            'registered_by'  => $registered_by,
            'notes'          => $notes ? sanitize_textarea_field( $notes ) : null,
        );

        $result = $this->wpdb->insert( $this->table, $insert_data );

        if ( false === $result ) {
            return array(
                'success' => false,
                'status'  => null,
                'message' => __( 'Signup failed. Please try again.', 'societypress' ),
            );
        }

        $signup_id = (int) $this->wpdb->insert_id;

        // Update opportunity status if now full
        $opportunities->check_and_update_status( $opportunity_id );

        // Fire action hook for notifications
        do_action( 'societypress_volunteer_signup', $signup_id, $member_id, $opportunity_id );

        $message = $status === 'confirmed'
            ? __( 'You have signed up for this volunteer opportunity.', 'societypress' )
            : __( 'This opportunity is full. You have been added to the waitlist.', 'societypress' );

        return array(
            'success'   => true,
            'status'    => $status,
            'signup_id' => $signup_id,
            'message'   => $message,
        );
    }

    /**
     * Reactivate a cancelled signup.
     *
     * @param int      $signup_id     Signup ID.
     * @param int|null $registered_by Admin who reactivated.
     * @param string   $notes         Optional notes.
     * @return array Result array.
     */
    private function reactivate_signup( int $signup_id, ?int $registered_by, string $notes ): array {
        $signup = $this->get( $signup_id );
        if ( ! $signup ) {
            return array(
                'success' => false,
                'status'  => null,
                'message' => __( 'Signup not found.', 'societypress' ),
            );
        }

        // Check capacity
        $opportunities = societypress()->volunteer_opportunities;
        $has_capacity  = $opportunities->has_capacity( $signup['opportunity_id'] );
        $status        = $has_capacity ? 'confirmed' : 'waitlist';

        $update_data = array(
            'status'        => $status,
            'signed_up_at'  => current_time( 'mysql' ),
            'confirmed_at'  => $status === 'confirmed' ? current_time( 'mysql' ) : null,
            'cancelled_at'  => null,
            'registered_by' => $registered_by,
        );

        if ( $notes ) {
            $update_data['notes'] = sanitize_textarea_field( $notes );
        }

        $this->wpdb->update(
            $this->table,
            $update_data,
            array( 'id' => $signup_id )
        );

        // Update opportunity status
        $opportunities->check_and_update_status( $signup['opportunity_id'] );

        $message = $status === 'confirmed'
            ? __( 'You have signed up for this volunteer opportunity.', 'societypress' )
            : __( 'This opportunity is full. You have been added to the waitlist.', 'societypress' );

        return array(
            'success'   => true,
            'status'    => $status,
            'signup_id' => $signup_id,
            'message'   => $message,
        );
    }

    /**
     * Cancel a signup.
     *
     * WHY: Allows members to withdraw. Automatically promotes next waitlisted person.
     *
     * @param int    $signup_id Signup ID.
     * @param string $reason    Optional cancellation reason.
     * @return array Result array.
     */
    public function cancel( int $signup_id, string $reason = '' ): array {
        $signup = $this->get( $signup_id );

        if ( ! $signup ) {
            return array(
                'success' => false,
                'message' => __( 'Signup not found.', 'societypress' ),
            );
        }

        if ( $signup['status'] === 'cancelled' ) {
            return array(
                'success' => false,
                'message' => __( 'This signup was already cancelled.', 'societypress' ),
            );
        }

        $was_confirmed   = $signup['status'] === 'confirmed';
        $opportunity_id  = $signup['opportunity_id'];

        // Update notes with cancellation reason
        $notes = $signup['notes'];
        if ( $reason ) {
            $notes = $notes ? $notes . "\n\nCancellation: " . $reason : 'Cancellation: ' . $reason;
        }

        $this->wpdb->update(
            $this->table,
            array(
                'status'       => 'cancelled',
                'cancelled_at' => current_time( 'mysql' ),
                'notes'        => $notes,
            ),
            array( 'id' => $signup_id )
        );

        // Fire cancellation hook
        do_action( 'societypress_volunteer_cancelled', $signup_id, $signup['member_id'], $opportunity_id );

        // If was confirmed, promote from waitlist
        if ( $was_confirmed ) {
            $this->process_waitlist( $opportunity_id );
        }

        // Update opportunity status (might reopen if was filled)
        societypress()->volunteer_opportunities->check_and_update_status( $opportunity_id );

        return array(
            'success' => true,
            'message' => __( 'Your signup has been cancelled.', 'societypress' ),
        );
    }

    /**
     * Mark a signup as completed with hours logged.
     *
     * WHY: Tracks volunteer hours for recognition programs and reporting.
     *
     * @param int        $signup_id Signup ID.
     * @param float|null $hours     Hours worked (optional).
     * @param string     $notes     Optional completion notes.
     * @return array Result array.
     */
    public function complete( int $signup_id, ?float $hours = null, string $notes = '' ): array {
        $signup = $this->get( $signup_id );

        if ( ! $signup ) {
            return array(
                'success' => false,
                'message' => __( 'Signup not found.', 'societypress' ),
            );
        }

        if ( $signup['status'] !== 'confirmed' ) {
            return array(
                'success' => false,
                'message' => __( 'Only confirmed signups can be marked complete.', 'societypress' ),
            );
        }

        $update_notes = $signup['notes'];
        if ( $notes ) {
            $update_notes = $update_notes ? $update_notes . "\n\nCompletion: " . $notes : 'Completion: ' . $notes;
        }

        $this->wpdb->update(
            $this->table,
            array(
                'status'       => 'completed',
                'completed_at' => current_time( 'mysql' ),
                'hours_logged' => $hours,
                'notes'        => $update_notes,
            ),
            array( 'id' => $signup_id )
        );

        // Fire completion hook
        do_action( 'societypress_volunteer_completed', $signup_id, $signup['member_id'], $hours );

        return array(
            'success' => true,
            'message' => __( 'Volunteer work marked as completed.', 'societypress' ),
        );
    }

    /**
     * Mark a signup as no-show.
     *
     * WHY: Tracks reliability for future volunteer coordination.
     *
     * @param int    $signup_id Signup ID.
     * @param string $notes     Optional notes.
     * @return array Result array.
     */
    public function mark_no_show( int $signup_id, string $notes = '' ): array {
        $signup = $this->get( $signup_id );

        if ( ! $signup ) {
            return array(
                'success' => false,
                'message' => __( 'Signup not found.', 'societypress' ),
            );
        }

        $update_notes = $signup['notes'];
        if ( $notes ) {
            $update_notes = $update_notes ? $update_notes . "\n\nNo-show: " . $notes : 'No-show: ' . $notes;
        }

        $this->wpdb->update(
            $this->table,
            array(
                'status' => 'no_show',
                'notes'  => $update_notes,
            ),
            array( 'id' => $signup_id )
        );

        return array(
            'success' => true,
            'message' => __( 'Marked as no-show.', 'societypress' ),
        );
    }

    /**
     * Process the waitlist when a spot opens.
     *
     * WHY: Automatically promotes the first waitlisted person when a confirmed
     *      volunteer cancels. Fair first-come, first-served approach.
     *
     * @param int $opportunity_id Opportunity ID.
     * @return int|false Promoted signup ID, or false if no waitlist.
     */
    public function process_waitlist( int $opportunity_id ) {
        // Check if there's capacity
        $opportunities = societypress()->volunteer_opportunities;
        if ( ! $opportunities->has_capacity( $opportunity_id ) ) {
            return false;
        }

        // Get first waitlisted signup
        $waitlisted = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table}
                 WHERE opportunity_id = %d AND status = 'waitlist'
                 ORDER BY signed_up_at ASC
                 LIMIT 1",
                $opportunity_id
            ),
            ARRAY_A
        );

        if ( ! $waitlisted ) {
            return false;
        }

        // Promote to confirmed
        $this->wpdb->update(
            $this->table,
            array(
                'status'       => 'confirmed',
                'confirmed_at' => current_time( 'mysql' ),
            ),
            array( 'id' => $waitlisted['id'] )
        );

        // Fire hook for notification
        do_action( 'societypress_volunteer_waitlist_promoted', $waitlisted['id'], $waitlisted['member_id'], $opportunity_id );

        return (int) $waitlisted['id'];
    }

    /**
     * Get a single signup by ID.
     *
     * @param int $signup_id Signup ID.
     * @return array|null Signup data or null.
     */
    public function get( int $signup_id ): ?array {
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE id = %d",
                $signup_id
            ),
            ARRAY_A
        );

        return $row ?: null;
    }

    /**
     * Get a member's signup for a specific opportunity.
     *
     * @param int $opportunity_id Opportunity ID.
     * @param int $member_id      Member ID.
     * @return array|null Signup data or null.
     */
    public function get_signup( int $opportunity_id, int $member_id ): ?array {
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table}
                 WHERE opportunity_id = %d AND member_id = %d",
                $opportunity_id,
                $member_id
            ),
            ARRAY_A
        );

        return $row ?: null;
    }

    /**
     * Check if a member is signed up for an opportunity (any active status).
     *
     * @param int $opportunity_id Opportunity ID.
     * @param int $member_id      Member ID.
     * @return string|false Status ('confirmed' or 'waitlist'), or false.
     */
    public function is_member_signed_up( int $opportunity_id, int $member_id ) {
        $signup = $this->get_signup( $opportunity_id, $member_id );

        if ( ! $signup || in_array( $signup['status'], array( 'cancelled', 'completed', 'no_show' ), true ) ) {
            return false;
        }

        return $signup['status'];
    }

    /**
     * Get all signups for an opportunity.
     *
     * @param int    $opportunity_id Opportunity ID.
     * @param string $status         Optional status filter.
     * @return array Array of signups with member info.
     */
    public function get_opportunity_signups( int $opportunity_id, string $status = '' ): array {
        $where_status = $status ? $this->wpdb->prepare( 'AND s.status = %s', $status ) : '';

        $results = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT s.*, m.first_name, m.last_name, mc.primary_email
                 FROM {$this->table} s
                 INNER JOIN {$this->members_table} m ON s.member_id = m.id
                 LEFT JOIN {$this->contact_table} mc ON m.id = mc.member_id
                 WHERE s.opportunity_id = %d {$where_status}
                 ORDER BY
                     CASE s.status
                         WHEN 'confirmed' THEN 1
                         WHEN 'waitlist' THEN 2
                         WHEN 'completed' THEN 3
                         ELSE 4
                     END,
                     s.signed_up_at ASC",
                $opportunity_id
            ),
            ARRAY_A
        );

        return $results ?: array();
    }

    /**
     * Get a member's active volunteer commitments.
     *
     * WHY: Shows members what they've signed up for in their portal.
     *
     * @param int $member_id Member ID.
     * @return array Array of active signups with opportunity info.
     */
    public function get_member_active( int $member_id ): array {
        $results = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT s.*, o.title, o.location, o.opportunity_type, o.date, o.day_of_week,
                        o.start_time, o.end_time, o.committee_id,
                        c.name as committee_name
                 FROM {$this->table} s
                 INNER JOIN {$this->opportunities_table} o ON s.opportunity_id = o.id
                 LEFT JOIN " . SocietyPress::table( 'committees' ) . " c ON o.committee_id = c.id
                 WHERE s.member_id = %d
                   AND s.status IN ('confirmed', 'waitlist')
                   AND o.is_active = 1
                   AND (o.opportunity_type != 'one_time' OR o.date >= CURDATE())
                 ORDER BY o.date ASC, o.start_time ASC, s.signed_up_at ASC",
                $member_id
            ),
            ARRAY_A
        );

        return $results ?: array();
    }

    /**
     * Get a member's volunteer history.
     *
     * @param int $member_id Member ID.
     * @param int $limit     Max records to return.
     * @return array Array of past volunteer signups.
     */
    public function get_member_history( int $member_id, int $limit = 20 ): array {
        $results = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT s.*, o.title, o.location, o.opportunity_type, o.date,
                        o.start_time, o.end_time,
                        c.name as committee_name
                 FROM {$this->table} s
                 INNER JOIN {$this->opportunities_table} o ON s.opportunity_id = o.id
                 LEFT JOIN " . SocietyPress::table( 'committees' ) . " c ON o.committee_id = c.id
                 WHERE s.member_id = %d
                   AND s.status IN ('completed', 'no_show')
                 ORDER BY s.completed_at DESC, s.signed_up_at DESC
                 LIMIT %d",
                $member_id,
                $limit
            ),
            ARRAY_A
        );

        return $results ?: array();
    }

    /**
     * Get a member's total volunteer hours for a year.
     *
     * WHY: Used for recognition programs, annual reports, and awards.
     *
     * @param int      $member_id Member ID.
     * @param int|null $year      Year to calculate (null = current year).
     * @return float Total hours.
     */
    public function get_member_hours( int $member_id, ?int $year = null ): float {
        $year = $year ?: (int) date( 'Y' );

        $total = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT SUM(hours_logged) FROM {$this->table}
                 WHERE member_id = %d
                   AND status = 'completed'
                   AND YEAR(completed_at) = %d",
                $member_id,
                $year
            )
        );

        return (float) ( $total ?? 0 );
    }

    /**
     * Get volunteer hour leaderboard.
     *
     * WHY: Recognition and gamification - shows top volunteers.
     *
     * @param int      $limit Number of top volunteers.
     * @param int|null $year  Year (null = current).
     * @return array Array of members with their total hours.
     */
    public function get_hours_leaderboard( int $limit = 10, ?int $year = null ): array {
        $year = $year ?: (int) date( 'Y' );

        $results = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT m.id, m.first_name, m.last_name, SUM(s.hours_logged) as total_hours
                 FROM {$this->table} s
                 INNER JOIN {$this->members_table} m ON s.member_id = m.id
                 WHERE s.status = 'completed'
                   AND YEAR(s.completed_at) = %d
                   AND s.hours_logged IS NOT NULL
                 GROUP BY m.id
                 ORDER BY total_hours DESC
                 LIMIT %d",
                $year,
                $limit
            ),
            ARRAY_A
        );

        return $results ?: array();
    }

    /**
     * Get waitlist position for a signup.
     *
     * @param int $signup_id Signup ID.
     * @return int|false Position (1-based) or false if not waitlisted.
     */
    public function get_waitlist_position( int $signup_id ) {
        $signup = $this->get( $signup_id );

        if ( ! $signup || $signup['status'] !== 'waitlist' ) {
            return false;
        }

        $position = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) + 1 FROM {$this->table}
                 WHERE opportunity_id = %d
                   AND status = 'waitlist'
                   AND signed_up_at < %s",
                $signup['opportunity_id'],
                $signup['signed_up_at']
            )
        );

        return (int) $position;
    }

    /**
     * Get count of waitlisted signups for an opportunity.
     *
     * @param int $opportunity_id Opportunity ID.
     * @return int Waitlist count.
     */
    public function get_waitlist_count( int $opportunity_id ): int {
        $count = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table}
                 WHERE opportunity_id = %d AND status = 'waitlist'",
                $opportunity_id
            )
        );

        return (int) $count;
    }

    /**
     * Admin override: Force sign up a member (bypass capacity).
     *
     * WHY: Admins may need to add someone for special circumstances.
     *
     * @param int    $opportunity_id Opportunity ID.
     * @param int    $member_id      Member ID.
     * @param int    $registered_by  Admin member ID.
     * @param string $notes          Reason for override.
     * @return array Result array.
     */
    public function admin_signup( int $opportunity_id, int $member_id, int $registered_by, string $notes = '' ): array {
        // Check if already signed up
        $existing = $this->get_signup( $opportunity_id, $member_id );
        if ( $existing && $existing['status'] !== 'cancelled' ) {
            return array(
                'success' => false,
                'message' => __( 'Member is already signed up for this opportunity.', 'societypress' ),
            );
        }

        $admin_notes = $notes ?: __( 'Admin override registration', 'societypress' );

        if ( $existing && $existing['status'] === 'cancelled' ) {
            // Reactivate with confirmed status
            $this->wpdb->update(
                $this->table,
                array(
                    'status'        => 'confirmed',
                    'signed_up_at'  => current_time( 'mysql' ),
                    'confirmed_at'  => current_time( 'mysql' ),
                    'cancelled_at'  => null,
                    'registered_by' => $registered_by,
                    'notes'         => $admin_notes,
                ),
                array( 'id' => $existing['id'] )
            );
            $signup_id = $existing['id'];
        } else {
            // Create new with confirmed status (bypass capacity check)
            $this->wpdb->insert(
                $this->table,
                array(
                    'opportunity_id' => $opportunity_id,
                    'member_id'      => $member_id,
                    'status'         => 'confirmed',
                    'signed_up_at'   => current_time( 'mysql' ),
                    'confirmed_at'   => current_time( 'mysql' ),
                    'registered_by'  => $registered_by,
                    'notes'          => $admin_notes,
                )
            );
            $signup_id = (int) $this->wpdb->insert_id;
        }

        return array(
            'success'   => true,
            'signup_id' => $signup_id,
            'message'   => __( 'Member has been signed up.', 'societypress' ),
        );
    }

    /**
     * Delete all signups for a member.
     *
     * WHY: Called when a member is deleted from the system.
     *
     * @param int $member_id Member ID.
     * @return bool Success or failure.
     */
    public function delete_by_member( int $member_id ): bool {
        $result = $this->wpdb->delete(
            $this->table,
            array( 'member_id' => $member_id ),
            array( '%d' )
        );

        return false !== $result;
    }

    /**
     * Get status label for display.
     *
     * @param string $status Status code.
     * @return string Human-readable label.
     */
    public static function get_status_label( string $status ): string {
        $labels = array(
            'confirmed' => __( 'Confirmed', 'societypress' ),
            'waitlist'  => __( 'Waitlist', 'societypress' ),
            'completed' => __( 'Completed', 'societypress' ),
            'cancelled' => __( 'Cancelled', 'societypress' ),
            'no_show'   => __( 'No-Show', 'societypress' ),
        );

        return $labels[ $status ] ?? $status;
    }
}
