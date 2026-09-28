<?php
/**
 * Product editing experience and relationship synchronization.
 *
 * @package MBA_Site_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add structured product fields below the block editor.
 */
function mba_core_add_product_meta_boxes(): void {
	add_meta_box(
		'mba-product-details',
		__( 'Product details', 'mba-site-core' ),
		'mba_core_render_product_meta_box',
		'mba_product',
		'normal',
		'high',
		array( '__block_editor_compatible_meta_box' => true )
	);
}
add_action( 'add_meta_boxes_mba_product', 'mba_core_add_product_meta_boxes' );

/**
 * Enqueue media/repeater helpers only on product editing screens.
 *
 * @param string $hook_suffix Current admin page hook.
 */
function mba_core_product_admin_assets( string $hook_suffix ): void {
	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || 'mba_product' !== $screen->post_type ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'mba-product-admin', plugins_url( 'assets/js/product-admin.js', dirname( __DIR__ ) . '/mba-site-core.php' ), array( 'jquery', 'jquery-ui-sortable' ), MBA_CORE_VERSION, true );
	wp_localize_script(
		'mba-product-admin',
		'mbaProductAdmin',
		array(
			'galleryTitle' => __( 'Choose product gallery images', 'mba-site-core' ),
			'pdfTitle'     => __( 'Choose technical PDF', 'mba-site-core' ),
			'finishTitle'  => __( 'Choose finish image', 'mba-site-core' ),
			'removeImage'  => __( 'Remove image', 'mba-site-core' ),
			'noPdf'        => __( 'No PDF selected', 'mba-site-core' ),
		)
	);
	wp_enqueue_style( 'mba-product-admin', plugins_url( 'assets/css/product-admin.css', dirname( __DIR__ ) . '/mba-site-core.php' ), array(), MBA_CORE_VERSION );
}
add_action( 'admin_enqueue_scripts', 'mba_core_product_admin_assets' );

/**
 * Read a meta value with a predictable default.
 *
 * @param int   $post_id Post ID.
 * @param string $key Meta key.
 * @param mixed $default Default value.
 * @return mixed
 */
function mba_core_product_meta_value( int $post_id, string $key, $default = '' ) {
	$value = get_post_meta( $post_id, $key, true );
	return '' === $value ? $default : $value;
}

/**
 * Render a simple repeatable list of text values.
 *
 * @param string        $name Field name.
 * @param array<string> $values Existing values.
 * @param string        $placeholder Input placeholder.
 */
function mba_core_render_string_repeater( string $name, array $values, string $placeholder ): void {
	if ( empty( $values ) ) {
		$values = array( '' );
	}
	?>
	<div class="mba-repeater" data-kind="string">
		<div class="mba-repeater-rows">
			<?php foreach ( $values as $value ) : ?>
				<div class="mba-repeater-row">
					<input class="widefat" type="text" name="<?php echo esc_attr( $name ); ?>[]" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>">
					<button type="button" class="button-link-delete mba-remove-row"><?php esc_html_e( 'Remove', 'mba-site-core' ); ?></button>
				</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="button mba-add-row"><?php esc_html_e( 'Add row', 'mba-site-core' ); ?></button>
	</div>
	<?php
}

/**
 * Render the product editing fields.
 *
 * @param WP_Post $post Product post.
 */
