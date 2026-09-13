<?php
/**
 * Global business information settings.
 *
 * @package MBA_Site_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the settings page.
 */
function mba_core_add_settings_page(): void {
	add_options_page(
		__( 'MBA Settings', 'mba-site-core' ),
		__( 'MBA Settings', 'mba-site-core' ),
		'manage_options',
		'mba-settings',
		'mba_core_render_settings_page'
	);
}
add_action( 'admin_menu', 'mba_core_add_settings_page' );

/**
 * Register global fields once so templates never duplicate business details.
 */
function mba_core_register_settings(): void {
	$fields = array(
		'mba_legal_name'       => __( 'Legal business name', 'mba-site-core' ),
		'mba_phone'            => __( 'Main phone', 'mba-site-core' ),
		'mba_whatsapp'         => __( 'WhatsApp number', 'mba-site-core' ),
		'mba_whatsapp_message' => __( 'Default WhatsApp message', 'mba-site-core' ),
		'mba_email'            => __( 'Enquiry email', 'mba-site-core' ),
		'mba_address'          => __( 'Address', 'mba-site-core' ),
		'mba_opening_hours'    => __( 'Opening hours', 'mba-site-core' ),
		'mba_service_areas'    => __( 'Service areas', 'mba-site-core' ),
		'mba_maps_url'         => __( 'Google Maps directions URL', 'mba-site-core' ),
		'mba_facebook_url'     => __( 'Facebook URL', 'mba-site-core' ),
		'mba_instagram_url'    => __( 'Instagram URL', 'mba-site-core' ),
		'mba_tiktok_url'       => __( 'TikTok URL', 'mba-site-core' ),
	);

	register_setting(
		'mba_settings_group',
		'mba_site_settings',
		array(
			'type'              => 'object',
			'default'           => array(),
			'sanitize_callback' => 'mba_core_sanitize_settings',
		)
	);

	add_settings_section( 'mba_business_details', __( 'Business details', 'mba-site-core' ), '__return_false', 'mba-settings' );

	foreach ( $fields as $key => $label ) {
		add_settings_field( $key, $label, 'mba_core_render_text_setting', 'mba-settings', 'mba_business_details', array( 'key' => $key ) );
	}
}
add_action( 'admin_init', 'mba_core_register_settings' );

/**
 * Sanitize every stored setting.
 */
function mba_core_sanitize_settings( $value ): array {
	$output = array();
	foreach ( (array) $value as $key => $field_value ) {
		$output[ sanitize_key( $key ) ] = str_ends_with( (string) $key, '_url' ) ? esc_url_raw( $field_value ) : sanitize_textarea_field( $field_value );
	}
	return $output;
}

/**
 * Render one settings input.
 */
function mba_core_render_text_setting( array $args ): void {
	$options = get_option( 'mba_site_settings', array() );
	$key     = $args['key'];
	printf(
		'<input class="regular-text" type="text" name="mba_site_settings[%1$s]" value="%2$s">',
		esc_attr( $key ),
		esc_attr( $options[ $key ] ?? '' )
	);
}

/**
 * Render the settings form.
 */
function mba_core_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'MBA Settings', 'mba-site-core' ); ?></h1>
		<p><?php esc_html_e( 'Update these values once to change company details throughout the website.', 'mba-site-core' ); ?></p>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'mba_settings_group' );
			do_settings_sections( 'mba-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

