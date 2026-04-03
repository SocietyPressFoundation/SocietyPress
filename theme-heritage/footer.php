<?php
/**
 * Heritage Child Theme — Footer
 *
 * WHY this overrides the parent: The Heritage "Classic" archetype uses a
 * substantial 3-column footer with widget areas, giving Harold full control
 * over footer content (org description, quick links, contact info) from the
 * admin Widgets screen — no template editing needed.
 *
 * Layout:
 * - Gold accent bar (3px) at top
 * - Three-column widget grid (collapses to 1 column on mobile)
 * - Bottom bar: copyright + "Powered by SocietyPress"
 *
 * If no widgets are assigned, each column shows a sensible default so the
 * footer never looks empty on a fresh install.
 *
 * IMPORTANT: This file must include the search dropdown and user dropdown
 * JavaScript from the parent theme's footer.php. Those scripts handle
 * click-to-expand search and touch-friendly user menu behavior. Without
 * them, search and user dropdowns break on touch devices.
 *
 * @package Heritage
 * @since   1.1.0
 */
?>

    <footer class="site-footer heritage-footer">

        <!-- Gold accent line at top of footer -->
        <div class="heritage-footer-accent"></div>

        <!-- Three-column footer body -->
        <div class="heritage-footer-columns">

            <!-- Column 1: About / Organization description -->
            <div class="heritage-footer-col">
                <?php if ( is_active_sidebar( 'heritage-footer-1' ) ) : ?>
                    <?php dynamic_sidebar( 'heritage-footer-1' ); ?>
                <?php else : ?>
                    <?php
                    /* WHY default content: On a fresh install, the footer should
                       never be empty. We show the site name and description as a
                       reasonable placeholder. Once Harold adds widgets from
                       Appearance -> Widgets, these defaults disappear. */
                    ?>
                    <h3><?php bloginfo( 'name' ); ?></h3>
                    <p><?php bloginfo( 'description' ); ?></p>
                <?php endif; ?>
            </div>

            <!-- Column 2: Quick links -->
            <div class="heritage-footer-col">
                <?php if ( is_active_sidebar( 'heritage-footer-2' ) ) : ?>
                    <?php dynamic_sidebar( 'heritage-footer-2' ); ?>
                <?php else : ?>
                    <?php
                    /* WHY default links: The most common pages a society visitor
                       looks for. These use home_url() so they work regardless of
                       the actual page slugs — if the page doesn't exist, the link
                       just 404s, which is better than an empty footer column. */
                    ?>
                    <h3><?php esc_html_e( 'Quick Links', 'heritage' ); ?></h3>
                    <ul>
                        <li><a href="<?php echo esc_url( home_url( '/events/' ) ); ?>"><?php esc_html_e( 'Events', 'heritage' ); ?></a></li>
                        <li><a href="<?php echo esc_url( home_url( '/library/' ) ); ?>"><?php esc_html_e( 'Library', 'heritage' ); ?></a></li>
                        <li><a href="<?php echo esc_url( home_url( '/join/' ) ); ?>"><?php esc_html_e( 'Join', 'heritage' ); ?></a></li>
                        <li><a href="<?php echo esc_url( home_url( '/newsletters/' ) ); ?>"><?php esc_html_e( 'Newsletters', 'heritage' ); ?></a></li>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Column 3: Contact info -->
            <div class="heritage-footer-col">
                <?php if ( is_active_sidebar( 'heritage-footer-3' ) ) : ?>
                    <?php dynamic_sidebar( 'heritage-footer-3' ); ?>
                <?php else : ?>
                    <?php
                    /* WHY default contact block: Shows a generic "get in touch"
                       message. The admin email is already public in WordPress, so
                       displaying it here is safe. Harold will replace this with a
                       Text widget containing the society's actual address and phone. */
                    ?>
                    <h3><?php esc_html_e( 'Contact', 'heritage' ); ?></h3>
                    <p><?php echo esc_html( get_option( 'blogname' ) ); ?></p>
                    <p>
                        <a href="mailto:<?php echo esc_attr( get_option( 'admin_email' ) ); ?>">
                            <?php echo esc_html( get_option( 'admin_email' ) ); ?>
                        </a>
                    </p>
                <?php endif; ?>
            </div>

        </div>

        <!-- Bottom bar: copyright + powered by -->
        <div class="heritage-footer-bottom">
            <div class="heritage-footer-bottom-inner">
                <span>
                    &copy; <?php echo esc_html( wp_date( 'Y' ) ); ?>
                    <?php bloginfo( 'name' ); ?>.
                    <?php esc_html_e( 'All rights reserved.', 'heritage' ); ?>
                </span>
                <span>
                    <?php
                    printf(
                        /* translators: %s: SocietyPress link */
                        esc_html__( 'Powered by %s', 'heritage' ),
                        '<a href="https://getsocietypress.org">SocietyPress</a>'
                    );
                    ?>
                </span>
            </div>
        </div>

    </footer>

</div><!-- .site -->

<!-- Search dropdown toggle — vanilla JS, no dependencies.
     WHY here: The DOM must be ready. This handler controls the click-to-expand
     search field in the header. Copied from the parent theme's footer.php
     because child theme footer overrides replace the parent entirely. -->
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
