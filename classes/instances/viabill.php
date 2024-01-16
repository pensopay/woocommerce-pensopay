<?php

class WC_PensoPay_ViaBill extends WC_PensoPay_Instance {

	public $main_settings = null;

	protected $_isWdp = false;

	public function maybe_disable_gateway( $gateways ) {
		if ( isset( $gateways[ $this->id ] ) && is_checkout() && ( $cart = WC()->cart ) ) {
			$cart_total = (float) $cart->get_total( 'edit' );
			$cart_min   = 120;

			if ( ! ( $cart_total >= $cart_min ) || 'DKK' !== strtoupper( get_woocommerce_currency() ) ) {
				unset( $gateways[ $this->id ] );
			}
		}

		return $gateways;
	}

	public function __construct() {
		parent::__construct();

		// Get gateway variables
		$this->id = 'viabill';

        $this->method_title = 'Pensopay - ViaBill';

		$this->setup();

		$this->title       = $this->s( 'title' );
		$this->description = $this->s( 'description' );

		add_filter( 'woocommerce_available_payment_gateways', [ $this, 'maybe_disable_gateway' ] );
        add_filter( 'woocommerce_pensopay_cardtypelock_viabill', [ $this, 'filter_cardtypelock' ] );

	    add_filter('woocommerce_get_price_html', [ $this, 'viabill_price_html' ], 10, 2);
	    add_filter('woocommerce_cart_totals_order_total_html', [ $this, 'viabill_price_html_cart' ], 10, 1);
	    add_filter('woocommerce_gateway_method_description', [ $this, 'viabill_payment_method' ], 10, 2);
	    add_action('woocommerce_checkout_order_review', [ $this, 'viabill_checkout_order_review'], 10, 0);
	    $that = $this;
	    add_action('wdp_price_display_init_hooks', static function() use($that) { $that->_isWdp = false; }, 10, 0);
	    add_action('wdp_price_display_remove_hooks', static function() use($that) { $that->_isWdp = true; }, 10, 0);
    }

    public function is_available() {
	    $currency = get_woocommerce_currency();
	    if (in_array($currency, ['DKK', 'NOK', 'USD'])) {
		    return parent::is_available();
		    // && isset($this->settings['id']) && !empty($this->settings['id'])
	    }
	    return false;
    }

	public function viabill_header()
	{
	    if ($this->is_available()): ?>
        <script type="text/javascript">
            var o;

            var viabillInit = function() {
                o =document.createElement('script');
                o.type='text/javascript';
                o.async=true;
                o.id = 'viabillscript';
                o.src='https://pricetag.viabill.com/script/<?= isset($this->settings['id']) ? $this->settings['id'] : '' ?>';
                var s=document.getElementsByTagName('script')[0];
                s.parentNode.insertBefore(o,s);
            };

            var viabillReset = function() {
                document.getElementById('viabillscript').remove();
                vb = null;
                pricetag = null;
                viabillInit();
            };

            jQuery(document).ready(function() {
                viabillInit();
                jQuery('body').on('updated_checkout', viabillReset);
            });
        </script>
        <?php endif;
	}

	/**
	 * payment_fields function.
	 *
	 * Prints out the description of the gateway. Also adds two checkboxes for viaBill/creditcard for customers to choose how to pay.
	 *
	 * @access public
	 * @return void
	 */
	public function payment_fields(): void {
		echo wpautop( wptexturize( $this->description ) ) . $this->getViabillPriceHtml('basket', WC()->cart->get_total('nodisplay'));
	}

	public function viabill_payment_method($description, $instance)
	{
		return $description;
	}

	public function getViabillPriceHtml($type, $price)
	{
		return sprintf('<div class="viabill-pricetag" data-view="%s" data-price="%s"></div>', $type, $price);
	}

    /**
     * Display pricetag in cart
     *
     * @param $value
     * @return string
     */
	public function viabill_price_html_cart($value)
	{
		if (is_cart() && isset($this->settings['show_pricetag_in_cart']) && $this->settings['show_pricetag_in_cart'] === 'yes') {
			return $value . $this->getViabillPriceHtml('basket', WC()->cart->get_total('nodisplay'));
		} else {
			return $value;
		}
	}

