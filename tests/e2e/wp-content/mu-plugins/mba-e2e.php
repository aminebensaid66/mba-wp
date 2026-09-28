<?php
/** Test-only form isolation and mail transport for the disposable E2E Compose project. */
if ( ! defined( 'WP_ENVIRONMENT_TYPE' ) || 'local' !== WP_ENVIRONMENT_TYPE || '1' !== ( $_SERVER['HTTP_X_MBA_E2E_TEST'] ?? '' ) ) {
	return;
}

add_action(
	'init',
	static function (): void {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		foreach ( array( 'mba_quote_rate_', 'mba_contact_rate_' ) as $prefix ) {
			delete_transient( $prefix . hash_hmac( 'sha256', $ip, wp_salt( 'auth' ) ) );
		}
	},
	0
);

add_filter(
	'pre_wp_mail',
	static function ( $preempt, array $attributes ) {
		unset( $attributes );
		return 'fail' === ( $_SERVER['HTTP_X_MBA_E2E_MAIL'] ?? '' ) ? false : true;
	},
	10,
	2
);
