<?php
/**
 * SocietyPress Demo Seed Script
 *
 * Populates the demo site (demo.getsocietypress.org) with realistic sample data
 * for "Kindred Genealogical Society" — a fictional county-rooted, statewide
 * North Dakota genealogical society based in Kindred, Cass County.
 *
 * Run via WP-CLI after reset-demo.sh truncates all SP tables:
 *   wp eval-file /path/to/seed-demo.php
 *
 * This script is idempotent — it checks for empty tables before inserting.
 * Members are imported from sample-data/members/members.csv (570 rows).
 * Records are imported from sample-data/records/*.csv (12 collections, ~2,132 rows).
 * Events use dates relative to today so they never go stale.
 */

if ( ! defined( 'ABSPATH' ) ) {
	WP_CLI::error( 'Must be run via wp eval-file with WordPress loaded.' );
}

global $wpdb;
$prefix = $wpdb->prefix . 'sp_';

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function seed_date_relative( $days_offset ) {
	return gmdate( 'Y-m-d', strtotime( "+{$days_offset} days" ) );
}

function seed_time( $hour, $minute = 0 ) {
	return sprintf( '%02d:%02d:00', $hour, $minute );
}

function seed_past_date( $days_ago ) {
	return gmdate( 'Y-m-d', strtotime( "-{$days_ago} days" ) );
}

// ---------------------------------------------------------------------------
// 1. SETTINGS — Kindred Genealogical Society
// ---------------------------------------------------------------------------

WP_CLI::log( 'Configuring settings...' );

$settings = get_option( 'societypress_settings', [] );
$settings = array_merge( $settings, [
	// Organization
	'organization_name'       => 'Kindred Genealogical Society',
	'organization_address'    => "100 Main Street\nKindred, ND 58051",
	'organization_phone'      => '(701) 555-0192',
	'organization_email'      => 'info@kindredgenealogy.org',

	// Membership
	'membership_period_type'  => 'annual',
	'membership_start_month'  => 1,
	'late_join_months'        => 3,
	'grace_period_months'     => 2,

	// Directory
	'dir_show_city_state'     => 1,
	'dir_show_phone'          => 0,
	'dir_show_email'          => 1,
	'dir_show_website'        => 0,
	'dir_show_tier'           => 1,
	'dir_show_join_date'      => 1,
	'dir_show_surnames'       => 1,
	'dir_per_page'            => 25,

	// Events
	'events_default_visibility'    => 'public',
	'events_default_registration'  => 1,
	'events_guest_registration'    => 1,
	'events_per_page'              => 12,
	'events_calendar_start_day'    => 0,

	// Stripe — test mode, no keys (demo only)
	'stripe_test_mode'             => 1,
	'stripe_currency'              => 'usd',

	// PayPal — test mode
	'paypal_test_mode'             => 1,

	// Design
	'design_color_primary'         => '#2d5f3f',
	'design_color_primary_hover'   => '#3a7a52',
	'design_color_accent'          => '#c4933f',
	'design_color_header_bg'       => '#2d5f3f',
	'design_color_header_text'     => '#ffffff',
	'design_color_footer_bg'       => '#1a3625',
	'design_color_footer_text'     => '#e8e0d4',
	'design_font_body'             => 'system',
	'design_font_heading'          => 'playfair',
	'design_font_size'             => 16,
	'design_content_width'         => 'standard',

	// Homepage hero — uses the video bundled with the parent theme.
	// WHY hardcoded HTTPS URLs: get_template_directory_uri() returns http://
	// on this server even though siteurl is https://, causing mixed content
	// blocking on the video. Hardcoding avoids that.
	'homepage_hero_type'           => 'video',
	'homepage_hero_media'          => 'https://demo.getsocietypress.org/wp-content/themes/societypress/assets/hero-background.mp4',
	'homepage_hero_poster'         => 'https://demo.getsocietypress.org/wp-content/themes/societypress/assets/hero-background-poster.jpg',
	'homepage_hero_headline'       => 'Kindred Genealogical Society',
	'homepage_hero_subtitle'       => 'Tracing North Dakota Roots Since 1995.',
	'homepage_hero_cta_text'       => 'Upcoming Events',
	'homepage_hero_cta_url'        => '/events/',
	'homepage_hero_overlay'        => 40,

	// Email
	'email_from_name'              => 'Kindred Genealogical Society',
	'email_from_email'             => 'info@kindredgenealogy.org',
	'welcome_email_enabled'        => 1,
	'welcome_email_subject'        => 'Welcome to Kindred Genealogical Society!',

	// Renewal
	'renewal_reminder_30d'         => 1,
	'renewal_reminder_15d'         => 1,
	'renewal_reminder_7d'          => 1,

	// Analytics — disabled for demo
	'analytics_exclude_admins'     => 1,

	// Store
	'store_intro_text'             => 'Browse books, maps, and publications from our collection. All proceeds support the society.',

	// Getting Started — dismissed so it does not show on dashboard
	'getting_started_dismissed'    => 1,
]);
update_option( 'societypress_settings', $settings );

// Set the WordPress site name + tagline so wp-admin and the browser title
// reflect the society identity. blogname and blogdescription are stored
// outside societypress_settings, so they need their own update_option calls.
update_option( 'blogname',        'Kindred Genealogical Society' );
update_option( 'blogdescription', 'Tracing North Dakota Roots Since 1995' );

// Enable all modules — must be a simple indexed array of slug strings,
// NOT an associative array. sp_module_enabled() uses in_array() on values.
update_option( 'sp_enabled_modules', [
	'members', 'events', 'library', 'newsletters', 'resources', 'governance',
	'store', 'records', 'donations', 'blast_email', 'gallery', 'help_requests',
	'documents', 'voting', 'lineage', 'research_services',
] );

// Mark setup wizard and getting started as completed so the frontend
// renders normally and the dashboard doesn't show the checklist
update_option( 'sp_wizard_completed', 1 );
update_option( 'sp_getting_started_dismissed', 1 );

WP_CLI::log( '  Settings configured.' );

// ---------------------------------------------------------------------------
// 2. MEMBERSHIP TIERS — customize the defaults
// ---------------------------------------------------------------------------

WP_CLI::log( 'Configuring membership tiers...' );

$tiers = $wpdb->get_results( "SELECT * FROM {$prefix}membership_tiers ORDER BY sort_order" );
if ( count( $tiers ) >= 5 ) {
	$updates = [
		[ 'name' => 'Individual',    'price' => 30.00,  'duration_months' => 12 ],
		[ 'name' => 'Family',        'price' => 45.00,  'duration_months' => 12 ],
		[ 'name' => 'Student',       'price' => 15.00,  'duration_months' => 12 ],
		[ 'name' => 'Life Member',   'price' => 400.00, 'duration_months' => 0  ],
		[ 'name' => 'Honorary',      'price' => 0.00,   'duration_months' => 0  ],
	];
	foreach ( $tiers as $i => $tier ) {
		if ( isset( $updates[ $i ] ) ) {
			$wpdb->update( "{$prefix}membership_tiers", $updates[ $i ], [ 'id' => $tier->id ] );
		}
	}
	WP_CLI::log( '  Tiers updated.' );
}

$tier_rows = $wpdb->get_results( "SELECT id, name FROM {$prefix}membership_tiers ORDER BY sort_order" );
$tier_ids  = [];
foreach ( $tier_rows as $t ) {
	$tier_ids[ $t->name ] = $t->id;
}

