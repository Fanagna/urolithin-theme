<?php
/**
 * Panstellar — functions.php
 *
 * Converti depuis le thème Shopify Dawn 15.1.0 (layout/theme.liquid + sections/header.liquid).
 * Correspondances Liquid → WordPress :
 *   - {{ 'x.css' | asset_url | stylesheet_tag }}  → wp_enqueue_style()
 *   - {{ 'x.js'  | asset_url }}                    → wp_enqueue_script()
 *   - {{ 'icon-x.svg' | inline_asset_content }}    → panstellar_icon( 'x' )
 *   - {{ shop.name }}                              → get_bloginfo( 'name' )
 *   - {{ settings.logo }}                          → get_theme_mod( 'custom_logo' )
 *   - {{ routes.*_url }}                           → helpers panstellar_*_url()
 *   - {{ cart.item_count }} / {{ cart == empty }}  → panstellar_cart_*() (WooCommerce)
 *   - {{ customer }}                               → is_user_logged_in()
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PANSTELLAR_VERSION', '1.0.0' );

define( 'PANSTELLAR_DIR', get_template_directory() );

// Walkers de menu du header (HTML Dawn : dropdown, mega, drawer).
require_once PANSTELLAR_DIR . '/inc/header-menu-walkers.php';

/* -------------------------------------------------------------------------
 * 1. SETUP DU THÈME
 * ---------------------------------------------------------------------- */
function panstellar_setup() {
	load_theme_textdomain( 'panstellar', get_template_directory() . '/languages' );

	// Menus : principal du header (équivalent de « section.settings.menu ») + footer.
	register_nav_menus(
		array(
			'primary' => __( 'Header menu', 'panstellar' ),
			'footer'  => __( 'Footer menu', 'panstellar' ),
		)
	);

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );

	// Compatibilité WooCommerce.
	add_theme_support( 'woocommerce' );
}
add_action( 'after_setup_theme', 'panstellar_setup' );

/* -------------------------------------------------------------------------
 * 2. RÉGLAGES DU HEADER (équivalents de section.settings.*)
 *
 * Valeurs par défaut extraites de config/settings_data.json (boutique réelle).
 * Modifiables via le Customizer (get_theme_mod) ou via add_filter().
 * ---------------------------------------------------------------------- */

/**
 * Retourne un réglage du header.
 *
 * @param string $key     Clé du réglage (sans préfixe).
 * @param mixed  $default Valeur par défaut.
 * @return mixed
 */
function panstellar_header_setting( $key, $default = '' ) {
	$map = array(
		'logo_position'          => 'middle-left',
		'menu'                   => 'primary',
		'menu_type_desktop'      => 'dropdown',            // dropdown | mega | drawer
		'sticky_header_type'     => 'reduce-logo-size',    // none | on-scroll-up | always | reduce-logo-size
		'show_line_separator'    => true,
		'color_scheme'           => 'scheme-5a9a3de0-927a-4340-b2ed-cabdd3e7d381',
		'menu_color_scheme'      => 'inverse',
		'enable_country_selector'=> false,
		'enable_language_selector'=> false,
		'enable_customer_avatar' => true,
		'mobile_logo_position'   => 'center',              // center | left
		'margin_bottom'          => 0,
		'padding_top'            => 20,
		'padding_bottom'         => 20,
	);
	if ( array_key_exists( $key, $map ) && '' === $default ) {
		$default = $map[ $key ];
	}

	/**
	 * Filtre les réglages du header Panstellar.
	 *
	 * @param mixed  $default Valeur par défaut.
	 * @param string $key     Clé du réglage.
	 */
	return apply_filters( 'panstellar_header_setting_' . $key, get_theme_mod( 'panstellar_header_' . $key, $default ), $key );
}

/**
 * Réglages globaux du thème (équivalents de settings.*).
 *
 * @param string $key     Clé.
 * @param mixed  $default Valeur par défaut.
 * @return mixed
 */
function panstellar_theme_setting( $key, $default = '' ) {
	$map = array(
		'predictive_search_enabled' => false,   // Activer via add_filter('panstellar_theme_setting_predictive_search_enabled', '__return_true') + endpoint AJAX.
		'cart_type'                 => 'drawer',   // drawer | notification
		'customer_accounts_enabled' => true,
		'social_twitter_link'       => '',
		'social_facebook_link'      => '',
		'social_pinterest_link'     => '',
		'social_instagram_link'     => '',
		'social_tiktok_link'        => '',
		'social_tumblr_link'        => '',
		'social_snapchat_link'      => '',
		'social_youtube_link'       => '',
		'social_vimeo_link'         => '',

		// ── Template produit (équivalents de templates/product.json) ──
		// main-product.
		'product_enable_sticky_info'   => true,
		'product_media_size'           => 'small',
		'product_constrain_to_viewport'=> true,
		'product_media_fit'            => 'cover',
		'product_gallery_layout'       => 'thumbnail_slider',
		'product_image_zoom'           => 'hover',
		'product_mobile_thumbnails'    => 'show',
		// related-products.
		'related_heading'              => 'You may also like',
		'related_heading_size'         => 'h2',
		'related_products_to_show'     => 4,
		'related_columns_desktop'      => 4,
		'related_columns_mobile'       => '2',

		// ── Template collection (équivalents de templates/collection.json) ──
		// main-collection-banner.
		'collection_show_description'   => true,
		'collection_show_image'         => false,
		'collection_hero_color_scheme'  => 'inverse',
		// main-collection-product-grid.
		'collection_color_scheme'       => 'inverse',
		'collection_quick_add'          => 'standard',
		'collection_enable_filtering'   => true,
		'collection_filter_type'        => 'vertical',
		'collection_enable_sorting'     => true,
		// card-product (snippets/card-product.liquid).
		'card_color_scheme'             => 'inverse',

		// ── Template recherche (équivalents de templates/search.json) ──
		// main-search.
		'search_enable_filtering'      => true,
		'search_enable_sorting'        => true,
		'search_filter_type'           => 'horizontal',
		'search_columns_desktop'       => 4,
		'search_image_ratio'           => 'adapt',
		'search_image_shape'           => 'default',
		'search_show_secondary_image'  => false,
		'search_show_vendor'           => false,
		'search_show_rating'           => false,
		'search_article_show_date'     => true,
		'search_article_show_author'   => false,
		'search_columns_mobile'        => '2',

		// ── Template panier (équivalents de templates/cart.json) ──
		// main-cart-items.
		'cart_color_scheme'            => 'background-1',
		'cart_padding_top'             => 36,
		'cart_padding_bottom'          => 36,
		// main-cart-footer.
		'cart_footer_color_scheme'     => 'background-1',
		'cart_footer_padding_top'      => 20,
		'cart_footer_padding_bottom'   => 40,
		// cart-note (settings.show_cart_note).
		'cart_show_note'               => true,

		// ── Template blog (équivalents de templates/blog.json) ──
		// main-blog.
		'blog_layout'                  => 'collage',
		'blog_show_image'              => true,
		'blog_image_height'            => 'medium',
		'blog_show_date'               => true,
		'blog_show_author'             => false,
		'blog_padding_top'             => 36,
		'blog_padding_bottom'          => 36,
		// card-product/article (settings.blog_card_* de settings_data.json).
		'blog_card_style'              => 'standard',
		'blog_card_color_scheme'       => 'background-2',
		// main-article (équivalents de templates/article.json).
		'article_show_date'            => true,
		'article_show_author'          => false,

		// ── Template page (équivalents de templates/page.json) ──
		// main-page.
		'page_padding_top'             => 28,
		'page_padding_bottom'          => 28,
	);
	if ( array_key_exists( $key, $map ) && '' === $default ) {
		$default = $map[ $key ];
	}
	return apply_filters( 'panstellar_theme_setting_' . $key, get_theme_mod( 'panstellar_' . $key, $default ) );
}

/* -------------------------------------------------------------------------
 * 2b. RÉGLAGES DU FOOTER (équivalents de section.settings.* du footer)
 *
 * Valeurs par défaut extraites de sections/footer-group.json (boutique réelle).
 * ---------------------------------------------------------------------- */
function panstellar_footer_setting( $key, $default = '' ) {
	$map = array(
		'color_scheme'            => 'accent-1',
		'newsletter_enable'       => true,
		'newsletter_heading'      => 'Subscribe to our emails',
		'enable_follow_on_shop'   => false,
		'show_social'             => true,
		'enable_country_selector' => false, // Via WPML/Polylang (hook).
		'enable_language_selector'=> false, // Via WPML/Polylang (hook).
		'payment_enable'          => true,
		'show_policy'             => false,
		'margin_top'              => 0,
		'padding_top'             => 36,
		'padding_bottom'          => 36,
	);
	if ( array_key_exists( $key, $map ) && '' === $default ) {
		$default = $map[ $key ];
	}
	return apply_filters( 'panstellar_footer_setting_' . $key, get_theme_mod( 'panstellar_footer_' . $key, $default ), $key );
}

/* -------------------------------------------------------------------------
 * 2c. RÉGLAGES DE LA PAGE D'ACCUEIL (équivalents de templates/index.json)
 *
 * Valeurs par défaut extraites de templates/index.json (boutique réelle) :
 *   - slideshow : 1 slide « AFFORDABLE SUPPLEMENTS »
 *   - multicolumn : « Why Choose Us? » (4 colonnes)
 *   - bloc AI « Featured Products » (reconstruit : pas de Liquid source)
 * ---------------------------------------------------------------------- */
