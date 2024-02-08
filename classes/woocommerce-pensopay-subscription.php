<?php
/**
 * WC_PensoPay_Subscription class
 *
 * @class 		WC_PensoPay_Subscription
 * @version		1.0.0
 * @package		Woocommerce_PensoPay/Classes
 * @category	Class
 * @author 		PensoPay
 */

class WC_PensoPay_Subscription {

	/**
	 * Checks if a subscription is up for renewal.
	 * Ensures backwards compatibility.
	 *
	 * @access public static
	 *
	 * @param WC_Order|int $order [description]
	 *
	 * @return boolean
	 */
	public static function is_renewal( $order ): bool {
		if ( function_exists( 'wcs_order_contains_renewal' ) ) {
			return wcs_order_contains_renewal( $order );
		}

		return false;
	}

	/**
	 * Checks if Woocommerce Subscriptions is enabled or not
	 * @return bool
	 */
	public static function plugin_is_active(): bool {
		return class_exists( 'WC_Subscriptions' ) && WC_Subscriptions::$name = 'subscription';
	}

	/**
	 * Convenience wrapper for wcs_cart_contains_failed_renewal_order_payment
	 *
	 * @return array|bool
	 */
	public static function cart_contains_failed_renewal_order_payment() {
		if ( function_exists( 'wcs_cart_contains_failed_renewal_order_payment' ) ) {
			return wcs_cart_contains_failed_renewal_order_payment();
		}

		return false;
	}

	/**
	 * Convenience wrapper for wcs_cart_contains_renewal
	 *
	 * @return false|string
	 */
	public static function cart_contains_renewal() {
		if ( function_exists( 'wcs_cart_contains_renewal' ) ) {
			return wcs_cart_contains_renewal();
		}

		return false;
	}

	/**
	 * @param $order
	 *
	 * @return bool
	 */
	public static function order_contains_early_renewal( $order ): bool {
		if ( function_exists( 'wcs_order_contains_early_renewal' ) ) {
			return wcs_order_contains_early_renewal( $order );
		}

		return false;
	}

	/**
	 * Convenience wrapper for wcs_get_subscriptions_for_renewal_order
	 *
	 * @param $order
	 * @param bool $single - to return a single item or not
	 *
	 * @return WC_Subscription|WC_Subscription[]
	 */
	public static function get_subscriptions_for_renewal_order( $order, bool $single = false ) {
		if ( function_exists( 'wcs_get_subscriptions_for_renewal_order' ) ) {
			$subscriptions = wcs_get_subscriptions_for_renewal_order( $order );

			return $single ? end( $subscriptions ) : $subscriptions;
		}

		return [];
	}

	/**
	 * Convenience wrapper for wcs_get_subscriptions_for_order
	 *
	 * @param $order
	 *
	 * @return WC_Subscription[]
	 */
	public static function get_subscriptions_for_order( $order, $args = [] ): array {
		if ( function_exists( 'wcs_get_subscriptions_for_order' ) ) {
			return wcs_get_subscriptions_for_order( $order, $args );
		}

		return [];
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return int|null
	 */
	public static function get_subscription_id( WC_Order $order ): ?int {
		$order_id = $order->get_id();
		if ( self::is_subscription( $order_id ) ) {
			return $order_id;
		}

		if ( WC_PensoPay_Order_Utils::contains_subscription( $order ) ) {
			// Find all subscriptions
			$subscriptions = self::get_subscriptions_for_order( $order_id );
			// Get the last one and base the transaction on it.
			// Fetch the post ID of the subscription, not the parent order.
			return end( $subscriptions )->get_id();
		}

		// Check if it is a renewal order and return the subscription ID from it.
		if ( self::is_renewal( $order ) && $subscriptions = self::get_subscriptions_for_order( $order, [ 'order_type' => [ 'renewal' ] ] ) ) {
			return end( $subscriptions )->get_id();
		}
		return null;
	}

	public static function get_subscription( $entity ): ?WC_Subscription {
		if ( ! self::plugin_is_active() ) {
			return null;
		}

		if ( ! is_object( $entity ) ) {
			return wcs_get_subscription( $entity );
		}

		$order_type = OrderUtil::get_order_type( $entity );

		if ( 'shop_subscription' === $order_type ) {
			return $entity;
		}

		if ( 'shop_order' === $order_type ) {
			$subscriptions = self::get_subscriptions_for_order( $entity );

			if ( ! empty( $subscriptions ) ) {
				return end( $subscriptions );
			}
		}

		return null;
	}

	/**
	 * Check if a given object is a WC_Subscription (or child class of WC_Subscription), or if a given ID
	 * belongs to a post with the subscription post type ('shop_subscription')
	 *
	 * @param $subscription
	 *
	 * @return bool
	 */
	public static function is_subscription( $subscription ): bool {
		if ( function_exists( 'wcs_is_subscription' ) ) {
			return wcs_is_subscription( $subscription );
		}

		return false;
	}

	/**
	 * Checks if the current cart has a switch product
	 * @return bool
	 */
	public static function cart_contains_switches(): bool {
		if ( class_exists( 'WC_Subscriptions_Switcher' ) && method_exists( 'WC_Subscriptions_Switcher', 'cart_contains_switches' ) ) {
			return WC_Subscriptions_Switcher::cart_contains_switches() !== false;
		}

		return false;
	}
}
