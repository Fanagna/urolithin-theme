<?php
/**
 * Slideshow home — template-parts/home/slideshow.php
 *
 * Conversion de sections/slideshow.liquid (thème Shopify Dawn 15.1.0).
 * Le HTML, les classes CSS et le responsive design sont conservés à l'identique.
 * Configuration réelle (templates/index.json) : 1 slide « AFFORDABLE SUPPLEMENTS »,
 * layout full_bleed, hauteur medium, slider_visual counter, pas d'auto-rotate.
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Réglages (équivalents de section.settings.* / block.settings.*) ──────
$layout          = panstellar_home_setting( 'slideshow_layout' );
$height          = panstellar_home_setting( 'slideshow_height' );
$slider_visual   = panstellar_home_setting( 'slideshow_visual' );
$auto_rotate     = (bool) panstellar_home_setting( 'slideshow_autoplay' );
$speed           = (int) panstellar_home_setting( 'slideshow_speed' );
$show_text_below = (bool) panstellar_home_setting( 'slideshow_show_text_below' );

// Nombre de slides : la home réelle en a 1. Extensible via add_filter().
$slide_count = max( 1, (int) apply_filters( 'panstellar_home_slides_count', 1 ) );

$image                 = panstellar_home_setting( 'slideshow_image' );
$heading               = panstellar_home_setting( 'slideshow_heading' );
$heading_size          = panstellar_home_setting( 'slideshow_heading_size' );
$subheading            = panstellar_home_setting( 'slideshow_subheading' );
$button_label          = panstellar_home_setting( 'slideshow_button_label' );
$button_link           = panstellar_home_setting( 'slideshow_link' );
$box_align             = panstellar_home_setting( 'slideshow_box_align' );
$show_text_box         = (bool) panstellar_home_setting( 'slideshow_show_text_box' );
$text_alignment        = panstellar_home_setting( 'slideshow_text_alignment' );
$text_alignment_mobile = panstellar_home_setting( 'slideshow_text_alignment_mobile' );
$overlay_opacity       = (float) panstellar_home_setting( 'slideshow_overlay_opacity' ) / 100;
$color_scheme          = panstellar_home_setting( 'slideshow_color_scheme' );
$accessibility         = panstellar_home_setting( 'slideshow_accessibility' );

// URL du bouton : 'shop' → page boutique WooCommerce ; sinon URL ou lien interne.
if ( 'shop' === $button_link && function_exists( 'wc_get_page_permalink' ) ) {
	$button_url = wc_get_page_permalink( 'shop' );
	$button_url = $button_url ? $button_url : home_url( '/' );
} else {
	$button_url = $button_link;
}
?>

<slideshow-component
	class="slider-mobile-gutter<?php echo 'grid' === $layout ? ' page-width' : ''; ?><?php echo $show_text_below ? ' mobile-text-below' : ''; ?>"
	role="region"
	aria-roledescription="<?php esc_attr_e( 'Carousel', 'panstellar' ); ?>"
	aria-label="<?php echo esc_attr( $accessibility ); ?>"
>
	<?php if ( $auto_rotate && 1 < $slide_count ) : ?>
		<div class="slideshow__controls slideshow__controls--top slider-buttons<?php echo $show_text_below ? ' slideshow__controls--border-radius-mobile' : ''; ?>">
			<button type="button" class="slider-button slider-button--prev" name="previous" aria-label="<?php esc_attr_e( 'Previous slideshow', 'panstellar' ); ?>" aria-controls="Slider-home">
				<span class="svg-wrapper"><?php panstellar_icon( 'caret' ); ?></span>
			</button>
			<div class="slider-counter slider-counter--<?php echo esc_attr( $slider_visual ); ?> caption">
				<span class="slider-counter--current">1</span>
				<span aria-hidden="true"> / </span>
				<span class="visually-hidden"><?php esc_html_e( 'of', 'panstellar' ); ?></span>
				<span class="slider-counter--total"><?php echo esc_html( $slide_count ); ?></span>
			</div>
			<button type="button" class="slider-button slider-button--next" name="next" aria-label="<?php esc_attr_e( 'Next slideshow', 'panstellar' ); ?>" aria-controls="Slider-home">
				<span class="svg-wrapper"><?php panstellar_icon( 'caret' ); ?></span>
			</button>
			<button type="button" class="slideshow__autoplay slider-button" aria-label="<?php esc_attr_e( 'Pause slideshow', 'panstellar' ); ?>">
				<span class="svg-wrapper"><?php panstellar_icon( 'pause' ); ?></span>
				<span class="svg-wrapper"><?php panstellar_icon( 'play' ); ?></span>
			</button>
		</div>
	<?php endif; ?>

	<div
		class="slideshow banner banner--<?php echo esc_attr( $height ); ?> grid grid--1-col slider slider--everywhere<?php echo $show_text_below ? ' banner--mobile-bottom' : ''; ?><?php echo ! $image ? ' slideshow--placeholder' : ''; ?>"
		id="Slider-home"
		aria-live="polite"
		aria-atomic="true"
		data-autoplay="<?php echo $auto_rotate ? 'true' : 'false'; ?>"
		data-speed="<?php echo esc_attr( $speed ); ?>"
	>
		<style>
			#Slide-home-1 .banner__media::after { opacity: <?php echo esc_attr( $overlay_opacity ); ?>; }
		</style>
		<div class="slideshow__slide grid__item grid--1-col slider__slide" id="Slide-home-1" role="group" aria-roledescription="<?php esc_attr_e( 'Slide', 'panstellar' ); ?>" aria-label="1 <?php esc_attr_e( 'of', 'panstellar' ); ?> 1" tabindex="-1">
			<div class="slideshow__media banner__media media<?php echo ! $image ? ' placeholder' : ''; ?>">
				<?php if ( $image ) : ?>
					<?php if ( is_numeric( $image ) ) : ?>
						<?php echo wp_get_attachment_image( (int) $image, 'full', false, array( 'fetchpriority' => 'high', 'sizes' => '100vw', 'class' => 'motion-reduce' ) ); ?>
					<?php else : ?>
						<img
							src="<?php echo esc_url( $image ); ?>"
							alt="<?php echo esc_attr( $heading ); ?>"
							width="3840"
							height="2160"
							sizes="100vw"
							loading="eager"
							fetchpriority="high"
						>
					<?php endif; ?>
				<?php else : ?>
					<span class="home-slideshow-placeholder" aria-hidden="true"></span>
				<?php endif; ?>
			</div>
			<div class="slideshow__text-wrapper banner__content banner__content--<?php echo esc_attr( $box_align ); ?> page-width<?php echo ! $show_text_box ? ' banner--desktop-transparent' : ''; ?>">
				<div class="slideshow__text banner__box content-container content-container--full-width-mobile color-<?php echo esc_attr( $color_scheme ); ?> gradient slideshow__text--<?php echo esc_attr( $text_alignment ); ?> slideshow__text-mobile--<?php echo esc_attr( $text_alignment_mobile ); ?>">
					<?php if ( $heading ) : ?>
						<h2 class="banner__heading inline-richtext <?php echo esc_attr( $heading_size ); ?>">
							<?php echo esc_html( $heading ); ?>
						</h2>
					<?php endif; ?>
					<?php if ( $subheading ) : ?>
						<div class="banner__text rte">
							<p><?php echo esc_html( $subheading ); ?></p>
						</div>
					<?php endif; ?>
					<?php if ( $button_label ) : ?>
						<div class="banner__buttons">
							<a
								<?php if ( $button_url ) : ?>
									href="<?php echo esc_url( $button_url ); ?>"
								<?php else : ?>
									role="link" aria-disabled="true"
								<?php endif; ?>
								class="button button--primary"
							>
								<?php echo esc_html( $button_label ); ?>
							</a>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<?php if ( ! $auto_rotate && 1 < $slide_count ) : // contrôles uniquement si plusieurs slides (comme Dawn). ?>
		<div class="slideshow__controls slider-buttons<?php echo $show_text_below ? ' slideshow__controls--border-radius-mobile' : ''; ?>">
			<button type="button" class="slider-button slider-button--prev" name="previous" aria-label="<?php esc_attr_e( 'Previous slideshow', 'panstellar' ); ?>" aria-controls="Slider-home">
				<span class="svg-wrapper"><?php panstellar_icon( 'caret' ); ?></span>
			</button>
			<div class="slider-counter slider-counter--<?php echo esc_attr( $slider_visual ); ?> caption">
				<span class="slider-counter--current">1</span>
				<span aria-hidden="true"> / </span>
				<span class="visually-hidden"><?php esc_html_e( 'of', 'panstellar' ); ?></span>
				<span class="slider-counter--total"><?php echo esc_html( $slide_count ); ?></span>
			</div>
			<button type="button" class="slider-button slider-button--next" name="next" aria-label="<?php esc_attr_e( 'Next slideshow', 'panstellar' ); ?>" aria-controls="Slider-home">
				<span class="svg-wrapper"><?php panstellar_icon( 'caret' ); ?></span>
			</button>
		</div>
	<?php endif; ?>
</slideshow-component>
