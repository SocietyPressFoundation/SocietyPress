<?php
/**
 * The header for our theme
 *
 * WHY: Displays the head section and opening site structure.
 * Contains logo, navigation, and accessibility features.
 *
 * @package SocietyPress
 * @since 1.0.0
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary">
	<?php esc_html_e( 'Skip to content', 'societypress' ); ?>
</a>

<div id="page" class="site">
	<header id="masthead" class="site-header">

		<?php if ( has_nav_menu( 'utility' ) ) : ?>
			<!-- Utility Menu (Contact info, member login, etc.) -->
			<div class="utility-bar">
				<div class="sp-container">
					<nav class="utility-navigation" aria-label="<?php esc_attr_e( 'Utility Menu', 'societypress' ); ?>">
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'utility',
								'menu_class'     => 'utility-menu',
								'depth'          => 1,
							)
						);
						?>
					</nav>
				</div>
			</div>
		<?php endif; ?>

		<!-- Main Header -->
		<div class="site-branding-navigation">
			<div class="sp-container">
				<div class="header-inner">

					<!-- Logo / Site Title -->
					<div class="site-branding">
						<?php
						if ( has_custom_logo() ) :
							the_custom_logo();
						else :
							?>
							<div class="site-title-group">
								<h1 class="site-title">
									<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
										<?php bloginfo( 'name' ); ?>
									</a>
								</h1>
								<?php
								$description = get_bloginfo( 'description', 'display' );
								if ( $description || is_customize_preview() ) :
									?>
									<p class="site-description"><?php echo $description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
								<?php endif; ?>
							</div>
							<?php
						endif;
						?>
					</div>

					<!-- Primary Navigation -->
					<nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e( 'Primary Menu', 'societypress' ); ?>">
						<div class="navigation-wrapper">
							<?php
							if ( has_nav_menu( 'primary' ) ) {
								wp_nav_menu(
									array(
										'theme_location' => 'primary',
										'menu_class'     => 'primary-menu',
										'container'      => false,
									)
								);
							}
							?>

							<!-- Login link for visitors (Log Out handled by SocietyPress plugin menu) -->
							<!-- Search is now injected into nav menu via SocietyPress plugin -->
							<?php if ( ! is_user_logged_in() ) : ?>
							<div class="menu-account">
								<a href="<?php echo esc_url( wp_login_url( home_url( '/' ) ) ); ?>" class="account-link">
									<?php esc_html_e( 'Log In', 'societypress' ); ?>
								</a>
							</div>
							<?php endif; ?>
						</div>
					</nav>

					<!-- Mobile Menu Toggle -->
					<button class="mobile-menu-toggle" aria-controls="primary-menu" aria-expanded="false">
						<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'societypress' ); ?></span>
						<span class="menu-icon" aria-hidden="true">
							<span></span>
							<span></span>
							<span></span>
						</span>
					</button>

				</div><!-- .header-inner -->
			</div><!-- .sp-container -->
		</div><!-- .site-branding-navigation -->

	</header><!-- #masthead -->

	<?php
	/**
	 * Below Header Widget Area
	 *
	 * WHY: Provides a spot for breadcrumbs, announcements, or other site-wide content
	 *      that should appear below the header but above main content.
	 */
	if ( is_active_sidebar( 'below-header' ) ) :
	?>
		<div class="below-header-area">
			<?php dynamic_sidebar( 'below-header' ); ?>
		</div>
	<?php endif; ?>

	<?php
	/**
	 * Automatic Breadcrumbs (Customizer setting)
	 *
	 * WHY: If enabled in Customizer and no widget is being used, show breadcrumbs automatically.
	 *      Simpler option for users who don't want to fuss with widgets.
	 */
	if ( get_theme_mod( 'societypress_show_breadcrumbs', false ) && ! is_active_sidebar( 'below-header' ) ) :
		societypress_breadcrumbs();
	endif;
	?>
