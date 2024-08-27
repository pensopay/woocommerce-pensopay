<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Automattic\WooCommerce\Container;

if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
    class WC_PensoPay_Gateway_Block extends AbstractPaymentMethodType {

        protected string $settings_key;

        protected WC_PensoPay $gateway;

        public function __construct( WC_PensoPay $gateway ) {
            $this->gateway = $gateway;
            $this->name    = 'payment-gateway-' . $this->gateway->id;
        }

        /**
         * @return void
         */
        public function initialize() {
            $this->settings = $this->gateway->settings;
        }

        /**
         * Retrieves the script handles for the payment method.
         *
         * @return string[] An array of script handles.
         */
        public function get_payment_method_script_handles() {
            $dependencies = [
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
                'wp-hooks'
            ];

            wp_register_script( 'pensopay-blocks-integration', WC_PP()->plugin_url( 'assets/javascript/checkout-blocks.js' ), $dependencies, null, [ 'in_footer' => true ] );
            wp_register_style( 'pensopay-blocks-integration-styles', WC_PP()->plugin_url( 'assets/stylesheets/checkout-blocks.css' ), [], null );

            return [ 'pensopay-blocks-integration' ];
        }

        public function is_active() {
            return $this->gateway->is_available();
        }

        public function get_payment_method_data() {
            return [
                'label'       => $this->gateway->get_title(),
                'description' => $this->gateway->description,
                'supports'    => $this->gateway->supports,
                'icon'        => $this->gateway->get_icon()
            ];
        }
    }
}
