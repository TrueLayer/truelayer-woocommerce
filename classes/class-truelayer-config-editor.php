<?php
/**
 * Config Editor Class file.
 *
 * @package TrueLayer_For_WooCommerce/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The TrueLayer Config Editor class
 */
class TrueLayer_Config_Editor {
	/**
	 * The path to the file you want to edit.
	 *
	 * @var string
	 */
	private $config_path;

	/**
	 * The path to the backup file that will be created.
	 *
	 * @var string
	 */
	private $backup_path;

	/**
	 * Class constructor to setup the class.
	 *
	 * @param string $path The path to the file you want to edit. Defaults to wp-config.php.
	 * @param string $backup_path The path to the backup file. Defaults to wp-config-backup-{$timestamp}.php. Where the timestamp is the current unix timestamp.
	 */
	public function __construct( $path = '', $backup_path = '' ) {
		$timestamp         = time();
		$this->config_path = empty( $path ) ? ABSPATH . 'wp-config.php' : $path;
		$this->backup_path = empty( $backup_path ) ? ABSPATH . "wp-config-backup-{$timestamp}.php" : $backup_path;
	}

	/**
	 * Add a specific key with a value to the wp-config.php file as a definition.
	 *
	 * @param string $key_name The name of the key to add.
	 * @param string $key_value The value of the key to add.
	 *
	 * @return bool True if the key was added, false if it was not.
	 */
	public function add_key( $key_name, $key_value ) {
		$key_name  = $this->sanitize_key_name( $key_name );
		$key_value = $this->sanitize_key_value( $key_value );

		// Create a backup for the config file first.
		$backup_result = $this->create_backup();

		// If the backup failed, then we cannot continue, since it might mean the write protection of the folder is on.
		if ( ! $backup_result ) {
			TrueLayer_Logger::log( "Failed to create a backup file from the original. Original file: $this->config_path. Backup path: $this->backup_path" );
			return false; // Backup failed
		}

		$config_data = file( $this->config_path, FILE_IGNORE_NEW_LINES );

		// Check if key already exists
		foreach ( $config_data as $line ) {
			if ( strpos( $line, $key_name ) !== false ) {
				TrueLayer_Logger::log( "A key with the name $key_name already exists in the file $this->config_path." );
				return false; // Key already exists
			}
		}

		// Find the place to add the new key
		$insert_at = 0;
		foreach ( $config_data as $index => $line ) {
			// Try to insert the new key after the nonce salt definition
			if ( strpos( $line, 'NONCE_SALT' ) !== false ) {
				$insert_at = $index + 1;
				break;
			}

			// If we did not find the nonce salt definition, then test against the "stop editing" comment
			if ( strpos( $line, '/* That\'s all, stop editing! Happy publishing. */' ) !== false ) {
				$insert_at = $index;
				break;
			}
		}

		// Insert the new key
		array_splice( $config_data, $insert_at, 0, "define( '$key_name', '$key_value' );" );

		return $this->save_config( $config_data );
	}

	/**
	 * Remove a specific key from the wp-config.php file.
	 *
	 * @param string $key_name The name of the key to remove.
	 *
	 * @return bool True if the key was removed, false if it was not.
	 */
	public function remove_key( $key_name ) {
		$config_data = file( $this->config_path, FILE_IGNORE_NEW_LINES );

		// Find the key and remove it
		foreach ( $config_data as $index => $line ) {
			if ( strpos( $line, $key_name ) !== false ) {
				unset( $config_data[ $index ] );
				return $this->save_config( $config_data );
			}
		}

		TrueLayer_Logger::log( "The key was not found when removing the definition. Filename: $this->config_path. Definition: $key_name." );
		return false; // Key not found.
	}

	/**
	 * Saves the config data to the wp-config.php file.
	 *
	 * @param array $config_data The config data to save.
	 *
	 * @return bool True if the config was saved, false if it was not.
	 */
	protected function save_config( $config_data ) {
		$temp_file = $this->config_path . '.tmp';

		// Write to a temporary file first
		$write_success = file_put_contents( $temp_file, implode( PHP_EOL, $config_data ) );

		if ( $write_success === false ) {
			TrueLayer_Logger::log( "Writing to a temporary file failed when adding the definition. Filename: $temp_file." );
			return false; // Writing to temp file failed
		}

		// Now rename the temporary file to replace the original config
		return rename( $temp_file, $this->config_path );
	}

	/**
	 * Create a backup of the wp-config.php file.
	 *
	 * @return bool True if the backup was created, false if it was not.
	 */
	protected function create_backup() {
		return copy( $this->config_path, $this->backup_path );
	}

	/**
	 * Sanitize and clean the key name
	 *
	 * @param string $key_name The name of the key to sanitize.
	 *
	 * @return string The sanitized key name.
	 */
	protected function sanitize_key_name( $key_name ) {
		// Ensure uppercase.
		$key_name = strtoupper( $key_name );

		// Remove all non-alphanumeric characters
		$key_name = preg_replace( '/[^A-Z0-9_]/', '', $key_name );

		return $key_name;
	}

	/**
	 * Sanitize and clean the key value
	 *
	 * @param string $key_value The value of the key to sanitize.
	 *
	 * @return string The sanitized key value.
	 */
	protected function sanitize_key_value( $key_value ) {
		// Sanitize the string to be used in a PHP string enclosed by single quotes.
		$key_value = str_replace( '\'', "", $key_value );

		return $key_value;
	}
}
