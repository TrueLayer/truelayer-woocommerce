<?php
use Defuse\Crypto\Key;

/**
 * The TrueLayer Migration Functions file.
 *
 * Contains all the functions that are used to migrate the plugin from one version to another.
 *
 * @package TrueLayer_For_WooCommerce/Includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ensure the TrueLayer key exists in the wp-config.php file.
 *
 * @return void
 */
function truelayer_140_ensure_truelayer_key_exists() {
	// Check if the TRUELAYER_KEY definition has already been added. If its already defined, just return.
	if ( defined( 'TRUELAYER_KEY' ) ) {
		return;
	}

	// Get the config editor class.
	$config_editor = new TrueLayer_Config_Editor();

	// Get the encryption key to use.
	$key           = Key::createNewRandomKey();
	$generated_key = $key->saveToAsciiSafeString();

	// Add the key to the wp-config.php file.
	$result = $config_editor->add_key( 'TRUELAYER_KEY', $generated_key );

	// If the key was not successfully added, then we need to show an admin notice to the merchant.
	if ( ! $result ) {
		TrueLayer_Logger::log( 'Failed to add the TRUELAYER_KEY to the wp-config.php file.' );
		return;
	}

	Truelayer_Logger::log( 'TRUELAYER_KEY added to the wp-config.php file.' );
	do_action( 'true_layer_key_set' );
}

/**
 * Ensure the TrueLayer settings are encrypted in the database.
 *
 * @return void
 */
function truelayer_140_ensure_settings_encrypted() {
	// Get the settings from the database.
	$settings = get_option( 'woocommerce_truelayer_settings', array() );

	// If the settings are empty, then we don't need to do anything.
	if ( empty( $settings ) ) {
		return;
	}

	$encryption = Truelayer_Encryption::get_instance();

	// Loop each setting and check if we should encrypt it or not.
	foreach ( $settings as $key => $value ) {
		// Ensure its a value that should be encrypted.
		if ( ! $encryption->is_encrypted_key( $key ) ) {
			continue;
		}

		// If the value is empty, then we don't need to do anything.
		if ( empty( $value ) ) {
			continue;
		}

		// If the value is already encrypted, then we don't need to do anything. If it fails to decrypt it will return false.
		if ( $encryption->decrypt( $value ) ) {
			continue;
		}

		// Clear any errors that may have been generated.
		delete_option( 'truelayer_encryption_error' );

		// Encrypt the value.
		$encrypted_value = $encryption->encrypt( $value );

		// Update the setting with the new encrypted value.
		$settings[ $key ] = $encrypted_value ? $encrypted_value : '';
	}

	// Update the settings in the database.
	update_option( 'woocommerce_truelayer_settings', $settings );
}
