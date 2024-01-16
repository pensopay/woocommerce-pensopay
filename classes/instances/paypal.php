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
		add_filter( 'woocommerce_pensopay_transaction_params_basket', [ $this, '_return_empty_array' ], 30, 2 );
		add_filter( 'woocommerce_pensopay_transaction_params_shipping_row', [ $this, '_return_empty_array' ], 30, 2 );
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
	public function filter_cardtypelock(): string {
		return 'paypal';
	}

	/**
	 * @param array $items
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public function _return_empty_array( array $items, WC_Order $order ): array {
		if ( $order->get_payment_method() === $this->id ) {
			$items = [];
		}

		return $items;
	}

	/**
	 * Sets gateway icons on frontend
	 *
	 * @param $icon
	 * @param $id
	 *
	 * @return string
	 */
	public function apply_gateway_icons( $icon, $id ) {
		if ( $id === $this->id ) {
			$icon = $this->gateway_icon_create( 'paypal', $this->gateway_icon_size() );
		}

		return $icon;
	}
}
