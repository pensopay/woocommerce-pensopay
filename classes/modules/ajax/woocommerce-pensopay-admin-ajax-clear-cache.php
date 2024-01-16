<?php

class WC_PensoPay_Admin_Ajax_Clear_Cache extends WC_PensoPay_Admin_Ajax_Action {

	public function action(): string {
		return 'settings/clear-cache';
	}

	public function execute(): void {
		global $wpdb;

		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wcpp_transaction_%' OR option_name LIKE '_transient_timeout_wcpp_transaction_%'" );

		wp_send_json_success( [ 'message' => 'The transaction cache has been cleared.' ] );
	}

	protected function is_action_allowed(): bool {
		return apply_filters( "woocommerce_pensopay_api_is_{$this->action()}_allowed", woocommerce_pensopay_can_user_flush_cache() );
	}
}
