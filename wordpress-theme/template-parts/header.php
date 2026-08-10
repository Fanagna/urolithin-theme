<?php
/**
 * Header Panstellar — template-parts/header.php
 *
 * Conversion de sections/header.liquid (thème Shopify Dawn 15.1.0).
 * Le HTML, les classes CSS et le responsive design sont conservés à l'identique.
 * Les variables Liquid sont remplacées par les helpers de functions.php.
 *
 * À inclure dans header.php (ou tout layout) via : get_template_part( 'template-parts/header' );
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Réglages du header (équivalents de section.settings.*) ──────────────
$logo_position          = panstellar_header_setting( 'logo_position' );
$mobile_logo_position   = panstellar_header_setting( 'mobile_logo_position' );
$menu_type_desktop      = panstellar_header_setting( 'menu_type_desktop' );
$sticky_header_type     = panstellar_header_setting( 'sticky_header_type' );
$show_line_separator    = (bool) panstellar_header_setting( 'show_line_separator' );
$color_scheme           = panstellar_header_setting( 'color_scheme' );
$menu_color_scheme      = panstellar_header_setting( 'menu_color_scheme' );
$enable_country_selector = (bool) panstellar_header_setting( 'enable_country_selector' );
$enable_language_selector = (bool) panstellar_header_setting( 'enable_language_selector' );
$enable_customer_avatar = (bool) panstellar_header_setting( 'enable_customer_avatar' );
$menu_location          = panstellar_header_setting( 'menu', 'primary' );

// ── Réglages globaux (équivalents de settings.*) ────────────────────────
$predictive_search_enabled = (bool) panstellar_theme_setting( 'predictive_search_enabled' );
$cart_type                = panstellar_theme_setting( 'cart_type' );
$customer_accounts_enabled = (bool) panstellar_theme_setting( 'customer_accounts_enabled' );

$has_social    = panstellar_has_social_links();
$has_localization = ( $enable_country_selector || $enable_language_selector );
$has_menu      = has_nav_menu( $menu_location );
$has_app_block = false; // Les blocs d'apps Shopify (@app) n'ont pas d'équivalent WP ici → hook ci-dessous.

// Panier calculé une seule fois (WooCommerce) — évite les appels répétés à WC()->cart.
$cart_is_empty = panstellar_cart_is_empty();
$cart_count    = panstellar_cart_count();

// Classes conditionnelles de l'élément <header> (conservées à l'identique).
$header_classes = 'header header--' . esc_attr( $logo_position ) . ' header--mobile-' . esc_attr( $mobile_logo_position ) . ' page-width';
if ( 'drawer' === $menu_type_desktop ) {
	$header_classes .= ' drawer-menu';
}
if ( $has_menu ) {
	$header_classes .= ' header--has-menu';
}
if ( $has_app_block ) {
	$header_classes .= ' header--has-app';
}
if ( $has_social ) {
	$header_classes .= ' header--has-social';
}
if ( $customer_accounts_enabled ) {
	$header_classes .= ' header--has-account';
}
if ( $has_localization ) {
	$header_classes .= ' header--has-localizations';
}

// ── Wrapper sticky (équivalent de <sticky-header data-sticky-type=…> / <div>) ──
$wrapper_tag = ( 'none' !== $sticky_header_type ) ? 'sticky-header' : 'div';
$wrapper_attrs = ( 'none' !== $sticky_header_type )
	? ' data-sticky-type="' . esc_attr( $sticky_header_type ) . '"'
	: '';

$is_front_page = is_front_page();

// Blocs d'apps (équivalent du {% for block in section.blocks %}{% when '@app' %}).
$app_blocks_html = '';
ob_start();
/**
 * Hook pour injecter des blocs d'apps dans le header (équivalent des blocs @app Shopify).
 */
