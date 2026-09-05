<?php
/**
 * Theme Footer
 *
 * WHY: A polished three-column footer that pulls from SocietyPress settings
 * so it works out of the box without Harold configuring anything. Column 1
 * shows the organization's name, address, and contact info (from Organization
 * settings). Column 2 shows a footer navigation menu (registered in
 * functions.php). Column 3 shows social media icons. Below all three,
 * a copyright line and an optional "Powered by SocietyPress" credit.
 *
 * Everything is driven by CSS custom properties so colors and fonts are
 * controllable from the Design settings page.
 *
 * @package SocietyPress
 */

$sp = get_option( 'societypress_settings', [] );
$org_name    = $sp['organization_name']    ?? get_bloginfo( 'name' );
$org_address = $sp['organization_address'] ?? '';
$org_phone   = $sp['organization_phone']   ?? '';
$org_email   = $sp['organization_email']   ?? '';
?>

    <footer class="site-footer">
        <div class="footer-inner">

            <!-- Column 1: Organization info -->
            <div class="footer-col footer-col-info">
                <?php if ( is_active_sidebar( 'sp-footer-1' ) ) : ?>
                    <?php dynamic_sidebar( 'sp-footer-1' ); ?>
                <?php else : ?>
                <h3 class="footer-heading"><?php echo esc_html( $org_name ); ?></h3>
                <?php if ( $org_address ) : ?>
                    <p class="footer-text"><?php echo nl2br( esc_html( $org_address ) ); ?></p>
                <?php endif; ?>
                <?php if ( $org_phone ) : ?>
                    <p class="footer-text">
                        <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $org_phone ) ); ?>">
                            <?php echo esc_html( sp_format_phone( (string) $org_phone ) ); ?>
                        </a>
                    </p>
                <?php endif; ?>
                <?php if ( $org_email ) : ?>
                    <p class="footer-text">
                        <a href="mailto:<?php echo esc_attr( $org_email ); ?>">
                            <?php echo esc_html( $org_email ); ?>
                        </a>
                    </p>
                <?php endif; ?>
                <?php endif; ?>
                <?php
                /*
                 * Affiliation logos, "up beside the columns" placement — the
                 * slot that prints only when Website → Affiliations is set to
                 * it.
                 *
                 * WHY inside this first column rather than after the grid: the
                 * grid makes every column as tall as the longest one, and this
                 * column — name, address, phone, email — is normally the short
                 * one, so the empty footer collects underneath it. Rendering
                 * here puts the logos in that pocket; the plugin's stylesheet
                 * pushes them down to sit on the floor of the footer, level
                 * with the foot of the longest column beside them, costing the
                 * footer no extra height at all.
                 */
                if ( function_exists( 'sp_affiliation_logos' ) ) {
                    sp_affiliation_logos( 'inline' );
                }
                ?>
            </div>

            <!-- Column 2: Footer navigation menu -->
            <div class="footer-col footer-col-nav">
                <?php if ( is_active_sidebar( 'sp-footer-2' ) ) : ?>
                    <?php dynamic_sidebar( 'sp-footer-2' ); ?>
                <?php elseif ( has_nav_menu( 'footer' ) ) : ?>
                    <h3 class="footer-heading"><?php esc_html_e( 'Quick Links', 'societypress' ); ?></h3>
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'footer',
                        'container'      => 'nav',
                        'container_class' => 'footer-nav',
                        'depth'          => 1,
                        'fallback_cb'    => false,
                    ]);
                    ?>
                <?php elseif ( has_nav_menu( 'primary' ) ) : ?>
                    <!-- WHY: Fall back to the primary menu if no footer menu is set.
                         This way the footer has useful links on day one without
                         Harold needing to create a second menu. -->
                    <h3 class="footer-heading"><?php esc_html_e( 'Quick Links', 'societypress' ); ?></h3>
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'primary',
                        'container'      => 'nav',
                        'container_class' => 'footer-nav',
                        'depth'          => 1,
                        'fallback_cb'    => false,
                    ]);
                    ?>
                <?php endif; ?>
            </div>

            <!-- Column 3: Social media & connect -->
            <div class="footer-col footer-col-social">
                <?php if ( is_active_sidebar( 'sp-footer-3' ) ) : ?>
                    <?php dynamic_sidebar( 'sp-footer-3' ); ?>
                <?php else : ?>
                <h3 class="footer-heading"><?php esc_html_e( 'Connect', 'societypress' ); ?></h3>
                <?php if ( function_exists( 'sp_social_icons' ) ) : ?>
                    <?php sp_social_icons( 'footer' ); ?>
                <?php endif; ?>
                <p class="footer-text footer-tagline">
                    <?php echo esc_html( get_bloginfo( 'description' ) ?: '' ); ?>
                </p>
                <?php endif; ?>
            </div>

        </div>

        <?php
        /*
         * Affiliation logos — the organizations this society belongs to, set at
         * Website → Affiliations. Placed below the columns and above the
         * copyright line because that is where a badge row reads as belonging
         * to the whole site rather than to one column of it. Prints nothing at
         * all when none are configured, and nothing when the inline slot above
         * has already printed them.
         */
        if ( function_exists( 'sp_affiliation_logos' ) ) {
            sp_affiliation_logos( 'below' );
        }
        ?>

        <!-- Bottom bar: copyright + powered-by -->
        <div class="footer-bottom">
            <p>
                &copy; <?php echo wp_date( 'Y' ); ?> <?php echo esc_html( $org_name ); ?>.
                <?php esc_html_e( 'All rights reserved.', 'societypress' ); ?>
            </p>
        </div>
    </footer>

