<?php

class WC_PensoPay_Subscriptions_Early_Renewals extends WC_PensoPay_Module {

	public function hooks() {
		add_action( 'woocommerce_pensopay_scheduled_subscription_payment_after', [ $this, 'maybe_check_payment_status' ], 10, 3 );
	}

	/**
	 * @param WC_Subscription $subscription
	 * @param WC_Order $renewal_order
	 * @param $response
	 *
	 * @return void
	 */
	public function maybe_check_payment_status( WC_Subscription $subscription, WC_Order $renewal_order, $response ): void {
		if ( $this->should_payment_status_be_checked( $response, $renewal_order ) ) {
			$max_checks           = apply_filters( 'woocommerce_pensopay_early_renewal_payment_checks_limit', 10 );
			$delay_between_checks = apply_filters( 'woocommerce_pensopay_early_renewal_payment_checks_delay', 3 );
			$is_paid              = false;

			try {
				while ( ! $is_paid ) {
					$is_paid = $this->check_if_paid( $renewal_order, $max_checks, 1, $delay_between_checks );
				}
			} catch ( RuntimeException $e ) {
				// NOOP
			}
		}
	}

	/**
	 * Recursively check if the early renewal orders has been paid or not
	 *
	 * @param WC_Order $renewal_order
	 * @param int $max_checks
	 * @param int $checks_count
	 * @param int $delay_between_checks
	 *
	 * @return bool
	 */
	protected function check_if_paid( WC_Order $renewal_order, int $max_checks, int $checks_count = 1, int $delay_between_checks = 0 ): bool {
		if ( $checks_count > $max_checks ) {
			return true;
		}

		// Update the order object to check if any changes have been made to the order object from i.e. callbacks
		$renewal_order->get_data_store()->read( $renewal_order );

		// If the order has switched status to failed, we will throw an exception
		// to break out of the check loop.
		if ( $renewal_order->has_status( 'failed' ) ) {
			throw new RuntimeException();
		}

		// Check if the order still needs payment
		// If so, we will run an optional delay before checking again
		if ( $renewal_order->needs_payment() ) {
			if ( $delay_between_checks ) {
				sleep( $delay_between_checks );
			}

			// Check again
			return $this->check_if_paid( $renewal_order, $max_checks, $checks_count + 1, $delay_between_checks );
		}

		return true;
	}

	private function should_payment_status_be_checked( $response, WC_Order $order ) {
		$check = function_exists( 'wcs_order_contains_early_renewal' ) && wcs_order_contains_early_renewal( $order ) && $order->needs_payment();

		return apply_filters( 'woocommerce_pensopay_scheduled_subscription_payment_check_status_after_payment', $check, $response, $order );
	}
}
