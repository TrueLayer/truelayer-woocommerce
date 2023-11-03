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
		add_action( 'admin_notices', array( $this, 'check_truelayer_encryption_error' ), 20 );
		add_action( 'admin_notices', array( $this, 'check_truelayer_encryption_key_error' ), 25 );
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
		$message = __( "<b>TrueLayer for WooCommerce</b> was unable to save data to the <code>wp-config.php</code> file. The following code needs to be manually added to <code>wp-config.php</code> for the plugin to function properly.\n ", 'truelayer-for-woocommerce' );
		?>
		<div class="truelayer-message notice woocommerce-message notice-error">
			<p>
				<?php echo wp_kses_post($message); ?>
				<code>define( 'TRUELAYER_KEY', "<?php echo esc_html($this->generate_new_key()) ?>");</code>
			</p>
		</div>
		<?php
	}

	/**
	 * Check if we have a encryption error saved in the database.
	 *
	 * @return void
	 */
	public function check_truelayer_encryption_error() {
		if ( did_action( 'true_layer_key_set' ) ) {
			return;
		}

		// Check if we have a encryption error saved in the database.
		$encryption_error = get_option( 'truelayer_encryption_error', false );

		// If we don't have one, just return.
		if ( ! $encryption_error ) {
			return;
		}

		// If we have one, then display it.
		?>
		<div class="truelayer-message notice woocommerce-message notice-error">
			<p>
				<?php echo wp_kses_post($encryption_error); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Check if we have a encryption error saved in the database.
	 *
	 * @return void
	 */
	public function check_truelayer_encryption_key_error() {
		if ( did_action( 'true_layer_key_set' ) ) {
			return;
		}

		// Check if we have a encryption error saved in the database.
		$encryption_error = get_option( 'truelayer_encryption_key_error', false );

		// If we don't have one, just return.
		if ( ! $encryption_error ) {
			return;
		}

		// If we have one, then display it.
		?>
		<div class="truelayer-message notice woocommerce-message notice-error">
			<p>
				<?php echo wp_kses_post($encryption_error); ?>
				<code>define( 'TRUELAYER_KEY', "<?php echo esc_html($this->generate_new_key()) ?>");</code>
			</p>
		</div>
		<?php
	}

	/**
	 * Generate a new encryption key for the merchant to paste into the WP Config file.
	 *
	 * @return string
	 */
	private function generate_new_key() {
		$key           = Key::createNewRandomKey();
		$generated_key = $key->saveToAsciiSafeString();

		return $generated_key;
	}
}

Truelayer_Admin_Notices::get_instance();
