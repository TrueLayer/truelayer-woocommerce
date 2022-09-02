<?php
/**
 * Functions file for the plugin.
 *
 * @package  True_Layer/Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prints error message as notices.
 *
 * @param WP_Error $wp_error A WordPress error object.
 * @return void
 */
function truelayer_print_error_message( $wp_error ) {
	wc_print_notice( $wp_error->get_error_message(), 'error' );
}

/**
 * Check if order is authorized and confirm order.
 *
 * @param WC_Order|bool $order The WooCommerce order.
 * @param string        $transaction_id The TrueLayer payment ID.
 * @return void
 */
function truelayer_confirm_order( $order, $transaction_id ) {

	if ( ! is_wp_error( $order ) ) {

		$order->payment_complete( $transaction_id );
		$order->add_order_note( __( 'Order successfully placed with TrueLayer. Payment transaction id:', 'truelayer-for-woocommerce' ) . ' ' . $transaction_id );
	}

}

/**
 * Check if order is authorized and confirm order.
 *
 * @param string $currency The currency used.
 * @return string
 */
function truelayer_get_merchant_account_id( $currency = 'GBP' ) {
	$settings            = get_option( 'woocommerce_truelayer_settings', array() );
	$merchant_account_id = 'GBP' === $currency ? $settings['truelayer_beneficiary_merchant_account_id'] : $settings['truelayer_beneficiary_merchant_account_id_eur'];

	return $merchant_account_id;
}

/**
 * Retrieve headers from PHP $_SERVER.
 *
 * @param array $parameters PHP $_SERVER.
 * @return array
 */
function truelayer_get_all_headers( $parameters ) {
	$headers = array();

	foreach ( $parameters as $key => $value ) {
		if ( substr( $key, 0, 5 ) === 'HTTP_' ) {
			$headers[ substr( str_replace( '_', '-', $key ), 5 ) ] = $value;
		} elseif ( \in_array( $key, array( 'CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5' ), true ) ) {
			$headers[ str_replace( '_', '-', $key ) ] = $value;
		}
	}
	return array_change_key_case( $headers, CASE_LOWER );
}
