<?php
/**
 * Cart drawer — template-parts/cart-drawer.php
 *
 * Conversion de snippets/cart-drawer.liquid (thème Shopify Dawn 15.1.0).
 * Le HTML, les classes CSS et le responsive design sont conservés à l'identique.
 *
 * Correspondances Shopify → WordPress :
 *   - cart / cart.items               → WC()->cart->get_cart()
 *   - item.image / item.product.title → $_product->get_image() / get_name()
 *   - item.original_price / final_*   → prix + sous-total WooCommerce
 *   - /cart/change.js (AJAX)          → endpoints ?wc-ajax=update_qty / remove_cart_item
 *   - {{ routes.cart_url }}           → wc_get_cart_url()
 *
 * Les helpers panstellar_cart_drawer_items_html() et panstellar_cart_drawer_footer_html()
 * sont aussi utilisés par le filtre woocommerce_add_to_cart_fragments (mise à jour AJAX).
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'WC' ) ) {
	return; // WooCommerce requis.
}

$cart_is_empty = WC()->cart->is_empty();

$cart_color_scheme = panstellar_theme_setting( 'cart_color_scheme', 'scheme-5a9a3de0-927a-4340-b2ed-cabdd3e7d381' );
?>

<cart-drawer class="drawer<?php echo $cart_is_empty ? ' is-empty' : ''; ?>">
	<div id="CartDrawer" class="cart-drawer">
		<div id="CartDrawer-Overlay" class="cart-drawer__overlay"></div>
		<div
			class="drawer__inner gradient color-<?php echo esc_attr( $cart_color_scheme ); ?>"
			role="dialog"
			aria-modal="true"
			aria-label="<?php esc_attr_e( 'Cart', 'panstellar' ); ?>"
			tabindex="-1"
		>
			<?php if ( $cart_is_empty ) : ?>
				<div class="drawer__inner-empty">
					<div class="cart-drawer__warnings center">
						<div class="cart-drawer__empty-content">
							<h2 class="cart__empty-text"><?php esc_html_e( 'Your cart is empty', 'panstellar' ); ?></h2>
							<button
								class="drawer__close"
								type="button"
								onclick="this.closest('cart-drawer').close()"
								aria-label="<?php esc_attr_e( 'Close', 'panstellar' ); ?>"
							>
								<span class="svg-wrapper">
									<?php panstellar_icon( 'close' ); ?>
								</span>
							</button>
							<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="button">
								<?php esc_html_e( 'Continue shopping', 'panstellar' ); ?>
							</a>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<div class="drawer__header">
				<h2 class="drawer__heading"><?php esc_html_e( 'Your cart', 'panstellar' ); ?></h2>
				<button
					class="drawer__close"
					type="button"
					onclick="this.closest('cart-drawer').close()"
					aria-label="<?php esc_attr_e( 'Close', 'panstellar' ); ?>"
				>
					<span class="svg-wrapper">
						<?php panstellar_icon( 'close' ); ?>
					</span>
				</button>
			</div>

			<cart-drawer-items<?php echo $cart_is_empty ? ' class="is-empty"' : ''; ?>>
				<form
					action="<?php echo esc_url( wc_get_cart_url() ); ?>"
					id="CartDrawer-Form"
					class="cart__contents cart-drawer__form"
					method="post"
				>
					<?php echo panstellar_cart_drawer_items_html(); // phpcs:ignore WordPress.Security.EscapeOutput -- HTML généré par le helper. ?>
					<div id="CartDrawer-CartErrors" role="alert"></div>
				</form>
			</cart-drawer-items>

			<?php if ( ! $cart_is_empty ) : ?>
				<div class="drawer__footer">
					<?php echo panstellar_cart_drawer_footer_html(); // phpcs:ignore WordPress.Security.EscapeOutput -- HTML généré par le helper. ?>
					<div class="cart__ctas">
						<a
							href="<?php echo esc_url( wc_get_cart_url() ); ?>"
							class="cart__update-button button button--secondary"
						>
							<?php esc_html_e( 'View cart', 'panstellar' ); ?>
						</a>
						<a
							href="<?php echo esc_url( wc_get_checkout_url() ); ?>"
							id="CartDrawer-Checkout"
							class="cart__checkout-button button"
						>
							<?php esc_html_e( 'Check out', 'panstellar' ); ?>
						</a>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</cart-drawer>