// ---------------------------------------------------------------------------
// 3. MEMBERS — Import 570 from sample-data/members/members.csv
//
// WHY: The Kindred dataset's member roster is hand-curated North Dakota names
// with realistic addresses, join dates, and demographics. The plugin's own
// import processor (sp_process_import_batch) handles ENS-style CSV columns
// out of the box, including auto-detecting the field mapping.
// ---------------------------------------------------------------------------

WP_CLI::log( 'Importing members from CSV...' );

$member_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}members" );
if ( $member_count > 0 ) {
	WP_CLI::log( '  Members already exist, skipping.' );
} else {
	$csv_path = '/home/charle24/domains/getsocietypress.org/public_html/demo/sample-data/members/members.csv';

	if ( ! file_exists( $csv_path ) ) {
		WP_CLI::warning( "  Member CSV not found: $csv_path" );
	} elseif ( ! function_exists( 'sp_process_import_batch' ) ) {
		WP_CLI::warning( '  sp_process_import_batch() not available — plugin may not be active.' );
	} else {
		// Process in batches so we don't hit memory or timeout limits.
		// Empty $field_map triggers the plugin's built-in ENS auto-detection.
		$offset      = 0;
		$batch_size  = 100;
		$total_imp   = 0;
		$total_skip  = 0;
		$all_errors  = [];

		while ( true ) {
			$results = sp_process_import_batch( $csv_path, [], $offset, $batch_size );
			$total_imp  += (int) ( $results['imported'] ?? 0 );
			$total_skip += (int) ( $results['skipped'] ?? 0 );
			if ( ! empty( $results['errors'] ) ) {
				$all_errors = array_merge( $all_errors, $results['errors'] );
			}
			if ( ! empty( $results['done'] ) ) {
				break;
			}
			$offset += $batch_size;
			// Defensive cap — should never trigger for a 570-row CSV.
			if ( $offset > 5000 ) break;
		}

		WP_CLI::log( "  Imported $total_imp members, skipped $total_skip." );
		if ( $all_errors ) {
			$show = array_slice( $all_errors, 0, 5 );
			foreach ( $show as $err ) {
				WP_CLI::warning( "    $err" );
			}
			if ( count( $all_errors ) > 5 ) {
				WP_CLI::warning( '    ...and ' . ( count( $all_errors ) - 5 ) . ' more.' );
			}
		}
	}
}

// ---------------------------------------------------------------------------
// 4. EVENTS — relative to today, North Dakota / Cass County themed
// ---------------------------------------------------------------------------

WP_CLI::log( 'Creating events...' );

$event_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}events" );
if ( $event_count > 0 ) {
	WP_CLI::log( '  Events already exist, skipping.' );
} else {
	// Get event category IDs
	$cat_rows  = $wpdb->get_results( "SELECT id, name FROM {$prefix}event_categories" );
	$cat_map   = [];
	foreach ( $cat_rows as $c ) {
		$cat_map[ strtolower( $c->name ) ] = $c->id;
	}
	$default_cat = ! empty( $cat_map ) ? reset( $cat_map ) : null;

	$events = [
		// Past events
		[ 'Pioneer Rest Cemetery Walk', 'Join us for a guided walk through Pioneer Rest Cemetery. Our docents share stories of the founding merchants, homesteaders, and Norwegian settlers who shaped Kindred and the surrounding Red River Valley.', -21, 10, 0, 12, 0, 'Pioneer Rest Cemetery', 'County Road 14, Kindred, ND', 30, 'public', 'field trip' ],
		[ 'Monthly Meeting: DNA Testing for Genealogists', 'Guest speaker Dr. Rebecca Foster of North Dakota State University discusses how autosomal DNA testing can break through brick walls in your research. Q&A to follow.', -14, 19, 0, 21, 0, 'Kindred Community Center', '100 Main St, Kindred, ND', 60, 'public', 'meeting' ],
		[ 'Beginner Genealogy Workshop', 'New to family history? This hands-on workshop covers vital records, census data, online databases, and organizing your findings.', -7, 13, 0, 16, 0, 'Kindred Public Library', '20 Elm St, Kindred, ND', 20, 'public', 'workshop' ],
		// Upcoming events
		[ 'Monthly Meeting: Norwegian Settlement of the Red River Valley', 'Local historian Marcus Thompson presents his research on Norwegian immigration to eastern North Dakota in the 1870s and 1880s, drawing on church records, ship manifests, and family papers.', 3, 19, 0, 21, 0, 'Kindred Community Center', '100 Main St, Kindred, ND', 60, 'public', 'meeting' ],
		[ 'Courthouse Records Research Day', 'Spend the morning at the Cass County Courthouse learning to navigate deed books, probate records, and marriage indexes with archivist Janet Mills.', 10, 9, 30, 12, 30, 'Cass County Courthouse', '211 9th St S, Fargo, ND', 15, 'members_only', 'workshop' ],
		[ 'Kindred Founders Day Celebration', 'Annual celebration of Kindred\'s founding in 1881. Period demonstrations, historical displays, and dedication of a new historical marker honoring the town\'s railroad heritage.', 17, 10, 0, 16, 0, 'Kindred Town Square', 'Main St & Railway Ave, Kindred, ND', 200, 'public', 'social' ],
		[ 'Using FamilySearch: Tips and Tricks', 'Learn advanced search techniques, the FamilySearch catalog, and the wiki. Bring a laptop.', 24, 14, 0, 16, 30, 'Kindred Public Library', '20 Elm St, Kindred, ND', 25, 'public', 'workshop' ],
		[ 'Board of Directors Meeting', 'Regular quarterly board meeting. Agenda includes budget review, membership report, and fall lecture series planning.', 28, 18, 30, 20, 0, 'KGS Office', '100 Main St Suite 2, Kindred, ND', 20, 'members_only', 'meeting' ],
		[ 'Summer Potluck and Show & Tell', 'Bring a dish to share and your most interesting genealogical find! Members share photos, heirlooms, and research breakthroughs.', 38, 17, 0, 20, 0, 'Sheyenne River Park Pavilion', 'River Rd, Kindred, ND', 80, 'members_only', 'social' ],
		[ 'Preserving Family Photographs', 'Conservator Sarah Quinn demonstrates storage, scanning, and restoration of old photos. Learn to identify and date daguerreotypes, tintypes, and cabinet cards.', 45, 10, 0, 12, 0, 'Kindred Public Library', '20 Elm St, Kindred, ND', 25, 'public', 'workshop' ],
		[ 'Monthly Meeting: Civil War Soldiers from Cass County', 'Author Robert Caldwell presents his new book documenting every Cass County man who served in the Civil War — most of them having migrated west to homestead afterward. Signed copies available.', 52, 19, 0, 21, 0, 'Kindred Community Center', '100 Main St, Kindred, ND', 60, 'public', 'meeting' ],
		[ 'Historic Homes Tour', 'Walking tour of Kindred and Casselton\'s most significant late-19th-century homes, led by architectural historian Dr. Karen Whitfield. Includes three private residences.', 60, 13, 0, 16, 0, 'Kindred Town Hall', '100 Main St, Kindred, ND', 25, 'public', 'field trip' ],
		[ 'Fall Lecture: Immigration to North Dakota', 'Professor Margaret Koehler traces major immigration waves to Dakota Territory and early statehood — Norwegians, Germans from Russia, Czechs, and Icelanders — through 1920.', 75, 19, 0, 21, 0, 'Kindred Community Center', '100 Main St, Kindred, ND', 60, 'public', 'meeting' ],
		[ 'Indexing Party: 1900 Census', 'Help us transcribe the 1900 federal census for Cass County. No experience needed. Pizza and drinks provided.', 85, 10, 0, 15, 0, 'Kindred Public Library', '20 Elm St, Kindred, ND', 20, 'members_only', 'workshop' ],
		[ 'Annual Holiday Open House', 'End-of-year celebration with hot cider, lefse, year-in-review slideshow, and election of officers. Bring a friend!', 100, 14, 0, 17, 0, 'Kindred Community Center', '100 Main St, Kindred, ND', 100, 'public', 'social' ],
	];

	foreach ( $events as $e ) {
		$days_offset = $e[2];
		$event_date  = ( $days_offset >= 0 ) ? seed_date_relative( $days_offset ) : seed_past_date( abs( $days_offset ) );
		$slug        = sanitize_title( $e[0] );
		$cat_id      = $cat_map[ strtolower( $e[11] ) ] ?? $default_cat;
		$ev_status   = ( $days_offset < 0 ) ? 'completed' : 'scheduled';

		$wpdb->insert( "{$prefix}events", [
			'title'                => $e[0],
			'slug'                 => $slug,
			'description'          => $e[1],
			'event_date'           => $event_date,
			'start_time'           => seed_time( $e[3], $e[4] ),
			'end_time'             => seed_time( $e[5], $e[6] ),
			'location_name'        => $e[7],
			'location_address'     => $e[8],
			'visibility'           => $e[10],
			'registration_enabled' => 1,
			'registration_limit'   => $e[9],
			'guest_registration'   => ( $e[10] === 'public' ) ? 1 : 0,
			'status'               => $ev_status,
			'category_id'          => $cat_id,
			'created_at'           => current_time( 'mysql' ),
			'updated_at'           => current_time( 'mysql' ),
		] );
	}
	WP_CLI::log( '  Created ' . count( $events ) . ' events.' );
}

