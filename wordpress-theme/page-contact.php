<?php
/**
 * Page contact — page-contact.php
 *
 * Conversion de templates/page.contact.json (Dawn 15) :
 *   - rich-text : « Ask Us Anything » (h1) + texte d'intro, centré, full-width,
 *     padding 40/52
 *   - main (main-page) : disabled
 *   - contact-form : « Contact us by e-mail » (h1), scheme background-1,
 *     padding 36/36
 *
 * NB : ce template est chargé pour toute page WordPress dont le slug est
 * « contact » (hiérarchie page-{slug}.php).
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="MainContent" class="content-for-layout focus-none" role="main" tabindex="-1">
	<?php
	get_template_part(
		'template-parts/page/rich-text',
		null,
		array(
			'blocks' => array(
				'heading' => __( 'Ask Us Anything', 'panstellar' ),
				'text'    => __( 'Have a question? Our dedicated team is here to help with product information, order status, cancellations, or any other inquiries. We\'ll respond to your e-mails within 24 hours.', 'panstellar' ),
			),
			'content_position' => 'center',
			'alignment'        => 'center',
			'full_width'       => true,
			'color_scheme'     => 'background-1',
			'padding_top'      => 40,
			'padding_bottom'   => 52,
		)
	);

	get_template_part(
		'template-parts/page/contact-form',
		null,
		array(
			'heading'      => __( 'Contact us by e-mail', 'panstellar' ),
			'color_scheme' => 'background-1',
			'padding_top'  => 36,
			'padding_bottom'=> 36,
		)
	);
	?>
</main>

<?php
get_footer();
