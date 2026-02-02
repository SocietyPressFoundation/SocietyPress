<?php
/**
 * Volunteer Opportunities Management
 *
 * CRUD operations for volunteer opportunities posted by committee chairs and admins.
 *
 * WHY: Societies run on volunteers. This system allows committee chairs to post
 *      their volunteer needs (like job listings), making it easy for members
 *      to find ways to contribute to the organization.
 *
 * @package SocietyPress
 * @since 0.54d
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SocietyPress_Volunteer_Opportunities
 *
 * Handles volunteer opportunity CRUD operations.
 */
class SocietyPress_Volunteer_Opportunities {

    /**
     * WordPress database object.
     *
     * @var wpdb
     */
    private $wpdb;

    /**
     * Opportunities table name.
     *
     * @var string
     */
    private string $table;

    /**
     * Signups table name.
     *
     * @var string
     */
    private string $signups_table;

    /**
     * Committees table name.
     *
     * @var string
     */
    private string $committees_table;

    /**
     * Members table name.
     *
     * @var string
     */
    private string $members_table;

    /**
     * Valid opportunity types.
     *
     * WHY: Different scheduling patterns for different volunteer needs.
     *      - one_time: A single event on a specific date
     *      - recurring: Repeats on a certain day each week
     *      - ongoing: No specific schedule, always available
     */
    public const TYPES = array( 'one_time', 'recurring', 'ongoing' );

    /**
     * Valid statuses.
     *
     * WHY: Tracks opportunity lifecycle.
     *      - open: Accepting signups
     *      - filled: Reached capacity
     *      - closed: Manually closed, no longer needed
     *      - cancelled: Event/need cancelled
     */
    public const STATUSES = array( 'open', 'filled', 'closed', 'cancelled' );

    /**
     * Days of week for recurring opportunities.
     *
     * WHY: 0 = Sunday through 6 = Saturday, matching PHP date('w').
     */
    public const DAYS_OF_WEEK = array(
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    );

    /**
     * Constructor.
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb             = $wpdb;
        $this->table            = SocietyPress::table( 'volunteer_opportunities' );
        $this->signups_table    = SocietyPress::table( 'volunteer_signups' );
        $this->committees_table = SocietyPress::table( 'committees' );
        $this->members_table    = SocietyPress::table( 'members' );
    }

    /**
     * Create a new volunteer opportunity.
     *
     * @param array $data Opportunity data.
     * @return int|false New opportunity ID or false on failure.
     */
    public function create( array $data ) {
        // Validate required fields
        if ( empty( $data['title'] ) || empty( $data['posted_by'] ) ) {
            return false;
        }

        // Validate opportunity type
        $type = $data['opportunity_type'] ?? 'one_time';
        if ( ! in_array( $type, self::TYPES, true ) ) {
            $type = 'one_time';
        }

        $insert_data = array(
            'committee_id'      => isset( $data['committee_id'] ) ? absint( $data['committee_id'] ) : null,
            'title'             => sanitize_text_field( $data['title'] ),
            'description'       => isset( $data['description'] ) ? wp_kses_post( $data['description'] ) : null,
            'location'          => isset( $data['location'] ) ? sanitize_text_field( $data['location'] ) : null,
            'opportunity_type'  => $type,
            'date'              => isset( $data['date'] ) && $data['date'] ? $data['date'] : null,
            'day_of_week'       => isset( $data['day_of_week'] ) ? absint( $data['day_of_week'] ) : null,
            'start_time'        => isset( $data['start_time'] ) && $data['start_time'] ? $data['start_time'] : null,
            'end_time'          => isset( $data['end_time'] ) && $data['end_time'] ? $data['end_time'] : null,
            'capacity'          => isset( $data['capacity'] ) && $data['capacity'] > 0 ? absint( $data['capacity'] ) : null,
            'skills_needed'     => isset( $data['skills_needed'] ) ? sanitize_textarea_field( $data['skills_needed'] ) : null,
            'contact_member_id' => isset( $data['contact_member_id'] ) ? absint( $data['contact_member_id'] ) : null,
            'posted_by'         => absint( $data['posted_by'] ),
            'status'            => 'open',
            'is_active'         => isset( $data['is_active'] ) ? ( $data['is_active'] ? 1 : 0 ) : 1,
            'sort_order'        => isset( $data['sort_order'] ) ? absint( $data['sort_order'] ) : 0,
        );

        $result = $this->wpdb->insert(
            $this->table,
            $insert_data
        );

        if ( false === $result ) {
            return false;
        }

        $opportunity_id = (int) $this->wpdb->insert_id;

        // Fire action hook for notifications, etc.
        do_action( 'societypress_volunteer_opportunity_created', $opportunity_id, $insert_data );

        return $opportunity_id;
    }

