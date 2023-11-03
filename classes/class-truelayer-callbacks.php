<?php //phpcs:ignore
/**
 * Class for handling redirection during payment.
 *
 * @package TrueLayer/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for handling redirection during payment.
 */
class TrueLayer_Callbacks {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_api_truelayer_callback', array( $this, 'notification_cb' ) );
	}


	/**
	 * Obtain the TrueLayer payment status.
	 *
	 * @return void
	 */
	public function notification_cb() {
		$unsafe_body = file_get_contents( 'php://input' );
		$body        = json_decode( $unsafe_body, true );

		// Sanitize body.
		array_walk_recursive(
			$body,
			function ( &$v ) {
				$v = filter_var( trim( $v ), FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			}
		);

		$headers = truelayer_get_all_headers( $_SERVER );

		$log_id = isset( $body['payment_id'] ) ? $body['payment_id'] : $body['beneficiary']['payment_source_id'];
		TrueLayer_Logger::log( TrueLayer_Logger::format_log( $log_id, 'WEBHOOK CALLBACK', $body['type'], wp_parse_url( untrailingslashit( home_url( '/wc-api/TrueLayer_Callback' ) ), PHP_URL_PATH ), wp_json_encode( $body ), '200' ) );

		// Make sure we have a payment_id.
		if ( empty( $body['payment_id'] ) && empty( $body['beneficiary']['payment_source_id'] ) ) {
			TrueLayer_Logger::log( sprintf( 'WEBHOOK CALLBACK ERROR [body]: Missing payment_id' ) );
			return;
		}

		// Make sure verification is ok.
		try {
			$verification = Truelayer_Helper_Verifying::get_tl_verification( $unsafe_body, $headers );
		} catch ( Exception $e ) {
			TrueLayer_Logger::log( sprintf( 'WEBHOOK CALLBACK ERROR [verification]. Trying again with a path where we do not try to remove trailing slash.', $e->getMessage() ) );
			// Try again. This time by passing a third param to get_tl_verification that doesn't try to remove a trailing slash from the path.
			try {
				$verification = Truelayer_Helper_Verifying::get_tl_verification( $unsafe_body, $headers, false );
			} catch ( Exception $e ) {
				TrueLayer_Logger::log( sprintf( 'WEBHOOK CALLBACK ERROR [verification]: %s', $e->getMessage() ) );
				return;
			}
		}

		if ( isset( $body['type'] ) ) {
			switch ( $body['type'] ) {
				case 'payment_executed':
					$this->handle_payment_executed( $body );
					break;
				case 'payment_settled':
					$this->handle_payment_settled( $body );
					break;
				case 'payment_failed':
					$this->handle_payment_failed( $body );
					break;
				case 'payout_executed':
					$this->handle_payout_executed( $body );
					break;
				case 'payout_failed':
					$this->handle_payout_failed( $body );
					break;
				case 'refund_executed':
					$this->handle_refund_executed( $body );
					break;
				case 'refund_failed':
					$this->handle_refund_failed( $body );
					break;
				default:
					$status   = $body['type'];
					$order = $this->get_woocommerce_order_from_payment_id( $body['payment_id'] );
					$order_id = is_object( $order ) ? $order->get_id() : '';
					TrueLayer_Logger::log( "Unhandled callback for order {$order_id}. Callback type: {$status}" );
					break;
			}
		}
	}

	/**
	 * Handle the TrueLayer payment_executed callback.
	 *
	 * @param array $body The information returned in the webhook from TrueLayer.
	 *
	 * @return bool
	 */
	public function handle_payment_executed( $body ) {
		$order = $this->get_woocommerce_order_from_payment_id( $body['payment_id'] );

		// Bail if we don't have an order.
		if ( ! is_object( $order ) ) {
			return;
		}

		$order_id = $order->get_id();
		TrueLayer_Logger::log( "Handle payment_executed callback for order ID {$order_id}." );

		// Error handling.
		if ( isset( $body['failure_reason'] ) && ! empty( $body['failure_reason'] ) ) {
			switch ( $body['failure_reason'] ) {

				case 'authorization_failed':
					$note = __( 'TrueLayer payment failed.', 'truelayer-for-woocommerce' );
					break;

				case 'provider_rejected':
					$note = __( 'TrueLayer payment failed: payment provider rejection.', 'truelayer-for-woocommerce' );
					break;

				default:
					$note = __( 'TrueLayer payment failed: unkown event.', 'truelayer-for-woocommerce' );
					break;
			}
			TrueLayer_Logger::log( sprintf( 'RETURN ERROR [%s]: %s WC Order id is: %s.', $body['failure_reason'], $note, $order_id ) );
			$order->add_order_note( $note );
			return false;
		}

		// Success handling.
		truelayer_confirm_order( $order, $body['payment_id'] );
		return true;
	}

	/**
	 * Handle the TrueLayer payment_settled callback.
	 *
	 * @param array $body The information returned in the webhook from TrueLayer.
	 *
	 * @return bool
	 */
	public function handle_payment_settled( $body ) {

		$order = $this->get_woocommerce_order_from_payment_id( $body['payment_id'] );

		// Bail if we don't have an order.
		if ( ! is_object( $order ) ) {
			return;
		}

		$order_id = $order->get_id();
		TrueLayer_Logger::log( "Handle payment_settled callback for order ID {$order_id}." );

		// Error handling.
		if ( isset( $body['failure_reason'] ) && ! empty( $body['failure_reason'] ) ) {
			$note = __( 'TrueLayer payment settlement failed. Failure reason: ', 'truelayer-for-woocommerce' ) . $body['failure_reason'];
			$order->add_order_note( $note );
			return false;
		}

		// Success handling.
		if ( isset( $body['payment_source']['id'] ) ) {
			$payment_source_id = sanitize_text_field( $body['payment_source']['id'] );
			$payment_user_id   = sanitize_text_field( $body['user_id'] );
			update_post_meta( $order->get_id(), '_truelayer_payment_source_id', $payment_source_id );
			update_post_meta( $order->get_id(), '_truelayer_payment_user_id', $payment_user_id );
			/* Translators: TrueLayer payment source id and payment user id returned in payment settled callback. */
			$order->add_order_note( sprintf( __( 'TrueLayer payment settled. Payment source id: %1$s. Payment user id: %2$s.', 'truelayer-for-woocommerce' ), $payment_source_id, $payment_user_id ) );
		}
		return true;
	}

	/**
	 * Handle the TrueLayer handle_payment_failed callback.
	 *
	 * @param array $body The information returned in the webhook from TrueLayer.
	 *
	 * @return bool
	 */
	public function handle_payment_failed( $body ) {

		$order = $this->get_woocommerce_order_from_payment_id( $body['payment_id'] );

		// Bail if we don't have an order.
		if ( ! is_object( $order ) ) {
			return;
		}

		$order_id = $order->get_id();
		TrueLayer_Logger::log( "Handle payment_failed callback for order ID {$order_id}." );

		// Error handling.
		if ( isset( $body['failure_reason'] ) && ! empty( $body['failure_reason'] ) ) {
			switch ( $body['failure_reason'] ) {

				case 'authorization_failed':
					$note = __( 'TrueLayer payment failed.', 'truelayer-for-woocommerce' );
					break;

				case 'provider_rejected':
					$note = __( 'TrueLayer payment failed: payment provider rejection.', 'truelayer-for-woocommerce' );
					break;

				default:
					$note = __( 'TrueLayer payment failed: unkown event.', 'truelayer-for-woocommerce' );
					break;
			}
			$order->set_status( 'failed', $note );
			$order->save();
		}
	}

	/**
	 * Handle the TrueLayer payout_executed callback.
	 *
	 * @param array $body The information returned in the webhook from TrueLayer.
	 *
	 * @return bool
	 */
	public function handle_payout_executed( $body ) {

		$order = $this->get_woocommerce_order_from_payment_source_id( $body['beneficiary']['payment_source_id'] );

		// Bail if we don't have an order.
		if ( ! is_object( $order ) ) {
			return;
		}

		$order_id = $order->get_id();
		TrueLayer_Logger::log( "Handle payout_executed callback for order ID {$order_id}." );

		// Error handling.
		if ( isset( $body['failure_reason'] ) && ! empty( $body['failure_reason'] ) ) {
			$note = __( 'TrueLayer payout executed failed. Failure reason: ', 'truelayer-for-woocommerce' ) . $body['failure_reason'];
			$order->add_order_note( $note );
			return false;
		}

		// Success handling.
		if ( isset( $body['payout_id'] ) ) {
			$payout_id = sanitize_text_field( $body['payout_id'] );
			update_post_meta( $order->get_id(), '_truelayer_payout_id', $payout_id );
			/* Translators: TrueLayer payment source id and payment user id returned in payment settled callback. */
			$order->add_order_note( sprintf( __( 'TrueLayer payout executed. Payout id: %1$s.', 'truelayer-for-woocommerce' ), $payout_id ) );
		}
		return true;
	}

	/**
	 * Handle the TrueLayer handle_payout_failed callback.
	 *
	 * @param array $body The information returned in the webhook from TrueLayer.
	 *
	 * @return bool
	 */
	public function handle_payout_failed( $body ) {

		$order = $this->get_woocommerce_order_from_payment_source_id( $body['beneficiary']['payment_source_id'] );

		// Bail if we don't have an order.
		if ( ! is_object( $order ) ) {
			return;
		}

		$order_id = $order->get_id();
		TrueLayer_Logger::log( "Handle handle_payout_failed callback for order ID {$order_id}." );

		// Error handling.
		if ( isset( $body['failure_reason'] ) && ! empty( $body['failure_reason'] ) ) {
			/* Translators: Payout failure reason. */
			$note = sprintf( __( 'TrueLayer payout failed. Failure reason: %s.', 'truelayer-for-woocommerce' ), $body['failure_reason'] );
			$order->set_status( 'on-hold', $note );
			$order->save();
		}
	}

	/**
	 * Handle the TrueLayer refund_executed callback.
	 *
	 * @param array $body The information returned in the webhook from TrueLayer.
	 *
	 * @return bool
	 */
	public function handle_refund_executed( $body ) {

		$order = $this->get_woocommerce_order_from_transaction_id( $body['payment_id'] );

		// Bail if we don't have an order.
		if ( ! is_object( $order ) ) {
			return;
		}

		$order_id = $order->get_id();
		TrueLayer_Logger::log( "Handle refund_executed callback for order ID {$order_id}." );

		// Error handling.
		if ( isset( $body['failure_reason'] ) && ! empty( $body['failure_reason'] ) ) {
			$note = __( 'TrueLayer refund executed failed. Failure reason: ', 'truelayer-for-woocommerce' ) . $body['failure_reason'];
			$order->add_order_note( $note );
			return false;
		}

		// Success handling.
		if ( isset( $body['refund_id'] ) ) {
			$refund_id = sanitize_text_field( $body['refund_id'] );
			update_post_meta( $order->get_id(), '_truelayer_refund_id', $refund_id );
			/* Translators: TrueLayer payment source id and payment user id returned in payment settled callback. */
			$order->add_order_note( sprintf( __( 'TrueLayer refund executed. Refund id: %1$s.', 'truelayer-for-woocommerce' ), $refund_id ) );
		}
		return true;
	}

	/**
	 * Handle the TrueLayer handlerefund_failed callback.
	 *
	 * @param array $body The information returned in the webhook from TrueLayer.
	 *
	 * @return bool
	 */
	public function handle_refund_failed( $body ) {

		$order = $this->get_woocommerce_order_from_transaction_id( $body['payment_id'] );

		// Bail if we don't have an order.
		if ( ! is_object( $order ) ) {
			return;
		}

		$order_id = $order->get_id();
		TrueLayer_Logger::log( "Handle handle_refund_failed callback for order ID {$order_id}." );

		// Error handling.
		if ( isset( $body['failure_reason'] ) && ! empty( $body['failure_reason'] ) ) {
			/* Translators: Payout failure reason. */
			$note = sprintf( __( 'TrueLayer refund failed. Failure reason: %s.', 'truelayer-for-woocommerce' ), $body['failure_reason'] );
			$order->set_status( 'on-hold', $note );
			$order->save();
		}
	}

	/**
	 * Get WooCommerce order based on payment id.
	 *
	 * @param string $payment_id The payment id from TrueLayer.
	 *
	 * @return object
	 */
	public function get_woocommerce_order_from_payment_id( $payment_id ) {

		$query_args = array(
			'fields'      => 'ids',
			'post_type'   => wc_get_order_types(),
			'post_status' => array_keys( wc_get_order_statuses() ),
			'meta_key'    => '_truelayer_payment_id', // phpcs:ignore WordPress.DB.SlowDBQuery -- Slow DB Query is ok here, we need to limit to our meta key.
			'meta_value'  => $payment_id, // phpcs:ignore WordPress.DB.SlowDBQuery -- Slow DB Query is ok here, we need to limit to our meta key.
			'date_query'  => array(
				array(
					'after' => '20 day ago',
				),
			),
		);

		$orders = get_posts( $query_args );

		// We could not find a correlating WooCommerce order, let's bail.
		if ( empty( $orders ) ) {
			TrueLayer_Logger::log( sprintf( 'WEBHOOK CALLBACK ERROR [orders]: Callback could not obtain the WooCommerce order ID.' ) );
			return false;
		}

		// Set the order from the first order id returned.
		$order_id = $orders[0];
		return wc_get_order( $order_id );
	}

	/**
	 * Get WooCommerce order based on payment source id.
	 *
	 * @param string $payment_source_id The payment source id from TrueLayer.
	 *
	 * @return object
	 */
	public function get_woocommerce_order_from_payment_source_id( $payment_source_id ) {

		$query_args = array(
			'fields'      => 'ids',
			'post_type'   => wc_get_order_types(),
			'post_status' => array_keys( wc_get_order_statuses() ),
			'meta_key'    => '_truelayer_payment_source_id', // phpcs:ignore WordPress.DB.SlowDBQuery -- Slow DB Query is ok here, we need to limit to our meta key.
			'meta_value'  => $payment_source_id, // phpcs:ignore WordPress.DB.SlowDBQuery -- Slow DB Query is ok here, we need to limit to our meta key.
			'date_query'  => array(
				array(
					'after' => '40 day ago',
				),
			),
		);

		$orders = get_posts( $query_args );

		// We could not find a correlating WooCommerce order, let's bail.
		if ( empty( $orders ) ) {
			TrueLayer_Logger::log( sprintf( 'WEBHOOK CALLBACK ERROR [orders]: Callback could not obtain the WooCommerce order ID.' ) );
			return false;
		}

		// Set the order from the first order id returned.
		$order_id = $orders[0];
		return wc_get_order( $order_id );
	}

	/**
	 * Get WooCommerce order based on transaction id.
	 *
	 * @param string $transaction_id The transaction id from TrueLayer.
	 *
	 * @return object
	 */
	public function get_woocommerce_order_from_transaction_id( $transaction_id ) {

		$query_args = array(
			'fields'      => 'ids',
			'post_type'   => wc_get_order_types(),
			'post_status' => array_keys( wc_get_order_statuses() ),
			'meta_key'    => '_transaction_id', // phpcs:ignore WordPress.DB.SlowDBQuery -- Slow DB Query is ok here, we need to limit to our meta key.
			'meta_value'  => $transaction_id, // phpcs:ignore WordPress.DB.SlowDBQuery -- Slow DB Query is ok here, we need to limit to our meta key.
			'date_query'  => array(
				array(
					'after' => '100 day ago',
				),
			),
		);

		$orders = get_posts( $query_args );

		// We could not find a correlating WooCommerce order, let's bail.
		if ( empty( $orders ) ) {
			TrueLayer_Logger::log( sprintf( 'WEBHOOK CALLBACK ERROR [orders]: Callback could not obtain the WooCommerce order ID.' ) );
			return false;
		}

		// Set the order from the first order id returned.
		$order_id = $orders[0];
		return wc_get_order( $order_id );
	}

} new TrueLayer_Callbacks();
