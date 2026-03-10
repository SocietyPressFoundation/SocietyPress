<?php
/**
 * Theme Footer
 *
 * WHY: Closes out the page structure opened in header.php. Includes a
 * simple copyright line and the wp_footer() call that WordPress needs
 * for scripts and admin bar functionality.
 *
 * @package SocietyPress
 */
?>

    <footer class="site-footer">
        <p>&copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>. All rights reserved.</p>
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

<?php wp_footer(); ?>
</body>
</html>
