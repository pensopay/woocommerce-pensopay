<?php
/**
 * Ensure that payments.quickpay.net is a trusted redirect host
 */
add_filter( 'allowed_redirect_hosts', static function ( array $hosts ): array {
	$hosts[] = 'payment.quickpay.net';

	return $hosts;
} );