function panstellar_home_setting( $key, $default = '' ) {
	$map = array(
		// ── Slideshow (sections/slideshow.liquid) ──
		'slideshow_layout'               => 'full_bleed',
		'slideshow_height'               => 'medium',
		'slideshow_visual'               => 'counter',
		'slideshow_autoplay'             => false,
		'slideshow_speed'                => 5,
		'slideshow_show_text_below'      => true,
		'slideshow_image'                => '',   // Media Library (id ou URL) via Customizer.
		'slideshow_heading'              => 'AFFORDABLE SUPPLEMENTS',
		'slideshow_heading_size'         => 'h0',
		'slideshow_subheading'           => 'GET YOUR FAVORITE SUPPLEMENTS TODAY',
		'slideshow_button_label'         => 'Shop Now',
		'slideshow_link'                 => 'shop',   // shop | collections slug | URL.
		'slideshow_box_align'            => 'middle-left',
		'slideshow_show_text_box'        => false,
		'slideshow_text_alignment'       => 'left',
		'slideshow_text_alignment_mobile'=> 'center',
		'slideshow_overlay_opacity'      => 0,
		'slideshow_color_scheme'         => 'background-1',
		'slideshow_accessibility'        => 'Slideshow about our brand',

		// ── Multicolumn (sections/multicolumn.liquid) ──
		'multicolumn_title'              => 'Why Choose Us?',
		'multicolumn_heading_size'       => 'h1',
		'multicolumn_image_width'        => 'half',
		'multicolumn_image_ratio'        => 'adapt',
		'multicolumn_columns_desktop'    => 4,
		'multicolumn_alignment'          => 'center',
		'multicolumn_background'         => 'none',
		'multicolumn_button_label'       => '',
		'multicolumn_button_link'        => '',
		'multicolumn_color_scheme'       => 'inverse',
		'multicolumn_columns_mobile'     => '2',
		'multicolumn_swipe_mobile'       => false,
		'multicolumn_padding_top'        => 40,
		'multicolumn_padding_bottom'     => 0,
		// Colonnes : array( image, title, text ) — filtrables.
		'multicolumn_columns'            => array(
			array( 'image' => '', 'title' => '', 'text' => '<h3>Quality Guaranteed</h3>' ),
			array( 'image' => '', 'title' => '', 'text' => '<h3>Money Back Guarantee</h3>' ),
			array( 'image' => '', 'title' => '', 'text' => '<h3>SSL Secure</h3>' ),
			array( 'image' => '', 'title' => '', 'text' => '<h3>Free and Fast Shipping from US and the UK</h3>' ),
		),

		// ── Featured Products (bloc AI reconstruit) ──
		'featured_heading'               => 'Featured Products',
		'featured_subheading'            => 'Discover our premium biohacking supplements',
		'featured_collection'            => 'best-seller',  // Slug de catégorie WooCommerce.
		'featured_products_count'        => 12,
		'featured_button_text'           => 'View Product',
		'featured_container_width'       => 1400,
		'featured_per_row_desktop'       => 4,
		'featured_per_row_tablet'        => 2,
		'featured_card_gap'              => 20,
		'featured_padding_top'           => 60,
		'featured_padding_bottom'        => 60,
		// Couleurs du bloc AI (settings réels de templates/index.json).
		'featured_background_color'      => '#0a0a0a',
		'featured_heading_color'         => '#ffffff',
		'featured_subheading_color'      => '#cccccc',
		'featured_card_background'       => '#1a1a1a',
		'featured_image_background'      => '#2a2a2a',
		'featured_product_title_color'   => '#ffffff',
		'featured_price_color'           => '#00d4aa',
		'featured_button_background'     => '#ffffff',
		'featured_button_text_color'     => '#000000',
		'featured_button_hover_background'      => '#00d4aa',
		'featured_nav_button_background'       => '#ffffff',
		'featured_nav_button_color'            => '#000000',
		'featured_nav_button_hover_background' => '#00d4aa',
		'featured_card_border_radius'    => 12,
		'featured_button_border_radius'  => 6,
	);
	if ( array_key_exists( $key, $map ) && '' === $default ) {
		$default = $map[ $key ];
	}
	return apply_filters( 'panstellar_home_setting_' . $key, get_theme_mod( 'panstellar_home_' . $key, $default ), $key );
}

/* -------------------------------------------------------------------------
 * 2d. RÉGLAGES DU BLOG (équivalents de templates/blog.json + article.json)
 *
 * Valeurs par défaut extraites de templates/blog.json + article.json
 * + settings_data.json (blog_card_style: standard, blog_card_color_scheme).
 * ---------------------------------------------------------------------- */
function panstellar_blog_setting( $key, $default = '' ) {
	$map = array(
		// main-blog (templates/blog.json).
		'layout'           => 'collage',
		'show_image'       => true,
		'image_height'     => 'medium',
		'show_date'        => true,
		'show_author'      => false,
		'padding_top'      => 36,
		'padding_bottom'   => 36,
		// settings.blog_card_* (settings_data.json).
		'card_style'       => 'standard',
		'card_color_scheme'=> 'background-2',
		// main-article (templates/article.json).
		'article_show_date'    => true,
		'article_show_author'  => false,
	);
	if ( array_key_exists( $key, $map ) && '' === $default ) {
		$default = $map[ $key ];
	}
	return apply_filters( 'panstellar_blog_setting_' . $key, get_theme_mod( 'panstellar_blog_' . $key, $default ), $key );
}

/**
 * CSS dynamique du blog (équivalent du bloc {% style %} de main-blog.liquid).
 */
function panstellar_blog_dynamic_css() {
	$top    = (int) panstellar_blog_setting( 'padding_top', 36 );
	$bottom = (int) panstellar_blog_setting( 'padding_bottom', 36 );

	$mobile_top    = (int) round( $top * 0.75 );
	$mobile_bottom = (int) round( $bottom * 0.75 );

	return sprintf(
		'.section-main-blog-padding { padding-top: %1$dpx; padding-bottom: %2$dpx; }' .
		'@media screen and (min-width: 750px) { .section-main-blog-padding { padding-top: %3$dpx; padding-bottom: %4$dpx; } }',
		$mobile_top,
		$mobile_bottom,
		$top,
		$bottom
	);
}

/**
 * Callback de rendu d'un commentaire (équivalent du comment.liquid Dawn).
 *
 * @param WP_Comment $comment Commentaire courant.
 * @param array      $args    Arguments wp_list_comments.
 * @param int        $depth   Profondeur.
 */
function panstellar_comment( $comment, $args, $depth ) {
	$comment_author = get_comment_author( $comment );
	$comment_date   = get_comment_date( '', $comment );
	?>
	<article id="comment-<?php comment_ID(); ?>" class="article-template__comments-comment">
		<?php comment_text(); ?>
		<footer class="right">
			<span class="circle-divider caption-with-letter-spacing">
				<?php echo esc_html( $comment_author ); ?>
			</span>
			<span class="caption-with-letter-spacing">
				<time datetime="<?php echo esc_attr( get_comment_date( 'c', $comment ) ); ?>">
					<?php echo esc_html( $comment_date ); ?>
				</time>
			</span>
		</footer>
	</article>
	<?php
}

/**
 * Arguments du formulaire de commentaire (équivalent du form « new_comment »
 * de main-article.liquid) — HTML Dawn : fields .field, bouton .button.
 *
 * @return array
 */
function panstellar_comment_form_args() {
	$commenter = wp_get_current_commenter();

	$fields = array(
		'author' =>
			'<div class="article-template__comment-fields">' .
			'<div class="field field--with-error">' .
			'<input type="text" name="author" id="CommentForm-author" class="field__input" autocomplete="name" value="' . esc_attr( $commenter['comment_author'] ) . '" aria-required="true" required placeholder="' . esc_attr__( 'Name', 'panstellar' ) . '">' .
			'<label class="field__label" for="CommentForm-author">' . esc_html__( 'Name', 'panstellar' ) . ' <span aria-hidden="true">*</span></label>' .
			'</div>',
		'email'  =>
			'<div class="field field--with-error">' .
			'<input type="email" name="email" id="CommentForm-email" autocomplete="email" class="field__input" value="' . esc_attr( $commenter['comment_author_email'] ) . '" autocorrect="off" autocapitalize="off" aria-required="true" required placeholder="' . esc_attr__( 'Email', 'panstellar' ) . '">' .
			'<label class="field__label" for="CommentForm-email">' . esc_html__( 'Email', 'panstellar' ) . ' <span aria-hidden="true">*</span></label>' .
			'</div>' .
			'</div>',
		'url'    => '',
	);

	$comment_field =
		'<div class="field field--with-error">' .
		'<textarea rows="5" name="comment" id="CommentForm-body" class="text-area field__input" aria-required="true" required placeholder="' . esc_attr__( 'Message', 'panstellar' ) . '"></textarea>' .
		'<label class="form__label field__label" for="CommentForm-body">' . esc_html__( 'Message', 'panstellar' ) . ' <span aria-hidden="true">*</span></label>' .
		'</div>';

	return array(
		'fields'               => $fields,
		'comment_field'        => $comment_field,
		'class_form'           => 'comment-form',
		'title_reply'          => __( 'Leave a comment', 'panstellar' ),
		'title_reply_before'   => '<h2 id="reply-title" class="comment-reply-title">',
		'title_reply_after'    => '</h2>',
		'label_submit'         => __( 'Post comment', 'panstellar' ),
		'submit_button'        => '<input name="%1$s" type="submit" id="%2$s" class="button" value="%3$s">',
		'comment_notes_before' => '',
		'comment_notes_after'  => '',
	);
}

