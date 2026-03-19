<?php
/**
 * SocietyPress One-Click Installer
 *
 * A single-file installer that takes a society administrator from empty hosting
 * to a fully running SocietyPress installation. Upload this file, visit it in
 * a browser, fill out one form, and SocietyPress + WordPress are installed,
 * configured, and ready to go.
 *
 * WHY: Our target users are non-technical society volunteers. Asking them to
 * install WordPress, find and upload a plugin, activate a theme, and configure
 * settings is too many steps. This installer reduces it to: upload one file,
 * fill out one form, click one button.
 *
 * SECURITY: This file MUST self-delete after successful installation. An
 * installer script left on a production server is a critical vulnerability.
 *
 * @package    SocietyPress
 * @license    GPL-2.0-or-later
 * @version    1.0.0
 */

// ============================================================================
// SAFETY CHECKS
// ============================================================================

// Prevent direct CLI execution — this is a web-only tool
if ( php_sapi_name() === 'cli' ) {
    echo "This installer must be run from a web browser.\n";
    exit( 1 );
}

// If WordPress is already installed in this directory, don't run
if ( file_exists( __DIR__ . '/wp-config.php' ) && file_exists( __DIR__ . '/wp-includes/version.php' ) ) {
    sp_installer_die(
        'WordPress Is Already Installed',
        'A WordPress installation already exists in this directory. If you need to reinstall, '
        . 'remove the existing files first. If SocietyPress is already running, you can safely '
        . 'delete this install.php file.'
    );
}

// ============================================================================
// CONFIGURATION
// ============================================================================

// Where to download WordPress and SocietyPress from
define( 'SP_INSTALLER_WP_URL',     'https://wordpress.org/latest.zip' );
define( 'SP_INSTALLER_BUNDLE_URL', 'https://getsocietypress.org/downloads/societypress-latest.zip' );
define( 'SP_INSTALLER_GITHUB_REPO', 'charles-stricklin/SocietyPress' );
define( 'SP_INSTALLER_SALT_URL',    'https://api.wordpress.org/secret-key/1.1/salt/' );

// Demo mode: if a config file exists outside the web root, load it.
// WHY: On the demo site, DB credentials are pre-configured so visitors don't need
// to know them. The config file lives outside public_html so it's never web-accessible.
// When this file exists, the installer hides the DB fields and shows "Pre-configured."
// On a real install (no config file), the full DB form is shown.
// WHY: We check multiple common paths because different hosts put the home
// directory in different places, and $_SERVER['HOME'] isn't always set.
$sp_demo_config = '';
$sp_demo_candidates = [
    dirname( $_SERVER['DOCUMENT_ROOT'] ) . '/private/sp-demo-config.php',
    ( $_SERVER['HOME'] ?? '' ) . '/private/sp-demo-config.php',
    '/home/' . ( $_SERVER['USER'] ?? get_current_user() ) . '/private/sp-demo-config.php',
    dirname( dirname( $_SERVER['DOCUMENT_ROOT'] ) ) . '/private/sp-demo-config.php',
];
foreach ( $sp_demo_candidates as $candidate ) {
    if ( $candidate && file_exists( $candidate ) ) {
        $sp_demo_config = $candidate;
        break;
    }
}
define( 'SP_INSTALLER_DEMO_MODE', ! empty( $sp_demo_config ) );
if ( SP_INSTALLER_DEMO_MODE ) {
    require_once $sp_demo_config;
}

// Minimum requirements
define( 'SP_INSTALLER_MIN_PHP',     '8.0.0' );
define( 'SP_INSTALLER_MIN_MYSQL',   '5.7.0' );


// ============================================================================
// MAIN ROUTING
// ============================================================================

session_start();

$step = $_GET['step'] ?? 'check';

// If the form was submitted, process it
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['sp_install_nonce'] ) ) {
    sp_installer_process();
    exit;
}

// Otherwise show the appropriate step
switch ( $step ) {
    case 'check':
        sp_installer_check_requirements();
        break;
    case 'configure':
        sp_installer_show_form();
        break;
    case 'complete':
        sp_installer_show_complete();
        break;
    default:
        sp_installer_check_requirements();
}
exit;


// ============================================================================
// STEP 1: REQUIREMENTS CHECK
// ============================================================================

/**
 * Check that the server meets all requirements before showing the config form.
 *
 * WHY: Better to tell Harold upfront "your hosting doesn't support this" than
 * to let him fill out a form and fail halfway through the install.
 */
