<?php
/**
* Update WC_PensoPay to 4.6
*
* @author 	PensoPay
* @version  1.0
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Ignore user aborts and allow the script to run forever if supported
ignore_user_abort( TRUE );
set_time_limit( 0 );


global $wpdb;

if (WC_PensoPay_Helper::is_HPOS_enabled()) {
	$subscriptions = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wc_orders p WHERE p.type = 'shop_subscription' AND p.status NOT IN ('draft', 'trash') AND NOT EXISTS(SELECT 1 FROM {$wpdb->prefix}wc_orders_meta pm WHERE p.id=pm.order_id AND pm.meta_key IN ('_transaction_id', 'TRANSACTION_ID'))", OBJECT );
} else {
	$subscriptions = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} p WHERE p.post_type = 'shop_subscription' AND p.post_status NOT IN ('draft', 'trash') AND NOT EXISTS(SELECT 1 FROM {$wpdb->postmeta} pm WHERE p.ID=pm.post_id AND pm.meta_key IN ('_transaction_id', 'TRANSACTION_ID'))", OBJECT );
}

if (!empty($subscriptions)) {
    foreach( $subscriptions as $subscription_post ) {
        // Change from DB object to a PP Order object
        $subscription = new WC_PensoPay_Order($subscription_post->ID);
        // Create order object
        $order = new WC_PensoPay_Order($subscription_post->post_parent);
        $transaction_id = $order->get_transaction_id();

        $order_id = $order->get_id();
        $subscription_id = $subscription->get_id();

        if (!empty($transaction_id) && $order->has_pensopay_payment()) {
            $logger = new WC_PensoPay_Log();
            $transaction = new WC_PensoPay_API_Subscription();

            try {
                // Check if the transaction ID is actually a transaction of type subscription. If not, an exception will be thrown.
                $response = $transaction->get($transaction_id);

                // Set the transaction ID on the parent order
                $subscription->set_transaction_id($transaction_id);
                

                // Cleanup: Remove the IDs from the parent order
                delete_post_meta($order_id, '_transaction_id', $transaction_id);
                delete_post_meta($order_id, 'TRANSACTION_ID', $transaction_id);
                

                $logger->add(sprintf('Migrated transaction (%d) from parent order ID: %s to subscription order ID: %s', $transaction_id, $subscription_id, $order_id));
            } catch( PensoPay_API_Exception $e ) {
                $logger->add(sprintf('Failed migration of transaction (%d) from parent order ID: %s to subscription order ID: %s. Error: %s', $transaction_id, $subscription_id, $order_id, $e->getMessage()));
            }
        }
    }
}

