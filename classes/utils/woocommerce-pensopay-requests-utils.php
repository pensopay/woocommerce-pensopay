<?php

class WC_PensoPay_Requests_Utils {
	/**
	 * Check if the current request is trying to change the payment gateway
	 * @return bool
	 */
	public static function is_request_to_change_payment(): bool {
		$is_request_to_change_payment = false;

		if ( WC_PensoPay_Subscription::plugin_is_active() ) {
			$is_request_to_change_payment = WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment;

			if ( ! $is_request_to_change_payment && ! empty( $_GET['pensopay_change_payment_method'] ) ) {
				$is_request_to_change_payment = true;
			}
		}

		return apply_filters( 'woocommerce_pensopay_is_request_to_change_payment', $is_request_to_change_payment );
	}

	public static function is_current_admin_screen( string ...$screen_ids ): bool {
		$screen = get_current_screen();

		return $screen && in_array( $screen->id, $screen_ids, true );
	}

	public static function get_edit_order_screen_id(): string {
		return WC_PensoPay_Helper::is_HPOS_enabled() ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order';
	}

	public static function get_edit_subscription_screen_id(): string {
		return WC_PensoPay_Helper::is_HPOS_enabled() && function_exists( 'wcs_get_page_screen_id' )? wcs_get_page_screen_id( 'shop-subscription' ) : 'shop_subscription';
	}
}