// ---------------------------------------------------------------------------
// 5. LIBRARY CATALOG — North Dakota / Cass County focused
// ---------------------------------------------------------------------------

WP_CLI::log( 'Creating library catalog...' );

$lib_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}library_items" );
if ( $lib_count > 0 ) {
	WP_CLI::log( '  Library items already exist, skipping.' );
} else {
	// title, author, publisher, pub_year (int), call_number, media_type, acq_code, description
	$lib_items = [
		[ 'Cass County: A History 1873-1973', 'Edward Thornton', 'Kindred Press', 1973, '978.4 THO', 'Book', 'Gift', 'Centennial county history covering settlement, railroads, agriculture, and twentieth-century change.' ],
		[ 'Pioneers of the Red River Valley', 'Martha Collins', 'Kindred Genealogical Society', 1998, '929.3 COL', 'Book', 'KGS Publication', 'Biographical sketches of 200+ pioneer families who homesteaded in eastern Dakota Territory before 1890.' ],
		[ 'Cass County Cemeteries, Vol. 1', 'KGS Cemetery Committee', 'Kindred Genealogical Society', 2005, '929.5 KGS', 'Book', 'KGS Publication', 'Tombstone inscriptions from 12 cemeteries in the northern half of Cass County.' ],
		[ 'Cass County Cemeteries, Vol. 2', 'KGS Cemetery Committee', 'Kindred Genealogical Society', 2007, '929.5 KGS', 'Book', 'KGS Publication', 'Tombstone inscriptions from 14 cemeteries in the southern half of Cass County, including all rural family plots.' ],
		[ 'Civil War Soldiers from Cass County', 'Robert Caldwell', 'North Dakota State University Press', 2024, '973.7 CAL', 'Book', 'Purchase', 'Complete roster with unit histories and post-war homestead records — most served before settling here.' ],
		[ 'The Norwegian Settlement of Eastern Dakota', 'Marcus Thompson', 'NDSU Press', 2019, '977.4 THO', 'Book', 'Purchase', 'Documented immigration patterns, congregational records, and family histories of Norwegian settlers 1870-1900.' ],
		[ 'Germans from Russia in North Dakota', 'Hans Mueller', 'Germans from Russia Heritage Society', 2002, '929.3 MUE', 'Book', 'Donation', 'Immigration patterns, Black Sea villages of origin, church records, and family histories.' ],
		[ 'North Dakota Land Patents and Homestead Records', 'James Harrison', 'North Dakota Historical Society', 1990, '333.1 HAR', 'Book', 'Gift', 'Guide to understanding ND federal land records, including the 1862 Homestead Act case files.' ],
		[ 'Tracing Your North Dakota Ancestors', 'Carol Jenkins', 'Genealogical Publishing Co.', 2008, '929.1 JEN', 'Book', 'Purchase', 'County-by-county guide to genealogical resources in North Dakota.' ],
		[ 'Cass County Marriage Records 1875-1925', 'KGS Records Committee', 'Kindred Genealogical Society', 2010, '929.3 KGS', 'Book', 'KGS Publication', 'Index of all marriage records filed at the Cass County courthouse from territorial days through 1925.' ],
		[ 'Cass County Birth and Death Records 1893-1925', 'KGS Records Committee', 'Kindred Genealogical Society', 2013, '929.3 KGS', 'Book', 'KGS Publication', 'Transcribed vital records from the Cass County Health Department.' ],
		[ 'One-Room Schoolhouses of Cass County', 'Virginia Patterson', 'Kindred Press', 2003, '371.01 PAT', 'Book', 'Gift', 'History and photographs of 53 one-room schools with teacher and student lists drawn from county school district records.' ],
		[ 'The Czechs in North Dakota', 'David Novak', 'University of North Dakota Press', 1995, '929.3 NOV', 'Book', 'Gift', 'Czech immigration to North Dakota, with emphasis on the Pisek and Lankin communities.' ],
		[ 'Cass County Atlas 1893', '', 'Cass County Surveyor', 1893, '912 CAS', 'Book', 'Gift', 'Reproduction of the original 1893 county atlas with township and section maps.' ],
		[ 'Cass County Atlas 1910', '', 'Cass County Surveyor', 1910, '912 CAS', 'Book', 'Gift', 'Reproduction with farms, villages, and the Northern Pacific railroad lines.' ],
		[ 'Churches of Cass County', 'Ruth Anderson', 'Kindred Genealogical Society', 2015, '277.84 AND', 'Book', 'KGS Publication', 'History of 40+ Lutheran, Catholic, Methodist, and other congregations with founding dates and early membership rolls.' ],
		[ 'From Métis Trails to the Northern Pacific', 'Frank Mitchell', 'Kindred Press', 1988, '388.1 MIT', 'Book', 'Gift', 'Transportation history of the Red River Valley: Native trails, oxcart routes, steamboats, and railroads.' ],
		[ 'The Bonanza Farms of the Red River Valley', 'George Foster', 'Kindred Genealogical Society', 2018, '338.4 FOS', 'Book', 'KGS Publication', 'The great wheat farms of the 1870s and 1880s — owners, managers, hired hands, and the Norwegian and German laborers they employed.' ],
		// Periodicals
		[ 'Kindred Chronicle, 1995-2005', 'KGS', 'Kindred Genealogical Society', 1995, 'PERIODICAL', 'Periodical', 'KGS Publication', 'Bound newsletter volumes 1-10.' ],
		[ 'Kindred Chronicle, 2006-2015', 'KGS', 'Kindred Genealogical Society', 2006, 'PERIODICAL', 'Periodical', 'KGS Publication', 'Bound newsletter volumes 11-20.' ],
		[ 'Kindred Chronicle, 2016-2022', 'KGS', 'Kindred Genealogical Society', 2016, 'PERIODICAL', 'Periodical', 'KGS Publication', 'Bound newsletter volumes 21-27.' ],
		[ 'Red River Valley Historian, 1980-2010', 'Red River Valley Historical Society', 'Red River Valley Historical Society', 1980, 'PERIODICAL', 'Periodical', 'Gift', 'Complete run of the regional historical journal.' ],
		// Maps
		[ 'Cass County Township Map 1875', '', 'Dakota Territorial Surveyor', 1875, 'MAP-001', 'Map', 'Gift', 'Early territorial township map showing original Dakota land grant boundaries.' ],
		[ 'Cass County Road Map 1908', '', 'Cass County Engineer', 1908, 'MAP-002', 'Map', 'Gift', 'Detailed road map showing farms, schools, and churches in the early statehood era.' ],
		[ 'Sanborn Fire Insurance Map: Kindred 1898', '', 'Sanborn Map Company', 1898, 'MAP-003', 'Map', 'Purchase', 'Building-by-building map of the Kindred business district.' ],
		[ 'USGS Topo: Kindred Quadrangle 1959', '', 'U.S. Geological Survey', 1959, 'MAP-004', 'Map', 'Gift', '7.5 minute quadrangle showing terrain, roads, and structures.' ],
		// Vertical files
		[ 'Anderson Family File', '', '', null, 'VF-AND', 'Vertical File', 'Gift', 'Clippings, correspondence, and notes on the Anderson family of Kindred and Davenport.' ],
		[ 'Olson Family File', '', '', null, 'VF-OLS', 'Vertical File', 'Donation', 'Family group sheets, obituaries, and photos for the Olsons of Kindred and Carrington.' ],
		[ 'Kindred Business District History', '', '', null, 'VF-BUS', 'Vertical File', 'KGS Publication', 'Research file on Main Street businesses, 1881-1950.' ],
		[ 'Cass County Schools', '', '', null, 'VF-SCH', 'Vertical File', 'Gift', 'Lists of teachers and students drawn from county school district records.' ],
		[ 'Kindred Gazette Index', '', '', null, 'VF-NEWS', 'Vertical File', 'KGS Publication', 'Card index to births, deaths, and marriages from the Kindred Gazette, 1890-1915.' ],
	];

	foreach ( $lib_items as $item ) {
		$wpdb->insert( "{$prefix}library_items", [
			'title'       => $item[0],
			'author'      => $item[1],
			'publisher'   => $item[2],
			'pub_year'    => $item[3],
			'call_number' => $item[4],
			'media_type'  => $item[5],
			'acq_code'    => $item[6],
			'description' => $item[7],
			'available'   => 1,
			'created_at'  => current_time( 'mysql' ),
			'updated_at'  => current_time( 'mysql' ),
		] );
	}
	WP_CLI::log( '  Created ' . count( $lib_items ) . ' library items.' );
}

