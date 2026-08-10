<?php
/**
 * Info produit — template-parts/single-product/product-info.php
 *
 * Conversion des blocs de sections/main-product.liquid (Dawn 15.1.0) :
 *   title → price → description → variant_picker → quantity_selector → buy_buttons
 * (ordre réel de templates/product.json).
 * Les données proviennent de WooCommerce ($product) ; les classes CSS Dawn
 * sont conservées à l'identique.
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product = isset( $GLOBALS['product'] ) ? $GLOBALS['product'] : wc_get_product( get_the_ID() );
if ( ! $product ) {
	return;
}

$enable_sticky = (bool) panstellar_theme_setting( 'product_enable_sticky_info', true );
$product_id    = $product->get_id();
$product_title = $product->get_name();
$is_purchasable = $product->is_purchasable() && $product->is_in_stock();

// Variantes (WooCommerce).
$is_variable = $product->is_type( 'variable' );
$variations  = array();
if ( $is_variable && function_exists( 'wc_get_product' ) ) {
	$variations = $product->get_available_variations();
}
$selected_variant = $product->get_default_attributes();

// Prix (WooCommerce).
$price_html = $product->get_price_html();
?>

<product-info
	id="ProductInfo-<?php echo esc_attr( $product_id ); ?>"
	class="product__info-container<?php echo $enable_sticky ? ' product__column-sticky' : ''; ?>"
	data-section="<?php echo esc_attr( $product_id ); ?>"
	data-url="<?php the_permalink(); ?>"
	data-update-url="true"
>

	<?php
	// ── BLOC : title ──────────────────────────────────────────────────
	?>
	<div class="product__title">
		<h1><?php echo esc_html( $product_title ); ?></h1>
	</div>

	<?php
	// ── BLOC : prix (avec badges) ─────────────────────────────────────
	?>
	<div id="price-<?php echo esc_attr( $product_id ); ?>" role="status">
		<?php if ( $price_html ) : ?>
			<div class="price price--large">
				<?php echo wp_kses_post( $price_html ); ?>
			</div>
		<?php endif; ?>
	</div>

	<?php
	// ── BLOC : description ────────────────────────────────────────────
	$description = $product->get_description();
	if ( $description ) :
		?>
		<div class="product__description rte quick-add-hidden">
			<?php echo wp_kses_post( $description ); ?>
		</div>
	<?php endif; ?>

	<?php
	// ── BLOC : variant_picker (boutons radio) ────────────────────────
	// WooCommerce : on boucle sur les attributs de variation (color/size…).
	if ( $is_variable ) :
		$attributes = $product->get_variation_attributes();
		foreach ( $attributes as $attribute_name => $options ) :
			$taxonomy = wc_attribute_taxonomy_name( str_replace( 'pa_', '', $attribute_name ) );
			$label    = function_exists( 'wc_attribute_label' ) ? wc_attribute_label( $attribute_name, $product ) : $attribute_name;
			?>
			<variant-selects
				id="variant-selects-<?php echo esc_attr( $product_id ); ?>"
				class="no-js-hidden product-form__input"
				data-section="<?php echo esc_attr( $product_id ); ?>"
				data-url="<?php the_permalink(); ?>"
			>
				<div class="product-form__input product-form__input--dropdown">
					<label class="form__label" for="Option-<?php echo esc_attr( $product_id ); ?>-0">
						<?php echo esc_html( $label ); ?>
					</label>
					<select
						id="Option-<?php echo esc_attr( $product_id ); ?>-0"
						class="select__select"
						name="attribute_<?php echo esc_attr( $attribute_name ); ?>"
						form="product-form-<?php echo esc_attr( $product_id ); ?>"
					>
						<?php foreach ( $options as $option ) : ?>
							<option value="<?php echo esc_attr( $option ); ?>"<?php selected( isset( $selected_variant[ $attribute_name ] ) ? $selected_variant[ $attribute_name ] : '', $option ); ?>>
								<?php echo esc_html( $option ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</variant-selects>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php
	// ── BLOC : quantity_selector ──────────────────────────────────────
	?>
	<div id="Quantity-Form-<?php echo esc_attr( $product_id ); ?>" class="product-form__input product-form__quantity">
		<label class="quantity__label form__label" for="Quantity-<?php echo esc_attr( $product_id ); ?>">
			<?php esc_html_e( 'Quantity', 'panstellar' ); ?>
		</label>
		<quantity-input class="quantity">
			<button class="quantity__button" name="minus" type="button">
				<span class="visually-hidden"><?php esc_html_e( 'Decrease quantity', 'panstellar' ); ?></span>
				<span class="svg-wrapper"><?php panstellar_icon( 'minus' ); ?></span>
			</button>
			<input
				class="quantity__input"
				type="number"
				name="quantity"
				id="Quantity-<?php echo esc_attr( $product_id ); ?>"
				data-cart-quantity="0"
				data-min="1"
				min="1"
				step="1"
				value="1"
				form="product-form-<?php echo esc_attr( $product_id ); ?>"
			>
			<button class="quantity__button" name="plus" type="button">
				<span class="visually-hidden"><?php esc_html_e( 'Increase quantity', 'panstellar' ); ?></span>
				<span class="svg-wrapper"><?php panstellar_icon( 'plus' ); ?></span>
			</button>
		</quantity-input>
	</div>

	<?php
	// ── BLOC : buy_buttons (form WooCommerce AJAX) ────────────────────
	?>
	<div class="product-form__buttons">
		<form
			action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>"
			class="cart"
			method="post"
			enctype="multipart/form-data"
			id="product-form-<?php echo esc_attr( $product_id ); ?>"
		>
			<?php
			// Variante sélectionnée par défaut (si produit variable).
			if ( $is_variable && function_exists( 'wc_get_product' ) ) :
				$default_variation_id = $product->get_children();
				$default_variation_id = ! empty( $default_variation_id ) ? reset( $default_variation_id ) : 0;
				?>
				<input type="hidden" name="variation_id" class="variation_id" value="<?php echo esc_attr( $default_variation_id ); ?>">
				<input type="hidden" name="product_id" value="<?php echo esc_attr( $product_id ); ?>">
			<?php else : ?>
				<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product_id ); ?>">
			<?php endif; ?>

			<?php if ( $is_variable ) : ?>
				<div class="woocommerce-variation-add-to-cart variations_button">
			<?php endif; ?>

			<button
				type="submit"
				name="add"
				class="product-form__submit button button--full-width button--primary"
				<?php echo $is_purchasable ? '' : 'disabled'; ?>
			>
				<span><?php echo $is_purchasable ? esc_html__( 'Add to cart', 'panstellar' ) : esc_html__( 'Sold out', 'panstellar' ); ?></span>
				<div class="loading-overlay__spinner hidden">
					<?php get_template_part( 'template-parts/loading-spinner' ); ?>
				</div>
			</button>

			<?php if ( $is_variable ) : ?>
				</div>
			<?php endif; ?>
		</form>
	</div>

</product-info>
