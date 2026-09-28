<?php
/** Search metadata, safe redirects, and verified structured data. @package MBA_Site_Core */
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Keep only exact, same-site redirect paths from owner-entered mappings.
 *
 * @param string $value Submitted line-based mapping.
 * @return string
 */
function mba_core_seo_sanitize_redirects( string $value ): string {
	$rules = array();
	$home_host = strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) );
	foreach ( preg_split( '/\r\n|\r|\n/', $value ) as $line ) {
		$parts = explode( '=>', $line );
		if ( 2 !== count( $parts ) ) {
			continue;
		}
		$source = trim( $parts[0] );
		$target = trim( $parts[1] );
		$source_parts = wp_parse_url( $source );
		$target_parts = wp_parse_url( $target );
		if ( ! is_array( $source_parts ) || isset( $source_parts['scheme'] ) || isset( $source_parts['host'] ) || isset( $source_parts['query'] ) || isset( $source_parts['fragment'] ) || ! str_starts_with( (string) ( $source_parts['path'] ?? '' ), '/' ) || str_starts_with( (string) ( $source_parts['path'] ?? '' ), '//' ) || ! is_array( $target_parts ) ) {
			continue;
		}
		$target_host = strtolower( (string) ( $target_parts['host'] ?? $home_host ) );
		$target_path = (string) ( $target_parts['path'] ?? '' );
		if ( $target_host !== $home_host || ! str_starts_with( $target_path, '/' ) || str_starts_with( $target_path, '//' ) || untrailingslashit( (string) $source_parts['path'] ) === untrailingslashit( $target_path ) ) {
			continue;
		}
		$source_path = untrailingslashit( (string) $source_parts['path'] );
		$source_path = $source_path ? $source_path : '/';
		$clean_target = $target_path . ( isset( $target_parts['query'] ) ? '?' . $target_parts['query'] : '' );
		$rules[ $source_path ] = $clean_target;
	}
	$cyclic = array();
	foreach ( array_keys( $rules ) as $origin ) {
		$visited = array();
		$current = $origin;
		while ( isset( $rules[ $current ] ) ) {
			if ( isset( $visited[ $current ] ) ) {
				$cyclic[ $origin ] = true;
				break;
			}
			$visited[ $current ] = true;
			$target_path = wp_parse_url( $rules[ $current ], PHP_URL_PATH );
			$current = untrailingslashit( is_string( $target_path ) ? $target_path : '' );
			$current = $current ? $current : '/';
		}
	}
	foreach ( array_keys( $cyclic ) as $origin ) {
		unset( $rules[ $origin ] ); }
	$output = array();
	foreach ( $rules as $source => $target ) {
		$output[] = $source . ' => ' . $target;
	}
	return implode( "\n", $output );
}

/** Resolve normalized redirects into a path lookup. @return array<string,string> */
function mba_core_seo_redirect_map(): array {
	$value = (string) mba_core_setting( 'mba_seo_redirects' );
	$rules = array();
	foreach ( preg_split( '/\r\n|\r|\n/', $value ) as $line ) {
		$parts = explode( ' => ', $line, 2 );
		if ( 2 === count( $parts ) ) {
			$rules[ $parts[0] ] = home_url( $parts[1] ); }
	}
	return $rules;
}

/** Perform only configured same-site permanent redirects. */
function mba_core_seo_redirect(): void {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || empty( $_SERVER['REQUEST_URI'] ) ) {
		return;
	}
	$path = wp_parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH );
	$path = untrailingslashit( is_string( $path ) ? $path : '' );
	$path = $path ? $path : '/';
	$redirects = mba_core_seo_redirect_map();
	if ( isset( $redirects[ $path ] ) && home_url( $path ) !== $redirects[ $path ] ) {
		wp_safe_redirect( $redirects[ $path ], 301 );
		exit;
	}
}
add_action( 'template_redirect', 'mba_core_seo_redirect', 1 );

/** Per-content SEO overrides for editor-managed titles and descriptions. */
function mba_core_seo_register_meta(): void {
	foreach ( array( 'page', 'post', 'mba_product', 'mba_project' ) as $post_type ) {
		register_post_meta(
			$post_type,
			'mba_seo_title',
			array(
				'type' => 'string',
				'single' => true,
				'show_in_rest' => false,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback' => static function ( bool $allowed, string $key, int $post_id ): bool {
					return current_user_can( 'edit_post', $post_id );
				},
			)
		);
		register_post_meta(
			$post_type,
			'mba_seo_description',
			array(
				'type' => 'string',
				'single' => true,
				'show_in_rest' => false,
				'sanitize_callback' => 'sanitize_textarea_field',
				'auth_callback' => static function ( bool $allowed, string $key, int $post_id ): bool {
					return current_user_can( 'edit_post', $post_id );
				},
			)
		);
	}
}
add_action( 'init', 'mba_core_seo_register_meta' );

