<?php
/**
 * Structured project fields and editing controls.
 *
 * @package MBA_Site_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keep only a real ISO calendar date.
 *
 * @param mixed $value Date input.
 * @return string
 */
function mba_core_sanitize_project_date( $value ): string {
	if ( ! is_string( $value ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
		return '';
	}
	$parts = array_map( 'intval', explode( '-', $value ) );
	return checkdate( $parts[1], $parts[2], $parts[0] ) ? $value : '';
}

/**
 * Register validated project fields for templates and REST consumers.
 */
function mba_core_register_project_meta(): void {
	$scalars = array(
		'mba_location_city'   => array( 'string', 'sanitize_text_field' ),
		'mba_location_region' => array( 'string', 'sanitize_text_field' ),
		'mba_completion_date' => array( 'string', 'mba_core_sanitize_project_date' ),
		'mba_challenge'       => array( 'string', 'wp_kses_post' ),
		'mba_solution'        => array( 'string', 'wp_kses_post' ),
		'mba_result'          => array( 'string', 'wp_kses_post' ),
		'mba_materials'       => array( 'string', 'sanitize_textarea_field' ),
		'mba_profiles'        => array( 'string', 'sanitize_textarea_field' ),
		'mba_glazing'         => array( 'string', 'sanitize_textarea_field' ),
		'mba_colors'          => array( 'string', 'sanitize_textarea_field' ),
		'mba_featured'        => array( 'boolean', 'rest_sanitize_boolean' ),
		'mba_display_order'   => array( 'integer', 'mba_core_sanitize_display_order' ),
	);
	foreach ( $scalars as $key => $config ) {
		mba_core_register_product_scalar( 'mba_project', $key, $config[0], $config[1] );
	}
	$image_item = array(
		'type'    => 'integer',
		'minimum' => 1,
	);
	foreach ( array( 'mba_gallery_before', 'mba_gallery_during', 'mba_gallery_after' ) as $key ) {
		mba_core_register_product_array( 'mba_project', $key, $image_item, 'mba_core_sanitize_image_id_list' );
	}
	register_post_meta(
		'mba_project',
		'mba_testimonial',
		array(
			'type'              => 'integer',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'mba_core_sanitize_testimonial_id',
			'auth_callback'     => 'mba_core_product_meta_auth',
		)
	);
}
add_action( 'init', 'mba_core_register_project_meta' );

/**
 * Require a testimonial record, or zero to clear the relationship.
 *
 * @param mixed $value Raw ID.
 * @return int
 */
function mba_core_sanitize_testimonial_id( $value ): int {
	$id = mba_core_valid_positive_id( $value );
	return $id && 'mba_testimonial' === get_post_type( $id ) ? $id : 0;
}

/**
 * Add the project editor panel.
 */
function mba_core_add_project_meta_box(): void {
	add_meta_box( 'mba-project-details', __( 'Project details', 'mba-site-core' ), 'mba_core_render_project_meta_box', 'mba_project', 'normal', 'high', array( '__block_editor_compatible_meta_box' => true ) );
}
add_action( 'add_meta_boxes_mba_project', 'mba_core_add_project_meta_box' );

/**
 * Load media controls on project editing screens.
 *
 * @param string $hook_suffix Admin page.
 */
function mba_core_project_admin_assets( string $hook_suffix ): void {
	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || 'mba_project' !== $screen->post_type ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'mba-project-admin', plugins_url( 'assets/js/project-admin.js', dirname( __DIR__ ) . '/mba-site-core.php' ), array( 'jquery', 'jquery-ui-sortable' ), MBA_CORE_VERSION, true );
	wp_localize_script(
		'mba-project-admin',
		'mbaProjectAdmin',
		array(
			'choose' => __( 'Choose project photos', 'mba-site-core' ),
			'remove' => __( 'Remove photo', 'mba-site-core' ),
		)
	);
	wp_enqueue_style( 'mba-product-admin', plugins_url( 'assets/css/product-admin.css', dirname( __DIR__ ) . '/mba-site-core.php' ), array(), MBA_CORE_VERSION );
}
add_action( 'admin_enqueue_scripts', 'mba_core_project_admin_assets' );

/**
 * Render one sortable gallery whose captions live in the Media Library.
 *
 * @param WP_Post $post  Current project.
 * @param string  $key   Meta key.
 * @param string  $label Visible label.
 */
function mba_core_render_project_gallery( WP_Post $post, string $key, string $label ): void {
	$images = mba_core_sanitize_image_id_list( mba_core_product_meta_value( $post->ID, $key, array() ) );
	?>
	<div class="mba-field mba-project-gallery" data-field="<?php echo esc_attr( $key ); ?>">
		<label><strong><?php echo esc_html( $label ); ?></strong> <span class="mba-optional"><?php esc_html_e( 'Optional', 'mba-site-core' ); ?></span></label>
		<input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( implode( ',', $images ) ); ?>">
		<ul class="mba-gallery-list">
			<?php foreach ( $images as $image_id ) : ?>
				<li data-id="<?php echo esc_attr( (string) $image_id ); ?>">
					<?php echo wp_kses_post( wp_get_attachment_image( $image_id, 'thumbnail' ) ); ?>
					<button type="button" class="button-link-delete mba-project-gallery-remove" aria-label="<?php esc_attr_e( 'Remove photo', 'mba-site-core' ); ?>">×</button>
				</li>
			<?php endforeach; ?>
		</ul>
		<button type="button" class="button mba-project-gallery-choose"><?php esc_html_e( 'Choose photos', 'mba-site-core' ); ?></button>
	</div>
	<?php
}

