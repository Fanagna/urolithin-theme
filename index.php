<?php
/**
 * Template d'index — index.php
 *
 * Template par défaut du thème. La migration des templates
 * (front-page, single-product, archive-product…) sera faite
 * aux étapes ultérieures (voir MIGRATION.md §6).
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h1 class="h2"><?php the_title(); ?></h1>
			<div class="rte">
				<?php the_content(); ?>
			</div>
		</article>
		<?php
	endwhile;
endif;

get_footer();
