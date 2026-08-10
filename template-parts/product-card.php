<?php
/**
 * Carte produit — template-parts/product-card.php
 *
 * Conversion de snippets/card-product.liquid (Dawn 15) utilisée par
 * le template collection.json :
 *   - media_aspect_ratio: portrait (ratio 0.8)
 *   - image_shape: blob
 *   - show_secondary_image: true
 *   - show_vendor: false, show_rating: false
 *   - quick_add: standard
 *
 * Les données proviennent de WooCommerce ($product).
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var WC_Product $product */
$product = isset( $args['product'] ) ? $args['product'] : null;
if ( ! $product instanceof WC_Product ) {
	return;
}

$section_id = isset( $args['section_id'] ) ? $args['section_id'] : 'product-grid';
$lazy_load  = ! empty( $args['lazy_load'] );

// Ratio / forme de l'image (défauts = collection : portrait + blob).
$media_aspect_ratio = isset( $args['media_aspect_ratio'] ) ? $args['media_aspect_ratio'] : 'portrait';
$image_shape        = isset( $args['image_shape'] ) ? $args['image_shape'] : 'blob';

$product_id        = $product->get_id();
$permalink         = get_permalink( $product_id );
$product_title     = $product->get_name();
$is_available      = $product->is_in_stock();
$is_on_sale        = $product->is_on_sale();
$featured_image_id = $product->get_image_id();
$media_1_id        = 0;
$gallery_ids       = $product->get_gallery_image_ids();
if ( ! empty( $gallery_ids ) ) {
	$media_1_id = (int) $gallery_ids[0];
}	// ratio : portrait = 0.8, adapt = ratio de l'image (défaut 1), square = 1.
	$ratio = 1;
	if ( 'portrait' === $media_aspect_ratio ) {
		$ratio = 0.8;
	} elseif ( 'adapt' === $media_aspect_ratio ) {
		$image_id = $featured_image_id ? $featured_image_id : $media_1_id;
		$img_meta = $image_id ? wp_get_attachment_image_src( $image_id, 'full' ) : false;
		if ( $img_meta && ! empty( $img_meta[1] ) && $img_meta[1] > 0 ) {
			$ratio = $img_meta[2] / $img_meta[1];
		}
		if ( $ratio <= 0 ) {
			$ratio = 1;
		}
	}
	$shape_class = ( $image_shape && 'default' !== $image_shape ) ? ' shape--' . sanitize_html_class( $image_shape ) : '';
?>

