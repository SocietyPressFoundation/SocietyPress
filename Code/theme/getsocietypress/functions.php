<?php
/**
 * getsocietypress Theme Functions
 *
 * Sets up the theme: enqueues styles and fonts, registers nav menus,
 * disables the block editor, and configures theme supports.
 *
 * This is a classic PHP theme — no Gutenberg, no FSE, no theme.json.
 *
 * @package getsocietypress
 * @version 0.02d
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme Setup
 *
 * Runs on 'after_setup_theme' — registers all the core features WordPress
 * needs to know about: title tag, thumbnails, menus, custom logo, HTML5
 * markup, and content width.
 */
function gsp_theme_setup() {
    /* Let WordPress manage the document <title> tag */
    add_theme_support( 'title-tag' );

    /* Enable featured images on posts and pages */
    add_theme_support( 'post-thumbnails' );

    /* Custom logo support for the site header */
    add_theme_support( 'custom-logo', array(
        'height'      => 72,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ) );

    /* HTML5 markup for search forms, comments, galleries, etc. */
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ) );

    /* Register navigation menus */
    register_nav_menus( array(
        'primary' => __( 'Primary Navigation', 'getsocietypress' ),
        'footer'  => __( 'Footer Navigation', 'getsocietypress' ),
    ) );

    /* Set the content width for embeds and images */
    if ( ! isset( $GLOBALS['content_width'] ) ) {
        $GLOBALS['content_width'] = 1200;
    }
}
add_action( 'after_setup_theme', 'gsp_theme_setup' );


/**
 * Enqueue Styles and Scripts
 *
 * Loads the Inter font from Google Fonts, the theme stylesheet, and
 * the theme's vanilla JS file. No jQuery, no frameworks — ever.
 */
function gsp_enqueue_assets() {
    /* Google Fonts — Inter, weights 300–800 */
    wp_enqueue_style(
        'gsp-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
        array(),
        null
    );

    /* Main theme stylesheet */
    wp_enqueue_style(
        'gsp-style',
        get_stylesheet_uri(),
        array( 'gsp-google-fonts' ),
        wp_get_theme()->get( 'Version' )
    );

    /* Theme JavaScript — vanilla JS, no jQuery dependency */
    wp_enqueue_script(
        'gsp-theme-js',
        get_template_directory_uri() . '/js/theme.js',
        array(),
        wp_get_theme()->get( 'Version' ),
        true
    );
}
add_action( 'wp_enqueue_scripts', 'gsp_enqueue_assets' );


/**
 * Disable the Block Editor (Gutenberg) Entirely
 *
 * SocietyPress uses the classic editor. The block editor, Full Site Editing,
 * and theme.json are all prohibited per CLAUDE.md. These filters ensure
 * the block editor never loads for any post type.
 */
add_filter( 'use_block_editor_for_post', '__return_false' );
add_filter( 'use_block_editor_for_post_type', '__return_false' );

/* Remove block-related inline CSS that WordPress injects by default */
function gsp_remove_block_css() {
    wp_dequeue_style( 'wp-block-library' );
    wp_dequeue_style( 'wp-block-library-theme' );
    wp_dequeue_style( 'wc-blocks-style' );
    wp_dequeue_style( 'global-styles' );
}
add_action( 'wp_enqueue_scripts', 'gsp_remove_block_css', 100 );


/**
 * Disable WordPress Emojis
 *
 * We don't need the emoji scripts and styles — they add extra HTTP
 * requests for functionality the site doesn't use.
 */
function gsp_disable_emojis() {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'gsp_disable_emojis' );


/**
 * Clean Up wp_head
 *
 * Remove unnecessary meta tags and links that WordPress outputs by default.
 * Keeps the markup clean and reduces information leakage.
 */
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'rest_output_link_wp_head' );


/**
 * Custom Nav Walker — Bare Links
 *
 * Strips the default <ul>/<li> wrapper from wp_nav_menu() and outputs
 * plain <a> tags. This keeps the nav markup identical to what we had
 * when the links were hardcoded, so the existing CSS works unchanged.
 * Also adds a "current" class to the active page link.
 *
 * NOTE on OOP exception: the rest of this theme is function-based per
 * the project convention. A class is required here only because
 * WordPress's wp_nav_menu() Walker API mandates a class that extends
 * Walker_Nav_Menu — there is no function-based alternative.
 */
class GSP_Nav_Walker extends Walker_Nav_Menu {

