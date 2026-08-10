<?php
/**
 * Drawer de menu — template-parts/header-drawer.php
 *
 * Conversion de snippets/header-drawer.liquid (menu mobile/desktop drawer).
 * Structure <details>/<summary> conservée pour le JS Dawn (HeaderDrawer).
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$menu_type_desktop      = panstellar_header_setting( 'menu_type_desktop' );
$menu_color_scheme      = panstellar_header_setting( 'menu_color_scheme' );
$enable_customer_avatar = (bool) panstellar_header_setting( 'enable_customer_avatar' );
$menu_location          = panstellar_header_setting( 'menu', 'primary' );
$customer_accounts_enabled = (bool) panstellar_theme_setting( 'customer_accounts_enabled' );

$breakpoint = ( 'drawer' === $menu_type_desktop ) ? 'desktop' : 'tablet';
$socials    = panstellar_social_links();
?>
<header-drawer data-breakpoint="<?php echo esc_attr( $breakpoint ); ?>">
	<details id="Details-menu-drawer-container" class="menu-drawer-container">
		<summary
			class="header__icon header__icon--menu header__icon--summary link focus-inset"
			aria-label="<?php esc_attr_e( 'Menu', 'panstellar' ); ?>"
		>
			<span>
				<?php panstellar_icon( 'hamburger' ); ?>
				<?php panstellar_icon( 'close' ); ?>
			</span>
		</summary>
		<div id="menu-drawer" class="gradient menu-drawer motion-reduce color-<?php echo esc_attr( $menu_color_scheme ); ?>">
			<div class="menu-drawer__inner-container">
				<div class="menu-drawer__navigation-container">
					<nav class="menu-drawer__navigation">
						<?php
						if ( has_nav_menu( $menu_location ) ) {
							wp_nav_menu(
								array(
									'theme_location' => $menu_location,
									'menu_class'     => 'menu-drawer__menu has-submenu list-menu',
									'container'      => false,
									'items_wrap'     => '<ul id="%1$s" class="%2$s" role="list">%3$s</ul>',
									'walker'         => new Panstellar_Drawer_Walker(),
									'menu_color_scheme' => $menu_color_scheme,
								)
							);
						}
						?>
					</nav>
					<div class="menu-drawer__utility-links">
						<?php if ( $customer_accounts_enabled ) : ?>
							<a
								href="<?php echo panstellar_account_url(); // phpcs:ignore WordPress.Security.EscapeOutput -- helper déjà esc_url. ?>"
								class="menu-drawer__account link focus-inset h5 medium-hide large-up-hide"
							>
								<?php if ( $enable_customer_avatar ) : ?>
									<account-icon>
										<span class="svg-wrapper">
											<?php panstellar_icon( 'account' ); ?>
										</span>
									</account-icon>
								<?php else : ?>
									<span class="svg-wrapper">
										<?php panstellar_icon( 'account' ); ?>
									</span>
								<?php endif; ?>
								<?php echo panstellar_customer_logged_in() ? esc_html__( 'Account', 'panstellar' ) : esc_html__( 'Log in', 'panstellar' ); ?>
							</a>
						<?php endif; ?>

						<?php
						// Localisation mobile (équivalent des formulaires du drawer Shopify).
						if ( panstellar_header_setting( 'enable_country_selector' ) || panstellar_header_setting( 'enable_language_selector' ) ) {
							/**
							 * Hook de localisation mobile (pays + langue).
							 */
							do_action( 'panstellar_header_drawer_localization' );
						}
						?>

						<?php if ( $socials ) : ?>
							<ul class="list list-social list-unstyled" role="list">
								<?php foreach ( $socials as $handle => $url ) : ?>
									<li class="list-social__item">
										<a href="<?php echo esc_url( $url ); ?>" class="list-social__link link">
											<span class="svg-wrapper">
												<?php panstellar_icon( $handle ); ?>
											</span>
											<span class="visually-hidden"><?php echo esc_html( ucfirst( $handle ) ); ?></span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</details>
</header-drawer>