/** Register editor controls only during the admin meta-box lifecycle. */
function mba_core_seo_add_meta_boxes(): void {
	foreach ( array( 'page', 'post', 'mba_product', 'mba_project' ) as $post_type ) {
		add_meta_box( 'mba-seo', __( 'Search and social preview', 'mba-site-core' ), 'mba_core_seo_meta_box', $post_type, 'normal', 'default' );
	}
}
add_action( 'add_meta_boxes', 'mba_core_seo_add_meta_boxes' );

/** Render editor SEO overrides; the featured image remains the per-page share-image control. */
function mba_core_seo_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'mba_save_seo', 'mba_seo_nonce' );
	$title = (string) get_post_meta( $post->ID, 'mba_seo_title', true );
	$description = (string) get_post_meta( $post->ID, 'mba_seo_description', true );
	echo '<p><label for="mba-seo-title">' . esc_html__( 'SEO title', 'mba-site-core' ) . '</label><br><input class="widefat" id="mba-seo-title" name="mba_seo_title" maxlength="180" value="' . esc_attr( $title ) . '"></p>';
	echo '<p><label for="mba-seo-description">' . esc_html__( 'Search/social description', 'mba-site-core' ) . '</label><br><textarea class="widefat" id="mba-seo-description" name="mba_seo_description" rows="3" maxlength="320">' . esc_textarea( $description ) . '</textarea></p>';
	echo '<p class="description">' . esc_html__( 'The featured image is used for social previews; the site-wide social image is the fallback.', 'mba-site-core' ) . '</p>';
}

/** Save editor SEO overrides after capability and nonce checks. */
function mba_core_seo_save_meta( int $post_id ): void {
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) || ! isset( $_POST['mba_seo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mba_seo_nonce'] ) ), 'mba_save_seo' ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$title_raw = wp_unslash( $_POST['mba_seo_title'] ?? '' );
	$description_raw = wp_unslash( $_POST['mba_seo_description'] ?? '' );
	$title = is_string( $title_raw ) ? sanitize_text_field( $title_raw ) : '';
	$description = is_string( $description_raw ) ? sanitize_textarea_field( $description_raw ) : '';
	$title ? update_post_meta( $post_id, 'mba_seo_title', mb_substr( $title, 0, 180 ) ) : delete_post_meta( $post_id, 'mba_seo_title' );
	$description ? update_post_meta( $post_id, 'mba_seo_description', mb_substr( $description, 0, 320 ) ) : delete_post_meta( $post_id, 'mba_seo_description' );
}
add_action( 'save_post', 'mba_core_seo_save_meta' );

/** Determine a useful description without inventing business claims. */
function mba_core_seo_description(): string {
	$post = is_singular() ? get_queried_object() : null;
	if ( $post instanceof WP_Post ) {
		$override = (string) get_post_meta( $post->ID, 'mba_seo_description', true );
		if ( $override ) {
			return $override;
		}
		if ( has_excerpt( $post ) ) {
			return wp_trim_words( wp_strip_all_tags( $post->post_excerpt ), 35, '' ); }
		$content = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
		if ( trim( $content ) ) {
			return wp_trim_words( $content, 35, '' ); }
	}
	$description = (string) mba_core_setting( 'mba_seo_description' );
	return $description ? $description : (string) mba_core_setting( 'mba_description' );
}

/** Resolve editor overrides and WordPress content into safe page metadata. */
function mba_core_seo_title(): string {
	$post = is_singular() ? get_queried_object() : null;
	if ( $post instanceof WP_Post ) {
		$override = (string) get_post_meta( $post->ID, 'mba_seo_title', true );
		if ( $override ) {
			return $override; }
	}
	if ( is_front_page() && mba_core_setting( 'mba_seo_title' ) ) {
		return (string) mba_core_setting( 'mba_seo_title' ); }
	return function_exists( 'wp_get_document_title' ) ? wp_get_document_title() : get_bloginfo( 'name' );
}

/** Apply the editor title override to the document title as well as previews. */
function mba_core_seo_document_title( string $title ): string {
	$post = is_singular() ? get_queried_object() : null;
	if ( $post instanceof WP_Post ) {
		$override = (string) get_post_meta( $post->ID, 'mba_seo_title', true );
		if ( $override ) {
			return $override; }
	}
	return is_front_page() && mba_core_setting( 'mba_seo_title' ) ? (string) mba_core_setting( 'mba_seo_title' ) : $title;
}
add_filter( 'pre_get_document_title', 'mba_core_seo_document_title' );

