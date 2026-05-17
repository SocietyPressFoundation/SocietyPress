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
// EARLY PHP VERSION GUARD
// ============================================================================
// WHY: SocietyPress requires PHP 8.0+. The function bodies later in this file
// use PHP 8.0+ syntax (match, nullsafe, named args), which PHP 7.x rejects at
// parse time — meaning a truly old host produces a generic 500 with no useful
// message. This guard uses only PHP 5.x-compatible syntax so it can fire on
// any host that still successfully parses the surrounding code (e.g., partial
// parsers, opcache edge cases, or PHP variants with looser parsing). For hosts
// where the parser hard-rejects the whole file before reaching this line, the
// guard is harmless dead weight — but it costs nothing to leave in.
if ( PHP_VERSION_ID < 80000 ) {
    header( 'Content-Type: text/html; charset=utf-8' );
    echo '<!doctype html><meta charset="utf-8"><title>PHP Version Too Old</title>'
        . '<div style="font-family:system-ui,sans-serif;max-width:600px;margin:60px auto;padding:24px;border:1px solid #e5e7eb;border-radius:8px">'
        . '<h1 style="margin-top:0;color:#991B1B">PHP version too old</h1>'
        . '<p>SocietyPress requires PHP 8.0 or newer. This server is running PHP '
        . htmlspecialchars( PHP_VERSION, ENT_QUOTES, 'UTF-8' ) . '.</p>'
        . '<p>Most cPanel hosts let you change the PHP version under the "MultiPHP Manager" or "PHP Selector" tool. After upgrading to PHP 8.0+, reload this page.</p>'
        . '</div>';
    exit( 1 );
}

// ============================================================================
// SAFETY CHECKS
// ============================================================================

// Prevent direct CLI execution — this is a web-only tool
if ( php_sapi_name() === 'cli' ) {
    echo "This installer must be run from a web browser.\n";
    exit( 1 );
}

// WHY OR (not AND): Either file alone signals a partial or full WordPress
// install in this directory. A failed install can leave wp-config.php on
// disk without wp-includes/version.php (or vice versa) — using AND would
// let the installer overwrite a half-finished install, potentially
// destroying a wp-config.php with the only copy of the DB password.
if ( file_exists( __DIR__ . '/wp-config.php' ) || file_exists( __DIR__ . '/wp-includes/version.php' ) ) {
    sp_installer_die(
        'WordPress Is Already Installed',
        'A WordPress installation already exists in this directory (or a previous install left files behind). If you need to reinstall, '
        . 'remove the existing wp-config.php and wp-includes/ directory first. If SocietyPress is already running, you can safely '
        . 'delete this sp-installer.php file.'
    );
}

// ============================================================================
// CONFIGURATION
// ============================================================================

// Where to download WordPress and SocietyPress from
define( 'SP_INSTALLER_WP_URL',     'https://wordpress.org/latest.zip' );
define( 'SP_INSTALLER_BUNDLE_URL', 'https://getsocietypress.org/downloads/societypress-latest.zip' );
define( 'SP_INSTALLER_GITHUB_REPO', 'SocietyPressFoundation/SocietyPress' );
define( 'SP_INSTALLER_SALT_URL',    'https://api.wordpress.org/secret-key/1.1/salt/' );

// Demo mode: if a config file exists outside the web root, load it.
// WHY: On the demo site, DB credentials are pre-configured so visitors don't need
// to know them. The config file lives outside public_html so it's never web-accessible.
// When this file exists, the installer hides the DB fields and shows "Pre-configured."
// On a real install (no config file), the full DB form is shown.
//
// SECURITY: Earlier this loop walked $_SERVER['DOCUMENT_ROOT'], $_SERVER['HOME'],
// and $_SERVER['USER'] to build candidate paths. Those values can be influenced
// by request headers (Host:) or by misconfigured shared-hosting layouts, and the
// resolved path then went straight into require_once — i.e., attacker-influenced
// arbitrary file include. We now require the demo config to be at one fixed
// absolute path that has nothing to do with $_SERVER. If the file isn't there,
// demo mode is simply off.
$sp_demo_config = '/home/charle24/private/sp-demo-config.php';
if ( ! file_exists( $sp_demo_config ) ) {
    $sp_demo_config = '';
}
define( 'SP_INSTALLER_DEMO_MODE', $sp_demo_config !== '' );
if ( SP_INSTALLER_DEMO_MODE ) {
    require_once $sp_demo_config;
}

// Minimum requirements
define( 'SP_INSTALLER_MIN_PHP',     '8.0.0' );
define( 'SP_INSTALLER_MIN_MYSQL',   '5.7.0' );


// ============================================================================
// MAIN ROUTING
// ============================================================================

// WHY suppress display_errors here: shared hosts default php.ini often has
// display_errors=On, which would inject absolute server paths into the
// rendered HTML on a stray notice/warning. error_log still receives them.
@ini_set( 'display_errors', '0' );
@ini_set( 'log_errors',     '1' );
error_reporting( E_ALL );