do_action( 'panstellar_header_app_blocks' );
$app_blocks_html = ob_get_clean();
?>
<link rel="stylesheet" href="<?php echo esc_url( get_template_directory_uri() . '/assets/css/component-list-menu.css' ); ?>" media="print" onload="this.media='all'">
<link rel="stylesheet" href="<?php echo esc_url( get_template_directory_uri() . '/assets/css/component-search.css' ); ?>" media="print" onload="this.media='all'">
<link rel="stylesheet" href="<?php echo esc_url( get_template_directory_uri() . '/assets/css/component-menu-drawer.css' ); ?>" media="print" onload="this.media='all'">
<link rel="stylesheet" href="<?php echo esc_url( get_template_directory_uri() . '/assets/css/component-cart-notification.css' ); ?>" media="print" onload="this.media='all'">

<?php if ( $predictive_search_enabled ) : ?>
	<link rel="stylesheet" href="<?php echo esc_url( get_template_directory_uri() . '/assets/css/component-price.css' ); ?>" media="print" onload="this.media='all'">
<?php endif; ?>

<?php if ( 'mega' === $menu_type_desktop ) : ?>
	<link rel="stylesheet" href="<?php echo esc_url( get_template_directory_uri() . '/assets/css/component-mega-menu.css' ); ?>" media="print" onload="this.media='all'">
<?php endif; ?>

<style>
	header-drawer {
		justify-self: start;
		margin-left: -1.2rem;
	}

	<?php if ( 'reduce-logo-size' === $sticky_header_type ) : ?>
		.scrolled-past-header .header__heading-logo-wrapper {
			width: 75%;
		}
	<?php endif; ?>

	<?php if ( 'drawer' !== $menu_type_desktop ) : ?>
		@media screen and (min-width: 990px) {
			header-drawer {
				display: none;
			}
		}
	<?php endif; ?>

	.menu-drawer-container {
		display: flex;
	}

	.list-menu {
		list-style: none;
		padding: 0;
		margin: 0;
	}

	.list-menu--inline {
		display: inline-flex;
		flex-wrap: wrap;
	}

	summary.list-menu__item {
		padding-right: 2.7rem;
	}

	.list-menu__item {
		display: flex;
		align-items: center;
		line-height: calc(1 + 0.3 / var(--font-body-scale));
	}

	.list-menu__item--link {
		text-decoration: none;
		padding-bottom: 1rem;
		padding-top: 1rem;
		line-height: calc(1 + 0.8 / var(--font-body-scale));
	}

	@media screen and (min-width: 750px) {
		.list-menu__item--link {
			padding-bottom: 0.5rem;
			padding-top: 0.5rem;
		}
	}
</style>