function mba_core_render_product_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'mba_save_product_details', 'mba_product_details_nonce' );
	$gallery      = mba_core_sanitize_id_list( mba_core_product_meta_value( $post->ID, 'mba_gallery', array() ) );
	$performance  = (array) mba_core_product_meta_value( $post->ID, 'mba_performance_details', array() );
	$finishes     = (array) mba_core_product_meta_value( $post->ID, 'mba_colors_finishes', array() );
	$related_faqs = mba_core_sanitize_id_list( mba_core_product_meta_value( $post->ID, 'mba_related_faqs', array() ) );
	$related_projects = mba_core_sanitize_id_list( mba_core_product_meta_value( $post->ID, 'mba_related_projects', array() ) );
	?>
	<div class="mba-product-fields">
		<p class="description mba-guidance"><strong><?php esc_html_e( 'Required:', 'mba-site-core' ); ?></strong> <?php esc_html_e( 'Use the core Product title to identify the item. All structured fields below are optional; publish only information that MBA has verified.', 'mba-site-core' ); ?></p>
		<p class="description mba-guidance"><strong><?php esc_html_e( 'Photo guidance:', 'mba-site-core' ); ?></strong> <?php esc_html_e( 'Use a landscape cover and clear gallery photos. Upload camera originals; WordPress creates responsive sizes and WebP copies where the server supports them. In the Media Library, add accurate alt text/captions and choose a crop focal point. Confirm publication consent before using client-property photos.', 'mba-site-core' ); ?></p>

		<div class="mba-field">
			<label for="mba_short_description"><strong><?php esc_html_e( 'Short description', 'mba-site-core' ); ?></strong> <span class="mba-optional"><?php esc_html_e( 'Optional', 'mba-site-core' ); ?></span></label>
			<textarea class="widefat" rows="3" id="mba_short_description" name="mba_short_description"><?php echo esc_textarea( mba_core_product_meta_value( $post->ID, 'mba_short_description' ) ); ?></textarea>
		</div>

		<div class="mba-grid-fields">
			<div class="mba-field"><label><strong><?php esc_html_e( 'Benefits', 'mba-site-core' ); ?></strong> <span class="mba-optional"><?php esc_html_e( 'Optional', 'mba-site-core' ); ?></span></label><?php mba_core_render_string_repeater( 'mba_benefits', (array) mba_core_product_meta_value( $post->ID, 'mba_benefits', array() ), __( 'Example: easy daily ventilation', 'mba-site-core' ) ); ?></div>
			<div class="mba-field"><label><strong><?php esc_html_e( 'Configurations', 'mba-site-core' ); ?></strong> <span class="mba-optional"><?php esc_html_e( 'Optional', 'mba-site-core' ); ?></span></label><?php mba_core_render_string_repeater( 'mba_configurations', (array) mba_core_product_meta_value( $post->ID, 'mba_configurations', array() ), __( 'Example: one leaf', 'mba-site-core' ) ); ?></div>
			<div class="mba-field"><label><strong><?php esc_html_e( 'Glazing options', 'mba-site-core' ); ?></strong> <span class="mba-optional"><?php esc_html_e( 'Optional', 'mba-site-core' ); ?></span></label><?php mba_core_render_string_repeater( 'mba_glazing_options', (array) mba_core_product_meta_value( $post->ID, 'mba_glazing_options', array() ), __( 'Only options confirmed by MBA', 'mba-site-core' ) ); ?></div>
			<div class="mba-field"><label><strong><?php esc_html_e( 'Recommended applications', 'mba-site-core' ); ?></strong> <span class="mba-optional"><?php esc_html_e( 'Optional', 'mba-site-core' ); ?></span></label><?php mba_core_render_string_repeater( 'mba_applications', (array) mba_core_product_meta_value( $post->ID, 'mba_applications', array() ), __( 'Example: residential renovation', 'mba-site-core' ) ); ?></div>
		</div>

		<div class="mba-field">
			<label for="mba_materials_profiles"><strong><?php esc_html_e( 'Materials and profile systems', 'mba-site-core' ); ?></strong> <span class="mba-optional"><?php esc_html_e( 'Optional', 'mba-site-core' ); ?></span></label>
			<?php
			wp_editor(
				(string) mba_core_product_meta_value( $post->ID, 'mba_materials_profiles' ),
				'mba_materials_profiles',
				array(
					'textarea_name' => 'mba_materials_profiles',
					'textarea_rows' => 5,
					'media_buttons' => false,
				)
			);
			?>
		</div>

		<div class="mba-field">
			<label><strong><?php esc_html_e( 'Colors and finishes', 'mba-site-core' ); ?></strong> <span class="mba-optional"><?php esc_html_e( 'Optional image per finish', 'mba-site-core' ); ?></span></label>
			<div class="mba-finish-rows">
				<?php
				if ( empty( $finishes ) ) {
					$finishes = array(
						array(
							'label' => '',
							'image_id' => 0,
						),
					);
				}
				?>
				<?php
				foreach ( $finishes as $finish ) :
					$image_id = absint( $finish['image_id'] ?? 0 );
					?>
					<div class="mba-finish-row mba-repeater-row">
						<input type="text" name="mba_colors_finishes[label][]" value="<?php echo esc_attr( $finish['label'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Finish label', 'mba-site-core' ); ?>">
						<input type="hidden" class="mba-finish-image-id" name="mba_colors_finishes[image_id][]" value="<?php echo esc_attr( (string) $image_id ); ?>">
						<span class="mba-finish-preview"><?php echo $image_id ? wp_kses_post( wp_get_attachment_image( $image_id, 'thumbnail' ) ) : ''; ?></span>
						<button type="button" class="button mba-choose-finish-image"><?php esc_html_e( 'Choose image', 'mba-site-core' ); ?></button>
						<button type="button" class="button-link-delete mba-remove-row"><?php esc_html_e( 'Remove', 'mba-site-core' ); ?></button>
					</div>
				<?php endforeach; ?>
			</div>
			<button type="button" class="button mba-add-finish"><?php esc_html_e( 'Add finish', 'mba-site-core' ); ?></button>
		</div>

		<div class="mba-field">
			<label><strong><?php esc_html_e( 'Verified performance details', 'mba-site-core' ); ?></strong> <span class="mba-optional"><?php esc_html_e( 'Optional — do not enter estimated or supplier values unless MBA has verified them for this product.', 'mba-site-core' ); ?></span></label>
			<div class="mba-performance-rows">
				<?php
				if ( empty( $performance ) ) {
					$performance = array(
						array(
							'label' => '',
							'value' => '',
						),
					);
				}
				?>
				<?php foreach ( $performance as $row ) : ?>
					<div class="mba-performance-row mba-repeater-row">
						<input type="text" name="mba_performance_details[label][]" value="<?php echo esc_attr( $row['label'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Label', 'mba-site-core' ); ?>">
						<input type="text" name="mba_performance_details[value][]" value="<?php echo esc_attr( $row['value'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Verified value', 'mba-site-core' ); ?>">
						<button type="button" class="button-link-delete mba-remove-row"><?php esc_html_e( 'Remove', 'mba-site-core' ); ?></button>
					</div>
				<?php endforeach; ?>
			</div>
			<button type="button" class="button mba-add-performance"><?php esc_html_e( 'Add performance row', 'mba-site-core' ); ?></button>
		</div>

		<div class="mba-field">
			<label for="mba_maintenance"><strong><?php esc_html_e( 'Maintenance guidance', 'mba-site-core' ); ?></strong> <span class="mba-optional"><?php esc_html_e( 'Optional', 'mba-site-core' ); ?></span></label>
			<?php
			wp_editor(
				(string) mba_core_product_meta_value( $post->ID, 'mba_maintenance' ),
				'mba_maintenance',
				array(
					'textarea_name' => 'mba_maintenance',
					'textarea_rows' => 4,
					'media_buttons' => false,
				)
			);
			?>
		</div>

		<div class="mba-field">
			<label><strong><?php esc_html_e( 'Gallery', 'mba-site-core' ); ?></strong> <span class="mba-optional"><?php esc_html_e( 'Optional; drag thumbnails to reorder.', 'mba-site-core' ); ?></span></label>
			<input type="hidden" id="mba_gallery" name="mba_gallery" value="<?php echo esc_attr( implode( ',', $gallery ) ); ?>">
			<ul class="mba-gallery-list">
				<?php foreach ( $gallery as $image_id ) : ?>
					<li data-id="<?php echo esc_attr( (string) $image_id ); ?>"><?php echo wp_kses_post( wp_get_attachment_image( $image_id, 'thumbnail' ) ); ?><button type="button" class="button-link-delete mba-gallery-remove" aria-label="<?php esc_attr_e( 'Remove image', 'mba-site-core' ); ?>">×</button></li>
				<?php endforeach; ?>
			</ul>
			<button type="button" class="button mba-gallery-choose"><?php esc_html_e( 'Choose gallery images', 'mba-site-core' ); ?></button>
		</div>

		<div class="mba-field">
			<label><strong><?php esc_html_e( 'Technical document', 'mba-site-core' ); ?></strong> <span class="mba-optional"><?php esc_html_e( 'Optional PDF only; use an approved MBA/supplier document.', 'mba-site-core' ); ?></span></label>
			<?php $document_id = absint( mba_core_product_meta_value( $post->ID, 'mba_technical_document', 0 ) ); ?>
			<input type="hidden" id="mba_technical_document" name="mba_technical_document" value="<?php echo esc_attr( (string) $document_id ); ?>">
			<span class="mba-document-label"><?php echo $document_id ? esc_html( get_the_title( $document_id ) ) : esc_html__( 'No PDF selected', 'mba-site-core' ); ?></span>
			<button type="button" class="button mba-document-choose"><?php esc_html_e( 'Choose PDF', 'mba-site-core' ); ?></button>
			<button type="button" class="button-link-delete mba-document-remove"><?php esc_html_e( 'Remove', 'mba-site-core' ); ?></button>
		</div>

		<div class="mba-grid-fields">
			<div class="mba-field"><?php mba_core_render_relationship_select( 'mba_related_faqs', __( 'Related FAQs', 'mba-site-core' ), 'mba_faq', $related_faqs ); ?></div>
			<div class="mba-field"><?php mba_core_render_relationship_select( 'mba_related_projects', __( 'Related projects', 'mba-site-core' ), 'mba_project', $related_projects ); ?></div>
		</div>

		<div class="mba-grid-fields mba-compact-fields">
			<div class="mba-field"><label><input type="checkbox" name="mba_featured" value="1" <?php checked( (bool) mba_core_product_meta_value( $post->ID, 'mba_featured', false ) ); ?>> <strong><?php esc_html_e( 'Feature this product', 'mba-site-core' ); ?></strong></label></div>
			<div class="mba-field"><label for="mba_display_order"><strong><?php esc_html_e( 'Display order', 'mba-site-core' ); ?></strong></label><input type="number" min="0" max="9999" step="1" id="mba_display_order" name="mba_display_order" value="<?php echo esc_attr( (string) absint( mba_core_product_meta_value( $post->ID, 'mba_display_order', 0 ) ) ); ?>"></div>
		</div>
	</div>
	<?php
}

