<?php
/**
 * SocietyPress Custom Admin Router
 *
 * Provides a completely separate admin interface at /sp-admin/ for volunteer
 * webmasters. This keeps volunteers away from the WordPress dashboard complexity
 * while giving them a simple, focused interface for managing society data.
 *
 * WHY THIS EXISTS:
 * The target user is an 80-year-old volunteer who is terrified of breaking
 * something. They should never see wp-admin. This router intercepts requests
 * to /sp-admin/* and renders our custom admin interface instead of WordPress.
 *
 * @package SocietyPress
 * @since 0.59
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SP_Admin_Router
 *
 * Handles routing for the custom /sp-admin/ interface.
 */
class SP_Admin_Router {

    /**
     * Singleton instance.
     *
     * @var SP_Admin_Router|null
     */
    private static ?SP_Admin_Router $instance = null;

    /**
     * Current route being processed.
     *
     * @var string
     */
    private string $current_route = 'dashboard';

    /**
     * Current route parameters (IDs, actions, etc.).
     *
     * @var array
     */
    private array $route_params = [];

    /**
     * Available modules and their settings.
     *
     * WHY: Centralizes module configuration. Each module has an icon, label,
     * and whether it requires explicit permission (dashboard/help are always accessible).
     *
     * @var array
     */
    private array $modules = [
        'dashboard'     => [ 'icon' => '🏠', 'label' => 'Dashboard',     'requires_permission' => false ],
        'members'       => [ 'icon' => '👥', 'label' => 'Members',       'requires_permission' => true ],
        'events'        => [ 'icon' => '📅', 'label' => 'Events',        'requires_permission' => true ],
        'newsletters'   => [ 'icon' => '📰', 'label' => 'Newsletters',   'requires_permission' => true ],
        'groups'        => [ 'icon' => '🏷️', 'label' => 'Groups',        'requires_permission' => true ],
        'leadership'    => [ 'icon' => '👔', 'label' => 'Leadership',    'requires_permission' => true ],
        'transactions'  => [ 'icon' => '💰', 'label' => 'Transactions',  'requires_permission' => true ],
        'pages'         => [ 'icon' => '📄', 'label' => 'Pages',         'requires_permission' => true ],
        'help'          => [ 'icon' => '❓', 'label' => 'Help',          'requires_permission' => false ],
    ];