function sp_installer_check_requirements(): void {
    $checks = [];
    $all_pass = true;

    // PHP version
    $php_ok = version_compare( PHP_VERSION, SP_INSTALLER_MIN_PHP, '>=' );
    $checks[] = [
        'label'  => 'PHP ' . SP_INSTALLER_MIN_PHP . '+',
        'status' => $php_ok,
        'value'  => PHP_VERSION,
        'note'   => $php_ok ? '' : 'Contact your hosting provider to upgrade PHP.',
    ];
    if ( ! $php_ok ) $all_pass = false;

    // ZipArchive (needed to extract downloads)
    $zip_ok = class_exists( 'ZipArchive' );
    $checks[] = [
        'label'  => 'ZipArchive Extension',
        'status' => $zip_ok,
        'value'  => $zip_ok ? 'Available' : 'Missing',
        'note'   => $zip_ok ? '' : 'Required to extract downloaded files. Contact your host.',
    ];
    if ( ! $zip_ok ) $all_pass = false;

    // cURL or allow_url_fopen (needed to download files)
    $curl_ok = function_exists( 'curl_init' );
    $fopen_ok = (bool) ini_get( 'allow_url_fopen' );
    $dl_ok = $curl_ok || $fopen_ok;
    $dl_value = $curl_ok ? 'cURL available' : ( $fopen_ok ? 'allow_url_fopen enabled' : 'Neither available' );
    $checks[] = [
        'label'  => 'HTTP Downloads (cURL or allow_url_fopen)',
        'status' => $dl_ok,
        'value'  => $dl_value,
        'note'   => $dl_ok ? '' : 'The installer needs to download WordPress and SocietyPress. Contact your host.',
    ];
    if ( ! $dl_ok ) $all_pass = false;

    // Sodium (needed for SocietyPress encryption)
    $sodium_ok = function_exists( 'sodium_crypto_aead_xchacha20poly1305_ietf_encrypt' );
    $checks[] = [
        'label'  => 'Sodium Extension (for encryption)',
        'status' => $sodium_ok,
        'value'  => $sodium_ok ? 'Available' : 'Missing',
        'note'   => $sodium_ok ? '' : 'SocietyPress uses this to encrypt sensitive member data. It works without it, but encryption will be disabled.',
    ];
    // Sodium is recommended but not required — don't fail the check

    // MySQL / MariaDB via PDO or mysqli
    $db_ok = extension_loaded( 'mysqli' ) || extension_loaded( 'pdo_mysql' );
    $checks[] = [
        'label'  => 'MySQL Support (mysqli or PDO)',
        'status' => $db_ok,
        'value'  => $db_ok ? ( extension_loaded( 'mysqli' ) ? 'mysqli' : 'PDO' ) : 'Missing',
        'note'   => $db_ok ? '' : 'WordPress requires MySQL. Contact your host.',
    ];
    if ( ! $db_ok ) $all_pass = false;

    // Directory writable
    $write_ok = is_writable( __DIR__ );
    $checks[] = [
        'label'  => 'Directory Writable',
        'status' => $write_ok,
        'value'  => $write_ok ? 'Yes' : 'No',
        'note'   => $write_ok ? '' : 'The installer needs to write files to this directory. Check your hosting file permissions.',
    ];
    if ( ! $write_ok ) $all_pass = false;

    // Disk space (need ~100MB for WordPress + SocietyPress + temp files)
    $free_space = @disk_free_space( __DIR__ );
    $space_ok = ( $free_space === false ) || ( $free_space > 100 * 1024 * 1024 );
    $checks[] = [
        'label'  => 'Disk Space (100 MB+ recommended)',
        'status' => $space_ok,
        'value'  => $free_space !== false ? round( $free_space / 1024 / 1024 ) . ' MB free' : 'Unable to check',
        'note'   => $space_ok ? '' : 'You may not have enough disk space. WordPress + SocietyPress need about 100 MB.',
    ];

    sp_installer_render_page( 'Server Requirements Check', function () use ( $checks, $all_pass ) {
        ?>
        <p style="margin-bottom: 24px; color: #6B7280;">
            Before we begin, let's make sure your hosting can run SocietyPress.
        </p>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
            <?php foreach ( $checks as $check ) : ?>
                <tr style="border-bottom: 1px solid #E5E7EB;">
                    <td style="padding: 12px 16px; font-weight: 500;">
                        <?php echo htmlspecialchars( $check['label'] ); ?>
                    </td>
                    <td style="padding: 12px 16px; text-align: center; width: 40px;">
                        <?php if ( $check['status'] ) : ?>
                            <span style="color: #16A34A; font-size: 20px;">&#10003;</span>
                        <?php else : ?>
                            <span style="color: #DC2626; font-size: 20px;">&#10005;</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 12px 16px; color: #6B7280;">
                        <?php echo htmlspecialchars( $check['value'] ); ?>
                        <?php if ( $check['note'] ) : ?>
                            <br><small style="color: #DC2626;"><?php echo htmlspecialchars( $check['note'] ); ?></small>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <?php if ( $all_pass ) : ?>
            <p style="color: #16A34A; font-weight: 600; margin-bottom: 16px;">
                &#10003; Your server meets all requirements. Ready to install!
            </p>
            <a href="?step=configure" class="sp-btn">Continue to Setup</a>
        <?php else : ?>
            <p style="color: #DC2626; font-weight: 600;">
                &#10005; Some requirements are not met. Please resolve the issues above before continuing.
            </p>
            <p style="margin-top: 12px;">
                <a href="?" class="sp-btn" style="background: #6B7280;">Re-Check Requirements</a>
            </p>
        <?php endif; ?>
        <?php
    } );
}


// ============================================================================
// STEP 2: CONFIGURATION FORM
// ============================================================================

/**
 * Show the main configuration form: database credentials + site info.
 *
 * WHY: We collect everything in one form so Harold only has to fill out one
 * page. The form is designed to be as clear as possible for someone who has
 * never seen a database configuration screen before.
 */
