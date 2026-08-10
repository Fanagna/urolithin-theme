<?php
/**
 * Carte article — template-parts/search/article-card.php
 *
 * Conversion de snippets/article-card.liquid (Dawn 15) utilisée par la
 * recherche (templates/search.json) :
 *   - show_image: true, media_aspect_ratio: 1 (ratio carré)
 *   - show_date: true (article_show_date), show_author: false
 *   - show_badge: true, show_excerpt: false
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$article_id   = isset( $args['article_id'] ) ? (int) $args['article_id'] : 0;
$lazy_load    = ! empty( $args['lazy_load'] );
$show_date    = ! empty( $args['show_date'] );
$show_author  = ! empty( $args['show_author'] );
$ratio        = 1; // media_aspect_ratio: 1 (config search.json).

if ( ! $article_id ) {
	return;
}

$title     = get_the_title( $article_id );
$permalink = get_permalink( $article_id );
$date      = get_the_date( '', $article_id );
$author    = get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $article_id ) );
$image     = get_the_post_thumbnail_url( $article_id, 'large' );
$blog_name = __( 'Blog', 'panstellar' );
?>

<div class="article-card-wrapper card-wrapper underline-links-hover">
	<div
		class="
			card article-card
			card--standard
			<?php echo $image ? 'card--media' : 'card--text'; ?>
			ratio
		"
		style="--ratio-percent: <?php echo esc_attr( 1 / $ratio * 100 ); ?>%;"
	>
		<div
			class="card__inner color-background-1 gradient ratio"
			style="--ratio-percent: <?php echo esc_attr( 1 / $ratio * 100 ); ?>%;"
		>
			<?php if ( $image ) : ?>
				<div class="article-card__image-wrapper card__media">
					<div class="article-card__image media media--hover-effect">
						<img
							src="<?php echo esc_url( $image ); ?>"
							alt="<?php echo esc_attr( $title ); ?>"
							class="motion-reduce"
							<?php echo $lazy_load ? 'loading="lazy"' : ''; ?>
							width="600"
							height="600"
						>
					</div>
				</div>
			<?php endif; ?>
			<div class="card__content">
				<div class="card__information">
					<h3 class="card__heading">
						<a href="<?php echo esc_url( $permalink ); ?>" class="full-unstyled-link">
							<?php echo esc_html( mb_substr( $title, 0, 50 ) ); ?>
						</a>
					</h3>
					<div class="article-card__info caption-with-letter-spacing h5">
						<?php if ( $show_date ) : ?>
							<time class="circle-divider" datetime="<?php echo esc_attr( get_the_date( 'c', $article_id ) ); ?>">
								<?php echo esc_html( $date ); ?>
							</time>
						<?php endif; ?>
						<?php if ( $show_author ) : ?>
							<span><?php echo esc_html( $author ); ?></span>
						<?php endif; ?>
					</div>
				</div>
				<div class="card__badge bottom left">
					<span class="badge color-scheme-1"><?php echo esc_html( $blog_name ); ?></span>
				</div>
			</div>
		</div>
		<div class="card__content">
			<div class="card__information">
				<h3 class="card__heading">
					<a href="<?php echo esc_url( $permalink ); ?>" class="full-unstyled-link">
						<?php echo esc_html( mb_substr( $title, 0, 50 ) ); ?>
					</a>
				</h3>
				<div class="article-card__info caption-with-letter-spacing h5">
					<?php if ( $show_date ) : ?>
						<time class="circle-divider" datetime="<?php echo esc_attr( get_the_date( 'c', $article_id ) ); ?>">
							<?php echo esc_html( $date ); ?>
						</time>
					<?php endif; ?>
					<?php if ( $show_author ) : ?>
						<span><?php echo esc_html( $author ); ?></span>
					<?php endif; ?>
				</div>
			</div>
			<div class="card__badge bottom left">
				<span class="badge color-scheme-1"><?php echo esc_html( $blog_name ); ?></span>
			</div>
		</div>
	</div>
</div>
