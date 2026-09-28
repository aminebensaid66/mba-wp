<?php
/**
 * Dynamic navigation and company details for block templates.
 *
 * @package MBA_Menuiseries
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Add a keyboard-first bypass link before the shared site header. */
function mba_theme_render_skip_link(): void {
	echo '<a class="mba-skip-link" href="#main">' . esc_html__( 'Passer au contenu principal', 'mba-menuiseries' ) . '</a>';
}
add_action( 'wp_body_open', 'mba_theme_render_skip_link' );

/**
 * Register server-rendered theme blocks.
 */
function mba_theme_register_navigation_blocks(): void {
	foreach ( array( 'site-header', 'site-footer', 'breadcrumbs' ) as $name ) {
		register_block_type( dirname( __DIR__ ) . '/blocks/' . $name, array( 'render_callback' => 'mba_theme_render_' . str_replace( '-', '_', $name ) ) );
	}
}
add_action( 'init', 'mba_theme_register_navigation_blocks' );

/**
 * Read company values while keeping the theme safe without the site plugin.
 *
 * @param string $key Setting key.
 * @return string|int
 */
function mba_theme_setting( string $key ) {
	return function_exists( 'mba_core_setting' ) ? mba_core_setting( $key ) : '';
}

/**
 * Build a core navigation link or submenu.
 *
 * @param string                  $label Visible label.
 * @param string                  $url   Destination.
 * @param array<array<string,mixed>> $children Child navigation blocks.
 * @return array<string,mixed>
 */
function mba_theme_navigation_item( string $label, string $url, array $children = array() ): array {
	return array(
		'blockName' => $children ? 'core/navigation-submenu' : 'core/navigation-link',
		'attrs' => array(
			'label' => $label,
			'url' => $url,
			'kind' => 'custom',
			'isTopLevelLink' => true,
		),
		'innerBlocks' => $children,
		'innerHTML' => '',
		'innerContent' => array_fill( 0, count( $children ), null ),
	);
}

/**
 * Core navigation owns submenu and modal focus/keyboard behavior.
 *
 * @return string
 */
function mba_theme_navigation(): string {
	$products_url = get_post_type_archive_link( 'mba_product' );
	$products_url = $products_url ? $products_url : home_url( '/produits/' );
	$products = array( mba_theme_navigation_item( __( 'Tous les produits', 'mba-menuiseries' ), $products_url ) );
	$terms = get_terms(
		array(
			'taxonomy' => 'mba_product_category',
			'hide_empty' => true,
			'orderby' => 'name',
		)
	);
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$products[] = mba_theme_navigation_item( $term->name, add_query_arg( 'categorie', $term->slug, $products_url ) );
		}
	}
	$items = array(
		mba_theme_navigation_item( __( 'Accueil', 'mba-menuiseries' ), home_url( '/' ) ),
		mba_theme_navigation_item( __( 'Entreprise', 'mba-menuiseries' ), home_url( '/entreprise/' ) ),
		mba_theme_navigation_item( __( 'Produits', 'mba-menuiseries' ), $products_url, $products ),
		mba_theme_navigation_item( __( 'Réalisations', 'mba-menuiseries' ), home_url( '/realisations/' ) ),
		mba_theme_navigation_item( __( 'Conseils', 'mba-menuiseries' ), home_url( '/conseils/' ) ),
		mba_theme_navigation_item( __( 'FAQ', 'mba-menuiseries' ), home_url( '/faq/' ) ),
		mba_theme_navigation_item( __( 'Contact', 'mba-menuiseries' ), home_url( '/contact/' ) ),
	);
	return render_block(
		array(
			'blockName' => 'core/navigation',
			'attrs' => array(
				'overlayMenu' => 'mobile',
				'openSubmenusOnClick' => true,
				'showSubmenuIcon' => true,
				'ariaLabel' => __( 'Navigation principale', 'mba-menuiseries' ),
				'className' => 'mba-primary-navigation',
				'layout' => array(
					'type' => 'flex',
					'justifyContent' => 'right',
				),
			),
			'innerBlocks' => $items,
			'innerHTML' => '',
			'innerContent' => array_fill( 0, count( $items ), null ),
		)
	);
}

/**
 * Render the shared header.
 *
 * @return string
 */
function mba_theme_render_site_header(): string {
	$name = (string) mba_theme_setting( 'mba_legal_name' );
	$name = $name ? $name : get_bloginfo( 'name' );
	$logo = (int) mba_theme_setting( 'mba_logo_id' );
	$label = (string) mba_theme_setting( 'mba_quote_cta_label' );
	$label = $label ? $label : __( 'Demander un devis', 'mba-menuiseries' );
	return '<div class="mba-site-header"><div class="mba-header-inner"><a class="mba-brand" href="' . esc_url( home_url( '/' ) ) . '" aria-label="' . esc_attr( $name ) . '">' . ( $logo ? wp_get_attachment_image(
		$logo,
		'mba-logo',
		false,
		array(
			'class' => 'mba-company-logo',
			'alt' => $name,
		)
	) : esc_html( $name ) ) . '</a>' . mba_theme_navigation() . '<a class="wp-element-button mba-header-quote" href="' . esc_url( home_url( '/devis/' ) ) . '">' . esc_html( $label ) . '</a></div></div>';
}