    /**
     * Skip the <li> open tag entirely — we only want <a> tags.
     */
    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $item_classes = (array) $item->classes;
        $link_classes = array();

        /* Carry over WordPress built-in current-page CSS classes so theme CSS can target them */
        if ( in_array( 'current-menu-item', $item_classes, true ) ) {
            $link_classes[] = 'current';
            $link_classes[] = 'current-menu-item';
        }
        if ( in_array( 'current-menu-ancestor', $item_classes, true ) ) {
            $link_classes[] = 'current-menu-ancestor';
        }
        if ( in_array( 'current-menu-parent', $item_classes, true ) ) {
            $link_classes[] = 'current-menu-parent';
        }

        $class_attr = $link_classes ? ' class="' . implode( ' ', $link_classes ) . '"' : '';
        $output .= '<a href="' . esc_url( $item->url ) . '"' . $class_attr . '>' . esc_html( $item->title ) . '</a>';
    }

    /* We don't need closing </li> tags since we never opened them */
    public function end_el( &$output, $item, $depth = 0, $args = null ) {}

    /* We don't need <ul> wrappers for submenus (depth is 1 anyway) */
    public function start_lvl( &$output, $depth = 0, $args = null ) {}
    public function end_lvl( &$output, $depth = 0, $args = null ) {}
}


/**
 * Customizer — Announcement Bar
 *
 * Adds a "Site Announcement" section to Appearance > Customize with:
 * - Enable/disable toggle
 * - Text field for the announcement message
 *
 * When disabled (or empty), the bar simply doesn't render in header.php.
 */
function gsp_customizer_announcement( $wp_customize ) {

    /* Section */
    $wp_customize->add_section( 'gsp_announcement', array(
        'title'    => __( 'Site Announcement', 'getsocietypress' ),
        'priority' => 30,
    ) );

    /* Enable toggle */
    $wp_customize->add_setting( 'gsp_announce_enabled', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ) );

    $wp_customize->add_control( 'gsp_announce_enabled', array(
        'label'   => __( 'Show announcement bar', 'getsocietypress' ),
        'section' => 'gsp_announcement',
        'type'    => 'checkbox',
    ) );

    /* Hero badge text — the small pill above the hero headline */
    $wp_customize->add_setting( 'gsp_hero_badge', array(
        'default'           => 'Open Source & Free',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'gsp_hero_badge', array(
        'label'       => __( 'Hero badge text', 'getsocietypress' ),
        'description' => __( 'The small label above the homepage headline (e.g. "Open Source & Free"). Leave blank to hide.', 'getsocietypress' ),
        'section'     => 'gsp_announcement',
        'type'        => 'text',
    ) );

    /* Announcement text */
    $wp_customize->add_setting( 'gsp_announce_text', array(
        'default'           => 'SocietyPress v0.01d is here — a free, open-source platform for genealogical societies.',
        'sanitize_callback' => 'wp_kses_post',
    ) );

    $wp_customize->add_control( 'gsp_announce_text', array(
        'label'   => __( 'Announcement text', 'getsocietypress' ),
        'section' => 'gsp_announcement',
        'type'    => 'textarea',
    ) );
}
add_action( 'customize_register', 'gsp_customizer_announcement' );


/**
 * Custom Excerpt Length
 *
 * Keep excerpts concise for the "Latest Updates" cards on the homepage.
 */
function gsp_excerpt_length( $length ) {
    return 20;
}
add_filter( 'excerpt_length', 'gsp_excerpt_length' );


/**
 * Current SocietyPress plugin version.
 *
 * Hardcoded here so every page can cite the same version without a network
 * round-trip. Bump this constant whenever a new release ships — a fresh
 * deploy is already part of the release flow, so this stays in sync
 * naturally.
 *
 * We used to fetch this from GitHub's API, but the repo is private so the
 * request always fell through to the fallback anyway. Removing the fetch
 * saves a per-12h API miss (no log noise, no wasted HTTP timeout budget)
 * and removes one reason for the page to hang if GitHub is slow.
 *
 * Returns the version as a plain string (e.g. "1.0.19"), no "v" prefix.
 */
function gsp_get_sp_version() {
    return '1.0.19';
}


/**
 * Download URL for the latest SocietyPress bundle.
 *
 * The bundle lives at /downloads/societypress-latest.zip on this domain and
 * is rebuilt by scripts/deploy.sh bundle. Centralized here so every page can
 * link to the same URL without hardcoding.
 */
