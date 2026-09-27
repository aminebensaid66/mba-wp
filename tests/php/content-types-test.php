<?php
/**
 * Dependency-free registration contract tests for issue #4.
 */

define( 'ABSPATH', __DIR__ );

$GLOBALS['mba_test_post_types'] = array();
$GLOBALS['mba_test_taxonomies'] = array();

function __( $text, $domain = null ) { // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain
	return $text;
}
function add_action( $hook, $callback ) {
	$GLOBALS['mba_test_actions'][ $hook ][] = $callback;
}
function register_post_type( $name, $args ) {
	$GLOBALS['mba_test_post_types'][ $name ] = $args;
}
function register_taxonomy( $name, $object_type, $args ) {
	$GLOBALS['mba_test_taxonomies'][ $name ] = array( 'object_type' => $object_type, 'args' => $args );
}

require dirname( __DIR__, 2 ) . '/wp-content/plugins/mba-site-core/includes/content-types.php';
mba_core_register_content_types();

$fail = static function ( string $message ): void {
	fwrite( STDERR, $message . PHP_EOL );
	exit( 1 );
};

$expected = array( 'mba_product', 'mba_project', 'mba_faq', 'mba_testimonial', 'mba_partner' );
foreach ( $expected as $type ) {
	isset( $GLOBALS['mba_test_post_types'][ $type ] ) || $fail( "Missing post type {$type}" );
	$args = $GLOBALS['mba_test_post_types'][ $type ];
	true === $args['show_ui'] || $fail( "{$type} must be owner-editable" );
	true === $args['show_in_rest'] || $fail( "{$type} must support REST/Gutenberg" );
	true === $args['map_meta_cap'] || $fail( "{$type} must map normal editor post capabilities" );
	in_array( 'revisions', $args['supports'], true ) || $fail( "{$type} must preserve revisions" );
	isset( $args['labels']['add_new_item'], $args['labels']['edit_item'], $args['labels']['all_items'] ) || $fail( "{$type} labels incomplete" );
}

$product = $GLOBALS['mba_test_post_types']['mba_product'];
$project = $GLOBALS['mba_test_post_types']['mba_project'];
true === $product['public'] && 'produits' === $product['has_archive'] || $fail( 'Product public archive mismatch.' );
true === $project['public'] && 'realisations' === $project['has_archive'] || $fail( 'Project public archive mismatch.' );

foreach ( array( 'mba_faq', 'mba_testimonial', 'mba_partner' ) as $private_type ) {
	$args = $GLOBALS['mba_test_post_types'][ $private_type ];
	false === $args['public'] || $fail( "{$private_type} must not expose public singles" );
	false === $args['publicly_queryable'] || $fail( "{$private_type} must not be publicly queryable" );
	false === $args['has_archive'] || $fail( "{$private_type} must not expose an archive" );
}

foreach ( array( 'mba_product_category', 'mba_application_type', 'mba_project_type', 'mba_project_location', 'mba_faq_category' ) as $taxonomy ) {
	isset( $GLOBALS['mba_test_taxonomies'][ $taxonomy ] ) || $fail( "Missing taxonomy {$taxonomy}" );
	true === $GLOBALS['mba_test_taxonomies'][ $taxonomy ]['args']['show_in_rest'] || $fail( "{$taxonomy} must support REST" );
}

$source = file_get_contents( dirname( __DIR__, 2 ) . '/wp-content/plugins/mba-site-core/includes/content-types.php' );
false === strpos( $source, 'flush_rewrite_rules' ) || $fail( 'Rewrite rules must not flush during normal registration.' );

echo "Content type registration assertions passed.\n";