/**
 * CSS dynamique des pages (équivalent du bloc {% style %} de main-page.liquid).
 */
function panstellar_page_dynamic_css() {
	$top    = (int) panstellar_theme_setting( 'page_padding_top', 28 );
	$bottom = (int) panstellar_theme_setting( 'page_padding_bottom', 28 );

	$mobile_top    = (int) round( $top * 0.75 );
	$mobile_bottom = (int) round( $bottom * 0.75 );

	return sprintf(
		'.section-main-page-padding { padding-top: %1$dpx; padding-bottom: %2$dpx; }' .
		'@media screen and (min-width: 750px) { .section-main-page-padding { padding-top: %3$dpx; padding-bottom: %4$dpx; } }',
		$mobile_top,
		$mobile_bottom,
		$top,
		$bottom
	);
}

/* -------------------------------------------------------------------------
 * 3. HELPERS D'URLS (équivalents de {{ routes.* }})
 * ---------------------------------------------------------------------- */

function panstellar_home_url() {
	return esc_url( home_url( '/' ) );
}

function panstellar_search_url() {
	return esc_url( home_url( '/?s=' ) );
}

/**
 * URL du panier WooCommerce. Retourne la page panier si WooCommerce est actif,
 * sinon la page d'accueil (fallback sans dépendance).
 */
function panstellar_cart_url() {
	if ( function_exists( 'wc_get_cart_url' ) ) {
		return esc_url( wc_get_cart_url() );
	}
	return panstellar_home_url();
}

/**
 * URL du checkout WooCommerce (fallback panier si WooCommerce inactif).
 */
function panstellar_checkout_url() {
	if ( function_exists( 'wc_get_checkout_url' ) ) {
		return esc_url( wc_get_checkout_url() );
	}
	return panstellar_cart_url();
}

/**
 * URL du compte client WooCommerce (My Account).
 */
function panstellar_account_url() {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		$url = wc_get_page_permalink( 'myaccount' );
		if ( $url ) {
			return esc_url( $url );
		}
	}
	return wp_login_url();
}

/* -------------------------------------------------------------------------
 * 4. HELPERS PANIER & CLIENT (équivalents de cart / customer)
 * ---------------------------------------------------------------------- */

/**
 * Le panier est-il vide ?
 */
function panstellar_cart_is_empty() {
	if ( function_exists( 'WC' ) && WC()->cart ) {
		return WC()->cart->is_empty();
	}
	return true;
}

/**
 * Nombre d'articles dans le panier.
 */
function panstellar_cart_count() {
	if ( function_exists( 'WC' ) && WC()->cart ) {
		return (int) WC()->cart->get_cart_contents_count();
	}
	return 0;
}

/**
 * Client connecté ? (équivalent de {% if customer %})
 */
function panstellar_customer_logged_in() {
	return is_user_logged_in();
}

/* -------------------------------------------------------------------------
 * 5. HELPERS D'ICÔNES SVG (équivalents de {{ 'icon-x.svg' | inline_asset_content }})
 * ---------------------------------------------------------------------- */

/**
 * Affiche le contenu d'une icône SVG inline (comme inline_asset_content).
 *
 * @param string $name Nom du fichier sans extension (ex: 'account').
 */
function panstellar_icon( $name ) {
	echo panstellar_get_icon( $name ); // phpcs:ignore WordPress.Security.EscapeOutput -- SVG inoffensif local.
}

/**
 * Retourne le contenu d'un SVG du dossier assets/icons/.
 *
 * @param string $name Nom du fichier sans extension.
 * @return string
 */
function panstellar_get_icon( $name ) {
	$name = sanitize_file_name( $name );

	// Les fichiers du thème sont préfixés « icon- » (ex. icon-caret.svg), mais
	// les appels du thème utilisent le nom court (panstellar_icon( 'caret' )).
	// On essaie les deux formes pour rester compatible avec les deux usages.
	$path = get_template_directory() . '/assets/icons/' . $name . '.svg';
	if ( ! file_exists( $path ) && 0 !== strpos( $name, 'icon-' ) ) {
		$path = get_template_directory() . '/assets/icons/icon-' . $name . '.svg';
	}

	if ( ! file_exists( $path ) ) {
		return '';
	}

	$svg = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions

	// Sécurité minimale : on ne garde que le <svg>…</svg>.
	if ( preg_match( '#<svg[^>]*>.*?</svg>#is', $svg, $matches ) ) {
		return $matches[0];
	}
	return $svg;
}

/* -------------------------------------------------------------------------
 * 6. LOGO (équivalents de {{ settings.logo }})
 * ---------------------------------------------------------------------- */

/**
 * Retourne l'image du logo (custom-logo WordPress) ou le nom du site.
 *
 * @param int $width Largeur souhaitée (px).
 * @return string HTML.
 */
function panstellar_logo( $width = 180 ) {
	$logo_id = (int) get_theme_mod( 'custom_logo' );
	$name    = get_bloginfo( 'name' );

	if ( $logo_id ) {
		$logo_alt = get_post_meta( $logo_id, '_wp_attachment_image_alt', true );
		if ( ! $logo_alt ) {
			$logo_alt = $name;
		}
		$attr = array(
			'class'    => 'header__heading-logo motion-reduce',
			'decoding' => 'async',
		);
		$img  = wp_get_attachment_image( $logo_id, 'full', false, $attr );
		if ( $img ) {
			return '<div class="header__heading-logo-wrapper">' . $img . '</div>';
		}
	}

	// Fallback : nom du site (équivalent de <span class="h2">{{ shop.name }}</span>).
	return '<span class="h2">' . esc_html( $name ) . '</span>';
}

/* -------------------------------------------------------------------------
 * 6b. FRAGMENT PANIER AJAX (équivalent de la mise à jour cart-icon-bubble)
 * ---------------------------------------------------------------------- */

/**
 * Retourne le HTML de la bulle « cart-count-bubble » (équivalent du rendu
 * conditionnel cart != empty de sections/cart-icon-bubble.liquid).
 */
function panstellar_cart_bubble_html() {
	$count = panstellar_cart_count();
	ob_start();
	?>
	<a
		href="<?php echo panstellar_cart_url(); // phpcs:ignore WordPress.Security.EscapeOutput -- helper déjà esc_url. ?>"
		class="header__icon header__icon--cart link focus-inset"
		id="cart-icon-bubble"
	>
		<span class="svg-wrapper"><?php panstellar_icon( panstellar_cart_is_empty() ? 'cart-empty' : 'cart' ); ?></span>
		<span class="visually-hidden"><?php esc_html_e( 'Cart', 'panstellar' ); ?></span>
		<?php if ( ! panstellar_cart_is_empty() ) : ?>
			<div class="cart-count-bubble" data-cart-count-bubble>
				<?php if ( $count < 100 ) : ?>
					<span aria-hidden="true" data-cart-count><?php echo esc_html( (string) $count ); ?></span>
				<?php endif; ?>
				<span class="visually-hidden"><?php echo esc_html( sprintf( /* translators: %s: cart item count */ _n( '%s item', '%s items', $count, 'panstellar' ), $count ) ); ?></span>
			</div>
		<?php endif; ?>
	</a>
	<?php
	return ob_get_clean();
}

/**
 * Enregistre la bulle panier dans les fragments AJAX WooCommerce
 * (mise à jour après add-to-cart sans rechargement).
 *
 * @param array $fragments Fragments existants.
 * @return array
 */
function panstellar_add_cart_fragment( $fragments ) {
	$fragments['#cart-icon-bubble'] = panstellar_cart_bubble_html();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'panstellar_add_cart_fragment' );

/**
 * Rendu du contenu des items du cart drawer (utilisé par le template-part
 * ET par les fragments AJAX — défini ici pour être disponible pendant les
 * requêtes ?wc-ajax= où le footer n'est jamais rendu).
 */
