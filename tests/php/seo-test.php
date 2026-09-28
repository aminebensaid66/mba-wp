<?php
/** Dependency-free SEO redirect and indexing contracts for issue #21. */

define( 'ABSPATH', __DIR__ );
$GLOBALS['mba_seo_test_options'] = array();
$GLOBALS['mba_seo_test_post'] = null;
class_alias(
	get_class(
		new class() {
			public int $ID = 1;
			public string $post_type = 'post';
			public string $post_status = 'publish';
			public string $post_content = 'Visible post content';
			public string $post_excerpt = '';
		}
	),
	'WP_Post'
);
function add_action( $hook, $callback ) {}
function add_filter( $hook, $callback ) {}
function __( $text, $domain = null ) {
	return $text; }
function register_post_meta( $type, $key, $args ) {}
function add_meta_box( $id, $title, $callback, $type, $context, $priority ) {}
function home_url( $path = '/' ) {
	return 'https://mba.example' . $path;
}
function wp_parse_url( $url, $component = -1 ) {
	return parse_url( $url, $component );
}
function untrailingslashit( $value ) {
	return rtrim( $value, '/' );
}
function mba_core_setting( $key ) {
	return $GLOBALS['mba_seo_test_options'][ $key ] ?? '';
}
function is_admin() {
	return false; }
function wp_doing_ajax() {
	return false; }
function wp_doing_cron() {
	return false; }
function is_search() {
	return false; }
function is_404() {
	return false; }
function is_attachment() {
	return false; }
function is_preview() {
	return $GLOBALS['mba_seo_test_preview'] ?? false; }
function get_option( $key, $default = false ) {
	return $GLOBALS['mba_seo_test_options'][ $key ] ?? $default;
}
function wp_get_environment_type() {
	return $GLOBALS['mba_seo_test_env'] ?? 'production'; }
function is_singular( $post_types = '' ) {
	$post = $GLOBALS['mba_seo_test_post'];
	return $post && ( ! $post_types || in_array( $post->post_type, (array) $post_types, true ) );
}
function get_queried_object() {
	return $GLOBALS['mba_seo_test_post']; }
function is_page( $slug = '' ) {
	return 'entreprise' === $slug && 'entreprise' === ( $GLOBALS['mba_seo_test_page'] ?? '' ); }
function get_post_meta( $post_id, $key, $single = false ) {
	return ''; }
function has_excerpt( $post ) {
	return '' !== $post->post_excerpt; }
function wp_strip_all_tags( $content ) {
	return strip_tags( $content ); }
function strip_shortcodes( $content ) {
	return $content; }
function wp_trim_words( $text, $num_words = 55, $more = null ) {
	return $text; }
function get_the_title( $post ) {
	return 'Visible title'; }
function get_permalink( $post = null ) {
	return 'https://mba.example/visible/'; }
function get_post_type_archive_link( $post_type ) {
	return 'https://mba.example/' . $post_type . '/'; }
function get_post_ancestors( $post ) {
	return array(); }
function has_post_thumbnail( $post = null ) {
	return false; }
function get_the_date( $format, $post = null ) {
	return '2026-09-28T00:00:00+00:00'; }
function get_the_modified_date( $format, $post = null ) {
	return '2026-09-28T00:00:00+00:00'; }

require dirname( __DIR__, 2 ) . '/wp-content/plugins/mba-site-core/includes/seo.php';

