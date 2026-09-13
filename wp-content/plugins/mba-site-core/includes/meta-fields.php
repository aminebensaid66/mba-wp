<?php
/**
 * Native REST-enabled fields for the initial content model.
 *
 * @package MBA_Site_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register safe scalar metadata. Rich repeaters and media relationships are a
 * dedicated backlog item so their editing experience can be tested properly.
 */
function mba_core_register_meta_fields(): void {
	$fields = array(
		'mba_product' => array(
			'mba_short_description' => 'string',
			'mba_materials_profiles' => 'string',
			'mba_maintenance'        => 'string',
			'mba_featured'           => 'boolean',
			'mba_display_order'      => 'integer',
		),
		'mba_project' => array(
			'mba_location_city'   => 'string',
			'mba_location_region' => 'string',
			'mba_completion_date' => 'string',
			'mba_challenge'       => 'string',
			'mba_solution'        => 'string',
			'mba_result'          => 'string',
			'mba_featured'        => 'boolean',
			'mba_display_order'   => 'integer',
		),
		'mba_testimonial' => array(
			'mba_customer_label'              => 'string',
			'mba_customer_location'           => 'string',
			'mba_rating'                      => 'integer',
			'mba_publication_consent_confirmed' => 'boolean',
			'mba_featured'                    => 'boolean',
		),
		'mba_partner' => array(
			'mba_partner_url'   => 'string',
			'mba_display_order' => 'integer',
			'mba_featured'      => 'boolean',
		),
	);

	foreach ( $fields as $post_type => $post_fields ) {
		foreach ( $post_fields as $key => $type ) {
			register_post_meta(
				$post_type,
				$key,
				array(
					'type'              => $type,
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => 'string' === $type ? 'sanitize_text_field' : null,
					'auth_callback'     => static function (): bool {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}
}
add_action( 'init', 'mba_core_register_meta_fields' );

