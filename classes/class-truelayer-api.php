<?php
/**
 * API Class File
 *
 * @package TrueLayer_For_WooCommerce/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The TrueLayer API class.
 */
class TrueLayer_API {


	/**
	 * Get a JWT auth token.
	 *
	 * @return string
	 */
	public function get_token() {
		$token = false === get_transient( 'truelayer_bearer_token' ) ? '' : get_transient( 'truelayer_bearer_token' );

		// Decrypt token.
		try {
			$token = TruelayerEncryption()->decrypt( $token );
		} catch ( Exception $e ) {
			TrueLayer_Logger::log( sprintf( 'TrueLayer bearer token not encrypted when fetched from transient. Error message: %s', $e->getMessage() ) );
		}

		if ( empty( $token ) ) {

			// Fetch a new token from TrueLayer.
			$request  = new TrueLayer_Request_Get_Token();
			$response = $request->request();

			if ( ! is_wp_error( $response ) && isset( $response['access_token'] ) ) {
				$token = $response['access_token'];
				// Encrypt token before saving it to db.
				try {
					$encrypted_token = TruelayerEncryption()->encrypt( $token );
					set_transient( 'truelayer_bearer_token', $encrypted_token, $response['expires_in'] );
				} catch ( Exception $e ) {
					TrueLayer_Logger::log( sprintf( 'TrueLayer bearer token could not be encrypted when saved to db. Error message: %s', $e->getMessage() ) );
				}
			}
		}
		return $token;
	}

	/**
	 * Create a TrueLayer payment.
	 *
	 * @param int $order_id The WooCommerce Order ID.
	 * @return mixed
	 */
	public function create_payment( $order_id ) {
		$request  = new TrueLayer_Request_Create_Payment( array( 'order_id' => $order_id ) );
		$response = $request->request();

		return $this->check_for_api_error( $response );
	}

	/**
	 * Refund Payment via TrueLayer.
	 *
	 * @param int    $order_id the WooCOmmerce Order ID.
	 * @param int    $amount The amount to be refunded.
	 * @param string $reason the refund reason.
	 * @return mixed
	 */
	public function refund_payment( $order_id, $amount, $reason ) {
		$request  = new TrueLayer_Request_Refunds(
			array(
				'order_id' => $order_id,
				'amount'   => $amount,
				'reason'   => $reason,
			)
		);
		$response = $request->request();

		return $this->check_for_api_error( $response );
	}

	/**
	 * Get the TrueLayer payment status.
	 *
	 * @param string $transaction_id The TrueLayer payment ID.
	 * @return mixed
	 */
	public function get_payment_status( $transaction_id ) {
		$request  = new TrueLayer_Request_Get_Payment_Status( array( 'transaction_id' => $transaction_id ) );
		$response = $request->request();

		return $this->check_for_api_error( $response );
	}

	/**
	 * Get the TrueLayer payment status.
	 *
	 * @param string $transaction_id The TrueLayer payment ID.
	 * @return mixed
	 */
	public function get_merchant_accounts( $transaction_id ) {
		$request  = new TrueLayer_Get_Merchant_Account( array( 'transaction_id' => $transaction_id ) );
		$response = $request->request();

		foreach ( $response['items'] as $truelayer_item ) {

			if ( 'GBP' === $truelayer_item['currency'] ) {
				update_post_meta( $transaction_id, '_truelayer_merchant_account_id', $truelayer_item['id'] );
			}
		}

		return $this->check_for_api_error( $response );
	}

	/**
	 * Checks for WP Errors and returns either the response as array or a false.
	 *
	 * @param array $response The response from the request.
	 * @return mixed
	 */
	private function check_for_api_error( $response ) {
		if ( is_wp_error( $response ) ) {
			if ( ! is_admin() ) {
				truelayer_print_error_message( $response );
			}
		}
		return $response;
	}

}
