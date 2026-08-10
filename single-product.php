<?php
/**
 * Template produit — single-product.php
 *
 * Conversion de templates/product.json (boutique réelle) :
 *   ordre : main-product (galerie + info) → related-products → apps.
 *
 * Deux modes :
 *   - Par défaut : layout Dawn (product-media-gallery + product-info + related).
 *   - PDP custom « UA Stack » : activable via le filtre
 *     panstellar_use_uas_pdp (par exemple sur un produit / une catégorie).
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Filtre permettant d'utiliser le PDP custom UA Stack pour certains produits.
$use_uas_pdp = (bool) apply_filters( 'panstellar_use_uas_pdp', false, get_the_ID() );
?>

<main id="MainContent" class="content-for-layout focus-none" role="main" tabindex="-1">
	<?php
	while ( have_posts() ) :
		the_post();

		// $product global attendu par les template-parts (pattern WooCommerce).
		global $product;
		$product = wc_get_product( get_the_ID() );
		?>
		<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'product' ); ?>>
			<?php if ( $use_uas_pdp ) : ?>
				<?php
				// ── PDP custom UA Stack (conversion de sections/uas-pdp.liquid) ──
				get_template_part( 'template-parts/single-product/uas-pdp' );
				?>
			<?php else : ?>
				<?php
				// ── Layout Dawn (conversion de sections/main-product.liquid) ──
				?>
				<div class="product product--small product--left product--thumbnail_slider grid grid--1-col grid--2-col-tablet">
					<div class="grid__item product__media-wrapper">
						<?php get_template_part( 'template-parts/single-product/product-media-gallery' ); ?>
					</div>
					<div class="product__info-wrapper grid__item">
						<?php get_template_part( 'template-parts/single-product/product-info' ); ?>
					</div>
				</div>

				<?php
				// ── Produits liés (conversion de sections/related-products.liquid) ──
				get_template_part( 'template-parts/single-product/related-products' );
				?>
			<?php endif; ?>
		</div>
		<?php
	endwhile;
	?>
</main>

<?php
get_footer();
