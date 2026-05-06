<?php
/**
 * Sitemap Page Template (page-sitemap.php)
 *
 * Human-readable sitemap. WordPress 5.5+ auto-generates the machine-readable
 * XML sitemap at /wp-sitemap.xml; this page exists for humans who want to
 * see the whole site at a glance.
 *
 * Groups are hand-curated so related pages stay together (not just
 * alphabetical). Add new pages to the relevant group below as they're
 * published.
 *
 * @package getsocietypress
 * @version 0.04d
 */

defined( 'ABSPATH' ) || exit;

get_header();

/*
 * Sitemap groups. Each group is ['heading' => ..., 'links' => [ [slug, label, description], ... ]].
 * Descriptions are optional (render as small gray text under the link).
 */
$gsp_sitemap = array(

    array(
        'heading' => 'Learn about SocietyPress',
        'links'   => array(
            array( '/', 'Home', 'The overview &mdash; start here.' ),
            array( '/features/', 'Features', '16 feature modules, one platform.' ),
            array( '/requirements/', 'Requirements', 'What you need on your server.' ),
            array( '/showcase/', 'Showcase', 'The five bundled child themes plus real society sites as they come online.' ),
            array( '/about/', 'About', 'Why the project exists and who is behind it.' ),
        ),
    ),

    array(
        'heading' => 'Get SocietyPress',
        'links'   => array(
            array( '/download/', 'Download', 'The latest stable release.' ),
            array( '/installation/', 'Installation Guide', 'Step-by-step, non-technical.' ),
            array( '/setup/', 'First-Time Setup', 'The 3-step wizard that gets your site usable.' ),
            array( '/changelog/', 'Changelog', 'Release notes written for society admins.' ),
        ),
    ),

    array(
        'heading' => 'Documentation &amp; Help',
        'links'   => array(
            array( '/docs/', 'Documentation Hub', 'Category-by-category summaries and guides.' ),
            array( '/faq/', 'Frequently Asked Questions', null ),
            array( '/ens-migration/', 'Moving from ENS', 'Migration guide for EasyNetSites societies.' ),
            array( '/docs/ens-migration-guide/', 'ENS Migration — Full Guide', 'Step-by-step walkthrough.' ),
            array( '/docs/troubleshooting/', 'Troubleshooting', 'The ten issues society admins hit most, with fixes.' ),
        ),
    ),

    array(
        'heading' => 'Get Involved',
        'links'   => array(
            array( '/community/', 'Community', 'How to connect with other SocietyPress users.' ),
            array( '/feedback/', 'Feedback', 'General feedback and comments.' ),
            array( '/bug-reports/', 'Report a Bug', null ),
            array( '/feature-requests/', 'Request a Feature', null ),
            array( '/roadmap/', 'Roadmap', 'What we\'re working on next.' ),
            array( '/donate/', 'Donate', 'Support continued development.' ),
            array( '/sponsors/', 'Sponsors &amp; Contributors', null ),
            array( '/contact/', 'Contact', null ),
        ),
    ),

    array(
        'heading' => 'Site Information',
        'links'   => array(
            array( '/privacy-policy/', 'Privacy Policy', null ),
            array( '/terms/', 'Terms of Use', null ),
            array( '/accessibility/', 'Accessibility Statement', null ),
            array( '/status/', 'Status', null ),
        ),
    ),

);
?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <h1 class="page-hero__title">Sitemap</h1>
            <p class="page-hero__subtitle">
                Every page on getsocietypress.org, grouped by purpose.
            </p>
        </div>
    </div>
</section>

<section class="sitemap-page section">
    <div class="container container--narrow">

        <?php foreach ( $gsp_sitemap as $group ) : ?>
            <div class="sitemap-group">
                <h2 class="sitemap-group__heading"><?php echo wp_kses_post( $group['heading'] ); ?></h2>
                <ul class="sitemap-group__list">
                    <?php foreach ( $group['links'] as $link ) :
                        list( $slug, $label, $description ) = array_pad( $link, 3, null );
                    ?>
                        <li class="sitemap-group__item">
                            <a href="<?php echo esc_url( home_url( $slug ) ); ?>" class="sitemap-group__link">
                                <?php echo wp_kses_post( $label ); ?>
                            </a>
                            <?php if ( $description ) : ?>
                                <span class="sitemap-group__description"><?php echo wp_kses_post( $description ); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

        <div class="sitemap-page__footnote">
            <p>
                Looking for the machine-readable XML sitemap for your search
                engine? It's at
                <a href="<?php echo esc_url( home_url( '/sitemap.xml' ) ); ?>">/sitemap.xml</a>.
            </p>
        </div>

    </div>
</section>

<?php get_footer(); ?>
