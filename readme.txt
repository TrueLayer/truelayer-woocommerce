=== TrueLayer for WooCommerce ===
Contributors: krokedil
Tags: ecommerce, e-commerce, woocommerce, truelayer, payments, instant payments, refunds, open banking
Requires at least: 5.0
Tested up to: 6.4.1
Requires PHP: 7.4
WC requires at least: 6.0.0
WC tested up to: 8.3.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Stable tag: 1.4.2

TrueLayer for WooCommerce is a plugin that extends WooCommerce, allowing you to take payments via TrueLayer.


== DESCRIPTION ==
TrueLayer for WooCommerce is a plugin that allows you to accept quick and easy payments via your chosen bank. Direct account-to-account payments and refunds that settle in seconds.

= Key benefits of instant bank payments: =
- **Instant payments and refunds**
Direct account-to-account payments and refunds that settle in seconds.
- **Virtually eliminate payment fraud**
With bank authentication built into the payment process, fraud is near impossible.
- **Smoother checkout experiences**
Offer checkout experiences with less steps, no manual data entry, and with higher payment acceptance rates.
- **Reduce transaction and operational costs**
Remove payment intermediaries and their associated costs.

= Key benefits of TrueLayer for WooCommerce: =
- **Enable instant bank payments at checkout**
Add account-to-account payments as a payment method at checkout, providing more choice and a more secure payment option to customers.
- **Simple setup**
Plug-and-play setup. No manual API integration required.
- **Get started immediately**
Simply drop our ready-made plugin into a WooCommerce webshop to get started.
- **Customisable**
Customise the payment experience to match your brand and deliver an environment that your customers know and trust.

= Get started =
To get started with TrueLayer, you need to [sign up](https://console.truelayer.com/) for TrueLayer, and get the data from your TrueLayer console.

Once you obtain and customize your TrueLayer credentials, go to you WooCommerce admin page and activate TrueLayer for WooCommerce in the WooCommerce > Plugins.
In the next step, either go to the plugin settings via the Settings link under the listed plugin, or via WooCommerce > Settings > Payments > TrueLayer.

More information on how to get started can be found in the [plugin documentation](https://docs.krokedil.com/truelayer-for-woocommerce/).


== INSTALLATION	 ==
1. Download and unzip the latest release zip file.
2. If you use the WordPress plugin uploader to install this plugin skip to step 4.
3. Upload the entire plugin directory to your /wp-content/plugins/ directory.
4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
5. Go WooCommerce Settings --> Payment Gateways and configure your TrueLayer settings.
6. Read more about the configuration process in the [plugin documentation](https://docs.krokedil.com/truelayer-for-woocommerce/).

== CHANGELOG ==
= 2024.04.26        - version 1.4.2 =
* Fix               - Fixes issue with billing state field being mandatory

= 2023.11.30        - version 1.4.1 =
* Tweak             - Updated Wordpress.org assets.
* Tweak             - Bump supported versions.

= 2023.10.26        - version 1.4.0 =
* Feature           - Added automatic migration functionality to be able to make necessary changes when a new version is installed.
* Enhancement       - Added fallbacks and deeper error handling when the encryption key is being set to the wp-config file.
* Enhancement       - Improved the logic behind the encryption and decryption of stored API credentials to reduce the amount of times we encrypt/decrypt these settings.
* Fix               - Fixes an issue with trying to re-encode settings that where already encoded when a new encryption key is created.
* Fix               - Fixes creating multiple encryption keys when the plugin is first installed.

= 2023.09.19        - version 1.3.0 =
* Feature           - Add feature to set a custom release channel for the payment in TrueLayer. This can be set on the settings page for the plugin in the setting "Release channel". If left empty no release channel will be sent to TrueLayer.
* Fix               - Fixed some deprecation warnings for PHP version 8.2+.

= 2023.09.15        - version 1.2.1 =
* Fix               - Remove sensitive data from logs

= 2023.05.08        - version 1.2.0 =
* Feature           - Added support for embedded payment page for the checkout as a option.
* Feature           - Add customer address to the create payment request if we have any.
* Enhancement       - We now save the customers date of birth to the order under the meta filed, _truelayer_user_birth_date. This can be filtered using the truelayer_birth_date_field filter.

= 2023.02.14        - version 1.1.0 =
* Feature           - Add support for sending different customer types in create payment request. New setting "Banking Provider Types" introduced to control this feature.
* Tweak             - Bumped minimum required PHP version to 7.4.
* Fix               - Fix PHP8 related error that could happen during plugin activation when TrueLayer config key was created.

= 2022.09.30        - version 1.0.4 =
* Fix               - Do not try to load plugin if WooCommerce isn't activated. Could cause fatal error.
* Fix               - Remove enqueuing of unused admin css file.

= 2022.09.26        - version 1.0.3 =
* Tweak             - GitHub/SVN tweak.

= 2022.09.26        - version 1.0.2 =
* Tweak             - Supports WC 6.9.4.

= 2022.07.29        - version 1.0.1 =
* Tweak				- Logging improvements.
* Fix				- Use untampered received body in callback verifying.
* Fix				- Add logic for trying path with and without trailing slash in callback verifying.

= 2022.07.18        - version 1.0.0 =
* Feature			- Adds support for EUR.
* Feature			- Adds support for selecting which EUR countries TrueLayer should be available for in checkout.
* Feature			- Stores bearer token & TrueLayer API credentials encrypted in db. Uses [defuse/php-encryption](https://github.com/defuse/php-encryption) for encryption/decryption.
* Tweak				- Refunds now executed via payments/{id}/refunds endpoint instead of /payouts.
* Tweak				- Improved messaging in order note when refund fails.
* Tweak				- Uses Ramsey lib to generate UUID.
* Tweak				- Updates TrueLayer signing SDK to v0.1.0.
* Tweak				- Adds verifying webhook callbacks via TrueLayer signing SDK.
* Tweak				- Adds TL-Agent to headers in requests sent to TrueLayer.
* Tweak				- Saves TL-Trace-Id to plugin log.
* Tweak				- Remove access_token from plugin log when returned in get token request.

= 2022.05.30        - version 0.9.3 =
* Tweak				- Logging tweaks.
* Fix				- Use production client id and secret when retrieve token in requests towards production environment.

= 2022.05.26        - version 0.9.2 =
* Fix				- Adds missing vendor folder to plugin.

= 2022.05.25        - version 0.9.1 =
* Tweak				- Remove the unused Beneficiary Type from plugin settings field.
* Fix				- User order customer data for user name sent in create payment request.

= 2022.05.24        - version 0.9.0 =
* Initial release.