function sp_installer_show_form(): void {
    // Generate a one-time nonce for CSRF protection
    $nonce = bin2hex( random_bytes( 16 ) );
    $_SESSION['sp_install_nonce'] = $nonce;

    sp_installer_render_page( 'Configure Your Site', function () use ( $nonce ) {
        ?>
        <?php if ( SP_INSTALLER_DEMO_MODE ) : ?>
            <p style="margin-bottom: 24px; color: #6B7280;">
                This is what the SocietyPress installer looks like. The database fields below show
                example values — on your own hosting, you'd fill in the real credentials from your
                hosting provider. For this demo, just scroll down, enter a society name and password,
                and click Install.
            </p>
        <?php else : ?>
            <p style="margin-bottom: 24px; color: #6B7280;">
                You'll need your database credentials from your hosting provider. If you're not sure
                where to find them, check your hosting control panel (cPanel, Plesk, etc.) under
                "MySQL Databases" or "Database Management."
            </p>
        <?php endif; ?>

        <form method="post" action="?step=configure" id="sp-install-form">
            <input type="hidden" name="sp_install_nonce" value="<?php echo htmlspecialchars( $nonce ); ?>">

            <!-- Database Settings -->
            <h2 style="font-size: 18px; font-weight: 700; color: #0D1F3C; margin: 32px 0 16px; padding-bottom: 8px; border-bottom: 2px solid #C9973A;">
                Database Connection
            </h2>

            <?php if ( SP_INSTALLER_DEMO_MODE ) : ?>
                <!-- Demo mode: show realistic example fields (disabled) with coaching text.
                     Real credentials are submitted via hidden inputs underneath. Howard sees
                     what he'll encounter on his own hosting, with explanations. -->
                <input type="hidden" name="db_name" value="<?php echo htmlspecialchars( SP_DEMO_DB_NAME ); ?>">
                <input type="hidden" name="db_user" value="<?php echo htmlspecialchars( SP_DEMO_DB_USER ); ?>">
                <input type="hidden" name="db_pass" value="<?php echo htmlspecialchars( SP_DEMO_DB_PASS ); ?>">
                <input type="hidden" name="db_host" value="<?php echo htmlspecialchars( SP_DEMO_DB_HOST ); ?>">
                <input type="hidden" name="db_prefix" value="wp_">

                <p style="color: #6B7280; font-size: 13px; margin-bottom: 16px;">
                    When you install on your own hosting, you'll fill in the database credentials
                    your hosting provider gives you. Here's what that looks like:
                </p>

                <table class="sp-form-table">
                    <tr>
                        <th><label>Database Name</label></th>
                        <td>
                            <input type="text" value="elmcounty_societypress" disabled readonly tabindex="-1"
                                   style="background: #F3F4F6; color: #9CA3AF; border: 1px dashed #D1D5DB; cursor: not-allowed; opacity: 0.7;">
                            <p class="desc">&larr; Your hosting provider gives you this when you create a MySQL database.</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Database Username</label></th>
                        <td>
                            <input type="text" value="elmcounty_spadmin" disabled readonly tabindex="-1"
                                   style="background: #F3F4F6; color: #9CA3AF; border: 1px dashed #D1D5DB; cursor: not-allowed; opacity: 0.7;">
                            <p class="desc">&larr; Created at the same time as the database.</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Database Password</label></th>
                        <td>
                            <input type="password" value="fake-password-example" disabled readonly tabindex="-1"
                                   style="background: #F3F4F6; color: #9CA3AF; border: 1px dashed #D1D5DB; cursor: not-allowed; opacity: 0.7;">
                            <p class="desc">&larr; The password you set when creating the database user.</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Database Host</label></th>
                        <td>
                            <input type="text" value="localhost" disabled readonly tabindex="-1"
                                   style="background: #F3F4F6; color: #9CA3AF; border: 1px dashed #D1D5DB; cursor: not-allowed; opacity: 0.7;">
                            <p class="desc">&larr; Almost always "localhost." Only change if your host tells you to.</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Table Prefix</label></th>
                        <td>
                            <input type="text" value="wp_" disabled readonly tabindex="-1"
                                   style="background: #F3F4F6; color: #9CA3AF; border: 1px dashed #D1D5DB; cursor: not-allowed; opacity: 0.7;">
                            <p class="desc">&larr; Leave as "wp_" unless you have a reason to change it.</p>
                        </td>
                    </tr>
                </table>

                <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px; padding: 12px 16px; margin: 16px 0 8px; font-size: 13px; color: #1E40AF;">
                    &#9432; For this demo, the database is pre-configured. Just fill in your society details below and click Install.
                </div>

            <?php else : ?>
                <!-- Production mode: full DB configuration form -->
                <p style="color: #6B7280; font-size: 13px; margin-bottom: 16px;">
                    Your hosting provider gave you these when you created a MySQL database.
                </p>

                <table class="sp-form-table">
                    <tr>
                        <th><label for="db_name">Database Name</label></th>
                        <td>
                            <input type="text" id="db_name" name="db_name" required
                                   placeholder="e.g., society_db" autocomplete="off">
                            <p class="desc">The name of the database you created for this site.</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="db_user">Database Username</label></th>
                        <td>
                            <input type="text" id="db_user" name="db_user" required
                                   placeholder="e.g., society_user" autocomplete="off">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="db_pass">Database Password</label></th>
                        <td>
                            <input type="password" id="db_pass" name="db_pass"
                                   placeholder="Your database password" autocomplete="new-password">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="db_host">Database Host</label></th>
                        <td>
                            <input type="text" id="db_host" name="db_host" value="localhost">
                            <p class="desc">Almost always "localhost." Only change this if your host tells you to.</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="db_prefix">Table Prefix</label></th>
                        <td>
                            <input type="text" id="db_prefix" name="db_prefix" value="wp_"
                                   pattern="[a-zA-Z_][a-zA-Z0-9_]*" maxlength="20">
                            <p class="desc">Leave as "wp_" unless you have a reason to change it.</p>
                        </td>
                    </tr>
                </table>
            <?php endif; ?>

            <!-- Site Settings -->
            <h2 style="font-size: 18px; font-weight: 700; color: #0D1F3C; margin: 32px 0 16px; padding-bottom: 8px; border-bottom: 2px solid #C9973A;">
                Your Society
            </h2>

            <table class="sp-form-table">
                <tr>
                    <th><label for="site_title">Society Name</label></th>
                    <td>
                        <input type="text" id="site_title" name="site_title" required
                               placeholder="e.g., Elm County Genealogical Society">
                        <p class="desc">This appears as your site title. You can change it later.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="admin_email">Admin Email</label></th>
                    <td>
                        <input type="email" id="admin_email" name="admin_email" required
                               placeholder="you@example.com">
                        <p class="desc">Used for admin login and system notifications.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="admin_user">Admin Username</label></th>
                    <td>
                        <input type="text" id="admin_user" name="admin_user" required
                               value="admin" pattern="[a-zA-Z0-9_\-\.]{3,60}">
                        <p class="desc">Your login username. Choose something memorable.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="admin_pass">Admin Password</label></th>
                    <td>
                        <input type="password" id="admin_pass" name="admin_pass" required
                               minlength="8" autocomplete="new-password">
                        <p class="desc">At least 8 characters. Make it strong — this protects your entire site.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="admin_pass2">Confirm Password</label></th>
                    <td>
                        <input type="password" id="admin_pass2" name="admin_pass2" required
                               minlength="8" autocomplete="new-password">
                    </td>
                </tr>
            </table>

            <!-- Honeypot — bots fill this in, humans don't see it -->
            <div style="position: absolute; left: -9999px;" aria-hidden="true">
                <input type="text" name="sp_hp_field" tabindex="-1" autocomplete="off">
            </div>

            <div style="margin-top: 32px; text-align: center;">
                <button type="submit" class="sp-btn" id="sp-install-btn">
                    Install SocietyPress
                </button>
                <p style="color: #6B7280; font-size: 13px; margin-top: 12px;">
                    This will download and install WordPress + SocietyPress. It may take a minute or two.
                </p>
            </div>
        </form>

        <script>
        // Simple client-side password match check
        document.getElementById('sp-install-form').addEventListener('submit', function(e) {
            var p1 = document.getElementById('admin_pass').value;
            var p2 = document.getElementById('admin_pass2').value;
            if (p1 !== p2) {
                e.preventDefault();
                alert('Passwords do not match. Please re-enter.');
                document.getElementById('admin_pass2').focus();
                return;
            }
            // Show progress indicator
            var btn = document.getElementById('sp-install-btn');
            btn.disabled = true;
            btn.textContent = 'Installing... please wait';
            btn.style.opacity = '0.7';
        });
        </script>
        <?php
    } );
}


