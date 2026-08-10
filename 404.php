<?php
/**
 * Page 404 — 404.php
 *
 * Le thème Shopify (Dawn 15) n'a pas de template 404 personnalisé : il
 * utilise le design Dawn par défaut (sections/main-404.liquid) :
 *   - titre « 404 »
 *   - « Page not found » + message
 *   - bouton « Continue shopping » vers la boutique
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$continue_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '';
if ( ! $continue_url ) {
	$continue_url = home_url( '/' );
}
?>

<main id="MainContent" class="content-for-layout focus-none" role="main" tabindex="-1">
	<div class="page-width page-width--narrow">
		<div class="section-404-padding">
			<h1 class="h0">404</h1>
			<h2 class="h2">
				<?php esc_html_e( 'Page not found', 'panstellar' ); ?>
			</h2>
			<p>
				<?php esc_html_e( 'The page you are looking for does not exist or has been moved. Please use the search or continue shopping.', 'panstellar' ); ?>
			</p>

			<div class="section-404__buttons">
				<a href="<?php echo esc_url( $continue_url ); ?>" class="button">
					<?php esc_html_e( 'Continue shopping', 'woocommerce' ); ?>
				</a>
			</div>

			<?php get_search_form(); ?>
		</div>
	</div>
</main>

<?php
get_footer();
