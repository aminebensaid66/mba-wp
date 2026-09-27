<?php
/**
 * Structured product detail page.
 *
 * @package MBA_Menuiseries
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mba_theme_register_product_detail_block(): void {
	register_block_type( dirname( __DIR__ ) . '/blocks/product-detail', array( 'render_callback' => 'mba_theme_render_product_detail' ) );
}
add_action( 'init', 'mba_theme_register_product_detail_block' );

function mba_product_detail_meta( int $id, string $key, $default = '' ) {
	$value = get_post_meta( $id, $key, true );
	return '' === $value ? $default : $value;
}

function mba_product_detail_section( string $id, string $title, string $content ): string {
	return $content ? '<section class="mba-section mba-product-detail-section" aria-labelledby="' . esc_attr( $id ) . '"><h2 id="' . esc_attr( $id ) . '">' . esc_html( $title ) . '</h2>' . $content . '</section>' : '';
}

function mba_product_detail_list( $values ): string {
	$values = is_array( $values ) ? array_filter( array_map( 'strval', $values ) ) : array();
	if ( ! $values ) {
		return '';
	}
	$html = '<ul class="mba-detail-list">';
	foreach ( $values as $value ) {
		$html .= '<li>' . esc_html( $value ) . '</li>';
	}
	return $html . '</ul>';
}

function mba_product_detail_related_cards( array $ids, string $class = '' ): string {
	$ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
	if ( ! $ids ) {
		return '';
	}
	$query = new WP_Query(
		array(
			'post_type' => array( 'mba_product', 'mba_project' ),
			'post__in' => $ids,
			'post_status' => 'publish',
			'posts_per_page' => count( $ids ),
			'orderby' => 'post__in',
			'no_found_rows' => true,
		)
	);
	if ( ! $query->have_posts() ) {
		return '';
	}
	$html = '<div class="mba-home-cards ' . esc_attr( $class ) . '">';
	while ( $query->have_posts() ) {
		$query->the_post();
		$html .= '<article class="mba-card">' . ( has_post_thumbnail() ? '<a href="' . esc_url( get_permalink() ) . '">' . get_the_post_thumbnail( get_the_ID(), 'large', array( 'loading' => 'lazy' ) ) . '</a>' : '' ) . '<div class="mba-card__body"><h3><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h3></div></article>';
	}
	wp_reset_postdata();
	return $html . '</div>';
}

function mba_theme_render_product_detail(): string {
	$product = get_post();
	if ( ! $product || 'mba_product' !== $product->post_type ) {
		return '';
	}
	$id = (int) $product->ID;
	$title = get_the_title( $product );
	$html = '<main id="main" class="mba-product-detail"><section class="mba-product-hero mba-section" aria-labelledby="mba-product-title">' . ( has_post_thumbnail( $product ) ? '<div class="mba-product-hero__media">' . get_the_post_thumbnail( $product, 'large', array( 'loading' => 'eager' ) ) . '</div>' : '' ) . '<div><p class="mba-eyebrow">' . esc_html__( 'Produit', 'mba-menuiseries' ) . '</p><h1 id="mba-product-title">' . esc_html( $title ) . '</h1>' . ( mba_product_detail_meta( $id, 'mba_short_description' ) ? '<p class="mba-lede">' . nl2br( esc_html( mba_product_detail_meta( $id, 'mba_short_description' ) ) ) . '</p>' : '' ) . '</div></section>';
	$html .= mba_product_detail_section( 'mba-benefits-heading', __( 'Bénéfices', 'mba-menuiseries' ), mba_product_detail_list( mba_product_detail_meta( $id, 'mba_benefits', array() ) ) );
	$html .= mba_product_detail_section( 'mba-configurations-heading', __( 'Configurations et options', 'mba-menuiseries' ), mba_product_detail_list( array_merge( (array) mba_product_detail_meta( $id, 'mba_configurations', array() ), (array) mba_product_detail_meta( $id, 'mba_glazing_options', array() ), (array) mba_product_detail_meta( $id, 'mba_applications', array() ) ) ) );
	foreach ( array(
		'mba_materials_profiles' => __( 'Matériaux et profils', 'mba-menuiseries' ),
		'mba_maintenance' => __( 'Entretien', 'mba-menuiseries' ),
	) as $key => $label ) {
		$value = (string) mba_product_detail_meta( $id, $key );
		$html .= mba_product_detail_section( $key . '-heading', $label, $value ? '<div class="mba-rich-text">' . wp_kses_post( $value ) . '</div>' : '' );
	}
	$finishes = mba_product_detail_meta( $id, 'mba_colors_finishes', array() );
	$finish_html = '';
	if ( is_array( $finishes ) ) {
		$finish_html = '<ul class="mba-detail-list">';
		foreach ( $finishes as $finish ) {
			if ( ! empty( $finish['label'] ) ) {
				$finish_html .= '<li>' . esc_html( $finish['label'] ) . '</li>';
			}
		}
		$finish_html .= '</ul>';
	}
	$html .= mba_product_detail_section( 'mba-finishes-heading', __( 'Couleurs et finitions', 'mba-menuiseries' ), $finish_html );
	$performance = mba_product_detail_meta( $id, 'mba_performance_details', array() );
	$performance_html = '';
	if ( is_array( $performance ) ) {
		$performance_html = '<dl class="mba-detail-specs">';
		foreach ( $performance as $row ) {
			if ( ! empty( $row['label'] ) && ! empty( $row['value'] ) ) {
				$performance_html .= '<dt>' . esc_html( $row['label'] ) . '</dt><dd>' . esc_html( $row['value'] ) . '</dd>';
			}
		}
		$performance_html .= '</dl>';
	}
	$html .= mba_product_detail_section( 'mba-performance-heading', __( 'Performances vérifiées', 'mba-menuiseries' ), $performance_html );
	$gallery = mba_product_detail_meta( $id, 'mba_gallery', array() );
	$gallery_html = '';
	if ( is_array( $gallery ) && $gallery ) {
		$gallery_html = '<div class="mba-product-gallery">';
		foreach ( $gallery as $image_id ) {
			$full = wp_get_attachment_image_url( (int) $image_id, 'full' );
			$thumb = wp_get_attachment_image( (int) $image_id, 'large', false, array( 'loading' => 'lazy' ) );
			if ( $full && $thumb ) {
				$gallery_html .= '<a href="' . esc_url( $full ) . '" data-lightbox="mba-product-' . esc_attr( (string) $id ) . '">' . $thumb . '</a>';
			}
		}
		$gallery_html .= '</div>';
	}
	$html .= mba_product_detail_section( 'mba-gallery-heading', __( 'Galerie', 'mba-menuiseries' ), $gallery_html );
	$document = (int) mba_product_detail_meta( $id, 'mba_technical_document', 0 );
	if ( $document ) {
		$url = wp_get_attachment_url( $document );
		$file = get_attached_file( $document );
		$size = $file && file_exists( $file ) ? filesize( $file ) : 0;
		$size_label = $size ? size_format( $size ) : '';
		$html .= mba_product_detail_section( 'mba-document-heading', __( 'Document technique', 'mba-menuiseries' ), $url ? '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Télécharger le PDF', 'mba-menuiseries' ) . '</a> <span class="mba-file-meta">application/pdf' . ( $size_label ? ', ' . esc_html( $size_label ) : '' ) . '</span>' : '' );
	}
	$projects = mba_product_detail_meta( $id, 'mba_related_projects', array() );
	$html .= mba_product_detail_section( 'mba-related-projects-heading', __( 'Réalisations associées', 'mba-menuiseries' ), mba_product_detail_related_cards( is_array( $projects ) ? $projects : array() ) );
	$faqs = mba_product_detail_meta( $id, 'mba_related_faqs', array() );
	$faq_html = '';
	if ( is_array( $faqs ) && $faqs ) {
		$faq_posts = get_posts(
			array(
				'post_type' => 'mba_faq',
				'post__in' => array_map( 'absint', $faqs ),
				'post_status' => 'publish',
				'posts_per_page' => count( $faqs ),
				'orderby' => 'post__in',
			)
		);
		foreach ( $faq_posts as $faq ) {
			$faq_html .= '<details><summary>' . esc_html( get_the_title( $faq ) ) . '</summary><div>' . wp_kses_post( apply_filters( 'the_content', $faq->post_content ) ) . '</div></details>';
		}
	}
	$html .= mba_product_detail_section( 'mba-related-faqs-heading', __( 'Questions fréquentes', 'mba-menuiseries' ), $faq_html );
	$related = get_posts(
		array(
			'post_type' => 'mba_product',
			'post__not_in' => array( $id ),
			'post_status' => 'publish',
			'posts_per_page' => 3,
			'orderby' => 'date',
		)
	);
	$html .= mba_product_detail_section( 'mba-related-products-heading', __( 'Produits associés', 'mba-menuiseries' ), mba_product_detail_related_cards( wp_list_pluck( $related, 'ID' ) ) );
	$quote_url = add_query_arg(
		array(
			'product_id' => $id,
			'product_slug' => $product->post_name,
		),
		home_url( '/devis/' )
	);
	return $html . '<section class="mba-section mba-home-final-cta" aria-labelledby="mba-product-cta-heading"><h2 id="mba-product-cta-heading">' . esc_html__( 'Ce produit vous intéresse ?', 'mba-menuiseries' ) . '</h2><a class="wp-element-button" href="' . esc_url( $quote_url ) . '">' . esc_html__( 'Demander un devis', 'mba-menuiseries' ) . '</a></section></main>';
}
