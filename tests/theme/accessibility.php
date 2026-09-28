<?php
/** Dependency-free accessibility source and contrast assertions for issue #24. */

$root = dirname( __DIR__, 2 );
$json = json_decode( file_get_contents( $root . '/wp-content/themes/mba-menuiseries/theme.json' ), true, 512, JSON_THROW_ON_ERROR );
$css = file_get_contents( $root . '/wp-content/themes/mba-menuiseries/assets/css/site.css' );
$fail = static function ( string $message ): void {
	fwrite( STDERR, $message . PHP_EOL );
	exit( 1 );
};
$palette = array_column( $json['settings']['color']['palette'] ?? array(), 'color', 'slug' );
$palette['focus'] = $json['settings']['custom']['focus']['color'] ?? '';

$luminance = static function ( string $hex ): float {
	$hex = ltrim( $hex, '#' );
	$channels = array_map(
		static function ( int $offset ) use ( $hex ): float {
			$channel = hexdec( substr( $hex, $offset, 2 ) ) / 255;
			return $channel <= 0.04045 ? $channel / 12.92 : ( ( $channel + 0.055 ) / 1.055 ) ** 2.4;
		},
		array( 0, 2, 4 )
	);
	return ( 0.2126 * $channels[0] ) + ( 0.7152 * $channels[1] ) + ( 0.0722 * $channels[2] );
};
$contrast = static function ( string $first, string $second ) use ( $luminance ): float {
	$values = array( $luminance( $first ), $luminance( $second ) );
	rsort( $values );
	return ( $values[0] + 0.05 ) / ( $values[1] + 0.05 );
};

foreach ( array( array( 'ink', 'white' ), array( 'aluminium', 'white' ), array( 'accent', 'white' ), array( 'focus', 'white' ) ) as $pair ) {
	$ratio = $contrast( $palette[ $pair[0] ] ?? '', $palette[ $pair[1] ] ?? '' );
	$ratio >= 4.5 || $fail( sprintf( '%s on %s has insufficient normal-text contrast (%.2f:1).', $pair[0], $pair[1], $ratio ) );
}
$contrast( $palette['ink'], $palette['white'] ) >= 3 || $fail( 'The white focus outline must contrast with dark surfaces.' );
$contrast( $palette['focus'], $palette['white'] ) >= 3 || $fail( 'The colored focus ring must contrast with light surfaces.' );

foreach ( array( 'a:focus-visible', 'outline:', 'box-shadow: 0 0 0 6px var(--mba-focus)', 'prefers-reduced-motion', '.mba-skip-link:focus' ) as $needle ) {
	false !== strpos( $css, $needle ) || $fail( "Missing accessible interaction style: {$needle}" );
}

echo "Accessibility source and contrast assertions passed.\n";
