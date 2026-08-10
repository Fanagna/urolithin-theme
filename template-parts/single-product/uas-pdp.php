<?php
/**
 * PDP UA Stack — template-parts/single-product/uas-pdp.php
 *
 * Conversion de sections/uas-pdp.liquid (PDP custom « UA Stack »).
 * Le HTML, les classes CSS (uas-pdp.css) et le responsive sont conservés.
 *
 * Correspondances Shopify → WordPress :
 *   - selling_plan (15/23/31 %) → WooCommerce Subscriptions (guards) avec
 *     fallback sur les settings (prix texte de la section).
 *   - {% form 'product' %} → form WooCommerce (add-to-cart + subscription).
 *   - image_url / asset_url → panstellar_uas_image() (assets/images/uas/).
 *   - AJAX /cart/add.js → endpoint WooCommerce (wc-ajax=add_to_cart) via JS.
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product = isset( $GLOBALS['product'] ) ? $GLOBALS['product'] : wc_get_product( get_the_ID() );
if ( ! $product ) {
	return;
}

// ── Réglages de la section (équivalents de section.settings.*) ───────────
$settings = apply_filters(
	'panstellar_uas_pdp_settings',
	array(
		'pill_text'     => 'Liposomal · 4-Active Stack',
		'hero_title'    => 'Power<br>You Can Feel.',
		'hero_subtitle' => 'Liposomal Urolithin A derived from pomegranate.',
		'card_title'    => 'Urolithin A — Single-Active',
		'onetime_label' => 'One-time purchase',
		'sub_label'     => 'Subscribe & Save 15%',
		'sub_period'    => 'Ships every 90 days • Cancel anytime',
		'sub_price'     => '$118.15',
		'sub_unit'      => 'delivery',
		'benefit_1'     => '45 Servings',
		'benefit_2'     => 'Single-Active',
		'benefit_3'     => 'Liposomal',
		'benefit_4'     => 'No Blend',
		'benefit_5'     => '90-Day Guarantee',
		'cta_text'      => 'ADD TO CART',
		'shipping_text' => 'Free U.S. shipping · 1-2 day delivery',
		'plan_3m_price' => '$118.15',
		'plan_3m_off'   => '15% OFF',
		'plan_6m_price' => '$107.03',
		'plan_6m_off'   => '23% OFF',
		'plan_12m_price'=> '$95.91',
		'plan_12m_off'  => '31% OFF',
		'lab_url'       => '',
		'guarantee_url' => '',
		'library_url'   => '',
	)
);

// ── Données produit WooCommerce ──────────────────────────────────────────
$product_id     = $product->get_id();
$product_title  = $product->get_name();
$price          = $product->get_price();
$price_html     = $product->get_price_html();
$is_available   = $product->is_in_stock() && $product->is_purchasable();
$product_url    = get_permalink( $product_id );

// Variantes (si produit variable).
$is_variable  = $product->is_type( 'variable' );
$variants     = array();
$default_vid  = 0;
if ( $is_variable ) {
	foreach ( $product->get_available_variations() as $variation ) {
		$variants[] = array(
			'id'        => $variation['variation_id'],
			'title'     => $variation['attributes_html'] ? wp_strip_all_tags( $variation['attributes_html'] ) : $product_title,
			'available' => $variation['is_in_stock'],
		);
	}
	// Variante par défaut : on privilégie une variante disponible.
	$children = $product->get_children();
	$default_vid = 0;
	foreach ( $children as $child_id ) {
		$child = wc_get_product( $child_id );
		if ( $child && $child->is_in_stock() ) {
			$default_vid = (int) $child_id;
			break;
		}
	}
	if ( ! $default_vid && ! empty( $children ) ) {
		$default_vid = (int) reset( $children );
	}
}

// ── Abonnements : WooCommerce Subscriptions (guards) ─────────────────────
$has_subscriptions = function_exists( 'wcs_product_has_subscription' ) || class_exists( 'WC_Subscriptions_Product' );
$plan_15 = array( 'price' => $settings['plan_3m_price'], 'off' => $settings['plan_3m_off'], 'label' => '3 MONTHS (SAVE 15%)' );
$plan_23 = array( 'price' => $settings['plan_6m_price'], 'off' => $settings['plan_6m_off'], 'label' => '6 MONTHS (SAVE 23%)' );
$plan_31 = array( 'price' => $settings['plan_12m_price'], 'off' => $settings['plan_12m_off'], 'label' => '12 MONTHS (SAVE 31%)' );

// Helper image : asset Shopify → assets/images/uas/ du thème.
$uas_img = function ( $name, $alt = '', $width = '', $height = '', $extra = '' ) {
	$path = get_template_directory() . '/assets/images/uas/' . $name;
	$url  = get_template_directory_uri() . '/assets/images/uas/' . $name;
	if ( ! file_exists( $path ) ) {
		return '';
	}
	$attrs = '';
	if ( $alt ) {
		$attrs .= ' alt="' . esc_attr( $alt ) . '"';
	}
	if ( $width ) {
		$attrs .= ' width="' . esc_attr( $width ) . '"';
	}
	if ( $height ) {
		$attrs .= ' height="' . esc_attr( $height ) . '"';
	}
	if ( $extra ) {
		$attrs .= ' ' . $extra;
	}
	return '<img src="' . esc_url( $url ) . '"' . $attrs . '>';
};
?>

<div class="uas-pdp" id="uas-pdp-<?php echo esc_attr( $product_id ); ?>">

	<!-- ══ HERO ══ -->
	<section class="uas-hero" id="uas-buy-<?php echo esc_attr( $product_id ); ?>">

		<picture class="uas-hero__bg-picture">
			<source media="(max-width: 767px)" srcset="<?php echo esc_url( get_template_directory_uri() . '/assets/images/uas/uas-hero-mobile.webp' ); ?>">
			<?php echo $uas_img( 'uas-hero.webp', '', '1920', '820', 'class="uas-hero__bg" loading="eager"' ); // phpcs:ignore WordPress.Security.EscapeOutput -- helper échappe. ?>
		</picture>

		<div class="uas-pill"><?php echo esc_html( $settings['pill_text'] ); ?></div>

		<div class="uas-wrap uas-hero__grid">

			<div class="uas-hero__copy">
				<h1><?php echo wp_kses_post( $settings['hero_title'] ); ?></h1>
				<p><?php echo esc_html( $settings['hero_subtitle'] ); ?></p>
				<div class="uas-hero__icons" aria-label="Points clés produit">
					<div><?php echo $uas_img( 'uas-icon-flask.png', 'Third-party tested', '34', '34' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span>Third-Party<br>Tested</span></div>
					<div><?php echo $uas_img( 'uas-icon-factory.png', 'cGMP Manufactured', '34', '34' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span>cGMP<br>Manufactured</span></div>
					<div><?php echo $uas_img( 'uas-icon-coa-document.png', 'COA By Batch', '34', '34' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span>COA<br>By Batch</span></div>
					<div><?php echo $uas_img( 'uas-icon-leaf.png', 'Non-GMO', '34', '34' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span>Non-GMO</span></div>
				</div>
			</div>

			<!-- BUY CARD -->
			<aside class="uas-buy-card">
				<h2><?php echo esc_html( $settings['card_title'] ); ?></h2>

				<?php if ( count( $variants ) > 1 ) : ?>
					<div class="uas-variant-select">
						<select id="uas-variant-<?php echo esc_attr( $product_id ); ?>" aria-label="<?php esc_attr_e( 'Variant', 'panstellar' ); ?>">
							<?php foreach ( $variants as $variant ) : ?>
								<option
									value="<?php echo esc_attr( $variant['id'] ); ?>"
									data-price="<?php echo esc_attr( $product->get_price() ? wc_price( $product->get_price() ) : $price_html ); ?>"
									data-available="<?php echo $variant['available'] ? 'true' : 'false'; ?>"
									<?php selected( $variant['id'], $default_vid ); ?>
								>
									<?php echo esc_html( $variant['title'] . ( $variant['available'] ? '' : ' — Épuisé' ) ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>

				<!-- One-time -->
				<div class="uas-choice" id="uas-choice-once-<?php echo esc_attr( $product_id ); ?>" role="button" tabindex="0" aria-label="<?php esc_attr_e( 'One-time purchase', 'panstellar' ); ?>">
					<div class="uas-choice__head">
						<span class="uas-radio" aria-hidden="true"></span>
						<div>
							<div class="uas-choice__title"><?php echo esc_html( $settings['onetime_label'] ); ?></div>
							<div class="uas-choice__price"><span id="uas-price-<?php echo esc_attr( $product_id ); ?>"><?php echo wp_kses_post( $price_html ); ?></span></div>
						</div>
					</div>
				</div>

				<!-- Subscribe -->
				<div class="uas-choice uas-choice--active" id="uas-choice-sub-<?php echo esc_attr( $product_id ); ?>" role="button" tabindex="0" aria-label="<?php esc_attr_e( 'Subscription', 'panstellar' ); ?>">
					<span class="uas-popular">MOST POPULAR</span>
					<div class="uas-choice__head">
						<span class="uas-radio" aria-hidden="true"></span>
						<div>
							<div class="uas-choice__title" style="color:#9a0010"><?php echo esc_html( $settings['sub_label'] ); ?></div>
							<div class="uas-choice__ship"><?php echo esc_html( $settings['sub_period'] ); ?></div>
							<div class="uas-choice__price"><?php echo esc_html( $settings['sub_price'] ); ?> <small>/ <?php echo esc_html( $settings['sub_unit'] ); ?></small></div>
						</div>
					</div>
				</div>

				<!-- Benefits -->
				<div class="uas-benefit-grid">
					<div class="uas-benefit"><?php echo $uas_img( 'uas-icon-medal.png', '45 servings', '15', '15' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $settings['benefit_1'] ); ?></span></div>
					<div class="uas-benefit"><?php echo $uas_img( 'uas-icon-molecule.png', 'Single active', '15', '15' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $settings['benefit_2'] ); ?></span></div>
					<div class="uas-benefit"><?php echo $uas_img( 'uas-icon-liposomal.png', 'Liposomal', '15', '15' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $settings['benefit_3'] ); ?></span></div>
					<div class="uas-benefit"><?php echo $uas_img( 'uas-icon-no-blend.png', 'No blend', '15', '15' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $settings['benefit_4'] ); ?></span></div>
					<div class="uas-benefit"><?php echo $uas_img( 'uas-icon-shield.png', 'Guarantee', '15', '15' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $settings['benefit_5'] ); ?></span></div>
				</div>

				<?php $uas_form_id = 'uas-form-' . $product_id; ?>
				<form
					action="<?php echo esc_url( $product_url ); ?>"
					method="post"
					enctype="multipart/form-data"
					id="<?php echo esc_attr( $uas_form_id ); ?>"
					class="uas-atc-form"
					data-product-id="<?php echo esc_attr( $product_id ); ?>"
					data-variant-id="<?php echo esc_attr( $default_vid ); ?>"
				>
					<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product_id ); ?>">
					<input type="hidden" name="quantity" value="1">
					<?php if ( $is_variable ) : ?>
						<input type="hidden" name="variation_id" class="variation_id" value="<?php echo esc_attr( $default_vid ); ?>">
					<?php endif; ?>
					<?php if ( $has_subscriptions ) : ?>
						<?php do_action( 'panstellar_uas_subscription_field', $product ); ?>
					<?php else : ?>
						<input type="hidden" name="subscription_plan" id="uas-hero-sp-<?php echo esc_attr( $product_id ); ?>" value="15">
					<?php endif; ?>
					<button type="submit" name="add" class="uas-cta" id="uas-cta-<?php echo esc_attr( $product_id ); ?>" <?php echo $is_available ? '' : 'disabled'; ?>>
						<?php echo $is_available ? esc_html__( 'SUBSCRIBE &amp; SAVE', 'panstellar' ) : esc_html__( 'Sold Out', 'panstellar' ); ?>
					</button>
				</form>

				<div class="uas-card-foot">
					<span><?php echo $uas_img( 'uas-icon-lock.svg', '', '17', '17' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Secure Checkout', 'panstellar' ); ?></span>
					<span><?php echo $uas_img( 'uas-icon-clock.svg', '', '17', '17' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Cancel Anytime', 'panstellar' ); ?></span>
				</div>

				<div class="uas-shipping">
					<?php echo $uas_img( 'uas-icon-truck.webp', 'Shipping', '20', '20' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php echo esc_html( $settings['shipping_text'] ); ?>
				</div>

				<!-- Visual accordions -->
				<div class="uas-accordion">

					<!-- Supports -->
					<div class="uas-acc-row">
						<button class="uas-acc-btn" type="button" aria-expanded="false">
							<span class="uas-acc-label">
								<?php echo $uas_img( 'uas-icon-heart.png', '', '31', '31' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								Supports
							</span>
							<span class="uas-acc-note">Designed to support.</span>
							<span class="uas-acc-chevron" aria-hidden="true">⌄</span>
						</button>
						<div class="uas-acc-panel">
							<div class="uas-support-grid">
								<div class="uas-support-card"><?php echo $uas_img( 'uas-icon-bolt.webp', '', '34', '34' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span>Felt energy</span></div>
								<div class="uas-support-card"><?php echo $uas_img( 'uas-icon-battery.webp', '', '34', '34' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span>Less afternoon slump</span></div>
								<div class="uas-support-card"><?php echo $uas_img( 'uas-icon-brain.webp', '', '34', '34' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span>Daily focus</span></div>
								<div class="uas-support-card"><?php echo $uas_img( 'uas-icon-leaf-circle.webp', '', '34', '34' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span>Healthy aging</span></div>
							</div>
						</div>
					</div>

					<!-- Ingredients -->
					<div class="uas-acc-row">
						<button class="uas-acc-btn" type="button" aria-expanded="false">
							<span class="uas-acc-label">
								<?php echo $uas_img( 'uas-acc-title-flask.webp', '', '31', '31' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								Ingredients
							</span>
							<span class="uas-acc-note">Full list inside.</span>
							<span class="uas-acc-chevron" aria-hidden="true">⌄</span>
						</button>
						<div class="uas-acc-panel">
							<div class="uas-ing-acc-grid">
								<div class="uas-ing-acc-card"><?php echo $uas_img( 'uas-acc-ing-urolithin.webp', '', '48', '48' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span>Urolithin A</span></div>
								<div class="uas-ing-acc-card"><?php echo $uas_img( 'uas-acc-ing-liposomal.webp', '', '48', '48' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span>Liposomal</span></div>
								<div class="uas-ing-acc-card"><?php echo $uas_img( 'uas-acc-ing-pomegranate.webp', '', '48', '48' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span>Derived from<br>pomegranate</span></div>
								<div class="uas-ing-acc-card"><?php echo $uas_img( 'uas-acc-ing-2000.webp', '', '48', '48' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span>2000 mg</span></div>
								<div class="uas-ing-acc-card"><?php echo $uas_img( 'uas-acc-ing-capsule.webp', '', '48', '48' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span>90 capsules</span></div>
								<div class="uas-ing-acc-card"><?php echo $uas_img( 'uas-acc-ing-45.png', '', '48', '48' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span>45 servings</span></div>
							</div>
						</div>
					</div>

					<!-- Certificates -->
					<div class="uas-acc-row">
						<button class="uas-acc-btn" type="button" aria-expanded="false">
							<span class="uas-acc-label">
								<?php echo $uas_img( 'uas-acc-title-shield.webp', '', '31', '31' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								Certificates
							</span>
							<span class="uas-acc-note">Verified quality. Total transparency.</span>
							<span class="uas-acc-chevron" aria-hidden="true">⌄</span>
						</button>
						<div class="uas-acc-panel">
							<div class="uas-cert-layout">
								<div class="uas-cert-icons">
									<div><?php echo $uas_img( 'uas-acc-cert-thirdparty.webp', '', '26', '26' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span>Third-Party Tested</span></div>
									<div><?php echo $uas_img( 'uas-acc-cert-coa.png', '', '26', '26' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span>COA available by batch</span></div>
									<div><?php echo $uas_img( 'uas-acc-cert-cgmp.png', '', '26', '26' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span>cGMP</span></div>
									<div><?php echo $uas_img( 'uas-acc-cert-usa.png', '', '26', '26' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span>USA</span></div>
								</div>
								<?php echo $uas_img( 'uas-acc-cert-paper.png', 'Certificate of Analysis', '', '', 'class="uas-coa-paper" loading="lazy"' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</div>
						</div>
					</div>

				</div><!-- /.uas-accordion -->
			</aside><!-- /.uas-buy-card -->

		</div><!-- /.uas-hero__grid -->
	</section><!-- /.uas-hero -->

	<!-- ══ ICON STRIP ══ -->
	<section class="uas-strip">
		<div class="uas-wrap">
			<div class="uas-strip__grid">
				<div class="uas-strip__item"><?php echo $uas_img( 'uas-icon-bolt.webp', 'Felt energy', '42', '42' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><b>Felt<br>energy</b></div>
				<div class="uas-strip__item"><?php echo $uas_img( 'uas-icon-battery.webp', 'Less afternoon slump', '42', '42' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><b>Less afternoon<br>slump</b></div>
				<div class="uas-strip__item"><?php echo $uas_img( 'uas-icon-brain.webp', 'Daily focus', '42', '42' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><b>Daily<br>focus</b></div>
				<div class="uas-strip__item"><?php echo $uas_img( 'uas-icon-leaf-circle.webp', 'Healthy aging', '42', '42' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><b>Healthy<br>aging</b></div>
			</div>
			<div class="uas-strip__cta">
				<?php esc_html_e( 'Power you can feel. The 4-Active Stack.', 'panstellar' ); ?>
				<a href="#uas-buy-<?php echo esc_attr( $product_id ); ?>"><?php esc_html_e( 'Add to Cart', 'panstellar' ); ?> →</a>
			</div>
		</div>
	</section>

	<!-- ══ DELIVERY SECTION ══ -->
	<section class="uas-delivery">
		<div class="uas-delivery__copy">
			<h2><?php esc_html_e( 'Most of It', 'panstellar' ); ?><br><?php esc_html_e( 'Is Wasted.', 'panstellar' ); ?></h2>
			<p><?php esc_html_e( 'Liposomal delivery protects every dose.', 'panstellar' ); ?></p>
			<?php if ( $settings['library_url'] ) : ?>
				<a href="<?php echo esc_url( $settings['library_url'] ); ?>"><?php esc_html_e( 'View library', 'panstellar' ); ?> →</a>
			<?php endif; ?>
		</div>
		<div class="uas-step">
			<?php echo $uas_img( 'uas-ingredient-5.webp', 'Encapsulated', '', '84', 'loading="lazy"' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div><strong><?php esc_html_e( '1. Encapsulated', 'panstellar' ); ?></strong><span><?php esc_html_e( 'Each active is wrapped in a lipid shield.', 'panstellar' ); ?></span></div>
		</div>
		<div class="uas-step">
			<?php echo $uas_img( 'uas-ingredient-liposome.webp', 'Protected', '', '84', 'loading="lazy"' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div><strong><?php esc_html_e( '2. Protected', 'panstellar' ); ?></strong><span><?php esc_html_e( 'Shield protects actives from stomach acids.', 'panstellar' ); ?></span></div>
		</div>
		<div class="uas-step">
			<?php echo $uas_img( 'uas-icon-truck.webp', 'Delivered', '', '84', 'loading="lazy"' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div><strong><?php esc_html_e( '3. Delivered', 'panstellar' ); ?></strong><span><?php esc_html_e( 'Safely reaches the intestinal lining.', 'panstellar' ); ?></span></div>
		</div>
		<div class="uas-step">
			<?php echo $uas_img( 'uas-ingredient-1.webp', 'Absorbed', '', '84', 'loading="lazy"' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div><strong><?php esc_html_e( '4. Absorbed', 'panstellar' ); ?></strong><span><?php esc_html_e( 'Actives released for maximum use.', 'panstellar' ); ?></span></div>
		</div>
	</section>

	<!-- ══ INGREDIENT STACK + VS COMPARISON ══ -->
	<section class="uas-stack-compare">
		<div class="uas-stack-compare__inner">
			<section class="uas-stack">
				<div class="uas-stack__box">
					<h2 class="uas-section-title"><?php esc_html_e( 'The 4-Active Stack.', 'panstellar' ); ?></h2>
					<div class="uas-ingredient-cards">
						<div class="uas-ing-card uas-ing-card--active">
							<div class="uas-ing-card__img"><?php echo $uas_img( 'uas-ingredient-3.webp', 'Urolithin A', '', '', 'loading="lazy"' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
							<h3>Urolithin A</h3><b>2000mg</b><p><?php esc_html_e( 'Mitochondrial renewal', 'panstellar' ); ?></p>
						</div>
						<div class="uas-ing-card">
							<div class="uas-ing-card__img"><?php echo $uas_img( 'uas-ingredient-nmn.webp', 'NMN', '', '', 'loading="lazy"' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
							<h3>NMN</h3><b>1000mg</b><p><?php esc_html_e( 'NAD+ precursor', 'panstellar' ); ?></p>
						</div>
						<div class="uas-ing-card">
							<div class="uas-ing-card__img"><?php echo $uas_img( 'uas-ingredient-2.webp', 'Trans-resveratrol', '', '', 'loading="lazy"' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
							<h3>Trans-resveratrol</h3><b>500mg</b><p><?php esc_html_e( 'Sirtuin support', 'panstellar' ); ?></p>
						</div>
						<div class="uas-ing-card">
							<div class="uas-ing-card__img"><?php echo $uas_img( 'uas-ingredient-4.webp', 'BioPerine', '', '', 'loading="lazy"' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
							<h3>BioPerine®</h3><b>10mg</b><p><?php esc_html_e( 'Absorption boost', 'panstellar' ); ?></p>
						</div>
					</div>
					<div class="uas-stack__link">
						<?php esc_html_e( 'Get the stack', 'panstellar' ); ?> <span aria-hidden="true">→</span>
						<a href="#uas-buy-<?php echo esc_attr( $product_id ); ?>"><?php esc_html_e( 'Add to Cart', 'panstellar' ); ?></a>
					</div>
				</div>
			</section>

			<section class="uas-compare" aria-label="<?php esc_attr_e( 'Product comparison', 'panstellar' ); ?>">
				<div class="uas-compare__side uas-compare__side--left">
					<?php echo $uas_img( 'uas-packshot.webp', $product_title, '760', '900', 'class="uas-compare__product" loading="lazy"' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<h3><?php esc_html_e( 'Panstellar Urolithin Liposomal', 'panstellar' ); ?></h3>
					<div class="uas-compare__list">
						<div><span class="uas-check" aria-hidden="true">✓</span><?php esc_html_e( 'Single-Active (No Blend)', 'panstellar' ); ?></div>
						<div><span class="uas-check" aria-hidden="true">✓</span><?php esc_html_e( 'Liposomal Delivery', 'panstellar' ); ?></div>
						<div><span class="uas-check" aria-hidden="true">✓</span><?php esc_html_e( 'Clinically Dosed 2000mg', 'panstellar' ); ?></div>
						<div><span class="uas-check" aria-hidden="true">✓</span><?php esc_html_e( 'COA Verified', 'panstellar' ); ?></div>
						<div><span class="uas-check" aria-hidden="true">✓</span><?php esc_html_e( 'Third-Party Tested', 'panstellar' ); ?></div>
						<div><span class="uas-check" aria-hidden="true">✓</span><?php esc_html_e( '90-Day Guarantee', 'panstellar' ); ?></div>
					</div>
				</div>
				<div class="uas-vs-col" aria-hidden="true"><div class="uas-vs">VS</div></div>
				<div class="uas-compare__side">
					<?php echo $uas_img( 'uas-comparison-bottles.webp', 'Typical supplement approach', '920', '600', 'class="uas-compare__bottles" loading="lazy"' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<h3><?php esc_html_e( 'Typical Approach', 'panstellar' ); ?></h3>
					<div class="uas-compare__list">
						<div><span class="uas-cross" aria-hidden="true">×</span><?php esc_html_e( 'Multi-Ingredient Blends', 'panstellar' ); ?></div>
						<div><span class="uas-cross" aria-hidden="true">×</span><?php esc_html_e( 'No Liposomal Delivery', 'panstellar' ); ?></div>
						<div><span class="uas-cross" aria-hidden="true">×</span><?php esc_html_e( 'Underdosed', 'panstellar' ); ?></div>
						<div><span class="uas-cross" aria-hidden="true">×</span><?php esc_html_e( 'COA Varies', 'panstellar' ); ?></div>
						<div><span class="uas-cross" aria-hidden="true">×</span><?php esc_html_e( 'Testing Often Missing', 'panstellar' ); ?></div>
						<div><span class="uas-cross" aria-hidden="true">×</span><?php esc_html_e( 'No Guarantee', 'panstellar' ); ?></div>
					</div>
				</div>
			</section>
		</div>
	</section>

	<!-- ══ INGREDIENTS ══ -->
	<section class="uas-ingredients">
		<div class="uas-wrap uas-ing-grid">
			<div class="uas-ing-title"><h2><?php esc_html_e( 'From Nature,', 'panstellar' ); ?><br><?php esc_html_e( 'Backed by Science.', 'panstellar' ); ?></h2></div>
			<div class="uas-ing-scroll">
				<div class="uas-ing-card"><?php echo $uas_img( 'uas-ingredient-pomegranate.webp', 'Pomegranate', '136', '86', 'loading="lazy"' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><b><?php esc_html_e( 'Urolithin A', 'panstellar' ); ?><br>(2000mg)</b><small><?php esc_html_e( 'Derived from', 'panstellar' ); ?><br><?php esc_html_e( 'pomegranate', 'panstellar' ); ?></small></div>
				<div class="uas-ing-card"><?php echo $uas_img( 'uas-ingredient-phosphatidylcholine.webp', 'Phosphatidylcholine', '136', '86', 'loading="lazy"' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><b><?php esc_html_e( 'Phosphatidylcholine', 'panstellar' ); ?><br>(<?php esc_html_e( 'Liposomal Matrix', 'panstellar' ); ?>)</b><small><?php esc_html_e( 'Supports delivery', 'panstellar' ); ?></small></div>
				<div class="uas-ing-card"><?php echo $uas_img( 'uas-ingredient-sunflower.webp', 'Sunflower lecithin', '136', '86', 'loading="lazy"' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><b><?php esc_html_e( 'Sunflower Lecithin', 'panstellar' ); ?><br>(<?php esc_html_e( 'Helps encapsulation', 'panstellar' ); ?>)</b><small><?php esc_html_e( 'Plant-based carrier', 'panstellar' ); ?></small></div>
				<div class="uas-ing-card"><?php echo $uas_img( 'uas-ingredient-rice-flour.webp', 'Rice flour', '136', '86', 'loading="lazy"' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><b><?php esc_html_e( 'Rice Flour', 'panstellar' ); ?><br>(<?php esc_html_e( 'Filler', 'panstellar' ); ?>)</b><small><?php esc_html_e( 'Clean & simple', 'panstellar' ); ?></small></div>
				<div class="uas-ing-card"><?php echo $uas_img( 'uas-ingredient-vegetable-capsule.webp', 'Vegetable capsule', '136', '86', 'loading="lazy"' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><b><?php esc_html_e( 'Vegetable Capsule', 'panstellar' ); ?><br>(<?php esc_html_e( 'Vegan', 'panstellar' ); ?>)</b><small><?php esc_html_e( 'Plant-based capsule', 'panstellar' ); ?></small></div>
			</div>
		</div>
	</section>

	<!-- ══ QUALITY ══ -->
	<section class="uas-quality">
		<div class="uas-wrap uas-quality-grid">
			<h2><?php esc_html_e( 'Quality You', 'panstellar' ); ?><br><?php esc_html_e( 'Can Trust.', 'panstellar' ); ?></h2>
			<div class="uas-quality-icons">
				<div class="uas-q-item"><?php echo $uas_img( 'uas-icon-coa-document.png', 'COA', '34', '34' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><div><b><?php esc_html_e( 'COA by Batch', 'panstellar' ); ?></b><small><?php esc_html_e( 'Transparency you can see', 'panstellar' ); ?></small></div></div>
				<div class="uas-q-item"><?php echo $uas_img( 'uas-icon-factory.png', 'cGMP', '34', '34' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><div><b><?php esc_html_e( 'cGMP', 'panstellar' ); ?></b><small><?php esc_html_e( 'Quality you can trust', 'panstellar' ); ?></small></div></div>
				<div class="uas-q-item"><?php echo $uas_img( 'uas-icon-flask.png', 'Purity tested', '34', '34' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><div><b><?php esc_html_e( 'Purity Tested', 'panstellar' ); ?></b><small><?php esc_html_e( 'Third-party verified', 'panstellar' ); ?></small></div></div>
				<div class="uas-q-item"><?php echo $uas_img( 'uas-icon-shield.png', 'Made in USA', '34', '34' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><div><b><?php esc_html_e( 'Made in USA', 'panstellar' ); ?></b><small><?php esc_html_e( 'With globally sourced ingredients', 'panstellar' ); ?></small></div></div>
			</div>
			<div class="uas-lab-card">
				<?php echo $uas_img( 'uas-lab-results.webp', 'Lab results', '720', '400', 'loading="lazy"' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<div>
					<h3><?php esc_html_e( 'Lab results.', 'panstellar' ); ?><br><?php esc_html_e( 'Real data.', 'panstellar' ); ?></h3>
					<?php if ( $settings['lab_url'] ) : ?>
						<a href="<?php echo esc_url( $settings['lab_url'] ); ?>"><?php esc_html_e( 'View library', 'panstellar' ); ?> →</a>
					<?php else : ?>
						<span style="color:#e9c488;font-size:13px;font-weight:800;"><?php esc_html_e( 'View library', 'panstellar' ); ?> →</span>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>

	<!-- ══ SUBSCRIBE / PLANS ══ -->
	<section class="uas-subscribe" id="uas-plans-<?php echo esc_attr( $product_id ); ?>">
		<div class="uas-wrap uas-sub-grid">
			<div class="uas-sub-left">
				<h2><?php esc_html_e( 'Commit. Save.', 'panstellar' ); ?><br><?php esc_html_e( 'Skip Anytime.', 'panstellar' ); ?></h2>
				<ul class="uas-sub-list">
					<li><?php esc_html_e( 'Subscribe & save on every order', 'panstellar' ); ?></li>
					<li><?php esc_html_e( 'Ships every 90 days', 'panstellar' ); ?></li>
					<li><?php esc_html_e( 'Cancel or pause anytime', 'panstellar' ); ?></li>
					<li><?php esc_html_e( 'COA access included', 'panstellar' ); ?></li>
				</ul>
			</div>

			<div class="uas-plans" data-product-id="<?php echo esc_attr( $product_id ); ?>">
				<div class="uas-plan uas-plan--popular uas-plan--selected" data-plan-id="15" role="button" tabindex="0" aria-pressed="true">
					<div class="uas-plan__tag">MOST POPULAR</div>
					<h3><?php esc_html_e( '3 Months', 'panstellar' ); ?></h3>
					<small><?php esc_html_e( '1 bottle • 2 refills', 'panstellar' ); ?></small>
					<div class="uas-plan__monthly"><?php echo esc_html( $plan_15['price'] ); ?> / <?php esc_html_e( 'delivery', 'panstellar' ); ?></div>
					<div class="uas-plan__off"><?php echo esc_html( $plan_15['off'] ); ?></div>
				</div>
				<div class="uas-plan" data-plan-id="23" role="button" tabindex="0" aria-pressed="false">
					<h3><?php esc_html_e( '6 Months', 'panstellar' ); ?></h3>
					<small><?php esc_html_e( '1 bottle • 5 refills', 'panstellar' ); ?></small>
					<div class="uas-plan__monthly"><?php echo esc_html( $plan_23['price'] ); ?> / <?php esc_html_e( 'delivery', 'panstellar' ); ?></div>
					<div class="uas-plan__off"><?php echo esc_html( $plan_23['off'] ); ?></div>
				</div>
				<div class="uas-plan" data-plan-id="31" role="button" tabindex="0" aria-pressed="false">
					<h3><?php esc_html_e( '12 Months', 'panstellar' ); ?></h3>
					<small><?php esc_html_e( '1 bottle • 11 refills', 'panstellar' ); ?></small>
					<div class="uas-plan__monthly"><?php echo esc_html( $plan_31['price'] ); ?> / <?php esc_html_e( 'delivery', 'panstellar' ); ?></div>
					<div class="uas-plan__off"><?php echo esc_html( $plan_31['off'] ); ?></div>
				</div>
			</div>

			<div class="uas-lock">
				<h3><?php esc_html_e( 'Lock your routine.', 'panstellar' ); ?></h3>
				<?php $uas_sub_form_id = 'uas-sub-form-' . $product_id; ?>
				<form
					action="<?php echo esc_url( $product_url ); ?>"
					method="post"
					enctype="multipart/form-data"
					id="<?php echo esc_attr( $uas_sub_form_id ); ?>"
					class="uas-atc-form"
					data-product-id="<?php echo esc_attr( $product_id ); ?>"
					data-variant-id="<?php echo esc_attr( $default_vid ); ?>"
				>
					<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product_id ); ?>">
					<input type="hidden" name="quantity" value="1">
					<?php if ( $is_variable ) : ?>
						<input type="hidden" name="variation_id" class="variation_id" value="<?php echo esc_attr( $default_vid ); ?>">
					<?php endif; ?>
					<?php if ( $has_subscriptions ) : ?>
						<?php do_action( 'panstellar_uas_subscription_field', $product ); ?>
					<?php else : ?>
						<input type="hidden" name="subscription_plan" id="uas-selling-plan-<?php echo esc_attr( $product_id ); ?>" value="15">
					<?php endif; ?>
					<button type="submit" name="add" class="uas-cta" id="uas-sub-btn-<?php echo esc_attr( $product_id ); ?>" <?php echo $is_available ? '' : 'disabled'; ?>>
						<?php echo $is_available ? esc_html__( 'SUBSCRIBE — 3 MONTHS (SAVE 15%)', 'panstellar' ) : esc_html__( 'Sold Out', 'panstellar' ); ?>
					</button>
				</form>
				<small><?php esc_html_e( 'Cancel or skip anytime', 'panstellar' ); ?></small>
			</div>
		</div>

		<div class="uas-wrap uas-bottom-row">
			<div class="uas-guarantee">
				<div class="uas-seal">90</div>
				<div>
					<h3><?php esc_html_e( '90 Days. No Risk.', 'panstellar' ); ?></h3>
					<p><?php esc_html_e( 'Not a fit? Full refund.', 'panstellar' ); ?></p>
					<?php if ( $settings['guarantee_url'] ) : ?>
						<p><a href="<?php echo esc_url( $settings['guarantee_url'] ); ?>" style="color:var(--gold);"><?php esc_html_e( 'View guarantee details', 'panstellar' ); ?> →</a></p>
					<?php else : ?>
						<p style="text-decoration:underline;cursor:default;"><?php esc_html_e( 'View guarantee details', 'panstellar' ); ?> →</p>
					<?php endif; ?>
				</div>
			</div>

			<div class="uas-reviews">
				<div class="uas-review"><div class="uas-stars">★★★★★</div><b><?php esc_html_e( '"Clean labels. No fillers. Exactly why I switched."', 'panstellar' ); ?></b><br><small><?php esc_html_e( '— Verified reviewer', 'panstellar' ); ?></small></div>
				<div class="uas-review"><div class="uas-stars">★★★★★</div><b><?php esc_html_e( '"COA transparency is a huge plus for me."', 'panstellar' ); ?></b><br><small><?php esc_html_e( '— Review from website', 'panstellar' ); ?></small></div>
				<div class="uas-review"><div class="uas-stars">★★★★★</div><b><?php esc_html_e( '"Finally a single-active I can trust."', 'panstellar' ); ?></b><br><small><?php esc_html_e( '— All our guarantees', 'panstellar' ); ?></small></div>
			</div>

			<div class="uas-faq-mini" aria-label="<?php esc_attr_e( 'Quick FAQ', 'panstellar' ); ?>">
				<div class="uas-faq-mini__item">
					<button class="uas-faq-mini__btn" type="button" aria-expanded="false">
						<?php esc_html_e( 'Why single-active?', 'panstellar' ); ?> <span class="uas-faq-mini__chevron" aria-hidden="true">⌄</span>
					</button>
					<div class="uas-faq-mini__panel">
						<?php esc_html_e( 'Most Urolithin A products blend multiple compounds, which dilutes each individual dose and makes it harder to know what\'s actually working. Single-active means 100% of the dose is Urolithin A — nothing competing, nothing diluted.', 'panstellar' ); ?>
					</div>
				</div>
				<div class="uas-faq-mini__item">
					<button class="uas-faq-mini__btn" type="button" aria-expanded="false">
						<?php esc_html_e( 'Is it pure?', 'panstellar' ); ?> <span class="uas-faq-mini__chevron" aria-hidden="true">⌄</span>
					</button>
					<div class="uas-faq-mini__panel">
						<?php esc_html_e( 'Yes. Each batch is COA-verified by an independent third-party lab. Beyond Urolithin A, the only other ingredients are rice flour (filler), phosphatidylcholine (liposomal matrix), sunflower lecithin, and a vegetable capsule.', 'panstellar' ); ?>
					</div>
				</div>
				<div class="uas-faq-mini__item">
					<button class="uas-faq-mini__btn" type="button" aria-expanded="false">
						<?php esc_html_e( 'Is it tested?', 'panstellar' ); ?> <span class="uas-faq-mini__chevron" aria-hidden="true">⌄</span>
					</button>
					<div class="uas-faq-mini__panel">
						<?php esc_html_e( 'Every batch is sent to a third-party laboratory and tested for purity, potency, and contaminants. Certificates of Analysis are available by batch on request.', 'panstellar' ); ?>
					</div>
				</div>
				<div class="uas-faq-mini__item">
					<button class="uas-faq-mini__btn" type="button" aria-expanded="false">
						<?php esc_html_e( 'What is the guarantee?', 'panstellar' ); ?> <span class="uas-faq-mini__chevron" aria-hidden="true">⌄</span>
					</button>
					<div class="uas-faq-mini__panel">
						<?php esc_html_e( 'We offer a 90-day money-back guarantee. If you\'re not satisfied for any reason within 90 days of purchase, contact us for a full refund — no questions asked.', 'panstellar' ); ?>
					</div>
				</div>
			</div>
		</div>
	</section>

</div><!-- /.uas-pdp -->

<!-- STICKY ATC -->
<div class="uas-sticky" id="uas-sticky-<?php echo esc_attr( $product_id ); ?>" aria-label="<?php esc_attr_e( 'Quick purchase', 'panstellar' ); ?>">
	<div class="uas-wrap uas-sticky__inner">
		<div class="uas-sticky__info">
			<span class="uas-sticky__name"><?php echo esc_html( $product_title ); ?></span>
			<span class="uas-sticky__price" id="uas-sticky-price-<?php echo esc_attr( $product_id ); ?>"><?php echo wp_kses_post( $price_html ); ?></span>
		</div>
		<?php $uas_sticky_form_id = 'uas-sticky-form-' . $product_id; ?>
		<form
			action="<?php echo esc_url( $product_url ); ?>"
			method="post"
			enctype="multipart/form-data"
			id="<?php echo esc_attr( $uas_sticky_form_id ); ?>"
			class="uas-atc-form"
			data-product-id="<?php echo esc_attr( $product_id ); ?>"
			data-variant-id="<?php echo esc_attr( $default_vid ); ?>"
		>
			<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product_id ); ?>">
			<input type="hidden" name="quantity" value="1">
			<?php if ( $is_variable ) : ?>
				<input type="hidden" name="variation_id" class="variation_id" value="<?php echo esc_attr( $default_vid ); ?>">
			<?php endif; ?>
			<?php if ( $has_subscriptions ) : ?>
				<?php do_action( 'panstellar_uas_subscription_field', $product ); ?>
			<?php else : ?>
				<input type="hidden" name="subscription_plan" id="uas-sticky-plan-<?php echo esc_attr( $product_id ); ?>" value="15">
			<?php endif; ?>
			<button type="submit" name="add" class="uas-sticky__btn" id="uas-sticky-btn-<?php echo esc_attr( $product_id ); ?>" <?php echo $is_available ? '' : 'disabled'; ?>>
				<?php echo $is_available ? esc_html( $settings['cta_text'] ) : esc_html__( 'Sold Out', 'panstellar' ); ?>
			</button>
		</form>
	</div>
</div>
