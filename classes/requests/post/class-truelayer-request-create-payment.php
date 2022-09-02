<?php
/**
 * Create Payment request body class.
 *
 * @package TrueLayer_For_WooCommerce/Classes/Requests/Post
 */

/**
 * Class TrueLayer_Request_Create_Payment
 */
class TrueLayer_Request_Create_Payment extends TrueLayer_Request_Post {

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
		$this->log_title       = 'Create payment';
		$this->endpoint        = '/payments';
		$this->idempotency_key = Truelayer_Helper_Signing::get_uuid();
		$this->order_id        = $arguments['order_id'];
	}

	/**
	 * Create the request body.
	 *
	 * @return string
	 */
	protected function get_body() {

		$order               = wc_get_order( $this->order_id );
		$settings            = $this->settings;
		$merchant_account_id = truelayer_get_merchant_account_id( $order->get_currency() );

		$body = array(
			'amount_in_minor' => TrueLayer_Helper_Order::get_order_amount( $order ),
			'currency'        => get_woocommerce_currency(),
			'payment_method'  => array(
				'type'               => 'bank_transfer',

				'beneficiary'        => array(
					'type'                => 'merchant_account',
					'account_holder_name' => $settings['truelayer_beneficiary_account_holder_name'],
					'merchant_account_id' => $merchant_account_id,
				),

				'provider_selection' => array(
					'type'   => 'user_selected',
					'filter' => array(
						'countries' => array( $order->get_billing_country() ),
					),
				),
			),
			'user'            => array(
				'name'  => TrueLayer_Helper_Order::get_account_holder_name( $order ),
				'email' => $order->get_billing_email(),
			),
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
