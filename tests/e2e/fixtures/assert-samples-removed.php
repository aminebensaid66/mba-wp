<?php
/** Confirm the sample cleanup command removes only records marked by the seeder. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( 1 );
}

foreach ( array( 'attachment', 'mba_faq', 'mba_project', 'mba_product' ) as $post_type ) {
	$matches = get_posts(
		array(
			'post_type' => $post_type,
			'post_status' => 'attachment' === $post_type ? 'inherit' : array( 'draft', 'pending', 'private', 'publish', 'future' ),
			'posts_per_page' => 1,
			'fields' => 'ids',
			'meta_key' => '_mba_sample_content',
			'meta_value' => '1',
		)
	);
	if ( $matches ) {
		WP_CLI::error( 'The sample cleanup left marked records in ' . $post_type . '.' );
	}
}
WP_CLI::success( 'No seeder-marked posts or attachments remain after cleanup.' );
