<?php

class WC_PensoPay_Sofort extends WC_PensoPay_Instance {

    public $main_settings = NULL;

    public function __construct() {
        parent::__construct();

        // Get gateway variables
        $this->id = 'sofort';

        $this->method_title = 'PensoPay - Sofort';

        $this->setup();

        $this->title = $this->s('title');
        $this->description = $this->s('description');

        add_filter( 'woocommerce_pensopay_cardtypelock_sofort', array( $this, 'filter_cardtypelock' ) );
    }


    /**
     * init_form_fields function.
     *
     * Initiates the plugin settings form fields
     *
     * @access public
     * @return array
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __( 'Enable', 'woo-pensopay' ),
                'type' => 'checkbox',
                'label' => __( 'Enable Sofort payment', 'woo-pensopay' ),
                'default' => 'no'
            ),
            '_Shop_setup' => array(
                'type' => 'title',
                'title' => __( 'Shop setup', 'woo-pensopay' ),
            ),
            'title' => array(
                'title' => __( 'Title', 'woo-pensopay' ),
                'type' => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', 'woo-pensopay' ),
                'default' => __('Sofort', 'woo-pensopay')
            ),
            'description' => array(
                'title' => __( 'Customer Message', 'woo-pensopay' ),
                'type' => 'textarea',
                'description' => __( 'This controls the description which the user sees during checkout.', 'woo-pensopay' ),
                'default' => __('Pay with your mobile phone', 'woo-pensopay')
            ),
        );
    }


    /**
     * filter_cardtypelock function.
     *
     * Sets the cardtypelock
     *
     * @access public
     * @return string
     */
    public function filter_cardtypelock( )
    {
        return 'sofort';
    }
}
