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

require dirname( __DIR__, 2 ) . '/wp-content/plugins/mba-site-core/includes/product-meta.php';
require dirname( __DIR__, 2 ) . '/wp-content/plugins/mba-site-core/includes/project-fields.php';

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
array() === mba_core_sanitize_performance_details( array( array( 'label' => 'U-value', 'value' => '' ) ) ) || $fail( 'Incomplete performance claim must be rejected.' );
array( array( 'label' => 'Silver', 'image_id' => 0 ) ) === mba_core_sanitize_colors_finishes( array( array( 'label' => 'Silver', 'image_id' => 12 ) ) ) || $fail( 'Non-image swatch must be rejected.' );
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
