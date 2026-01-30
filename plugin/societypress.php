<?php
/**
 * Plugin Name: SocietyPress
 * Plugin URI: https://getsocietypress.org
 * Description: Membership management for genealogical and historical societies. Handles member registration, dues, renewals, directories, committees, and governance.
 * Version: 0.42d
 * Author: Stricklin Development
 * Author URI: https://stricklindevelopment.com/
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
define( 'SOCIETYPRESS_VERSION', '0.42d' );

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
     * Public join form.
     *
     * @var SocietyPress_Join_Form|null
     */
    public ?SocietyPress_Join_Form $join_form = null;

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
            require_once SOCIETYPRESS_PATH . 'admin/class-import-events.php';
            require_once SOCIETYPRESS_PATH . 'admin/class-dashboard-widgets.php';
        }

        if ( ! is_admin() ) {
            require_once SOCIETYPRESS_PATH . 'public/class-directory.php';
            require_once SOCIETYPRESS_PATH . 'public/class-portal.php';
            require_once SOCIETYPRESS_PATH . 'public/class-join-form.php';
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
        add_action( 'init', array( $this, 'register_image_sizes' ) );

        // Replace {{organization_name}} in nav menu items
        add_filter( 'nav_menu_item_title', array( $this, 'replace_menu_placeholders' ), 10, 2 );

        // Simple utility shortcodes
        add_shortcode( 'sp_year', array( $this, 'shortcode_year' ) );
        add_shortcode( 'sp_organization', array( $this, 'shortcode_organization' ) );
    }

    /**
     * Shortcode: Current year.
     *
     * Usage: [sp_year]
     * Output: 2026
     *
     * @return string Current year.
     */
    public function shortcode_year(): string {
        return date_i18n( 'Y' );
    }

    /**
     * Shortcode: Organization name.
     *
     * Usage: [sp_organization]
     * Output: Organization name from settings (or site name as fallback)
     *
     * @return string Organization name.
     */
    public function shortcode_organization(): string {
        return esc_html( societypress_get_setting( 'organization_name', get_bloginfo( 'name' ) ) );
    }

    /**
     * Replace placeholders in navigation menu item titles.
     *
     * WHY: Allows admins to use {{organization_name}} in menu labels
     *      so they don't have to update menus if the org name changes.
     *
     * @param string   $title Menu item title.
     * @param WP_Post  $item  Menu item object.
     * @return string Modified title.
     */
    public function replace_menu_placeholders( string $title, $item ): string {
        if ( strpos( $title, '{{organization_name}}' ) !== false ) {
            $org_name = societypress_get_setting( 'organization_name', get_bloginfo( 'name' ) );
            $title = str_replace( '{{organization_name}}', $org_name, $title );
        }
        return $title;
    }

    /**
     * Register custom image sizes for member photos.
     */
    public function register_image_sizes(): void {
        // Square crop for member photos (displays as circle via CSS)
        add_image_size( 'sp-member-photo', 200, 200, true );
        add_image_size( 'sp-member-photo-small', 80, 80, true );
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
        $this->database      = new SocietyPress_Database();
        $this->members       = new SocietyPress_Members();
        $this->tiers         = new SocietyPress_Tiers();
        $this->events        = new SocietyPress_Events();
        $this->user_manager  = new SocietyPress_User_Manager();
        $this->notifications = new SocietyPress_Notifications();
        $this->license       = new SocietyPress_License();
        $this->updater       = new SocietyPress_Updater( SOCIETYPRESS_BASENAME, SOCIETYPRESS_VERSION );

        // Initialize theme updater if theme is active
        $theme = wp_get_theme( 'societypress' );
        if ( $theme->exists() ) {
            $this->theme_updater = new SocietyPress_Theme_Updater( 'societypress', $theme->get( 'Version' ) );
        }

        if ( is_admin() ) {
            $this->admin = new SocietyPress_Admin();
        }

        if ( ! is_admin() ) {
            $this->directory = new SocietyPress_Directory();
            $this->portal    = new SocietyPress_Portal();
            $this->join_form = new SocietyPress_Join_Form();
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

/**
 * Get a plugin setting.
 *
 * Helper function that works on both frontend and admin.
 *
 * @param string $key     Setting key.
 * @param mixed  $default Default value if not set.
 * @return mixed Setting value.
 */
function societypress_get_setting( string $key, $default = null ) {
    static $defaults = null;

    // Cache defaults to avoid repeated array creation
    if ( null === $defaults ) {
        $defaults = array(
            'members_per_page'          => 20,
            'member_photos_enabled'     => true,
            'dashboard_widgets_enabled' => true,
            'dashboard_expiring_days'   => 30,
            'dashboard_recent_days'     => 30,
            'organization_name'         => get_bloginfo( 'name' ),
            'admin_email'               => get_option( 'admin_email' ),
            'email_from_name'           => get_bloginfo( 'name' ),
            'email_from_email'          => get_option( 'admin_email' ),
            'expiration_model'          => 'calendar_year',
            'directory_per_page'        => 24,
            'portal_enabled'            => true,
        );
    }

    $settings = get_option( 'societypress_settings', array() );

    if ( null === $default && isset( $defaults[ $key ] ) ) {
        $default = $defaults[ $key ];
    }

    return $settings[ $key ] ?? $default;
}

/**
 * Get valid US state and territory codes.
 *
 * WHY: Provides consistent list for validation and autocomplete.
 *      Includes US states, DC, and territories.
 *
 * @return array Associative array of code => name.
 */
function societypress_get_us_states(): array {
    return array(
        'AL' => 'Alabama',
        'AK' => 'Alaska',
        'AZ' => 'Arizona',
        'AR' => 'Arkansas',
        'CA' => 'California',
        'CO' => 'Colorado',
        'CT' => 'Connecticut',
        'DE' => 'Delaware',
        'DC' => 'District of Columbia',
        'FL' => 'Florida',
        'GA' => 'Georgia',
        'HI' => 'Hawaii',
        'ID' => 'Idaho',
        'IL' => 'Illinois',
        'IN' => 'Indiana',
        'IA' => 'Iowa',
        'KS' => 'Kansas',
        'KY' => 'Kentucky',
        'LA' => 'Louisiana',
        'ME' => 'Maine',
        'MD' => 'Maryland',
        'MA' => 'Massachusetts',
        'MI' => 'Michigan',
        'MN' => 'Minnesota',
        'MS' => 'Mississippi',
        'MO' => 'Missouri',
        'MT' => 'Montana',
        'NE' => 'Nebraska',
        'NV' => 'Nevada',
        'NH' => 'New Hampshire',
        'NJ' => 'New Jersey',
        'NM' => 'New Mexico',
        'NY' => 'New York',
        'NC' => 'North Carolina',
        'ND' => 'North Dakota',
        'OH' => 'Ohio',
        'OK' => 'Oklahoma',
        'OR' => 'Oregon',
        'PA' => 'Pennsylvania',
        'RI' => 'Rhode Island',
        'SC' => 'South Carolina',
        'SD' => 'South Dakota',
        'TN' => 'Tennessee',
        'TX' => 'Texas',
        'UT' => 'Utah',
        'VT' => 'Vermont',
        'VA' => 'Virginia',
        'WA' => 'Washington',
        'WV' => 'West Virginia',
        'WI' => 'Wisconsin',
        'WY' => 'Wyoming',
        // Territories
        'AS' => 'American Samoa',
        'GU' => 'Guam',
        'MP' => 'Northern Mariana Islands',
        'PR' => 'Puerto Rico',
        'VI' => 'U.S. Virgin Islands',
        // Military
        'AA' => 'Armed Forces Americas',
        'AE' => 'Armed Forces Europe',
        'AP' => 'Armed Forces Pacific',
    );
}

/**
 * Validate a state/province code.
 *
 * WHY: Ensures state codes are valid before saving.
 *      Accepts 2-letter US codes or allows empty/international.
 *
 * @param string $code The state code to validate.
 * @return bool True if valid US code or empty, false otherwise.
 */
function societypress_is_valid_state( string $code ): bool {
    // Empty is allowed (international members)
    if ( empty( $code ) ) {
        return true;
    }

    // Normalize to uppercase
    $code = strtoupper( trim( $code ) );

    // Check against valid US states
    $valid_states = societypress_get_us_states();
    return isset( $valid_states[ $code ] );
}

/**
 * Normalize a state code.
 *
 * WHY: Converts input to proper 2-letter uppercase format.
 *
 * @param string $code The state code to normalize.
 * @return string Normalized code (uppercase, trimmed).
 */
function societypress_normalize_state( string $code ): string {
    return strtoupper( trim( $code ) );
}

// Initialize
societypress();
