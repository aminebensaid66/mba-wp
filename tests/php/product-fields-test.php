<?php
/**
 * Product metadata validation contract tests for issue #5.
 */

define( 'ABSPATH', __DIR__ );

$GLOBALS['mba_product_test_meta'] = array();
$GLOBALS['mba_product_test_types'] = array(
	11 => 'attachment',
	12 => 'attachment',
	21 => 'mba_faq',
	22 => 'mba_project',
	23 => 'mba_product',
	24 => 'mba_testimonial',
);

function add_action( $hook, $callback ) {
	$GLOBALS['mba_product_test_actions'][ $hook ][] = $callback;
}
function register_post_meta( $type, $key, $args ) {
	$GLOBALS['mba_product_test_meta'][ $type ][ $key ] = $args;
}
function absint( $value ) {
	return abs( (int) $value );
}
function sanitize_text_field( $value ) {
	return trim( strip_tags( $value ) );
}
function sanitize_textarea_field( $value ) {
	return sanitize_text_field( $value );
}
function wp_kses_post( $value ) {
	return strip_tags( $value, '<p><strong><em>' );
}
function rest_sanitize_boolean( $value ) {
	return (bool) $value;
}
function wp_attachment_is_image( $id ) {
	return 11 === $id;
}
function get_post_type( $id ) {
	return $GLOBALS['mba_product_test_types'][ $id ] ?? false;
}
function get_post_mime_type( $id ) {
	return 12 === $id ? 'application/pdf' : 'image/png';
}
function current_user_can( $capability, $post_id ) {
	return 'edit_post' === $capability && 23 === $post_id;
}
function esc_url_raw( $value, $protocols ) {
	return trim( $value );
}
function wp_parse_url( $value, $component ) {
	return parse_url( $value, $component );
}
function add_filter( $hook, $callback ) {
	$GLOBALS['mba_product_test_filters'][ $hook ][] = $callback;
}
function __( $text, $domain ) {
	return $text;
}
function is_email( $value ) {
	return filter_var( $value, FILTER_VALIDATE_EMAIL );
}
function sanitize_email( $value ) {
	return $value;
}
function wp_get_attachment_metadata( $id ) {
	return 11 === $id ? array(
		'width' => 1600,
		'height' => 1200,
	) : array();
}
function add_settings_error( $setting, $code, $message, $type ) {
	$GLOBALS['mba_product_test_errors'][] = $code;
}
function get_option( $key, $default = false ) {
	return $GLOBALS['mba_product_test_options'][ $key ] ?? $default;
}

require dirname( __DIR__, 2 ) . '/wp-content/plugins/mba-site-core/includes/product-meta.php';
require dirname( __DIR__, 2 ) . '/wp-content/plugins/mba-site-core/includes/project-fields.php';
require dirname( __DIR__, 2 ) . '/wp-content/plugins/mba-site-core/includes/reusable-content.php';
require dirname( __DIR__, 2 ) . '/wp-content/plugins/mba-site-core/includes/settings.php';

$fail = static function ( string $message ): void {
	fwrite( STDERR, $message . PHP_EOL );
	exit( 1 );
};

