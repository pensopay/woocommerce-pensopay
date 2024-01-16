<?php

class WC_PensoPay_Admin_Ajax_Empty_Logs extends WC_PensoPay_Admin_Ajax_Action {

	public function action(): string {
		return 'settings/empty-logs';
	}

	public function execute(): void {
		WC_PP()->log->clear();
		wp_send_json_success( [ 'message' => 'Logs successfully emptied.' ] );
	}

	protected function is_action_allowed(): bool {
		return apply_filters( "woocommerce_pensopay_api_is_{$this->action()}_allowed", woocommerce_pensopay_can_user_empty_logs() );
	}
}
