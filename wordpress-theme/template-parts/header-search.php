<?php
/**
 * Modale de recherche — template-parts/header-search.php
 *
 * Conversion de snippets/header-search.liquid.
 * Le champ de recherche utilise name="s" (recherche WordPress/WooCommerce)
 * à la place de name="q" (Shopify).
 *
 * Usage :
 *   get_template_part( 'template-parts/header-search', null, array( 'input_id' => 'Search-In-Modal' ) );
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$input_id = isset( $args['input_id'] ) ? $args['input_id'] : 'Search-In-Modal';

// Recherche prédictive : désactivée par défaut (nécessiterait un endpoint AJAX
// WordPress. À activer via add_filter('panstellar_theme_setting_predictive_search_enabled', '__return_true')).
$predictive_search_enabled = (bool) panstellar_theme_setting( 'predictive_search_enabled' );

$search_query = get_search_query();
?>
<details-modal class="header__search">
	<details>
		<summary
			class="header__icon header__icon--search header__icon--summary link focus-inset modal__toggle"
			aria-haspopup="dialog"
			aria-label="<?php esc_attr_e( 'Search', 'panstellar' ); ?>"
		>
			<span>
				<span class="svg-wrapper">
					<?php panstellar_icon( 'search' ); ?>
				</span>
				<span class="svg-wrapper header__icon-close">
					<?php panstellar_icon( 'close' ); ?>
				</span>
			</span>
		</summary>
		<div
			class="search-modal modal__content gradient"
			role="dialog"
			aria-modal="true"
			aria-label="<?php esc_attr_e( 'Search', 'panstellar' ); ?>"
		>
			<div class="modal-overlay"></div>
			<div class="search-modal__content search-modal__content-bottom" tabindex="-1">
				<?php if ( $predictive_search_enabled ) : ?>
					<predictive-search class="search-modal__form" data-loading-text="<?php esc_attr_e( 'Loading…', 'panstellar' ); ?>">
				<?php else : ?>
					<search-form class="search-modal__form">
				<?php endif; ?>
				<form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" role="search" class="search search-modal__form">
					<div class="field">
						<input
							class="search__input field__input"
							id="<?php echo esc_attr( $input_id ); ?>"
							type="search"
							name="s"
							value="<?php echo esc_attr( $search_query ); ?>"
							placeholder="<?php esc_attr_e( 'Search', 'panstellar' ); ?>"
							<?php if ( $predictive_search_enabled ) : ?>
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
							<?php endif; ?>
						>
						<label class="field__label" for="<?php echo esc_attr( $input_id ); ?>"><?php esc_html_e( 'Search', 'panstellar' ); ?></label>
						<button
							type="reset"
							class="reset__button field__button<?php echo '' === $search_query ? ' hidden' : ''; ?>"
							aria-label="<?php esc_attr_e( 'Clear search term', 'panstellar' ); ?>"
						>
							<span class="svg-wrapper">
								<?php panstellar_icon( 'reset' ); ?>
							</span>
						</button>
						<button class="search__button field__button" aria-label="<?php esc_attr_e( 'Search', 'panstellar' ); ?>">
							<span class="svg-wrapper">
								<?php panstellar_icon( 'search' ); ?>
							</span>
						</button>
					</div>

					<?php if ( $predictive_search_enabled ) : ?>
						<div class="predictive-search predictive-search--header" tabindex="-1" data-predictive-search>
							<?php get_template_part( 'template-parts/loading-spinner', null, array( 'class' => 'predictive-search__loading-state' ) ); ?>
						</div>

						<span class="predictive-search-status visually-hidden" role="status" aria-hidden="true"></span>
					<?php endif; ?>
				</form>
				<?php if ( $predictive_search_enabled ) : ?>
					</predictive-search>
				<?php else : ?>
					</search-form>
				<?php endif; ?>
				<button
					type="button"
					class="search-modal__close-button modal__close-button link link--text focus-inset"
					aria-label="<?php esc_attr_e( 'Close', 'panstellar' ); ?>"
				>
					<span class="svg-wrapper">
						<?php panstellar_icon( 'close' ); ?>
					</span>
				</button>
			</div>
		</div>
	</details>
</details-modal>
