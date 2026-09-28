<?php
/**
 * Validated global company settings and owner capabilities.
 *
 * @package MBA_Site_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Field catalogue shared by validation, forms, and public getters.
 *
 * @return array<string,array{label:string,type:string,help:string}>
 */
function mba_core_settings_fields(): array {
	$fields = array(
		'mba_legal_name' => array( __( 'Legal business name', 'mba-site-core' ), 'text', '' ),
		'mba_description' => array( __( 'Short company description', 'mba-site-core' ), 'textarea', '' ),
		'mba_logo_id' => array( __( 'Company logo', 'mba-site-core' ), 'image', __( 'Approved transparent PNG/WebP, preferably at least 600 px wide and under 200 KB.', 'mba-site-core' ) ),
		'mba_favicon_id' => array( __( 'Favicon', 'mba-site-core' ), 'favicon', __( 'Square image at least 512×512 px. Updates the native WordPress site icon.', 'mba-site-core' ) ),
		'mba_phone' => array( __( 'Main phone', 'mba-site-core' ), 'phone', __( 'International country code required, e.g. +216 12 345 678. Spaces, parentheses, dots and hyphens are accepted.', 'mba-site-core' ) ),
		'mba_secondary_phone' => array( __( 'Secondary phone', 'mba-site-core' ), 'phone', '' ),
		'mba_whatsapp' => array( __( 'WhatsApp number', 'mba-site-core' ), 'phone', __( 'International number registered with WhatsApp.', 'mba-site-core' ) ),
		'mba_whatsapp_message' => array( __( 'Default WhatsApp message', 'mba-site-core' ), 'textarea', '' ),
		'mba_email' => array( __( 'Enquiry email', 'mba-site-core' ), 'email', '' ),
		'mba_address' => array( __( 'Public business address', 'mba-site-core' ), 'textarea', '' ),
		'mba_opening_hours' => array( __( 'Opening hours', 'mba-site-core' ), 'textarea', __( 'Confirmed hours only; leave blank if unconfirmed.', 'mba-site-core' ) ),
		'mba_service_areas' => array( __( 'Service areas', 'mba-site-core' ), 'textarea', __( 'One general service area per line.', 'mba-site-core' ) ),
		'mba_maps_url' => array( __( 'Map directions URL', 'mba-site-core' ), 'url', '' ),
		'mba_maps_embed_url' => array( __( 'Google Maps embed URL', 'mba-site-core' ), 'map', __( 'HTTPS URL beginning https://www.google.com/maps/embed; do not paste iframe HTML.', 'mba-site-core' ) ),
		'mba_showroom_guidance' => array( __( 'Workshop/showroom visit guidance', 'mba-site-core' ), 'textarea', __( 'Confirmed visit instructions, appointment requirements, and accessibility notes only.', 'mba-site-core' ) ),
		'mba_facebook_url' => array( __( 'Facebook URL', 'mba-site-core' ), 'url', '' ),
		'mba_instagram_url' => array( __( 'Instagram URL', 'mba-site-core' ), 'url', '' ),
		'mba_linkedin_url' => array( __( 'LinkedIn URL', 'mba-site-core' ), 'url', '' ),
		'mba_tiktok_url' => array( __( 'TikTok URL', 'mba-site-core' ), 'url', '' ),
		'mba_youtube_url' => array( __( 'YouTube URL', 'mba-site-core' ), 'url', '' ),
		'mba_quote_response_time' => array( __( 'Quote response time', 'mba-site-core' ), 'text', __( 'Enter only a response time confirmed by MBA; leave blank to omit the promise.', 'mba-site-core' ) ),
		'mba_quote_require_email' => array( __( 'Require email for quote requests', 'mba-site-core' ), 'checkbox', __( 'Leave unchecked to allow phone-only enquiries.', 'mba-site-core' ) ),
		'mba_footer_content' => array( __( 'Footer content', 'mba-site-core' ), 'textarea', '' ),
		'mba_quote_cta_label' => array( __( 'Quote CTA label', 'mba-site-core' ), 'text', '' ),
		'mba_contact_cta_label' => array( __( 'Contact CTA label', 'mba-site-core' ), 'text', '' ),
		'mba_seo_title' => array( __( 'Default SEO title', 'mba-site-core' ), 'text', __( 'Used as the homepage title when no page-specific title is set.', 'mba-site-core' ) ),
		'mba_seo_description' => array( __( 'Default SEO description', 'mba-site-core' ), 'textarea', __( 'Used as the homepage description and general fallback.', 'mba-site-core' ) ),
		'mba_seo_image_id' => array( __( 'Default social share image', 'mba-site-core' ), 'image', __( 'Used when a page has no featured image.', 'mba-site-core' ) ),
		'mba_seo_redirects' => array( __( 'Permanent redirects', 'mba-site-core' ), 'redirects', __( 'One internal path per line: /old-path/ => /new-path/. Targets must stay on this site.', 'mba-site-core' ) ),
		'mba_partner_disclaimer' => array( __( 'Partner/certification disclaimer', 'mba-site-core' ), 'textarea', '' ),
		'mba_homepage_hero_heading' => array( __( 'Homepage hero heading', 'mba-site-core' ), 'text', __( 'One clear heading; leave blank to use the page title.', 'mba-site-core' ) ),
		'mba_homepage_value_proposition' => array( __( 'Homepage value proposition', 'mba-site-core' ), 'textarea', '' ),
		'mba_homepage_trust_highlights' => array( __( 'Homepage trust highlights', 'mba-site-core' ), 'textarea', __( 'One confirmed highlight per line; leave blank to hide this section.', 'mba-site-core' ) ),
		'mba_homepage_reasons' => array( __( 'Homepage reasons to choose MBA', 'mba-site-core' ), 'textarea', __( 'One confirmed reason per line; leave blank to hide this section.', 'mba-site-core' ) ),
		'mba_homepage_process' => array( __( 'Homepage project process', 'mba-site-core' ), 'textarea', __( 'One confirmed step per line; leave blank to hide this section.', 'mba-site-core' ) ),
		'mba_homepage_materials' => array( __( 'Homepage materials and finishes', 'mba-site-core' ), 'textarea', '' ),
		'mba_homepage_final_cta' => array( __( 'Homepage final CTA text', 'mba-site-core' ), 'textarea', '' ),
		'mba_homepage_hero_image_id' => array( __( 'Homepage hero image', 'mba-site-core' ), 'image', '' ),
		'mba_homepage_intro_image_id' => array( __( 'Homepage introduction image', 'mba-site-core' ), 'image', '' ),
		'mba_homepage_process_image_id' => array( __( 'Homepage process image', 'mba-site-core' ), 'image', '' ),
		'mba_homepage_materials_image_id' => array( __( 'Homepage materials image', 'mba-site-core' ), 'image', '' ),
		'mba_company_history' => array( __( 'Company history', 'mba-site-core' ), 'textarea', '' ),
		'mba_company_founder_team' => array( __( 'Founder and team', 'mba-site-core' ), 'textarea', '' ),
		'mba_company_values' => array( __( 'Values and quality approach', 'mba-site-core' ), 'textarea', '' ),
		'mba_company_capabilities' => array( __( 'Capabilities and services', 'mba-site-core' ), 'textarea', __( 'One confirmed capability per line.', 'mba-site-core' ) ),
		'mba_company_certifications' => array( __( 'Certifications and verified claims', 'mba-site-core' ), 'textarea', __( 'Leave blank until each claim is supplied and verified.', 'mba-site-core' ) ),
		'mba_company_workshop_gallery' => array( __( 'Workshop/team gallery', 'mba-site-core' ), 'gallery', __( 'Use approved images with captions and alt text; confirm publication consent.', 'mba-site-core' ) ),
	);
	$result = array();
	foreach ( $fields as $key => $field ) {
		$result[ $key ] = array(
			'label' => $field[0],
			'type' => $field[1],
			'help' => $field[2],
		);
	}
	return $result;
}