// ============================================================================
// STEP 3: PROCESS THE INSTALLATION
// ============================================================================

/**
 * Process the form submission and run the full installation.
 *
 * WHY: This is the core of the installer. Each step is wrapped in error
 * handling so we can give Harold a clear message about what went wrong if
 * anything fails. The order matters — we validate first, download second,
 * configure third, and clean up last.
 */
function sp_installer_process(): void {
    // CSRF check
    if ( ! isset( $_SESSION['sp_install_nonce'] ) || $_POST['sp_install_nonce'] !== $_SESSION['sp_install_nonce'] ) {
        sp_installer_die( 'Security Error', 'Invalid session. Please go back and try again.' );
    }
    unset( $_SESSION['sp_install_nonce'] );

    // Honeypot check
    if ( ! empty( $_POST['sp_hp_field'] ) ) {
        // Bot detected — silently die
        http_response_code( 403 );
        exit;
    }

    // Collect and sanitize inputs
    $db_name   = trim( $_POST['db_name'] ?? '' );
    $db_user   = trim( $_POST['db_user'] ?? '' );
    $db_pass   = $_POST['db_pass'] ?? '';
    $db_host   = trim( $_POST['db_host'] ?? 'localhost' );
    $db_prefix = preg_replace( '/[^a-zA-Z0-9_]/', '', $_POST['db_prefix'] ?? 'wp_' );

    $site_title  = trim( $_POST['site_title'] ?? '' );
    $admin_email = trim( $_POST['admin_email'] ?? '' );
    $admin_user  = trim( $_POST['admin_user'] ?? '' );
    $admin_pass  = $_POST['admin_pass'] ?? '';
    $admin_pass2 = $_POST['admin_pass2'] ?? '';

    // Validate
    $errors = [];
    if ( empty( $db_name ) )    $errors[] = 'Database name is required.';
    if ( empty( $db_user ) )    $errors[] = 'Database username is required.';
    if ( empty( $site_title ) ) $errors[] = 'Society name is required.';
    if ( empty( $admin_email ) || ! filter_var( $admin_email, FILTER_VALIDATE_EMAIL ) ) {
        $errors[] = 'A valid admin email address is required.';
    }
    if ( empty( $admin_user ) || strlen( $admin_user ) < 3 ) {
        $errors[] = 'Admin username must be at least 3 characters.';
    }
    if ( strlen( $admin_pass ) < 8 ) {
        $errors[] = 'Admin password must be at least 8 characters.';
    }
    if ( $admin_pass !== $admin_pass2 ) {
        $errors[] = 'Passwords do not match.';
    }
    if ( ! empty( $db_prefix ) && ! preg_match( '/^[a-zA-Z_][a-zA-Z0-9_]*$/', $db_prefix ) ) {
        $errors[] = 'Table prefix must start with a letter or underscore and contain only letters, numbers, and underscores.';
    }
    if ( $errors ) {
        sp_installer_die( 'Validation Error', implode( '<br>', array_map( 'htmlspecialchars', $errors ) ) );
    }

    // Ensure prefix ends with underscore
    if ( substr( $db_prefix, -1 ) !== '_' ) {
        $db_prefix .= '_';
    }

    $install_dir = __DIR__;
    $log = [];

    // ---- 1. Test database connection ----
    $log[] = 'Testing database connection...';
    $conn = @new mysqli( $db_host, $db_user, $db_pass, $db_name );
    if ( $conn->connect_error ) {
        sp_installer_die(
            'Database Connection Failed',
            'Could not connect to the database. Please check your credentials.<br><br>'
            . '<strong>Error:</strong> ' . htmlspecialchars( $conn->connect_error ) . '<br><br>'
            . 'Common fixes:<ul style="margin: 8px 0 0 20px;">'
            . '<li>Make sure the database name, username, and password are correct</li>'
            . '<li>Make sure the database user has permission to access the database</li>'
            . '<li>If your host uses a non-standard database host, enter it instead of "localhost"</li>'
            . '</ul>'
        );
    }
    $conn->close();
    $log[] = 'Database connection successful.';

    // ---- 2. Download WordPress ----
    $log[] = 'Downloading WordPress...';
    $wp_zip_path = $install_dir . '/wordpress-latest.zip';
    $wp_downloaded = sp_installer_download( SP_INSTALLER_WP_URL, $wp_zip_path );
    if ( ! $wp_downloaded ) {
        sp_installer_die( 'Download Failed', 'Could not download WordPress from wordpress.org. Please check that your server allows outbound HTTP connections.' );
    }
    $log[] = 'WordPress downloaded.';

    // ---- 3. Extract WordPress ----
    $log[] = 'Extracting WordPress...';
    $zip = new ZipArchive();
    if ( $zip->open( $wp_zip_path ) !== true ) {
        @unlink( $wp_zip_path );
        sp_installer_die( 'Extract Failed', 'Could not open the WordPress ZIP file.' );
    }

    // WordPress ZIP extracts to a "wordpress/" subdirectory — we need to move
    // the contents up to the install directory
    $zip->extractTo( $install_dir );
    $zip->close();
    @unlink( $wp_zip_path );

    // Move files from wordpress/ subdirectory to install root
    $wp_subdir = $install_dir . '/wordpress';
    if ( is_dir( $wp_subdir ) ) {
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator( $wp_subdir, RecursiveDirectoryIterator::SKIP_DOTS ),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ( $items as $item ) {
            $target = $install_dir . '/' . substr( $item->getPathname(), strlen( $wp_subdir ) + 1 );
            if ( $item->isDir() ) {
                @mkdir( $target, 0755, true );
            } else {
                @rename( $item->getPathname(), $target );
            }
        }
        // Clean up the now-empty wordpress directory
        sp_installer_rmdir( $wp_subdir );
    }
    $log[] = 'WordPress extracted.';

    // ---- 4. Generate security keys ----
    $log[] = 'Generating security keys...';
    $salts = sp_installer_download_string( SP_INSTALLER_SALT_URL );
    if ( ! $salts || strpos( $salts, 'AUTH_KEY' ) === false ) {
        // Fallback: generate our own keys if the API is unreachable
        $salts = '';
        $key_names = [
            'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY',
            'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT',
        ];
        foreach ( $key_names as $name ) {
            $salts .= "define( '{$name}', '" . bin2hex( random_bytes( 32 ) ) . "' );\n";
        }
    }
    $log[] = 'Security keys generated.';

    // ---- 5. Write wp-config.php ----
    $log[] = 'Writing configuration file...';
    $config_sample = $install_dir . '/wp-config-sample.php';
    if ( ! file_exists( $config_sample ) ) {
        sp_installer_die( 'Configuration Error', 'wp-config-sample.php not found. The WordPress download may be corrupted.' );
    }

    $config = file_get_contents( $config_sample );

    // Replace database constants
    $config = str_replace( "database_name_here", $db_name, $config );
    $config = str_replace( "username_here", $db_user, $config );
    $config = str_replace( "password_here", $db_pass, $config );
    $config = str_replace( "localhost", $db_host, $config );
    $config = preg_replace( '/\$table_prefix\s*=\s*\'wp_\'/', "\$table_prefix = '{$db_prefix}'", $config );

    // Replace salt block
    $config = preg_replace(
        '/define\(\s*\'AUTH_KEY\'.*?define\(\s*\'NONCE_SALT\'[^;]*;\s*/s',
        trim( $salts ) . "\n\n",
        $config
    );

    // Add debug settings (off for production)
    $config = str_replace(
        "define( 'WP_DEBUG', false );",
        "define( 'WP_DEBUG', false );\ndefine( 'WP_DEBUG_LOG', false );\ndefine( 'WP_DEBUG_DISPLAY', false );",
        $config
    );

    if ( ! file_put_contents( $install_dir . '/wp-config.php', $config ) ) {
        sp_installer_die( 'Write Error', 'Could not write wp-config.php. Check that the directory is writable.' );
    }
    $log[] = 'Configuration file written.';

    // ---- 6. Run WordPress installation ----
    $log[] = 'Installing WordPress...';

    // WHY: We define ABSPATH and load wp-admin/install.php's core function
    // directly rather than making an HTTP request to ourselves. This avoids
    // issues with firewalls, self-referencing URLs, and HTTP vs HTTPS mismatches.
    define( 'ABSPATH', $install_dir . '/' );
    define( 'WPINC', 'wp-includes' );

    // Suppress WordPress startup output
    ob_start();

    // Load WordPress core
    require_once ABSPATH . 'wp-settings.php';

    // Run the install
    if ( ! function_exists( 'wp_install' ) ) {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    }

    $result = wp_install( $site_title, $admin_user, $admin_email, true, '', $admin_pass );

    ob_end_clean();

    if ( is_wp_error( $result ) ) {
        sp_installer_die( 'WordPress Install Failed', 'Error: ' . htmlspecialchars( $result->get_error_message() ) );
    }
    $log[] = 'WordPress installed.';

    // ---- 7. Set permalinks ----
    $log[] = 'Configuring permalinks...';
    global $wp_rewrite;
    $wp_rewrite->set_permalink_structure( '/%postname%/' );
    $wp_rewrite->flush_rules( true );
    $log[] = 'Permalinks configured.';

    // ---- 8. Download and install SocietyPress ----
    // WHY: We try our own bundle URL first (hosted on getsocietypress.org) because
    // it's a direct download with no redirects. GitHub's archive URLs require redirect
    // following and sometimes fail on locked-down shared hosts. The bundle contains
    // the plugin + parent theme + all child themes in a simple flat structure.
    $log[] = 'Downloading SocietyPress...';

    $sp_zip_path = $install_dir . '/societypress-latest.zip';
    $sp_downloaded = sp_installer_download( SP_INSTALLER_BUNDLE_URL, $sp_zip_path );

    if ( ! $sp_downloaded ) {
        // Fallback: try GitHub
        $log[] = 'Bundle download failed, trying GitHub...';
        $gh_url = sp_installer_get_github_release_url();
        if ( ! $gh_url ) {
            $gh_url = 'https://github.com/' . SP_INSTALLER_GITHUB_REPO . '/archive/refs/heads/main.zip';
        }
        $sp_downloaded = sp_installer_download( $gh_url, $sp_zip_path );
    }

    if ( ! $sp_downloaded ) {
        sp_installer_die(
            'SocietyPress Download Failed',
            'WordPress was installed successfully, but we could not download SocietyPress. '
            . 'You can install it manually: download the plugin from '
            . '<a href="https://github.com/' . htmlspecialchars( SP_INSTALLER_GITHUB_REPO ) . '/releases">GitHub</a> '
            . 'and upload it through your WordPress admin panel.'
        );
    }
    $log[] = 'SocietyPress downloaded.';

    // ---- 9. Extract SocietyPress plugin and themes ----
    $log[] = 'Installing SocietyPress plugin and themes...';

    $zip = new ZipArchive();
    if ( $zip->open( $sp_zip_path ) !== true ) {
        @unlink( $sp_zip_path );
        sp_installer_die( 'Extract Failed', 'Could not open the SocietyPress ZIP file.' );
    }

    // The bundle ZIP has a flat structure:
    //   societypress/societypress.php  → wp-content/plugins/societypress/
    //   themes/societypress/           → wp-content/themes/societypress/
    //   themes/heritage/               → wp-content/themes/heritage/
    //   themes/coastline/              → wp-content/themes/coastline/
    //   themes/prairie/                → wp-content/themes/prairie/
    //   themes/ledger/                 → wp-content/themes/ledger/
    //
    // If we got a GitHub ZIP instead (fallback), it has:
    //   SocietyPress-main/plugin/      → wp-content/plugins/societypress/
    //   SocietyPress-main/theme/       → wp-content/themes/societypress/
    //   SocietyPress-main/theme-NAME/  → wp-content/themes/NAME/

    // Detect which format we got
    $is_bundle = false;
    $top_dir   = '';
    for ( $i = 0; $i < $zip->numFiles; $i++ ) {
        $name = $zip->getNameIndex( $i );
        if ( strpos( $name, 'societypress/societypress.php' ) === 0 ) {
            $is_bundle = true;
            break;
        }
        if ( preg_match( '#^([^/]+)/plugin/#', $name, $m ) ) {
            $top_dir = $m[1];
            break;
        }
    }

    if ( $is_bundle ) {
        // Bundle format: extract directly
        for ( $i = 0; $i < $zip->numFiles; $i++ ) {
            $name = $zip->getNameIndex( $i );

            // Plugin: societypress/* → plugins/societypress/*
            if ( strpos( $name, 'societypress/' ) === 0 && strpos( $name, 'themes/' ) !== 0 ) {
                $target = WP_CONTENT_DIR . '/plugins/' . $name;
                if ( substr( $name, -1 ) === '/' ) {
                    @mkdir( $target, 0755, true );
                } else {
                    @mkdir( dirname( $target ), 0755, true );
                    file_put_contents( $target, $zip->getFromIndex( $i ) );
                }
            }

            // Themes: themes/* → themes/*
            if ( strpos( $name, 'themes/' ) === 0 ) {
                $target = WP_CONTENT_DIR . '/' . $name;
                if ( substr( $name, -1 ) === '/' ) {
                    @mkdir( $target, 0755, true );
                } else {
                    @mkdir( dirname( $target ), 0755, true );
                    file_put_contents( $target, $zip->getFromIndex( $i ) );
                }
            }
        }
    } elseif ( $top_dir ) {
        // GitHub format: extract with path rewriting
        $plugin_dir = WP_CONTENT_DIR . '/plugins/societypress/';
        @mkdir( $plugin_dir, 0755, true );

        $child_themes = [ 'heritage', 'coastline', 'prairie', 'ledger' ];

        for ( $i = 0; $i < $zip->numFiles; $i++ ) {
            $name = $zip->getNameIndex( $i );

            // Plugin
            if ( strpos( $name, "{$top_dir}/plugin/" ) === 0 ) {
                $rel = substr( $name, strlen( "{$top_dir}/plugin/" ) );
                if ( $rel === '' ) continue;
                $target = $plugin_dir . $rel;
                if ( substr( $name, -1 ) === '/' ) { @mkdir( $target, 0755, true ); }
                else { @mkdir( dirname( $target ), 0755, true ); file_put_contents( $target, $zip->getFromIndex( $i ) ); }
            }

            // Parent theme
            if ( strpos( $name, "{$top_dir}/theme/" ) === 0 && strpos( $name, "{$top_dir}/theme-" ) !== 0 ) {
                $rel = substr( $name, strlen( "{$top_dir}/theme/" ) );
                if ( $rel === '' || strpos( $rel, 'saghs/' ) === 0 ) continue;
                $target = WP_CONTENT_DIR . '/themes/societypress/' . $rel;
                if ( substr( $name, -1 ) === '/' ) { @mkdir( $target, 0755, true ); }
                else { @mkdir( dirname( $target ), 0755, true ); file_put_contents( $target, $zip->getFromIndex( $i ) ); }
            }

            // Child themes
            foreach ( $child_themes as $ct ) {
                if ( strpos( $name, "{$top_dir}/theme-{$ct}/" ) === 0 ) {
                    $rel = substr( $name, strlen( "{$top_dir}/theme-{$ct}/" ) );
                    if ( $rel === '' ) continue;
                    $target = WP_CONTENT_DIR . "/themes/{$ct}/" . $rel;
                    if ( substr( $name, -1 ) === '/' ) { @mkdir( $target, 0755, true ); }
                    else { @mkdir( dirname( $target ), 0755, true ); file_put_contents( $target, $zip->getFromIndex( $i ) ); }
                }
            }
        }
    } else {
        $zip->close();
        @unlink( $sp_zip_path );
        sp_installer_die( 'Extract Failed', 'Could not find SocietyPress in the downloaded archive.' );
    }

    $zip->close();
    @unlink( $sp_zip_path );
    $log[] = 'SocietyPress plugin and themes installed.';

    // ---- 10. Activate plugin and theme ----
    $log[] = 'Activating SocietyPress...';

    // Activate the plugin
    $active_plugins = get_option( 'active_plugins', [] );
    $sp_plugin_file = 'societypress/societypress.php';
    if ( ! in_array( $sp_plugin_file, $active_plugins, true ) ) {
        $active_plugins[] = $sp_plugin_file;
        update_option( 'active_plugins', $active_plugins );
    }

    // Activate the theme
    update_option( 'template', 'societypress' );
    update_option( 'stylesheet', 'societypress' );

    $log[] = 'SocietyPress activated.';

    // ---- 11. Self-destruct ----
    // WHY: An installer script left on a production server is a critical
    // security vulnerability. Anyone who finds it could reinstall WordPress
    // and take over the site. Delete it immediately.
    $log[] = 'Cleaning up installer...';
    @unlink( __FILE__ );
    $log[] = 'Installer removed.';

    // ---- 12. Store completion data for the success page ----
    $_SESSION['sp_install_complete'] = true;
    $_SESSION['sp_install_log']      = $log;
    $_SESSION['sp_admin_url']        = site_url( '/wp-admin/' );
    $_SESSION['sp_site_url']         = site_url( '/' );

    // Redirect to the success page
    header( 'Location: ' . site_url( '/wp-admin/index.php' ) );
    exit;
}


