<?php
/**
 * Multicolumn home — template-parts/home/multicolumn.php
 *
 * Conversion de sections/multicolumn.liquid (thème Shopify Dawn 15.1.0).
 * Le HTML, les classes CSS et le responsive design sont conservés à l'identique.
 * Configuration réelle (templates/index.json) : « Why Choose Us? »,
 * 4 colonnes (Quality Guaranteed / Money Back Guarantee / SSL Secure /
 * Free and Fast Shipping), color scheme inverse, 4 colonnes desktop / 2 mobile.
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Réglages (équivalents de section.settings.*) ─────────────────────────
$title            = panstellar_home_setting( 'multicolumn_title' );
$heading_size     = panstellar_home_setting( 'multicolumn_heading_size' );
$image_width      = panstellar_home_setting( 'multicolumn_image_width' );
$image_ratio      = panstellar_home_setting( 'multicolumn_image_ratio' );
$columns_desktop  = (int) panstellar_home_setting( 'multicolumn_columns_desktop' );
$alignment        = panstellar_home_setting( 'multicolumn_alignment' );
$background_style = panstellar_home_setting( 'multicolumn_background' );
$button_label     = panstellar_home_setting( 'multicolumn_button_label' );
$button_link      = panstellar_home_setting( 'multicolumn_button_link' );
$color_scheme     = panstellar_home_setting( 'multicolumn_color_scheme' );
$columns_mobile   = panstellar_home_setting( 'multicolumn_columns_mobile' );
$swipe_on_mobile  = (bool) panstellar_home_setting( 'multicolumn_swipe_mobile' );
$columns          = panstellar_home_setting( 'multicolumn_columns' ); // array( image, title, text ).

$show_mobile_slider = $swipe_on_mobile && count( $columns ) > (int) $columns_mobile;
?>

<div class="multicolumn color-<?php echo esc_attr( $color_scheme ); ?> gradient background-<?php echo esc_attr( $background_style ); ?><?php echo ! $title ? ' no-heading' : ''; ?>">
	<div class="page-width home-multicolumn-padding isolate">
		<?php if ( $title ) : ?>
			<div class="title-wrapper-with-link title-wrapper--self-padded-mobile title-wrapper--no-top-margin multicolumn__title">
				<h2 class="title inline-richtext <?php echo esc_attr( $heading_size ); ?>">
					<?php echo esc_html( $title ); ?>
				</h2>
			</div>
		<?php endif; ?>

		<slider-component class="slider-mobile-gutter">
			<ul
				class="multicolumn-list contains-content-container grid grid--<?php echo esc_attr( $columns_mobile ); ?>-col-tablet-down grid--<?php echo esc_attr( $columns_desktop ); ?>-col-desktop<?php echo $show_mobile_slider ? ' slider slider--tablet grid--peek' : ''; ?>"
				id="Slider-home-multicolumn"
				role="list"
			>
				<?php foreach ( $columns as $index => $column ) : ?>
					<?php
					$col_image = isset( $column['image'] ) ? $column['image'] : '';
					$col_title = isset( $column['title'] ) ? $column['title'] : '';
					$col_text  = isset( $column['text'] ) ? $column['text'] : '';

					$empty_column = '';
					if ( ! $col_image && ! $col_title && ! $col_text ) {
						$empty_column = ' multicolumn-list__item--empty';
					}
					?>
					<li
						id="Slide-home-multicolumn-<?php echo esc_attr( $index + 1 ); ?>"
						class="multicolumn-list__item grid__item<?php echo $show_mobile_slider ? ' slider__slide' : ''; ?><?php echo 'center' === $alignment ? ' center' : ''; ?><?php echo esc_attr( $empty_column ); ?>"
					>
						<div class="multicolumn-card content-container">
							<?php if ( $col_image ) : ?>
								<div class="multicolumn-card__image-wrapper multicolumn-card__image-wrapper--<?php echo esc_attr( $image_width ); ?>-width multicolumn-card-spacing">
									<div class="media media--transparent media--<?php echo esc_attr( $image_ratio ); ?>">
										<?php if ( is_numeric( $col_image ) ) : ?>
											<?php echo wp_get_attachment_image( (int) $col_image, 'medium', false, array( 'class' => 'multicolumn-card__image', 'loading' => 'lazy' ) ); ?>
										<?php else : ?>
											<img
												src="<?php echo esc_url( $col_image ); ?>"
												alt="<?php echo esc_attr( wp_strip_all_tags( $col_title ? $col_title : $col_text ) ); ?>"
												class="multicolumn-card__image"
												loading="lazy"
											>
										<?php endif; ?>
									</div>
								</div>
							<?php endif; ?>
							<div class="multicolumn-card__info">
								<?php if ( $col_title ) : ?>
									<h3 class="inline-richtext"><?php echo esc_html( $col_title ); ?></h3>
								<?php endif; ?>
								<?php if ( $col_text ) : ?>
									<div class="rte"><?php echo wp_kses_post( $col_text ); ?></div>
								<?php endif; ?>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php if ( $show_mobile_slider ) : ?>
				<div class="slider-buttons large-up-hide">
					<button type="button" class="slider-button slider-button--prev" name="previous" aria-label="<?php esc_attr_e( 'Previous slide', 'panstellar' ); ?>">
						<span class="svg-wrapper"><?php panstellar_icon( 'caret' ); ?></span>
					</button>
					<div class="slider-counter caption">
						<span class="slider-counter--current">1</span>
						<span aria-hidden="true"> / </span>
						<span class="visually-hidden"><?php esc_html_e( 'of', 'panstellar' ); ?></span>
						<span class="slider-counter--total"><?php echo esc_html( count( $columns ) ); ?></span>
					</div>
					<button type="button" class="slider-button slider-button--next" name="next" aria-label="<?php esc_attr_e( 'Next slide', 'panstellar' ); ?>">
						<span class="svg-wrapper"><?php panstellar_icon( 'caret' ); ?></span>
					</button>
				</div>
			<?php endif; ?>
		</slider-component>

		<?php if ( $button_label ) : ?>
			<div class="center<?php echo $show_mobile_slider ? ' small-hide medium-hide' : ''; ?>">
				<a
					class="button button--primary"
					<?php if ( $button_link ) : ?>
						href="<?php echo esc_url( $button_link ); ?>"
					<?php else : ?>
						role="link" aria-disabled="true"
					<?php endif; ?>
				>
					<?php echo esc_html( $button_label ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</div>
