<?php
/**
 * Product metadata schemas and validation.
 *
 * @package MBA_Site_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Authorize metadata against the product being edited.
 *
 * @param bool   $allowed Existing decision.
 * @param string $meta_key Metadata key.
 * @param int    $post_id  Product ID.
 * @return bool
 */
function mba_core_product_meta_auth( bool $allowed, string $meta_key, int $post_id ): bool {
	unset( $allowed, $meta_key );
	return current_user_can( 'edit_post', $post_id );
}

/**
 * Parse a positive ID without converting malformed values into another ID.
 *
 * @param mixed $value Raw value.
 * @return int
 */
function mba_core_valid_positive_id( $value ): int {
	if ( ! is_int( $value ) && ! is_string( $value ) ) {
		return 0;
	}
	$id = filter_var( $value, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );
	return false === $id ? 0 : $id;
}

/**
 * Normalize positive, unique IDs without changing editor order.
 *
 * @param mixed $value Raw values.
 * @return array<int>
 */
function mba_core_sanitize_id_list( $value ): array {
	$ids = array();
	foreach ( (array) $value as $item ) {
		$id = mba_core_valid_positive_id( $item );
		if ( $id && ! in_array( $id, $ids, true ) ) {
			$ids[] = $id;
		}
	}
	return $ids;
}

/**
 * Sanitize a text repeater.
 *
 * @param mixed $value Raw values.
 * @return array<string>
 */
function mba_core_sanitize_string_list( $value ): array {
	$clean = array();
	foreach ( (array) $value as $item ) {
		if ( ! is_scalar( $item ) ) {
			continue;
		}
		$item = sanitize_text_field( (string) $item );
		if ( '' !== $item ) {
			$clean[] = $item;
		}
	}
	return $clean;
}

/**
 * Accept only IDs of image attachments.
 *
 * @param mixed $value Raw values.
 * @return array<int>
 */
function mba_core_sanitize_image_id_list( $value ): array {
	return array_values( array_filter( mba_core_sanitize_id_list( $value ), 'wp_attachment_is_image' ) );
}

/**
 * Accept only an existing PDF attachment.
 *
 * @param mixed $value Raw value.
 * @return int
 */
function mba_core_sanitize_pdf_attachment_id( $value ): int {
	$id = mba_core_valid_positive_id( $value );
	return $id && 'attachment' === get_post_type( $id ) && 'application/pdf' === get_post_mime_type( $id ) ? $id : 0;
}

/**
 * Limit display order to the range shown in the editor.
 *
 * @param mixed $value Raw value.
 * @return int
 */
function mba_core_sanitize_display_order( $value ): int {
	return min( 9999, max( 0, (int) $value ) );
}

/**
 * Keep related IDs for a single content type.
 *
 * @param mixed  $value     Raw values.
 * @param string $post_type Expected type.
 * @return array<int>
 */
function mba_core_sanitize_relationship_ids( $value, string $post_type ): array {
	$ids = array();
	foreach ( mba_core_sanitize_id_list( $value ) as $id ) {
		if ( get_post_type( $id ) === $post_type ) {
			$ids[] = $id;
		}
	}
	return $ids;
}

/**
 * Sanitize related FAQ IDs.
 *
 * @param mixed $value Raw values.
 * @return array<int>
 */
function mba_core_sanitize_faq_ids( $value ): array {
	return mba_core_sanitize_relationship_ids( $value, 'mba_faq' );
}

/**
 * Sanitize related project IDs.
 *
 * @param mixed $value Raw values.
 * @return array<int>
 */
function mba_core_sanitize_project_ids( $value ): array {
	return mba_core_sanitize_relationship_ids( $value, 'mba_project' );
}

/**
 * Sanitize related product IDs on FAQ and project backlinks.
 *
 * @param mixed $value Raw values.
 * @return array<int>
 */
function mba_core_sanitize_product_ids( $value ): array {
	return mba_core_sanitize_relationship_ids( $value, 'mba_product' );
}

/**
 * Keep complete verified performance rows.
 *
 * @param mixed $value Raw rows.
 * @return array<array{label:string,value:string}>
 */
function mba_core_sanitize_performance_details( $value ): array {
	$rows = array();
	foreach ( (array) $value as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$label = sanitize_text_field( (string) ( $row['label'] ?? '' ) );
		$detail = sanitize_text_field( (string) ( $row['value'] ?? '' ) );
		if ( '' !== $label && '' !== $detail ) {
			$rows[] = array(
				'label' => $label,
				'value' => $detail,
			);
		}
	}
	return $rows;
}

