<?php
/**
 * Class that handles the plugin installation and updates.
 *
 * @package TrueLayer_For_WooCommerce/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * The TrueLayer Install class
 */
class TrueLayer_Install {
	const SLUG = 'truelayer';
	const UPDATE_FUNCTIONS_FILE_PATH = TRUELAYER_WC_PLUGIN_PATH . '/includes/truelayer-migration-functions.php';
	const PLUGIN_NAME = 'TrueLayer for WooCommerce';
	const VERSION = TRUELAYER_WC_PLUGIN_VERSION;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'check_version' ), 10 );
		add_action( self::SLUG . '_update', array( __CLASS__, 'run_version_changes' ), 10, 2 );
	}

	/**
	 * List of callable functions to run when a specific version is installed.
	 *
	 * When adding a new version, add the version number as a key and the callable functions as an array value with the string to the method name.
	 * Methods are run in the order they are added to the array.
	 *
	 * @var array
	 */
	private static $version_changes = array(
		'1.4.0' => array(
			'truelayer_140_ensure_truelayer_key_exists',
			'truelayer_140_ensure_settings_encrypted'
		)
	);

	/**
	 * Check the current version and run the installer if needed.
	 *
	 * @return void
	 */
	public static function check_version() {
		// Get the new and current version thats been applied.
		$current_version = get_option( self::SLUG . '_version', '0.0.0' );

		// If the current version is lower than the new version, run the installer.
		if ( version_compare( $current_version, self::VERSION, '<' ) ) {
			do_action( self::SLUG . '_update', $current_version, self::VERSION );
		}
	}

	/**
	 * Run the version changes.
	 *
	 * @param string $current_version The current version.
	 * @param string $new_version     The new version.
	 *
	 * @return void
	 */
	public static function run_version_changes( $current_version, $new_version ) {
		require_once self::UPDATE_FUNCTIONS_FILE_PATH;

		// Get all changes between the old and new version.
		$changes = self::get_version_changes( $current_version, $new_version );

		// Get any previously applied changes.
		$applied_changes = get_option( self::SLUG . '_applied_changes', array() );

		try {
			// Run the changes.
			foreach ( $changes as $change ) {
				// If the change has already been applied, skip it.
				if ( in_array( $change, $applied_changes, true ) ) {
					continue;
				}

				do_action( self::SLUG . '_pre_update_' . $change, $current_version, $new_version );
				call_user_func( $change );
				do_action( self::SLUG . '_post_update_' . $change, $current_version, $new_version );

				// Add the change to the applied changes.
				$applied_changes[] = $change;
			}

			// Update the applied changes in the database.
			update_option( self::SLUG . '_applied_changes', $applied_changes, false );
		}
		catch (Exception $e) {
			// Log the error.
			$plugin_name = self::PLUGIN_NAME;
			error_log( "Error when updating {$plugin_name}: " . $e->getMessage() );
		}

		// Update the version in the database.
		update_option( self::SLUG . '_version', $new_version, false );
	}

	/**
	 * Get the version changes between two versions.
	 *
	 * @param string $current_version The old version.
	 * @param string $new_version The new version.
	 *
	 * @return array
	 */
	private static function get_version_changes( $current_version, $new_version ) {
		$changes = array();

		foreach ( self::$version_changes as $version => $version_changes ) {
			if ( version_compare( $current_version, $version, '<' ) && version_compare( $new_version, $version, '>=' ) ) {
				$changes = array_merge( $changes, $version_changes );
			}
		}

		return $changes;
	}

}
