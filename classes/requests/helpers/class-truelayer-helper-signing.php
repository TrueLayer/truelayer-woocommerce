<?php
/**
 * Helper class for the request signing.
 *
 * @package TrueLayer/Classes/Requests/Helpers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TrueLayer\Signing\Signer;
use Ramsey\Uuid\Uuid;

/**
 * Heler class for the request signing.
 */
class Truelayer_Helper_Signing {
	/**
	 * Get the Tl Signature for the request.
	 *
	 * @param array             $body The request body.
	 * @param TrueLayer_Request $request The request class.
	 * @return string
	 */
	public static function get_tl_signature( $body, $request ) {
		$signer = Signer::signWithPem( $request->get_certificate(), $request->get_private_key() );
		$signer->method( $request->method )
			->path( $request->endpoint )
			->header( 'Idempotency-Key', $request->idempotency_key )
			->body( json_encode( $body ) );

		$signature = $signer->sign();

		return $signature;
	}

	/**
	 * Get a new UUID.
	 *
	 * @return \Ramsey\Uuid\UuidInterface
	 */
	public static function get_uuid() {

		return Uuid::uuid4();
	}
}
