<?php
/**
 * Database Installation and Management
 *
 * Creates and manages all plugin database tables.
 * Uses WordPress dbDelta() for safe table creation/modification.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SocietyPress_Database
 *
 * Handles database table creation and management.
 */
class SocietyPress_Database {

    /**
     * WordPress database object.
     *
     * @var wpdb
     */
    private $wpdb;

    /**
     * Charset and collation.
     *
     * @var string
     */
    private string $charset_collate;

    /**
     * Constructor.
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb            = $wpdb;
        $this->charset_collate = $wpdb->get_charset_collate();
    }

    /**
     * Get table name with prefix.
     *
     * @param string $table Table name without prefix.
     * @return string Full table name.
     */
    private function table( string $table ): string {
        return $this->wpdb->prefix . SOCIETYPRESS_TABLE_PREFIX . $table;
    }

    /**
     * Install all database tables.
     */
    public function install(): void {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $this->create_members_table();
        $this->create_membership_tiers_table();
        $this->create_member_meta_table();
        $this->create_member_contact_table();
        $this->create_member_surnames_table();
        $this->create_member_research_areas_table();
        $this->create_member_relationships_table();
        $this->create_payments_table();
        $this->create_renewal_reminders_table();
        $this->create_positions_table();
        $this->create_position_holders_table();
        $this->create_committees_table();
        $this->create_committee_members_table();
        $this->create_audit_log_table();
        $this->create_licenses_table();
        $this->create_event_slots_table();
        $this->create_event_registrations_table();
    }

    /**
     * Members table.
     *
     * Core member record linked to WordPress user.
     */
    private function create_members_table(): void {
        $table = $this->table( 'members' );

        $sql = "CREATE TABLE {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED DEFAULT NULL,
            membership_tier_id BIGINT(20) UNSIGNED NOT NULL,
            status ENUM('active','expired','pending','cancelled','deceased') NOT NULL DEFAULT 'pending',
            first_name VARCHAR(100) NOT NULL,
            middle_name VARCHAR(100) DEFAULT NULL,
            last_name VARCHAR(100) NOT NULL,
            photo_id BIGINT(20) UNSIGNED DEFAULT NULL,
            birth_date DATE DEFAULT NULL,
            birth_year_only TINYINT(1) DEFAULT 0,
            join_date DATE NOT NULL,
            expiration_date DATE DEFAULT NULL,
            auto_renew TINYINT(1) NOT NULL DEFAULT 0,
            directory_visible TINYINT(1) NOT NULL DEFAULT 1,
            show_birthday_in_directory TINYINT(1) NOT NULL DEFAULT 0,
            date_of_death DATE DEFAULT NULL,
            death_reported_by VARCHAR(255) DEFAULT NULL,
            how_heard_about_us VARCHAR(255) DEFAULT NULL,
            communication_preference ENUM('email','mail','both') NOT NULL DEFAULT 'email',
            portal_last_login DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY membership_tier_id (membership_tier_id),
            KEY status (status),
            KEY expiration_date (expiration_date),
            KEY last_name (last_name),
            KEY directory_visible (directory_visible),
            KEY portal_last_login (portal_last_login)
        ) {$this->charset_collate};";

