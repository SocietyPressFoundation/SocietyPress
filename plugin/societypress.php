<?php
/**
 * Plugin Name: SocietyPress
 * Plugin URI: https://github.com/charles-stricklin/SocietyPress
 * Description: Membership management for genealogical and historical societies. Handles member registration, dues, renewals, directories, committees, and governance.
 * Version: 0.23d
 * Author: Charles Stricklin
 * Author URI: https://stricklindevelopment.com/studiopress/
 * License: Proprietary
 * Text Domain: societypress
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 *
 * @package SocietyPress
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Plugin version.
 */
define( 'SOCIETYPRESS_VERSION', '0.23d' );

/**
 * Plugin directory path.
 */
define( 'SOCIETYPRESS_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'SOCIETYPRESS_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename.
 */
define( 'SOCIETYPRESS_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Database table prefix for SocietyPress tables.
 */
define( 'SOCIETYPRESS_TABLE_PREFIX', 'sp_' );

/**
 * Encryption key option name.
 */
define( 'SOCIETYPRESS_ENCRYPTION_KEY_OPTION', 'societypress_encryption_key' );

/**
 * Main plugin class.
 *
 * Singleton pattern ensures only one instance runs.
 */
final class SocietyPress {

    /**
     * Single instance.
     *
     * @var SocietyPress|null
     */
    private static ?SocietyPress $instance = null;

    /**
     * Database manager.
     *
     * @var SocietyPress_Database|null
     */
    public ?SocietyPress_Database $database = null;

    /**
     * Members manager.
     *
     * @var SocietyPress_Members|null
     */
    public ?SocietyPress_Members $members = null;

    /**
     * Tiers manager.
     *
     * @var SocietyPress_Tiers|null
     */
    public ?SocietyPress_Tiers $tiers = null;

    /**
     * Events manager.
     *
     * @var SocietyPress_Events|null
     */
    public ?SocietyPress_Events $events = null;

    /**
     * User manager.
     *
     * @var SocietyPress_User_Manager|null
     */
    public ?SocietyPress_User_Manager $user_manager = null;

    /**
     * Admin interface.
     *
     * @var SocietyPress_Admin|null
     */
    public ?SocietyPress_Admin $admin = null;

    /**
     * Email notifications.
     *
     * @var SocietyPress_Notifications|null
     */
    public ?SocietyPress_Notifications $notifications = null;

    /**
     * License validation.
     *
     * @var SocietyPress_License|null
     */
    public ?SocietyPress_License $license = null;

    /**
     * Public directory.
     *
     * @var SocietyPress_Directory|null
     */
    public ?SocietyPress_Directory $directory = null;

    /**
     * Member portal.
     *
     * @var SocietyPress_Portal|null
     */
    public ?SocietyPress_Portal $portal = null;

    /**
     * Auto-updater.
     *
     * @var SocietyPress_Updater|null
     */
    public ?SocietyPress_Updater $updater = null;

    /**
     * Theme auto-updater.
     *
     * @var SocietyPress_Theme_Updater|null
     */
    public ?SocietyPress_Theme_Updater $theme_updater = null;

    /**
     * Get the single instance.
     *
     * @return SocietyPress
     */
    public static function instance(): SocietyPress {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Prevent cloning.
     */
    private function __clone() {}

    /**
     * Prevent unserializing.
     */
    public function __wakeup() {
        throw new Exception( 'Cannot unserialize singleton' );
    }

    /**
     * Load required files.
     */
    private function load_dependencies(): void {
        require_once SOCIETYPRESS_PATH . 'includes/class-database.php';
        require_once SOCIETYPRESS_PATH . 'includes/class-encryption.php';
        require_once SOCIETYPRESS_PATH . 'includes/class-members.php';
        require_once SOCIETYPRESS_PATH . 'includes/class-tiers.php';
        require_once SOCIETYPRESS_PATH . 'includes/class-events.php';
        require_once SOCIETYPRESS_PATH . 'includes/class-user-manager.php';
        require_once SOCIETYPRESS_PATH . 'includes/class-notifications.php';
        require_once SOCIETYPRESS_PATH . 'includes/class-license.php';
        require_once SOCIETYPRESS_PATH . 'includes/class-updater.php';
        require_once SOCIETYPRESS_PATH . 'includes/class-theme-updater.php';

        if ( is_admin() ) {
            require_once SOCIETYPRESS_PATH . 'admin/class-admin.php';
            require_once SOCIETYPRESS_PATH . 'admin/class-members-list-table.php';
            require_once SOCIETYPRESS_PATH . 'admin/class-import.php';
            require_once SOCIETYPRESS_PATH . 'admin/class-dashboard-widgets.php';
        }

        if ( ! is_admin() ) {
            require_once SOCIETYPRESS_PATH . 'public/class-directory.php';
            require_once SOCIETYPRESS_PATH . 'public/class-portal.php';
        }
    }

    /**
     * Register hooks.
     */
    private function init_hooks(): void {
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        add_action( 'plugins_loaded', array( $this, 'init_components' ) );
        add_action( 'init', array( $this, 'load_textdomain' ) );
    }

    /**
     * Plugin activation.
     */
    public function activate(): void {
        // Create database tables
        $this->database = new SocietyPress_Database();
        $this->database->install();

        // Generate encryption key if needed
        if ( ! get_option( SOCIETYPRESS_ENCRYPTION_KEY_OPTION ) ) {
            $key = SocietyPress_Encryption::generate_key();
            update_option( SOCIETYPRESS_ENCRYPTION_KEY_OPTION, $key, false );
        }

        // Create default tiers
        $tiers = new SocietyPress_Tiers();
        $tiers->maybe_create_defaults();

        // Add capabilities
        $this->add_capabilities();

        // Store version
        update_option( 'societypress_version', SOCIETYPRESS_VERSION );

        // Schedule cron jobs
        if ( ! wp_next_scheduled( 'societypress_send_reminders' ) ) {
            wp_schedule_event( strtotime( 'tomorrow 9:00am' ), 'daily', 'societypress_send_reminders' );
        }

        if ( ! wp_next_scheduled( 'societypress_check_license' ) ) {
            wp_schedule_event( time(), 'daily', 'societypress_check_license' );
        }

        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation.
     */
    public function deactivate(): void {
        wp_clear_scheduled_hook( 'societypress_check_expirations' );
        wp_clear_scheduled_hook( 'societypress_send_reminders' );
        wp_clear_scheduled_hook( 'societypress_check_license' );
        flush_rewrite_rules();
    }

    /**
     * Initialize components.
     */
    public function init_components(): void {
        $this->database      = new SocietyPress_Database();
        $this->members       = new SocietyPress_Members();
        $this->tiers         = new SocietyPress_Tiers();
        $this->events        = new SocietyPress_Events();
        $this->user_manager  = new SocietyPress_User_Manager();
        $this->notifications = new SocietyPress_Notifications();
        $this->license       = new SocietyPress_License();
        $this->updater       = new SocietyPress_Updater( SOCIETYPRESS_BASENAME, SOCIETYPRESS_VERSION, $this->license );

        // Initialize theme updater if theme is active
        $theme = wp_get_theme( 'societypress' );
        if ( $theme->exists() ) {
            $this->theme_updater = new SocietyPress_Theme_Updater( 'societypress', $theme->get( 'Version' ), $this->license );
        }

        if ( is_admin() ) {
            $this->admin = new SocietyPress_Admin();
        }

        if ( ! is_admin() ) {
            $this->directory = new SocietyPress_Directory();
            $this->portal    = new SocietyPress_Portal();
        }
    }

    /**
     * Add capabilities to administrator role.
     */
    private function add_capabilities(): void {
        $admin = get_role( 'administrator' );
        if ( $admin ) {
            $admin->add_cap( 'manage_society_members', true );
            $admin->add_cap( 'view_society_reports', true );
            $admin->add_cap( 'export_society_members', true );
        }

        $subscriber = get_role( 'subscriber' );
        if ( $subscriber ) {
            $subscriber->add_cap( 'access_member_portal', true );
        }
    }

    /**
     * Load text domain.
     */
    public function load_textdomain(): void {
        load_plugin_textdomain(
            'societypress',
            false,
            dirname( SOCIETYPRESS_BASENAME ) . '/languages'
        );
    }

    /**
     * Get table name with prefixes.
     *
     * @param string $table Table name without prefix.
     * @return string Full table name.
     */
    public static function table( string $table ): string {
        global $wpdb;
        return $wpdb->prefix . SOCIETYPRESS_TABLE_PREFIX . $table;
    }
}

/**
 * Get the plugin instance.
 *
 * @return SocietyPress
 */
function societypress(): SocietyPress {
    return SocietyPress::instance();
}

// Initialize
societypress();
