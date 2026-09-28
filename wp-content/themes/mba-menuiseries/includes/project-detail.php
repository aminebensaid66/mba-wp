<?php
/** Structured project case-study page. @package MBA_Menuiseries */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

function mba_theme_register_project_detail_block(): void {
	register_block_type( dirname( __DIR__ ) . '/blocks/project-detail', array( 'render_callback' => 'mba_theme_render_project_detail' ) );
}
add_action( 'init', 'mba_theme_register_project_detail_block' );

function mba_project_detail_meta( int $id, string $key, $default = '' ) {
	$value = get_post_meta( $id, $key, true );
	return '' === $value ? $default : $value;
}

function mba_project_detail_section( string $id, string $title, string $content ): string {
	return $content ? '<section class="mba-section mba-project-detail-section" aria-labelledby="' . esc_attr( $id ) . '"><h2 id="' . esc_attr( $id ) . '">' . esc_html( $title ) . '</h2>' . $content . '</section>' : '';
}

function mba_project_detail_gallery( int $project_id, string $key, string $label ): string {
	$images = mba_project_detail_meta( $project_id, $key, array() );
	if ( ! is_array( $images ) || ! $images ) {
		return ''; }
	$html = '<section class="mba-section mba-project-gallery-section" aria-labelledby="' . esc_attr( $key . '-heading' ) . '"><h2 id="' . esc_attr( $key . '-heading' ) . '">' . esc_html( $label ) . '</h2><div class="mba-project-gallery" role="list">';
	foreach ( $images as $image_id ) {
		$image_id = absint( $image_id );
		$full = wp_get_attachment_image_url( $image_id, 'full' );
		if ( ! $full ) {
			continue; }
		$caption = wp_get_attachment_caption( $image_id );
		$alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
		$alt = $alt ? $alt : sprintf( __( 'Photo %s du projet', 'mba-menuiseries' ), $label );
		$thumb = wp_get_attachment_image(
			$image_id,
			'mba-gallery',
			false,
			array(
				'loading' => 'lazy',
				'decoding' => 'async',
				'alt' => $alt,
				'sizes' => '(max-width: 650px) 100vw, (max-width: 1023px) 50vw, 33vw',
			)
		);
		if ( ! $thumb ) {
			continue; }
		$html .= '<figure role="listitem"><button type="button" class="mba-project-gallery__trigger" data-lightbox-open data-full-src="' . esc_url( $full ) . '" data-caption="' . esc_attr( $caption ) . '" data-alt="' . esc_attr( $alt ) . '" aria-label="' . esc_attr( sprintf( __( 'Ouvrir la photo : %s', 'mba-menuiseries' ), $label ) ) . '">' . $thumb . '</button>' . ( $caption ? '<figcaption>' . esc_html( $caption ) . '</figcaption>' : '' ) . '</figure>';
	}
	return $html . '</div></section>';
}

function mba_project_detail_related_projects( int $project_id ): string {
	$terms = wp_get_post_terms( $project_id, 'mba_project_type', array( 'fields' => 'ids' ) );
	$query = new WP_Query(
		array(
			'post_type' => 'mba_project',
			'post_status' => 'publish',
			'post__not_in' => array( $project_id ),
			'posts_per_page' => 3,
			'orderby' => 'date',
			'no_found_rows' => true,
			'tax_query' => $terms ? array(
				array(
					'taxonomy' => 'mba_project_type',
					'field' => 'term_id',
					'terms' => $terms,
				),
			) : array(),
		)
	);
	if ( ! $query->have_posts() ) {
		return ''; }
	$html = '<div class="mba-grid mba-project-related-grid">';
	while ( $query->have_posts() ) {
		$query->the_post();
		$url = add_query_arg( 'from_project', $project_id, get_permalink() );
		$html .= '<article class="mba-card">' . ( has_post_thumbnail() ? '<a href="' . esc_url( $url ) . '">' . get_the_post_thumbnail(
			get_the_ID(),
			'mba-card',
			array(
				'loading' => 'lazy',
				'decoding' => 'async',
				'sizes' => '(max-width: 650px) 100vw, (max-width: 1023px) 50vw, 33vw',
			)
		) . '</a>' : '' ) . '<div class="mba-card__body"><h3><a href="' . esc_url( $url ) . '">' . esc_html( get_the_title() ) . '</a></h3></div></article>';
	}
	wp_reset_postdata();
	return $html . '</div>';
}

