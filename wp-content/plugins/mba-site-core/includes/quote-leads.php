<?php
/** Secure public quote form and private lead storage. @package MBA_Site_Core */
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

function mba_core_register_quote_form_block(): void {
	register_block_type( dirname( __DIR__ ) . '/blocks/quote-form', array( 'render_callback' => 'mba_core_render_quote_form' ) );
}
add_action( 'init', 'mba_core_register_quote_form_block' );

function mba_core_quote_form_status(): string {
	$status = isset( $_GET['quote_status'] ) ? sanitize_key( wp_unslash( $_GET['quote_status'] ) ) : '';
	$messages = array(
		'success' => __( 'Merci, votre demande a été enregistrée. MBA vous recontactera.', 'mba-site-core' ),
		'error' => __( 'Votre demande n’a pas pu être enregistrée. Vérifiez les champs et réessayez.', 'mba-site-core' ),
		'email_error' => __( 'Votre demande est enregistrée, mais la notification n’a pas pu être envoyée. Contactez MBA par téléphone.', 'mba-site-core' ),
		'ack_error' => __( 'Votre demande est enregistrée. L’e-mail de confirmation n’a pas pu être envoyé.', 'mba-site-core' ),
		'spam' => __( 'La demande n’a pas été acceptée. Veuillez réessayer plus tard.', 'mba-site-core' ),
	);
	if ( ! isset( $messages[ $status ] ) ) {
		return '';
	}
	$response_time = 'success' === $status ? (string) mba_core_setting( 'mba_quote_response_time' ) : '';
	$reference = isset( $_GET['quote_ref'] ) ? sanitize_text_field( wp_unslash( $_GET['quote_ref'] ) ) : '';
	return '<p class="mba-quote-notice" role="status">' . esc_html( $messages[ $status ] . ( $response_time ? ' ' . sprintf( __( 'Délai de réponse prévu : %s.', 'mba-site-core' ), $response_time ) : '' ) . ( preg_match( '/^[a-f0-9]{12}$/', $reference ) ? ' ' . sprintf( __( 'Référence : %s.', 'mba-site-core' ), $reference ) : '' ) ) . '</p>';
}

function mba_core_quote_select( string $name, string $label, array $options, bool $required = true, bool $multiple = false, array $selected_values = array() ): string {
	$required_attr = $required ? ' required' : '';
	$name_attr = $multiple ? $name . '[]' : $name;
	$html = '<div class="mba-quote-field"><label for="mba-quote-' . esc_attr( $name ) . '">' . esc_html( $label ) . ( $required ? ' *' : '' ) . '</label><select id="mba-quote-' . esc_attr( $name ) . '" name="' . esc_attr( $name_attr ) . '"' . ( $multiple ? ' multiple size="5"' : '' ) . $required_attr . '>';
	if ( ! $multiple ) {
		$html .= '<option value="">' . esc_html__( 'Choisir…', 'mba-site-core' ) . '</option>'; }
	foreach ( $options as $value => $option_label ) {
		$html .= '<option value="' . esc_attr( (string) $value ) . '"' . selected( in_array( (string) $value, array_map( 'strval', $selected_values ), true ), true, false ) . '>' . esc_html( $option_label ) . '</option>'; }
	return $html . '</select></div>';
}

