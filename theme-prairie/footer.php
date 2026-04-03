<?php
/**
 * Prairie Theme — Minimal Footer
 *
 * WHY this overrides the parent footer: The Explorer layout is sidebar-driven,
 * so the footer should be minimal — just a single row with copyright and a
 * few utility links. No multi-column widget areas, no social icons. The sidebar
 * is the primary navigation; the footer is just a graceful end to the page.
 *
 * Also includes the search dropdown and user menu JS from the parent theme
 * footer pattern — these inline scripts handle click-to-expand interactions
 * for the header elements.
 *
 * @package Prairie
 * @since   1.1.0
 */
?>

    </div><!-- .prairie-content (closed here to match the layout wrapper opened in page/front-page templates) -->
    </div><!-- .prairie-layout -->

    <footer class="prairie-footer">
        <div class="prairie-footer-inner">
            <p class="prairie-footer-copy">
                &copy; <?php echo wp_date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'prairie' ); ?>
            </p>

            <?php
            /* WHY we check for the footer menu: If the admin hasn't set one up,
               we show a sensible default (privacy policy link from WP settings).
               If they have assigned a menu to prairie-top-nav, we re-use those
               links here too — keeps things consistent without requiring a
               separate footer menu. */
            if ( has_nav_menu( 'prairie-top-nav' ) ) :
            ?>
            <nav aria-label="<?php esc_attr_e( 'Footer navigation', 'prairie' ); ?>">
                <?php
                wp_nav_menu( [
                    'theme_location' => 'prairie-top-nav',
                    'container'      => false,
                    'menu_class'     => 'prairie-footer-links',
                    'depth'          => 1,
                ] );
                ?>
            </nav>
            <?php else : ?>
            <ul class="prairie-footer-links">
                <?php
                /* WHY privacy policy link: WordPress has a built-in privacy
                   policy page setting. If the admin has set one, we link to it
                   automatically — saves them the trouble. */
                $privacy_url = get_privacy_policy_url();
                if ( $privacy_url ) :
                ?>
                <li><a href="<?php echo esc_url( $privacy_url ); ?>"><?php esc_html_e( 'Privacy Policy', 'prairie' ); ?></a></li>
                <?php endif; ?>
                <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'prairie' ); ?></a></li>
            </ul>
            <?php endif; ?>
        </div>
    </footer>

</div><!-- .site -->

<!-- Search dropdown toggle — vanilla JS, no dependencies.
     WHY here: The DOM must be ready. This is a compact handler that mirrors
     the parent theme's pattern for the expandable search in the header. -->
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
     WHY: CSS :hover works for mouse users but touch devices don't fire :hover
     reliably. This adds a click/tap toggle as a parallel mechanism. -->
<script>
(function() {
    var menu    = document.querySelector('.sp-user-menu');
    var trigger = menu ? menu.querySelector('.sp-user-trigger') : null;

    if (!trigger) return;

    trigger.setAttribute('aria-expanded', 'false');

    trigger.addEventListener('click', function(e) {
        e.preventDefault();
        var isOpen = menu.classList.toggle('sp-user-open');
        trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    document.addEventListener('click', function(e) {
        if (!menu.classList.contains('sp-user-open')) return;
        if (!menu.contains(e.target)) {
            menu.classList.remove('sp-user-open');
            trigger.setAttribute('aria-expanded', 'false');
        }
    });

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
