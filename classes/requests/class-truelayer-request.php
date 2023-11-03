<?php
/**
 * Krokedil Paynopva for WooCommerce request base class.
 *
 * @package @package TrueLayer_For_WooCommerce/classes/requests/
 */

if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed indirectly.
	exit;
}

/**
 * Class TrueLayer_Request
 */
abstract class TrueLayer_Request {

	/**
	 * The request method.
	 *
	 * @var string
	 */
	public $method;

	/**
	 * The request method.
	 *
	 * @var string
	 */
	public $endpoint;

	/**
	 * The request idempotency_key.
	 *
	 * @var string
	 */
	public $idempotency_key;

	/**
	 * The request title.
	 *
	 * @var string
	 */
	protected $log_title;

	/**
	 * The TrueLayer session id.
	 *
	 * @var string
	 */
	protected $truelayer_session_id;

	/**
	 * The request arguments.
	 *
	 * @var array
	 */
	protected $arguments;

	/**
	 * Plugin settings
	 *
	 * @var array
	 */
	protected $settings;


	/**
	 * Class constructor.
	 *
	 * @param array $arguments Constructor arguments.
	 */
	public function __construct( $arguments = array() ) {
		$this->arguments = $arguments;

		// Load TrueLayer settings and sets their use here.
		$this->settings = get_option( 'woocommerce_truelayer_settings' );
	}

	/**
	 * Get the API URL base.
	 *
	 * @return string
	 */
	public function get_api_url_base() {
		return $this->is_test_mode() ? $this->test_api_url_base() : $this->api_url_base();
	}

	/**
	 * Get the API URL base.
	 *
	 * @return string
	 */
	public function get_auth_api_url_base() {
		return $this->is_test_mode() ? $this->auth_test_api_url_base() : $this->auth_api_url_base();
	}

	/**
	 * Check for test mode.
	 *
	 * @return string
	 */
	protected function is_test_mode() {
		return 'yes' === $this->settings['testmode'];
	}

	/**
	 * API URL base.
	 *
	 * @return string
	 */
	private function api_url_base() {
		return 'https://api.truelayer.com';
	}

	/**
	 * Test environment API URL base.
	 *
	 * @return string
	 */
	private function test_api_url_base() {
		return 'https://api.truelayer-sandbox.com';
	}

	/**
	 * API URL base.
	 *
	 * @return string
	 */
	private function auth_api_url_base() {
		return 'https://auth.truelayer.com';
	}

	/**
	 * Test environment API URL base.
	 *
	 * @return string
	 */
	private function auth_test_api_url_base() {
		return 'https://auth.truelayer-sandbox.com';
	}

	/**
	 * Get Live Environment Credentials.
	 *
	 * @return string
	 */
	protected function get_client_id() {
		return $this->is_test_mode() ? $this->settings['truelayer_sandbox_client_id'] : $this->settings['truelayer_client_id'];
	}

	/**
	 * Get Sandbox Environment Credentials.
	 *
	 * @return string
	 */
	protected function get_client_secret() {
		$key           = $this->is_test_mode() ? 'truelayer_sandbox_client_secret' : 'truelayer_client_secret';
		$client_secret = TruelayerEncryption()->decrypt_value( $key );

		return $client_secret;
	}

	/**
	 * Get the Private Key.
	 *
	 * @return string
	 */
	public function get_certificate() {
		$key         = $this->is_test_mode() ? 'truelayer_sandbox_client_certificate' : 'truelayer_client_certificate';
		$certificate = TruelayerEncryption()->decrypt_value( $key );

		return $certificate;
	}

	/**
	 * Get the Private Key.
	 *
	 * @return string
	 */
	public function get_private_key() {
		$key         = $this->is_test_mode() ? 'truelayer_sandbox_client_private_key' : 'truelayer_client_private_key';
		$private_key = TruelayerEncryption()->decrypt_value( $key );

		return $private_key;
	}

	/**
	 * Request headers.
	 *
	 * @param array $body The Request Body.
	 * @return array
	 */
	protected function get_request_headers( $body = array() ) {
		return array(
			'Content-Type' => 'application/json',
			'TL-Agent'     => 'truelayer-woocommerce/' . TRUELAYER_WC_PLUGIN_VERSION,
		);
	}

	/**
	 * Get the user agent.
	 *
	 * @return string
	 */
	protected function get_user_agent() {
		return 'WooCommerce: ' . WC()->version . ' - Plugin version: ' . TRUELAYER_WC_PLUGIN_VERSION . ' - PHP Version: ' . PHP_VERSION . ' - Krokedil';
	}

	/**
	 * Get the request args.
	 *
	 * @return array
	 */
	abstract protected function get_request_args();

	/**
	 * Get the request URL.
	 *
	 * @return string
	 */
	abstract protected function get_request_url();

	/**
	 * Make the request.
	 *
	 * @return bool|object
	 */
	public function request() {
		$url  = $this->get_request_url();
		$args = $this->get_request_args();

		if ( ! $args ) {
			return false;
		}

		$response = wp_remote_request( $url, $args );

		return $this->process_response( $response, $args, $url );
	}

	/**
	 * Processes the response checking for errors.
	 *
	 * @param object|WP_Error $response The response from the request.
	 * @param array           $request_args The request args.
	 * @param string          $request_url The request url.
	 * @return array|WP_Error
	 */
	protected function process_response( $response, $request_args, $request_url ) {
		$response_code = wp_remote_retrieve_response_code( $response );

		if ( $response_code < 200 || $response_code > 299 ) {
			$data          = 'URL: ' . $request_url . ' - ' . wp_json_encode( $request_args );
			$error_message = '';

			if ( null !== json_decode( $response['body'], true ) ) {
				$errors = json_decode( $response['body'], true );

				foreach ( $errors as $error ) {

					$error_message .= ' ' . $error;
				}
			}

			$code   = wp_remote_retrieve_response_code( $response );
			$return = new WP_Error( $code, json_decode( $response['body'], true ), $data );
		} else {
			$return = json_decode( wp_remote_retrieve_body( $response ), true );
		}

		$this->log_response( $response, $request_args, $request_url );

		return $return;
	}

	/**
	 * Logs the response from the request.
	 *
	 * @param array|WP_Error $response The response from the request.
	 * @param array           $request_args The request args.
	 * @param string          $request_url The request URL.
	 * @return void
	 */
	protected function log_response( $response, $request_args, $request_url ) {
		$body        = json_decode( wp_remote_retrieve_body( $response ), true );
		$id          = $body['id'] ?? null;
		$method      = $this->method;
		$code        = wp_remote_retrieve_response_code( $response );
		$title       = $this->log_title;
		$tl_trace_id = wp_remote_retrieve_header( $response, 'TL-Trace-Id' );

		$log = TrueLayer_Logger::format_log( $id, $method, $title, $request_args, $response, $code, $request_url, $tl_trace_id );
		TrueLayer_Logger::log( $log );
	}
}
