<?php
/**
 * Durable content types and taxonomies.
 *
 * @package MBA_Site_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build complete owner-facing labels for a post type.
 *
 * @param string $singular Singular translated label.
 * @param string $plural   Plural translated label.
 * @return array<string, string>
 */
function mba_core_post_type_labels( string $singular, string $plural ): array {
	return array(
		'name'                  => $plural,
		'singular_name'         => $singular,
		'menu_name'             => $plural,
		'name_admin_bar'        => $singular,
		'add_new'               => __( 'Add New', 'mba-site-core' ),
		'add_new_item'          => sprintf( __( 'Add New %s', 'mba-site-core' ), $singular ),
		'new_item'              => sprintf( __( 'New %s', 'mba-site-core' ), $singular ),
		'edit_item'             => sprintf( __( 'Edit %s', 'mba-site-core' ), $singular ),
		'view_item'             => sprintf( __( 'View %s', 'mba-site-core' ), $singular ),
		'all_items'             => sprintf( __( 'All %s', 'mba-site-core' ), $plural ),
		'search_items'          => sprintf( __( 'Search %s', 'mba-site-core' ), $plural ),
		'not_found'             => sprintf( __( 'No %s found.', 'mba-site-core' ), strtolower( $plural ) ),
		'not_found_in_trash'    => sprintf( __( 'No %s found in Trash.', 'mba-site-core' ), strtolower( $plural ) ),
		'featured_image'        => __( 'Cover image', 'mba-site-core' ),
		'set_featured_image'    => __( 'Set cover image', 'mba-site-core' ),
		'remove_featured_image' => __( 'Remove cover image', 'mba-site-core' ),
		'use_featured_image'    => __( 'Use as cover image', 'mba-site-core' ),
	);
}

/**
 * Register all durable business content independently from the active theme.
 */
function mba_core_register_content_types(): void {
	$common = array(
		'show_ui'           => true,
		'show_in_rest'      => true,
		'map_meta_cap'      => true,
		'capability_type'   => 'post',
		'delete_with_user'  => false,
		'supports'          => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
	);

	register_post_type(
		'mba_product',
		array_merge(
			$common,
			array(
				'labels'             => mba_core_post_type_labels( __( 'Product', 'mba-site-core' ), __( 'Products', 'mba-site-core' ) ),
				'public'             => true,
				'publicly_queryable' => true,
				'exclude_from_search' => false,
				'has_archive'        => 'produits',
				'rewrite'            => array(
					'slug' => 'produits',
					'with_front' => false,
				),
				'menu_icon'          => 'dashicons-screenoptions',
				'menu_position'      => 20,
			)
		)
	);

	register_post_type(
		'mba_project',
		array_merge(
			$common,
			array(
				'labels'             => mba_core_post_type_labels( __( 'Project', 'mba-site-core' ), __( 'Projects', 'mba-site-core' ) ),
				'public'             => true,
				'publicly_queryable' => true,
				'exclude_from_search' => false,
				'has_archive'        => 'realisations',
				'rewrite'            => array(
					'slug' => 'realisations',
					'with_front' => false,
				),
				'menu_icon'          => 'dashicons-format-gallery',
				'menu_position'      => 21,
			)
		)
	);

	$editor_only = array(
		'public'              => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'rewrite'             => false,
		'show_ui'             => true,
		'show_in_rest'        => true,
		'map_meta_cap'        => true,
		'capability_type'     => 'post',
		'delete_with_user'    => false,
		'supports'            => array( 'title', 'editor', 'revisions', 'custom-fields' ),
	);

	register_post_type(
		'mba_faq',
		array_merge(
			$editor_only,
			array(
				'labels'        => mba_core_post_type_labels( __( 'FAQ', 'mba-site-core' ), __( 'FAQs', 'mba-site-core' ) ),
				'menu_icon'     => 'dashicons-editor-help',
				'menu_position' => 22,
			)
		)
	);

	register_post_type(
		'mba_testimonial',
		array_merge(
			$editor_only,
			array(
				'labels'        => mba_core_post_type_labels( __( 'Testimonial', 'mba-site-core' ), __( 'Testimonials', 'mba-site-core' ) ),
				'menu_icon'     => 'dashicons-testimonial',
				'menu_position' => 23,
			)
		)
	);

	register_post_type(
		'mba_partner',
		array_merge(
			$editor_only,
			array(
				'labels'        => mba_core_post_type_labels( __( 'Partner', 'mba-site-core' ), __( 'Partners', 'mba-site-core' ) ),
				'menu_icon'     => 'dashicons-groups',
				'menu_position' => 24,
			)
		)
	);

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
			'rewrite'           => array(
				'slug' => 'categorie-produit',
				'with_front' => false,
			),
		)
	);

	register_taxonomy(
		'mba_application_type',
		'mba_product',
		array(
			'labels'            => array(
				'name'          => __( 'Application Types', 'mba-site-core' ),
				'singular_name' => __( 'Application Type', 'mba-site-core' ),
			),
			'public'            => true,
			'show_in_rest'      => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug' => 'application',
				'with_front' => false,
			),
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
			'rewrite'           => array(
				'slug' => 'type-projet',
				'with_front' => false,
			),
		)
	);

	register_taxonomy(
		'mba_project_location',
		'mba_project',
		array(
			'labels'            => array(
				'name'          => __( 'Project Locations', 'mba-site-core' ),
				'singular_name' => __( 'Project Location', 'mba-site-core' ),
			),
			'public'            => true,
			'show_in_rest'      => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug' => 'lieu-projet',
				'with_front' => false,
			),
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
			'publicly_queryable' => false,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'rewrite'           => false,
		)
	);
}
add_action( 'init', 'mba_core_register_content_types' );