// ============================================================================
// STEP 4: COMPLETION PAGE
// ============================================================================

/**
 * Show the success page after installation.
 */
function sp_installer_show_complete(): void {
    $admin_url = $_SESSION['sp_admin_url'] ?? '/wp-admin/';
    $site_url  = $_SESSION['sp_site_url'] ?? '/';
    $log       = $_SESSION['sp_install_log'] ?? [];

    // Clear session data
    unset( $_SESSION['sp_install_complete'], $_SESSION['sp_install_log'],
           $_SESSION['sp_admin_url'], $_SESSION['sp_site_url'] );

    sp_installer_render_page( 'Installation Complete!', function () use ( $admin_url, $site_url, $log ) {
        ?>
        <div style="text-align: center; padding: 20px 0;">
            <div style="font-size: 64px; margin-bottom: 16px;">&#127881;</div>
            <h2 style="font-size: 24px; color: #0D1F3C; margin-bottom: 8px;">SocietyPress is ready!</h2>
            <p style="color: #6B7280; margin-bottom: 32px;">
                Your site is installed and configured. The SocietyPress setup wizard will guide you
                through the rest — your organization details, membership settings, and design choices.
            </p>

            <a href="<?php echo htmlspecialchars( $admin_url ); ?>" class="sp-btn">
                Go to Your Dashboard &rarr;
            </a>

            <p style="margin-top: 16px;">
                <a href="<?php echo htmlspecialchars( $site_url ); ?>" style="color: #C9973A; text-decoration: none;">
                    or visit your new site
                </a>
            </p>
        </div>

        <?php if ( $log ) : ?>
            <details style="margin-top: 32px; padding: 16px; background: #F9FAFB; border-radius: 8px;">
                <summary style="cursor: pointer; font-weight: 600; color: #6B7280;">Installation Log</summary>
                <pre style="margin-top: 8px; font-size: 12px; color: #374151; white-space: pre-wrap;"><?php
                    echo htmlspecialchars( implode( "\n", $log ) );
                ?></pre>
            </details>
        <?php endif; ?>
        <?php
    } );
}


// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Download a file from a URL to a local path.
 * Tries cURL first, falls back to file_get_contents.
 */
function sp_installer_download( string $url, string $dest ): bool {
    // Try cURL first
    if ( function_exists( 'curl_init' ) ) {
        $ch = curl_init( $url );
        $fp = fopen( $dest, 'w' );
        if ( ! $fp ) return false;

        curl_setopt_array( $ch, [
            CURLOPT_FILE            => $fp,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_MAXREDIRS       => 5,
            CURLOPT_TIMEOUT         => 300,
            CURLOPT_SSL_VERIFYPEER  => true,
            CURLOPT_USERAGENT       => 'SocietyPress-Installer/1.0',
        ] );

        $success = curl_exec( $ch );
        $code    = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        curl_close( $ch );
        fclose( $fp );

        if ( $success && $code >= 200 && $code < 400 && filesize( $dest ) > 1000 ) {
            return true;
        }
        @unlink( $dest );
    }

    // Fallback to file_get_contents
    if ( ini_get( 'allow_url_fopen' ) ) {
        $ctx = stream_context_create( [
            'http' => [
                'timeout'    => 300,
                'user_agent' => 'SocietyPress-Installer/1.0',
                'follow_location' => true,
            ],
        ] );
        $data = @file_get_contents( $url, false, $ctx );
        if ( $data && strlen( $data ) > 1000 ) {
            return (bool) file_put_contents( $dest, $data );
        }
    }

    return false;
}

