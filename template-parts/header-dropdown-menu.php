<?php
/**
 * Menu dropdown desktop — template-parts/header-dropdown-menu.php
 *
 * Conversion de snippets/header-dropdown-menu.liquid.
 * Structure <details>/<summary> conservée pour le JS Dawn (HeaderMenu).
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$menu_location     = panstellar_header_setting( 'menu', 'primary' );
$menu_color_scheme = panstellar_header_setting( 'menu_color_scheme' );

if ( ! has_nav_menu( $menu_location ) ) {
	return;
}
?>
<nav class="header__inline-menu">
	<?php
	wp_nav_menu(
		array(
			'theme_location'    => $menu_location,
			'menu_class'        => 'list-menu list-menu--inline',
			'container'         => false,
			'items_wrap'        => '<ul id="%1$s" class="%2$s" role="list">%3$s</ul>',
			'walker'            => new Panstellar_Dropdown_Walker(),
			'menu_color_scheme' => $menu_color_scheme,
		)
	);
	?>
</nav>
