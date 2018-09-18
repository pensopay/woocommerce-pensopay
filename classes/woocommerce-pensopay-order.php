<?php

/**
 * WC_PensoPay_Order class
 *
 * @class        WC_PensoPay_Order
 * @version        1.0.0
 * @package        Woocommerce_PensoPay/Classes
 * @category    Class
 * @author        PensoPay
 */

include_once WCPP_PATH . 'classes/base/woocommerce-pensopay-base-order.php';

if (version_compare( WC_VERSION, '3.0', '<' )) {
    /**
     * Class WC_PensoPay_Order
     * 
     * Used for legacy support WC <= 2.6.14
     */
    class WC_PensoPay_Order extends WC_PensoPay_Base_Order
    {
        /**
         * @return mixed|string
         */
        public function get_transaction_id( )
        {
            return $this->base_get_transaction_id();
        }

        /**
         * Sets the order transaction id
         * @param $transaction_id
         */
        public function set_transaction_id($transaction_id)
        {
            update_post_meta($this->id, '_transaction_id', $transaction_id);
        }
    }
} else {
    /**
     * Class WC_PensoPay_Order
     * 
     * Used for WC >= 3.0
     */
    class WC_PensoPay_Order extends WC_PensoPay_Base_Order
    {
        /**
         * @param string $context
         * @return mixed|string
         */
        public function get_transaction_id($context = 'view')
        {
            return $this->base_get_transaction_id($context);
        }
    }
}
