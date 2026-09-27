<?php
/**
 * FAQ, testimonial and partner fields and owner editing panels.
 *
 * @package MBA_Site_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validate an optional image attachment.
 *
 * @param mixed $value Raw ID.
 * @return int
 */
function mba_core_sanitize_image_id( $value ): int {
	$id = mba_core_valid_positive_id( $value );
	return $id && wp_attachment_is_image( $id ) ? $id : 0;
}

/**
 * Keep a supplied rating from one to five; zero means no rating.
 *
 * @param mixed $value Raw rating.
 * @return int
 */
function mba_core_sanitize_rating( $value ): int {
	$rating = filter_var( $value, FILTER_VALIDATE_INT );
	return false !== $rating && $rating >= 1 && $rating <= 5 ? $rating : 0;
}

/**
 * Accept only HTTP(S) partner links.
 *
 * @param mixed $value Raw URL.
 * @return string
 */
function mba_core_sanitize_partner_url( $value ): string {
	if ( ! is_string( $value ) ) {
		return '';
	}
	$url = esc_url_raw( $value, array( 'http', 'https' ) );
	return in_array( wp_parse_url( $url, PHP_URL_SCHEME ), array( 'http', 'https' ), true ) ? $url : '';
}

/**
 * Validate an optional related project.
 *
 * @param mixed $value Raw ID.
 * @return int
 */
function mba_core_sanitize_single_project_id( $value ): int {
	$id = mba_core_valid_positive_id( $value );
	return $id && 'mba_project' === get_post_type( $id ) ? $id : 0;
}

/**
 * Register structured fields; answers/reviews/descriptions use the core editor.
 */
function mba_core_register_reusable_meta(): void {
	$fields = array(
		'mba_faq' => array(
			'mba_display_order' => array( 'integer', 'mba_core_sanitize_display_order' ),
		),
		'mba_testimonial' => array(
			'mba_customer_label' => array( 'string', 'sanitize_text_field' ),
			'mba_company' => array( 'string', 'sanitize_text_field' ),
			'mba_customer_location' => array( 'string', 'sanitize_text_field' ),
			'mba_rating' => array( 'integer', 'mba_core_sanitize_rating' ),
			'mba_customer_photo' => array( 'integer', 'mba_core_sanitize_image_id' ),
			'mba_related_project' => array( 'integer', 'mba_core_sanitize_single_project_id' ),
			'mba_publication_consent_confirmed' => array( 'boolean', 'rest_sanitize_boolean' ),
			'mba_featured' => array( 'boolean', 'rest_sanitize_boolean' ),
			'mba_display_order' => array( 'integer', 'mba_core_sanitize_display_order' ),
		),
		'mba_partner' => array(
			'mba_logo' => array( 'integer', 'mba_core_sanitize_image_id' ),
			'mba_partner_url' => array( 'string', 'mba_core_sanitize_partner_url' ),
			'mba_display_order' => array( 'integer', 'mba_core_sanitize_display_order' ),
			'mba_featured' => array( 'boolean', 'rest_sanitize_boolean' ),
		),
	);
	foreach ( $fields as $type => $type_fields ) {
		foreach ( $type_fields as $key => $config ) {
			mba_core_register_product_scalar( $type, $key, $config[0], $config[1] );
		}
	}
}
add_action( 'init', 'mba_core_register_reusable_meta' );

/**
 * Enforce consent for every metadata write, including REST and imports.
 *
 * @param int    $meta_id Metadata row ID.
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @param mixed  $value   New value.
 */
function mba_core_enforce_testimonial_consent( int $meta_id, int $post_id, string $key, $value ): void {
	unset( $meta_id, $value );
	if ( 'mba_testimonial' !== get_post_type( $post_id ) || ! in_array( $key, array( 'mba_featured', 'mba_publication_consent_confirmed' ), true ) ) {
		return;
	}
	if ( get_post_meta( $post_id, 'mba_featured', true ) && ! get_post_meta( $post_id, 'mba_publication_consent_confirmed', true ) ) {
		update_post_meta( $post_id, 'mba_featured', false );
	}
}
add_action( 'added_post_meta', 'mba_core_enforce_testimonial_consent', 10, 4 );
add_action( 'updated_post_meta', 'mba_core_enforce_testimonial_consent', 10, 4 );

