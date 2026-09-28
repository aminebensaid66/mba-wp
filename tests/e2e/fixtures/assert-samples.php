<?php
/** Assert the local content seed stays unpublished and its relationships/media are intact. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( 1 );
}

$fail = static function ( string $message ): void {
	WP_CLI::error( $message );
};
$expected_posts = array(
	'product-window-example' => 'mba_product',
	'project-renovation-example' => 'mba_project',
	'faq-validation-example' => 'mba_faq',
);
$sample_ids = array();
foreach ( $expected_posts as $key => $post_type ) {
	$matches = get_posts(
		array(
			'post_type' => $post_type,
			'post_status' => 'draft',
			'posts_per_page' => 2,
			'fields' => 'ids',
			'meta_key' => '_mba_sample_key',
			'meta_value' => $key,
		)
	);
	if ( 1 !== count( $matches ) || 1 !== (int) get_post_meta( $matches[0], '_mba_sample_content', true ) ) {
		$fail( 'Sample post missing, duplicated, or not marked as synthetic: ' . $key );
	}
	$sample_ids[ $key ] = (int) $matches[0];
}

$attachments = array();
foreach ( array( 'illustration-landscape', 'illustration-portrait', 'illustration-square' ) as $key ) {
	$matches = get_posts(
		array(
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'posts_per_page' => 2,
			'fields' => 'ids',
			'meta_key' => '_mba_sample_key',
			'meta_value' => $key,
		)
	);
	if ( 1 !== count( $matches ) || ! wp_attachment_is_image( $matches[0] ) ) {
		$fail( 'Sample illustration missing, duplicated, or not recognized as an image: ' . $key );
	}
	$attachment_id = (int) $matches[0];
	$metadata = wp_get_attachment_metadata( $attachment_id );
	if ( empty( $metadata['width'] ) || empty( $metadata['height'] ) || empty( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ) {
		$fail( 'Sample illustration metadata or alt text missing: ' . $key );
	}
	$attachments[ $key ] = $attachment_id;
}

$product_id = $sample_ids['product-window-example'];
$project_id = $sample_ids['project-renovation-example'];
$faq_id = $sample_ids['faq-validation-example'];
if ( array( $project_id ) !== get_post_meta( $product_id, 'mba_related_projects', true )
	|| array( $faq_id ) !== get_post_meta( $product_id, 'mba_related_faqs', true )
	|| array( $product_id ) !== get_post_meta( $project_id, 'mba_related_products', true )
	|| array( $product_id ) !== get_post_meta( $faq_id, 'mba_related_products', true ) ) {
	$fail( 'Sample product, project, and FAQ relationships are incomplete.' );
}
if ( 'MBA E2E owner edit must survive a seed rerun.' !== get_post_meta( $product_id, 'mba_short_description', true ) ) {
	$fail( 'Rerunning the sample seeder overwrote an editor value.' );
}
if ( array( $attachments['illustration-portrait'] ) !== get_post_meta( $project_id, 'mba_gallery_before', true )
	|| array( $attachments['illustration-landscape'] ) !== get_post_meta( $project_id, 'mba_gallery_after', true )
	|| array( $attachments['illustration-square'], $attachments['illustration-portrait'] ) !== get_post_meta( $product_id, 'mba_gallery', true ) ) {
	$fail( 'Sample galleries do not exercise varied image ratios.' );
}

WP_CLI::success( 'Three marked drafts, three varied-ratio Media Library images, and all sample relationships are valid.' );
