<?php
/**
 * Facets recherche (mode horizontal) — template-parts/search/facets.php
 *
 * Conversion de snippets/facets.liquid (Dawn 15) pour le template
 * search.json : filter_type = horizontal, enable_filtering = true,
 * enable_sorting = true.
 *
 * Rendu de la barre de filtres horizontale (dropdowns catégories + prix,
 * tri, compteur) + drawer mobile + pills actifs. Les IDs (FacetFiltersForm,
 * FacetFiltersFormMobile, SortBy…) sont identiques à ceux de la collection
 * pour réutiliser assets/js/collection.js.
 *
 * Les paramètres GET suivent les conventions WooCommerce : filter_product_cat,
 * min_price, max_price, orderby — appliqués par panstellar_search_pre_get_posts.
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$enable_filtering = ! empty( $args['enable_filtering'] );
$enable_sorting   = ! empty( $args['enable_sorting'] );

if ( ! $enable_filtering && ! $enable_sorting ) {
	return;
}

// ── Données partagées ────────────────────────────────────────────
$found_posts = isset( $GLOBALS['wp_query']->found_posts ) ? (int) $GLOBALS['wp_query']->found_posts : 0;
$search_term = get_search_query();

// URL de base : page de recherche sans filtres/tri/pagination.
$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
$base_url    = remove_query_arg( array( 'orderby', 'min_price', 'max_price', 'paged' ), home_url( $request_uri ) );
foreach ( array_keys( $_GET ) as $get_key ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( strpos( (string) $get_key, 'filter_' ) === 0 ) {
		$base_url = remove_query_arg( $get_key, $base_url );
	}
}
$base_url = apply_filters( 'panstellar_search_facets_base_url', $base_url );

// ── Tri (équivalent des search.sort_options de Dawn) ─────────────
$sort_options = array(
	'relevance' => __( 'Relevance', 'panstellar' ),
	'price'     => __( 'Price, low to high', 'woocommerce' ),
	'price-desc'=> __( 'Price, high to low', 'woocommerce' ),
	'title'     => __( 'Alphabetically, A-Z', 'woocommerce' ),
	'title-desc'=> __( 'Alphabetically, Z-A', 'woocommerce' ),
	'date'      => __( 'Date, new to old', 'woocommerce' ),
);
$current_sort = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'relevance'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( ! isset( $sort_options[ $current_sort ] ) ) {
	$current_sort = 'relevance';
}

// ── Pills actifs ─────────────────────────────────────────────────
$active_pills = array();

foreach ( $_GET as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( strpos( $key, 'filter_' ) === 0 && $value ) {
		$taxonomy = str_replace( 'filter_', '', $key );
		$slugs    = array_filter( array_map( 'sanitize_title', explode( ',', (string) $value ) ) );
		if ( $slugs ) {
			$tax = get_taxonomy( $taxonomy );
			if ( $tax ) {
				foreach ( $slugs as $slug ) {
					$term = get_term_by( 'slug', $slug, $taxonomy );
					if ( $term ) {
						$active_pills[] = array(
							'label' => $tax->labels->singular_name . ': ' . $term->name,
							'url'   => add_query_arg( array( $key => implode( ',', array_diff( $slugs, array( $slug ) ) ) ), $base_url ),
						);
					}
				}
			}
		}
	}
}

if ( isset( $_GET['min_price'] ) && '' !== $_GET['min_price'] && ! is_array( $_GET['min_price'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$min = wc_format_decimal( (string) wp_unslash( $_GET['min_price'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$active_pills[] = array(
		'label' => sprintf( __( 'Price: %s -', 'panstellar' ), wc_price( $min ) ),
		'url'   => remove_query_arg( 'min_price', $base_url ),
	);
}
if ( isset( $_GET['max_price'] ) && '' !== $_GET['max_price'] && ! is_array( $_GET['max_price'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$max = wc_format_decimal( (string) wp_unslash( $_GET['max_price'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$active_pills[] = array(
		'label' => sprintf( __( 'Price: - %s', 'panstellar' ), wc_price( $max ) ),
		'url'   => remove_query_arg( 'max_price', $base_url ),
	);
}
$active_pills = apply_filters( 'panstellar_search_facets_active_pills', $active_pills, $base_url );

// ── Groupes de filtres ───────────────────────────────────────────
$category_filters = array();
if ( taxonomy_exists( 'product_cat' ) ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'number'     => 50,
		)
	);
	if ( $terms && ! is_wp_error( $terms ) ) {
		$active_cats = isset( $_GET['filter_product_cat'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			? explode( ',', sanitize_text_field( wp_unslash( $_GET['filter_product_cat'] ) ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			: array();
		foreach ( $terms as $term ) {
			$category_filters[] = array(
				'label'  => $term->name,
				'slug'   => $term->slug,
				'count'  => (int) $term->count,
				'active' => in_array( $term->slug, $active_cats, true ),
			);
		}
	}
}
$category_filters = apply_filters( 'panstellar_search_facets_categories', $category_filters );

// Prix max.
$max_price = 0;
$max_price_product = wc_get_products(
	array(
		'status'  => 'publish',
		'limit'   => 1,
		'orderby' => 'price',
		'order'   => 'DESC',
	)
);
if ( ! empty( $max_price_product ) ) {
	$max_price = (float) $max_price_product[0]->get_price();
}
if ( ! $max_price ) {
	$max_price = 1000;
}
$min_price_active = isset( $_GET['min_price'] ) && '' !== $_GET['min_price'] && ! is_array( $_GET['min_price'] ) ? wc_format_decimal( (string) wp_unslash( $_GET['min_price'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$max_price_active = isset( $_GET['max_price'] ) && '' !== $_GET['max_price'] && ! is_array( $_GET['max_price'] ) ? wc_format_decimal( (string) wp_unslash( $_GET['max_price'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>

<div class="facets-container scroll-trigger animate--fade-in">
	<?php if ( $enable_filtering || $enable_sorting ) : ?>
		<facet-filters-form class="facets small-hide">
			<form id="FacetFiltersForm" class="facets__form" method="get" action="<?php echo esc_url( $base_url ); ?>">
				<input type="hidden" name="s" value="<?php echo esc_attr( $search_term ); ?>">

				<?php if ( $enable_filtering ) : ?>
					<div id="FacetsWrapperDesktop" class="facets__wrapper">
					<h2 class="facets__heading caption-large text-body" id="verticalTitle" tabindex="-1">
						<?php esc_html_e( 'Filter:', 'woocommerce' ); ?>
					</h2>

						<?php
						// ── Dropdown : catégories ─────────────────────────
						if ( ! empty( $category_filters ) ) :
							?>
							<details
								id="Details-category-1"
								class="disclosure-has-popup facets__disclosure js-filter"
								data-index="1"
							>
								<summary
									class="facets__summary caption-large focus-offset"
									aria-label="<?php esc_attr_e( 'Category', 'woocommerce' ); ?> (<?php echo esc_attr( count( $active_pills ) ); ?>)"
								>
									<div>
										<span class="facets__summary-label">
											<?php esc_html_e( 'Category', 'woocommerce' ); ?>
										</span>
										<span class="svg-wrapper"><?php panstellar_icon( 'caret' ); ?></span>
									</div>
								</summary>
								<div id="Facet-1-search" class="parent-display facets__display">
									<div class="facets__header">
										<div>
											<span class="facets__selected">
												<?php esc_html_e( 'Selected', 'woocommerce' ); ?>
											</span>
										</div>
										<facet-remove>
											<a href="<?php echo esc_url( remove_query_arg( 'filter_product_cat', $base_url ) ); ?>" class="facets__reset link underlined-link">
												<?php esc_html_e( 'Reset', 'woocommerce' ); ?>
											</a>
										</facet-remove>
									</div>
									<fieldset class="facets-wrap parent-wrap">
										<legend class="visually-hidden"><?php esc_html_e( 'Category', 'woocommerce' ); ?></legend>
										<ul class="facets-layout facets-layout-list facets-layout-list--text facets__list list-unstyled" role="list">
											<?php foreach ( $category_filters as $i => $cat ) : ?>
												<?php $input_id = 'Filter-search-category-' . ( $i + 1 ); ?>
												<li class="list-menu__item facets__item">
													<label for="<?php echo esc_attr( $input_id ); ?>" class="facets__label facet-checkbox<?php echo $cat['active'] ? ' active' : ''; ?>">
														<input
															type="checkbox"
															name="filter_product_cat"
															value="<?php echo esc_attr( $cat['slug'] ); ?>"
															id="<?php echo esc_attr( $input_id ); ?>"
															<?php checked( $cat['active'] ); ?>
														>
														<?php panstellar_icon( 'square' ); ?>
														<div class="svg-wrapper"><?php panstellar_icon( 'checkmark' ); ?></div>
														<span class="facet-checkbox__text" aria-hidden="true">
															<span class="facet-checkbox__text-label"><?php echo esc_html( $cat['label'] ); ?></span> (<?php echo esc_html( $cat['count'] ); ?>)
														</span>
														<span class="visually-hidden">
															<?php echo esc_html( $cat['label'] ); ?> (<?php echo esc_html( $cat['count'] ); ?>)
														</span>
													</label>
												</li>
											<?php endforeach; ?>
										</ul>
									</fieldset>
								</div>
							</details>
						<?php endif; ?>

						<?php
						// ── Dropdown : prix ──────────────────────────────
						?>
						<details
							id="Details-price-2"
							class="disclosure-has-popup facets__disclosure js-filter"
							data-index="2"
						>
							<summary class="facets__summary caption-large focus-offset">
								<div>
									<span><?php esc_html_e( 'Price', 'woocommerce' ); ?></span>
									<span class="svg-wrapper"><?php panstellar_icon( 'caret' ); ?></span>
								</div>
							</summary>
							<div id="Facet-2-search" class="facets__display">
								<div class="facets__header">
									<span class="facets__selected">
										<?php
										printf(
											/* translators: %s: prix maximum. */
											esc_html__( 'The highest price is %s', 'woocommerce' ),
											wp_kses_post( wc_price( $max_price ) )
										);
										?>
									</span>
									<facet-remove>
										<a href="<?php echo esc_url( remove_query_arg( array( 'min_price', 'max_price' ), $base_url ) ); ?>" class="facets__reset link underlined-link">
											<?php esc_html_e( 'Reset', 'woocommerce' ); ?>
										</a>
									</facet-remove>
								</div>
								<price-range class="facets__price">
									<div class="field">
										<input
											class="field__input"
											name="min_price"
											id="Filter-search-Price-GTE"
											type="number"
											placeholder="0"
											min="0"
											max="<?php echo esc_attr( $max_price ); ?>"
											value="<?php echo esc_attr( $min_price_active ); ?>"
										>
										<label class="field__label" for="Filter-search-Price-GTE"><?php esc_html_e( 'From', 'woocommerce' ); ?></label>
									</div>
									<div class="field">
										<input
											class="field__input"
											name="max_price"
											id="Filter-search-Price-LTE"
											type="number"
											placeholder="<?php echo esc_attr( ceil( $max_price ) ); ?>"
											min="0"
											max="<?php echo esc_attr( ceil( $max_price ) ); ?>"
											value="<?php echo esc_attr( $max_price_active ); ?>"
										>
										<label class="field__label" for="Filter-search-Price-LTE"><?php esc_html_e( 'To', 'woocommerce' ); ?></label>
									</div>
								</price-range>
							</div>
						</details>
					</div>
				<?php endif; ?>

				<div class="active-facets active-facets-desktop">
					<?php foreach ( $active_pills as $pill ) : ?>
						<facet-remove>
							<a href="<?php echo esc_url( $pill['url'] ); ?>" class="active-facets__button active-facets__button--light">
								<span class="active-facets__button-inner button button--tertiary">
									<?php echo esc_html( $pill['label'] ); ?>
									<span class="svg-wrapper"><?php panstellar_icon( 'close-small' ); ?></span>
									<span class="visually-hidden"><?php esc_html_e( 'Clear filter', 'woocommerce' ); ?></span>
								</span>
							</a>
						</facet-remove>
					<?php endforeach; ?>
					<facet-remove class="active-facets__button-wrapper">
						<a href="<?php echo esc_url( $base_url ); ?>" class="active-facets__button-remove underlined-link">
							<span><?php esc_html_e( 'Clear all', 'woocommerce' ); ?></span>
						</a>
					</facet-remove>
				</div>

				<?php if ( $enable_sorting ) : ?>
					<div class="facet-filters sorting caption">
						<div class="facet-filters__field">
							<h2 class="facet-filters__label caption-large text-body">
								<label for="SortBy"><?php esc_html_e( 'Sort by:', 'woocommerce' ); ?></label>
							</h2>
							<div class="select">
								<select
									name="orderby"
									class="facet-filters__sort select__select caption-large"
									id="SortBy"
									aria-describedby="a11y-refresh-page-message"
								>
									<?php foreach ( $sort_options as $value => $label ) : ?>
										<option
											value="<?php echo esc_attr( $value ); ?>"
											<?php selected( $current_sort, $value ); ?>
										>
											<?php echo esc_html( $label ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<span class="svg-wrapper"><?php panstellar_icon( 'caret' ); ?></span>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<div class="product-count light" role="status">
					<h2 class="product-count__text text-body">
						<span id="ProductCountDesktop">
							<?php
							printf(
								/* translators: %1$s: terme recherché, %2$s: nombre de résultats. */
								esc_html__( '%2$s results for “%1$s”', 'panstellar' ),
								esc_html( $search_term ),
								esc_html( number_format_i18n( $found_posts ) )
							);
							?>
						</span>
					</h2>
					<?php get_template_part( 'template-parts/loading-spinner' ); ?>
				</div>

				<?php foreach ( $_GET as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					if ( 's' === $key || 'filter_product_cat' === $key || 'min_price' === $key || 'max_price' === $key || 'orderby' === $key || 'paged' === $key ) {
						continue;
					}
					if ( is_array( $value ) ) {
						continue;
					}
					?>
					<input type="hidden" name="<?php echo esc_attr( sanitize_key( $key ) ); ?>" value="<?php echo esc_attr( (string) $value ); ?>">
				<?php } ?>
			</form>
		</facet-filters-form>
	<?php endif; ?>

	<?php
	// ── Drawer mobile (menu-drawer Dawn) ─────────────────────────────
	?>
	<menu-drawer
		class="mobile-facets__wrapper medium-hide large-up-hide"
		data-breakpoint="mobile"
	>
		<details class="mobile-facets__disclosure disclosure-has-popup">
			<summary class="mobile-facets__open-wrapper focus-offset">
				<span class="mobile-facets__open">
					<span class="svg-wrapper"><?php panstellar_icon( 'filter' ); ?></span>
					<span class="mobile-facets__open-label button-label medium-hide large-up-hide">
						<?php
						if ( $enable_filtering && $enable_sorting ) {
							esc_html_e( 'Filter and sort', 'woocommerce' );
						} elseif ( $enable_filtering ) {
							esc_html_e( 'Filter', 'woocommerce' );
						} else {
							esc_html_e( 'Sort', 'woocommerce' );
						}
						?>
					</span>
					<span class="mobile-facets__open-label button-label small-hide">
						<?php esc_html_e( 'Filter', 'woocommerce' ); ?>
					</span>
				</span>
				<span tabindex="0" class="mobile-facets__close">
					<span class="svg-wrapper"><?php panstellar_icon( 'close' ); ?></span>
				</span>
			</summary>
			<facet-filters-form>
				<form id="FacetFiltersFormMobile" class="mobile-facets" method="get" action="<?php echo esc_url( $base_url ); ?>">
					<input type="hidden" name="s" value="<?php echo esc_attr( $search_term ); ?>">
					<div class="mobile-facets__inner gradient">
						<div class="mobile-facets__header">
							<div class="mobile-facets__header-inner">
								<h2 class="mobile-facets__heading medium-hide large-up-hide">
									<?php esc_html_e( 'Filter and sort', 'woocommerce' ); ?>
								</h2>
								<h2 class="mobile-facets__heading small-hide">
									<?php esc_html_e( 'Filter', 'woocommerce' ); ?>
								</h2>
								<p class="mobile-facets__count">
									<?php
									printf(
										/* translators: %1$s: terme recherché, %2$s: nombre de résultats. */
										esc_html__( '%2$s results for “%1$s”', 'panstellar' ),
										esc_html( $search_term ),
										esc_html( number_format_i18n( $found_posts ) )
									);
									?>
								</p>
							</div>
						</div>
						<div id="FacetsWrapperMobile" class="mobile-facets__main has-submenu gradient">
							<?php if ( $enable_filtering && ! empty( $category_filters ) ) : ?>
								<details
									id="Details-Mobile-category-1-search"
									class="mobile-facets__details js-filter"
									data-index="mobile-1"
								>
									<summary class="mobile-facets__summary focus-inset">
										<div>
											<span><?php esc_html_e( 'Category', 'woocommerce' ); ?></span>
											<span class="mobile-facets__arrow"><?php panstellar_icon( 'arrow' ); ?></span>
										</div>
									</summary>
									<div id="FacetMobile-1-search" class="mobile-facets__submenu gradient">
										<button
											class="mobile-facets__close-button link link--text focus-inset"
											aria-expanded="true"
											type="button"
										>
											<?php panstellar_icon( 'arrow' ); ?>
											<span><?php esc_html_e( 'Category', 'woocommerce' ); ?></span>
										</button>
										<ul class="facets-layout facets-layout-list facets-layout-list--text mobile-facets__list list-unstyled" role="list">
											<?php foreach ( $category_filters as $i => $cat ) : ?>
												<li class="mobile-facets__item list-menu__item">
													<label for="Filter-search-category-mobile-<?php echo esc_attr( $i + 1 ); ?>" class="facets__label mobile-facets__label<?php echo $cat['active'] ? ' active' : ''; ?>">
														<input
															class="mobile-facets__checkbox"
															type="checkbox"
															name="filter_product_cat"
															value="<?php echo esc_attr( $cat['slug'] ); ?>"
															id="Filter-search-category-mobile-<?php echo esc_attr( $i + 1 ); ?>"
															<?php checked( $cat['active'] ); ?>
														>
														<span class="mobile-facets__highlight"></span>
														<?php panstellar_icon( 'square' ); ?>
														<span class="svg-wrapper"><?php panstellar_icon( 'checkmark' ); ?></span>
														<span class="facet-checkbox__text" aria-hidden="true">
															<span class="facet-checkbox__text-label"><?php echo esc_html( $cat['label'] ); ?></span> (<?php echo esc_html( $cat['count'] ); ?>)
														</span>
													</label>
												</li>
											<?php endforeach; ?>
										</ul>
										<div class="mobile-facets__footer gradient">
											<facet-remove class="mobile-facets__clear-wrapper">
												<a href="<?php echo esc_url( $base_url ); ?>" class="mobile-facets__clear underlined-link">
													<?php esc_html_e( 'Clear', 'woocommerce' ); ?>
												</a>
											</facet-remove>
											<button type="button" class="button button--primary" data-mobile-facets-apply>
												<?php esc_html_e( 'Apply', 'woocommerce' ); ?>
											</button>
										</div>
									</div>
								</details>
							<?php endif; ?>

							<?php if ( $enable_filtering ) : ?>
								<details
									id="Details-Mobile-price-2-search"
									class="mobile-facets__details js-filter"
									data-index="mobile-2"
								>
									<summary class="mobile-facets__summary focus-inset">
										<div>
											<span><?php esc_html_e( 'Price', 'woocommerce' ); ?></span>
											<span class="mobile-facets__arrow"><?php panstellar_icon( 'arrow' ); ?></span>
										</div>
									</summary>
									<div id="FacetMobile-2-search" class="mobile-facets__submenu gradient">
										<button
											class="mobile-facets__close-button link link--text focus-inset"
											aria-expanded="true"
											type="button"
										>
											<?php panstellar_icon( 'arrow' ); ?>
											<?php esc_html_e( 'Price', 'woocommerce' ); ?>
										</button>
										<p class="mobile-facets__info">
											<?php
											printf(
												/* translators: %s: prix maximum. */
												esc_html__( 'The highest price is %s', 'woocommerce' ),
												wp_kses_post( wc_price( $max_price ) )
											);
											?>
										</p>
										<price-range class="facets__price">
											<div class="field">
												<input
													class="field__input"
													name="min_price"
													id="Mobile-Filter-search-Price-GTE"
													type="number"
													placeholder="0"
													min="0"
													max="<?php echo esc_attr( $max_price ); ?>"
													value="<?php echo esc_attr( $min_price_active ); ?>"
												>
												<label class="field__label" for="Mobile-Filter-search-Price-GTE"><?php esc_html_e( 'From', 'woocommerce' ); ?></label>
											</div>
											<div class="field">
												<input
													class="field__input"
													name="max_price"
													id="Mobile-Filter-search-Price-LTE"
													type="number"
													placeholder="<?php echo esc_attr( ceil( $max_price ) ); ?>"
													min="0"
													max="<?php echo esc_attr( ceil( $max_price ) ); ?>"
													value="<?php echo esc_attr( $max_price_active ); ?>"
												>
												<label class="field__label" for="Mobile-Filter-search-Price-LTE"><?php esc_html_e( 'To', 'woocommerce' ); ?></label>
											</div>
										</price-range>
										<div class="mobile-facets__footer">
											<facet-remove class="mobile-facets__clear-wrapper">
												<a href="<?php echo esc_url( $base_url ); ?>" class="mobile-facets__clear underlined-link">
													<?php esc_html_e( 'Clear', 'woocommerce' ); ?>
												</a>
											</facet-remove>
											<button type="button" class="button button--primary" data-mobile-facets-apply>
												<?php esc_html_e( 'Apply', 'woocommerce' ); ?>
											</button>
										</div>
									</div>
								</details>
							<?php endif; ?>

							<?php if ( $enable_sorting ) : ?>
								<div id="Details-Mobile-SortBy-search" class="mobile-facets__details js-filter" data-index="mobile-3">
									<div class="mobile-facets__summary">
										<div class="mobile-facets__sort">
											<label for="SortBy-mobile"><?php esc_html_e( 'Sort by:', 'woocommerce' ); ?></label>
											<div class="select">
												<select name="orderby" class="select__select" id="SortBy-mobile" aria-describedby="a11y-refresh-page-message">
													<?php foreach ( $sort_options as $value => $label ) : ?>
														<option
															value="<?php echo esc_attr( $value ); ?>"
															<?php selected( $current_sort, $value ); ?>
														>
															<?php echo esc_html( $label ); ?>
														</option>
													<?php endforeach; ?>
												</select>
												<span class="svg-wrapper"><?php panstellar_icon( 'caret' ); ?></span>
											</div>
										</div>
									</div>
								</div>
							<?php endif; ?>

							<div class="mobile-facets__footer">
								<facet-remove class="mobile-facets__clear-wrapper">
									<a href="<?php echo esc_url( $base_url ); ?>" class="mobile-facets__clear underlined-link">
										<?php esc_html_e( 'Clear all', 'woocommerce' ); ?>
									</a>
								</facet-remove>
								<button type="button" class="button button--primary" data-mobile-facets-apply>
									<?php esc_html_e( 'Apply', 'woocommerce' ); ?>
								</button>
							</div>
						</div>

						<?php foreach ( $_GET as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
							if ( 's' === $key || 'filter_product_cat' === $key || 'min_price' === $key || 'max_price' === $key || 'orderby' === $key || 'paged' === $key ) {
								continue;
							}
							if ( is_array( $value ) ) {
								continue;
							}
							?>
							<input type="hidden" name="<?php echo esc_attr( sanitize_key( $key ) ); ?>" value="<?php echo esc_attr( (string) $value ); ?>">
						<?php } ?>
					</div>
				</form>
			</facet-filters-form>
		</details>
	</menu-drawer>

	<div class="active-facets active-facets-mobile medium-hide large-up-hide">
		<?php foreach ( $active_pills as $pill ) : ?>
			<facet-remove>
				<a href="<?php echo esc_url( $pill['url'] ); ?>" class="active-facets__button active-facets__button--light">
					<span class="active-facets__button-inner button button--tertiary">
						<?php echo esc_html( $pill['label'] ); ?>
						<span class="svg-wrapper"><?php panstellar_icon( 'close-small' ); ?></span>
						<span class="visually-hidden"><?php esc_html_e( 'Clear filter', 'woocommerce' ); ?></span>
					</span>
				</a>
			</facet-remove>
		<?php endforeach; ?>
		<facet-remove class="active-facets__button-wrapper">
			<a href="<?php echo esc_url( $base_url ); ?>" class="active-facets__button-remove underlined-link">
				<span><?php esc_html_e( 'Clear all', 'woocommerce' ); ?></span>
			</a>
		</facet-remove>
	</div>
</div>
