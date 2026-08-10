<?php
/**
 * Walkers de menu du header Panstellar.
 *
 * Reproduisent fidèlement le HTML des snippets Shopify Dawn :
 *   - snippets/header-dropdown-menu.liquid  → Panstellar_Dropdown_Walker
 *   - snippets/header-mega-menu.liquid      → Panstellar_Mega_Walker
 *   - snippets/header-drawer.liquid         → Panstellar_Drawer_Walker
 *
 * Les classes CSS Dawn sont conservées à l'identique pour que les styles
 * copiés (base.css + component-*.css) fonctionnent sans modification.
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base commune : helpers de rendu partagés par les 3 walkers.
 */
abstract class Panstellar_Header_Walker extends Walker_Nav_Menu {

	/**
	 * Icône « caret » (flèche de sous-menu).
	 *
	 * @return string
	 */
	protected function caret_icon() {
		return '<span class="svg-wrapper">' . panstellar_get_icon( 'caret' ) . '</span>';
	}

	/**
	 * Icône « arrow » (retour).
	 *
	 * @return string
	 */
	protected function arrow_icon() {
		return '<span class="svg-wrapper">' . panstellar_get_icon( 'arrow' ) . '</span>';
	}

	/**
	 * L'item a-t-il des sous-items ?
	 *
	 * @param WP_Post $item Item de menu.
	 * @return bool
	 */
	protected function has_children( $item ) {
		return in_array( 'menu-item-has-children', (array) $item->classes, true );
	}

	/**
	 * L'item est-il courant (page active) ?
	 *
	 * @param WP_Post $item Item de menu.
	 * @return bool
	 */
	protected function is_current( $item ) {
		return in_array( 'current-menu-item', (array) $item->classes, true );
	}

	/**
	 * Handle unique dérivé du titre (équivalent de link.handle).
	 *
	 * @param WP_Post $item Item de menu.
	 * @return string
	 */
	protected function handle( $item ) {
		return sanitize_title( $item->title ) . '-' . $item->ID;
	}

	/**
	 * Attribut aria-current si l'item est courant.
	 *
	 * @param WP_Post $item Item de menu.
	 * @return string
	 */
	protected function aria_current( $item ) {
		return $this->is_current( $item ) ? ' aria-current="page"' : '';
	}
}

/**
 * Menu desktop « dropdown » — équivalent de snippets/header-dropdown-menu.liquid.
 */
class Panstellar_Dropdown_Walker extends Panstellar_Header_Walker {

	/**
	 * Racine du menu (ul).
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$scheme = $args->menu_color_scheme ?? 'inverse';
		$output .= '<ul id="HeaderMenu-MenuList-' . $depth . '" class="header__submenu list-menu list-menu--disclosure color-' . esc_attr( $scheme ) . ' gradient caption-large motion-reduce global-settings-popup" role="list" tabindex="-1">';
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</ul>';
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$title    = apply_filters( 'the_title', $item->title, $item->ID );
		$handle   = $this->handle( $item );
		$current  = $this->is_current( $item );
		$has_children = $this->has_children( $item );

		$output .= '<li>';

		if ( $has_children ) {
			$output .= '<header-menu>';
			$output .= '<details id="Details-HeaderMenu-' . esc_attr( $handle ) . '">';
			$output .= '<summary id="HeaderMenu-' . esc_attr( $handle ) . '" class="header__menu-item list-menu__item link focus-inset">';
			$output .= '<span' . ( $current ? ' class="header__active-menu-item"' : '' ) . '>' . esc_html( $title ) . '</span>';
			$output .= $this->caret_icon();
			$output .= '</summary>';
		} else {
			$output .= '<a id="HeaderMenu-' . esc_attr( $handle ) . '" href="' . esc_url( $item->url ) . '" class="header__menu-item list-menu__item link link--text focus-inset"' . $this->aria_current( $item ) . '>';
			$output .= '<span' . ( $current ? ' class="header__active-menu-item"' : '' ) . '>' . esc_html( $title ) . '</span>';
			$output .= '</a>';
		}
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		if ( $this->has_children( $item ) ) {
			$output .= '</details></header-menu>';
		}
		$output .= '</li>';
	}
}

/**
 * Menu desktop « mega » — équivalent de snippets/header-mega-menu.liquid.
 */
class Panstellar_Mega_Walker extends Panstellar_Header_Walker {

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$scheme = $args->menu_color_scheme ?? 'inverse';

		if ( 0 === $depth ) {
			$output .= '<div id="MegaMenu-Content" class="mega-menu__content color-' . esc_attr( $scheme ) . ' gradient motion-reduce global-settings-popup" tabindex="-1">';
			$output .= '<ul class="mega-menu__list page-width" role="list">';
		} else {
			$output .= '<ul class="list-unstyled" role="list">';
		}
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= ( 0 === $depth ) ? '</ul></div>' : '</ul>';
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$title    = apply_filters( 'the_title', $item->title, $item->ID );
		$handle   = $this->handle( $item );
		$current  = $this->is_current( $item );
		$has_children = $this->has_children( $item );

		$output .= '<li>';

