<?php
/**
 * Theme bootstrap.
 *
 * @package MBA_Menuiseries
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme features.
 */
function mba_theme_setup(): void {
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
}
add_action( 'wp_enqueue_scripts', 'mba_theme_enqueue_assets' );
