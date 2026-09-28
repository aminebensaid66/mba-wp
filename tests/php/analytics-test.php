<?php
/** Dependency-free analytics environment and measurement-ID tests for issue #22. */

define( 'ABSPATH', __DIR__ );
define( 'MBA_CORE_VERSION', '1.0.0' );
$GLOBALS['mba_analytics_options'] = array();
$GLOBALS['mba_analytics_environment'] = 'production';
function add_action( $hook, $callback ) {}
function add_filter( $hook, $callback ) {}
function __( $text, $domain = null ) {
	return $text; }
function get_option( $key, $default = false ) {
	return $GLOBALS['mba_analytics_options'][ $key ] ?? $default; }
function wp_get_environment_type() {
	return $GLOBALS['mba_analytics_environment']; }

require dirname( __DIR__, 2 ) . '/wp-content/plugins/mba-site-core/includes/settings.php';
require dirname( __DIR__, 2 ) . '/wp-content/plugins/mba-site-core/includes/analytics.php';

$fail = static function ( string $message ): void {
	fwrite( STDERR, $message . PHP_EOL );
	exit( 1 );
};
'G-ABC1234567' === mba_core_validate_setting( 'analytics_id', 'g-abc1234567' ) || $fail( 'Valid GA4 IDs should be normalized.' );
'' === mba_core_validate_setting( 'analytics_id', 'https://evil.example/tag' ) || $fail( 'Arbitrary analytics scripts must be rejected.' );
$GLOBALS['mba_analytics_options']['mba_site_settings'] = array(
	'mba_analytics_production_id' => 'G-PROD123456',
	'mba_analytics_staging_id' => 'G-STAGE1234',
);
'G-PROD123456' === mba_core_analytics_measurement_id() || $fail( 'Production must use its production measurement ID.' );
$GLOBALS['mba_analytics_environment'] = 'staging';
'G-STAGE1234' === mba_core_analytics_measurement_id() || $fail( 'Staging must use a distinct staging measurement ID.' );
$GLOBALS['mba_analytics_environment'] = 'development';
'' === mba_core_analytics_measurement_id() || $fail( 'Development traffic must never be sent to analytics.' );

echo "Analytics consent configuration assertions passed.\n";