/**
 * Render a relationship multi-select.
 *
 * @param string     $name Field name.
 * @param string     $label Label.
 * @param string     $post_type Target post type.
 * @param array<int> $selected Selected IDs.
 */
function mba_core_render_relationship_select( string $name, string $label, string $post_type, array $selected ): void {
	$items = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);
	?>
	<label for="<?php echo esc_attr( $name ); ?>"><strong><?php echo esc_html( $label ); ?></strong> <span class="mba-optional"><?php esc_html_e( 'Optional', 'mba-site-core' ); ?></span></label>
	<select class="widefat" multiple size="6" id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>[]">
		<?php foreach ( $items as $item ) : ?>
			<option value="<?php echo esc_attr( (string) $item->ID ); ?>" <?php selected( in_array( $item->ID, $selected, true ) ); ?>>
			<?php
			echo esc_html(
				$item->post_title ? $item->post_title : sprintf(
					/* translators: %d: WordPress post ID. */
					__( '(untitled #%d)', 'mba-site-core' ),
					$item->ID
				)
			);
			?>
				</option>
		<?php endforeach; ?>
	</select>
	<?php
}

/**
 * Build object rows from parallel POST arrays.
 *
 * @param array<string,mixed> $raw Posted array.
 * @param array<string>       $keys Row keys.
 * @return array<array<string,mixed>>
 */