function panstellar_cart_drawer_items_html() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return '';
	}

	ob_start();
	?>
	<div id="CartDrawer-CartItems" class="drawer__contents js-contents">
		<?php if ( ! WC()->cart->is_empty() ) : ?>
			<div class="drawer__cart-items-wrapper">
				<table class="cart-items" role="table">
					<thead role="rowgroup">
						<tr role="row">
							<th id="CartDrawer-ColumnProductImage" role="columnheader">
								<span class="visually-hidden"><?php esc_html_e( 'Image', 'panstellar' ); ?></span>
							</th>
							<th id="CartDrawer-ColumnProduct" class="caption-with-letter-spacing" scope="col" role="columnheader">
								<?php esc_html_e( 'Product', 'panstellar' ); ?>
							</th>
							<th id="CartDrawer-ColumnTotal" class="right caption-with-letter-spacing" scope="col" role="columnheader">
								<?php esc_html_e( 'Total', 'panstellar' ); ?>
							</th>
							<th id="CartDrawer-ColumnQuantity" role="columnheader">
								<span class="visually-hidden"><?php esc_html_e( 'Quantity', 'panstellar' ); ?></span>
							</th>
						</tr>
					</thead>

					<tbody role="rowgroup">
						<?php
						$index = 0;
						foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
							$index++;
							$_product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;
							if ( ! $_product ) {
								continue;
							}
							$quantity       = (int) $cart_item['quantity'];
							$item_price     = WC()->cart->get_product_price( $_product );
							$item_subtotal  = WC()->cart->get_product_subtotal( $_product, $quantity );
							$item_url       = $_product->get_permalink();
							$item_name      = $_product->get_name();
							$item_image     = $_product->get_image( 'woocommerce_thumbnail', array( 'class' => 'cart-item__image', 'width' => 150 ) );
							$variation_data = function_exists( 'wc_get_formatted_cart_item_data' ) ? wc_get_formatted_cart_item_data( $cart_item, true ) : '';
							?>
							<tr id="CartDrawer-Item-<?php echo esc_attr( $index ); ?>" class="cart-item" role="row">
								<td class="cart-item__media" role="cell" headers="CartDrawer-ColumnProductImage">
									<?php if ( $item_image ) : ?>
										<a href="<?php echo esc_url( $item_url ); ?>" class="cart-item__link" tabindex="-1" aria-hidden="true"> </a>
										<?php echo $item_image; // phpcs:ignore WordPress.Security.EscapeOutput -- HTML généré par WooCommerce. ?>
									<?php endif; ?>
								</td>

								<td class="cart-item__details" role="cell" headers="CartDrawer-ColumnProduct">
									<a href="<?php echo esc_url( $item_url ); ?>" class="cart-item__name h4 break">
										<?php echo esc_html( $item_name ); ?>
									</a>

									<div class="product-option">
										<?php echo wp_kses_post( $item_price ); ?>
									</div>

									<?php if ( $variation_data ) : ?>
										<dl>
											<div class="product-option">
												<?php echo wp_kses_post( $variation_data ); ?>
											</div>
										</dl>
									<?php endif; ?>
								</td>

								<td class="cart-item__totals right" role="cell" headers="CartDrawer-ColumnTotal">
									<?php get_template_part( 'template-parts/loading-spinner' ); ?>
									<div class="cart-item__price-wrapper">
										<span class="price price--end"><?php echo wp_kses_post( $item_subtotal ); ?></span>
									</div>
								</td>

								<td class="cart-item__quantity" role="cell" headers="CartDrawer-ColumnQuantity">
									<quantity-popover>
										<div class="cart-item__quantity-wrapper quantity-popover-wrapper">
											<div class="quantity-popover-container">
												<quantity-input class="quantity cart-quantity">
													<button class="quantity__button" name="minus" type="button">
														<span class="visually-hidden"><?php esc_html_e( 'Decrease quantity', 'panstellar' ); ?></span>
														<span class="svg-wrapper"><?php panstellar_icon( 'minus' ); ?></span>
													</button>
													<input
														class="quantity__input"
														type="number"
														name="updates[]"
														value="<?php echo esc_attr( $quantity ); ?>"
														data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>"
														min="0"
														data-min="0"
														step="1"
														aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name */ __( 'Quantity of %s', 'panstellar' ), $item_name ) ); ?>"
														id="Drawer-quantity-<?php echo esc_attr( $index ); ?>"
														data-index="<?php echo esc_attr( $index ); ?>"
													>
													<button class="quantity__button" name="plus" type="button">
														<span class="visually-hidden"><?php esc_html_e( 'Increase quantity', 'panstellar' ); ?></span>
														<span class="svg-wrapper"><?php panstellar_icon( 'plus' ); ?></span>
													</button>
												</quantity-input>
											</div>
											<cart-remove-button
												id="CartDrawer-Remove-<?php echo esc_attr( $index ); ?>"
												data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>"
											>
												<button
													type="button"
													class="button button--tertiary cart-remove-button"
													aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name */ __( 'Remove %s', 'panstellar' ), $item_name ) ); ?>"
												>
													<span class="svg-wrapper">
														<?php panstellar_icon( 'remove' ); ?>
													</span>
												</button>
											</cart-remove-button>
										</div>
										<div
											id="CartDrawer-LineItemError-<?php echo esc_attr( $index ); ?>"
											class="cart-item__error"
											role="alert"
										>
											<small class="cart-item__error-text"></small>
											<span class="svg-wrapper">
												<?php panstellar_icon( 'error' ); ?>
											</span>
										</div>
									</quantity-popover>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
		<p id="CartDrawer-LiveRegionText" class="visually-hidden" role="status"></p>
		<p id="CartDrawer-LineItemStatus" class="visually-hidden" aria-hidden="true" role="status">
			<?php esc_html_e( 'Loading…', 'panstellar' ); ?>
		</p>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Rendu du footer du cart drawer (sans les CTAs, rendus statiquement par
 * le template-part — évite la duplication lors du replaceWith des fragments).
 */
function panstellar_cart_drawer_footer_html() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return '';
	}

	$cart_subtotal = WC()->cart->get_cart_subtotal();

	ob_start();
	?>
	<div class="cart-drawer__footer">
		<div class="totals" role="status">
			<h2 class="totals__total"><?php esc_html_e( 'Estimated total', 'panstellar' ); ?></h2>
			<p class="totals__total-value"><?php echo wp_kses_post( $cart_subtotal ); ?></p>
		</div>

		<small class="tax-note caption-large rte">
			<?php esc_html_e( 'Taxes, discounts and shipping calculated at checkout.', 'panstellar' ); ?>
		</small>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Fragments AJAX du cart drawer : items + footer du drawer sont re-rendus
 * après chaque modification du panier (équivalent des sections Shopify
 * « cart-drawer » / « cart-icon-bubble » de cart-drawer.js).
 *
 * @param array $fragments Fragments existants.
 * @return array
 */
function panstellar_cart_drawer_fragments( $fragments ) {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return $fragments;
	}
	$fragments['#CartDrawer-CartItems'] = panstellar_cart_drawer_items_html();
	if ( ! WC()->cart->is_empty() ) {
		$fragments['.cart-drawer__footer'] = panstellar_cart_drawer_footer_html();
	}
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'panstellar_cart_drawer_fragments' );

/**
 * Rendu du contenu de la page panier (woocommerce/cart/cart.php) — défini ici
 * pour être disponible pendant les requêtes ?wc-ajax=get_refreshed_fragments
 * (où le template n'est jamais rendu) et pour le rendu initial du template.
 *
 * @param bool $echo Afficher (true) ou retourner (false).
 * @return string
 */
