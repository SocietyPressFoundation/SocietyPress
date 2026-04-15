<?php
/**
 * Sponsors & Contributors Page Template (page-sponsors.php)
 *
 * Recognition page. Starts deliberately small — it's better to ship an
 * honest "here's who's helped so far" than a fake wall of logos. Add
 * names to the arrays below as sponsors and contributors come on board.
 *
 * @package getsocietypress
 * @version 0.04d
 */

defined( 'ABSPATH' ) || exit;

get_header();

/*
 * Contributor lists. Edit these arrays to add people and organizations.
 * Kept in code (not WP admin) so recognition travels with the repo.
 */
$gsp_founding_society = array(
    array(
        'name' => 'the society',
        'url'  => 'https://www.rootsweb.com/~upstream-society/',
        'role' => 'Founding society &mdash; the organization whose needs prompted SocietyPress to exist in the first place, and whose members are the primary field-test site.',
    ),
);

$gsp_contributors = array(
    array(
        'name'        => 'Charles Stricklin',
        'role'        => 'Creator and lead developer',
        'description' => 'Writes the code, runs the project, attends the conferences.',
    ),
    array(
        'name'        => 'a contributor',
        'role'        => 'ENS feedback and field testing',
        'description' => 'the society webmaster whose experience on ENS informed the ENS migration path and the 73-field CSV importer.',
    ),
);

$gsp_sponsors = array(
    // Empty for now. Add entries like:
    // array( 'name' => 'Society Name', 'tier' => 'Supporter', 'url' => 'https://example.org' ),
);
?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <h1 class="page-hero__title">Sponsors &amp; Contributors</h1>
            <p class="page-hero__subtitle">
                The people and organizations making SocietyPress possible.
            </p>
        </div>
    </div>
</section>

<section class="sponsors-intro section">
    <div class="container container--narrow">
        <p>
            SocietyPress is free software in the truest sense: free of charge,
            freely licensed, and freely modifiable. It's kept alive by a small
            group of dedicated people and the societies that put it to work.
            This page exists to recognize them.
        </p>
    </div>
</section>

<!-- Founding society -->
<section class="sponsors-section section">
    <div class="container container--narrow">

        <h2 class="sponsors-section__heading">Founding Society</h2>
        <p class="sponsors-section__lede">
            SocietyPress started inside a working genealogical society, not
            an incubator or a venture fund.
        </p>

        <div class="sponsors-list">
            <?php foreach ( $gsp_founding_society as $org ) : ?>
                <div class="sponsors-card sponsors-card--featured">
                    <h3 class="sponsors-card__name">
                        <?php if ( ! empty( $org['url'] ) ) : ?>
                            <a href="<?php echo esc_url( $org['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $org['name'] ); ?></a>
                        <?php else : ?>
                            <?php echo esc_html( $org['name'] ); ?>
                        <?php endif; ?>
                    </h3>
                    <p class="sponsors-card__role"><?php echo wp_kses_post( $org['role'] ); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- Contributors -->
<section class="sponsors-section sponsors-section--alt section">
    <div class="container container--narrow">

        <h2 class="sponsors-section__heading">Contributors</h2>
        <p class="sponsors-section__lede">
            People whose time, expertise, or reality-check feedback has
            shaped the project directly.
        </p>

        <div class="sponsors-list">
            <?php foreach ( $gsp_contributors as $person ) : ?>
                <div class="sponsors-card">
                    <h3 class="sponsors-card__name"><?php echo esc_html( $person['name'] ); ?></h3>
                    <div class="sponsors-card__role-label"><?php echo esc_html( $person['role'] ); ?></div>
                    <p class="sponsors-card__description"><?php echo wp_kses_post( $person['description'] ); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- Sponsors (financial supporters) -->
<section class="sponsors-section section">
    <div class="container container--narrow">

        <h2 class="sponsors-section__heading">Financial Supporters</h2>

        <?php if ( ! empty( $gsp_sponsors ) ) : ?>
            <div class="sponsors-list">
                <?php foreach ( $gsp_sponsors as $sponsor ) : ?>
                    <div class="sponsors-card">
                        <h3 class="sponsors-card__name">
                            <?php if ( ! empty( $sponsor['url'] ) ) : ?>
                                <a href="<?php echo esc_url( $sponsor['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $sponsor['name'] ); ?></a>
                            <?php else : ?>
                                <?php echo esc_html( $sponsor['name'] ); ?>
                            <?php endif; ?>
                        </h3>
                        <?php if ( ! empty( $sponsor['tier'] ) ) : ?>
                            <div class="sponsors-card__role-label"><?php echo esc_html( $sponsor['tier'] ); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="sponsors-empty">
                <p>
                    No financial sponsors yet &mdash; we're brand new at
                    this, and no one's had the chance. If your society or
                    your organization wants to be the first, a donation of
                    any amount lets us credit you here.
                </p>
                <a href="<?php echo esc_url( home_url( '/donate/' ) ); ?>" class="btn btn-primary">
                    Donate
                </a>
            </div>
        <?php endif; ?>

    </div>
</section>

<!-- Want to help? -->
<section class="sponsors-cta">
    <div class="container">
        <div class="sponsors-cta__content">
            <h2>Want to be listed here?</h2>
            <p>
                There are three ways to end up on this page: donate toward
                the project's hosting and conference costs, contribute
                substantively to the code or documentation, or be a society
                running SocietyPress whose feedback shapes what we build
                next. Any of those earns you recognition here.
            </p>
            <div class="sponsors-cta__actions">
                <a href="<?php echo esc_url( home_url( '/donate/' ) ); ?>" class="btn btn-primary btn-lg">
                    Donate
                </a>
                <a href="<?php echo esc_url( home_url( '/community/' ) ); ?>" class="btn btn-outline btn-lg">
                    Get in Touch
                </a>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
