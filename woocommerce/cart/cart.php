<?php
/**
 * Page panier — woocommerce/cart/cart.php
 *
 * Conversion de templates/cart.json + sections/main-cart-items.liquid
 * + sections/main-cart-footer.liquid (Dawn 15).
 *
 * Structure Dawn fidèle :
 *   - cart-items : titre « Your cart », état vide, formulaire #cart
 *     avec le tableau .cart-items (quantité, suppression, prix)
 *   - cart-footer : note de commande, sous-total, bouton checkout
 *
 * Les données proviennent de WC()->cart. Les interactions AJAX
 * (quantité, suppression, note) sont gérées par assets/js/cart-page.js
 * via les endpoints WooCommerce (?wc-ajax=update_qty / remove_cart_item).
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
	return;
}

$cart            = WC()->cart;
$cart_is_empty   = $cart->is_empty();
$continue_url    = apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) );
$show_cart_note  = (bool) panstellar_theme_setting( 'cart_show_note', true );
?>

<cart-items class="gradient color-<?php echo esc_attr( panstellar_theme_setting( 'cart_color_scheme', 'background-1' ) ); ?> isolate<?php echo $cart_is_empty ? ' is-empty' : ' section-cart-items-padding'; ?>">
	<div class="page-width">
		<div class="title-wrapper-with-link">
			<h1 class="title title--primary"><?php esc_html_e( 'Your cart', 'panstellar' ); ?></h1>
			<a href="<?php echo esc_url( $continue_url ); ?>" class="underlined-link">
				<?php esc_html_e( 'Continue shopping', 'woocommerce' ); ?>
			</a>
		</div>

		<div class="cart__warnings">
			<h1 class="cart__empty-text"><?php esc_html_e( 'Your cart is empty', 'woocommerce' ); ?></h1>
			<a href="<?php echo esc_url( $continue_url ); ?>" class="button">
				<?php esc_html_e( 'Continue shopping', 'woocommerce' ); ?>
			</a>
		</div>

		<form action="<?php echo esc_url( wc_get_cart_url() ); ?>" class="cart__contents critical-hidden" method="post" id="cart">
			<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
			<?php
			// ── Tableau des items (rendu initial — le helper inclut le wrapper #main-cart-items) ──
			panstellar_cart_page_items_html( true );
			?>

			<p class="visually-hidden" id="cart-live-region-text" aria-live="polite" role="status"></p>
			<p class="visually-hidden" id="shopping-cart-line-item-status" aria-live="polite" aria-hidden="true" role="status">
				<?php esc_html_e( 'Loading…', 'panstellar' ); ?>
			</p>
		</form>
	</div>
</cart-items>

<?php
// ── Footer du panier (main-cart-footer.liquid) ───────────────────
panstellar_cart_page_footer_html( true );