$fail = static function ( string $message ): void {
	fwrite( STDERR, $message . PHP_EOL );
	exit( 1 );
};
$redirects = mba_core_seo_sanitize_redirects(
	"/old-page/ => /new-page/\n/legacy/ => /new-page/\n/away/ => https://evil.example/\n/loop/ => /loop/\n/cycle-a/ => /cycle-b/\n/cycle-b/ => /cycle-a/\n//evil.example/path => /safe/\n/bad/?query=1 => /safe/"
);
"/old-page => /new-page/\n/legacy => /new-page/" === $redirects || $fail( 'Only normalized internal redirects should be retained: ' . var_export( $redirects, true ) );
$GLOBALS['mba_seo_test_options']['mba_seo_redirects'] = $redirects;
array(
	'/old-page' => 'https://mba.example/new-page/',
	'/legacy' => 'https://mba.example/new-page/',
) === mba_core_seo_redirect_map() || $fail( 'Redirect lookup must preserve local paths and destinations.' );
$GLOBALS['mba_seo_test_options']['blog_public'] = 1;
array() === mba_core_seo_robots( array() ) || $fail( 'Production public site must keep its robots policy unchanged.' );
$GLOBALS['mba_seo_test_preview'] = true;
array(
	'noindex' => true,
	'follow' => true,
) === mba_core_seo_robots( array() ) || $fail( 'Preview pages must be noindex.' );
$GLOBALS['mba_seo_test_preview'] = false;
$_GET['contact_status'] = 'success';
array(
	'noindex' => true,
	'follow' => true,
) === mba_core_seo_robots( array() ) || $fail( 'Contact confirmation pages must be noindex.' );
unset( $_GET['contact_status'] );
$_GET['categorie'] = 'fenetres';
array(
	'noindex' => true,
	'follow' => true,
) === mba_core_seo_robots( array() ) || $fail( 'Filtered internal archives must be noindex.' );
unset( $_GET['categorie'] );
$GLOBALS['mba_seo_test_options']['blog_public'] = 0;
array(
	'noindex' => true,
	'follow' => true,
) === mba_core_seo_robots( array() ) || $fail( 'Staging/private site must be noindex.' );
$GLOBALS['mba_seo_test_options']['blog_public'] = 1;
$GLOBALS['mba_seo_test_env'] = 'staging';
array(
	'noindex' => true,
	'follow' => true,
) === mba_core_seo_robots( array() ) || $fail( 'Staging environment must be noindex.' );
$types = mba_core_seo_sitemap_post_types(
	array(
		'page' => array(),
		'attachment' => array(),
		'mba_quote_lead' => array(),
		'mba_contact_lead' => array(),
		'mba_product' => array(),
	)
);
array(
	'page' => array(),
	'mba_product' => array(),
) === $types || $fail( 'Private leads and attachment pages must not enter the sitemap.' );
$GLOBALS['mba_seo_test_env'] = 'production';
true === mba_core_seo_sitemaps_enabled( true ) || $fail( 'Production public site should expose its sitemap.' );
false === mba_core_seo_sitemaps_enabled( false ) || $fail( 'Disabled WordPress sitemaps must remain disabled.' );
$GLOBALS['mba_seo_test_env'] = 'staging';
false === mba_core_seo_sitemaps_enabled( true ) || $fail( 'Staging sitemaps must remain disabled.' );
$GLOBALS['mba_seo_test_env'] = 'production';
$GLOBALS['mba_seo_test_options']['blog_public'] = 0;
false === mba_core_seo_sitemaps_enabled( true ) || $fail( 'Private sites must not expose their sitemap.' );

$GLOBALS['mba_seo_test_options'] = array(
	'blog_public' => 1,
	'mba_legal_name' => 'MBA Menuiseries',
	'mba_address' => 'Confirmed public address',
	'mba_phone' => '+21612345678',
	'mba_company_capabilities' => "Fenêtres\nPortes",
);
$schemas = mba_core_seo_schema();
'LocalBusiness' === $schemas[0]['@type'] && 'Confirmed public address' === $schemas[0]['address']['streetAddress'] || $fail( 'LocalBusiness schema must use the verified public address.' );
$GLOBALS['mba_seo_test_post'] = new WP_Post();
$GLOBALS['mba_seo_test_post']->post_type = 'mba_product';
$schemas = mba_core_seo_schema();
$product_schemas = array_values(
	array_filter(
		$schemas,
		static function ( array $schema ): bool {
			return 'Product' === ( $schema['@type'] ?? '' );
		}
	)
);
$product = $product_schemas[0] ?? array();
'Product' === $product['@type'] && ! isset( $product['offers'] ) && ! isset( $product['aggregateRating'] ) && ! isset( $product['review'] ) || $fail( 'Product schema must avoid invented prices and reviews.' );
$breadcrumb_schemas = array_values(
	array_filter(
		$schemas,
		static function ( array $schema ): bool {
			return 'BreadcrumbList' === ( $schema['@type'] ?? '' );
		}
	)
);
isset( $breadcrumb_schemas[0]['itemListElement'][0]['position'] ) || $fail( 'Published content must expose breadcrumb schema.' );
$GLOBALS['mba_seo_test_post']->post_type = 'post';
$schemas = mba_core_seo_schema();
$article_schemas = array_values(
	array_filter(
		$schemas,
		static function ( array $schema ): bool {
			return 'Article' === ( $schema['@type'] ?? '' );
		}
	)
);
isset( $article_schemas[0] ) || $fail( 'Published posts must receive Article schema.' );
$GLOBALS['mba_seo_test_post'] = null;
$GLOBALS['mba_seo_test_page'] = 'entreprise';
$schemas = mba_core_seo_schema();
array( 'Fenêtres', 'Portes' ) === array_column( array_slice( $schemas, 1 ), 'name' ) || $fail( 'Service schema must derive from configured visible capabilities.' );

echo "SEO redirects and indexing assertions passed.\n";
