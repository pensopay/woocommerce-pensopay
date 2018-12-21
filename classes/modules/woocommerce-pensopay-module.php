<?php

/**
 * Class WC_PensoPay_Module
 */
abstract class WC_PensoPay_Module {

	protected static $instances;

	/**
	 * WC_PensoPay_Module constructor.
	 */
	protected function __construct() {
		$this->hooks();
	}

	/**
	 * Adds hooks and filters
	 *
	 * @return mixed
	 */
	abstract public function hooks();

	/**
	 * @return mixed
	 */
	public static function get_instance() {
		$class = get_called_class();

		if ( ! isset( self::$instances[ $class ] ) ) {
			self::$instances[ $class ] = new $class;
		}

		return self::$instances[ $class ];
	}

	/**
	 *
	 */
	final private function __clone() {
	}

}