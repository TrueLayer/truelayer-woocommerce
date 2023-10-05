<?php
/**
 * Class for TrueLayer gateway settings.
 *
 * @package TrueLayer/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TrueLayer_Fields class.
 *
 * TrueLayer for WooCommerce settings fields.
 */
class TrueLayer_Fields {

	/**
	 * Returns the fields.
	 */
	public static function fields() {
		$settings = array(
			'enabled'                                   => array(
				'title'       => __( 'Enable/Disable', 'truelayer-for-woocommerce' ),
				'label'       => __( 'Enable TrueLayer', 'truelayer-for-woocommerce' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'yes',
			),
			'title'                                     => array(
				'title'       => __( 'Title', 'truelayer-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'truelayer-for-woocommerce' ),
				'default'     => __( 'Instant Bank Transfer', 'truelayer-for-woocommerce' ),
				'desc_tip'    => true,
			),
			'description'                               => array(
				'title'       => __( 'Description', 'truelayer-for-woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'truelayer-for-woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),

			'testmode'                                  => array(
				'title'   => __( 'Test mode', 'truelayer-for-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable test mode for TrueLayer', 'truelayer-for-woocommerce' ),
				'default' => 'yes',
			),

			'logging'                                   => array(
				'title'       => __( 'Logging', 'truelayer-for-woocommerce' ),
				'label'       => __( 'Log debug messages', 'truelayer-for-woocommerce' ),
				'type'        => 'checkbox',
				'description' => __( 'Save debug messages to the WooCommerce System Status log.', 'truelayer-for-woocommerce' ),
				'default'     => 'yes',
				'desc_tip'    => true,
			),

			// Beneficiary credentials.
			'truelayer_beneficiary_credentials'         => array(
				'title' => 'TrueLayer beneficiary credentials',
				'type'  => 'title',
			),
			'truelayer_beneficiary_account_holder_name' => array(
				'title'       => __( 'Account holder name', 'truelayer-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter your Truelayer beneficiary account holder name', 'truelayer-for-woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'truelayer_beneficiary_merchant_account_id' => array(
				'title'       => __( 'Merchant Account ID - GBP', 'truelayer-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter your TrueLayer GBP Merchant Account ID', 'truelayer-for-woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'truelayer_beneficiary_merchant_account_id_eur' => array(
				'title'       => __( 'Merchant Account ID - EUR', 'truelayer-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter your TrueLayer EUR Merchant Account ID', 'truelayer-for-woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'available_eur_countries'                   => array(
				'title'       => __( 'Available EUR countries', 'truelayer-for-woocommerce' ),
				'type'        => 'multiselect',
				'class'       => 'wc-enhanced-select',
				'description' => __( 'Select the countries that should be available in checkout if EUR is the selected currency.', 'truelayer-for-woocommerce' ),
				'options'     => array(
					'FR' => __( 'France', 'truelayer-for-woocommerce' ),
					'IE' => __( 'Ireland', 'truelayer-for-woocommerce' ),
					'LT' => __( 'Lithuania', 'truelayer-for-woocommerce' ),
					'NL' => __( 'Netherlands', 'truelayer-for-woocommerce' ),
					'ES' => __( 'Spain', 'truelayer-for-woocommerce' ),
				),
				'default'     => '',
				'desc_tip'    => true,
			),

			// Production credentials.
			'truelayer_credentials'                     => array(
				'title' => 'TrueLayer Production credentials',
				'type'  => 'title',
			),

			'truelayer_client_id'                       => array(
				'title'       => __( 'Client ID', 'truelayer-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter your TrueLayer Client ID', 'truelayer-for-woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'truelayer_client_secret'                   => array(
				'title'       => __( 'Client Secret', 'truelayer-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter your TrueLayer Client Secret', 'truelayer-for-woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),

			// KID - Certificate ID.
			'truelayer_client_certificate'              => array(
				'title'       => __( 'Client Certificate', 'truelayer-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter your TrueLayer Client Certificate', 'truelayer-for-woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'truelayer_client_private_key'              => array(
				'title'       => __( 'Client Private Key', 'truelayer-for-woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'Enter your TrueLayer Client Private Key', 'truelayer-for-woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),

			// Sandbox credentials.
			'truelayer_sandbox_credentials'             => array(
				'title' => 'TrueLayer Sandbox credentials',
				'type'  => 'title',
			),
			'truelayer_sandbox_client_id'               => array(
				'title'       => __( 'Sandbox Client ID', 'truelayer-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter your TrueLayer Sandbox Client ID', 'truelayer-for-woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'truelayer_sandbox_client_secret'           => array(
				'title'       => __( 'Sandbox Client Secret', 'truelayer-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter your TrueLayer Sandbox Client Secret', 'truelayer-for-woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			// Sandbox KID - Certificate ID.
			'truelayer_sandbox_client_certificate'      => array(
				'title'       => __( 'Sandbox Client Certificate', 'truelayer-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter your TrueLayer Sandbox Client Certificate', 'truelayer-for-woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'truelayer_sandbox_client_private_key'      => array(
				'title'       => __( 'Sandbox Client Private Key', 'truelayer-for-woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'Enter your TrueLayer Sandbox Client Private Key', 'truelayer-for-woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'truelayer_advance'                         => array(
				'title' => __( 'TrueLayer advanced settings', 'truelayer-for-woocommerce' ),
				'type'  => 'title',
			),

			'truelayer_banking_providers'               => array(
				'title'    => __( 'Banking Provider Types', 'truelayer-for-woocommerce' ),
				'type'     => 'multiselect',
				'options'  => array(
					'Retail'    => __( 'Retail', 'truelayer-for-woocommerce' ),
					'Business'  => __( 'Business', 'truelayer-for-woocommerce' ),
					'Corporate' => __( 'Corporate', 'truelayer-for-woocommerce' ),
				),
				'default'  => '',
				'desc_tip' => true,
			),

			'truelayer_release_channel'                     => array(
				'title'       => __( 'Release channel', 'truelayer-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter the release channel that you wish to use for TrueLayer', 'truelayer-for-woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),

			'truelayer_payment_page_type'               => array(
				'title'    => __( 'Payments Page Types', 'truelayer-for-woocommerce' ),
				'type'     => 'select',
				'options'  => array(
					'HPP'    => __( 'Hosted Payments Page', 'truelayer-for-woocommerce' ),
					'EPP'  => __( 'Embedded Payments Page', 'truelayer-for-woocommerce' ),
				),
				'default'  => 'HPP',
				'desc_tip' => true,
			),

		);

		return apply_filters( 'truelayer_gateway_settings', $settings );
	}
}
