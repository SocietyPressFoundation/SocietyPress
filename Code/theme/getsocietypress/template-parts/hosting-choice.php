<?php
/**
 * "Who does what?" — the self-host vs. hosted comparison table.
 *
 * Shared by the download page and the Hosting page so the two pages can
 * never describe the choice differently. Charles's brief was to make the
 * difference "stupidly obvious": one row per chore, and each cell answers
 * a single question — who does this? — in a word or two.
 *
 * Plain <table> on purpose: it is genuinely tabular data, and screen
 * readers announce the row and column headers for every cell.
 *
 * @package getsocietypress
 */

defined( 'ABSPATH' ) || exit;

$gsp_from_price = gsp_get_hosting_from_price();
?>

<section class="host-compare section" id="compare">
    <div class="container">

        <div class="section-header">
            <h2>Who does what?</h2>
            <p>Same software either way. The only difference is who takes care of the website.</p>
        </div>

        <div class="host-compare__wrap">
            <table class="host-compare__table">
                <thead>
                    <tr>
                        <td class="host-compare__corner"></td>
                        <th scope="col">
                            <span class="host-compare__option">Option A</span>
                            Host it yourself
                        </th>
                        <th scope="col" class="host-compare__col--hosted">
                            <span class="host-compare__option">Option B</span>
                            We host it for you
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th scope="row">The SocietyPress software</th>
                        <td><strong>Free</strong></td>
                        <td class="host-compare__col--hosted"><strong>Free</strong></td>
                    </tr>
                    <tr>
                        <th scope="row">Find and pay a web host</th>
                        <td>You</td>
                        <td class="host-compare__col--hosted"><strong>Us</strong></td>
                    </tr>
                    <tr>
                        <th scope="row">Install SocietyPress</th>
                        <td>You, with the one-click installer</td>
                        <td class="host-compare__col--hosted"><strong>Us</strong></td>
                    </tr>
                    <tr>
                        <th scope="row">Install updates</th>
                        <td>You</td>
                        <td class="host-compare__col--hosted"><strong>Us</strong></td>
                    </tr>
                    <tr>
                        <th scope="row">Daily backups</th>
                        <td>You, or your host</td>
                        <td class="host-compare__col--hosted"><strong>Us</strong></td>
                    </tr>
                    <tr>
                        <th scope="row">Security certificate (the padlock)</th>
                        <td>You, or your host</td>
                        <td class="host-compare__col--hosted"><strong>Us</strong></td>
                    </tr>
                    <tr>
                        <th scope="row">Help when something goes wrong</th>
                        <td>Documentation and community forums</td>
                        <td class="host-compare__col--hosted"><strong>Us, by email</strong></td>
                    </tr>
                    <tr>
                        <th scope="row">Who owns your data</th>
                        <td>You</td>
                        <td class="host-compare__col--hosted">You</td>
                    </tr>
                    <tr class="host-compare__row--cost">
                        <th scope="row">What you pay</th>
                        <td>Your web host, usually $5&ndash;15 a month</td>
                        <td class="host-compare__col--hosted">From $<?php echo esc_html( $gsp_from_price ); ?> a month, all in</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="host-compare__corner"></td>
                        <td>
                            <a href="<?php echo esc_url( home_url( '/download/#download' ) ); ?>" class="btn btn-outline">Download free</a>
                        </td>
                        <td class="host-compare__col--hosted">
                            <a href="<?php echo esc_url( home_url( '/hosting/#plans' ) ); ?>" class="btn btn-primary">See hosting plans</a>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>
</section>
