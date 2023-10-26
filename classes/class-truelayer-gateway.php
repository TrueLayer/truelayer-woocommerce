<?php // phpcs:ignore
/**
 * TrueLayer payment for WooComerce gateway class.
 *
 * @package TrueLayer_For_WooCommerce/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TrueLayer_Payment_Gateway.
 */
class TrueLayer_Payment_Gateway extends WC_Payment_Gateway {
	/**
	 * Allowed currencies.
	 *
	 * @var array
	 */
	public $allowed_currencies = array( 'GBP', 'EUR' );

	/**
	 * The plugin testmode.
	 *
	 * @var string
	 */
	public $testmode;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->id                 = 'truelayer';
		$this->method_title       = __( 'TrueLayer', 'truelayer-for-woocommerce' );
		$this->method_description = __( 'Direct bank payments via TrueLayer.', 'truelayer-for-woocommerce' );
		$this->supports           = apply_filters(
			'truelayer_payment_gateway_supports',
			array(
				'products',
				'refunds',
			)
		);

		$this->init_form_fields();
		$this->init_settings();

		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );

		$this->testmode = $this->get_option( 'testmode' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			array(
				$this,
				'process_admin_options',
			)
		);
	}

	/**
	 * Get option from DB.
	 *
	 * @param string $key Option key.
	 * @param mixed  $empty_value Value when empty.
	 *
	 * @return string
	 */
	public function get_option( $key, $empty_value = null ) {
		// Get the original value from the parent method.
		$parent_value = parent::get_option( $key, $empty_value );

		// If the current key is not one that is encrypted, then just return the parent value.
		if ( ! TruelayerEncryption()->is_encrypted_key( $key ) ) {
			return $parent_value;
		}

		// Attempt to get the decrypted value.
		$decrypted_value = TruelayerEncryption()->decrypt_value( $key, $parent_value );

		// If the decrypted value is empty, then return an empty string to prevent issues from re-encrypting an already encrypted value.
		return $decrypted_value ?? '';
	}

	/**
	 * Get gateway icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		$icon_src   = TRUELAYER_WC_PLUGIN_URL . '/assets/images/icon-instant-bank-transfer.svg';
		$icon_width = '25';
		$icon_html  = '<img src="' . $icon_src . '" alt="TrueLayer" style="max-width:' . $icon_width . 'px"/>';
		return apply_filters( 'truelayer_icon_html', $icon_html );
	}

	/**
	 * Initialize settings fields.
	 */
	public function init_form_fields() {
		$this->form_fields = TrueLayer_Fields::fields();
	}

	/**
	 * Check currency.
	 *
	 * @param WC_Order|bool $order The WooCommerce order.
	 */
	public function country_currency_check( $order = false ) {
		// Check if allowed currency.
		if ( ! in_array( get_woocommerce_currency(), $this->allowed_currencies, true ) ) {
			return new WP_Error( 'currency', 'Currency not allowed for TrueLayer' );
		}

		// Check if customer is available for EUR.
		if ( 'EUR' === get_woocommerce_currency() && ! empty( WC()->customer ) ) {
			if ( method_exists( WC()->customer, 'get_billing_country' ) ) {
				if ( ! in_array( WC()->customer->get_billing_country(), $this->get_option( 'available_eur_countries' ), true ) ) {
					return new WP_Error( 'country', 'Customer country is not available for EUR payments.' );
				}
			}
		}

		// Check if customer is available for GBP.
		if ( 'GBP' === get_woocommerce_currency() && ! empty( WC()->customer ) ) {
			if ( method_exists( WC()->customer, 'get_billing_country' ) ) {
				if ( ! in_array( WC()->customer->get_billing_country(), array( 'GB' ), true ) ) {
					return new WP_Error( 'country', 'Customer country is not available for GBP payments.' );
				}
			}
		}

		return true;
	}

	/**
	 * Process the payment.
	 *
	 * @param int $order_id WooCommerce order id.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$settings    = get_option( 'woocommerce_truelayer_settings', array() );
		$epp_enabled = $settings['truelayer_payment_page_type'] ?? 'HPP';

		$response = TrueLayer()->api->create_payment( $order_id );

		if ( is_wp_error( $response ) ) {
			$note = __( 'Failed creating order with TrueLayer', 'truelayer-for-woocommerce' );
			wc_add_notice( $note, 'error' );
                        TrueLayer_Logger::log( sprintf( 'Failed creating order with TrueLayer. Error message: %s', $response->get_error_message() ) );

			return array(
				'result' => 'error',
			);
		}

		$truelayer_payment_id    = $response['id'];
		$truelayer_payment_token = $response['resource_token'];

		update_post_meta( $order_id, '_truelayer_payment_id', $truelayer_payment_id );
		update_post_meta( $order_id, '_truelayer_payment_token', $truelayer_payment_token );

		$build_test_url = Truelayer_Helper_Hosted_Payment_Page_URL::build_hosted_payment_page_url( $order_id );

		if ( 'EPP' === $epp_enabled ) {
			$url = add_query_arg(
				array(
					'payment_id' => $truelayer_payment_id,
					'token'      => $truelayer_payment_token
				),
				home_url( '/wc-api/TrueLayer_Redirect/' )
			);

			return array(
				'result'   => 'success',
				'redirect' => '#truelayer=' . rawurlencode( $url ),
			);
		}

		return array(
			'result'   => 'success',
			'redirect' => $build_test_url,
		);

	}

	/**
	 * Process TrueLayer refunds
	 *
	 * @param integer $order_id WooCommerce order id.
	 * @param integer $amount The refund amount.
	 * @param string  $reason Reason for refund.
	 * @return bool
	 */
	public function process_refund( $order_id, $amount = 0, $reason = '' ) {

		$order = wc_get_order( $order_id );

		$truelayer_refund = TrueLayer()->api->refund_payment( $order_id, $amount, $reason );

		if ( is_wp_error( $truelayer_refund ) ) {
			// translators: refund error.
			$order->add_order_note( sprintf( __( 'TrueLayer order refund failed: %s', 'truelayer-for-woocommerce' ), wp_json_encode( $truelayer_refund->get_error_message() ) ) );
			return false;
		}

		// translators: refund amount.
		$text          = __( 'TrueLayer received refund request (%s). Awaiting refund executed notification from TrueLayer.', 'truelayer-for-woocommerce' );
		$formated_text = sprintf( $text, wc_price( $amount ) );
		$order->add_order_note( $formated_text );
		return true;
	}

	/**
	 * Check if payment method is available.
	 *
	 * @return boolean
	 */
	public function is_available() {
		if ( 'yes' !== $this->get_option( 'enabled' ) ) {
			return false;
		}
		$settings = get_option( 'woocommerce_truelayer_settings' );

		// Check country and currency.
		if ( is_wp_error( $this->country_currency_check() ) ) {
			return false;
		}

		// Check that we have a merchant account id for GBP (if current currency is GBP).
		if ( 'GBP' === get_woocommerce_currency() && empty( $settings['truelayer_beneficiary_merchant_account_id'] ) ) {
			return false;
		}

		// Check that we have a merchant account id for EUR (if current currency is EUR).
		if ( 'EUR' === get_woocommerce_currency() && empty( $settings['truelayer_beneficiary_merchant_account_id_eur'] ) ) {
			return false;
		}

		return true;
	}

		/**
		 * Add sidebar to the settings page.
		 */
	public function admin_options() {
		?>
		<!-- Admin settings page wrapper/container -->
		<div id="truelayer-wrapper">

			<!-- Admin settings fields -->
			<div id="truelayer-main">
				<table class="form-table">
			<?php $this->generate_settings_html(); ?>
				</table>
			</div>
			<div id="krokdocs-sidebar">
				<?php echo wp_kses_post( WC_TrueLayer_Banners::settings_sidebar() ); ?>
			</div>
			</div>
			<!-- Save button separator -->
			<div class="save-separator"></div>
			<?php
	}

}

/**
 * Register new payment gateway
 *
 * @wp_hook woocommerce_payment_gateways
 * @param  array $methods New registered payment method.
 * @return array $methods New registered payment method.
 */
function register_truelayer_gateway( $methods ) {
	$methods[] = 'TrueLayer_Payment_Gateway';
	return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'register_truelayer_gateway' );
