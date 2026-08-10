<?php
/**
 * Template catalogue — archive-product.php
 *
 * Conversion de templates/collection.json (boutique réelle) :
 *   order: [banner (main-collection-banner), product-grid (main-collection-product-grid)]
 *
 * Config réelle de la boutique :
 *   - 20 produits / page, 4 colonnes desktop, 2 colonnes mobile
 *   - image_ratio: portrait, image_shape: blob, show_secondary_image: true
 *   - quick_add: standard, filtering: vertical, sorting: true
 *
 * La boucle utilise la requête principale WooCommerce (WC_Query applique
 * nativement orderby / min_price / max_price / filter_<taxonomy>).
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="MainContent" class="content-for-layout focus-none" role="main" tabindex="-1">
	<?php
	// ── Bloc : bannière de collection (main-collection-banner.liquid) ──
	get_template_part( 'template-parts/collection/banner' );
	?>

	<?php
	// ── Bloc : grille de produits + facets (main-collection-product-grid.liquid) ──
	?>
	<div class="section-product-grid-padding gradient color-<?php echo esc_attr( panstellar_theme_setting( 'collection_color_scheme', 'inverse' ) ); ?>">
		<?php
		// Facets : tri vertical + filtres (snippets/facets.liquid).
		get_template_part(
			'template-parts/collection/facets',
			null,
			array(
				'enable_filtering' => (bool) panstellar_theme_setting( 'collection_enable_filtering', true ),
				'enable_sorting'   => (bool) panstellar_theme_setting( 'collection_enable_sorting', true ),
				'filter_type'      => panstellar_theme_setting( 'collection_filter_type', 'vertical' ),
			)
		);
		?>

		<?php
		// ── Grille produit (card-product.liquid) + pagination ──────────
		?>
		<div class="product-grid-container scroll-trigger animate--slide-in" id="ProductGridContainer" data-cascade>
			<?php if ( ! have_posts() ) : ?>
				<div class="collection collection--empty page-width" id="product-grid" data-id="product-grid">
					<div class="loading-overlay gradient"></div>
					<div class="title-wrapper center">
						<h2 class="title title--primary">
							<?php esc_html_e( 'No products found', 'woocommerce' ); ?>
							<br>
							<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="underlined-link link">
								<?php esc_html_e( 'Use fewer filters or clear all', 'woocommerce' ); ?>
							</a>
						</h2>
					</div>
				</div>
			<?php else : ?>
				<div class="collection page-width">
					<div class="loading-overlay gradient"></div>
					<ul
						id="product-grid"
						data-id="product-grid"
						class="
							grid product-grid grid--2-col-tablet-down
							grid--4-col-desktop
						"
					>
						<?php
						$index = 0;
						while ( have_posts() ) {
							the_post();
							$index++;

							$card_product = wc_get_product( get_the_ID() );
							if ( ! $card_product ) {
								continue;
							}
							?>
							<li
								class="grid__item scroll-trigger animate--slide-in"
								data-cascade
								style="--animation-order: <?php echo esc_attr( $index ); ?>;"
							>
								<?php
								get_template_part(
									'template-parts/product-card',
									null,
									array(
										'product'    => $card_product,
										'section_id' => 'product-grid',
										'lazy_load'  => $index > 2,
										'quick_add'  => panstellar_theme_setting( 'collection_quick_add', 'standard' ),
									)
								);
								?>
							</li>
							<?php
						}
						?>
					</ul>

					<?php
					// ── Pagination (snippets/pagination.liquid) ───────────────
					get_template_part( 'template-parts/collection/pagination' );
					?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</main>

<?php
get_footer();