function mba_core_render_quote_form(): string {
	wp_enqueue_script( 'mba-quote-form', plugins_url( 'assets/js/quote-form.js', dirname( __DIR__ ) . '/mba-site-core.php' ), array(), MBA_CORE_VERSION, true );
	$products = get_posts(
		array(
			'post_type' => 'mba_product',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
		)
	);
	$product_options = array();
	foreach ( $products as $product ) {
		$product_options[ $product->ID ] = $product->post_title; }
	$project_options = array(
		'renovation' => __( 'Rénovation', 'mba-site-core' ),
		'new_build' => __( 'Construction neuve', 'mba-site-core' ),
		'repair' => __( 'Réparation', 'mba-site-core' ),
		'other' => __( 'Autre', 'mba-site-core' ),
	);
	$email_required = (bool) apply_filters( 'mba_quote_email_required', mba_core_setting( 'mba_quote_require_email' ) );
	$source_product = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;
	if ( ! $source_product && isset( $_GET['product_slug'] ) ) {
		$match = get_page_by_path( sanitize_title( wp_unslash( $_GET['product_slug'] ) ), 'OBJECT', 'mba_product' );
		$source_product = $match ? (int) $match->ID : 0; }
	if ( ! $source_product || 'publish' !== get_post_status( $source_product ) || 'mba_product' !== get_post_type( $source_product ) ) {
		$source_product = 0; }
	$source_project = isset( $_GET['project_id'] ) ? absint( $_GET['project_id'] ) : 0;
	if ( ! $source_project && isset( $_GET['source_project'] ) ) {
		$match = get_page_by_path( sanitize_title( wp_unslash( $_GET['source_project'] ) ), 'OBJECT', 'mba_project' );
		$source_project = $match ? (int) $match->ID : 0; }
	if ( ! $source_project || 'publish' !== get_post_status( $source_project ) || 'mba_project' !== get_post_type( $source_project ) ) {
		$source_project = 0; }
	if ( $source_product ) {
		$product_options = array( $source_product => get_the_title( $source_product ) ) + $product_options; }
	$token = wp_generate_password( 32, false, false );
	$source_signature = hash_hmac( 'sha256', $source_product . ':' . $source_project, wp_salt( 'auth' ) );
	$landing_path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '/devis/';
	$landing_page = home_url( is_string( $landing_path ) ? $landing_path : '/devis/' );
	$html = '<main id="main" class="mba-quote"><section class="mba-section" aria-labelledby="mba-quote-title"><h1 id="mba-quote-title">' . esc_html__( 'Demander un devis', 'mba-site-core' ) . '</h1>' . mba_core_quote_form_status() . '<p>' . esc_html__( 'Décrivez votre projet. Les champs marqués d’un astérisque sont obligatoires.', 'mba-site-core' ) . '</p><form class="mba-quote-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" enctype="multipart/form-data"><input type="hidden" name="action" value="mba_submit_quote"><input type="hidden" name="quote_token" value="' . esc_attr( $token ) . '"><input type="hidden" name="source_product" value="' . esc_attr( (string) $source_product ) . '"><input type="hidden" name="source_project" value="' . esc_attr( (string) $source_project ) . '"><input type="hidden" name="source_signature" value="' . esc_attr( $source_signature ) . '"><input type="hidden" name="landing_page" value="' . esc_url( $landing_page ) . '"><input type="hidden" name="source_page" value="' . esc_url( wp_get_referer() ? wp_get_referer() : home_url( '/devis/' ) ) . '">';
	$html .= wp_nonce_field( 'mba_submit_quote', 'mba_quote_nonce', true, false );
	foreach ( array( 'utm_source', 'utm_medium', 'utm_campaign' ) as $utm_key ) {
		$utm_value = isset( $_GET[ $utm_key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $utm_key ] ) ) : '';
		$html .= '<input type="hidden" name="' . esc_attr( $utm_key ) . '" value="' . esc_attr( $utm_value ) . '">';
	}
	$html .= '<div class="mba-quote-honeypot" aria-hidden="true"><label for="mba-quote-website">' . esc_html__( 'Leave this field empty', 'mba-site-core' ) . '</label><input id="mba-quote-website" type="text" name="website" tabindex="-1" autocomplete="off"></div>';
	$html .= '<div class="mba-quote-grid"><div class="mba-quote-field"><label for="mba-quote-name">' . esc_html__( 'Nom complet *', 'mba-site-core' ) . '</label><input id="mba-quote-name" name="full_name" autocomplete="name" required maxlength="120"></div><div class="mba-quote-field"><label for="mba-quote-phone">' . esc_html__( 'Téléphone *', 'mba-site-core' ) . '</label><input id="mba-quote-phone" type="tel" name="phone" autocomplete="tel" required maxlength="40"></div><div class="mba-quote-field"><label for="mba-quote-email">' . esc_html__( 'E-mail', 'mba-site-core' ) . ( $email_required ? ' *' : '' ) . '</label><input id="mba-quote-email" type="email" name="email" autocomplete="email" maxlength="190"' . ( $email_required ? ' required' : '' ) . '></div><div class="mba-quote-field"><label for="mba-quote-city">' . esc_html__( 'Ville / gouvernorat *', 'mba-site-core' ) . '</label><input id="mba-quote-city" name="location" autocomplete="address-level2" required maxlength="120"></div></div>';
	$html .= mba_core_quote_select(
		'customer_type',
		__( 'Type de client', 'mba-site-core' ),
		array(
			'individual' => __( 'Particulier', 'mba-site-core' ),
			'business' => __( 'Entreprise', 'mba-site-core' ),
		)
	);
	$html .= mba_core_quote_select( 'project_type', __( 'Type de projet', 'mba-site-core' ), $project_options );
	$html .= mba_core_quote_select( 'products', __( 'Produits souhaités', 'mba-site-core' ), $product_options, true, true, $source_product ? array( $source_product ) : array() );
	$html .= '<div class="mba-quote-field"><label for="mba-quote-quantity">' . esc_html__( 'Quantité ou dimensions approximatives', 'mba-site-core' ) . '</label><input id="mba-quote-quantity" name="quantity" maxlength="200"></div>';
	$html .= mba_core_quote_select(
		'timeframe',
		__( 'Délai souhaité', 'mba-site-core' ),
		array(
			'soon' => __( 'Dès que possible', 'mba-site-core' ),
			'1_3_months' => __( 'Dans 1 à 3 mois', 'mba-site-core' ),
			'later' => __( 'Plus tard', 'mba-site-core' ),
			'undecided' => __( 'À définir', 'mba-site-core' ),
		),
		false
	);
	$html .= '<div class="mba-quote-field"><label for="mba-quote-message">' . esc_html__( 'Message *', 'mba-site-core' ) . '</label><textarea id="mba-quote-message" name="message" rows="6" required maxlength="5000"></textarea></div><div class="mba-quote-field"><label for="mba-quote-files">' . esc_html__( 'Photos ou plans (3 fichiers maximum, 5 Mo chacun)', 'mba-site-core' ) . '</label><input id="mba-quote-files" type="file" name="quote_files[]" accept=".jpg,.jpeg,.png,.webp,.pdf" multiple><p class="description">' . esc_html__( 'Formats autorisés : JPEG, PNG, WebP et PDF.', 'mba-site-core' ) . '</p></div><fieldset class="mba-quote-field"><legend>' . esc_html__( 'Moyen de contact préféré *', 'mba-site-core' ) . '</legend><label><input type="radio" name="preferred_contact" value="phone" required> ' . esc_html__( 'Téléphone', 'mba-site-core' ) . '</label> <label><input type="radio" name="preferred_contact" value="email"> ' . esc_html__( 'E-mail', 'mba-site-core' ) . '</label></fieldset><div class="mba-quote-field"><label><input type="checkbox" name="privacy_consent" value="1" required> ' . esc_html__( 'J’accepte que MBA utilise ces informations pour répondre à ma demande. *', 'mba-site-core' ) . '</label></div><button class="wp-element-button" type="submit">' . esc_html__( 'Envoyer ma demande', 'mba-site-core' ) . '</button></form></section></main>';
	return $html;
}