/**
 * Normalize international phone syntax without guessing a country code.
 *
 * @param mixed $value Input.
 * @return string
 */
function mba_core_sanitize_phone( $value ): string {
	if ( ! is_string( $value ) || preg_match( '/[^0-9+\s().-]/', $value ) ) {
		return '';
	}
	$phone = preg_replace( '/[\s().-]/', '', $value );
	return is_string( $phone ) && preg_match( '/^\+[1-9][0-9]{6,14}$/', $phone ) ? $phone : '';
}

/**
 * Validate a setting without emitting frontend notices.
 *
 * @param string $type  Field type.
 * @param mixed  $value Input.
 * @return string|int|bool|array<int>
 */
function mba_core_validate_setting( string $type, $value ) {
	if ( 'redirects' === $type ) {
		return is_string( $value ) && function_exists( 'mba_core_seo_sanitize_redirects' ) ? mba_core_seo_sanitize_redirects( $value ) : '';
	}
	if ( in_array( $type, array( 'image', 'favicon' ), true ) ) {
		$id = mba_core_sanitize_image_id( $value );
		if ( 'favicon' === $type && $id ) {
			$size = wp_get_attachment_metadata( $id );
			if ( ! is_array( $size ) || ( $size['width'] ?? 0 ) < 512 || ( $size['width'] ?? 0 ) !== ( $size['height'] ?? 0 ) ) {
				return 0;
			}
		}
		return $id;
	}
	if ( 'gallery' === $type ) {
		$values = is_array( $value ) ? $value : explode( ',', (string) $value );
		return mba_core_sanitize_image_id_list( $values );
	}
	if ( 'checkbox' === $type ) {
		return rest_sanitize_boolean( $value );
	}
	if ( ! is_string( $value ) ) {
		return '';
	}
	if ( 'phone' === $type ) {
		return mba_core_sanitize_phone( $value );
	}
	if ( 'email' === $type ) {
		return is_email( trim( $value ) ) ? sanitize_email( trim( $value ) ) : '';
	}
	if ( in_array( $type, array( 'url', 'map' ), true ) ) {
		$url = mba_core_sanitize_partner_url( $value );
		if ( 'map' === $type && ( 'https' !== wp_parse_url( $url, PHP_URL_SCHEME ) || ! in_array( wp_parse_url( $url, PHP_URL_HOST ), array( 'www.google.com', 'google.com' ), true ) || ! str_starts_with( (string) wp_parse_url( $url, PHP_URL_PATH ), '/maps/embed' ) ) ) {
			return '';
		}
		return $url;
	}
	return 'textarea' === $type ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );
}

