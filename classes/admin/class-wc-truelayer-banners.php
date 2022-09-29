<?php
/**
 * TrueLayer banners file.
 *
 * @package  TrueLayer_Checkout/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'WC_TrueLayer_Banners' ) ) {
	/**
	 * Displays merchant information in the backend.
	 */
	class WC_TrueLayer_Banners {
		/**
		 * WC_TrueLayer_Banners constructor.
		 */
		public function __construct() {
		}

		/**
		 * Adds banners to the settings sidebar.
		 */
		public static function settings_sidebar() {
			$redirect_uri      = home_url( '/wc-api/TrueLayer_Redirect/' );
			$callback_uri      = home_url( '/wc-api/TrueLayer_Callback/' );
			$krokedil_logo_src = TRUELAYER_WC_PLUGIN_URL . '/assets/images/krokedil-logo.png';
			?>
			<div id="krokdocs-text-container">
				<h3 class="krokdocs-sidebar-title">Get started</h3>
				<p class="krokdocs-sidebar-main-text">
					<a href="https://docs.krokedil.com/truelayer-for-woocommerce/" target="_blank">Documentation</a> <br/>
					<a href="https://krokedil.com/product/truelayer-for-woocommerce/" target="_blank">Plugin site</a>
				</p>
				<h3 class="krokdocs-sidebar-title">Configuration</h3>
				<p class="krokdocs-sidebar-main-text">
					Please make sure to register these 
					URL's in your <a href="https://console.truelayer.com/payments-api/settings" target="_blank"> TrueLayer Console</a>.
				</p>

				<p>
					<span class="small-title">Redirect URI:</span><br>
					<code><?php echo esc_url( $redirect_uri ); ?></code>
				</p>
				<p>
					<span class="small-title">Webhook URI:</span><br>
					<code><?php echo esc_url( $callback_uri ); ?></code>
				</p>
				<h3 class="krokdocs-sidebar-title">Support</h3>
				<p class="krokdocs-sidebar-main-text">
					If you have questions regarding a certain purchase you are welcome to contact <a href="https://www.truelayer.com/" target="_blank">TrueLayer</a>.
				</p>
				<p class="krokdocs-sidebar-main-text">
					If you have technical questions or questions regarding the configuration of the plugin you are welcome to contact <a href="https://www.krokedil.com/support" target="_blank">Krokedil</a>.
				</p>
				<div id="krokdocs-sidebar-bottom-holder">
					<p id="krokdocs-sidebar-logo-follow-up-text">
						Developed by:
					</p>
					<img id="krokdocs-sidebar-krokedil-logo-right"
					src="<?php echo esc_url( $krokedil_logo_src ); ?>">
				</div>
			</div>		
			<?php
		}
	}

	new WC_TrueLayer_Banners();
}