function mba_core_quote_private_dir(): string {
	$document_root = isset( $_SERVER['DOCUMENT_ROOT'] ) ? realpath( sanitize_text_field( wp_unslash( $_SERVER['DOCUMENT_ROOT'] ) ) ) : false;
	$private_base = $document_root ? dirname( $document_root ) : dirname( ABSPATH );
	$directory = apply_filters( 'mba_quote_private_upload_directory', $private_base . '/mba-private-leads' );
	return untrailingslashit( (string) $directory );
}

function mba_core_quote_store_uploads(): array|WP_Error {
	if ( empty( $_FILES['quote_files'] ) || ! is_array( $_FILES['quote_files']['name'] ) ) {
		return array(); }
	$files = $_FILES['quote_files'];
	$indexes = array();
	foreach ( $files['name'] as $index => $name ) {
		if ( '' !== $name ) {
			$indexes[] = $index; }
	}
	if ( count( $indexes ) > 3 ) {
		return new WP_Error( 'too_many_files', __( 'Vous pouvez joindre trois fichiers maximum.', 'mba-site-core' ) ); }
	$allowed = array(
		'jpg' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'png' => 'image/png',
		'webp' => 'image/webp',
		'pdf' => 'application/pdf',
	);
	$directory = mba_core_quote_private_dir();
	if ( ! wp_mkdir_p( $directory ) || ! is_writable( $directory ) ) {
		return new WP_Error( 'storage_unavailable', __( 'Le stockage sécurisé est indisponible.', 'mba-site-core' ) ); }
	@chmod( $directory, 0700 );
	$stored = array();
	foreach ( $indexes as $index ) {
		$name = sanitize_file_name( wp_basename( (string) $files['name'][ $index ] ) );
		$size = (int) $files['size'][ $index ];
		$tmp = (string) $files['tmp_name'][ $index ];
		$extension = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
		if ( UPLOAD_ERR_OK !== (int) $files['error'][ $index ] || ! isset( $allowed[ $extension ] ) || $size < 1 || $size > 5 * 1024 * 1024 || ! is_uploaded_file( $tmp ) ) {
			mba_core_quote_remove_uploads( $stored );
			return new WP_Error( 'invalid_file', __( 'Un fichier est invalide ou dépasse 5 Mo.', 'mba-site-core' ) ); }
		$finfo = new finfo( FILEINFO_MIME_TYPE );
		$mime = $finfo->file( $tmp );
		$checked = wp_check_filetype_and_ext( $tmp, $name );
		if ( $mime !== $allowed[ $extension ] || $checked['type'] !== $allowed[ $extension ] || ( 'application/pdf' !== $mime && ! @getimagesize( $tmp ) ) ) {
			mba_core_quote_remove_uploads( $stored );
			return new WP_Error( 'invalid_mime', __( 'Le type réel d’un fichier ne correspond pas à son extension.', 'mba-site-core' ) ); }
		$target = $directory . '/' . wp_generate_password( 40, false, false ) . '.' . $extension;
		if ( ! move_uploaded_file( $tmp, $target ) ) {
			mba_core_quote_remove_uploads( $stored );
			return new WP_Error( 'file_move_failed', __( 'Un fichier n’a pas pu être stocké.', 'mba-site-core' ) ); }
		@chmod( $target, 0600 );
		$stored[] = array(
			'path' => $target,
			'name' => $name,
			'mime' => $mime,
			'size' => $size,
		);
	}
	return $stored;
}

