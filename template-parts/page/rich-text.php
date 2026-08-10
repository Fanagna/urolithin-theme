<?php
/**
 * Rich text — template-parts/page/rich-text.php
 *
 * Conversion de sections/rich-text.liquid (Dawn 15) utilisée par la page
 * contact (templates/page.contact.json) :
 *   - heading : « Ask Us Anything » (h1)
 *   - text : « Have a question? … » (centré)
 *   - full_width: true, desktop_content_position: center,
 *     content_alignment: center, padding_top: 40, padding_bottom: 52
 *
 * Paramètres (via $args) :
 *   - blocks            array( 'heading' => …|'' , 'text' => …|'' , 'caption' => …|'' )
 *   - content_position  center | left | right (défaut center)
 *   - alignment         center | left | right (défaut center)
 *   - full_width        bool (défaut true)
 *   - color_scheme      scheme (défaut 'background-1')
 *   - padding_top/bottom (défaut 40 / 52)
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$blocks           = isset( $args['blocks'] ) ? $args['blocks'] : array( 'heading' => '', 'text' => '' );
$content_position = isset( $args['content_position'] ) ? $args['content_position'] : 'center';
$alignment        = isset( $args['alignment'] ) ? $args['alignment'] : 'center';
$full_width       = isset( $args['full_width'] ) ? (bool) $args['full_width'] : true;
$color_scheme     = isset( $args['color_scheme'] ) ? $args['color_scheme'] : 'background-1';
$padding_top      = isset( $args['padding_top'] ) ? (int) $args['padding_top'] : 40;
$padding_bottom   = isset( $args['padding_bottom'] ) ? (int) $args['padding_bottom'] : 52;

$heading = isset( $blocks['heading'] ) ? $blocks['heading'] : '';
$text    = isset( $blocks['text'] ) ? $blocks['text'] : '';
$caption = isset( $blocks['caption'] ) ? $blocks['caption'] : '';
$order   = 0;
?>

<div class="isolate<?php echo $full_width ? '' : ' page-width'; ?>">
	<div class="rich-text content-container color-<?php echo esc_attr( $color_scheme ); ?> gradient<?php echo $full_width ? ' rich-text--full-width content-container--full-width' : ''; ?> section-rich-text-padding">
		<div class="rich-text__wrapper rich-text__wrapper--<?php echo esc_attr( $content_position ); ?><?php echo $full_width ? ' page-width' : ''; ?>">
			<div class="rich-text__blocks <?php echo esc_attr( $alignment ); ?>">
				<?php if ( $caption ) : ?>
					<p class="rich-text__caption caption-with-letter-spacing scroll-trigger animate--slide-in" data-cascade style="--animation-order: <?php echo esc_attr( ++$order ); ?>;">
						<?php echo esc_html( $caption ); ?>
					</p>
				<?php endif; ?>

				<?php if ( $heading ) : ?>
					<h2 class="rich-text__heading rte inline-richtext h1 scroll-trigger animate--slide-in" data-cascade style="--animation-order: <?php echo esc_attr( ++$order ); ?>;">
						<?php echo wp_kses_post( $heading ); ?>
					</h2>
				<?php endif; ?>

				<?php if ( $text ) : ?>
					<div class="rich-text__text rte scroll-trigger animate--slide-in" data-cascade style="--animation-order: <?php echo esc_attr( ++$order ); ?>;">
						<?php echo wp_kses_post( $text ); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
