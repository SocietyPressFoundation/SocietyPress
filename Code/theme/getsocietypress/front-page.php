<?php
/**
 * Homepage Template (front-page.php)
 *
 * The main landing page for getsocietypress.org. Tells the SocietyPress story
 * in clear, confident sections:
 *
 * 1. Hero — what it is, two CTAs (download + demo)
 * 2. Features — 6-card grid showing the real breadth of the platform
 * 3. Demo — see it in action callout
 * 4. Migration — EasyNetSites societies are the target market
 * 5. Latest Updates — recent blog posts (hidden when none exist)
 * 6. CTA Banner — donation callout
 *
 * @package getsocietypress
 * @version 0.03d
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<!-- ==========================================================================
     HERO SECTION
     ========================================================================== -->
<section class="hero">
    <div class="container">
        <div class="hero__content">

            <div class="hero__badge">
                Free. Open Source. Built for Genealogy.
            </div>

            <h1 class="hero__title">
                The Platform Your <span>Genealogical Society</span> Deserves
            </h1>

            <p class="hero__subtitle">
                A free, open-source platform built specifically for genealogical
                and historical societies. Membership management, event calendars,
                a research library, genealogical record collections, a store,
                newsletters, and more &mdash; all in one place.
            </p>

            <div class="hero__actions">
                <a href="<?php echo esc_url( home_url( '/download/' ) ); ?>" class="btn btn-primary btn-lg">
                    Download SocietyPress
                </a>
                <a href="https://demo.getsocietypress.org" class="btn btn-outline btn-lg" target="_blank" rel="noopener">
                    See the Demo
                </a>
            </div>

        </div>
    </div>
</section>


<!-- ==========================================================================
     FEATURES SECTION — 6 cards showing the real breadth
     ========================================================================== -->
<section class="features section">
    <div class="container">

        <div class="section-header">
            <h2>Everything Your Society Needs</h2>
            <p>
                16 feature modules. One platform. No piecing together
                a dozen WordPress plugins and hoping they work together.
            </p>
        </div>

        <div class="grid-3">

            <!-- Feature 1: Membership Management -->
            <div class="feature-card">
                <div class="feature-card__icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h3>Membership Management</h3>
                <p>
                    Individual and organizational members, membership tiers,
                    automated renewal reminders, a self-service member portal,
                    and a searchable member directory with privacy controls.
                </p>
                <a href="<?php echo esc_url( home_url( '/features/' ) ); ?>" class="feature-card__link">
                    Learn more &rarr;
                </a>
            </div>

            <!-- Feature 2: Events & Calendar -->
            <div class="feature-card">
                <div class="feature-card__icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
                <h3>Events &amp; Calendar</h3>
                <p>
                    Meetings, workshops, seminars, and cemetery walks. Online
                    registration with capacity limits, waitlists, Stripe payments,
                    iCal export, and a browsable calendar grid.
                </p>
                <a href="<?php echo esc_url( home_url( '/features/' ) ); ?>" class="feature-card__link">
                    Learn more &rarr;
                </a>
            </div>

            <!-- Feature 3: Genealogical Records -->
            <div class="feature-card">
                <div class="feature-card__icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </div>
                <h3>Genealogical Records</h3>
                <p>
                    Publish your society's transcribed records &mdash; cemetery indexes,
                    census extractions, marriage registers &mdash; with full-text search.
                    Import and export using the open <a href="https://genrecord.org">GENRECORD</a> standard.
                </p>
                <a href="<?php echo esc_url( home_url( '/features/' ) ); ?>" class="feature-card__link">
                    Learn more &rarr;
                </a>
            </div>

            <!-- Feature 4: Research Library -->
            <div class="feature-card">
                <div class="feature-card__icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                    </svg>
                </div>
                <h3>Research Library</h3>
                <p>
                    A full OPAC-style catalog for your society's book collection.
                    Call numbers, shelf locations, cover images via Open Library,
                    and a public search interface for visitors.
                </p>
                <a href="<?php echo esc_url( home_url( '/features/' ) ); ?>" class="feature-card__link">
                    Learn more &rarr;
                </a>
            </div>

            <!-- Feature 5: Newsletter Archive -->
            <div class="feature-card">
                <div class="feature-card__icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                </div>
                <h3>Newsletter Archive</h3>
                <p>
                    Upload your society's newsletters as PDFs. Automatic cover
                    thumbnails, inline preview with zoom, and download restricted
                    to members only. Years of back issues, always available.
                </p>
                <a href="<?php echo esc_url( home_url( '/features/' ) ); ?>" class="feature-card__link">
                    Learn more &rarr;
                </a>
            </div>

            <!-- Feature 6: Volunteers & Governance -->
            <div class="feature-card">
                <div class="feature-card__icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                </div>
                <h3>Volunteers &amp; Governance</h3>
                <p>
                    Track officer positions and committee assignments. Post volunteer
                    opportunities with signup and waitlists. Log volunteer hours.
                    Run board elections with secure online ballots.
                </p>
                <a href="<?php echo esc_url( home_url( '/features/' ) ); ?>" class="feature-card__link">
                    Learn more &rarr;
                </a>
            </div>

        </div>

    </div>
</section>


<!-- ==========================================================================
     DEMO SECTION — See it in action
     ========================================================================== -->
<section class="demo-section section">
    <div class="container">
        <div class="demo-section__content">
            <div class="demo-section__text">
                <h2>See It in Action</h2>
                <p>
                    The best way to understand SocietyPress is to use it.
                    The demo site is a fully functional installation with sample
                    members, events, a library catalog, genealogical record
                    collections, and more. Log in, click around, break things.
                </p>
                <a href="https://demo.getsocietypress.org" class="btn btn-primary btn-lg" target="_blank" rel="noopener">
                    Visit the Demo
                </a>
            </div>
            <div class="demo-section__features">
                <ul>
                    <li>1,100+ sample members with full profiles</li>
                    <li>6,500+ genealogical records (real public data)</li>
                    <li>19,000+ library catalog items</li>
                    <li>16 feature modules, all active</li>
                    <li>Page builder with 21 widget types</li>
                    <li>5 pre-built child themes</li>
                </ul>
            </div>
        </div>
    </div>
</section>


<!-- ==========================================================================
     MIGRATION SECTION — For societies leaving EasyNetSites
     ========================================================================== -->
<section class="migration-section section">
    <div class="container">
        <div class="section-header">
            <h2>Moving from Another Platform?</h2>
            <p>
                If your society is using EasyNetSites, a static HTML site, or
                a patchwork of tools that barely work together, SocietyPress
                was built for exactly this moment.
            </p>
        </div>
        <div class="grid-3">
            <div class="migration-card">
                <h3>Import Your Data</h3>
                <p>
                    CSV import for members, events, library items, and resource links.
                    GENRECORD import for genealogical record collections. Bring your
                    data with you &mdash; nothing gets left behind.
                </p>
            </div>
            <div class="migration-card">
                <h3>Keep What Works</h3>
                <p>
                    Already have a website design you like? SocietyPress includes a
                    color extractor that matches your current site's palette, and a
                    theme builder so you can start familiar, not from scratch.
                </p>
            </div>
            <div class="migration-card">
                <h3>No Monthly Fees</h3>
                <p>
                    SocietyPress is free. Not "free trial." Not "free tier."
                    Free. Host it on any standard web hosting account.
                    Your only cost is hosting &mdash; typically $5&ndash;15/month.
                </p>
            </div>
        </div>
    </div>
</section>


<!-- ==========================================================================
     YOUR DATA IS YOURS — data ownership commitment
     This is a statement of principle, not a feature list. Visually distinct
     from surrounding sections so it reads as a promise, not marketing copy.
     ========================================================================== -->
<section class="data-yours">
    <div class="container">
        <div class="data-yours__content">

            <h2>Your Data Is Yours</h2>

            <p class="data-yours__lede">
                Every piece of data your society puts into SocietyPress can come
                back out &mdash; in a standard format, with one click, at any time,
                no questions asked.
            </p>

            <p>
                Members. Events. Donations. Library catalogs. Genealogical records.
                Newsletters. Documents. Photos. Settings. <strong>Everything.</strong>
            </p>

            <p>
                No export fees. No &ldquo;contact us to request your data.&rdquo;
                No degraded export that&rsquo;s missing half the fields.
                Your data is yours. It always was.
            </p>

            <p>
                SocietyPress is GPL-licensed, open-source software. If you ever
                want to leave, you take everything with you. We&rsquo;d rather build
                something so good you never want to &mdash; but we&rsquo;ll never
                make that choice for you.
            </p>

        </div>
    </div>
</section>


<!-- ==========================================================================
     RECENTLY SHIPPED — pulls from GitHub releases via a 6h-cached helper.
     Hidden when the API is unavailable so the page doesn't show "0 releases."
     ========================================================================== -->
<?php
$gsp_releases = function_exists( 'gsp_get_github_releases' ) ? gsp_get_github_releases( 3 ) : array();
if ( ! empty( $gsp_releases ) ) :
?>
<section class="updates section">
    <div class="container">

        <div class="section-header">
            <h2>Recently Shipped</h2>
            <p>The most recent SocietyPress releases &mdash;
                <a href="https://github.com/SocietyPressFoundation/SocietyPress/releases" target="_blank" rel="noopener">all releases on GitHub &rarr;</a>
            </p>
        </div>

        <div class="grid-3">
            <?php foreach ( $gsp_releases as $release ) :
                $excerpt = '';
                if ( ! empty( $release['body'] ) ) {
                    $excerpt = wp_trim_words( wp_strip_all_tags( $release['body'] ), 25, '&hellip;' );
                }
            ?>
                <article class="update-card">
                    <div class="update-card__body">
                        <div class="update-card__date">
                            <?php echo esc_html( wp_date( 'F j, Y', strtotime( $release['date'] ) ) ); ?>
                        </div>
                        <h3>
                            <a href="<?php echo esc_url( $release['url'] ); ?>" target="_blank" rel="noopener">
                                <?php echo esc_html( $release['tag'] ); ?>
                            </a>
                        </h3>
                        <?php if ( $excerpt ) : ?>
                            <p class="update-card__excerpt"><?php echo wp_kses_post( $excerpt ); ?></p>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

    </div>
</section>
<?php endif; ?>


<!-- ==========================================================================
     LATEST UPDATES — only shown when real posts exist
     ========================================================================== -->
<?php
$recent_posts = new WP_Query( array(
    'posts_per_page' => 3,
    'post_status'    => 'publish',
) );

if ( $recent_posts->have_posts() ) :
?>
<section class="updates section">
    <div class="container">

        <div class="section-header">
            <h2>Latest Updates</h2>
            <p>News, releases, and project updates.</p>
        </div>

        <div class="grid-3">
            <?php while ( $recent_posts->have_posts() ) : $recent_posts->the_post(); ?>
                <article class="update-card">
                    <div class="update-card__image">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <?php the_post_thumbnail( 'medium_large' ); ?>
                        <?php endif; ?>
                    </div>
                    <div class="update-card__body">
                        <div class="update-card__date">
                            <?php echo esc_html( get_the_date( 'F j, Y' ) ); ?>
                        </div>
                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <p class="update-card__excerpt"><?php the_excerpt(); ?></p>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>

    </div>
</section>
<?php
wp_reset_postdata();
endif;
?>


<!-- ==========================================================================
     CTA BANNER
     ========================================================================== -->
<section class="cta-banner">
    <div class="container">
        <div class="cta-banner__content">

            <h2>Built for Societies, by Someone Who Runs One</h2>
            <p>
                SocietyPress was born from firsthand experience managing a
                genealogical society's website and membership. It's free software,
                freely given. Donations are never expected, always appreciated.
            </p>

            <div class="cta-banner__actions">
                <a href="<?php echo esc_url( home_url( '/donate/' ) ); ?>" class="btn btn-primary btn-lg">
                    Support the Project
                </a>
                <a href="<?php echo esc_url( home_url( '/about/' ) ); ?>" class="btn btn-outline btn-lg">
                    About SocietyPress
                </a>
            </div>

        </div>
    </div>
</section>

<?php get_footer(); ?>
