<?php
/**
 * Main checkout file.
 *
 * @package TrueLayer_For_WooCommerce/Classes/Checkout
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Truelayer_Checkout class.
 */
class Truelayer_Checkout {
    /**
     * Truelayer_Checkout constructor.
     */
    public function __construct() {
        add_filter('woocommerce_get_country_locale', array( $this, 'make_state_required' ), 10, 1);
    }

    /**
     *
     * Filter out default WooCommerce settings for country locale.
     * If state is defined and hidden it should stay optional, otherwise it should be required.
     *
     * @return array
     */
    public function make_state_required( $country_locale ) {

        //Hidden is not set if it's not true
        foreach( $country_locale AS $country => $settings ) {
            if( ! isset( $settings['state']['hidden'] ) || ( isset( $settings['state']['hidden'] ) && ! empty( $state_hidden = $settings['state']['hidden'] ) && ( false === $state_hidden ) ) ) {
                $country_locale[$country]['state']['required'] = true;
            }
        }

        return $country_locale;
    }
}
new Truelayer_Checkout();
