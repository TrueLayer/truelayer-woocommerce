<?php
/**
 * Refund request body class.
 *
 * @package TrueLayer_For_WooCommerce/Classes/Requests/Order_Management
 */

/**
 * Class TrueLayer_Request_Refunds
 */
class TrueLayer_Request_Refunds extends TrueLayer_Request_Post {

	/**
	 * WooCommerce Order ID
	 *
	 * @var int
	 */
	public $order_id;

	/**
	 * Class constructor.
	 *
	 * @param array $arguments the class constructor arguments array.
	 */
	public function __construct( $arguments ) {
		parent::__construct( $arguments );
		$this->order_id        = $arguments['order_id'];
		$this->order           = wc_get_order( $this->order_id );
		$this->log_title       = 'Refund payment';
		$this->endpoint        = '/payments/' . $this->order->get_transaction_id() . '/refunds';
		$this->idempotency_key = Truelayer_Helper_Signing::get_uuid();

		$this->amount = round( $arguments['amount'] * 100 );
		$this->reason = $arguments['reason'];
	}

	/**
	 * Create the request body.
	 *
	 * @return string
	 */
	protected function get_body() {

		$body = array(
			'amount_in_minor' => $this->amount,
			'reference'       => $this->reason,
		);

		return $body;
	}

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {
		$truelayer_token_url = $this->get_api_url_base() . $this->endpoint;

		return $truelayer_token_url;
	}

	/**
	 * Request headers.
	 *
	 * @param array $body The request body.
	 * @return array
	 */
	protected function get_request_headers( $body = array() ) {
		$token = TrueLayer()->api->get_token();

		return array(
			'Content-Type'    => 'application/json',
			'Idempotency-Key' => $this->idempotency_key,
			'Tl-Signature'    => Truelayer_Helper_Signing::get_tl_signature( $body, $this ),
			'TL-Agent'        => 'truelayer-woocommerce/' . TRUELAYER_WC_PLUGIN_VERSION,
			'Authorization'   => "Bearer $token",
		);
	}

	/**
	 * Get request arguments and check request body.
	 *
	 * @return bool|array
	 */
	protected function get_request_args() {
		$body = $this->get_body();

		return array(
			'headers'    => $this->get_request_headers( $body ),
			'user-agent' => $this->get_user_agent(),
			'method'     => $this->method,
			'timeout'    => apply_filters( 'truelayer_request_timeout', 10 ),
			'body'       => apply_filters( 'truelayer_request_args', wp_json_encode( $body ) ),
		);
	}
}
