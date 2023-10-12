<?php
/**
 * Config Keys Class File
 *
 * @package TrueLayer_For_WooCommerce/Classes
 */

use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Key;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The TrueLayer Config Keys class.
 */
class TrueLayer_Config_Keys {

	/**
	 * The reference the *Singleton* instance of this class.
	 *
	 * @var $instance
	 */
	protected static $instance;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'maybe_create_truelayer_key' ) );
	}

	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return self::$instance The *Singleton* instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Private clone method to prevent cloning of the instance.
	 *
	 * @return void
	 */
	public function __clone() {
		wc_doing_it_wrong( __FUNCTION__, __( 'Nope' ), '1.0' );
	}

	/**
	 * Private unserialize method to prevent unserializing.
	 *
	 * @return void
	 */
	public function __wakeup() {
		wc_doing_it_wrong( __FUNCTION__, __( 'Nope' ), '1.0' );
	}

	/**
	 * Create a TRUELAYER_KEY and save it to wp-config.php if needed.
	 *
	 * @return void|false.
	 */
	public function create_truelayer_key_and_save_to_wp_config() {
		include_once TRUELAYER_WC_PLUGIN_PATH . '/vendor/autoload.php';
		try {
			$key           = Key::createNewRandomKey();
			$generated_key = $key->saveToAsciiSafeString();
		} catch ( EnvironmentIsBrokenException $e ) {
			$error[] = $e->getMessage();
			return false;
		}

		$config_file_name = 'wp-config';
		$config_file      = ABSPATH . $config_file_name . '.php';
		$lines            = file( $config_file, FILE_IGNORE_NEW_LINES );
		$offset           = 0;
		foreach ( $lines as $key => $line ) {
			if ( false !== stripos( $line, 'NONCE_SALT' ) ) {
				$offset = $key;
			}

			// If we find any lines that contain the TRUELAYER_KEY constant, then we don't need to add it.
			if ( false !== stripos( $line, 'TRUELAYER_KEY' ) ) {
				return;
			}
		}
		array_splice( $lines, $offset + 1, 0, array( "define( 'TRUELAYER_KEY', " . "'" . $generated_key . "' );" ) );
		$data = implode( PHP_EOL, $lines );
        $res = file_put_contents(  $config_file, $data );//phpcs:ignore
		if ( false !== $res ) {
			do_action( 'true_layer_key_set' );
		}
	}

	/**
	 * Deletes Truelayer key from wp-config file on plugin deactivation.
	 *
	 * @return void
	 */
	public function delete_truelayer_key_from_wp_config() {
		if ( defined( 'TRUELAYER_KEY' ) ) {
			$config_file = ABSPATH . 'wp-config.php';
			$lines       = file( $config_file, FILE_IGNORE_NEW_LINES );
			foreach ( $lines as $key => $line ) {
				if ( false !== stripos( $line, 'TRUELAYER_KEY' ) ) {
					unset( $lines[ $key ] );
				}
			}
			$data = implode( PHP_EOL, $lines );
            file_put_contents( $config_file, $data );//phpcs:ignore
		}
	}

	/**
	 * Check if TRUELAYER_KEY needs to be created and saved to wp-config.php.
	 *
	 * @return void.
	 */
	public function maybe_create_truelayer_key() {
		if ( defined( 'TRUELAYER_KEY' ) ) {
			return;
		}

		$this->create_truelayer_key_and_save_to_wp_config();
	}

}
TrueLayer_Config_Keys::get_instance();