function panstellar_cart_page_items_html( $echo = false ) {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return '';
	}

	ob_start();
	?>
	<div class="cart__items" id="main-cart-items" data-id="cart-items">
	<?php if ( ! WC()->cart->is_empty() ) : ?>
		<div class="js-contents">
			<table class="cart-items">
				<caption class="visually-hidden"><?php esc_html_e( 'Your cart', 'panstellar' ); ?></caption>
				<thead>
					<tr>
						<th class="caption-with-letter-spacing" colspan="2" scope="col"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
						<th class="medium-hide large-up-hide right caption-with-letter-spacing" colspan="1" scope="col"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
						<th class="cart-items__heading--wide cart-items__heading--quantity small-hide caption-with-letter-spacing" colspan="1" scope="col"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
						<th class="small-hide right caption-with-letter-spacing" colspan="1" scope="col"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
					</tr>
				</thead>

				<tbody>
					<?php
					$index = 0;
					foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
						$index++;
						$_product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;
						if ( ! $_product ) {
							continue;
						}
						$quantity       = (int) $cart_item['quantity'];
						$item_price     = WC()->cart->get_product_price( $_product );
						$item_subtotal  = WC()->cart->get_product_subtotal( $_product, $quantity );
						$line_subtotal  = $_product->get_price() * $quantity;
						$item_url       = $_product->get_permalink();
						$item_name      = $_product->get_name();
						$item_image     = $_product->get_image( 'woocommerce_thumbnail', array( 'class' => 'cart-item__image' ) );
						$variation_data = function_exists( 'wc_get_formatted_cart_item_data' ) ? wc_get_formatted_cart_item_data( $cart_item, true ) : '';
						?>
						<tr class="cart-item" id="CartItem-<?php echo esc_attr( $index ); ?>">
							<td class="cart-item__media">
								<?php if ( $item_image ) : ?>
									<a href="<?php echo esc_url( $item_url ); ?>" class="cart-item__link" aria-hidden="true" tabindex="-1"> </a>
									<div class="cart-item__image-container gradient global-media-settings">
										<?php echo $item_image; // phpcs:ignore WordPress.Security.EscapeOutput -- HTML généré par WooCommerce. ?>
									</div>
								<?php endif; ?>
							</td>

							<td class="cart-item__details">
								<a href="<?php echo esc_url( $item_url ); ?>" class="cart-item__name h4 break"><?php echo esc_html( $item_name ); ?></a>

								<div class="product-option"><?php echo wp_kses_post( $item_price ); ?></div>

								<?php if ( $variation_data ) : ?>
									<dl>
										<div class="product-option"><?php echo wp_kses_post( $variation_data ); ?></div>
									</dl>
								<?php endif; ?>
							</td>

							<td class="cart-item__totals right medium-hide large-up-hide">
								<?php get_template_part( 'template-parts/loading-spinner' ); ?>
								<div class="cart-item__price-wrapper">
									<span class="price price--end"><?php echo wp_kses_post( wc_price( $line_subtotal ) ); ?></span>
								</div>
							</td>

							<td class="cart-item__quantity">
								<quantity-popover>
									<div class="cart-item__quantity-wrapper quantity-popover-wrapper">
										<label class="visually-hidden" for="Quantity-<?php echo esc_attr( $index ); ?>">
											<?php esc_html_e( 'Quantity', 'woocommerce' ); ?>
										</label>
										<div class="quantity-popover-container">
											<quantity-input class="quantity cart-quantity">
												<button class="quantity__button" name="minus" type="button">
													<span class="visually-hidden"><?php esc_html_e( 'Decrease quantity', 'woocommerce' ); ?></span>
													<span class="svg-wrapper"><?php panstellar_icon( 'minus' ); ?></span>
												</button>
												<input
													class="quantity__input"
													data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>"
													type="number"
													name="cart[<?php echo esc_attr( $cart_item_key ); ?>][qty]"
													value="<?php echo esc_attr( $quantity ); ?>"
													min="0"
													step="1"
													aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name */ __( 'Quantity of %s', 'woocommerce' ), $item_name ) ); ?>"
													id="Quantity-<?php echo esc_attr( $index ); ?>"
													data-index="<?php echo esc_attr( $index ); ?>"
												>
												<button class="quantity__button" name="plus" type="button">
													<span class="visually-hidden"><?php esc_html_e( 'Increase quantity', 'woocommerce' ); ?></span>
													<span class="svg-wrapper"><?php panstellar_icon( 'plus' ); ?></span>
												</button>
											</quantity-input>
										</div>
										<cart-remove-button
											id="Remove-<?php echo esc_attr( $index ); ?>"
											data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>"
										>
											<a
												href="<?php echo esc_url( add_query_arg( 'remove_item', $cart_item_key, wc_get_cart_url() ) ); ?>"
												class="button button--tertiary"
												aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name */ __( 'Remove %s', 'woocommerce' ), $item_name ) ); ?>"
											>
												<span class="svg-wrapper"><?php panstellar_icon( 'remove' ); ?></span>
											</a>
										</cart-remove-button>
									</div>
									<div class="cart-item__error" id="Line-item-error-<?php echo esc_attr( $index ); ?>" role="alert">
										<small class="cart-item__error-text"></small>
										<span class="svg-wrapper"><?php panstellar_icon( 'error' ); ?></span>
									</div>
								</quantity-popover>
							</td>

							<td class="cart-item__totals right small-hide">
								<?php get_template_part( 'template-parts/loading-spinner' ); ?>
								<div class="cart-item__price-wrapper">
									<span class="price price--end"><?php echo wp_kses_post( $item_subtotal ); ?></span>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
	</div>
	<?php

	$html = ob_get_clean();
	if ( $echo ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- HTML construit avec échappement.
		return '';
	}
	return $html;
}

/**
 * Rendu du footer de la page panier (subtotal + note + checkout).
 *
 * @param bool $echo Afficher (true) ou retourner (false).
 * @return string
 */
function panstellar_cart_page_footer_html( $echo = false ) {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return '';
	}

	$cart_is_empty   = WC()->cart->is_empty();
	$show_cart_note  = (bool) panstellar_theme_setting( 'cart_show_note', true );
	$checkout_url    = wc_get_checkout_url();
	$continue_url    = apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) );

	ob_start();
	?>
	<div
		class="gradient color-<?php echo esc_attr( panstellar_theme_setting( 'cart_footer_color_scheme', 'background-1' ) ); ?><?php echo $cart_is_empty ? ' is-empty' : ''; ?>"
		id="main-cart-footer"
		data-id="cart-footer"
	>
		<div class="page-width">
			<div class="cart__footer isolate section-cart-footer-padding">
				<?php if ( $show_cart_note ) : ?>
					<cart-note class="cart__note field">
						<label for="Cart-note"><?php esc_html_e( 'Order special instructions', 'woocommerce' ); ?></label>
						<textarea
							class="text-area field__input"
							name="woocommerce-cart-note"
							form="cart"
							id="Cart-note"
							placeholder="<?php esc_attr_e( 'Order special instructions', 'woocommerce' ); ?>"
						><?php echo esc_textarea( WC()->cart->get_customer()->get_customer_note() ); ?></textarea>
					</cart-note>
				<?php endif; ?>

				<div class="cart__blocks js-contents">
					<?php if ( ! $cart_is_empty ) : ?>
						<div>
							<?php
							// ── Bloc subtotal (blocs « subtotal » de cart.json) ──
							$cart_subtotal_html = WC()->cart->get_cart_subtotal();
							?>
							<div class="totals">
								<h2 class="totals__total"><?php esc_html_e( 'Estimated total', 'woocommerce' ); ?></h2>
								<p class="totals__total-value"><?php echo wp_kses_post( $cart_subtotal_html ); ?></p>
							</div>

							<small class="tax-note caption-large rte">
								<?php esc_html_e( 'Taxes, discounts and shipping calculated at checkout.', 'woocommerce' ); ?>
							</small>
						</div>

						<div class="cart__ctas">
							<a
								href="<?php echo esc_url( $checkout_url ); ?>"
								id="checkout"
								class="cart__checkout-button button"
							>
								<?php esc_html_e( 'Checkout', 'woocommerce' ); ?>
							</a>
						</div>

						<div id="cart-errors"></div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	<?php

	$html = ob_get_clean();
	if ( $echo ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- HTML construit avec échappement.
		return '';
	}
	return $html;
}

/**
 * Fragments AJAX de la page panier : les items et le footer de la page
 * (main-cart-items / main-cart-footer) sont re-rendus après chaque
 * modification — équivalent de la section Shopify « main-cart-items ».
 *
 * @param array $fragments Fragments existants.
 * @return array
 */
function panstellar_cart_page_fragments( $fragments ) {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return $fragments;
	}
	$fragments['#main-cart-items'] = panstellar_cart_page_items_html();
	$fragments['.cart__footer']    = panstellar_cart_page_footer_html();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'panstellar_cart_page_fragments' );

/**
 * Sauvegarde AJAX de la note de commande de la page panier.
 *
 * La note est persistée sur le client WooCommerce (WC()->customer) pour être
 * reprise au checkout. WooCommerce ne fournit pas d'endpoint wc-ajax natif
 * pour la note (seul le POST complet du formulaire le gère) : on expose donc
 * un endpoint admin-ajax dédié, sécurisé par le nonce « woocommerce-cart »
 * déjà présent dans le formulaire panier.
 */
add_action( 'wp_ajax_panstellar_cart_note', 'panstellar_cart_note_ajax' );
add_action( 'wp_ajax_nopriv_panstellar_cart_note', 'panstellar_cart_note_ajax' );

function panstellar_cart_note_ajax() {
	check_ajax_referer( 'woocommerce-cart', 'security' );

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( array( 'message' => __( 'Cart unavailable.', 'panstellar' ) ) );
	}

	$note = isset( $_POST['woocommerce-cart-note'] ) ? wc_clean( wp_unslash( $_POST['woocommerce-cart-note'] ) ) : '';

	WC()->customer->set_customer_note( $note );
	wc_set_customer_session_cookie( true );

	// Fragments cohérents (bulle header + drawer + page).
	$fragments = apply_filters( 'woocommerce_add_to_cart_fragments', array() );
	wp_send_json( array( 'fragments' => $fragments ) );
}

/* -------------------------------------------------------------------------
 * 6c. NEWSLETTER (équivalent de la soumission newsletter.liquid)
 * ---------------------------------------------------------------------- */
add_action( 'admin_post_panstellar_newsletter', 'panstellar_newsletter_handler' );
add_action( 'admin_post_nopriv_panstellar_newsletter', 'panstellar_newsletter_handler' );

/**
 * Handler du formulaire newsletter du footer.
 *
 * Équivalent de la soumission du formulaire Shopify (newsletter.liquid) :
 * vérifie le nonce, valide l'email, puis enregistre l'abonné dans une option
 * (panstellar_newsletter_subscribers). Un plugin (ex. Mailchimp for WP)
 * peut consommer ces emails via le filtre 'panstellar_newsletter_subscribed'.
 */
function panstellar_newsletter_handler() {
	$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );

	if (
		! isset( $_POST['panstellar_newsletter_nonce'], $_POST['newsletter_email'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['panstellar_newsletter_nonce'] ) ), 'panstellar_newsletter' )
	) {
		wp_safe_redirect( add_query_arg( 'newsletter', 'error', $redirect ) );
		exit;
	}

	$email = sanitize_email( wp_unslash( $_POST['newsletter_email'] ) );

	if ( ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'newsletter', 'invalid', $redirect ) );
		exit;
	}

	$subscribers = get_option( 'panstellar_newsletter_subscribers', array() );
	if ( ! is_array( $subscribers ) ) {
		$subscribers = array();
	}
	if ( ! in_array( $email, $subscribers, true ) ) {
		$subscribers[] = $email;
		update_option( 'panstellar_newsletter_subscribers', $subscribers, false );
	}

	/**
	 * Hook permettant à un plugin (Mailchimp, Klaviyo…) de consommer l'abonnement.
	 *
	 * @param string $email Email validé de l'abonné.
	 */
	do_action( 'panstellar_newsletter_subscribed', $email );

	wp_safe_redirect( add_query_arg( 'newsletter', 'success', $redirect ) );
	exit;
}

