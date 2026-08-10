<?php
/**
 * Spinner de chargement — template-parts/loading-spinner.php
 *
 * Conversion de snippets/loading-spinner.liquid.
 *
 * Usage :
 *   get_template_part( 'template-parts/loading-spinner', null, array( 'class' => 'predictive-search__loading-state' ) );
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$class = isset( $args['class'] ) ? $args['class'] : 'loading__spinner hidden';
$path  = get_template_directory() . '/assets/icons/loading-spinner.svg';
?>
<div class="<?php echo esc_attr( $class ); ?>">
	<?php
	if ( file_exists( $path ) ) {
		echo file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.Security.EscapeOutput -- SVG local inoffensif.
	} else {
		// Fallback spinner inline (aucune dépendance).
		?>
		<svg class="spinner" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
			<circle class="path" fill="none" stroke-width="6" stroke-linecap="round" cx="33" cy="33" r="30"></circle>
		</svg>
		<?php
	}
	?>
</div>
