<?php

/**
 * Class WC_PensoPay_Orders
 */
class WC_PensoPay_Subscriptions extends WC_PensoPay_Module {

	/**
	 * @return void
	 */
	public function hooks() {
		add_action( 'woocommerce_pensopay_callback_subscription_authorized', [ $this, 'on_subscription_authorized' ], 5, 3 );
	}

	/**
	 * @param WC_PensoPay_Order $subscription
	 * @param WC_PensoPay_Order $parent_order
	 * @param object $transaction
	 */
	public function on_subscription_authorized( $subscription, $parent_order, $transaction ) {
		if ( function_exists( 'wcs_get_subscriptions_for_order' ) && ! WC_PensoPay_Subscription::is_subscription( $parent_order->get_id() ) ) {
			$subscriptions = wcs_get_subscriptions_for_order( $parent_order, [ 'order_type' => 'any' ] );

			if ( ! empty( $subscriptions ) ) {
				foreach ( $subscriptions as $sub ) {
					if ( $subscription && $subscription->get_id() === $sub->get_id() ) {
						continue;
					}

					update_post_meta( $sub->get_id(), '_pensopay_transaction_id', $transaction->id );
				}
			}
		}
	}

}