function mba_core_quote_remove_uploads( array $uploads ): void {
	foreach ( $uploads as $file ) {
		if ( isset( $file['path'] ) && is_file( $file['path'] ) ) {
			wp_delete_file( $file['path'] ); }
	}
}

/** Delete a lead's private attachments only when the file remains in the private lead directory. */
function mba_core_quote_delete_lead_uploads( int $lead_id ): void {
	if ( 'mba_quote_lead' !== get_post_type( $lead_id ) ) {
		return; }
	$uploads = get_post_meta( $lead_id, '_mba_quote_uploads', true );
	$base = realpath( mba_core_quote_private_dir() );
	if ( ! $base || ! is_array( $uploads ) ) {
		return; }
	foreach ( $uploads as $upload ) {
		if ( empty( $upload['path'] ) || ! is_string( $upload['path'] ) ) {
			continue; }
		$path = realpath( $upload['path'] );
		if ( $path && str_starts_with( $path, $base . DIRECTORY_SEPARATOR ) && is_file( $path ) ) {
			wp_delete_file( $path ); }
	}
}
add_action( 'before_delete_post', 'mba_core_quote_delete_lead_uploads' );

function mba_core_quote_is_spam( string $honeypot ): bool {
	return '' !== trim( $honeypot );
}

function mba_core_quote_submission_status( bool $lead_saved, bool $notification_sent, bool $acknowledgement_sent = true ): string {
	if ( ! $lead_saved ) {
		return 'error';
	}
	if ( ! $notification_sent ) {
		return 'email_error';
	}
	return $acknowledgement_sent ? 'success' : 'ack_error';
}

