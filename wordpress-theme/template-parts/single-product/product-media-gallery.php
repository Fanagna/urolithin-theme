<?php
/**
 * Galerie média produit — template-parts/single-product/product-media-gallery.php
 *
 * Conversion de snippets/product-media-gallery.liquid (Dawn 15.1.0).
 * Le HTML, les classes CSS et le responsive design sont conservés.
 * Les médias proviennent de WooCommerce (image produit + galerie).
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Réglages (équivalents de section.settings.* du main-product) ─────────
$gallery_layout    = panstellar_theme_setting( 'product_gallery_layout', 'thumbnail_slider' ); // thumbnail_slider | thumbnail | columns | stacked
$media_size        = panstellar_theme_setting( 'product_media_size', 'small' );                // small | medium | large
$constrain_to_view = (bool) panstellar_theme_setting( 'product_constrain_to_viewport', true );
$media_fit         = panstellar_theme_setting( 'product_media_fit', 'cover' );                 // contain | cover
$image_zoom        = panstellar_theme_setting( 'product_image_zoom', 'hover' );                // lightbox | hover | none
$mobile_thumbnails = panstellar_theme_setting( 'product_mobile_thumbnails', 'show' );          // show | hide

$product = isset( $GLOBALS['product'] ) ? $GLOBALS['product'] : wc_get_product( get_the_ID() );
if ( ! $product ) {
	return;
}

$image_ids = array_filter( array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() ) );
if ( empty( $image_ids ) ) {
	return;
}

$zoom_class = 'none' === $image_zoom ? '' : ' image-zoom';
$gallery_class = 'thumbnail_slider' === $gallery_layout ? ' thumbnail-slider' : ( 'columns' === $gallery_layout ? ' columns' : ( 'stacked' === $gallery_layout ? ' stacked' : '' ) );
?>

<media-gallery
	class="product__media-gallery product__media-gallery--<?php echo esc_attr( $gallery_layout ); ?>"
	data-desktop-layout="<?php echo esc_attr( $gallery_layout ); ?>"
	data-media-size="<?php echo esc_attr( $media_size ); ?>"
	data-image-zoom="<?php echo esc_attr( $image_zoom ); ?>"
>
	<div
		id="GalleryStatus-<?php the_ID(); ?>"
		class="visually-hidden"
		role="status"
	></div>

	<slider-component
		id="GalleryViewer-<?php the_ID(); ?>"
		class="slider-mobile-gutter<?php echo esc_attr( $zoom_class ); ?>"
	>
		<a class="skip-to-content-link button visually-hidden quick-add-hidden" href="#ProductInfo-<?php the_ID(); ?>">
			<?php esc_html_e( 'Skip to product information', 'panstellar' ); ?>
		</a>
		<ul
			id="Slider-Gallery-<?php the_ID(); ?>"
			class="product__media-list contains-media grid grid--peek list-unstyled slider slider--everywhere"
			role="list"
		>
			<?php foreach ( $image_ids as $index => $image_id ) : ?>
				<?php
				$image_url  = wp_get_attachment_image_url( $image_id, 'large' );
				$image_full = wp_get_attachment_image_url( $image_id, 'full' );
				$alt        = trim( wp_strip_all_tags( get_post_meta( $image_id, '_wp_attachment_image_alt', true ) ) );
				if ( ! $alt ) {
					$alt = $product->get_name();
				}
				?>
				<li
					id="Slide-<?php the_ID(); ?>-<?php echo esc_attr( $index + 1 ); ?>"
					class="product__media-item grid__item slider__slide<?php echo 0 === $index ? ' is-active' : ''; ?>"
					data-media-id="<?php the_ID(); ?>-<?php echo esc_attr( $index + 1 ); ?>"
					role="listitem"
				>
					<div class="product__media media media--transparent global-media-settings" style="padding-bottom: 100%;">
						<?php if ( 'none' !== $image_zoom ) : ?>
							<a href="<?php echo esc_url( $image_full ); ?>" data-pswp-width="1600" data-pswp-height="1600" target="_blank">
						<?php endif; ?>
							<img
								src="<?php echo esc_url( $image_url ); ?>"
								alt="<?php echo esc_attr( $alt ); ?>"
								class="motion-reduce"
								width="1600"
								height="1600"
								sizes="(min-width: 1000px) calc((100vw - 11.5rem) / 2), calc(100vw - 4rem)"
								<?php echo 0 !== $index ? 'loading="lazy"' : 'loading="eager" fetchpriority="high"'; ?>
							>
						<?php if ( 'none' !== $image_zoom ) : ?>
							</a>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php if ( count( $image_ids ) > 1 ) : ?>
			<div class="slider-buttons no-js-hidden quick-add-hidden">
				<button type="button" class="slider-button slider-button--prev" name="previous" aria-label="<?php esc_attr_e( 'Slide left', 'panstellar' ); ?>">
					<span class="svg-wrapper"><?php panstellar_icon( 'caret' ); ?></span>
				</button>
				<div class="slider-counter caption">
					<span class="slider-counter--current">1</span>
					<span aria-hidden="true"> / </span>
					<span class="visually-hidden"><?php esc_html_e( 'of', 'panstellar' ); ?></span>
					<span class="slider-counter--total"><?php echo esc_html( count( $image_ids ) ); ?></span>
				</div>
				<button type="button" class="slider-button slider-button--next" name="next" aria-label="<?php esc_attr_e( 'Slide right', 'panstellar' ); ?>">
					<span class="svg-wrapper"><?php panstellar_icon( 'caret' ); ?></span>
				</button>
			</div>
		<?php endif; ?>
	</slider-component>

	<?php if ( 'thumbnail_slider' === $gallery_layout && count( $image_ids ) > 1 ) : ?>
		<slider-component
			id="GalleryThumbnails-<?php the_ID(); ?>"
			class="thumbnail-slider thumbnail-slider--no-slide"
		>
			<button type="button" class="slider-button slider-button--prev small-hide medium-hide large-up-hide" name="previous" aria-label="<?php esc_attr_e( 'Previous slide', 'panstellar' ); ?>" aria-controls="GalleryThumbnails-<?php the_ID(); ?>">
				<span class="svg-wrapper"><?php panstellar_icon( 'caret' ); ?></span>
			</button>
			<ul
				id="Slider-Thumbnails-<?php the_ID(); ?>"
				class="thumbnail-list list-unstyled slider slider--mobile slider--tablet-up"
				role="list"
			>
				<?php foreach ( $image_ids as $index => $image_id ) : ?>
					<?php $thumb_url = wp_get_attachment_image_url( $image_id, 'medium' ); ?>
					<li
						id="Slide-Thumbnails-<?php the_ID(); ?>-<?php echo esc_attr( $index + 1 ); ?>"
						class="thumbnail-list__item slider__slide<?php echo 0 === $index ? ' is-active' : ''; ?>"
						data-target="<?php the_ID(); ?>-<?php echo esc_attr( $index + 1 ); ?>"
						data-media-position="<?php echo esc_attr( $index + 1 ); ?>"
					>
						<button class="thumbnail global-media-settings global-media-settings--no-shadow" type="button" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: position */ __( 'Load image %s in gallery view', 'panstellar' ), $index + 1 ) ); ?>" aria-current="<?php echo 0 === $index ? 'true' : 'false'; ?>">
							<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" width="416" height="416" loading="lazy">
						</button>
					</li>
				<?php endforeach; ?>
			</ul>
			<button type="button" class="slider-button slider-button--next small-hide medium-hide large-up-hide" name="next" aria-label="<?php esc_attr_e( 'Next slide', 'panstellar' ); ?>" aria-controls="GalleryThumbnails-<?php the_ID(); ?>">
				<span class="svg-wrapper"><?php panstellar_icon( 'caret' ); ?></span>
			</button>
		</slider-component>
	<?php endif; ?>
</media-gallery>
