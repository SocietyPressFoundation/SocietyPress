<?php
/**
 * Parlor Child Theme — Footer
 *
 * WHY this overrides the parent: Parlor uses a classic 2-column footer layout
 * that traditional organizational websites use — org info on the left, useful
 * links on the right, copyright bar at the bottom. Familiar and functional.
 *
 * Layout:
 * - 2-column body: organization info (left) + footer links widget area (right)
 * - Bottom bar: copyright line
 *
 * @package Parlor
 * @since   1.0.0
 */
?>

    <footer class="site-footer">

        <!-- Two-column footer body -->
        <div class="parlor-footer-inner">

            <!-- Left column: Organization identity and description.
                 WHY widget area: Lets the admin control what goes here without
                 editing theme files. If no widgets are added, we show a sensible
                 default with the site name and tagline so the footer isn't empty. -->
            <div class="parlor-footer-col parlor-footer-info">
                <?php if ( is_active_sidebar( 'parlor-footer-1' ) ) : ?>
                    <?php dynamic_sidebar( 'parlor-footer-1' ); ?>
                <?php else : ?>
                    <!-- WHY fallback: Until the admin adds widgets, show the org
                         name and description so the footer isn't blank. Once widgets
                         are added, this disappears automatically. -->
                    <h3><?php bloginfo( 'name' ); ?></h3>
                    <?php
                    $description = get_bloginfo( 'description', 'display' );
                    if ( $description ) :
                    ?>
                        <p><?php echo esc_html( $description ); ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Right column: Useful links.
                 WHY widget area: The admin can drop a Navigation Menu widget,
                 a custom HTML widget with hand-picked links, or any other
                 widget they want. If empty, we fall back to a footer nav menu
                 or a small set of sensible defaults. -->
            <div class="parlor-footer-col parlor-footer-links">
                <?php if ( is_active_sidebar( 'parlor-footer-2' ) ) : ?>
                    <?php dynamic_sidebar( 'parlor-footer-2' ); ?>
                <?php elseif ( has_nav_menu( 'footer' ) ) : ?>
                    <h3><?php esc_html_e( 'Quick Links', 'parlor' ); ?></h3>
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'footer',
                        'container'      => false,
                        'depth'          => 1,
                    ]);
                    ?>
                <?php else : ?>
                    <!-- WHY hardcoded fallback: Until the admin creates a footer
                         menu or adds widgets, show sensible defaults so the right
                         column isn't empty. Once a footer menu or widgets are
                         assigned, these disappear. -->
                    <h3><?php esc_html_e( 'Quick Links', 'parlor' ); ?></h3>
                    <ul>
                        <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'parlor' ); ?></a></li>
                        <li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About', 'parlor' ); ?></a></li>
                        <li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact', 'parlor' ); ?></a></li>
                    </ul>
                <?php endif; ?>
            </div>

        </div>

        <!-- Bottom bar: copyright.
             WHY separate bar: Visually anchors the page and gives a clear
             "end of page" signal. Standard pattern for organizational sites. -->
        <div class="parlor-footer-bottom">
            <p>&copy; <?php echo wp_date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'parlor' ); ?></p>
            <p class="parlor-powered-by"><?php
                printf(
                    /* translators: %s: SocietyPress link */
                    esc_html__( 'Powered by %s', 'parlor' ),
                    '<a href="https://getsocietypress.org">SocietyPress</a>'
                );
            ?></p>
        </div>

    </footer>

</div><!-- .site -->

<!-- Search dropdown toggle — vanilla JS, no dependencies.
     WHY here: The DOM must be ready. This is a small handler — not
     worth a separate .js file. Mirrors the parent theme's approach. -->
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
     WHY: The CSS :hover dropdown works fine for mouse users, but touch
     devices don't reliably fire :hover. This adds a click/tap toggle
     as a parallel mechanism so both input methods work. -->
<script>
(function() {
    var menu    = document.querySelector('.sp-user-menu');
    var trigger = menu ? menu.querySelector('.sp-user-trigger') : null;

    /* Nothing to do if the user isn't logged in (no trigger exists) */
    if (!trigger) return;

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
