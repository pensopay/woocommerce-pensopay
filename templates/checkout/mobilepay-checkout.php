<?php
/**
 * Possible themes: darkblue, mpblue, white, whitestroke
 */
$mobilepay_theme = strtolower( apply_filters( 'woocommerce_pensopay_mobilepay_checkout_button_theme', 'mpblue' ) );

/**
 * Possible sizes: small, medium, large
 */
$mobilepay_size = strtolower( apply_filters( 'woocommerce_pensopay_mobilepay_checkout_button_size', 'medium' ) );

$button_base_src = WC_PP()->plugin_url( 'assets/images/mobilepay-checkout/' . $mobilepay_theme . '/' . $mobilepay_size );

?>
<div class="mobilepay-checkout">
    <h3 class="mobilepay-checkout__headline"><?php echo apply_filters( 'woocommerce_pensopay_mobilepay_checkout_checkout_headline', __( 'Fast checkout with MobilePay Checkout', 'woo-quickpay' ) ) ?></h3>
    <p class="mobilepay-checkout__text"><?php echo apply_filters( 'woocommerce_pensopay_mobilepay_checkout_checkout_text', __( 'Go directly to the MobilePay window with MobilePay Checkout. We will automatically receive your billing- and shipping information from MobilePay.', 'woo-quickpay' ) ) ?></p>
	<p>
		<a href="#" class="mobilepay-checkout--force">
			<img src="<?php echo $button_base_src ?>@1x.png" srcset="<?php echo $button_base_src ?>@2x.png 2x" />
		</a>
	</p>
</div>