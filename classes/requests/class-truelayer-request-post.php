<?php
/**
 * TrueLayer Request POST base Class
 *
 * @package TrueLayer_For_WooCommerce/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parent class of all POST requests for the TrueLayer API.
 *
 * @class    TrueLayer_Request_Post
 * @version  1.0
 * @package  TrueLayer_For_WooCommerce/Classes/Request
 * @category Class
 * @author   Krokedil
 */
abstract class TrueLayer_Request_Post extends TrueLayer_Request {

	/**
	 * Class constructor
	 *
	 * @param array $arguments Constructor arguments.
	 */
	public function __construct( $arguments = array() ) {
		parent::__construct( $arguments );
		$this->method = 'POST';
	}

	/**
	 * Get body abstract function.
	 *
	 * @return void
	 */
	abstract protected function get_body();
}
