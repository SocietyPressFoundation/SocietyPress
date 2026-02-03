<?php
/**
 * SocietyPress Installer
 *
 * A simple, wizard-style installer for WordPress and SocietyPress.
 * Designed for non-technical users at genealogical and historical societies.
 *
 * USAGE:
 * 1. Upload this single file to your web hosting
 * 2. Visit the file in your browser (e.g., https://yoursite.com/sp-installer.php)
 * 3. Follow the on-screen steps
 * 4. DELETE THIS FILE when finished (very important for security!)
 *
 * @package    SocietyPress
 * @subpackage Installer
 * @version    1.0.0
 * @author     Stricklin Development
 * @link       https://getsocietypress.org
 */

// ============================================================================
// CONFIGURATION - Update these URLs if your download locations change
// ============================================================================

define('SP_INSTALLER_VERSION', '1.0.0');
define('SP_WORDPRESS_DOWNLOAD_URL', 'https://wordpress.org/latest.zip');
define('SP_WORDPRESS_SALTS_URL', 'https://api.wordpress.org/secret-key/1.1/salt/');
define('SP_PLUGIN_DOWNLOAD_URL', 'https://getsocietypress.org/api/v1/plugins/societypress/download.php');
define('SP_THEME_DOWNLOAD_URL', 'https://getsocietypress.org/api/v1/themes/societypress/download.php');
define('SP_DOCS_URL', 'https://getsocietypress.org/docs/');

// ============================================================================
// SECURITY CHECK - Prevent direct execution in WordPress
// ============================================================================

if (defined('ABSPATH')) {
    die('This installer cannot run inside WordPress. Please access it directly.');
}

// ============================================================================
// SESSION HANDLING
// ============================================================================

session_start();

// Reset session if:
// 1. User explicitly requests reset (?reset=1)
// 2. WordPress is not installed but session thinks we're past step 3
//    (This handles the case where user wiped the install and started over)
$wordpress_exists = file_exists(dirname(__FILE__) . '/wp-config.php');
$force_reset = isset($_GET['reset']) && $_GET['reset'] === '1';
$stale_session = isset($_SESSION['sp_installer']['step'])
    && $_SESSION['sp_installer']['step'] > 3
    && !$wordpress_exists;

if ($force_reset || $stale_session) {
    unset($_SESSION['sp_installer']);
}

// Initialize session data if not set
if (!isset($_SESSION['sp_installer'])) {
    $_SESSION['sp_installer'] = [
        'step' => 1,
        'db_host' => 'localhost',
        'db_name' => '',
        'db_user' => '',
        'db_pass' => '',
        'db_prefix' => 'wp_',
        'site_title' => '',
        'admin_user' => '',
        'admin_pass' => '',
        'admin_email' => '',
        'org_name' => '',
        'org_address' => '',
        'org_phone' => '',
        'org_email' => '',
        'cpanel_detected' => false,
        'cpanel_user' => '',
        'install_path' => dirname(__FILE__),
    ];
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Check if we're likely on cPanel hosting.
 * Looks for common cPanel indicators.
 *
 * WHY: cPanel hosting allows automated database creation via UAPI,
 *      which makes installation much easier for non-technical users.
 *
 * @return bool True if cPanel is likely available.
 */
function sp_detect_cpanel(): bool {
    // Check for cPanel-specific paths
    $cpanel_indicators = [
        '/usr/local/cpanel',
        '/home/' . get_current_user() . '/public_html',
        '/home/' . get_current_user() . '/.cpanel',
    ];

    foreach ($cpanel_indicators as $path) {
        if (file_exists($path) || is_dir($path)) {
            return true;
        }
    }

    // Check for cPanel environment variables
    if (isset($_SERVER['CPANEL']) || isset($_SERVER['cPanel'])) {
        return true;
    }

    // Check if we're in a typical cPanel directory structure
    $current_path = dirname(__FILE__);
    if (preg_match('#/home/[^/]+/(public_html|domains)#', $current_path)) {
        return true;
    }

    return false;
}

/**
 * Generate a cryptographically secure random password.
 *
 * WHY: Users often choose weak passwords. Auto-generating a strong one
 *      and displaying it prominently helps maintain security.
 *
 * @param int $length Password length (default 16).
 * @return string Random password.
 */
function sp_generate_password(int $length = 16): string {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    $max = strlen($chars) - 1;

    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, $max)];
    }

    return $password;
}

/**
 * Generate a safe database/username (alphanumeric only, limited length).
 *
 * WHY: cPanel has restrictions on database and username formats.
 *      This ensures compatibility across different hosting providers.
 *
 * @param string $prefix Prefix for the generated name.
 * @param int $random_length Length of random suffix.
 * @return string Safe identifier.
 */
function sp_generate_db_name(string $prefix = 'sp', int $random_length = 6): string {
    $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $random = '';

    for ($i = 0; $i < $random_length; $i++) {
        $random .= $chars[random_int(0, strlen($chars) - 1)];
    }

    return substr($prefix . '_' . $random, 0, 16); // cPanel often limits to 16 chars
}

/**
 * Test database connection with provided credentials.
 *
 * WHY: We need to verify the database exists and is accessible
 *      before attempting WordPress installation.
 *
 * @param string $host Database host.
 * @param string $user Database username.
 * @param string $pass Database password.
 * @param string $name Database name.
 * @return array ['success' => bool, 'message' => string]
 */
function sp_test_db_connection(string $host, string $user, string $pass, string $name): array {
    // Suppress errors, we'll handle them ourselves
    $conn = @new mysqli($host, $user, $pass, $name);

    if ($conn->connect_error) {
        return [
            'success' => false,
            'message' => 'Could not connect: ' . $conn->connect_error
        ];
    }

    $conn->close();
    return [
        'success' => true,
        'message' => 'Connection successful!'
    ];
}

/**
 * Try to connect to cPanel API at a specific host/port.
 *
 * @param string $host Hostname.
 * @param int    $port Port number.
 * @param string $auth Base64-encoded credentials.
 * @return bool True if connection works.
 */
