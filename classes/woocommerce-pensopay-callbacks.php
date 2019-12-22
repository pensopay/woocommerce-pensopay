<?php

/**
 * Class WC_PensoPay_Callbacks
 */
class WC_PensoPay_Callbacks {

	/**
	 * Regular payment logic for authorized transactions
	 *
	 * @param WC_PensoPay_Order $order
	 * @param stdClass $transaction
	 */
	public static function payment_authorized( $order, $transaction ) {
		// Add order transaction fee if available
		if ( ! empty( $transaction->fee ) ) {
			$order->add_transaction_fee( $transaction->fee );
		}

		// Check for pre-order
		if ( WC_PensoPay_Helper::has_preorder_plugin() && WC_Pre_Orders_Order::order_contains_pre_order( $order ) && WC_Pre_Orders_Order::order_requires_payment_tokenization( $order->get_id() ) ) {
			try {
				// Set transaction ID without marking the payment as complete
				$order->set_transaction_id( $transaction->id );
			} catch ( WC_Data_Exception $e ) {
				WC_PP()->log->add( __( 'An error occured while setting transaction id: %d on order %s. %s', $transaction->id, $order->get_id(), $e->getMessage() ) );
			}
			WC_Pre_Orders_Order::mark_order_as_pre_ordered( $order );
		} // Regular product
		else {
			// Register the payment on the order
			$order->payment_complete( $transaction->id );
		}

		// Write a note to the order history
		$order->note( sprintf( __( 'Payment authorized. Transaction ID: %s', 'woo-pensopay' ), $transaction->id ) );

		// Fallback to save transaction IDs since this has seemed to sometimes fail when using WC_Order::payment_complete
		self::save_transaction_id_fallback( $order, $transaction );

		do_action( 'woocommerce_pensopay_callback_payment_authorized', $order, $transaction );
	}

	/**
	 * @param WC_PensoPay_Order $subscription
	 * @param WC_PensoPay_Order $parent_order
	 * @param stdClass $transaction
	 */
	public static function subscription_authorized( $subscription, $parent_order, $transaction ) {
		$subscription->note( sprintf( __( 'Subscription authorized. Transaction ID: %s', 'woo-pensopay' ), $transaction->id ) );
		// Activate the subscription

		// Check if there is an initial payment on the subscription.
		// We are saving the total before completing the original payment.
		// This gives us the correct payment for the auto initial payment on subscriptions.
		$subscription_initial_payment = $parent_order->get_total();

		// Mark the payment as complete
		//$subscription->set_transaction_id($json->id);
		// Temporarily save the transaction ID on a custom meta row to avoid empty values in 3.0.
		update_post_meta( $subscription->get_id(), '_pensopay_transaction_id', $transaction->id );

		//$subscription->payment_complete($json->id);
		$subscription->set_transaction_order_id( $transaction->order_id );

		// Only make an instant payment if there is an initial payment
		if ( $subscription_initial_payment > 0 ) {
			// Check if this is an order containing a subscription
			if ( ! WC_PensoPay_Subscription::is_subscription( $parent_order->get_id() ) && $parent_order->contains_subscription() ) {
				// Process a recurring payment, but only if the subscription needs a payment.
				// This check was introduced to avoid possible double payments in case PensoPay sends callbacks more than once.
				if ( ( $wcs_subscription = wcs_get_subscription( $subscription->get_id() ) ) && $wcs_subscription->needs_payment() ) {
					WC_PP()->process_recurring_payment( new WC_PensoPay_API_Subscription(), $transaction->id, $subscription_initial_payment, $parent_order );
				}
			}
		}
		// If there is no initial payment, we will mark the order as complete.
		// This is usually happening if a subscription has a free trial.
		else {
			// Only complete the order payment if we are not changing payment method.
			// This is to avoid the subscription going into a 'processing' limbo.
			if ( empty( $transaction->variables->change_payment ) ) {
				$parent_order->payment_complete();
			}
		}

		do_action( 'woocommerce_pensopay_callback_subscription_authorized', $subscription, $parent_order, $transaction );
	}

	/**
	 * Common logic for authorized payments/subscriptions
	 *
	 * @param WC_PensoPay_Order $order
	 * @param stdClass $transaction
	 */
	public static function authorized( $order, $transaction ) {
		// Set the transaction order ID
		$order->set_transaction_order_id( $transaction->order_id );

		// Remove payment link
		$order->delete_payment_link();

		// Remove payment ID, now we have the transaction ID
		$order->delete_payment_id();
	}

	/**
	 * @param WC_PensoPay_Order $order
	 * @param stdClass $transaction
	 */
	public static function save_transaction_id_fallback( $order, $transaction ) {
		try {
			if ( ! empty( $transaction->id ) ) {
				$order->set_transaction_id( $transaction->id );
				$order->save();
			}
		} catch ( WC_Data_Exception $e ) {
			wc_get_logger()->error( $e->getMessage() );
		}
	}
}