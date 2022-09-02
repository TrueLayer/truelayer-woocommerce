<?php
/**
 * Helper class for the TrueLayer callback request verifying.
 *
 * @package TrueLayer/Classes/Requests/Helpers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TrueLayer\Signing\Exceptions\InvalidSignatureException;
use TrueLayer\Signing\Verifier;

/**
 * Heler class for the callback verifying.
 */
class Truelayer_Helper_Verifying {
	/**
	 * Get the Tl Signature for the requst.
	 *
	 * @param array $body The request body.
	 * @param array $headers The callback headers.
	 * @param bool  $path_remove_trailing_slash if path should be run through untrailingslashit() or not.
	 * @throws InvalidSignatureException $e The error message if verification fails.
	 * @return string
	 */
	public static function get_tl_verification( $body, $headers, $path_remove_trailing_slash = true ) {
		$tl_signature        = $headers['tl-signature'];
		$tl_signature_array  = explode( '..', $headers['tl-signature'] );
		$tl_signature_header = json_decode( base64_decode( $tl_signature_array[0] ), true );
		$path                = true === $path_remove_trailing_slash ? wp_parse_url( untrailingslashit( home_url( '/wc-api/TrueLayer_Callback' ) ), PHP_URL_PATH ) : wp_parse_url( trailingslashit( home_url( '/wc-api/TrueLayer_Callback' ) ), PHP_URL_PATH );

		// Control that the jku url is one of the approved.
		if ( ! in_array( $tl_signature_header['jku'], self::get_valid_jku_urls(), true ) ) {
			throw new Exception( 'Provided jku URL is not correct' );
		}

		$response = wp_remote_get( esc_url_raw( $tl_signature_header['jku'] ) );
		$keys     = json_decode( wp_remote_retrieve_body( $response ), true );

		$verifier = Verifier::verifyWithJsonKeys( ...$keys['keys'] );
		$verifier->method( 'POST' )
			->path( $path )
			->headers(
				$headers
			)
			->body( $body );

		try {

			$verification = $verifier->verify( $tl_signature );
		} catch ( Exception $e ) {
			throw $e;
		}
		return $verification;
	}

	/**
	 * Return approved URL's from where the JKU can be fetched from.
	 *
	 * @return array
	 */
	private static function get_valid_jku_urls() {
		return array(
			'https://webhooks.truelayer-sandbox.com/.well-known/jwks',
			'https://webhooks.truelayer.com/.well-known/jwks',
		);
	}
}
