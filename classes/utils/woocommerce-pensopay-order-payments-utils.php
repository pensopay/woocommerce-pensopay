<?php

class WC_PensoPay_Order_Payments_Utils {
	/**
	 * @param WC_Order $order
	 *
	 * @return string|null
	 */
	public static function get_payment_id( WC_Order $order ): ?string {
		return $order->get_meta( 'QUICKPAY_PAYMENT_ID' ) ?: null;
	}

	/**
	 * @param WC_Order $order
	 * @param string $payment_link
	 *
	 * @return void
	 */
	public static function set_payment_id( WC_Order $order, string $payment_link ): void {
		$order->update_meta_data( 'QUICKPAY_PAYMENT_ID', $payment_link );
		$order->save_meta_data();
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return void
	 */
	public static function delete_payment_id( WC_Order $order ): void {
		$order->delete_meta_data( 'QUICKPAY_PAYMENT_ID' );
		$order->save_meta_data();
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return string|null
	 */
	public static function get_payment_link( WC_Order $order ): ?string {
		return $order->get_meta( 'QUICKPAY_PAYMENT_LINK' ) ?: null;
	}

	public static function set_payment_link( WC_Order $order, $payment_link ): void {
		$order->update_meta_data( 'QUICKPAY_PAYMENT_LINK', $payment_link );
		$order->save_meta_data();
	}

	public static function delete_payment_link( WC_Order $order ): void {
		$order->delete_meta_data( 'QUICKPAY_PAYMENT_LINK' );
		$order->save_meta_data();
	}

	public static function get_transaction_order_id( WC_Order $order ): ?string {
		return $order->get_meta( 'TRANSACTION_ORDER_ID' ) ?: null;
	}

	public static function set_transaction_order_id( WC_Order $order, $transaction_order_id ): void {
		$order->update_meta_data( 'TRANSACTION_ORDER_ID', $transaction_order_id );
		$order->save_meta_data();
	}

	public static function add_order_item_transaction_fee( WC_Order $order, int $fee_in_cents ): bool {
		if ( $fee_in_cents > 0 ) {
			$fee = new WC_Order_Item_Fee();

			$fee->set_name( __( 'Payment Fee', 'woo-pensopay' ) );
			$fee->set_total( $fee_in_cents / 100 );
			$fee->set_tax_status( 'none' );
			$fee->set_total_tax( 0 );
			$fee->set_order_id( $order->get_id() );

			$fee->save();

			$order->add_item( apply_filters( 'woocommerce_pensopay_transaction_fee_data', $fee, $order ) );

			$order->calculate_taxes();
			$order->calculate_totals( false );
			$order->save();

			return true;
		}

		return false;
	}

	/**
	 * get_transaction_params function.
	 *
	 * Returns the necessary basic params to send to PensoPay when creating a payment
	 *
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public static function prepare_transaction_params( WC_Order $order ): array {
		$is_subscription = WC_PensoPay_Order_Utils::contains_subscription( $order ) || WC_PensoPay_Requests_Utils::is_request_to_change_payment() || WC_PensoPay_Subscription::is_subscription( $order->get_id() );

		$params_subscription = [];

		if ( $is_subscription ) {
			$params_subscription = [
				'description' => apply_filters( 'woocommerce_pensopay_transaction_params_description', 'woocommerce-subscription', $order ),
			];
		}

		$params = array_merge( [
			'order_id'         => self::get_order_number_for_api( $order ),
			'basket'           => WC_PensoPay_Order_Transaction_Data_Utils::get_basket_params( $order ),
			'shipping_address' => WC_PensoPay_Order_Transaction_Data_Utils::get_shipping_address( $order ),
			'invoice_address'  => WC_PensoPay_Order_Transaction_Data_Utils::get_invoice_address( $order ),
			'shipping'         => WC_PensoPay_Order_Transaction_Data_Utils::get_shipping_params( $order ),
			'shopsystem'       => WC_PensoPay_Order_Transaction_Data_Utils::get_shop_system_params( $order ),
		], WC_PensoPay_Order_Transaction_Data_Utils::get_custom_variables( $order ) );

		return apply_filters( 'woocommerce_pensopay_transaction_params', array_merge( $params, $params_subscription ), $order );
	}

	/**
	 * get_transaction_link_params function.
	 *
	 * Returns the necessary basic params to send to PensoPay when creating a payment link
	 *
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public static function prepare_transaction_link_params( WC_Order $order ): array {
		return [
			'order_id'    => self::get_order_number_for_api( $order ),
			'continueurl' => WC_PensoPay_Order_Transaction_Data_Utils::get_continue_url( $order ),
			'cancelurl'   => WC_PensoPay_Order_Transaction_Data_Utils::get_cancellation_url( $order ),
			'amount'      => WC_PensoPay_Helper::price_multiply( $order->get_total(), $order->get_currency() ),
		];
	}

	/**
	 * get_order_number_for_api function.
	 *
	 * Prefix the order number if necessary. This is done
	 * because PensoPay requires the order number to contain at least
	 * 4 chars.
	 *
	 * @param WC_Order $order
	 * @param bool $recurring
	 *
	 * @return string
	 */
	public static function get_order_number_for_api( WC_Order $order, bool $recurring = false ): string {
		$minimum_length = 4;

		$order_id = $order->get_id();

		// When changing payment method on subscriptions
		if ( WC_PensoPay_Subscription::is_subscription( $order_id ) ) {
			$order_number = $order_id;
		} // On initial subscription authorizations
		else if ( ! $recurring && ! WC_PensoPay_Order_Utils::contains_switch_order( $order ) && WC_PensoPay_Order_Utils::contains_subscription( $order ) ) {
			// Find all subscriptions
			$subscriptions = WC_PensoPay_Subscription::get_subscriptions_for_order( $order_id );
			// Get the last one and base the transaction on it.
			$subscription = end( $subscriptions );
			// Fetch the ID of the subscription, not the parent order.
			$order_number = $subscription->get_id();

			// If an initial payment on a subscription failed (recurring payment), create a new subscription with appended ID.
			if ( ( $failed_payment_count = self::get_failed_payment_count( $order ) ) > 0 ) {
				$order_number .= sprintf( '-%d', $failed_payment_count );
			}
		} // On recurring / payment attempts
		else {
			// Normal orders - get the order number
			$order_number = WC_PensoPay_Order_Utils::get_clean_order_number( $order );
			// If an initial payment on a subscription failed (recurring payment), create a new subscription with appended ID.
			if ( ( $failed_payment_count = self::get_failed_payment_count( $order ) ) > 0 ) {
				$order_number .= sprintf( '-%d', $failed_payment_count );
			} // If manual payment of renewal, append the order number to avoid duplicate order numbers.
			else if ( WC_PensoPay_Subscription::cart_contains_failed_renewal_order_payment() ) {
				// Get the last one and base the transaction on it.
				$subscription = WC_PensoPay_Subscription::get_subscriptions_for_renewal_order( $order, true );
				$order_number .= sprintf( '-%s', $subscription ? $subscription->get_failed_payment_count() : WC_PensoPay_Helper::create_random_string( 3 ) );
			}
			// FIXME: This is for backwards compatability only. Before 4.5.6 orders were not set to 'FAILED' when a recurring payment failed.
			// FIXME: To allow customers to pay the outstanding, we must append a value to the order number to avoid errors with duplicate order numbers in the API.
			else if ( WC_PensoPay_Subscription::cart_contains_renewal() ) {
				$order_number .= sprintf( '-%s', WC_PensoPay_Helper::create_random_string( 3 ) );
			}
		}

		if ( WC_PensoPay_Requests_Utils::is_request_to_change_payment() ) {
			$order_number .= sprintf( '-%s', WC_PensoPay_Helper::create_random_string( 3 ) );
		}

		$order_number_length = strlen( $order_number );

		if ( $order_number_length < $minimum_length ) {
			preg_match( '/\d+/', $order_number, $digits );

			if ( ! empty( $digits ) ) {
				$missing_digits = $minimum_length - $order_number_length;
				$order_number   = str_replace( $digits[0], str_pad( $digits[0], strlen( $digits[0] ) + $missing_digits, 0, STR_PAD_LEFT ), $order_number );
			}
		}

//		$order_number = 'PEN-' . $order_number;

		return apply_filters( 'woocommerce_pensopay_order_number_for_api', $order_number, $order, $recurring );
	}

	/**
	 * Increase the amount of payment attempts done through PensoPay
	 *
	 * @param WC_Order $order
	 *
	 * @return int
	 */
	public static function get_failed_payment_count( WC_Order $order ): int {
		return (int) $order->get_meta( '_quickpay_failed_payment_count' );
	}


	/**
	 * Increase the amount of payment attempts done through PensoPay
	 *
	 * @param WC_Order $order
	 *
	 * @return int
	 */
	public static function increase_failed_payment_count( WC_Order $order ): int {
		$count = self::get_failed_payment_count( $order );
		$order->update_meta_data( '_quickpay_failed_payment_count', ++ $count );
		$order->save_meta_data();

		return $count;
	}

	/**
	 * Reset the amount of payment attempts done through PensoPay
	 *
	 * @param WC_Order $order
	 */
	public static function reset_failed_payment_count( WC_Order $order ): void {
		$order->delete_meta_data( '_quickpay_failed_payment_count' );
		$order->save_meta_data();
	}

	public static function get_payment_method_change_count( WC_Order $order ): int {
		return (int ) $order->get_meta( '_quickpay_payment_method_change_count' );
	}

	/**
	 * Increases the amount of times the customer has updated his card.
	 *
	 * @param WC_Order $order
	 *
	 * @return int
	 */
	public static function increase_payment_method_change_count( WC_Order $order ): int {
		$count = self::get_payment_method_change_count( $order );

		$order->update_meta_data( '_quickpay_payment_method_change_count', ++ $count );
		$order->save_meta_data();

		return $count;
	}

	/**
	 * Checks if the order is paid with the PensoPay module.
	 *
	 * @param WC_Order $order
	 *
	 * @return bool
	 */
	public static function is_order_using_pensopay( WC_Order $order ): bool {
		return in_array( $order->get_payment_method(), [
			'anyday-split',
			'pensopay_apple_pay',
			'pensopay_google_pay',
			'ideal',
			'fbg1886',
			'ideal',
			'klarna',
			'klarna-payments',
			'mobilepay',
			'mobilepay_checkout',
			'mobilepay-subscriptions',
			'pensopay_paypal',
			'pensopay',
			'pensopay-extra',
			'resurs',
			'sofort',
			'swish',
			'trustly',
			'viabill',
			'vipps',
		], true );
	}
}
