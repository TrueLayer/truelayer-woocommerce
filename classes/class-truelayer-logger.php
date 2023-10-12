<?php
/**
 * Logging class file.
 *
 * @package TrueLayer_For_WooCommerce/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TrueLayer_Logger class.
 */
class TrueLayer_Logger {
	/**
	 * Log message string
	 *
	 * @var $log
	 */
	private static $log;

	/**
	 * Logs an event.
	 *
	 * @param string $data The data string.
	 */
	public static function log( $data ) {
		$settings = get_option( 'woocommerce_truelayer_settings' );

		if ( 'yes' === $settings['logging'] ) {
			$message = self::format_data( $data );
			if ( empty( self::$log ) ) {
				self::$log = new WC_Logger();
			}
			self::$log->add( 'truelayer-checkout-for-woocommerce', wp_json_encode( $message ) );
		}

		if ( isset( $data['response']['code']['response']['code'] ) && ( $data['response']['code']['response']['code'] < 200 || $data['response']['code']['response']['code'] > 299 ) ) {
			self::log_to_db( $data );
		}
	}

	/**
	 * Formats the log data to prevent json error.
	 *
	 * @param string $data Json string of data.
	 * @return array
	 */
	public static function format_data( $data ) {
		if ( isset( $data['request']['body'] ) ) {
			$data['request']['body'] = json_decode( $data['request']['body'], true );
		}
		return $data;
	}

	/**
	 * Formats the log data to be logged.
	 *
	 * @param string $truelayer_order_id The TrueLayer order id.
	 * @param string $method The method.
	 * @param string $title The title for the log.
	 * @param array  $request_args The request args.
	 * @param array  $response The response.
	 * @param string $code The status code.
	 * @param string $request_url The request url.
	 * @param string $tl_trace_id The returned TrueLayer trace ID.
	 * @return array
	 */
	public static function format_log( $truelayer_order_id, $method, $title, $request_args, $response, $code, $request_url = null, $tl_trace_id = '' ) {
		// Clean the request args body from the client_secret if its set.
		if ( isset( $request_args['body'] ) ) {
			$request_body = json_decode($request_args['body'], true);

			if ( isset( $request_body['client_secret'] ) ) {
				unset( $request_body['client_secret'] );
				$request_args['body'] = wp_json_encode($request_body);
			}
		}

		// Remove sensitive data from the logs if the filter is set to true.
		if ( apply_filters( 'truelayer_remove_sensitive_data_from_logs', true ) ) {
			// Remove the access token from the logs if it was set, and the response is not a WP_Error.
			if ( ! is_wp_error( $response ) && isset( $response['body'] ) ) {
				$response_body                 = json_decode( $response['body'], true );
				$response_body['access_token'] = 'Removed from log';
				$response['body']              = $response_body; // Dont json encode to prevent double encoding in the log.
			}

			// Remove the auth header from the request args if its set.
			if ( isset( $request_args['headers']['Authorization'] ) ) {
				$request_args['headers']['Authorization'] = 'Removed from log';
			}
		}

		return array(
			'id'             => $truelayer_order_id,
			'type'           => $method,
			'title'          => $title,
			'request'        => $request_args,
			'request_url'    => $request_url,
			'response'       => array(
				'body'        => $response,
				'code'        => $code,
				'tl_trace_id' => $tl_trace_id,
			),
			'timestamp'      => date( 'Y-m-d H:i:s' ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions -- Date is not used for display.
			'stack'          => self::get_stack(),
			'plugin_version' => TRUELAYER_WC_PLUGIN_VERSION,
		);
	}

	/**
	 * Gets the stack for the request.
	 *
	 * @return array
	 */
	public static function get_stack() {
		$debug_data = debug_backtrace(); // phpcs:ignore WordPress.PHP.DevelopmentFunctions -- Data is not used for display.
		$stack      = array();
		foreach ( $debug_data as $data ) {
			$extra_data = '';
			if ( ! in_array( $data['function'], array( 'get_stack', 'format_log' ), true ) ) {
				if ( in_array( $data['function'], array( 'do_action', 'apply_filters' ), true ) ) {
					if ( isset( $data['object'] ) ) {
						$priority   = $data['object']->current_priority();
						$name       = key( $data['object']->current() );
						$extra_data = $name . ' : ' . $priority;
					}
				}
			}
			$stack[] = $data['function'] . $extra_data;
		}
		return $stack;
	}

	/**
	 * Logs an event in the WP DB.
	 *
	 * @param array $data The data to be logged.
	 */
	public static function log_to_db( $data ) {
		$logs = get_option( 'krokedil_debuglog_truelayer', array() );

		if ( ! empty( $logs ) ) {
			$logs = json_decode( $logs );
		}

		$logs   = array_slice( $logs, -14 );
		$logs[] = $data;
		$logs   = wp_json_encode( $logs );
		update_option( 'krokedil_debuglog_truelayer', $logs, false );
	}

}
