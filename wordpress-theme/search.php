<?php
/**
 * Template recherche — search.php
 *
 * Conversion de templates/search.json + sections/main-search.liquid :
 *   - header : titre + formulaire de recherche (main-search)
 *   - facets horizontales (filter_type: horizontal, tri + filtres)
 *   - grille de résultats : produits (card-product), articles
 *     (article-card), pages (carte simple)
 *   - pagination (24 résultats / page)
 *
 * La requête principale est enrichie par panstellar_search_pre_get_posts
 * (post_type product + tri + filtres prix/catégorie).
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$search_performed = is_search();
$search_term      = get_search_query();
$found_posts      = (int) $GLOBALS['wp_query']->found_posts;

$show_facets = (bool) panstellar_theme_setting( 'search_enable_filtering', true ) || (bool) panstellar_theme_setting( 'search_enable_sorting', true );
?>

<main id="MainContent" class="content-for-layout focus-none" role="main" tabindex="-1">
	<div class="template-search<?php echo ( ! $search_performed || 0 === $found_posts ) ? ' template-search--empty' : ''; ?> section-search-padding">
		<div class="template-search__header page-width scroll-trigger animate--fade-in">
			<h1 class="h2 center">
				<?php
				if ( $search_performed ) {
					esc_html_e( 'Search results', 'panstellar' );
				} else {
					esc_html_e( 'Search', 'panstellar' );
				}
				?>
			</h1>

			<div class="template-search__search">
				<main-search>
					<form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" role="search" class="search">
						<div class="field">
							<input
								class="search__input field__input"
								id="Search-In-Template"
								type="search"
								name="s"
								value="<?php echo esc_attr( $search_term ); ?>"
								placeholder="<?php esc_attr_e( 'Search', 'panstellar' ); ?>"
								role="combobox"
								aria-expanded="false"
								aria-owns="predictive-search-results"
								aria-controls="predictive-search-results"
								aria-haspopup="listbox"
								aria-autocomplete="list"
								autocorrect="off"
								autocomplete="off"
								autocapitalize="off"
								spellcheck="false"
							>
							<label class="field__label" for="Search-In-Template"><?php esc_html_e( 'Search', 'panstellar' ); ?></label>

							<button
								type="reset"
								class="reset__button field__button<?php echo '' === $search_term ? ' hidden' : ''; ?>"
								aria-label="<?php esc_attr_e( 'Clear search term', 'woocommerce' ); ?>"
							>
								<span class="svg-wrapper"><?php panstellar_icon( 'reset' ); ?></span>
							</button>
							<button type="submit" class="search__button field__button" aria-label="<?php esc_attr_e( 'Search', 'panstellar' ); ?>">
								<span class="svg-wrapper"><?php panstellar_icon( 'search' ); ?></span>
							</button>
						</div>
					</form>
				</main-search>
			</div>

			<?php if ( $search_performed && 0 === $found_posts ) : ?>
				<p role="status"><?php printf( esc_html__( 'No results found for “%s”', 'woocommerce' ), esc_html( $search_term ) ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( $search_performed ) : ?>
			<?php
			// ── Facets horizontales (snippets/facets.liquid, mode horizontal) ──
			if ( $show_facets && $found_posts > 0 ) {
				get_template_part(
					'template-parts/search/facets',
					null,
					array(
						'enable_filtering' => (bool) panstellar_theme_setting( 'search_enable_filtering', true ),
						'enable_sorting'   => (bool) panstellar_theme_setting( 'search_enable_sorting', true ),
					)
				);
			}

			// ── Grille de résultats ─────────────────────────────────────
			?>
			<div class="product-grid-container page-width" id="ProductGridContainer">
				<?php if ( ! have_posts() ) : ?>
					<div class="template-search__results collection collection--empty page-width" id="product-grid" data-id="search">
						<div class="loading-overlay gradient"></div>
						<div class="title-wrapper center">
							<h2 class="title title--primary">
								<?php esc_html_e( 'No results found', 'woocommerce' ); ?>
								<br>
								<a href="<?php echo esc_url( remove_query_arg( array( 'filter_product_cat', 'min_price', 'max_price', 'orderby', 'paged' ), home_url( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/' ) ) ); ?>" class="underlined-link link">
									<?php esc_html_e( 'Use fewer filters or clear all', 'woocommerce' ); ?>
								</a>
							</h2>
						</div>
					</div>
				<?php else : ?>
					<div class="template-search__results collection page-width" id="product-grid" data-id="search">
						<div class="loading-overlay gradient"></div>
						<ul
							class="
								grid product-grid
								grid--2-col-tablet-down
								grid--4-col-desktop
							"
							role="list"
						>
							<?php
							$index = 0;
							while ( have_posts() ) {
								the_post();
								$index++;
								$post_type = get_post_type();
								?>
								<li class="grid__item scroll-trigger animate--slide-in" data-cascade>
									<?php if ( 'product' === $post_type ) : ?>
										<?php
										$search_product = wc_get_product( get_the_ID() );
										if ( $search_product ) {
										get_template_part(
											'template-parts/product-card',
											null,
											array(
												'product'           => $search_product,
												'section_id'        => 'search',
												'lazy_load'         => $index > 2,
												'quick_add'         => 'none',
												'media_aspect_ratio'=> panstellar_theme_setting( 'search_image_ratio', 'adapt' ),
												'image_shape'       => panstellar_theme_setting( 'search_image_shape', 'default' ),
											)
										);
										}
										?>
									<?php elseif ( 'post' === $post_type ) : ?>
										<?php
										get_template_part(
											'template-parts/search/article-card',
											null,
											array(
												'article_id' => get_the_ID(),
												'lazy_load'  => $index > 2,
												'show_date'  => (bool) panstellar_theme_setting( 'search_article_show_date', true ),
												'show_author'=> (bool) panstellar_theme_setting( 'search_article_show_author', false ),
											)
										);
										?>
									<?php else : ?>
										<?php
										// ── Carte « page » (case 'page' de Dawn) ──
										?>
										<div class="article-card-wrapper card-wrapper underline-links-hover">
											<div class="card card--card card--text ratio color-background-1" style="--ratio-percent: 100%;">
												<div class="card__content">
													<div class="card__information">
													<h3 class="card__heading">
														<a href="<?php the_permalink(); ?>" class="full-unstyled-link">
															<?php echo esc_html( mb_substr( get_the_title(), 0, 50 ) ); ?>
														</a>
													</h3>
													</div>
													<div class="card__badge bottom left">
														<span class="badge color-scheme-1"><?php esc_html_e( 'Page', 'panstellar' ); ?></span>
													</div>
												</div>
											</div>
										</div>
									<?php endif; ?>
								</li>
								<?php
							}
							?>
						</ul>

						<?php
						// ── Pagination (snippets/pagination.liquid) ────────
						get_template_part( 'template-parts/collection/pagination' );
						?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
