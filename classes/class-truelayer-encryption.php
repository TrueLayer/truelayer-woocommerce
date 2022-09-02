<?php
/**
 * Helper class for encrypting and decrypting sensitive data.
 *
 * @package TrueLayer/Classes/
 */

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Key;

/**
 * Class Truelayer_Encryption
 */
class Truelayer_Encryption {

	/**
	 * The reference the *Singleton* instance of this class.
	 *
	 * @var $instance
	 */
	protected static $instance;

	/**
	 * Key to use for encryption.
	 *
	 * @var string
	 */
	private $key;

	/**
	 * The plugin settings.
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * Test mode.
	 *
	 * @var string
	 */
	protected $testmode;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->key      = $this->get_default_key();
		$this->settings = get_option( 'woocommerce_truelayer_settings', array() );
		add_filter( 'woocommerce_settings_api_sanitized_fields_truelayer', array( $this, 'update_truelayer_credit' ) );
		add_action( 'init', array( $this, 'maybe_encrypt_settings_after_plugin_update' ), 20 );
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
	 * Update specific gateway settings.
	 *
	 * @param array $settings The gateway settings.
	 *
	 * @return mixed
	 */
	public function update_truelayer_credit( $settings ) {
		// if the key does not exist, don't do anything.
		if ( ! defined( 'TRUELAYER_KEY' ) ) {
			return false;
		}
		$private_key = null;
		if ( 'yes' === $settings['testmode'] ) {
			$private_key        = 'truelayer_sandbox_client_private_key';
			$client_secret      = 'truelayer_sandbox_client_secret';
			$client_certificate = 'truelayer_sandbox_client_certificate';
		} else {
			$private_key        = 'truelayer_client_private_key';
			$client_secret      = 'truelayer_client_secret';
			$client_certificate = 'truelayer_client_certificate';

		}
		$truelayer_options = array( $private_key, $client_secret, $client_certificate );
		foreach ( $truelayer_options as $truelayer_option ) {
			if ( ! empty( $this->settings[ $truelayer_option ] ) ) {
				$old_value = $this->settings[ $truelayer_option ];
				try {
					if ( $this->decrypt( $settings[ $truelayer_option ] ) === $this->decrypt( $old_value ) ) {
						$settings[ $truelayer_option ] = $old_value;
					} else {
						$settings[ $truelayer_option ] = $this->encrypt( $settings[ $truelayer_option ] );
					}
				} catch ( EnvironmentIsBrokenException | WrongKeyOrModifiedCiphertextException $e ) {
					$settings[ $truelayer_option ] = $this->encrypt( $settings[ $truelayer_option ] );
				}
			} else {
				$settings[ $truelayer_option ] = $this->encrypt( $settings[ $truelayer_option ] );
			}
		}
		return $settings;

	}

	/**
	 * Checks if settings keys needs to be encrypted.
	 * Helps users running version 0.9.3 to automatically encrypt keys when updating the plugin without needing to resave the plugin settings manually.
	 *
	 * @Todo: remove this function in a future version since all new plugin users will save the plugin settings when configuring the plugin, and that will trigger the encryption.
	 *
	 * @return bool|void
	 */
	public function maybe_encrypt_settings_after_plugin_update() {

		// if the key does not exist, do nothing.
		if ( ! defined( 'TRUELAYER_KEY' ) ) {
			return;
		}

		// If plugin settings is empty, do nothing.
		$settings = get_option( 'woocommerce_truelayer_settings', array() );
		if ( empty( $settings ) ) {
			return;
		}

		// Maybe encrypt the values.
		$updated_settings = $this->update_truelayer_credit( $settings );

		// If the updated settings has changed (values have been encrypted), save it to the woocommerce_truelayer_settings option.
		if ( $settings !== $updated_settings ) {
			update_option( 'woocommerce_truelayer_settings', $updated_settings, 'yes' );
		}
	}

	/**
	 * Encrypts a value.
	 *
	 * @param string $value Value to encrypt.
	 * @return string|void Encrypted value, or false on failure.
	 */
	public function encrypt( $value ) {
		if ( defined( 'TRUELAYER_KEY' ) ) {
			return Crypto::encrypt( $value, $this->key );
		}
	}

	/**
	 * Decrypts a value.
	 *
	 * @param string $raw_value Value to decrypt.
	 * @return string|void Decrypted value, or false on failure.
	 * @throws EnvironmentIsBrokenException Env is broken.
	 * @throws WrongKeyOrModifiedCiphertextException Wrong key.
	 */
	public function decrypt( $raw_value ) {
		if ( defined( 'TRUELAYER_KEY' ) ) {
			return Crypto::Decrypt( $raw_value, $this->key );
		}

	}

	/**
	 * Gets the default encryption key to use.
	 *
	 * @since 1.0.0
	 *
	 * @return Key|null Default (not user-based) encryption key.
	 */
	private function get_default_key() {
		if ( defined( 'TRUELAYER_KEY' ) && is_string( TRUELAYER_KEY ) ) {
			return Key::loadFromAsciiSafeString( TRUELAYER_KEY );
		}
		return null;
	}
}
Truelayer_Encryption::get_instance();


/**
 * Main instance TruelayerEncryption.
 *
 * Returns the main instance of TruelayerEncryption.
 *
 * @return Truelayer_Encryption
 */
function TruelayerEncryption() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
	return Truelayer_Encryption::get_instance();
}