/**
 * Render confirmed contact/social details without empty placeholder sections.
 *
 * @return string
 */
function mba_theme_render_site_footer(): string {
	$name = (string) mba_theme_setting( 'mba_legal_name' );
	$name = $name ? $name : get_bloginfo( 'name' );
	$html = '<div class="mba-site-footer"><div class="mba-footer-grid"><div><p class="mba-footer-brand">' . esc_html( $name ) . '</p>';
	foreach ( array( 'mba_description', 'mba_footer_content', 'mba_partner_disclaimer' ) as $key ) {
		$value = (string) mba_theme_setting( $key );
		if ( $value ) {
			$html .= '<p>' . nl2br( esc_html( $value ) ) . '</p>';
		}
	}
	$html .= '</div><nav aria-label="' . esc_attr__( 'Navigation de pied de page', 'mba-menuiseries' ) . '"><ul class="mba-footer-links">';
	foreach ( array(
		'/produits/' => __( 'Produits', 'mba-menuiseries' ),
		'/realisations/' => __( 'Réalisations', 'mba-menuiseries' ),
		'/faq/' => __( 'FAQ', 'mba-menuiseries' ),
		'/contact/' => __( 'Contact', 'mba-menuiseries' ),
		'/devis/' => __( 'Demander un devis', 'mba-menuiseries' ),
	) as $path => $label ) {
		$html .= '<li><a href="' . esc_url( home_url( $path ) ) . '">' . esc_html( $label ) . '</a></li>';
	}
	$html .= '</ul></nav><div class="mba-footer-contact">';
	if ( function_exists( 'mba_core_render_company_block' ) ) {
		foreach ( array( 'mba_phone', 'mba_secondary_phone', 'mba_whatsapp', 'mba_email', 'mba_address', 'mba_opening_hours', 'mba_service_areas' ) as $key ) {
			$detail = mba_core_render_company_block(
				array(
					'key' => $key,
					'label' => 'mba_whatsapp' === $key ? 'WhatsApp' : '',
				)
			);
			if ( $detail ) {
				$html .= '<p>' . $detail . '</p>';
			}
		}
	}
	$html .= '</div></div><div class="mba-footer-bottom"><p>© ' . esc_html( wp_date( 'Y' ) ) . ' ' . esc_html( $name ) . '</p><ul class="mba-footer-links mba-footer-inline">';
	$privacy = get_privacy_policy_url();
	if ( $privacy ) {
		$html .= '<li><a href="' . esc_url( $privacy ) . '">' . esc_html__( 'Confidentialité', 'mba-menuiseries' ) . '</a></li>';
	}
	$legal = get_page_by_path( 'mentions-legales' );
	if ( $legal && 'publish' === $legal->post_status ) {
		$html .= '<li><a href="' . esc_url( get_permalink( $legal ) ) . '">' . esc_html__( 'Mentions légales', 'mba-menuiseries' ) . '</a></li>';
	}
	foreach ( array(
		'facebook' => 'Facebook',
		'instagram' => 'Instagram',
		'linkedin' => 'LinkedIn',
		'tiktok' => 'TikTok',
		'youtube' => 'YouTube',
	) as $network => $label ) {
		$url = (string) mba_theme_setting( 'mba_' . $network . '_url' );
		if ( $url ) {
			$html .= '<li><a href="' . esc_url( $url ) . '" rel="me">' . esc_html( $label ) . '</a></li>';
		}
	}
	if ( function_exists( 'mba_core_analytics_measurement_id' ) && mba_core_analytics_measurement_id() ) {
		$html .= '<li><button class="mba-consent-open" type="button" data-mba-consent-open>' . esc_html__( 'Paramètres de confidentialité', 'mba-menuiseries' ) . '</button></li>';
	}
	$html .= '</ul></div></div>';
	return $html . mba_theme_render_mobile_conversion_actions();
}

/**
 * Render compact mobile call and WhatsApp actions from validated settings.
 *
 * @return string
 */