/**
 * Validate only known settings and report rejected fields to the editor.
 *
 * @param mixed $value Submitted settings.
 * @return array<string,string|int>
 */
function mba_core_sanitize_settings( $value ): array {
	$output = array();
	$value  = is_array( $value ) ? $value : array();
	foreach ( mba_core_settings_fields() as $key => $field ) {
		$raw = $value[ $key ] ?? '';
		$output[ $key ] = mba_core_validate_setting( $field['type'], $raw );
		if ( ! empty( $raw ) && ( '' === $output[ $key ] || 0 === $output[ $key ] ) ) {
			add_settings_error( 'mba_site_settings', $key, sprintf( __( '%s was rejected. Please use the format described below.', 'mba-site-core' ), $field['label'] ), 'error' );
		}
	}
	return $output;
}

/**
 * Public API: unknown/missing text returns empty; missing media returns zero.
 *
 * @param string $key Known key.
 * @return string|int|bool
 */
function mba_core_setting( string $key ) {
	$fields = mba_core_settings_fields();
	if ( ! isset( $fields[ $key ] ) ) {
		return '';
	}
	$settings = get_option( 'mba_site_settings', array() );
	return mba_core_validate_setting( $fields[ $key ]['type'], is_array( $settings ) ? ( $settings[ $key ] ?? '' ) : '' );
}

/**
 * Provide a readable international display number.
 *
 * @param string $key Phone key.
 * @return string
 */
function mba_core_phone_display( string $key = 'mba_phone' ): string {
	$phone = mba_core_sanitize_phone( mba_core_setting( $key ) );
	return preg_match( '/^\+216([0-9]{2})([0-9]{3})([0-9]{3})$/', $phone, $matches ) ? '+216 ' . $matches[1] . ' ' . $matches[2] . ' ' . $matches[3] : $phone;
}

/**
 * Telephone URL from the canonical global number.
 *
 * @param string $key Phone key.
 * @return string
 */
function mba_core_phone_url( string $key = 'mba_phone' ): string {
	$phone = mba_core_sanitize_phone( mba_core_setting( $key ) );
	return $phone ? 'tel:' . $phone : '';
}

/**
 * WhatsApp link, omitted when the global number is missing.
 *
 * @return string
 */
function mba_core_whatsapp_url( string $context = '' ): string {
	$phone = mba_core_sanitize_phone( mba_core_setting( 'mba_whatsapp' ) );
	if ( ! $phone ) {
		return '';
	}
	$message = (string) mba_core_setting( 'mba_whatsapp_message' );
	if ( $context ) {
		$message = trim( $message . ( $message ? "\n\n" : '' ) . $context ); }
	return 'https://wa.me/' . substr( $phone, 1 ) . ( $message ? '?text=' . rawurlencode( $message ) : '' );
}