/**
 * Add owner panels.
 */
function mba_core_add_reusable_meta_boxes(): void {
	foreach ( array( 'mba_faq', 'mba_testimonial', 'mba_partner' ) as $type ) {
		add_meta_box( 'mba-reusable-details', __( 'Entry details', 'mba-site-core' ), 'mba_core_render_reusable_meta_box', $type, 'normal', 'high', array( '__block_editor_compatible_meta_box' => true ) );
	}
}
add_action( 'add_meta_boxes', 'mba_core_add_reusable_meta_boxes' );

/**
 * Load owner media helpers.
 *
 * @param string $hook_suffix Admin page.
 */
function mba_core_reusable_admin_assets( string $hook_suffix ): void {
	$screen = get_current_screen();
	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) || ! $screen || ! in_array( $screen->post_type, array( 'mba_faq', 'mba_testimonial', 'mba_partner' ), true ) ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script( 'mba-entry-admin', plugins_url( 'assets/js/entry-admin.js', dirname( __DIR__ ) . '/mba-site-core.php' ), array( 'jquery' ), MBA_CORE_VERSION, true );
	wp_localize_script( 'mba-entry-admin', 'mbaEntryAdmin', array( 'choose' => __( 'Choose an image', 'mba-site-core' ) ) );
	wp_enqueue_style( 'mba-product-admin', plugins_url( 'assets/css/product-admin.css', dirname( __DIR__ ) . '/mba-site-core.php' ), array(), MBA_CORE_VERSION );
}
add_action( 'admin_enqueue_scripts', 'mba_core_reusable_admin_assets' );

/**
 * Render one labeled scalar input.
 *
 * @param WP_Post $post  Entry.
 * @param string  $key   Meta key.
 * @param string  $label Label.
 * @param string  $type  HTML input type.
 */
function mba_core_render_entry_input( WP_Post $post, string $key, string $label, string $type = 'text' ): void {
	?>
	<div class="mba-field"><label for="<?php echo esc_attr( $key ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label><input class="widefat" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" type="<?php echo esc_attr( $type ); ?>" value="<?php echo esc_attr( (string) mba_core_product_meta_value( $post->ID, $key ) ); ?>"></div>
	<?php
}

/**
 * Render a validated image selector.
 *
 * @param WP_Post $post  Entry.
 * @param string  $key   Meta key.
 * @param string  $label Label.
 */
function mba_core_render_entry_image( WP_Post $post, string $key, string $label ): void {
	$id = mba_core_sanitize_image_id( mba_core_product_meta_value( $post->ID, $key, 0 ) );
	?>
	<div class="mba-field mba-entry-image"><strong><?php echo esc_html( $label ); ?></strong><input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( (string) $id ); ?>"><div class="mba-entry-image-preview"><?php echo $id ? wp_kses_post( wp_get_attachment_image( $id, 'thumbnail' ) ) : ''; ?></div><button type="button" class="button mba-entry-image-choose"><?php esc_html_e( 'Choose image', 'mba-site-core' ); ?></button> <button type="button" class="button-link-delete mba-entry-image-remove"><?php esc_html_e( 'Remove image', 'mba-site-core' ); ?></button></div>
	<?php
}

/**
 * Render FAQ, testimonial, or partner fields.
 *
 * @param WP_Post $post Entry.
 */
