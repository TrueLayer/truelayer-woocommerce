<?php
/**
 * TrueLayer Request GET base Class
 *
 * @package TrueLayer_For_WooCommerce/Classes/Requests
 */

defined( 'ABSPATH' ) || exit;

/**
 * Parent class of all GET requests for the TrueLayer API.
 *
 * @class    TrueLayer_Request_Get
 * @version  1.0
 * @package  TrueLayer_For_WooCommerce/Classes/Request
 * @category Class
 * @author   Krokedil
 */
abstract class Truelayer_Request_Get extends TrueLayer_Request {

	/**
	 * Class constructor.
	 *
	 * @param array $arguments The request arguments.
	 */
	public function __construct( $arguments ) {
		parent::__construct( $arguments );
		$this->method = 'GET';
	}

	/**
	 * Builds the request args for a GET request.
	 *
	 * @return array
	 */
	public function get_request_args() {
		$array = array(
			'headers' => $this->get_request_headers(),
			'method'  => $this->method,
		);

		return $array;
	}
}
