<?php
/**
 * Content types and taxonomies.
 *
 * @package MBA_Site_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register a public content type with editor-friendly defaults.
 */
function mba_core_register_post_type( string $slug, string $singular, string $plural, string $archive ): void {
	register_post_type(
		$slug,
		array(
			'labels'       => array(
				'name'          => $plural,
				'singular_name' => $singular,
				'add_new_item'  => sprintf( __( 'Add New %s', 'mba-site-core' ), $singular ),
				'edit_item'     => sprintf( __( 'Edit %s', 'mba-site-core' ), $singular ),
			),
			'public'       => true,
			'show_in_rest' => true,
			'has_archive'  => $archive,
			'rewrite'      => array( 'slug' => $archive ),
			'menu_icon'    => 'dashicons-format-gallery',
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
		)
	);
}

/**
 * Register project and catalogue data independently from the theme.
 */
function mba_core_register_content_types(): void {
	mba_core_register_post_type( 'mba_product', __( 'Product', 'mba-site-core' ), __( 'Products', 'mba-site-core' ), 'produits' );
	mba_core_register_post_type( 'mba_project', __( 'Project', 'mba-site-core' ), __( 'Projects', 'mba-site-core' ), 'realisations' );
	mba_core_register_post_type( 'mba_faq', __( 'FAQ', 'mba-site-core' ), __( 'FAQs', 'mba-site-core' ), 'faq-items' );
	mba_core_register_post_type( 'mba_testimonial', __( 'Testimonial', 'mba-site-core' ), __( 'Testimonials', 'mba-site-core' ), 'temoignages' );
	mba_core_register_post_type( 'mba_partner', __( 'Partner', 'mba-site-core' ), __( 'Partners', 'mba-site-core' ), 'partenaires' );

	register_taxonomy(
		'mba_product_category',
		array( 'mba_product', 'mba_project' ),
		array(
			'labels'            => array(
				'name'          => __( 'Product Categories', 'mba-site-core' ),
				'singular_name' => __( 'Product Category', 'mba-site-core' ),
			),
			'public'            => true,
			'show_in_rest'      => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => 'categorie-produit' ),
		)
	);

	register_taxonomy(
		'mba_project_type',
		'mba_project',
		array(
			'labels'            => array(
				'name'          => __( 'Project Types', 'mba-site-core' ),
				'singular_name' => __( 'Project Type', 'mba-site-core' ),
			),
			'public'            => true,
			'show_in_rest'      => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => 'type-projet' ),
		)
	);

	register_taxonomy(
		'mba_faq_category',
		'mba_faq',
		array(
			'labels'            => array(
				'name'          => __( 'FAQ Categories', 'mba-site-core' ),
				'singular_name' => __( 'FAQ Category', 'mba-site-core' ),
			),
			'public'            => false,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
		)
	);
}
add_action( 'init', 'mba_core_register_content_types' );

