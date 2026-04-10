#!/bin/bash
# SocietyPress Demo Nightly Rebuild
#
# Resets demo.getsocietypress.org to a clean state every night.
# Truncates all SP tables, re-seeds defaults, then populates with
# sample data for "Heritage Valley Historical Society."
#
# Designed to run unattended via cron at 3 AM Central:
#   0 3 * * * /home/charle24/rebuild-demo.sh >> /home/charle24/rebuild-demo.log 2>&1
#
# Can also be run manually (no confirmation prompt like reset-demo.sh).

WP_PATH="/home/charle24/domains/getsocietypress.org/public_html/demo"
WP="php -d disable_functions='' /usr/local/bin/wp --path=$WP_PATH"
SEED_SCRIPT="$WP_PATH/sample-data/seed-demo.php"

echo ""
echo "=== SocietyPress Demo Rebuild — $(date) ==="

# ---------------------------------------------------------------------------
# 1. Delete all non-admin WP users (member accounts from previous seed)
# ---------------------------------------------------------------------------

echo "Removing previous member user accounts..."
USER_IDS=$($WP user list --role=subscriber --field=ID 2>/dev/null)
if [ -n "$USER_IDS" ]; then
    for uid in $USER_IDS; do
        $WP user delete "$uid" --yes 2>/dev/null
    done
    echo "  Removed subscriber accounts."
else
    echo "  No subscriber accounts to remove."
fi

# ---------------------------------------------------------------------------
# 2. Truncate all SP tables and re-seed defaults
# ---------------------------------------------------------------------------

echo "Truncating SP tables..."

$WP eval '
global $wpdb;
$prefix = $wpdb->prefix . "sp_";
$tables = $wpdb->get_col( $wpdb->prepare( "SHOW TABLES LIKE %s", $prefix . "%" ) );
if ( empty( $tables ) ) { WP_CLI::error( "No SP tables found." ); }
$wpdb->query( "SET FOREIGN_KEY_CHECKS = 0" );
foreach ( $tables as $table ) {
    if ( strpos( $table, $prefix ) !== 0 ) continue;
    $wpdb->query( "TRUNCATE TABLE `" . esc_sql( $table ) . "`" );
}
$wpdb->query( "SET FOREIGN_KEY_CHECKS = 1" );
WP_CLI::log( "  Truncated " . count( $tables ) . " tables." );
' 2>&1

echo "Re-seeding defaults..."

$WP eval '
if ( function_exists( "sp_maybe_seed_default_tiers" ) )      { sp_maybe_seed_default_tiers();      WP_CLI::log( "  Membership tiers." ); }
if ( function_exists( "sp_maybe_seed_event_categories" ) )   { sp_maybe_seed_event_categories();   WP_CLI::log( "  Event categories." ); }
if ( function_exists( "sp_maybe_seed_resource_categories" ) ){ sp_maybe_seed_resource_categories(); WP_CLI::log( "  Resource categories." ); }
if ( function_exists( "sp_maybe_seed_library_categories" ) ) { sp_maybe_seed_library_categories();  WP_CLI::log( "  Library categories." ); }
if ( function_exists( "sp_maybe_seed_document_categories" ) ){ sp_maybe_seed_document_categories(); WP_CLI::log( "  Document categories." ); }
if ( function_exists( "sp_maybe_create_newsletter_category" ) ){ sp_maybe_create_newsletter_category(); WP_CLI::log( "  Newsletter category." ); }
if ( function_exists( "sp_maybe_seed_genealogy_sites" ) )    { sp_maybe_seed_genealogy_sites();    WP_CLI::log( "  Genealogy sites." ); }
' 2>&1

# ---------------------------------------------------------------------------
# 3. Run seed script (members, events, library, records, etc.)
# ---------------------------------------------------------------------------

echo "Running seed script..."
$WP eval-file "$SEED_SCRIPT" 2>&1

# ---------------------------------------------------------------------------
# 3b. Attach newsletter covers and PDFs
# ---------------------------------------------------------------------------

ATTACH_SCRIPT="$WP_PATH/sample-data/attach-newsletters.php"
if [ -f "$ATTACH_SCRIPT" ]; then
    echo "Attaching newsletter files..."
    $WP eval-file "$ATTACH_SCRIPT" 2>&1
fi

# ---------------------------------------------------------------------------
# 4. Reset demo user password (in case someone changed it)
# ---------------------------------------------------------------------------

echo "Resetting demo user..."
$WP user update societypressrocks --user_pass=societypressrocks 2>/dev/null || \
    $WP user create societypressrocks demo@example.com --role=administrator --user_pass=societypressrocks 2>/dev/null

# ---------------------------------------------------------------------------
# 5. Ensure .htaccess exists (WP core download doesn't create it)
# ---------------------------------------------------------------------------

HTACCESS="$WP_PATH/.htaccess"
if [ ! -f "$HTACCESS" ]; then
    echo "Creating .htaccess..."
    cat > "$HTACCESS" << 'HTEOF'
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
HTEOF
    echo "  .htaccess created."
fi

echo ""
echo "=== Rebuild complete — $(date) ==="
