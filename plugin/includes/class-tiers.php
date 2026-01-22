<?php
/**
 * Membership Tiers Management
 *
 * Handles CRUD operations for membership tiers (Individual, Family, etc.).
 * Tiers define pricing, duration, and member capacity for each membership level.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SocietyPress_Tiers
 *
 * Manages membership tier definitions and operations.
 */
class SocietyPress_Tiers {

    /**
     * WordPress database object.
     *
     * @var wpdb
     */
    private $wpdb;

    /**
     * Tiers table name.
     *
     * @var string
     */
    private string $table;

    /**
     * Cached tiers for performance.
     *
     * @var array|null
     */
    private static ?array $cache = null;

    /**
     * Default tiers to create on plugin activation.
     *
     * These represent typical genealogical society membership levels.
     * Organizations can modify these after installation.
     *
     * @var array
     */
    private const DEFAULTS = array(
        array(
            'slug'            => 'individual',
            'name'            => 'Individual',
            'description'     => 'Standard membership for one person.',
            'price'           => 35.00,
            'duration_months' => 12,
            'max_members'     => 1,
            'sort_order'      => 10,
        ),
        array(
            'slug'            => 'family',
            'name'            => 'Family',
            'description'     => 'Membership for a household. Includes primary member plus one additional family member at the same address.',
            'price'           => 45.00,
            'duration_months' => 12,
            'max_members'     => 2,
            'sort_order'      => 20,
        ),
        array(
            'slug'            => 'student',
            'name'            => 'Student',
            'description'     => 'Discounted membership for full-time students with valid student ID.',
            'price'           => 20.00,
            'duration_months' => 12,
            'max_members'     => 1,
            'sort_order'      => 30,
        ),
        array(
            'slug'            => 'lifetime',
            'name'            => 'Lifetime',
            'description'     => 'One-time payment for permanent membership. Never worry about renewals again.',
            'price'           => 500.00,
            'duration_months' => 0, // 0 = never expires
            'max_members'     => 1,
            'sort_order'      => 40,
        ),
        array(
            'slug'            => 'institutional',
            'name'            => 'Institutional',
            'description'     => 'Membership for libraries, archives, and other organizations.',
            'price'           => 50.00,
            'duration_months' => 12,
            'max_members'     => 1,
            'sort_order'      => 50,
        ),
    );

    /**
     * Constructor.
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb  = $wpdb;
        $this->table = SocietyPress_Core::table( 'membership_tiers' );
    }

    /**
     * Create default tiers if none exist.
     *
     * Called during plugin activation. Only creates defaults if
     * the tiers table is empty, preserving any existing configuration.
     *
     * @return void
     */
    public function maybe_create_defaults(): void {
        // Check if any tiers exist
        $count = (int) $this->wpdb->get_var( "SELECT COUNT(*) FROM {$this->table}" );

        if ( $count > 0 ) {
            return;
        }

        // Insert default tiers
        foreach ( self::DEFAULTS as $tier ) {
            $this->create( $tier );
        }

        // Clear cache after creating defaults
        self::$cache = null;
    }