array( 11 ) === mba_core_sanitize_image_id_list( array( 11, 12, 11, 0 ) ) || $fail( 'Gallery must keep only valid unique images in order.' );
array() === mba_core_sanitize_id_list( array( -11, array( 11 ), 'invalid' ) ) || $fail( 'Malformed IDs must be rejected.' );
0 === mba_core_sanitize_pdf_attachment_id( 11 ) || $fail( 'Image must not be accepted as PDF.' );
0 === mba_core_sanitize_pdf_attachment_id( -12 ) || $fail( 'Negative PDF ID must be rejected.' );
12 === mba_core_sanitize_pdf_attachment_id( 12 ) || $fail( 'Valid PDF must be accepted.' );
array( 21 ) === mba_core_sanitize_faq_ids( array( 21, 22, 21 ) ) || $fail( 'FAQ IDs must reject other content types.' );
array( 22 ) === mba_core_sanitize_project_ids( array( 22, 23 ) ) || $fail( 'Project IDs must reject other content types.' );
array( 23 ) === mba_core_sanitize_product_ids( array( 23, 21 ) ) || $fail( 'Backlinks must contain only products.' );
9999 === mba_core_sanitize_display_order( 20000 ) || $fail( 'Order must be bounded.' );
0 === mba_core_sanitize_display_order( -4 ) || $fail( 'Negative order must become zero.' );
array() === mba_core_sanitize_performance_details(
	array(
		array(
			'label' => 'U-value',
			'value' => '',
		),
	)
) || $fail( 'Incomplete performance claim must be rejected.' );
array(
	array(
		'label' => 'Silver',
		'image_id' => 0,
	),
) === mba_core_sanitize_colors_finishes(
	array(
		array(
			'label' => 'Silver',
			'image_id' => 12,
		),
	)
) || $fail( 'Non-image swatch must be rejected.' );
false === mba_core_product_meta_auth( true, 'mba_gallery', 22 ) || $fail( 'Metadata auth must check the actual post.' );

mba_core_register_product_meta();
foreach ( array( 'mba_benefits', 'mba_configurations', 'mba_glazing_options', 'mba_colors_finishes', 'mba_performance_details', 'mba_applications', 'mba_gallery', 'mba_related_faqs', 'mba_related_projects', 'mba_technical_document', 'mba_featured', 'mba_display_order' ) as $key ) {
	isset( $GLOBALS['mba_product_test_meta']['mba_product'][ $key ] ) || $fail( "Missing product field {$key}." );
}
foreach ( array( 'mba_project', 'mba_faq' ) as $type ) {
	isset( $GLOBALS['mba_product_test_meta'][ $type ]['mba_related_products'] ) || $fail( "Missing {$type} backlink field." );
}

echo "Product metadata assertions passed.\n";

'' === mba_core_sanitize_project_date( '2025-02-29' ) || $fail( 'Invalid calendar dates must be rejected.' );
'' === mba_core_sanitize_project_date( '29/02/2024' ) || $fail( 'Ambiguous date formats must be rejected.' );
'2024-02-29' === mba_core_sanitize_project_date( '2024-02-29' ) || $fail( 'Real leap dates must be accepted.' );
24 === mba_core_sanitize_testimonial_id( 24 ) || $fail( 'Testimonial relationship must accept testimonials.' );
0 === mba_core_sanitize_testimonial_id( 23 ) || $fail( 'Testimonial relationship must reject products.' );
mba_core_register_project_meta();
foreach ( array( 'mba_location_city', 'mba_location_region', 'mba_completion_date', 'mba_challenge', 'mba_solution', 'mba_result', 'mba_materials', 'mba_profiles', 'mba_glazing', 'mba_colors', 'mba_gallery_before', 'mba_gallery_during', 'mba_gallery_after', 'mba_testimonial', 'mba_featured', 'mba_display_order' ) as $key ) {
	isset( $GLOBALS['mba_product_test_meta']['mba_project'][ $key ] ) || $fail( "Missing project field {$key}." );
}
! isset( $GLOBALS['mba_product_test_meta']['mba_project']['mba_street_address'] ) || $fail( 'Project fields must not request a private street address.' );
echo "Project metadata assertions passed.\n";

