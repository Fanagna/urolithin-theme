<?php
/**
 * Footer Panstellar — template-parts/footer.php
 *
 * Conversion de sections/footer.liquid (thème Shopify Dawn 15.1.0).
 * Le HTML, les classes CSS et le responsive design sont conservés à l'identique.
 * La configuration réelle (sections/footer-group.json) est reproduite :
 *   - bloc link_list « Quick Links » (menu footer)
 *   - bloc text « Panstellar Shop » (coordonnées)
 *   - newsletter activée
 *   - icônes sociales + paiements
 *   - color scheme accent-1
 *
 * À inclure dans footer.php via : get_template_part( 'template-parts/footer' );
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Réglages du footer (équivalents de section.settings.*) ──────────────
$color_scheme       = panstellar_footer_setting( 'color_scheme' );
$newsletter_enable  = (bool) panstellar_footer_setting( 'newsletter_enable' );
$newsletter_heading = panstellar_footer_setting( 'newsletter_heading' );
$show_social        = (bool) panstellar_footer_setting( 'show_social' );
$payment_enable     = (bool) panstellar_footer_setting( 'payment_enable' );
$show_policy        = (bool) panstellar_footer_setting( 'show_policy' );
$enable_country_selector  = (bool) panstellar_footer_setting( 'enable_country_selector' );
$enable_language_selector = (bool) panstellar_footer_setting( 'enable_language_selector' );

$has_social_icons = panstellar_has_social_links();
$socials          = panstellar_social_links();
?>
<footer class="footer color-<?php echo esc_attr( $color_scheme ); ?> gradient section-footer-padding">

	<div class="footer__content-top page-width">
		<div class="footer__blocks-wrapper grid grid--1-col grid--2-col grid--4-col-tablet">
			<?php
			// ── BLOC 1 : menu (équivalent du bloc link_list « Quick Links ») ──
			$footer_menu = panstellar_footer_setting( 'footer_menu', 'footer' );
			if ( has_nav_menu( $footer_menu ) ) :
				?>
				<div class="footer-block grid__item footer-block--menu">
					<h2 class="footer-block__heading inline-richtext"><?php echo wp_kses_post( panstellar_footer_setting( 'footer_menu_heading', '<strong>Quick Links</strong>' ) ); ?></h2>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => $footer_menu,
							'menu_class'     => 'footer-block__details-content list-unstyled',
							'container'      => false,
							'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
							'walker'         => new Panstellar_Footer_Walker(),
						)
					);
					?>
				</div>
			<?php endif; ?>

			<?php
			// ── BLOC 2 : texte (équivalent du bloc text « Panstellar Shop ») ──
			$footer_text_heading = panstellar_footer_setting( 'footer_text_heading', '<strong>Panstellar Shop</strong>' );
			$footer_text_subtext = panstellar_footer_setting(
				'footer_text_subtext',
				'<p>651 N Broad St, Ste 5, Middletown, DE, United States<br>Mail : contact@panstellarshop.com<br>Phone : 302 321 5459<br>Monday-Friday : 07:30AM to 11:30AM EST</p>'
			);
			if ( $footer_text_heading || $footer_text_subtext ) :
				?>
				<div class="footer-block grid__item">
					<?php if ( $footer_text_heading ) : ?>
						<h2 class="footer-block__heading inline-richtext"><?php echo wp_kses_post( $footer_text_heading ); ?></h2>
					<?php endif; ?>
					<div class="footer-block__details-content rte">
						<?php echo wp_kses_post( $footer_text_subtext ); ?>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<div class="footer-block--newsletter">
			<?php if ( $newsletter_enable ) : ?>
				<div class="footer-block__newsletter">
					<?php if ( $newsletter_heading ) : ?>
						<h2 class="footer-block__heading inline-richtext"><?php echo esc_html( $newsletter_heading ); ?></h2>
					<?php endif; ?>

					<?php if ( function_exists( 'mc4wp_show_form' ) ) : ?>
						<?php mc4wp_show_form(); // Plugin Mailchimp for WP : si présent. ?>
					<?php else : ?>
					<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" class="footer__newsletter newsletter-form">
						<input type="hidden" name="action" value="panstellar_newsletter">
						<?php wp_nonce_field( 'panstellar_newsletter', 'panstellar_newsletter_nonce' ); ?>
							<div class="newsletter-form__field-wrapper">
								<div class="field">
									<input
										id="NewsletterForm--footer"
										type="email"
										name="newsletter_email"
										class="field__input"
										value=""
										aria-required="true"
										autocorrect="off"
										autocapitalize="off"
										autocomplete="email"
										placeholder="<?php esc_attr_e( 'Email address', 'panstellar' ); ?>"
										required
									>
									<label class="field__label" for="NewsletterForm--footer">
										<?php esc_html_e( 'Email address', 'panstellar' ); ?>
									</label>
									<button
										type="submit"
										class="newsletter-form__button field__button"
										name="commit"
										id="Subscribe"
										aria-label="<?php esc_attr_e( 'Subscribe', 'panstellar' ); ?>"
									>
										<span class="svg-wrapper">
											<?php panstellar_icon( 'arrow' ); ?>
										</span>
									</button>
								</div>
							</div>
						</form>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $show_social && $has_social_icons ) : ?>
				<?php get_template_part( 'template-parts/social-icons', null, array( 'class' => 'footer__list-social' ) ); ?>
			<?php endif; ?>
		</div>
	</div>

	<div class="footer__content-bottom">
		<div class="footer__content-bottom-wrapper page-width">
			<div class="footer__column footer__localization isolate">
				<?php
				if ( $enable_country_selector || $enable_language_selector ) {
					/**
					 * Hook de localisation footer (pays + langue) — WPML/Polylang.
					 */
					do_action( 'panstellar_footer_localization' );
				}
				?>
			</div>
			<div class="footer__column footer__column--info">
				<?php if ( $payment_enable ) : ?>
					<div class="footer__payment">
						<span class="visually-hidden"><?php esc_html_e( 'Payment methods', 'panstellar' ); ?></span>
						<ul class="list list-payment" role="list">
							<?php
							// Icônes de paiement : placeholders (cadres blancs) — remplacer par les
							// vrais SVG des passerelles WooCommerce actives via add_filter('panstellar_payment_icons').
							$payment_icons = apply_filters(
								'panstellar_payment_icons',
								array( 'visa', 'mastercard', 'amex', 'paypal', 'apple_pay', 'google_pay' )
							);
							foreach ( $payment_icons as $type ) :
								?>
								<li class="list-payment__item">
									<svg class="icon icon--full-color" viewBox="0 0 38 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-labelledby="pi-<?php echo esc_attr( $type ); ?>" width="38" height="24">
										<title id="pi-<?php echo esc_attr( $type ); ?>"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $type ) ) ); ?></title>
										<rect opacity=".07" width="38" height="24" rx="3"/><rect y="1" width="37" height="22" rx="2" fill="#fff"/>
									</svg>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="footer__content-bottom-wrapper page-width<?php echo ( ! $enable_country_selector && ! $enable_language_selector ) ? ' footer__content-bottom-wrapper--center' : ''; ?>">
			<div class="footer__copyright caption">
				<small class="copyright__content">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>, <a href="<?php echo panstellar_home_url(); // phpcs:ignore WordPress.Security.EscapeOutput -- helper déjà esc_url. ?>"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
				</small>
				<small class="copyright__content"><?php echo wp_kses_post( sprintf( /* translators: %s: WordPress. */ __( 'Powered by %s', 'panstellar' ), '<a href="https://wordpress.org/" rel="nofollow">WordPress</a>' ) ); ?></small>
				<?php if ( $show_policy ) : ?>
					<ul class="policies list-unstyled">
						<?php
						$policies = apply_filters(
							'panstellar_footer_policies',
							array(
								array( 'url' => home_url( '/privacy-policy/' ), 'title' => __( 'Privacy policy', 'panstellar' ) ),
								array( 'url' => home_url( '/refund_returns/' ), 'title' => __( 'Refund policy', 'panstellar' ) ),
								array( 'url' => home_url( '/terms-of-service/' ), 'title' => __( 'Terms of service', 'panstellar' ) ),
							)
						);
						foreach ( $policies as $policy ) :
							?>
							<li>
								<small class="copyright__content"><a href="<?php echo esc_url( $policy['url'] ); ?>"><?php echo esc_html( $policy['title'] ); ?></a></small>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<?php
	// Bloc custom-liquid « FDA disclaimer » (équivalent de custom_liquid_hchjwd dans footer-group.json).
	if ( panstellar_footer_setting( 'show_fda_disclaimer', true ) ) :
		?>
		<div class="fda-disclaimer">
			<?php echo wp_kses_post( panstellar_footer_setting( 'fda_disclaimer_text', 'These statements have not been evaluated by the Food and Drug Administration. This product is not intended to diagnose, treat, cure, or prevent any disease.' ) ); ?>
		</div>
	<?php endif; ?>

</footer>
