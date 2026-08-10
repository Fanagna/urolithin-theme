<?php
/**
 * Produits liés — template-parts/single-product/related-products.php
 *
 * Conversion de sections/related-products.liquid (Dawn 15.1.0).
 * Configuration réelle (templates/product.json) : heading « You may also like »,
 * 4 produits, 4 colonnes desktop / 2 mobile, image square.
 * Utilise les produits liés WooCommerce (wc_get_related_products).
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product = isset( $GLOBALS['product'] ) ? $GLOBALS['product'] : wc_get_product( get_the_ID() );
if ( ! $product || ! function_exists( 'wc_get_related_products' ) ) {
	return;
}

// ── Réglages (équivalents de section.settings.*) ─────────────────────────
$heading        = panstellar_theme_setting( 'related_heading', 'You may also like' );
$heading_size   = panstellar_theme_setting( 'related_heading_size', 'h2' );
$products_count = (int) panstellar_theme_setting( 'related_products_to_show', 4 );
$columns_desktop = (int) panstellar_theme_setting( 'related_columns_desktop', 4 );
$columns_mobile = panstellar_theme_setting( 'related_columns_mobile', '2' );

// Produits liés WooCommerce.
$related_ids = wc_get_related_products( $product->get_id(), $products_count );
if ( empty( $related_ids ) ) {
	return;
}

$related_products = array();
foreach ( $related_ids as $related_id ) {
	$related = wc_get_product( $related_id );
	if ( $related && $related->is_visible() ) {
		$related_products[] = $related;
	}
}
if ( empty( $related_products ) ) {
	return;
}
?>

<div class="related-products color-background-1 gradient no-js-hidden section-related-products-padding">
	<div class="page-width">
		<div class="title-wrapper-with-link title-wrapper--self-padded-mobile title-wrapper--no-top-margin">
			<?php if ( $heading ) : ?>
				<h2 class="title inline-richtext <?php echo esc_attr( $heading_size ); ?>">
					<?php echo esc_html( $heading ); ?>
				</h2>
			<?php endif; ?>
		</div>

		<slider-component class="slider-mobile-gutter">
			<ul
				class="related-products__list contains-card contains-card--product contains-card--standard grid grid--peek grid--2-col-tablet-down grid--<?php echo esc_attr( $columns_desktop ); ?>-col-desktop slider slider--tablet"
				id="Slider-related"
				role="list"
			>
				<?php foreach ( $related_products as $index => $related ) : ?>
					<li
						id="Slide-related-<?php echo esc_attr( $index + 1 ); ?>"
						class="grid__item slider__slide related-products__item"
						role="listitem"
					>
						<div class="card-wrapper product-card-wrapper">
							<div class="card card--standard card--media">
								<div class="card__inner color-background-1 gradient ratio" style="--ratio-percent: 100%;">
									<?php if ( has_post_thumbnail( $related->get_id() ) ) : ?>
										<a href="<?php echo esc_url( get_permalink( $related->get_id() ) ); ?>" class="card__media" aria-label="<?php echo esc_attr( $related->get_name() ); ?>">
											<div class="media media--transparent media--hover-effect">
												<?php echo $related->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy', 'class' => 'motion-reduce' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- HTML généré par WooCommerce. ?>
											</div>
										</a>
									<?php endif; ?>
								</div>
								<div class="card__content">
									<div class="card__information">
										<h3 class="card__heading h5">
											<a href="<?php echo esc_url( get_permalink( $related->get_id() ) ); ?>" class="full-unstyled-link">
												<?php echo esc_html( $related->get_name() ); ?>
											</a>
										</h3>
										<div class="card-information">
											<span class="caption-large light"></span>
											<div class="price">
												<?php echo wp_kses_post( $related->get_price_html() ); ?>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php if ( count( $related_products ) > $columns_desktop ) : ?>
				<div class="slider-buttons no-js-hidden">
					<button type="button" class="slider-button slider-button--prev" name="previous" aria-label="<?php esc_attr_e( 'Slide left', 'panstellar' ); ?>">
						<span class="svg-wrapper"><?php panstellar_icon( 'caret' ); ?></span>
					</button>
					<div class="slider-counter caption">
						<span class="slider-counter--current">1</span>
						<span aria-hidden="true"> / </span>
						<span class="visually-hidden"><?php esc_html_e( 'of', 'panstellar' ); ?></span>
						<span class="slider-counter--total"><?php echo esc_html( count( $related_products ) ); ?></span>
					</div>
					<button type="button" class="slider-button slider-button--next" name="next" aria-label="<?php esc_attr_e( 'Slide right', 'panstellar' ); ?>">
						<span class="svg-wrapper"><?php panstellar_icon( 'caret' ); ?></span>
					</button>
				</div>
			<?php endif; ?>
		</slider-component>
	</div>
</div>
