<?php
/**
 * Public Roadmap Page Template (page-roadmap.php)
 *
 * User-facing roadmap — four columns: Shipped, Working On, Up Next,
 * Someday/Maybe. Translated from the engineering TO-DO into society-admin
 * language. Update by editing the arrays below.
 *
 * @package getsocietypress
 * @version 0.04d
 */

defined( 'ABSPATH' ) || exit;

get_header();

$gsp_roadmap = array(

    'shipped' => array(
        'heading'     => 'Shipped',
        'description' => 'Everything in SocietyPress today &mdash; proven, in production, in the download.',
        'items'       => array(
            'Membership management with individual and organizational members',
            'Events engine with categories, registrations, waitlists, Stripe/PayPal payments, iCal export',
            'Genealogical record collections with GENRECORD and GEDCOM import/export',
            'Research library catalog (OPAC-style)',
            'Newsletter archive with PDF uploads and member-restricted downloads',
            'Page builder with 21 widget types',
            'Design system with 7 color pickers and live preview',
            'Committees, officers, and volunteer tracking',
            'Donations with Stripe and PayPal',
            'Photos &amp; videos with nested folders',
            'Blast email for member communications',
            'Online balloting with audit trail',
            'One-click full-site export',
            'Progressive Web App (PWA) support',
            'XChaCha20-Poly1305 encryption for sensitive member fields',
            'GDPR export and erasure integration',
            '8 role templates across 10 access areas',
            '5 child themes (Heritage, Coastline, Prairie, Ledger, Parlor)',
            'One-click installer (sp-installer.php)',
            'ENS migration path with CSV import of the standard 73-field export',
        ),
    ),

    'working' => array(
        'heading'     => 'Working On',
        'description' => 'Active right now. These are the things getting code changes this week.',
        'items'       => array(
            'Long-form documentation articles (each module gets its own guide)',
            'Store checkout &mdash; catalog display works; Stripe checkout is the next ship',
            'Records imports for the more common historical record formats',
            'Remaining i18n gaps across events, page builder, volunteers, store, donations, records, imports',
            'Marketing site polish &mdash; you\'re reading some of it right now',
        ),
    ),

    'next' => array(
        'heading'     => 'Up Next',
        'description' => 'On deck after the current work wraps. Roughly the next 1&ndash;3 months.',
        'items'       => array(
            'GDPR export coverage for the donations table',
            'Committees dedicated admin menu entry',
            'Video walkthroughs for installation, setup wizard, and first-month operation',
            'Downloadable PDF one-pagers for board members, treasurers, and librarians',
            'Softaculous installer package',
            'Mailing list for release announcements and security advisories',
            'Contributor recognition for early adopters',
            'More migration guides &mdash; Blue Crab, Wild Apricot, static-HTML-to-SocietyPress',
            'RootsTech 2027 launch preparation',
        ),
    ),

    'someday' => array(
        'heading'     => 'Someday / Maybe',
        'description' => 'Ideas we like but haven\'t committed to. No dates. Feedback on any of these is especially welcome.',
        'items'       => array(
            'Mobile admin app (iOS and Android) for event check-in',
            'Deep integration with FamilySearch and Ancestry (carefully &mdash; neither has a welcoming API)',
            'Built-in transcription workbench for society record projects',
            'Federated society-of-societies directory',
            'AI-assisted record parsing (only if it stays local and doesn\'t send member data to third parties)',
            'Native Spanish, French, and German translations (helped along by better i18n coverage)',
            'Accessibility statement at WCAG 2.2 AAA for a subset of pages',
        ),
    ),

    'wontdo' => array(
        'heading'     => 'Deliberately Not Doing',
        'description' => 'Things we\'ve considered and chosen against, so you know where we stand.',
        'items'       => array(
            'A paid &ldquo;Pro&rdquo; version &mdash; the project is GPL forever',
            'A hosted SaaS version where we run your site for you',
            'An app store for paid add-ons',
            'Gutenberg block editor support (conflicts with the page builder)',
            'Cross-site tracking or &ldquo;phone home&rdquo; analytics',
            'Partnerships with ad networks or affiliate marketing',
        ),
    ),

);
?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <h1 class="page-hero__title">Roadmap</h1>
            <p class="page-hero__subtitle">
                What we've shipped, what we're building, what's next &mdash;
                and what we've deliberately said no to.
            </p>
        </div>
    </div>
</section>

<section class="roadmap-page section">
    <div class="container">

        <?php foreach ( $gsp_roadmap as $key => $col ) : ?>
            <div class="roadmap-column roadmap-column--<?php echo esc_attr( $key ); ?>">
                <h2 class="roadmap-column__heading"><?php echo esc_html( $col['heading'] ); ?></h2>
                <p class="roadmap-column__description"><?php echo wp_kses_post( $col['description'] ); ?></p>
                <ul class="roadmap-column__items">
                    <?php foreach ( $col['items'] as $item ) : ?>
                        <li><?php echo wp_kses_post( $item ); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

    </div>
</section>

<section class="roadmap-cta">
    <div class="container">
        <div class="roadmap-cta__content">
            <h2>See something missing?</h2>
            <p>
                If your society needs something that isn't here, tell us.
                Feature requests from people actually running SocietyPress
                are how the roadmap gets written.
            </p>
            <div class="roadmap-cta__actions">
                <a href="<?php echo esc_url( home_url( '/feature-requests/' ) ); ?>" class="btn btn-primary btn-lg">
                    Request a Feature
                </a>
                <a href="<?php echo esc_url( home_url( '/bug-reports/' ) ); ?>" class="btn btn-secondary btn-lg">
                    Report a Bug
                </a>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
