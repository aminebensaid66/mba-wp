<?php
/** Dependency-free asset-budget and delivery-contract assertions for issue #25. */

$root = dirname( __DIR__, 2 );
$theme = $root . '/wp-content/themes/mba-menuiseries';
$fail = static function ( string $message ): void {
	fwrite( STDERR, $message . PHP_EOL );
	exit( 1 );
};
$gzip_size = static function ( string $path ) use ( $fail ): int {
	$content = file_get_contents( $path );
	$compressed = gzencode( $content, 9 );
	false !== $compressed || $fail( 'PHP zlib is required to enforce compressed asset budgets.' );
	return strlen( $compressed );
};

$css_path = $theme . '/assets/css/site.css';
$css_gzip = $gzip_size( $css_path );
$css_gzip <= 7 * 1024 || $fail( sprintf( 'Theme CSS exceeds its 7 KiB gzip budget (%d bytes).', $css_gzip ) );

$assets = array(
	'theme navigation' => $theme . '/assets/js/navigation.js',
	'mobile conversion' => $theme . '/assets/js/mobile-conversion.js',
	'project gallery' => $theme . '/assets/js/project-gallery.js',
	'consent-gated map' => $theme . '/assets/js/contact-map.js',
	'homepage motion' => $theme . '/assets/js/home-motion.js',
	'optional analytics manager' => $root . '/wp-content/plugins/mba-site-core/assets/js/analytics.js',
);
$js_gzip = 0;
foreach ( $assets as $path ) {
	$js_gzip += $gzip_size( $path );
}
$js_gzip <= 7 * 1024 || $fail( sprintf( 'First-party site JavaScript exceeds its 7 KiB gzip budget (%d bytes).', $js_gzip ) );

$homepage = file_get_contents( $theme . '/includes/homepage.php' );
false !== strpos( $homepage, "'loading' => \$is_hero ? 'eager'" ) && false !== strpos( $homepage, "'fetchpriority' => \$is_hero ? 'high'" ) || $fail( 'The homepage hero must remain discoverable and high priority.' );

foreach ( array( 'product-archive.php', 'project-archive.php', 'blog.php' ) as $archive ) {
	$content = file_get_contents( $theme . '/includes/' . $archive );
	false !== strpos( $content, "'loading' => \$prioritize_image ? 'eager' : 'lazy'" ) && false !== strpos( $content, "'fetchpriority' => \$prioritize_image ? 'high' : 'auto'" ) || $fail( "The first card must be the archive LCP candidate and receive image priority: {$archive}" );
	false !== strpos( $content, '0 === $query->current_post' ) || $fail( "Only the first archive card should receive image priority: {$archive}" );
}

$functions = file_get_contents( $theme . '/functions.php' );
false !== strpos( $functions, 'function mba_theme_asset_version' ) && false !== strpos( $functions, 'mba_theme_asset_version( \'assets/js/mobile-conversion.js\' )' ) || $fail( 'Theme script URLs need content-derived cache versions.' );

printf( "Performance asset budgets passed (CSS %d B gzip; tracked first-party JS %d B gzip).\n", $css_gzip, $js_gzip );
