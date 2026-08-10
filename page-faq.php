<?php
/**
 * Page FAQ — page-faq.php
 *
 * Conversion de templates/page.faq.json (boutique réelle). Le template
 * contient 19 blocs « ai_gen_block_57c6f8f » (accordéons FAQ par produit)
 * générés par une app — données extraites dans inc/faq-data.php.
 *
 * Config réelle de chaque bloc : fond #00143e, titre 36px blanc,
 * items fond #f9f9f9, bordure #e0e0e0 (hover #5c1ada), radius 8,
 * question 18px #121212, réponse 16px #666666, icône 24px #5c1ada.
 *
 * NB : chargé pour toute page WordPress dont le slug est « faq ».
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$faqs = include get_template_directory() . '/inc/faq-data.php';
if ( ! is_array( $faqs ) ) {
	$faqs = array();
}

get_header();
?>

<style>
	.faq-item__icon { transition: transform .2s ease; }
	details[open] .faq-item__icon { transform: rotate(45deg); }
</style>

<main id="MainContent" class="content-for-layout focus-none faq-page" role="main" tabindex="-1">
	<?php foreach ( $faqs as $index => $faq ) : ?>
		<section class="faq-section" style="background-color: #00143e;">
			<div class="faq-section__inner" style="max-width: 900px; padding: 60px 20px; margin: 0 auto;">
				<header class="faq-section__header" style="text-align: center; margin-bottom: 40px;">
					<h2 class="faq-section__title" style="color: #ffffff; font-size: 36px; font-weight: 700; margin: 0 0 16px;">
						<?php echo esc_html( $faq['title'] ); ?>
					</h2>
					<?php if ( ! empty( $faq['description'] ) ) : ?>
						<p class="faq-section__description" style="color: #666666; font-size: 16px; margin: 0;">
							<?php echo esc_html( $faq['description'] ); ?>
						</p>
					<?php endif; ?>
				</header>

				<div class="faq-section__items" style="display: flex; flex-direction: column; gap: 12px;">
					<?php foreach ( $faq['qa'] as $qa_index => $qa ) : ?>
						<details
							class="accordion faq-item"
							<?php echo 0 === $qa_index && 0 === $index ? 'open' : ''; ?>
							style="background: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 8px;"
						>
							<summary
								class="faq-item__question accordion__title"
								style="cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 20px; font-size: 18px; color: #121212; font-weight: 600;"
							>
								<span><?php echo esc_html( $qa[0] ); ?></span>
								<span class="faq-item__icon" style="flex-shrink: 0; color: #5c1ada; font-size: 24px; line-height: 1;" aria-hidden="true">+</span>
							</summary>
							<div
								class="faq-item__answer accordion__content"
								style="padding: 0 20px 20px; font-size: 16px; color: #666666; line-height: 1.6; white-space: pre-line;"
							>
								<?php echo esc_html( $qa[1] ); ?>
							</div>
						</details>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endforeach; ?>
</main>

<?php
get_footer();
