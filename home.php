<?php
/**
 * Blog — home.php
 *
 * Conversion de templates/blog.json + sections/main-blog.liquid (Dawn 15).
 * Config réelle (blog.json) : layout = collage, show_image = true,
 * image_height = medium, show_date = true, show_author = false,
 * padding_top/bottom = 36, pagination 6 articles/page.
 *
 * Correspondances Liquid → WordPress :
 *   - blog.title        → single_post_title / get_the_title( page_for_posts )
 *   - blog.articles     → boucle native have_posts() (pre_get_posts règle 6/page)
 *   - render article-card → get_template_part( 'template-parts/blog/article-card' )
 *   - render pagination → template-parts/collection/pagination.php (réutilisé)
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$blog_layout   = panstellar_blog_setting( 'layout', 'collage' );
$show_image    = (bool) panstellar_blog_setting( 'show_image', true );
$image_height  = panstellar_blog_setting( 'image_height', 'medium' );
$show_date     = (bool) panstellar_blog_setting( 'show_date', true );
$show_author   = (bool) panstellar_blog_setting( 'show_author', false );
$page_for_posts= (int) get_option( 'page_for_posts' );
$blog_title    = $page_for_posts ? get_the_title( $page_for_posts ) : __( 'Blog', 'panstellar' );
?>

<main id="MainContent" class="content-for-layout focus-none" role="main" tabindex="-1">
	<div class="main-blog page-width section-main-blog-padding">
		<h1 class="title--primary scroll-trigger animate--fade-in">
			<?php echo esc_html( $blog_title ); ?>
		</h1>

		<?php if ( have_posts() ) : ?>
			<div class="blog-articles <?php echo 'collage' === $blog_layout ? 'blog-articles--collage' : ''; ?>">
				<?php
				$index = 0;
				while ( have_posts() ) :
					the_post();
					$index++;
					?>
					<div
						class="blog-articles__article article scroll-trigger animate--slide-in"
						data-cascade
						style="--animation-order: <?php echo esc_attr( $index ); ?>;"
					>
						<?php
						get_template_part(
							'template-parts/blog/article-card',
							null,
							array(
								'article_id'  => get_the_ID(),
								'show_image'  => $show_image,
								'media_height'=> $image_height,
								'show_date'   => $show_date,
								'show_author' => $show_author,
								'show_excerpt'=> true,
								'show_badge'  => false,
							)
						);
						?>
					</div>
				<?php endwhile; ?>
			</div>

			<?php get_template_part( 'template-parts/collection/pagination' ); ?>
		<?php else : ?>
			<p class="blog-articles__empty">
				<?php esc_html_e( 'No blog posts yet.', 'panstellar' ); ?>
			</p>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
