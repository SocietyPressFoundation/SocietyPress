<?php
/**
 * Status Page Template (page-status.php)
 *
 * Lightweight status page. Where we'd surface incidents and planned
 * maintenance if any were active. No paid monitoring integration yet —
 * this is a stub with a manually-maintained incident log that lives in
 * the array below. Edit the array to record incidents as they occur.
 *
 * @package getsocietypress
 * @version 0.04d
 */

defined( 'ABSPATH' ) || exit;

get_header();

/*
 * Incident log. Newest first. Each entry:
 *   'date' — ISO date (YYYY-MM-DD)
 *   'title' — one-line summary
 *   'status' — 'resolved' | 'ongoing' | 'investigating'
 *   'description' — short paragraph explaining what happened and what was done
 */
$gsp_incidents = array(
    // No incidents yet.
);

/*
 * What we monitor. Update the 'status' field if anything goes red.
 * 'operational' | 'degraded' | 'outage'
 */
$gsp_components = array(
    array( 'name' => 'getsocietypress.org website',      'status' => 'operational' ),
    array( 'name' => 'Download endpoint (latest .zip)', 'status' => 'operational' ),
    array( 'name' => 'Installer (sp-installer.php)',     'status' => 'operational' ),
    array( 'name' => 'Demo site (demo.getsocietypress.org)', 'status' => 'operational' ),
    array( 'name' => 'Documentation',                     'status' => 'operational' ),
);

$gsp_has_incidents = ! empty( $gsp_incidents );
$gsp_any_ongoing   = false;
foreach ( $gsp_incidents as $inc ) {
    if ( in_array( $inc['status'], array( 'ongoing', 'investigating' ), true ) ) {
        $gsp_any_ongoing = true;
        break;
    }
}

$gsp_overall_class = $gsp_any_ongoing ? 'status-hero--alert' : 'status-hero--ok';
$gsp_overall_label = $gsp_any_ongoing ? 'Active incident' : 'All systems operational';
?>

<section class="status-hero <?php echo esc_attr( $gsp_overall_class ); ?>">
    <div class="container">
        <div class="status-hero__content">
            <div class="status-hero__indicator" aria-hidden="true"></div>
            <h1 class="status-hero__label"><?php echo esc_html( $gsp_overall_label ); ?></h1>
            <p class="status-hero__note">
                Last checked <?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), current_time( 'timestamp' ) ) ); ?>.
            </p>
        </div>
    </div>
</section>


<section class="status-components section">
    <div class="container container--narrow">

        <h2 class="status-section__heading">Components</h2>

        <ul class="status-components__list">
            <?php foreach ( $gsp_components as $component ) : ?>
                <li class="status-component status-component--<?php echo esc_attr( $component['status'] ); ?>">
                    <span class="status-component__name"><?php echo esc_html( $component['name'] ); ?></span>
                    <span class="status-component__state">
                        <?php
                        switch ( $component['status'] ) {
                            case 'operational': echo 'Operational'; break;
                            case 'degraded':    echo 'Degraded performance'; break;
                            case 'outage':      echo 'Outage'; break;
                            default:            echo esc_html( $component['status'] );
                        }
                        ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>

    </div>
</section>


<section class="status-incidents section">
    <div class="container container--narrow">

        <h2 class="status-section__heading">Incident history</h2>

        <?php if ( $gsp_has_incidents ) : ?>
            <ul class="status-incident-list">
                <?php foreach ( $gsp_incidents as $inc ) : ?>
                    <li class="status-incident status-incident--<?php echo esc_attr( $inc['status'] ); ?>">
                        <div class="status-incident__header">
                            <time class="status-incident__date"><?php echo esc_html( $inc['date'] ); ?></time>
                            <span class="status-incident__badge"><?php echo esc_html( ucfirst( $inc['status'] ) ); ?></span>
                        </div>
                        <h3 class="status-incident__title"><?php echo esc_html( $inc['title'] ); ?></h3>
                        <p class="status-incident__description"><?php echo wp_kses_post( $inc['description'] ); ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <div class="status-incidents__empty">
                <p>
                    No incidents to report. This page logs outages and
                    degraded service when they happen &mdash; as of now, the
                    slate is clean.
                </p>
            </div>
        <?php endif; ?>

    </div>
</section>


<section class="status-note">
    <div class="container container--narrow">
        <p>
            <strong>A note on what this page does and doesn't cover.</strong>
            This page tracks the availability of getsocietypress.org and its
            services. It does not monitor individual SocietyPress
            installations at societies &mdash; those are self-hosted and
            operated by each society independently. If your society's site
            is having problems, contact your hosting provider's support
            team. If you believe the issue is caused by SocietyPress itself,
            <a href="<?php echo esc_url( home_url( '/bug-reports/' ) ); ?>">file a bug report</a>.
        </p>
    </div>
</section>

<?php get_footer(); ?>
