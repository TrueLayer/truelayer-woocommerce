<?php
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use PHPUnit\Framework\TestCase;

global $settings;
$settings = array();



class TrueLayerEncryptionTest extends TestCase {
	private $settings = array();

	// Set the definition before all tests.
	public static function setUpBeforeClass(): void {
		// Add a definition for the encryption key.
		$key = Key::createNewRandomKey();
		define( 'TRUELAYER_KEY', $key->saveToAsciiSafeString() );

		// Set up the settings array.
		$settings = array(
			// Some settings that should not be encrypted.
			'testmode'                             => 'yes',
			'available_eur_countries'              => array( 'FR', 'IE', 'LT', 'NL', 'ES' ),
			// All the settings that should be encrypted.
			'truelayer_sandbox_client_private_key' => Crypto::encrypt( 'test_sandbox_key', $key ),
			'truelayer_sandbox_client_secret'      => Crypto::encrypt( 'test_sandbox_secret', $key ),
			'truelayer_sandbox_client_certificate' => Crypto::encrypt( 'test_sandbox_cert', $key ),
			'truelayer_client_private_key'         => Crypto::encrypt( 'test_prod_key', $key ),
			'truelayer_client_secret'              => Crypto::encrypt( 'test_prod_secret', $key ),
			'truelayer_client_certificate'         => Crypto::encrypt( 'test_prod_cert', $key ),
		);

		// Set the global mock_settings variable.
		global $mock_settings;
		$mock_settings['woocommerce_truelayer_settings'] = $settings;

		// Include the class we are testing.
		include_once PLUGIN_ROOT . '/classes/class-truelayer-encryption.php';
	}

	protected function setUp(): void {
		// Set the global settings variable.
		global $mock_settings;
		$this->settings = $mock_settings['woocommerce_truelayer_settings'];
	}

	// Remove the definition after the test has run.
	protected function tearDown(): void {
		unset( $GLOBALS['settings'] );
	}

	public function test_get_instance() {
		$instance = Truelayer_Encryption::get_instance();
		$this->assertInstanceOf( Truelayer_Encryption::class, $instance );
	}

	public function test_can_encrypt_value() {
		$instance = Truelayer_Encryption::get_instance();
		$result   = $instance->encrypt( 'test' );

		$this->assertNotEquals( 'test', $result );
		$this->assertNotEquals( '', $result );
	}

	public function test_can_decrypt_value() {
		$instance  = Truelayer_Encryption::get_instance();
		$encrypted = $instance->encrypt( 'test' );
		$result    = $instance->decrypt( $encrypted );

		$this->assertEquals( 'test', $result );
	}

	public function test_encrypt_returns_empty_string_if_value_is_empty() {
		$instance = Truelayer_Encryption::get_instance();
		$result   = $instance->encrypt( '' );

		$this->assertEquals( '', $result );
	}

	public function test_encrypt_returns_value_if_value_is_not_string() {
		$instance = Truelayer_Encryption::get_instance();
		$result   = $instance->encrypt( array() );

		$this->assertEquals( array(), $result );
	}

	public function test_decrypt_returns_empty_string_if_value_is_empty() {
		$instance = Truelayer_Encryption::get_instance();
		$result   = $instance->decrypt( '' );

		$this->assertEquals( '', $result );
	}

	public function test_decrypt_returns_value_if_value_is_not_string() {
		$instance = Truelayer_Encryption::get_instance();
		$result   = $instance->decrypt( array() );

		$this->assertEquals( array(), $result );
	}

	public function test_can_encrypt_settings() {
		$instance = Truelayer_Encryption::get_instance();
		$result   = $instance->encrypt_values( $this->settings );

		// Check that the settings that should be encrypted are encrypted.
		$this->assertNotEquals( 'test_sandbox_key', $result['truelayer_sandbox_client_private_key'] );
		$this->assertNotEquals( 'test_sandbox_secret', $result['truelayer_sandbox_client_secret'] );
		$this->assertNotEquals( 'test_sandbox_cert', $result['truelayer_sandbox_client_certificate'] );
		$this->assertNotEquals( 'test_prod_key', $result['truelayer_client_private_key'] );
		$this->assertNotEquals( 'test_prod_secret', $result['truelayer_client_secret'] );
		$this->assertNotEquals( 'test_prod_cert', $result['truelayer_client_certificate'] );

		// Check that the settings did not get returned as empty values.
		$this->assertNotEquals( '', $result['truelayer_sandbox_client_private_key'] );
		$this->assertNotEquals( '', $result['truelayer_sandbox_client_secret'] );
		$this->assertNotEquals( '', $result['truelayer_sandbox_client_certificate'] );
		$this->assertNotEquals( '', $result['truelayer_client_private_key'] );
		$this->assertNotEquals( '', $result['truelayer_client_secret'] );
		$this->assertNotEquals( '', $result['truelayer_client_certificate'] );

		// Check that the settings that should not be encrypted are not encrypted.
		$this->assertEquals( 'yes', $result['testmode'] );
		$this->assertEquals( array( 'FR', 'IE', 'LT', 'NL', 'ES' ), $result['available_eur_countries'] );
	}

	public function test_can_decrypt_settings() {
		$instance = Truelayer_Encryption::get_instance();

		// Decrypt the settings.
		$decrypted_settings = $instance->decrypt_values( $this->settings );

		// Check that the settings that should be encrypted are decrypted.
		$this->assertEquals( 'test_sandbox_key', $decrypted_settings['truelayer_sandbox_client_private_key'] );
		$this->assertEquals( 'test_sandbox_secret', $decrypted_settings['truelayer_sandbox_client_secret'] );
		$this->assertEquals( 'test_sandbox_cert', $decrypted_settings['truelayer_sandbox_client_certificate'] );
		$this->assertEquals( 'test_prod_key', $decrypted_settings['truelayer_client_private_key'] );
		$this->assertEquals( 'test_prod_secret', $decrypted_settings['truelayer_client_secret'] );
		$this->assertEquals( 'test_prod_cert', $decrypted_settings['truelayer_client_certificate'] );

		// Check that the settings that are not encryptable have not changed.
		$this->assertEquals( 'yes', $decrypted_settings['testmode'] );
		$this->assertEquals( array( 'FR', 'IE', 'LT', 'NL', 'ES' ), $decrypted_settings['available_eur_countries'] );
	}
}
