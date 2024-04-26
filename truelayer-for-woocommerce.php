<?php // phpcs:ignore
/**
 * Plugin Name: TrueLayer for WooCommerce
 * Plugin URI: https://krokedil.com/product/truelayer-for-woocommerce/
 * Description: TrueLayer for WooCommerce.
 * Author: Krokedil
 * Author URI: https://krokedil.com/
 * Version: 1.4.2
 * Text Domain: truelayer-for-woocommerce
 * Domain Path: /languages
 *
 * WC requires at least: 6.0.0
 * WC tested up to: 8.3.0
 *
 * Copyright (c) 2022-2023 Krokedil
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Constants
 */
define( 'TRUELAYER_WC_MAIN_FILE', __FILE__ );
define( 'TRUELAYER_WC_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'TRUELAYER_WC_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'TRUELAYER_WC_PLUGIN_VERSION', '1.4.2' );

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
		 * Reference to the install class.
		 *
		 * @var TrueLayer_Install
		 */
		public $install;


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
			add_action( 'plugins_loaded', array( $this, 'init' ) );
		}

		/**
		 * Init the plugin.
		 */
		public function init() {
			if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
				return;
			}

			// Include the autoloader from composer. If it fails, we'll just return and not load the plugin. But an admin notice will show to the merchant.
			if ( ! $this->init_composer() ) {
				return;
			}


			load_plugin_textdomain( 'truelayer-for-woocommerce', false, plugin_basename( __DIR__ ) . '/languages' );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
			$this->include_files();
		}

		/**
		 * Try to load the autoloader from Composer.
		 *
		 * @return mixed
		 */
		public function init_composer() {
			$autoloader = TRUELAYER_WC_PLUGIN_PATH . '/vendor/autoload.php';

			if ( ! is_readable( $autoloader ) ) {
				self::missing_autoloader();
				return false;
			}

			$autoloader_result = require $autoloader;
			if ( ! $autoloader_result ) {
				return false;
			}

			return $autoloader_result;
		}

		/**
		 * Print error message if the composer autoloader is missing.
		 *
		 * @return void
		 */
		protected static function missing_autoloader() {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( // phpcs:ignore
					esc_html__( 'Your installation of TrueLayer for WooCommerce is not complete. If you installed this plugin directly from Github please refer to the readme.dev.txt file in the plugin.', 'truelayer-for-woocommerce' )
				);
			}
			add_action(
				'admin_notices',
				function () {
					?>
				<div class="notice notice-error">
					<p>
						<?php echo esc_html__( 'Your installation of TrueLayer for WooCommerce is not complete. If you installed this plugin directly from Github please refer to the readme.dev.txt file in the plugin.', 'truelayer-for-woocommerce' ); ?>
					</p>
				</div>
				<?php
				}
			);
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
			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/class-truelayer-config-editor.php';
			include_once TRUELAYER_WC_PLUGIN_PATH . '/classes/class-truelayer-install.php';

			$this->api     = new Truelayer_API();
			$this->install = new TrueLayer_Install();
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
