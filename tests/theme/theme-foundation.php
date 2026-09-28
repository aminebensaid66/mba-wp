<?php
/**
 * Dependency-free assertions for issue #3 theme foundations.
 */

$root  = dirname( __DIR__, 2 );
$json  = json_decode( file_get_contents( $root . '/wp-content/themes/mba-menuiseries/theme.json' ), true, 512, JSON_THROW_ON_ERROR );
$css   = file_get_contents( $root . '/wp-content/themes/mba-menuiseries/assets/css/site.css' );
$navigation = file_get_contents( $root . '/wp-content/themes/mba-menuiseries/includes/navigation.php' );
$faq        = file_get_contents( $root . '/wp-content/themes/mba-menuiseries/includes/faq.php' );
$fail  = static function ( string $message ): void {
	fwrite( STDERR, $message . PHP_EOL );
	exit( 1 );
};

$custom = $json['settings']['custom'] ?? array();
isset( $custom['focus']['color'], $custom['media']['ratio'], $custom['border']['subtle'] ) || $fail( 'Missing centralized focus/media/border tokens.' );

foreach ( array( '430px', '768px', '1024px', '1440px', 'prefers-reduced-motion', 'object-fit: cover', 'min-height: 44px' ) as $needle ) {
	false !== strpos( $css, $needle ) || $fail( "Missing CSS foundation: {$needle}" );
}

false === strpos( $navigation, 'mba_theme_render_skip_link' ) || $fail( 'Do not duplicate WordPress core’s built-in skip link.' );
false !== strpos( $css, 'a:focus-visible' ) || $fail( 'Keyboard controls need visible focus styling.' );
false !== strpos( $faq, '<details class="mba-faq-item"><summary>' ) || $fail( 'FAQ disclosure controls must expose native keyboard and screen-reader state.' );
false !== strpos( $faq, 'aria-expanded="false"' ) && $fail( 'FAQ disclosure state must not contradict a visible answer.' );

$main_files = array_merge(
	glob( $root . '/wp-content/themes/mba-menuiseries/includes/*.php' ),
	glob( $root . '/wp-content/themes/mba-menuiseries/templates/*.html' )
);
foreach ( $main_files as $path ) {
	$content = file_get_contents( $path );
	if ( preg_match_all( '/<main\\b([^>]*)>/', $content, $matches ) ) {
		foreach ( $matches[1] as $attributes ) {
			false !== strpos( $attributes, 'id="main"' ) && false !== strpos( $attributes, 'tabindex="-1"' ) || $fail( 'Every main landmark must be a focusable skip-link target: ' . basename( $path ) );
		}
	}
}

if ( false !== strpos( $css, 'min-width: 320px' ) || false !== strpos( $css, 'min-inline-size: 320px' ) ) {
	$fail( 'Fixed minimum viewport width would break zoom/reflow.' );
}

echo "Theme foundation assertions passed.\n";