function mba_core_render_reusable_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'mba_save_reusable_details', 'mba_reusable_details_nonce' );
	?>
	<div class="mba-product-fields">
	<?php if ( 'mba_faq' === $post->post_type ) : ?>
		<p class="description mba-guidance"><?php esc_html_e( 'Use the title for the question and the main editor for its answer. Choose an FAQ category in the sidebar. Product links and display order are optional.', 'mba-site-core' ); ?></p>
		<?php mba_core_render_relationship_select( 'mba_related_products', __( 'Related products', 'mba-site-core' ), 'mba_product', mba_core_sanitize_product_ids( mba_core_product_meta_value( $post->ID, 'mba_related_products', array() ) ) ); ?>
	<?php elseif ( 'mba_testimonial' === $post->post_type ) : ?>
		<p class="description mba-guidance"><?php esc_html_e( 'Use the main editor for the exact customer review. Confirm permission to publish the review, name, location, and optional photo. Do not invent ratings. Featuring requires publication consent.', 'mba-site-core' ); ?></p>
		<?php
		mba_core_render_entry_input( $post, 'mba_customer_label', __( 'Customer name or approved public label', 'mba-site-core' ) );
		mba_core_render_entry_input( $post, 'mba_company', __( 'Company (optional)', 'mba-site-core' ) );
		mba_core_render_entry_input( $post, 'mba_customer_location', __( 'General location (optional)', 'mba-site-core' ) );
		mba_core_render_entry_image( $post, 'mba_customer_photo', __( 'Customer photo (optional, with consent)', 'mba-site-core' ) );
		$projects = get_posts(
			array(
				'post_type' => 'mba_project',
				'posts_per_page' => -1,
				'post_status' => array( 'publish', 'draft', 'pending', 'private' ),
				'orderby' => 'title',
				'order' => 'ASC',
			)
		);
		?>
		<div class="mba-field"><label for="mba_rating"><strong><?php esc_html_e( 'Customer rating (optional, 1–5)', 'mba-site-core' ); ?></strong></label><input type="number" id="mba_rating" name="mba_rating" min="1" max="5" step="1" value="<?php echo get_post_meta( $post->ID, 'mba_rating', true ) ? esc_attr( (string) get_post_meta( $post->ID, 'mba_rating', true ) ) : ''; ?>"></div>
		<div class="mba-field"><label for="mba_related_project"><strong><?php esc_html_e( 'Related project (optional)', 'mba-site-core' ); ?></strong></label><select id="mba_related_project" name="mba_related_project"><option value="0"><?php esc_html_e( 'None', 'mba-site-core' ); ?></option>
		<?php
		foreach ( $projects as $project ) :
			?>
			<option value="<?php echo esc_attr( (string) $project->ID ); ?>" <?php selected( (int) get_post_meta( $post->ID, 'mba_related_project', true ), $project->ID ); ?>><?php echo esc_html( $project->post_title ); ?></option><?php endforeach; ?></select></div>
		<div class="mba-field"><label><input type="checkbox" name="mba_publication_consent_confirmed" value="1" <?php checked( (bool) get_post_meta( $post->ID, 'mba_publication_consent_confirmed', true ) ); ?>> <?php esc_html_e( 'Publication consent confirmed', 'mba-site-core' ); ?></label></div>
	<?php else : ?>
		<p class="description mba-guidance"><?php esc_html_e( 'Use the title for the partner name and the main editor for its description. Only publish logos you have permission to use. Prefer transparent PNG/WebP, at least 600 px wide and under 200 KB; preserve the original logo proportions and set meaningful alt text in Media Library. All details below are optional.', 'mba-site-core' ); ?></p>
		<?php
		mba_core_render_entry_image( $post, 'mba_logo', __( 'Partner logo', 'mba-site-core' ) );
		mba_core_render_entry_input( $post, 'mba_partner_url', __( 'Website URL (HTTP/HTTPS)', 'mba-site-core' ), 'url' );
		?>
	<?php endif; ?>
		<div class="mba-field"><label for="mba_display_order"><strong><?php esc_html_e( 'Display order (optional; lower first)', 'mba-site-core' ); ?></strong></label><input type="number" id="mba_display_order" name="mba_display_order" min="0" max="9999" step="1" value="<?php echo esc_attr( (string) mba_core_sanitize_display_order( get_post_meta( $post->ID, 'mba_display_order', true ) ) ); ?>"></div>
		<?php if ( 'mba_faq' !== $post->post_type ) : ?>
			<div class="mba-field"><label><input type="checkbox" name="mba_featured" value="1" <?php checked( (bool) get_post_meta( $post->ID, 'mba_featured', true ) ); ?>> <?php esc_html_e( 'Feature this entry', 'mba-site-core' ); ?></label></div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Keep FAQ links usable from both sides.
 *
 * @param int        $faq_id  FAQ ID.
 * @param array<int> $old_ids Previous products.
 * @param array<int> $new_ids Selected products.
 */
