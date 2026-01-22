<?php
/**
 * Plugin Name: SocietyPress Core
 * Plugin URI: https://github.com/charles-stricklin/SocietyPress
 * Description: Membership management for genealogical and historical societies. Handles member registration, dues, renewals, directories, committees, and governance.
 * Version: 1.0.0
 * Author: Charles Stricklin
 * Author URI: https://charlesstricklin.com
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
define( 'SOCIETYPRESS_CORE_VERSION', '1.0.0' );

/**
 * Plugin directory path.
 */
define( 'SOCIETYPRESS_CORE_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'SOCIETYPRESS_CORE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename.
 */
define( 'SOCIETYPRESS_CORE_BASENAME', plugin_basename( __FILE__ ) );

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
final class SocietyPress_Core {

    /**
     * Single instance.
     *
     * @var SocietyPress_Core|null
     */
    private static ?SocietyPress_Core $instance = null;

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
     * Admin interface.
     *
     * @var SocietyPress_Admin|null
     */
    public ?SocietyPress_Admin $admin = null;

    /**
     * Get the single instance.
     *
     * @return SocietyPress_Core
     */
    public static function instance(): SocietyPress_Core {
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
        require_once SOCIETYPRESS_CORE_PATH . 'includes/class-database.php';
        require_once SOCIETYPRESS_CORE_PATH . 'includes/class-encryption.php';
        require_once SOCIETYPRESS_CORE_PATH . 'includes/class-members.php';
        require_once SOCIETYPRESS_CORE_PATH . 'includes/class-tiers.php';

        if ( is_admin() ) {
            require_once SOCIETYPRESS_CORE_PATH . 'admin/class-admin.php';
            require_once SOCIETYPRESS_CORE_PATH . 'admin/class-members-list-table.php';
            require_once SOCIETYPRESS_CORE_PATH . 'admin/class-import.php';
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
        update_option( 'societypress_version', SOCIETYPRESS_CORE_VERSION );

        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation.
     */
    public function deactivate(): void {
        wp_clear_scheduled_hook( 'societypress_check_expirations' );
        wp_clear_scheduled_hook( 'societypress_send_reminders' );
        flush_rewrite_rules();
    }

    /**
     * Initialize components.
     */
    public function init_components(): void {
        $this->database = new SocietyPress_Database();
        $this->members  = new SocietyPress_Members();
        $this->tiers    = new SocietyPress_Tiers();

        if ( is_admin() ) {
            $this->admin = new SocietyPress_Admin();
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
    }

    /**
     * Load text domain.
     */
    public function load_textdomain(): void {
        load_plugin_textdomain(
            'societypress',
            false,
            dirname( SOCIETYPRESS_CORE_BASENAME ) . '/languages'
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
 * @return SocietyPress_Core
 */
function societypress(): SocietyPress_Core {
    return SocietyPress_Core::instance();
}

// Initialize
societypress();