function sp_test_cpanel_connection(string $host, int $port, string $auth): bool {
    $context = stream_context_create([
        'http' => [
            'header' => "Authorization: Basic {$auth}\r\n",
            'ignore_errors' => true,
            'timeout' => 5,
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);

    $url = "https://{$host}:{$port}/execute/Mysql/list_databases";
    $result = @file_get_contents($url, false, $context);

    if ($result === false) {
        return false;
    }

    $response = json_decode($result, true);
    return isset($response['status']);
}

/**
 * Auto-detect cPanel host and port by trying common configurations.
 *
 * WHY: Different hosts configure cPanel differently. We try common
 *      configurations so users don't need to know technical details.
 *
 * @param string $cpanel_user cPanel username.
 * @param string $cpanel_pass cPanel password.
 * @return array|null ['host' => string, 'port' => int] or null if not found.
 */
function sp_detect_cpanel_endpoint(string $cpanel_user, string $cpanel_pass): ?array {
    $auth = base64_encode("{$cpanel_user}:{$cpanel_pass}");

    // Common cPanel configurations to try
    $endpoints = [
        ['host' => 'localhost', 'port' => 2083],
        ['host' => 'localhost', 'port' => 2082],
        ['host' => '127.0.0.1', 'port' => 2083],
        ['host' => '127.0.0.1', 'port' => 2082],
    ];

    // Also try the server's hostname if available
    $server_host = $_SERVER['HTTP_HOST'] ?? '';
    if ($server_host && strpos($server_host, 'localhost') === false) {
        // Try common cPanel subdomains
        $base_domain = preg_replace('/^www\./', '', $server_host);
        $endpoints[] = ['host' => 'cpanel.' . $base_domain, 'port' => 2083];
        $endpoints[] = ['host' => 'cp.' . $base_domain, 'port' => 2083];
        $endpoints[] = ['host' => $server_host, 'port' => 2083];
        $endpoints[] = ['host' => $server_host, 'port' => 2082];
        // Some hosts use non-standard ports
        $endpoints[] = ['host' => $server_host, 'port' => 2222];
        $endpoints[] = ['host' => 'localhost', 'port' => 2222];
    }

    // Try to find cPanel hostname from common environment hints
    if (isset($_SERVER['SERVER_NAME'])) {
        $endpoints[] = ['host' => $_SERVER['SERVER_NAME'], 'port' => 2083];
        $endpoints[] = ['host' => $_SERVER['SERVER_NAME'], 'port' => 2222];
    }

    foreach ($endpoints as $endpoint) {
        if (sp_test_cpanel_connection($endpoint['host'], $endpoint['port'], $auth)) {
            return $endpoint;
        }
    }

    return null;
}

/**
 * Create database via cPanel UAPI.
 *
 * WHY: cPanel's UAPI allows us to create databases programmatically,
 *      eliminating the need for users to navigate cPanel manually.
 *
 * @param string $cpanel_user cPanel username.
 * @param string $cpanel_pass cPanel password.
 * @param string $db_name Database name to create.
 * @param string $db_user Database user to create.
 * @param string $db_pass Password for database user.
 * @param string|null $cpanel_host cPanel hostname (auto-detect if null).
 * @param int|null    $cpanel_port cPanel port (auto-detect if null).
 * @return array ['success' => bool, 'message' => string]
 */
function sp_cpanel_create_database(string $cpanel_user, string $cpanel_pass, string $db_name, string $db_user, string $db_pass, ?string $cpanel_host = null, ?int $cpanel_port = null): array {
    // Auto-detect cPanel endpoint if not provided
    if ($cpanel_host === null || $cpanel_port === null) {
        $detected = sp_detect_cpanel_endpoint($cpanel_user, $cpanel_pass);
        if ($detected === null) {
            return [
                'success' => false,
                'message' => 'Could not auto-detect cPanel. Please use Manual Entry to create the database in cPanel yourself, or click "Show Advanced" and enter your cPanel hostname and port.'
            ];
        }
        $cpanel_host = $detected['host'];
        $cpanel_port = $detected['port'];
    }

    // Store detected endpoint for debugging
    $endpoint_info = "Using cPanel at {$cpanel_host}:{$cpanel_port}";

    // cPanel prefixes database and user names with the account username
    $full_db_name = $cpanel_user . '_' . $db_name;
    $full_db_user = $cpanel_user . '_' . $db_user;

    $base_url = "https://{$cpanel_host}:{$cpanel_port}";
    $auth = base64_encode("{$cpanel_user}:{$cpanel_pass}");

    $context = stream_context_create([
        'http' => [
            'header' => "Authorization: Basic {$auth}\r\n",
            'ignore_errors' => true,
            'timeout' => 30,
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);

    // Step 1: Create database
    $create_db_url = "{$base_url}/execute/Mysql/create_database?name=" . urlencode($full_db_name);
    $result = @file_get_contents($create_db_url, false, $context);

    if ($result === false) {
        return [
            'success' => false,
            'message' => "Could not connect to cPanel at {$cpanel_host}:{$cpanel_port}. Please check your credentials or create the database manually in cPanel."
        ];
    }

    // Debug: if response looks like HTML, show a helpful message
    if (strpos($result, '<html') !== false || strpos($result, '<!DOCTYPE') !== false) {
        return [
            'success' => false,
            'message' => "cPanel returned an HTML page instead of JSON. This usually means the API endpoint is blocked or requires a different authentication method. Please create the database manually in cPanel."
        ];
    }

    $response = json_decode($result, true);

    // If JSON decode failed, show raw response
    if ($response === null && !empty($result)) {
        return [
            'success' => false,
            'message' => "cPanel returned unexpected response: " . substr($result, 0, 300)
        ];
    }
    if (!isset($response['status']) || $response['status'] !== 1) {
        // Try to extract error from various cPanel response formats
        $error = 'Unknown error creating database';
        if (isset($response['errors']) && is_array($response['errors']) && !empty($response['errors'])) {
            $error = is_string($response['errors'][0]) ? $response['errors'][0] : json_encode($response['errors'][0]);
        } elseif (isset($response['error'])) {
            $error = $response['error'];
        } elseif (isset($response['message'])) {
            $error = $response['message'];
        } elseif (isset($response['cpanelresult']['error'])) {
            $error = $response['cpanelresult']['error'];
        } else {
            // Show raw response for debugging
            $error = 'cPanel error (raw): ' . substr($result, 0, 500);
        }
        return ['success' => false, 'message' => "{$endpoint_info}. Error: {$error}"];
    }

    // Step 2: Create database user
    $create_user_url = "{$base_url}/execute/Mysql/create_user?name=" . urlencode($full_db_user) . "&password=" . urlencode($db_pass);
    $result = @file_get_contents($create_user_url, false, $context);
    $response = json_decode($result, true);

    if (!isset($response['status']) || $response['status'] !== 1) {
        $error = $response['errors'][0] ?? 'Unknown error creating database user';
        return ['success' => false, 'message' => $error];
    }

    // Step 3: Grant all privileges
    $grant_url = "{$base_url}/execute/Mysql/set_privileges_on_database?user=" . urlencode($full_db_user) . "&database=" . urlencode($full_db_name) . "&privileges=ALL%20PRIVILEGES";
    $result = @file_get_contents($grant_url, false, $context);
    $response = json_decode($result, true);

    if (!isset($response['status']) || $response['status'] !== 1) {
        $error = $response['errors'][0] ?? 'Unknown error granting privileges';
        return ['success' => false, 'message' => $error];
    }

    return [
        'success' => true,
        'message' => 'Database created successfully!',
        'db_name' => $full_db_name,
        'db_user' => $full_db_user,
        'db_pass' => $db_pass,
    ];
}

/**
 * Download a file from URL to local path.
 *
 * WHY: We need to download WordPress core and SocietyPress files
 *      from their respective servers during installation.
 *
 * @param string $url URL to download from.
 * @param string $destination Local file path.
 * @return array ['success' => bool, 'message' => string]
 */
function sp_download_file(string $url, string $destination): array {
    $context = stream_context_create([
        'http' => [
            'timeout' => 120, // 2 minutes for large files
            'user_agent' => 'SocietyPress-Installer/' . SP_INSTALLER_VERSION,
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);

    $content = @file_get_contents($url, false, $context);

    if ($content === false) {
        return [
            'success' => false,
            'message' => "Could not download from {$url}. Please check your internet connection."
        ];
    }

    $written = @file_put_contents($destination, $content);

    if ($written === false) {
        return [
            'success' => false,
            'message' => "Could not save file to {$destination}. Please check folder permissions."
        ];
    }

    return [
        'success' => true,
        'message' => 'Download complete!',
        'size' => $written,
    ];
}

/**
 * Extract a ZIP file to a directory.
 *
 * WHY: WordPress and SocietyPress are distributed as ZIP files.
 *      We need to extract them to the correct locations.
 *
 * @param string $zip_file Path to ZIP file.
 * @param string $destination Directory to extract to.
 * @param bool $strip_root If true, strips the root folder from the archive.
 * @return array ['success' => bool, 'message' => string]
 */
function sp_extract_zip(string $zip_file, string $destination, bool $strip_root = false): array {
    if (!class_exists('ZipArchive')) {
        return [
            'success' => false,
            'message' => 'PHP ZipArchive extension is not available. Please contact your hosting provider.'
        ];
    }

    $zip = new ZipArchive();
    $result = $zip->open($zip_file);

    if ($result !== true) {
        return [
            'success' => false,
            'message' => 'Could not open ZIP file. It may be corrupted.'
        ];
    }

    if ($strip_root) {
        // Find the root folder name in the archive
        $root_folder = '';
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (strpos($name, '/') !== false) {
                $root_folder = explode('/', $name)[0];
                break;
            }
        }

        // Extract each file, stripping the root folder
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);

            if (strpos($name, $root_folder . '/') === 0) {
                $new_name = substr($name, strlen($root_folder) + 1);

                if (empty($new_name)) {
                    continue; // Skip the root folder itself
                }

                $target_path = $destination . '/' . $new_name;

                // Create directory if needed
                if (substr($name, -1) === '/') {
                    if (!is_dir($target_path)) {
                        mkdir($target_path, 0755, true);
                    }
                } else {
                    // Ensure parent directory exists
                    $parent = dirname($target_path);
                    if (!is_dir($parent)) {
                        mkdir($parent, 0755, true);
                    }

                    // Extract file
                    $content = $zip->getFromIndex($i);
                    file_put_contents($target_path, $content);
                }
            }
        }
    } else {
        $zip->extractTo($destination);
    }

    $zip->close();

    return [
        'success' => true,
        'message' => 'Extraction complete!'
    ];
}

/**
 * Generate wp-config.php content.
 *
 * WHY: WordPress requires a wp-config.php file with database credentials
 *      and security keys. We generate this automatically.
 *
 * @param array $config Configuration values.
 * @return string wp-config.php content.
 */
function sp_generate_wp_config(array $config): string {
    // Fetch fresh salts from WordPress
    $salts = @file_get_contents(SP_WORDPRESS_SALTS_URL);
    if ($salts === false) {
        // Fallback: generate our own salts
        $salt_keys = ['AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY',
                      'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT'];
        $salts = '';
        foreach ($salt_keys as $key) {
            $value = base64_encode(random_bytes(48));
            $salts .= "define('{$key}', '{$value}');\n";
        }
    }

    $db_host = addslashes($config['db_host']);
    $db_name = addslashes($config['db_name']);
    $db_user = addslashes($config['db_user']);
    $db_pass = addslashes($config['db_pass']);
    $db_prefix = preg_replace('/[^a-z0-9_]/i', '', $config['db_prefix']);

    return "<?php
/**
 * WordPress Configuration File
 *
 * Generated by SocietyPress Installer on " . date('Y-m-d H:i:s') . "
 *
 * @package WordPress
 */

// ** Database settings ** //
define('DB_NAME', '{$db_name}');
define('DB_USER', '{$db_user}');
define('DB_PASSWORD', '{$db_pass}');
define('DB_HOST', '{$db_host}');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

// ** Authentication Unique Keys and Salts ** //
{$salts}

// ** Database Table Prefix ** //
\$table_prefix = '{$db_prefix}';

// ** Debugging ** //
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);

// ** Memory Limit ** //
define('WP_MEMORY_LIMIT', '256M');

// ** Automatic Updates ** //
define('WP_AUTO_UPDATE_CORE', 'minor');

// ** File Editing ** //
define('DISALLOW_FILE_EDIT', true);

// ** Absolute Path ** //
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

// ** Sets up WordPress vars and included files ** //
require_once ABSPATH . 'wp-settings.php';
";
}

/**
 * Run WordPress installation programmatically.
 *
 * WHY: Instead of redirecting users to wp-admin/install.php,
 *      we run the installation silently to maintain the wizard flow.
 *
 * @param string $wp_path Path to WordPress installation.
 * @param array $config Installation configuration.
 * @return array ['success' => bool, 'message' => string]
 */
function sp_install_wordpress(string $wp_path, array $config): array {
    // Load WordPress core
    define('WP_INSTALLING', true);

    // Prevent WordPress from outputting anything
    ob_start();

    require_once $wp_path . '/wp-load.php';
    require_once $wp_path . '/wp-admin/includes/upgrade.php';
    require_once $wp_path . '/wp-includes/class-wpdb.php';

    // Check if already installed
    if (is_blog_installed()) {
        ob_end_clean();
        return [
            'success' => false,
            'message' => 'WordPress is already installed in this location.'
        ];
    }

    // Install WordPress
    $result = wp_install(
        $config['site_title'],     // Blog title
        $config['admin_user'],     // Username
        $config['admin_email'],    // Email
        true,                      // Public (allow search engines)
        '',                        // Deprecated
        $config['admin_pass'],     // Password
        false                      // Language (default)
    );

    ob_end_clean();

    if (is_wp_error($result)) {
        return [
            'success' => false,
            'message' => $result->get_error_message()
        ];
    }

    return [
        'success' => true,
        'message' => 'WordPress installed successfully!',
        'admin_user' => $config['admin_user'],
        'admin_pass' => $config['admin_pass'],
    ];
}

/**
 * Activate a WordPress plugin.
 *
 * WHY: After installing the SocietyPress plugin files, we need to
 *      activate it so WordPress recognizes it and runs its setup.
 *
 * @param string $wp_path Path to WordPress installation.
 * @param string $plugin Plugin file path relative to plugins directory.
 * @return array ['success' => bool, 'message' => string]
 */
function sp_activate_plugin(string $wp_path, string $plugin): array {
    if (!defined('ABSPATH')) {
        define('ABSPATH', $wp_path . '/');
    }

    // Load WordPress if not already loaded
    if (!function_exists('activate_plugin')) {
        require_once $wp_path . '/wp-load.php';
        require_once $wp_path . '/wp-admin/includes/plugin.php';
    }

    $result = activate_plugin($plugin);

    if (is_wp_error($result)) {
        return [
            'success' => false,
            'message' => $result->get_error_message()
        ];
    }

    return [
        'success' => true,
        'message' => 'Plugin activated successfully!'
    ];
}

/**
 * Activate a WordPress theme.
 *
 * WHY: After installing the SocietyPress theme files, we need to
 *      activate it so the site uses our theme instead of a default.
 *
 * @param string $wp_path Path to WordPress installation.
 * @param string $theme Theme directory name.
 * @return array ['success' => bool, 'message' => string]
 */
function sp_activate_theme(string $wp_path, string $theme): array {
    if (!defined('ABSPATH')) {
        define('ABSPATH', $wp_path . '/');
    }

    // Load WordPress if not already loaded
    if (!function_exists('switch_theme')) {
        require_once $wp_path . '/wp-load.php';
    }

    switch_theme($theme);

    // Verify the theme was activated
    $current = get_option('stylesheet');

    if ($current !== $theme) {
        return [
            'success' => false,
            'message' => 'Theme could not be activated. Please activate it manually in WordPress admin.'
        ];
    }

    return [
        'success' => true,
        'message' => 'Theme activated successfully!'
    ];
}

/**
 * Save SocietyPress initial settings.
 *
 * WHY: Pre-configuring organization info saves users a step
 *      and ensures the plugin is ready to use immediately.
 *
 * @param string $wp_path Path to WordPress installation.
 * @param array $settings Organization settings.
 * @return array ['success' => bool, 'message' => string]
 */
function sp_save_settings(string $wp_path, array $settings): array {
    if (!defined('ABSPATH')) {
        define('ABSPATH', $wp_path . '/');
    }

    if (!function_exists('update_option')) {
        require_once $wp_path . '/wp-load.php';
    }

    // Get existing settings or empty array
    $existing = get_option('societypress_settings', []);

    // Merge new settings
    $new_settings = array_merge($existing, [
        'organization_name' => $settings['org_name'] ?? '',
        'organization_address' => $settings['org_address'] ?? '',
        'organization_phone' => $settings['org_phone'] ?? '',
        'organization_email' => $settings['org_email'] ?? '',
    ]);

    update_option('societypress_settings', $new_settings);

    return [
        'success' => true,
        'message' => 'Settings saved successfully!'
    ];
}

/**
 * Check system requirements.
 *
 * WHY: We need to verify the hosting environment can run WordPress
 *      and SocietyPress before attempting installation.
 *
 * @return array Array of requirement checks with pass/fail status.
 */
function sp_check_requirements(): array {
    $requirements = [];

    // PHP Version
    $php_version = phpversion();
    $php_required = '8.0.0';
    $requirements['php'] = [
        'name' => 'PHP Version',
        'required' => $php_required . '+',
        'current' => $php_version,
        'pass' => version_compare($php_version, $php_required, '>='),
        'message' => version_compare($php_version, $php_required, '>=')
            ? "PHP {$php_version} is installed."
            : "PHP {$php_required} or higher is required. You have {$php_version}.",
    ];

    // Required Extensions
    $extensions = ['mysqli', 'curl', 'json', 'zip', 'openssl', 'mbstring'];
    foreach ($extensions as $ext) {
        $loaded = extension_loaded($ext);
        $requirements[$ext] = [
            'name' => strtoupper($ext) . ' Extension',
            'required' => 'Enabled',
            'current' => $loaded ? 'Enabled' : 'Not Found',
            'pass' => $loaded,
            'message' => $loaded
                ? "{$ext} extension is available."
                : "{$ext} extension is required but not installed.",
        ];
    }

    // Write Permissions
    $writable = is_writable(dirname(__FILE__));
    $requirements['writable'] = [
        'name' => 'Write Permissions',
        'required' => 'Writable',
        'current' => $writable ? 'Writable' : 'Not Writable',
        'pass' => $writable,
        'message' => $writable
            ? 'The current directory is writable.'
            : 'Cannot write to the current directory. Please check folder permissions.',
    ];

    // Disk Space
    $free_space = @disk_free_space(dirname(__FILE__));
    $required_space = 50 * 1024 * 1024; // 50MB
    $space_pass = $free_space === false || $free_space >= $required_space;
    $requirements['disk'] = [
        'name' => 'Disk Space',
        'required' => '50 MB+',
        'current' => $free_space === false ? 'Unknown' : round($free_space / 1024 / 1024) . ' MB',
        'pass' => $space_pass,
        'message' => $space_pass
            ? 'Sufficient disk space available.'
            : 'At least 50 MB of free disk space is required.',
    ];

    // Check if WordPress already exists
    $wp_exists = file_exists(dirname(__FILE__) . '/wp-config.php') || file_exists(dirname(__FILE__) . '/wp-includes');
    $requirements['existing'] = [
        'name' => 'Existing Installation',
        'required' => 'None',
        'current' => $wp_exists ? 'WordPress Found' : 'None',
        'pass' => !$wp_exists,
        'message' => $wp_exists
            ? 'WordPress already exists in this directory. Delete existing files or install elsewhere.'
            : 'Directory is ready for fresh installation.',
    ];

    return $requirements;
}

/**
 * Sanitize user input.
 *
 * WHY: Security best practice - always sanitize user input
 *      to prevent XSS and injection attacks.
 *
 * @param string $input Raw input.
 * @return string Sanitized input.
 */
function sp_sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// ============================================================================
// PROCESS FORM SUBMISSIONS
// ============================================================================

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'check_requirements':
            // Just move to step 2 if all requirements pass
            $requirements = sp_check_requirements();
            $all_pass = true;
            foreach ($requirements as $req) {
                if (!$req['pass']) {
                    $all_pass = false;
                    break;
                }
            }
            if ($all_pass) {
                $_SESSION['sp_installer']['step'] = 2;
                $_SESSION['sp_installer']['cpanel_detected'] = sp_detect_cpanel();
            } else {
                $error_message = 'Please fix the requirements above before continuing.';
            }
            break;

        case 'setup_database':
            $db_host = sp_sanitize($_POST['db_host'] ?? 'localhost');
            $db_name = sp_sanitize($_POST['db_name'] ?? '');
            $db_user = sp_sanitize($_POST['db_user'] ?? '');
            $db_pass = $_POST['db_pass'] ?? ''; // Don't sanitize password
            $db_prefix = sp_sanitize($_POST['db_prefix'] ?? 'wp_');

            // Test connection
            $test = sp_test_db_connection($db_host, $db_user, $db_pass, $db_name);

            if ($test['success']) {
                $_SESSION['sp_installer']['db_host'] = $db_host;
                $_SESSION['sp_installer']['db_name'] = $db_name;
                $_SESSION['sp_installer']['db_user'] = $db_user;
                $_SESSION['sp_installer']['db_pass'] = $db_pass;
                $_SESSION['sp_installer']['db_prefix'] = $db_prefix;
                $_SESSION['sp_installer']['step'] = 3;
            } else {
                $error_message = $test['message'];
            }
            break;

        case 'setup_database_cpanel':
            $cpanel_user = sp_sanitize($_POST['cpanel_user'] ?? '');
            $cpanel_pass = $_POST['cpanel_pass'] ?? '';

            // Allow auto-detection if host/port are empty
            $cpanel_host = !empty($_POST['cpanel_host']) ? sp_sanitize($_POST['cpanel_host']) : null;
            $cpanel_port = !empty($_POST['cpanel_port']) ? (int) $_POST['cpanel_port'] : null;

            // Generate names
            $db_name = sp_generate_db_name('sp', 4);
            $db_user = sp_generate_db_name('sp', 4);
            $db_pass = sp_generate_password(20);

            $result = sp_cpanel_create_database($cpanel_user, $cpanel_pass, $db_name, $db_user, $db_pass, $cpanel_host, $cpanel_port);

            if ($result['success']) {
                $_SESSION['sp_installer']['db_host'] = 'localhost';
                $_SESSION['sp_installer']['db_name'] = $result['db_name'];
                $_SESSION['sp_installer']['db_user'] = $result['db_user'];
                $_SESSION['sp_installer']['db_pass'] = $result['db_pass'];
                $_SESSION['sp_installer']['db_prefix'] = 'wp_';
                $_SESSION['sp_installer']['cpanel_user'] = $cpanel_user;
                $_SESSION['sp_installer']['step'] = 3;
            } else {
                $error_message = $result['message'];
            }
            break;

        case 'download_wordpress':
            $install_path = $_SESSION['sp_installer']['install_path'];
            $zip_path = $install_path . '/wordpress.zip';

            // Download WordPress
            $download = sp_download_file(SP_WORDPRESS_DOWNLOAD_URL, $zip_path);

            if (!$download['success']) {
                $error_message = $download['message'];
                break;
            }

            // Extract WordPress (strip root folder)
            $extract = sp_extract_zip($zip_path, $install_path, true);

            if (!$extract['success']) {
                $error_message = $extract['message'];
                break;
            }

            // Clean up zip
            @unlink($zip_path);

            $_SESSION['sp_installer']['step'] = 4;
            break;

        case 'configure_wordpress':
            $config = [
                'db_host' => $_SESSION['sp_installer']['db_host'],
                'db_name' => $_SESSION['sp_installer']['db_name'],
                'db_user' => $_SESSION['sp_installer']['db_user'],
                'db_pass' => $_SESSION['sp_installer']['db_pass'],
                'db_prefix' => $_SESSION['sp_installer']['db_prefix'],
            ];

            $wp_config_content = sp_generate_wp_config($config);
            $install_path = $_SESSION['sp_installer']['install_path'];

            $written = @file_put_contents($install_path . '/wp-config.php', $wp_config_content);

            if ($written === false) {
                $error_message = 'Could not write wp-config.php. Please check file permissions.';
                break;
            }

            $_SESSION['sp_installer']['step'] = 5;
            break;

        case 'install_wordpress':
            $site_title = sp_sanitize($_POST['site_title'] ?? 'My Society');
            $admin_user = sp_sanitize($_POST['admin_user'] ?? 'admin');
            $admin_pass = $_POST['admin_pass'] ?? sp_generate_password();
            $admin_email = sp_sanitize($_POST['admin_email'] ?? '');

            // Validate
            if (empty($admin_email) || !filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
                $error_message = 'Please enter a valid email address.';
                break;
            }

            if (strlen($admin_user) < 3) {
                $error_message = 'Username must be at least 3 characters.';
                break;
            }

            if (strlen($admin_pass) < 8) {
                $error_message = 'Password must be at least 8 characters.';
                break;
            }

            $_SESSION['sp_installer']['site_title'] = $site_title;
            $_SESSION['sp_installer']['admin_user'] = $admin_user;
            $_SESSION['sp_installer']['admin_pass'] = $admin_pass;
            $_SESSION['sp_installer']['admin_email'] = $admin_email;

            $install_path = $_SESSION['sp_installer']['install_path'];

            $result = sp_install_wordpress($install_path, [
                'site_title' => $site_title,
                'admin_user' => $admin_user,
                'admin_pass' => $admin_pass,
                'admin_email' => $admin_email,
            ]);

            if ($result['success']) {
                $_SESSION['sp_installer']['step'] = 6;
            } else {
                $error_message = $result['message'];
            }
            break;

        case 'install_societypress':
            $install_path = $_SESSION['sp_installer']['install_path'];
            $plugins_path = $install_path . '/wp-content/plugins';
            $themes_path = $install_path . '/wp-content/themes';

            // Download and install plugin
            $plugin_zip = $install_path . '/societypress-plugin.zip';
            $download = sp_download_file(SP_PLUGIN_DOWNLOAD_URL, $plugin_zip);

            if (!$download['success']) {
                $error_message = 'Could not download plugin: ' . $download['message'];
                break;
            }

            // Extract plugin
            $extract = sp_extract_zip($plugin_zip, $plugins_path, false);
            @unlink($plugin_zip);

            if (!$extract['success']) {
                $error_message = 'Could not extract plugin: ' . $extract['message'];
                break;
            }

            // Download and install theme
            $theme_zip = $install_path . '/societypress-theme.zip';
            $download = sp_download_file(SP_THEME_DOWNLOAD_URL, $theme_zip);

            if (!$download['success']) {
                $error_message = 'Could not download theme: ' . $download['message'];
                break;
            }

            // Extract theme
            $extract = sp_extract_zip($theme_zip, $themes_path, false);
            @unlink($theme_zip);

            if (!$extract['success']) {
                $error_message = 'Could not extract theme: ' . $extract['message'];
                break;
            }

            // Activate plugin and theme via direct database update
            // This is more reliable than trying to load WordPress
            $db_host = $_SESSION['sp_installer']['db_host'];
            $db_name = $_SESSION['sp_installer']['db_name'];
            $db_user = $_SESSION['sp_installer']['db_user'];
            $db_pass = $_SESSION['sp_installer']['db_pass'];
            $db_prefix = $_SESSION['sp_installer']['db_prefix'];

            $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
            if ($conn->connect_error) {
                $error_message = 'Could not connect to database to activate plugin/theme. Please activate them manually in WordPress admin.';
            } else {
                // Activate plugin by updating active_plugins option
                $active_plugins = serialize(['societypress/societypress.php']);
                $stmt = $conn->prepare("UPDATE {$db_prefix}options SET option_value = ? WHERE option_name = 'active_plugins'");
                $stmt->bind_param('s', $active_plugins);
                $stmt->execute();
                $stmt->close();

                // Activate theme by updating template and stylesheet options
                $theme_name = 'societypress';
                $stmt = $conn->prepare("UPDATE {$db_prefix}options SET option_value = ? WHERE option_name = 'template'");
                $stmt->bind_param('s', $theme_name);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare("UPDATE {$db_prefix}options SET option_value = ? WHERE option_name = 'stylesheet'");
                $stmt->bind_param('s', $theme_name);
                $stmt->execute();
                $stmt->close();

                $conn->close();
            }

            $_SESSION['sp_installer']['step'] = 7;
            break;

        case 'configure_societypress':
            $org_name = sp_sanitize($_POST['org_name'] ?? '');
            $org_address = sp_sanitize($_POST['org_address'] ?? '');
            $org_phone = sp_sanitize($_POST['org_phone'] ?? '');
            $org_email = sp_sanitize($_POST['org_email'] ?? '');

            $_SESSION['sp_installer']['org_name'] = $org_name;
            $_SESSION['sp_installer']['org_address'] = $org_address;
            $_SESSION['sp_installer']['org_phone'] = $org_phone;
            $_SESSION['sp_installer']['org_email'] = $org_email;

            $install_path = $_SESSION['sp_installer']['install_path'];

            sp_save_settings($install_path, [
                'org_name' => $org_name,
                'org_address' => $org_address,
                'org_phone' => $org_phone,
                'org_email' => $org_email,
            ]);

            $_SESSION['sp_installer']['step'] = 8;
            break;

        case 'skip_configure':
            $_SESSION['sp_installer']['step'] = 8;
            break;

        case 'delete_installer':
            // Attempt to delete this file
            $deleted = @unlink(__FILE__);
            if ($deleted) {
                // Redirect to site
                $site_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
                header('Location: ' . $site_url);
                exit;
            } else {
                $error_message = 'Could not delete installer automatically. Please delete sp-installer.php manually via FTP or file manager.';
            }
            break;

        case 'restart':
            session_destroy();
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
            break;
    }
}

