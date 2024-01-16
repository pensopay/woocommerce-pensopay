<?php

class WC_PensoPay_Order_Transaction_Data_Utils {

	public static function get_shipping_address( WC_Order $order ): array {
		$shipping_name = trim( $order->get_formatted_shipping_full_name() );
		$company_name  = $order->get_shipping_company();

		if ( empty ( $shipping_name ) && ! empty( $company_name ) ) {
			$shipping_name = $company_name;
		}

		$params = [
			'name'            => $shipping_name,
			'street'          => WC_PensoPay_Address::get_street_name( $order->get_shipping_address_1() ),
			'house_number'    => WC_PensoPay_Address::get_house_number( $order->get_shipping_address_1() ),
			'house_extension' => WC_PensoPay_Address::get_house_extension( $order->get_shipping_address_1() ),
			'city'            => $order->get_shipping_city(),
			'region'          => $order->get_shipping_state(),
			'zip_code'        => $order->get_shipping_postcode(),
			'country_code'    => WC_PensoPay_Countries::getAlpha3FromAlpha2( $order->get_shipping_country() ),
			'phone_number'    => $order->get_billing_phone(),
			'mobile_number'   => $order->get_billing_phone(),
			'email'           => $order->get_billing_email(),
		];

		return apply_filters( 'woocommerce_pensopay_transaction_params_shipping', $params, $order );
	}

