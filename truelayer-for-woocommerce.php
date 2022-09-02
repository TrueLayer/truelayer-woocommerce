<?php // phpcs:ignore
/**
 * Plugin Name: TrueLayer for WooCommerce
 * Plugin URI: https://krokedil.com/product/truelayer-for-woocommerce/
 * Description: TrueLayer for WooCommerce.
 * Author: Krokedil
 * Author URI: https://krokedil.com/
 * Version: 1.0.1
 * Text Domain: truelayer-for-woocommerce
 * Domain Path: /languages
 *
 * WC requires at least: 6.0.0
 * WC tested up to: 6.7.0
 *
 * Copyright (c) 2022 Krokedil
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Key;
/**
 * Constants
 */
define( 'TRUELAYER_WC_MAIN_FILE', __FILE__ );
define( 'TRUELAYER_WC_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'TRUELAYER_WC_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'TRUELAYER_WC_PLUGIN_VERSION', '1.0.1' );

if ( ! class_exists( 'TrueLayer_For_WooCommerce' ) ) {
	/**
	 * Class Truelayer_For_WooCommerce
	 */
	class TrueLayer_For_WooCommerce {

		/**
		 * The reference the *Singleton* instance of this class.
		 *
		 * @var $instance
		 */
		protected static $instance;

		/**
		 *  Reference to API class.
		 *
		 * @var Truelayer_API
		 */
		public $api;


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
		 * Notices (array)
		 *
		 * @var array
		 */
		public $notices = array();

		/**
		 * Class constructor.
		 */
		protected function __construct() {
			add_action( 'truelayer_plugin_activated', array( $this, 'include_files_for_activation_hook' ) );
			add_action( 'plugins_loaded', array( $this, 'init' ) );
			register_activation_hook( __FILE__, array( $this, 'create_truelayer_key' ) );
			register_deactivation_hook( __FILE__, array( $this, 'delete_truelayer_key' ) );
		}
		/**
		 * Defines a TRUELAYER_KEY constant and saves it to wp-config.php on plugin activation.
		 *
		 * @return void|false
		 */
		public function create_truelayer_key() {
			if ( defined( 'TRUELAYER_KEY' ) ) {
				return;
			}
			do_action( 'truelayer_plugin_activated' );
			TrueLayer_Config_Keys::create_truelayer_key_and_save_to_wp_config();
		}

		/**
		 * Deletes Truelayer key from wp-config file on plugin deactivation
		 *
		 * @return void
		 */
		public function delete_truelayer_key() {
			if ( ! defined( 'TRUELAYER_KEY' ) ) {
				return;
			}
			TrueLayer_Config_Keys::delete_truelayer_key_from_wp_config();
		}

		/**
		 * Include files for actiivation hook.
		 */
		public function include_files_for_activation_hook() {
			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/class-truelayer-config-keys.php';
		}

		/**
		 * Init the plugin.
		 */
		public function init() {
			load_plugin_textdomain( 'truelayer-for-woocommerce', false, plugin_basename( __DIR__ ) . '/languages' );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
			$this->include_files();
		}

		/**
		 * Adds TrueLayer action links.
		 *
		 * @param array $links TrueLayer action link before filtering.
		 *
		 * @return array Filtered links.
		 */
		public function plugin_action_links( $links ) {
			$setting_link = $this->get_setting_link();
			$plugin_links = array(
				'<a href="' . $setting_link . '">' . __( 'Settings', 'truelayer-for-woocommerce' ) . '</a>',
				'<a href="https://krokedil.com/">' . __( 'Support', 'truelayer-for-woocommerce' ) . '</a>',
			);

			return array_merge( $plugin_links, $links );
		}

		/**
		 * Get setting link.
		 *
		 * @since 1.0.0
		 *
		 * @return string Setting link
		 */
		public function get_setting_link() {
			$section_slug = 'truelayer';

			$params = array(
				'page'    => 'wc-settings',
				'tab'     => 'checkout',
				'section' => $section_slug,
			);

			return add_query_arg( $params, 'admin.php' );
		}

		/**
		 * Includes the files for the plugin
		 *
		 * @return void
		 */
		public function include_files() {
			include_once TRUELAYER_WC_PLUGIN_PATH . '/vendor/autoload.php';
			include_once TRUELAYER_WC_PLUGIN_PATH . '/includes/truelayer-functions.php';
			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/class-truelayer-fields.php';
			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/class-truelayer-gateway.php';

			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/requests/class-truelayer-request.php';
			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/requests/class-truelayer-request-post.php';
			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/requests/class-truelayer-request-get.php';
			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/requests/post/class-truelayer-request-get-token.php';
			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/requests/post/class-truelayer-request-create-payment.php';
			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/requests/get/class-truelayer-request-get-payment-status.php';
			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/requests/get/class-truelayer-get-merchant-accounts.php';

			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/requests/order_management/class-truelayer-request-refunds.php';

			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/requests/helpers/class-truelayer-helper-order.php';
			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/requests/helpers/class-truelayer-helper-signing.php';
			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/requests/helpers/class-truelayer-helper-verifying.php';
			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/requests/helpers/class-truelayer-helper-hosted-payment-page-url.php';

			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/class-truelayer-confirmation.php';
			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/class-truelayer-callbacks.php';

			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/class-truelayer-api.php';
			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/class-truelayer-logger.php';
			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/class-truelayer-status.php';
			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/class-truelayer-assets.php';

			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/admin/class-wc-truelayer-banners.php';
			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/class-truelayer-encryption.php';
			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/admin/class-truelayer-admin-notices.php';
			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/class-truelayer-config-keys.php';

			$this->api = new Truelayer_API();
		}

	}
	TrueLayer_For_WooCommerce::get_instance();
}

/**
 * Main instance TrueLayer_For_WooCommerce.
 *
 * Returns the main instance of TrueLayer_For_WooCommerce.
 *
 * @return TrueLayer_For_WooCommerce
 */
function TrueLayer() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
	return TrueLayer_For_WooCommerce::get_instance();
}