function gsp_get_download_url() {
    return home_url( '/downloads/societypress-latest.zip' );
}


/**
 * Community Pulse dashboard widget.
 *
 * Three-panel "what's happening in the forums" widget registered on the
 * WordPress admin Dashboard. Answers the three questions a moderator has
 * when they log in:
 *
 *   - What are people SAYING?       — latest topics & replies
 *   - What are they ASKING?          — unanswered topics
 *   - What are they TALKING ABOUT?   — most active threads (last 30 days)
 *
 * Gated to users with the `moderate` capability (bbPress moderators) and
 * to administrators. Regular members don't see it when they log in — they
 * have no reason to look at a moderation-oriented dashboard panel.
 *
 * Queries hit bbPress's native post types (topic, reply) and postmeta
 * (_bbp_reply_count, _bbp_last_active_time). No caching layer — the
 * dashboard is admin-side and the query volume is trivial.
 */
function gsp_dashboard_pulse_render() {
    // Defense in depth: gsp_register_dashboard_widgets() already gates the
    // callback registration on these caps, but a second check here means
    // the query-and-render path is safe even if the function is ever
    // called directly (e.g. by a misconfigured AJAX hook).
    if ( ! current_user_can( 'moderate' ) && ! current_user_can( 'manage_options' ) ) {
        return;
    }
    global $wpdb;

    // Latest 6 posts (topics + replies). 6 fits the widget column without scrolling.
    $saying = $wpdb->get_results(
        "SELECT ID, post_type, post_title, post_date, post_parent, post_author
           FROM {$wpdb->posts}
          WHERE post_type IN ('topic', 'reply')
            AND post_status = 'publish'
          ORDER BY post_date DESC
          LIMIT 6"
    );

    // Unanswered topics.
    $asking = $wpdb->get_results(
        "SELECT p.ID, p.post_title, p.post_date, p.post_parent, p.post_author
           FROM {$wpdb->posts} p
      LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_bbp_reply_count'
          WHERE p.post_type = 'topic'
            AND p.post_status = 'publish'
            AND ( m.meta_value IS NULL OR m.meta_value = '0' )
          ORDER BY p.post_date DESC
          LIMIT 6"
    );

    // Most-replied-to topics, last 30 days.
    $talking = $wpdb->get_results(
        "SELECT p.ID, p.post_title, p.post_date, p.post_parent, p.post_author,
                CAST(rc.meta_value AS UNSIGNED) AS reply_count
           FROM {$wpdb->posts} p
           JOIN {$wpdb->postmeta} rc ON rc.post_id = p.ID AND rc.meta_key = '_bbp_reply_count'
           JOIN {$wpdb->postmeta} la ON la.post_id = p.ID AND la.meta_key = '_bbp_last_active_time'
          WHERE p.post_type = 'topic'
            AND p.post_status = 'publish'
            AND CAST(rc.meta_value AS UNSIGNED) > 0
            AND la.meta_value >= DATE_SUB( NOW(), INTERVAL 30 DAY )
          ORDER BY reply_count DESC, la.meta_value DESC
          LIMIT 6"
    );

    /* Inline styles — kept here so the widget is self-contained.
       Using admin-compatible tokens (not our marketing theme's CSS vars)
       so the widget looks native inside /wp-admin/. */
    ?>
    <style>
        .gsp-pulse { margin: -12px; }
        .gsp-pulse__panel { border-top: 1px solid #dcdcde; padding: 10px 12px; }
        .gsp-pulse__panel:first-child { border-top: none; }
        .gsp-pulse__title { margin: 0 0 8px; font-size: 13px; font-weight: 600; color: #1d2327; text-transform: uppercase; letter-spacing: 0.04em; }
        .gsp-pulse__title small { display: block; font-size: 11px; font-weight: 400; color: #646970; text-transform: none; letter-spacing: 0; margin-top: 2px; }
        .gsp-pulse__list { list-style: none; padding: 0; margin: 0; }
        .gsp-pulse__item { padding: 6px 0; border-top: 1px solid #f0f0f1; }
        .gsp-pulse__item:first-child { border-top: none; }
        .gsp-pulse__link { display: block; color: #2271b1; text-decoration: none; font-weight: 500; line-height: 1.3; }
        .gsp-pulse__link:hover { color: #135e96; text-decoration: underline; }
        .gsp-pulse__meta { display: block; color: #646970; font-size: 11px; margin-top: 2px; }
        .gsp-pulse__meta a { color: #646970; text-decoration: none; }
        .gsp-pulse__meta a:hover { color: #135e96; }
        .gsp-pulse__count { background: #dcdcde; color: #1d2327; padding: 1px 6px; border-radius: 10px; font-size: 10px; margin-left: 4px; }
        .gsp-pulse__empty { color: #646970; font-size: 12px; font-style: italic; margin: 0; padding: 4px 0; }
        .gsp-pulse__footer { border-top: 1px solid #dcdcde; padding: 10px 12px; text-align: right; background: #f6f7f7; margin-top: 0; }
        .gsp-pulse__footer a { text-decoration: none; font-size: 12px; }
    </style>

    <div class="gsp-pulse">

        <!-- SAYING -->
        <div class="gsp-pulse__panel">
            <h3 class="gsp-pulse__title">
                What people are saying
                <small>Latest posts across every forum</small>
            </h3>
            <?php if ( ! empty( $saying ) ) : ?>
                <ul class="gsp-pulse__list">
                    <?php foreach ( $saying as $p ) : ?>
                        <?php gsp_dashboard_pulse_saying_row( $p ); ?>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p class="gsp-pulse__empty">Nothing posted yet.</p>
            <?php endif; ?>
        </div>

        <!-- ASKING -->
        <div class="gsp-pulse__panel">
            <h3 class="gsp-pulse__title">
                What they're asking
                <small>Topics with no replies yet</small>
            </h3>
            <?php if ( ! empty( $asking ) ) : ?>
                <ul class="gsp-pulse__list">
                    <?php foreach ( $asking as $t ) : ?>
                        <?php gsp_dashboard_pulse_topic_row( $t ); ?>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p class="gsp-pulse__empty">No open questions right now.</p>
            <?php endif; ?>
        </div>

        <!-- TALKING ABOUT -->
        <div class="gsp-pulse__panel">
            <h3 class="gsp-pulse__title">
                What they're talking about
                <small>Most active threads, last 30 days</small>
            </h3>
            <?php if ( ! empty( $talking ) ) : ?>
                <ul class="gsp-pulse__list">
                    <?php foreach ( $talking as $t ) : ?>
                        <?php gsp_dashboard_pulse_topic_row( $t, (int) $t->reply_count ); ?>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p class="gsp-pulse__empty">No active threads yet.</p>
            <?php endif; ?>
        </div>

        <div class="gsp-pulse__footer">
            <a href="<?php echo esc_url( home_url( '/forums/' ) ); ?>" target="_blank" rel="noopener">
                Open the forums &rarr;
            </a>
        </div>

    </div>
    <?php
}

/**
 * Render one row of the SAYING panel (topics + replies, mixed).
 */
function gsp_dashboard_pulse_saying_row( $post ) {
    $author = get_the_author_meta( 'display_name', $post->post_author );

    $time_ago = function_exists( 'bbp_get_time_since' )
        ? bbp_get_time_since( get_gmt_from_date( $post->post_date ) )
        : human_time_diff( strtotime( $post->post_date ), time() ) . ' ago';

    if ( 'reply' === $post->post_type ) {
        $topic_id    = function_exists( 'bbp_get_reply_topic_id' )
            ? bbp_get_reply_topic_id( $post->ID )
            : $post->post_parent;
        $topic       = get_post( $topic_id );
        $topic_title = $topic ? $topic->post_title : '(topic)';
        $link        = function_exists( 'bbp_get_reply_url' )
            ? bbp_get_reply_url( $post->ID )
            : get_permalink( $topic_id );
        $action      = 'replied to';
    } else {
        $topic_title = $post->post_title;
        $link        = get_permalink( $post->ID );
        $action      = 'started';
    }
    ?>
    <li class="gsp-pulse__item">
        <a class="gsp-pulse__link" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $topic_title ); ?></a>
        <span class="gsp-pulse__meta">
            <?php echo esc_html( $author ); ?> <?php echo esc_html( $action ); ?> &middot; <?php echo esc_html( $time_ago ); ?>
        </span>
    </li>
    <?php
}

/**
 * Render one row of the ASKING or TALKING panel (topic-only).
 * Pass $reply_count to show it as a pill (used by TALKING).
 */
function gsp_dashboard_pulse_topic_row( $topic, $reply_count = null ) {
    $author = get_the_author_meta( 'display_name', $topic->post_author );
    $forum  = $topic->post_parent ? get_post( $topic->post_parent ) : null;
    $time_ago = function_exists( 'bbp_get_time_since' )
        ? bbp_get_time_since( get_gmt_from_date( $topic->post_date ) )
        : human_time_diff( strtotime( $topic->post_date ), time() ) . ' ago';
    ?>
    <li class="gsp-pulse__item">
        <a class="gsp-pulse__link" href="<?php echo esc_url( get_permalink( $topic->ID ) ); ?>">
            <?php echo esc_html( $topic->post_title ); ?>
            <?php if ( null !== $reply_count ) : ?>
                <span class="gsp-pulse__count"><?php echo (int) $reply_count; ?></span>
            <?php endif; ?>
        </a>
        <span class="gsp-pulse__meta">
            <?php if ( $forum ) : ?>
                <?php echo esc_html( $forum->post_title ); ?> &middot;
            <?php endif; ?>
            <?php echo esc_html( $author ); ?> &middot; <?php echo esc_html( $time_ago ); ?>
        </span>
    </li>
    <?php
}

/**
 * Register the widget on the admin dashboard.
 *
 * Gated: only moderators and administrators see the Community Pulse. Any
 * bbPress participant who logs into /wp-admin/ would otherwise see this,
 * and it's a moderation tool, not a member-facing surface.
 */
function gsp_register_dashboard_widgets() {
    if ( ! current_user_can( 'moderate' ) && ! current_user_can( 'manage_options' ) ) {
        return;
    }
    wp_add_dashboard_widget(
        'gsp_community_pulse',
        'Community Pulse',
        'gsp_dashboard_pulse_render'
    );
}
add_action( 'wp_dashboard_setup', 'gsp_register_dashboard_widgets' );


/**
 * Custom /sitemap.xml endpoint.
 *
 * WHY: WordPress 5.5+'s built-in /wp-sitemap.xml registers its providers on
 * the `init` action, but on this install the providers array comes back
 * empty (something in the load order on this host prevents
 * WP_Sitemaps::register_sitemaps() from running). That behavior is
 * hard to chase down remotely, so instead of fixing core we publish our
 * own sitemap — smaller, but sufficient for search engines on a
 * marketing site with mostly static pages.
 *
 * Rewrite maps /sitemap.xml -> index.php?gsp_sitemap=1. template_redirect
 * catches that query var, outputs the sitemap XML, and exits before WP
 * tries (and fails) to serve any template.
 */
function gsp_sitemap_rewrite_rules() {
    add_rewrite_rule( '^sitemap\.xml$', 'index.php?gsp_sitemap=1', 'top' );
}
add_action( 'init', 'gsp_sitemap_rewrite_rules' );

function gsp_sitemap_query_vars( $vars ) {
    $vars[] = 'gsp_sitemap';
    return $vars;
}
add_filter( 'query_vars', 'gsp_sitemap_query_vars' );

/*
 * Prevent WP's canonical redirect from adding a trailing slash to
 * /sitemap.xml — it would 301 the XML URL to /sitemap.xml/, which is
 * not a valid sitemap path for most crawlers even if the body is the
 * same XML. This runs early enough to short-circuit redirect_canonical.
 */
function gsp_sitemap_skip_canonical( $redirect_url ) {
    if ( get_query_var( 'gsp_sitemap' ) ) {
        return false;
    }
    return $redirect_url;
}
add_filter( 'redirect_canonical', 'gsp_sitemap_skip_canonical', 10, 1 );

function gsp_sitemap_render() {
    if ( ! get_query_var( 'gsp_sitemap' ) ) {
        return;
    }

    // Collect every public, published page + post. Forums and bbPress
    // topics get their own urlset so they appear in the crawl budget
    // alongside the static pages.
    $pages = get_posts( array(
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'numberposts'    => -1,
        'orderby'        => 'modified',
        'order'          => 'DESC',
    ) );
    $posts = get_posts( array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'numberposts'    => -1,
        'orderby'        => 'modified',
        'order'          => 'DESC',
    ) );
    $forums = get_posts( array(
        'post_type'      => 'forum',
        'post_status'    => 'publish',
        'numberposts'    => -1,
        'orderby'        => 'modified',
        'order'          => 'DESC',
    ) );
    $topics = get_posts( array(
        'post_type'      => 'topic',
        'post_status'    => 'publish',
        'numberposts'    => -1,
        'orderby'        => 'modified',
        'order'          => 'DESC',
    ) );

    $all = array_merge( $pages, $posts, $forums, $topics );

    // Priority/frequency hints. Homepage and hubs rank highest; deep
    // utility pages (privacy, terms) are lower-priority reference.
    $high_priority_slugs = array(
        'home', 'features', 'requirements', 'download', 'docs',
        'installation', 'setup', 'faq', 'ens-migration',
    );
    $low_priority_slugs = array(
        'privacy-policy', 'terms', 'accessibility', 'security-policy',
        'sitemap', 'status',
    );

    header( 'Content-Type: application/xml; charset=UTF-8' );
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    // Homepage first, explicit so it gets the loudest priority even if
    // its post_date is older than recent changes.
    printf(
        "  <url>\n    <loc>%s</loc>\n    <priority>1.0</priority>\n    <changefreq>weekly</changefreq>\n  </url>\n",
        esc_url( home_url( '/' ) )
    );

    foreach ( $all as $p ) {
        // Skip the "home" page since we emitted home_url('/') already.
        if ( $p->post_name === 'home' ) {
            continue;
        }

        $priority = '0.6';
        if ( in_array( $p->post_name, $high_priority_slugs, true ) ) {
            $priority = '0.9';
        } elseif ( in_array( $p->post_name, $low_priority_slugs, true ) ) {
            $priority = '0.3';
        }

        // Forum topics decay — recent ones are more interesting than
        // months-old ones. Simple tier based on days since last modified.
        if ( 'topic' === $p->post_type ) {
            $days_old = ( time() - strtotime( $p->post_modified ) ) / DAY_IN_SECONDS;
            if ( $days_old > 90 ) {
                $priority = '0.3';
            } elseif ( $days_old > 30 ) {
                $priority = '0.5';
            } else {
                $priority = '0.7';
            }
        }

        $changefreq = 'monthly';
        if ( 'post' === $p->post_type || 'topic' === $p->post_type ) {
            $changefreq = 'weekly';
        }

        printf(
            "  <url>\n    <loc>%s</loc>\n    <lastmod>%s</lastmod>\n    <changefreq>%s</changefreq>\n    <priority>%s</priority>\n  </url>\n",
            esc_url( get_permalink( $p->ID ) ),
            esc_html( mysql2date( 'c', $p->post_modified_gmt ? $p->post_modified_gmt : $p->post_modified ) ),
            esc_html( $changefreq ),
            esc_html( $priority )
        );
    }

    echo '</urlset>';
    exit;
}
add_action( 'template_redirect', 'gsp_sitemap_render' );


/**
 * robots.txt enhancements.
 *
 * Points crawlers at our sitemap.xml (WP doesn't add this by default)
 * and disallows a few non-public paths that don't need crawling —
 * installer files, the wp-admin area (already default), and the
 * /feedback/ form since its only purpose is submission, not indexing.
 */
function gsp_robots_txt( $output, $public ) {
    if ( '0' === (string) $public ) {
        // Site is set to "Discourage search engines" — respect that.
        return $output;
    }
    $output .= "\nSitemap: " . home_url( '/sitemap.xml' ) . "\n";
    // WP core's robots_txt output already emits "Disallow: /cms/wp-admin/"
    // because siteurl is /cms (it respects the subdirectory install).
    // We only add what WP doesn't already cover.
    $output .= "Disallow: /cms/wp-includes/\n";
    $output .= "Disallow: /cms/xmlrpc.php\n";
    $output .= "Disallow: /sp-installer.php\n";
    return $output;
}
add_filter( 'robots_txt', 'gsp_robots_txt', 10, 2 );


/**
 * Social meta tags — Open Graph and Twitter Card.
 *
 * Every page gets a meaningful title, description, type, URL, and
 * image for social-media shares (Facebook, LinkedIn, Twitter/X,
 * Mastodon, Slack previews, iMessage link unfurling). Without these,
 * shared links render as a bare URL with no preview.
 *
 * Strategy:
 *   - Title: the post/page title, or the site name on the home page.
 *   - Description: the post excerpt, a manual description (if set via
 *     a page-by-page filter), or the site tagline as fallback.
 *   - Image: the post's featured image, or the site logo PNG at 512×512
 *     that we generated for the favicon.
 *   - URL: canonical permalink.
 *   - Type: "article" for single posts, "website" otherwise.
 *
 * Values are filterable so child code can override per page if needed.
 */
function gsp_social_meta() {

    /* Title / description resolution order matters: the homepage must
       check is_front_page() FIRST because it's also is_singular(). A
       static front page would otherwise set og:title to "Home" (the
       page's title) instead of the site name. */

    // Title.
    if ( is_front_page() || is_home() ) {
        $title = get_bloginfo( 'name' );
        $tagline = get_bloginfo( 'description' );
        if ( ! empty( $tagline ) ) {
            $title .= ' — ' . $tagline;
        }
    } elseif ( is_singular() ) {
        $title = wp_strip_all_tags( get_the_title() );
    } else {
        $title = wp_get_document_title();
    }
    $title = apply_filters( 'gsp_social_title', $title );

    // Description.
    if ( is_front_page() || is_home() ) {
        $desc = get_bloginfo( 'description' );
    } elseif ( is_singular() ) {
        $desc = get_the_excerpt();
        if ( empty( $desc ) ) {
            $desc = wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', get_the_ID() ) ), 30, '' );
        }
        // Fall back to the site tagline if the page genuinely has no
        // content yet (empty template-driven pages would otherwise
        // emit an empty description).
        if ( empty( trim( $desc ) ) ) {
            $desc = get_bloginfo( 'description' );
        }
    } else {
        $desc = get_bloginfo( 'description' );
    }
    $desc = apply_filters( 'gsp_social_description', $desc );
    $desc = trim( wp_strip_all_tags( $desc ) );
    // Cap at ~300 chars to avoid awkward wrapping in previews.
    if ( mb_strlen( $desc ) > 300 ) {
        $desc = mb_substr( $desc, 0, 297 ) . '…';
    }

    // URL.
    $url = is_singular() ? get_permalink() : home_url( add_query_arg( null, null ) );
    $url = apply_filters( 'gsp_social_url', $url );

    // Image — featured image on singles, site icon otherwise.
    $image = '';
    if ( is_singular() && has_post_thumbnail() ) {
        $image_arr = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
        if ( ! empty( $image_arr[0] ) ) {
            $image = $image_arr[0];
        }
    }
    if ( ! $image ) {
        $site_icon_id = (int) get_option( 'site_icon' );
        if ( $site_icon_id ) {
            $icon_arr = wp_get_attachment_image_src( $site_icon_id, 'full' );
            if ( ! empty( $icon_arr[0] ) ) {
                $image = $icon_arr[0];
            }
        }
    }
    $image = apply_filters( 'gsp_social_image', $image );

    // Type.
    $og_type = ( is_singular( 'post' ) ) ? 'article' : 'website';

    // Site name.
    $site_name = get_bloginfo( 'name' );

    ?>

    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo esc_attr( $title ); ?>">
    <meta property="og:description" content="<?php echo esc_attr( $desc ); ?>">
    <meta property="og:url" content="<?php echo esc_url( $url ); ?>">
    <meta property="og:type" content="<?php echo esc_attr( $og_type ); ?>">
    <meta property="og:site_name" content="<?php echo esc_attr( $site_name ); ?>">
    <meta property="og:locale" content="<?php echo esc_attr( get_locale() ); ?>">
    <?php if ( $image ) : ?>
        <meta property="og:image" content="<?php echo esc_url( $image ); ?>">
        <meta property="og:image:alt" content="<?php echo esc_attr( $site_name ); ?>">
    <?php endif; ?>
    <?php if ( is_singular( 'post' ) ) : ?>
        <meta property="article:published_time" content="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
        <meta property="article:modified_time" content="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>">
        <meta property="article:author" content="<?php echo esc_attr( get_the_author() ); ?>">
    <?php endif; ?>

    <!-- Twitter Card -->
    <meta name="twitter:card" content="<?php echo esc_attr( $image ? 'summary_large_image' : 'summary' ); ?>">
    <meta name="twitter:title" content="<?php echo esc_attr( $title ); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr( $desc ); ?>">
    <?php if ( $image ) : ?>
        <meta name="twitter:image" content="<?php echo esc_url( $image ); ?>">
    <?php endif; ?>

    <!-- Canonical -->
    <link rel="canonical" href="<?php echo esc_url( $url ); ?>">

    <?php
}
add_action( 'wp_head', 'gsp_social_meta', 5 );


/**
 * JSON-LD Organization + WebSite structured data.
 *
 * Outputs on the homepage only. Helps search engines build the
 * knowledge-graph card for SocietyPress and enables a sitelinks
 * search box in Google results. The Organization block carries the
 * project's canonical name, URL, logo, description, and links out to
 * where SocietyPress also appears (GitHub, eventually Mastodon, etc.).
 */
function gsp_structured_data() {
    if ( ! is_front_page() && ! is_home() ) {
        return;
    }

    $site_name = get_bloginfo( 'name' );
    $site_url  = home_url( '/' );
    $tagline   = get_bloginfo( 'description' );

    $logo_url = '';
    $site_icon_id = (int) get_option( 'site_icon' );
    if ( $site_icon_id ) {
        $icon_arr = wp_get_attachment_image_src( $site_icon_id, 'full' );
        if ( ! empty( $icon_arr[0] ) ) {
            $logo_url = $icon_arr[0];
        }
    }

    $organization = array(
        '@context'    => 'https://schema.org',
        '@type'       => 'Organization',
        'name'        => $site_name,
        'url'         => $site_url,
        'description' => $tagline,
        'sameAs'      => array(
            'https://github.com/SocietyPressFoundation/SocietyPress',
        ),
    );

    if ( $logo_url ) {
        $organization['logo'] = $logo_url;
    }

    $website = array(
        '@context'      => 'https://schema.org',
        '@type'         => 'WebSite',
        'name'          => $site_name,
        'url'           => $site_url,
        'description'   => $tagline,
        'potentialAction' => array(
            '@type'        => 'SearchAction',
            'target'       => array(
                '@type'       => 'EntryPoint',
                'urlTemplate' => $site_url . '?s={search_term_string}',
            ),
            'query-input'  => 'required name=search_term_string',
        ),
    );

    $software = array(
        '@context'     => 'https://schema.org',
        '@type'        => 'SoftwareApplication',
        'name'         => 'SocietyPress',
        'url'          => $site_url,
        'description'  => 'A free, open-source WordPress plugin and theme suite for genealogical and historical societies.',
        'applicationCategory' => 'BusinessApplication',
        'operatingSystem'     => 'Any (WordPress 6.0+)',
        'offers'       => array(
            '@type'    => 'Offer',
            'price'    => '0',
            'priceCurrency' => 'USD',
        ),
        'license'      => 'https://www.gnu.org/licenses/gpl-2.0.html',
    );

    echo "\n<script type=\"application/ld+json\">" . wp_json_encode( $organization, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . "</script>\n";
    echo "<script type=\"application/ld+json\">" . wp_json_encode( $website,      JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . "</script>\n";
    echo "<script type=\"application/ld+json\">" . wp_json_encode( $software,     JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . "</script>\n";
}
add_action( 'wp_head', 'gsp_structured_data', 6 );


/**
 * Fallback favicon.
 *
 * WordPress's Site Identity Customizer lets an admin upload a real site
 * icon (PNG, generates the full favicon/apple-touch-icon/PWA set). If no
 * site icon is uploaded, we output an inline SVG favicon using the same
 * brand mark as the header's fallback logo. That way browser tabs always
 * show a recognizable icon rather than the generic document glyph.
 *
 * The inline SVG is base64-encoded into a data URL so no extra HTTP
 * request is needed.
 */
function gsp_fallback_favicon() {
    if ( function_exists( 'has_site_icon' ) && has_site_icon() ) {
        return; // Real site icon set in Customizer — WP outputs its own.
    }

    $svg =
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">' .
        '<rect x="16" y="16" width="480" height="480" rx="68" ry="68" fill="#C9973A"/>' .
        '<text x="256" y="384" font-family="Georgia, serif" font-size="360" font-weight="700" fill="#0D1F3C" text-anchor="middle">S</text>' .
        '</svg>';

    $data_url = 'data:image/svg+xml;base64,' . base64_encode( $svg );

    echo '<link rel="icon" type="image/svg+xml" href="' . esc_attr( $data_url ) . '">' . "\n";
    echo '<meta name="theme-color" content="#0D1F3C">' . "\n";
}
add_action( 'wp_head', 'gsp_fallback_favicon', 2 );


/**
 * Custom Excerpt "Read More" Text
 *
 * Replace the default "[...]" with something cleaner.
 */
function gsp_excerpt_more( $more ) {
    return '&hellip;';
}
add_filter( 'excerpt_more', 'gsp_excerpt_more' );