	public static function get_invoice_address( WC_Order $order ): array {
		$billing_name = trim( $order->get_formatted_billing_full_name() );
		$company_name = $order->get_billing_company();

		if ( empty ( $billing_name ) && ! empty( $company_name ) ) {
			$billing_name = $company_name;
		}

		$params = [
			'name'            => $billing_name,
			'street'          => WC_PensoPay_Address::get_street_name( $order->get_billing_address_1() ),
			'house_number'    => WC_PensoPay_Address::get_house_number( $order->get_billing_address_1() ),
			'house_extension' => WC_PensoPay_Address::get_house_extension( $order->get_billing_address_1() ),
			'city'            => $order->get_billing_city(),
			'region'          => $order->get_billing_state(),
			'zip_code'        => $order->get_billing_postcode(),
			'country_code'    => WC_PensoPay_Countries::getAlpha3FromAlpha2( $order->get_billing_country() ),
			'phone_number'    => $order->get_billing_phone(),
			'mobile_number'   => $order->get_billing_phone(),
			'email'           => $order->get_billing_email(),
		];

		return apply_filters( 'woocommerce_pensopay_transaction_params_invoice', $params, $order );
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public static function get_basket_params( WC_Order $order ): array {
		// Contains order items in PensoPay basket format
		$basket = [];

		foreach ( $order->get_items() as $item_line ) {
			$basket[] = self::get_transaction_basket_params_line_helper( $order, $item_line );
		}

		if ( apply_filters( 'woocommerce_pensopay_transaction_params_basket_apply_fees', true, $order ) ) {
			foreach ( $order->get_items( 'fee' ) as $item_line ) {
				/** @var WC_Order_Item_Fee $item_line */
				$basket[] = self::get_transaction_basket_params_fee_helper( $order, $item_line );
			}
		}


		return apply_filters( 'woocommerce_pensopay_transaction_params_basket', $basket, $order );
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public static function get_shop_system_params( WC_Order $order ): array {
		$params = [
			'name'    => 'WooCommerce',
			'version' => WCPP_VERSION,
		];

		return apply_filters( 'woocommerce_pensopay_transaction_params_shopsystem', $params, $order );
	}

	/**
	 * get_custom_variables function.
	 *
	 * Returns custom variables chosen in the gateway settings. This information will
	 * be sent to PensoPay and stored with the transaction.
	 *
	 * @access public
	 *
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public static function get_custom_variables( WC_Order $order ): array {
		$custom_vars_settings = (array) WC_PP()->s( 'pensopay_custom_variables' );
		$custom_vars          = [];

		// Single: Order Email
		if ( in_array( 'customer_email', $custom_vars_settings, true ) ) {
			$custom_vars[ __( 'Customer Email', 'woo-pensopay' ) ] = $order->get_billing_email();
		}

		// Single: Order Phone
		if ( in_array( 'customer_phone', $custom_vars_settings, true ) ) {
			$custom_vars[ __( 'Customer Phone', 'woo-pensopay' ) ] = $order->get_billing_phone();
		}

		// Single: Browser User Agent
		if ( in_array( 'browser_useragent', $custom_vars_settings, true ) ) {
			$custom_vars[ __( 'User Agent', 'woo-pensopay' ) ] = $order->get_customer_user_agent();
		}

		// Single: Shipping Method
		if ( in_array( 'shipping_method', $custom_vars_settings, true ) ) {
			$custom_vars[ __( 'Shipping Method', 'woo-pensopay' ) ] = $order->get_shipping_method();
		}

		// Save a POST ID reference on the transaction
		$custom_vars['order_post_id'] = $order->get_id();

		// Get the correct order_post_id. We want to fetch the ID of the subscription to store data on subscription (if available).
		// But only on the first attempt. In case of failed auto capture on the initial order, we dont want to add the subscription ID.
		// If we are handlong a product switch, we will not need this ID as we are making a regular payment.
		if ( ! WC_PensoPay_Order_Utils::contains_switch_order( $order ) ) {
			$subscription_id = WC_PensoPay_Subscription::get_subscription_id( $order );
			if ( $subscription_id ) {
				$custom_vars['subscription_post_id'] = $subscription_id;
			}
		}

		if ( WC_PensoPay_Requests_Utils::is_request_to_change_payment() ) {
			$custom_vars['change_payment'] = true;
		}

		$custom_vars['payment_method'] = $order->get_payment_method();

		$custom_vars = apply_filters( 'woocommerce_pensopay_transaction_params_variables', $custom_vars, $order );

		ksort( $custom_vars );

		return [ 'variables' => $custom_vars ];
	}

	/**
	 * Returns the order's continue callback url
	 *
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	public static function get_continue_url( WC_Order $order ): string {
		if ( method_exists( $order, 'get_checkout_order_received_url' ) ) {
			return $order->get_checkout_order_received_url();
		}

		return add_query_arg( 'key', $order->get_order_key(), add_query_arg( 'order', $order->get_id(), get_permalink( get_option( 'woocommerce_thanks_page_id' ) ) ) );
	}

	/**
	 * Returns the order's cancellation callback url
	 *
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	public static function get_cancellation_url( WC_Order $order ): string {
		if ( method_exists( $order, 'get_cancel_order_url' ) ) {
			return str_replace( '&amp;', '&', $order->get_cancel_order_url() );
		}

		return add_query_arg( 'key', $order->get_order_key(), add_query_arg( [
			'order'                => $order->get_id(),
			'payment_cancellation' => 'yes',
		], get_permalink( get_option( 'woocommerce_cart_page_id' ) ) ) );
	}

	public static function should_auto_capture_order( WC_Order $order ): bool {
		// Get the autocapture settings
		$auto_capture_default = wc_string_to_bool( WC_PP()->s( 'pensopay_autocapture' ) );
		$auto_capture_virtual = wc_string_to_bool( WC_PP()->s( 'pensopay_autocapture_virtual' ) );

		$has_virtual_products  = WC_PensoPay_Order_Utils::contains_virtual_products( $order );
		$has_physical_products = WC_PensoPay_Order_Utils::contains_physical_products( $order );

		// If the two options are the same, return immediately.
		if ( $auto_capture_default === $auto_capture_virtual ) {
			return $auto_capture_default;
		}

		// If the order contains both virtual and nonvirtual products,
		// we use the 'pensopay_autopay' as the option of choice.
		if ( $has_virtual_products && $has_physical_products ) {
			return $auto_capture_default;
		}

		// Or check if the order contains virtual products only
		if ( $has_virtual_products ) {
			return $auto_capture_virtual;
		}

		return $auto_capture_default;
	}


	/**
	 * @param WC_Order $order
	 * @param WC_Order_Item_Product $line_item
	 *
	 * @return array
	 */
	private static function get_transaction_basket_params_line_helper( WC_Order $order, WC_Order_Item $line_item ): array {
		$price = ( (float) $line_item->get_total() + (float) $line_item->get_total_tax() ) / $line_item->get_quantity();

		return [
			'qty'        => $line_item->get_quantity(),
			'item_no'    => $line_item->get_product_id(), //
			'item_name'  => esc_attr( $line_item->get_name() ),
			'item_price' => WC_PensoPay_Helper::price_multiply( $price, $order->get_currency() ),
			'vat_rate'   => self::get_tax_rate( $line_item ) // Basket item VAT rate (ex. 0.25 for 25%)
		];
	}

	/**
	 * @param WC_Order $order
	 * @param WC_Order_Item_Fee $fee_item
	 *
	 * @return array
	 */
	private static function get_transaction_basket_params_fee_helper( WC_Order $order, WC_Order_Item_Fee $fee_item ): array {
		$price = ( (float) $fee_item->get_total() + (float) $fee_item->get_total_tax() ) / $fee_item->get_quantity();

		return [
			'qty'        => $fee_item->get_quantity(),
			'item_no'    => sanitize_title( $fee_item->get_name() ), //
			'item_name'  => esc_attr( $fee_item->get_name() ),
			'item_price' => WC_PensoPay_Helper::price_multiply( $price, $order->get_currency() ),
			'vat_rate'   => self::get_tax_rate( $fee_item ) // Basket item VAT rate (ex. 0.25 for 25%)
		];
	}

	/**
	 * @param WC_Order_Item_Fee $item
	 *
	 * @return float
	 */
	private static function get_tax_rate( WC_Order_Item $item ) {
		$vat_rate = 0;

		if ( wc_tax_enabled() ) {
			$taxes = WC_Tax::get_rates( $item->get_tax_class() );
			//Get rates of the product
			$rates = array_shift( $taxes );
			//Take only the item rate and round it.
			$vat_rate = ! empty( $rates ) ? round( array_shift( $rates ) ) : 0;
		}

		return $vat_rate > 0 ? $vat_rate / 100 : 0;
	}

	/**
	 * Creates shipping basket row.
	 *
	 * @return array
	 */
	public static function get_shipping_params( WC_Order $order ): array {
		$shipping_tax      = (float) $order->get_shipping_tax();
		$shipping_total    = (float) $order->get_shipping_total();
		$shipping_incl_vat = $shipping_total;
		$shipping_vat_rate = 0;

		if ( $shipping_tax && $shipping_total ) {
			$shipping_incl_vat += $shipping_tax;
			$shipping_vat_rate = $shipping_tax / $shipping_total; // Basket item VAT rate (ex. 0.25 for 25%)
		}

		return apply_filters( 'woocommerce_pensopay_transaction_params_shipping_row', [
			'method'   => 'own_delivery',
			'company'  => $order->get_shipping_method(),
			'amount'   => WC_PensoPay_Helper::price_multiply( $shipping_incl_vat, $order->get_currency() ),
			'vat_rate' => $shipping_vat_rate,
		], $order );
	}
}
