<?php

/**
 * Class WC_PensoPay_MobilePay_Checkout
 */
class WC_PensoPay_MobilePay_Checkout extends WC_PensoPay_Instance {

	/**
	 * @var null
	 */
	private $checkout_fields = null;

	public function __construct() {
		parent::__construct();

		// Get gateway variables
		$this->id = 'mobilepay_checkout';

		$this->method_title = 'Pensopay - MobilePay Checkout';

		$this->setup();

		$this->title       = $this->s( 'title' );
		$this->description = $this->s( 'description' );

		add_filter( 'woocommerce_pensopay_cardtypelock_' . $this->id, [ $this, 'filter_cardtypelock' ] );
		add_action( 'woocommerce_after_checkout_validation', [ $this, 'checkout_validation' ], 100, 2 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_filter( 'woocommerce_pensopay_transaction_link_params', [ $this, 'filter_transaction_link_params'], 10, 3 );
		add_action( 'woocommerce_pensopay_accepted_callback_before_processing_status_authorize', [$this, 'callback_save_address'], 10, 2 );
		add_action( 'woocommerce_checkout_before_customer_details', [ $this, 'insert_woocommerce_pensopay_mobilepay_checkout'], 10 );
		add_filter( 'woocommerce_update_order_review_fragments', [ $this, 'update_order_review_fragments' ], 10, 1 );
		add_filter( 'woocommerce_pensopay_automatic_shipping_address', [ $this, 'maybe_modify_object_shipping_address' ], 10, 4 );
		add_filter( 'woocommerce_available_payment_gateways', [ $this, 'maybe_disable_gateway' ] );
	}

	/**
	 * @param array $params
	 * @param WC_PensoPay_Order $order
	 * @param string $payment_method
	 *
	 * @return array
	 */
	public function filter_transaction_link_params( $params, $order, $payment_method ) {
		if ( strtolower( $payment_method ) === $this->id ) {
			$params['invoice_address_selection']  = apply_filters( 'woocommerce_pensopay_mobilepay_checkout_invoice_address_selection', true, $params, $order );
			$params['shipping_address_selection'] = apply_filters( 'woocommerce_pensopay_mobilepay_checkout_shipping_address_selection', $this->maybe_disable_automatic_shipping_address_selection( $order ), $params, $order );
		}

		return $params;
	}

	/**
	 * By default, when submitting an order with MobilePay Checkout, automatic address selection by the acquirer is enabled.
	 * But, in order to support shipping plugins to set the address of a parcel shop / pick-up point, we will check
	 * if the shipping address has been set - if so, we will disable the automatic address selection.
	 *
	 * @param WC_Order $order
	 *
	 * @return bool
	 */
	private function maybe_disable_automatic_shipping_address_selection( WC_Order $order ): bool {
		$enabled = true;

		if (
			! ( empty( trim( $order->get_formatted_shipping_full_name() ) ) && empty( $order->get_shipping_company() ) )
			|| ! empty( $order->get_shipping_address_1() )
			|| ! empty( $order->get_shipping_postcode()
			            || ! empty( $order->get_shipping_city() ) ) ) {
			$enabled = false;
		}

		return $enabled;
	}

	/**
	 * Removes all required fields validation errors to avoid validation errors when checking out with MobilePay Checkout.
	 * We will validate all fields set as required by developers.
	 *
	 * @param array $data
	 * @param \WP_Error $errors
	 */
	public function checkout_validation( $data, $errors ) {

		if ( strtolower( $data['payment_method'] ) === $this->id ) {

			$is_at_least_wc_4_3_0 = version_compare( wc()->version, '4.3', '>=' );

			// Below WC 4.3 all validation errors were grouped to a single required-field code. From 4.3 WC sets a code per field.
			if ( $is_at_least_wc_4_3_0 ) {
				$error_codes = $errors->get_error_codes();
				if ( ! empty( $error_codes ) ) {
					foreach ( $error_codes as $error_code ) {
						if ( preg_match( '/_required$/', $error_code ) ) {
							$errors->remove( $error_code );
						}
					}
				}
			} else {
				$errors->remove( 'required-field' );
			}

			if ( ! empty( $data['shipping_method'] ) ) {
				$shipping_rate_required_fields = $this->get_shipping_rate_required_address_fields();

				foreach ( $data['shipping_method'] as $shipping_method_id ) {
					if ( array_key_exists( $shipping_method_id, $shipping_rate_required_fields ) && ! empty( $shipping_rate_required_fields[ $shipping_method_id ] ) ) {
						foreach ( $shipping_rate_required_fields[ $shipping_method_id ] as $required_field ) {
							$field_label   = $this->get_checkout_field_label_by_id( $required_field );
							$error_message = apply_filters( 'woocommerce_checkout_required_field_notice', sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>' . esc_html( $field_label ) . '</strong>' ), $field_label );
							if ( $is_at_least_wc_4_3_0 ) {
								$errors->add( $required_field . '_required', $error_message );
							} else {
								$errors->add( 'required-field', $error_message );
							}
						}
					}
				}
			}

			do_action( 'woocommerce_pensopay_after_checkout_validation', $data, $errors );
		}
	}

	/**
	 * Returns the checkout field label based on the field ID.
	 *
	 * @param $required_field
	 *
	 * @return string
	 */
	private function get_checkout_field_label_by_id( $required_field ) {
		$field_label = '';

		if ( $this->checkout_fields === null ) {
			$this->checkout_fields = WC_Checkout::instance()->get_checkout_fields();
		}

		$field_parts = explode( '_', $required_field );

		if ( ! empty( $field_parts ) ) {
			$section = reset( $field_parts );

			if ( array_key_exists( $section, $this->checkout_fields ) && array_key_exists( $required_field, $this->checkout_fields[ $section ] ) && ! empty( $this->checkout_fields[ $section ][ $required_field ]['label'] ) ) {
				$field_label = $this->checkout_fields[ $section ][ $required_field ]['label'];
			}
		}

		return apply_filters( 'woocommerce_pensopay_mobilepay_checkout_checkout_field_label', $field_label, $required_field, $this->checkout_fields );
	}

	/**
	 * Insert fast track to MobilePay checkout with a button to easily select MobilePay and checkout.
	 */
	public function insert_woocommerce_pensopay_mobilepay_checkout() {
		if ( $this->is_gateway_available() ) {
			woocommerce_pensopay_get_template( 'checkout/mobilepay-checkout.php', [
                'text' => $this->description,
            ] );
		}
	}

	/**
	 * @return bool
	 */
	public function is_gateway_available() {
		return array_key_exists( $this->id, WC()->payment_gateways->get_available_payment_gateways() );
	}

	/**
	 * @return int
	 */
	public function is_enabled() {
		return $this->enabled === 'yes';
	}

	/**
	 * @param WC_Order $order
	 * @param object $transaction
	 */
	public function callback_save_address( WC_Order $order, $transaction ) {
        if ( isset($transaction->variables->payment_method) && $transaction->variables->payment_method === $this->id && $this->is_enabled() ) {
            $billing_address  = apply_filters( 'woocommerce_pensopay_automatic_billing_address', ! empty( $transaction->invoice_address ) ? $transaction->invoice_address : null, $order, $transaction );
            $shipping_address = apply_filters( 'woocommerce_pensopay_automatic_shipping_address', ! empty( $transaction->shipping_address ) ? $transaction->shipping_address : null, $order, $billing_address, $transaction );

			do_action( 'woocommerce_pensopay_save_automatic_addresses_before', $order, $billing_address, $shipping_address, $transaction );
			try {
				$customer    = null;
                $create_user = apply_filters( 'woocommerce_pensopay_mobilepay_checkout_create_user', wc()->checkout()->is_registration_required(), $order, $transaction );

				if ( ! $order->get_customer_id() && $create_user ) {
					$customer_id = wc_create_new_customer( $billing_address->email, $billing_address->email, wp_generate_password() );
					if ( ! is_wp_error( $customer_id ) ) {
						$customer = new WC_Customer( $customer_id );
					}
				} else if ( $order->get_customer_id() && $this->s( 'mobilepay_checkout_update_existing_customer_data' ) ) {
					$customer = new WC_Customer( $order->get_customer_id() );
				}

				if ( $customer && ! is_wp_error( $customer ) ) {
					$this->set_object_billing_address( $customer, $billing_address );
					$this->set_object_shipping_address( $customer, $shipping_address );
					$customer->save();
					$order->set_customer_id( $customer->get_id() );
				}

				$this->set_object_billing_address( $order, $billing_address );
				$this->set_object_shipping_address( $order, $shipping_address );

			} catch ( \Exception $e ) {
				WC_PP()->log->add( $e->getMessage(), __FILE__, __LINE__ );
			}
			$order->save();

			do_action( 'woocommerce_pensopay_save_automatic_addresses_after', $order, $billing_address, $shipping_address, $transaction );
		}
	}

	/**
	 * @param WC_Customer|WC_Order $object
	 * @param                      $address
	 *
	 * @throws WC_Data_Exception
	 */
	private function set_object_billing_address( $object, $address ) {
		if ( ! empty( $address ) && ( is_a( $object, 'WC_Customer' ) || is_a( $object, 'WC_Order' ) ) ) {
			$object->set_billing_first_name( $this->get_first_name( $address->name ) );
			$object->set_billing_last_name( $this->get_last_name( $address->name ) );
			$object->set_billing_address_1( $this->get_formatted_address( $address ) );
			$object->set_billing_address_2( $address->att );
			$object->set_billing_phone( $address->phone_number );
			$object->set_billing_country( WC_PensoPay_Countries::getAlpha3FromAlpha2( $address->country_code ) );
			$object->set_billing_company( $address->company_name );
			$object->set_billing_city( $address->city );
			$object->set_billing_postcode( $address->zip_code );
			$object->set_billing_email( $address->email );
		}
	}

	/**
	 * @param $full_name
	 *
	 * @return string
	 */
	private function get_first_name( $full_name ) {
		$names = explode( ' ', $full_name );

		// Remove the supposed last name if more values are present
		if ( is_array( $names ) && count( $names ) > 1 ) {
			array_pop( $names );
		}

		return is_array( $names ) && ! empty( $names ) ? implode( ' ', $names ) : '';
	}

	/**
	 * @param $full_name
	 *
	 * @return string
	 */
	private function get_last_name( $full_name ) {
		$names = explode( ' ', $full_name );

		return is_array( $names ) && ! empty( $names ) ? end( $names ) : '';
	}

	/**
	 * @param object $address
	 *
	 * @return string
	 */
	private function get_formatted_address( $address ) {
		$address = sprintf( '%s %s', $address->street, $address->house_number );

		if ( ! empty( $address->houses_extension ) ) {
			$address .= ", {$address->house_extension}";
		}

		return apply_filters( 'woocommerce_pensopay_automatic_formatted_address', $address );
	}

	/**
	 * @param WC_Customer|WC_Order $object
	 * @param                      $address
	 *
	 * @throws WC_Data_Exception
	 */
	private function set_object_shipping_address( $object, $address ) {
		if ( ! empty( $address ) && ( is_a( $object, 'WC_Customer' ) || is_a( $object, 'WC_Order' ) ) ) {
			$object->set_shipping_first_name( $this->get_first_name( $address->name ) );
			$object->set_shipping_last_name( $this->get_last_name( $address->name ) );
			$object->set_shipping_address_1( $this->get_formatted_address( $address ) );
			$object->set_shipping_address_2( $address->att );
			$object->set_shipping_country( WC_PensoPay_Countries::getAlpha3FromAlpha2( $address->country_code ) );
			$object->set_shipping_company( $address->company_name );
			$object->set_shipping_city( $address->city );
			$object->set_shipping_postcode( $address->zip_code );
			$object->set_shipping_address_2( $address->att );
		}
	}

	/**
	 * @param stdClass|null $shipping_address
	 * @param \WC_Order $order
	 * @param stdClass|null $billing_address
	 * @param stdClass $transaction
	 *
	 * @return object
	 */
	public function maybe_modify_object_shipping_address( $shipping_address, $order, $billing_address, $transaction ) {

		/**
		 * If shipping_address_selection is set to false on the payment link, we will make sure to set the shipping address to NULL.
		 * This is because the shipping address may be returned either way from MobilePay which will break compatibility with drop point plugins etc.
		 * This is considered a bug between PensoPay and MobilePay and will hopefully be fixed.
		 *
		 * Reported on November 2019-11-28 to SSP.
		 */
		if ( isset( $transaction->link, $transaction->link->shipping_address_selection ) && $transaction->link->shipping_address_selection === false ) {
			$shipping_address = null;
		}

		if ( $shipping_address !== null ) {
			$name         = trim( $shipping_address->name );
			$street       = trim( $shipping_address->street );
			$city         = trim( $shipping_address->city );
			$postcode     = trim( $shipping_address->zip_code );
			$house_number = trim( $shipping_address->house_number );

			if ( is_object( $billing_address ) && ( empty( $name ) || empty( $street ) || empty( $city ) || empty( $postcode ) || empty( $house_number ) ) ) {
				$shipping_address = $billing_address;
			}
		}

		return $shipping_address;
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
			'enabled'                                          => [
				'title'   => __( 'Enable', 'woo-pensopay' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable MobilePay Checkout payment', 'woo-pensopay' ),
				'default' => 'no',
			],
			'_Shop_setup'                                      => [
				'type'  => 'title',
				'title' => __( 'Shop setup', 'woo-pensopay' ),
			],
			'title'                                            => [
				'title'       => __( 'Title', 'woo-pensopay' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woo-pensopay' ),
				'default'     => __( 'MobilePay Checkout', 'woo-pensopay' ),
			],
			'description'                                      => [
				'title'       => __( 'Customer Message', 'woo-pensopay' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'woo-pensopay' ),
				'default'     => __( 'Fast checkout with your mobile phone. With MobilePay Checkout we will automatically receive your billing- and shipping details from MobilePay and save it on your order.', 'woo-pensopay' ),
			],
			'mobilepay_checkout_update_existing_customer_data' => [
				'title'       => __( 'MobilePay Checkout - Update Existing Customers', 'woo-pensopay' ),
				'type'        => 'checkbox',
				'label'       => __( 'Update Existing Customers Accounts', 'woo-pensopay' ),
				'description' => __( 'If enabled, and if an exisiting customer is ordering, the plugin will update the address on both order and customer level. This requires that the user is logged in when ordering. Previous orders will NOT be affected.', 'woo-pensopay' ),
				'default'     => 'yes',
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
		return 'mobilepay';
	}

	/**
	 * Enqueue static scripts
	 */
	public function enqueue_scripts() {
		if ( $this->is_enabled() && is_checkout() ) {
			wp_enqueue_script( 'wcpp-mobilepay', plugins_url( '/assets/javascript/mobilepay.js', dirname( __DIR__ ) ), [ 'jquery' ], WC_PensoPay_Helper::static_version(), true );
		}
	}

	/**
	 * Allow external plugins to define required shipping fields to avoid the fields from being
	 * disabled when choosing MobilePay Checkout.
	 *
	 * @param $fragments
	 *
	 * @return array
	 */
	public function get_shipping_rate_required_address_fields() {
		$required_fields = [];
		if ( $packages = WC()->shipping()->get_packages() ) {

			$package = reset( $packages );

			if ( $package && ! empty( $package['rates'] ) ) {
				foreach ( $package['rates'] as $rate ) {
					$required_fields[ $rate->get_id() ] = apply_filters( 'woocommerce_pensopay_mobilepay_checkout_shipping_package_rate_required_fields', [], $rate, $package );
				}
			}
		}

		return $required_fields;
	}

	/**
	 * Add custom data to the frontend through the update_order_review action.
	 *
	 * @param $fragments
	 *
	 * @return mixed
	 */
	public function update_order_review_fragments( $fragments ) {
		$fragments['mpco_toggle_checkout_fields_appearance'] = apply_filters( 'woocommerce_pensopay_mobilepay_checkout_toggle_shipping_fields_appearance', true );
		$fragments['mpco_required_fields']                   = $this->get_shipping_rate_required_address_fields();

		return $fragments;
	}

	public function maybe_disable_gateway( $gateways ) {
		if ( isset( $gateways[ $this->id ] ) && is_checkout() && ( $cart = WC()->cart ) ) {

			if ( !in_array(strtoupper( get_woocommerce_currency() ), ['DKK', 'EUR']) || !in_array(WC()->customer->get_shipping_country(), ['DK', 'FI']) ) {
				unset( $gateways[ $this->id ] );
			}
		}

		return $gateways;
	}
}