/**
 * Download a URL and return the content as a string.
 */
function sp_installer_download_string( string $url ): ?string {
    if ( function_exists( 'curl_init' ) ) {
        $ch = curl_init( $url );
        curl_setopt_array( $ch, [
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_TIMEOUT         => 30,
            CURLOPT_SSL_VERIFYPEER  => true,
            CURLOPT_USERAGENT       => 'SocietyPress-Installer/1.0',
        ] );
        $result = curl_exec( $ch );
        curl_close( $ch );
        if ( $result ) return $result;
    }

    if ( ini_get( 'allow_url_fopen' ) ) {
        $result = @file_get_contents( $url );
        if ( $result ) return $result;
    }

    return null;
}

/**
 * Get the latest release ZIP URL from GitHub.
 */
function sp_installer_get_github_release_url(): ?string {
    $api_url = 'https://api.github.com/repos/' . SP_INSTALLER_GITHUB_REPO . '/releases/latest';
    $json = sp_installer_download_string( $api_url );
    if ( ! $json ) return null;

    $data = json_decode( $json, true );
    return $data['zipball_url'] ?? null;
}

/**
 * Recursively remove a directory.
 */
function sp_installer_rmdir( string $dir ): void {
    if ( ! is_dir( $dir ) ) return;
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS ),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ( $items as $item ) {
        if ( $item->isDir() ) {
            @rmdir( $item->getPathname() );
        } else {
            @unlink( $item->getPathname() );
        }
    }
    @rmdir( $dir );
}

