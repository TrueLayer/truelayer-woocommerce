<?php
use PHPUnit\Framework\TestCase;

class TrueLayerConfigKeyTest extends TestCase {
	const CONFIG_PATH = 'tests/phpunit/fixtures/wp-config.php';
	const BACKUP_PATH = 'tests/phpunit/fixtures/wp-config-backup.php';
	const HAS_KEY_PATH = 'tests/phpunit/fixtures/wp-config-has-key.php';
	const NO_KEY_PATH = 'tests/phpunit/fixtures/wp-config-no-key.php';

	protected function setUp(): void {
		include_once PLUGIN_ROOT . '/classes/class-truelayer-config-editor.php';

		// Ensure the fixture files do not have a whitespace at the end of the file.
		$has_key_file = file_get_contents( self::HAS_KEY_PATH );
		$no_key_file  = file_get_contents( self::NO_KEY_PATH );
		file_put_contents( self::HAS_KEY_PATH, rtrim( $has_key_file ) );
		file_put_contents( self::NO_KEY_PATH, rtrim( $no_key_file ) );

		// Create a copy of the wp-config-no-key.php file to use as a fixture.
		copy( self::NO_KEY_PATH, self::CONFIG_PATH );
	}

	protected function tearDown(): void {
		@unlink( self::CONFIG_PATH );
		@unlink( self::BACKUP_PATH );
	}

	public function test_can_add_key() {
		$config_editor = new TrueLayer_Config_Editor( self::CONFIG_PATH, self::BACKUP_PATH );

		$config_editor->add_key( 'TEST_KEY', 'TEST_VALUE' );

		$this->assertFileEquals( self::CONFIG_PATH, self::HAS_KEY_PATH );
		// Backup should exist of the file that contains no key.
		$this->assertFileEquals( self::BACKUP_PATH, self::NO_KEY_PATH );
	}

	public function test_can_remove_key() {
		$config_editor = new TrueLayer_Config_Editor( self::CONFIG_PATH, self::BACKUP_PATH );

		$config_editor->add_key( 'TEST_KEY', 'TEST_VALUE' );
		$config_editor->remove_key( 'TEST_KEY' );

		$this->assertFileEquals( self::CONFIG_PATH, self::NO_KEY_PATH );
	}

	public function test_can_only_add_key_once() {
		$config_editor = new TrueLayer_Config_Editor( self::CONFIG_PATH, self::BACKUP_PATH );

		$config_editor->add_key( 'TEST_KEY', 'TEST_VALUE' );
		$config_editor->add_key( 'TEST_KEY', 'TEST_VALUE' );

		$this->assertFileEquals( self::CONFIG_PATH, self::HAS_KEY_PATH );
	}

	public function test_create_backup_error_handling() {
		// Mock the original class so we can force the create_backup() method to fail, but all other methods to function as normal.
		$config_editor = $this->getMockBuilder( 'TrueLayer_Config_Editor' )
			->setConstructorArgs( [ self::CONFIG_PATH, self::BACKUP_PATH ] )
			->onlyMethods( [ 'create_backup' ] )
			->getMock();

		// Force the create_backup() method to fail.
		$config_editor->expects( $this->once() )
			->method( 'create_backup' )
			->willReturn( false );

		// Try to add a key, which should fail.
		$config_editor->add_key( 'TEST_KEY', 'TEST_VALUE' );

		// The file should not have changed.
		$this->assertFileEquals( self::CONFIG_PATH, self::NO_KEY_PATH );
	}

	public function test_save_config_error_handling() {
		// Mock the original class so we can force the save_config() method to fail, but all other methods to function as normal.
		$config_editor = $this->getMockBuilder( 'TrueLayer_Config_Editor' )
			->setConstructorArgs( [ self::CONFIG_PATH, self::BACKUP_PATH ] )
			->onlyMethods( [ 'save_config' ] )
			->getMock();

		// Force the save_config() method to fail.
		$config_editor->expects( $this->once() )
			->method( 'save_config' )
			->willReturn( false );

		// Try to add a key, which should fail.
		$config_editor->add_key( 'TEST_KEY', 'TEST_VALUE' );

		// The file should not have changed.
		$this->assertFileEquals( self::CONFIG_PATH, self::NO_KEY_PATH );
	}

	public function test_key_and_value_sanitation() {
		$config_editor = new TrueLayer_Config_Editor( self::CONFIG_PATH, self::BACKUP_PATH );

		$config_editor->add_key( '?>TEST_KEY', 'TEST_VALUE\'' );

		$this->assertFileEquals( self::CONFIG_PATH, self::HAS_KEY_PATH );
	}
}
