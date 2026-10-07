<?php
/**
 * Theme bootstrap.
 *
 * @package MBA_Menuiseries
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/includes/navigation.php';
require_once __DIR__ . '/includes/homepage.php';
require_once __DIR__ . '/includes/company-page.php';
require_once __DIR__ . '/includes/product-archive.php';
require_once __DIR__ . '/includes/product-detail.php';
require_once __DIR__ . '/includes/project-archive.php';
require_once __DIR__ . '/includes/project-detail.php';
require_once __DIR__ . '/includes/blog.php';
require_once __DIR__ . '/includes/faq.php';
require_once __DIR__ . '/includes/contact-page.php';

/** Return a content-derived version so browsers refetch changed theme assets. */
function mba_theme_asset_version( string $relative_path ): string {
	$path = get_theme_file_path( $relative_path );
	return file_exists( $path ) ? (string) filemtime( $path ) : '0.1.0';
}

/**
 * Theme features.
 */
function mba_theme_setup(): void {
	add_theme_support( 'title-tag' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/site.css' );
}
add_action( 'after_setup_theme', 'mba_theme_setup' );

/**
 * Load the small shared stylesheet.
 */
function mba_theme_enqueue_assets(): void {
	$path = get_theme_file_path( 'assets/css/site.css' );
	wp_enqueue_style(
		'mba-theme',
		get_theme_file_uri( 'assets/css/site.css' ),
		array(),
		file_exists( $path ) ? (string) filemtime( $path ) : '0.1.0'
	);
	if ( is_front_page() ) {
		wp_enqueue_script( 'mba-home-motion', get_theme_file_uri( 'assets/js/home-motion.js' ), array(), mba_theme_asset_version( 'assets/js/home-motion.js' ), true );
	}
	if ( is_singular( 'mba_project' ) ) {
		wp_enqueue_script( 'mba-project-gallery', get_theme_file_uri( 'assets/js/project-gallery.js' ), array(), mba_theme_asset_version( 'assets/js/project-gallery.js' ), true );
	}
	if ( ( function_exists( 'mba_core_phone_url' ) && mba_core_phone_url() ) || ( function_exists( 'mba_core_whatsapp_url' ) && mba_core_whatsapp_url() ) ) {
		wp_enqueue_script( 'mba-mobile-conversion', get_theme_file_uri( 'assets/js/mobile-conversion.js' ), array(), mba_theme_asset_version( 'assets/js/mobile-conversion.js' ), true );
	}
}
add_action( 'wp_enqueue_scripts', 'mba_theme_enqueue_assets' );