// ---------------------------------------------------------------------------
// 6. RESOURCE LINKS — North Dakota focused
// ---------------------------------------------------------------------------

WP_CLI::log( 'Creating resource links...' );

$res_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}resources" );
if ( $res_count > 0 ) {
	WP_CLI::log( '  Resources already exist, skipping.' );
} else {
	// Get or create resource categories
	$res_cats = $wpdb->get_results( "SELECT id, name FROM {$prefix}resource_categories" );
	$res_cat_map = [];
	foreach ( $res_cats as $rc ) {
		$res_cat_map[ strtolower( $rc->name ) ] = $rc->id;
	}
	if ( empty( $res_cat_map ) ) {
		$cats = ['National Databases','North Dakota Resources','Census & Vital Records','Military Records','Immigration','DNA & Genetics','Newspapers','Education & How-To'];
		foreach ( $cats as $i => $name ) {
			$wpdb->insert( "{$prefix}resource_categories", [
				'name' => $name, 'slug' => sanitize_title( $name ), 'sort_order' => $i,
			] );
			$res_cat_map[ strtolower( $name ) ] = $wpdb->insert_id;
		}
	}

	$resources = [
		[ 'FamilySearch', 'https://www.familysearch.org', 'Free genealogy database with billions of records.', 'national databases' ],
		[ 'Ancestry.com', 'https://www.ancestry.com', 'Largest subscription genealogy database. Free access at many libraries.', 'national databases' ],
		[ 'FindAGrave', 'https://www.findagrave.com', 'Cemetery records and photos contributed by volunteers worldwide.', 'national databases' ],
		[ 'BillionGraves', 'https://billiongraves.com', 'GPS-tagged headstone photos.', 'national databases' ],
		[ 'National Archives (NARA)', 'https://www.archives.gov', 'Federal records: census, military, immigration, and land.', 'national databases' ],
		[ 'State Historical Society of North Dakota', 'https://www.history.nd.gov', 'State archives and library, with extensive online collections.', 'north dakota resources' ],
		[ 'Digital Horizons', 'https://digitalhorizonsonline.org', 'Digital collections from North Dakota libraries, archives, and museums.', 'north dakota resources' ],
		[ 'NDSU Germans from Russia Heritage Collection', 'https://library.ndsu.edu/grhc', 'Premier research center for Germans-from-Russia genealogy.', 'north dakota resources' ],
		[ 'Red River Valley Genealogical Society', 'https://www.rrvgs.org', 'Regional society covering eastern North Dakota and northwest Minnesota.', 'north dakota resources' ],
		[ 'Steve Morse One-Step Tools', 'https://stevemorse.org', 'Powerful search tools for census, Ellis Island, and more.', 'census & vital records' ],
		[ 'Fold3', 'https://www.fold3.com', 'Military records: pension files, service records, casualty lists.', 'military records' ],
		[ 'NPS Civil War Soldiers', 'https://www.nps.gov/civilwar/search-soldiers.htm', 'Searchable database of Civil War service records — many ND settlers were veterans.', 'military records' ],
		[ 'Ellis Island', 'https://www.libertyellisfoundation.org', 'Ship manifest records for Port of New York arrivals.', 'immigration' ],
		[ 'Castle Garden', 'https://www.castlegarden.org', 'Pre-Ellis Island immigration records (1820-1892).', 'immigration' ],
		[ 'Norway Heritage Ship Lists', 'https://www.norwayheritage.com', 'Ship manifests and emigration records from Norway, 1825-1925.', 'immigration' ],
		[ 'ISOGG Wiki', 'https://isogg.org/wiki/', 'Comprehensive DNA testing guide for genealogists.', 'dna & genetics' ],
		[ 'DNA Painter', 'https://dnapainter.com', 'Visual chromosome mapping tool for genetic genealogy.', 'dna & genetics' ],
		[ 'Chronicling America', 'https://chroniclingamerica.loc.gov', 'Library of Congress historic newspaper archive — strong North Dakota coverage.', 'newspapers' ],
		[ 'Newspapers.com', 'https://www.newspapers.com', 'Large newspaper archive (subscription). Many ND papers including the Fargo Forum.', 'newspapers' ],
		[ 'Cyndi\'s List', 'https://www.cyndislist.com', 'Categorized directory of 330,000+ genealogy links.', 'education & how-to' ],
		[ 'RootsTech', 'https://www.rootstech.org', 'Annual genealogy conference with free virtual option.', 'education & how-to' ],
		[ 'Board for Certification of Genealogists', 'https://bcgcertification.org', 'Professional standards and certification.', 'education & how-to' ],
	];

	foreach ( $resources as $r ) {
		$cat_id = $res_cat_map[ $r[3] ] ?? reset( $res_cat_map );
		$wpdb->insert( "{$prefix}resources", [
			'title'       => $r[0],
			'url'         => $r[1],
			'description' => $r[2],
			'category_id' => $cat_id,
			'active'      => 1,
			'sort_order'  => 0,
			'created_at'  => current_time( 'mysql' ),
		] );
	}
	WP_CLI::log( '  Created ' . count( $resources ) . ' resource links.' );
}