/**
 * Keep finish labels and optional valid swatch images.
 *
 * @param mixed $value Raw rows.
 * @return array<array{label:string,image_id:int}>
 */
function mba_core_sanitize_colors_finishes( $value ): array {
	$rows = array();
	foreach ( (array) $value as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$label = sanitize_text_field( (string) ( $row['label'] ?? '' ) );
		$image_id = mba_core_valid_positive_id( $row['image_id'] ?? 0 );
		$image_id = $image_id && wp_attachment_is_image( $image_id ) ? $image_id : 0;
		if ( '' !== $label ) {
			$rows[] = array(
				'label'    => $label,
				'image_id' => $image_id,
			);
		}
	}
	return $rows;
}

/**
 * Register scalar metadata for one content type.
 *
 * @param string   $post_type Content type.
 * @param string   $key       Meta key.
 * @param string   $type      REST type.
 * @param callable $sanitize  Sanitizer.
 */
function mba_core_register_product_scalar( string $post_type, string $key, string $type, callable $sanitize ): void {
	register_post_meta(
		$post_type,
		$key,
		array(
			'type'              => $type,
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => $sanitize,
			'auth_callback'     => 'mba_core_product_meta_auth',
		)
	);
}

/**
 * Register an array with a REST item schema.
 *
 * @param string              $post_type Content type.
 * @param string              $key       Meta key.
 * @param array<string,mixed> $items     REST item schema.
 * @param callable            $sanitize  Sanitizer.
 */
function mba_core_register_product_array( string $post_type, string $key, array $items, callable $sanitize ): void {
	register_post_meta(
		$post_type,
		$key,
		array(
			'type'              => 'array',
			'single'            => true,
			'default'           => array(),
			'show_in_rest'      => array(
				'schema' => array(
					'type'    => 'array',
					'items'   => $items,
					'default' => array(),
				),
			),
			'sanitize_callback' => $sanitize,
			'auth_callback'     => 'mba_core_product_meta_auth',
		)
	);
}

/**
 * Register structured product data and reverse relationship fields.
 */
function mba_core_register_product_meta(): void {
	$scalars = array(
		'mba_short_description'  => array( 'string', 'sanitize_textarea_field' ),
		'mba_materials_profiles' => array( 'string', 'wp_kses_post' ),
		'mba_maintenance'        => array( 'string', 'wp_kses_post' ),
		'mba_technical_document' => array( 'integer', 'mba_core_sanitize_pdf_attachment_id' ),
		'mba_featured'           => array( 'boolean', 'rest_sanitize_boolean' ),
		'mba_display_order'      => array( 'integer', 'mba_core_sanitize_display_order' ),
	);
	foreach ( $scalars as $key => $config ) {
		mba_core_register_product_scalar( 'mba_product', $key, $config[0], $config[1] );
	}

	$string_item = array( 'type' => 'string' );
	$id_item = array(
		'type'    => 'integer',
		'minimum' => 1,
	);
	foreach ( array( 'mba_benefits', 'mba_configurations', 'mba_glazing_options', 'mba_applications' ) as $key ) {
		mba_core_register_product_array( 'mba_product', $key, $string_item, 'mba_core_sanitize_string_list' );
	}
	mba_core_register_product_array( 'mba_product', 'mba_gallery', $id_item, 'mba_core_sanitize_image_id_list' );
	mba_core_register_product_array( 'mba_product', 'mba_related_faqs', $id_item, 'mba_core_sanitize_faq_ids' );
	mba_core_register_product_array( 'mba_product', 'mba_related_projects', $id_item, 'mba_core_sanitize_project_ids' );
	foreach ( array( 'mba_faq', 'mba_project' ) as $post_type ) {
		mba_core_register_product_array( $post_type, 'mba_related_products', $id_item, 'mba_core_sanitize_product_ids' );
	}

	mba_core_register_product_array(
		'mba_product',
		'mba_performance_details',
		array(
			'type'       => 'object',
			'properties' => array(
				'label' => array( 'type' => 'string' ),
				'value' => array( 'type' => 'string' ),
			),
		),
		'mba_core_sanitize_performance_details'
	);
	mba_core_register_product_array(
		'mba_product',
		'mba_colors_finishes',
		array(
			'type'       => 'object',
			'properties' => array(
				'label'    => array( 'type' => 'string' ),
				'image_id' => array(
					'type' => 'integer',
					'minimum' => 0,
				),
			),
		),
		'mba_core_sanitize_colors_finishes'
	);
}
add_action( 'init', 'mba_core_register_product_meta' );
