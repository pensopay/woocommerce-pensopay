<?php

class WC_PensoPay_PayPal extends WC_PensoPay_Instance {

	public $main_settings = null;

	public function __construct() {
		parent::__construct();

		// Get gateway variables
		$this->id = 'pensopay_paypal';

		$this->method_title = 'Pensopay - PayPal';

		$this->setup();

		$this->title       = $this->s( 'title' );
		$this->description = $this->s( 'description' );

		add_filter( 'woocommerce_pensopay_cardtypelock_pensopay_paypal', [ $this, 'filter_cardtypelock' ] );
		add_filter( 'woocommerce_pensopay_transaction_params_basket', [ $this, 'filter_basket_items' ], 30, 2 );
		add_filter( 'woocommerce_pensopay_transaction_params_shipping_row', [ $this, 'filter_shipping_row' ], 30, 2 );
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
				'label'   => __( 'Enable PayPal payment', 'woo-pensopay' ),
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
				'default'     => __( 'PayPal', 'woo-pensopay' )
			],
			'description' => [
				'title'       => __( 'Customer Message', 'woo-pensopay' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'woo-pensopay' ),
				'default'     => __( 'Pay with PayPal', 'woo-pensopay' )
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
		return 'paypal';
	}

	/**
	 * @param array $items
	 * @param WC_PensoPay_Order $order
	 *
	 * @return array
	 */
	public function filter_basket_items( $items, $order ) {
		if ( $order->get_payment_method() === $this->id ) {
			$items = [];
		}

		return $items;
	}

	/**
	 * FILTER: apply_gateway_icons function.
	 *
	 * Sets gateway icons on frontend
	 *
	 * @access public
	 * @return void
	 */
	public function apply_gateway_icons( $icon, $id ) {
		if ( $id == $this->id ) {
			$icon = $this->gateway_icon_create( 'paypal', $this->gateway_icon_size() );
		}

		return $icon;
	}

    /**
     * This will solve a bug where the basket isn't sent to gateway but shipping is, effectively canceling due to
     * amount mismatch.
     *
     * @param array $data
     *
     * @return array
     */
    public function filter_shipping_row( $data, $order ) {
        if ( $order->get_payment_method() === $this->id ) {
            return [];
        }
        return $data;
    }
}