// ---------------------------------------------------------------------------
// 7. NEWSLETTERS — 12 quarterly Kindred Chronicle issues
//
// Volume scheme: Volume 31 = Winter 2026 (society's 31st year of publication).
// PDF + cover image attachment is handled by attach-newsletters.php.
// ---------------------------------------------------------------------------

WP_CLI::log( 'Creating newsletters...' );

$nl_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}newsletters" );
if ( $nl_count > 0 ) {
	WP_CLI::log( '  Newsletters already exist, skipping.' );
} else {
	$newsletters = [
		[ 'Kindred Chronicle — Winter 2026', seed_past_date( 90 ),  31, 1, 'Cemetery indexing project update, DNA testing workshop recap, new book acquisitions.' ],
		[ 'Kindred Chronicle — Fall 2025',   seed_past_date( 180 ), 30, 4, 'Annual meeting minutes, treasurer\'s report, fall lecture series schedule.' ],
		[ 'Kindred Chronicle — Summer 2025', seed_past_date( 270 ), 30, 3, 'Founders Day recap, new members welcome, courthouse research day notes.' ],
		[ 'Kindred Chronicle — Spring 2025', seed_past_date( 360 ), 30, 2, 'Spring workshop series, library catalog now online, membership drive.' ],
		[ 'Kindred Chronicle — Winter 2025', seed_past_date( 450 ), 30, 1, 'Year in review, officer elections, holiday open house photos.' ],
		[ 'Kindred Chronicle — Fall 2024',   seed_past_date( 540 ), 29, 4, 'Norwegian settlement series begins, Welsh heritage month.' ],
		[ 'Kindred Chronicle — Summer 2024', seed_past_date( 630 ), 29, 3, 'Summer picnic photos, research trip to the State Archives in Bismarck.' ],
		[ 'Kindred Chronicle — Spring 2024', seed_past_date( 720 ), 29, 2, 'Genealogy workshop for beginners, new vertical files, volunteer spotlight.' ],
		[ 'Kindred Chronicle — Winter 2024', seed_past_date( 810 ), 29, 1, 'Annual report, budget summary, digitization project plans.' ],
		[ 'Kindred Chronicle — Fall 2023',   seed_past_date( 900 ), 28, 4, 'Guest speaker series, North Dakota History Day results.' ],
		[ 'Kindred Chronicle — Summer 2023', seed_past_date( 990 ), 28, 3, 'Cass County sesquicentennial planning, oral history launch.' ],
		[ 'Kindred Chronicle — Spring 2023', seed_past_date( 1080), 28, 2, 'Immigration records workshop, new map acquisitions.' ],
	];

	foreach ( $newsletters as $nl ) {
		$wpdb->insert( "{$prefix}newsletters", [
			'title'        => $nl[0],
			'slug'         => sanitize_title( $nl[0] ),
			'pub_date'     => $nl[1],
			'volume'       => $nl[2],
			'issue_number' => $nl[3],
			'description'  => $nl[4],
			'visibility'   => 'members_only',
			'created_at'   => current_time( 'mysql' ),
			'updated_at'   => current_time( 'mysql' ),
		] );
	}
	WP_CLI::log( '  Created ' . count( $newsletters ) . ' newsletters.' );
}

// ---------------------------------------------------------------------------
// 8. DONATIONS — 25 sample donations
// ---------------------------------------------------------------------------

WP_CLI::log( 'Creating donations...' );

$don_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}donations" );
if ( $don_count > 0 ) {
	WP_CLI::log( '  Donations already exist, skipping.' );
} else {
	$donors  = ['Harold Wilson','Robert Harrison','Anonymous','Kindred Garden Club','Estate of James Foster','Patricia Edwards','Thomas Mitchell','Anonymous','David Clark','Susan Phillips','The Thornton Family','Helen Campbell','Anonymous','Ronald Olson','Janet Morgan','Anonymous','George Spencer','Ruth Anderson','William Price','Carol Jenkins','The Cass County Foundation','Frank Mitchell','Anonymous','Dorothy Collins','Kenneth Walker'];
	$amounts = [ 25, 50, 50, 100, 2500, 75, 100, 50, 200, 150, 500, 100, 25, 50, 250, 100, 1000, 75, 50, 200, 5000, 100, 25, 150, 50 ];

	for ( $i = 0; $i < 25; $i++ ) {
		$is_anon = ( $donors[ $i ] === 'Anonymous' ) ? 1 : 0;
		$wpdb->insert( "{$prefix}donations", [
			'donor_name'   => $is_anon ? 'Anonymous' : $donors[ $i ],
			'amount'       => $amounts[ $i ],
			'type'         => 'cash',
			'date'         => seed_past_date( rand( 5, 730 ) ),
			'is_anonymous' => $is_anon,
			'note'         => '',
			'created_at'   => current_time( 'mysql' ),
		] );
	}
	WP_CLI::log( '  Created 25 donations.' );
}

// ---------------------------------------------------------------------------
// 9. GROUPS (Committees)
// ---------------------------------------------------------------------------

WP_CLI::log( 'Creating groups...' );

$grp_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}groups" );
if ( $grp_count > 0 ) {
	WP_CLI::log( '  Groups already exist, skipping.' );
} else {
	$groups = [
		[ 'Cemetery Committee', 'Indexes and preserves Cass County cemeteries. Conducts annual cemetery walks at Pioneer Rest and partner sites.' ],
		[ 'Library Committee', 'Manages the society library collection. Handles acquisitions, cataloging, and Open Library Saturdays.' ],
		[ 'Programs Committee', 'Plans monthly meetings, guest speakers, and the annual fall lecture series.' ],
		[ 'Membership Committee', 'Recruits new members, manages renewals, and welcomes new joiners across North Dakota.' ],
		[ 'Publications Committee', 'Produces the Kindred Chronicle newsletter and special society publications.' ],
	];
	foreach ( $groups as $i => $g ) {
		$wpdb->insert( "{$prefix}groups", [
			'name'        => $g[0],
			'slug'        => sanitize_title( $g[0] ),
			'description' => $g[1],
			'status'      => 'active',
			'sort_order'  => $i,
			'created_at'  => current_time( 'mysql' ),
		] );
	}
	WP_CLI::log( '  Created ' . count( $groups ) . ' groups.' );
}

// ---------------------------------------------------------------------------
// Bulk record inserter — shared by the CSV collections and the GENRECORD load.
//
// WHY: Both importers originally issued one INSERT per record plus one INSERT
// per field value plus an UPDATE to backfill search_text. Across ~8,100 records
// that came to roughly 62,000 round trips and pushed the nightly rebuild past
// ten minutes. Batching into multi-row INSERTs, with search_text computed
// up front so the UPDATE disappears, cuts that to a few hundred queries.
//
// The record IDs come from $wpdb->insert_id after a multi-row INSERT, which
// returns the id of the FIRST row in the batch; the rest follow sequentially.
// That holds because this seeder is the only writer while it runs — a safe
// assumption for a script that just truncated every table it touches.
//
// $rows is a list of ordered value arrays; $field_ids maps column index to the
// record_collection_fields row id. Returns the number of records written.
// ---------------------------------------------------------------------------