<div class="card-wrapper product-card-wrapper underline-links-hover">
	<div
		class="
			card card--standard
			<?php echo $featured_image_id ? 'card--media' : 'card--text'; ?>
			card--shape
			card--extend-height
		"
		style="--ratio-percent: <?php echo esc_attr( 1 / $ratio * 100 ); ?>%;"
	>
		<div
			class="card__inner color-<?php echo esc_attr( panstellar_theme_setting( 'card_color_scheme', 'inverse' ) ); ?> gradient ratio"
			style="--ratio-percent: <?php echo esc_attr( 1 / $ratio * 100 ); ?>%;"
		>
			<?php if ( $featured_image_id ) : ?>
				<div class="card__media<?php echo esc_attr( $shape_class ); ?> color-<?php echo esc_attr( panstellar_theme_setting( 'card_color_scheme', 'inverse' ) ); ?> gradient">
					<div class="media media--transparent media--hover-effect">
						<?php
						echo wp_get_attachment_image(
							$featured_image_id,
							'woocommerce_thumbnail',
							false,
							array(
								'class'    => 'motion-reduce',
								'loading'  => $lazy_load ? 'lazy' : 'eager',
								'decoding' => 'async',
							)
						);
						?>

						<?php if ( $media_1_id ) : ?>
							<?php
							echo wp_get_attachment_image(
								$media_1_id,
								'woocommerce_thumbnail',
								false,
								array(
									'class'    => 'motion-reduce',
									'loading'  => 'lazy',
									'decoding' => 'async',
									'alt'      => '',
								)
							);
							?>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
			<div class="card__content">
				<div class="card__information">
					<h3 class="card__heading h5">
						<a
							href="<?php echo esc_url( $permalink ); ?>"
							id="CardLink-<?php echo esc_attr( $section_id ); ?>-<?php echo esc_attr( $product_id ); ?>"
							class="full-unstyled-link"
							aria-labelledby="CardLink-<?php echo esc_attr( $section_id ); ?>-<?php echo esc_attr( $product_id ); ?> Badge-<?php echo esc_attr( $section_id ); ?>-<?php echo esc_attr( $product_id ); ?>"
						>
							<?php echo esc_html( $product_title ); ?>
						</a>
					</h3>
				</div>
				<div class="card__badge bottom left">
					<?php if ( ! $is_available ) : ?>
						<span
							id="Badge-<?php echo esc_attr( $section_id ); ?>-<?php echo esc_attr( $product_id ); ?>"
							class="badge badge--bottom-left color-scheme-1"
						>
							<?php esc_html_e( 'Sold out', 'woocommerce' ); ?>
						</span>
					<?php elseif ( $is_on_sale ) : ?>
						<span
							id="Badge-<?php echo esc_attr( $section_id ); ?>-<?php echo esc_attr( $product_id ); ?>"
							class="badge badge--bottom-left color-scheme-4"
						>
							<?php esc_html_e( 'Sale', 'woocommerce' ); ?>
						</span>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<div class="card__content">
			<div class="card__information">
				<h3 class="card__heading h5">
					<a
						href="<?php echo esc_url( $permalink ); ?>"
						id="CardLink-<?php echo esc_attr( $section_id ); ?>-<?php echo esc_attr( $product_id ); ?>"
						class="full-unstyled-link"
						aria-labelledby="CardLink-<?php echo esc_attr( $section_id ); ?>-<?php echo esc_attr( $product_id ); ?> Badge-<?php echo esc_attr( $section_id ); ?>-<?php echo esc_attr( $product_id ); ?>"
					>
						<?php echo esc_html( $product_title ); ?>
					</a>
				</h3>
				<div class="card-information">
					<span class="caption-large light"></span>

					<?php
					// ── Prix (classes Dawn, CSS component-price.css) ──
					$price_html = $product->get_price_html();
					if ( $price_html ) :
						?>
						<div class="price">
							<?php echo wp_kses_post( $price_html ); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<?php
			// ── Quick add standard ──────────────────────────────────────
			$quick_add = isset( $args['quick_add'] ) ? $args['quick_add'] : 'standard';
			if ( 'standard' === $quick_add ) :
				$product_form_id = 'quick-add-' . $section_id . '-' . $product_id;
				$variation_count = $product->get_type() === 'variable' ? count( $product->get_children() ) : 1;
				?>
				<div class="quick-add no-js-hidden">
					<?php if ( $variation_count > 1 && 'variable' === $product->get_type() ) : ?>
						<?php
						// Produit variable : bouton « Choose options » → page produit
						// (équivalent du modal QuickAdd Dawn, simplifié côté WooCommerce).
						?>
						<a
							href="<?php echo esc_url( $permalink ); ?>"
							id="<?php echo esc_attr( $product_form_id ); ?>-submit"
							class="quick-add__submit button button--full-width button--secondary"
							aria-haspopup="dialog"
							aria-labelledby="<?php echo esc_attr( $product_form_id ); ?>-submit title-<?php echo esc_attr( $section_id ); ?>-<?php echo esc_attr( $product_id ); ?>"
						>
							<?php esc_html_e( 'Choose options', 'woocommerce' ); ?>
						</a>
					<?php else : ?>
						<?php if ( $is_available && $product->is_purchasable() ) : ?>
							<form
								action="<?php echo esc_url( wc_get_cart_url() ); ?>"
								class="form"
								method="post"
								data-type="add-to-cart-form"
							>
								<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product_id ); ?>">
								<?php if ( 'simple' === $product->get_type() ) : ?>
									<input type="hidden" name="quantity" value="1">
								<?php endif; ?>
								<button
									id="<?php echo esc_attr( $product_form_id ); ?>-submit"
									type="submit"
									name="add"
									class="quick-add__submit button button--full-width button--secondary"
									aria-haspopup="dialog"
									aria-labelledby="<?php echo esc_attr( $product_form_id ); ?>-submit title-<?php echo esc_attr( $section_id ); ?>-<?php echo esc_attr( $product_id ); ?>"
									aria-live="polite"
								>
									<span><?php esc_html_e( 'Add to cart', 'woocommerce' ); ?></span>
								</button>
							</form>
						<?php else : ?>
							<a
								href="<?php echo esc_url( $permalink ); ?>"
								id="<?php echo esc_attr( $product_form_id ); ?>-submit"
								class="quick-add__submit button button--full-width button--secondary"
								aria-haspopup="dialog"
								aria-labelledby="<?php echo esc_attr( $product_form_id ); ?>-submit title-<?php echo esc_attr( $section_id ); ?>-<?php echo esc_attr( $product_id ); ?>"
							>
								<span><?php esc_html_e( 'Sold out', 'woocommerce' ); ?></span>
							</a>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