0 === mba_core_sanitize_rating( 6 ) || $fail( 'Out-of-range ratings must be rejected.' );
0 === mba_core_sanitize_rating( 1.5 ) || $fail( 'Fractional ratings must be rejected.' );
5 === mba_core_sanitize_rating( '5' ) || $fail( 'A supplied valid rating must be accepted.' );
'' === mba_core_sanitize_partner_url( 'javascript:alert(1)' ) || $fail( 'Unsafe partner URL scheme must be rejected.' );
'https://example.com' === mba_core_sanitize_partner_url( 'https://example.com' ) || $fail( 'HTTPS partner links must be accepted.' );
0 === mba_core_sanitize_image_id( 12 ) || $fail( 'Partner logos must reject PDF attachments.' );
mba_core_register_reusable_meta();
foreach ( array( 'mba_faq', 'mba_testimonial', 'mba_partner' ) as $type ) {
	isset( $GLOBALS['mba_product_test_meta'][ $type ]['mba_display_order'] ) || $fail( "Missing {$type} ordering." );
}
echo "Reusable content metadata assertions passed.\n";

'+21612345678' === mba_core_sanitize_phone( '+216 (12) 345-678' ) || $fail( 'International phone formatting must normalize safely.' );
'' === mba_core_sanitize_phone( '12345678' ) || $fail( 'Phone country code must not be guessed.' );
'' === mba_core_sanitize_phone( '+216call12345678' ) || $fail( 'Phone letters must not be silently removed.' );
$settings = mba_core_sanitize_settings(
	array(
		'mba_phone' => '+216 12 345 678',
		'mba_email' => 'invalid',
		'mba_maps_embed_url' => 'https://evil.example/maps/embed',
		'unknown' => 'untrusted',
	)
);
'+21612345678' === $settings['mba_phone'] || $fail( 'Canonical phone missing from settings.' );
'' === $settings['mba_email'] || $fail( 'Invalid email must be rejected.' );
'' === $settings['mba_maps_embed_url'] || $fail( 'Unapproved embed source must be rejected.' );
! isset( $settings['unknown'] ) || $fail( 'Unknown settings must be rejected.' );
0 === mba_core_validate_setting( 'favicon', 11 ) || $fail( 'Non-square favicon must be rejected.' );
$GLOBALS['mba_product_test_options']['mba_site_settings'] = $settings;
'+216 12 345 678' === mba_core_phone_display() || $fail( 'Display phone must derive from canonical global value.' );
'tel:+21612345678' === mba_core_phone_url() || $fail( 'Telephone link must use canonical global value.' );
'' === mba_core_whatsapp_url() || $fail( 'Missing WhatsApp must not invent a number.' );
$GLOBALS['mba_product_test_options']['mba_site_settings'] = mba_core_sanitize_settings(
	array(
		'mba_phone' => '+216 12 345 678',
		'mba_whatsapp' => '+216 98 765 432',
		'mba_whatsapp_message' => 'Bonjour MBA',
	)
);
'https://wa.me/21698765432?text=Bonjour%20MBA' === mba_core_whatsapp_url() || $fail( 'WhatsApp URL must use the international number and encoded editable draft.' );
'https://wa.me/21698765432?text=Bonjour%20MBA%0A%0AProduit%20%3A%20Fen%C3%AAtre' === mba_core_whatsapp_url( 'Produit : Fenêtre' ) || $fail( 'Contextual product/project text must be appended and URL encoded.' );
'' === mba_core_setting( 'mba_quote_response_time' ) || $fail( 'Missing response time must not invent a promise.' );
true === mba_core_validate_setting( 'checkbox', '1' ) || $fail( 'Quote email requirement setting must support an enabled value.' );
false === mba_core_validate_setting( 'checkbox', '0' ) || $fail( 'Quote email requirement setting must support a disabled value.' );
'' === mba_core_render_company_block( array( 'key' => 'unknown' ) ) || $fail( 'Unknown company block must render nothing.' );
foreach ( array( 'mba_homepage_hero_heading', 'mba_homepage_value_proposition', 'mba_homepage_trust_highlights', 'mba_homepage_process', 'mba_homepage_hero_image_id', 'mba_homepage_intro_image_id' ) as $key ) {
	isset( mba_core_settings_fields()[ $key ] ) || $fail( "Missing homepage setting {$key}." );
}
echo "Company settings assertions passed.\n";
