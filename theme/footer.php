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

<?php wp_footer(); ?>
</body>
</html>
