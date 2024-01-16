<?php

class WC_PensoPay_Instance extends WC_PensoPay {
    
    public $main_settings = null;
    
    public function __construct() {
        parent::__construct();
        
        $this->supports = [
            'products', 
            'refunds'
        ];
    }
    
    public function setup() {
        $this->hooks_and_filters();

		// Keep a reference
		$this->main_settings = $this->settings;

		// Load the form fields and settings
		$this->init_form_fields();
		$this->init_settings();
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
		$this->form_fields = [];
	}

    /**
    * Prints the admin settings form
    *
    * @access public
    * @return string
    */
    public function generate_settings_html( $form_fields = [], $echo = true ) {
        $main_settings_args = [
            'page'    => 'wc-settings',
            'tab'     => 'checkout',
            'section' => 'wc_pensopay'
        ];

        $html = "<h3>Pensopay - {$this->method_title}, v" . WCPP_VERSION . "</h3>";
        $html .= "<p>" . sprintf( __( 'Allows you to receive payments via Pensopay %s.', 'woo-pensopay' ), $this->id ) . "</p>";
        $html .= "<p>" . sprintf( __( 'This module has it\'s main configuration inside the \'Pensopay\' tab.', 'woo-pensopay' ), 's' ) . "</p>";
        $html .= "<p>" . sprintf( __( 'Click <a href="%s">here</a> to access the main configuration.', 'woo-pensopay' ), add_query_arg( $main_settings_args, admin_url( 'admin.php') ) ) . "</p>";

        $html .= get_parent_class( get_parent_class( get_parent_class( $this ) ) )::generate_settings_html( $form_fields, $echo );

		if ( $echo ) {
			echo $html; // WPCS: XSS ok.
		} else {
			return $html;
		}
	}

	/**
	 * s function.
	 *
	 * Returns a setting if set. Introduced to prevent undefined key when introducing new settings.
	 * In an instance class, this method first check if a local setting is set in the current instance. If not, it will check for the same setting inside
	 * the core library.
	 *
	 * @access public
	 * @return string
	 */
	public function s( $key, $default = null ) {
		if ( isset( $this->settings[ $key ] ) ) {
			return $this->settings[ $key ];
		}

		if ( isset( $this->main_settings[ $key ] ) ) {
			return $this->main_settings[ $key ];
		}

		return ! is_null( $default ) ? $default : '';
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
			$icons_maxheight = $this->gateway_icon_size();
			$icon            .= $this->gateway_icon_create( strtolower( $this->id ), $icons_maxheight );
		}

		return $icon;
	}

	/**
	 * @return string|string[]
	 */
	protected function get_sanitized_method_title() {
		return str_replace( 'QuickPay - ', '', $this->method_title );
	}
}
