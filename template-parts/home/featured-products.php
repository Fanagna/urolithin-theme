<?php
/**
 * Featured Products — template-parts/home/featured-products.php
 *
 * Reconstruction du bloc AI « Featured Products » (ai_gen_block_805788d
 * dans templates/index.json). Ce bloc n'a pas de section Liquid source :
 * il a été généré par le générateur de blocs AI Shopify. On reproduit son
 * design (fond sombre, prix turquoise, cartes arrondies) avec un carrousel
 * de produits WooCommerce.
 *
 * Réglages réels du bloc : collection « best-seller », 12 produits,
 * 4 par ligne desktop / 2 tablette, fond #0a0a0a, prix #00d4aa…
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Réglages (équivalents des settings du bloc AI) ───────────────────────
$heading          = panstellar_home_setting( 'featured_heading' );
$subheading       = panstellar_home_setting( 'featured_subheading' );
$collection_slug  = panstellar_home_setting( 'featured_collection' );
$products_count   = (int) panstellar_home_setting( 'featured_products_count' );
$button_text      = panstellar_home_setting( 'featured_button_text' );
$per_row_desktop  = (int) panstellar_home_setting( 'featured_per_row_desktop' );
$per_row_tablet   = (int) panstellar_home_setting( 'featured_per_row_tablet' );

// WooCommerce requis pour ce bloc — sinon on n'affiche rien.
if ( ! function_exists( 'wc_get_products' ) ) {
	return;
}

// ── Récupération des produits de la collection (catégorie WooCommerce) ───
$args = array(
	'status'   => 'publish',
	'limit'    => $products_count,
	'orderby'  => 'date',
	'order'    => 'DESC',
	'paginate' => false,
);

if ( $collection_slug ) {
	$args['category'] = array( $collection_slug );
}

// Filtre pour personnaliser la requête produits.
$args = apply_filters( 'panstellar_home_featured_products_args', $args, $collection_slug );

$products = wc_get_products( $args );

// Fallback : si la catégorie n'existe pas, on affiche les derniers produits publiés.
if ( empty( $products ) ) {
	$args = array(
		'status'  => 'publish',
		'limit'   => $products_count,
		'orderby' => 'date',
		'order'   => 'DESC',
	);
	$args = apply_filters( 'panstellar_home_featured_products_fallback_args', $args );
	$products = wc_get_products( $args );
}

if ( empty( $products ) ) {
	return;
}

// ── CSS dynamique : couleurs du bloc (settings AI via panstellar_home_setting) ──
// sanitize_hex_color renvoie '' si invalide/vide → propriété CSS ignorée (sûr).
$hex = function ( $key ) {
	return sanitize_hex_color( (string) panstellar_home_setting( $key ) );
};

$css  = '.home-featured-products { background-color: ' . $hex( 'featured_background_color' ) . '; }' . "\n";
$css .= '.home-featured-products .featured-carousel__heading { color: ' . $hex( 'featured_heading_color' ) . '; }' . "\n";
$css .= '.home-featured-products .featured-carousel__subheading { color: ' . $hex( 'featured_subheading_color' ) . '; }' . "\n";
$css .= '.home-featured-products .featured-card { background: ' . $hex( 'featured_card_background' ) . '; border-radius: ' . (int) panstellar_home_setting( 'featured_card_border_radius' ) . 'px; }' . "\n";
$css .= '.home-featured-products .featured-card__image { background: ' . $hex( 'featured_image_background' ) . '; }' . "\n";
$css .= '.home-featured-products .featured-card__title { color: ' . $hex( 'featured_product_title_color' ) . '; }' . "\n";
$css .= '.home-featured-products .featured-card__price { color: ' . $hex( 'featured_price_color' ) . '; }' . "\n";
$css .= '.home-featured-products .featured-card__button { background: ' . $hex( 'featured_button_background' ) . '; color: ' . $hex( 'featured_button_text_color' ) . '; border-radius: ' . (int) panstellar_home_setting( 'featured_button_border_radius' ) . 'px; }' . "\n";
$css .= '.home-featured-products .featured-card__button:hover { background: ' . $hex( 'featured_button_hover_background' ) . '; }' . "\n";
$css .= '.home-featured-products .featured-nav { background: ' . $hex( 'featured_nav_button_background' ) . '; color: ' . $hex( 'featured_nav_button_color' ) . '; }' . "\n";
$css .= '.home-featured-products .featured-nav:hover { background: ' . $hex( 'featured_nav_button_hover_background' ) . '; }' . "\n";

wp_register_style( 'panstellar-home-featured', false, array( 'panstellar-home' ), PANSTELLAR_VERSION );
wp_enqueue_style( 'panstellar-home-featured' );
wp_add_inline_style( 'panstellar-home-featured', $css );
?>

<section class="home-featured-products" aria-label="<?php echo esc_attr( $heading ); ?>">
	<div class="featured-carousel page-width" style="max-width: <?php echo esc_attr( (int) panstellar_home_setting( 'featured_container_width' ) ); ?>px;">
		<?php if ( $heading || $subheading ) : ?>
			<div class="featured-carousel__header">
				<?php if ( $heading ) : ?>
					<h2 class="featured-carousel__heading title"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>
				<?php if ( $subheading ) : ?>
					<p class="featured-carousel__subheading"><?php echo esc_html( $subheading ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<slider-component class="featured-carousel__slider slider-mobile-gutter">
			<ul
				class="featured-carousel__list slider slider--tablet slider--everywhere grid--peek"
				id="Slider-featured"
				role="list"
				style="--featured-cols-desktop: <?php echo esc_attr( $per_row_desktop ); ?>; --featured-cols-tablet: <?php echo esc_attr( $per_row_tablet ); ?>;"
			>
				<?php foreach ( $products as $index => $product ) : ?>
					<?php
					if ( ! is_object( $product ) || ! method_exists( $product, 'get_id' ) ) {
						continue;
					}
					$product_id    = $product->get_id();
					$product_url   = get_permalink( $product_id );
					$product_name  = $product->get_name();
					$product_image = $product->get_image( 'woocommerce_medium', array( 'loading' => 'lazy' ) );
					?>
					<li id="Slide-featured-<?php echo esc_attr( $index + 1 ); ?>" class="featured-card slider__slide grid__item" role="group">
						<?php if ( $product_image ) : ?>
							<a href="<?php echo esc_url( $product_url ); ?>" class="featured-card__image" tabindex="-1" aria-hidden="true">
								<?php echo $product_image; // phpcs:ignore WordPress.Security.EscapeOutput -- HTML généré par WooCommerce. ?>
							</a>
						<?php endif; ?>
						<div class="featured-card__body">
							<h3 class="featured-card__title">
								<a href="<?php echo esc_url( $product_url ); ?>"><?php echo esc_html( $product_name ); ?></a>
							</h3>
							<div class="featured-card__price">
								<?php echo wp_kses_post( $product->get_price_html() ); ?>
							</div>
							<?php if ( $button_text ) : ?>
								<a href="<?php echo esc_url( $product_url ); ?>" class="featured-card__button">
									<?php echo esc_html( $button_text ); ?>
								</a>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php if ( count( $products ) > $per_row_desktop ) : ?>
				<div class="featured-carousel__controls slider-buttons">
					<button type="button" class="featured-nav slider-button slider-button--prev" name="previous" aria-label="<?php esc_attr_e( 'Previous slide', 'panstellar' ); ?>">
						<span class="svg-wrapper"><?php panstellar_icon( 'caret' ); ?></span>
					</button>
					<button type="button" class="featured-nav slider-button slider-button--next" name="next" aria-label="<?php esc_attr_e( 'Next slide', 'panstellar' ); ?>">
						<span class="svg-wrapper"><?php panstellar_icon( 'caret' ); ?></span>
					</button>
				</div>
			<?php endif; ?>
		</slider-component>
	</div>
</section>