		if ( $has_children && 0 === $depth ) {
			$output .= '<header-menu>';
			$output .= '<details id="Details-HeaderMenu-' . esc_attr( $handle ) . '" class="mega-menu">';
			$output .= '<summary id="HeaderMenu-' . esc_attr( $handle ) . '" class="header__menu-item list-menu__item link focus-inset">';
			$output .= '<span' . ( $current ? ' class="header__active-menu-item"' : '' ) . '>' . esc_html( $title ) . '</span>';
			$output .= $this->caret_icon();
			$output .= '</summary>';
		} elseif ( 1 === $depth ) {
			// Lien de niveau 2 dans la grille du megamenu.
			$output .= '<a id="HeaderMenu-' . esc_attr( $handle ) . '" href="' . esc_url( $item->url ) . '" class="mega-menu__link mega-menu__link--level-2 link' . ( $current ? ' mega-menu__link--active' : '' ) . '"' . $this->aria_current( $item ) . '>';
			$output .= esc_html( $title );
			$output .= '</a>';
		} else {
			// Lien de niveau 3 (sous liste).
			$output .= '<a id="HeaderMenu-' . esc_attr( $handle ) . '" href="' . esc_url( $item->url ) . '" class="mega-menu__link link' . ( $current ? ' mega-menu__link--active' : '' ) . '"' . $this->aria_current( $item ) . '>';
			$output .= esc_html( $title );
			$output .= '</a>';
		}
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		if ( $this->has_children( $item ) && 0 === $depth ) {
			$output .= '</details></header-menu>';
		}
		$output .= '</li>';
	}
}

/**
 * Menu du footer — équivalent du rendu {% for link in block.settings.menu.links %}
 * de sections/footer.liquid (simple liste de liens).
 */
class Panstellar_Footer_Walker extends Panstellar_Header_Walker {

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		// Le footer Dawn ne gère pas de sous-menus : on les ignore.
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$title   = apply_filters( 'the_title', $item->title, $item->ID );
		$current = $this->is_current( $item );

		$output .= '<li>';
		$output .= '<a href="' . esc_url( $item->url ) . '" class="link link--text list-menu__item list-menu__item--link' . ( $current ? ' list-menu__item--active' : '' ) . '"' . $this->aria_current( $item ) . '>';
		$output .= esc_html( $title );
		$output .= '</a>';
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}

/**
 * Menu mobile « drawer » — équivalent de snippets/header-drawer.liquid.
 */
class Panstellar_Drawer_Walker extends Panstellar_Header_Walker {

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$parent_title = isset( $this->current_parent_title ) ? $this->current_parent_title : '';

		if ( 0 === $depth ) {
			// Sous-menu de premier niveau : avec wrapper inner-submenu (HTML Dawn de link.links).
			$output .= '<div class="menu-drawer__submenu has-submenu gradient motion-reduce" tabindex="-1">';
			$output .= '<div class="menu-drawer__inner-submenu">';
			$output .= '<button class="menu-drawer__close-button link link--text focus-inset" aria-expanded="true">';
			$output .= $this->arrow_icon();
			$output .= esc_html( $parent_title );
			$output .= '</button>';
			$output .= '<ul class="menu-drawer__menu list-menu" role="list" tabindex="-1">';
		} else {
			// Sous-sous-menu (childlink.links) : sans inner-submenu (HTML Dawn).
			$output .= '<div class="menu-drawer__submenu has-submenu gradient motion-reduce">';
			$output .= '<button class="menu-drawer__close-button link link--text focus-inset" aria-expanded="true">';
			$output .= $this->arrow_icon();
			$output .= esc_html( $parent_title );
			$output .= '</button>';
			$output .= '<ul class="menu-drawer__menu list-menu" role="list" tabindex="-1">';
		}
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</ul>';
		if ( 0 === $depth ) {
			$output .= '</div>';
		}
		$output .= '</div>';
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$title    = apply_filters( 'the_title', $item->title, $item->ID );
		$handle   = $this->handle( $item );
		$current  = $this->is_current( $item );
		$has_children = $this->has_children( $item );

		$this->current_parent_title = $title;

		$output .= '<li>';

		if ( $has_children ) {
			$output .= '<details id="Details-menu-drawer-menu-item-' . esc_attr( $handle ) . '">';
			$output .= '<summary id="HeaderDrawer-' . esc_attr( $handle ) . '" class="menu-drawer__menu-item list-menu__item link link--text focus-inset' . ( $current ? ' menu-drawer__menu-item--active' : '' ) . '">';
			$output .= esc_html( $title );
			$output .= $this->arrow_icon();
			$output .= $this->caret_icon();
			$output .= '</summary>';
		} else {
			$output .= '<a id="HeaderDrawer-' . esc_attr( $handle ) . '" href="' . esc_url( $item->url ) . '" class="menu-drawer__menu-item link link--text list-menu__item focus-inset' . ( $current ? ' menu-drawer__menu-item--active' : '' ) . '"' . $this->aria_current( $item ) . '>';
			$output .= esc_html( $title );
			$output .= '</a>';
		}
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		if ( $this->has_children( $item ) ) {
			$output .= '</details>';
		}
		$output .= '</li>';
	}
}