function mba_core_quote_validate_submission( array $fields, bool $has_products, bool $require_email ): bool {
	return ! empty( $fields['name'] )
		&& strlen( (string) $fields['name'] ) <= 120
		&& ! empty( $fields['phone'] )
		&& strlen( (string) $fields['phone'] ) <= 40
		&& ! empty( $fields['phone_valid'] )
		&& strlen( (string) ( $fields['email_raw'] ?? '' ) ) <= 190
		&& ( empty( $fields['email_raw'] ) || ! empty( $fields['email_valid'] ) )
		&& ( ! $require_email || ! empty( $fields['email'] ) )
		&& ! empty( $fields['location'] )
		&& strlen( (string) $fields['location'] ) <= 120
		&& in_array( $fields['customer'] ?? '', array( 'individual', 'business' ), true )
		&& in_array( $fields['project_type'] ?? '', array( 'renovation', 'new_build', 'repair', 'other' ), true )
		&& $has_products
		&& ! empty( $fields['message'] )
		&& strlen( (string) $fields['message'] ) <= 5000
		&& strlen( (string) ( $fields['quantity'] ?? '' ) ) <= 200
		&& in_array( $fields['timeframe'] ?? '', array( '', 'soon', '1_3_months', 'later', 'undecided' ), true )
		&& in_array( $fields['preferred'] ?? '', array( 'phone', 'email' ), true )
		&& ( 'email' !== ( $fields['preferred'] ?? '' ) || ! empty( $fields['email'] ) )
		&& ! empty( $fields['consent'] );
}

function mba_core_quote_redirect( string $status, string $token = '' ): never {
	$url = wp_get_referer();
	$url = $url && wp_parse_url( $url, PHP_URL_HOST ) === wp_parse_url( home_url( '/' ), PHP_URL_HOST ) ? $url : home_url( '/devis/' );
	$url = remove_query_arg( array( 'quote_status', 'quote_ref' ), $url );
	$url = add_query_arg( 'quote_status', $status, $url );
	if ( $token ) {
		$url = add_query_arg( 'quote_ref', substr( hash( 'sha256', $token ), 0, 12 ), $url ); }
	wp_safe_redirect( $url );
	exit;
}

