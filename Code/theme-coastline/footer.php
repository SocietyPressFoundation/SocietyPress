<?php
/**
 * Coastline Child Theme — Footer
 *
 * WHY: Overrides the parent footer to provide a two-column footer layout
 * with widget areas plus a bottom copyright bar. The parent theme has a
 * single-line footer — the magazine archetype needs more real estate for
 * org info, links, and navigation.
 *
 * This template preserves ALL parent footer functionality:
 * - Search dropdown toggle JS
 * - User dropdown click/tap toggle JS
 * - wp_footer() hook
 *
 * @package Coastline
 * @since   1.1.0
 */
?>

    <footer class="site-footer">

        <!-- Two-column footer widget areas.
             WHY two columns: Societies typically need space for org info
             (address, hours, description) on one side and quick links or
             contact info on the other. Two columns provide this without
             being overwhelming. -->
        <div class="coastline-footer-columns">

            <div class="coastline-footer-col">
                <?php if ( is_active_sidebar( 'coastline-footer-1' ) ) : ?>
                    <?php dynamic_sidebar( 'coastline-footer-1' ); ?>
                <?php else : ?>
                    <!-- Default content when no widgets are configured.
                         WHY: The footer shouldn't look broken on first activation.
                         This gives societies a reasonable starting point. -->
                    <h3 class="coastline-footer-default-title"><?php esc_html_e( 'About Us', 'coastline' ); ?></h3>
                    <p class="coastline-footer-default-text">
                        <?php echo esc_html( get_bloginfo( 'description', 'display' ) ); ?>
                    </p>
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

            <div class="coastline-footer-col">
                <?php if ( is_active_sidebar( 'coastline-footer-2' ) ) : ?>
                    <?php dynamic_sidebar( 'coastline-footer-2' ); ?>
                <?php else : ?>
                    <!-- Default quick links when no widgets are configured. -->
                    <h3 class="coastline-footer-default-title"><?php esc_html_e( 'Quick Links', 'coastline' ); ?></h3>
                    <ul class="coastline-footer-default-links">
                        <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'coastline' ); ?></a></li>
                        <?php if ( function_exists( 'sp_get_search_page_url' ) ) : ?>
                        <li><a href="<?php echo esc_url( sp_get_search_page_url() ); ?>"><?php esc_html_e( 'Search', 'coastline' ); ?></a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Member Login', 'coastline' ); ?></a></li>
                    </ul>
                <?php endif; ?>
            </div>

        </div>

        <?php
        /*
         * Affiliation logos — set at Website → Affiliations in the admin.
         * Sits above the bottom bar so the badges read as belonging to the
         * whole site. Prints nothing when none are configured, and nothing
         * when the inline slot above has already printed them.
         */
        if ( function_exists( 'sp_affiliation_logos' ) ) {
            sp_affiliation_logos( 'below' );
        }
        ?>

        <!-- Bottom bar: copyright line -->
        <div class="coastline-footer-bottom">
            <p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'coastline' ); ?></p>
        </div>

    </footer>

</div><!-- .site -->

<!-- Search dropdown toggle — vanilla JS, no dependencies.
     WHY here and not in a .js file: The DOM must be ready. This is a small
     handler — not worth a separate HTTP request. Copied from parent footer
     so the search dropdown works identically in this child theme. -->
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

    /* Close when clicking outside the search wrapper */
    document.addEventListener('click', function(e) {
        if (!wrap.classList.contains('sp-search-open')) return;
        if (!wrap.contains(e.target)) {
            wrap.classList.remove('sp-search-open');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

    /* Close on Escape key */
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
     WHY: CSS :hover dropdown works for mouse users but touch devices don't
     reliably fire :hover. This adds a click/tap toggle as a parallel
     mechanism. The CSS :hover rules stay intact. Copied from parent footer. -->
<script>
(function() {
    var menu    = document.querySelector('.sp-user-menu');
    var trigger = menu ? menu.querySelector('.sp-user-trigger') : null;

    /* Nothing to do if the user isn't logged in (no trigger exists) */
    if (!trigger) return;

    /* WHY we add aria-expanded here instead of in PHP: The HTML is generated
       by the plugin, so adding it there would require a plugin change for a
       theme-level UX concern. Setting it via JS keeps the theme self-contained. */
    trigger.setAttribute('aria-expanded', 'false');

    trigger.addEventListener('click', function(e) {
        /* WHY preventDefault: The trigger is an <a> linking to My Account.
           On touch devices we want the FIRST tap to open the dropdown, not
           navigate away. Desktop mouse users can still hover to see the
           dropdown and click the link to navigate. */
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

    /* Close on Escape key */
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