// Get current step
$current_step = $_SESSION['sp_installer']['step'];

// ============================================================================
// HTML OUTPUT
// ============================================================================

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocietyPress Installer</title>
    <style>
        /*
         * Installer Styles
         * Designed for maximum readability and ease of use by non-technical users.
         * Large fonts, high contrast, clear visual hierarchy.
         */

        * {
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            font-size: 18px;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .header {
            background: #1e3a5f;
            color: #fff;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0 0 10px 0;
            font-size: 32px;
            font-weight: 600;
        }

        .header p {
            margin: 0;
            opacity: 0.9;
            font-size: 16px;
        }

        .progress-bar {
            display: flex;
            justify-content: space-between;
            padding: 20px 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }

        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            position: relative;
        }

        .progress-step::after {
            content: '';
            position: absolute;
            top: 15px;
            left: 50%;
            width: 100%;
            height: 3px;
            background: #dee2e6;
            z-index: 0;
        }

        .progress-step:last-child::after {
            display: none;
        }

        .progress-step .number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #dee2e6;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .progress-step.active .number {
            background: #1e3a5f;
            color: #fff;
            transform: scale(1.1);
        }

        .progress-step.complete .number {
            background: #28a745;
            color: #fff;
        }

        .progress-step .label {
            font-size: 11px;
            color: #6c757d;
            margin-top: 5px;
            text-align: center;
        }

        .progress-step.active .label {
            color: #1e3a5f;
            font-weight: 600;
        }

        .content {
            padding: 40px;
        }

        .content h2 {
            margin: 0 0 10px 0;
            color: #1e3a5f;
            font-size: 28px;
        }

        .content .subtitle {
            color: #6c757d;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 16px;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .alert-info {
            background: #e7f3ff;
            color: #004085;
            border: 1px solid #b8daff;
        }

        .requirement-list {
            list-style: none;
            padding: 0;
            margin: 0 0 30px 0;
        }

        .requirement-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 8px;
        }

        .requirement-item .icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-weight: bold;
            font-size: 14px;
        }

        .requirement-item.pass .icon {
            background: #28a745;
            color: #fff;
        }

        .requirement-item.fail .icon {
            background: #dc3545;
            color: #fff;
        }

        .requirement-item .text {
            flex: 1;
        }

        .requirement-item .text .name {
            font-weight: 600;
            display: block;
        }

        .requirement-item .text .detail {
            font-size: 14px;
            color: #6c757d;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #1e3a5f;
        }

        .form-group .help-text {
            font-size: 14px;
            color: #6c757d;
            margin-top: 6px;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group textarea {
            width: 100%;
            padding: 14px 16px;
            font-size: 18px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            transition: border-color 0.2s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .btn {
            display: inline-block;
            padding: 16px 32px;
            font-size: 18px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: #1e3a5f;
            color: #fff;
        }

        .btn-primary:hover {
            background: #2c5282;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
            color: #fff;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-success {
            background: #28a745;
            color: #fff;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-danger {
            background: #dc3545;
            color: #fff;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-lg {
            padding: 20px 40px;
            font-size: 20px;
        }

        .btn-block {
            display: block;
            width: 100%;
            text-align: center;
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

        .button-group .btn {
            flex: 1;
        }

        .info-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .info-box h4 {
            margin: 0 0 10px 0;
            color: #1e3a5f;
        }

        .info-box code {
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 16px;
        }

        .credentials-box {
            background: #e7f3ff;
            border: 2px solid #b8daff;
            border-radius: 8px;
            padding: 24px;
            margin: 20px 0;
        }

        .credentials-box h4 {
            margin: 0 0 16px 0;
            color: #004085;
        }

        .credentials-box .credential {
            display: flex;
            margin-bottom: 12px;
            align-items: center;
        }

        .credentials-box .credential .label {
            width: 120px;
            font-weight: 600;
            color: #004085;
        }

        .credentials-box .credential .value {
            flex: 1;
            background: #fff;
            padding: 10px 14px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 16px;
            border: 1px solid #b8daff;
        }

        .tabs {
            display: flex;
            border-bottom: 2px solid #dee2e6;
            margin-bottom: 24px;
        }

        .tab {
            padding: 12px 24px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            font-weight: 600;
            color: #6c757d;
            transition: all 0.2s ease;
        }

        .tab:hover {
            color: #1e3a5f;
        }

        .tab.active {
            color: #1e3a5f;
            border-bottom-color: #1e3a5f;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .security-warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .security-warning h4 {
            margin: 0 0 10px 0;
            color: #856404;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .security-warning p {
            margin: 0;
            color: #856404;
        }

        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #1e3a5f;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
            vertical-align: middle;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .loading-overlay.active {
            display: flex;
        }

        .loading-box {
            background: #fff;
            padding: 40px 60px;
            border-radius: 12px;
            text-align: center;
        }

        .loading-box .spinner-lg {
            width: 50px;
            height: 50px;
            border-width: 5px;
            margin: 0 auto 20px;
        }

        .loading-box p {
            margin: 0;
            font-size: 18px;
            color: #333;
        }

        @media (max-width: 600px) {
            body {
                padding: 10px;
            }

            .content {
                padding: 24px;
            }

            .progress-bar {
                padding: 15px;
            }

            .progress-step .label {
                display: none;
            }

            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SocietyPress Installer</h1>
            <p>WordPress + SocietyPress installation wizard</p>
        </div>

        <div class="progress-bar">
            <?php
            $steps = [
                1 => 'Check',
                2 => 'Database',
                3 => 'Download',
                4 => 'Config',
                5 => 'WordPress',
                6 => 'SocietyPress',
                7 => 'Settings',
                8 => 'Done',
            ];
            foreach ($steps as $num => $label):
                $class = '';
                if ($num < $current_step) $class = 'complete';
                elseif ($num === $current_step) $class = 'active';
            ?>
            <div class="progress-step <?php echo $class; ?>">
                <span class="number"><?php echo $num < $current_step ? '&#10003;' : $num; ?></span>
                <span class="label"><?php echo $label; ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="content">
            <?php if ($error_message): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if ($current_step > 1): ?>
            <div class="security-warning">
                <h4>&#9888; Security Reminder</h4>
                <p>Remember to <strong>delete this installer file</strong> (sp-installer.php) when you're finished!</p>
            </div>
            <?php endif; ?>

            <?php
            // ================================================================
            // STEP 1: Requirements Check
            // ================================================================
            if ($current_step === 1):
                $requirements = sp_check_requirements();
                $all_pass = true;
                foreach ($requirements as $req) {
                    if (!$req['pass']) $all_pass = false;
                }
            ?>
            <h2>Welcome!</h2>
            <p class="subtitle">Let's make sure your server is ready for WordPress and SocietyPress.</p>

            <ul class="requirement-list">
                <?php foreach ($requirements as $key => $req): ?>
                <li class="requirement-item <?php echo $req['pass'] ? 'pass' : 'fail'; ?>">
                    <span class="icon"><?php echo $req['pass'] ? '&#10003;' : '&#10007;'; ?></span>
                    <span class="text">
                        <span class="name"><?php echo $req['name']; ?></span>
                        <span class="detail"><?php echo $req['message']; ?></span>
                    </span>
                </li>
                <?php endforeach; ?>
            </ul>

            <form method="post">
                <input type="hidden" name="action" value="check_requirements">
                <button type="submit" class="btn btn-primary btn-lg btn-block" <?php echo !$all_pass ? 'disabled' : ''; ?>>
                    <?php echo $all_pass ? 'Continue to Database Setup' : 'Please Fix Issues Above'; ?>
                </button>
            </form>

            <?php
            // ================================================================
            // STEP 2: Database Setup
            // ================================================================
            elseif ($current_step === 2):
            ?>
            <h2>Database Setup</h2>
            <p class="subtitle">WordPress needs a database to store your content.</p>

            <div class="alert alert-info">
                <strong>First, create a database in your hosting control panel:</strong>
                <ol style="margin: 10px 0 0 0; padding-left: 20px;">
                    <li>Log into your hosting control panel (cPanel, Plesk, etc.)</li>
                    <li>Find "MySQL Databases" or "Databases"</li>
                    <li>Create a new database</li>
                    <li>Create a new database user with a password</li>
                    <li>Add the user to the database with "All Privileges"</li>
                    <li>Enter those details below</li>
                </ol>
            <form method="post">
                <input type="hidden" name="action" value="setup_database">

                <div class="form-group">
                    <label for="db_host">Database Host</label>
                    <input type="text" id="db_host" name="db_host" value="localhost" required>
                    <p class="help-text">Usually "localhost" - your hosting provider can confirm.</p>
                </div>

                <div class="form-group">
                    <label for="db_name">Database Name</label>
                    <input type="text" id="db_name" name="db_name" required>
                    <p class="help-text">The name of the database you created.</p>
                </div>

                <div class="form-group">
                    <label for="db_user">Database Username</label>
                    <input type="text" id="db_user" name="db_user" required>
                </div>

                <div class="form-group">
                    <label for="db_pass">Database Password</label>
                    <input type="password" id="db_pass" name="db_pass">
                </div>

                <div class="form-group">
                    <label for="db_prefix">Table Prefix</label>
                    <input type="text" id="db_prefix" name="db_prefix" value="wp_">
                    <p class="help-text">Leave as "wp_" unless you have a specific reason to change it.</p>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    Test Connection &amp; Continue
                </button>
            </form>

            <?php
            // ================================================================
            // STEP 3: Download WordPress
            // ================================================================
            elseif ($current_step === 3):
            ?>
            <h2>Download WordPress</h2>
            <p class="subtitle">We'll download the latest version of WordPress.</p>

            <div class="info-box">
                <h4>Database Configured</h4>
                <p>Database: <code><?php echo sp_sanitize($_SESSION['sp_installer']['db_name']); ?></code></p>
                <p>Your database connection is ready.</p>
            </div>

            <form method="post">
                <input type="hidden" name="action" value="download_wordpress">
                <button type="submit" class="btn btn-primary btn-lg btn-block" onclick="showLoading('Downloading WordPress... This may take a minute.')">
                    Download &amp; Extract WordPress
                </button>
            </form>

            <?php
            // ================================================================
            // STEP 4: Configure WordPress
            // ================================================================
            elseif ($current_step === 4):
            ?>
            <h2>Configure WordPress</h2>
            <p class="subtitle">Creating the WordPress configuration file.</p>

            <div class="alert alert-success">
                WordPress files downloaded successfully!
            </div>

            <form method="post">
                <input type="hidden" name="action" value="configure_wordpress">
                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    Create Configuration File
                </button>
            </form>

            <?php
            // ================================================================
            // STEP 5: Install WordPress
            // ================================================================
            elseif ($current_step === 5):
                $suggested_pass = sp_generate_password(16);
            ?>
            <h2>WordPress Setup</h2>
            <p class="subtitle">Create your admin account and site settings.</p>

            <form method="post">
                <input type="hidden" name="action" value="install_wordpress">

                <div class="form-group">
                    <label for="site_title">Site Title</label>
                    <input type="text" id="site_title" name="site_title" placeholder="My Genealogical Society" required>
                    <p class="help-text">Your organization's name. You can change this later.</p>
                </div>

                <div class="form-group">
                    <label for="admin_user">Admin Username</label>
                    <input type="text" id="admin_user" name="admin_user" value="siteadmin" required>
                    <p class="help-text">Avoid using "admin" for security reasons.</p>
                </div>

                <div class="form-group">
                    <label for="admin_pass">Admin Password</label>
                    <input type="text" id="admin_pass" name="admin_pass" value="<?php echo $suggested_pass; ?>" required>
                    <p class="help-text">We've generated a strong password. Write it down or save it somewhere safe!</p>
                </div>

                <div class="form-group">
                    <label for="admin_email">Admin Email</label>
                    <input type="email" id="admin_email" name="admin_email" required>
                    <p class="help-text">You'll use this email to log in and receive notifications.</p>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block" onclick="showLoading('Installing WordPress...')">
                    Install WordPress
                </button>
            </form>

            <?php
            // ================================================================
            // STEP 6: Install SocietyPress
            // ================================================================
            elseif ($current_step === 6):
            ?>
            <h2>Install SocietyPress</h2>
            <p class="subtitle">Now let's add the SocietyPress plugin and theme.</p>

            <div class="alert alert-success">
                WordPress is installed and ready!
            </div>

            <div class="credentials-box">
                <h4>Your WordPress Login</h4>
                <div class="credential">
                    <span class="label">Username:</span>
                    <span class="value"><?php echo sp_sanitize($_SESSION['sp_installer']['admin_user']); ?></span>
                </div>
                <div class="credential">
                    <span class="label">Password:</span>
                    <span class="value"><?php echo sp_sanitize($_SESSION['sp_installer']['admin_pass']); ?></span>
                </div>
                <p style="margin-top: 12px; font-size: 14px;"><strong>Write these down!</strong> You'll need them to log into your site.</p>
            </div>

            <form method="post">
                <input type="hidden" name="action" value="install_societypress">
                <button type="submit" class="btn btn-primary btn-lg btn-block" onclick="showLoading('Installing SocietyPress plugin and theme...')">
                    Install SocietyPress
                </button>
            </form>

            <?php
            // ================================================================
            // STEP 7: Configure SocietyPress
            // ================================================================
            elseif ($current_step === 7):
            ?>
            <h2>Organization Info</h2>
            <p class="subtitle">Enter your organization's details. You can skip this and do it later.</p>

            <div class="alert alert-success">
                SocietyPress plugin and theme installed!
            </div>

            <form method="post">
                <input type="hidden" name="action" value="configure_societypress">

                <div class="form-group">
                    <label for="org_name">Organization Name</label>
                    <input type="text" id="org_name" name="org_name" placeholder="Springfield Genealogical Society">
                </div>

                <div class="form-group">
                    <label for="org_address">Address</label>
                    <textarea id="org_address" name="org_address" placeholder="123 Main Street&#10;Springfield, IL 62701"></textarea>
                </div>

                <div class="form-group">
                    <label for="org_phone">Phone Number</label>
                    <input type="text" id="org_phone" name="org_phone" placeholder="(555) 123-4567">
                </div>

                <div class="form-group">
                    <label for="org_email">Contact Email</label>
                    <input type="email" id="org_email" name="org_email" placeholder="info@mygenealogy.org">
                </div>

                <div class="button-group">
                    <button type="submit" name="action" value="skip_configure" class="btn btn-secondary">
                        Skip for Now
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Save &amp; Finish
                    </button>
                </div>
            </form>

            <?php
            // ================================================================
            // STEP 8: Complete!
            // ================================================================
            elseif ($current_step === 8):
                $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
                $site_url = $protocol . '://' . $_SERVER['HTTP_HOST'];
                $admin_url = $site_url . '/wp-admin/';
            ?>
            <h2 style="color: #28a745;">Installation Complete!</h2>
            <p class="subtitle">Your SocietyPress website is ready to use.</p>

            <div class="credentials-box">
                <h4>Important Information - Save This!</h4>
                <div class="credential">
                    <span class="label">Site URL:</span>
                    <span class="value"><?php echo $site_url; ?></span>
                </div>
                <div class="credential">
                    <span class="label">Admin URL:</span>
                    <span class="value"><?php echo $admin_url; ?></span>
                </div>
                <div class="credential">
                    <span class="label">Username:</span>
                    <span class="value"><?php echo sp_sanitize($_SESSION['sp_installer']['admin_user']); ?></span>
                </div>
                <div class="credential">
                    <span class="label">Password:</span>
                    <span class="value"><?php echo sp_sanitize($_SESSION['sp_installer']['admin_pass']); ?></span>
                </div>
            </div>

            <div class="security-warning" style="border-color: #dc3545; background: #f8d7da;">
                <h4 style="color: #721c24;">&#9888; Delete This Installer Now!</h4>
                <p style="color: #721c24;">For security, you <strong>must</strong> delete the installer file (sp-installer.php). Anyone who accesses it could potentially take over your site.</p>
            </div>

            <div class="button-group">
                <form method="post" style="flex: 1;">
                    <input type="hidden" name="action" value="delete_installer">
                    <button type="submit" class="btn btn-danger btn-block">
                        Delete Installer
                    </button>
                </form>
                <a href="<?php echo $admin_url; ?>" class="btn btn-success" style="flex: 1; text-align: center;">
                    Go to Admin Dashboard
                </a>
            </div>

            <div class="info-box" style="margin-top: 30px;">
                <h4>What's Next?</h4>
                <ul>
                    <li>Log into your admin dashboard</li>
                    <li>Go to <strong>SocietyPress &gt; Settings</strong> to configure your organization</li>
                    <li>Add your first member in <strong>SocietyPress &gt; Add New Member</strong></li>
                    <li>Create events in <strong>SocietyPress &gt; Add New Event</strong></li>
                    <li>Read the <a href="<?php echo SP_DOCS_URL; ?>" target="_blank">documentation</a> for more help</li>
                </ul>
            </div>

            <?php endif; ?>
        </div>
    </div>

    <div class="loading-overlay" id="loading">
        <div class="loading-box">
            <div class="spinner spinner-lg"></div>
            <p id="loading-text">Please wait...</p>
        </div>
    </div>

    <script>
    function showLoading(message) {
        document.getElementById('loading-text').textContent = message || 'Please wait...';
        document.getElementById('loading').classList.add('active');
    }
    </script>
</body>
</html>
