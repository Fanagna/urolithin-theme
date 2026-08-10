<?php
/**
 * Bouton de partage — template-parts/share-button.php
 *
 * Conversion de snippets/share-button.liquid (Dawn 15) : <share-button>
 * avec un <details> contenant le champ URL (copie / fermeture).
 * Le JS (assets/js/share.js — copie du share.js Dawn) définit le custom
 * element « share-button ».
 *
 * Paramètres (via $args) :
 *   - share_link   URL à partager (défaut : URL courante)
 *   - share_label  Libellé du bouton (défaut : « Share »)
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$share_link  = isset( $args['share_link'] ) ? $args['share_link'] : get_permalink();
$share_label = isset( $args['share_label'] ) ? $args['share_label'] : __( 'Share', 'panstellar' );
$id          = isset( $args['id'] ) ? $args['id'] : 'Share-1';
?>

<share-button id="<?php echo esc_attr( $id ); ?>" class="share-button quick-add-hidden">
	<button class="share-button__button hidden">
		<span class="svg-wrapper"><?php panstellar_icon( 'share' ); ?></span>
		<?php echo esc_html( $share_label ); ?>
	</button>
	<details id="Details-<?php echo esc_attr( $id ); ?>">
		<summary class="share-button__button">
			<span class="svg-wrapper"><?php panstellar_icon( 'share' ); ?></span>
			<?php echo esc_html( $share_label ); ?>
		</summary>
		<div class="share-button__fallback motion-reduce">
			<div class="field">
				<span id="ShareMessage-<?php echo esc_attr( $id ); ?>" class="share-button__message hidden" role="status"> </span>
				<input
					type="text"
					class="field__input"
					id="ShareUrl-<?php echo esc_attr( $id ); ?>"
					value="<?php echo esc_attr( $share_link ); ?>"
					placeholder="<?php esc_attr_e( 'Link', 'panstellar' ); ?>"
					onclick="this.select();"
					readonly
				>
				<label class="field__label" for="ShareUrl-<?php echo esc_attr( $id ); ?>">
					<?php esc_html_e( 'Link', 'panstellar' ); ?>
				</label>
			</div>
			<button class="share-button__close hidden">
				<span class="svg-wrapper">
					<?php panstellar_icon( 'close' ); ?>
				</span>
				<span class="visually-hidden"><?php esc_html_e( 'Close share', 'panstellar' ); ?></span>
			</button>
			<button class="share-button__copy">
				<span class="svg-wrapper">
					<?php panstellar_icon( 'copy' ); ?>
				</span>
				<span class="visually-hidden"><?php esc_html_e( 'Copy link', 'panstellar' ); ?></span>
			</button>
		</div>
	</details>
</share-button>
