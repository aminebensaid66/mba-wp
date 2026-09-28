<?php
/** Secure short contact form and private enquiry storage. @package MBA_Site_Core */
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

function mba_core_contact_is_spam( string $honeypot ): bool {
	return '' !== trim( $honeypot );
}

function mba_core_contact_validate( array $fields ): bool {
	return ! empty( $fields['name'] )
		&& strlen( (string) $fields['name'] ) <= 120
		&& ! empty( $fields['message'] )
		&& strlen( (string) $fields['message'] ) <= 3000
		&& ( ! empty( $fields['email'] ) || ! empty( $fields['phone'] ) )
		&& ( empty( $fields['email'] ) || ( ! empty( $fields['email_valid'] ) && strlen( (string) $fields['email'] ) <= 190 ) )
		&& ( empty( $fields['phone'] ) || ( ! empty( $fields['phone_valid'] ) && strlen( (string) $fields['phone'] ) <= 40 ) )
		&& ! empty( $fields['consent'] );
}

function mba_core_contact_status( bool $saved, bool $notified, bool $acknowledged = true ): string {
	if ( ! $saved ) {
		return 'error'; }
	if ( ! $notified ) {
		return 'email_error'; }
	return $acknowledged ? 'success' : 'ack_error';
}

function mba_core_contact_status_message(): string {
	$status = isset( $_GET['contact_status'] ) ? sanitize_key( wp_unslash( $_GET['contact_status'] ) ) : '';
	$messages = array(
		'success' => __( 'Merci, votre message a été transmis à MBA.', 'mba-site-core' ),
		'error' => __( 'Le message n’a pas pu être enregistré. Vérifiez les champs et réessayez.', 'mba-site-core' ),
		'email_error' => __( 'Votre message est enregistré, mais MBA n’a pas reçu la notification. Contactez-nous par téléphone.', 'mba-site-core' ),
		'ack_error' => __( 'Votre message est enregistré, mais l’e-mail de confirmation n’a pas pu être envoyé.', 'mba-site-core' ),
		'spam' => __( 'Le message n’a pas été accepté. Réessayez plus tard.', 'mba-site-core' ),
		'processing' => __( 'Votre message est en cours de traitement. Merci de patienter.', 'mba-site-core' ),
	);
	$reference = isset( $_GET['contact_ref'] ) ? sanitize_text_field( wp_unslash( $_GET['contact_ref'] ) ) : '';
	$message = isset( $messages[ $status ] ) ? esc_html( $messages[ $status ] ) : '';
	if ( preg_match( '/^[a-f0-9]{12}$/', $reference ) ) {
		$message .= ' ' . sprintf( esc_html__( 'Référence : %s.', 'mba-site-core' ), esc_html( $reference ) ); }
	return $message ? '<p class="mba-contact-notice" role="status">' . $message . '</p>' : '';
}

function mba_core_render_contact_form(): string {
	wp_enqueue_script( 'mba-contact-form', plugins_url( 'assets/js/contact-form.js', dirname( __DIR__ ) . '/mba-site-core.php' ), array(), MBA_CORE_VERSION, true );
	$token = wp_generate_password( 32, false, false );
	$privacy_page = get_page_by_path( 'confidentialite' );
	$privacy_link = $privacy_page && 'publish' === $privacy_page->post_status ? ' <a href="' . esc_url( get_permalink( $privacy_page ) ) . '">' . esc_html__( 'Politique de confidentialité', 'mba-site-core' ) . '</a>' : '';
	$action = esc_url( admin_url( 'admin-post.php' ) );
	$html = '<section class="mba-section mba-contact-form-section" aria-labelledby="mba-contact-form-heading"><h2 id="mba-contact-form-heading">' . esc_html__( 'Envoyer un message', 'mba-site-core' ) . '</h2>' . mba_core_contact_status_message() . '<form class="mba-contact-form" method="post" action="' . $action . '"><input type="hidden" name="action" value="mba_submit_contact"><input type="hidden" name="contact_token" value="' . esc_attr( $token ) . '"><input type="hidden" name="source_page" value="' . esc_url( wp_get_referer() ? wp_get_referer() : home_url( '/contact/' ) ) . '">';
	$html .= wp_nonce_field( 'mba_submit_contact', 'mba_contact_nonce', true, false );
	$html .= '<div class="mba-contact-honeypot" aria-hidden="true"><label for="mba-contact-website">' . esc_html__( 'Leave this field empty', 'mba-site-core' ) . '</label><input id="mba-contact-website" name="website" tabindex="-1" autocomplete="off"></div><div class="mba-contact-field"><label for="mba-contact-name">' . esc_html__( 'Nom complet *', 'mba-site-core' ) . '</label><input id="mba-contact-name" name="full_name" autocomplete="name" maxlength="120" required></div><div class="mba-contact-field"><label for="mba-contact-phone">' . esc_html__( 'Téléphone', 'mba-site-core' ) . '</label><input id="mba-contact-phone" name="phone" type="tel" autocomplete="tel" maxlength="40"></div><div class="mba-contact-field"><label for="mba-contact-email">' . esc_html__( 'E-mail', 'mba-site-core' ) . '</label><input id="mba-contact-email" name="email" type="email" autocomplete="email" maxlength="190"><p class="description">' . esc_html__( 'Indiquez un téléphone ou une adresse e-mail.', 'mba-site-core' ) . '</p></div><div class="mba-contact-field"><label for="mba-contact-message">' . esc_html__( 'Votre message *', 'mba-site-core' ) . '</label><textarea id="mba-contact-message" name="message" rows="5" maxlength="3000" required></textarea></div><div class="mba-contact-field"><label><input type="checkbox" name="privacy_consent" value="1" required> ' . esc_html__( 'J’accepte que MBA utilise ces informations pour répondre à mon message. *', 'mba-site-core' ) . '</label>' . $privacy_link . '</div><button class="wp-element-button" type="submit">' . esc_html__( 'Envoyer', 'mba-site-core' ) . '</button></form></section>';
	return $html;
}

