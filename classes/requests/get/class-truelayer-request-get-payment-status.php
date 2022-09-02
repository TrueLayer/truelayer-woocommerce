<?php
/**
 * Class for the request to fetch the TrueLayer payment status.
 *
 * @package TrueLayer_For_WooCommerce/Classes/Requests/Get
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class for the request to add a item to the TrueLayer payment statusr.
 */
class TrueLayer_Request_Get_Payment_Status extends TrueLayer_Request_Get {

	/**
	 * Class constructor.
	 *
	 * @param array $arguments The request arguments.
	 */
	public function __construct( $arguments ) {
		parent::__construct( $arguments );
		$this->log_title       = 'Get TrueLayer payment status';
		$this->endpoint        = $this->get_request_url();
		$this->idempotency_key = Truelayer_Helper_Signing::get_uuid();
		$this->arguments       = $arguments;
	}

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {
		$truelayer_payment_id = $this->arguments['transaction_id'];

		return $this->get_api_url_base() . "/payments/{$truelayer_payment_id}";
	}


	/**
	 * Request headers.
	 *
	 * @param array $body The request body.
	 * @return array
	 */
	protected function get_request_headers( $body = array() ) {
		$token = TrueLayer()->api->get_token();

		$request_body = array(
			'X-TL-Webhook-Timestamp' => '',
			'Tl-Signature'           => Truelayer_Helper_Signing::get_tl_signature( $body, $this ),
			'TL-Agent'               => 'truelayer-woocommerce/' . TRUELAYER_WC_PLUGIN_VERSION,
			'Authorization'          => "Bearer $token",
		);

		return $request_body;
	}
}
