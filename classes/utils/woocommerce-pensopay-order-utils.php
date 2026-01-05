<?php

class WC_PensoPay_Order_Utils {
	public static function get_order( $mixed_entity ): ?WC_Order {
		if ( $mixed_entity instanceof WC_Order ) {
			return $mixed_entity;
		}

		switch ( true ) {
			case is_int( $mixed_entity ):
				return wc_get_order( $mixed_entity ) ?: null;
			case $mixed_entity instanceof WP_Post:
				return wc_get_order( $mixed_entity->ID ) ?: null;
			default:
				return null;
		}
	}

	public static function is_failed_renewal( WC_Order $order ): bool {
		$renewal_failure = false;

		if ( WC_PensoPay_Subscription::plugin_is_active() ) {
			$renewal_failure = ( WC_PensoPay_Subscription::is_renewal( $order ) and $order->get_status() === 'failed' );
		}

		return $renewal_failure;
	}

	/**
	 * contains_subscription function
	 *
	 * Checks if an order contains a subscription product
	 *
	 * @param WC_Order|int $order_id_or_object
	 *
	 * @return boolean
	 */
	public static function contains_subscription( $order_id_or_object ): bool {
		if ( WC_PensoPay_Subscription::plugin_is_active() ) {
			return (bool) wcs_order_contains_subscription( $order_id_or_object );
		}

		return false;
	}

	/**
	 * @param WC_Order|int $order_id_or_object
	 *
	 * @return bool
	 */
	public static function contains_switch_order( $order_id_or_object ): bool {
		if ( function_exists( 'wcs_order_contains_switch' ) ) {
			return (bool) wcs_order_contains_switch( $order_id_or_object );
		}

		return false;
	}

	/**
	 * Checks if the order switches a subscription from free to paid.
	 * In this case we would often require authorization of a subscription transaction in order to
	 * be able to authorize payments automatically in the future.
	 *
	 * @param $order
	 *
	 * @return bool
	 */
	public static function switches_from_free_to_paid( $order ): bool {
		if ( ! self::contains_switch_order( $order ) ) {
			return false;
		}

		if ( ! ( $subscription_id = $order->get_meta( '_subscription_switch' ) ) || ! ( $subscription = woocommerce_pensopay_get_subscription( $subscription_id ) ) ) {
			return false;
		}

		// Make sure to cast the return value as WC returns the total as a string regardless of context.
		$old_total = (float) $subscription->get_total();
		$new_total = (float) $order->get_total();

		return $old_total === 0.0 && $new_total > 0;
	}

    /**
     * @param WC_Order $order
     * @param string|null $message
     *
     * @return void
     */
	public static function add_note( WC_Order $order, ?string $message ): void {
		if ( $message ) {
			$order->add_order_note( 'PensoPay: ' . $message );
		}
	}

	public static function contains_virtual_products( WC_Order $order ): bool {
		$order_items = $order->get_items( 'line_item' );
		foreach ( $order_items as $order_item ) {
			if ( ( $order_item instanceof WC_Order_Item_Product ) && ( $product = $order_item->get_product() ) && $product->is_virtual() ) {
				return true;
			}
		}

		return false;
	}

	public static function contains_physical_products( WC_Order $order ): bool {
		$order_items = $order->get_items( 'line_item' );
		foreach ( $order_items as $order_item ) {
			if ( ( $order_item instanceof WC_Order_Item_Product ) && ( $product = $order_item->get_product() ) && ! $product->is_virtual() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * TODO: Implement as a filter or consider data migration to make this logic obsolete in the future
	 *
	 * @param WC_Order $order
	 *
	 * @return array|mixed|string
	 */
	public static function get_transaction_id( WC_Order $order ) {
		// Search for custom transaction meta added in 4.8 to avoid transaction ID
		// sometimes being empty on subscriptions in WC 3.0.
		$transaction_id = $order->get_meta( '_pensopay_transaction_id' );
		if ( empty( $transaction_id ) ) {

			$transaction_id = $order->get_transaction_id();

			if ( empty( $transaction_id ) ) {
				// Search for original transaction ID. The transaction might be temporarily removed by
				// subscriptions. Use this one instead (if available).
				$transaction_id = $order->get_meta( '_transaction_id_original' );
				if ( empty( $transaction_id ) ) {
					// Check if the old legacy TRANSACTION ID meta value is available.
					$transaction_id = $order->get_meta( 'TRANSACTION_ID' );
				}
			}
		}

		return $transaction_id ?: null;
	}

	/**
	 * get_clean_order_number function
	 *
	 * Returns the order number without leading #
	 *
	 * @access public
	 *
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	public static function get_clean_order_number( WC_Order $order ): string {
		return str_replace( '#', '', $order->get_order_number() );
	}
}