/** Emit title overrides, descriptions, canonical URLs, social cards, and JSON-LD. */
function mba_core_seo_head(): void {
	$title = mba_core_seo_title();
	$description = mba_core_seo_description();
	if ( $description ) {
		echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
	}
	if ( $title ) {
		echo '<meta property="og:title" content="' . esc_attr( $title ) . '"><meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
	}
	$og_type = is_singular( 'post' ) ? 'article' : ( is_singular( 'mba_product' ) ? 'product' : 'website' );
	echo '<meta property="og:type" content="' . esc_attr( $og_type ) . '"><meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
	if ( $description ) {
		echo '<meta property="og:description" content="' . esc_attr( $description ) . '"><meta name="twitter:description" content="' . esc_attr( $description ) . '">' . "\n";
	}
	$image_id = 0;
	$post = is_singular() ? get_queried_object() : null;
	if ( $post instanceof WP_Post && has_post_thumbnail( $post ) ) {
		$image_id = (int) get_post_thumbnail_id( $post );
	}
	if ( ! $image_id ) {
		$image_id = (int) mba_core_setting( 'mba_seo_image_id' );
	}
	$image = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';
	if ( $image ) {
		echo '<meta property="og:image" content="' . esc_url( $image ) . '"><meta name="twitter:card" content="summary_large_image"><meta name="twitter:image" content="' . esc_url( $image ) . '">' . "\n";
	} else {
		echo '<meta name="twitter:card" content="summary">' . "\n";
	}
	$canonical = mba_core_seo_canonical();
	if ( $canonical ) {
		echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";
		echo '<meta property="og:url" content="' . esc_url( $canonical ) . '">' . "\n";
	}
	foreach ( mba_core_seo_schema() as $schema ) {
		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . '</script>' . "\n";
	}
}
add_action( 'wp_head', 'mba_core_seo_head', 1 );

/** Use stable page URLs rather than tracking/query-string variants. */
function mba_core_seo_canonical(): string {
	if ( is_search() || is_404() || is_attachment() ) {
		return ''; }
	if ( is_singular() ) {
		$url = get_permalink();
		return $url ? (string) $url : ''; }
	if ( is_front_page() ) {
		return home_url( '/' ); }
	if ( is_post_type_archive() ) {
		return (string) get_pagenum_link( max( 1, (int) get_query_var( 'paged' ) ) ); }
	if ( is_category() || is_tag() || is_tax() ) {
		return (string) get_pagenum_link( max( 1, (int) get_query_var( 'paged' ) ) ); }
	return (string) get_pagenum_link( max( 1, (int) get_query_var( 'paged' ) ) );
}

