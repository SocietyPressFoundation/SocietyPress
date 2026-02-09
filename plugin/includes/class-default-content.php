<?php
/**
 * Default Content Setup
 *
 * Creates starter pages and cleans up WordPress defaults on first activation.
 *
 * WHY: When a society installs SocietyPress for the first time, they shouldn't
 *      have to stare at "Hello World!" and figure out what pages they need.
 *      This gives them a ready-made site structure with guided placeholder
 *      content they can fill in. Octogenarians who failed Computer 101
 *      should open their new site and think "oh, I just need to fill this in"
 *      instead of "where do I even start?"
 *
 * SAFETY: Only runs once per installation. An option flag
 *         ('societypress_default_content_created') prevents it from running
 *         again on reactivation, upgrades, or migrations.
 *
 * @package SocietyPress
 * @since 0.60d
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SocietyPress_Default_Content
 *
 * Handles one-time creation of starter pages and cleanup of WordPress defaults.
 */
class SocietyPress_Default_Content {

	/**
	 * Option name used to track whether default content has already been created.
	 *
	 * WHY: We only want this to run once — on the very first activation.
	 *      Reactivating the plugin, updating it, or migrating the site should
	 *      NOT re-create content the admin may have already customized or deleted.
	 */
	const OPTION_KEY = 'societypress_default_content_created';

	/**
	 * Create default content if it hasn't been created yet.
	 *
	 * Called from the plugin's activate() method. Checks the option flag
	 * first — if default content was already created in a previous activation,
	 * this is a no-op.
	 */
	public static function maybe_create(): void {
		// Already ran once before — don't touch anything
		if ( get_option( self::OPTION_KEY ) ) {
			return;
		}

		self::remove_wordpress_defaults();
		$page_ids = self::create_default_pages();
		self::set_static_front_page( $page_ids );

		// Mark as done so we never run again
		update_option( self::OPTION_KEY, current_time( 'mysql' ), false );
	}

	/**
	 * Remove the default WordPress starter content.
	 *
	 * WHY: "Hello World!", "Sample Page", and "Hi, this is a comment" are
	 *      useless for a society website. Leaving them in just confuses
	 *      non-technical admins who wonder why there's a blog post they
	 *      didn't write.
	 *
	 * Only deletes content with the exact default IDs (1, 2, 3) and only
	 * if the titles still match the WordPress defaults — so if an admin
	 * somehow already repurposed post #1, we leave it alone.
	 */
	private static function remove_wordpress_defaults(): void {
		// Delete "Hello world!" post (ID 1) if it's still the default
		$hello_world = get_post( 1 );
		if ( $hello_world && 'Hello world!' === $hello_world->post_title ) {
			wp_delete_post( 1, true );
		}

		// Delete "Sample Page" (ID 2) if it's still the default
		$sample_page = get_post( 2 );
		if ( $sample_page && 'Sample Page' === $sample_page->post_title ) {
			wp_delete_post( 2, true );
		}

		// Delete the default comment on post 1
		$default_comment = get_comment( 1 );
		if ( $default_comment && (int) $default_comment->comment_post_ID === 1 ) {
			wp_delete_comment( 1, true );
		}
	}

	/**
	 * Create the starter pages for a society website.
	 *
	 * WHY: Every society website needs roughly the same set of pages.
	 *      Rather than making admins create them from scratch (and wonder
	 *      what pages they even need), we pre-create them with helpful
	 *      placeholder content that guides them on what to fill in.
	 *
	 * Each page includes:
	 * - A clear heading
	 * - Placeholder text explaining what content to put there
	 * - Working shortcodes and links where appropriate
	 * - Gutenberg block markup for proper formatting
	 *
	 * @return array Associative array of slug => post ID for the created pages.
	 */
	private static function create_default_pages(): array {
		$pages = self::get_page_definitions();
		$created = array();

		foreach ( $pages as $slug => $page ) {
			// Skip if a page with this slug already exists (someone created it manually)
			$existing = get_page_by_path( $slug );
			if ( $existing ) {
				$created[ $slug ] = $existing->ID;
				continue;
			}

			$post_id = wp_insert_post( array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $page['title'],
				'post_name'    => $slug,
				'post_content' => $page['content'],
				'menu_order'   => $page['order'],
			) );

			if ( ! is_wp_error( $post_id ) ) {
				$created[ $slug ] = $post_id;
			}
		}