<div class="section-header">
<<?php echo esc_attr( $wrapper_tag ); ?><?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput -- attribut construit avec esc_attr. ?> class="header-wrapper color-<?php echo esc_attr( $color_scheme ); ?> gradient<?php echo $show_line_separator ? ' header-wrapper--border-bottom' : ''; ?>">
	<header class="<?php echo esc_attr( $header_classes ); ?>">
		<?php
		// Drawer mobile (équivalent de {% render 'header-drawer' %}).
		if ( $has_menu ) {
			get_template_part( 'template-parts/header-drawer' );
		}

		// Recherche en position haut-centre (équivalent du render conditionnel).
		if ( 'top-center' === $logo_position || ! $has_menu ) {
			get_template_part( 'template-parts/header-search', null, array( 'input_id' => 'Search-In-Modal-1' ) );
		}

		// Logo (sauf position middle-center).
		if ( 'middle-center' !== $logo_position ) :
			if ( $is_front_page ) :
				?>
				<h1 class="header__heading">
			<?php endif; ?>
			<a href="<?php echo panstellar_home_url(); // phpcs:ignore WordPress.Security.EscapeOutput -- helper déjà esc_url. ?>" class="header__heading-link link link--text focus-inset">
				<?php echo panstellar_logo(); // phpcs:ignore WordPress.Security.EscapeOutput -- helper sécurisé. ?>
			</a>
			<?php
			if ( $is_front_page ) :
				?>
				</h1>
			<?php endif; ?>
		<?php endif; ?>

		<?php
		// Menu desktop (dropdown / mega) — équivalent des renders conditionnels.
		if ( $has_menu ) :
			if ( 'dropdown' === $menu_type_desktop ) :
				get_template_part( 'template-parts/header-dropdown-menu' );
			elseif ( 'drawer' !== $menu_type_desktop ) :
				get_template_part( 'template-parts/header-mega-menu' );
			endif;
		endif;
		?>

		<?php if ( 'middle-center' === $logo_position ) : ?>
			<?php if ( $is_front_page ) : ?>
				<h1 class="header__heading">
			<?php endif; ?>
			<a href="<?php echo panstellar_home_url(); // phpcs:ignore WordPress.Security.EscapeOutput -- helper déjà esc_url. ?>" class="header__heading-link link link--text focus-inset">
				<?php echo panstellar_logo(); // phpcs:ignore WordPress.Security.EscapeOutput -- helper sécurisé. ?>
			</a>
			<?php if ( $is_front_page ) : ?>
				</h1>
			<?php endif; ?>
		<?php endif; ?>

		<div class="header__icons<?php echo $has_localization ? ' header__icons--localization header-localization' : ''; ?>">
			<div class="desktop-localization-wrapper">
				<?php
				// Localisation pays / langue — WPML / Polylang : voir inc/localization ou hook.
				if ( $enable_country_selector || $enable_language_selector ) {
					/**
					 * Hook de localisation (pays + langue) — équivalent des formulaires
					 * {% form 'localization' %} de Shopify. À implémenter avec WPML/Polylang.
					 */
					do_action( 'panstellar_header_localization' );
				}
				?>
			</div>

			<?php get_template_part( 'template-parts/header-search', null, array( 'input_id' => 'Search-In-Modal' ) ); ?>

			<?php if ( $customer_accounts_enabled ) : ?>
				<a href="<?php echo panstellar_account_url(); // phpcs:ignore WordPress.Security.EscapeOutput -- helper déjà esc_url. ?>" class="header__icon header__icon--account link focus-inset<?php echo $has_menu ? ' small-hide' : ''; ?>">
					<?php if ( $enable_customer_avatar ) : ?>
						<account-icon>
							<span class="svg-wrapper"><?php panstellar_icon( 'account' ); ?></span>
						</account-icon>
					<?php else : ?>
						<span class="svg-wrapper"><?php panstellar_icon( 'account' ); ?></span>
					<?php endif; ?>
					<span class="visually-hidden">
						<?php echo panstellar_customer_logged_in() ? esc_html__( 'Account', 'panstellar' ) : esc_html__( 'Log in', 'panstellar' ); ?>
					</span>
				</a>
			<?php endif; ?>

			<?php echo $app_blocks_html; // phpcs:ignore WordPress.Security.EscapeOutput -- hook. ?>

			<a href="<?php echo panstellar_cart_url(); // phpcs:ignore WordPress.Security.EscapeOutput -- helper déjà esc_url. ?>" class="header__icon header__icon--cart link focus-inset" id="cart-icon-bubble">
				<?php if ( $cart_is_empty ) : ?>
					<span class="svg-wrapper"><?php panstellar_icon( 'cart-empty' ); ?></span>
				<?php else : ?>
					<span class="svg-wrapper"><?php panstellar_icon( 'cart' ); ?></span>
				<?php endif; ?>
				<span class="visually-hidden"><?php esc_html_e( 'Cart', 'panstellar' ); ?></span>
				<?php if ( ! $cart_is_empty ) : ?>
					<div class="cart-count-bubble" data-cart-count-bubble>
						<?php if ( $cart_count < 100 ) : ?>
							<span aria-hidden="true" data-cart-count><?php echo esc_html( (string) $cart_count ); ?></span>
						<?php endif; ?>
						<span class="visually-hidden"><?php echo esc_html( sprintf( /* translators: %s: cart item count */ _n( '%s item', '%s items', $cart_count, 'panstellar' ), $cart_count ) ); ?></span>
					</div>
				<?php endif; ?>
			</a>
		</div>
	</header>
</<?php echo esc_attr( $wrapper_tag ); ?>>
</div>

<?php
// Notification panier (équivalent de {% if settings.cart_type == "notification" %} render 'cart-notification' %}.
if ( 'notification' === $cart_type ) {
	get_template_part( 'template-parts/cart-notification', null, array( 'color_scheme' => $color_scheme, 'desktop_menu_type' => $menu_type_desktop ) );
}
