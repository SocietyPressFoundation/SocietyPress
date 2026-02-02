<?php
/**
 * Committees Management
 *
 * CRUD operations for committees and committee membership.
 *
 * WHY: Societies organize work through committees (Library, Programs, Membership, etc.).
 *      Committee chairs often need to post volunteer opportunities or manage their
 *      committee's activities without full admin access.
 *
 * @package SocietyPress
 * @since 0.54d
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SocietyPress_Committees
 *
 * Handles committee CRUD and membership operations.
 */
class SocietyPress_Committees {

    /**
     * WordPress database object.
     *
     * @var wpdb
     */
    private $wpdb;

    /**
     * Committees table name.
     *
     * @var string
     */
    private string $table;

    /**
     * Committee members table name.
     *
     * @var string
     */
    private string $members_table;

    /**
     * Valid committee member roles.
     *
     * WHY: Chairs and co-chairs have special permissions like posting
     *      volunteer opportunities for their committee.
     */
    public const ROLES = array( 'chair', 'co_chair', 'member' );

    /**
     * Constructor.
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb          = $wpdb;
        $this->table         = SocietyPress::table( 'committees' );
        $this->members_table = SocietyPress::table( 'committee_members' );
    }

    /**
     * Get a single committee by ID.
     *
     * @param int $id Committee ID.
     * @return array|null Committee data or null.
     */
    public function get( int $id ): ?array {
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE id = %d",
                $id
            ),
            ARRAY_A
        );

        return $row ?: null;
    }

    /**
     * Get a committee by slug.
     *
     * @param string $slug Committee slug.
     * @return array|null Committee data or null.
     */
    public function get_by_slug( string $slug ): ?array {
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE slug = %s",
                $slug
            ),
            ARRAY_A
        );

        return $row ?: null;
    }

    /**
     * Get all committees.
     *
     * @param bool $active_only Whether to only return active committees.
     * @return array Array of committee records.
     */
    public function get_all( bool $active_only = true ): array {
        $where = $active_only ? 'WHERE is_active = 1' : '';

        $results = $this->wpdb->get_results(
            "SELECT * FROM {$this->table} {$where} ORDER BY sort_order ASC, name ASC",
            ARRAY_A
        );

        return $results ?: array();
    }

    /**
     * Get members of a committee.
     *
     * WHY: Returns all current members with their roles, useful for
     *      displaying committee rosters and determining permissions.
     *
     * @param int  $committee_id Committee ID.
     * @param bool $current_only Only return current members (no left_date).
     * @return array Array of committee members with member details.
     */
    public function get_members( int $committee_id, bool $current_only = true ): array {
        $sp_members = SocietyPress::table( 'members' );

        $where_current = $current_only ? 'AND cm.left_date IS NULL' : '';

        $results = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT cm.*, m.first_name, m.last_name, m.status as member_status
                 FROM {$this->members_table} cm
                 INNER JOIN {$sp_members} m ON cm.member_id = m.id
                 WHERE cm.committee_id = %d {$where_current}
                 ORDER BY
                     CASE cm.role
                         WHEN 'chair' THEN 1
                         WHEN 'co_chair' THEN 2
                         ELSE 3
                     END,
                     m.last_name ASC,
                     m.first_name ASC",
                $committee_id
            ),
            ARRAY_A
        );

        return $results ?: array();
    }

    /**
     * Get the chair(s) of a committee.
     *
     * WHY: Chairs and co-chairs have elevated permissions for their committee.
     *      Returns both chair and co_chair roles.
     *
     * @param int $committee_id Committee ID.
     * @return array Array of chair/co-chair member records.
     */
    public function get_chairs( int $committee_id ): array {
        $sp_members = SocietyPress::table( 'members' );

        $results = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT cm.*, m.first_name, m.last_name, m.user_id
                 FROM {$this->members_table} cm
                 INNER JOIN {$sp_members} m ON cm.member_id = m.id
                 WHERE cm.committee_id = %d
                   AND cm.role IN ('chair', 'co_chair')
                   AND cm.left_date IS NULL
                 ORDER BY
                     CASE cm.role WHEN 'chair' THEN 1 ELSE 2 END,
                     m.last_name ASC",
                $committee_id
            ),
            ARRAY_A
        );

        return $results ?: array();
    }

    /**
     * Check if a member is a chair or co-chair of a committee.
     *
     * WHY: Used to determine if a member has permission to post
     *      volunteer opportunities or manage committee activities.
     *
     * @param int $committee_id Committee ID.
     * @param int $member_id    Member ID.
     * @return bool True if member is chair or co-chair.
     */
    public function is_chair( int $committee_id, int $member_id ): bool {
        $result = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->members_table}
                 WHERE committee_id = %d
                   AND member_id = %d
                   AND role IN ('chair', 'co_chair')
                   AND left_date IS NULL",
                $committee_id,
                $member_id
            )
        );

        return (int) $result > 0;
    }

    /**
     * Get all committees where a member is chair or co-chair.
     *
     * WHY: Used to determine which committees a member can manage,
     *      particularly for posting volunteer opportunities.
     *
     * @param int $member_id Member ID.
     * @return array Array of committee records.
     */
    public function get_committees_where_chair( int $member_id ): array {
        $results = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT c.* FROM {$this->table} c
                 INNER JOIN {$this->members_table} cm ON c.id = cm.committee_id
                 WHERE cm.member_id = %d
                   AND cm.role IN ('chair', 'co_chair')
                   AND cm.left_date IS NULL
                   AND c.is_active = 1
                 ORDER BY c.sort_order ASC, c.name ASC",
                $member_id
            ),
            ARRAY_A
        );

        return $results ?: array();
    }

    /**
     * Get a member's current committee memberships.
     *
     * @param int $member_id Member ID.
     * @return array Array of committee membership records with committee details.
     */
    public function get_member_committees( int $member_id ): array {
        $results = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT cm.*, c.name as committee_name, c.slug as committee_slug
                 FROM {$this->members_table} cm
                 INNER JOIN {$this->table} c ON cm.committee_id = c.id
                 WHERE cm.member_id = %d
                   AND cm.left_date IS NULL
                   AND c.is_active = 1
                 ORDER BY c.sort_order ASC, c.name ASC",
                $member_id
            ),
            ARRAY_A
        );

        return $results ?: array();
    }

    /**
     * Create a new committee.
     *
     * @param array $data Committee data.
     * @return int|false New committee ID or false on failure.
     */
    public function create( array $data ) {
        // Validate required fields
        if ( empty( $data['name'] ) ) {
            return false;
        }

        // Generate slug if not provided
        $slug = ! empty( $data['slug'] )
            ? sanitize_title( $data['slug'] )
            : sanitize_title( $data['name'] );

        // Ensure slug is unique
        $slug = $this->unique_slug( $slug );

        $insert_data = array(
            'slug'        => $slug,
            'name'        => sanitize_text_field( $data['name'] ),
            'description' => isset( $data['description'] ) ? sanitize_textarea_field( $data['description'] ) : null,
            'is_standing' => isset( $data['is_standing'] ) ? ( $data['is_standing'] ? 1 : 0 ) : 1,
            'is_active'   => isset( $data['is_active'] ) ? ( $data['is_active'] ? 1 : 0 ) : 1,
            'sort_order'  => isset( $data['sort_order'] ) ? absint( $data['sort_order'] ) : 0,
        );

        $result = $this->wpdb->insert(
            $this->table,
            $insert_data,
            array( '%s', '%s', '%s', '%d', '%d', '%d' )
        );

        if ( false === $result ) {
            return false;
        }

        return (int) $this->wpdb->insert_id;
    }

    /**
     * Update a committee.
     *
     * @param int   $id   Committee ID.
     * @param array $data Data to update.
     * @return bool Success or failure.
     */
    public function update( int $id, array $data ): bool {
        $existing = $this->get( $id );
        if ( ! $existing ) {
            return false;
        }

        $allowed = array( 'name', 'slug', 'description', 'is_standing', 'is_active', 'sort_order' );
        $update  = array();

        foreach ( $allowed as $field ) {
            if ( array_key_exists( $field, $data ) ) {
                switch ( $field ) {
                    case 'name':
                        $update['name'] = sanitize_text_field( $data['name'] );
                        break;
                    case 'slug':
                        $new_slug = sanitize_title( $data['slug'] );
                        // Only check uniqueness if slug is changing
                        if ( $new_slug !== $existing['slug'] ) {
                            $new_slug = $this->unique_slug( $new_slug, $id );
                        }
                        $update['slug'] = $new_slug;
                        break;
                    case 'description':
                        $update['description'] = sanitize_textarea_field( $data['description'] );
                        break;
                    case 'is_standing':
                    case 'is_active':
                        $update[ $field ] = $data[ $field ] ? 1 : 0;
                        break;
                    case 'sort_order':
                        $update['sort_order'] = absint( $data['sort_order'] );
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
     * Delete a committee (soft delete by setting inactive).
     *
     * WHY: Soft delete preserves history. Hard delete available if needed.
     *
     * @param int  $id   Committee ID.
     * @param bool $hard Whether to permanently delete.
     * @return bool Success or failure.
     */
    public function delete( int $id, bool $hard = false ): bool {
        if ( $hard ) {
            // First remove all committee member records
            $this->wpdb->delete(
                $this->members_table,
                array( 'committee_id' => $id ),
                array( '%d' )
            );

            // Then delete the committee
            $result = $this->wpdb->delete(
                $this->table,
                array( 'id' => $id ),
                array( '%d' )
            );

            return false !== $result;
        }

        // Soft delete - just mark inactive
        return $this->update( $id, array( 'is_active' => false ) );
    }

    /**
     * Add a member to a committee.
     *
     * @param int    $committee_id Committee ID.
     * @param int    $member_id    Member ID.
     * @param string $role         Role (chair, co_chair, member).
     * @param string $joined_date  Date joined (Y-m-d format).
     * @param string $notes        Optional notes.
     * @return int|false New membership ID or false.
     */
    public function add_member( int $committee_id, int $member_id, string $role = 'member', string $joined_date = '', string $notes = '' ) {
        // Validate role
        if ( ! in_array( $role, self::ROLES, true ) ) {
            $role = 'member';
        }

        // Check if already an active member
        $existing = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT id FROM {$this->members_table}
                 WHERE committee_id = %d AND member_id = %d AND left_date IS NULL",
                $committee_id,
                $member_id
            )
        );

        if ( $existing ) {
            // Already a member - could update role instead
            return false;
        }

        $insert_data = array(
            'committee_id' => $committee_id,
            'member_id'    => $member_id,
            'role'         => $role,
            'joined_date'  => $joined_date ?: current_time( 'Y-m-d' ),
            'notes'        => $notes ? sanitize_textarea_field( $notes ) : null,
        );

        $result = $this->wpdb->insert(
            $this->members_table,
            $insert_data,
            array( '%d', '%d', '%s', '%s', '%s' )
        );

        if ( false === $result ) {
            return false;
        }

        return (int) $this->wpdb->insert_id;
    }

    /**
     * Remove a member from a committee.
     *
     * WHY: Sets left_date rather than deleting to preserve history.
     *
     * @param int    $committee_id Committee ID.
     * @param int    $member_id    Member ID.
     * @param string $left_date    Date left (Y-m-d format).
     * @return bool Success or failure.
     */
    public function remove_member( int $committee_id, int $member_id, string $left_date = '' ): bool {
        $result = $this->wpdb->update(
            $this->members_table,
            array( 'left_date' => $left_date ?: current_time( 'Y-m-d' ) ),
            array(
                'committee_id' => $committee_id,
                'member_id'    => $member_id,
                'left_date'    => null,
            ),
            array( '%s' ),
            array( '%d', '%d', null )
        );

        return false !== $result && $result > 0;
    }

    /**
     * Update a member's committee role.
     *
     * @param int    $committee_id Committee ID.
     * @param int    $member_id    Member ID.
     * @param string $role         New role.
     * @return bool Success or failure.
     */
    public function update_member_role( int $committee_id, int $member_id, string $role ): bool {
        if ( ! in_array( $role, self::ROLES, true ) ) {
            return false;
        }

        $result = $this->wpdb->update(
            $this->members_table,
            array( 'role' => $role ),
            array(
                'committee_id' => $committee_id,
                'member_id'    => $member_id,
                'left_date'    => null,
            ),
            array( '%s' ),
            array( '%d', '%d', null )
        );

        return false !== $result;
    }

    /**
     * Get committee membership count.
     *
     * @param int  $committee_id Committee ID.
     * @param bool $current_only Only count current members.
     * @return int Member count.
     */
    public function get_member_count( int $committee_id, bool $current_only = true ): int {
        $where_current = $current_only ? 'AND left_date IS NULL' : '';

        $count = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->members_table}
                 WHERE committee_id = %d {$where_current}",
                $committee_id
            )
        );

        return (int) $count;
    }

    /**
     * Generate a unique slug.
     *
     * @param string   $slug       Base slug.
     * @param int|null $exclude_id Committee ID to exclude from check.
     * @return string Unique slug.
     */
    private function unique_slug( string $slug, ?int $exclude_id = null ): string {
        $original = $slug;
        $counter  = 1;

        while ( $this->slug_exists( $slug, $exclude_id ) ) {
            $slug = $original . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if a slug already exists.
     *
     * @param string   $slug       Slug to check.
     * @param int|null $exclude_id Committee ID to exclude.
     * @return bool True if exists.
     */
    private function slug_exists( string $slug, ?int $exclude_id = null ): bool {
        $where_exclude = $exclude_id ? $this->wpdb->prepare( 'AND id != %d', $exclude_id ) : '';

        $exists = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table} WHERE slug = %s {$where_exclude}",
                $slug
            )
        );

        return (int) $exists > 0;
    }

    /**
     * Delete all committee memberships for a member.
     *
     * WHY: Called when a member is deleted from the system to clean up
     *      their committee associations.
     *
     * @param int $member_id Member ID.
     * @return bool Success or failure.
     */
    public function delete_member_memberships( int $member_id ): bool {
        $result = $this->wpdb->delete(
            $this->members_table,
            array( 'member_id' => $member_id ),
            array( '%d' )
        );

        return false !== $result;
    }
}
