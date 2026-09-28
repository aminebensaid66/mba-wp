<?php
/**
 * Seed unmistakable, unpublished content for a local WordPress development site.
 * Run only through bin/seed-sample-content.sh; this file is not loaded on site activation.
 */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( 1 );
}

foreach ( array( 'mba_product', 'mba_project', 'mba_faq' ) as $required_type ) {
	if ( ! post_type_exists( $required_type ) ) {
		WP_CLI::error( 'Activate MBA Site Core before seeding sample content.' );
	}
}

/**
 * Return a stable sample post, creating it as a draft only when absent.
 *
 * @param string               $key  Stable fixture key.
 * @param array<string, mixed> $args Post data.
 * @return int
 */
function mba_sample_post( string $key, array $args ): int {
	$existing = get_posts(
		array(
			'post_type' => $args['post_type'],
			'post_status' => array( 'draft', 'pending', 'private', 'publish' ),
			'posts_per_page' => 1,
			'fields' => 'ids',
			'meta_key' => '_mba_sample_key',
			'meta_value' => $key,
		)
	);
	if ( $existing ) {
		return (int) $existing[0];
	}

	$args['post_status'] = 'draft';
	$post_id = wp_insert_post( $args, true );
	if ( is_wp_error( $post_id ) ) {
		WP_CLI::error( $post_id->get_error_message() );
	}
	add_post_meta( $post_id, '_mba_sample_key', $key, true );
	add_post_meta( $post_id, '_mba_sample_content', 1, true );
	return (int) $post_id;
}

/**
 * Add a local illustration as a normal Media Library image attachment.
 *
 * @param string $key       Stable fixture key.
 * @param string $filename Illustration filename.
 * @param int    $parent_id Owning sample post.
 * @param string $alt       Explicit replacement reminder for image alt text.
 * @param string $caption   Explicit replacement reminder for caption.
 * @param int    $width     Intrinsic width.
 * @param int    $height    Intrinsic height.
 * @return int
 */
function mba_sample_attachment( string $key, string $filename, int $parent_id, string $alt, string $caption, int $width, int $height ): int {
	$existing = get_posts(
		array(
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'posts_per_page' => 1,
			'fields' => 'ids',
			'meta_key' => '_mba_sample_key',
			'meta_value' => $key,
		)
	);
	if ( $existing ) {
		return (int) $existing[0];
	}

	$source = __DIR__ . '/media/' . $filename;
	$contents = file_get_contents( $source );
	if ( false === $contents ) {
		WP_CLI::error( 'Cannot read bundled sample illustration: ' . $filename );
	}
	$allow_sample_svg = static function ( array $mimes ): array {
		$mimes['svg'] = 'image/svg+xml';
		return $mimes;
	};
	add_filter( 'upload_mimes', $allow_sample_svg );
	$upload = wp_upload_bits( $filename, null, $contents );
	remove_filter( 'upload_mimes', $allow_sample_svg );
	if ( $upload['error'] ) {
		WP_CLI::error( $upload['error'] );
	}
	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/svg+xml',
			'post_title' => 'EXEMPLE FICTIF À REMPLACER — ' . $caption,
			'post_excerpt' => $caption,
			'post_content' => 'Illustration SVG créée pour les tests locaux. Elle ne représente ni un bâtiment ni un projet MBA réel.',
			'post_status' => 'inherit',
		),
		$upload['file'],
		$parent_id,
		true
	);
	if ( is_wp_error( $attachment_id ) ) {
		WP_CLI::error( $attachment_id->get_error_message() );
	}
	add_post_meta( $attachment_id, '_mba_sample_key', $key, true );
	add_post_meta( $attachment_id, '_mba_sample_content', 1, true );
	add_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt, true );
	wp_update_attachment_metadata(
		$attachment_id,
		array(
			'file' => _wp_relative_upload_path( $upload['file'] ),
			'width' => $width,
			'height' => $height,
			'sizes' => array(),
			'image_meta' => array(),
		)
	);
	return (int) $attachment_id;
}

/**
 * Set a starter value only when an editor has not already saved that field.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Metadata key.
 * @param mixed  $value   Sample value.
 */
function mba_sample_meta_default( int $post_id, string $key, $value ): void {
	if ( ! metadata_exists( 'post', $post_id, $key ) ) {
		add_post_meta( $post_id, $key, $value, true );
	}
}

/**
 * Find or create a clearly labeled taxonomy term.
 *
 * @param string $name     Term name.
 * @param string $taxonomy Taxonomy name.
 * @return int
 */
function mba_sample_term( string $name, string $taxonomy ): int {
	$existing = term_exists( $name, $taxonomy );
	if ( $existing ) {
		return (int) ( is_array( $existing ) ? $existing['term_id'] : $existing );
	}
	$created = wp_insert_term( $name, $taxonomy );
	if ( is_wp_error( $created ) ) {
		WP_CLI::error( $created->get_error_message() );
	}
	return (int) $created['term_id'];
}

