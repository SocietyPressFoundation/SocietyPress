<?php
/**
 * SAGHS Child Theme — Footer
 *
 * WHY this overrides the parent: SAGHS needs a footer matching the reference
 * site (sb-saghs.ens-201.com) — org name and description on the left, a short
 * list of links on the right, contact details in the bottom bar, and the
 * society logo repeated at the very bottom.
 *
 * Layout:
 * - Burgundy accent bar (4px)
 * - Two-column body: org identity (left) + links (right)
 * - Bottom bar: address/phone/email (left) + copyright (right)
 * - Logo strip: logo (left)
 *
 * @package SAGHS
 * @since   0.04d
 */
?>

    <footer class="site-footer">

        <!-- Burgundy accent line at top of footer -->
        <div class="saghs-footer-accent"></div>

        <!-- Two-column footer body -->
        <div class="saghs-footer-inner">

            <!-- Left: Organization name & mission statement -->
            <div class="saghs-footer-col saghs-footer-mission">
                <h3><?php bloginfo( 'name' ); ?></h3>
                <p>
                    Gateway to the resources of the San Antonio Genealogical and
                    Historical Society; promoting research, preserving records,
                    and increasing awareness through our library, publications,
                    and classes.
                </p>
            </div>

            <!--
                Right: Quick links — no heading, just the links.
                WHY no heading: The reference site shows bare links without a
                column title. Keeps it clean and doesn't compete with the org
                name for visual weight.
            -->
            <div class="saghs-footer-col saghs-footer-links">
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

        </div>

        <!--
            Bottom bar: contact details on the left, copyright on the right.
            WHY contact is down here instead of in a column: The reference site
            puts address/phone/email in the footer bottom bar, not in the main
            body. This keeps the main area clean with just the mission + links.
        -->
        <div class="saghs-footer-bottom">
            <div class="saghs-footer-contact-info">
                <span>911 Melissa, San Antonio, Texas 78213, USA</span>
                <span class="saghs-footer-separator"></span>
                <span><a href="tel:+12103425242">210-342-5242</a></span>
                <span class="saghs-footer-separator"></span>
                <span><a href="mailto:askus@txsaghs.org">askus@txsaghs.org</a></span>
            </div>
            <div class="saghs-footer-copyright">
                <span>Copyright &copy; <?php echo date( 'Y' ); ?> by <?php bloginfo( 'name' ); ?>.</span>
                <span>All Rights Reserved.</span>
                <span>Powered by <a href="https://getsocietypress.org">SocietyPress</a></span>
            </div>
        </div>

        <!--
            Logo strip: repeats the society logo at the very bottom of the page.
            WHY: The reference site shows the logo again below the copyright bar
            as a visual bookend — it anchors the brand at both top and bottom.
        -->
        <div class="saghs-footer-logo-strip">
            <?php if ( has_custom_logo() ) : ?>
                <?php the_custom_logo(); ?>
            <?php endif; ?>
        </div>

    </footer>

</div><!-- .site -->

<?php wp_footer(); ?>
</body>
</html>
