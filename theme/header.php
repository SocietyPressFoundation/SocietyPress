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

							<!-- Menu Search -->
							<div class="menu-search">
								<button class="search-toggle" aria-label="<?php esc_attr_e( 'Toggle search', 'societypress' ); ?>" aria-expanded="false">
									<?php esc_html_e( 'Search', 'societypress' ); ?>
								</button>
								<div class="search-dropdown">
									<?php get_search_form(); ?>
								</div>
							</div>

							<!-- Login/Logout -->
							<div class="menu-account">
								<?php if ( is_user_logged_in() ) : ?>
									<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="account-link">
										<?php esc_html_e( 'Log Out', 'societypress' ); ?>
									</a>
								<?php else : ?>
									<a href="<?php echo esc_url( wp_login_url( home_url( '/' ) ) ); ?>" class="account-link">
										<?php esc_html_e( 'Log In', 'societypress' ); ?>
									</a>
								<?php endif; ?>
							</div>
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