/**
 * Register a dynamic company-detail block for block-theme templates.
 */
function mba_core_register_company_block(): void {
	register_block_type(
		dirname( __DIR__ ) . '/blocks/company-detail',
		array(
			'render_callback' => 'mba_core_render_company_block',
		)
	);
}
add_action( 'init', 'mba_core_register_company_block' );

/**
 * Render a setting with context-appropriate escaping and no missing placeholder.
 *
 * @param array<string,mixed> $attributes Block attributes.
 * @return string
 */
function mba_core_render_company_block( array $attributes ): string {
	$key = is_string( $attributes['key'] ?? null ) ? $attributes['key'] : '';
	$value = mba_core_setting( $key );
	if ( ! $value ) {
		return '';
	}
	$label = is_string( $attributes['label'] ?? null ) ? $attributes['label'] : '';
	if ( 'mba_logo_id' === $key ) {
		return wp_get_attachment_image( (int) $value, 'medium', false, array( 'class' => 'mba-company-logo' ) );
	}
	if ( 'mba_favicon_id' === $key ) {
		return '';
	}
	$url = '';
	if ( in_array( $key, array( 'mba_phone', 'mba_secondary_phone' ), true ) ) {
		$url = mba_core_phone_url( $key );
		$value = mba_core_phone_display( $key );
	} elseif ( 'mba_whatsapp' === $key ) {
		$url = mba_core_whatsapp_url();
		$value = mba_core_phone_display( $key );
	} elseif ( 'mba_email' === $key ) {
		$url = 'mailto:' . $value;
	} elseif ( str_ends_with( $key, '_url' ) ) {
		$url = (string) $value;
	}
	if ( $url ) {
		return '<a class="mba-company-detail" href="' . esc_url( $url ) . '">' . esc_html( $label ? $label : (string) $value ) . '</a>';
	}
	return '<span class="mba-company-detail">' . nl2br( esc_html( (string) $value ) ) . '</span>';
}

/**
 * Grant only the custom settings capability to owner-facing roles.
 */
function mba_core_install_owner_caps(): void {
	foreach ( array( 'administrator', 'editor' ) as $name ) {
		$role = get_role( $name );
		if ( $role ) {
			$role->add_cap( 'manage_mba_settings' );
		}
	}
	update_option( 'mba_owner_caps_version', '1', false );
}

/**
 * Upgrade capabilities once on existing installations.
 */
function mba_core_upgrade_owner_caps(): void {
	if ( '1' !== get_option( 'mba_owner_caps_version' ) ) {
		mba_core_install_owner_caps();
	}
}
add_action( 'init', 'mba_core_upgrade_owner_caps' );

/**
 * Authorize this settings group without general admin access.
 *
 * @return string
 */
function mba_core_settings_capability(): string {
	return 'manage_mba_settings';
}
add_filter( 'option_page_capability_mba_settings_group', 'mba_core_settings_capability' );

/**
 * Register the owner settings screen.
 */
function mba_core_add_settings_page(): void {
	add_options_page( __( 'MBA Settings', 'mba-site-core' ), __( 'MBA Settings', 'mba-site-core' ), 'manage_mba_settings', 'mba-settings', 'mba_core_render_settings_page' );
}
add_action( 'admin_menu', 'mba_core_add_settings_page' );

/**
 * Register the form.
 */
function mba_core_register_settings(): void {
	register_setting(
		'mba_settings_group',
		'mba_site_settings',
		array(
			'type' => 'object',
			'default' => array(),
			'sanitize_callback' => 'mba_core_sanitize_settings',
		)
	);
	add_settings_section( 'mba_business_details', __( 'Business details', 'mba-site-core' ), '__return_false', 'mba-settings' );
	foreach ( mba_core_settings_fields() as $key => $field ) {
		add_settings_field(
			$key,
			$field['label'],
			'mba_core_render_setting',
			'mba-settings',
			'mba_business_details',
			array(
				'key' => $key,
				'label_for' => $key,
			)
		);
	}
}
add_action( 'admin_init', 'mba_core_register_settings' );

/**
 * Render a typed field.
 *
 * @param array<string,string> $args Field arguments.
 */
