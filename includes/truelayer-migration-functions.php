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
		return;
	}

	do_action( 'true_layer_key_set' );
}
