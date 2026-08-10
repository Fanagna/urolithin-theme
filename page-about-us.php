<?php
/**
 * Page À propos — page-about-us.php
 *
 * Conversion de templates/page.about-us.json (boutique réelle). Le template
 * utilise des blocs « ai_gen_block_* » générés par une app (aucune section
 * Liquid dans le thème) : on reconstruit ici le rendu statique fidèle avec
 * les couleurs/tailles définies dans les settings JSON (fond #012a7e).
 *
 * Blocs actifs (dans l'ordre du template) :
 *   1. ai_gen_block_1cb35c0  — Hero « Panstellar Shop » (titre 92px, intro)
 *   2. ai_gen_block_df74ffd  — « Our Story » (2 colonnes texte + image)
 *   3. ai_gen_block_31a5436  — « Crafting Excellence in the USA » (image + texte centré)
 *   4. ai_gen_block_6c30bf6  — « Empowering Recovery and Performance » (image + texte)
 *   5. ai_gen_block_e24b38f  — « The Future of Wellness » (texte)
 *   6. ai_gen_block_e24b38f  — « Join Our Community » (texte)
 *
 * Les images Shopify (shopify://shop_images/…) peuvent être fournies via le
 * filtre « panstellar_about_images » (array key → URL) ou le Customizer.
 *
 * NB : chargé pour toute page WordPress dont le slug est « about-us ».
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$images = (array) apply_filters(
	'panstellar_about_images',
	array(
		'story'  => '', // shopify://shop_images/about_su_image_480x480_99622b21-1003-4a58-a36c-9e5ec7401be1.png
		'usa'    => '', // shopify://shop_images/image_about_us_1_480x480_3a85d43f-271f-448f-ab41-26b1e02ad9ae.jpg
		'recovery' => '', // shopify://shop_images/image_about_us_2_480x480_82758163-a7cf-4ee0-a1cf-706c0f0062fb.jpg
	)
);

get_header();
?>

<style>
	/* Tailles mobiles des blocs about (settings heading_size_mobile du JSON). */
	@media (max-width: 749px) {
		.about-hero__title { font-size: 32px !important; }
		.about-craft__title { font-size: 28px !important; }
		.about-split__title,
		.about-empower__title { font-size: 24px !important; }
		.about-split__inner,
		.about-empower__inner { flex-direction: column; }
	}
</style>