		return $created;
	}

	/**
	 * Set the Home page as the static front page.
	 *
	 * WHY: WordPress defaults to showing blog posts on the front page,
	 *      which makes zero sense for a society website. The Home page
	 *      we just created is a much better landing page.
	 *
	 * @param array $page_ids Associative array of slug => post ID.
	 */
	private static function set_static_front_page( array $page_ids ): void {
		if ( ! empty( $page_ids['home'] ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $page_ids['home'] );
		}
	}

	/**
	 * Define the starter pages and their content.
	 *
	 * WHY: Kept as a separate method so the page definitions are easy to
	 *      find, read, and update without wading through logic code.
	 *
	 * @return array Associative array of slug => [title, content, order].
	 */
	private static function get_page_definitions(): array {
		return array(

			'home' => array(
				'title'   => __( 'Home', 'societypress' ),
				'order'   => 1,
				'content' => '<!-- wp:heading -->
<h2>Welcome to Our Society</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Welcome to your society\'s home on the web! This is where your members and the public come to learn about your organization, find upcoming events, and get involved.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>Getting started:</strong> Edit this page to introduce your society. Talk about your mission, your history, and what makes your organization special. Keep it warm and inviting — this is most visitors\' first impression.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Upcoming Events</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Check our <a href="/events/">Events Calendar</a> for meetings, workshops, and programs.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Become a Member</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Interested in joining? Visit our <a href="/membership/">Membership</a> page to learn about the benefits and how to sign up.</p>
<!-- /wp:paragraph -->',
			),

			'about' => array(
				'title'   => __( 'About', 'societypress' ),
				'order'   => 2,
				'content' => '<!-- wp:heading -->
<h2>About Our Society</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Tell your story here. When was the society founded? Why? What do you do for the community?</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Our Mission</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Describe your society\'s mission and purpose. What drives your organization? What are your goals?</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Our History</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Share the history of your society — when it was established, key milestones, and how it has grown over the years.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Meeting Location</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Let visitors know where and when you meet:</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[societypress_contact show_form="no"]
<!-- /wp:shortcode -->',
			),

			'membership' => array(
				'title'   => __( 'Membership', 'societypress' ),
				'order'   => 3,
				'content' => '<!-- wp:heading -->
<h2>Join Our Society</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>We\'d love to have you as a member! Here\'s what you need to know about joining.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Member Benefits</h3>
<!-- /wp:heading -->

<!-- wp:list -->
<ul>
<li>Access to our research library and resources</li>
<li>Monthly meetings with guest speakers and workshops</li>
<li>Member directory to connect with fellow researchers</li>
<li>Society newsletter with articles, tips, and news</li>
<li>Access to exclusive members-only events and programs</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading {"level":3} -->
<h3>Membership Levels</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Edit this section to describe your membership tiers and annual dues. You can manage your levels in the WordPress admin under Members → Member Levels.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>How to Join</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Describe your sign-up process here. If you accept online registration, include the link or form. If members join in person or by mail, provide those instructions and your mailing address.</p>
<!-- /wp:paragraph -->',
			),

			'events' => array(
				'title'   => __( 'Events', 'societypress' ),
				'order'   => 4,
				'content' => '<!-- wp:heading -->
<h2>Events Calendar</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Stay up to date with our meetings, workshops, classes, and special programs. All members and guests are welcome unless noted otherwise.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><a href="/events/">Browse All Upcoming Events →</a></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Regular Meetings</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Describe your regular meeting schedule here — for example: "We meet the second Saturday of each month at 10:00 AM."</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Special Programs</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Highlight any recurring special programs, annual events, or seasonal workshops your society offers.</p>
<!-- /wp:paragraph -->',
			),

			'contact-us' => array(
				'title'   => __( 'Contact Us', 'societypress' ),
				'order'   => 5,
				'content' => '<!-- wp:heading -->
<h2>Get in Touch</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>We\'d love to hear from you! Whether you have questions about membership, upcoming events, or anything else, don\'t hesitate to reach out.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[societypress_contact]
<!-- /wp:shortcode -->

<!-- wp:paragraph -->
<p><em>You can update your society\'s contact information in the WordPress admin under SocietyPress → Settings.</em></p>
<!-- /wp:paragraph -->',
			),

			'member-portal' => array(
				'title'   => __( 'Member Portal', 'societypress' ),
				'order'   => 6,
				'content' => '<!-- wp:heading -->
<h2>Member Portal</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Welcome back! This is your members-only area. From here you can access the member directory, manage your account, and find resources available only to society members.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Quick Links</h3>
<!-- /wp:heading -->

<!-- wp:list -->
<ul>
<li><a href="/newsletter-archive/">Newsletter Archive</a> — Past issues of our society newsletter</li>
<li><a href="/resources/">Resources &amp; Library</a> — Research guides, databases, and helpful links</li>
<li><a href="/events/">Upcoming Events</a> — Register for meetings and workshops</li>
</ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p><em>This page is a great place to add any members-only content, announcements, or links to restricted resources.</em></p>
<!-- /wp:paragraph -->',
			),

			'newsletter-archive' => array(
				'title'   => __( 'Newsletter Archive', 'societypress' ),
				'order'   => 7,
				'content' => '<!-- wp:heading -->
<h2>Newsletter Archive</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Browse past issues of our society newsletter. Members can download any issue as a PDF.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><em>Upload newsletter PDFs to <code>wp-content/newsletters/</code> using the naming format <code>YYYY_MM_Month_Newsletter.pdf</code> (e.g., 2026_03_March_Newsletter.pdf). They\'ll appear here automatically with thumbnail previews.</em></p>
<!-- /wp:paragraph -->',
			),

			'resources' => array(
				'title'   => __( 'Resources', 'societypress' ),
				'order'   => 8,
				'content' => '<!-- wp:heading -->
<h2>Resources &amp; Library</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>A collection of research tools, guides, and reference materials curated for our members.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Research Databases</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>List any databases, archives, or online tools your society provides access to or recommends.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>How-To Guides</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Share beginner-friendly guides, tips, and tutorials for members who are just getting started with their research.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Society Library</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>If your society maintains a physical or digital library, describe what\'s available and how members can access it.</p>
<!-- /wp:paragraph -->',
			),

			'leadership' => array(
				'title'   => __( 'Leadership', 'societypress' ),
				'order'   => 9,
				'content' => '<!-- wp:heading -->
<h2>Our Leadership</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Meet the people who keep our society running. Our officers and board members volunteer their time to serve the organization and its members.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Officers &amp; Board of Directors</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>You can manage leadership positions and current holders in the WordPress admin under Organization → Leadership. Update this page to display your current officers, or describe how your governance structure works.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Committees</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>List your society\'s active committees and what they do. You can manage committee membership in the admin under Organization → Committees.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Interested in volunteering? Contact us to learn about open committee positions.</p>
<!-- /wp:paragraph -->',
			),

		);
	}
}
