<?php
/**
 * Page — page.php
 *
 * Conversion de templates/page.json + sections/main-page.liquid (Dawn 15).
 * Config réelle (page.json) : padding_top/bottom = 28.
 *
 * Particularité Dawn : le titre de la page est MASQUÉ dans la config réelle
 * (bloc commenté dans main-page.liquid) — on reproduit ce comportement.
 * Pour réafficher le titre : add_filter( 'panstellar_page_show_title', '__return_true' ).
 *
 * Correspondances Liquid → WordPress :
 *   - page.title    → get_the_title() (masqué par défaut)
 *   - page.content  → the_content()
 *   - {% style %}   → panstellar_page_dynamic_css() (padding responsive)
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// NB : les pages WooCommerce (boutique, panier, checkout, compte) chargent
// leurs propres templates via la hiérarchie WooCommerce — page.php ne
// s'applique qu'aux pages classiques du site.

get_header();

$show_title = (bool) apply_filters( 'panstellar_page_show_title', false );
?>

<main id="MainContent" class="content-for-layout focus-none" role="main" tabindex="-1">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<div class="page-width page-width--narrow section-main-page-padding">
			<?php if ( $show_title ) : ?>
				<h1 class="main-page-title page-title h0 scroll-trigger animate--fade-in">
					<?php the_title(); ?>
				</h1>
			<?php endif; ?>
			<div class="rte scroll-trigger animate--slide-in">
				<?php the_content(); ?>
			</div>
		</div>
	<?php endwhile; ?>
</main>

<?php
get_footer();
