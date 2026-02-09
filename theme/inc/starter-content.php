<?php
/**
 * Starter Content on Theme Activation
 *
 * WHY: A brand-new WordPress install with SocietyPress looks barren — no pages,
 * no menus, just "Hello world!" and an empty site. For our target audience
 * (non-technical society administrators), that's intimidating. This file
 * automatically creates essential pages, menus, and reading settings so the
 * site looks and feels ready to use from the moment the theme is activated.
 *
 * SAFETY: Only runs on fresh installs (≤2 pages, no custom menus). Sets an
 * option flag so it never runs twice, even if the theme is deactivated and
 * reactivated.
 *
 * @package SocietyPress
 * @since 1.38d
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create starter content when the theme is first activated.
 *
 * WHY: Hooked to after_switch_theme so it runs once at activation time.
 * The guard conditions ensure it only fires on genuinely fresh installs
 * and never runs a second time.
 */
function societypress_create_starter_content() {
	// WHY: Option flag prevents this from ever running twice. If someone
	// deactivates and reactivates the theme, their customized content is safe.
	if ( get_option( 'societypress_starter_content_created' ) ) {
		return;
	}

	// WHY: On an existing site with real content, we don't want to inject
	// starter pages. Count published + draft pages — a fresh WP install
	// has at most 2 (the Sample Page and possibly a privacy page).
	$page_count = wp_count_posts( 'page' );
	$total_pages = $page_count->publish + $page_count->draft;
	if ( $total_pages > 2 ) {
		// Existing site with real content — mark as done and bail.
		update_option( 'societypress_starter_content_created', true );
		return;
	}

	// WHY: If custom menus already exist, someone has already been configuring
	// this site and we shouldn't overwrite their work.
	$existing_menus = wp_get_nav_menus();
	if ( ! empty( $existing_menus ) ) {
		update_option( 'societypress_starter_content_created', true );
		return;
	}

	// ─── Create Pages ─────────────────────────────────────────────────
	// WHY: Each page uses the appropriate SocietyPress template so the
	// site is fully functional without any manual template assignment.

	$pages = array(
		'Home' => array(
			'template' => '', // Uses front-page.php automatically.
			'content'  => sprintf(
				'<p>%s</p>',
				esc_html__( 'Welcome to our society! We are a community dedicated to preserving and sharing our heritage. Explore our site to learn about upcoming events, read the latest news, or become a member.', 'societypress' )
			),
		),
		'About Us' => array(
			'template' => 'templates/template-full-width.php',
			'content'  => sprintf(
				"<h2>%s</h2>\n<p>%s</p>\n\n<h2>%s</h2>\n<p>%s</p>\n\n<h2>%s</h2>\n<p>%s</p>",
				esc_html__( 'Our Mission', 'societypress' ),
				esc_html__( 'Our society is dedicated to the collection, preservation, and dissemination of historical and genealogical information. We strive to educate and inspire current and future generations.', 'societypress' ),
				esc_html__( 'Our History', 'societypress' ),
				esc_html__( 'Founded by a group of passionate researchers, our society has grown into a thriving community of members who share a love of history and genealogy. Update this section with your organization\'s founding story and milestones.', 'societypress' ),
				esc_html__( 'Meetings', 'societypress' ),
				esc_html__( 'We hold regular meetings featuring guest speakers, workshops, and research sessions. Check our Events page for the latest schedule.', 'societypress' )
			),
		),
		'Events' => array(
			'template' => 'templates/template-events.php',
			'content'  => sprintf(
				'<p>%s</p>',
				esc_html__( 'Browse our upcoming events, classes, and meetings. Click on any event for full details and registration information.', 'societypress' )
			),
		),
		'Join Us' => array(
			'template' => 'templates/template-join.php',
			'content'  => sprintf(
				'<p>%s</p>',
				esc_html__( 'We\'d love to have you as a member! Fill out the form below to get started. Membership includes access to our research library, events, newsletters, and member directory.', 'societypress' )
			),
		),
		'Contact Us' => array(
			'template' => 'templates/template-contact.php',
			'content'  => sprintf(
				'<p>%s</p>',
				esc_html__( 'Have a question or want to learn more? Reach out to us using the form below, or use the contact information provided.', 'societypress' )
			),
		),
		'News' => array(
			'template' => '', // Set as Posts Page — WordPress handles the rest.
			'content'  => '',
		),
		'Member Portal' => array(
			'template' => 'templates/template-portal.php',
			'content'  => '', // Template handles everything.
		),
		'Member Directory' => array(
			'template' => 'templates/template-directory.php',
			'content'  => '', // Template handles everything.
		),
		'Newsletters' => array(
			'template' => 'templates/template-newsletters.php',
			'content'  => sprintf(
				'<p>%s</p>',
				esc_html__( 'Browse our newsletter archive below. Click on any issue to read it online, or download a PDF copy.', 'societypress' )
			),
		),
	);

	$created_pages = array(); // Stores 'slug' => page_id for menu creation.

	foreach ( $pages as $title => $page_data ) {
		// WHY: Check if a page with this title already exists to avoid duplicates.
		$existing = get_page_by_title( $title, OBJECT, 'page' );
		if ( $existing ) {
			$created_pages[ sanitize_title( $title ) ] = $existing->ID;
			continue;
		}

		$page_args = array(
			'post_title'   => $title,
			'post_content' => $page_data['content'],
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_author'  => get_current_user_id(),
		);

		$page_id = wp_insert_post( $page_args );

		if ( ! is_wp_error( $page_id ) && $page_id > 0 ) {
			// Assign the page template if one is specified.
			if ( ! empty( $page_data['template'] ) ) {
				update_post_meta( $page_id, '_wp_page_template', $page_data['template'] );
			}
			$created_pages[ sanitize_title( $title ) ] = $page_id;
		}
	}

	// ─── Reading Settings ─────────────────────────────────────────────
	// WHY: WordPress defaults to showing latest posts on the homepage. Society
	// sites need a proper static front page with the hero slider and sections.

	if ( isset( $created_pages['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $created_pages['home'] );
	}

	if ( isset( $created_pages['news'] ) ) {
		update_option( 'page_for_posts', $created_pages['news'] );
	}

	// ─── Create Menus ─────────────────────────────────────────────────
	// WHY: Without menus, navigation is empty. These provide sensible defaults
	// that match the theme's registered menu locations.

	// Primary Menu — main site navigation.
	$primary_menu_id = wp_create_nav_menu( __( 'Primary Menu', 'societypress' ) );
	if ( ! is_wp_error( $primary_menu_id ) ) {
		$primary_items = array( 'home', 'about-us', 'events', 'news', 'join-us', 'contact-us' );
		$menu_order = 1;

		foreach ( $primary_items as $slug ) {
			if ( isset( $created_pages[ $slug ] ) ) {
				wp_update_nav_menu_item(
					$primary_menu_id,
					0,
					array(
						'menu-item-title'     => get_the_title( $created_pages[ $slug ] ),
						'menu-item-object'    => 'page',
						'menu-item-object-id' => $created_pages[ $slug ],
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
						'menu-item-position'  => $menu_order,
					)
				);
				$menu_order++;
			}
		}

		// Assign to the primary menu location.
		$locations = get_theme_mod( 'nav_menu_locations', array() );
		$locations['primary'] = $primary_menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	// Footer Menu — smaller set of links for the site footer.
	$footer_menu_id = wp_create_nav_menu( __( 'Footer Menu', 'societypress' ) );
	if ( ! is_wp_error( $footer_menu_id ) ) {
		$footer_items = array( 'about-us', 'contact-us', 'join-us' );
		$menu_order = 1;

		foreach ( $footer_items as $slug ) {
			if ( isset( $created_pages[ $slug ] ) ) {
				wp_update_nav_menu_item(
					$footer_menu_id,
					0,
					array(
						'menu-item-title'     => get_the_title( $created_pages[ $slug ] ),
						'menu-item-object'    => 'page',
						'menu-item-object-id' => $created_pages[ $slug ],
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
						'menu-item-position'  => $menu_order,
					)
				);
				$menu_order++;
			}
		}

		// Assign to the footer menu location.
		$locations = get_theme_mod( 'nav_menu_locations', array() );
		$locations['footer'] = $footer_menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	// ─── Clean Up Default Content ─────────────────────────────────────
	// WHY: WordPress ships with a "Hello world!" post and "Sample Page" that
	// look unprofessional. Replace/remove them only if they're still the
	// unmodified defaults — if someone already edited them, leave them alone.

	// Replace "Hello world!" with a welcome post.
	$hello_world = get_page_by_title( 'Hello world!', OBJECT, 'post' );
	if ( $hello_world && 'Welcome to WordPress. This is your first post. Edit or delete it, then start writing!' === trim( wp_strip_all_tags( $hello_world->post_content ) ) ) {
		$site_name = get_bloginfo( 'name' );
		wp_update_post(
			array(
				'ID'           => $hello_world->ID,
				'post_title'   => sprintf(
					/* translators: %s: Site name */
					__( 'Welcome to %s', 'societypress' ),
					$site_name
				),
				'post_content' => sprintf(
					"<p>%s</p>\n\n<p>%s</p>",
					sprintf(
						/* translators: %s: Site name */
						esc_html__( 'Welcome to %s! We\'re excited to share news, events, and updates with our community.', 'societypress' ),
						esc_html( $site_name )
					),
					esc_html__( 'Stay tuned for upcoming announcements, event recaps, and articles of interest to our members and visitors.', 'societypress' )
				),
			)
		);
	}

	// Delete the default "Sample Page" if it's still untouched.
	$sample_page = get_page_by_title( 'Sample Page', OBJECT, 'page' );
	if ( $sample_page ) {
		$sample_content = trim( wp_strip_all_tags( $sample_page->post_content ) );
		// WHY: WordPress's default Sample Page starts with "This is an example page."
		if ( strpos( $sample_content, 'This is an example page' ) === 0 ) {
			wp_delete_post( $sample_page->ID, true ); // Force delete (bypass trash).
		}
	}

	// ─── Mark Complete ────────────────────────────────────────────────
	// WHY: Set the flag so this never runs again on this site.
	update_option( 'societypress_starter_content_created', true );

	// WHY: Set a transient flag so we can show the one-time admin notice
	// explaining what was created and suggesting next steps.
	set_transient( 'societypress_starter_content_notice', true, 60 * 60 ); // 1 hour.
}
add_action( 'after_switch_theme', 'societypress_create_starter_content' );

/**
 * Display a one-time admin notice after starter content is created.
 *
 * WHY: The admin just activated the theme and a bunch of pages/menus appeared.
 * Without an explanation, that could be confusing. This notice tells them
 * what happened and gives them clear next steps to make the site their own.
 */
function societypress_starter_content_admin_notice() {
	// Only show if the transient flag is set (means content was just created).
	if ( ! get_transient( 'societypress_starter_content_notice' ) ) {
		return;
	}

	// Only show to users who can manage the site.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// WHY: Check if user has already dismissed this notice in this session.
	if ( get_user_meta( get_current_user_id(), 'societypress_starter_notice_dismissed', true ) ) {
		delete_transient( 'societypress_starter_content_notice' );
		return;
	}
	?>
	<div class="notice notice-success is-dismissible societypress-starter-notice">
		<h3 style="margin-top: 0.5em;">
			<?php esc_html_e( 'SocietyPress Theme Activated — Your Site Is Ready!', 'societypress' ); ?>
		</h3>
		<p>
			<?php esc_html_e( 'We\'ve set up your site with starter pages, navigation menus, and homepage settings so you can hit the ground running. Here\'s what was created:', 'societypress' ); ?>
		</p>
		<ul style="list-style: disc; margin-left: 2em;">
			<li><?php esc_html_e( 'Pages: Home, About Us, Events, Join Us, Contact Us, News, Member Portal, Member Directory, Newsletters', 'societypress' ); ?></li>
			<li><?php esc_html_e( 'Primary navigation menu and footer menu', 'societypress' ); ?></li>
			<li><?php esc_html_e( 'Static homepage and news page configured', 'societypress' ); ?></li>
		</ul>
		<p><strong><?php esc_html_e( 'Suggested next steps:', 'societypress' ); ?></strong></p>
		<ol style="margin-left: 2em;">
			<li>
				<a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>">
					<?php esc_html_e( 'Set your logo and site identity', 'societypress' ); ?>
				</a>
			</li>
			<li>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=sp_slide' ) ); ?>">
					<?php esc_html_e( 'Create hero slides for your homepage', 'societypress' ); ?>
				</a>
			</li>
			<li>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>">
					<?php esc_html_e( 'Update page content with your organization\'s info', 'societypress' ); ?>
				</a>
			</li>
			<li>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-settings' ) ); ?>">
					<?php esc_html_e( 'Configure SocietyPress plugin settings', 'societypress' ); ?>
				</a>
			</li>
		</ol>
	</div>
	<?php
}
add_action( 'admin_notices', 'societypress_starter_content_admin_notice' );

/**
 * Handle dismissal of the starter content notice.
 *
 * WHY: Once the admin clicks the dismiss "X" on the notice, we clear the
 * transient so it doesn't show again. WordPress fires this AJAX action
 * automatically for dismissible notices.
 */
function societypress_dismiss_starter_notice() {
	if ( current_user_can( 'manage_options' ) ) {
		delete_transient( 'societypress_starter_content_notice' );
		update_user_meta( get_current_user_id(), 'societypress_starter_notice_dismissed', true );
	}
}
add_action( 'wp_ajax_dismiss_societypress_starter_notice', 'societypress_dismiss_starter_notice' );

/**
 * Enqueue inline script to handle notice dismissal via AJAX.
 *
 * WHY: WordPress's is-dismissible class adds a dismiss button, but doesn't
 * automatically fire an AJAX call. We need a small script to persist the
 * dismissal so the notice doesn't reappear on the next page load.
 */
function societypress_starter_notice_script() {
	// Only load when the notice transient exists and user can manage options.
	if ( ! get_transient( 'societypress_starter_content_notice' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<script>
	jQuery(document).ready(function($) {
		$(document).on('click', '.societypress-starter-notice .notice-dismiss', function() {
			$.post(ajaxurl, {
				action: 'dismiss_societypress_starter_notice'
			});
		});
	});
	</script>
	<?php
}
add_action( 'admin_footer', 'societypress_starter_notice_script' );