function kgs_bulk_insert_records( $wpdb, string $prefix, int $collection_id, array $field_ids, array $rows ): int {
	if ( empty( $rows ) ) {
		return 0;
	}

	$now          = current_time( 'mysql' );
	$record_chunk = 500;
	$value_chunk  = 2000;

	// ---- Pass 1: the record rows, with search_text already assembled ----
	$search_texts = [];
	foreach ( $rows as $row ) {
		$parts = [];
		foreach ( $row as $val ) {
			$val = trim( (string) $val );
			if ( $val !== '' ) {
				$parts[] = $val;
			}
		}
		$search_texts[] = implode( ' ', $parts );
	}

	$record_ids = [];
	foreach ( array_chunk( $search_texts, $record_chunk ) as $chunk ) {
		$placeholders = [];
		$values       = [];
		foreach ( $chunk as $search_text ) {
			$placeholders[] = '(%d, %s, %s, %s)';
			$values[]       = $collection_id;
			$values[]       = $search_text;
			$values[]       = $now;
			$values[]       = $now;
		}
		$sql = "INSERT INTO {$prefix}records (collection_id, search_text, created_at, updated_at) VALUES "
			. implode( ', ', $placeholders );
		$wpdb->query( $wpdb->prepare( $sql, $values ) );

		$first_id = (int) $wpdb->insert_id;
		if ( ! $first_id ) {
			// Without ids there is nothing to hang the field values on, so stop
			// rather than write orphaned rows.
			return count( $record_ids );
		}
		for ( $i = 0; $i < count( $chunk ); $i++ ) {
			$record_ids[] = $first_id + $i;
		}
	}

	// ---- Pass 2: the field values ----
	$pending = [];
	foreach ( $rows as $idx => $row ) {
		if ( ! isset( $record_ids[ $idx ] ) ) {
			continue;
		}
		foreach ( $row as $col_idx => $val ) {
			if ( ! isset( $field_ids[ $col_idx ] ) ) {
				continue;
			}
			$val = trim( (string) $val );
			if ( $val === '' ) {
				continue;
			}
			$pending[] = [ $record_ids[ $idx ], $field_ids[ $col_idx ], $val ];
		}
	}

	foreach ( array_chunk( $pending, $value_chunk ) as $chunk ) {
		$placeholders = [];
		$values       = [];
		foreach ( $chunk as $triple ) {
			$placeholders[] = '(%d, %d, %s)';
			$values[]       = $triple[0];
			$values[]       = $triple[1];
			$values[]       = $triple[2];
		}
		$sql = "INSERT INTO {$prefix}record_values (record_id, field_id, field_value) VALUES "
			. implode( ', ', $placeholders );
		$wpdb->query( $wpdb->prepare( $sql, $values ) );
	}

	return count( $record_ids );
}

// ---------------------------------------------------------------------------
// 10. RECORDS — Import from CSV files
// ---------------------------------------------------------------------------

WP_CLI::log( 'Creating genealogical record collections...' );

$rec_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}record_collections" );
if ( $rec_count > 0 ) {
	WP_CLI::log( '  Record collections already exist, skipping.' );
} else {
	$csv_dir = '/home/charle24/domains/getsocietypress.org/public_html/demo/sample-data/records/';

	$collections = [
		[ 'cemetery-burial-index.csv',      'Cass County Cemetery Index',              'cemetery',     'Tombstone inscriptions from cemeteries across the county.',          '1875-2000', 'Cass County, North Dakota' ],
		[ 'census-transcriptions.csv',      'Cass County Census Transcriptions',       'census',       'Transcribed federal census records.',                                '1880-1940', 'Cass County, North Dakota' ],
		[ 'church-records.csv',             'Red River Valley Church Records',         'church',       'Baptisms, marriages, and burials from Lutheran, Catholic, and Methodist churches.', '1875-1950', 'Cass County, North Dakota' ],
		[ 'obituary-index.csv',             'Cass County Obituary Index',              'obituary',     'Index to obituaries in the Kindred Gazette and Fargo Forum.',        '1890-1980', 'Cass County, North Dakota' ],
		[ 'marriage-records.csv',           'Cass County Marriage Records',            'marriage',     'Marriage records from the Cass County Courthouse.',                  '1875-1950', 'Cass County, North Dakota' ],
		[ 'vital-records.csv',              'Cass County Vital Records',               'vital',        'Birth and death records from the county health department.',         '1893-1960', 'Cass County, North Dakota' ],
		[ 'military-records.csv',           'North Dakota Military Records',           'military',     'Service records of ND residents from the Civil War through WWII.',   '1861-1945', 'North Dakota' ],
		[ 'land-deed-records.csv',          'Cass County Land & Deed Records',         'land',         'Land patents, transfers, and deeds, including original homestead case files.', '1873-1920', 'Cass County, North Dakota' ],
		[ 'probate-estate-records.csv',     'Cass County Probate Records',             'probate',      'Wills, inventories, and estate records.',                            '1875-1950', 'Cass County, North Dakota' ],
		[ 'immigration-naturalization.csv',  'North Dakota Naturalization Records',    'immigration',  'Naturalization papers filed in district courts across the state.',   '1880-1930', 'North Dakota' ],
		[ 'newspaper-abstracts.csv',        'Kindred Gazette Abstracts',               'newspaper',    'Genealogically significant items from the Kindred Gazette.',         '1890-1940', 'Kindred, North Dakota' ],
		[ 'tax-lists.csv',                  'Cass County Tax Lists',                   'tax',          'Annual tax assessment records for property owners.',                 '1880-1910', 'Cass County, North Dakota' ],
	];

	foreach ( $collections as $coll ) {
		$csv_file = $csv_dir . $coll[0];

		$wpdb->insert( "{$prefix}record_collections", [
			'name'         => $coll[1],
			'slug'         => sanitize_title( $coll[1] ),
			'description'  => $coll[3],
			'record_type'  => $coll[2],
			'source_info'  => 'Kindred Genealogical Society Research Collection',
			'date_range'   => $coll[4],
			'location'     => $coll[5],
			'access_level' => 'public',
			'status'       => 'active',
			'record_count' => 0,
			'created_at'   => current_time( 'mysql' ),
			'updated_at'   => current_time( 'mysql' ),
		] );
		$collection_id = $wpdb->insert_id;

		if ( ! file_exists( $csv_file ) ) {
			WP_CLI::warning( "  CSV not found: {$coll[0]} — collection created empty." );
			continue;
		}

		$fh = fopen( $csv_file, 'r' );
		if ( ! $fh ) continue;
		$headers = fgetcsv( $fh );
		if ( ! $headers ) { fclose( $fh ); continue; }

		$field_ids = [];
		foreach ( $headers as $idx => $header ) {
			$field_name = trim( $header );
			$wpdb->insert( "{$prefix}record_collection_fields", [
				'collection_id' => $collection_id,
				'field_name'    => $field_name,
				'field_slug'    => sanitize_title( $field_name ),
				'field_type'    => 'text',
				'sort_order'    => $idx,
				'required'      => 0,
				'searchable'    => 1,
				'is_public'     => 1,
			] );
			$field_ids[ $idx ] = $wpdb->insert_id;
		}

		$csv_rows = [];
		while ( ( $row = fgetcsv( $fh ) ) !== false ) {
			$csv_rows[] = $row;
		}
		fclose( $fh );

		$imported = kgs_bulk_insert_records( $wpdb, $prefix, $collection_id, $field_ids, $csv_rows );

		$wpdb->update( "{$prefix}record_collections", [ 'record_count' => $imported ], [ 'id' => $collection_id ] );
		WP_CLI::log( "  {$coll[1]}: $imported records." );
	}
}

