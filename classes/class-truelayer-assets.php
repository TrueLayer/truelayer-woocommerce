<?php
/**
 * Main assets file.
 *
 * @package TrueLayer_For_WooCommerce/Classes/Assets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TrueLayer_Assets class.
 */
class TrueLayer_Assets {

	/**
	 * True if inside WordPress administration interface.
	 *
	 * @var bool
	 */
	public $admin_request;

	/**
	 * INB_Assets constructor.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'truelayer_load_admin_js' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'truelayer_load_admin_css' ) );
                add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 *
	 * Checks whether a SCRIPT_DEBUG constant exists.
	 * If there is, the plugin will use minified files.
	 *
	 * @return string
	 */
	protected function truelayer_is_script_debug_enabled() {
		return ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	}


	/**
	 * Loads scripts for the plugin.
	 */
	public function truelayer_load_admin_js() {
		if ( ! $this->check_for_load_assets() ) {
			return;
		}

		$src          = TRUELAYER_WC_PLUGIN_URL . '/assets/js/truelayer-for-woocommerce-admin.min.js';
		$dependencies   = array( 'jquery' );

		wp_register_script( 'truelayer-for-woocommerce-admin', $src, $dependencies, TRUELAYER_WC_PLUGIN_VERSION, false );
		wp_enqueue_script( 'truelayer-for-woocommerce-admin' );
	}

	/**
	 * Loads scripts for the EPP (if enabled).
	 */
	public function enqueue_scripts() {
		$settings    = get_option( 'woocommerce_truelayer_settings', array() );
		$epp_enabled = $settings['truelayer_payment_page_type'] ?? 'HPP';

		if ( 'EPP' !== $epp_enabled ) {
			return;
		}

		if ( ! is_checkout() ) {
			return;
		}

		if ( is_order_received_page() ) {
			return;
		}

		wp_register_script(
			'truelayer-for-woocommerce',
			plugins_url( 'assets/js/truelayer-for-woocommerce.min.js', TRUELAYER_WC_MAIN_FILE ),
			array( 'jquery' ),
			TRUELAYER_WC_PLUGIN_VERSION,
			true
		);

		$checkout_localize_params = array(
			'testmode' => $settings['testmode'] ?? 'yes'
		);

		wp_localize_script( 'truelayer-for-woocommerce', 'truelayerParams', $checkout_localize_params );
		wp_enqueue_script( 'truelayer-for-woocommerce' );
	}

	/**
	 * Check if assets should be loaded function
	 *
	 * @return bool
	 */
	protected function check_for_load_assets() {
		$section = filter_input( INPUT_GET, 'section', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( empty( $section ) || 'truelayer' !== $section ) {
			return false;
		}
		return true;
	}

	/**
	 * Loads style for the plugin.
	 */
	public function truelayer_load_admin_css() {

		if ( ! $this->check_for_load_assets() ) {
			return;
		}

		$style_version = $this->truelayer_is_script_debug_enabled();
		wp_register_style(
			'truelayer-style',
			TRUELAYER_WC_PLUGIN_URL . '/assets/css/truelayer-for-woocommerce-admin' . $style_version . '.css',
			array(),
			TRUELAYER_WC_PLUGIN_VERSION
		);
		wp_enqueue_style( 'truelayer-style' );
	}

}
new Truelayer_Assets();
