<?php

class WC_PensoPay_Trustly extends WC_PensoPay_Instance {

	public $main_settings = null;

	public function __construct() {
		parent::__construct();

		// Get gateway variables
		$this->id = 'trustly';

        $this->method_title = 'Pensopay - Trustly';

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
            'enabled' => [
                'title' => __( 'Enable', 'woo-pensopay' ),
                'type' => 'checkbox',
                'label' => __( 'Enable Trustly payment', 'woo-pensopay' ),
                'default' => 'no'
            ],
            '_Shop_setup' => [
                'type' => 'title',
                'title' => __( 'Shop setup', 'woo-pensopay' ),
            ],
            'title' => [
                'title' => __( 'Title', 'woo-pensopay' ),
                'type' => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', 'woo-pensopay' ),
                'default' => __('Trustly', 'woo-pensopay')
            ],
            'description' => [
                'title' => __( 'Customer Message', 'woo-pensopay' ),
                'type' => 'textarea',
                'description' => __( 'This controls the description which the user sees during checkout.', 'woo-pensopay' ),
                'default' => __('Pay with Trustly', 'woo-pensopay')
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
		return 'trustly';
	}
}