</div><!-- .site -->

<!-- Search dropdown toggle — vanilla JS, no dependencies.
     WHY here: The DOM must be ready. This is a ~20-line handler — not
     worth a separate .js file. -->
<script>
(function() {
    var wrap   = document.querySelector('.sp-header-search-wrap');
    var toggle = wrap ? wrap.querySelector('.sp-search-toggle') : null;
    var form   = wrap ? wrap.querySelector('.sp-header-search') : null;
    var input  = form ? form.querySelector('input[type="text"]') : null;

    if (!toggle || !form) return;

    toggle.addEventListener('click', function() {
        wrap.classList.add('sp-search-open');
        toggle.setAttribute('aria-expanded', 'true');
        if (input) input.focus();
    });

    // Close when clicking outside the search wrapper
    document.addEventListener('click', function(e) {
        if (!wrap.classList.contains('sp-search-open')) return;
        if (!wrap.contains(e.target)) {
            wrap.classList.remove('sp-search-open');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && wrap.classList.contains('sp-search-open')) {
            wrap.classList.remove('sp-search-open');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.focus();
        }
    });
})();
</script>

<!-- User dropdown click/tap toggle — vanilla JS, no dependencies.
     WHY: The CSS :hover dropdown works fine for mouse users, but touch
     devices (phones, tablets, some hybrids) don't reliably fire :hover.
     This adds a click/tap toggle as a parallel mechanism. The CSS :hover
     rules stay intact — this JS just adds/removes a class that the CSS
     also responds to, so both input methods work. -->
<script>
(function() {
    var menu    = document.querySelector('.sp-user-menu');
    var trigger = menu ? menu.querySelector('.sp-user-trigger') : null;

    /* Nothing to do if the user isn't logged in (no trigger exists) */
    if (!trigger) return;

    /* WHY we add aria-expanded here instead of in PHP: The HTML is
       generated by the plugin, so adding it there would require a plugin
       change for a theme-level UX concern. Setting it via JS keeps the
       theme self-contained. */
    trigger.setAttribute('aria-expanded', 'false');

    trigger.addEventListener('click', function(e) {
        e.preventDefault();

        var isOpen = menu.classList.toggle('sp-user-open');
        trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    /* Close when clicking/tapping anywhere outside the menu */
    document.addEventListener('click', function(e) {
        if (!menu.classList.contains('sp-user-open')) return;
        if (!menu.contains(e.target)) {
            menu.classList.remove('sp-user-open');
            trigger.setAttribute('aria-expanded', 'false');
        }
    });

    /* Close on Escape key — consistent with the search dropdown behavior */
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && menu.classList.contains('sp-user-open')) {
            menu.classList.remove('sp-user-open');
            trigger.setAttribute('aria-expanded', 'false');
            trigger.focus();
        }
    });
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
