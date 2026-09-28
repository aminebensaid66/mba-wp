<?php
/**
 * Shareable, server-rendered product archive.
 *
 * @package MBA_Menuiseries
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mba_theme_register_product_archive_block(): void {
	register_block_type( dirname( __DIR__ ) . '/blocks/product-archive', array( 'render_callback' => 'mba_theme_render_product_archive' ) );
}
add_action( 'init', 'mba_theme_register_product_archive_block' );

function mba_product_archive_card( WP_Post $product ): string {
	$image = get_the_post_thumbnail(
		$product,
		'mba-card',
		array(
			'loading' => 'lazy',
			'decoding' => 'async',
			'sizes' => '(max-width: 650px) 100vw, (max-width: 1023px) 50vw, 33vw',
		)
	);
	$terms = get_the_terms( $product, 'mba_product_category' );
	$labels = array();
	if ( $terms && ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$labels[] = $term->name;
		}
	}
	$html = '<article class="mba-card mba-product-card">';
	if ( $image ) {
		$html .= '<a href="' . esc_url( get_permalink( $product ) ) . '">' . $image . '</a>';
	}
	$html .= '<div class="mba-card__body">' . ( $labels ? '<p class="mba-card__meta">' . esc_html( implode( ' · ', $labels ) ) . '</p>' : '' ) . '<h2><a href="' . esc_url( get_permalink( $product ) ) . '">' . esc_html( get_the_title( $product ) ) . '</a></h2>';
	$excerpt = get_the_excerpt( $product );
	if ( $excerpt ) {
		$html .= '<p>' . esc_html( $excerpt ) . '</p>';
	}
	return $html . '</div></article>';
}

function mba_theme_render_product_archive(): string {
	$category = isset( $_GET['categorie'] ) ? sanitize_title( wp_unslash( $_GET['categorie'] ) ) : '';
	$application = isset( $_GET['application'] ) ? sanitize_title( wp_unslash( $_GET['application'] ) ) : '';
	$tax_query = array( 'relation' => 'AND' );
	if ( $category ) {
		$tax_query[] = array(
			'taxonomy' => 'mba_product_category',
			'field' => 'slug',
			'terms' => $category,
		);
	}
	if ( $application ) {
		$tax_query[] = array(
			'taxonomy' => 'mba_application_type',
			'field' => 'slug',
			'terms' => $application,
		);
	}
	$args = array(
		'post_type'      => 'mba_product',
		'post_status'    => 'publish',
		'posts_per_page' => 9,
		'paged'          => max( 1, (int) get_query_var( 'paged' ) ),
		'orderby'        => 'date',
		'order'          => 'DESC',
	);
	if ( count( $tax_query ) > 1 ) {
		$args['tax_query'] = $tax_query;
	}
	$query = new WP_Query( $args );
	$categories = get_terms(
		array(
			'taxonomy' => 'mba_product_category',
			'hide_empty' => true,
			'orderby' => 'name',
		)
	);
	$applications = get_terms(
		array(
			'taxonomy' => 'mba_application_type',
			'hide_empty' => true,
			'orderby' => 'name',
		)
	);
	$html = '<main id="main" class="mba-product-archive"><section class="mba-section" aria-labelledby="mba-products-title"><h1 id="mba-products-title">' . esc_html__( 'Produits', 'mba-menuiseries' ) . '</h1><p>' . esc_html__( 'Explorez les produits publiés par MBA et utilisez les filtres pour trouver les options pertinentes.', 'mba-menuiseries' ) . '</p><form class="mba-archive-filters" method="get" action="' . esc_url( get_post_type_archive_link( 'mba_product' ) ) . '"><div><label for="mba-category-filter">' . esc_html__( 'Catégorie', 'mba-menuiseries' ) . '</label><select id="mba-category-filter" name="categorie"><option value="">' . esc_html__( 'Toutes les catégories', 'mba-menuiseries' ) . '</option>';
	if ( ! is_wp_error( $categories ) ) {
		foreach ( $categories as $term ) {
			$html .= '<option value="' . esc_attr( $term->slug ) . '" ' . selected( $category, $term->slug, false ) . '>' . esc_html( $term->name ) . '</option>';
		}
	}
	$html .= '</select></div><div><label for="mba-application-filter">' . esc_html__( 'Application', 'mba-menuiseries' ) . '</label><select id="mba-application-filter" name="application"><option value="">' . esc_html__( 'Toutes les applications', 'mba-menuiseries' ) . '</option>';
	if ( ! is_wp_error( $applications ) ) {
		foreach ( $applications as $term ) {
			$html .= '<option value="' . esc_attr( $term->slug ) . '" ' . selected( $application, $term->slug, false ) . '>' . esc_html( $term->name ) . '</option>';
		}
	}
	$html .= '</select></div><button class="wp-element-button" type="submit">' . esc_html__( 'Filtrer', 'mba-menuiseries' ) . '</button>';
	if ( $category || $application ) {
		$html .= '<a class="mba-filter-reset" href="' . esc_url( get_post_type_archive_link( 'mba_product' ) ) . '">' . esc_html__( 'Réinitialiser', 'mba-menuiseries' ) . '</a>';
	}
	$html .= '</form>';
	if ( $query->have_posts() ) {
		$html .= '<div class="mba-product-grid">';
		while ( $query->have_posts() ) {
			$query->the_post();
			$html .= mba_product_archive_card( get_post() );
		}
		$html .= '</div>';
		$pagination = paginate_links(
			array(
				'total' => $query->max_num_pages,
				'current' => max( 1, (int) get_query_var( 'paged' ) ),
				'type' => 'list',
				'add_args' => array_filter(
					array(
						'categorie' => $category,
						'application' => $application,
					)
				),
			)
		);
		if ( $pagination ) {
			$html .= '<nav class="mba-pagination" aria-label="' . esc_attr__( 'Pagination des produits', 'mba-menuiseries' ) . '">' . wp_kses_post( $pagination ) . '</nav>';
		}
	} else {
		$html .= '<div class="mba-empty-state" role="status"><h2>' . esc_html__( 'Aucun produit trouvé', 'mba-menuiseries' ) . '</h2><p>' . esc_html__( 'Essayez de réinitialiser les filtres ou demandez conseil à MBA.', 'mba-menuiseries' ) . '</p><p><a class="wp-element-button" href="' . esc_url( get_post_type_archive_link( 'mba_product' ) ) . '">' . esc_html__( 'Voir tous les produits', 'mba-menuiseries' ) . '</a> <a class="wp-element-button" href="' . esc_url( home_url( '/devis/' ) ) . '">' . esc_html__( 'Demander un devis', 'mba-menuiseries' ) . '</a></p></div>';
	}
	wp_reset_postdata();
	return $html . '</section></main>';
}
