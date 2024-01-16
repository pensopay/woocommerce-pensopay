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
	 * @param WC_Subscription $subscription
	 * @param WC_Order $parent_order
	 * @param object $transaction
	 */
	public function on_subscription_authorized( WC_Subscription $subscription, WC_Order $parent_order, $transaction ): void {
		if ( function_exists( 'wcs_get_subscriptions_for_order' ) && ! WC_PensoPay_Subscription::is_subscription( $parent_order->get_id() ) ) {
			$subscriptions = wcs_get_subscriptions_for_order( $parent_order, [ 'order_type' => 'any' ] );

			if ( ! empty( $subscriptions ) ) {
				foreach ( $subscriptions as $sub ) {
					if ( $subscription && $subscription->get_id() === $sub->get_id() ) {
						continue;
					}

					$sub->get_meta( '_pensopay_transaction_id' );
				}
			}
		}
	}
}
