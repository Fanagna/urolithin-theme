<?php
/**
 * Page d'accueil — front-page.php
 *
 * Conversion de templates/index.json (boutique réelle) :
 *   ordre : slideshow → multicolumn → featured products.
 * Chaque section devient un template-part sous template-parts/home/.
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<?php
// 1. Slideshow (équivalent de la section slideshow_kn3RAk).
get_template_part( 'template-parts/home/slideshow' );

// 2. Multicolumn « Why Choose Us? » (équivalent de la section multicolumn_JkMKgh).
get_template_part( 'template-parts/home/multicolumn' );

// 3. Featured Products (équivalent du bloc AI 1774337735c49ca034).
get_template_part( 'template-parts/home/featured-products' );
?>

<?php
get_footer();
