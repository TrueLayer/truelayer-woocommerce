<?php
/**
 * Admin notice class file.
 *
 * @package  TrueLayer_Checkout/Classes/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Key;
/**
 * Returns error messages depending on
 *
 * @class    Truelayer_Admin_Notices
 * @author   Krokedil
 */
class Truelayer_Admin_Notices {

	/**
	 * The reference the *Singleton* instance of this class.
	 *
	 * @var $instance
	 */
	protected static $instance;

	/**
	 * Checks if Truelayer gateway is enabled.
	 *
	 * @var $enabled
	 */
	protected $enabled;

	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return Truelayer_Admin_Notices
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Truelayer_Admin_Notices constructor.
	 */
	public function __construct() {
		$settings      = get_option( 'woocommerce_truelayer_settings', array() );
		$this->enabled = $settings['enabled'] ?? 'no';
		add_action( 'admin_notices', array( $this, 'check_truelayer_key' ), 15 );
	}




	/**
	 * Check if truelayer key exist.
	 *
	 * @return void
	 */
	public function check_truelayer_key() {
		if ( defined( 'TRUELAYER_KEY' ) || did_action( 'true_layer_key_set' ) ) {
			return;
		}
		?>
			<div class="truelayer-message notice woocommerce-message notice-error">
				<?php
				$key           = Key::createNewRandomKey();
				$generated_key = $key->saveToAsciiSafeString();
				echo wp_kses_post(
					wpautop(
						'<p>' . __( "<b>TrueLayer for WooCommerce</b> was unable to save data to the <code>wp-config.php</code> file. The following code needs to be manually added to <code>wp-config.php</code> for the plugin to function properly.\n " . "<code>define( 'TRUELAYER_KEY', " . "'" . $generated_key . "' );</code>" ),
						'truelayer-for-woocommerce'
					) .
					'</p>'
				);
				?>
			</div>
			<?php

	}

}

Truelayer_Admin_Notices::get_instance();
