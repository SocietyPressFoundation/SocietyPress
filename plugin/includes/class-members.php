<?php
/**
 * Members Management
 *
 * CRUD operations for society members.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SocietyPress_Members
 */
class SocietyPress_Members {

    /**
     * Database object.
     *
     * @var wpdb
     */
    private $wpdb;

    /**
     * Valid statuses.
     */
    public const STATUSES = array( 'active', 'expired', 'pending', 'cancelled', 'deceased' );

    /**
     * Valid relationship types.
     */
    public const RELATIONSHIP_TYPES = array( 'spouse', 'family_member', 'referred_by' );

    /**
     * Constructor.
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    /**
     * Get member by ID.
     *
     * @param int $id Member ID.
     * @return object|null
     */
    public function get( int $id ): ?object {
        $table = SocietyPress::table( 'members' );
        return $this->wpdb->get_row( $this->wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) ) ?: null;
    }

    /**
     * Get member by email.
     *
     * @param string $email Email address.
     * @return object|null
     */
    public function get_by_email( string $email ): ?object {
        $members = SocietyPress::table( 'members' );
        $contact = SocietyPress::table( 'member_contact' );

        return $this->wpdb->get_row( $this->wpdb->prepare(
            "SELECT m.* FROM {$members} m
             INNER JOIN {$contact} c ON m.id = c.member_id
             WHERE c.primary_email = %s",
            $email
        ) ) ?: null;
    }

    /**
     * Get member by WordPress user ID.
     *
     * WHY: Allows looking up member record from logged-in WP user.
     *      Uses the user_id column in sp_members table (set by User Manager).
     *
     * @param int $user_id WordPress user ID.
     * @return object|null Member object or null.
     */
    public function get_by_user_id( int $user_id ): ?object {
        $table = SocietyPress::table( 'members' );

        return $this->wpdb->get_row( $this->wpdb->prepare(
            "SELECT * FROM {$table} WHERE user_id = %d",
            $user_id
        ) ) ?: null;
    }

    /**
     * Get member with all related data.
     *
     * @param int $id Member ID.
     * @return array|null
     */
    public function get_full( int $id ): ?array {
        $member = $this->get( $id );
        if ( ! $member ) {
            return null;
        }

        $data              = (array) $member;
        $tier              = $this->get_tier( $id );
        $data['tier']      = $tier ? (array) $tier : null;
        $contact           = $this->get_contact( $id );
        $data['contact']   = $contact ? (array) $contact : null;
        $data['surnames']  = $this->get_surnames( $id );
        $data['meta']      = $this->get_all_meta( $id );

        return $data;
    }

    /**
     * Create a member.
     *
     * @param array $data Member data.
     * @return int|false New ID or false.
     */
    public function create( array $data ) {
        $table = SocietyPress::table( 'members' );

        // Validate required
        if ( empty( $data['first_name'] ) || empty( $data['last_name'] ) || empty( $data['membership_tier_id'] ) || empty( $data['join_date'] ) ) {
            return false;
        }

        $insert = array(
            'user_id'                    => $data['user_id'] ?? null,
            'membership_tier_id'         => absint( $data['membership_tier_id'] ),
            'status'                     => $data['status'] ?? 'pending',
            'first_name'                 => sanitize_text_field( $data['first_name'] ),
            'middle_name'                => ! empty( $data['middle_name'] ) ? sanitize_text_field( $data['middle_name'] ) : null,
            'last_name'                  => sanitize_text_field( $data['last_name'] ),
            'photo_id'                   => ! empty( $data['photo_id'] ) ? absint( $data['photo_id'] ) : null,
            'birth_date'                 => $data['birth_date'] ?? null,
            'birth_year_only'            => ! empty( $data['birth_year_only'] ) ? 1 : 0,
            'join_date'                  => $data['join_date'],
            'expiration_date'            => $data['expiration_date'] ?? null,
            'auto_renew'                 => ! empty( $data['auto_renew'] ) ? 1 : 0,
            'directory_visible'          => isset( $data['directory_visible'] ) ? ( $data['directory_visible'] ? 1 : 0 ) : 1,
            'show_birthday_in_directory' => ! empty( $data['show_birthday_in_directory'] ) ? 1 : 0,
            'how_heard_about_us'         => sanitize_text_field( $data['how_heard_about_us'] ?? '' ),
            'communication_preference'   => $data['communication_preference'] ?? 'email',
        );

        $result = $this->wpdb->insert( $table, $insert );

        if ( false === $result ) {
            return false;
        }

        $member_id = $this->wpdb->insert_id;
        $this->log_action( 'create', 'member', $member_id, $insert );

        do_action( 'societypress_member_created', $member_id, $insert );

        return $member_id;
    }

    /**
     * Update a member.
     *
     * @param int   $id   Member ID.
     * @param array $data Data to update.
     * @return bool
     */
    public function update( int $id, array $data ): bool {
        $table    = SocietyPress::table( 'members' );
        $existing = $this->get( $id );

        if ( ! $existing ) {
            return false;
        }

        $allowed = array(
            'user_id', 'membership_tier_id', 'status', 'first_name', 'middle_name', 'last_name',
            'photo_id', 'birth_date', 'birth_year_only', 'join_date', 'expiration_date',
            'auto_renew', 'directory_visible', 'show_birthday_in_directory',
            'date_of_death', 'death_reported_by', 'how_heard_about_us', 'communication_preference',
        );

        $update = array();
        foreach ( $allowed as $field ) {
            if ( array_key_exists( $field, $data ) ) {
                $update[ $field ] = $data[ $field ];
            }
        }

        if ( empty( $update ) ) {
            return false;
        }

        $result = $this->wpdb->update( $table, $update, array( 'id' => $id ) );

        if ( false === $result ) {
            return false;
        }

        $this->log_action( 'update', 'member', $id, array( 'old' => (array) $existing, 'new' => $update ) );

        do_action( 'societypress_member_updated', $id, $update, $existing );

        return true;
    }

    /**
     * Delete a member.
     *
     * @param int $id Member ID.
     * @return bool
     */
    public function delete( int $id ): bool {
        $member = $this->get_full( $id );
        if ( ! $member ) {
            return false;
        }

        // Delete related data
        $this->delete_all_meta( $id );
        $this->delete_contact( $id );

        $this->wpdb->delete( SocietyPress::table( 'member_surnames' ), array( 'member_id' => $id ) );
        $this->wpdb->delete( SocietyPress::table( 'member_research_areas' ), array( 'member_id' => $id ) );
        $this->wpdb->delete( SocietyPress::table( 'member_relationships' ), array( 'member_id' => $id ) );
        $this->wpdb->delete( SocietyPress::table( 'member_relationships' ), array( 'related_member_id' => $id ) );
        $this->wpdb->delete( SocietyPress::table( 'renewal_reminders' ), array( 'member_id' => $id ) );
        $this->wpdb->delete( SocietyPress::table( 'committee_members' ), array( 'member_id' => $id ) );
        $this->wpdb->delete( SocietyPress::table( 'position_holders' ), array( 'member_id' => $id ) );

        $result = $this->wpdb->delete( SocietyPress::table( 'members' ), array( 'id' => $id ) );

        if ( false === $result ) {
            return false;
        }

        $this->log_action( 'delete', 'member', $id, $member );

        do_action( 'societypress_member_deleted', $id, $member );

        return true;
    }

    /**
     * Get members with filtering.
     *
     * @param array $args Query args.
     * @return array
     */
    public function get_members( array $args = array() ): array {
        $defaults = array(
            'status'  => null,
            'tier_id' => null,
            'search'  => '',
            'orderby' => 'last_name',
            'order'   => 'ASC',
            'limit'   => 20,
            'offset'  => 0,
        );

        $args = wp_parse_args( $args, $defaults );

        $table   = SocietyPress::table( 'members' );
        $contact = SocietyPress::table( 'member_contact' );

        $where  = array( '1=1' );
        $join   = '';
        $values = array();

        if ( ! empty( $args['status'] ) ) {
            if ( is_array( $args['status'] ) ) {
                $placeholders = implode( ', ', array_fill( 0, count( $args['status'] ), '%s' ) );
                $where[]      = "m.status IN ({$placeholders})";
                $values       = array_merge( $values, $args['status'] );
            } else {
                $where[]  = 'm.status = %s';
                $values[] = $args['status'];
            }
        }

        if ( ! empty( $args['tier_id'] ) ) {
            $where[]  = 'm.membership_tier_id = %d';
            $values[] = absint( $args['tier_id'] );
        }

        if ( ! empty( $args['search'] ) ) {
            $term    = '%' . $this->wpdb->esc_like( $args['search'] ) . '%';
            $join   .= " LEFT JOIN {$contact} c ON m.id = c.member_id";
            $where[] = '(m.first_name LIKE %s OR m.last_name LIKE %s OR c.primary_email LIKE %s OR c.city LIKE %s OR c.state_province LIKE %s OR c.street_address LIKE %s OR c.postal_code LIKE %s)';
            $values  = array_merge( $values, array( $term, $term, $term, $term, $term, $term, $term ) );
        }

        $allowed_orderby = array( 'id', 'first_name', 'last_name', 'status', 'join_date', 'expiration_date' );
        $orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'last_name';
        $order           = 'DESC' === strtoupper( $args['order'] ) ? 'DESC' : 'ASC';

        $sql = "SELECT DISTINCT m.* FROM {$table} m {$join} WHERE " . implode( ' AND ', $where ) . " ORDER BY m.{$orderby} {$order}";

        if ( $args['limit'] > 0 ) {
            $sql     .= ' LIMIT %d OFFSET %d';
            $values[] = absint( $args['limit'] );
            $values[] = absint( $args['offset'] );
        }

        if ( ! empty( $values ) ) {
            $sql = $this->wpdb->prepare( $sql, $values );
        }

        return $this->wpdb->get_results( $sql );
    }

    /**
     * Count members.
     *
     * @param array $args Filter args.
     * @return int
     */
    public function count( array $args = array() ): int {
        $table  = SocietyPress::table( 'members' );
        $where  = array( '1=1' );
        $values = array();

        if ( ! empty( $args['status'] ) ) {
            $where[]  = 'status = %s';
            $values[] = $args['status'];
        }

        if ( ! empty( $args['tier_id'] ) ) {
            $where[]  = 'membership_tier_id = %d';
            $values[] = absint( $args['tier_id'] );
        }

        $sql = "SELECT COUNT(*) FROM {$table} WHERE " . implode( ' AND ', $where );

        if ( ! empty( $values ) ) {
            $sql = $this->wpdb->prepare( $sql, $values );
        }

        return (int) $this->wpdb->get_var( $sql );
    }

    // =========================================================================
    // Contact
    // =========================================================================

    /**
     * Get contact info.
     *
     * @param int $id Member ID.
     * @return object|null
     */
    public function get_contact( int $id ): ?object {
        $table = SocietyPress::table( 'member_contact' );
        return $this->wpdb->get_row( $this->wpdb->prepare( "SELECT * FROM {$table} WHERE member_id = %d", $id ) ) ?: null;
    }

    /**
     * Save contact info.
     *
     * @param int   $id   Member ID.
     * @param array $data Contact data.
     * @return bool
     */
    public function save_contact( int $id, array $data ): bool {
        $table    = SocietyPress::table( 'member_contact' );
        $existing = $this->get_contact( $id );

        if ( empty( $data['primary_email'] ) || ! is_email( $data['primary_email'] ) ) {
            return false;
        }

        $contact = array(
            'member_id'                 => $id,
            'primary_email'             => sanitize_email( $data['primary_email'] ),
            'secondary_email'           => ! empty( $data['secondary_email'] ) ? sanitize_email( $data['secondary_email'] ) : null,
            'home_phone'                => sanitize_text_field( $data['home_phone'] ?? '' ),
            'cell_phone'                => sanitize_text_field( $data['cell_phone'] ?? '' ),
            'work_phone'                => sanitize_text_field( $data['work_phone'] ?? '' ),
            'preferred_phone'           => $data['preferred_phone'] ?? 'cell',
            'street_address'            => sanitize_textarea_field( $data['street_address'] ?? '' ),
            'address_line_2'            => sanitize_text_field( $data['address_line_2'] ?? '' ),
            'city'                      => sanitize_text_field( $data['city'] ?? '' ),
            'state_province'            => sanitize_text_field( $data['state_province'] ?? '' ),
            'postal_code'               => sanitize_text_field( $data['postal_code'] ?? '' ),
            'country'                   => sanitize_text_field( $data['country'] ?? 'USA' ),
            'mailing_address_different' => ! empty( $data['mailing_address_different'] ) ? 1 : 0,
            'mailing_street_address'    => sanitize_textarea_field( $data['mailing_street_address'] ?? '' ),
            'mailing_address_line_2'    => sanitize_text_field( $data['mailing_address_line_2'] ?? '' ),
            'mailing_city'              => sanitize_text_field( $data['mailing_city'] ?? '' ),
            'mailing_state_province'    => sanitize_text_field( $data['mailing_state_province'] ?? '' ),
            'mailing_postal_code'       => sanitize_text_field( $data['mailing_postal_code'] ?? '' ),
            'mailing_country'           => sanitize_text_field( $data['mailing_country'] ?? '' ),
        );

        if ( $existing ) {
            return false !== $this->wpdb->update( $table, $contact, array( 'member_id' => $id ) );
        }

        return false !== $this->wpdb->insert( $table, $contact );
    }

    /**
     * Update contact info (alias for save_contact).
     *
     * @param int   $id   Member ID.
     * @param array $data Contact data.
     * @return bool
     */
    public function update_contact( int $id, array $data ): bool {
        return $this->save_contact( $id, $data );
    }

    /**
     * Delete contact.
     *
     * @param int $id Member ID.
     * @return bool
     */
    public function delete_contact( int $id ): bool {
        return false !== $this->wpdb->delete( SocietyPress::table( 'member_contact' ), array( 'member_id' => $id ) );
    }

    // =========================================================================
    // Meta
    // =========================================================================

    /**
     * Get meta value.
     *
     * @param int    $id  Member ID.
     * @param string $key Meta key.
     * @return mixed
     */
    public function get_meta( int $id, string $key ) {
        $table = SocietyPress::table( 'member_meta' );

        $row = $this->wpdb->get_row( $this->wpdb->prepare(
            "SELECT meta_value, is_encrypted FROM {$table} WHERE member_id = %d AND meta_key = %s",
            $id,
            $key
        ) );

        if ( ! $row ) {
            return null;
        }

        if ( $row->is_encrypted ) {
            return SocietyPress_Encryption::decrypt( $row->meta_value );
        }

        return maybe_unserialize( $row->meta_value );
    }

    /**
     * Get all meta.
     *
     * @param int $id Member ID.
     * @return array
     */
    public function get_all_meta( int $id ): array {
        $table = SocietyPress::table( 'member_meta' );
        $rows  = $this->wpdb->get_results( $this->wpdb->prepare(
            "SELECT meta_key, meta_value, is_encrypted FROM {$table} WHERE member_id = %d",
            $id
        ) );

        $meta = array();
        foreach ( $rows as $row ) {
            if ( $row->is_encrypted ) {
                $meta[ $row->meta_key ] = SocietyPress_Encryption::decrypt( $row->meta_value );
            } else {
                $meta[ $row->meta_key ] = maybe_unserialize( $row->meta_value );
            }
        }

        return $meta;
    }

    /**
     * Save meta.
     *
     * @param int    $id      Member ID.
     * @param string $key     Meta key.
     * @param mixed  $value   Value.
     * @param bool   $encrypt Encrypt value.
     * @return bool
     */
    public function save_meta( int $id, string $key, $value, bool $encrypt = false ): bool {
        $table = SocietyPress::table( 'member_meta' );

        if ( ! is_scalar( $value ) ) {
            $value = maybe_serialize( $value );
        }

        if ( $encrypt && ! empty( $value ) ) {
            $value = SocietyPress_Encryption::encrypt( (string) $value );
        }

        $existing = $this->wpdb->get_var( $this->wpdb->prepare(
            "SELECT id FROM {$table} WHERE member_id = %d AND meta_key = %s",
            $id,
            $key
        ) );

        if ( $existing ) {
            return false !== $this->wpdb->update(
                $table,
                array( 'meta_value' => $value, 'is_encrypted' => $encrypt ? 1 : 0 ),
                array( 'id' => $existing )
            );
        }

        return false !== $this->wpdb->insert( $table, array(
            'member_id'    => $id,
            'meta_key'     => $key,
            'meta_value'   => $value,
            'is_encrypted' => $encrypt ? 1 : 0,
        ) );
    }

    /**
     * Update meta (alias for save_meta).
     *
     * @param int    $id      Member ID.
     * @param string $key     Meta key.
     * @param mixed  $value   Value.
     * @param bool   $encrypt Encrypt value.
     * @return bool
     */
    public function update_meta( int $id, string $key, $value, bool $encrypt = false ): bool {
        return $this->save_meta( $id, $key, $value, $encrypt );
    }

    /**
     * Delete all meta.
     *
     * @param int $id Member ID.
     * @return bool
     */
    public function delete_all_meta( int $id ): bool {
        return false !== $this->wpdb->delete( SocietyPress::table( 'member_meta' ), array( 'member_id' => $id ) );
    }

    // =========================================================================
    // Surnames
    // =========================================================================

    /**
     * Get surnames.
     *
     * @param int $id Member ID.
     * @return array
     */
    public function get_surnames( int $id ): array {
        $table = SocietyPress::table( 'member_surnames' );
        return $this->wpdb->get_results( $this->wpdb->prepare(
            "SELECT * FROM {$table} WHERE member_id = %d ORDER BY surname_normalized",
            $id
        ) );
    }

    /**
     * Add surname.
     *
     * @param int    $id       Member ID.
     * @param string $surname  Surname.
     * @param string $variants Variants.
     * @param string $notes    Notes.
     * @return int|false
     */
    public function add_surname( int $id, string $surname, string $variants = '', string $notes = '' ) {
        $result = $this->wpdb->insert( SocietyPress::table( 'member_surnames' ), array(
            'member_id'          => $id,
            'surname'            => sanitize_text_field( $surname ),
            'surname_normalized' => strtoupper( trim( $surname ) ),
            'variants'           => sanitize_text_field( $variants ),
            'notes'              => sanitize_textarea_field( $notes ),
        ) );

        return false !== $result ? $this->wpdb->insert_id : false;
    }

    // =========================================================================
    // Tier
    // =========================================================================

    /**
     * Get member's tier.
     *
     * @param int $id Member ID.
     * @return object|null
     */
    public function get_tier( int $id ): ?object {
        $member = $this->get( $id );
        if ( ! $member ) {
            return null;
        }

        $table = SocietyPress::table( 'membership_tiers' );
        return $this->wpdb->get_row( $this->wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $member->membership_tier_id ) ) ?: null;
    }

    // =========================================================================
    // Status
    // =========================================================================

    /**
     * Update status.
     *
     * @param int    $id     Member ID.
     * @param string $status New status.
     * @return bool
     */
    public function update_status( int $id, string $status ): bool {
        if ( ! in_array( $status, self::STATUSES, true ) ) {
            return false;
        }

        $member = $this->get( $id );
        if ( ! $member ) {
            return false;
        }

        $old = $member->status;
        $result = $this->update( $id, array( 'status' => $status ) );

        if ( $result && $old !== $status ) {
            do_action( 'societypress_member_status_changed', $id, $status, $old );
        }

        return $result;
    }

    /**
     * Mark deceased.
     *
     * @param int    $id          Member ID.
     * @param string $date        Date of death.
     * @param string $reported_by Who reported.
     * @return bool
     */
    public function mark_deceased( int $id, string $date, string $reported_by = '' ): bool {
        return $this->update( $id, array(
            'status'            => 'deceased',
            'date_of_death'     => $date,
            'death_reported_by' => $reported_by,
            'directory_visible' => 0,
        ) );
    }

    // =========================================================================
    // Audit
    // =========================================================================

    /**
     * Log action.
     *
     * @param string $action      Action.
     * @param string $object_type Object type.
     * @param int    $object_id   Object ID.
     * @param array  $details     Details.
     */
    private function log_action( string $action, string $object_type, int $object_id, array $details = array() ): void {
        $this->wpdb->insert( SocietyPress::table( 'audit_log' ), array(
            'user_id'     => get_current_user_id(),
            'action'      => $action,
            'object_type' => $object_type,
            'object_id'   => $object_id,
            'details'     => wp_json_encode( $details ),
            'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent'  => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 255 ) : '',
        ) );
    }
}
