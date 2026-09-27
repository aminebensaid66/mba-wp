<?php
/**
 * Title: Media card grid placeholder
 * Slug: mba-menuiseries/media-card-grid
 * Categories: gallery
 * Description: Responsive neutral cards for layout testing before approved photography is supplied.
 */
?>
<!-- wp:group {"className":"mba-section mba-section--muted","layout":{"type":"constrained"}} -->
<div class="wp-block-group mba-section mba-section--muted">
	<!-- wp:group {"className":"mba-grid mba-grid--wide","layout":{"type":"default"}} -->
	<div class="wp-block-group mba-grid mba-grid--wide">
		<!-- wp:group {"className":"mba-card","layout":{"type":"default"}} -->
		<div class="wp-block-group mba-card">
			<!-- wp:group {"className":"mba-placeholder","layout":{"type":"constrained"}} -->
			<div class="wp-block-group mba-placeholder"><!-- wp:paragraph --><p><?php esc_html_e( 'Owner-managed image', 'mba-menuiseries' ); ?></p><!-- /wp:paragraph --></div>
			<!-- /wp:group -->
			<!-- wp:group {"className":"mba-card__body","layout":{"type":"constrained"}} -->
			<div class="wp-block-group mba-card__body"><!-- wp:heading {"level":3} --><h3 class="wp-block-heading"><?php esc_html_e( 'Editable card title', 'mba-menuiseries' ); ?></h3><!-- /wp:heading --></div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
		<!-- wp:group {"className":"mba-card","layout":{"type":"default"}} -->
		<div class="wp-block-group mba-card">
			<!-- wp:group {"className":"mba-placeholder","layout":{"type":"constrained"}} -->
			<div class="wp-block-group mba-placeholder"><!-- wp:paragraph --><p><?php esc_html_e( 'Portrait or landscape image', 'mba-menuiseries' ); ?></p><!-- /wp:paragraph --></div>
			<!-- /wp:group -->
			<!-- wp:group {"className":"mba-card__body","layout":{"type":"constrained"}} -->
			<div class="wp-block-group mba-card__body"><!-- wp:heading {"level":3} --><h3 class="wp-block-heading"><?php esc_html_e( 'Editable card title', 'mba-menuiseries' ); ?></h3><!-- /wp:heading --></div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
