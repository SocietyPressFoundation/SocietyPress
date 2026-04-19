<?php
/**
 * Site Footer
 *
 * Five-column layout: branding + tagline on the left, four link columns
 * (Product, Resources, Community, Legal) on the right. Copyright bar
 * at bottom with security.txt + sitemap links.
 *
 * All colors use CSS custom properties — nothing is hardcoded here.
 *
 * @package getsocietypress
 * @version 0.04d
 */

defined( 'ABSPATH' ) || exit;
?>

</main><!-- #main-content, opened in header.php -->

<!-- ==========================================================================
     SITE FOOTER
     ========================================================================== -->
<footer class="site-footer" role="contentinfo">
    <div class="container">

        <!-- Footer Link Grid — branding column + four link columns -->
        <div class="footer-grid footer-grid--five">

            <!-- Column 1: Branding -->
            <div class="footer-brand">
                <div class="footer-brand__name">Society<span>Press</span></div>
                <p class="footer-brand__tagline">
                    A free, open-source platform built for genealogical societies.
                    No pricing. No tiers. Just community software, freely given.
                </p>
                <p class="footer-brand__demo">
                    <a href="https://demo.getsocietypress.org" target="_blank" rel="noopener">
                        &rarr; Try the live demo
                    </a>
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
                    <li><a href="<?php echo esc_url( home_url( '/roadmap/' ) ); ?>">Roadmap</a></li>
                </ul>
            </div>

            <!-- Column 3: Resources -->
            <div class="footer-links">
                <h4>Resources</h4>
                <ul>
                    <li><a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>">Documentation</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>">FAQ</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/installation/' ) ); ?>">Installation</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/setup/' ) ); ?>">First-Time Setup</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/ens-migration/' ) ); ?>">ENS Migration</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/status/' ) ); ?>">Status</a></li>
                </ul>
            </div>

            <!-- Column 4: Community -->
            <div class="footer-links">
                <h4>Community</h4>
                <ul>
                    <li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/donate/' ) ); ?>">Donate</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/sponsors/' ) ); ?>">Sponsors &amp; Contributors</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/bug-reports/' ) ); ?>">Report a Bug</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/feature-requests/' ) ); ?>">Request a Feature</a></li>
                </ul>
            </div>

            <!-- Column 5: Legal -->
            <div class="footer-links">
                <h4>Legal</h4>
                <ul>
                    <li><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">Privacy Policy</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">Terms of Use</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/accessibility/' ) ); ?>">Accessibility</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/.well-known/security.txt' ) ); ?>">security.txt</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/sitemap/' ) ); ?>">Sitemap</a></li>
                </ul>
            </div>

        </div>

        <!-- Copyright Bar -->
        <div class="footer-bottom">
            <span>&copy; <?php echo esc_html( date( 'Y' ) ); ?> SocietyPress. Released under the GPL v2 license.</span>
            <div class="footer-bottom__links">
                <a href="https://github.com/charles-stricklin/SocietyPress" rel="noopener">GitHub</a>
                <a href="<?php echo esc_url( home_url( '/wp-sitemap.xml' ) ); ?>">XML Sitemap</a>
            </div>
        </div>

    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
