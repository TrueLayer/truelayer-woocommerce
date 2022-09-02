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

		$script_version = $this->truelayer_is_script_debug_enabled();
		$src            = TRUELAYER_WC_PLUGIN_URL . '/assets/js/truelayer-for-woocommerce-admin' . $script_version . '.js';
		$dependencies   = array( 'jquery' );

		wp_register_script( 'truelayer-for-woocommerce-admin', $src, $dependencies, TRUELAYER_WC_PLUGIN_VERSION, false );
		wp_enqueue_script( 'truelayer-for-woocommerce-admin' );
	}


	/**
	 * Check if assets should be loaded function
	 *
	 * @return bool
	 */
	protected function check_for_load_assets() {
		$section = filter_input( INPUT_GET, 'section', FILTER_SANITIZE_STRING );
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
