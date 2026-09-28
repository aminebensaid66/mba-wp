<?php
/** WordPress personal-data export and erasure for private enquiry records. @package MBA_Site_Core */
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/** Register private quote/contact leads with the built-in privacy tools. */
function mba_core_register_privacy_tools( array $tools ): array {
	$tools['mba-leads'] = array(
		'exporter_friendly_name' => __( 'MBA quote and contact enquiries', 'mba-site-core' ),
		'callback'               => 'mba_core_export_lead_data',
	);
	return $tools;
}
add_filter( 'wp_privacy_personal_data_exporters', 'mba_core_register_privacy_tools' );

/** Register the lead eraser separately because WordPress exposes distinct exporter and eraser registries. */
function mba_core_register_privacy_erasers( array $erasers ): array {
	$erasers['mba-leads'] = array(
		'eraser_friendly_name' => __( 'MBA quote and contact enquiries', 'mba-site-core' ),
		'callback'             => 'mba_core_erase_lead_data',
	);
	return $erasers;
}
add_filter( 'wp_privacy_personal_data_erasers', 'mba_core_register_privacy_erasers' );

/** Return one page of enquiry data matching the verified privacy-request email. */
function mba_core_export_lead_data( string $email, int $page = 1 ): array {
	$email = sanitize_email( $email );
	if ( ! $email || ! is_email( $email ) ) {
		return array(
			'data' => array(),
			'done' => true,
		); }
	$posts = get_posts(
		array(
			'post_type'      => 'mba_quote_lead',
			'post_status'    => array( 'private', 'publish', 'draft', 'pending', 'trash' ),
			'posts_per_page' => 100,
			'paged'          => max( 1, $page ),
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'     => '_mba_quote_email',
					'value'   => $email,
					'compare' => '=',
				),
			),
		)
	);
	$posts = array_merge(
		$posts,
		get_posts(
			array(
				'post_type'      => 'mba_contact_lead',
				'post_status'    => array( 'private', 'publish', 'draft', 'pending', 'trash' ),
				'posts_per_page' => 100,
				'paged'          => max( 1, $page ),
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'meta_query'     => array(
					array(
						'key'     => '_mba_contact_email',
						'value'   => $email,
						'compare' => '=',
					),
				),
			)
		)
	);
	$data  = array();
	foreach ( $posts as $post ) {
		$prefix = 'mba_quote_lead' === $post->post_type ? '_mba_quote_' : '_mba_contact_';
		$fields = array(
			'name'              => __( 'Name', 'mba-site-core' ),
			'full_name'         => __( 'Name', 'mba-site-core' ),
			'email'             => __( 'Email', 'mba-site-core' ),
			'phone'             => __( 'Phone', 'mba-site-core' ),
			'location'          => __( 'Location', 'mba-site-core' ),
			'message'           => __( 'Message', 'mba-site-core' ),
			'source_page'       => __( 'Source page', 'mba-site-core' ),
			'landing_page'      => __( 'Landing page', 'mba-site-core' ),
			'products'          => __( 'Products', 'mba-site-core' ),
			'project_type'      => __( 'Project type', 'mba-site-core' ),
			'customer_type'     => __( 'Customer type', 'mba-site-core' ),
			'preferred_contact' => __( 'Preferred contact', 'mba-site-core' ),
			'quantity'          => __( 'Quantity or dimensions', 'mba-site-core' ),
			'timeframe'         => __( 'Timeframe', 'mba-site-core' ),
		);
		$items  = array();
		foreach ( $fields as $key => $label ) {
			$value = get_post_meta( $post->ID, $prefix . $key, true );
			if ( '' === $value || array() === $value ) {
				continue; }
			if ( is_array( $value ) ) {
				$value = implode( ', ', array_map( 'strval', $value ) ); }
			$items[] = array(
				'name'  => $label,
				'value' => (string) $value,
			);
		}
		$uploads = get_post_meta( $post->ID, '_mba_quote_uploads', true );
		if ( is_array( $uploads ) ) {
			foreach ( $uploads as $upload ) {
				if ( ! empty( $upload['name'] ) ) {
					$items[] = array(
						'name'  => __( 'Private attachment filename', 'mba-site-core' ),
						'value' => sanitize_file_name( (string) $upload['name'] ),
					);
				}
			}
		}
		$items[] = array(
			'name'  => __( 'Submitted', 'mba-site-core' ),
			'value' => get_date_from_gmt( $post->post_date_gmt, 'Y-m-d H:i:s' ),
		);
		$data[]  = array(
			'group_id'    => 'mba-enquiries',
			'group_label' => __( 'MBA enquiries', 'mba-site-core' ),
			'item_id'     => 'mba-enquiry-' . $post->ID,
			'data'        => $items,
		);
	}
	return array(
		'data' => $data,
		'done' => count( $posts ) < 100,
	);
}

/** Permanently remove matched enquiry records and any quote files stored outside the public uploads folder. */
function mba_core_erase_lead_data( string $email, int $page = 1 ): array {
	$email = sanitize_email( $email );
	if ( ! $email || ! is_email( $email ) ) {
		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		); }
	$posts = get_posts(
		array(
			'post_type'      => array( 'mba_quote_lead', 'mba_contact_lead' ),
			'post_status'    => array( 'private', 'publish', 'draft', 'pending', 'trash' ),
			'posts_per_page' => 100,
			/* Erased rows disappear from the result set, so each AJAX page handles the next oldest batch. */
			'paged'          => 1,
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => '_mba_quote_email',
					'value'   => $email,
					'compare' => '=',
				),
				array(
					'key'     => '_mba_contact_email',
					'value'   => $email,
					'compare' => '=',
				),
			),
		)
	);
	$removed  = false;
	$retained = false;
	foreach ( $posts as $post ) {
		if ( wp_delete_post( $post->ID, true ) ) {
			$removed = true;
		} else {
			$retained = true;
		}
	}
	return array(
		'items_removed'  => $removed,
		'items_retained' => $retained,
		'messages'       => $retained ? array( __( 'Some enquiry records could not be deleted. A site administrator must review and remove them manually.', 'mba-site-core' ) ) : array(),
		'done'           => count( $posts ) < 100 || $retained,
	);
}
