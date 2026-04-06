<?php
/**
 * Ledger Child Theme — Footer
 *
 * WHY this overrides the parent: Ledger uses a 3-column footer with widget
 * areas, a social media icon row, and a bottom bar — the standard "dashboard"
 * footer pattern. The parent theme has a simple single-line footer.
 *
 * Layout:
 * - 3 widget columns (About, Navigation, Contact)
 * - Social media icons row (Facebook, Instagram, YouTube via inline SVGs)
 * - Bottom bar: copyright left, "Powered by SocietyPress" right
 *
 * Each column has a widget area. If no widgets are assigned, default
 * content renders so the footer is never empty.
 *
 * @package Ledger
 * @since   1.1.0
 */
?>

    <footer class="ledger-footer">

        <!-- 3-column footer body with widget areas -->
        <div class="ledger-footer-columns">

            <!-- Column 1: About / Organization info -->
            <div class="ledger-footer-col">
                <?php if ( is_active_sidebar( 'ledger-footer-1' ) ) : ?>
                    <?php dynamic_sidebar( 'ledger-footer-1' ); ?>
                <?php else : ?>
                    <!-- WHY default content: Until the admin adds widgets, the
                         footer shouldn't be empty. We show the site name and
                         tagline as a reasonable fallback. -->
                    <h3><?php bloginfo( 'name' ); ?></h3>
                    <?php
                    $description = get_bloginfo( 'description', 'display' );
                    if ( $description ) :
                    ?>
                        <p><?php echo esc_html( $description ); ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Column 2: Navigation / Quick links -->
            <div class="ledger-footer-col">
                <?php if ( is_active_sidebar( 'ledger-footer-2' ) ) : ?>
                    <?php dynamic_sidebar( 'ledger-footer-2' ); ?>
                <?php else : ?>
                    <!-- WHY hardcoded fallback: Until widgets are configured,
                         show common navigation links so the footer has structure.
                         Once the admin adds a Navigation Menu widget, these vanish. -->
                    <h3><?php esc_html_e( 'Quick Links', 'ledger' ); ?></h3>
                    <ul>
                        <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'ledger' ); ?></a></li>
                        <li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About Us', 'ledger' ); ?></a></li>
                        <li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact', 'ledger' ); ?></a></li>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Column 3: Contact info -->
            <div class="ledger-footer-col">
                <?php if ( is_active_sidebar( 'ledger-footer-3' ) ) : ?>
                    <?php dynamic_sidebar( 'ledger-footer-3' ); ?>
                <?php else : ?>
                    <h3><?php esc_html_e( 'Contact Us', 'ledger' ); ?></h3>
                    <p><?php esc_html_e( 'Add contact information here by placing a Text widget in the "Footer Column 3 (Contact)" widget area.', 'ledger' ); ?></p>
                <?php endif; ?>
            </div>

        </div>

        <!-- Social media icons row
             WHY inline SVGs: No external icon library dependency, no font loading,
             no FOUT. Each icon is a tiny inline SVG that scales crisply and inherits
             color via CSS fill: currentColor. We read the same social URL settings
             the plugin stores so admins only configure these once. -->
        <?php
        $sp_settings = get_option( 'societypress_settings', [] );
        $social_links = [
            'social_facebook'  => [
                'label' => __( 'Facebook', 'ledger' ),
                'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
            ],
            'social_instagram' => [
                'label' => __( 'Instagram', 'ledger' ),
                'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>',
            ],
            'social_youtube'   => [
                'label' => __( 'YouTube', 'ledger' ),
                'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
            ],
        ];

        $has_social = false;
        foreach ( $social_links as $key => $info ) {
            if ( ! empty( $sp_settings[ $key ] ) ) {
                $has_social = true;
                break;
            }
        }

        if ( $has_social ) :
        ?>
        <div class="ledger-footer-social">
            <?php foreach ( $social_links as $key => $info ) : ?>
                <?php if ( ! empty( $sp_settings[ $key ] ) ) : ?>
                    <a href="<?php echo esc_url( $sp_settings[ $key ] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $info['label'] ); ?>">
                        <?php echo $info['svg']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — static SVG markup ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Bottom bar: copyright + powered by -->
        <div class="ledger-footer-bottom">
            <div class="ledger-footer-bottom-inner">
                <span>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'ledger' ); ?></span>
                <span><?php
                    printf(
                        /* translators: %s: SocietyPress link */
                        esc_html__( 'Powered by %s', 'ledger' ),
                        '<a href="https://getsocietypress.org">SocietyPress</a>'
                    );
                ?></span>
            </div>
        </div>

    </footer>

</div><!-- .site -->

<!-- Search dropdown toggle — vanilla JS, no dependencies.
     WHY here: The DOM must be ready. This is a small handler — not
     worth a separate .js file. Mirrors parent theme behavior. -->
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

    document.addEventListener('click', function(e) {
        if (!wrap.classList.contains('sp-search-open')) return;
        if (!wrap.contains(e.target)) {
            wrap.classList.remove('sp-search-open');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

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
     WHY: CSS :hover dropdown works for mouse but not touch devices.
     This adds a click/tap toggle as a parallel mechanism. -->
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
