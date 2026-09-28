<?php
/** Dependency-free validation and delivery-state tests for issue #19. */

define( 'ABSPATH', __DIR__ );
function add_action( $hook, $callback ) {}

require dirname( __DIR__, 2 ) . '/wp-content/plugins/mba-site-core/includes/contact-leads.php';

$fail = static function ( string $message ): void {
	fwrite( STDERR, $message . PHP_EOL );
	exit( 1 );
};
$valid = array(
	'name' => 'Client',
	'email' => '',
	'email_valid' => false,
	'phone' => '+21612345678',
	'phone_valid' => true,
	'message' => 'Bonjour',
	'consent' => '1',
);
true === mba_core_contact_validate( $valid ) || $fail( 'A valid phone-only enquiry must pass.' );
$email_only = $valid;
$email_only['phone'] = '';
$email_only['phone_valid'] = false;
$email_only['email'] = 'client@example.test';
$email_only['email_valid'] = true;
true === mba_core_contact_validate( $email_only ) || $fail( 'A valid email-only enquiry must pass.' );
$missing_channel = $valid;
$missing_channel['phone'] = '';
$missing_channel['phone_valid'] = false;
false === mba_core_contact_validate( $missing_channel ) || $fail( 'An enquiry without a contact channel must fail.' );
$missing_consent = $valid;
$missing_consent['consent'] = '';
false === mba_core_contact_validate( $missing_consent ) || $fail( 'An enquiry without consent must fail.' );
true === mba_core_contact_is_spam( 'bot' ) && false === mba_core_contact_is_spam( '' ) || $fail( 'Honeypot classification mismatch.' );
'success' === mba_core_contact_status( true, true ) || $fail( 'Successful delivery status mismatch.' );
'email_error' === mba_core_contact_status( true, false ) || $fail( 'Failed notification status mismatch.' );
'ack_error' === mba_core_contact_status( true, true, false ) || $fail( 'Failed acknowledgement status mismatch.' );
'error' === mba_core_contact_status( false, false ) || $fail( 'Failed storage status mismatch.' );

echo "Contact form validation and status assertions passed.\n";
