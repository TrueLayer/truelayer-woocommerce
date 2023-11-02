<?php
/**
 * Helper class for encrypting and decrypting sensitive data.
 *
 * @package TrueLayer/Classes/
 */

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\BadFormatException;
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
	 * @var Key
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
	 * The keys for the settings that are encrypted.
	 *
	 * @var array
	 */
	protected $encrypted_keys = array(
		'truelayer_sandbox_client_private_key',
		'truelayer_sandbox_client_secret',
		'truelayer_sandbox_client_certificate',
		'truelayer_client_private_key',
		'truelayer_client_secret',
		'truelayer_client_certificate',
	);

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->key      = $this->get_default_key();
		$this->settings = get_option( 'woocommerce_truelayer_settings', array() );
		add_filter( 'woocommerce_settings_api_sanitized_fields_truelayer', array( $this, 'encrypt_values' ) );
	}

	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return self The *Singleton* instance.
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
	 * Encrypt any values that should be encrypted before we store them in the database.
	 *
	 * @param array $settings The gateway settings.
	 *
	 * @return mixed
	 */
	public function encrypt_values( $settings ) {
		// If the key does not exist, don't do anything.
		if ( ! defined( 'TRUELAYER_KEY' ) ) {
			return false;
		}

		// Clear any encryption error notices. New errors will be generated if an issue exists.
		delete_option( 'truelayer_encryption_error' );

		// Loop each of the encrypted keys and ensure the value is encrypted before saving.
		foreach ( $this->encrypted_keys as $encrypted_key ) {
			// Get the current value of the setting before updating.
			$current_value = $this->settings[ $encrypted_key ] ?? false;
			$new_value     = $settings[ $encrypted_key ] ?? false;

			// If the new value is empty, continue.
			if ( empty( $new_value ) ) {
				continue;
			}

			// Compare if the new value is the same as the current value after it is decrypted.
			$are_equal = $this->are_equal( $current_value, $new_value );

			// If the values are not equal, then encrypt the new value, else just save the current value since its already encrypted.
			$encrypted_value = $are_equal ? $current_value : $this->encrypt( $new_value );

			// Update the value in the settings array.
			$settings[ $encrypted_key ] = $encrypted_value ? $encrypted_value : '';
		}

		return $settings;
	}

	/**
	 * Returns the settings with the encrypted values decrypted.
	 *
	 * @param array $settings The gateway settings.
	 *
	 * @return array
	 */
	public function decrypt_values( $settings ) {
		// If the key does not exist, don't do anything.
		if ( ! defined( 'TRUELAYER_KEY' ) ) {
			return $settings;
		}

		// Clear any encryption error notices. New errors will be generated if an issue exists.
		delete_option( 'truelayer_encryption_error' );

		foreach ( $this->encrypted_keys as $encrypted_key ) {
			$decrypted_value            = $this->decrypt_value( $encrypted_key );

			$settings[ $encrypted_key ] = $decrypted_value ? $decrypted_value : '';
		}

		return $settings;
	}

	/**
	 * Returns the setting with the encrypted values decrypted.
	 *
	 * @param string $key The key to decrypt.
	 * @param string|bool $value The value to decrypt. Pass null to use the value from the settings array.
	 *
	 * @return string
	 */
	public function decrypt_value( $key, $value = null ) {
		$value = $value ?? $this->settings[ $key ] ?? '';

		// If the value is empty, just return it.
		if ( empty( $value ) ) {
			return $value;
		}

		return $this->decrypt( $value );
	}


	/**
	 * Encrypts a value.
	 *
	 * @param string $value Value to encrypt.
	 * @return string|bool Encrypted value or false on failure
	 */
	public function encrypt( $value ) {
		// If the value to encrypt is an empty string, or if its not a string, or if the key is null, just return the raw value.
		if ( empty( $value ) || ! is_string( $value ) || null === $this->key ) {
			return $value;
		}

		if ( defined( 'TRUELAYER_KEY' ) ) {
			try {
				return Crypto::encrypt( $value, $this->key );
			}
			catch (EnvironmentIsBrokenException | WrongKeyOrModifiedCiphertextException | BadFormatException $e) {
				// If the value could not be encrypted, then we should add a error notice to the admin.
				$message = __( 'There was an error when encrypting the settings for TrueLayer, please reconfigure the plugin and ensure the settings are saved properly.', 'truelayer-for-woocommerce' );
				update_option( 'truelayer_encryption_error', $message, 'no' );

				// Add a notice that the value could not be encrypted.
				return false;
			}
		}

		return false;
	}

	/**
	 * Decrypts a value.
	 *
	 * @param string $raw_value Value to decrypt.
	 * @return string|bool Decrypted value, or false on failure.
	 */
	public function decrypt( $raw_value ) {
		// If the value to decrypt is an empty string or if its not a string, or if the key is null, just return the raw value.
		if ( empty( $raw_value ) || ! is_string( $raw_value ) || null === $this->key ) {
			return $raw_value;
		}

		if ( defined( 'TRUELAYER_KEY' ) ) {
			try {
				return Crypto::decrypt( $raw_value, $this->key );
			}
			catch (EnvironmentIsBrokenException | WrongKeyOrModifiedCiphertextException | BadFormatException $e) {
				// If the value could not be decrypted, then we should add a error notice to the admin.
				$message = __( 'There was an error when decrypting the settings for TrueLayer, please reconfigure the plugin and ensure the settings are saved properly.', 'truelayer-for-woocommerce' );
				update_option( 'truelayer_encryption_error', $message, 'no' );

				// Add a notice that the value could not be decrypted.
				return false;
			}
		}

		return false;
	}

	/**
	 * Test if the settings key is one that is a encrypted value.
	 *
	 * @param string $key The key to test.
	 * @return bool
	 */
	public function is_encrypted_key( $key ) {
		return in_array( $key, $this->encrypted_keys, true );
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
			// Delete any encryption key errors stored, if issue persists a new error will be generated.
			delete_option( 'truelayer_encryption_key_error' );
			try {
				return Key::loadFromAsciiSafeString( TRUELAYER_KEY );
			}
			catch (BadFormatException | EnvironmentIsBrokenException $e) {
				$message = __( 'There was an error when loading the encryption key for TrueLayer, please ensure the encryption key saved in the <code>wp-config.php</code> file as <code>TRUELAYER_KEY</code> has not been modified. To reset delete the definition and add a new key.', 'truelayer-for-woocommerce' );
				update_option( 'truelayer_encryption_key_error', $message, 'no' );
				return null;
			}
		}
		return null;
	}

	/**
	 * Tests if the encrypted current value in the database is equal to the new value we want to save.
	 *
	 * @param string $current_value The current value in the database.
	 * @param string $new_value The new value we want to save.
	 *
	 * @return bool
	 */
	private function are_equal( $current_value, $new_value ) {
		// First decrypt the current value.
		$decrypted_current_value = empty( $current_value ) ? '' : $this->decrypt( $current_value );

		// Compare the decrypted current value to the new value.
		return $decrypted_current_value === $new_value;
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