// ---------------------------------------------------------------------------
// 10b. HART ISLAND GENRECORD — public-domain demo of the .genrecord format
//
// The .gedrec file at sample-data/records/hart-island-burials.gedrec is a
// real public-domain dataset (NYC Hart Island burial records, ~150 rows).
// Loading it on demo proves the GENRECORD differentiator the marketing
// site advertises, using real data rather than synthetic placeholders.
//
// Idempotent: skip if a collection with the GENRECORD-derived slug
// already exists.
// ---------------------------------------------------------------------------

WP_CLI::log( 'Loading Hart Island GENRECORD dataset...' );

$gr_path = '/home/charle24/domains/getsocietypress.org/public_html/demo/sample-data/records/hart-island-burials.gedrec';
if ( ! file_exists( $gr_path ) ) {
	WP_CLI::log( '  Skipping — file not present at ' . $gr_path );
} elseif ( ! function_exists( 'sp_parse_genrecord_file' ) ) {
	WP_CLI::log( '  Skipping — sp_parse_genrecord_file() not available.' );
} else {
	$parsed = sp_parse_genrecord_file( $gr_path );
	if ( is_wp_error( $parsed ) ) {
		WP_CLI::log( '  Parse failed: ' . $parsed->get_error_message() );
	} else {
		$header  = $parsed['header'];
		$columns = $parsed['columns'];
		$rows    = $parsed['rows'];

		// GENRECORD spec keys are case-insensitive; the parser lowercases them.
		$col_name    = sanitize_text_field( $header['collection'] ?? 'Hart Island Burials' );
		$slug        = sanitize_title( $col_name );
		$gr_type     = strtoupper( $header['type'] ?? 'BIO' );
		$type_map    = function_exists( 'sp_genrecord_type_to_sp' ) ? sp_genrecord_type_to_sp() : [];
		$sp_type     = $type_map[ $gr_type ] ?? 'general';
		$description = sanitize_textarea_field( $header['description'] ?? $header['notes'] ?? '' );
		$location    = sanitize_text_field( $header['location'] ?? '' );
		$date_range  = sanitize_text_field( $header['date-range'] ?? '' );

		$existing = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM {$prefix}record_collections WHERE slug = %s",
			$slug
		) );
		if ( $existing ) {
			WP_CLI::log( '  Hart Island collection already present; skipping import.' );
		} else {
			$wpdb->insert( "{$prefix}record_collections", [
				'name'         => $col_name,
				'slug'         => $slug,
				'description'  => $description,
				'record_type'  => $sp_type,
				// WHY: Society is an optional GENRECORD header and is absent on
				// third-party data like the Hart Island set — NYC didn't compile
				// it for a genealogical society. Join only the parts that exist
				// so the field never opens with a dangling " | " separator.
				'source_info'  => implode( ' | ', array_filter( [
					trim( (string) ( $header['society'] ?? '' ) ),
					! empty( $header['license'] ) ? 'License: ' . $header['license'] : '',
				] ) ),
				'date_range'   => $date_range,
				'location'     => $location,
				'access_level' => 'public',
				'status'       => 'active',
				'record_count' => 0,
			] );
			$gr_collection_id = (int) $wpdb->insert_id;

			$field_ids = [];
			foreach ( $columns as $i => $col ) {
				if ( strtolower( trim( $col ) ) === 'record_id' ) continue;
				$wpdb->insert( "{$prefix}record_collection_fields", [
					'collection_id' => $gr_collection_id,
					'field_name'    => sanitize_text_field( $col ),
					'field_slug'    => sanitize_key( $col ) ?: 'field_' . $i,
					'field_type'    => 'text',
					'sort_order'    => $i,
					'searchable'    => 1,
					'is_public'     => 1,
				] );
				$field_ids[ $i ] = (int) $wpdb->insert_id;
			}

			$record_count = kgs_bulk_insert_records( $wpdb, $prefix, $gr_collection_id, $field_ids, $rows );
			$wpdb->update( "{$prefix}record_collections", [ 'record_count' => $record_count ], [ 'id' => $gr_collection_id ] );
			WP_CLI::log( "  Hart Island: $record_count records, " . count( $field_ids ) . ' fields.' );
		}
	}
}


// ---------------------------------------------------------------------------
// 11. DEMO ADMIN MEMBER RECORD
// ---------------------------------------------------------------------------

WP_CLI::log( 'Creating demo admin member record...' );

$demo_user = get_user_by( 'login', 'societypressrocks' );
if ( $demo_user ) {
	$has_record = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM {$prefix}members WHERE user_id = %d", $demo_user->ID
	) );
	if ( ! $has_record ) {
		$life_tier = $wpdb->get_var( "SELECT id FROM {$prefix}membership_tiers WHERE name = 'Life Member' LIMIT 1" );
		// Give the WP user a real name too
		wp_update_user( [
			'ID'           => $demo_user->ID,
			'first_name'   => 'Harold',
			'last_name'    => 'Wilson',
			'display_name' => 'Harold Wilson',
		] );

		$wpdb->insert( "{$prefix}members", [
			'user_id'        => $demo_user->ID,
			'first_name'     => 'Harold',
			'last_name'      => 'Wilson',
			'phone'          => '(701) 555-0192',
			'address_1'      => '100 Main Street',
			'city'           => 'Kindred',
			'state'          => 'ND',
			'postal_code'    => '58051',
			'country'        => 'US',
			'tier_id'        => $life_tier ?: 1,
			'status'         => 'active',
			'lifetime'       => 1,
			'join_date'      => '2020-01-01',
			'dir_show_name'  => 1,
			'dir_show_email' => 1,
			'created_at'     => current_time( 'mysql' ),
			'updated_at'     => current_time( 'mysql' ),
		] );
		WP_CLI::log( '  Demo admin member record created.' );
	} else {
		WP_CLI::log( '  Demo admin already has a member record.' );
	}
}

// ---------------------------------------------------------------------------
// 11b. STATIC PAGE COPY
//
// WHY: sp_maybe_create_default_pages() seeds About, Events, and Resources with
// bracketed authoring prompts — "[Describe your society's mission here.]" —
// which is right for a real society staring at a blank site, and wrong for a
// public demo, where a visitor reads the brackets as unfinished software. Fill
// them in with copy that matches the rest of the Kindred dataset. The plugin's
// prompts are left untouched so a fresh install still guides the admin.
// ---------------------------------------------------------------------------

// The plugin now ships a Records page, but sp_maybe_create_default_pages()
// returns early the moment any published page exists, so an already-built demo
// never receives it. Create it here as well, idempotently, and add it to the
// primary menu — otherwise thousands of imported records stay reachable only
// from wp-admin.
WP_CLI::log( 'Ensuring the Records page exists...' );

$records_page = get_page_by_path( 'records' );
if ( ! $records_page ) {
	$records_id = wp_insert_post( [
		'post_title'   => 'Records',
		'post_content' => '',
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_name'    => 'records',
	] );
	if ( $records_id && ! is_wp_error( $records_id ) ) {
		update_post_meta( $records_id, '_wp_page_template', 'sp-records' );
		WP_CLI::log( '  Created Records page.' );
	}
} else {
	$records_id = $records_page->ID;
	update_post_meta( $records_id, '_wp_page_template', 'sp-records' );
	WP_CLI::log( '  Records page already present.' );
}

