<?php
/** Privacy export, erasure, and private-attachment cleanup assertions. */
define( 'ABSPATH', __DIR__ );
$hooks = array();
$filters = array();
$leads = array(
	1 => array( 'type' => 'mba_quote_lead', 'meta' => array( '_mba_quote_email' => 'client@example.com', '_mba_quote_full_name' => 'Client', '_mba_quote_message' => 'Please call', '_mba_quote_uploads' => array( array( 'path' => '/private/path/plan.pdf', 'name' => 'plan.pdf' ) ) ) ),
	2 => array( 'type' => 'mba_contact_lead', 'meta' => array( '_mba_contact_email' => 'client@example.com', '_mba_contact_name' => 'Client', '_mba_contact_message' => 'Contact me' ) ),
	3 => array( 'type' => 'mba_quote_lead', 'meta' => array( '_mba_quote_email' => 'other@example.com', '_mba_quote_full_name' => 'Other' ) ),
);
function add_action( string $hook, $callback ): void { global $hooks; $hooks[ $hook ] = $callback; }
function add_filter( string $hook, $callback ): void { global $filters; $filters[ $hook ] = $callback; }
function apply_filters( string $hook, $value ) { global $test_private_dir; return 'mba_quote_private_upload_directory' === $hook ? $test_private_dir : $value; }
function __( string $text ): string { return $text; }
function sanitize_email( string $email ): string { return filter_var( $email, FILTER_SANITIZE_EMAIL ); }
function sanitize_text_field( string $text ): string { return trim( strip_tags( $text ) ); }
function wp_unslash( string $text ): string { return $text; }
function untrailingslashit( string $path ): string { return rtrim( $path, '/\\' ); }
function is_email( string $email ): bool { return (bool) filter_var( $email, FILTER_VALIDATE_EMAIL ); }
function get_posts( array $args ): array {
	global $leads;
	$found = array();
	foreach ( $leads as $id => $lead ) {
		if ( ! in_array( $lead['type'], (array) $args['post_type'], true ) ) { continue; }
		$meta = $args['meta_query'];
		$matches = isset( $meta['relation'] ) && 'OR' === $meta['relation'] ? false : true;
		foreach ( $meta as $clause ) {
			if ( ! is_array( $clause ) || ! isset( $clause['key'] ) ) { continue; }
			$match = ( $lead['meta'][ $clause['key'] ] ?? null ) === $clause['value'];
			$matches = isset( $meta['relation'] ) && 'OR' === $meta['relation'] ? ( $matches || $match ) : ( $matches && $match );
		}
		if ( $matches ) { $found[] = (object) array( 'ID' => $id, 'post_type' => $lead['type'], 'post_date_gmt' => '2026-09-01 10:00:00' ); }
	}
	return array_slice( $found, 0, (int) ( $args['posts_per_page'] ?? 100 ) );
}
function get_post_meta( int $id, string $key, bool $single = false ) { global $leads; return $leads[ $id ]['meta'][ $key ] ?? ''; }
function get_post_type( int $id ): string { global $leads; return $leads[ $id ]['type'] ?? ''; }
function get_date_from_gmt( string $date, string $format ): string { return $date; }
function sanitize_file_name( string $name ): string { return basename( $name ); }
function wp_delete_file( string $path ): bool { return unlink( $path ); }
function wp_delete_post( int $id, bool $force = false ) { global $leads, $hooks; if ( isset( $leads[ $id ] ) && isset( $hooks['before_delete_post'] ) ) { ( $hooks['before_delete_post'] )( $id ); } unset( $leads[ $id ] ); return true; }
require dirname( __DIR__, 2 ) . '/wp-content/plugins/mba-site-core/includes/quote-leads.php';
require dirname( __DIR__, 2 ) . '/wp-content/plugins/mba-site-core/includes/privacy.php';
$fail = static function ( string $message ): void { fwrite( STDERR, $message . PHP_EOL ); exit( 1 ); };
$export = mba_core_export_lead_data( 'client@example.com' );
2 === count( $export['data'] ) || $fail( 'Personal-data export must return only quote/contact records for the requested email.' );
str_contains( (string) json_encode( $export['data'] ), 'Please call' ) || $fail( 'Export must include stored enquiry content.' );
false !== strpos( (string) json_encode( $export['data'] ), 'Private attachment filename' ) || $fail( 'Quote attachment names must be included in the export.' );
false !== strpos( (string) json_encode( $export['data'] ), 'private-leads' ) && $fail( 'Export must not expose private filesystem paths.' );

$test_private_dir = sys_get_temp_dir() . '/mba-private-leads-' . bin2hex( random_bytes( 4 ) );
mkdir( $test_private_dir, 0700 );
$attachment = $test_private_dir . '/lead.pdf';
file_put_contents( $attachment, 'private' );
$leads[1]['meta']['_mba_quote_uploads'] = array( array( 'path' => $attachment, 'name' => 'plan.pdf' ) );
$erasure = mba_core_erase_lead_data( 'client@example.com' );
$erasure['items_removed'] && ! $erasure['items_retained'] || $fail( 'Privacy erasure must permanently remove matching leads.' );
! isset( $leads[1] ) && ! isset( $leads[2] ) || $fail( 'Erasure must remove both quote and contact leads for the requested email.' );
! file_exists( $attachment ) || $fail( 'Deleting a quote lead must remove its private attachment.' );
isset( $leads[3] ) || $fail( 'Erasure must not affect unrelated leads.' );

for ( $id = 100; $id < 205; $id++ ) {
	$leads[ $id ] = array( 'type' => 'mba_contact_lead', 'meta' => array( '_mba_contact_email' => 'bulk@example.com' ) );
}
$first_page = mba_core_erase_lead_data( 'bulk@example.com' );
false === $first_page['done'] || $fail( 'Erasure must request another privacy-tools page when a full batch was deleted.' );
$second_page = mba_core_erase_lead_data( 'bulk@example.com', 2 );
true === $second_page['done'] || $fail( 'Erasure must finish after deleting the final partial batch.' );
foreach ( $leads as $lead ) {
	'bulk@example.com' !== ( $lead['meta']['_mba_contact_email'] ?? '' ) || $fail( 'Paged erasure must remove every matching lead without skipping records.' );
}
rmdir( $test_private_dir );
echo "Privacy export, erasure, and private attachment cleanup assertions passed.\n";
