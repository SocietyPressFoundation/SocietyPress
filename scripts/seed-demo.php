<?php
/**
 * SocietyPress Demo Seed Script
 *
 * Populates the demo site (demo.getsocietypress.org) with realistic sample data
 * for "Heritage Valley Historical Society" — a fictional mid-size genealogical
 * and historical society.
 *
 * Run via WP-CLI after reset-demo.sh truncates all SP tables:
 *   wp eval-file /path/to/seed-demo.php
 *
 * This script is idempotent — it checks for empty tables before inserting.
 * Events use dates relative to today so they never go stale.
 *
 * Column names verified against actual database schemas 2026-04-10.
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
// 1. SETTINGS — Heritage Valley Historical Society
// ---------------------------------------------------------------------------

WP_CLI::log( 'Configuring settings...' );

$settings = get_option( 'societypress_settings', [] );
$settings = array_merge( $settings, [
	// Organization
	'organization_name'       => 'Heritage Valley Historical Society',
	'organization_address'    => "450 Main Street\nHeritage Valley, OH 43001",
	'organization_phone'      => '(740) 555-0192',
	'organization_email'      => 'info@heritagevalleyhistorical.org',

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
	'homepage_hero_headline'       => 'Heritage Valley Historical Society',
	'homepage_hero_subtitle'       => 'Preserving Our Past. Connecting Our Present.',
	'homepage_hero_cta_text'       => 'Upcoming Events',
	'homepage_hero_cta_url'        => '/events/',
	'homepage_hero_overlay'        => 40,

	// Email
	'email_from_name'              => 'Heritage Valley Historical Society',
	'email_from_email'             => 'info@heritagevalleyhistorical.org',
	'welcome_email_enabled'        => 1,
	'welcome_email_subject'        => 'Welcome to Heritage Valley Historical Society!',

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

// Enable all modules — must be a simple indexed array of slug strings,
// NOT an associative array. sp_module_enabled() uses in_array() on values.
update_option( 'sp_enabled_modules', [
	'members', 'events', 'library', 'newsletters', 'resources', 'governance',
	'store', 'records', 'donations', 'blast_email', 'gallery', 'help_requests',
	'documents', 'voting',
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
// 3. MEMBERS — 250 members (each needs a WP user + sp_members row)
// ---------------------------------------------------------------------------

WP_CLI::log( 'Creating members...' );

$member_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}members" );
if ( $member_count > 0 ) {
	WP_CLI::log( '  Members already exist, skipping.' );
} else {
	$first_m = ['James','John','Robert','William','David','Richard','Thomas','Charles','Michael','Daniel','Joseph','Edward','George','Frank','Harold','Donald','Arthur','Walter','Raymond','Kenneth','Gerald','Carl','Roy','Lawrence','Peter','Ralph','Henry','Howard','Jack','Eugene','Patrick','Nathan','Timothy','Russell','Dennis','Kevin','Gregory','Larry','Roger','Mark','Stephen','Scott','Craig','Alan','Bruce','Wayne','Dale','Jerome','Curtis','Vernon','Ross','Clayton','Vance','Luther','Harvey','Willis','Chester','Otis','Marshall','Floyd','Dwight','Lester','Percy','Horace','Milo','Byron','Murray','Nelson','Stuart','Miles','Glenn','Darrell','Ernie','Dean','Clyde','Norman','Glen'];
	$first_f = ['Mary','Patricia','Linda','Barbara','Elizabeth','Jennifer','Susan','Harold','Dorothy','Lisa','Nancy','Karen','Helen','Sandra','Donna','Carol','Ruth','Sharon','Michelle','Laura','Sarah','Kimberly','Deborah','Jessica','Shirley','Cynthia','Angela','Melissa','Brenda','Amy','Anna','Jean','Martha','Teresa','Ann','Marie','Frances','Janet','Catherine','Alice','Virginia','Judy','Grace','Betty','Evelyn','Joan','Janice','Christine','Kathleen','Diane','Bonnie','Gloria','Irene','Joyce','Lillian','Rose','Mabel','Ruby','Lucille','Hazel','Vivian','Doris','Gladys','Mildred','Edna','Florence','Pearl','Ella','Lois','Agnes','Bernice','Velma','Ethel'];
	$lasts = ['Anderson','Baker','Campbell','Clark','Collins','Cooper','Davis','Edwards','Evans','Fisher','Foster','Garcia','Gordon','Graham','Green','Hall','Harris','Harrison','Hayes','Henderson','Hill','Howard','Hughes','Jackson','Jenkins','Johnson','Jones','Kelly','Kennedy','King','Lee','Lewis','Martin','Martinez','Miller','Mitchell','Moore','Morgan','Murphy','Nelson','Parker','Patterson','Perry','Phillips','Powell','Price','Reed','Richardson','Roberts','Robinson','Rodriguez','Rogers','Ross','Russell','Sanders','Scott','Shaw','Simpson','Smith','Spencer','Stewart','Sullivan','Taylor','Thomas','Thompson','Turner','Walker','Wallace','Walsh','Ward','Watson','White','Williams','Wilson','Wood','Wright','Young'];

	$statuses = array_merge(
		array_fill( 0, 178, 'active' ),
		array_fill( 0, 35, 'expired' ),
		array_fill( 0, 20, 'lapsed' ),
		array_fill( 0, 10, 'pending' ),
		array_fill( 0, 5, 'active' ),   // these will be lifetime
		array_fill( 0, 2, 'deceased' )
	);
	shuffle( $statuses );

	$cities = ['Heritage Valley','Oakfield','Millbrook','Cedar Falls','Riverside','Maple Grove','Fairview','Greenville','Springfield','Lincoln','Marion','Hartford','Preston','Granville','Westport','Carlisle','Ashland','Brookfield','Thornton','Waverly'];
	$streets = ['Main','Oak','Elm','Maple','Cedar','Pine','Church','Mill','River','High','Washington','Market','Broad','Spring','Park'];
	$street_types = ['St','Ave','Rd','Dr','Ln','Ct'];
	$zips = ['43001','43002','43003','43004','43005','43006','43007','43008','43009','43010'];

	$created = 0;
	$now = current_time( 'mysql' );

	for ( $i = 0; $i < 250; $i++ ) {
		$sex    = ( $i % 3 === 0 ) ? 'F' : 'M';
		$fname  = $sex === 'M' ? $first_m[ $i % count( $first_m ) ] : $first_f[ $i % count( $first_f ) ];
		$lname  = $lasts[ $i % count( $lasts ) ];
		$status = $statuses[ $i ];
		$city   = $cities[ $i % count( $cities ) ];
		$zip    = $zips[ $i % count( $zips ) ];

		// Tier
		$is_lifetime = ( $i >= 243 && $i < 248 );
		$is_honorary = ( $i >= 248 );
		if ( $is_lifetime ) {
			$tier_id = $tier_ids['Life Member'] ?? 4;
			$status  = 'active';
		} elseif ( $is_honorary ) {
			$tier_id = $tier_ids['Honorary'] ?? 5;
			$status  = 'active';
		} elseif ( $i % 7 === 0 ) {
			$tier_id = $tier_ids['Family'] ?? 2;
		} elseif ( $i % 11 === 0 ) {
			$tier_id = $tier_ids['Student'] ?? 3;
		} else {
			$tier_id = $tier_ids['Individual'] ?? 1;
		}

		$join_days_ago = rand( 30, 5475 );
		$join_date     = seed_past_date( $join_days_ago );

		if ( $status === 'active' && ! $is_lifetime && ! $is_honorary ) {
			$exp_date = seed_date_relative( rand( 30, 300 ) );
		} elseif ( $status === 'expired' || $status === 'lapsed' ) {
			$exp_date = seed_past_date( rand( 10, 365 ) );
		} elseif ( $status === 'deceased' ) {
			$exp_date = seed_past_date( rand( 100, 1000 ) );
		} else {
			$exp_date = seed_date_relative( rand( 30, 365 ) );
		}

		if ( $is_lifetime || $is_honorary ) {
			$exp_date = null;
		}

		$email    = strtolower( $fname . '.' . $lname . $i . '@example.com' );
		$username = strtolower( $fname . '.' . $lname . $i );

		// Create WP user (subscriber role)
		$user_id = wp_insert_user( [
			'user_login' => $username,
			'user_email' => $email,
			'user_pass'  => wp_generate_password( 16 ),
			'first_name' => $fname,
			'last_name'  => $lname,
			'role'       => 'subscriber',
		] );

		if ( is_wp_error( $user_id ) ) {
			continue; // skip duplicates
		}

		$is_deceased = ( $status === 'deceased' ) ? 1 : 0;
		if ( $is_deceased ) {
			$status = 'active'; // deceased is a flag, not a status
		}

		$wpdb->insert( "{$prefix}members", [
			'user_id'          => $user_id,
			'first_name'       => $fname,
			'last_name'        => $lname,
			'phone'            => sprintf( '(740) 555-%04d', rand( 1000, 9999 ) ),
			'address_1'        => rand( 100, 9999 ) . ' ' . $streets[ rand( 0, count( $streets ) - 1 ) ] . ' ' . $street_types[ rand( 0, 5 ) ],
			'city'             => $city,
			'state'            => 'OH',
			'postal_code'      => $zip,
			'country'          => 'US',
			'tier_id'          => $tier_id,
			'status'           => $status,
			'join_date'        => $join_date,
			'expiration_date'  => $exp_date,
			'deceased'         => $is_deceased,
			'lifetime'         => ( $is_lifetime ) ? 1 : 0,
			'gender'           => $sex === 'M' ? 'male' : 'female',
			'dir_show_name'    => 1,
			'dir_show_address' => 1,
			'dir_show_email'   => 1,
			'dir_show_phone'   => 0,
			'created_at'       => $now,
			'updated_at'       => $now,
		] );
		$created++;
	}
	WP_CLI::log( "  Created $created members (with WP user accounts)." );
}

// ---------------------------------------------------------------------------
// 4. EVENTS — relative to today
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
		[ 'Heritage Valley Cemetery Walk', 'Join us for a guided walk through Pioneer Rest Cemetery. Our docents share stories of the founders, merchants, and families who shaped Heritage Valley.', -21, 10, 0, 12, 0, 'Pioneer Rest Cemetery', 'County Road 12, Heritage Valley, OH', 30, 'public', 'field trip' ],
		[ 'Monthly Meeting: DNA Testing for Genealogists', 'Guest speaker Dr. Rebecca Foster discusses how autosomal DNA testing can break through brick walls in your research. Q&A to follow.', -14, 19, 0, 21, 0, 'Heritage Valley Community Center', '450 Main St, Heritage Valley, OH', 60, 'public', 'meeting' ],
		[ 'Beginner Genealogy Workshop', 'New to family history? This hands-on workshop covers vital records, census data, online databases, and organizing your findings.', -7, 13, 0, 16, 0, 'Heritage Valley Public Library', '200 Elm St, Heritage Valley, OH', 20, 'public', 'workshop' ],
		// Upcoming events
		[ 'Monthly Meeting: Mapping the Underground Railroad in Ohio', 'Local historian Marcus Thompson presents his research on Underground Railroad routes through our county, including newly discovered safe houses.', 3, 19, 0, 21, 0, 'Heritage Valley Community Center', '450 Main St, Heritage Valley, OH', 60, 'public', 'meeting' ],
		[ 'Courthouse Records Research Day', 'Spend the morning at the county courthouse learning to navigate deed books, probate records, and marriage indexes with archivist Janet Mills.', 10, 9, 30, 12, 30, 'Heritage County Courthouse', '100 Court Square, Heritage Valley, OH', 15, 'members_only', 'workshop' ],
		[ 'Heritage Valley Founders Day Celebration', 'Annual celebration of Heritage Valley\'s founding in 1832. Period demonstrations, historical displays, and dedication of a new historical marker.', 17, 10, 0, 16, 0, 'Heritage Valley Town Square', 'Main St & Market St, Heritage Valley, OH', 200, 'public', 'social' ],
		[ 'Using FamilySearch: Tips and Tricks', 'Learn advanced search techniques, the FamilySearch catalog, and the wiki. Bring a laptop.', 24, 14, 0, 16, 30, 'Heritage Valley Public Library', '200 Elm St, Heritage Valley, OH', 25, 'public', 'workshop' ],
		[ 'Board of Directors Meeting', 'Regular quarterly board meeting. Agenda includes budget review, membership report, and fall lecture series planning.', 28, 18, 30, 20, 0, 'HVHS Office', '450 Main St Suite 2, Heritage Valley, OH', 20, 'members_only', 'meeting' ],
		[ 'Summer Potluck and Show & Tell', 'Bring a dish to share and your most interesting genealogical find! Members share photos, heirlooms, and research breakthroughs.', 38, 17, 0, 20, 0, 'Riverside Park Pavilion', 'River Rd, Heritage Valley, OH', 80, 'members_only', 'social' ],
		[ 'Preserving Family Photographs', 'Conservator Sarah Quinn demonstrates storage, scanning, and restoration of old photos. Learn to identify and date daguerreotypes, tintypes, and cabinet cards.', 45, 10, 0, 12, 0, 'Heritage Valley Public Library', '200 Elm St, Heritage Valley, OH', 25, 'public', 'workshop' ],
		[ 'Monthly Meeting: Civil War Soldiers of Heritage County', 'Author Robert Caldwell presents his new book documenting every Heritage County man who served. Signed copies available.', 52, 19, 0, 21, 0, 'Heritage Valley Community Center', '450 Main St, Heritage Valley, OH', 60, 'public', 'meeting' ],
		[ 'Historic Homes Tour', 'Walking tour of Heritage Valley\'s most significant 19th-century homes, led by architectural historian Dr. Karen Whitfield. Includes three private residences.', 60, 13, 0, 16, 0, 'Heritage Valley Town Hall', '300 Main St, Heritage Valley, OH', 25, 'public', 'field trip' ],
		[ 'Fall Lecture: Immigration Through Ohio', 'Professor Margaret Koehler traces major immigration waves through Ohio from the early 1800s through 1920 — German, Irish, Welsh, and Eastern European settlers.', 75, 19, 0, 21, 0, 'Heritage Valley Community Center', '450 Main St, Heritage Valley, OH', 60, 'public', 'meeting' ],
		[ 'Indexing Party: 1880 Census', 'Help us transcribe the 1880 federal census for Heritage County. No experience needed. Pizza and drinks provided.', 85, 10, 0, 15, 0, 'Heritage Valley Public Library', '200 Elm St, Heritage Valley, OH', 20, 'members_only', 'workshop' ],
		[ 'Annual Holiday Open House', 'End-of-year celebration with hot cider, cookies, year-in-review slideshow, and election of officers. Bring a friend!', 100, 14, 0, 17, 0, 'Heritage Valley Community Center', '450 Main St, Heritage Valley, OH', 100, 'public', 'social' ],
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
// 5. LIBRARY CATALOG — 35 items
// ---------------------------------------------------------------------------

WP_CLI::log( 'Creating library catalog...' );

$lib_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}library_items" );
if ( $lib_count > 0 ) {
	WP_CLI::log( '  Library items already exist, skipping.' );
} else {
	// title, author, publisher, pub_year (int), call_number, media_type, acq_code, description
	$lib_items = [
		[ 'Heritage County: A History 1800-1900', 'Edward Thornton', 'Heritage Valley Press', 1965, '977.1 THO', 'Book', 'Gift', 'Comprehensive county history from settlement through the turn of the century.' ],
		[ 'Pioneers of the Valley', 'Martha Collins', 'Heritage Valley Historical Society', 1978, '929.3 COL', 'Book', 'HVHS Publication', 'Biographical sketches of 200+ pioneer families who settled before 1850.' ],
		[ 'Heritage County Cemeteries, Vol. 1', 'HVHS Cemetery Committee', 'Heritage Valley Historical Society', 1985, '929.5 HVH', 'Book', 'HVHS Publication', 'Tombstone inscriptions from 12 cemeteries in the northern half of the county.' ],
		[ 'Heritage County Cemeteries, Vol. 2', 'HVHS Cemetery Committee', 'Heritage Valley Historical Society', 1987, '929.5 HVH', 'Book', 'HVHS Publication', 'Tombstone inscriptions from 14 cemeteries in the southern half of the county.' ],
		[ 'Civil War Soldiers of Heritage County', 'Robert Caldwell', 'Ohio University Press', 2024, '973.7 CAL', 'Book', 'Purchase', 'Complete roster with unit histories and biographical data.' ],
		[ 'The Underground Railroad in Ohio', 'Marcus Thompson', 'Kent State University Press', 2019, '973.7 THO', 'Book', 'Purchase', 'Documented safe houses and routes with primary source materials.' ],
		[ 'German Settlers of Central Ohio', 'Hans Mueller', 'German Heritage Society', 1992, '929.3 MUE', 'Book', 'Donation', 'Immigration patterns, church records, and family histories.' ],
		[ 'Ohio Land Grants and Patents', 'James Harrison', 'Ohio Historical Society', 1970, '333.1 HAR', 'Book', 'Gift', 'Guide to understanding Ohio land records.' ],
		[ 'Tracing Your Ohio Ancestors', 'Carol Jenkins', 'Genealogical Publishing Co.', 2001, '929.1 JEN', 'Book', 'Purchase', 'County-by-county guide to genealogical resources in Ohio.' ],
		[ 'Heritage County Marriage Records 1830-1880', 'HVHS Records Committee', 'Heritage Valley Historical Society', 1990, '929.3 HVH', 'Book', 'HVHS Publication', 'Index of all marriage records filed at the courthouse.' ],
		[ 'Heritage County Birth and Death Records 1867-1908', 'HVHS Records Committee', 'Heritage Valley Historical Society', 1993, '929.3 HVH', 'Book', 'HVHS Publication', 'Transcribed vital records from the county health department.' ],
		[ 'One-Room Schoolhouses of Heritage County', 'Virginia Patterson', 'Heritage Valley Press', 2003, '371.01 PAT', 'Book', 'Gift', 'History and photographs of 47 one-room schools with teacher and student lists.' ],
		[ 'The Welsh in Ohio', 'David Evans', 'University of Wales Press', 1988, '929.3 EVA', 'Book', 'Gift', 'Welsh immigration to Ohio, particularly slate quarry and coal mining communities.' ],
		[ 'Heritage County Atlas 1875', '', 'Heritage County Surveyor', 1875, '912 HER', 'Book', 'Gift', 'Reproduction of the original 1875 county atlas with township maps.' ],
		[ 'Heritage County Atlas 1905', '', 'Heritage County Surveyor', 1905, '912 HER', 'Book', 'Gift', 'Reproduction with farms, villages, and railroad lines.' ],
		[ 'Churches of Heritage Valley', 'Ruth Anderson', 'Heritage Valley Historical Society', 1995, '277.71 AND', 'Book', 'HVHS Publication', 'History of 30+ churches with founding dates and early membership rolls.' ],
		[ 'Indian Trails to Turnpikes', 'Frank Mitchell', 'Heritage Valley Press', 1958, '388.1 MIT', 'Book', 'Gift', 'Transportation history: Native American paths, canals, and railroads.' ],
		[ 'The Mills of Heritage Valley', 'George Foster', 'Heritage Valley Historical Society', 2008, '338.4 FOS', 'Book', 'HVHS Publication', 'Grist mills, saw mills, and woolen mills of early Heritage Valley.' ],
		// Periodicals
		[ 'Heritage Valley Quarterly, 1975-1985', 'HVHS', 'Heritage Valley Historical Society', 1975, 'PERIODICAL', 'Periodical', 'HVHS Publication', 'Bound newsletter volumes 1-10.' ],
		[ 'Heritage Valley Quarterly, 1986-1995', 'HVHS', 'Heritage Valley Historical Society', 1986, 'PERIODICAL', 'Periodical', 'HVHS Publication', 'Bound newsletter volumes 11-20.' ],
		[ 'Heritage Valley Quarterly, 1996-2005', 'HVHS', 'Heritage Valley Historical Society', 1996, 'PERIODICAL', 'Periodical', 'HVHS Publication', 'Bound newsletter volumes 21-30.' ],
		[ 'Ohio Genealogical Society Quarterly, 1960-1980', 'OGS', 'Ohio Genealogical Society', 1960, 'PERIODICAL', 'Periodical', 'Gift', 'Complete run of the state society journal.' ],
		// Maps
		[ 'Heritage County Township Map 1840', '', 'Ohio General Land Office', 1840, 'MAP-001', 'Map', 'Gift', 'Early township map showing original land grant boundaries.' ],
		[ 'Heritage County Road Map 1900', '', 'Heritage County Engineer', 1900, 'MAP-002', 'Map', 'Gift', 'Detailed road map showing farms, schools, and churches.' ],
		[ 'Sanborn Fire Insurance Map: Heritage Valley 1892', '', 'Sanborn Map Company', 1892, 'MAP-003', 'Map', 'Purchase', 'Building-by-building map of Heritage Valley business district.' ],
		[ 'USGS Topo: Heritage Valley Quad 1955', '', 'U.S. Geological Survey', 1955, 'MAP-004', 'Map', 'Gift', '7.5 minute quadrangle showing terrain, roads, and structures.' ],
		// Vertical files
		[ 'Anderson Family File', '', '', null, 'VF-AND', 'Vertical File', 'Gift', 'Clippings, correspondence, and notes on the Anderson family.' ],
		[ 'Baker Family File', '', '', null, 'VF-BAK', 'Vertical File', 'Donation', 'Family group sheets, obituaries, and photos.' ],
		[ 'Heritage Valley Business District History', '', '', null, 'VF-BUS', 'Vertical File', 'HVHS Publication', 'Research file on Main Street businesses, 1850-1950.' ],
		[ 'Heritage County Schools', '', '', null, 'VF-SCH', 'Vertical File', 'Gift', 'Lists of teachers and students from county school records.' ],
		[ 'Heritage Valley Newspapers Index', '', '', null, 'VF-NEWS', 'Vertical File', 'HVHS Publication', 'Card index to births, deaths, and marriages, 1870-1920.' ],
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
// 6. RESOURCE LINKS
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
		$cats = ['National Databases','Ohio Resources','Census & Vital Records','Military Records','Immigration','DNA & Genetics','Newspapers','Education & How-To'];
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
		[ 'Ohio History Connection', 'https://www.ohiohistory.org', 'State historical society with archives and online collections.', 'ohio resources' ],
		[ 'Ohio Genealogical Society', 'https://www.ogs.org', 'State genealogical society with county chapter network.', 'ohio resources' ],
		[ 'Ohio Memory', 'https://ohiomemory.org', 'Digital collections from Ohio libraries, archives, and museums.', 'ohio resources' ],
		[ 'Steve Morse One-Step Tools', 'https://stevemorse.org', 'Powerful search tools for census, Ellis Island, and more.', 'census & vital records' ],
		[ 'Fold3', 'https://www.fold3.com', 'Military records: pension files, service records, casualty lists.', 'military records' ],
		[ 'NPS Civil War Soldiers', 'https://www.nps.gov/civilwar/search-soldiers.htm', 'Searchable database of Civil War service records.', 'military records' ],
		[ 'Ellis Island', 'https://www.libertyellisfoundation.org', 'Ship manifest records for Port of New York arrivals.', 'immigration' ],
		[ 'Castle Garden', 'https://www.castlegarden.org', 'Pre-Ellis Island immigration records (1820-1892).', 'immigration' ],
		[ 'ISOGG Wiki', 'https://isogg.org/wiki/', 'Comprehensive DNA testing guide for genealogists.', 'dna & genetics' ],
		[ 'DNA Painter', 'https://dnapainter.com', 'Visual chromosome mapping tool for genetic genealogy.', 'dna & genetics' ],
		[ 'Chronicling America', 'https://chroniclingamerica.loc.gov', 'Library of Congress historic newspaper archive. Free.', 'newspapers' ],
		[ 'Newspapers.com', 'https://www.newspapers.com', 'Large newspaper archive (subscription). Many Ohio papers.', 'newspapers' ],
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
// 7. NEWSLETTERS — 12 quarterly issues
// ---------------------------------------------------------------------------

WP_CLI::log( 'Creating newsletters...' );

$nl_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}newsletters" );
if ( $nl_count > 0 ) {
	WP_CLI::log( '  Newsletters already exist, skipping.' );
} else {
	$newsletters = [
		[ 'Heritage Valley Quarterly — Winter 2026', seed_past_date( 90 ),  31, 1, 'Cemetery indexing project update, DNA testing workshop recap, new book acquisitions.' ],
		[ 'Heritage Valley Quarterly — Fall 2025',   seed_past_date( 180 ), 30, 4, 'Annual meeting minutes, treasurer\'s report, fall lecture series schedule.' ],
		[ 'Heritage Valley Quarterly — Summer 2025', seed_past_date( 270 ), 30, 3, 'Founders Day recap, new members welcome, courthouse fire recovery update.' ],
		[ 'Heritage Valley Quarterly — Spring 2025', seed_past_date( 360 ), 30, 2, 'Spring workshop series, library catalog now online, membership drive.' ],
		[ 'Heritage Valley Quarterly — Winter 2025', seed_past_date( 450 ), 30, 1, 'Year in review, officer elections, holiday open house photos.' ],
		[ 'Heritage Valley Quarterly — Fall 2024',   seed_past_date( 540 ), 29, 4, 'Civil War sesquicentennial events, Welsh heritage month.' ],
		[ 'Heritage Valley Quarterly — Summer 2024', seed_past_date( 630 ), 29, 3, 'Summer picnic photos, research trip to Columbus archives.' ],
		[ 'Heritage Valley Quarterly — Spring 2024', seed_past_date( 720 ), 29, 2, 'Genealogy workshop for beginners, new vertical files, volunteer spotlight.' ],
		[ 'Heritage Valley Quarterly — Winter 2024', seed_past_date( 810 ), 29, 1, 'Annual report, budget summary, digitization project plans.' ],
		[ 'Heritage Valley Quarterly — Fall 2023',   seed_past_date( 900 ), 28, 4, 'Guest speaker series, Ohio History Day results.' ],
		[ 'Heritage Valley Quarterly — Summer 2023', seed_past_date( 990 ), 28, 3, 'Heritage Valley Bicentennial planning, oral history launch.' ],
		[ 'Heritage Valley Quarterly — Spring 2023', seed_past_date( 1080), 28, 2, 'Immigration records workshop, new map acquisitions.' ],
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
	$donors  = ['Harold Wilson','Robert Harrison','Anonymous','Heritage Valley Garden Club','Estate of James Foster','Patricia Edwards','Thomas Mitchell','Anonymous','David Clark','Susan Phillips','The Thornton Family','Helen Campbell','Anonymous','Ronald Baker','Janet Morgan','Anonymous','George Spencer','Ruth Anderson','William Price','Carol Jenkins','The Heritage Foundation','Frank Mitchell','Anonymous','Dorothy Collins','Kenneth Walker'];
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
		[ 'Cemetery Committee', 'Indexes and preserves Heritage County cemeteries. Conducts annual cemetery walks.' ],
		[ 'Library Committee', 'Manages the society library collection. Handles acquisitions, cataloging, and open hours.' ],
		[ 'Programs Committee', 'Plans monthly meetings, guest speakers, and the annual lecture series.' ],
		[ 'Membership Committee', 'Recruits new members, manages renewals, and welcomes new joiners.' ],
		[ 'Publications Committee', 'Produces the Heritage Valley Quarterly newsletter and special publications.' ],
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
		[ 'cemetery-burial-index.csv',      'Heritage County Cemetery Index',        'cemetery',     'Tombstone inscriptions from all known cemeteries.',              '1790-2000', 'Heritage County, Ohio' ],
		[ 'census-transcriptions.csv',      'Heritage County Census Transcriptions', 'census',       'Transcribed federal census records.',                            '1850-1910', 'Heritage County, Ohio' ],
		[ 'church-records.csv',             'Heritage Valley Church Records',        'church',       'Baptisms, marriages, burials from area churches.',               '1731-1940', 'Heritage Valley, Ohio' ],
		[ 'obituary-index.csv',             'Heritage County Obituary Index',        'obituary',     'Index to obituaries in Heritage County newspapers.',             '1870-1970', 'Heritage County, Ohio' ],
		[ 'marriage-records.csv',           'Heritage County Marriage Records',      'marriage',     'Marriage records from the Heritage County Courthouse.',          '1837-1950', 'Heritage County, Ohio' ],
		[ 'vital-records.csv',              'Heritage County Vital Records',         'vital',        'Birth and death records.',                                       '1870-1960', 'Heritage County, Ohio' ],
		[ 'military-records.csv',           'Heritage County Military Records',      'military',     'Service records from the Texas Revolution through WWII.',        '1835-1945', 'Heritage County, Ohio' ],
		[ 'land-deed-records.csv',          'Heritage County Land & Deed Records',   'land',         'Land transfers, grants, and deeds.',                             '1731-1920', 'Heritage County, Ohio' ],
		[ 'probate-estate-records.csv',     'Heritage County Probate Records',       'probate',      'Wills, inventories, and estate records.',                        '1837-1950', 'Heritage County, Ohio' ],
		[ 'immigration-naturalization.csv',  'Immigration & Naturalization Records', 'immigration',  'Naturalization papers and immigration records.',                 '1840-1930', 'Heritage County, Ohio' ],
		[ 'newspaper-abstracts.csv',        'Heritage County Newspaper Abstracts',   'newspaper',    'Abstracts of genealogically significant newspaper items.',       '1848-1940', 'Heritage County, Ohio' ],
		[ 'tax-lists.csv',                  'Heritage County Tax Lists',             'tax',          'Annual tax assessment records for property owners.',              '1837-1910', 'Heritage County, Ohio' ],
	];

	foreach ( $collections as $coll ) {
		$csv_file = $csv_dir . $coll[0];

		$wpdb->insert( "{$prefix}record_collections", [
			'name'         => $coll[1],
			'slug'         => sanitize_title( $coll[1] ),
			'description'  => $coll[3],
			'record_type'  => $coll[2],
			'source_info'  => 'Heritage Valley Historical Society Research Collection',
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
			'phone'          => '(740) 555-0192',
			'address_1'      => '450 Main Street',
			'city'           => 'Heritage Valley',
			'state'          => 'OH',
			'postal_code'    => '43001',
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
				'content' => '<h2 style="text-align:center;">Welcome to Heritage Valley Historical Society</h2>'
				           . '<p style="text-align:center;font-size:1.1em;">Founded in 1962, we are dedicated to preserving and sharing the rich history of Heritage Valley and Heritage County, Ohio. Whether you are tracing your family roots, exploring local history, or looking for a community of fellow researchers, you have come to the right place.</p>',
			],
		],
		[
			'type'     => 'feature_cards',
			'settings' => [
				'columns' => 3,
				'cards'   => [
					[
						'title'       => 'Library & Archives',
						'description' => 'Books, maps, periodicals, and vertical files covering Heritage County history and genealogy.',
						'btn_text'    => 'Browse Catalog',
						'btn_url'     => '/library/',
						'image_id'    => 0,
					],
					[
						'title'       => 'Genealogical Records',
						'description' => 'Search 2,100+ cemetery, census, marriage, military, and land records for Heritage County.',
						'btn_text'    => 'Search Records',
						'btn_url'     => '/records/',
						'image_id'    => 0,
					],
					[
						'title'       => 'Join Our Society',
						'description' => 'Join a vibrant community of genealogists and history enthusiasts. Individual memberships from $30/year.',
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

WP_CLI::success( 'Demo seed complete. Heritage Valley Historical Society is ready.' );