function mba_core_sync_faq_products( int $faq_id, array $old_ids, array $new_ids ): void {
	foreach ( array_unique( array_merge( $old_ids, $new_ids ) ) as $product_id ) {
		if ( 'mba_product' !== get_post_type( $product_id ) ) {
			continue;
		}
		$faqs = mba_core_sanitize_faq_ids( get_post_meta( $product_id, 'mba_related_faqs', true ) );
		$faqs = in_array( $product_id, $new_ids, true ) ? array_merge( $faqs, array( $faq_id ) ) : array_diff( $faqs, array( $faq_id ) );
		update_post_meta( $product_id, 'mba_related_faqs', array_values( array_unique( $faqs ) ) );
	}
}

/**
 * Save owner edits after nonce/capability checks.
 *
 * @param int     $post_id Entry ID.
 * @param WP_Post $post    Entry.
 */
function mba_core_save_reusable_details( int $post_id, WP_Post $post ): void {
	if ( ! in_array( $post->post_type, array( 'mba_faq', 'mba_testimonial', 'mba_partner' ), true ) || ! isset( $_POST['mba_reusable_details_nonce'] ) ) {
		return;
	}
	$nonce = sanitize_text_field( wp_unslash( $_POST['mba_reusable_details_nonce'] ) );
	if ( ! wp_verify_nonce( $nonce, 'mba_save_reusable_details' ) || ! current_user_can( 'edit_post', $post_id ) || wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, 'mba_display_order', mba_core_sanitize_display_order( wp_unslash( $_POST['mba_display_order'] ?? 0 ) ) );
	if ( 'mba_faq' === $post->post_type ) {
		$old_ids = mba_core_sanitize_product_ids( get_post_meta( $post_id, 'mba_related_products', true ) );
		$new_ids = mba_core_sanitize_product_ids( wp_unslash( (array) ( $_POST['mba_related_products'] ?? array() ) ) );
		update_post_meta( $post_id, 'mba_related_products', $new_ids );
		mba_core_sync_faq_products( $post_id, $old_ids, $new_ids );
		return;
	}
	if ( 'mba_testimonial' === $post->post_type ) {
		foreach ( array( 'mba_customer_label', 'mba_company', 'mba_customer_location' ) as $key ) {
			update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ?? '' ) ) );
		}
		update_post_meta( $post_id, 'mba_customer_photo', mba_core_sanitize_image_id( wp_unslash( $_POST['mba_customer_photo'] ?? 0 ) ) );
		update_post_meta( $post_id, 'mba_related_project', mba_core_sanitize_single_project_id( wp_unslash( $_POST['mba_related_project'] ?? 0 ) ) );
		update_post_meta( $post_id, 'mba_rating', mba_core_sanitize_rating( wp_unslash( $_POST['mba_rating'] ?? 0 ) ) );
		$consent = isset( $_POST['mba_publication_consent_confirmed'] );
		update_post_meta( $post_id, 'mba_publication_consent_confirmed', $consent );
		update_post_meta( $post_id, 'mba_featured', $consent && isset( $_POST['mba_featured'] ) );
	} else {
		update_post_meta( $post_id, 'mba_logo', mba_core_sanitize_image_id( wp_unslash( $_POST['mba_logo'] ?? 0 ) ) );
		update_post_meta( $post_id, 'mba_partner_url', mba_core_sanitize_partner_url( wp_unslash( $_POST['mba_partner_url'] ?? '' ) ) );
		update_post_meta( $post_id, 'mba_featured', isset( $_POST['mba_featured'] ) );
	}
}
add_action( 'save_post', 'mba_core_save_reusable_details', 10, 2 );

/**
 * Clean reverse FAQ relationships on permanent deletion.
 *
 * @param int $post_id Deleted post.
 */
function mba_core_delete_faq_backlinks( int $post_id ): void {
	if ( 'mba_faq' === get_post_type( $post_id ) ) {
		mba_core_sync_faq_products( $post_id, mba_core_sanitize_product_ids( get_post_meta( $post_id, 'mba_related_products', true ) ), array() );
	}
}
add_action( 'before_delete_post', 'mba_core_delete_faq_backlinks' );