        dbDelta( $sql );
    }

    /**
     * Membership tiers table.
     */
    private function create_membership_tiers_table(): void {
        $table = $this->table( 'membership_tiers' );

        $sql = "CREATE TABLE {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            slug VARCHAR(50) NOT NULL,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            duration_months INT NOT NULL DEFAULT 12,
            max_members INT NOT NULL DEFAULT 1,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY is_active (is_active),
            KEY sort_order (sort_order)
        ) {$this->charset_collate};";

        dbDelta( $sql );
    }

    /**
     * Member meta table.
     *
     * Flexible key-value storage for additional data.
     */
    private function create_member_meta_table(): void {
        $table = $this->table( 'member_meta' );

        $sql = "CREATE TABLE {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            member_id BIGINT(20) UNSIGNED NOT NULL,
            meta_key VARCHAR(255) NOT NULL,
            meta_value LONGTEXT,
            is_encrypted TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY member_id (member_id),
            KEY meta_key (meta_key(191))
        ) {$this->charset_collate};";

        dbDelta( $sql );
    }

    /**
     * Member contact table.
     *
     * Stores contact info separately for easier access control.
     */
    private function create_member_contact_table(): void {
        $table = $this->table( 'member_contact' );

        $sql = "CREATE TABLE {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            member_id BIGINT(20) UNSIGNED NOT NULL,
            primary_email VARCHAR(255) NOT NULL,
            secondary_email VARCHAR(255) DEFAULT NULL,
            home_phone VARCHAR(50) DEFAULT NULL,
            cell_phone VARCHAR(50) DEFAULT NULL,
            work_phone VARCHAR(50) DEFAULT NULL,
            preferred_phone ENUM('home','cell','work') DEFAULT 'cell',
            street_address TEXT,
            address_line_2 VARCHAR(255) DEFAULT NULL,
            city VARCHAR(100) DEFAULT NULL,
            state_province VARCHAR(100) DEFAULT NULL,
            postal_code VARCHAR(20) DEFAULT NULL,
            country VARCHAR(100) NOT NULL DEFAULT 'USA',
            mailing_address_different TINYINT(1) NOT NULL DEFAULT 0,
            mailing_street_address TEXT,
            mailing_address_line_2 VARCHAR(255) DEFAULT NULL,
            mailing_city VARCHAR(100) DEFAULT NULL,
            mailing_state_province VARCHAR(100) DEFAULT NULL,
            mailing_postal_code VARCHAR(20) DEFAULT NULL,
            mailing_country VARCHAR(100) DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY member_id (member_id),
            KEY primary_email (primary_email)
        ) {$this->charset_collate};";

        dbDelta( $sql );
    }

    /**
     * Member surnames table.
     *
     * Surnames being researched, normalized for search.
     */
    private function create_member_surnames_table(): void {
        $table = $this->table( 'member_surnames' );

        $sql = "CREATE TABLE {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            member_id BIGINT(20) UNSIGNED NOT NULL,
            surname VARCHAR(100) NOT NULL,
            surname_normalized VARCHAR(100) NOT NULL,
            variants VARCHAR(255) DEFAULT NULL,
            notes TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY member_id (member_id),
            KEY surname_normalized (surname_normalized)
        ) {$this->charset_collate};";

        dbDelta( $sql );
    }

    /**
     * Member research areas table.
     */
    private function create_member_research_areas_table(): void {
        $table = $this->table( 'member_research_areas' );

        $sql = "CREATE TABLE {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            member_id BIGINT(20) UNSIGNED NOT NULL,
            area_type ENUM('county','state','country','region','other') NOT NULL DEFAULT 'county',
            area_name VARCHAR(255) NOT NULL,
            time_period VARCHAR(100) DEFAULT NULL,
            notes TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY member_id (member_id),
            KEY area_name (area_name(191))
        ) {$this->charset_collate};";

        dbDelta( $sql );
    }

    /**
     * Member relationships table.
     */
    private function create_member_relationships_table(): void {
        $table = $this->table( 'member_relationships' );

        $sql = "CREATE TABLE {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            member_id BIGINT(20) UNSIGNED NOT NULL,
            related_member_id BIGINT(20) UNSIGNED NOT NULL,
            relationship_type ENUM('spouse','family_member','referred_by') NOT NULL,
            notes VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY member_id (member_id),
            KEY related_member_id (related_member_id),
            UNIQUE KEY unique_relationship (member_id, related_member_id, relationship_type)
        ) {$this->charset_collate};";

        dbDelta( $sql );
    }

    /**
     * Payments table.
     */
    private function create_payments_table(): void {
        $table = $this->table( 'payments' );

        $sql = "CREATE TABLE {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            member_id BIGINT(20) UNSIGNED NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            currency VARCHAR(3) NOT NULL DEFAULT 'USD',
            gateway VARCHAR(50) NOT NULL DEFAULT 'manual',
            gateway_txn_id VARCHAR(255) DEFAULT NULL,
            payment_type ENUM('new','renewal','donation','other') NOT NULL DEFAULT 'new',
            status ENUM('completed','pending','failed','refunded') NOT NULL DEFAULT 'pending',
            notes TEXT,
            processed_by BIGINT(20) UNSIGNED DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY member_id (member_id),
            KEY status (status),
            KEY gateway (gateway),
            KEY created_at (created_at)
        ) {$this->charset_collate};";

        dbDelta( $sql );
    }

    /**
     * Renewal reminders table.
     */
    private function create_renewal_reminders_table(): void {
        $table = $this->table( 'renewal_reminders' );

        $sql = "CREATE TABLE {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            member_id BIGINT(20) UNSIGNED NOT NULL,
            reminder_type VARCHAR(50) NOT NULL,
            email_sent_to VARCHAR(255) DEFAULT NULL,
            email_opened TINYINT(1) NOT NULL DEFAULT 0,
            email_opened_at DATETIME DEFAULT NULL,
            sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY member_id (member_id),
            KEY reminder_type (reminder_type),
            UNIQUE KEY unique_reminder (member_id, reminder_type)
        ) {$this->charset_collate};";

        dbDelta( $sql );
    }

    /**
     * Positions table (officers, board).
     */
    private function create_positions_table(): void {
        $table = $this->table( 'positions' );

        $sql = "CREATE TABLE {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            slug VARCHAR(50) NOT NULL,
            title VARCHAR(100) NOT NULL,
            description TEXT,
            is_board_member TINYINT(1) NOT NULL DEFAULT 0,
            is_officer TINYINT(1) NOT NULL DEFAULT 0,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY sort_order (sort_order)
        ) {$this->charset_collate};";

        dbDelta( $sql );
    }

    /**
     * Position holders table.
     */
    private function create_position_holders_table(): void {
        $table = $this->table( 'position_holders' );

        $sql = "CREATE TABLE {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            position_id BIGINT(20) UNSIGNED NOT NULL,
            member_id BIGINT(20) UNSIGNED NOT NULL,
            term_start DATE NOT NULL,
            term_end DATE DEFAULT NULL,
            is_current TINYINT(1) NOT NULL DEFAULT 1,
            notes TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY position_id (position_id),
            KEY member_id (member_id),
            KEY is_current (is_current),
            KEY term_start (term_start)
        ) {$this->charset_collate};";

        dbDelta( $sql );
    }

    /**
     * Committees table.
     */
    private function create_committees_table(): void {
        $table = $this->table( 'committees' );

        $sql = "CREATE TABLE {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            slug VARCHAR(50) NOT NULL,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            is_standing TINYINT(1) NOT NULL DEFAULT 1,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY is_active (is_active),
            KEY sort_order (sort_order)
        ) {$this->charset_collate};";

        dbDelta( $sql );
    }

    /**
     * Committee members table.
     */
    private function create_committee_members_table(): void {
        $table = $this->table( 'committee_members' );

        $sql = "CREATE TABLE {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            committee_id BIGINT(20) UNSIGNED NOT NULL,
            member_id BIGINT(20) UNSIGNED NOT NULL,
            role ENUM('chair','co_chair','member') NOT NULL DEFAULT 'member',
            joined_date DATE NOT NULL,
            left_date DATE DEFAULT NULL,
            notes TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY committee_id (committee_id),
            KEY member_id (member_id),
            KEY role (role),
            UNIQUE KEY unique_membership (committee_id, member_id, joined_date)
        ) {$this->charset_collate};";

        dbDelta( $sql );
    }

    /**
     * Audit log table.
     */
    private function create_audit_log_table(): void {
        $table = $this->table( 'audit_log' );

        $sql = "CREATE TABLE {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            action VARCHAR(50) NOT NULL,
            object_type VARCHAR(50) NOT NULL,
            object_id BIGINT(20) UNSIGNED NOT NULL,
            details TEXT,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY action (action),
            KEY object_type (object_type),
            KEY object_id (object_id),
            KEY created_at (created_at)
        ) {$this->charset_collate};";

        dbDelta( $sql );
    }

    /**
     * Licenses table.
     *
     * Commercial license validation and tracking.
     */
    private function create_licenses_table(): void {
        $table = $this->table( 'licenses' );

        $sql = "CREATE TABLE {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            license_key VARCHAR(255) NOT NULL,
            license_email VARCHAR(255) NOT NULL,
            license_type ENUM('site','multisite','developer') NOT NULL DEFAULT 'site',
            status ENUM('active','expired','invalid','suspended') NOT NULL DEFAULT 'active',
            site_url VARCHAR(255) DEFAULT NULL,
            activation_date DATETIME DEFAULT NULL,
            expiration_date DATE DEFAULT NULL,
            last_check DATETIME DEFAULT NULL,
            check_failures INT NOT NULL DEFAULT 0,
            grace_period_ends DATETIME DEFAULT NULL,
            remote_data TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY license_key (license_key),
            KEY status (status),
            KEY expiration_date (expiration_date),
            KEY last_check (last_check)
        ) {$this->charset_collate};";

        dbDelta( $sql );
    }

    /**
     * Event slots table.
     *
     * WHY: Allows events to have multiple time slots (e.g., 10-11 AM, 11-12 PM)
     *      for members to register for specific times with capacity tracking.
     */
    private function create_event_slots_table(): void {
        $table = $this->table( 'event_slots' );

        $sql = "CREATE TABLE {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            event_id BIGINT(20) UNSIGNED NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            capacity INT UNSIGNED DEFAULT NULL,
            description VARCHAR(255) DEFAULT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY event_id (event_id),
            KEY is_active (is_active),
            KEY sort_order (sort_order)
        ) {$this->charset_collate};";

        dbDelta( $sql );
    }

    /**
     * Event registrations table.
     *
     * WHY: Tracks which members have registered for which event time slots,
     *      including status (confirmed, cancelled, waitlist) and admin notes.
     */
    private function create_event_registrations_table(): void {
        $table = $this->table( 'event_registrations' );

        $sql = "CREATE TABLE {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            slot_id BIGINT(20) UNSIGNED NOT NULL,
            member_id BIGINT(20) UNSIGNED NOT NULL,
            status ENUM('confirmed','cancelled','waitlist') NOT NULL DEFAULT 'confirmed',
            registered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            registered_by BIGINT(20) UNSIGNED DEFAULT NULL,
            cancelled_at DATETIME DEFAULT NULL,
            notes TEXT,
            PRIMARY KEY (id),
            KEY slot_id (slot_id),
            KEY member_id (member_id),
            KEY status (status),
            KEY registered_at (registered_at),
            UNIQUE KEY unique_registration (slot_id, member_id)
        ) {$this->charset_collate};";

        dbDelta( $sql );
    }

    /**
     * Drop all plugin tables.
     *
     * Called from uninstall.php only.
     */
    public function uninstall(): void {
        $tables = array(
            'event_registrations',
            'event_slots',
            'licenses',
            'audit_log',
            'committee_members',
            'committees',
            'position_holders',
            'positions',
            'renewal_reminders',
            'payments',
            'member_relationships',
            'member_research_areas',
            'member_surnames',
            'member_contact',
            'member_meta',
            'members',
            'membership_tiers',
        );

        foreach ( $tables as $table ) {
            $this->wpdb->query( "DROP TABLE IF EXISTS {$this->table( $table )}" );
        }
    }
}
