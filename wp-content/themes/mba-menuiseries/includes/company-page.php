<?php
/**
 * Editable company story page.
 *
 * @package MBA_Menuiseries
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mba_theme_register_company_page_block(): void {
	register_block_type( dirname( __DIR__ ) . '/blocks/company-page', array( 'render_callback' => 'mba_theme_render_company_page' ) );
}
add_action( 'init', 'mba_theme_register_company_page_block' );

function mba_company_setting( string $key ) {
	return function_exists( 'mba_core_setting' ) ? mba_core_setting( $key ) : '';
}

function mba_company_lines( string $value ): array {
	$lines = preg_split( '/\r\n|\r|\n/', $value );
	return array_values( array_filter( array_map( 'trim', is_array( $lines ) ? $lines : array() ) ) );
}

function mba_theme_render_company_page(): string {
	$name = (string) mba_company_setting( 'mba_legal_name' );
	$name = $name ? $name : get_bloginfo( 'name' );
	$html = '<main id="main" class="mba-company-page"><section class="mba-section mba-company-intro" aria-labelledby="mba-company-title"><p class="mba-eyebrow">' . esc_html( $name ) . '</p><h1 id="mba-company-title">' . esc_html__( 'Une entreprise au service de vos projets', 'mba-menuiseries' ) . '</h1>';
	$history = (string) mba_company_setting( 'mba_company_history' );
	if ( $history ) {
		$html .= '<p class="mba-lede">' . nl2br( esc_html( $history ) ) . '</p>';
	}
	$html .= '</section>';
	foreach ( array(
		'mba_company_founder_team' => __( 'Fondateur et équipe', 'mba-menuiseries' ),
		'mba_company_values' => __( 'Nos valeurs et qualité', 'mba-menuiseries' ),
	) as $key => $title ) {
		$value = (string) mba_company_setting( $key );
		if ( $value ) {
			$html .= '<section class="mba-section" aria-labelledby="' . esc_attr( $key ) . '"><h2 id="' . esc_attr( $key ) . '">' . esc_html( $title ) . '</h2><p>' . nl2br( esc_html( $value ) ) . '</p></section>';
		}
	}
	$gallery = mba_company_setting( 'mba_company_workshop_gallery' );
	if ( is_array( $gallery ) && $gallery ) {
		$html .= '<section class="mba-section" aria-labelledby="mba-workshop-heading"><h2 id="mba-workshop-heading">' . esc_html__( 'Atelier et équipe', 'mba-menuiseries' ) . '</h2><div class="mba-company-gallery">';
		foreach ( $gallery as $image_id ) {
			$html .= wp_get_attachment_image(
				(int) $image_id,
				'mba-gallery',
				false,
				array(
					'loading' => 'lazy',
					'decoding' => 'async',
					'sizes' => '(max-width: 650px) 100vw, (max-width: 1023px) 50vw, 33vw',
				)
			);
		}
		$html .= '</div></section>';
	}
	$capabilities = mba_company_lines( (string) mba_company_setting( 'mba_company_capabilities' ) );
	if ( $capabilities ) {
		$html .= '<section class="mba-section mba-home-list" aria-labelledby="mba-capabilities-heading"><h2 id="mba-capabilities-heading">' . esc_html__( 'Nos capacités', 'mba-menuiseries' ) . '</h2><ul>';
		foreach ( $capabilities as $item ) {
			$html .= '<li>' . esc_html( $item ) . '</li>';
		} $html .= '</ul></section>';
	}
	$certifications = (string) mba_company_setting( 'mba_company_certifications' );
	if ( $certifications ) {
		$html .= '<section class="mba-section" aria-labelledby="mba-certifications-heading"><h2 id="mba-certifications-heading">' . esc_html__( 'Certifications et partenaires', 'mba-menuiseries' ) . '</h2><p>' . nl2br( esc_html( $certifications ) ) . '</p></section>';
	}
	$areas = (string) mba_company_setting( 'mba_service_areas' );
	if ( $areas ) {
		$html .= '<section class="mba-section" aria-labelledby="mba-zones-heading"><h2 id="mba-zones-heading">' . esc_html__( 'Zones desservies', 'mba-menuiseries' ) . '</h2><p>' . nl2br( esc_html( $areas ) ) . '</p></section>';
	}
	$projects = function_exists( 'mba_homepage_content_query' ) ? mba_homepage_content_query( 'mba_project' ) : new WP_Query(
		array(
			'post_type' => 'mba_project',
			'post_status' => 'publish',
			'posts_per_page' => 3,
		)
	);
	if ( function_exists( 'mba_homepage_cards' ) && $projects->have_posts() ) {
		$html .= '<section class="mba-section" aria-labelledby="mba-company-projects-heading"><h2 id="mba-company-projects-heading">' . esc_html__( 'Réalisations sélectionnées', 'mba-menuiseries' ) . '</h2>' . mba_homepage_cards( $projects, 'mba-grid' ) . '</section>';
	}
	$label = (string) mba_company_setting( 'mba_quote_cta_label' );
	$label = $label ? $label : __( 'Demander un devis', 'mba-menuiseries' );
	return $html . '<section class="mba-section mba-home-final-cta" aria-labelledby="mba-company-cta-heading"><h2 id="mba-company-cta-heading">' . esc_html__( 'Parlons de votre projet', 'mba-menuiseries' ) . '</h2><a class="wp-element-button" href="' . esc_url( home_url( '/devis/' ) ) . '">' . esc_html( $label ) . '</a></section></main>';
}
