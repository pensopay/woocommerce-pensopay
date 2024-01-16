<?php

class WC_PensoPay_Google_Pay extends WC_PensoPay_Instance {

	public $main_settings = null;

	public function __construct() {
		parent::__construct();

		$this->supports = [
			'products',
			'subscriptions',
			'subscription_cancellation',
			'subscription_reactivation',
			'subscription_suspension',
			'subscription_amount_changes',
			'subscription_date_changes',
			'subscription_payment_method_change_admin',
			'subscription_payment_method_change_customer',
			'refunds',
			'multiple_subscriptions',
		];

        // Get gateway variables
        $this->id = 'pensopay_google_pay';

        $this->method_title = 'Pensopay - Google Pay';

		$this->setup();

		$this->title       = $this->s( 'title' );
		$this->description = $this->s( 'description' );

        add_filter( 'woocommerce_pensopay_cardtypelock_' . $this->id, [ $this, 'filter_cardtypelock' ] );

        add_filter( 'woocommerce_available_payment_gateways', [ $this, 'maybe_disable_gateway' ] );
        add_filter( 'woocommerce_pensopay_checkout_gateway_icon', [ $this, 'filter_icon' ] );
    }


	/**
	 * init_form_fields function.
	 *
	 * Initiates the plugin settings form fields
	 *
	 * @access public
	 * @return array
	 */
	public function init_form_fields(): void {
        $this->form_fields = [
            'enabled'     => [
                'title'   => __( 'Enable', 'woo-pensopay' ),
                'type'    => 'checkbox',
                'label'   => sprintf( __( 'Enable %s payment', 'woo-pensopay' ), 'Google Pay' ),
                'description' => sprintf(__( 'Works only in %s.', 'woo-pensopay' ), 'Chrome' ),
                'default' => 'no'
            ],
            '_Shop_setup' => [
                'type'  => 'title',
                'title' => __( 'Shop setup', 'woo-pensopay' ),
            ],
            'title'       => [
                'title'       => __( 'Title', 'woo-pensopay' ),
                'type'        => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', 'woo-pensopay' ),
                'default'     => __( 'Google Pay', 'woo-pensopay' )
            ],
            'description' => [
                'title'       => __( 'Customer Message', 'woo-pensopay' ),
                'type'        => 'textarea',
                'description' => __( 'This controls the description which the user sees during checkout.', 'woo-pensopay' ),
                'default'     => sprintf( __( 'Pay with %s', 'woo-pensopay' ), 'Google Pay' )
            ],
        ];
    }


	/**
	 * filter_cardtypelock function.
	 *
	 * Sets the cardtypelock
	 *
	 * @access public
	 * @return string
	 */
	public function filter_cardtypelock() {
		return 'google-pay';
	}

	/**
	 * @param $icon
	 *
	 * @return string
	 */
	public function filter_icon( $icon ) {
		if ( 'google_pay' === $icon ) {
			$icon = 'google-pay';
		}

		return $icon;
	}

	/**
	 * @param array $gateways
	 */
	public function maybe_disable_gateway( $gateways ) {
		if ( isset( $gateways[ $this->id ] ) && is_checkout() && ! WC_PensoPay_Helper::is_browser( 'chrome' ) ) {
			unset( $gateways[ $this->id ] );
		}

		return $gateways;
	}
}