/**
 * Render structured project fields under the editor.
 *
 * @param WP_Post $post Current project.
 */
function mba_core_render_project_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'mba_save_project_details', 'mba_project_details_nonce' );
	$products = mba_core_sanitize_product_ids( mba_core_product_meta_value( $post->ID, 'mba_related_products', array() ) );
	$testimonials = get_posts(
		array(
			'post_type' => 'mba_testimonial',
			'post_status' => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
		)
	);
	?>
	<div class="mba-product-fields">
		<p class="description mba-guidance"><strong><?php esc_html_e( 'Required:', 'mba-site-core' ); ?></strong> <?php esc_html_e( 'Use the project title. All fields below are optional. Select project type, general location and product categories in the editor sidebar for archive filters.', 'mba-site-core' ); ?></p>
		<p class="description mba-guidance"><strong><?php esc_html_e( 'Privacy and photos:', 'mba-site-core' ); ?></strong> <?php esc_html_e( 'Enter only city/region, never a private street address. Obtain client consent before publication. Upload original project photos; WordPress creates responsive sizes and WebP copies where supported. Set captions, alt text, and crop focal points in the Media Library; drag photos to reorder.', 'mba-site-core' ); ?></p>
		<div class="mba-grid-fields">
			<?php
			foreach ( array(
				'mba_location_city' => __( 'City', 'mba-site-core' ),
				'mba_location_region' => __( 'Region', 'mba-site-core' ),
			) as $key => $label ) :
				?>
				<div class="mba-field"><label for="<?php echo esc_attr( $key ); ?>"><strong><?php echo esc_html( $label ); ?></strong> <span class="mba-optional"><?php esc_html_e( 'Optional', 'mba-site-core' ); ?></span></label><input class="widefat" type="text" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( mba_core_product_meta_value( $post->ID, $key ) ); ?>"></div>
			<?php endforeach; ?>
			<div class="mba-field"><label for="mba_completion_date"><strong><?php esc_html_e( 'Completion date', 'mba-site-core' ); ?></strong> <span class="mba-optional"><?php esc_html_e( 'Optional', 'mba-site-core' ); ?></span></label><input class="widefat" type="date" id="mba_completion_date" name="mba_completion_date" value="<?php echo esc_attr( mba_core_product_meta_value( $post->ID, 'mba_completion_date' ) ); ?>"></div>
		</div>
		<?php
		foreach ( array(
			'mba_challenge' => __( 'Challenge', 'mba-site-core' ),
			'mba_solution' => __( 'Solution', 'mba-site-core' ),
			'mba_result' => __( 'Result', 'mba-site-core' ),
			'mba_materials' => __( 'Materials', 'mba-site-core' ),
			'mba_profiles' => __( 'Profiles', 'mba-site-core' ),
			'mba_glazing' => __( 'Glazing', 'mba-site-core' ),
			'mba_colors' => __( 'Colors', 'mba-site-core' ),
		) as $key => $label ) :
			?>
			<div class="mba-field"><label for="<?php echo esc_attr( $key ); ?>"><strong><?php echo esc_html( $label ); ?></strong> <span class="mba-optional"><?php esc_html_e( 'Optional', 'mba-site-core' ); ?></span></label><textarea class="widefat" rows="4" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>"><?php echo esc_textarea( mba_core_product_meta_value( $post->ID, $key ) ); ?></textarea></div>
		<?php endforeach; ?>
		<?php
		mba_core_render_project_gallery( $post, 'mba_gallery_before', __( 'Before photos', 'mba-site-core' ) );
		mba_core_render_project_gallery( $post, 'mba_gallery_during', __( 'During photos', 'mba-site-core' ) );
		mba_core_render_project_gallery( $post, 'mba_gallery_after', __( 'After photos', 'mba-site-core' ) );
		?>
		<div class="mba-grid-fields">
			<div class="mba-field"><?php mba_core_render_relationship_select( 'mba_related_products', __( 'Installed products', 'mba-site-core' ), 'mba_product', $products ); ?></div>
			<div class="mba-field"><label for="mba_testimonial"><strong><?php esc_html_e( 'Related testimonial', 'mba-site-core' ); ?></strong> <span class="mba-optional"><?php esc_html_e( 'Optional', 'mba-site-core' ); ?></span></label><select class="widefat" id="mba_testimonial" name="mba_testimonial"><option value="0"><?php esc_html_e( 'None', 'mba-site-core' ); ?></option>
			<?php
			foreach ( $testimonials as $testimonial ) :
				?>
				<option value="<?php echo esc_attr( (string) $testimonial->ID ); ?>" <?php selected( (int) mba_core_product_meta_value( $post->ID, 'mba_testimonial', 0 ), $testimonial->ID ); ?>><?php echo esc_html( $testimonial->post_title ); ?></option><?php endforeach; ?></select></div>
		</div>
		<div class="mba-grid-fields mba-compact-fields">
			<div class="mba-field"><label><input type="checkbox" name="mba_featured" value="1" <?php checked( (bool) mba_core_product_meta_value( $post->ID, 'mba_featured', false ) ); ?>> <strong><?php esc_html_e( 'Feature this project', 'mba-site-core' ); ?></strong></label></div>
			<div class="mba-field"><label for="mba_display_order"><strong><?php esc_html_e( 'Display order', 'mba-site-core' ); ?></strong></label><input type="number" min="0" max="9999" step="1" id="mba_display_order" name="mba_display_order" value="<?php echo esc_attr( (string) mba_core_sanitize_display_order( mba_core_product_meta_value( $post->ID, 'mba_display_order', 0 ) ) ); ?>"></div>
		</div>
	</div>
	<?php
}