$notice = '<p><strong>EXEMPLE FICTIF — À REMPLACER AVANT PUBLICATION.</strong> Aucun élément de cette fiche ne décrit une offre, une réalisation ou une promesse MBA vérifiée.</p>';
$product_id = mba_sample_post(
	'product-window-example',
	array(
		'post_type' => 'mba_product',
		'post_title' => 'EXEMPLE À REMPLACER — Fenêtre fictive en aluminium',
		'post_name' => 'exemple-fictif-fenetre-a-remplacer',
		'post_excerpt' => 'Fiche de démonstration. Remplacer par une description produit validée par MBA.',
		'post_content' => $notice . '<p>Texte de démonstration uniquement. Confirmer le nom commercial, les options et la disponibilité auprès de MBA.</p>',
	)
);
$project_id = mba_sample_post(
	'project-renovation-example',
	array(
		'post_type' => 'mba_project',
		'post_title' => 'EXEMPLE FICTIF À REMPLACER — Projet de démonstration',
		'post_name' => 'exemple-fictif-projet-a-remplacer',
		'post_excerpt' => 'Projet inventé pour tester les galeries et les liens. Aucun client, lieu ou résultat réel.',
		'post_content' => $notice . '<p>Ce projet est entièrement fictif. N’ajoutez un récit réel qu’après accord du client et validation des faits.</p>',
	)
);
$faq_id = mba_sample_post(
	'faq-validation-example',
	array(
		'post_type' => 'mba_faq',
		'post_title' => 'EXEMPLE À REMPLACER — Quelles informations faut-il vérifier ?',
		'post_name' => 'exemple-fictif-question-a-remplacer',
		'post_content' => $notice . '<p>Réponse de démonstration : demander à MBA de valider les informations avant toute publication. Ne pas utiliser ce texte comme conseil produit.</p>',
	)
);

$category_id = mba_sample_term( 'EXEMPLE — Catégorie à remplacer', 'mba_product_category' );
$project_type_id = mba_sample_term( 'EXEMPLE — Type de projet à remplacer', 'mba_project_type' );
if ( ! get_the_terms( $product_id, 'mba_product_category' ) ) {
	wp_set_object_terms( $product_id, array( $category_id ), 'mba_product_category' );
}
if ( ! get_the_terms( $project_id, 'mba_project_type' ) ) {
	wp_set_object_terms( $project_id, array( $project_type_id ), 'mba_project_type' );
}

$landscape_id = mba_sample_attachment( 'illustration-landscape', 'sample-landscape.svg', $project_id, 'Illustration fictive au format paysage — remplacer par une image approuvée.', 'Exemple paysage — à remplacer', 1200, 800 );
$portrait_id = mba_sample_attachment( 'illustration-portrait', 'sample-portrait.svg', $project_id, 'Illustration fictive au format portrait — remplacer par une image approuvée.', 'Exemple portrait — à remplacer', 800, 1200 );
$square_id = mba_sample_attachment( 'illustration-square', 'sample-square.svg', $product_id, 'Illustration fictive au format carré — remplacer par une image approuvée.', 'Exemple carré — à remplacer', 1000, 1000 );

mba_sample_meta_default( $product_id, 'mba_short_description', 'Exemple incomplet : remplacer ce texte par une description approuvée.' );
mba_sample_meta_default( $product_id, 'mba_benefits', array( 'Champ de démonstration à remplacer' ) );
mba_sample_meta_default( $product_id, 'mba_gallery', array( $square_id, $portrait_id ) );
mba_sample_meta_default( $product_id, 'mba_related_projects', array( $project_id ) );
mba_sample_meta_default( $product_id, 'mba_related_faqs', array( $faq_id ) );
mba_sample_meta_default( $project_id, 'mba_location_city', '' ); // Intentionally missing: never invent client/location data.
mba_sample_meta_default( $project_id, 'mba_challenge', 'Champ incomplet : ajouter seulement un défi confirmé et autorisé.' );
mba_sample_meta_default( $project_id, 'mba_gallery_before', array( $portrait_id ) );
mba_sample_meta_default( $project_id, 'mba_gallery_during', array() ); // Intentionally missing to exercise an optional field.
mba_sample_meta_default( $project_id, 'mba_gallery_after', array( $landscape_id ) );
mba_sample_meta_default( $project_id, 'mba_related_products', array( $product_id ) );
mba_sample_meta_default( $faq_id, 'mba_related_products', array( $product_id ) );

WP_CLI::success( 'Local sample products, project, FAQ, relationships, and varied-ratio illustrations are ready as drafts. Reruns preserve existing edits.' );