/** Build only supported schema from verified settings and visible page content. @return array<int,array<string,mixed>> */
function mba_core_seo_schema(): array {
	$schemas = array();
	$business_name = (string) mba_core_setting( 'mba_legal_name' );
	if ( $business_name ) {
		$organization = array(
			'@context' => 'https://schema.org',
			'@type' => mba_core_setting( 'mba_address' ) ? 'LocalBusiness' : 'Organization',
			'name' => $business_name,
			'url' => home_url( '/' ),
		);
		if ( mba_core_setting( 'mba_address' ) ) {
			$organization['address'] = array(
				'@type' => 'PostalAddress',
				'streetAddress' => (string) mba_core_setting( 'mba_address' ),
			); }
		if ( mba_core_setting( 'mba_phone' ) ) {
			$organization['telephone'] = (string) mba_core_setting( 'mba_phone' ); }
		if ( mba_core_setting( 'mba_email' ) ) {
			$organization['email'] = (string) mba_core_setting( 'mba_email' ); }
		$logo_id = (int) mba_core_setting( 'mba_logo_id' );
		if ( $logo_id ) {
			$logo = wp_get_attachment_image_url( $logo_id, 'full' );
			if ( $logo ) {
				$organization['logo'] = $logo; }
		}
		$social = array();
		foreach ( array( 'facebook', 'instagram', 'linkedin', 'tiktok', 'youtube' ) as $network ) {
			$url = (string) mba_core_setting( 'mba_' . $network . '_url' );
			if ( $url ) {
				$social[] = $url; }
		}
		if ( $social ) {
			$organization['sameAs'] = $social; }
		$schemas[] = $organization;
	}
	$post = is_singular() ? get_queried_object() : null;
	if ( $post instanceof WP_Post && 'post' === $post->post_type && 'publish' === $post->post_status ) {
		$article = array(
			'@context' => 'https://schema.org',
			'@type' => 'Article',
			'headline' => get_the_title( $post ),
			'mainEntityOfPage' => get_permalink( $post ),
			'datePublished' => get_the_date( DATE_W3C, $post ),
			'dateModified' => get_the_modified_date( DATE_W3C, $post ),
		);
		$description = mba_core_seo_description();
		if ( $description ) {
			$article['description'] = $description; }
		if ( has_post_thumbnail( $post ) ) {
			$article['image'] = wp_get_attachment_image_url( (int) get_post_thumbnail_id( $post ), 'full' ); }
		$schemas[] = $article;
	}
	if ( $post instanceof WP_Post && 'mba_product' === $post->post_type && 'publish' === $post->post_status ) {
		$product = array(
			'@context' => 'https://schema.org',
			'@type' => 'Product',
			'name' => get_the_title( $post ),
			'url' => get_permalink( $post ),
		);
		$description = mba_core_seo_description();
		if ( $description ) {
			$product['description'] = $description; }
		if ( has_post_thumbnail( $post ) ) {
			$product['image'] = wp_get_attachment_image_url( (int) get_post_thumbnail_id( $post ), 'full' ); }
		$schemas[] = $product;
	}
	if ( is_page( 'entreprise' ) ) {
		$capabilities = preg_split( '/\r\n|\r|\n/', (string) mba_core_setting( 'mba_company_capabilities' ) );
		foreach ( $capabilities as $capability ) {
			$capability = trim( $capability );
			if ( $capability ) {
				$service = array(
					'@context' => 'https://schema.org',
					'@type' => 'Service',
					'name' => $capability,
				);
				if ( $business_name ) {
					$service['provider'] = array(
						'@type' => 'Organization',
						'name' => $business_name,
					);
				}
				$schemas[] = $service;
			}
		}
	}
	if ( $post instanceof WP_Post && 'publish' === $post->post_status ) {
		$crumbs = array( array( __( 'Accueil', 'mba-site-core' ), home_url( '/' ) ) );
		if ( in_array( $post->post_type, array( 'mba_product', 'mba_project' ), true ) ) {
			$type_label = 'mba_product' === $post->post_type ? __( 'Produits', 'mba-site-core' ) : __( 'Réalisations', 'mba-site-core' );
			$archive = get_post_type_archive_link( $post->post_type );
			if ( $archive ) {
				$crumbs[] = array( $type_label, $archive ); }
		} elseif ( 'post' === $post->post_type ) {
			$blog_page = (int) get_option( 'page_for_posts' );
			$crumbs[] = array( __( 'Conseils', 'mba-site-core' ), $blog_page ? get_permalink( $blog_page ) : home_url( '/conseils/' ) );
		}
		foreach ( array_reverse( get_post_ancestors( $post ) ) as $ancestor_id ) {
			$crumbs[] = array( get_the_title( $ancestor_id ), get_permalink( $ancestor_id ) );
		}
		$crumbs[] = array( get_the_title( $post ), get_permalink( $post ) );
		$items = array();
		foreach ( $crumbs as $index => $crumb ) {
			$items[] = array(
				'@type' => 'ListItem',
				'position' => $index + 1,
				'name' => $crumb[0],
				'item' => $crumb[1],
			);
		}
		$schemas[] = array(
			'@context' => 'https://schema.org',
			'@type' => 'BreadcrumbList',
			'itemListElement' => $items,
		);
	}
	return $schemas;
}

/** Exclude searches, confirmations, attachments and staging sites from indexing. */
function mba_core_seo_robots( array $robots ): array {
	$filtered_archive = false;
	foreach ( array( 'categorie', 'application', 'type_projet', 'lieu', 'produit' ) as $filter_key ) {
		if ( isset( $_GET[ $filter_key ] ) ) {
			$filtered_archive = true;
			break;
		}
	}
	if ( is_search() || is_404() || is_attachment() || is_singular( array( 'mba_quote_lead', 'mba_contact_lead' ) ) || is_preview() || $filtered_archive || isset( $_GET['quote_status'] ) || isset( $_GET['contact_status'] ) || ! get_option( 'blog_public', 1 ) || 'production' !== wp_get_environment_type() ) {
		$robots['noindex'] = true;
		$robots['follow'] = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'mba_core_seo_robots' );

/** Keep non-public lead types and attachment pages out of the sitemap. */
function mba_core_seo_sitemap_post_types( array $post_types ): array {
	unset( $post_types['attachment'], $post_types['mba_quote_lead'], $post_types['mba_contact_lead'] );
	return $post_types;
}
add_filter( 'wp_sitemaps_post_types', 'mba_core_seo_sitemap_post_types' );

/** Do not publish a sitemap from private or staging environments. */
function mba_core_seo_sitemaps_enabled( bool $enabled ): bool {
	return $enabled && (bool) get_option( 'blog_public', 1 ) && 'production' === wp_get_environment_type();
}
add_filter( 'wp_sitemaps_enabled', 'mba_core_seo_sitemaps_enabled' );

/** Let this implementation own canonical markup without duplicating core output. */
function mba_core_seo_remove_core_canonical(): void {
	remove_action( 'wp_head', 'rel_canonical' );
}
add_action( 'wp', 'mba_core_seo_remove_core_canonical' );
