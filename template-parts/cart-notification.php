<?php
/**
 * Notification panier — template-parts/cart-notification.php
 *
 * Conversion de snippets/cart-notification.liquid.
 * Utilisé uniquement quand le réglage « cart_type » = notification.
 *
 * Usage :
 *   get_template_part( 'template-parts/cart-notification', null, array(
 *       'color_scheme'       => $color_scheme,
 *       'desktop_menu_type'  => $menu_type_desktop,
 *   ) );
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$color_scheme      = isset( $args['color_scheme'] ) ? $args['color_scheme'] : '';
$desktop_menu_type = isset( $args['desktop_menu_type'] ) ? $args['desktop_menu_type'] : 'drawer';
?>
<cart-notification>
	<div class="cart-notification-wrapper<?php echo 'drawer' !== $desktop_menu_type ? ' page-width' : ''; ?>">
		<div
			id="cart-notification"
			class="cart-notification focus-inset<?php echo $color_scheme ? ' color-' . esc_attr( $color_scheme ) . ' gradient' : ''; ?>"
			aria-modal="true"
			aria-label="<?php esc_attr_e( 'Item added to your cart', 'panstellar' ); ?>"
			role="dialog"
			tabindex="-1"
		>
			<div class="cart-notification__header">
				<h2 class="cart-notification__heading caption-large text-body">
					<span class="svg-wrapper"><?php panstellar_icon( 'checkmark' ); ?></span>
					<?php esc_html_e( 'Item added to your cart', 'panstellar' ); ?>
				</h2>
				<button
					type="button"
					class="cart-notification__close modal__close-button link link--text focus-inset"
					aria-label="<?php esc_attr_e( 'Close', 'panstellar' ); ?>"
				>
					<span class="svg-wrapper">
						<?php panstellar_icon( 'close' ); ?>
					</span>
				</button>
			</div>
			<div id="cart-notification-product" class="cart-notification-product"></div>
			<div class="cart-notification__links">
				<a
					href="<?php echo panstellar_cart_url(); // phpcs:ignore WordPress.Security.EscapeOutput -- helper déjà esc_url. ?>"
					id="cart-notification-button"
					class="button button--secondary button--full-width"
				>
					<?php esc_html_e( 'View my cart', 'panstellar' ); ?>
				</a>
				<form action="<?php echo panstellar_checkout_url(); // phpcs:ignore WordPress.Security.EscapeOutput -- helper déjà esc_url. ?>" method="post" id="cart-notification-form">
					<button class="button button--primary button--full-width" name="checkout">
						<?php esc_html_e( 'Check out', 'panstellar' ); ?>
					</button>
				</form>
				<button type="button" class="link button-label"><?php esc_html_e( 'Continue shopping', 'panstellar' ); ?></button>
			</div>
		</div>
	</div>
</cart-notification>
<style>
	.cart-notification {
		display: none;
	}
</style>
