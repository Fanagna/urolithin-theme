<?php
/**
 * Footer WordPress — footer.php
 *
 * Correspondance de layout/theme.liquid (partie basse) :
 *   fermeture de <main> + footer + wp_footer().
 *
 * Le composant footer (conversion de sections/footer.liquid →
 * template-parts/footer.php) est inclus ci-dessous.
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main>

<?php
// Composant footer (conversion de sections/footer.liquid → template-parts/footer.php).
get_template_part( 'template-parts/footer' );

// Cart drawer (conversion de snippets/cart-drawer.liquid → template-parts/cart-drawer.php).
// Rend en fin de page comme dans layout/theme.liquid.
get_template_part( 'template-parts/cart-drawer' );
?>

<?php wp_footer(); ?>
</body>
</html>
