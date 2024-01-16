<?php

/**
 * WC_PensoPay_Log class
 *
 * @class 		WC_PensoPay_Log
 * @since		4.0.0
 * @package		Woocommerce_PensoPay/Classes
 * @category	Logs
 * @author 		PensoPay
 */
class WC_PensoPay_Log {

	/* The domain handler used to name the log */
	private $_domain = 'woocommerce-pensopay';


	/* The WC_Logger instance */
	private $_logger;


	/**
	 * __construct.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
        $this->_logger = new WC_Logger();
    }


	/**
	 * add function.
	 *
	 * Uses the build in logging method in WooCommerce.
	 * Logs are available inside the System status tab
	 *
	 * @access public
	 *
	 * @param      $param
	 * @param null $file
	 * @param null $line
	 *
	 * @return void
	 */
	public function add( $param, $file = null, $line = null ) {
		if ( ! is_array( $param ) ) {
			$message = $param;
		} else {
			$message = '';
		}

		if ( $file ) {
			$message .= sprintf( 'File: %s -> ', $file );
		}

		if ( $line ) {
			$message .= sprintf( 'Line: %s -> ', $line );
		}

		if ( is_array( $param ) ) {
			$message .= print_r( $param, true );
		}

		$this->_logger->add( $this->_domain, $message );
	}


	/**
	 * clear function.
	 *
	 * Clears the entire log file
	 *
	 * @access public
	 * @return void
	 */
	public function clear() {
		return $this->_logger->clear( $this->_domain );
	}


	/**
	 * separator function.
	 *
	 * Inserts a separation line for better overview in the logs.
	 *
	 * @access public
	 * @return void
	 */
	public function separator() {
		$this->add( '--------------------' );
	}


	/**
	 * get_domain function.
	 *
	 * Returns the log text domain
	 *
	 * @access public
	 * @return string
	 */
	public function get_domain() {
		return $this->_domain;
	}

	/**
	 * Returns a link to the log files in the WP backend.
	 */
	public function get_admin_link() {
		$log_path       = wc_get_log_file_path( $this->_domain );
		$log_path_parts = explode( '/', $log_path );

		return add_query_arg( [
			'page'     => 'wc-status',
			'tab'      => 'logs',
			'log_file' => end( $log_path_parts )
		], admin_url( 'admin.php' ) );
	}
}