    /**
     * Get a single tier by ID.
     *
     * @param int $id Tier ID.
     * @return object|null Tier object or null if not found.
     */
    public function get( int $id ): ?object {
        $tier = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE id = %d",
                $id
            )
        );

        return $tier ?: null;
    }

    /**
     * Get a tier by slug.
     *
     * @param string $slug Tier slug (e.g., 'individual', 'family').
     * @return object|null Tier object or null if not found.
     */
    public function get_by_slug( string $slug ): ?object {
        $tier = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE slug = %s",
                $slug
            )
        );

        return $tier ?: null;
    }

    /**
     * Get all tiers.
     *
     * Returns cached results for performance. Use $force_refresh
     * to bypass cache when needed.
     *
     * @param bool $active_only Only return active tiers.
     * @param bool $force_refresh Bypass cache.
     * @return array Array of tier objects.
     */
    public function get_all( bool $active_only = false, bool $force_refresh = false ): array {
        $cache_key = $active_only ? 'active' : 'all';

        // Return cached if available and not forcing refresh
        if ( ! $force_refresh && isset( self::$cache[ $cache_key ] ) ) {
            return self::$cache[ $cache_key ];
        }

        $where = $active_only ? 'WHERE is_active = 1' : '';

        $tiers = $this->wpdb->get_results(
            "SELECT * FROM {$this->table} {$where} ORDER BY sort_order ASC, name ASC"
        );

        // Cache the results
        if ( null === self::$cache ) {
            self::$cache = array();
        }
        self::$cache[ $cache_key ] = $tiers ?: array();

        return self::$cache[ $cache_key ];
    }

    /**
     * Get active tiers only.
     *
     * Convenience method for the common use case of listing
     * tiers available for new memberships.
     *
     * @return array Array of active tier objects.
     */
    public function get_active(): array {
        return $this->get_all( true );
    }

    /**
     * Create a new tier.
     *
     * @param array $data Tier data.
     * @return int|false New tier ID or false on failure.
     */
    public function create( array $data ) {
        // Validate required fields
        if ( empty( $data['slug'] ) || empty( $data['name'] ) ) {
            return false;
        }

        // Sanitize slug
        $data['slug'] = sanitize_title( $data['slug'] );

        // Check for duplicate slug
        $existing = $this->get_by_slug( $data['slug'] );
        if ( $existing ) {
            return false;
        }

        // Prepare data with defaults
        $insert_data = array(
            'slug'            => $data['slug'],
            'name'            => sanitize_text_field( $data['name'] ),
            'description'     => isset( $data['description'] ) ? sanitize_textarea_field( $data['description'] ) : '',
            'price'           => isset( $data['price'] ) ? floatval( $data['price'] ) : 0.00,
            'duration_months' => isset( $data['duration_months'] ) ? absint( $data['duration_months'] ) : 12,
            'max_members'     => isset( $data['max_members'] ) ? absint( $data['max_members'] ) : 1,
            'is_active'       => isset( $data['is_active'] ) ? absint( $data['is_active'] ) : 1,
            'sort_order'      => isset( $data['sort_order'] ) ? absint( $data['sort_order'] ) : 0,
        );

        $result = $this->wpdb->insert(
            $this->table,
            $insert_data,
            array( '%s', '%s', '%s', '%f', '%d', '%d', '%d', '%d' )
        );

        if ( false === $result ) {
            return false;
        }

        // Clear cache
        self::$cache = null;

        return $this->wpdb->insert_id;
    }

    /**
     * Update an existing tier.
     *
     * @param int   $id   Tier ID.
     * @param array $data Data to update.
     * @return bool True on success, false on failure.
     */
    public function update( int $id, array $data ): bool {
        // Verify tier exists
        $tier = $this->get( $id );
        if ( ! $tier ) {
            return false;
        }

        // Build update data
        $update_data = array();
        $formats     = array();

        // Handle slug separately to check for duplicates
        if ( isset( $data['slug'] ) && $data['slug'] !== $tier->slug ) {
            $new_slug = sanitize_title( $data['slug'] );
            $existing = $this->get_by_slug( $new_slug );
            if ( $existing && $existing->id !== $id ) {
                return false; // Duplicate slug
            }
            $update_data['slug'] = $new_slug;
            $formats[]           = '%s';
        }

        // Standard text fields
        if ( isset( $data['name'] ) ) {
            $update_data['name'] = sanitize_text_field( $data['name'] );
            $formats[]           = '%s';
        }

        if ( isset( $data['description'] ) ) {
            $update_data['description'] = sanitize_textarea_field( $data['description'] );
            $formats[]                  = '%s';
        }

        // Numeric fields
        if ( isset( $data['price'] ) ) {
            $update_data['price'] = floatval( $data['price'] );
            $formats[]            = '%f';
        }

        if ( isset( $data['duration_months'] ) ) {
            $update_data['duration_months'] = absint( $data['duration_months'] );
            $formats[]                      = '%d';
        }

        if ( isset( $data['max_members'] ) ) {
            $update_data['max_members'] = absint( $data['max_members'] );
            $formats[]                  = '%d';
        }

        if ( isset( $data['is_active'] ) ) {
            $update_data['is_active'] = absint( $data['is_active'] );
            $formats[]                = '%d';
        }

        if ( isset( $data['sort_order'] ) ) {
            $update_data['sort_order'] = absint( $data['sort_order'] );
            $formats[]                 = '%d';
        }

        // Nothing to update
        if ( empty( $update_data ) ) {
            return true;
        }

        $result = $this->wpdb->update(
            $this->table,
            $update_data,
            array( 'id' => $id ),
            $formats,
            array( '%d' )
        );

        if ( false === $result ) {
            return false;
        }

        // Clear cache
        self::$cache = null;

        return true;
    }

    /**
     * Delete a tier.
     *
     * Will fail if any members are assigned to this tier.
     * Soft-delete by setting is_active = 0 is recommended instead.
     *
     * @param int $id Tier ID.
     * @return bool True on success, false on failure.
     */
    public function delete( int $id ): bool {
        // Check if any members use this tier
        $members_table = SocietyPress_Core::table( 'members' );
        $count         = (int) $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$members_table} WHERE membership_tier_id = %d",
                $id
            )
        );

        if ( $count > 0 ) {
            // Cannot delete tier with assigned members
            return false;
        }

        $result = $this->wpdb->delete(
            $this->table,
            array( 'id' => $id ),
            array( '%d' )
        );

        if ( false === $result ) {
            return false;
        }

        // Clear cache
        self::$cache = null;

        return true;
    }

    /**
     * Deactivate a tier (soft delete).
     *
     * Preferred over hard delete when tier has historical data.
     * Deactivated tiers won't show for new memberships but
     * existing members retain their tier assignment.
     *
     * @param int $id Tier ID.
     * @return bool True on success, false on failure.
     */
    public function deactivate( int $id ): bool {
        return $this->update( $id, array( 'is_active' => 0 ) );
    }

    /**
     * Activate a tier.
     *
     * @param int $id Tier ID.
     * @return bool True on success, false on failure.
     */
    public function activate( int $id ): bool {
        return $this->update( $id, array( 'is_active' => 1 ) );
    }

    /**
     * Get tiers formatted for dropdown select.
     *
     * @param bool $active_only Only include active tiers.
     * @return array Associative array of id => name.
     */
    public function get_dropdown_options( bool $active_only = true ): array {
        $tiers   = $this->get_all( $active_only );
        $options = array();

        foreach ( $tiers as $tier ) {
            $price_display    = '$' . number_format( $tier->price, 2 );
            $duration_display = $tier->duration_months > 0
                ? $tier->duration_months . ' mo'
                : 'Lifetime';

            $options[ $tier->id ] = sprintf(
                '%s (%s / %s)',
                $tier->name,
                $price_display,
                $duration_display
            );
        }

        return $options;
    }

    /**
     * Calculate expiration date based on tier duration.
     *
     * @param int         $tier_id    Tier ID.
     * @param string|null $start_date Start date (Y-m-d). Defaults to today.
     * @return string|null Expiration date (Y-m-d) or null for lifetime.
     */
    public function calculate_expiration( int $tier_id, ?string $start_date = null ): ?string {
        $tier = $this->get( $tier_id );
        if ( ! $tier ) {
            return null;
        }

        // Lifetime memberships don't expire
        if ( 0 === (int) $tier->duration_months ) {
            return null;
        }

        $start = $start_date ? new DateTime( $start_date ) : new DateTime();
        $start->modify( "+{$tier->duration_months} months" );

        return $start->format( 'Y-m-d' );
    }

    /**
     * Get count of members per tier.
     *
     * Useful for admin reports and statistics.
     *
     * @return array Associative array of tier_id => count.
     */
    public function get_member_counts(): array {
        $members_table = SocietyPress_Core::table( 'members' );

        $results = $this->wpdb->get_results(
            "SELECT membership_tier_id, COUNT(*) as count
             FROM {$members_table}
             WHERE status != 'cancelled'
             GROUP BY membership_tier_id",
            ARRAY_A
        );

        $counts = array();
        foreach ( $results as $row ) {
            $counts[ $row['membership_tier_id'] ] = (int) $row['count'];
        }

        return $counts;
    }

    /**
     * Get tier with member count.
     *
     * @param int $id Tier ID.
     * @return object|null Tier object with member_count property.
     */
    public function get_with_count( int $id ): ?object {
        $tier = $this->get( $id );
        if ( ! $tier ) {
            return null;
        }

        $members_table = SocietyPress_Core::table( 'members' );
        $tier->member_count = (int) $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$members_table}
                 WHERE membership_tier_id = %d AND status != 'cancelled'",
                $id
            )
        );

        return $tier;
    }

    /**
     * Clear the internal cache.
     *
     * Call this after making changes outside of this class.
     *
     * @return void
     */
    public function clear_cache(): void {
        self::$cache = null;
    }
}
