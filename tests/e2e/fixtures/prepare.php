<?php
/** Seed only synthetic content in the disposable Playwright WordPress project. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( 1 );
}

$pages = array(
	'contact' => '<!-- wp:mba/contact-page /-->',
	'devis' => '<!-- wp:mba/quote-form /-->',
	'entreprise' => '<!-- wp:mba/company-page /-->',
	'faq' => '<!-- wp:mba/faq /-->',
	'conseils' => '<!-- wp:mba/blog-archive /-->',
	'confidentialite' => '<!-- wp:paragraph --><p>Test privacy notice.</p><!-- /wp:paragraph -->',
);
foreach ( $pages as $slug => $content ) {
	$page_id = wp_insert_post(
		array(
			'post_type' => 'page',
			'post_status' => 'publish',
			'post_title' => 'MBA E2E ' . ucfirst( $slug ),
			'post_name' => $slug,
			'post_content' => $content,
		),
		true
	);
	if ( is_wp_error( $page_id ) ) {
		WP_CLI::error( $page_id->get_error_message() );
	}
	update_post_meta( $page_id, '_mba_e2e_fixture', true );
}

$content_ids = array();
foreach ( array(
	'mba_product' => 'MBA E2E Product',
	'mba_project' => 'MBA E2E Project',
) as $post_type => $title ) {
	$post_id = wp_insert_post(
		array(
			'post_type' => $post_type,
			'post_status' => 'publish',
			'post_title' => $title,
			'post_name' => 'mba-e2e-' . ( 'mba_product' === $post_type ? 'product' : 'project' ),
			'post_content' => 'Synthetic content used only by the browser test suite.',
		),
		true
	);
	if ( is_wp_error( $post_id ) ) {
		WP_CLI::error( $post_id->get_error_message() );
	}
	update_post_meta( $post_id, '_mba_e2e_fixture', true );
	$content_ids[ $post_type ] = $post_id;
}

$product_category = wp_insert_term( 'MBA E2E Category', 'mba_product_category' );
if ( is_wp_error( $product_category ) ) {
	WP_CLI::error( $product_category->get_error_message() );
}
wp_set_object_terms( $content_ids['mba_product'], (int) $product_category['term_id'], 'mba_product_category' );

$project_type = wp_insert_term( 'MBA E2E Renovation', 'mba_project_type' );
if ( is_wp_error( $project_type ) ) {
	WP_CLI::error( $project_type->get_error_message() );
}
wp_set_object_terms( $content_ids['mba_project'], (int) $project_type['term_id'], 'mba_project_type' );
update_post_meta( $content_ids['mba_project'], 'mba_related_products', array( $content_ids['mba_product'] ) );
// The archive sorts by this key and excludes projects that have never saved it.
update_post_meta( $content_ids['mba_project'], 'mba_featured', 0 );

$image = base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true );
$upload = wp_upload_bits( 'mba-e2e-gallery.png', null, $image );
if ( $upload['error'] ) {
	WP_CLI::error( $upload['error'] );
}
$attachment_id = wp_insert_attachment(
	array(
		'post_mime_type' => $upload['type'],
		'post_title' => 'MBA E2E gallery photo',
		'post_excerpt' => 'Synthetic gallery caption.',
		'post_status' => 'inherit',
	),
	$upload['file'],
	$content_ids['mba_project'],
	true
);
if ( is_wp_error( $attachment_id ) ) {
	WP_CLI::error( $attachment_id->get_error_message() );
}
require_once ABSPATH . 'wp-admin/includes/image.php';
wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );
update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'Synthetic project gallery photo' );
update_post_meta( $content_ids['mba_project'], 'mba_gallery_before', array( (int) $attachment_id ) );

WP_CLI::success( 'Synthetic pages and public content created for end-to-end tests.' );
