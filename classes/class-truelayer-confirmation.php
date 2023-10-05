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
class TrueLayer_Redirect {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_api_truelayer_redirect', array( $this, 'maybe_redirect' ) );
	}

	/**
	 * Redirects the customer to the appropriate page, but only if TrueLayer redirected the customer to the home page.
	 *
	 * @return void
	 */
	public function maybe_redirect() {

		$transaction_id = filter_input( INPUT_GET, 'payment_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$error          = filter_input( INPUT_GET, 'error', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		$query_args = array(
			'fields'      => 'ids',
			'post_type'   => wc_get_order_types(),
			'post_status' => array_keys( wc_get_order_statuses() ),
			'meta_key'    => '_truelayer_payment_id', // phpcs:ignore WordPress.DB.SlowDBQuery -- Slow DB Query is ok here, we need to limit to our meta key.
			'meta_value'  => $transaction_id, // phpcs:ignore WordPress.DB.SlowDBQuery -- Slow DB Query is ok here, we need to limit to our meta key.
			'date_query'  => array(
				array(
					'after' => '3 day ago',
				),
		),
		);

		$orders = get_posts( $query_args );

		// Set the order from the first order id returned .
		if ( ! empty( $orders ) ) {
			$order_id = $orders[0];
			$order    = wc_get_order( $order_id );

		} else {

			TrueLayer_Logger::log( sprintf( 'RETURN ERROR [orders]: Could not obtain the WooCommerce order ID. TrueLayer payment ID: %s', $transaction_id ) );
			$note = __( 'Something went wrong, please contact the store owner.', 'truelayer-for-woocommerce' );
			wc_add_notice( $note, 'error' );
			wp_redirect( wc_get_cart_url() );
			return;
		}

		if ( ! empty( $error ) ) {

			switch ( $error ) {

				case 'tl_hpp_cancel':
				case 'tl_hpp_abandoned':
					$note = __( 'Customer canceled the TrueLayer payment.', 'truelayer-for-woocommerce' );
					break;

				default:
					$note = __( 'TrueLayer payment failed: unkown event.', 'truelayer-for-woocommerce' );
					break;
			}

			TrueLayer_Logger::log( sprintf( 'Payment cancelled with TrueLayer . WC Order id is: % s ', $order_id ) );

			$order->add_order_note( $note );
				wc_add_notice( $note, 'error' );
				wp_redirect( wc_get_checkout_url() );
			exit;
		}

		wp_redirect(
			add_query_arg(
				array(
					'merchant_reference' => $order_id,
					'transaction_id'     => $transaction_id,
				),
				$order->get_checkout_order_received_url()
			),
		);

		exit;
	}

} new Truelayer_Redirect();
