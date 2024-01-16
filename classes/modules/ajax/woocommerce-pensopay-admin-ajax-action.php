<?php

/**
 *
 */
abstract class WC_PensoPay_Admin_Ajax_Action {

	/**
	 * @return string
	 */
	abstract public function action(): string;

	/**
	 * @return void
	 */
	abstract public function execute(): void;

	/**
	 *
	 */
	public function __construct() {
		add_action( 'woocommerce_api_pensopay/admin/' . $this->action(), [ $this, 'validate' ] );
	}

	/**
	 * @return bool
	 */
	protected function is_action_allowed(): bool {
		return apply_filters( "woocommerce_pensopay_api_is_{$this->action()}_allowed", current_user_can( 'manage_woocommerce' ) );
	}

	/**
	 * @return void
	 */
	public function validate(): void {
		if ( ! $this->is_action_allowed() ) {
			wp_send_json_error( __( 'You are not allowed to perform this action', 'woo-pensopay' ) );
		}

		$this->execute();
	}
}
