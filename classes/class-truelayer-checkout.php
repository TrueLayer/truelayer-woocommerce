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
        add_action('woocommerce_after_checkout_validation', array( $this, 'validate_state_for_true_layer' ), 10, 2);
    }

    /**
     * Trow validation error if state field is empty and not hidden by default in WC only if chosen shipping method is TrueLayer
     * @param $fields
     * @param $errors
     * @return void
     */
    function validate_state_for_true_layer( $data, $errors ){
        $selected_country = $data[ 'billing_country' ] ?? '';
        $countries = WC()->countries->get_country_locale();
        if ( ! empty( $selected_country_settings =  $countries[$selected_country] ) &&
            ( ! isset( $selected_country_settings['state']['hidden'] )
                || (
                    ! empty( $state_hidden = $selected_country_settings['state']['hidden'] )
                    && ( false === $selected_country_settings )
                )
            )
            && 'truelayer' === WC()->session->get('chosen_payment_method')
            && empty( $data['billing_state'] ) )
        {
            $errors->add( 'validation', sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>' . __('Billing State', 'woocommerce') . '</strong>' ) );
        }
    }
}
new Truelayer_Checkout();