/**
 * Maintain both sides of the project/product relationship.
 *
 * @param int        $project_id Project ID.
 * @param array<int> $old_ids    Previous product IDs.
 * @param array<int> $new_ids    Selected product IDs.
 */
function mba_core_sync_project_products( int $project_id, array $old_ids, array $new_ids ): void {
	foreach ( array_unique( array_merge( $old_ids, $new_ids ) ) as $product_id ) {
		if ( 'mba_product' !== get_post_type( $product_id ) ) {
			continue;
		}
		$projects = mba_core_sanitize_project_ids( get_post_meta( $product_id, 'mba_related_projects', true ) );
		if ( in_array( $product_id, $new_ids, true ) ) {
			$projects[] = $project_id;
			$projects   = array_values( array_unique( $projects ) );
		} else {
			$projects = array_values( array_diff( $projects, array( $project_id ) ) );
		}
		update_post_meta( $product_id, 'mba_related_projects', $projects );
	}
}

/**
 * Validate and persist project fields.
 *
 * @param int     $post_id Project ID.
 * @param WP_Post $post    Project.
 */
function mba_core_save_project_details( int $post_id, WP_Post $post ): void {
	if ( 'mba_project' !== $post->post_type || ! isset( $_POST['mba_project_details_nonce'] ) ) {
		return;
	}
	$nonce = sanitize_text_field( wp_unslash( $_POST['mba_project_details_nonce'] ) );
	if ( ! wp_verify_nonce( $nonce, 'mba_save_project_details' ) || ! current_user_can( 'edit_post', $post_id ) || wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	foreach ( array( 'mba_location_city', 'mba_location_region' ) as $key ) {
		update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ?? '' ) ) );
	}
	update_post_meta( $post_id, 'mba_completion_date', mba_core_sanitize_project_date( wp_unslash( $_POST['mba_completion_date'] ?? '' ) ) );
	foreach ( array( 'mba_challenge', 'mba_solution', 'mba_result' ) as $key ) {
		update_post_meta( $post_id, $key, wp_kses_post( wp_unslash( $_POST[ $key ] ?? '' ) ) );
	}
	foreach ( array( 'mba_materials', 'mba_profiles', 'mba_glazing', 'mba_colors' ) as $key ) {
		update_post_meta( $post_id, $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ?? '' ) ) );
	}
	foreach ( array( 'mba_gallery_before', 'mba_gallery_during', 'mba_gallery_after' ) as $key ) {
		$ids = explode( ',', (string) wp_unslash( $_POST[ $key ] ?? '' ) );
		update_post_meta( $post_id, $key, mba_core_sanitize_image_id_list( $ids ) );
	}
	update_post_meta( $post_id, 'mba_testimonial', mba_core_sanitize_testimonial_id( wp_unslash( $_POST['mba_testimonial'] ?? 0 ) ) );
	update_post_meta( $post_id, 'mba_featured', isset( $_POST['mba_featured'] ) );
	update_post_meta( $post_id, 'mba_display_order', mba_core_sanitize_display_order( wp_unslash( $_POST['mba_display_order'] ?? 0 ) ) );
	$old_products = mba_core_sanitize_product_ids( get_post_meta( $post_id, 'mba_related_products', true ) );
	$new_products = mba_core_sanitize_product_ids( wp_unslash( (array) ( $_POST['mba_related_products'] ?? array() ) ) );
	update_post_meta( $post_id, 'mba_related_products', $new_products );
	mba_core_sync_project_products( $post_id, $old_products, $new_products );
}
add_action( 'save_post_mba_project', 'mba_core_save_project_details', 10, 2 );

/**
 * Remove product backlinks before permanent project deletion.
 *
 * @param int $post_id Post ID.
 */
function mba_core_delete_project_backlinks( int $post_id ): void {
	if ( 'mba_project' !== get_post_type( $post_id ) ) {
		return;
	}
	$products = mba_core_sanitize_product_ids( get_post_meta( $post_id, 'mba_related_products', true ) );
	mba_core_sync_project_products( $post_id, $products, array() );
}
add_action( 'before_delete_post', 'mba_core_delete_project_backlinks' );
