<?php

class WC_PensoPay_MobilePay_Subscriptions extends WC_PensoPay_Instance {

	public $main_settings = null;

	public function __construct() {
		parent::__construct();

		// Get gateway variables
		$this->id = 'mobilepay-subscriptions';

		$this->method_title = 'PensoPay - MobilePay Subscriptions | Currently in BETA! Not suggested for live stores';

		$this->setup();

		$this->title       = $this->s( 'title' );
		$this->description = $this->s( 'description' );

        $this->supports = [
            'subscriptions',
            'subscription_cancellation',
            'subscription_reactivation',
            'subscription_suspension',
            'subscription_amount_changes',
            'subscription_date_changes',
            'subscription_payment_method_change_admin',
            'subscription_payment_method_change_customer',
            'refunds',
            'multiple_subscriptions'
        ];

		add_filter( 'woocommerce_pensopay_cardtypelock_mobilepay_subscriptions', [ $this, 'filter_cardtypelock' ] );
	}

	/**
	 * init_form_fields function.
	 *
	 * Initiates the plugin settings form fields
	 *
	 * @access public
	 * @return array
	 */
	public function init_form_fields() {
		$this->form_fields = [
			'enabled'     => [
				'title'   => __( 'Enable', 'woo-pensopay' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable MobilePay Subscription payment', 'woo-pensopay' ),
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
				'default'     => __( 'MobilePay Subscriptions', 'woo-pensopay' )
			],
			'description' => [
				'title'       => __( 'Customer Message', 'woo-pensopay' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'woo-pensopay' ),
				'default'     => __( 'Subscribe with your mobile phone', 'woo-pensopay' )
			]
		];
	}

    public function is_available() {
	    $is_available = parent::is_available();

        if( !$is_available || !class_exists( 'WC_Subscriptions_Product' ) || !is_checkout()) {
            return $is_available;
        }

        $bFound = false;
        foreach (WC()->cart->get_cart() as $item) {
            if (WC_Subscriptions_Product::is_subscription( $item['data']->get_id() )) {
                $bFound = true;
            }
        }

        return $bFound;
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
		return 'mobilepay-subscriptions';
	}
}