function mba_core_zip_rows( array $raw, array $keys ): array {
	$rows  = array();
	$count = 0;
	foreach ( $keys as $key ) {
		$count = max( $count, is_array( $raw[ $key ] ?? null ) ? count( $raw[ $key ] ) : 0 );
	}
	for ( $index = 0; $index < $count; $index++ ) {
		$row = array();
		foreach ( $keys as $key ) {
			$row[ $key ] = is_array( $raw[ $key ] ?? null ) ? ( $raw[ $key ][ $index ] ?? '' ) : '';
		}
		$rows[] = $row;
	}
	return $rows;
}

/**
 * Validate one target relationship list.
 *
 * @param mixed  $raw Raw posted IDs.
 * @param string $post_type Expected target type.
 * @return array<int>
 */
function mba_core_validate_relationship_ids( $raw, string $post_type ): array {
	$valid = array();
	foreach ( mba_core_sanitize_id_list( wp_unslash( (array) $raw ) ) as $post_id ) {
		if ( get_post_type( $post_id ) === $post_type ) {
			$valid[] = $post_id;
		}
	}
	return $valid;
}

/**
 * Synchronize product backlinks on related content.
 *
 * @param int        $product_id Product ID.
 * @param string     $target_type Target post type.
 * @param array<int> $old_ids Previous targets.
 * @param array<int> $new_ids New targets.
 */
