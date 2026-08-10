<?php
/**
 * Carte article — template-parts/blog/article-card.php
 *
 * Conversion de snippets/article-card.liquid (Dawn 15) utilisée par le
 * blog (templates/blog.json → sections/main-blog.liquid) :
 *   - card--standard, color background-2 (settings réels settings_data.json)
 *   - show_image: true, image_height: medium, show_date: true, show_author: false
 *   - show_excerpt: true (30 mots), show_badge: false
 *
 * Paramètres (via $args) :
 *   - article_id / post_id   ID de l'article (défaut : post courant)
 *   - show_image             Afficher l'image (défaut true)
 *   - media_height           adapt | small | medium | large (défaut medium)
 *   - show_date              Afficher la date (défaut true)
 *   - show_author            Afficher l'auteur (défaut false)
 *   - show_excerpt           Afficher l'extrait (défaut true)
 *   - show_badge             Afficher le badge « Blog » (défaut false)
 *   - lazy_load              Image en lazy-load (défaut true)
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$article_id   = isset( $args['article_id'] ) ? (int) $args['article_id'] : ( isset( $args['post_id'] ) ? (int) $args['post_id'] : get_the_ID() );
$show_image   = isset( $args['show_image'] ) ? (bool) $args['show_image'] : true;
$media_height = isset( $args['media_height'] ) ? $args['media_height'] : 'medium';
$show_date    = isset( $args['show_date'] ) ? (bool) $args['show_date'] : true;
$show_author  = isset( $args['show_author'] ) ? (bool) $args['show_author'] : false;
$show_excerpt = isset( $args['show_excerpt'] ) ? (bool) $args['show_excerpt'] : true;
$show_badge   = isset( $args['show_badge'] ) ? (bool) $args['show_badge'] : false;
$lazy_load    = isset( $args['lazy_load'] ) ? (bool) $args['lazy_load'] : true;

if ( ! $article_id ) {
	return;
}

$title      = get_the_title( $article_id );
$permalink  = get_permalink( $article_id );
$date       = get_the_date( '', $article_id );
$author     = get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $article_id ) );
$image      = get_the_post_thumbnail_url( $article_id, 'large' );
$image_id   = get_post_thumbnail_id( $article_id );
$excerpt    = '';
$has_text   = false;

if ( $show_excerpt ) {
	$excerpt = has_excerpt( $article_id ) ? get_the_excerpt( $article_id ) : wp_strip_all_tags( get_post_field( 'post_content', $article_id ) );
	if ( $excerpt ) {
		$excerpt  = wp_trim_words( $excerpt, 30, '…' );
		$has_text = true;
	}
}

// Ratio (media_aspect_ratio du snippet) : main-blog.liquid passe TOUJOURS
// article.image.aspect_ratio (même en mode medium) ; Shopify définit
// aspect_ratio = largeur / hauteur. Fallback 1 si pas d'image.
$ratio = 1;
if ( $image_id ) {
	$image_src = wp_get_attachment_image_src( $image_id, 'full' );
	if ( $image_src && (float) $image_src[2] > 0 ) {
		$ratio = (float) $image_src[1] / (float) $image_src[2];
		$ratio = max( 0.1, min( 2.5, $ratio ) );
	}
}

$comments_count = (int) get_comments_number( $article_id );
$comments_on    = comments_open( $article_id );
$blog_name      = __( 'Blog', 'panstellar' );
$blog_style     = apply_filters( 'panstellar_blog_setting_card_style', 'standard' );
$color_scheme   = apply_filters( 'panstellar_blog_setting_card_color_scheme', 'background-2' );

$card_classes   = 'card article-card card--' . esc_attr( $blog_style );
if ( 'adapt' !== $media_height ) {
	$card_classes .= ' article-card__image--' . esc_attr( $media_height );
}
$card_classes  .= ( $image && $show_image ) ? ' card--media' : ' card--text';
if ( 'card' === $blog_style ) {
	$card_classes .= ' color-' . esc_attr( $color_scheme ) . ' gradient';
}
$inner_classes  = 'card__inner';
if ( 'standard' === $blog_style ) {
	$inner_classes .= ' color-' . esc_attr( $color_scheme ) . ' gradient';
}
if ( ( $image && $show_image ) || 'standard' === $blog_style ) {
	$inner_classes .= ' ratio';
}
$ratio_percent = 1 / $ratio * 100;
?>

<div class="article-card-wrapper card-wrapper underline-links-hover">
	<div
		class="<?php echo esc_attr( trim( $card_classes ) ); ?>"
		style="--ratio-percent: <?php echo esc_attr( $ratio_percent ); ?>%;"
	>
		<div
			class="<?php echo esc_attr( trim( $inner_classes ) ); ?>"
			style="--ratio-percent: <?php echo esc_attr( $ratio_percent ); ?>%;"
		>
			<?php if ( $show_image && $image ) : ?>
				<div class="article-card__image-wrapper card__media">
					<div
						class="article-card__image media media--hover-effect"
						<?php if ( 'adapt' === $media_height ) : ?>
							style="padding-bottom: <?php echo esc_attr( $ratio_percent ); ?>%;"
						<?php endif; ?>
					>
						<?php
						echo wp_get_attachment_image(
							$image_id,
							'large',
							false,
							array(
								'class'    => 'motion-reduce',
								'alt'      => $title,
								'loading'  => $lazy_load ? 'lazy' : 'eager',
								'decoding' => 'async',
							)
						);
						?>
					</div>
				</div>
			<?php endif; ?>

			<div class="card__content">
				<div class="card__information">
					<h3 class="card__heading<?php echo $show_excerpt ? ' h2' : ''; ?>">
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

					<?php if ( $show_excerpt && $has_text ) : ?>
						<p class="article-card__excerpt rte-width">
							<?php echo esc_html( $excerpt ); ?>
						</p>
					<?php endif; ?>

					<?php if ( $show_excerpt ) : ?>
						<div class="article-card__footer">
							<?php if ( $comments_count > 0 && $comments_on ) : ?>
								<span><?php echo esc_html( sprintf( /* translators: %s: number of comments */ _n( '%s comment', '%s comments', $comments_count, 'panstellar' ), number_format_i18n( $comments_count ) ) ); ?></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>

				<?php if ( $show_badge ) : ?>
					<div class="card__badge bottom left">
						<span class="badge color-scheme-1"><?php echo esc_html( $blog_name ); ?></span>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="card__content">
			<div class="card__information">
				<h3 class="card__heading<?php echo $show_excerpt ? ' h2' : ''; ?>">
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

				<?php if ( $show_excerpt && $has_text ) : ?>
					<p class="article-card__excerpt rte-width">
						<?php echo esc_html( $excerpt ); ?>
					</p>
				<?php endif; ?>

				<?php if ( $show_excerpt ) : ?>
					<div class="article-card__footer">
						<?php if ( $comments_count > 0 && $comments_on ) : ?>
							<span><?php echo esc_html( sprintf( /* translators: %s: number of comments */ _n( '%s comment', '%s comments', $comments_count, 'panstellar' ), number_format_i18n( $comments_count ) ) ); ?></span>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $show_badge ) : ?>
				<div class="card__badge bottom left">
					<span class="badge color-scheme-1"><?php echo esc_html( $blog_name ); ?></span>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
