<?php
/**
 * Class for the request to fetch the TrueLayer merchant accounts.
 *
 * @package TrueLayer_For_WooCommerce/Classes/Requests/Get
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class for the request to add a item to the TrueLayer merchant accounts.
 */
class TrueLayer_Get_Merchant_Accounts extends TrueLayer_Request_Get {

	/**
	 * Class constructor.
	 *
	 * @param array $arguments The request arguments.
	 */
	public function __construct( $arguments ) {
		parent::__construct( $arguments );
		$this->log_title = 'Get TrueLayer merchant account';
		$this->arguments = $arguments;
	}

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {

		return $this->get_api_url_base() . '/merchant-accounts';
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
			'Authorization' => "Bearer $token",
		);

		return $request_body;
	}
}
