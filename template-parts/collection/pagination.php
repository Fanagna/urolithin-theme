<?php
/**
 * Pagination — template-parts/collection/pagination.php
 *
 * Conversion de snippets/pagination.liquid (Dawn 15) :
 * flèche précédent, pages numérotées, flèche suivant.
 * Liens construits manuellement (add_query_arg) pour préserver les
 * filtres actifs et éviter l'encodage du placeholder « %#% ».
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_query;

$total   = isset( $wp_query->max_num_pages ) ? (int) $wp_query->max_num_pages : 1;
$current = max( 1, (int) get_query_var( 'paged' ) );

if ( $total <= 1 ) {
	return;
}

// URL de base : page courante sans la pagination (filtres préservés).
$base_url = apply_filters( 'panstellar_pagination_base_url', '' );
if ( ! $base_url ) {
	$base_url = remove_query_arg( 'paged' );
}

// Pages numérotées (end_size = 1, mid_size = 2 — comme paginate_links()).
$page_numbers = array();
$ellipsis_shown = false;
for ( $i = 1; $i <= $total; $i++ ) {
	if ( 1 === $i || $total === $i || abs( $i - $current ) <= 2 ) {
		$page_numbers[] = $i;
		$ellipsis_shown = false;
	} elseif ( ! $ellipsis_shown ) {
		$page_numbers[] = '…';
		$ellipsis_shown = true;
	}
}
?>

<div class="pagination-wrapper">
	<nav class="pagination" role="navigation" aria-label="<?php esc_attr_e( 'Pagination', 'panstellar' ); ?>">
		<ul class="pagination__list list-unstyled" role="list">
			<?php if ( $current > 1 ) : ?>
				<li>
					<a
						href="<?php echo esc_url( add_query_arg( 'paged', $current - 1, $base_url ) ); ?>"
						class="pagination__item pagination__item--next pagination__item-arrow link motion-reduce"
						aria-label="<?php esc_attr_e( 'Previous page', 'woocommerce' ); ?>"
					>
						<span class="svg-wrapper">
							<?php panstellar_icon( 'caret' ); ?>
						</span>
					</a>
				</li>
			<?php endif; ?>

			<?php foreach ( $page_numbers as $page ) : ?>
				<li>
					<?php if ( '…' === $page ) : ?>
						<span class="pagination__item"><?php echo esc_html( $page ); ?></span>
					<?php elseif ( (int) $page === $current ) : ?>
						<a
							role="link"
							aria-disabled="true"
							class="pagination__item pagination__item--current light"
							aria-current="page"
							aria-label="<?php printf( esc_attr__( 'Page %s', 'woocommerce' ), esc_attr( $page ) ); ?>"
						>
							<?php echo esc_html( $page ); ?>
						</a>
					<?php else : ?>
						<a
							href="<?php echo esc_url( add_query_arg( 'paged', $page, $base_url ) ); ?>"
							class="pagination__item link"
							aria-label="<?php printf( esc_attr__( 'Page %s', 'woocommerce' ), esc_attr( $page ) ); ?>"
						>
							<?php echo esc_html( $page ); ?>
						</a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>

			<?php if ( $current < $total ) : ?>
				<li>
					<a
						href="<?php echo esc_url( add_query_arg( 'paged', $current + 1, $base_url ) ); ?>"
						class="pagination__item pagination__item--prev pagination__item-arrow link motion-reduce"
						aria-label="<?php esc_attr_e( 'Next page', 'woocommerce' ); ?>"
					>
						<span class="svg-wrapper">
							<?php panstellar_icon( 'caret' ); ?>
						</span>
					</a>
				</li>
			<?php endif; ?>
		</ul>
	</nav>
</div>
