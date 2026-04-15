<?php
/**
 * Site Footer
 *
 * Four-column layout: branding + tagline on the left, three link columns
 * (Product, Resources, Community) on the right. Copyright bar at bottom.
 *
 * All colors use CSS custom properties — nothing is hardcoded here.
 *
 * @package getsocietypress
 * @version 0.02d
 */

defined( 'ABSPATH' ) || exit;
?>

<!-- ==========================================================================
     SITE FOOTER
     ========================================================================== -->
<footer class="site-footer" role="contentinfo">
    <div class="container">

        <!-- Footer Link Grid — branding column + three link columns -->
        <div class="footer-grid">

            <!-- Column 1: Branding -->
            <div class="footer-brand">
                <div class="footer-brand__name">Society<span>Press</span></div>
                <p class="footer-brand__tagline">
                    A free, open-source platform built for genealogical societies.
                    No pricing. No tiers. Just community software, freely given.
                </p>
            </div>

            <!-- Column 2: Product Links -->
            <div class="footer-links">
                <h4>Product</h4>
                <ul>
                    <li><a href="<?php echo esc_url( home_url( '/features/' ) ); ?>">Features</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/requirements/' ) ); ?>">Requirements</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/download/' ) ); ?>">Download</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/showcase/' ) ); ?>">Showcase</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/changelog/' ) ); ?>">Changelog</a></li>
                </ul>
            </div>

            <!-- Column 3: Resources -->
            <div class="footer-links">
                <h4>Resources</h4>
                <ul>
                    <li><a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>">Documentation</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>">FAQ</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/ens-migration/' ) ); ?>">ENS Migration</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/news/' ) ); ?>">News &amp; Updates</a></li>
                </ul>
            </div>

            <!-- Column 4: Community -->
            <div class="footer-links">
                <h4>Community</h4>
                <ul>
                    <li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/donate/' ) ); ?>">Donate</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/community/' ) ); ?>">Community</a></li>
                </ul>
            </div>

        </div>

        <!-- Copyright Bar -->
        <div class="footer-bottom">
            <span>&copy; <?php echo esc_html( date( 'Y' ) ); ?> SocietyPress. Released under the GPL v2 license.</span>
            <div class="footer-bottom__links">
                <a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">Privacy Policy</a>
                <a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">Terms</a>
            </div>
        </div>

    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
