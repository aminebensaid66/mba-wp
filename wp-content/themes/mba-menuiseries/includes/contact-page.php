<?php
/** Dynamic contact page and consent-gated map. @package MBA_Menuiseries */
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

function mba_theme_register_contact_page_block(): void {
	register_block_type( dirname( __DIR__ ) . '/blocks/contact-page', array( 'render_callback' => 'mba_theme_render_contact_page' ) );
}
add_action( 'init', 'mba_theme_register_contact_page_block' );

function mba_theme_contact_detail( string $key, string $label ): string {
	$html = function_exists( 'mba_core_render_company_block' ) ? mba_core_render_company_block(
		array(
			'key' => $key,
			'label' => $label,
		)
	) : '';
	return $html ? '<li>' . $html . '</li>' : '';
}

function mba_theme_render_contact_page(): string {
	wp_enqueue_script( 'mba-contact-map', get_theme_file_uri( 'assets/js/contact-map.js' ), array(), '0.1.0', true );
	$html = '<main id="main" class="mba-contact"><section class="mba-section" aria-labelledby="mba-contact-title"><h1 id="mba-contact-title">' . esc_html__( 'Contact', 'mba-menuiseries' ) . '</h1>';
	$description = (string) mba_theme_setting( 'mba_description' );
	if ( $description ) {
		$html .= '<p class="mba-lede">' . nl2br( esc_html( $description ) ) . '</p>'; }
	$details = '';
	foreach ( array(
		'mba_phone' => __( 'Téléphone', 'mba-menuiseries' ),
		'mba_secondary_phone' => __( 'Téléphone secondaire', 'mba-menuiseries' ),
		'mba_email' => __( 'E-mail', 'mba-menuiseries' ),
		'mba_whatsapp' => __( 'WhatsApp', 'mba-menuiseries' ),
	) as $key => $label ) {
		$details .= mba_theme_contact_detail( $key, $label ); }
	$address = (string) mba_theme_setting( 'mba_address' );
	if ( $address ) {
		$details .= '<li><strong>' . esc_html__( 'Adresse', 'mba-menuiseries' ) . ':</strong> ' . nl2br( esc_html( $address ) ) . '</li>'; }
	if ( $details ) {
		$html .= '<section aria-labelledby="mba-contact-details-heading"><h2 id="mba-contact-details-heading">' . esc_html__( 'Nos coordonnées', 'mba-menuiseries' ) . '</h2><ul class="mba-contact-details">' . $details . '</ul></section>'; }
	$hours = (string) mba_theme_setting( 'mba_opening_hours' );
	if ( $hours ) {
		$html .= '<section aria-labelledby="mba-contact-hours-heading"><h2 id="mba-contact-hours-heading">' . esc_html__( 'Horaires', 'mba-menuiseries' ) . '</h2><p>' . nl2br( esc_html( $hours ) ) . '</p></section>'; }
	$areas = (string) mba_theme_setting( 'mba_service_areas' );
	if ( $areas ) {
		$html .= '<section aria-labelledby="mba-contact-areas-heading"><h2 id="mba-contact-areas-heading">' . esc_html__( 'Zones desservies', 'mba-menuiseries' ) . '</h2><ul>';
		foreach ( preg_split( '/\r\n|\r|\n/', $areas ) as $area ) {
			if ( trim( $area ) ) {
				$html .= '<li>' . esc_html( trim( $area ) ) . '</li>';
			}
		} $html .= '</ul></section>'; }
	$guidance = (string) mba_theme_setting( 'mba_showroom_guidance' );
	if ( $guidance ) {
		$html .= '<section aria-labelledby="mba-contact-visit-heading"><h2 id="mba-contact-visit-heading">' . esc_html__( 'Visiter notre atelier', 'mba-menuiseries' ) . '</h2><p>' . nl2br( esc_html( $guidance ) ) . '</p></section>'; }
	$directions = (string) mba_theme_setting( 'mba_maps_url' );
	$embed = (string) mba_theme_setting( 'mba_maps_embed_url' );
	if ( $directions || $embed ) {
		$html .= '<section class="mba-contact-map-section" aria-labelledby="mba-contact-map-heading"><h2 id="mba-contact-map-heading">' . esc_html__( 'Nous trouver', 'mba-menuiseries' ) . '</h2>';
		if ( $directions ) {
			$html .= '<p><a class="wp-element-button" href="' . esc_url( $directions ) . '" target="_blank" rel="noopener noreferrer" data-mba-analytics-event="directions_click">' . esc_html__( 'Ouvrir l’itinéraire', 'mba-menuiseries' ) . '</a></p>'; }
		if ( $embed ) {
			$html .= '<div class="mba-consent-map" data-consent-map data-map-src="' . esc_url( $embed ) . '"><p>' . esc_html__( 'La carte Google Maps ne se charge qu’après votre accord.', 'mba-menuiseries' ) . '</p><button type="button" class="wp-element-button" data-map-consent-trigger>' . esc_html__( 'Autoriser et charger la carte', 'mba-menuiseries' ) . '</button></div>'; }
		$html .= '</section>';
	}
	$social = '';
	foreach ( array(
		'facebook' => 'Facebook',
		'instagram' => 'Instagram',
		'linkedin' => 'LinkedIn',
		'tiktok' => 'TikTok',
		'youtube' => 'YouTube',
	) as $network => $label ) {
		$url = (string) mba_theme_setting( 'mba_' . $network . '_url' );
		if ( $url ) {
			$social .= '<li><a href="' . esc_url( $url ) . '" rel="me noopener noreferrer" target="_blank">' . esc_html( $label ) . '</a></li>'; }
	}
	if ( $social ) {
		$html .= '<section aria-labelledby="mba-contact-social-heading"><h2 id="mba-contact-social-heading">' . esc_html__( 'Suivez MBA', 'mba-menuiseries' ) . '</h2><ul class="mba-contact-social">' . $social . '</ul></section>'; }
	$html .= '</section>' . ( function_exists( 'mba_core_render_contact_form' ) ? mba_core_render_contact_form() : '' ) . '</main>';
	return $html;
}