    /**
     * Get singleton instance.
     *
     * @return SP_Admin_Router
     */
    public static function instance(): SP_Admin_Router {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor for singleton.
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks.
     *
     * WHY: We hook into multiple points:
     * - init: Register rewrite rules and query vars
     * - template_redirect: Intercept /sp-admin/ requests early
     * - admin_init: Block wp-admin for volunteers
     */
    private function init_hooks(): void {
        // Register rewrite rules
        add_action( 'init', [ $this, 'register_rewrite_rules' ] );
        add_filter( 'query_vars', [ $this, 'register_query_vars' ] );

        // Handle /sp-admin/ requests
        add_action( 'template_redirect', [ $this, 'handle_request' ], 1 );

        // Block wp-admin for volunteers
        add_action( 'admin_init', [ $this, 'block_wp_admin_for_volunteers' ] );

        // Flush rewrite rules if needed (on plugin activation)
        add_action( 'wp_loaded', [ $this, 'maybe_flush_rewrite_rules' ] );
    }

    /**
     * Register rewrite rules for /sp-admin/.
     *
     * WHY: Using rewrite rules gives us clean URLs like /sp-admin/members/123/edit
     * instead of ugly query strings. The regex captures everything after /sp-admin/
     * so we can parse it ourselves.
     */
    public function register_rewrite_rules(): void {
        // Match /sp-admin/ and everything after it
        add_rewrite_rule(
            '^sp-admin/?(.*)$',
            'index.php?sp_admin_route=$matches[1]',
            'top'
        );
    }

    /**
     * Register query vars.
     *
     * @param array $vars Existing query vars.
     * @return array Modified query vars.
     */
    public function register_query_vars( array $vars ): array {
        $vars[] = 'sp_admin_route';
        return $vars;
    }

    /**
     * Maybe flush rewrite rules.
     *
     * WHY: Rewrite rules are cached in the database. If our rule isn't there,
     * we need to flush. We use an option to track whether we've flushed to
     * avoid doing it on every page load (expensive operation).
     */
    public function maybe_flush_rewrite_rules(): void {
        if ( get_option( 'sp_admin_rewrite_rules_flushed' ) !== SOCIETYPRESS_VERSION ) {
            flush_rewrite_rules();
            update_option( 'sp_admin_rewrite_rules_flushed', SOCIETYPRESS_VERSION );
        }
    }

    /**
     * Handle incoming /sp-admin/ requests.
     *
     * WHY: This is the main entry point. We check if this is an sp-admin request,
     * verify the user is logged in and authorized, then route to the appropriate view.
     */
    public function handle_request(): void {
        global $wp_query;

        // Check if this is an sp-admin request
        if ( ! isset( $wp_query->query_vars['sp_admin_route'] ) ) {
            return;
        }

        // Must be logged in
        if ( ! is_user_logged_in() ) {
            wp_redirect( wp_login_url( home_url( '/' . $this->get_current_url_path() ) ) );
            exit;
        }

        // Must have sp_access_admin capability (volunteers and admins)
        if ( ! current_user_can( 'sp_access_admin' ) && ! current_user_can( 'administrator' ) ) {
            wp_die(
                '<h1>Access Denied</h1><p>You do not have permission to access this area.</p>',
                'Access Denied',
                [ 'response' => 403 ]
            );
        }

        // Parse the route
        $route_string = $wp_query->query_vars['sp_admin_route'];
        $this->parse_route( $route_string );

        // Check module-level permission
        $module = $this->get_current_module();
        if ( ! $this->user_can_access_module( $module ) ) {
            wp_redirect( home_url( '/sp-admin/' ) );
            exit;
        }

        // Render the admin interface
        $this->render();
        exit;
    }

    /**
     * Parse the route string into module, action, and parameters.
     *
     * Examples:
     * - "" or "/" -> dashboard
     * - "members" -> members list
     * - "members/123" -> member detail (id=123)
     * - "members/123/edit" -> member edit
     * - "members/new" -> new member form
     *
     * @param string $route_string The route from the URL.
     */
    private function parse_route( string $route_string ): void {
        $route_string = trim( $route_string, '/' );

        if ( empty( $route_string ) ) {
            $this->current_route = 'dashboard';
            return;
        }

        $parts = explode( '/', $route_string );

        // First part is always the module
        $this->current_route = sanitize_key( $parts[0] );

        // Additional parts are parameters
        if ( count( $parts ) > 1 ) {
            // Second part could be an ID or action like "new"
            if ( $parts[1] === 'new' ) {
                $this->route_params['action'] = 'new';
            } elseif ( is_numeric( $parts[1] ) ) {
                $this->route_params['id'] = absint( $parts[1] );

                // Third part would be an action on that ID
                if ( isset( $parts[2] ) ) {
                    $this->route_params['action'] = sanitize_key( $parts[2] );
                }
            } else {
                // Could be a sub-action like "import"
                $this->route_params['action'] = sanitize_key( $parts[1] );
            }
        }
    }

    /**
     * Get the current module from the route.
     *
     * @return string Module name.
     */
    public function get_current_module(): string {
        // Map route to module (some routes might be aliases)
        $route = $this->current_route;

        if ( isset( $this->modules[ $route ] ) ) {
            return $route;
        }

        // Default to dashboard for unknown routes
        return 'dashboard';
    }

    /**
     * Check if the current user can access a module.
     *
     * WHY: Per-module permissions let different volunteers manage different areas.
     * The treasurer can manage transactions, the membership chair can manage members,
     * etc. Administrators always have full access.
     *
     * @param string $module Module name.
     * @return bool True if user can access.
     */
    public function user_can_access_module( string $module ): bool {
        // Administrators can access everything
        if ( current_user_can( 'administrator' ) ) {
            return true;
        }

        // Some modules are always accessible
        if ( isset( $this->modules[ $module ] ) && ! $this->modules[ $module ]['requires_permission'] ) {
            return true;
        }

        // Check the permissions table
        global $wpdb;
        $user_id = get_current_user_id();
        $table   = $wpdb->prefix . 'sp_permissions';

        // Check if table exists first (might not be created yet)
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) !== $table ) {
            // Table doesn't exist yet, deny by default (except for admins, handled above)
            return false;
        }