function mba_theme_render_mobile_conversion_actions(): string {
	$phone = function_exists( 'mba_core_phone_url' ) ? mba_core_phone_url() : '';
	$whatsapp = function_exists( 'mba_core_whatsapp_url' ) ? mba_core_whatsapp_url( mba_theme_conversion_context() ) : '';
	if ( ! $phone && ! $whatsapp ) {
		return '';
	}
	$name = (string) mba_theme_setting( 'mba_legal_name' );
	$name = $name ? $name : get_bloginfo( 'name' );
	$type = is_singular( 'mba_product' ) ? 'product' : ( is_singular( 'mba_project' ) ? 'project' : 'general' );
	$count = ( $phone ? 1 : 0 ) + ( $whatsapp ? 1 : 0 );
	$html = '<nav class="mba-mobile-conversion' . ( 1 === $count ? ' mba-mobile-conversion--single' : '' ) . '" aria-label="' . esc_attr__( 'Actions de contact rapides', 'mba-menuiseries' ) . '">';
	if ( $phone ) {
		$html .= '<a class="mba-mobile-conversion__action" href="' . esc_url( $phone ) . '" data-mba-conversion="call" data-mba-content-type="' . esc_attr( $type ) . '">' . esc_html( sprintf( __( 'Appeler %s', 'mba-menuiseries' ), $name ) ) . '</a>';
	}
	if ( $whatsapp ) {
		$html .= '<a class="mba-mobile-conversion__action mba-mobile-conversion__action--whatsapp" href="' . esc_url( $whatsapp ) . '" target="_blank" rel="noopener noreferrer" data-mba-conversion="whatsapp" data-mba-content-type="' . esc_attr( $type ) . '">' . esc_html( sprintf( __( 'Écrire à %s sur WhatsApp', 'mba-menuiseries' ), $name ) ) . '</a>';
	}
	return $html . '</nav>';
}

/**
 * Add public product/project context to the editable WhatsApp draft.
 *
 * @return string
 */
function mba_theme_conversion_context(): string {
	if ( ! is_singular( array( 'mba_product', 'mba_project' ) ) ) {
		return '';
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return '';
	}
	$prefix = 'mba_product' === $post->post_type ? __( 'Bonjour, je vous contacte au sujet du produit :', 'mba-menuiseries' ) : __( 'Bonjour, je vous contacte au sujet de cette réalisation :', 'mba-menuiseries' );
	return $prefix . ' ' . get_the_title( $post );
}

/** Add bottom room only when a mobile action bar is configured. */
function mba_theme_mobile_conversion_body_class( array $classes ): array {
	if ( ( function_exists( 'mba_core_phone_url' ) && mba_core_phone_url() ) || ( function_exists( 'mba_core_whatsapp_url' ) && mba_core_whatsapp_url() ) ) {
		$classes[] = 'has-mba-mobile-conversion';
	}
	return $classes;
}
add_filter( 'body_class', 'mba_theme_mobile_conversion_body_class' );

/**
 * Render hierarchical context with the current page announced accessibly.
 *
 * @return string
 */
function mba_theme_render_breadcrumbs(): string {
	if ( is_front_page() ) {
		return '';
	}
	$items = array( array( __( 'Accueil', 'mba-menuiseries' ), home_url( '/' ) ) );
	if ( is_singular() ) {
		$post = get_post();
		if ( ! $post ) {
			return '';
		}
		if ( in_array( $post->post_type, array( 'mba_product', 'mba_project' ), true ) ) {
			$items[] = array( 'mba_product' === $post->post_type ? __( 'Produits', 'mba-menuiseries' ) : __( 'Réalisations', 'mba-menuiseries' ), get_post_type_archive_link( $post->post_type ) );
		} elseif ( 'post' === $post->post_type ) {
			$blog = (int) get_option( 'page_for_posts' );
			$items[] = array( __( 'Conseils', 'mba-menuiseries' ), $blog ? get_permalink( $blog ) : home_url( '/conseils/' ) );
		}
		foreach ( array_reverse( get_post_ancestors( $post ) ) as $parent ) {
			$items[] = array( get_the_title( $parent ), get_permalink( $parent ) );
		}
		$current = get_the_title( $post );
	} elseif ( is_search() ) {
		$current = __( 'Recherche', 'mba-menuiseries' );
	} elseif ( is_404() ) {
		$current = __( 'Page introuvable', 'mba-menuiseries' );
	} elseif ( is_home() ) {
		$current = __( 'Conseils', 'mba-menuiseries' );
	} else {
		$current = wp_strip_all_tags( get_the_archive_title() );
	}
	$html = '<nav class="mba-breadcrumbs" aria-label="' . esc_attr__( 'Fil d’Ariane', 'mba-menuiseries' ) . '"><ol>';
	foreach ( $items as $item ) {
		$html .= '<li><a href="' . esc_url( $item[1] ? $item[1] : home_url( '/' ) ) . '">' . esc_html( $item[0] ) . '</a><span aria-hidden="true">›</span></li>';
	}
	return $html . '<li><span aria-current="page">' . esc_html( $current ) . '</span></li></ol></nav>';
}

/**
 * Measure the sticky header to keep hash targets clear at every text size.
 */
function mba_theme_navigation_assets(): void {
	wp_enqueue_script( 'mba-navigation', get_theme_file_uri( 'assets/js/navigation.js' ), array(), (string) filemtime( get_theme_file_path( 'assets/js/navigation.js' ) ), true );
}
add_action( 'wp_enqueue_scripts', 'mba_theme_navigation_assets' );
