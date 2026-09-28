<?php
/**
 * Plugin Name: MBA Site Core
 * Description: Durable content types and business settings for MBA Menuiseries Belhaj Ali.
 * Version: 0.6.0
 * Author: MBA Menuiseries
 * Text Domain: mba-site-core
 * Domain Path: /languages
 * Requires at least: 6.6
 * Requires PHP: 8.1
 *
 * @package MBA_Site_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MBA_CORE_VERSION', '0.6.0' );
define( 'MBA_CORE_PATH', plugin_dir_path( __FILE__ ) );

require_once MBA_CORE_PATH . 'includes/content-types.php';
require_once MBA_CORE_PATH . 'includes/product-meta.php';
require_once MBA_CORE_PATH . 'includes/product-admin.php';
require_once MBA_CORE_PATH . 'includes/project-fields.php';
require_once MBA_CORE_PATH . 'includes/reusable-content.php';
require_once MBA_CORE_PATH . 'includes/settings.php';
require_once MBA_CORE_PATH . 'includes/quote-leads.php';
require_once MBA_CORE_PATH . 'includes/privacy.php';
require_once MBA_CORE_PATH . 'includes/contact-leads.php';
require_once MBA_CORE_PATH . 'includes/seo.php';
require_once MBA_CORE_PATH . 'includes/analytics.php';
require_once MBA_CORE_PATH . 'includes/media.php';

/**
 * Register rewrite structures once before activation flushes them.
 */
function mba_core_activate(): void {
	mba_core_register_content_types();
	mba_core_install_owner_caps();
	mba_core_install_lead_caps();
	flush_rewrite_rules();
}

/**
 * Remove only rewrite rules on deactivation; stored content is never deleted.
 */
function mba_core_deactivate(): void {
	flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'mba_core_activate' );
register_deactivation_hook( __FILE__, 'mba_core_deactivate' );
