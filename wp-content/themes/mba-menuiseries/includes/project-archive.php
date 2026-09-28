<?php
/**
 * Shareable project archive.
 *
 * @package MBA_Menuiseries
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mba_theme_register_project_archive_block(): void {
	register_block_type( dirname( __DIR__ ) . '/blocks/project-archive', array( 'render_callback' => 'mba_theme_render_project_archive' ) );
}
add_action( 'init', 'mba_theme_register_project_archive_block' );

function mba_project_archive_card( WP_Post $project ): string {
	$city = (string) get_post_meta( $project->ID, 'mba_location_city', true );
	$region = (string) get_post_meta( $project->ID, 'mba_location_region', true );
	$year = (string) get_post_meta( $project->ID, 'mba_completion_date', true );
	$year = $year ? substr( $year, 0, 4 ) : '';
	$products = array();
	foreach ( (array) get_post_meta( $project->ID, 'mba_related_products', true ) as $product_id ) {
		if ( 'mba_product' === get_post_type( $product_id ) ) {
			$products[] = get_the_title( $product_id );
		}
	}
	$meta = array_filter( array( trim( $city . ( $region ? ', ' . $region : '' ) ), $year, $products ? implode( ', ', $products ) : '' ) );
	$html = '<article class="mba-card mba-project-card">';
	if ( has_post_thumbnail( $project ) ) {
		$html .= '<a href="' . esc_url( get_permalink( $project ) ) . '">' . get_the_post_thumbnail(
			$project,
			'mba-card',
			array(
				'loading' => 'lazy',
				'decoding' => 'async',
				'sizes' => '(max-width: 650px) 100vw, (max-width: 1023px) 50vw, 33vw',
			)
		) . '</a>';
	}
	$html .= '<div class="mba-card__body"><h2><a href="' . esc_url( get_permalink( $project ) ) . '">' . esc_html( get_the_title( $project ) ) . '</a></h2>' . ( $meta ? '<p class="mba-card__meta">' . esc_html( implode( ' · ', $meta ) ) . '</p>' : '' ) . '</div></article>';
	return $html;
}

function mba_theme_render_project_archive(): string {
	$filters = array(
		'type' => isset( $_GET['type_projet'] ) ? sanitize_title( wp_unslash( $_GET['type_projet'] ) ) : '',
		'location' => isset( $_GET['lieu'] ) ? sanitize_title( wp_unslash( $_GET['lieu'] ) ) : '',
		'product' => isset( $_GET['produit'] ) ? absint( $_GET['produit'] ) : 0,
	);
	$tax_query = array( 'relation' => 'AND' );
	foreach ( array(
		'type' => 'mba_project_type',
		'location' => 'mba_project_location',
	) as $key => $taxonomy ) {
		if ( $filters[ $key ] ) {
			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'field' => 'slug',
				'terms' => $filters[ $key ],
			);
		}
	}
	$args = array(
		'post_type' => 'mba_project',
		'post_status' => 'publish',
		'posts_per_page' => 9,
		'paged' => max( 1, (int) get_query_var( 'paged' ) ),
		'meta_key' => 'mba_featured',
		'orderby' => array(
			'meta_value_num' => 'DESC',
			'date' => 'DESC',
		),
		'order' => 'DESC',
	);
	if ( count( $tax_query ) > 1 ) {
		$args['tax_query'] = $tax_query;
	}
	if ( $filters['product'] ) {
		$args['meta_query'] = array(
			array(
				'key' => 'mba_related_products',
				'value' => 'i:' . $filters['product'] . ';',
				'compare' => 'LIKE',
			),
		);
	}
	$query = new WP_Query( $args );
	$types = get_terms(
		array(
			'taxonomy' => 'mba_project_type',
			'hide_empty' => true,
			'orderby' => 'name',
		)
	);
	$locations = get_terms(
		array(
			'taxonomy' => 'mba_project_location',
			'hide_empty' => true,
			'orderby' => 'name',
		)
	);
	$products = get_posts(
		array(
			'post_type' => 'mba_product',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
		)
	);
	$html = '<main id="main" tabindex="-1" class="mba-project-archive"><section class="mba-section" aria-labelledby="mba-projects-title"><h1 id="mba-projects-title">' . esc_html__( 'Réalisations', 'mba-menuiseries' ) . '</h1><p>' . esc_html__( 'Découvrez des projets publiés par MBA. Les adresses privées ne sont jamais affichées.', 'mba-menuiseries' ) . '</p><form class="mba-archive-filters" method="get" action="' . esc_url( get_post_type_archive_link( 'mba_project' ) ) . '">';
	foreach ( array(
		'type' => array( __( 'Type de projet', 'mba-menuiseries' ), $types, 'type_projet', 'Toutes les catégories' ),
		'location' => array( __( 'Zone générale', 'mba-menuiseries' ), $locations, 'lieu', 'Toutes les zones' ),
	) as $key => $config ) {
		$html .= '<div><label for="mba-' . esc_attr( $key ) . '-filter">' . esc_html( $config[0] ) . '</label><select id="mba-' . esc_attr( $key ) . '-filter" name="' . esc_attr( $config[2] ) . '"><option value="">' . esc_html( $config[3] ) . '</option>';
		if ( ! is_wp_error( $config[1] ) ) {
			foreach ( $config[1] as $term ) {
				$html .= '<option value="' . esc_attr( $term->slug ) . '" ' . selected( $filters[ $key ], $term->slug, false ) . '>' . esc_html( $term->name ) . '</option>';
			}
		}
		$html .= '</select></div>';
	}
	$html .= '<div><label for="mba-product-filter">' . esc_html__( 'Produit installé', 'mba-menuiseries' ) . '</label><select id="mba-product-filter" name="produit"><option value="">' . esc_html__( 'Tous les produits', 'mba-menuiseries' ) . '</option>';
	foreach ( $products as $product ) {
		$html .= '<option value="' . esc_attr( (string) $product->ID ) . '" ' . selected( $filters['product'], $product->ID, false ) . '>' . esc_html( $product->post_title ) . '</option>';
	}
	$html .= '</select></div><button class="wp-element-button" type="submit">' . esc_html__( 'Filtrer', 'mba-menuiseries' ) . '</button>';
	if ( $filters['type'] || $filters['location'] || $filters['product'] ) {
		$html .= '<a class="mba-filter-reset" href="' . esc_url( get_post_type_archive_link( 'mba_project' ) ) . '">' . esc_html__( 'Réinitialiser', 'mba-menuiseries' ) . '</a>';
	}
	$html .= '</form>';
	if ( $query->have_posts() ) {
		$html .= '<div class="mba-product-grid">';
		while ( $query->have_posts() ) {
			$query->the_post();
			$html .= mba_project_archive_card( get_post() );
		}
		$html .= '</div>';
		$pagination = paginate_links(
			array(
				'total' => $query->max_num_pages,
				'current' => max( 1, (int) get_query_var( 'paged' ) ),
				'type' => 'list',
				'add_args' => array_filter(
					array(
						'type_projet' => $filters['type'],
						'lieu' => $filters['location'],
						'produit' => $filters['product'],
					)
				),
			)
		);
		if ( $pagination ) {
			$html .= '<nav class="mba-pagination" aria-label="' . esc_attr__( 'Pagination des réalisations', 'mba-menuiseries' ) . '">' . wp_kses_post( $pagination ) . '</nav>';
		}
	} else {
		$html .= '<div class="mba-empty-state" role="status"><h2>' . esc_html__( 'Aucune réalisation trouvée', 'mba-menuiseries' ) . '</h2><p>' . esc_html__( 'Essayez de réinitialiser les filtres ou demandez conseil à MBA.', 'mba-menuiseries' ) . '</p><p><a class="wp-element-button" href="' . esc_url( get_post_type_archive_link( 'mba_project' ) ) . '">' . esc_html__( 'Voir toutes les réalisations', 'mba-menuiseries' ) . '</a> <a class="wp-element-button" href="' . esc_url( home_url( '/devis/' ) ) . '">' . esc_html__( 'Demander un devis', 'mba-menuiseries' ) . '</a></p></div>';
	}
	wp_reset_postdata();
	return $html . '</section></main>';
}