function mba_core_sync_product_backlinks( int $product_id, string $target_type, array $old_ids, array $new_ids ): void {
	foreach ( array_unique( array_merge( $old_ids, $new_ids ) ) as $target_id ) {
		if ( get_post_type( $target_id ) !== $target_type ) {
			continue;
		}
		$products = mba_core_sanitize_id_list( get_post_meta( $target_id, 'mba_related_products', true ) );
		if ( in_array( $target_id, $new_ids, true ) ) {
			$products[] = $product_id;
			$products   = array_values( array_unique( $products ) );
		} else {
			$products = array_values( array_diff( $products, array( $product_id ) ) );
		}
		update_post_meta( $target_id, 'mba_related_products', $products );
	}
}

/**
 * Store and validate product fields.
 *
 * @param int     $post_id Product post ID.
 * @param WP_Post $post    Product post.
 */
function mba_core_save_product_details( int $post_id, WP_Post $post ): void {
	if ( 'mba_product' !== $post->post_type || ! isset( $_POST['mba_product_details_nonce'] ) ) {
		return;
	}
	$nonce = sanitize_text_field( wp_unslash( $_POST['mba_product_details_nonce'] ) );
	if ( ! wp_verify_nonce( $nonce, 'mba_save_product_details' ) || ! current_user_can( 'edit_post', $post_id ) || wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	$text_fields = array( 'mba_short_description' => 'sanitize_textarea_field' );
	foreach ( $text_fields as $key => $sanitize ) {
		update_post_meta( $post_id, $key, call_user_func( $sanitize, wp_unslash( $_POST[ $key ] ?? '' ) ) );
	}
	foreach ( array( 'mba_materials_profiles', 'mba_maintenance' ) as $key ) {
		update_post_meta( $post_id, $key, wp_kses_post( wp_unslash( $_POST[ $key ] ?? '' ) ) );
	}
	foreach ( array( 'mba_benefits', 'mba_configurations', 'mba_glazing_options', 'mba_applications' ) as $key ) {
		update_post_meta( $post_id, $key, mba_core_sanitize_string_list( wp_unslash( (array) ( $_POST[ $key ] ?? array() ) ) ) );
	}

	$performance_raw = wp_unslash( (array) ( $_POST['mba_performance_details'] ?? array() ) );
	$finish_raw      = wp_unslash( (array) ( $_POST['mba_colors_finishes'] ?? array() ) );
	update_post_meta( $post_id, 'mba_performance_details', mba_core_sanitize_performance_details( mba_core_zip_rows( $performance_raw, array( 'label', 'value' ) ) ) );
	update_post_meta( $post_id, 'mba_colors_finishes', mba_core_sanitize_colors_finishes( mba_core_zip_rows( $finish_raw, array( 'label', 'image_id' ) ) ) );

	$gallery = array();
	foreach ( mba_core_sanitize_id_list( explode( ',', (string) wp_unslash( $_POST['mba_gallery'] ?? '' ) ) ) as $image_id ) {
		if ( wp_attachment_is_image( $image_id ) ) {
			$gallery[] = $image_id;
		}
	}
	update_post_meta( $post_id, 'mba_gallery', array_values( array_unique( $gallery ) ) );

	$document_id = mba_core_valid_positive_id( wp_unslash( $_POST['mba_technical_document'] ?? 0 ) );
	if ( $document_id && 'application/pdf' !== get_post_mime_type( $document_id ) ) {
		mba_core_set_product_admin_error( __( 'The technical document was rejected because it is not a PDF.', 'mba-site-core' ) );
	} else {
		update_post_meta( $post_id, 'mba_technical_document', $document_id );
	}

	update_post_meta( $post_id, 'mba_featured', isset( $_POST['mba_featured'] ) );
	update_post_meta( $post_id, 'mba_display_order', mba_core_sanitize_display_order( wp_unslash( $_POST['mba_display_order'] ?? 0 ) ) );

	$old_faqs     = mba_core_sanitize_id_list( get_post_meta( $post_id, 'mba_related_faqs', true ) );
	$old_projects = mba_core_sanitize_id_list( get_post_meta( $post_id, 'mba_related_projects', true ) );
	$new_faqs     = mba_core_validate_relationship_ids( $_POST['mba_related_faqs'] ?? array(), 'mba_faq' );
	$new_projects = mba_core_validate_relationship_ids( $_POST['mba_related_projects'] ?? array(), 'mba_project' );
	update_post_meta( $post_id, 'mba_related_faqs', $new_faqs );
	update_post_meta( $post_id, 'mba_related_projects', $new_projects );
	mba_core_sync_product_backlinks( $post_id, 'mba_faq', $old_faqs, $new_faqs );
	mba_core_sync_product_backlinks( $post_id, 'mba_project', $old_projects, $new_projects );
}
add_action( 'save_post_mba_product', 'mba_core_save_product_details', 10, 2 );

/**
 * Remove reverse links before a product is permanently deleted.
 *
 * @param int $post_id Post ID.
 */
function mba_core_delete_product_backlinks( int $post_id ): void {
	if ( 'mba_product' !== get_post_type( $post_id ) ) {
		return;
	}
	foreach ( array(
		'mba_faq' => 'mba_related_faqs',
		'mba_project' => 'mba_related_projects',
	) as $type => $key ) {
		$old_ids = mba_core_sanitize_id_list( get_post_meta( $post_id, $key, true ) );
		mba_core_sync_product_backlinks( $post_id, $type, $old_ids, array() );
	}
}
add_action( 'before_delete_post', 'mba_core_delete_product_backlinks' );

/**
 * Persist a user-specific validation message across the post-save redirect.
 *
 * @param string $message Validation message.
 */
function mba_core_set_product_admin_error( string $message ): void {
	set_transient( 'mba_product_error_' . get_current_user_id(), $message, 60 );
}

/**
 * Display product validation errors.
 */
function mba_core_product_admin_notices(): void {
	$key     = 'mba_product_error_' . get_current_user_id();
	$message = get_transient( $key );
	if ( ! $message ) {
		return;
	}
	delete_transient( $key );
	printf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', esc_html( $message ) );
}
add_action( 'admin_notices', 'mba_core_product_admin_notices' );

/**
 * Add concrete cover-image guidance to the core featured image box.
 *
 * @param string $content Existing featured image control markup.
 * @param int    $post_id Current post ID.
 * @return string
 */
function mba_core_featured_image_guidance( string $content, int $post_id ): string {
	if ( 'mba_product' !== get_post_type( $post_id ) ) {
		return $content;
	}
	$content .= '<p class="description">' . esc_html__( 'Choose a landscape cover. Upload the original; WordPress generates responsive sizes and WebP copies where supported. Add meaningful alt text and confirm publication rights/consent.', 'mba-site-core' ) . '</p>';
	return $content;
}
add_filter( 'admin_post_thumbnail_html', 'mba_core_featured_image_guidance', 10, 2 );

/**
 * Improve the Products list with useful editorial columns.
 *
 * @param array<string,string> $columns Existing columns.
 * @return array<string,string>
 */
function mba_core_product_columns( array $columns ): array {
	$columns['mba_cover']    = __( 'Cover', 'mba-site-core' );
	$columns['mba_featured'] = __( 'Featured', 'mba-site-core' );
	$columns['mba_order']    = __( 'Order', 'mba-site-core' );
	return $columns;
}
add_filter( 'manage_mba_product_posts_columns', 'mba_core_product_columns' );

/**
 * Render custom Products columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Product post ID.
 */
function mba_core_product_column_content( string $column, int $post_id ): void {
	if ( 'mba_cover' === $column ) {
		$thumbnail = get_the_post_thumbnail( $post_id, array( 64, 48 ) );
		echo wp_kses_post( $thumbnail ? $thumbnail : '—' );
	} elseif ( 'mba_featured' === $column ) {
		echo get_post_meta( $post_id, 'mba_featured', true ) ? esc_html__( 'Yes', 'mba-site-core' ) : '—';
	} elseif ( 'mba_order' === $column ) {
		echo esc_html( (string) absint( get_post_meta( $post_id, 'mba_display_order', true ) ) );
	}
}
add_action( 'manage_mba_product_posts_custom_column', 'mba_core_product_column_content', 10, 2 );