<main id="MainContent" class="content-for-layout focus-none about-us-page" role="main" tabindex="-1">

	<?php
	// ── 1. Hero « Panstellar Shop » (ai_gen_block_1cb35c0) ─────────────
	?>
	<section class="about-hero" style="background-color: #012a7e;">
		<div class="about-hero__inner" style="max-width: 800px; padding: 60px 20px; margin: 0 auto; text-align: center;">
			<h1 class="about-hero__title" style="color: #ffffff; font-size: 92px; line-height: 1.1; margin: 0 0 24px; font-weight: 700;">
				<?php esc_html_e( 'Panstellar Shop', 'panstellar' ); ?>
			</h1>
			<div class="about-hero__text" style="color: #f3f3f3; font-size: 18px; line-height: 1.5;">
				<p><?php esc_html_e( 'Welcome to Panstellar Shop – Where Health Meets Innovation', 'panstellar' ); ?></p>
				<p><?php esc_html_e( 'At Panstellar Shop, we are committed to empowering you with premium-quality supplements designed to enhance your health, vitality, and overall well-being. Based in Fort Lauderdale, Florida, we specialize in crafting cutting-edge formulations that combine science and nature, ensuring that every product delivers exceptional results.', 'panstellar' ); ?></p>
			</div>
		</div>
	</section>

	<?php
	// ── 2. « Our Story » (ai_gen_block_df74ffd) ────────────────────────
	?>
	<section class="about-split" style="background-color: #012a7e;">
		<div class="about-split__inner" style="max-width: 1200px; padding: 50px 20px; margin: 0 auto; display: flex; align-items: center; gap: 40px; flex-wrap: wrap;">
			<div class="about-split__content" style="flex: 1 1 480px;">
				<h2 class="about-split__title" style="color: #ffffff; font-size: 32px; font-weight: 700; margin: 0 0 16px;">
					<?php esc_html_e( 'Our Story', 'panstellar' ); ?>
				</h2>
				<div class="about-split__text" style="color: #ffffff; font-size: 16px; line-height: 1.6;">
					<ul>
						<li><?php esc_html_e( 'In early 2022, our journey with BPC-157 began as passionate advocates for health and performance. As athletes and wellness enthusiasts, we understood the incredible potential of BPC-157 to support recovery, enhance performance, and improve overall well-being. But as we explored the products available on the market, one thing became clear: none met our standards for purity, potency, and effectiveness.', 'panstellar' ); ?></li>
						<li><?php esc_html_e( 'Determined to make a difference, we decided to create a version of BPC-157 that was better, stronger, and more reliable, something we could trust for ourselves and proudly share with others. This wasn\'t just about creating a product; it was about redefining what BPC-157 could do for athletes, fitness enthusiasts, and anyone on their journey to optimal health.', 'panstellar' ); ?></li>
					</ul>
				</div>
			</div>
			<?php if ( ! empty( $images['story'] ) ) : ?>
				<div class="about-split__image" style="flex: 1 1 320px;">
					<img src="<?php echo esc_url( $images['story'] ); ?>" alt="<?php esc_attr_e( 'Our story', 'panstellar' ); ?>" style="width: 100%; border-radius: 10px; display: block;">
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php
	// ── 3. « Crafting Excellence in the USA » (ai_gen_block_31a5436) ───
	?>
	<section class="about-craft" style="background-color: #012a7e;">
		<div class="about-craft__inner" style="max-width: 1200px; padding: 60px 20px; margin: 0 auto; text-align: center;">
			<h2 class="about-craft__title" style="color: #ffffff; font-size: 40px; font-weight: 700; margin: 0 0 40px;">
				<?php esc_html_e( 'Crafting Excellence in the USA', 'panstellar' ); ?>
			</h2>
			<?php if ( ! empty( $images['usa'] ) ) : ?>
				<img src="<?php echo esc_url( $images['usa'] ); ?>" alt="<?php esc_attr_e( 'Made in the USA', 'panstellar' ); ?>" style="width: 100%; height: 300px; object-fit: cover; border-radius: 10px; margin-bottom: 40px; display: block;">
			<?php endif; ?>
			<div class="about-craft__text" style="color: #ffffff; font-size: 16px; line-height: 1.6; max-width: 800px; margin: 0 auto;">
				<p><?php esc_html_e( 'Our product is proudly manufactured in the United States, adhering to the highest quality standards. Each batch undergoes rigorous testing in certified laboratories to ensure purity, potency, and safety. When you choose us, you\'re choosing a product backed by science and trusted by professionals.', 'panstellar' ); ?></p>
				<p><strong><?php esc_html_e( 'What sets us apart?', 'panstellar' ); ?></strong></p>
				<p><strong><?php esc_html_e( 'Unmatched Purity:', 'panstellar' ); ?></strong> <?php esc_html_e( 'Our BPC-157 is 99% pure, verified through third-party lab testing.', 'panstellar' ); ?></p>
				<p><strong><?php esc_html_e( 'Potency You Can Feel:', 'panstellar' ); ?></strong> <?php esc_html_e( 'Each capsule is designed to deliver the precise dosage needed to promote faster recovery and improved performance.', 'panstellar' ); ?></p>
				<p><strong><?php esc_html_e( 'Certified Quality:', 'panstellar' ); ?></strong> <?php esc_html_e( 'Manufactured in a GMP-certified facility, our formula meets the highest standards for safety and efficacy.', 'panstellar' ); ?></p>
			</div>
		</div>
	</section>

	<?php
	// ── 4. « Empowering Recovery and Performance » (ai_gen_block_6c30bf6) ─
	?>
	<section class="about-empower" style="background-color: #012a7e;">
		<div class="about-empower__inner" style="max-width: 1200px; padding: 40px 20px; margin: 0 auto; display: flex; align-items: center; gap: 40px; flex-wrap: wrap;">
			<?php if ( ! empty( $images['recovery'] ) ) : ?>
				<div class="about-empower__image" style="flex: 1 1 400px;">
					<img src="<?php echo esc_url( $images['recovery'] ); ?>" alt="<?php esc_attr_e( 'Recovery and performance', 'panstellar' ); ?>" style="width: 100%; border-radius: 10px; display: block;">
				</div>
			<?php endif; ?>
			<div class="about-empower__content" style="flex: 1 1 480px;">
				<h2 class="about-empower__title" style="color: #ffffff; font-size: 32px; font-weight: 700; margin: 0 0 16px;">
					<?php esc_html_e( 'Empowering Recovery and Performance', 'panstellar' ); ?>
				</h2>
				<div class="about-empower__text" style="color: #f3f3f3; font-size: 16px; line-height: 1.6;">
					<p><?php esc_html_e( 'Our enhanced formula has been specifically designed with athletes in mind. Whether you\'re recovering from intense training, seeking relief from muscle fatigue, or looking to support your joint health, our BPC-157 offers a solution you can rely on.', 'panstellar' ); ?></p>
					<p><?php esc_html_e( 'Countless customers, from professional athletes to weekend warriors, have shared their experiences with our product, describing faster healing, enhanced endurance, and better overall physical performance.', 'panstellar' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<?php
	// ── 5. « The Future of Wellness » (ai_gen_block_e24b38f) ────────────
	?>
	<section class="about-text" style="background-color: #012a7e;">
		<div class="about-text__inner" style="max-width: 800px; padding: 40px 20px; margin: 0 auto;">
			<h2 class="about-text__title" style="color: #ffffff; font-size: 18px; font-style: italic; margin: 0 0 20px;">
				<strong><?php esc_html_e( 'The Future of Wellness', 'panstellar' ); ?></strong>
			</h2>
			<div class="about-text__body" style="color: #f3f3f3; font-size: 16px; line-height: 1.6;">
				<p><?php esc_html_e( 'Our vision doesn\'t stop here. We\'re committed to pushing the boundaries of innovation in health supplements. Our mission is to continue providing cutting-edge products that empower people to achieve their goals, whether in sports, fitness, or everyday life.', 'panstellar' ); ?></p>
				<p><?php esc_html_e( 'If you\'re ready to experience the difference a truly enhanced BPC-157 can make, we invite you to join the movement. Discover what sets us apart and see why our customers choose us time and time again.', 'panstellar' ); ?></p>
				<p><?php esc_html_e( 'Welcome to the future of recovery. Welcome to the future of performance. Welcome to Panstellar shop', 'panstellar' ); ?></p>
			</div>
		</div>
	</section>

	<?php
	// ── 6. « Join Our Community » (ai_gen_block_e24b38f) ───────────────
	?>
	<section class="about-text" style="background-color: #012a7e;">
		<div class="about-text__inner" style="max-width: 800px; padding: 40px 20px; margin: 0 auto;">
			<h2 class="about-text__title" style="color: #ffffff; font-size: 30px; font-weight: 700; margin: 0 0 20px;">
				<?php esc_html_e( 'Join Our Community', 'panstellar' ); ?>
			</h2>
			<div class="about-text__body" style="color: #f3f3f3; font-size: 16px; line-height: 1.6;">
				<p><?php esc_html_e( 'When you shop with Panstellar shop, you\'re not just a customer – you\'re part of a community dedicated to living healthier, more mindful lives. Follow us on social media for wellness tips, product updates, and inspiring stories from our customers.', 'panstellar' ); ?></p>
				<p><?php esc_html_e( 'Thank you for choosing us as your partner in wellness. We\'re excited to be part of your journey to a healthier you and a healthier home.', 'panstellar' ); ?></p>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