	public function viabill_price_html($price, $product)
	{
        global $woocommerce_loop;

        //Do not show for advanced pricing
        if ($this->_isWdp) {
            return '';
        }

        //Frontpage / shop page
	    if ((is_front_page() || is_shop()) && isset($this->settings['show_pricetag_on_frontpage']) && $this->settings['show_pricetag_on_frontpage'] !== 'yes') {
	        return $price;
        }

	    //Category page
        if (is_product_category() && isset($this->settings['show_pricetag_on_category_page']) && $this->settings['show_pricetag_on_category_page'] !== 'yes') {
            return $price;
        }

        //Product page
        if (is_product() && isset($this->settings['show_pricetag_on_product_page']) && $this->settings['show_pricetag_on_product_page'] !== 'yes') {
            return $price;
        }

        //Related products
        if (is_product() && isset($woocommerce_loop['name']) && $woocommerce_loop['name'] === 'related' && isset($this->settings['show_pricetag_on_related_products']) && $this->settings['show_pricetag_on_related_products'] !== 'yes') {
            return $price;
        }
        global $wp_query;
		$post = $wp_query->get_queried_object();

		return $price . $this->getViabillPriceHtml(is_product() ? 'product' : 'list', $product->get_price());
	}

    /**
     * Show pricetag in checkout
     */
	public function viabill_checkout_order_review()
    {
	    if ( $this->is_available() ) {
		    if ( isset( $this->settings['show_pricetag_in_checkout'] ) && $this->settings['show_pricetag_in_checkout'] === 'yes' ) {
			    echo $this->getViabillPriceHtml( 'basket', WC()->cart->get_total( 'nodisplay' ) );
		    }
	    }
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
                'label' => __( 'Enable ViaBill payment', 'woo-pensopay' ),
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
                    'default' => __('ViaBill', 'woo-pensopay')
                ],
                'description' => [
                    'title' => __( 'Customer Message', 'woo-pensopay' ),
                    'type' => 'textarea',
                    'description' => __( 'This controls the description which the user sees during checkout.', 'woo-pensopay' ),
                    'default' => __('Pay with ViaBill', 'woo-pensopay')
                ],
            '_Pricetag'       => [
	            'type'  => 'title',
	            'title' => __( 'Pricetag settings', 'woo-pensopay' )
            ],
            'id'              => [
	            'title' => __( 'Viabill ID', 'woo-pensopay' ),
	            'type'  => 'text'
            ],
            'show_pricetag_on_frontpage' => [
	            'title'   => __( 'Show pricetag on frontpage', 'woo-pensopay' ),
	            'type'    => 'checkbox',
	            'label'   => __( 'Enable ViaBill pricetag on frontpage', 'woo-pensopay' ),
	            'default' => 'no'
            ],
            'show_pricetag_on_product_page' => [
	            'title'   => __( 'Show pricetag on product page', 'woo-pensopay' ),
	            'type'    => 'checkbox',
	            'label'   => __( 'Enable ViaBill pricetag on product page', 'woo-pensopay' ),
	            'default' => 'no'
            ],
            'show_pricetag_on_related_products' => [
	            'title'   => __( 'Show pricetag on related products', 'woo-pensopay' ),
	            'type'    => 'checkbox',
	            'label'   => __( 'Enable ViaBill pricetag on related products', 'woo-pensopay' ),
	            'default' => 'no'
            ],
            'show_pricetag_on_category_page' => [
	            'title'   => __( 'Show pricetag on category page', 'woo-pensopay' ),
	            'type'    => 'checkbox',
	            'label'   => __( 'Enable ViaBill pricetag on category page', 'woo-pensopay' ),
	            'default' => 'no'
            ],
            'show_pricetag_in_cart' => [
	            'title'   => __( 'Show pricetag in cart', 'woo-pensopay' ),
	            'type'    => 'checkbox',
	            'label'   => __( 'Enable ViaBill pricetag in cart', 'woo-pensopay' ),
	            'default' => 'no'
            ],
            'show_pricetag_in_checkout' => [
	            'title'   => __( 'Show pricetag in checkout', 'woo-pensopay' ),
	            'type'    => 'checkbox',
	            'label'   => __( 'Enable ViaBill pricetag in checkout', 'woo-pensopay' ),
	            'default' => 'no'
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
		return 'viabill';
	}
}
