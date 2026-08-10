<?php
/**
 * Article — single.php
 *
 * Conversion de templates/article.json + sections/main-article.liquid (Dawn 15).
 * Config réelle (article.json) : blocks featured_image (adapt), title
 * (blog_show_date = true, blog_show_author = false), share (« Share »), content.
 *
 * Correspondances Liquid → WordPress :
 *   - article.image           → get_the_post_thumbnail_url() (srcset natif WP)
 *   - article.title           → get_the_title()
 *   - article.published_at    → get_the_date() (<time>)
 *   - article.author          → get_the_author_meta()
 *   - article.content         → the_content()
 *   - share-button            → template-parts/share-button.php (share.js Dawn)
 *   - blog.url                → get_permalink( page_for_posts )
 *   - form new_comment        → comment_form() natif stylé Dawn
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// NB : les produits WooCommerce utilisent single-product.php directement
// (hiérarchie WordPress single-{post_type}.php) — single.php ne sert que
// pour les articles du blog (post_type « post »).

get_header();

$post_id      = get_the_ID();
$title        = get_the_title();
$image_url    = get_the_post_thumbnail_url( $post_id, 'full' );
$image_id     = get_post_thumbnail_id( $post_id );
$show_date    = (bool) panstellar_blog_setting( 'article_show_date', true );
$show_author  = (bool) panstellar_blog_setting( 'article_show_author', false );
$author       = get_the_author_meta( 'display_name' );
$date         = get_the_date( '', $post_id );
$blog_id      = (int) get_option( 'page_for_posts' );
$blog_url     = $blog_id ? get_permalink( $blog_id ) : home_url( '/' );
$blog_title   = $blog_id ? get_the_title( $blog_id ) : __( 'Blog', 'panstellar' );
$share_url    = get_permalink( $post_id );

// Ratio adapt : padding-bottom = 100 / aspect_ratio (comme main-article.liquid).
$hero_ratio_percent = '';
if ( $image_id ) {
	$hero_src = wp_get_attachment_image_src( $image_id, 'full' );
	if ( $hero_src && (float) $hero_src[2] > 0 ) {
		$hero_ratio_percent = 100 / ( (float) $hero_src[1] / (float) $hero_src[2] );
	}
}
?>

<main id="MainContent" class="content-for-layout focus-none" role="main" tabindex="-1">
	<article class="article-template" itemscope itemtype="https://schema.org/Article">
		<?php if ( $image_url && $image_id ) : ?>
			<div class="article-template__hero-container scroll-trigger animate--fade-in">
				<div class="article-template__hero-adapt media" style="padding-bottom: <?php echo esc_attr( $hero_ratio_percent ); ?>%;">
					<?php
					echo wp_get_attachment_image(
						$image_id,
						'full',
						false,
						array(
							'fetchpriority' => 'high',
							'decoding'     => 'async',
							'alt'          => $title,
							'itemprop'     => 'image',
						)
					);
					?>
				</div>
			</div>
		<?php endif; ?>

		<header class="page-width page-width--narrow scroll-trigger animate--fade-in">
			<h1 class="article-template__title" itemprop="headline">
				<?php echo esc_html( $title ); ?>
			</h1>
			<?php if ( $show_date ) : ?>
				<span class="circle-divider caption-with-letter-spacing">
					<time datetime="<?php echo esc_attr( get_the_date( 'c', $post_id ) ); ?>" itemprop="datePublished">
						<?php echo esc_html( $date ); ?>
					</time>
				</span>
			<?php endif; ?>
			<?php if ( $show_author ) : ?>
				<span class="caption-with-letter-spacing">
					<span itemprop="author"><?php echo esc_html( $author ); ?></span>
				</span>
			<?php endif; ?>
		</header>

		<div class="article-template__social-sharing page-width page-width--narrow scroll-trigger animate--slide-in">
			<?php
			get_template_part(
				'template-parts/share-button',
				null,
				array(
					'share_link'  => $share_url,
					'share_label' => __( 'Share', 'panstellar' ),
				)
			);
			?>
		</div>

		<div class="article-template__content page-width page-width--narrow rte scroll-trigger animate--slide-in" itemprop="articleBody">
			<?php
			the_content();
			wp_link_pages(
				array(
					'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'panstellar' ),
					'after'  => '</div>',
				)
			);
			?>
		</div>

		<div class="article-template__back element-margin-top center scroll-trigger animate--slide-in">
			<a href="<?php echo esc_url( $blog_url ); ?>" class="article-template__link link animate-arrow">
				<span class="icon-wrap">
					<span class="svg-wrapper">
						<?php panstellar_icon( 'arrow' ); ?>
					</span>
				</span>
				<?php echo esc_html( sprintf( /* translators: %s: blog title */ __( 'Back to %s', 'panstellar' ), $blog_title ) ); ?>
			</a>
		</div>

		<?php if ( comments_open( $post_id ) || get_comments_number() > 0 ) : ?>
			<div class="article-template__comment-wrapper background-secondary">
				<div id="comments" class="page-width page-width--narrow scroll-trigger animate--slide-in">
					<?php
					if ( get_comments_number() > 0 ) :
						$comments_count = (int) get_comments_number();
						?>
						<h2 id="Comments-<?php echo esc_attr( $post_id ); ?>" tabindex="-1">
							<?php echo esc_html( sprintf( /* translators: %s: number of comments */ _n( '%s comment', '%s comments', $comments_count, 'panstellar' ), number_format_i18n( $comments_count ) ) ); ?>
						</h2>
						<div class="article-template__comments">
							<?php
							wp_list_comments(
								array(
									'style'       => 'div',
									'short_ping'  => true,
									'callback'    => 'panstellar_comment',
									'max_depth'   => 2,
									'avatar_size' => 0,
								)
							);
							?>
						</div>
						<?php the_comments_pagination( array( 'prev_text' => '&larr;', 'next_text' => '&rarr;' ) ); ?>
					<?php endif; ?>

					<?php comment_form( panstellar_comment_form_args() ); ?>
				</div>
			</div>
		<?php endif; ?>
	</article>
</main>

<?php
get_footer();
