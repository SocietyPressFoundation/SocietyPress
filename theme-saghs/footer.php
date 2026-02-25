<?php
/**
 * the society Child Theme — Footer
 *
 * WHY this overrides the parent: the society needs a rich footer with org info,
 * contact details, nav links, and a "Powered by SocietyPress" credit line.
 * The parent footer is just a one-line copyright.
 *
 * Layout:
 * - Burgundy accent bar (4px)
 * - Two-column body: org info (left) + quick links (right)
 * - Bottom bar: copyright (left) + "Powered by SocietyPress" (right)
 * - Cream background throughout
 *
 * @package the society
 * @since   0.01d
 */
?>

    <footer class="site-footer">

        <!-- Burgundy accent line at top of footer -->
        <div class="society-footer-accent"></div>

        <!-- Two-column footer body -->
        <div class="society-footer-inner">

            <!-- Left column: Organization info & contact -->
            <div class="society-footer-info">
                <h3><?php bloginfo( 'name' ); ?></h3>
                <p>
                    Promoting genealogical research through education, resources,
                    and community since 1959.
                </p>
                <p>
                    911 Melissa Drive<br>
                    Springfield, Texas 78213
                </p>
                <p>
                    Phone: <a href="tel:+12103425242">210-342-5242</a><br>
                    Email: <a href="mailto:askus@upstream-society.org">askus@upstream-society.org</a>
                </p>
            </div>

            <!-- Right column: Quick links (from footer menu or hardcoded fallback) -->
            <div class="society-footer-links">
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
                        <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a></li>
                        <li><a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>">About Us</a></li>
                        <li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact Us</a></li>
                        <li><a href="<?php echo esc_url( home_url( '/library/' ) ); ?>">Library</a></li>
                    </ul>
                <?php endif; ?>
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
