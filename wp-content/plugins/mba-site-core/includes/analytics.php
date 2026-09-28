<?php
/** Consent-gated, privacy-safe analytics integration. @package MBA_Site_Core */
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/** Select a dedicated measurement ID only for production or explicitly configured staging. */
function mba_core_analytics_measurement_id(): string {
	$environment = wp_get_environment_type();
	if ( 'production' === $environment ) {
		return (string) mba_core_setting( 'mba_analytics_production_id' ); }
	if ( 'staging' === $environment ) {
		return (string) mba_core_setting( 'mba_analytics_staging_id' ); }
	return '';
}

/** Load the analytics manager without loading the third-party tag before consent. */
function mba_core_analytics_enqueue(): void {
	$measurement_id = mba_core_analytics_measurement_id();
	if ( ! $measurement_id ) {
		return; }
	wp_enqueue_script( 'mba-consent-analytics', plugins_url( 'assets/js/analytics.js', dirname( __DIR__ ) . '/mba-site-core.php' ), array(), MBA_CORE_VERSION, true );
	$content_type = is_singular( 'mba_product' ) ? 'product' : ( is_singular( 'mba_project' ) ? 'project' : '' );
	wp_localize_script(
		'mba-consent-analytics',
		'MBAAnalyticsConfig',
		array(
			'measurementId' => $measurement_id,
			'environment' => wp_get_environment_type(),
			'contentType' => $content_type,
			'labels' => array(
				'title' => __( 'Privacy preferences', 'mba-site-core' ),
				'description' => __( 'MBA uses optional analytics only with your permission. Necessary site functions are always active.', 'mba-site-core' ),
				'accept' => __( 'Accept analytics', 'mba-site-core' ),
				'reject' => __( 'Reject analytics', 'mba-site-core' ),
				'customize' => __( 'Manage preferences', 'mba-site-core' ),
				'analytics' => __( 'Allow anonymous usage analytics', 'mba-site-core' ),
				'save' => __( 'Save preferences', 'mba-site-core' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'mba_core_analytics_enqueue' );
