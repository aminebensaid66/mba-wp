<?php
/** Blog archive and article rendering. @package MBA_Menuiseries */
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

function mba_theme_blog_card( WP_Post $post ): string {
	$url = get_permalink( $post );
	return '<article class="mba-card mba-blog-card">' . ( has_post_thumbnail( $post ) ? '<a href="' . esc_url( $url ) . '">' . get_the_post_thumbnail(
		$post,
		'mba-card',
		array(
			'loading' => 'lazy',
			'decoding' => 'async',
			'sizes' => '(max-width: 650px) 100vw, (max-width: 1023px) 50vw, 33vw',
		)
	) . '</a>' : '' ) . '<div class="mba-card__body"><p class="mba-card__meta">' . esc_html( get_the_date( '', $post ) ) . '</p><h2><a href="' . esc_url( $url ) . '">' . esc_html( get_the_title( $post ) ) . '</a></h2><p>' . esc_html( get_the_excerpt( $post ) ) . '</p></div></article>';
}

function mba_theme_render_blog_archive(): string {
	$query = new WP_Query(
		array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'posts_per_page' => 9,
			'paged' => max( 1, (int) get_query_var( 'paged' ) ),
		)
	);
	$html = '<main id="main" tabindex="-1" class="mba-blog-archive"><section class="mba-section" aria-labelledby="mba-blog-title"><h1 id="mba-blog-title">' . esc_html__( 'Conseils', 'mba-menuiseries' ) . '</h1><nav class="mba-blog-categories" aria-label="' . esc_attr__( 'Catégories des conseils', 'mba-menuiseries' ) . '"><a href="' . esc_url( home_url( '/conseils/' ) ) . '">' . esc_html__( 'Toutes', 'mba-menuiseries' ) . '</a>';
	foreach ( get_categories( array( 'hide_empty' => true ) ) as $category ) {
		$html .= '<a href="' . esc_url( get_category_link( $category ) ) . '">' . esc_html( $category->name ) . '</a>'; }
	$html .= '</nav><div class="mba-grid mba-blog-grid">';
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$html .= mba_theme_blog_card( get_post() );
		}
	} else {
		$html .= '<p role="status">' . esc_html__( 'Aucun article disponible.', 'mba-menuiseries' ) . '</p>'; }
	$html .= '</div>';
	$links = paginate_links(
		array(
			'total' => $query->max_num_pages,
			'current' => max( 1, (int) get_query_var( 'paged' ) ),
			'type' => 'list',
		)
	);
	if ( $links ) {
		$html .= '<nav class="mba-pagination" aria-label="' . esc_attr__( 'Pagination des conseils', 'mba-menuiseries' ) . '">' . wp_kses_post( $links ) . '</nav>'; }
	wp_reset_postdata();
	return $html . '</section></main>';
}

function mba_theme_render_blog_article(): string {
	$post = get_post();
	if ( ! $post || 'post' !== $post->post_type ) {
		return ''; }
	$updated = get_the_modified_date( '', $post );
	$html = '<main id="main" tabindex="-1" class="mba-blog-article"><article class="mba-section"><header><p class="mba-card__meta">' . esc_html( sprintf( __( 'Publié le %1$s · Mis à jour le %2$s · Par %3$s', 'mba-menuiseries' ), get_the_date( '', $post ), $updated, get_the_author_meta( 'display_name', (int) $post->post_author ) ) ) . '</p><h1>' . esc_html( get_the_title( $post ) ) . '</h1>' . ( has_post_thumbnail( $post ) ? get_the_post_thumbnail(
		$post,
		'mba-hero',
		array(
			'loading' => 'eager',
			'fetchpriority' => 'high',
			'decoding' => 'async',
			'sizes' => '(max-width: 767px) 100vw, 66vw',
		)
	) : '' ) . '</header><div class="mba-article-content">' . apply_filters( 'the_content', $post->post_content ) . '</div></article>';
	$related = get_posts(
		array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'post__not_in' => array( $post->ID ),
			'posts_per_page' => 3,
			'category__in' => wp_get_post_categories( $post->ID ),
		)
	);
	if ( $related ) {
		$html .= '<section class="mba-section" aria-labelledby="mba-related-articles"><h2 id="mba-related-articles">' . esc_html__( 'À lire aussi', 'mba-menuiseries' ) . '</h2><div class="mba-grid">';
		foreach ( $related as $item ) {
			$html .= mba_theme_blog_card( $item );
		} $html .= '</div></section>'; }
	return $html . '<section class="mba-section mba-home-final-cta" aria-labelledby="mba-blog-cta"><h2 id="mba-blog-cta">' . esc_html__( 'Besoin d’un conseil pour votre projet ?', 'mba-menuiseries' ) . '</h2><a class="wp-element-button" href="' . esc_url( home_url( '/devis/' ) ) . '">' . esc_html__( 'Demander un devis', 'mba-menuiseries' ) . '</a></section></main>';
}

function mba_theme_register_blog_blocks(): void {
	register_block_type( dirname( __DIR__ ) . '/blocks/blog-archive', array( 'render_callback' => 'mba_theme_render_blog_archive' ) );
	register_block_type( dirname( __DIR__ ) . '/blocks/blog-article', array( 'render_callback' => 'mba_theme_render_blog_article' ) ); }
add_action( 'init', 'mba_theme_register_blog_blocks' );
