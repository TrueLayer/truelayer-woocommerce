<?php
/**
 * Helper class to get order lines for the requests.
 *
 * @package TrueLayer/Classes/Requests/Helpers
 */

defined( 'ABSPATH' ) || exit;

/**
 * Helper class to get order lines for the requests.
 */
class TrueLayer_Helper_Order {
	/**
	 * The WooCommerce order being processed.
	 *
	 * @var WC_Order
	 */
	public static $order;

	/**
	 * Get the order total amount.
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @return int
	 */
	public static function get_order_amount( $order ) {

		$order_amount = strval( $order->get_total() * 100 );

		return $order_amount;
	}

	/**
	 * Get the full account holder name.
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @return int
	 */
	public static function get_account_holder_name( $order ) {

		$account_holder_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

		return $account_holder_name;
	}
}