function mba_core_contact_redirect( string $status, string $token = '' ): never {
	$url = wp_get_referer();
	$url = $url && wp_parse_url( $url, PHP_URL_HOST ) === wp_parse_url( home_url( '/' ), PHP_URL_HOST ) ? $url : home_url( '/contact/' );
	$url = remove_query_arg( array( 'contact_status', 'contact_ref' ), $url );
	$url = add_query_arg( 'contact_status', $status, $url );
	if ( $token ) {
		$url = add_query_arg( 'contact_ref', substr( hash( 'sha256', $token ), 0, 12 ), $url ); }
	wp_safe_redirect( $url );
	exit;
}

function mba_core_handle_contact_submission(): void {
	$nonce = sanitize_text_field( wp_unslash( $_POST['mba_contact_nonce'] ?? '' ) );
	$token = sanitize_text_field( wp_unslash( $_POST['contact_token'] ?? '' ) );
	if ( ! wp_verify_nonce( $nonce, 'mba_submit_contact' ) || ! preg_match( '/^[A-Za-z0-9]{32}$/', $token ) ) {
		mba_core_contact_redirect( 'error' ); }
	$key = 'mba_contact_' . hash( 'sha256', $token );
	$prior = get_transient( $key );
	if ( is_array( $prior ) ) {
		mba_core_contact_redirect( 'processing' === $prior['status'] ? 'spam' : $prior['status'], $token ); }
	if ( mba_core_contact_is_spam( sanitize_text_field( wp_unslash( $_POST['website'] ?? '' ) ) ) ) {
		set_transient( $key, array( 'status' => 'spam' ), 3600 );
		mba_core_contact_redirect( 'spam' ); }
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$rate_key = 'mba_contact_rate_' . hash_hmac( 'sha256', $ip, wp_salt( 'auth' ) );
	if ( get_transient( $rate_key ) ) {
		mba_core_contact_redirect( 'spam' ); }
	$lock_key = 'mba_contact_lock_' . hash( 'sha256', $token );
	$existing_lock = get_option( $lock_key );
	if ( $existing_lock && (int) $existing_lock < time() - 5 * 60 ) {
		delete_option( $lock_key ); }
	if ( ! add_option( $lock_key, time(), '', false ) ) {
		mba_core_contact_redirect( 'spam' ); }
	set_transient( $rate_key, 1, 10 * 60 );
	$name = sanitize_text_field( wp_unslash( $_POST['full_name'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$email_raw = sanitize_text_field( wp_unslash( $_POST['email'] ?? '' ) );
	$phone_raw = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	$phone = preg_replace( '/[^0-9+]/', '', $phone_raw );
	$message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );
	$valid = mba_core_contact_validate(
		array(
			'name' => $name,
			'email' => $email,
			'email_valid' => $email && is_email( $email_raw ),
			'phone' => $phone,
			'phone_valid' => (bool) preg_match( '/^\+?[0-9][0-9 .()-]{5,30}$/', $phone_raw ),
			'message' => $message,
			'consent' => '1' === (string) ( $_POST['privacy_consent'] ?? '' ),
		)
	);
	if ( ! $valid ) {
		delete_option( $lock_key );
		delete_transient( $rate_key );
		mba_core_contact_redirect( 'error' ); }
	set_transient( $key, array( 'status' => 'processing' ), 5 * 60 );
	$lead_id = wp_insert_post(
		array(
			'post_type' => 'mba_contact_lead',
			'post_status' => 'private',
			'post_title' => sprintf( 'Contact enquiry — %s', $name ),
		),
		true
	);
	if ( is_wp_error( $lead_id ) ) {
		delete_transient( $key );
		delete_option( $lock_key );
		delete_transient( $rate_key );
		mba_core_contact_redirect( 'error' ); }
	$source = esc_url_raw( wp_unslash( $_POST['source_page'] ?? '' ) );
	if ( wp_parse_url( $source, PHP_URL_HOST ) !== wp_parse_url( home_url( '/' ), PHP_URL_HOST ) ) {
		$source = ''; }
	$data = array(
		'name' => $name,
		'phone' => $phone,
		'email' => $email,
		'message' => $message,
		'source_page' => $source,
	);
	$stored = true;
	foreach ( $data as $field => $value ) {
		update_post_meta( $lead_id, '_mba_contact_' . $field, $value );
		if ( get_post_meta( $lead_id, '_mba_contact_' . $field, true ) !== $value ) {
			$stored = false; }
	}
	if ( ! $stored ) {
		wp_delete_post( $lead_id, true );
		delete_transient( $key );
		delete_option( $lock_key );
		delete_transient( $rate_key );
		mba_core_contact_redirect( 'error' ); }
	$recipient = sanitize_email( mba_core_setting( 'mba_email' ) );
	$recipient = $recipient ? $recipient : sanitize_email( get_option( 'admin_email' ) );
	$body = sprintf( "Name: %s\nPhone: %s\nEmail: %s\nSource page: %s\nMessage:\n%s", $name, $phone, $email, $source, $message );
	$sent = wp_mail( $recipient, sprintf( __( 'New contact message #%d', 'mba-site-core' ), $lead_id ), $body, array( 'Content-Type: text/plain; charset=UTF-8' ) );
	if ( ! $sent ) {
		update_post_meta( $lead_id, '_mba_contact_notification_failed', true );
		$status = mba_core_contact_status( true, false );
		set_transient(
			$key,
			array(
				'status' => $status,
				'lead' => $lead_id,
			),
			24 * 60 * 60
		);
		delete_option( $lock_key );
		mba_core_contact_redirect( $status, $token ); }
	$ack_sent = ! $email || wp_mail( $email, __( 'Votre message a bien été reçu', 'mba-site-core' ), __( 'Merci pour votre message. L’équipe MBA vous répondra dès que possible.', 'mba-site-core' ), array( 'Content-Type: text/plain; charset=UTF-8' ) );
	if ( ! $ack_sent ) {
		update_post_meta( $lead_id, '_mba_contact_acknowledgement_failed', true ); }
	$status = mba_core_contact_status( true, true, $ack_sent );
	set_transient(
		$key,
		array(
			'status' => $status,
			'lead' => $lead_id,
		),
		24 * 60 * 60
	);
	delete_option( $lock_key );
	mba_core_contact_redirect( $status, $token );
}
add_action( 'admin_post_nopriv_mba_submit_contact', 'mba_core_handle_contact_submission' );
add_action( 'admin_post_mba_submit_contact', 'mba_core_handle_contact_submission' );

function mba_core_render_contact_lead_details( WP_Post $post ): void {
	if ( ! current_user_can( 'edit_post', $post->ID ) ) {
		return; }
	foreach ( array(
		'name' => 'Name',
		'phone' => 'Phone',
		'email' => 'Email',
		'source_page' => 'Source page',
	) as $field => $label ) {
		$value = get_post_meta( $post->ID, '_mba_contact_' . $field, true );
		if ( $value ) {
			echo '<p><strong>' . esc_html( $label ) . ':</strong> ' . esc_html( (string) $value ) . '</p>'; }
	}
	echo '<p><strong>Message:</strong><br>' . nl2br( esc_html( get_post_meta( $post->ID, '_mba_contact_message', true ) ) ) . '</p>';
}
add_action(
	'add_meta_boxes_mba_contact_lead',
	static function (): void {
		add_meta_box( 'mba-contact-lead-details', __( 'Private contact enquiry', 'mba-site-core' ), 'mba_core_render_contact_lead_details', 'mba_contact_lead', 'normal', 'high' );
	}
);