/* -------------------------------------------------------------------------
 * 6d. FORMULAIRE DE CONTACT (équivalent de sections/contact-form.liquid)
 * ---------------------------------------------------------------------- */
add_action( 'admin_post_panstellar_contact', 'panstellar_contact_handler' );
add_action( 'admin_post_nopriv_panstellar_contact', 'panstellar_contact_handler' );

/**
 * Handler du formulaire de contact (template-parts/page/contact-form.php).
 *
 * Équivalent de la soumission {% form 'contact' %} : vérifie le nonce,
 * valide les champs, puis envoie l'email via wp_mail() à l'administrateur.
 */
function panstellar_contact_handler() {
	$redirect = isset( $_POST['redirect'] ) ? esc_url_raw( wp_unslash( $_POST['redirect'] ) ) : home_url( '/' );

	if (
		! isset( $_POST['panstellar_contact_nonce'], $_POST['contact_email'], $_POST['contact_body'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['panstellar_contact_nonce'] ) ), 'panstellar_contact' )
	) {
		wp_safe_redirect( add_query_arg( 'contact', 'error', $redirect ) );
		exit;
	}

	$name  = isset( $_POST['contact_name'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_name'] ) ) : '';
	$email = sanitize_email( wp_unslash( $_POST['contact_email'] ) );
	$phone = isset( $_POST['contact_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_phone'] ) ) : '';
	$body  = sanitize_textarea_field( wp_unslash( $_POST['contact_body'] ) );

	if ( ! is_email( $email ) || '' === $body ) {
		// Repasse name/email pour pré-remplir le formulaire après l'erreur.
		$error_args = array(
			'contact' => 'error',
			'name'    => $name,
			'email'   => $email,
		);
		wp_safe_redirect( add_query_arg( $error_args, $redirect ) );
		exit;
	}

	$admin_email = get_option( 'admin_email' );
	$subject     = sprintf( /* translators: %s: site name */ __( '[%s] New contact form message', 'panstellar' ), get_bloginfo( 'name' ) );
	$message     = sprintf(
		"%s\n\n%s\n%s\n\n%s\n\n%s",
		sprintf( /* translators: %s: sender name */ __( 'Name: %s', 'panstellar' ), $name ),
		sprintf( /* translators: %s: sender email */ __( 'Email: %s', 'panstellar' ), $email ),
		$phone ? sprintf( /* translators: %s: phone */ __( 'Phone: %s', 'panstellar' ), $phone ) : '',
		__( 'Message:', 'panstellar' ),
		$body
	);

	$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );
	$sent    = wp_mail( $admin_email, $subject, $message, $headers );

	wp_safe_redirect( add_query_arg( 'contact', $sent ? 'sent' : 'error', $redirect ) );
	exit;
}

/* -------------------------------------------------------------------------
 * 7. RÉSEAUX SOCIAUX (équivalents de settings.social_*_link)
 * ---------------------------------------------------------------------- */

/**
 * Liste des réseaux sociaux activés.
 *
 * @return array<string,string> handle => url.
 */
function panstellar_social_links() {
	$networks = array(
		'twitter'   => 'social_twitter_link',
		'facebook'  => 'social_facebook_link',
		'pinterest' => 'social_pinterest_link',
		'instagram' => 'social_instagram_link',
		'tiktok'    => 'social_tiktok_link',
		'tumblr'    => 'social_tumblr_link',
		'snapchat'  => 'social_snapchat_link',
		'youtube'   => 'social_youtube_link',
		'vimeo'     => 'social_vimeo_link',
	);
	$links     = array();
	foreach ( $networks as $handle => $key ) {
		$url = panstellar_theme_setting( $key );
		if ( $url ) {
			$links[ $handle ] = $url;
		}
	}
	return $links;
}

/**
 * Y a-t-il des liens sociaux configurés ? (équivalent de l'assignation social_links)
 *
 * @return bool
 */
function panstellar_has_social_links() {
	return (bool) panstellar_social_links();
}

/* -------------------------------------------------------------------------
 * 8. ENQUEUE CSS / JS
 * ---------------------------------------------------------------------- */