    /**
     * Update an existing opportunity.
     *
     * @param int   $id   Opportunity ID.
     * @param array $data Data to update.
     * @return bool Success or failure.
     */
    public function update( int $id, array $data ): bool {
        $existing = $this->get( $id );
        if ( ! $existing ) {
            return false;
        }

        $allowed = array(
            'committee_id', 'title', 'description', 'location', 'opportunity_type',
            'date', 'day_of_week', 'start_time', 'end_time', 'capacity',
            'skills_needed', 'contact_member_id', 'status', 'is_active', 'sort_order',
        );

        $update = array();

        foreach ( $allowed as $field ) {
            if ( array_key_exists( $field, $data ) ) {
                switch ( $field ) {
                    case 'title':
                    case 'location':
                        $update[ $field ] = sanitize_text_field( $data[ $field ] );
                        break;
                    case 'description':
                        $update['description'] = wp_kses_post( $data['description'] );
                        break;
                    case 'skills_needed':
                        $update['skills_needed'] = sanitize_textarea_field( $data['skills_needed'] );
                        break;
                    case 'opportunity_type':
                        if ( in_array( $data['opportunity_type'], self::TYPES, true ) ) {
                            $update['opportunity_type'] = $data['opportunity_type'];
                        }
                        break;
                    case 'status':
                        if ( in_array( $data['status'], self::STATUSES, true ) ) {
                            $update['status'] = $data['status'];
                        }
                        break;
                    case 'committee_id':
                    case 'day_of_week':
                    case 'capacity':
                    case 'contact_member_id':
                    case 'sort_order':
                        $update[ $field ] = $data[ $field ] ? absint( $data[ $field ] ) : null;
                        break;
                    case 'date':
                    case 'start_time':
                    case 'end_time':
                        $update[ $field ] = $data[ $field ] ?: null;
                        break;
                    case 'is_active':
                        $update['is_active'] = $data['is_active'] ? 1 : 0;
                        break;
                }
            }
        }

        if ( empty( $update ) ) {
            return false;
        }

        $result = $this->wpdb->update(
            $this->table,
            $update,
            array( 'id' => $id ),
            null,
            array( '%d' )
        );

        return false !== $result;
    }

    /**
     * Delete or close an opportunity.
     *
     * WHY: Soft delete (close) preserves history. Hard delete for cleanup.
     *
     * @param int  $id   Opportunity ID.
     * @param bool $hard Whether to permanently delete.
     * @return bool Success or failure.
     */
    public function delete( int $id, bool $hard = false ): bool {
        if ( $hard ) {
            // Delete signups first
            $this->wpdb->delete(
                $this->signups_table,
                array( 'opportunity_id' => $id ),
                array( '%d' )
            );

            // Then delete opportunity
            $result = $this->wpdb->delete(
                $this->table,
                array( 'id' => $id ),
                array( '%d' )
            );

            return false !== $result;
        }

        // Soft delete - mark as closed and inactive
        return $this->update( $id, array( 'status' => 'closed', 'is_active' => false ) );
    }

