<?php
/**
 * WC_PensoPay_API_Subscription class
 *
 * @class 		WC_PensoPay_API_Subscription
 * @since		4.0.0
 * @package		Woocommerce_PensoPay/Classes
 * @category	Class
 * @author 		PensoPay
 * @docs        http://tech.pensopay.net/api/services/?scope=merchant
 */

class WC_PensoPay_API_Subscription extends WC_PensoPay_API_Transaction
{
  	/**
	* __construct function.
	*
	* @access public
	* @return void
	*/
    public function __construct( $resource_data = NULL )
    {
    	// Run the parent construct
    	parent::__construct();

    	// Set the resource data to an object passed in on object instantiation.
    	// Usually done when we want to perform actions on an object returned from
    	// the API sent to the plugin callback handler.
  		if( is_object( $resource_data ) )
  		{
  			$this->resource_data = $resource_data;
  		}

    	// Append the main API url
        $this->api_url .= 'subscriptions/';
    }


   	/**
	* create function.
	*
	* Creates a new subscription via the API
	*
	* @access public
	* @param  WC_PensoPay_Order $order
	* @return object
	* @throws PensoPay_API_Exception
	*/
    public function create( WC_PensoPay_Order $order )
    {
        return parent::create( $order );
    }


   	/**
	* recurring function.
	*
	* Sends a 'recurring' request to the PensoPay API
	*
	* @access public
	* @param  int $transaction_id
	* @param  int $amount
	* @return $request
	* @throws PensoPay_API_Exception
	*/
    public function recurring( $subscription_id, $order, $amount = NULL)
    {
        // Check if a custom amount ha been set
        if( $amount === NULL )
        {
            // No custom amount set. Default to the order total
            $amount = WC_Subscriptions_Order::get_recurring_total( $order );
        }

        if( ! $order instanceof WC_PensoPay_Order ) {
			$order_id = $order->get_id();
            $order = new WC_PensoPay_Order( $order_id );
        }

        $order_number = $order->get_order_number_for_api( $is_recurring = TRUE );

    	$request = $this->post( sprintf( '%d/%s?synchronized', $subscription_id, "recurring" ), array(
            'amount' => WC_PensoPay_Helper::price_multiply( $amount ),
            'order_id' => sprintf('%s', $order_number ),
            'auto_capture' => $order->get_autocapture_setting(),
            'autofee' => WC_PensoPay_Helper::option_is_enabled( WC_PP()->s( 'pensopay_autofee' ) ),
            'text_on_statement' => WC_PP()->s('pensopay_text_on_statement'),
            'order_post_id' => $order->get_id(),
        ), TRUE );

        return $request;
    }


  	/**
	* cancel function.
	*
	* Sends a 'cancel' request to the PensoPay API
	*
	* @access public
	* @param  int $subscription_id
	* @return void
	* @throws PensoPay_API_Exception
	*/
    public function cancel( $subscription_id )
    {
    	$this->post( sprintf( '%d/%s', $subscription_id, "cancel" ) );
    }


    /**
    * is_action_allowed function.
    *
    * Check if the action we are about to perform is allowed according to the current transaction state.
    *
    * @access public
    * @return boolean
    */
    public function is_action_allowed( $action )
    {
        $state = $this->get_current_type();

        $allowed_states = array(
            'cancel' => array( 'authorize' ),
            'standard_actions' => array( 'authorize' )
        );

        return array_key_exists( $action, $allowed_states ) AND in_array( $state, $allowed_states[$action] );
    }
}