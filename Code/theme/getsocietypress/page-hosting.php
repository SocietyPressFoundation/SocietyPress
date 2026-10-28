<?php
/**
 * Hosting Page Template (page-hosting.php)
 *
 * Option B: SocietyPress Hosting — we run the website, the society runs
 * the society. Paid hosting SERVICE; the software itself stays free and
 * GPL either way, and this page says so before it shows a single price.
 *
 * WHY the page is this explicit about limits (what's extra, how support
 * works, what happens when you leave): at these prices, unbounded support
 * would sink the service. Boards also respect a plain answer to "what if
 * we want out?" — especially societies burned by vendor lock-in.
 *
 * Sections:
 * 1. Hero — what it is, two buttons (get started / host it yourself)
 * 2. Who does what? — shared comparison table
 * 3. Plans — three tiers by membership, from gsp_get_hosting_plans()
 * 4. Included / costs extra
 * 5. Email — forwarding, mailbox limits, big mailings
 * 6. Moving in & leaving
 * 7. How support works
 * 8. CTA + fine print (who provides the service)
 *
 * @package getsocietypress
 * @version 0.01d
 */

defined( 'ABSPATH' ) || exit;

get_header();

$gsp_plans      = gsp_get_hosting_plans();
$gsp_signup_url = gsp_get_hosting_signup_url();
?>

<!-- ==========================================================================
     1. HOSTING HERO
     ========================================================================== -->
<section class="dl-hero host-hero">
    <div class="container">
        <div class="dl-hero__content">

            <p class="host-fork__label">Option B &middot; We host it for you</p>

            <h1 class="dl-hero__title">SocietyPress Hosting</h1>

            <p class="dl-hero__subtitle">
                We run the website. You run your society. Hosting, installation,
                updates, daily backups and support, all in one monthly or yearly price.
            </p>

            <div class="host-hero__actions">
                <a href="#plans" class="btn btn-primary btn-xl">See plans and prices</a>
                <a href="<?php echo esc_url( home_url( '/download/' ) ); ?>" class="btn btn-outline btn-xl">Or host it yourself, free</a>
            </div>

            <p class="dl-hero__meta dl-hero__meta--footer">
                <span>The SocietyPress software is free either way. You&rsquo;re paying for the service, not the software.</span>
            </p>

        </div>
    </div>
</section>


<!-- ==========================================================================
     2. WHO DOES WHAT? — shared with the download page
     ========================================================================== -->
<?php get_template_part( 'template-parts/hosting-choice' ); ?>


<!-- ==========================================================================
     3. PLANS
     Priced by membership size, the way boards already budget. Every plan
     includes everything in section 4 — the tiers differ only in storage.
     ========================================================================== -->
<section class="host-plans section" id="plans">
    <div class="container">

        <div class="section-header">
            <h2>Plans and prices</h2>
            <p>Every plan includes everything. Pick the one that fits your membership. Pay yearly and get two months free.</p>
        </div>

        <div class="host-plans__grid">
            <?php foreach ( $gsp_plans as $gsp_plan ) : ?>
                <div class="host-plans__card">
                    <h3 class="host-plans__name"><?php echo esc_html( $gsp_plan['name'] ); ?></h3>
                    <p class="host-plans__members"><?php echo esc_html( $gsp_plan['members'] ); ?></p>
                    <p class="host-plans__price">
                        $<?php echo esc_html( $gsp_plan['monthly'] ); ?><span> / month</span>
                    </p>
                    <p class="host-plans__annual">
                        or $<?php echo esc_html( $gsp_plan['annual'] ); ?> / year
                    </p>
                    <ul class="host-plans__list">
                        <li><?php echo esc_html( $gsp_plan['storage'] ); ?> of storage for photos, newsletters and files</li>
                        <li><?php echo esc_html( $gsp_plan['mailboxes'] ); ?> email mailboxes at your domain</li>
                        <li>Unlimited email forwarding</li>
                        <li>Everything listed below</li>
                    </ul>
                    <a href="<?php echo esc_url( $gsp_signup_url ); ?>" class="btn btn-primary host-plans__btn">Get started</a>
                </div>
            <?php endforeach; ?>
        </div>

        <p class="host-plans__note">
            No setup fee. No contract. Not sure which plan? Pick by your current membership; you can move up any time.
        </p>

    </div>
</section>


<!-- ==========================================================================
     4. INCLUDED / COSTS EXTRA
     ========================================================================== -->
<section class="host-scope section">
    <div class="container">

        <div class="host-scope__grid">

            <div class="host-scope__card">
                <h2>Included in every plan</h2>
                <ul class="host-scope__list host-scope__list--yes">
                    <li>Fast, secure web hosting</li>
                    <li>SocietyPress installed and set up for you</li>
                    <li>Every SocietyPress update, installed for you</li>
                    <li>Daily backups, stored off-site</li>
                    <li>Free security certificate (the padlock in the address bar)</li>
                    <li>Email at your own domain, set up for you on request (see below)</li>
                    <li>Help by email when something isn&rsquo;t working</li>
                </ul>
            </div>

            <div class="host-scope__card">
                <h2>Available for an extra charge</h2>
                <ul class="host-scope__list">
                    <li>
                        <strong>A domain name</strong>, like yoursociety.org &mdash;
                        we can register it for you, about $20 a year. Already have one? Use it at no charge.
                    </li>
                    <?php if ( gsp_hosting_migration_offer_open() ) : ?>
                        <li>
                            <strong>We move your old site for you</strong> &mdash; members, events,
                            newsletters and your main pages. $150 for societies up to 250 members,
                            $300 above that. <strong>Available through <?php echo esc_html( gsp_get_hosting_migration_deadline_label() ); ?>.</strong>
                            Or import your member spreadsheet yourself, free, any time.
                        </li>
                    <?php endif; ?>
                    <li>
                        <strong>Hands-on work</strong> &mdash; building pages, entering data,
                        redesigning menus. $50 an hour, and we&rsquo;ll always tell you
                        the estimate first.
                    </li>
                </ul>
            </div>

        </div>

    </div>