function mba_core_handle_quote_submission(): void {
	$nonce = isset( $_POST['mba_quote_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['mba_quote_nonce'] ) ) : '';
	$token = isset( $_POST['quote_token'] ) ? sanitize_text_field( wp_unslash( $_POST['quote_token'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'mba_submit_quote' ) || ! preg_match( '/^[A-Za-z0-9]{32}$/', $token ) ) {
		mba_core_quote_redirect( 'error' ); }
	$prior = get_transient( 'mba_quote_' . hash( 'sha256', $token ) );
	if ( is_array( $prior ) ) {
		mba_core_quote_redirect( 'processing' === $prior['status'] ? 'spam' : $prior['status'], $token ); }
	if ( mba_core_quote_is_spam( sanitize_text_field( wp_unslash( $_POST['website'] ?? '' ) ) ) ) {
		set_transient( 'mba_quote_' . hash( 'sha256', $token ), array( 'status' => 'spam' ), 3600 );
		mba_core_quote_redirect( 'spam' ); }
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$rate_key = 'mba_quote_rate_' . hash_hmac( 'sha256', $ip, wp_salt( 'auth' ) );
	if ( get_transient( $rate_key ) ) {
		mba_core_quote_redirect( 'spam' ); }
	$token_key = 'mba_quote_' . hash( 'sha256', $token );
	$lock_key = 'mba_quote_lock_' . hash( 'sha256', $token );
	if ( ! add_option( $lock_key, time(), '', false ) ) {
		mba_core_quote_redirect( 'spam' ); }
	set_transient( $rate_key, 1, 10 * 60 );
	$name = sanitize_text_field( wp_unslash( $_POST['full_name'] ?? '' ) );
	$phone_raw = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	$phone = preg_replace( '/[^0-9+]/', '', $phone_raw );
	$email_raw = sanitize_text_field( wp_unslash( $_POST['email'] ?? '' ) );
	$email = sanitize_email( $email_raw );
	$location = sanitize_text_field( wp_unslash( $_POST['location'] ?? '' ) );
	$customer = sanitize_key( wp_unslash( $_POST['customer_type'] ?? '' ) );
	$type = sanitize_key( wp_unslash( $_POST['project_type'] ?? '' ) );
	$message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );
	$preferred = sanitize_key( wp_unslash( $_POST['preferred_contact'] ?? '' ) );
	$timeframe = sanitize_key( wp_unslash( $_POST['timeframe'] ?? '' ) );
	$quantity = sanitize_text_field( wp_unslash( $_POST['quantity'] ?? '' ) );
	$valid_products = array();
	foreach ( array_map( 'absint', (array) wp_unslash( $_POST['products'] ?? array() ) ) as $product_id ) {
		if ( 'mba_product' === get_post_type( $product_id ) && 'publish' === get_post_status( $product_id ) ) {
			$valid_products[] = $product_id; }
	}
	$required_email = (bool) apply_filters( 'mba_quote_email_required', mba_core_setting( 'mba_quote_require_email' ) );
	set_transient( $token_key, array( 'status' => 'processing' ), 5 * 60 );
	$valid = mba_core_quote_validate_submission(
		array(
			'name' => $name,
			'phone' => $phone,
			'phone_valid' => (bool) preg_match( '/^\+?[0-9][0-9 .()-]{5,30}$/', $phone_raw ),
			'email_raw' => $email_raw,
			'email_valid' => $email && is_email( $email_raw ),
			'email' => $email,
			'location' => $location,
			'customer' => $customer,
			'project_type' => $type,
			'message' => $message,
			'quantity' => $quantity,
			'timeframe' => $timeframe,
			'preferred' => $preferred,
			'consent' => '1' === (string) ( $_POST['privacy_consent'] ?? '' ),
		),
		(bool) $valid_products,
		$required_email
	);
	if ( ! $valid ) {
		delete_transient( $token_key );
		delete_option( $lock_key );
		delete_transient( $rate_key );
		mba_core_quote_redirect( 'error' ); }
	$uploads = mba_core_quote_store_uploads();
	if ( is_wp_error( $uploads ) ) {
		delete_transient( $token_key );
		delete_option( $lock_key );
		delete_transient( $rate_key );
		mba_core_quote_redirect( 'error' ); }
	$source_product = absint( $_POST['source_product'] ?? 0 );
	if ( 'mba_product' !== get_post_type( $source_product ) || 'publish' !== get_post_status( $source_product ) ) {
		$source_product = 0; }
	$source_project = absint( $_POST['source_project'] ?? 0 );
	if ( 'mba_project' !== get_post_type( $source_project ) || 'publish' !== get_post_status( $source_project ) ) {
		$source_project = 0; }
	$source_signature = sanitize_text_field( wp_unslash( $_POST['source_signature'] ?? '' ) );
	$expected_signature = hash_hmac( 'sha256', $source_product . ':' . $source_project, wp_salt( 'auth' ) );
	if ( ! hash_equals( $expected_signature, $source_signature ) ) {
		$source_product = 0;
		$source_project = 0; }
	$lead_id = wp_insert_post(
		array(
			'post_type' => 'mba_quote_lead',
			'post_status' => 'private',
			'post_title' => sprintf( 'Quote request — %s', $name ),
		),
		true
	);
	if ( is_wp_error( $lead_id ) ) {
		mba_core_quote_remove_uploads( $uploads );
		delete_transient( $token_key );
		delete_option( $lock_key );
		delete_transient( $rate_key );
		mba_core_quote_redirect( 'error' ); }
	$source_page = esc_url_raw( wp_unslash( $_POST['source_page'] ?? '' ) );
	if ( wp_parse_url( $source_page, PHP_URL_HOST ) !== wp_parse_url( home_url( '/' ), PHP_URL_HOST ) ) {
		$source_page = ''; }
	$landing_page = esc_url_raw( wp_unslash( $_POST['landing_page'] ?? '' ) );
	if ( wp_parse_url( $landing_page, PHP_URL_HOST ) !== wp_parse_url( home_url( '/' ), PHP_URL_HOST ) ) {
		$landing_page = ''; }
	$data = array(
		'full_name' => $name,
		'phone' => $phone,
		'email' => $email,
		'location' => $location,
		'customer_type' => $customer,
		'project_type' => $type,
		'products' => $valid_products,
		'quantity' => $quantity,
		'timeframe' => $timeframe,
		'message' => $message,
		'preferred_contact' => $preferred,
		'source_product' => $source_product,
		'source_project' => $source_project,
		'landing_page' => $landing_page,
		'source_page' => $source_page,
		'utm_source' => sanitize_text_field( wp_unslash( $_POST['utm_source'] ?? '' ) ),
		'utm_medium' => sanitize_text_field( wp_unslash( $_POST['utm_medium'] ?? '' ) ),
		'utm_campaign' => sanitize_text_field( wp_unslash( $_POST['utm_campaign'] ?? '' ) ),
		'uploads' => $uploads,
	);
	$stored = true;
	foreach ( $data as $key => $value ) {
		update_post_meta( $lead_id, '_mba_quote_' . $key, $value );
		if ( get_post_meta( $lead_id, '_mba_quote_' . $key, true ) !== $value ) {
			$stored = false; }
	}
	if ( ! $stored ) {
		wp_delete_post( $lead_id, true );
		mba_core_quote_remove_uploads( $uploads );
		delete_transient( $token_key );
		delete_option( $lock_key );
		delete_transient( $rate_key );
		mba_core_quote_redirect( 'error' ); }
	$recipient = sanitize_email( mba_core_setting( 'mba_email' ) );
	$recipient = $recipient ? $recipient : sanitize_email( get_option( 'admin_email' ) );
	$subject = sprintf( __( 'New quote request #%d', 'mba-site-core' ), $lead_id );
	$body = sprintf( "Name: %s\nPhone: %s\nEmail: %s\nLocation: %s\nCustomer: %s\nProject: %s\nProducts: %s\nQuantity: %s\nTimeframe: %s\nPreferred contact: %s\nSource product: %s\nSource project: %s\nMessage:\n%s", $name, $phone, $email, $location, $customer, $type, implode( ', ', array_map( 'get_the_title', $valid_products ) ), $data['quantity'], $data['timeframe'], $preferred, $source_product ? get_the_title( $source_product ) : '', $source_project ? get_the_title( $source_project ) : '', $message );
	$attachments = array_map( static fn( $file ) => $file['path'], $uploads );
	$sent = wp_mail( $recipient, $subject, $body, array( 'Content-Type: text/plain; charset=UTF-8' ), $attachments );
	if ( ! $sent ) {
		update_post_meta( $lead_id, '_mba_quote_notification_failed', true );
		set_transient(
			$token_key,
			array(
				'status' => mba_core_quote_submission_status( true, false ),
				'lead' => $lead_id,
			),
			24 * 60 * 60
		);
		delete_option( $lock_key );
		mba_core_quote_redirect( mba_core_quote_submission_status( true, false ), $token ); }
	$acknowledgement_sent = ! $email || wp_mail( $email, __( 'Votre demande de devis a bien été reçue', 'mba-site-core' ), __( 'Merci pour votre demande. L’équipe MBA vous répondra dès que possible.', 'mba-site-core' ), array( 'Content-Type: text/plain; charset=UTF-8' ) );
	if ( ! $acknowledgement_sent ) {
		update_post_meta( $lead_id, '_mba_quote_acknowledgement_failed', true ); }
	set_transient(
		$token_key,
		array(
			'status' => mba_core_quote_submission_status( true, true, $acknowledgement_sent ),
			'lead' => $lead_id,
		),
		24 * 60 * 60
	);
	delete_option( $lock_key );
	mba_core_quote_redirect( mba_core_quote_submission_status( true, true, $acknowledgement_sent ), $token );
}
add_action( 'admin_post_nopriv_mba_submit_quote', 'mba_core_handle_quote_submission' );
add_action( 'admin_post_mba_submit_quote', 'mba_core_handle_quote_submission' );

function mba_core_render_quote_lead_details( WP_Post $post ): void {
	if ( ! current_user_can( 'edit_post', $post->ID ) ) {
		return; }
	echo '<dl>';
	foreach ( array(
		'full_name' => 'Name',
		'phone' => 'Phone',
		'email' => 'Email',
		'location' => 'Location',
		'customer_type' => 'Customer type',
		'project_type' => 'Project type',
		'quantity' => 'Quantity/dimensions',
		'timeframe' => 'Timeframe',
		'preferred_contact' => 'Preferred contact',
		'landing_page' => 'Landing page',
		'source_page' => 'Source page',
	) as $key => $label ) {
		$value = get_post_meta( $post->ID, '_mba_quote_' . $key, true );
		if ( $value ) {
			echo '<dt>' . esc_html( $label ) . '</dt><dd>' . esc_html( (string) $value ) . '</dd>'; }
	}
	echo '<dt>Message</dt><dd>' . nl2br( esc_html( get_post_meta( $post->ID, '_mba_quote_message', true ) ) ) . '</dd></dl>';
	$products = get_post_meta( $post->ID, '_mba_quote_products', true );
	if ( is_array( $products ) ) {
		echo '<p><strong>Products:</strong> ' . esc_html( implode( ', ', array_filter( array_map( 'get_the_title', $products ) ) ) ) . '</p>'; }
	$uploads = get_post_meta( $post->ID, '_mba_quote_uploads', true );
	if ( is_array( $uploads ) && $uploads ) {
		echo '<h3>' . esc_html__( 'Private attachments', 'mba-site-core' ) . '</h3><ul>';
		foreach ( $uploads as $index => $file ) {
			$url = wp_nonce_url( admin_url( 'admin-post.php?action=mba_quote_download&lead_id=' . $post->ID . '&file=' . $index ), 'mba_quote_download_' . $post->ID . '_' . $index );
			echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $file['name'] ) . '</a> (' . esc_html( size_format( (int) $file['size'] ) ) . ')</li>';
		}
		echo '</ul>';
	}
}
add_action(
	'add_meta_boxes_mba_quote_lead',
	static function (): void {
		add_meta_box( 'mba-quote-lead-details', __( 'Private quote request', 'mba-site-core' ), 'mba_core_render_quote_lead_details', 'mba_quote_lead', 'normal', 'high' );
	}
);

function mba_core_download_quote_file(): void {
	$lead_id = isset( $_GET['lead_id'] ) ? absint( $_GET['lead_id'] ) : 0;
	$file_index = isset( $_GET['file'] ) ? absint( $_GET['file'] ) : -1;
	check_admin_referer( 'mba_quote_download_' . $lead_id . '_' . $file_index );
	if ( 'mba_quote_lead' !== get_post_type( $lead_id ) || ! current_user_can( 'edit_post', $lead_id ) ) {
		wp_die( esc_html__( 'You cannot access this private file.', 'mba-site-core' ), '', array( 'response' => 403 ) ); }
	$uploads = get_post_meta( $lead_id, '_mba_quote_uploads', true );
	if ( ! is_array( $uploads ) || ! isset( $uploads[ $file_index ]['path'] ) ) {
		wp_die( esc_html__( 'File not found.', 'mba-site-core' ), '', array( 'response' => 404 ) ); }
	$base = realpath( mba_core_quote_private_dir() );
	$path = realpath( $uploads[ $file_index ]['path'] );
	if ( ! $base || ! $path || ! str_starts_with( $path, $base . DIRECTORY_SEPARATOR ) || ! is_file( $path ) ) {
		wp_die( esc_html__( 'File not found.', 'mba-site-core' ), '', array( 'response' => 404 ) ); }
	$file = $uploads[ $file_index ];
	nocache_headers();
	header( 'Content-Type: ' . esc_attr( $file['mime'] ) );
	header( 'Content-Length: ' . (string) filesize( $path ) );
	header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $file['name'] ) . '"' );
	readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_readfile
	exit;
}
add_action( 'admin_post_mba_quote_download', 'mba_core_download_quote_file' );
