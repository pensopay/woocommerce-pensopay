<?php

use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;

/**
 * Class WC_PensoPay_Blocks
 *
 * Loads WC blocks related logic
 */
class WC_PensoPay_Blocks_Checkout extends WC_PensoPay_Module {

    /**
     * @return void
     */
    public function hooks() {
        add_action( 'woocommerce_blocks_loaded', [ $this, 'check_blocks_support' ] );

        // Stylesheets
        add_action( 'wp_print_footer_scripts', [ $this, 'maybe_apply_block_styles' ], 1 );
        add_action( 'admin_print_scripts', [ $this, 'maybe_apply_block_styles' ], 5 );
    }

    /**
     * Loads the gateway specific stylesheet if the pensopay-blocks-integration script is loaded
     * @return void
     */
    public function maybe_apply_block_styles(): void {
        if ( wp_script_is( 'pensopay-blocks-integration', 'enqueued' ) ) {
            // Enqueue the stylesheet only if the JavaScript file is enqueued
            wp_enqueue_style( 'pensopay-blocks-integration-styles' );
        }
    }

    /**
     * Checks if the current environment supports blocks integration.
     *
     * @return void
     */
    public function check_blocks_support(): void {
        if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
            include_once WC_PP()->plugin_path( '/classes/instances/blocks/pensopay-block.php' );

            add_action( 'woocommerce_blocks_payment_method_type_registration', [ $this, 'register_gateway_blocks' ] );
        }
    }

    /**
     * Registers gateway blocks for the specified payment method registry.
     *
     * @param PaymentMethodRegistry $payment_method_registry The payment method registry to register gateway blocks for.
     *
     * @return void
     */
    public function register_gateway_blocks( PaymentMethodRegistry $payment_method_registry ): void {

        $this->register_module_block_settings();

        foreach ( wc()->payment_gateways()->payment_gateways() as $payment_gateway ) {
            if ( WC_PensoPay_Helper::is_plugin_gateway( $payment_gateway ) ) {
                $payment_method_registry->register( new WC_PensoPay_Gateway_Block( $payment_gateway ) );
            }
        }
    }

    public function register_module_block_settings(): void {
        if ( class_exists( 'Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry' ) ) {
            Automattic\WooCommerce\Blocks\Package::container()
                ->get( Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry::class )
                ->add( 'pensopay-plugin', $this->get_plugin_settings_data() );
        }
    }

    /**
     * @return array|array[]
     */
    protected function get_plugin_settings_data(): array {
        return [
            'gateways' => array_values( array_map( static fn( $gateway ) => $gateway->id, WC_PensoPay_Helper::get_plugin_gateways() ) )
        ];
    }
}