function mba_core_render_setting( array $args ): void {
	$key = $args['key'];
	$field = mba_core_settings_fields()[ $key ];
	$value = mba_core_setting( $key );
	$name = 'mba_site_settings[' . $key . ']';
	if ( in_array( $field['type'], array( 'image', 'favicon' ), true ) ) {
		printf( '<div class="mba-entry-image"><input type="hidden" id="%1$s" name="%2$s" value="%3$s"><div class="mba-entry-image-preview">%4$s</div><button type="button" class="button mba-entry-image-choose">%5$s</button> <button type="button" class="button-link-delete mba-entry-image-remove">%6$s</button></div>', esc_attr( $key ), esc_attr( $name ), esc_attr( (string) $value ), $value ? wp_kses_post( wp_get_attachment_image( (int) $value, 'thumbnail' ) ) : '', esc_html__( 'Choose image', 'mba-site-core' ), esc_html__( 'Remove image', 'mba-site-core' ) );
	} elseif ( 'gallery' === $field['type'] ) {
		$ids = array();
		if ( is_array( $value ) ) {
			$ids = $value;
		}
		printf( '<div class="mba-settings-gallery"><input type="hidden" class="mba-settings-gallery-ids" name="%1$s" value="%2$s"><div class="mba-settings-gallery-list">', esc_attr( $name ), esc_attr( implode( ',', $ids ) ) );
		if ( $ids ) {
			foreach ( $ids as $id ) {
				echo '<span data-id="' . esc_attr( (string) $id ) . '">' . wp_kses_post( wp_get_attachment_image( (int) $id, 'thumbnail' ) ) . '</span>';
			}
		}
		echo '</div><button type="button" class="button mba-settings-gallery-choose">' . esc_html__( 'Choose gallery images', 'mba-site-core' ) . '</button></div>';
	} elseif ( in_array( $field['type'], array( 'textarea', 'redirects' ), true ) ) {
		printf( '<textarea class="large-text" rows="3" id="%1$s" name="%2$s">%3$s</textarea>', esc_attr( $key ), esc_attr( $name ), esc_textarea( (string) $value ) );
	} elseif ( 'checkbox' === $field['type'] ) {
		printf( '<input type="hidden" name="%1$s" value="0"><label><input type="checkbox" id="%2$s" name="%1$s" value="1"%3$s> %4$s</label>', esc_attr( $name ), esc_attr( $key ), checked( (bool) $value, true, false ), esc_html__( 'Enabled', 'mba-site-core' ) );
	} else {
		$type = in_array( $field['type'], array( 'url', 'map' ), true ) ? 'url' : ( 'phone' === $field['type'] ? 'tel' : $field['type'] );
		printf( '<input class="regular-text" type="%1$s" id="%2$s" name="%3$s" value="%4$s">', esc_attr( $type ), esc_attr( $key ), esc_attr( $name ), esc_attr( (string) $value ) );
	}
	if ( $field['help'] ) {
		printf( '<p class="description">%s</p>', esc_html( $field['help'] ) );
	}
}

/**
 * Load media helpers on this screen only.
 *
 * @param string $hook Admin screen.
 */
function mba_core_settings_assets( string $hook ): void {
	if ( 'settings_page_mba-settings' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script( 'mba-entry-admin', plugins_url( 'assets/js/entry-admin.js', dirname( __DIR__ ) . '/mba-site-core.php' ), array( 'jquery' ), MBA_CORE_VERSION, true );
	wp_localize_script( 'mba-entry-admin', 'mbaEntryAdmin', array( 'choose' => __( 'Choose company image', 'mba-site-core' ) ) );
}
add_action( 'admin_enqueue_scripts', 'mba_core_settings_assets' );

/**
 * Sync the native favicon after settings change.
 *
 * @param mixed $old Previous settings (or option name on first creation).
 * @param mixed $new New settings.
 */
function mba_core_sync_site_icon( $old, $new ): void {
	unset( $old );
	if ( is_array( $new ) && isset( $new['mba_favicon_id'] ) ) {
		update_option( 'site_icon', mba_core_validate_setting( 'favicon', $new['mba_favicon_id'] ) );
	}
}
add_action( 'update_option_mba_site_settings', 'mba_core_sync_site_icon', 10, 2 );
add_action( 'add_option_mba_site_settings', 'mba_core_sync_site_icon', 10, 2 );

/**
 * Render the native nonce-protected settings form.
 */
function mba_core_render_settings_page(): void {
	if ( ! current_user_can( 'manage_mba_settings' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'MBA Settings', 'mba-site-core' ); ?></h1>
		<p><?php esc_html_e( 'Update company details once for use throughout the website. All fields are optional. Leave unconfirmed details blank; no sample business details are published.', 'mba-site-core' ); ?></p>
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