// WHY harden session before start: PHP defaults vary by host. Force the
// session cookie to HttpOnly and use strict-mode so an attacker can't
// pre-set a session ID via shared-host sibling processes.
@ini_set( 'session.use_strict_mode', '1' );
@ini_set( 'session.cookie_httponly', '1' );
// WHY cookie_secure when on HTTPS: prevents the install-session cookie
// (which carries the CSRF nonce) from leaking over plaintext if the user
// hits the http:// URL or a downgrade attack forces it.
if ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' ) {
    @ini_set( 'session.cookie_secure', '1' );
}
// WHY SameSite=Strict: defense-in-depth against CSRF on the install POST.
// The nonce check is the primary guard, but Strict means the install-session
// cookie never accompanies cross-site requests at all.
@ini_set( 'session.cookie_samesite', 'Strict' );
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

    // Retrieve saved form data and errors from session (if returning from a failed attempt)
    $saved = $_SESSION['sp_form_data'] ?? [];
    $form_errors = $_SESSION['sp_form_errors'] ?? [];
    unset( $_SESSION['sp_form_errors'] );
    // Keep sp_form_data in session so it survives multiple error cycles

    sp_installer_render_page( 'Configure Your Site', function () use ( $nonce, $saved, $form_errors ) {
        ?>
        <?php if ( $form_errors ) : ?>
            <div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 8px; padding: 16px 20px; margin-bottom: 24px;">
                <?php foreach ( $form_errors as $err ) : ?>
                    <p style="color: #991B1B; margin: 0 0 4px;"><?php echo htmlspecialchars( $err ); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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
                                   value="<?php echo htmlspecialchars( $saved['db_name'] ?? '' ); ?>"
                                   placeholder="e.g., society_db" autocomplete="off">
                            <p class="desc">The name of the database you created for this site.</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="db_user">Database Username</label></th>
                        <td>
                            <input type="text" id="db_user" name="db_user" required
                                   value="<?php echo htmlspecialchars( $saved['db_user'] ?? '' ); ?>"
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
                            <input type="text" id="db_host" name="db_host" value="<?php echo htmlspecialchars( $saved['db_host'] ?? 'localhost' ); ?>">
                            <p class="desc">Almost always "localhost." Only change this if your host tells you to.</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="db_prefix">Table Prefix</label></th>
                        <td>
                            <input type="text" id="db_prefix" name="db_prefix" value="<?php echo htmlspecialchars( $saved['db_prefix'] ?? 'wp_' ); ?>"
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
                               value="<?php echo htmlspecialchars( $saved['site_title'] ?? '' ); ?>"
                               placeholder="e.g., Elm County Genealogical Society">
                        <p class="desc">This appears as your site title. You can change it later.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="admin_email">Admin Email</label></th>
                    <td>
                        <input type="email" id="admin_email" name="admin_email" required
                               value="<?php echo htmlspecialchars( $saved['admin_email'] ?? '' ); ?>"
                               placeholder="you@example.com">
                        <p class="desc">Used for admin login and system notifications.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="admin_first">Your First Name</label></th>
                    <td>
                        <input type="text" id="admin_first" name="admin_first" required
                               value="<?php echo htmlspecialchars( $saved['admin_first'] ?? '' ); ?>"
                               placeholder="e.g., Harold">
                    </td>
                </tr>
                <tr>
                    <th><label for="admin_last">Your Last Name</label></th>
                    <td>
                        <input type="text" id="admin_last" name="admin_last" required
                               value="<?php echo htmlspecialchars( $saved['admin_last'] ?? '' ); ?>"
                               placeholder="e.g., Whitfield">
                    </td>
                </tr>
                <tr>
                    <th><label for="admin_user">Admin Username</label></th>
                    <td>
                        <input type="text" id="admin_user" name="admin_user" required
                               value="<?php echo htmlspecialchars( $saved['admin_user'] ?? '' ); ?>" pattern="[a-zA-Z0-9_\-\.]{3,60}"
                               placeholder="e.g. jsmith or harold.whitfield">
                        <p class="desc">Your login username. Choose something unique and memorable.</p>
                        <p id="sp-admin-warn" style="display:none; color: #DC2626; font-size: 13px; margin-top: 4px;">
                            &#9888; Avoid common usernames like &ldquo;admin&rdquo; &mdash; they're the first thing attackers try.
                        </p>
                        <script>
                        document.getElementById('admin_user').addEventListener('input', function() {
                            var bad = ['admin', 'administrator', 'root', 'superadmin', 'user', 'test'];
                            var warn = document.getElementById('sp-admin-warn');
                            warn.style.display = bad.indexOf(this.value.toLowerCase().trim()) !== -1 ? '' : 'none';
                        });
                        </script>
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

            <!-- SocietyPress Settings -->
            <h2 style="font-size: 18px; font-weight: 700; color: #0D1F3C; margin: 32px 0 16px; padding-bottom: 8px; border-bottom: 2px solid #C9973A;">
                Society Details
            </h2>
            <p style="color: #6B7280; font-size: 13px; margin-bottom: 16px;">
                These are used on your public website and in emails to members.
                Everything here can be changed later.
            </p>

            <table class="sp-form-table">
                <tr>
                    <th><label for="org_address">Mailing Address</label></th>
                    <td>
                        <textarea id="org_address" name="org_address" rows="3"
                                  placeholder="Optional — P.O. Box or society address"><?php echo htmlspecialchars( $saved['org_address'] ?? '' ); ?></textarea>
                        <p class="desc">Your society's public address, not your personal home address. Leave blank if your society doesn't publish one.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="org_phone">Phone Number</label></th>
                    <td>
                        <input type="tel" id="org_phone" name="org_phone"
                               value="<?php echo htmlspecialchars( $saved['org_phone'] ?? '' ); ?>"
                               placeholder="Optional">
                        <p class="desc">Leave blank if your society doesn't have a public phone number.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="org_email">Contact Email</label></th>
                    <td>
                        <input type="email" id="org_email" name="org_email"
                               value="<?php echo htmlspecialchars( $saved['org_email'] ?? '' ); ?>"
                               placeholder="Optional — e.g., info@yoursociety.org">
                        <p class="desc">A public email for your society (not your personal email). Leave blank to set later.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="membership_period">Membership Period</label></th>
                    <td>
                        <?php $mp = $saved['membership_period'] ?? 'annual'; ?>
                        <select id="membership_period" name="membership_period">
                            <option value="annual" <?php echo $mp === 'annual' ? 'selected' : ''; ?>>Annual (fixed year)</option>
                            <option value="rolling" <?php echo $mp === 'rolling' ? 'selected' : ''; ?>>Rolling (12 months from join date)</option>
                            <option value="lifetime" <?php echo $mp === 'lifetime' ? 'selected' : ''; ?>>Lifetime only</option>
                        </select>
                        <p class="desc">Most societies use annual memberships with a fixed fiscal year.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="membership_start_month">Fiscal Year Starts</label></th>
                    <td>
                        <?php $ms = (int) ( $saved['membership_start'] ?? 7 ); ?>
                        <select id="membership_start_month" name="membership_start_month">
                            <?php
                            $months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',
                                       7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
                            foreach ( $months as $num => $name ) {
                                $sel = $num === $ms ? ' selected' : '';
                                echo "<option value=\"{$num}\"{$sel}>{$name}</option>";
                            }
                            ?>
                        </select>
                        <p class="desc">When does your membership year begin? Many societies use July.</p>
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
    $admin_first = trim( $_POST['admin_first'] ?? '' );
    $admin_last  = trim( $_POST['admin_last'] ?? '' );
    $admin_user  = trim( $_POST['admin_user'] ?? '' );
    $admin_pass  = $_POST['admin_pass'] ?? '';
    $admin_pass2 = $_POST['admin_pass2'] ?? '';

    // SocietyPress settings collected in the installer form
    $org_email          = trim( $_POST['org_email'] ?? '' );
    $org_address        = trim( $_POST['org_address'] ?? '' );
    $org_phone          = trim( $_POST['org_phone'] ?? '' );
    $membership_period  = trim( $_POST['membership_period'] ?? 'annual' );
    $membership_start   = (int) ( $_POST['membership_start_month'] ?? 7 );

    // WHY: Save all form values to the session so they survive errors.
    // If the DB connection fails or anything else goes wrong, Harold gets
    // sent back to the form with all his data still filled in. Nobody
    // should have to retype 12 fields because of a wrong DB password.
    // WHY no passwords: session files on shared hosting (/tmp/sess_*) can be
    // readable by other PHP processes on the same server. Repopulating a
    // password field is a convenience; a leaked DB or admin password is a
    // compromise. Both db_pass and admin_pass are deliberately omitted.
    $_SESSION['sp_form_data'] = [
        'db_name'   => $db_name,
        'db_user'   => $db_user,
        'db_host'   => $db_host,
        'db_prefix' => $db_prefix,
        'site_title'  => $site_title,
        'admin_email' => $admin_email,
        'admin_first' => $admin_first,
        'admin_last'  => $admin_last,
        'admin_user'  => $admin_user,
        'org_email'          => $org_email,
        'org_address'        => $org_address,
        'org_phone'          => $org_phone,
        'membership_period'  => $membership_period,
        'membership_start'   => $membership_start,
        // Passwords (db_pass, admin_pass) deliberately NOT saved to session
    ];

    // Validate
    $errors = [];
    if ( empty( $db_name ) )    $errors[] = 'Database name is required.';
    if ( empty( $db_user ) )    $errors[] = 'Database username is required.';
    if ( empty( $site_title ) ) $errors[] = 'Society name is required.';
    if ( empty( $admin_email ) || ! filter_var( $admin_email, FILTER_VALIDATE_EMAIL ) ) {
        $errors[] = 'A valid admin email address is required.';
    }
    if ( empty( $admin_first ) ) $errors[] = 'First name is required.';
    if ( empty( $admin_last ) )  $errors[] = 'Last name is required.';
    // WHY explicit pattern: the HTML pattern attribute is client-side only and
    // can be bypassed by anyone POSTing directly. The username flows into a
    // var_export() call that becomes part of a generated PHP file; while
    // var_export produces correct PHP literals (so it is not directly
    // injectable), defense-in-depth says we restrict to a known-safe set.
    if ( empty( $admin_user ) || ! preg_match( '/^[a-zA-Z0-9_\-.]{3,60}$/', $admin_user ) ) {
        $errors[] = 'Admin username must be 3–60 characters using only letters, numbers, underscore, hyphen, or period.';
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
    // WHY allowlist: $membership_period gets written into societypress_settings
    // and is rendered unescaped in some admin contexts. The HTML <select> only
    // exposes three values, but a direct POST can send anything. Pin to the
    // allowlist server-side; fall back to 'annual' for any unexpected value.
    if ( ! in_array( $membership_period, [ 'annual', 'rolling', 'lifetime' ], true ) ) {
        $membership_period = 'annual';
    }
    // WHY db_host pattern: this value flows into wp-config.php via a regex
    // replacement below. A value containing newlines, quotes, or PHP tokens
    // could escape the constant string and inject code. Restrict to the
    // characters legal in a hostname / hostname:port / unix-socket path.
    if ( $db_host === '' ) {
        $db_host = 'localhost';
    } elseif ( ! preg_match( '/^[a-zA-Z0-9._\-:\/]+$/', $db_host ) ) {
        $errors[] = 'Database host contains invalid characters. Use a hostname like "localhost" or "db.example.com" (port and unix-socket paths are allowed).';
    }
    if ( $errors ) {
        $_SESSION['sp_form_errors'] = $errors;
        header( 'Location: ?step=configure' );
        exit;
    }

    // Ensure prefix ends with underscore
    if ( substr( $db_prefix, -1 ) !== '_' ) {
        $db_prefix .= '_';
    }

    $install_dir = __DIR__;
    $log = [];

    // ---- 1. Test database connection ----
    // WHY try/catch: PHP 8.1+ throws mysqli_sql_exception on connection failure
    //      instead of returning an error code. The @ suppression operator doesn't
    //      catch exceptions, so without this try/catch the installer shows a raw
    //      fatal error instead of a friendly message.
    $log[] = 'Testing database connection...';
    $db_error = '';
    try {
        $conn = new mysqli( $db_host, $db_user, $db_pass, $db_name );
        if ( $conn->connect_error ) {
            $db_error = $conn->connect_error;
        } else {
            $conn->close();
        }
    } catch ( mysqli_sql_exception $e ) {
        $db_error = $e->getMessage();
    }
    if ( $db_error ) {
        // Redirect back to the form with a generic error — the raw mysqli
        // message contains the username, hostname, and database name and
        // confirms valid users to anyone probing an exposed installer.
        // The detail still goes to error_log for the admin.
        @error_log( 'SocietyPress installer: database connection failed: ' . $db_error );
        $_SESSION['sp_form_errors'] = [
            'Could not connect to the database. Check your credentials and try again.',
            'If you are sure the credentials are right, ask your hosting provider whether the database is reachable.',
        ];
        header( 'Location: ?step=configure' );
        exit;
    }
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
    // the contents up to the install directory.
    //
    // SECURITY: We do NOT use $zip->extractTo() because that performs no path
    // validation. A ZIP entry named "../../etc/cron.d/evil" would escape the
    // install directory and write anywhere the web user can reach. The
    // wordpress.org ZIP is trusted, but trust is not a control — a MITM, a
    // compromised mirror, or a corrupt download could deliver a crafted ZIP.
    // We iterate entries manually and reject any whose normalized path falls
    // outside the install directory.
    $install_dir_real = realpath( $install_dir );
    if ( $install_dir_real === false ) {
        $zip->close();
        sp_installer_die( 'Extract Failed', 'Install directory could not be resolved.' );
    }
    for ( $i = 0; $i < $zip->numFiles; $i++ ) {
        $entry_name = $zip->getNameIndex( $i );
        if ( $entry_name === false || $entry_name === '' ) {
            continue;
        }
        // Reject any entry containing path-traversal sequences or null bytes,
        // and reject absolute paths (Windows drive letters too).
        if ( strpos( $entry_name, "\0" ) !== false
             || strpos( $entry_name, '..' ) !== false
             || $entry_name[0] === '/'
             || preg_match( '/^[A-Za-z]:/', $entry_name ) ) {
            $zip->close();
            sp_installer_die( 'Extract Failed', 'WordPress archive contains an unsafe entry path. Aborting for safety.' );
        }
        $target  = $install_dir_real . DIRECTORY_SEPARATOR . $entry_name;
        $is_dir  = ( substr( $entry_name, -1 ) === '/' );
        if ( $is_dir ) {
            if ( ! is_dir( $target ) ) {
                @mkdir( $target, 0755, true );
            }
            continue;
        }
        $parent = dirname( $target );
        if ( ! is_dir( $parent ) ) {
            @mkdir( $parent, 0755, true );
        }
        // Final containment check: the resolved parent directory must be the
        // install dir or beneath it. realpath() returns false if the path
        // doesn't exist, but mkdir above guarantees it does at this point.
        // WHY trailing DIRECTORY_SEPARATOR: a bare strpos prefix-match would
        // accept '/var/www/htmlevil' as inside '/var/www/html'. The trailing
        // separator forces a directory-boundary match.
        $parent_real = realpath( $parent );
        $needle      = rtrim( $install_dir_real, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
        $haystack    = rtrim( $parent_real, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
        if ( $parent_real === false || strpos( $haystack, $needle ) !== 0 ) {
            $zip->close();
            sp_installer_die( 'Extract Failed', 'WordPress archive entry resolved outside the install directory. Aborting for safety.' );
        }
        $stream = $zip->getStream( $entry_name );
        if ( $stream === false ) {
            continue;
        }
        $out = fopen( $target, 'wb' );
        if ( $out !== false ) {
            stream_copy_to_stream( $stream, $out );
            fclose( $out );
        }
        if ( is_resource( $stream ) ) {
            fclose( $stream );
        }
    }
    $zip->close();
    @unlink( $wp_zip_path );

    // Move files from wordpress/ subdirectory to install root.
    // SECURITY: Source paths come from our own filesystem walk after the
    // safe extraction above, so path traversal is not a concern here, but
    // we still verify each target is inside $install_dir as belt-and-braces.
    $wp_subdir = $install_dir . '/wordpress';
    if ( is_dir( $wp_subdir ) ) {
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator( $wp_subdir, RecursiveDirectoryIterator::SKIP_DOTS ),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ( $items as $item ) {
            $relative = substr( $item->getPathname(), strlen( $wp_subdir ) + 1 );
            if ( strpos( $relative, '..' ) !== false ) {
                continue; // belt-and-braces; should never trigger after safe extract
            }
            $target = $install_dir . '/' . $relative;
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

    // Replace database constants.
    // WHY preg_replace per constant (not str_replace): wp-config-sample.php
    // contains the literal "localhost" in inline comments AND in the DB_HOST
    // line. A naive str_replace( "localhost", $db_host, $config ) would also
    // overwrite every comment containing the word — and worse, the user's
    // db_host value would land verbatim in those positions, including any
    // quotes or PHP tokens it contained. Targeting only the constant's
    // single-quoted string and addslashes()-escaping the user value
    // contains both problems.
    // WHY preg_replace_callback (not preg_replace): a literal `$1` or `${1}`
    // in the user's value (cPanel-style auto-generated credentials sometimes
    // include literal dollar signs followed by digits) would be parsed as
    // a backreference inside a preg_replace replacement string and splice
    // the captured group back in place. addslashes() doesn't touch `$`.
    // The callback form treats the replacement as a plain string.
    $sp_set_config_value = function ( string $constant, string $value, string $config ): string {
        $escaped = addslashes( $value ); // safe for single-quoted PHP literal
        $pattern = "/(define\(\s*'" . preg_quote( $constant, '/' ) . "'\s*,\s*')[^']*('\s*\)\s*;)/";
        return preg_replace_callback( $pattern, function ( $m ) use ( $escaped ) {
            return $m[1] . $escaped . $m[2];
        }, $config, 1 );
    };
    $config = $sp_set_config_value( 'DB_NAME',     $db_name, $config );
    $config = $sp_set_config_value( 'DB_USER',     $db_user, $config );
    $config = $sp_set_config_value( 'DB_PASSWORD', $db_pass, $config );
    $config = $sp_set_config_value( 'DB_HOST',     $db_host, $config );
    $sp_prefix_safe = $db_prefix; // already validated as [a-zA-Z0-9_] earlier
    $config = preg_replace_callback(
        '/\$table_prefix\s*=\s*\'wp_\'/',
        function () use ( $sp_prefix_safe ) { return "\$table_prefix = '{$sp_prefix_safe}'"; },
        $config
    );

    // Replace salt block — use a callback so any `$1`-style sequence in the
    // upstream WP salt response (or a MITM-injected one) is treated as a
    // literal, not as a backreference.
    $sp_salts_block = trim( $salts ) . "\n\n";
    $config = preg_replace_callback(
        '/define\(\s*\'AUTH_KEY\'.*?define\(\s*\'NONCE_SALT\'[^;]*;\s*/s',
        function () use ( $sp_salts_block ) { return $sp_salts_block; },
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

    // ---- 6. Prepare for WordPress installation ----
    // WHY: We can't bootstrap wp-settings.php on this host (proc_open disabled,
    // and WordPress's DB error handler calls die() which can't be caught). Instead,
    // we let WordPress install itself via its own /wp-admin/install.php, and we
    // plant a must-use plugin that auto-activates SocietyPress + theme on first load.
    $log[] = 'Preparing WordPress installation...';

    // Create the mu-plugin that fires once after WP installs
    $mu_dir = $install_dir . '/wp-content/mu-plugins';
    @mkdir( $mu_dir, 0755, true );

    $mu_plugin = <<<'MUPLUGIN'
<?php
/**
 * SocietyPress Auto-Activator (must-use plugin)
 *
 * Two jobs, in two separate requests:
 *
 * 1. login_init hook — Auto-login. The bridge script stores a one-time
 *    transient in the DB containing the admin user ID plus a random 256-bit
 *    secret, then redirects the browser to wp-login.php with that secret in
 *    the sp_token query parameter. This hook reads the transient, verifies
 *    the secret matches sp_token via hash_equals (constant-time), deletes
 *    the transient, sets the auth cookie, and redirects to wp-admin.
 *
 *    WHY a secret, not just a file: without the secret, any request to
 *    wp-login.php between bridge-completion and legitimate first visit
 *    would trigger auto-login as admin. The secret is 256 bits of random
 *    and is only ever in the redirect URL the bridge just sent, so only
 *    the user whose browser followed that redirect can log in.
 *
 *    WHY a transient, not a file: the token file would sit in wp-content,
 *    which is web-accessible on most hosts. A crafted GET to the token
 *    filename could leak the secret. Transients live in the DB and are
 *    not reachable over HTTP.
 *
 * 2. admin_init hook — Activate SocietyPress + parent theme, apply installer
 *    settings, clean up default WordPress cruft, and self-destruct.
 */

// ---- Auto-login on first hit to wp-login.php ----
add_action( 'login_init', function () {
    $data = get_transient( 'sp_auto_login' );
    if ( ! is_array( $data ) ) {
        return;
    }

    $user_id = (int) ( $data['user_id'] ?? 0 );
    $secret  = (string) ( $data['secret']  ?? '' );
    $provided = (string) ( $_GET['sp_token'] ?? '' );

    if ( $user_id < 1 || $secret === '' || $provided === '' ) {
        // Leave the transient in place so the legitimate click (which carries
        // the correct token) can still consume it. The transient expires on
        // its own after a few minutes.
        return;
    }

    if ( ! hash_equals( $secret, $provided ) ) {
        // Mismatch — probably a crawler or scanner. Don't burn the transient.
        return;
    }

    // Secret matched. Single-use: delete before authenticating.
    delete_transient( 'sp_auto_login' );

    // Set the auth cookie in a normal WordPress context where COOKIEPATH,
    // COOKIE_DOMAIN, and session token infrastructure are all correct.
    wp_set_auth_cookie( $user_id, true );

    // Redirect to wp-admin — the admin_init hook below will handle the rest.
    wp_safe_redirect( admin_url() );
    exit;
} );

// Recursive directory delete for removing default themes.
// WHY: PHP has no built-in rmdir-recursive, and we can't rely on the
// installer's sp_installer_rmdir() since this mu-plugin runs in a
// separate request after WordPress is fully installed.
function sp_installer_mu_rmdir( string $dir ): void {
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

add_action( 'admin_init', function () {
    // Only run once — if SocietyPress is already active, bail
    $active = get_option( 'active_plugins', [] );
    if ( in_array( 'societypress/societypress.php', $active, true ) ) {
        // Clean up: delete this mu-plugin
        @unlink( __FILE__ );
        return;
    }

    // ---- Cleanup FIRST: remove WordPress default cruft ----
    // WHY: This MUST happen before SocietyPress activation. The plugin's
    // activation hook runs sp_maybe_create_default_pages(), which only
    // creates a Home page if zero published pages exist. WordPress ships
    // with a "Sample Page" that counts as a published page — if we don't
    // delete it first, the activation hook sees it and skips homepage
    // creation, leaving show_on_front stuck on "posts."

    // Delete "Hello world!" post, "Sample Page", and default comment
    $hello_post = get_page_by_path( 'hello-world', OBJECT, 'post' );
    if ( $hello_post ) { wp_delete_post( $hello_post->ID, true ); }

    $sample_page = get_page_by_path( 'sample-page' );
    if ( $sample_page ) { wp_delete_post( $sample_page->ID, true ); }

    $default_comments = get_comments( [ 'number' => 100 ] );
    foreach ( $default_comments as $c ) {
        wp_delete_comment( $c->comment_ID, true );
    }

    // Delete Hello Dolly — both the single-file and directory variants
    $hello_file = WP_PLUGIN_DIR . '/hello.php';
    if ( file_exists( $hello_file ) ) { @unlink( $hello_file ); }
    $hello_dir = WP_PLUGIN_DIR . '/hello-dolly';
    if ( is_dir( $hello_dir ) ) { sp_installer_mu_rmdir( $hello_dir ); }

    // Delete default Twenty* themes — SocietyPress is the only theme needed
    $default_themes = [ 'twentytwentythree', 'twentytwentyfour', 'twentytwentyfive' ];
    foreach ( $default_themes as $slug ) {
        $theme_dir = get_theme_root() . '/' . $slug;
        if ( is_dir( $theme_dir ) ) {
            sp_installer_mu_rmdir( $theme_dir );
        }
    }

    // ---- NOW activate SocietyPress ----
    // WHY activate_plugin() instead of update_option('active_plugins'):
    // Manually adding to the active_plugins array skips register_activation_hook.
    // That means sp_maybe_create_default_pages(), sp_create_tables(), and all
    // the other activation setup never runs. activate_plugin() loads the plugin
    // file, fires the activation hook, and does everything properly.
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    activate_plugin( 'societypress/societypress.php' );

    // Activate parent theme
    switch_theme( 'societypress' );

    // Set permalinks
    global $wp_rewrite;
    $wp_rewrite->set_permalink_structure( '/%postname%/' );
    $wp_rewrite->flush_rules( true );

    // ---- Apply installer-collected SocietyPress settings ----
    // WHY: The installer form collects org details and membership config so
    // Harold doesn't have to re-enter them in a separate setup wizard. The
    // installer writes these to a JSON file with a randomized filename
    // (sp-installer-config-<32hex>.json) that we glob for here, read,
    // merge into the SP settings, and delete.
    $config_file = '';
    $candidates  = glob( ABSPATH . 'sp-installer-config-*.json' );
    if ( $candidates ) {
        // Most recent wins if more than one exists (shouldn't, but defend).
        usort( $candidates, function ( $a, $b ) { return filemtime( $b ) <=> filemtime( $a ); } );
        $config_file = $candidates[0];
    }
    if ( $config_file && file_exists( $config_file ) ) {
        $installer_config = json_decode( file_get_contents( $config_file ), true );
        if ( is_array( $installer_config ) ) {
            $sp_settings = get_option( 'societypress_settings', [] );
            // Map installer fields to SP settings keys
            $sp_settings['organization_name']    = $installer_config['organization_name'] ?? $sp_settings['organization_name'] ?? '';
            $sp_settings['organization_email']   = $installer_config['organization_email'] ?? $sp_settings['organization_email'] ?? '';
            $sp_settings['organization_address'] = $installer_config['organization_address'] ?? '';
            $sp_settings['organization_phone']   = $installer_config['organization_phone'] ?? '';
            $sp_settings['membership_period_type'] = $installer_config['membership_period_type'] ?? 'annual';
            $sp_settings['membership_start_month'] = (int) ( $installer_config['membership_start_month'] ?? 7 );
            // Email from defaults to org name + email
            $sp_settings['email_from_name']  = $sp_settings['email_from_name'] ?: $sp_settings['organization_name'];
            $sp_settings['email_from_email'] = $sp_settings['email_from_email'] ?: $sp_settings['organization_email'];

            // Video hero defaults — the theme ships with a cinematic background
            // video that plays behind the society name on the home page. This is
            // the "blow their minds" first impression after install.
            $theme_url = get_template_directory_uri();
            if ( empty( $sp_settings['homepage_hero_type'] ) ) {
                $sp_settings['homepage_hero_type']     = 'video';
                $sp_settings['homepage_hero_media']    = $theme_url . '/assets/hero-background.mp4';
                $sp_settings['homepage_hero_poster']   = $theme_url . '/assets/hero-background-poster.jpg';
                $sp_settings['homepage_hero_headline'] = '';
                $sp_settings['homepage_hero_subtitle'] = 'Preserving Our Past. Connecting Our Present.';
                $sp_settings['homepage_hero_cta_text'] = 'Upcoming Events';
                $sp_settings['homepage_hero_cta_url']  = '/events/';
                $sp_settings['homepage_hero_overlay']  = 35;
                $sp_settings['homepage_hero_height']   = 'fullscreen';
            }

            update_option( 'societypress_settings', $sp_settings );

            // WHY: Don't mark wizard complete here — the wizard now handles
            //      branding uploads, package selection, and nav menu setup.
            //      The installer only covers org info and membership basics.
        }
        @unlink( $config_file );
    }

    // ---- Create a member record + set WP profile for the admin user ----
    // WHY: The person installing SocietyPress is almost always a member.
    // The installer collected their first/last name. We save it to the WP
    // user profile (so WordPress knows their name) and create an SP member
    // record (so the header shows their name instead of "Log In").
    $admin_user = wp_get_current_user();
    if ( $admin_user && $admin_user->ID && is_array( $installer_config ) ) {
        $first = $installer_config['admin_first_name'] ?? '';
        $last  = $installer_config['admin_last_name'] ?? '';

        // Save to WordPress user profile
        if ( $first ) update_user_meta( $admin_user->ID, 'first_name', $first );
        if ( $last )  update_user_meta( $admin_user->ID, 'last_name', $last );
        if ( $first || $last ) {
            wp_update_user( [
                'ID'           => $admin_user->ID,
                'display_name' => trim( $first . ' ' . $last ),
            ] );
        }

        // Create SP member record
        global $wpdb;
        $members_table = $wpdb->prefix . 'sp_members';
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT user_id FROM {$members_table} WHERE user_id = %d",
            $admin_user->ID
        ) );
        if ( ! $exists ) {
            if ( empty( $first ) ) $first = $admin_user->user_login;
            $wpdb->insert( $members_table, [
                'user_id'    => $admin_user->ID,
                'first_name' => $first,
                'last_name'  => $last,
                'status'     => 'active',
                'join_date'  => current_time( 'Y-m-d' ),
            ] );
        }
    }

    // Self-destruct
    @unlink( __FILE__ );

    // Redirect to the setup wizard — Harold still needs to upload branding,
    // choose packages, and set up navigation.
    wp_safe_redirect( admin_url( 'admin.php?page=sp-setup-wizard' ) );
    exit;
}, 1 );
MUPLUGIN;

    file_put_contents( $mu_dir . '/sp-auto-activate.php', $mu_plugin );
    $log[] = 'Auto-activator planted.';

    // ---- 6b. Write installer config for the mu-plugin to pick up ----
    // WHY: The mu-plugin runs in a separate request where $_POST is gone.
    // We write the SP settings to a JSON file that the mu-plugin reads,
    // applies to the societypress_settings option, and deletes. This is
    // how the installer-collected org details reach the plugin without
    // a second wizard step.
    //
    // WHY randomized filename: the previous fixed name `sp-installer-
    // config.json` was web-readable for the few seconds between write and
    // mu-plugin pickup. The file holds admin name/email/phone/address
    // (no passwords). Randomizing the filename closes blind URL probing
    // — the mu-plugin globs for the unique pattern.
    $installer_config = [
        'organization_name'    => $site_title,
        'organization_email'   => $org_email ?: '',
        'organization_address' => $org_address,
        'organization_phone'   => $org_phone,
        'membership_period_type' => $membership_period,
        'membership_start_month' => $membership_start,
        'admin_first_name'     => $admin_first,
        'admin_last_name'      => $admin_last,
    ];
    $config_filename = 'sp-installer-config-' . bin2hex( random_bytes( 16 ) ) . '.json';
    file_put_contents( $install_dir . '/' . $config_filename, json_encode( $installer_config ) );
    $log[] = 'Installer config written.';

    // ---- 7. Create a bridge script that runs WordPress's install with our data ----
    // WHY: We already collected the society name, admin email, username, and password
    // in our form. Redirecting to wp-admin/install.php makes the user enter it all
    // again — terrible UX. Instead, we create a temporary PHP script that loads
    // WordPress and runs wp_install() with the values we already have. The user
    // never sees WordPress's install screen.
    $log[] = 'Creating install bridge...';

    // Generate a one-time secret token for this bridge invocation.
    // WHY: The bridge script is publicly reachable for the brief window between
    // it being written and the browser hitting it. A random token in the URL
    // ensures only the user whose browser was redirected here can trigger it —
    // a crawler or scanner that hits /sp-bridge-install.php without the token
    // gets a 403 instead of running wp_install().
    $bridge_token = bin2hex( random_bytes( 16 ) );

    $bridge_script = $install_dir . '/sp-bridge-install.php';
    $bridge_code = '<?php' . "\n"
        . '/**' . "\n"
        . ' * Temporary bridge script — runs wp_install() with pre-collected data,' . "\n"
        . ' * sets up permalinks, and self-destructs. The user\'s browser is redirected' . "\n"
        . ' * here by the SocietyPress installer so WordPress installs in a normal' . "\n"
        . ' * HTTP request context (avoiding bootstrap issues).' . "\n"
        . ' */' . "\n"
        . "\n"
        . '// Token guard — only the redirect from the SocietyPress installer carries' . "\n"
        . '// the correct token. Anything else (crawlers, scanners, direct visits) gets' . "\n"
        . '// a 403 before WordPress is loaded or wp_install() is touched.' . "\n"
        . 'if ( ! isset( $_GET[\'token\'] ) || $_GET[\'token\'] !== ' . var_export( $bridge_token, true ) . ' ) {' . "\n"
        . '    http_response_code( 403 );' . "\n"
        . '    die( \'Unauthorized\' );' . "\n"
        . '}' . "\n"
        . "\n"
        . 'define( "WP_INSTALLING", true );' . "\n"
        . 'require_once __DIR__ . "/wp-load.php";' . "\n"
        . 'require_once ABSPATH . "wp-admin/includes/upgrade.php";' . "\n"
        . "\n"
        . '// var_export() produces valid PHP string literals with proper escaping of quotes' . "\n"
        . '// and special characters, making it safe for embedding user input into generated PHP.' . "\n"
        . '// This is intentional — do not replace with string concatenation.' . "\n"
        . '// Run the WordPress installation with data from the SocietyPress installer' . "\n"
        . '$result = wp_install(' . "\n"
        . '    ' . var_export( $site_title, true ) . ',' . "\n"
        . '    ' . var_export( $admin_user, true ) . ',' . "\n"
        . '    ' . var_export( $admin_email, true ) . ',' . "\n"
        . '    true,' . "\n"
        . '    "",' . "\n"
        . '    ' . var_export( $admin_pass, true ) . "\n"
        . ');' . "\n"
        . "\n"
        . 'if ( is_wp_error( $result ) ) {' . "\n"
        . '    wp_die( "Installation failed: " . $result->get_error_message() );' . "\n"
        . '}' . "\n"
        . "\n"
        . '// Write a one-time auto-login transient for the mu-plugin to pick up.' . "\n"
        . '// WHY: wp_set_auth_cookie() does not work reliably during WP_INSTALLING' . "\n"
        . '// because cookie constants (COOKIEPATH, COOKIE_DOMAIN) are derived from' . "\n"
        . '// the siteurl option, which is set AFTER wp_install() runs but the' . "\n"
        . '// constants were already defined (with wrong values) during bootstrap.' . "\n"
        . '// Instead, we hand a (user_id, secret) pair to the mu-plugin which runs' . "\n"
        . '// in a normal WordPress context where everything is properly initialized.' . "\n"
        . '// WHY a secret: without one, any hit to wp-login.php between now and the' . "\n"
        . '// legitimate browser following the redirect below would trigger auto-login' . "\n"
        . '// as admin. The secret is 256 bits of random — only carried in the URL we' . "\n"
        . '// are about to redirect to — so only that one request can consume the token.' . "\n"
        . '// WHY a transient (not a file): a token file in wp-content is web-reachable' . "\n"
        . '// on most hosts. Transients live in the DB and cannot be read over HTTP.' . "\n"
        . '$sp_token_secret = bin2hex( random_bytes( 32 ) );' . "\n"
        . 'set_transient( "sp_auto_login", array(' . "\n"
        . '    "user_id" => (int) $result["user_id"],' . "\n"
        . '    "secret"  => $sp_token_secret,' . "\n"
        . '), 5 * MINUTE_IN_SECONDS );' . "\n"
        . "\n"
        . '// Self-destruct — remove bridge script and main installer' . "\n"
        . '@unlink( __FILE__ );' . "\n"
        . '$installer = dirname( __FILE__ ) . "/sp-installer.php";' . "\n"
        . 'if ( file_exists( $installer ) ) { @unlink( $installer ); }' . "\n"
        . '// WHY: leftover .htaccess.sp-bak would otherwise sit web-readable' . "\n"
        . '// on Apache hosts forever, exposing the prior rewrite config.' . "\n"
        . '$htaccess_bak = dirname( __FILE__ ) . "/.htaccess.sp-bak";' . "\n"
        . 'if ( file_exists( $htaccess_bak ) ) { @unlink( $htaccess_bak ); }' . "\n"
        . "\n"
        . '// Redirect directly to wp-login.php carrying the secret. The mu-plugin\'s' . "\n"
        . '// login_init hook reads the transient, verifies sp_token matches the stored' . "\n"
        . '// secret, sets the auth cookie, and redirects to wp-admin.' . "\n"
        . 'header( "Location: " . rtrim( dirname( $_SERVER["SCRIPT_NAME"] ), "/" ) . "/wp-login.php?sp_token=" . $sp_token_secret );' . "\n"
        . 'exit;' . "\n";

    file_put_contents( $bridge_script, $bridge_code );
    $log[] = 'Bridge script created.';

    // Remove our .htaccess so the bridge script is reachable via HTTP
    $our_htaccess = $install_dir . '/.htaccess';
    $htaccess_bak = $install_dir . '/.htaccess.sp-bak';
    if ( file_exists( $our_htaccess ) ) {
        rename( $our_htaccess, $htaccess_bak );
    }

    // ---- 8. Download and install SocietyPress ----
    // WHY: We extract SocietyPress BEFORE redirecting to the WordPress installer
    // bridge script. The mu-plugin needs the plugin and theme files to already
    // be in place when it fires on first admin page load.
    // WHY: We try our own bundle URL first (hosted on getsocietypress.org) because
    // it's a direct download with no redirects. GitHub's archive URLs require redirect
    // following and sometimes fail on locked-down shared hosts. The bundle contains
    // the plugin + parent theme + all child themes in a simple flat structure.
    $log[] = 'Downloading SocietyPress...';

    $sp_zip_path = $install_dir . '/societypress-latest.zip';
    $sp_downloaded = false;

    // Try 1: Bundle ZIP in the same directory as this installer
    // WHY: The most reliable approach. If the admin uploaded societypress-bundle.zip
    // alongside sp-installer.php, we just use it. No HTTP, no path guessing.
    $local_bundle = $install_dir . '/societypress-bundle.zip';
    if ( file_exists( $local_bundle ) ) {
        $sp_downloaded = copy( $local_bundle, $sp_zip_path );
        if ( $sp_downloaded ) {
            $log[] = 'SocietyPress bundle found alongside installer.';
        }
    }

    // Try 2: Download from our server via HTTP
    if ( ! $sp_downloaded ) {
        $log[] = 'Local copy not found, trying HTTP download...';
        $sp_downloaded = sp_installer_download( SP_INSTALLER_BUNDLE_URL, $sp_zip_path );
    }

    // Try 3: GitHub fallback
    if ( ! $sp_downloaded ) {
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
    if ( ! isset( $log[ count( $log ) - 1 ] ) || strpos( $log[ count( $log ) - 1 ], 'copied' ) === false ) {
        $log[] = 'SocietyPress downloaded.';
    }

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

    // SECURITY: Both extraction paths build $target by concatenating user-
    // controllable ZIP entry names onto known install paths. A crafted entry
    // like "../../../etc/cron.d/evil" would escape the wp-content directory.
    // This helper validates every write: the entry name must be free of path
    // traversal sequences, and the resolved parent directory must live inside
    // wp-content. We close the zip and abort on any violation rather than
    // silently skip — a corrupt or malicious bundle is not safe to recover from.
    $wp_content_real = realpath( $install_dir . '/wp-content' );
    if ( $wp_content_real === false ) {
        @mkdir( $install_dir . '/wp-content', 0755, true );
        $wp_content_real = realpath( $install_dir . '/wp-content' );
    }
    $sp_safe_extract = function ( string $entry_name, string $target, ZipArchive $zip, int $index ) use ( $wp_content_real, $sp_zip_path ) {
        if ( strpos( $entry_name, "\0" ) !== false
             || strpos( $entry_name, '..' ) !== false
             || ( $entry_name !== '' && $entry_name[0] === '/' )
             || preg_match( '/^[A-Za-z]:/', $entry_name ) ) {
            $zip->close();
            @unlink( $sp_zip_path );
            sp_installer_die( 'Extract Failed', 'SocietyPress archive contains an unsafe entry path. Aborting for safety.' );
        }
        $is_dir = ( substr( $entry_name, -1 ) === '/' );
        $parent = $is_dir ? $target : dirname( $target );
        if ( ! is_dir( $parent ) ) {
            @mkdir( $parent, 0755, true );
        }
        $parent_real = realpath( $parent );
        // WHY trailing separator: bare prefix match would let
        // '/wp-contentevil' pass as inside '/wp-content'. Force boundary.
        $sp_needle    = rtrim( (string) $wp_content_real, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
        $sp_haystack  = rtrim( (string) $parent_real,     DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
        if ( $parent_real === false || $wp_content_real === false || strpos( $sp_haystack, $sp_needle ) !== 0 ) {
            $zip->close();
            @unlink( $sp_zip_path );
            sp_installer_die( 'Extract Failed', 'SocietyPress archive entry resolved outside wp-content. Aborting for safety.' );
        }
        if ( ! $is_dir ) {
            file_put_contents( $target, $zip->getFromIndex( $index ) );
        }
    };

    if ( $is_bundle ) {
        // Bundle format: extract directly
        for ( $i = 0; $i < $zip->numFiles; $i++ ) {
            $name = $zip->getNameIndex( $i );

            // Plugin: societypress/* → plugins/societypress/*
            if ( strpos( $name, 'societypress/' ) === 0 && strpos( $name, 'themes/' ) !== 0 ) {
                $target = $install_dir . '/wp-content' . '/plugins/' . $name;
                $sp_safe_extract( $name, $target, $zip, $i );
            }

            // Themes: themes/* → themes/*
            if ( strpos( $name, 'themes/' ) === 0 ) {
                $target = $install_dir . '/wp-content' . '/' . $name;
                $sp_safe_extract( $name, $target, $zip, $i );
            }
        }
    } elseif ( $top_dir ) {
        // GitHub format: extract with path rewriting
        $plugin_dir = $install_dir . '/wp-content' . '/plugins/societypress/';
        @mkdir( $plugin_dir, 0755, true );

        $child_themes = [ 'heritage', 'coastline', 'prairie', 'ledger', 'parlor' ];

        for ( $i = 0; $i < $zip->numFiles; $i++ ) {
            $name = $zip->getNameIndex( $i );

            // Plugin
            if ( strpos( $name, "{$top_dir}/plugin/" ) === 0 ) {
                $rel = substr( $name, strlen( "{$top_dir}/plugin/" ) );
                if ( $rel === '' ) continue;
                $sp_safe_extract( $rel, $plugin_dir . $rel, $zip, $i );
            }

            // Parent theme
            if ( strpos( $name, "{$top_dir}/theme/" ) === 0 && strpos( $name, "{$top_dir}/theme-" ) !== 0 ) {
                $rel = substr( $name, strlen( "{$top_dir}/theme/" ) );
                if ( $rel === '' ) continue;
                $sp_safe_extract( $rel, $install_dir . '/wp-content' . '/themes/societypress/' . $rel, $zip, $i );
            }

            // Child themes
            foreach ( $child_themes as $ct ) {
                if ( strpos( $name, "{$top_dir}/theme-{$ct}/" ) === 0 ) {
                    $rel = substr( $name, strlen( "{$top_dir}/theme-{$ct}/" ) );
                    if ( $rel === '' ) continue;
                    $sp_safe_extract( $rel, $install_dir . '/wp-content' . "/themes/{$ct}/" . $rel, $zip, $i );
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

    // ---- 10. Redirect to the bridge script to finish WordPress install ----
    // WHY: Server-to-self HTTP requests fail on this host. We redirect the user's
    // browser to the bridge script, which loads WordPress in a normal HTTP context,
    // runs wp_install(), sets up permalinks, self-destructs, and redirects to login.
    // The mu-plugin (planted in step 7) activates SocietyPress on first admin load.
    $log[] = 'Redirecting to WordPress installation...';
    $_SESSION['sp_install_log'] = $log;

    // WHY rtrim($base): SCRIPT_NAME includes the filename (e.g. /subdir/install.php),
    // so dirname() gives us /subdir. rtrim() guards against a trailing slash on root
    // installs where dirname('/install.php') would return '/'.
    // The token is appended so only this redirect can trigger the bridge script.
    $base = dirname( $_SERVER['SCRIPT_NAME'] );
    header( 'Location: ' . rtrim( $base, '/' ) . '/sp-bridge-install.php?token=' . urlencode( $bridge_token ) );
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
    // WHY a hard size cap: WordPress core is ~25 MB and the SocietyPress
    // bundle is ~9 MB. A compromised CDN or bad mirror serving an unbounded
    // response could exhaust disk space or PHP memory before the timeout
    // fires. 200 MB is well above any legitimate payload while still bounded.
    $max_bytes = 200 * 1024 * 1024;

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
            // CURLOPT_MAXFILESIZE checks the Content-Length header (when
            // present) up front and aborts early. CURLOPT_PROGRESSFUNCTION
            // catches servers that omit Content-Length by aborting once the
            // running total crosses the cap.
            CURLOPT_MAXFILESIZE     => $max_bytes,
            CURLOPT_NOPROGRESS      => false,
            CURLOPT_PROGRESSFUNCTION => function ( $resource, $dl_total, $dl_now, $ul_total, $ul_now ) use ( $max_bytes ) {
                return ( $dl_now > $max_bytes ) ? 1 : 0; // non-zero return aborts
            },
        ] );

        $success = curl_exec( $ch );
        $code    = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        curl_close( $ch );
        fclose( $fp );

        if ( $success && $code >= 200 && $code < 400 && filesize( $dest ) > 1000 && filesize( $dest ) <= $max_bytes ) {
            return true;
        }
        @unlink( $dest );
    }

    // Fallback to file_get_contents
    if ( ini_get( 'allow_url_fopen' ) ) {
        // WHY explicit ssl context: PHP stream wrappers don't enable peer
        // verification by default unless openssl.cafile / curl.cainfo is
        // set in php.ini, which most shared hosts don't. Force-on here so
        // a MITM can't slip a crafted bundle past us via the fallback path.
        $ctx = stream_context_create( [
            'http' => [
                'timeout'    => 300,
                'user_agent' => 'SocietyPress-Installer/1.0',
                'follow_location' => true,
            ],
            'ssl' => [
                'verify_peer'      => true,
                'verify_peer_name' => true,
            ],
        ] );
        $data = @file_get_contents( $url, false, $ctx, 0, $max_bytes + 1 );
        if ( $data && strlen( $data ) > 1000 && strlen( $data ) <= $max_bytes ) {
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
        // WHY: same as above — explicit peer verification on the stream
        // fallback so we don't quietly skip TLS validation when shared-host
        // php.ini doesn't set openssl.cafile.
        $ctx = stream_context_create( [
            'ssl' => [
                'verify_peer'      => true,
                'verify_peer_name' => true,
            ],
        ] );
        $result = @file_get_contents( $url, false, $ctx );
        if ( $result ) return $result;
    }

    return null;
}

/**
 * POST to a URL and return the response body as a string.
 * Used to submit the WordPress install form programmatically.
 */
function sp_installer_download_string_post( string $url, array $data ): ?string {
    if ( function_exists( 'curl_init' ) ) {
        $ch = curl_init( $url );
        curl_setopt_array( $ch, [
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => http_build_query( $data ),
            CURLOPT_TIMEOUT         => 60,
            CURLOPT_SSL_VERIFYPEER  => true,
            CURLOPT_USERAGENT       => 'SocietyPress-Installer/1.0',
        ] );
        $result = curl_exec( $ch );
        curl_close( $ch );
        if ( $result ) return $result;
    }

    if ( ini_get( 'allow_url_fopen' ) ) {
        $ctx = stream_context_create( [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query( $data ),
                'timeout' => 60,
            ],
            'ssl' => [
                'verify_peer'      => true,
                'verify_peer_name' => true,
            ],
        ] );
        $result = @file_get_contents( $url, false, $ctx );
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
        // WHY htmlspecialchars: most callers pass static literals, but a few
        // include dynamic content (like raw mysqli error messages, server
        // version strings, or paths). Escaping here means no caller can
        // accidentally inject unescaped output into the page.
        echo '<p style="color: #991B1B; margin: 0;">' . htmlspecialchars( $message, ENT_QUOTES, 'UTF-8' ) . '</p>';
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