        $has_permission = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE user_id = %d AND module = %s",
            $user_id,
            $module
        ) );

        return (bool) $has_permission;
    }

    /**
     * Get navigation items for the current user.
     *
     * @return array Array of modules the user can access.
     */
    public function get_user_navigation(): array {
        $nav = [];

        foreach ( $this->modules as $slug => $module ) {
            if ( $this->user_can_access_module( $slug ) ) {
                $nav[ $slug ] = $module;
            }
        }

        return $nav;
    }

    /**
     * Block wp-admin access for volunteer users.
     *
     * WHY: Volunteers should never see wp-admin. If they try to access it,
     * redirect them to /sp-admin/. We allow AJAX requests through because
     * some WordPress features (like media uploads) use admin-ajax.php.
     */
    public function block_wp_admin_for_volunteers(): void {
        // Don't block AJAX requests
        if ( wp_doing_ajax() ) {
            return;
        }

        // Only block users with sp_volunteer role who are NOT administrators
        if ( current_user_can( 'sp_volunteer' ) && ! current_user_can( 'administrator' ) ) {
            wp_redirect( home_url( '/sp-admin/' ) );
            exit;
        }
    }

    /**
     * Get the current URL path.
     *
     * @return string URL path.
     */
    private function get_current_url_path(): string {
        return trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
    }

    /**
     * Render the admin interface.
     *
     * WHY: This is where we output the HTML. We load a wrapper template that
     * includes the sidebar navigation and content area, then include the
     * appropriate view file based on the current route.
     */
    public function render(): void {
        // Set up template variables
        $router     = $this;
        $module     = $this->get_current_module();
        $route      = $this->current_route;
        $params     = $this->route_params;
        $navigation = $this->get_user_navigation();
        $user       = wp_get_current_user();

        // Determine the view file to load
        $view_file = $this->get_view_file( $module, $params );

        // Load the wrapper template (which will include the view)
        include SOCIETYPRESS_PATH . 'sp-admin/views/wrapper.php';
    }

    /**
     * Get the view file path for the current route.
     *
     * @param string $module Current module.
     * @param array  $params Route parameters.
     * @return string Path to view file.
     */
    private function get_view_file( string $module, array $params ): string {
        $base_path = SOCIETYPRESS_PATH . 'sp-admin/views/';

        // Check for action-specific view
        if ( isset( $params['action'] ) ) {
            $action_file = $base_path . $module . '/' . $params['action'] . '.php';
            if ( file_exists( $action_file ) ) {
                return $action_file;
            }
        }

        // Check for ID-specific view (detail/edit)
        if ( isset( $params['id'] ) ) {
            $detail_file = $base_path . $module . '/detail.php';
            if ( file_exists( $detail_file ) ) {
                return $detail_file;
            }
        }

        // Module list view
        $list_file = $base_path . $module . '/list.php';
        if ( file_exists( $list_file ) ) {
            return $list_file;
        }

        // Single file for simple modules (dashboard, help)
        $single_file = $base_path . $module . '.php';
        if ( file_exists( $single_file ) ) {
            return $single_file;
        }

        // Fallback to dashboard
        return $base_path . 'dashboard.php';
    }

    /**
     * Get route parameters.
     *
     * @return array Route parameters.
     */
    public function get_params(): array {
        return $this->route_params;
    }

    /**
     * Get a specific route parameter.
     *
     * @param string $key Parameter key.
     * @param mixed  $default Default value if not set.
     * @return mixed Parameter value or default.
     */
    public function get_param( string $key, $default = null ) {
        return $this->route_params[ $key ] ?? $default;
    }

    /**
     * Generate a URL for the sp-admin interface.
     *
     * @param string $module Module name.
     * @param array  $params Optional parameters (id, action).
     * @return string Full URL.
     */
    public function url( string $module = 'dashboard', array $params = [] ): string {
        $path = '/sp-admin/';

        if ( $module !== 'dashboard' ) {
            $path .= $module . '/';
        }

        if ( isset( $params['id'] ) ) {
            $path .= $params['id'] . '/';
        }

        if ( isset( $params['action'] ) ) {
            $path .= $params['action'] . '/';
        }

        return home_url( $path );
    }

    /**
     * Get success message for display.
     *
     * @param string $code Success code.
     * @return string Human-readable message.
     */
    public function get_success_message( string $code ): string {
        $messages = [
            'saved'   => 'Changes saved successfully.',
            'created' => 'Created successfully.',
            'deleted' => 'Deleted successfully.',
            'updated' => 'Updated successfully.',
        ];

        return $messages[ $code ] ?? 'Operation completed successfully.';
    }
}

/**
 * Get the SP Admin Router instance.
 *
 * @return SP_Admin_Router
 */
function sp_admin_router(): SP_Admin_Router {
    return SP_Admin_Router::instance();
}
