<?php
/**
 * Editable homepage composition.
 *
 * @package MBA_Menuiseries
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mba_theme_register_homepage_block(): void {
	register_block_type( dirname( __DIR__ ) . '/blocks/homepage', array( 'render_callback' => 'mba_theme_render_homepage' ) );
}
add_action( 'init', 'mba_theme_register_homepage_block' );

function mba_homepage_setting( string $key ) {
	return function_exists( 'mba_core_setting' ) ? mba_core_setting( $key ) : '';
}

function mba_homepage_lines( string $value ): array {
	$lines = preg_split( '/\r\n|\r|\n/', $value );
	return array_values( array_filter( array_map( 'trim', is_array( $lines ) ? $lines : array() ) ) );
}

function mba_homepage_content_query( string $post_type, int $limit = 3 ): WP_Query {
	$args = array(
		'post_type'      => $post_type,
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'no_found_rows'  => true,
		'orderby'        => array(
			'meta_value_num' => 'ASC',
			'date' => 'DESC',
		),
		'meta_key'       => 'mba_display_order',
	);
	$meta_query = array(
		array(
			'key' => 'mba_featured',
			'value' => '1',
		),
	);
	if ( 'mba_testimonial' === $post_type ) {
		$meta_query[] = array(
			'key' => 'mba_publication_consent_confirmed',
			'value' => '1',
		);
	}
	$featured = new WP_Query(
		$args + array(
			'meta_query' => $meta_query,
		)
	);
	return $featured->have_posts() ? $featured : new WP_Query( $args );
}

function mba_homepage_image( string $key, string $alt, string $class = '' ): string {
	$id = (int) mba_homepage_setting( $key );
	$is_hero = 'mba_homepage_hero_image_id' === $key;
	return $id ? (string) wp_get_attachment_image(
		$id,
		$is_hero ? 'mba-hero' : 'mba-gallery',
		false,
		array(
			'class' => $class,
			'alt' => $alt,
			'loading' => $is_hero ? 'eager' : 'lazy',
			'decoding' => 'async',
			'sizes' => $is_hero ? '100vw' : '(max-width: 767px) 100vw, 50vw',
			'fetchpriority' => $is_hero ? 'high' : 'auto',
		)
	) : '';
}

function mba_homepage_cards( WP_Query $query, string $class = '' ): string {
	if ( ! $query->have_posts() ) {
		return '';
	}
	$html = '<div class="mba-home-cards ' . esc_attr( $class ) . '">';
	while ( $query->have_posts() ) {
		$query->the_post();
		$is_partner = 'mba_partner' === get_post_type();
		$html .= '<article class="mba-card' . ( $is_partner ? ' mba-card--logo' : '' ) . '">';
		$image_id = get_post_thumbnail_id();
		if ( ! $image_id && 'mba_partner' === get_post_type() ) {
			$image_id = (int) get_post_meta( get_the_ID(), 'mba_logo', true );
		}
		if ( ! $image_id && 'mba_testimonial' === get_post_type() ) {
			$image_id = (int) get_post_meta( get_the_ID(), 'mba_customer_photo', true );
		}
		if ( $image_id ) {
			$html .= '<a href="' . esc_url( get_permalink() ) . '">' . wp_get_attachment_image(
				$image_id,
				$is_partner ? 'mba-logo' : 'mba-card',
				false,
				array(
					'loading' => 'lazy',
					'decoding' => 'async',
					'sizes' => '(max-width: 650px) 100vw, (max-width: 1023px) 50vw, 33vw',
				)
			) . '</a>';
		}
		$html .= '<div class="mba-card__body"><h3><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h3>' . ( has_excerpt() ? '<p>' . esc_html( get_the_excerpt() ) . '</p>' : '' ) . '</div></article>';
	}
	wp_reset_postdata();
	return $html . '</div>';
}

function mba_homepage_list_section( string $id, string $title, array $items, bool $ordered = false ): string {
	if ( ! $items ) {
		return '';
	}
	$tag = $ordered ? 'ol' : 'ul';
	$html = '<section class="mba-section mba-home-list" aria-labelledby="' . esc_attr( $id ) . '"><h2 id="' . esc_attr( $id ) . '">' . esc_html( $title ) . '</h2><' . $tag . '>';
	foreach ( $items as $item ) {
		$html .= '<li>' . esc_html( $item ) . '</li>';
	}
	return $html . '</' . $tag . '></section>';
}

function mba_theme_render_homepage(): string {
	$heading = (string) mba_homepage_setting( 'mba_homepage_hero_heading' );
	$heading = $heading ? $heading : __( 'Votre projet aluminium', 'mba-menuiseries' );
	$proposition = (string) mba_homepage_setting( 'mba_homepage_value_proposition' );
	$proposition = $proposition ? $proposition : (string) mba_homepage_setting( 'mba_description' );
	$company = (string) mba_homepage_setting( 'mba_legal_name' );
	$quote_label = (string) mba_homepage_setting( 'mba_quote_cta_label' );
	$quote_label = $quote_label ? $quote_label : __( 'Demander un devis', 'mba-menuiseries' );
	$html = '<main id="main" tabindex="-1" class="mba-homepage">';
	$html .= '<section class="mba-home-hero mba-section" aria-labelledby="mba-home-heading">';
	$hero = mba_homepage_image( 'mba_homepage_hero_image_id', $heading, 'mba-home-hero-image' );
	$html .= $hero ? '<div class="mba-home-hero__media">' . $hero . '</div>' : '';
	$html .= '<div class="mba-home-hero__content"><p class="mba-eyebrow">' . esc_html( $company ) . '</p><h1 id="mba-home-heading">' . esc_html( $heading ) . '</h1>' . ( $proposition ? '<p class="mba-lede">' . nl2br( esc_html( $proposition ) ) . '</p>' : '' ) . '<div class="mba-button-row"><a class="wp-element-button" href="' . esc_url( home_url( '/devis/' ) ) . '">' . esc_html( $quote_label ) . '</a><a class="wp-element-button is-style-outline" href="' . esc_url( home_url( '/realisations/' ) ) . '">' . esc_html__( 'Voir nos réalisations', 'mba-menuiseries' ) . '</a></div></div></section>';
	$html .= mba_homepage_list_section( 'mba-trust-heading', __( 'Repères MBA', 'mba-menuiseries' ), mba_homepage_lines( (string) mba_homepage_setting( 'mba_homepage_trust_highlights' ) ) );
	$products = mba_homepage_content_query( 'mba_product' );
	if ( $products->have_posts() ) {
		$html .= '<section class="mba-section" aria-labelledby="mba-products-heading"><h2 id="mba-products-heading">' . esc_html__( 'Produits sélectionnés', 'mba-menuiseries' ) . '</h2>' . mba_homepage_cards( $products, 'mba-grid' ) . '</section>';
	}
	$description = (string) mba_homepage_setting( 'mba_description' );
	$intro_image = mba_homepage_image( 'mba_homepage_intro_image_id', $company, 'mba-home-section-image' );
	if ( $description || $intro_image ) {
		$html .= '<section class="mba-section mba-home-split" aria-labelledby="mba-company-heading"><div><h2 id="mba-company-heading">' . esc_html__( 'À propos de MBA', 'mba-menuiseries' ) . '</h2>' . ( $description ? '<p>' . nl2br( esc_html( $description ) ) . '</p>' : '' ) . '<a href="' . esc_url( home_url( '/entreprise/' ) ) . '">' . esc_html__( 'Découvrir l’entreprise', 'mba-menuiseries' ) . '</a></div>' . $intro_image . '</section>';
	}
	$html .= mba_homepage_list_section( 'mba-reasons-heading', __( 'Nos engagements', 'mba-menuiseries' ), mba_homepage_lines( (string) mba_homepage_setting( 'mba_homepage_reasons' ) ) );
	$projects = mba_homepage_content_query( 'mba_project' );
	if ( $projects->have_posts() ) {
		$html .= '<section class="mba-section" aria-labelledby="mba-projects-heading"><h2 id="mba-projects-heading">' . esc_html__( 'Réalisations sélectionnées', 'mba-menuiseries' ) . '</h2>' . mba_homepage_cards( $projects, 'mba-grid' ) . '</section>';
	}
	$process = mba_homepage_lines( (string) mba_homepage_setting( 'mba_homepage_process' ) );
	if ( $process ) {
		$html .= '<section class="mba-section mba-home-split" aria-labelledby="mba-process-heading"><div>' . mba_homepage_list_section( 'mba-process-heading', __( 'Votre projet, étape par étape', 'mba-menuiseries' ), $process, true ) . '</div>' . mba_homepage_image( 'mba_homepage_process_image_id', __( 'Processus de projet', 'mba-menuiseries' ), 'mba-home-section-image' ) . '</section>';
	}
	$materials = (string) mba_homepage_setting( 'mba_homepage_materials' );
	if ( $materials ) {
		$html .= '<section class="mba-section mba-home-split" aria-labelledby="mba-materials-heading"><div><h2 id="mba-materials-heading">' . esc_html__( 'Matières et finitions', 'mba-menuiseries' ) . '</h2><p>' . nl2br( esc_html( $materials ) ) . '</p></div>' . mba_homepage_image( 'mba_homepage_materials_image_id', __( 'Matières et finitions', 'mba-menuiseries' ), 'mba-home-section-image' ) . '</section>';
	}
	foreach ( array(
		'mba_testimonial' => __( 'Avis clients', 'mba-menuiseries' ),
		'mba_partner' => __( 'Partenaires', 'mba-menuiseries' ),
	) as $type => $title ) {
		$query = mba_homepage_content_query( $type, 'mba_partner' === $type ? 6 : 3 );
		if ( $query->have_posts() ) {
			$html .= '<section class="mba-section" aria-labelledby="mba-' . esc_attr( $type ) . '-heading"><h2 id="mba-' . esc_attr( $type ) . '-heading">' . esc_html( $title ) . '</h2>' . mba_homepage_cards( $query, 'mba-grid' ) . '</section>';
		}
	}
	$posts = new WP_Query(
		array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'posts_per_page' => 3,
			'no_found_rows' => true,
		)
	);
	if ( $posts->have_posts() ) {
		$html .= '<section class="mba-section" aria-labelledby="mba-advice-heading"><h2 id="mba-advice-heading">' . esc_html__( 'Conseils', 'mba-menuiseries' ) . '</h2>' . mba_homepage_cards( $posts, 'mba-grid' ) . '</section>';
	}
	$final = (string) mba_homepage_setting( 'mba_homepage_final_cta' );
	$contact_label = (string) mba_homepage_setting( 'mba_contact_cta_label' );
	$contact_label = $contact_label ? $contact_label : __( 'Parlons de votre projet', 'mba-menuiseries' );
	$html .= '<section class="mba-section mba-home-final-cta" aria-labelledby="mba-final-cta-heading"><h2 id="mba-final-cta-heading">' . esc_html( $contact_label ) . '</h2>' . ( $final ? '<p>' . nl2br( esc_html( $final ) ) . '</p>' : '' ) . '<div class="mba-button-row"><a class="wp-element-button" href="' . esc_url( home_url( '/devis/' ) ) . '">' . esc_html( $quote_label ) . '</a>' . ( function_exists( 'mba_core_whatsapp_url' ) && mba_core_whatsapp_url() ? '<a class="wp-element-button" href="' . esc_url( mba_core_whatsapp_url() ) . '">' . esc_html__( 'WhatsApp', 'mba-menuiseries' ) . '</a>' : '' ) . ( function_exists( 'mba_core_phone_url' ) && mba_core_phone_url() ? '<a class="wp-element-button" href="' . esc_url( mba_core_phone_url() ) . '">' . esc_html__( 'Téléphoner', 'mba-menuiseries' ) . '</a>' : '' ) . '</div></section></main>';
	return $html;
}
