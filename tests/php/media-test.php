<?php
/** Dependency-free media pipeline contracts for issue #23. */

define( 'ABSPATH', __DIR__ );
$GLOBALS['mba_media_test_sizes'] = array();
$GLOBALS['mba_media_test_meta'] = array();
$GLOBALS['mba_media_test_webp'] = false;
class_alias(
	get_class(
		new class() {
			public int $ID = 1;
		}
	),
	'WP_Post'
);
function add_action( $hook, $callback ) {}
function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {}
function add_image_size( $name, $width, $height, $crop = false ) {
	$GLOBALS['mba_media_test_sizes'][ $name ] = array( $width, $height, $crop );
}
function wp_image_editor_supports( $args = array() ) {
	return $GLOBALS['mba_media_test_webp']; }
function wp_attachment_is_image( $id ) {
	return 1 === $id; }
function __( $text, $domain = null ) {
	return $text; }
function size_format( $size ) {
	return '2 MB'; }
function wp_max_upload_size() {
	return 2 * 1024 * 1024; }
function get_post_meta( $id, $key, $single = false ) {
	return $GLOBALS['mba_media_test_meta'][ $id ][ $key ] ?? ''; }
function esc_attr( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES ); }
function esc_html( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES ); }
function selected( $selected, $current, $echo = true ) {
	return (string) $selected === (string) $current ? ' selected="selected"' : ''; }
function current_user_can( $capability, $id ) {
	return 'edit_post' === $capability && 1 === $id; }
function sanitize_key( $value ) {
	return preg_replace( '/[^a-z0-9_-]/', '', strtolower( $value ) ); }
function update_post_meta( $id, $key, $value ) {
	$GLOBALS['mba_media_test_meta'][ $id ][ $key ] = $value; }

require dirname( __DIR__, 2 ) . '/wp-content/plugins/mba-site-core/includes/media.php';

$fail = static function ( string $message ): void {
	fwrite( STDERR, $message . PHP_EOL );
	exit( 1 );
};
mba_core_media_register_sizes();
array( 800, 600, false ) === $GLOBALS['mba_media_test_sizes']['mba-card'] || $fail( 'Card derivatives must preserve originals for CSS focal-point crops.' );
array( 1920, 1280, false ) === $GLOBALS['mba_media_test_sizes']['mba-hero'] || $fail( 'Hero derivatives must preserve the source aspect ratio.' );
array( 1600, 1200, false ) === $GLOBALS['mba_media_test_sizes']['mba-gallery'] || $fail( 'Gallery derivatives must preserve the source aspect ratio.' );
array( 600, 600, false ) === $GLOBALS['mba_media_test_sizes']['mba-logo'] || $fail( 'Logo derivatives must preserve their aspect ratio.' );
$formats = mba_core_media_output_formats( array( 'image/gif' => 'image/gif' ) );
! isset( $formats['image/jpeg'] ) || $fail( 'Unsupported servers must retain original image formats.' );
$GLOBALS['mba_media_test_webp'] = true;
$formats = mba_core_media_output_formats( array() );
'image/webp' === $formats['image/jpeg'] && ! isset( $formats['image/png'] ) || $fail( 'WebP photo derivatives should be enabled without altering transparent PNG originals.' );
$metadata = mba_core_media_clean_metadata(
	array(
		'aperture' => '2.8',
		'camera' => 'Camera model',
		'created_timestamp' => 1234567890,
		'focal_length' => '50mm',
		'iso' => '100',
		'shutter_speed' => '1/100',
		'keywords' => array( 'private-keyword' ),
		'caption' => 'Editorial caption',
		'credit' => 'Photographer',
		'copyright' => 'MBA',
		'orientation' => 1,
	)
);
array( 'caption' => 'Editorial caption', 'credit' => 'Photographer', 'copyright' => 'MBA', 'orientation' => 1 ) === $metadata || $fail( 'Clean camera and capture metadata while retaining editorial image metadata.' );
$oversize = mba_core_media_upload_prefilter(
	array(
		'name' => 'photo.jpg',
		'type' => 'image/jpeg',
		'size' => 26 * 1024 * 1024,
		'error' => UPLOAD_ERR_OK,
	)
);
false !== strpos( $oversize['error'], '25 MB' ) || $fail( 'Very large image uploads need an actionable size message.' );
$server_limit = mba_core_media_upload_prefilter(
	array(
		'name' => 'photo.jpg',
		'type' => 'image/jpeg',
		'size' => 3 * 1024 * 1024,
		'error' => UPLOAD_ERR_INI_SIZE,
	)
);
false !== strpos( $server_limit['error'], '2 MB' ) || $fail( 'Server upload-limit failures need an actionable message.' );
$GLOBALS['mba_media_test_webp'] = false;
$unsupported = mba_core_media_upload_prefilter(
	array(
		'name' => 'photo.heic',
		'type' => 'image/heic',
		'size' => 1 * 1024 * 1024,
		'error' => UPLOAD_ERR_OK,
	)
);
false !== strpos( $unsupported['error'], 'JPEG, PNG, or WebP' ) || $fail( 'Unsupported image formats need a usable replacement suggestion.' );
$fields = mba_core_media_attachment_fields( array(), new WP_Post() );
isset( $fields['mba_focal_position'] ) && false !== strpos( $fields['mba_focal_position']['html'], 'bottom-right' ) || $fail( 'Image attachment details must expose editable crop focal points.' );
$post = mba_core_media_save_attachment_fields( array( 'ID' => 1 ), array( 'mba_focal_position' => 'bottom-right' ) );
1 === $post['ID'] && 'bottom-right' === $GLOBALS['mba_media_test_meta'][1]['_mba_focal_position'] || $fail( 'Focal point edits must be stored only as an allowed preset.' );
$attributes = mba_core_media_image_attributes( array( 'style' => 'display:block;' ), new WP_Post() );
false !== strpos( $attributes['style'], 'object-position: right bottom' ) || $fail( 'Saved focal points must control front-end object positioning.' );

echo "Media pipeline assertions passed.\n";