    /**
     * Get a single opportunity by ID.
     *
     * @param int $id Opportunity ID.
     * @return array|null Opportunity data with committee and contact info, or null.
     */
    public function get( int $id ): ?array {
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT o.*,
                        c.name as committee_name,
                        c.slug as committee_slug,
                        m.first_name as contact_first_name,
                        m.last_name as contact_last_name,
                        p.first_name as posted_by_first_name,
                        p.last_name as posted_by_last_name
                 FROM {$this->table} o
                 LEFT JOIN {$this->committees_table} c ON o.committee_id = c.id
                 LEFT JOIN {$this->members_table} m ON o.contact_member_id = m.id
                 LEFT JOIN {$this->members_table} p ON o.posted_by = p.id
                 WHERE o.id = %d",
                $id
            ),
            ARRAY_A
        );

        return $row ?: null;
    }

    /**
     * Get all opportunities with optional filtering.
     *
     * @param array $filters Optional filters.
     *                       - committee_id: Filter by committee
     *                       - status: Filter by status
     *                       - type: Filter by opportunity_type
     *                       - active_only: Only show is_active = 1
     *                       - upcoming: Only show future dates (for one_time)
     * @return array Array of opportunities.
     */
    public function get_all( array $filters = array() ): array {
        $where_clauses = array();
        $params        = array();

        if ( isset( $filters['committee_id'] ) ) {
            if ( $filters['committee_id'] === null || $filters['committee_id'] === 'society' ) {
                $where_clauses[] = 'o.committee_id IS NULL';
            } else {
                $where_clauses[] = 'o.committee_id = %d';
                $params[]        = absint( $filters['committee_id'] );
            }
        }

        if ( isset( $filters['status'] ) ) {
            if ( is_array( $filters['status'] ) ) {
                $placeholders    = implode( ', ', array_fill( 0, count( $filters['status'] ), '%s' ) );
                $where_clauses[] = "o.status IN ({$placeholders})";
                $params          = array_merge( $params, $filters['status'] );
            } else {
                $where_clauses[] = 'o.status = %s';
                $params[]        = $filters['status'];
            }
        }

        if ( isset( $filters['type'] ) ) {
            $where_clauses[] = 'o.opportunity_type = %s';
            $params[]        = $filters['type'];
        }

        if ( isset( $filters['active_only'] ) && $filters['active_only'] ) {
            $where_clauses[] = 'o.is_active = 1';
        }

        if ( isset( $filters['upcoming'] ) && $filters['upcoming'] ) {
            // For one-time events, only show future dates
            // For recurring/ongoing, always show
            $where_clauses[] = "(o.opportunity_type != 'one_time' OR o.date >= CURDATE())";
        }

        $where_sql = ! empty( $where_clauses )
            ? 'WHERE ' . implode( ' AND ', $where_clauses )
            : '';

        $sql = "SELECT o.*,
                       c.name as committee_name,
                       c.slug as committee_slug
                FROM {$this->table} o
                LEFT JOIN {$this->committees_table} c ON o.committee_id = c.id
                {$where_sql}
                ORDER BY o.sort_order ASC, o.date ASC, o.created_at DESC";

        if ( ! empty( $params ) ) {
            $sql = $this->wpdb->prepare( $sql, $params );
        }

        $results = $this->wpdb->get_results( $sql, ARRAY_A );

        return $results ?: array();
    }

    /**
     * Get opportunities for a specific committee.
     *
     * @param int  $committee_id Committee ID.
     * @param bool $open_only    Only return open opportunities.
     * @return array Array of opportunities.
     */
    public function get_by_committee( int $committee_id, bool $open_only = false ): array {
        $filters = array(
            'committee_id' => $committee_id,
            'active_only'  => true,
        );

        if ( $open_only ) {
            $filters['status'] = 'open';
        }

        return $this->get_all( $filters );
    }

    /**
     * Get all open opportunities for member browsing.
     *
     * WHY: Main method for frontend display - shows what's available to sign up for.
     *
     * @return array Array of open opportunities.
     */
    public function get_open(): array {
        return $this->get_all( array(
            'status'      => 'open',
            'active_only' => true,
            'upcoming'    => true,
        ) );
    }

    /**
     * Get the count of confirmed signups for an opportunity.
     *
     * @param int $opportunity_id Opportunity ID.
     * @return int Signup count.
     */
    public function get_signup_count( int $opportunity_id ): int {
        $count = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->signups_table}
                 WHERE opportunity_id = %d AND status = 'confirmed'",
                $opportunity_id
            )
        );

        return (int) $count;
    }

    /**
     * Get remaining capacity for an opportunity.
     *
     * @param int $opportunity_id Opportunity ID.
     * @return int|null Remaining spots, or null if unlimited.
     */
    public function get_remaining_capacity( int $opportunity_id ): ?int {
        $opportunity = $this->get( $opportunity_id );

        if ( ! $opportunity || ! $opportunity['capacity'] ) {
            return null; // Unlimited
        }

        $confirmed = $this->get_signup_count( $opportunity_id );
        $remaining = (int) $opportunity['capacity'] - $confirmed;

        return max( 0, $remaining );
    }

    /**
     * Check if an opportunity has available capacity.
     *
     * @param int $opportunity_id Opportunity ID.
     * @return bool True if has capacity or unlimited.
     */
    public function has_capacity( int $opportunity_id ): bool {
        $remaining = $this->get_remaining_capacity( $opportunity_id );

        // Null means unlimited
        if ( null === $remaining ) {
            return true;
        }

        return $remaining > 0;
    }

    /**
     * Check if an opportunity is full and update its status.
     *
     * WHY: Automatically marks opportunity as "filled" when capacity is reached.
     *
     * @param int $opportunity_id Opportunity ID.
     * @return bool Whether opportunity is now full.
     */
    public function check_and_update_status( int $opportunity_id ): bool {
        $opportunity = $this->get( $opportunity_id );

        if ( ! $opportunity ) {
            return false;
        }

        // Check if capacity is set and filled
        if ( $opportunity['capacity'] && ! $this->has_capacity( $opportunity_id ) ) {
            // Mark as filled if not already
            if ( $opportunity['status'] === 'open' ) {
                $this->update( $opportunity_id, array( 'status' => 'filled' ) );
            }
            return true;
        }

        // If capacity was freed up and status was filled, reopen
        if ( $opportunity['status'] === 'filled' && $this->has_capacity( $opportunity_id ) ) {
            $this->update( $opportunity_id, array( 'status' => 'open' ) );
        }

        return false;
    }

    /**
     * Get opportunities posted by a specific member.
     *
     * WHY: Allows committee chairs to see/manage what they've posted.
     *
     * @param int $member_id Member ID who posted.
     * @return array Array of opportunities.
     */
    public function get_posted_by( int $member_id ): array {
        $results = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT o.*,
                        c.name as committee_name
                 FROM {$this->table} o
                 LEFT JOIN {$this->committees_table} c ON o.committee_id = c.id
                 WHERE o.posted_by = %d AND o.is_active = 1
                 ORDER BY o.created_at DESC",
                $member_id
            ),
            ARRAY_A
        );

        return $results ?: array();
    }

    /**
     * Format opportunity time for display.
     *
     * @param array $opportunity Opportunity data.
     * @return string Formatted time string.
     */
    public function format_time( array $opportunity ): string {
        if ( empty( $opportunity['start_time'] ) ) {
            return '';
        }

        $start = date( 'g:i A', strtotime( $opportunity['start_time'] ) );

        if ( ! empty( $opportunity['end_time'] ) ) {
            $end = date( 'g:i A', strtotime( $opportunity['end_time'] ) );
            return $start . ' – ' . $end;
        }

        return $start;
    }

    /**
     * Format opportunity schedule for display.
     *
     * WHY: Provides a human-readable summary of when the opportunity occurs.
     *
     * @param array $opportunity Opportunity data.
     * @return string Formatted schedule string.
     */
    public function format_schedule( array $opportunity ): string {
        $parts = array();

        switch ( $opportunity['opportunity_type'] ) {
            case 'one_time':
                if ( ! empty( $opportunity['date'] ) ) {
                    $parts[] = date_i18n( 'l, F j, Y', strtotime( $opportunity['date'] ) );
                }
                break;

            case 'recurring':
                if ( isset( $opportunity['day_of_week'] ) && isset( self::DAYS_OF_WEEK[ $opportunity['day_of_week'] ] ) ) {
                    $parts[] = 'Every ' . self::DAYS_OF_WEEK[ $opportunity['day_of_week'] ];
                }
                break;

            case 'ongoing':
                $parts[] = __( 'Ongoing – Flexible Schedule', 'societypress' );
                break;
        }

        $time = $this->format_time( $opportunity );
        if ( $time && $opportunity['opportunity_type'] !== 'ongoing' ) {
            $parts[] = $time;
        }

        return implode( ' • ', $parts );
    }

    /**
     * Get label for opportunity type.
     *
     * @param string $type Opportunity type.
     * @return string Human-readable label.
     */
    public static function get_type_label( string $type ): string {
        $labels = array(
            'one_time'  => __( 'One-Time', 'societypress' ),
            'recurring' => __( 'Recurring', 'societypress' ),
            'ongoing'   => __( 'Ongoing', 'societypress' ),
        );

        return $labels[ $type ] ?? $type;
    }

    /**
     * Get label for opportunity status.
     *
     * @param string $status Status.
     * @return string Human-readable label.
     */
    public static function get_status_label( string $status ): string {
        $labels = array(
            'open'      => __( 'Open', 'societypress' ),
            'filled'    => __( 'Filled', 'societypress' ),
            'closed'    => __( 'Closed', 'societypress' ),
            'cancelled' => __( 'Cancelled', 'societypress' ),
        );

        return $labels[ $status ] ?? $status;
    }
}
