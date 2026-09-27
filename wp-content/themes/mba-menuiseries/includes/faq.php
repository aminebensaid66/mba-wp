<?php
/** FAQ archive and progressive accordion. @package MBA_Menuiseries */
if ( ! defined( 'ABSPATH' ) ) {
	exit; }
function mba_theme_render_faq(): string {
	$terms = get_terms(
		array(
			'taxonomy' => 'mba_faq_category',
			'hide_empty' => true,
			'orderby' => 'name',
		)
	);
	$active = isset( $_GET['categorie'] ) ? sanitize_title( wp_unslash( $_GET['categorie'] ) ) : '';
	$args = array(
		'post_type' => 'mba_faq',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'orderby' => 'menu_order title',
		'order' => 'ASC',
	);
	if ( $active ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'mba_faq_category',
				'field' => 'slug',
				'terms' => $active,
			),
		); }
	$faqs = get_posts( $args );
	$html = '<main id="main" class="mba-faq"><section class="mba-section" aria-labelledby="mba-faq-title"><h1 id="mba-faq-title">' . esc_html__( 'Questions fréquentes', 'mba-menuiseries' ) . '</h1><nav class="mba-faq-categories" aria-label="' . esc_attr__( 'Catégories FAQ', 'mba-menuiseries' ) . '"><a href="' . esc_url( home_url( '/faq/' ) ) . '">' . esc_html__( 'Toutes', 'mba-menuiseries' ) . '</a>';
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$html .= '<a href="' . esc_url( add_query_arg( 'categorie', $term->slug, home_url( '/faq/' ) ) ) . '">' . esc_html( $term->name ) . '</a>'; }
	}
	$html .= '</nav><div class="mba-faq-list">';
	foreach ( $faqs as $faq ) {
		$button_id = 'mba-faq-button-' . $faq->ID;
		$panel_id = 'mba-faq-panel-' . $faq->ID;
		$html .= '<article class="mba-faq-item"><h2><button type="button" id="' . esc_attr( $button_id ) . '" aria-expanded="false" aria-controls="' . esc_attr( $panel_id ) . '">' . esc_html( get_the_title( $faq ) ) . '</button></h2><div id="' . esc_attr( $panel_id ) . '" role="region" aria-labelledby="' . esc_attr( $button_id ) . '"><div>' . wp_kses_post( apply_filters( 'the_content', $faq->post_content ) ) . '</div></div></article>'; }
	if ( ! $faqs ) {
		$html .= '<p role="status">' . esc_html__( 'Aucune question disponible.', 'mba-menuiseries' ) . '</p>'; }
	return $html . '</div></section></main>';
}
function mba_theme_register_faq_block(): void {
	register_block_type( dirname( __DIR__ ) . '/blocks/faq', array( 'render_callback' => 'mba_theme_render_faq' ) ); }
add_action( 'init', 'mba_theme_register_faq_block' );
