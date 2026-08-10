<?php
/**
 * Formulaire de recherche — searchform.php
 *
 * Formulaire Dawn (même markup que template-parts/header-search.php) utilisé
 * par get_search_form() — notamment sur la page 404.
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$search_query = get_search_query();
?>

<form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" role="search" class="search search-modal__form">
	<div class="field">
		<input
			class="search__input field__input"
			id="Search-In-Template"
			type="search"
			name="s"
			value="<?php echo esc_attr( $search_query ); ?>"
			placeholder="<?php esc_attr_e( 'Search', 'panstellar' ); ?>"
		>
		<label class="field__label" for="Search-In-Template">
			<?php esc_html_e( 'Search', 'panstellar' ); ?>
		</label>
		<input type="hidden" name="options[prefix]" value="last">
		<button type="reset" class="reset__button field__button" aria-label="<?php esc_attr_e( 'Clear search term', 'panstellar' ); ?>">
			<span class="svg-wrapper"><?php panstellar_icon( 'close' ); ?></span>
		</button>
		<button class="search__button field__button" aria-label="<?php esc_attr_e( 'Search', 'panstellar' ); ?>">
			<span class="svg-wrapper"><?php panstellar_icon( 'search' ); ?></span>
		</button>
	</div>
</form>
