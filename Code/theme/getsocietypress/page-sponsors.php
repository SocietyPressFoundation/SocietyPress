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
$gsp_contributors = array(
    array(
        'name'        => 'Charles Stricklin',
        'role'        => 'Creator, developer, and sole maintainer',
        'description' => 'Writes every line of code, runs the project, attends the conferences. SocietyPress is his work, start to finish.',
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
                Three ways to end up on this page: donate toward the
                project's hosting and conference costs, contribute code
                or documentation, or be one of the early-adopter
                societies whose feedback shapes what gets built next.
                Any of those earns you recognition here.
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
