<?php
/**
 * The template for displaying the footer
 *
 * WHY: Contains the closing of the #content div and all content after.
 * Displays footer widgets, footer menu, and copyright information.
 *
 * @package SocietyPress
 * @since 1.0.0
 */
?>

	<footer id="colophon" class="site-footer">

		<?php if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) ) : ?>
			<!-- Footer Widget Area -->
			<div class="footer-widgets">
				<div class="sp-container">
					<div class="footer-widgets-inner">
						<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
							<?php if ( is_active_sidebar( 'footer-' . $i ) ) : ?>
								<div class="footer-widget-column">
									<?php dynamic_sidebar( 'footer-' . $i ); ?>
								</div>
							<?php endif; ?>
						<?php endfor; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<!-- Footer Bottom -->
		<div class="footer-bottom">
			<div class="sp-container">
				<div class="footer-bottom-inner">

					<!-- Copyright -->
					<div class="site-info">
						<?php
						printf(
							/* translators: 1: Current year, 2: Site name */
							esc_html__( '&copy; %1$s %2$s', 'societypress' ),
							esc_html( date_i18n( 'Y' ) ),
							esc_html( get_bloginfo( 'name' ) )
						);
						?>
					</div>

					<!-- Footer Menu -->
					<?php if ( has_nav_menu( 'footer' ) ) : ?>
						<nav class="footer-navigation" aria-label="<?php esc_attr_e( 'Footer Menu', 'societypress' ); ?>">
							<?php
							wp_nav_menu(
								array(
									'theme_location' => 'footer',
									'menu_class'     => 'footer-menu',
									'depth'          => 1,
									'container'      => false,
								)
							);
							?>
						</nav>
					<?php endif; ?>

				</div><!-- .footer-bottom-inner -->
			</div><!-- .sp-container -->
		</div><!-- .footer-bottom -->

	</footer><!-- #colophon -->

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
