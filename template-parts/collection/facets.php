<?php
/**
 * Facets (filtres + tri) — template-parts/collection/facets.php
 *
 * Conversion de snippets/facets.liquid (Dawn 15) pour le template
 * collection.json : filter_type = vertical, enable_filtering = true,
 * enable_sorting = true.
 *
 * Rendu de l'aside (tri + filtres) uniquement : la grille produit et la
 * pagination sont rendues par archive-product.php (comme dans Dawn, où
 * main-collection-product-grid.liquid assemble le tout).
 *
 * Les paramètres GET utilisent les conventions WooCommerce natives :
 *   - filter_product_cat=<slug> (filtres par catégorie, format layered nav)
 *   - min_price / max_price (filtre prix)
 *   - orderby (tri : menu_order, popularity, title, title-desc, price, price-desc, date)
 * Ces paramètres sont appliqués nativement par WC_Query sur archive-product.
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$enable_filtering = ! empty( $args['enable_filtering'] );
$enable_sorting   = ! empty( $args['enable_sorting'] );
$filter_type      = isset( $args['filter_type'] ) ? $args['filter_type'] : 'vertical';

if ( ! $enable_filtering && ! $enable_sorting ) {
	return;
}

// ── Données partagées (calculées une fois, avant tout bloc conditionnel) ──
$found_posts = isset( $GLOBALS['wp_query']->found_posts ) ? (int) $GLOBALS['wp_query']->found_posts : 0;

// URL de base des filtres : page courante (boutique ou taxonomie produit),
// sans filtres/tri/pagination. REQUEST_URI peut être absent (CLI).
$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
$base_url    = remove_query_arg( array( 'orderby', 'min_price', 'max_price', 'paged' ), home_url( $request_uri ) );
foreach ( array_keys( $_GET ) as $get_key ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( strpos( (string) $get_key, 'filter_' ) === 0 ) {
		$base_url = remove_query_arg( $get_key, $base_url );
	}
}
$base_url = apply_filters( 'panstellar_facets_base_url', $base_url );

// ── Tri : valeurs Dawn → valeurs natives WooCommerce ─────────────
$sort_options = array(
	'menu_order'  => __( 'Featured', 'woocommerce' ),
	'popularity'  => __( 'Best selling', 'woocommerce' ),
	'title'       => __( 'Alphabetically, A-Z', 'woocommerce' ),
	'title-desc'  => __( 'Alphabetically, Z-A', 'woocommerce' ),
	'price'       => __( 'Price, low to high', 'woocommerce' ),
	'price-desc'  => __( 'Price, high to low', 'woocommerce' ),
	'date'        => __( 'Date, new to old', 'woocommerce' ),
);
$current_sort = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'menu_order'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( ! isset( $sort_options[ $current_sort ] ) ) {
	$current_sort = 'menu_order';
}

// ── Filtres actifs (pills) ───────────────────────────────────────
$active_pills = array(); // array( 'label' => ..., 'url' => ... )

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

$active_pills = apply_filters( 'panstellar_facets_active_pills', $active_pills, $base_url );

// ── Groupes de filtres ───────────────────────────────────────────
// Catégories : uniquement sur la page boutique (les pages catégorie
// sont déjà filtrées par le contexte).
$category_filters = array();
if ( is_shop() && taxonomy_exists( 'product_cat' ) ) {
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
$category_filters = apply_filters( 'panstellar_facets_categories', $category_filters );

// Prix max (pour le filtre prix).
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

$show_more_number = 10;
?>

<div class="facets-container scroll-trigger animate--fade-in">
	<?php
	// ── Barre de tri verticale (desktop) ─────────────────────────────
	if ( $enable_sorting ) :
		?>
		<facet-filters-form class="facets facets-vertical-sort page-width small-hide">
			<form class="facets-vertical-form" id="FacetSortForm" method="get" action="<?php echo esc_url( $base_url ); ?>">
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

				<?php
				// Conserver les filtres actifs lors du tri.
				foreach ( $_GET as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					if ( 'orderby' === $key || 'paged' === $key ) {
						continue;
					}
					if ( is_array( $value ) ) {
						continue;
					}
					?>
					<input type="hidden" name="<?php echo esc_attr( sanitize_key( $key ) ); ?>" value="<?php echo esc_attr( (string) $value ); ?>">
					<?php
				}
				?>

				<div class="product-count-vertical light" role="status">
					<h2 class="product-count__text text-body">
						<span id="ProductCountDesktop">
							<?php
							printf(
								/* translators: %s: nombre de produits. */
								esc_html( _n( '%s product', '%s products', $found_posts, 'panstellar' ) ),
								esc_html( number_format_i18n( $found_posts ) )
							);
							?>
						</span>
					</h2>
					<?php get_template_part( 'template-parts/loading-spinner' ); ?>
				</div>
			</form>
		</facet-filters-form>
	<?php endif; ?>

	<div class="facets-vertical page-width">
		<?php if ( $enable_filtering ) : ?>
			<aside
				aria-labelledby="verticalTitle"
				class="facets-wrapper"
				id="main-collection-filters"
				data-id="product-grid"
			>
				<facet-filters-form class="facets small-hide">
					<form id="FacetFiltersForm" class="facets__form-vertical" method="get" action="<?php echo esc_url( $base_url ); ?>">
						<div id="FacetsWrapperDesktop">
							<div class="active-facets active-facets-desktop">
								<div class="active-facets-vertical-filter">
									<?php if ( ! empty( $category_filters ) ) : ?>
										<h2
											class="facets__heading facets__heading--vertical caption-large text-body"
											id="verticalTitle"
											tabindex="-1"
										>
											<?php esc_html_e( 'Filter:', 'woocommerce' ); ?>
										</h2>
									<?php endif; ?>
									<facet-remove class="active-facets__button-wrapper">
										<a href="<?php echo esc_url( $base_url ); ?>" class="active-facets__button-remove underlined-link">
											<span><?php esc_html_e( 'Clear all', 'woocommerce' ); ?></span>
										</a>
									</facet-remove>
								</div>
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
							</div>

							<?php
							// ── Groupe : catégories ────────────────────────
							if ( ! empty( $category_filters ) ) :
								?>
								<details
									id="Details-category-1"
									class="facets__disclosure-vertical js-filter"
									data-index="1"
									open
								>
									<summary class="facets__summary caption-large focus-offset" aria-label="<?php esc_attr_e( 'Category', 'woocommerce' ); ?> (<?php echo esc_attr( count( $active_pills ) ); ?>)">
										<div>
											<span class="facets__summary-label">
												<?php esc_html_e( 'Category', 'woocommerce' ); ?>
											</span>
											<span class="svg-wrapper"><?php panstellar_icon( 'caret' ); ?></span>
										</div>
									</summary>
									<div id="Facet-1-product-grid" class="parent-display facets__display-vertical">
										<fieldset class="facets-wrap parent-wrap facets-wrap-vertical">
											<legend class="visually-hidden"><?php esc_html_e( 'Category', 'woocommerce' ); ?></legend>
											<ul class="facets-layout facets-layout-list facets-layout-list--text facets__list--vertical list-unstyled" role="list">
												<?php foreach ( $category_filters as $i => $cat ) : ?>
													<?php
													$input_id = 'Filter-category-' . ( $i + 1 );
													$current  = isset( $_GET['filter_product_cat'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_GET['filter_product_cat'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
													?>
													<li class="list-menu__item facets__item<?php echo $i >= $show_more_number ? ' show-more-item hidden' : ''; ?>">
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
										<?php if ( count( $category_filters ) > $show_more_number ) : ?>
											<show-more-button>
												<button class="button-show-more link underlined-link" id="Show-More-1-product-grid" type="button">
													<span class="label-show-more label-text"><span aria-hidden="true">+ </span><?php esc_html_e( 'Show more', 'woocommerce' ); ?></span>
													<span class="label-show-less label-text hidden"><span aria-hidden="true">- </span><?php esc_html_e( 'Show less', 'woocommerce' ); ?></span>
												</button>
											</show-more-button>
										<?php endif; ?>
									</div>
								</details>
							<?php endif; ?>

							<?php
							// ── Groupe : prix ──────────────────────────────
							?>
							<details
								id="Details-price-2"
								class="facets__disclosure-vertical js-filter"
								data-index="2"
							>
								<summary class="facets__summary caption-large focus-offset">
									<div>
										<span><?php esc_html_e( 'Price', 'woocommerce' ); ?></span>
										<span class="svg-wrapper"><?php panstellar_icon( 'caret' ); ?></span>
									</div>
								</summary>
								<div id="Facet-2-product-grid" class="facets__display-vertical">
									<div class="facets__header-vertical">
										<span class="facets__selected">
											<?php
											printf(
												/* translators: %s: prix maximum. */
												esc_html__( 'The highest price is %s', 'woocommerce' ),
												wp_kses_post( wc_price( $max_price ) )
											);
											?>
										</span>
									</div>
									<price-range class="facets__price">
										<div class="field">
											<input
												class="field__input"
												name="min_price"
												id="Filter-Price-GTE"
												type="number"
												placeholder="0"
												min="0"
												max="<?php echo esc_attr( $max_price ); ?>"
												value="<?php echo esc_attr( $min_price_active ); ?>"
											>
											<label class="field__label" for="Filter-Price-GTE"><?php esc_html_e( 'From', 'woocommerce' ); ?></label>
										</div>
										<div class="field">
											<input
												class="field__input"
												name="max_price"
												id="Filter-Price-LTE"
												type="number"
												placeholder="<?php echo esc_attr( ceil( $max_price ) ); ?>"
												min="0"
												max="<?php echo esc_attr( ceil( $max_price ) ); ?>"
												value="<?php echo esc_attr( $max_price_active ); ?>"
											>
											<label class="field__label" for="Filter-Price-LTE"><?php esc_html_e( 'To', 'woocommerce' ); ?></label>
										</div>
									</price-range>
								</div>
							</details>
						</div>

						<?php foreach ( $_GET as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
							if ( 'filter_product_cat' === $key || 'min_price' === $key || 'max_price' === $key || 'orderby' === $key || 'paged' === $key ) {
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

				<?php
				// ── Drawer mobile (menu-drawer Dawn) ──────────────────────
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
													/* translators: %s: nombre de produits. */
													esc_html( _n( '%s product', '%s products', $found_posts, 'panstellar' ) ),
													esc_html( number_format_i18n( $found_posts ) )
												);
												?>
											</p>
										</div>
									</div>
									<div id="FacetsWrapperMobile" class="mobile-facets__main has-submenu gradient">
										<?php if ( $enable_filtering && ! empty( $category_filters ) ) : ?>
											<details
												id="Details-Mobile-category-1-product-grid"
												class="mobile-facets__details js-filter"
												data-index="mobile-1"
											>
												<summary class="mobile-facets__summary focus-inset">
													<div>
														<span><?php esc_html_e( 'Category', 'woocommerce' ); ?></span>
														<span class="mobile-facets__arrow"><?php panstellar_icon( 'arrow' ); ?></span>
													</div>
												</summary>
												<div id="FacetMobile-1-product-grid" class="mobile-facets__submenu gradient">
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
																<label for="Filter-category-mobile-<?php echo esc_attr( $i + 1 ); ?>" class="facets__label mobile-facets__label<?php echo $cat['active'] ? ' active' : ''; ?>">
																	<input
																		class="mobile-facets__checkbox"
																		type="checkbox"
																		name="filter_product_cat"
																		value="<?php echo esc_attr( $cat['slug'] ); ?>"
																		id="Filter-category-mobile-<?php echo esc_attr( $i + 1 ); ?>"
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
												id="Details-Mobile-price-2-product-grid"
												class="mobile-facets__details js-filter"
												data-index="mobile-2"
											>
												<summary class="mobile-facets__summary focus-inset">
													<div>
														<span><?php esc_html_e( 'Price', 'woocommerce' ); ?></span>
														<span class="mobile-facets__arrow"><?php panstellar_icon( 'arrow' ); ?></span>
													</div>
												</summary>
												<div id="FacetMobile-2-product-grid" class="mobile-facets__submenu gradient">
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
																id="Mobile-Filter-Price-GTE"
																type="number"
																placeholder="0"
																min="0"
																max="<?php echo esc_attr( $max_price ); ?>"
																value="<?php echo esc_attr( $min_price_active ); ?>"
															>
															<label class="field__label" for="Mobile-Filter-Price-GTE"><?php esc_html_e( 'From', 'woocommerce' ); ?></label>
														</div>
														<div class="field">
															<input
																class="field__input"
																name="max_price"
																id="Mobile-Filter-Price-LTE"
																type="number"
																placeholder="<?php echo esc_attr( ceil( $max_price ) ); ?>"
																min="0"
																max="<?php echo esc_attr( ceil( $max_price ) ); ?>"
																value="<?php echo esc_attr( $max_price_active ); ?>"
															>
															<label class="field__label" for="Mobile-Filter-Price-LTE"><?php esc_html_e( 'To', 'woocommerce' ); ?></label>
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
											<div id="Details-Mobile-SortBy-product-grid" class="mobile-facets__details js-filter" data-index="mobile-3">
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
										if ( 'filter_product_cat' === $key || 'min_price' === $key || 'max_price' === $key || 'orderby' === $key || 'paged' === $key ) {
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
			</aside>
		<?php endif; ?>
	</div>
</div>