</section>


<!-- ==========================================================================
     5. EMAIL
     Spelled out because email is the one shared resource that can hurt
     every hosted society at once (see gsp_get_hosting_plans() for why).
     ========================================================================== -->
<section class="host-email section">
    <div class="container">

        <div class="section-header">
            <h2>Email at your own domain</h2>
            <p>Official addresses like president@yoursociety.org, set up for you.</p>
        </div>

        <div class="host-support__grid">
            <div class="host-support__item">
                <h3>Unlimited forwarding</h3>
                <p>
                    president@yoursociety.org can deliver straight to your president&rsquo;s
                    own inbox. When officers change, we point it at the new one. This is
                    what most societies need, and there&rsquo;s no limit.
                </p>
            </div>
            <div class="host-support__item">
                <h3>Mailboxes when you need them</h3>
                <p>
                    If someone needs a full mailbox with its own password, each plan
                    includes some: <?php echo esc_html( implode( ', ', array_map( static function ( $p ) { return $p['mailboxes'] . ' on ' . $p['name']; }, $gsp_plans ) ) ); ?>.
                    Mail stored in them counts toward your plan&rsquo;s storage.
                </p>
            </div>
            <div class="host-support__item">
                <h3>Big newsletter mailings</h3>
                <p>
                    Sending a newsletter to hundreds of members goes through a free
                    email-sending service such as Brevo, so it actually reaches inboxes.
                    We&rsquo;ll help you connect it.
                </p>
            </div>
        </div>

    </div>
</section>


<!-- ==========================================================================
     6. MOVING IN & LEAVING
     The exit terms are deliberately as prominent as the entry terms.
     ========================================================================== -->
<section class="host-moves section">
    <div class="container">

        <div class="host-scope__grid">

            <div class="host-scope__card">
                <h2>Moving in</h2>
                <p>
                    Coming from EasyNetSites or another platform? SocietyPress imports your
                    member spreadsheet directly, and our
                    <a href="<?php echo esc_url( home_url( '/docs/ens-migration/' ) ); ?>">migration guide</a>
                    walks you through it.<?php if ( gsp_hosting_migration_offer_open() ) : ?> Or let us do the move for you, through <?php echo esc_html( gsp_get_hosting_migration_deadline_label() ); ?> (see above).<?php endif; ?>
                </p>
            </div>

            <div class="host-scope__card">
                <h2>Leaving</h2>
                <p>
                    <strong>Your society&rsquo;s data is yours.</strong> Leave whenever you like.
                    We&rsquo;ll hand you a complete copy of your site &mdash; every member,
                    event, record, photo and page &mdash; ready for any web host to set up.
                    No exit fee. No lock-in.
                </p>
                <p>
                    After you cancel, we keep your site for 30 days in case you change
                    your mind, then delete it permanently. Your new host handles setting
                    it up on their end.
                </p>
            </div>

        </div>

    </div>
</section>


<!-- ==========================================================================
     7. HOW SUPPORT WORKS
     Written plainly so expectations are set before anyone signs up.
     ========================================================================== -->
<section class="host-support section">
    <div class="container">

        <div class="section-header">
            <h2>How support works</h2>
        </div>

        <div class="host-support__grid">
            <div class="host-support__item">
                <h3>One contact per society</h3>
                <p>Your society names one person who emails us. It keeps requests clear and answers going to the right place.</p>
            </div>
            <div class="host-support__item">
                <h3>Answers within two business days</h3>
                <p>Usually sooner. If the site is down, that jumps the queue.</p>
            </div>
            <div class="host-support__item">
                <h3>We keep it running</h3>
                <p>Anything broken on our side, we fix at no charge. &ldquo;How do I&hellip;?&rdquo; questions are included too.</p>
            </div>
        </div>

    </div>
</section>


<!-- ==========================================================================
     8. CTA + FINE PRINT
     ========================================================================== -->
<section class="host-cta">
    <div class="container">
        <div class="host-cta__content">
            <h2>Ready to hand off the technical side?</h2>
            <p>
                Email us your society&rsquo;s name and roughly how many members you have.
                We&rsquo;ll get you set up.
            </p>
            <div class="host-hero__actions">
                <a href="<?php echo esc_url( $gsp_signup_url ); ?>" class="btn btn-primary btn-lg">Get started</a>
                <a href="<?php echo esc_url( home_url( '/download/' ) ); ?>" class="btn btn-outline btn-lg">Host it yourself, free</a>
            </div>
            <p class="host-cta__fineprint">
                SocietyPress Hosting is provided by Stricklin Development, LLC.
                The SocietyPress software is free and open source under the GPL,
                whether or not you use our hosting.
            </p>
        </div>
    </div>
</section>

<?php get_footer(); ?>