function mba_theme_render_project_detail(): string {
	$project = get_post();
	if ( ! $project || 'mba_project' !== $project->post_type ) {
		return ''; }
	$id = (int) $project->ID;
	$location = trim( implode( ', ', array_filter( array( mba_project_detail_meta( $id, 'mba_location_city' ), mba_project_detail_meta( $id, 'mba_location_region' ) ) ) ) );
	$date = mba_project_detail_meta( $id, 'mba_completion_date' );
	$year = $date ? substr( (string) $date, 0, 4 ) : '';
	$hero = has_post_thumbnail( $project ) ? '<div class="mba-project-hero__media">' . get_the_post_thumbnail(
		$project,
		'mba-hero',
		array(
			'loading' => 'eager',
			'fetchpriority' => 'high',
			'decoding' => 'async',
			'sizes' => '(max-width: 767px) 100vw, 50vw',
		)
	) . '</div>' : '';
	$meta = array_filter( array( $location, $year ) );
	$html = '<main id="main" tabindex="-1" class="mba-project-detail"><section class="mba-project-hero mba-section" aria-labelledby="mba-project-title">' . $hero . '<div><p class="mba-eyebrow">' . esc_html__( 'Réalisation', 'mba-menuiseries' ) . '</p><h1 id="mba-project-title">' . esc_html( get_the_title( $project ) ) . '</h1>' . ( $meta ? '<p class="mba-card__meta">' . esc_html( implode( ' · ', $meta ) ) . '</p>' : '' ) . '</div></section>';
	foreach ( array(
		'mba_challenge' => __( 'Le défi', 'mba-menuiseries' ),
		'mba_solution' => __( 'La solution', 'mba-menuiseries' ),
		'mba_result' => __( 'Le résultat', 'mba-menuiseries' ),
	) as $key => $label ) {
		$html .= mba_project_detail_section( $key . '-heading', $label, wp_kses_post( mba_project_detail_meta( $id, $key ) ) ); }
	$specs = '';
	foreach ( array(
		'mba_materials' => __( 'Matériaux', 'mba-menuiseries' ),
		'mba_profiles' => __( 'Profils', 'mba-menuiseries' ),
		'mba_glazing' => __( 'Vitrage', 'mba-menuiseries' ),
		'mba_colors' => __( 'Couleurs', 'mba-menuiseries' ),
	) as $key => $label ) {
		$value = mba_project_detail_meta( $id, $key );
		if ( $value ) {
			$specs .= '<dt>' . esc_html( $label ) . '</dt><dd>' . nl2br( esc_html( $value ) ) . '</dd>'; }
	}
	$html .= mba_project_detail_section( 'mba-project-specifications-heading', __( 'Spécifications', 'mba-menuiseries' ), $specs ? '<dl class="mba-detail-specs">' . $specs . '</dl>' : '' );
	$html .= mba_project_detail_gallery( $id, 'mba_gallery_before', __( 'Avant', 'mba-menuiseries' ) ) . mba_project_detail_gallery( $id, 'mba_gallery_during', __( 'Pendant', 'mba-menuiseries' ) ) . mba_project_detail_gallery( $id, 'mba_gallery_after', __( 'Après', 'mba-menuiseries' ) );
	$testimonial_id = absint( mba_project_detail_meta( $id, 'mba_testimonial', 0 ) );
	if ( $testimonial_id && 'mba_testimonial' === get_post_type( $testimonial_id ) && 'publish' === get_post_status( $testimonial_id ) ) {
		$testimonial = get_post( $testimonial_id );
		$html .= mba_project_detail_section( 'mba-testimonial-heading', __( 'Témoignage client', 'mba-menuiseries' ), '<blockquote>' . wp_kses_post( apply_filters( 'the_content', $testimonial->post_content ) ) . '<cite>' . esc_html( $testimonial->post_title ) . '</cite></blockquote>' ); }
	$products = mba_project_detail_meta( $id, 'mba_related_products', array() );
	if ( is_array( $products ) && $products ) {
		$html .= mba_project_detail_section( 'mba-project-products-heading', __( 'Produits installés', 'mba-menuiseries' ), mba_product_detail_related_cards( $products ) ); }
	$html .= mba_project_detail_section( 'mba-project-related-heading', __( 'Projets similaires', 'mba-menuiseries' ), mba_project_detail_related_projects( $id ) );
	$quote_url = add_query_arg(
		array(
			'project_id' => $id,
			'source_project' => $project->post_name,
		),
		home_url( '/devis/' )
	);
	return $html . '<section class="mba-section mba-home-final-cta" aria-labelledby="mba-project-cta-heading"><h2 id="mba-project-cta-heading">' . esc_html__( 'Un projet similaire en tête ?', 'mba-menuiseries' ) . '</h2><a class="wp-element-button" href="' . esc_url( $quote_url ) . '">' . esc_html__( 'Demander un devis', 'mba-menuiseries' ) . '</a></section><dialog class="mba-lightbox" data-lightbox-dialog aria-labelledby="mba-lightbox-title" aria-describedby="mba-lightbox-caption"><button type="button" class="mba-lightbox__close" data-lightbox-close aria-label="' . esc_attr__( 'Fermer la photo', 'mba-menuiseries' ) . '">×</button><h2 id="mba-lightbox-title" class="screen-reader-text">' . esc_html__( 'Photo du projet', 'mba-menuiseries' ) . '</h2><img data-lightbox-image alt=""><p id="mba-lightbox-caption" data-lightbox-caption></p></dialog></main>';
}
