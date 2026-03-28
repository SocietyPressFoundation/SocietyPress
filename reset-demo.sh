#!/bin/bash
# SocietyPress Demo Reset Script
# Truncates all SP tables on demo.getsocietypress.org and re-seeds defaults.
#
# WHY: Evaluators exploring the demo site will create test data. This script
#      wipes everything back to a clean slate so the next person gets a fresh
#      experience. Also useful during development to start from a known state.
#
# Usage:
#   ./reset-demo.sh          Truncate all SP tables + re-seed defaults
#   ./reset-demo.sh --full   Same, but also deactivate/reactivate the plugin
#                             (fires all activation hooks, rebuilds tables)

HOST="skystra"
WP_PATH="~/domains/getsocietypress.org/public_html/demo"
WP="/usr/local/bin/wp --path=$WP_PATH"
REMOTE_SCRIPT="/tmp/sp-demo-reset-$$.php"

echo "=== SocietyPress Demo Reset ==="
echo "Target: demo.getsocietypress.org"
echo ""
echo "This will DELETE all SocietyPress data (members, events, library, etc.)"
echo "and restore only the default seed data (tiers, categories)."
echo ""
read -p "Are you sure? Type 'yes' to continue: " confirm
if [ "$confirm" != "yes" ]; then
    echo "Aborted."
    exit 0
fi

echo ""
echo "Uploading reset script..."

# WHY: SSH + wp eval + PHP quoting is a nightmare. Instead, we upload a
# small PHP script and run it via wp eval-file. Clean, debuggable, and
# the PHP runs with full WordPress context (wpdb, SP functions, etc.).
ssh "$HOST" "cat > $REMOTE_SCRIPT" << 'PHPEOF'
<?php
/**
 * SocietyPress demo reset — run via: wp eval-file /tmp/sp-demo-reset.php
 *
 * Truncates every sp_ table, then re-seeds default categories and tiers.
 * Tables themselves are preserved (only data is removed).
 */
global $wpdb;
$prefix = $wpdb->prefix . 'sp_';
$tables = $wpdb->get_col( $wpdb->prepare( 'SHOW TABLES LIKE %s', $prefix . '%' ) );

if ( empty( $tables ) ) {
    WP_CLI::error( 'No SP tables found. Is SocietyPress active?' );
}

WP_CLI::log( 'Truncating ' . count( $tables ) . ' tables...' );

$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 0' );
foreach ( $tables as $table ) {
    // Validate table name starts with our prefix — defense against any
    // unexpected SHOW TABLES result being used in a TRUNCATE statement.
    if ( strpos( $table, $prefix ) !== 0 ) {
        WP_CLI::warning( "  Skipped (wrong prefix): $table" );
        continue;
    }
    $wpdb->query( "TRUNCATE TABLE `" . esc_sql( $table ) . "`" );
    WP_CLI::log( "  Truncated: $table" );
}
$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 1' );

WP_CLI::success( count( $tables ) . ' tables truncated.' );

// Re-seed default data — these functions check for empty tables before inserting
WP_CLI::log( 'Re-seeding default data...' );

if ( function_exists( 'sp_maybe_seed_default_tiers' ) ) {
    sp_maybe_seed_default_tiers();
    WP_CLI::log( '  Membership tiers seeded.' );
}
if ( function_exists( 'sp_maybe_seed_event_categories' ) ) {
    sp_maybe_seed_event_categories();
    WP_CLI::log( '  Event categories seeded.' );
}
if ( function_exists( 'sp_maybe_seed_resource_categories' ) ) {
    sp_maybe_seed_resource_categories();
    WP_CLI::log( '  Resource categories seeded.' );
}
if ( function_exists( 'sp_maybe_seed_library_categories' ) ) {
    sp_maybe_seed_library_categories();
    WP_CLI::log( '  Library categories seeded.' );
}
if ( function_exists( 'sp_maybe_seed_document_categories' ) ) {
    sp_maybe_seed_document_categories();
    WP_CLI::log( '  Document categories seeded.' );
}
if ( function_exists( 'sp_maybe_create_newsletter_category' ) ) {
    sp_maybe_create_newsletter_category();
    WP_CLI::log( '  Newsletter category seeded.' );
}

WP_CLI::success( 'Demo reset complete. Default seed data restored.' );
PHPEOF

echo "Running reset..."
echo ""
ssh "$HOST" "$WP eval-file $REMOTE_SCRIPT"

# Clean up the temporary script
ssh "$HOST" "rm -f $REMOTE_SCRIPT"

# Full reset: deactivate + reactivate to fire all activation hooks
if [ "${1:-}" = "--full" ]; then
    echo ""
    echo "Full reset: deactivating and reactivating plugin..."
    ssh "$HOST" "$WP plugin deactivate societypress && $WP plugin activate societypress"
    echo "Plugin reactivated — all activation hooks fired (tables rebuilt via dbDelta)."
fi

echo ""
echo "=== Reset complete ==="
echo "Visit https://demo.getsocietypress.org/wp-admin/ to verify."
