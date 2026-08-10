<?php
/**
 * Icônes sociales — template-parts/social-icons.php
 *
 * Conversion de snippets/social-icons.liquid.
 * Le rendu est piloté par les liens sociaux configurés dans le Customizer
 * (équivalents de settings.social_*_link) via panstellar_social_links().
 *
 * Usage :
 *   get_template_part( 'template-parts/social-icons', null, array( 'class' => 'footer__list-social' ) );
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$class   = isset( $args['class'] ) ? $args['class'] : '';
$socials = panstellar_social_links();

if ( ! $socials ) {
	return;
}

// Libellés accessibles (équivalents de 'general.social.links.*').
$labels = array(
	'twitter'   => __( 'Twitter', 'panstellar' ),
	'facebook'  => __( 'Facebook', 'panstellar' ),
	'pinterest' => __( 'Pinterest', 'panstellar' ),
	'instagram' => __( 'Instagram', 'panstellar' ),
	'tiktok'    => __( 'TikTok', 'panstellar' ),
	'tumblr'    => __( 'Tumblr', 'panstellar' ),
	'snapchat'  => __( 'Snapchat', 'panstellar' ),
	'youtube'   => __( 'YouTube', 'panstellar' ),
	'vimeo'     => __( 'Vimeo', 'panstellar' ),
);
?>
<ul class="list-unstyled list-social<?php echo $class ? ' ' . esc_attr( $class ) : ''; ?>" role="list">
	<?php foreach ( $socials as $handle => $url ) : ?>
		<li class="list-social__item">
			<a href="<?php echo esc_url( $url ); ?>" class="link list-social__link">
				<span class="svg-wrapper">
					<?php panstellar_icon( $handle ); ?>
				</span>
				<span class="visually-hidden"><?php echo esc_html( isset( $labels[ $handle ] ) ? $labels[ $handle ] : ucfirst( $handle ) ); ?></span>
			</a>
		</li>
	<?php endforeach; ?>
</ul>
