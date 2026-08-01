#!/bin/bash
# SocietyPress Demo Nightly Rebuild
#
# Resets demo.getsocietypress.org to a clean state every night.
# Truncates all SP tables, re-seeds defaults, then populates with
# sample data for "Kindred Genealogical Society" — the fictional society
# seed-demo.php builds, which also overwrites the WordPress site title.
#
# Designed to run unattended via cron at 3 AM Central. The server clock is US
# Eastern (one hour ahead of Central), so the cron fires at 04:00 server time:
#   0 4 * * * /home/charle24/rebuild-demo.sh >> /home/charle24/rebuild-demo.log 2>&1
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
    # WHY: `wp user delete` takes any number of IDs at once. Looping one ID per
    # call spawned a fresh PHP process and full WordPress bootstrap per member,
    # which cost minutes once the demo carried a real 570-member roster.
    COUNT=$(echo "$USER_IDS" | wc -w | tr -d ' ')
    $WP user delete $USER_IDS --yes 2>/dev/null
    echo "  Removed $COUNT subscriber accounts."
else
    echo "  No subscriber accounts to remove."
fi

# ---------------------------------------------------------------------------
# 1b. Remove WordPress's stock content
# ---------------------------------------------------------------------------
# WHY: sp-installer.php strips "Hello world!", "Sample Page", and the canned
# comment on a real install, but a demo rebuilt straight from WP-CLI never
# goes through the installer. Repeat the sweep here so the demo can never
# greet a visitor with WordPress boilerplate. This must run before seeding:
# sp_maybe_create_default_pages() only builds a Home page when no published
# page exists, and "Sample Page" is enough to suppress it.

echo "Removing WordPress default content..."

$WP eval '
$hello = get_page_by_path( "hello-world", OBJECT, "post" );
if ( $hello ) { wp_delete_post( $hello->ID, true ); WP_CLI::log( "  Deleted \"Hello world!\" post." ); }

$sample = get_page_by_path( "sample-page" );
if ( $sample ) { wp_delete_post( $sample->ID, true ); WP_CLI::log( "  Deleted \"Sample Page\"." ); }

$comments = get_comments( [ "number" => 100 ] );
foreach ( $comments as $c ) { wp_delete_comment( $c->comment_ID, true ); }
if ( $comments ) { WP_CLI::log( "  Deleted " . count( $comments ) . " default comment(s)." ); }
' 2>&1

# Hello Dolly ships with WordPress and has no place on a society demo.
rm -f "$WP_PATH/wp-content/plugins/hello.php"

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
# 3c. Build the newsletter text index
#
# WHY this has to run here: the rebuild deletes and re-creates every newsletter
# row, so yesterday's index rows point at issues that no longer exist. Without
# this step the demo comes back each morning with covers and downloads intact
# but nothing searchable inside them, which is the half of the module worth
# showing. Also fills in each issue's contents list from its printed one.
# ---------------------------------------------------------------------------

echo "Indexing newsletter text..."
$WP eval '
global $wpdb;
$ids = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}sp_newsletters" );
$ok = 0; $toc = 0; $failed = 0;
foreach ( $ids as $id ) {
    $r = sp_newsletter_build_index( (int) $id );
    if ( is_wp_error( $r ) ) { $failed++; continue; }
    $ok++;
    if ( ! empty( $r["toc_items"] ) ) { $toc++; }
}
echo "  Indexed {$ok} of " . count( $ids ) . " newsletters, {$toc} with a contents list";
if ( $failed ) { echo ", {$failed} failed"; }
echo ".\n";
' 2>&1

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