/**
 * Show an error page and stop.
 */
function sp_installer_die( string $title, string $message ): void {
    sp_installer_render_page( $title, function () use ( $message ) {
        echo '<div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 8px; padding: 20px; margin-bottom: 24px;">';
        echo '<p style="color: #991B1B; margin: 0;">' . $message . '</p>';
        echo '</div>';
        echo '<a href="javascript:history.back()" class="sp-btn" style="background: #6B7280;">Go Back</a>';
    } );
    exit;
}

/**
 * Render a full HTML page with SocietyPress branding.
 *
 * WHY: Every page the installer shows should look professional and on-brand.
 * This is Harold's first impression of SocietyPress — it needs to feel polished
 * and trustworthy, not like a raw PHP script.
 */
function sp_installer_render_page( string $title, callable $content ): void {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars( $title ); ?> — SocietyPress Installer</title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

            * { margin: 0; padding: 0; box-sizing: border-box; }

            body {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                background: #f0f2f5;
                color: #1A1A1A;
                line-height: 1.6;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 40px 20px;
            }

            .sp-installer {
                background: #fff;
                border-radius: 16px;
                box-shadow: 0 4px 24px rgba(0,0,0,0.08);
                max-width: 680px;
                width: 100%;
                overflow: hidden;
            }

            .sp-installer-header {
                background: #0D1F3C;
                color: #fff;
                padding: 24px 32px;
                text-align: center;
            }

            .sp-installer-header h1 {
                font-size: 14px;
                font-weight: 400;
                letter-spacing: 2px;
                text-transform: uppercase;
                color: #C9973A;
                margin-bottom: 4px;
            }

            .sp-installer-header .step-title {
                font-size: 22px;
                font-weight: 700;
            }

            .sp-installer-body {
                padding: 32px;
            }

            .sp-btn {
                display: inline-block;
                background: #C9973A;
                color: #fff;
                border: none;
                padding: 14px 32px;
                font-size: 15px;
                font-weight: 600;
                border-radius: 8px;
                cursor: pointer;
                text-decoration: none;
                transition: background 0.2s;
            }
            .sp-btn:hover { background: #B8862F; }

            .sp-form-table {
                width: 100%;
                border-collapse: collapse;
            }
            .sp-form-table tr {
                border-bottom: 1px solid #F3F4F6;
            }
            .sp-form-table th {
                text-align: left;
                padding: 14px 16px 14px 0;
                font-weight: 600;
                font-size: 14px;
                color: #374151;
                width: 160px;
                vertical-align: top;
                padding-top: 18px;
            }
            .sp-form-table td {
                padding: 12px 0;
            }
            .sp-form-table input[type="text"],
            .sp-form-table input[type="email"],
            .sp-form-table input[type="password"] {
                width: 100%;
                padding: 10px 14px;
                border: 1px solid #D1D5DB;
                border-radius: 6px;
                font-size: 14px;
                font-family: inherit;
                transition: border-color 0.2s;
            }
            .sp-form-table input:focus {
                outline: none;
                border-color: #C9973A;
                box-shadow: 0 0 0 3px rgba(201, 151, 58, 0.1);
            }
            .sp-form-table .desc {
                font-size: 12px;
                color: #9CA3AF;
                margin-top: 4px;
            }

            @media (max-width: 600px) {
                .sp-form-table th {
                    display: block;
                    width: 100%;
                    padding-bottom: 4px;
                }
                .sp-form-table td {
                    display: block;
                    padding-top: 0;
                }
                .sp-installer-body { padding: 20px; }
            }
        </style>
    </head>
    <body>
        <div class="sp-installer">
            <div class="sp-installer-header">
                <h1>SocietyPress</h1>
                <div class="step-title"><?php echo htmlspecialchars( $title ); ?></div>
            </div>
            <div class="sp-installer-body">
                <?php $content(); ?>
            </div>
        </div>
    </body>
    </html>
    <?php
}
