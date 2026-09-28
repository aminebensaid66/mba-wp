<?php
/** Remove only content created by tools/sample-content/seed.php. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( 1 );
}

$removed = 0;
foreach ( array( 'attachment', 'mba_faq', 'mba_project', 'mba_product' ) as $post_type ) {
	$ids = get_posts(
		array(
			'post_type' => $post_type,
			'post_status' => 'attachment' === $post_type ? 'inherit' : array( 'draft', 'pending', 'private', 'publish', 'future' ),
			'posts_per_page' => -1,
			'fields' => 'ids',
			'meta_key' => '_mba_sample_content',
			'meta_value' => '1',
		)
	);
	foreach ( $ids as $post_id ) {
		if ( wp_delete_post( (int) $post_id, true ) ) {
			++$removed;
		}
	}
}
WP_CLI::success( sprintf( 'Permanently removed %d locally seeded sample records and illustrations.', $removed ) );
