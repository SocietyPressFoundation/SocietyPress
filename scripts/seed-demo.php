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

		$imported = 0;
		while ( ( $row = fgetcsv( $fh ) ) !== false ) {
			$search_parts = [];
			$wpdb->insert( "{$prefix}records", [
				'collection_id' => $collection_id,
				'search_text'   => '',
				'created_at'    => current_time( 'mysql' ),
				'updated_at'    => current_time( 'mysql' ),
			] );
			$record_id = $wpdb->insert_id;

			foreach ( $row as $col_idx => $value ) {
				if ( ! isset( $field_ids[ $col_idx ] ) ) continue;
				$value = trim( $value );
				if ( $value === '' ) continue;
				$wpdb->insert( "{$prefix}record_values", [
					'record_id'   => $record_id,
					'field_id'    => $field_ids[ $col_idx ],
					'field_value' => $value,
				] );
				$search_parts[] = $value;
			}

			$wpdb->update( "{$prefix}records", [ 'search_text' => implode( ' ', $search_parts ) ], [ 'id' => $record_id ] );
			$imported++;
		}
		fclose( $fh );

		$wpdb->update( "{$prefix}record_collections", [ 'record_count' => $imported ], [ 'id' => $collection_id ] );
		WP_CLI::log( "  {$coll[1]}: $imported records." );
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