// Slot it into the primary menu next to Library, where a researcher looking for
// holdings would expect to find it.
if ( ! empty( $records_id ) && ! is_wp_error( $records_id ) ) {
	$menu = wp_get_nav_menu_object( 'Primary Menu' );
	if ( $menu ) {
		$already = false;
		foreach ( wp_get_nav_menu_items( $menu->term_id ) ?: [] as $item ) {
			if ( (int) $item->object_id === (int) $records_id && $item->object === 'page' ) {
				$already = true;
				break;
			}
		}
		if ( ! $already ) {
			$library     = get_page_by_path( 'library' );
			$library_pos = 0;
			foreach ( wp_get_nav_menu_items( $menu->term_id ) ?: [] as $item ) {
				if ( $library && (int) $item->object_id === (int) $library->ID ) {
					$library_pos = (int) $item->menu_order;
				}
			}
			wp_update_nav_menu_item( $menu->term_id, 0, [
				'menu-item-title'     => 'Records',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $records_id,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $library_pos ? $library_pos + 1 : 0,
			] );
			WP_CLI::log( '  Added Records to the primary menu.' );
		}
	}
}

WP_CLI::log( 'Filling in static page copy...' );

$page_copy = [
	'about' => '<h2>Our Mission</h2>'
		. '<p>Kindred Genealogical Society collects, preserves, and shares the'
		. ' genealogical and historical record of Cass County and the Red River'
		. ' Valley. We help researchers of every experience level trace families'
		. ' who homesteaded, farmed, and built communities across eastern North'
		. ' Dakota — and we make sure the records that document them survive.</p>'
		. '<h2>Our History</h2>'
		. '<p>The society was founded in 1995 by eleven researchers who met in the'
		. ' basement of the Kindred public library to index local cemetery'
		. ' inscriptions. That first project grew into a catalog of transcribed'
		. ' cemetery, church, census, and courthouse records covering more than a'
		. ' century of county history. Today we hold a research library, publish a'
		. ' quarterly newsletter, and maintain searchable record collections'
		. ' available to anyone.</p>'
		. '<h2>Meetings</h2>'
		. '<p>We meet the first Saturday of each month at 10:00 AM in the community'
		. ' room at 402 Elm Street, Kindred, North Dakota. Meetings run about two'
		. ' hours and usually include a program or guest speaker. Visitors are'
		. ' welcome and no reservation is needed — come find out whether we can'
		. ' help with your family.</p>',

	'events' => '<p>Check our calendar for upcoming meetings, workshops, and'
		. ' special events.</p>'
		. '<h2>Regular Meetings</h2>'
		. '<p>Our general meeting is the first Saturday of every month at 10:00 AM'
		. ' in the community room at 402 Elm Street, Kindred. The board meets the'
		. ' third Tuesday of each month at 6:30 PM, and board meetings are open to'
		. ' any member who wants to attend.</p>'
		. '<h2>Special Programs</h2>'
		. '<p>Several times a year we host workshops on the skills our members ask'
		. ' about most: reading German script and Norwegian parish registers,'
		. ' searching federal land records, using DNA results alongside paper'
		. ' research, and preserving family photographs. Our annual fall seminar'
		. ' brings in a visiting speaker and fills quickly, so watch the newsletter'
		. ' and the calendar for registration.</p>',

	'resources' => '<p>Browse our collection of research materials and guides.</p>'
		. '<h2>Research Databases</h2>'
		. '<p>Our transcribed record collections — cemetery inscriptions, census'
		. ' transcriptions, church registers, obituaries, marriages, vital'
		. ' records, military service, land and deed records, probate files,'
		. ' naturalizations, newspaper abstracts, and tax lists — are searchable'
		. ' free of charge from our Records page. Members also have access to'
		. ' subscription databases on the workstations in our research library.</p>'
		. '<h2>How-To Guides</h2>'
		. '<p>New to genealogy, or new to North Dakota research? Our guides cover'
		. ' getting started with what your family already knows, locating a'
		. ' homestead file, tracing Germans from Russia and Norwegian immigrant'
		. ' lines, and finding records when the county courthouse burned. Members'
		. ' can download all of them from the member portal.</p>'
		. '<h2>Society Library</h2>'
		. '<p>Our library holds county and family histories, plat books, church'
		. ' anniversary volumes, periodicals, and a vertical file of surname'
		. ' folders built up over thirty years. The catalog is online and open to'
		. ' everyone. The reading room is open Tuesdays and Saturdays, and members'
		. ' may borrow circulating material for three weeks.</p>',
];

$copy_updated = 0;
foreach ( $page_copy as $slug => $content ) {
	$page = get_page_by_path( $slug );
	if ( ! $page ) {
		continue;
	}
	wp_update_post( [
		'ID'           => $page->ID,
		'post_content' => $content,
	] );
	$copy_updated++;
}
WP_CLI::log( "  Filled in $copy_updated pages." );

// ---------------------------------------------------------------------------
// 12. HOMEPAGE PAGE BUILDER WIDGETS
// ---------------------------------------------------------------------------

WP_CLI::log( 'Configuring homepage widgets...' );

$home_id = (int) get_option( 'page_on_front' );
if ( $home_id ) {
	$widgets = [
		[
			'type'     => 'rich_text',
			'settings' => [
				'content' => '<h2 style="text-align:center;">Welcome to Kindred Genealogical Society</h2>'
				           . '<p style="text-align:center;font-size:1.1em;">Founded in 1995 and based in Kindred, North Dakota, we are dedicated to preserving and sharing the rich genealogical and historical heritage of Cass County and the Red River Valley. Whether you are tracing your family roots, researching ND homesteaders, or connecting with fellow researchers, you have come to the right place.</p>',
			],
		],
		[
			'type'     => 'feature_cards',
			'settings' => [
				'columns' => 3,
				'cards'   => [
					[
						'title'       => 'Library & Archives',
						'description' => 'Books, maps, periodicals, and vertical files covering Cass County and North Dakota genealogy.',
						'btn_text'    => 'Browse Catalog',
						'btn_url'     => '/library/',
						'image_id'    => 0,
					],
					[
						'title'       => 'Genealogical Records',
						'description' => 'Search 2,100+ cemetery, census, marriage, military, and land records spanning Cass County and North Dakota.',
						'btn_text'    => 'Search Records',
						'btn_url'     => '/records/',
						'image_id'    => 0,
					],
					[
						'title'       => 'Join Our Society',
						'description' => 'Join a vibrant statewide community of genealogists and history enthusiasts. Individual memberships from $30/year.',
						'btn_text'    => 'Join Today',
						'btn_url'     => '/join/',
						'image_id'    => 0,
					],
				],
			],
		],
		[
			'type'     => 'upcoming_events',
			'settings' => [
				'count'         => 5,
				'layout'        => 'cards',
				'show_date'     => 1,
				'show_time'     => 1,
				'show_location' => 1,
				'category_id'   => 0,
			],
		],
		[
			'type'     => 'heading',
			'settings' => [
				'text'     => 'Visit Us',
				'subtitle' => 'We would love to meet you',
			],
		],
		[
			'type'     => 'contact_card',
			'settings' => [],
		],
	];
	update_post_meta( $home_id, '_sp_page_widgets', $widgets );
	WP_CLI::log( '  Homepage configured with ' . count( $widgets ) . ' widgets.' );
}

// ---------------------------------------------------------------------------
// Done
// ---------------------------------------------------------------------------

WP_CLI::success( 'Demo seed complete. Kindred Genealogical Society is ready.' );
