<?php
/**
 * Payment Tokenization request body class.
 *
 * @package TrueLayer_For_WooCommerce/Classes/Requests/Post
 */

/**
 * Class TrueLayer_Request_Get_Token
 */
class TrueLayer_Request_Get_Token extends TrueLayer_Request_Post {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->log_title = 'Get Token';
	}

	/**
	 * Create the request body.
	 *
	 * @return string
	 */
	protected function get_body() {
		$body = array(
			'grant_type'    => 'client_credentials',
			'client_id'     => $this->get_client_id(),
			'client_secret' => $this->get_client_secret(),
			'scope'         => 'payments',
		);

		return $body;
	}

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {
		$truelayer_token_url = $this->get_auth_api_url_base() . '/connect/token';
		return $truelayer_token_url;
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
