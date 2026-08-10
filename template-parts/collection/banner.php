<?php
/**
 * Bannière de collection — template-parts/collection/banner.php
 *
 * Conversion de sections/main-collection-banner.liquid
 * (template collection.json : show_collection_description = true,
 *  show_collection_image = false, color_scheme = inverse).
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$show_description = (bool) panstellar_theme_setting( 'collection_show_description', true );
$show_image       = (bool) panstellar_theme_setting( 'collection_show_image', false );
$color_scheme     = panstellar_theme_setting( 'collection_hero_color_scheme', 'inverse' );

// Contexte : page boutique ou taxonomie produit.
$collection_title = woocommerce_page_title( false );
$collection_image = '';
$description      = '';

if ( is_tax( 'product_cat' ) || is_tax( 'product_tag' ) ) {
	$term = get_queried_object();
	if ( $term instanceof WP_Term ) {
		$collection_title = $term->name;
		$description      = term_description( $term->term_id );
		$thumbnail_id     = get_term_meta( $term->term_id, 'thumbnail_id', true );
		if ( $thumbnail_id ) {
			$collection_image = wp_get_attachment_url( $thumbnail_id );
		}
	}
	} elseif ( is_shop() ) {
		// Description de la page boutique : contenu de la page WooCommerce
		// (équivalent de {{ collection.description }} de Dawn). NB :
		// woocommerce_page_description() echo au lieu de retourner → on lit
		// le contenu de la page directement.
		$shop_page_id = (int) wc_get_page_id( 'shop' );
		if ( $shop_page_id > 0 ) {
			$description = get_post_field( 'post_content', $shop_page_id );
			if ( empty( $description ) ) {
				$description = get_the_excerpt( $shop_page_id );
			}
			$collection_image = get_the_post_thumbnail_url( $shop_page_id, 'full' );
		}
	}

$collection_image = apply_filters( 'panstellar_collection_banner_image', $collection_image );
$description      = apply_filters( 'panstellar_collection_banner_description', $description );

$with_image_class = ( $show_image && $collection_image ) ? ' collection-hero--with-image' : '';
?>

<div class="collection-hero<?php echo esc_attr( $with_image_class ); ?> color-<?php echo esc_attr( $color_scheme ); ?> gradient">
	<div class="collection-hero__inner page-width scroll-trigger animate--fade-in">
		<div class="collection-hero__text-wrapper">
			<h1 class="collection-hero__title">
				<span class="visually-hidden"><?php esc_html_e( 'Collection:', 'panstellar' ); ?> </span>
				<?php echo esc_html( $collection_title ); ?>
			</h1>

			<?php if ( $show_description && $description ) : ?>
				<div class="collection-hero__description rte"><?php echo wp_kses_post( $description ); ?></div>
			<?php endif; ?>
		</div>

		<?php if ( $show_image && $collection_image ) : ?>
			<div class="collection-hero__image-container media gradient">
				<img
					loading="lazy"
					src="<?php echo esc_url( $collection_image ); ?>"
					alt="<?php echo esc_attr( $collection_title ); ?>"
					width="750"
					height="750"
				>
			</div>
		<?php endif; ?>
	</div>
</div>
