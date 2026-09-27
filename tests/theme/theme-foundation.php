<?php
/**
 * Dependency-free assertions for issue #3 theme foundations.
 */

$root  = dirname( __DIR__, 2 );
$json  = json_decode( file_get_contents( $root . '/wp-content/themes/mba-menuiseries/theme.json' ), true, 512, JSON_THROW_ON_ERROR );
$css   = file_get_contents( $root . '/wp-content/themes/mba-menuiseries/assets/css/site.css' );
$fail  = static function ( string $message ): void {
	fwrite( STDERR, $message . PHP_EOL );
	exit( 1 );
};

$custom = $json['settings']['custom'] ?? array();
isset( $custom['focus']['color'], $custom['media']['ratio'], $custom['border']['subtle'] ) || $fail( 'Missing centralized focus/media/border tokens.' );

foreach ( array( '430px', '768px', '1024px', '1440px', 'prefers-reduced-motion', 'object-fit: cover', 'min-height: 44px' ) as $needle ) {
	false !== strpos( $css, $needle ) || $fail( "Missing CSS foundation: {$needle}" );
}

if ( false !== strpos( $css, 'min-width: 320px' ) || false !== strpos( $css, 'min-inline-size: 320px' ) ) {
	$fail( 'Fixed minimum viewport width would break zoom/reflow.' );
}

echo "Theme foundation assertions passed.\n";