function panstellar_scripts() {
	$theme_uri = get_template_directory_uri();

	// Polices réelles de la boutique (settings_data.json : type_header_font=poppins, type_body_font=inter).
	wp_enqueue_style(
		'panstellar-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap',
		array(),
		null
	);

	// Styles du layout (équivalents des stylesheet_tag de layout/theme.liquid).
	// NB : les component-*.css du header sont émis par template-parts/header.php
	// (fidèle à sections/header.liquid qui rend ses propres <link>).
	wp_enqueue_style( 'panstellar-base', $theme_uri . '/assets/css/base.css', array( 'panstellar-fonts' ), PANSTELLAR_VERSION );

	// Feuille principale du header (variables de couleur + styles custom).
	wp_enqueue_style( 'panstellar-header', $theme_uri . '/assets/css/header.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );

	// Styles inline dynamiques (padding / margin, équivalents du {% style %} de header.liquid).
	wp_add_inline_style( 'panstellar-header', panstellar_header_dynamic_css() );

	// Cart drawer (conversion de snippets/cart-drawer.liquid) : styles + JS globaux.
	wp_enqueue_style( 'panstellar-cart-drawer', $theme_uri . '/assets/css/component-cart-drawer.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
	wp_enqueue_style( 'panstellar-quantity-popover', $theme_uri . '/assets/css/quantity-popover.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
	wp_enqueue_script( 'panstellar-cart-drawer', $theme_uri . '/assets/js/cart-drawer.js', array(), PANSTELLAR_VERSION, true );

	// Footer : styles du footer + composants (équivalents des stylesheet_tag de footer.liquid).
	wp_enqueue_style( 'panstellar-footer', $theme_uri . '/assets/css/section-footer.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
	wp_enqueue_style( 'panstellar-newsletter', $theme_uri . '/assets/css/component-newsletter.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
	wp_enqueue_style( 'panstellar-list-menu', $theme_uri . '/assets/css/component-list-menu.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
	wp_enqueue_style( 'panstellar-list-payment', $theme_uri . '/assets/css/component-list-payment.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
	wp_enqueue_style( 'panstellar-list-social', $theme_uri . '/assets/css/component-list-social.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );

	// Padding / margin dynamiques du footer (équivalent du {% style %} de footer.liquid).
	wp_add_inline_style( 'panstellar-footer', panstellar_footer_dynamic_css() );

	// ── Page d'accueil : styles des sections (slideshow, multicolumn, produits) ──
	if ( is_front_page() ) {
		wp_enqueue_style( 'panstellar-card', $theme_uri . '/assets/css/component-card.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-image-banner', $theme_uri . '/assets/css/section-image-banner.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-slider', $theme_uri . '/assets/css/component-slider.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-slideshow', $theme_uri . '/assets/css/component-slideshow.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-multicolumn', $theme_uri . '/assets/css/section-multicolumn.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		// NB : component-price.css est déjà émis par template-parts/header.php (pas de doublon).
		wp_enqueue_style( 'panstellar-home', $theme_uri . '/assets/css/home.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );

		// Styles inline dynamiques (équivalents des blocs {% style %} des sections).
		wp_add_inline_style( 'panstellar-home', panstellar_home_dynamic_css() );

		// JavaScript home : SliderComponent + SlideshowComponent (autonomes, sans jQuery).
		wp_enqueue_script( 'panstellar-home', $theme_uri . '/assets/js/home.js', array(), PANSTELLAR_VERSION, true );
	}

	// ── Template produit (conversion de templates/product.json) ──
	if ( is_product() ) {
		wp_enqueue_style( 'panstellar-main-product', $theme_uri . '/assets/css/section-main-product.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-variant-picker', $theme_uri . '/assets/css/component-product-variant-picker.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-related', $theme_uri . '/assets/css/section-related-products.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );

		// JS produit (galerie, quantity, variantes) + JS du PDP custom UA Stack.
		wp_enqueue_script( 'panstellar-product', $theme_uri . '/assets/js/product.js', array(), PANSTELLAR_VERSION, true );
		wp_enqueue_script( 'panstellar-uas-pdp', $theme_uri . '/assets/js/uas-pdp.js', array(), PANSTELLAR_VERSION, true );

		// Données pour le JS : endpoint AJAX WooCommerce (?wc-ajax=) + fragments.
		wp_localize_script(
			'panstellar-uas-pdp',
			'panstellarUas',
			array(
				'ajaxUrl'          => home_url( '/' ),
				'cartFragmentsUrl' => home_url( '/?wc-ajax=get_refreshed_fragments' ),
			)
		);

		// CSS du PDP custom UA Stack (si utilisé pour ce produit).
		if ( apply_filters( 'panstellar_use_uas_pdp', false, get_the_ID() ) ) {
			wp_enqueue_style( 'panstellar-uas-pdp', $theme_uri . '/assets/css/uas-pdp.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		}

		// JSON des variations pour product.js (galerie de sélection).
		$current_product = wc_get_product( get_the_ID() );
		if ( $current_product && $current_product->is_type( 'variable' ) ) {
			$variations = array_map(
				function ( $v ) {
					return array(
						'variation_id' => $v['variation_id'],
						'attributes'   => $v['attributes'],
						'display_price'=> $v['display_price'],
					);
				},
				$current_product->get_available_variations()
			);
			wp_add_inline_script(
				'panstellar-product',
				'var panstellarVariations = ' . wp_json_encode( $variations ) . ';',
				'before'
			);
		}
	}

	// ── Template collection / catalogue (conversion de templates/collection.json) ──
	if ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) {
		wp_enqueue_style( 'panstellar-collection', $theme_uri . '/assets/css/template-collection.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-collection-hero', $theme_uri . '/assets/css/component-collection-hero.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-facets', $theme_uri . '/assets/css/component-facets.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-show-more', $theme_uri . '/assets/css/component-show-more.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-swatch-input', $theme_uri . '/assets/css/component-swatch-input.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-swatch', $theme_uri . '/assets/css/component-swatch.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-card', $theme_uri . '/assets/css/component-card.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-mask-blobs', $theme_uri . '/assets/css/mask-blobs.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-quick-add', $theme_uri . '/assets/css/quick-add.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-pagination', $theme_uri . '/assets/css/component-pagination.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );

		// JS des facets (soumission filtres/tri, drawer mobile, show-more).
		wp_enqueue_script( 'panstellar-collection', $theme_uri . '/assets/js/collection.js', array(), PANSTELLAR_VERSION, true );

		// Données pour collection.js.
		wp_localize_script(
			'panstellar-collection',
			'panstellarCollection',
			array(
				'ajax' => true,
			)
		);
	}

	// ── Template recherche (conversion de templates/search.json) ──
	if ( is_search() ) {
		wp_enqueue_style( 'panstellar-search', $theme_uri . '/assets/css/component-search.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-collection', $theme_uri . '/assets/css/template-collection.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-card', $theme_uri . '/assets/css/component-card.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-article-card', $theme_uri . '/assets/css/component-article-card.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-facets', $theme_uri . '/assets/css/component-facets.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-pagination', $theme_uri . '/assets/css/component-pagination.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );

		// JS : facets (collection.js, réutilisé avec les mêmes IDs) + recherche.
		wp_enqueue_script( 'panstellar-collection', $theme_uri . '/assets/js/collection.js', array(), PANSTELLAR_VERSION, true );
		wp_enqueue_script( 'panstellar-search', $theme_uri . '/assets/js/search.js', array(), PANSTELLAR_VERSION, true );

		wp_localize_script(
			'panstellar-collection',
			'panstellarCollection',
			array(
				'ajax' => true,
			)
		);
	}

	// ── Page panier (conversion de templates/cart.json) ──
	if ( is_cart() ) {
		wp_enqueue_style( 'panstellar-cart', $theme_uri . '/assets/css/component-cart.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-cart-items', $theme_uri . '/assets/css/component-cart-items.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-totals', $theme_uri . '/assets/css/component-totals.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-discounts', $theme_uri . '/assets/css/component-discounts.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-quantity-popover', $theme_uri . '/assets/css/quantity-popover.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );

		wp_enqueue_script( 'panstellar-cart-page', $theme_uri . '/assets/js/cart-page.js', array(), PANSTELLAR_VERSION, true );

		wp_localize_script(
			'panstellar-cart-page',
			'panstellarCartPage',
			array(
				'ajaxUrl'  => home_url( '/' ),
				'adminUrl' => admin_url( 'admin-ajax.php' ),
			)
		);
	}

	// ── Blog (conversion de templates/blog.json) : home.php + single.php ──
	if ( is_home() ) {
		wp_enqueue_style( 'panstellar-main-blog', $theme_uri . '/assets/css/section-main-blog.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-article-card', $theme_uri . '/assets/css/component-article-card.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-card', $theme_uri . '/assets/css/component-card.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-pagination', $theme_uri . '/assets/css/component-pagination.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );

		// Padding dynamique (équivalent du {% style %} de main-blog.liquid).
		wp_add_inline_style( 'panstellar-main-blog', panstellar_blog_dynamic_css() );
	}

	// ── Article (conversion de templates/article.json) : single.php ──
	if ( is_singular( 'post' ) ) {
		wp_enqueue_style( 'panstellar-blog-post', $theme_uri . '/assets/css/section-blog-post.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );

		// JS du share-button (dépend de DetailsDisclosure de global.js Dawn).
		// NB : les styles .share-button__* vivent dans base.css (déjà chargé).
		wp_enqueue_script( 'panstellar-details-disclosure', $theme_uri . '/assets/js/details-disclosure.js', array(), PANSTELLAR_VERSION, true );
		wp_enqueue_script( 'panstellar-share', $theme_uri . '/assets/js/share.js', array( 'panstellar-details-disclosure' ), PANSTELLAR_VERSION, true );

		// window.accessibilityStrings.shareSuccess (requis par share.js).
		wp_localize_script(
			'panstellar-share',
			'accessibilityStrings',
			array(
				'shareSuccess' => __( 'Link copied to clipboard', 'panstellar' ),
			)
		);
	}

	// ── Pages classiques (conversion de templates/page.json) : page.php ──
	// NB : is_page() est exclu des pages WooCommerce (boutique, panier…) qui
	// ont leurs propres templates + enqueues. La boutique (is_shop) est aussi
	// un post type « page » → exclue explicitement.
	if ( is_page() && ! is_shop() && ! is_cart() && ! is_checkout() && ! is_account_page() && ! is_wc_endpoint_url() && ! is_page( array( 'contact', 'faq', 'about-us' ) ) ) {
		wp_enqueue_style( 'panstellar-main-page', $theme_uri . '/assets/css/section-main-page.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );

		// Padding dynamique (équivalent du {% style %} de main-page.liquid).
		wp_add_inline_style( 'panstellar-main-page', panstellar_page_dynamic_css() );
	}

	// ── Page contact (conversion de templates/page.contact.json) ──
	if ( is_page( 'contact' ) ) {
		wp_enqueue_style( 'panstellar-rich-text', $theme_uri . '/assets/css/section-rich-text.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
		wp_enqueue_style( 'panstellar-contact-form', $theme_uri . '/assets/css/section-contact-form.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );

		// Padding dynamique des deux sections.
		wp_add_inline_style(
			'panstellar-rich-text',
			'.section-rich-text-padding { padding-top: 30px; padding-bottom: 39px; }' .
			'@media screen and (min-width: 750px) { .section-rich-text-padding { padding-top: 40px; padding-bottom: 52px; } }'
		);
		wp_add_inline_style(
			'panstellar-contact-form',
			'.section-contact-form-padding { padding-top: 27px; padding-bottom: 27px; }' .
			'@media screen and (min-width: 750px) { .section-contact-form-padding { padding-top: 36px; padding-bottom: 36px; } }'
		);
	}

	// ── Page FAQ (conversion de templates/page.faq.json) ──
	if ( is_page( 'faq' ) ) {
		wp_enqueue_style( 'panstellar-accordion', $theme_uri . '/assets/css/component-accordion.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );
	}

	// ── Page À propos (conversion de templates/page.about-us.json) ──
	// NB : les styles des sections about sont en ligne dans le template
	// (blocs ai_gen reconstruits) — aucun CSS dédié requis.
	if ( is_page( 'about-us' ) ) {
		// Rien à enqueuer (styles inline + base.css).
	}

	// ── Page 404 (404.php, design Dawn par défaut) ──
	if ( is_404() ) {
		// Le formulaire de recherche Dawn (searchform.php) a besoin de
		// component-search.css, sinon le champ est non stylé.
		wp_enqueue_style( 'panstellar-search', $theme_uri . '/assets/css/component-search.css', array( 'panstellar-base' ), PANSTELLAR_VERSION );

		wp_add_inline_style(
			'panstellar-search',
			'.section-404-padding { padding: 8rem 0; text-align: center; }' .
			'.section-404-padding .search { max-width: 44rem; margin: 2rem auto 0; }' .
			'.section-404-padding .h0 { font-size: clamp(6rem, 16vw, 12rem); margin: 0 0 1rem; }'
		);
	}

	// JavaScript du header (StickyHeader, HeaderDrawer, MenuDrawer, modale de recherche).
	wp_enqueue_script( 'panstellar-header', $theme_uri . '/assets/js/header.js', array(), PANSTELLAR_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'panstellar_scripts' );

/* -------------------------------------------------------------------------
 * 8b. RECHERCHE (équivalent du search.performed / search.results de Shopify)
 *
 * - Inclut les produits WooCommerce dans la recherche (comme Shopify qui
 *   retourne produits + articles + pages).
 * - Applique le tri (orderby) et les filtres (filter_*, min/max price) —
 *   les mêmes paramètres GET que les facets.
 * ---------------------------------------------------------------------- */
function panstellar_search_pre_get_posts( $q ) {
	if ( ! is_search() || ! $q->is_main_query() || is_admin() ) {
		return;
	}

	// 1. Inclure les produits dans la recherche (post_type: product).
	$post_types = array( 'post', 'page', 'product' );
	$q->set( 'post_type', $post_types );

	// 2. Tri (valeurs Dawn de search.sort_options → paramètres WP).
	$price_sort_active = false;
	$orderby = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	switch ( $orderby ) {
		case 'title':
			$q->set( 'orderby', 'title' );
			$q->set( 'order', 'ASC' );
			break;
		case 'title-desc':
			$q->set( 'orderby', 'title' );
			$q->set( 'order', 'DESC' );
			break;
		case 'date':
			$q->set( 'orderby', 'date' );
			$q->set( 'order', 'DESC' );
			break;
		case 'price':
		case 'price-desc':
			$price_sort_active = true;
			$q->set( 'orderby', 'meta_value_num' );
			$q->set( 'meta_key', '_price' ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$q->set( 'order', 'price' === $orderby ? 'ASC' : 'DESC' );
			break;
	}

	// 3. Filtres : catégories (filter_product_cat, format layered nav).
	$meta_query = $q->get( 'meta_query' );
	if ( ! is_array( $meta_query ) ) {
		$meta_query = array();
	}
	$tax_query = $q->get( 'tax_query' );
	if ( ! is_array( $tax_query ) ) {
		$tax_query = array();
	}

	if ( isset( $_GET['filter_product_cat'] ) && '' !== $_GET['filter_product_cat'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$slugs = array_filter( array_map( 'sanitize_title', explode( ',', sanitize_text_field( wp_unslash( $_GET['filter_product_cat'] ) ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $slugs ) {
			$tax_query[] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => $slugs,
				'operator' => 'IN',
			);
		}
	}

	// 4. Filtre prix (min_price / max_price — convention WooCommerce).
	$price_filter_active = false;
	if ( isset( $_GET['min_price'] ) && '' !== $_GET['min_price'] && ! is_array( $_GET['min_price'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$price_filter_active = true;
		$meta_query[] = array(
			'key'     => '_price',
			'value'   => (float) wc_format_decimal( (string) wp_unslash( $_GET['min_price'] ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'compare' => '>=',
			'type'    => 'NUMERIC',
		);
	}
	if ( isset( $_GET['max_price'] ) && '' !== $_GET['max_price'] && ! is_array( $_GET['max_price'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$price_filter_active = true;
		$meta_query[] = array(
			'key'     => '_price',
			'value'   => (float) wc_format_decimal( (string) wp_unslash( $_GET['max_price'] ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'compare' => '<=',
			'type'    => 'NUMERIC',
		);
	}

	// Les filtres/tri prix ne s'appliquent qu'aux produits (comme les facets
	// Shopify) : on restreint le post_type pour éviter que le JOIN sur _price
	// n'élimine silencieusement les articles/pages des résultats.
	if ( $price_sort_active || $price_filter_active || ! empty( $tax_query ) ) {
		$q->set( 'post_type', 'product' );
	}

	if ( $meta_query ) {
		$q->set( 'meta_query', $meta_query ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	}
	if ( $tax_query ) {
		$q->set( 'tax_query', $tax_query ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	}
}
add_action( 'pre_get_posts', 'panstellar_search_pre_get_posts' );

/**
 * Blog : 6 articles par page (équivalent de « paginate blog.articles by 6 »
 * de main-blog.liquid) sur la page d'index du blog et les archives.
 *
 * @param WP_Query $q Requête.
 */
function panstellar_blog_pre_get_posts( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	// Uniquement les contextes blog : index du blog + archives de posts
	// (catégories, tags, dates, auteurs). NB : is_archive() couvrirait AUSSI
	// les archives produits WooCommerce — on les exclut explicitement.
	$is_blog_context = is_home() || is_category() || is_tag() || is_date() || is_author() || is_year() || is_month() || is_day() || is_time();
	if ( $is_blog_context && ! is_post_type_archive( 'product' ) && ! is_tax( 'product_cat' ) && ! is_tax( 'product_tag' ) ) {
		$q->set( 'posts_per_page', 6 );
	}
}
add_action( 'pre_get_posts', 'panstellar_blog_pre_get_posts' );

/**
 * CSS dynamique du footer (équivalent du bloc {% style %} de footer.liquid).
 */
function panstellar_footer_dynamic_css() {
	$margin_top     = (int) panstellar_footer_setting( 'margin_top' );
	$padding_top    = (int) panstellar_footer_setting( 'padding_top' );
	$padding_bottom = (int) panstellar_footer_setting( 'padding_bottom' );

	$css  = '.footer { margin-top: ' . (int) round( $margin_top * 0.75 ) . 'px; }' . "\n";
	$css .= '.section-footer-padding { padding-top: ' . (int) round( $padding_top * 0.75 ) . 'px; padding-bottom: ' . (int) round( $padding_bottom * 0.75 ) . 'px; }' . "\n";
	$css .= '@media screen and (min-width: 750px) {' . "\n";
	$css .= '  .footer { margin-top: ' . $margin_top . 'px; }' . "\n";
	$css .= '  .section-footer-padding { padding-top: ' . $padding_top . 'px; padding-bottom: ' . $padding_bottom . 'px; }' . "\n";
	$css .= '}' . "\n";

	return $css;
}

/**
 * CSS dynamique du header (équivalent du bloc {% style %}.header de header.liquid).
 */
function panstellar_header_dynamic_css() {
	$padding_top    = (int) panstellar_header_setting( 'padding_top' );
	$padding_bottom = (int) panstellar_header_setting( 'padding_bottom' );
	$margin_bottom  = (int) panstellar_header_setting( 'margin_bottom' );

	$half_top    = (int) round( $padding_top * 0.5 );
	$half_bottom = (int) round( $padding_bottom * 0.5 );
	$margin_75   = (int) round( $margin_bottom * 0.75 );

	$css  = '.header { padding: ' . $half_top . 'px 3rem ' . $half_bottom . 'px 3rem; }' . "\n";
	$css .= '.section-header { position: sticky; margin-bottom: ' . $margin_75 . 'px; }' . "\n";
	$css .= '@media screen and (min-width: 750px) { .section-header { margin-bottom: ' . $margin_bottom . 'px; } }' . "\n";
	$css .= '@media screen and (min-width: 990px) { .header { padding-top: ' . $padding_top . 'px; padding-bottom: ' . $padding_bottom . 'px; } }' . "\n";

	// Sticky « reduce-logo-size » (équivalent du style conditionnel de header.liquid).
	if ( 'reduce-logo-size' === panstellar_header_setting( 'sticky_header_type' ) ) {
		$css .= '.scrolled-past-header .header__heading-logo-wrapper { width: 75%; }' . "\n";
	}

	// Cacher le header-drawer sur desktop quand le menu n'est pas « drawer ».
	if ( 'drawer' !== panstellar_header_setting( 'menu_type_desktop' ) ) {
		$css .= '@media screen and (min-width: 990px) { header-drawer { display: none; } }' . "\n";
	}

	return $css;
}

/**
 * CSS dynamique de la page d'accueil.
 *
 * Équivalents des blocs {% style %} de slideshow.liquid / multicolumn.liquid
 * + styles du bloc AI « Featured Products » (reconstruit).
 */
function panstellar_home_dynamic_css() {
	$css = '';

	// ── Multicolumn : padding (équivalent du {% style %} section-{id}-padding) ──
	$mc_top    = (int) panstellar_home_setting( 'multicolumn_padding_top' );
	$mc_bottom = (int) panstellar_home_setting( 'multicolumn_padding_bottom' );
	$css .= '.home-multicolumn-padding { padding-top: ' . (int) round( $mc_top * 0.75 ) . 'px; padding-bottom: ' . (int) round( $mc_bottom * 0.75 ) . 'px; }' . "\n";
	$css .= '@media screen and (min-width: 750px) { .home-multicolumn-padding { padding-top: ' . $mc_top . 'px; padding-bottom: ' . $mc_bottom . 'px; } }' . "\n";

	// ── Featured Products (bloc AI reconstruit) ──
	$css .= '.home-featured-products { padding-top: ' . (int) panstellar_home_setting( 'featured_padding_top' ) . 'px; padding-bottom: ' . (int) panstellar_home_setting( 'featured_padding_bottom' ) . 'px; }' . "\n";
	$css .= '.home-featured-products .featured-carousel__list { gap: ' . (int) panstellar_home_setting( 'featured_card_gap' ) . 'px; }' . "\n";

	return $css;
}

/* -------------------------------------------------------------------------
 * 9. JSON-LD (équivalents des <script type="application/ld+json"> de header.liquid)
 * ---------------------------------------------------------------------- */
function panstellar_json_ld() {
	$name     = get_bloginfo( 'name' );
	$home     = panstellar_home_url();
	$logo_id  = (int) get_theme_mod( 'custom_logo' );
	$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';

	$org = array(
		'@context' => 'http://schema.org',
		'@type'    => 'Organization',
		'name'     => $name,
		'url'      => $home,
		'sameAs'   => array_values( panstellar_social_links() ),
	);
	if ( $logo_url ) {
		$org['logo'] = $logo_url;
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $org ) . '</script>' . "\n";

	// WebSite + SearchAction uniquement sur la page d'accueil (comme request.page_type == 'index').
	if ( is_front_page() ) {
		$site = array(
			'@context'        => 'http://schema.org',
			'@type'           => 'WebSite',
			'name'            => $name,
			'url'             => $home,
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => home_url( '/?s={search_term_string}' ),
				'query-input' => 'required name=search_term_string',
			),
		);
		echo '<script type="application/ld+json">' . wp_json_encode( $site ) . '</script>' . "\n";
	}
}
add_action( 'wp_head', 'panstellar_json_ld' );
