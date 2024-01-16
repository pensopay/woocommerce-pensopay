<?php

/**
 * Class WC_PensoPay_Extra
 *
 * Used to add an extra gateway with customizable payment methods. Can be used i.e. in addition to the main instance when embedded payments are enabled but you still
 * want to offer Dankort-betalinger for NETS customers etc.
 */
class WC_PensoPay_Extra extends WC_PensoPay_Instance {

	public $main_settings = null;

	public function __construct() {
		parent::__construct();

		// Get gateway variables
		$this->id = 'pensopay-extra';

		$this->method_title = 'Pensopay - Extra';

		$this->setup();

		$this->title       = $this->s( 'title' );
		$this->description = $this->s( 'description' );

		add_filter( 'woocommerce_pensopay_cardtypelock_' . $this->id, [ $this, 'filter_cardtypelock' ] );
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
			'enabled'        => [
				'title'   => __( 'Enable', 'woo-pensopay' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Extra Pensopay gateway', 'woo-pensopay' ),
				'default' => 'no'
			],
			'_Shop_setup'    => [
				'type'  => 'title',
				'title' => __( 'Shop setup', 'woo-pensopay' ),
			],
			'title'          => [
				'title'       => __( 'Title', 'woo-pensopay' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woo-pensopay' ),
				'default'     => __( 'Pensopay', 'woo-pensopay' )
			],
			'description'    => [
				'title'       => __( 'Customer Message', 'woo-pensopay' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'woo-pensopay' ),
				'default'     => __( 'Pay', 'woo-pensopay' )
			],
			'cardtypelock'   => [
				'title'       => __( 'Payment methods', 'woo-pensopay' ),
				'type'        => 'text',
				'description' => __( 'Default: creditcard. Type in the cards you wish to accept (comma separated). See the valid payment types here: <b>http://tech.quickpay.net/appendixes/payment-methods/</b>', 'woo-pensopay' ),
				'default'     => 'creditcard',
			],
			'pensopay_icons' => [
				'title'             => __( 'Credit card icons', 'woo-pensopay' ),
				'type'              => 'multiselect',
				'description'       => __( 'Choose the card icons you wish to show next to the Pensopay payment option in your shop.', 'woo-pensopay' ),
				'desc_tip'          => true,
				'class'             => 'wc-enhanced-select',
				'css'               => 'width: 450px;',
				'custom_attributes' => [
					'data-placeholder' => __( 'Select icons', 'woo-pensopay' )
				],
				'default'           => '',
				'options'           => WC_PensoPay_Settings::get_card_icons(),
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
		return $this->s( 'cardtypelock' );
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
			$icon = '';

			$icons = $this->s( 'pensopay_icons' );

			if ( ! empty( $icons ) ) {
				$icons_maxheight = $this->gateway_icon_size();

				foreach ( $icons as $key => $item ) {
					$icon .= $this->gateway_icon_create( $item, $icons_maxheight );
				}
			}
		}

		return $icon;
	}
}
