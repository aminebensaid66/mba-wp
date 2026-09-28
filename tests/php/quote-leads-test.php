<?php
/** Dependency-free quote validation/state tests for issue #18. */
define( 'ABSPATH', __DIR__ );
function add_action( $hook, $callback ) {}
require dirname( __DIR__, 2 ) . '/wp-content/plugins/mba-site-core/includes/quote-leads.php';

$fail = static function ( string $message ): void {
	fwrite( STDERR, $message . PHP_EOL );
	exit( 1 );
};
$valid = array(
	'name' => 'Customer Name',
	'phone' => '+21612345678',
	'phone_valid' => true,
	'email_raw' => '',
	'email_valid' => false,
	'email' => '',
	'location' => 'Tunis',
	'customer' => 'individual',
	'project_type' => 'renovation',
	'message' => 'Window replacement',
	'quantity' => '',
	'timeframe' => '',
	'preferred' => 'phone',
	'consent' => true,
);
true === mba_core_quote_validate_submission( $valid, true, false ) || $fail( 'Valid phone-only request should pass when email is optional.' );
false === mba_core_quote_validate_submission( $valid, true, true ) || $fail( 'Missing email should fail when configured as required.' );
$invalid = $valid;
$invalid['email_raw'] = 'not-an-email';
false === mba_core_quote_validate_submission( $invalid, true, false ) || $fail( 'Malformed optional email must still fail.' );
false === mba_core_quote_validate_submission( $valid, false, false ) || $fail( 'Request without a published product must fail.' );
$no_consent = $valid;
$no_consent['consent'] = false;
false === mba_core_quote_validate_submission( $no_consent, true, false ) || $fail( 'Request without privacy consent must fail.' );
true === mba_core_quote_is_spam( 'automated submission' ) || $fail( 'Honeypot content must be classified as spam.' );
false === mba_core_quote_is_spam( '' ) || $fail( 'An empty honeypot must not be classified as spam.' );
'success' === mba_core_quote_submission_status( true, true ) || $fail( 'Saved and notified request should report success.' );
'email_error' === mba_core_quote_submission_status( true, false ) || $fail( 'Saved request with failed notification must report email failure.' );
'ack_error' === mba_core_quote_submission_status( true, true, false ) || $fail( 'Failed acknowledgement must not report full success.' );
'error' === mba_core_quote_submission_status( false, false ) || $fail( 'Unstored request must never report success.' );

echo "Quote validation and outcome assertions passed.\n";
