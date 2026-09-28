<?php
/** Simulate an owner edit between two seeder runs to verify edit preservation. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( 1 );
}
$sample = get_posts(
	array(
		'post_type' => 'mba_product',
		'post_status' => 'draft',
		'posts_per_page' => 1,
		'fields' => 'ids',
		'meta_key' => '_mba_sample_key',
		'meta_value' => 'product-window-example',
	)
);
if ( ! $sample ) {
	WP_CLI::error( 'Sample product was not created before its preservation test.' );
}
update_post_meta( (int) $sample[0], 'mba_short_description', 'MBA E2E owner edit must survive a seed rerun.' );
WP_CLI::success( 'Saved an editor change to the sample product.' );
