<?php
/**
 * the society Child Theme — Footer
 *
 * WHY this overrides the parent: the society needs a rich 3-column footer matching
 * the reference site layout — org mission (left), quick links (center), and
 * contact info (right), all on a cream background with a burgundy accent bar.
 *
 * Layout:
 * - Burgundy accent bar (4px)
 * - Three-column body: mission (left) + quick links (center) + contact (right)
 * - Bottom bar: copyright (left) + "Powered by SocietyPress" (right)
 * - Cream background throughout
 *
 * @package the society
 * @since   0.02d
 */
?>

    <footer class="site-footer">

        <!-- Burgundy accent line at top of footer -->
        <div class="society-footer-accent"></div>

        <!-- Three-column footer body -->
        <div class="society-footer-inner">

            <!-- Column 1: Organization mission & identity -->
            <div class="society-footer-col society-footer-mission">
                <h3><?php bloginfo( 'name' ); ?></h3>
                <p>
                    Gateway to the resources of the Sample Genealogical and
                    Historical Society; promoting research, preserving records,
                    and increasing awareness through our library, publications,
                    and classes.
                </p>
            </div>

            <!-- Column 2: Quick links (from footer menu or hardcoded fallback) -->
            <div class="society-footer-col society-footer-links">
                <h3>Quick Links</h3>
                <?php if ( has_nav_menu( 'footer' ) ) : ?>
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'footer',
                        'container'      => false,
                        'depth'          => 1,
                    ]);
                    ?>
                <?php else : ?>
                    <!--
                        WHY hardcoded fallback: Until the admin creates a footer
                        menu in Appearance > Menus, we show sensible defaults so
                        the footer isn't empty. Once a footer menu is assigned,
                        these disappear and the menu takes over.
                    -->
                    <ul>
                        <li><a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>">About Us</a></li>
                        <li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact Us</a></li>
                        <li><a href="<?php echo esc_url( home_url( '/library/' ) ); ?>">Library Hours &amp; Location</a></li>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Column 3: Contact information -->
            <div class="society-footer-col society-footer-contact">
                <h3>Contact Us</h3>
                <p>
                    911 Melissa Drive<br>
                    Springfield, Texas 78213
                </p>
                <p>
                    Phone: <a href="tel:+12103425242">210-342-5242</a>
                </p>
                <p>
                    Email: <a href="mailto:askus@upstream-society.org">askus@upstream-society.org</a>
                </p>
            </div>

        </div>

        <!-- Bottom bar: copyright + credit -->
        <div class="society-footer-bottom">
            <span>&copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>. All rights reserved.</span>
            <span>Powered by <a href="https://getsocietypress.org">SocietyPress</a></span>
        </div>

    </footer>

</div><!-- .site -->

<?php wp_footer(); ?>
</body>
</html>
