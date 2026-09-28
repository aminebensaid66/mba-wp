<?php
/** Responsive image sizes, safe upload feedback, and editable focal points. @package MBA_Site_Core */
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/** Register presentation sizes while preserving the uploaded original. */
function mba_core_media_register_sizes(): void {
	add_image_size( 'mba-card', 800, 600, false );
	add_image_size( 'mba-hero', 1920, 1280, false );
	add_image_size( 'mba-gallery', 1600, 1200, false );
	add_image_size( 'mba-logo', 600, 600, false );
}
add_action( 'after_setup_theme', 'mba_core_media_register_sizes' );

/** Request WebP intermediates when the active WordPress editor can create them. */
function mba_core_media_output_formats( array $formats ): array {
	if ( wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
		$formats['image/jpeg'] = 'image/webp';
	}
	return $formats;
}
add_filter( 'image_editor_output_format', 'mba_core_media_output_formats' );

/** Keep editorial metadata while omitting camera and capture details from WordPress metadata. */
function mba_core_media_clean_metadata( array $metadata ): array {
	foreach ( array( 'aperture', 'camera', 'created_timestamp', 'focal_length', 'iso', 'shutter_speed', 'keywords' ) as $key ) {
		unset( $metadata[ $key ] );
	}
	return $metadata;
}
add_filter( 'wp_read_image_metadata', 'mba_core_media_clean_metadata' );

/** Replace PHP's terse image-size failure with a usable owner-facing message. */
function mba_core_media_upload_prefilter( array $file ): array {
	if ( in_array( (int) ( $file['error'] ?? 0 ), array( UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE ), true ) ) {
		$file['error'] = sprintf(
			/* translators: %s is the WordPress server upload limit. */
			__( 'This image exceeds the server upload limit (%s). Choose a smaller image or ask the site host to raise the limit; WordPress will create responsive sizes automatically.', 'mba-site-core' ),
			size_format( wp_max_upload_size() )
		);
		return $file;
	}
	if ( str_starts_with( (string) ( $file['type'] ?? '' ), 'image/' ) && (int) ( $file['size'] ?? 0 ) > 25 * 1024 * 1024 ) {
		$file['error'] = __( 'This image is over 25 MB. Upload the original from your camera; if it is larger than 25 MB, choose a smaller export. WordPress will generate responsive copies.', 'mba-site-core' );
		return $file;
	}
	$mime = (string) ( $file['type'] ?? '' );
	if ( str_starts_with( $mime, 'image/' ) && ! wp_image_editor_supports( array( 'mime_type' => $mime ) ) ) {
		$file['error'] = sprintf( __( 'This server cannot process %s images. Upload a JPEG, PNG, or WebP image instead.', 'mba-site-core' ), strtoupper( substr( $mime, 6 ) ) );
	}
	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'mba_core_media_upload_prefilter' );

/** Allowed object-position presets for reusable Media Library images. @return array<string,string> */
function mba_core_media_focal_positions(): array {
	return array(
		'center' => 'center center',
		'top' => 'center top',
		'bottom' => 'center bottom',
		'left' => 'left center',
		'right' => 'right center',
		'top-left' => 'left top',
		'top-right' => 'right top',
		'bottom-left' => 'left bottom',
		'bottom-right' => 'right bottom',
	);
}

/** Add a crop focus selector to image attachment details. */
function mba_core_media_attachment_fields( array $fields, WP_Post $post ): array {
	if ( ! wp_attachment_is_image( $post->ID ) ) {
		return $fields;
	}
	$current = (string) get_post_meta( $post->ID, '_mba_focal_position', true );
	$current = isset( mba_core_media_focal_positions()[ $current ] ) ? $current : 'center';
	$options = '';
	foreach ( mba_core_media_focal_positions() as $value => $position ) {
		$options .= '<option value="' . esc_attr( $value ) . '"' . selected( $current, $value, false ) . '>' . esc_html( ucwords( str_replace( '-', ' ', $value ) ) ) . '</option>';
	}
	$fields['mba_focal_position'] = array(
		'label' => __( 'Crop focal point', 'mba-site-core' ),
		'input' => 'html',
		'html' => '<select name="attachments[' . esc_attr( (string) $post->ID ) . '][mba_focal_position]">' . $options . '</select>',
		'helps' => __( 'Used for cover/card crops. The original image remains unchanged.', 'mba-site-core' ),
	);
	return $fields;
}
add_filter( 'attachment_fields_to_edit', 'mba_core_media_attachment_fields', 10, 2 );

/** Save only known focal-point presets for images the current user can edit. */
function mba_core_media_save_attachment_fields( array $post, array $attachment ): array {
	if ( ! isset( $attachment['mba_focal_position'] ) || ! current_user_can( 'edit_post', (int) $post['ID'] ) || ! wp_attachment_is_image( (int) $post['ID'] ) ) {
		return $post;
	}
	$value = sanitize_key( (string) $attachment['mba_focal_position'] );
	$value = isset( mba_core_media_focal_positions()[ $value ] ) ? $value : 'center';
	update_post_meta( (int) $post['ID'], '_mba_focal_position', $value );
	return $post;
}
add_filter( 'attachment_fields_to_save', 'mba_core_media_save_attachment_fields', 10, 2 );

/** Apply the chosen focal point to generated attachment images. */
function mba_core_media_image_attributes( array $attributes, WP_Post $attachment ): array {
	$value = (string) get_post_meta( $attachment->ID, '_mba_focal_position', true );
	$positions = mba_core_media_focal_positions();
	if ( isset( $positions[ $value ] ) ) {
		$style = isset( $attributes['style'] ) ? rtrim( (string) $attributes['style'], '; ' ) . '; ' : '';
		$attributes['style'] = $style . 'object-position: ' . $positions[ $value ] . ';';
	}
	return $attributes;
}
add_filter( 'wp_get_attachment_image_attributes', 'mba_core_media_image_attributes', 10, 2 );
