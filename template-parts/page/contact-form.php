<?php
/**
 * Formulaire de contact — template-parts/page/contact-form.php
 *
 * Conversion de sections/contact-form.liquid (Dawn 15) utilisée par la page
 * contact (templates/page.contact.json) :
 *   - heading : « Contact us by e-mail » (h1)
 *   - color_scheme: background-1, padding_top/bottom: 36
 *
 * Le formulaire Shopify {% form 'contact' %} → formulaire WP soumis vers
 * admin-post.php (action panstellar_contact, nonce vérifié, wp_mail vers
 * l'admin). Les messages succès/erreur sont affichés via $_GET.
 *
 * Paramètres (via $args) :
 *   - heading        Titre du formulaire (défaut « Contact us by e-mail »)
 *   - color_scheme   Scheme (défaut background-1)
 *   - padding_top/bottom (défaut 36)
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heading       = isset( $args['heading'] ) ? $args['heading'] : __( 'Contact us by e-mail', 'panstellar' );
$color_scheme  = isset( $args['color_scheme'] ) ? $args['color_scheme'] : 'background-1';
$padding_top   = isset( $args['padding_top'] ) ? (int) $args['padding_top'] : 36;
$padding_bottom= isset( $args['padding_bottom'] ) ? (int) $args['padding_bottom'] : 36;

$sent_ok    = isset( $_GET['contact'] ) && 'sent' === sanitize_key( wp_unslash( $_GET['contact'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$sent_error = isset( $_GET['contact'] ) && 'error' === sanitize_key( wp_unslash( $_GET['contact'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$old        = isset( $_GET['name'] ) ? sanitize_text_field( wp_unslash( $_GET['name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$old_email  = isset( $_GET['email'] ) ? sanitize_email( wp_unslash( $_GET['email'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>

<div class="color-<?php echo esc_attr( $color_scheme ); ?> gradient">
	<div class="contact page-width page-width--narrow section-contact-form-padding">
		<?php if ( $heading ) : ?>
			<h2 class="title title-wrapper--no-top-margin inline-richtext h1 scroll-trigger animate--slide-in">
				<?php echo esc_html( $heading ); ?>
			</h2>
		<?php else : ?>
			<h2 class="visually-hidden"><?php esc_html_e( 'Contact form', 'panstellar' ); ?></h2>
		<?php endif; ?>

		<?php if ( $sent_ok ) : ?>
			<h2 class="form-status form-status-list form__message" tabindex="-1" autofocus>
				<span class="svg-wrapper"><?php panstellar_icon( 'success' ); ?></span>
				<?php esc_html_e( 'Thanks for contacting us. We will get back to you soon.', 'panstellar' ); ?>
			</h2>
		<?php elseif ( $sent_error ) : ?>
			<div class="form__message">
				<h2 class="form-status caption-large text-body" role="alert" tabindex="-1" autofocus>
					<span class="svg-wrapper"><?php panstellar_icon( 'error' ); ?></span>
					<?php esc_html_e( 'The form could not be sent. Please try again.', 'panstellar' ); ?>
				</h2>
			</div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="isolate scroll-trigger animate--slide-in" id="ContactForm">
			<?php wp_nonce_field( 'panstellar_contact', 'panstellar_contact_nonce' ); ?>
			<input type="hidden" name="action" value="panstellar_contact">
			<input type="hidden" name="redirect" value="<?php echo esc_url( get_permalink() ); ?>">

			<div class="contact__fields">
				<div class="field">
					<input
						class="field__input"
						autocomplete="name"
						type="text"
						id="ContactForm-name"
						name="contact_name"
						value="<?php echo esc_attr( $old ); ?>"
						placeholder="<?php esc_attr_e( 'Name', 'panstellar' ); ?>"
					>
					<label class="field__label" for="ContactForm-name"><?php esc_html_e( 'Name', 'panstellar' ); ?></label>
				</div>
				<div class="field field--with-error">
					<input
						autocomplete="email"
						type="email"
						id="ContactForm-email"
						class="field__input"
						name="contact_email"
						spellcheck="false"
						autocapitalize="off"
						value="<?php echo esc_attr( $old_email ); ?>"
						aria-required="true"
						required
						placeholder="<?php esc_attr_e( 'Email', 'panstellar' ); ?>"
					>
					<label class="field__label" for="ContactForm-email">
						<?php esc_html_e( 'Email', 'panstellar' ); ?><span aria-hidden="true">*</span>
					</label>
				</div>
			</div>

			<div class="field">
				<input
					type="tel"
					id="ContactForm-phone"
					class="field__input"
					autocomplete="tel"
					name="contact_phone"
					pattern="[0-9+\- ]*"
					placeholder="<?php esc_attr_e( 'Phone number', 'panstellar' ); ?>"
				>
				<label class="field__label" for="ContactForm-phone"><?php esc_html_e( 'Phone number', 'panstellar' ); ?></label>
			</div>

			<div class="field">
				<textarea
					rows="10"
					id="ContactForm-body"
					class="text-area field__input"
					name="contact_body"
					aria-required="true"
					required
					placeholder="<?php esc_attr_e( 'Comment', 'panstellar' ); ?>"
				></textarea>
				<label class="form__label field__label" for="ContactForm-body">
					<?php esc_html_e( 'Comment', 'panstellar' ); ?><span aria-hidden="true">*</span>
				</label>
			</div>

			<div class="contact__button">
				<button type="submit" class="button">
					<?php esc_html_e( 'Send', 'panstellar' ); ?>
				</button>
			</div>
		</form>
	</div>
</div>
