=== WooCommerce PensoPay ===
Contributors: PensoPay
Tags: gateway, woocommerce, pensopay, payment, psp
Requires at least: 4.0.0
Tested up to: 6.6.1
Stable tag: trunk
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrates your PensoPay payment gateway into your WooCommerce installation.

== Description ==
With WooCommerce PensoPay, you are able to integrate your PensoPay gateway to your WooCommerce install. With a wide list of API features including secure capturing, refunding and cancelling payments directly from your WooCommerce order overview. This is only a part of the many features found in this plugin.

== Installation ==
1. Upload the 'woocommerce-pensopay' folder to /wp-content/plugins/ on your server.
2. Log in to Wordpress administration, click on the 'Plugins' tab.
3. Find WooCommerce PensoPay in the plugin overview and activate it.
4. Go to WooCommerce -> Settings -> Payment Gateways -> PensoPay.
5. Fill in all the fields in the "PensoPay account" section and save the settings.
6. You are good to go.

== Dependencies ==
General:
1. PHP: >= 5.4
2. WooCommerce >= 3.0
3. If WooCommerce Subscriptions is used, the required minimum version is >= 2.0

== Changelog ==
= 7.1.2 =
* Bump

= 7.1.1 =
* Feature: Show warning when attempting to refund an entire order instead of individual items
* Feature: Add support for Checkout Blocks
* Fix for subscription failed payments and submit new card at the same time
* Fix to allow for email template editing from the admin
* Add support for Checkout Blocks
* Show warning when attempting to refund an entire order instead of individual items
* Add translation
* Tested version bump
* Fix README.txt format

= 7.1.0 =
* Remove MobilePay Checkout after deprecation

= 7.0.6 =
* Fix subscription bug related to wrong meta key being fetched

= 7.0.5 =
* Bug fix with subscription renewals in updating payment method after failed payment
* Deprecated functions fix

= 7.0.4 =
* Feat: HPOS (High Performance Order Storage)
* Feat: Support for Finnish payment window.
* Fix: Proper restrictions for some payment method countries/currencies.
* Fix: Auto currency
* Fix: MP Subs renewals, update to latest
* Fix: Remove ISK from list of non-decimal currencies as the QuickPay API requires ISK amount to be multiplied
* Fix: Update autofee helper text.
* Fix: Bump tested WP version number to 6.3
* Fix: Bump tested WC version number to 8.1
* Fix: Manually creating a payment link from wp-admin on subscriptions with empty transaction IDs could lead to errors on link generation
* Fix: Problem with transaction fee from callbacks triggering an error when setting it on the order object.
* Fix: Remove strict return type from WC_Pensopay_Paypal::apply_gateway_icons
* Feat: Added support for High Performance Order Storage / Custom Order Tables
* Feat: Added template for meta-box-order.php
* Feat: Added template for meta-box-subscription.php
* Feat: Added support for Early Renewals modal
* Fix: Added payment.quickpay.net as a whitelisted host to avoid problems with wp_safe_redirect when changing payment method in WCS 5.1.0 and above.
* Fix: Adjust the link to payment methods documentation
* Fix: WC_Pensopay::remove_renewal_meta_data wasn't removing subscription meta data from renewal orders properly.
* Fix: 'Create payment' now patches transactions in 'initial' state and creates new payments in case they have already been authorized.
* Fix: 'Create payment' now ensures unique order numbers by adding a random string to the order number before sending it to the API. This fixes problems with duplicate order number errors from the API.
* Dev: Refactor order logic in general which means we are deprecating the WC_Pensopay_Order object and its methods. For better compatibility, and to avoid overhead, we are solely relying on the WC_Order object.
* Dev: Introducing utility helper classes used to replace logic in the WC_Pensopay_Order object
* Dev: Bump minimum required version of WooCommerce to 7.1.0
* Dev: Bump minimum required version of WooCommerce Subscriptions to 5.0
* Dev: Bump minimum required version of PHP to 7.4
* Fix: Avoid requesting quickpay_fetch_private_key on all order / subscription related pages.
* Fix: Add fees to basket items array
* Fix: Refactor WC_Pensopay_Order::get_transaction_basket_params_line_helper
* Fix: Remove shipping[tracking_number] and shipping[tracking_url] by default as they were empty anyway and resulted in problems with Resurs payments
* Fix: Vipps - adjust payment method to "vipps,vippspsp"
* Dev: Introducing filter woocommerce_pensopaypay_transaction_params_basket_apply_fees
* Fix: Rely on auto_capture_at instead of due_date for MPS payments
* Fix: Enhance the way auto_capture_at is calculated. It now relies on the timezone used in WordPress but can be changed with the filter woocommerce_pensopaypay_mps_timezone
* Feat: MobilePay Subscriptions - setting added to control status transition when a payment agreement is cancelled outside WooCommerce.
* Dev: add filter woocommerce_pensopaypay_mps_cancelled_from_status
* Dev: add filter woocommerce_pensopaypay_mps_cancel_agreement_status_options
* Fix: Bump tested with WC version to 6.6
* Fix: Bump tested with WP version to 6.0
* Feat: Anyday - hide gateway if currency is not DKK and if cart total is not within 300 - 30.000
* Fix: Remove VISA Electron card logo
* Feat: Add Google Pay as payment gateway
* Fix: Adjust SVG icons for Paypal, Apple Pay and Klarna to show properly in Safari
* Feat: Only show Apple Pay in Safari browsers
* Fix: MobilePay Subscription gateway is now available when using the "Change Payment" option from the account page.
* Feat: Add Apple Pay gateway - works only in Safari.
* Feat: Show a more user-friendly error message when payments fail in the callback handler.
* Dev: Add new filter woocommerce_pensopaypay_checkout_gateway_icon
* Fix: Bump WC + WP tested with versions to latest versions
* Dev: Add WC_Pensopay_Countries::getAlpha2FromAlpha3
* Fix: Use alpha2 country code instead of alpha3 country code in MP Checkout callbacks
* Fix: Modify force checkout logic used for MobilePay Checkout to enhance theme support.
* Fix: WC_Pensopay_API_Transaction::get_brand removes prefixed pensopaypay_ when fallback to variables.
* Fix: Refund now supports location header to avoid wrong response messages when capturing Klarna and Anyday payments.
* Dev: Add filter woocommerce_pensopaypay_transaction_params
* Dev: Add filter woocommerce_pensopaypay_transaction_params_description
* Bump WC tested with version
* Bump WP tested with version
* Feat: MobilePay Checkout now automatically ticks the terms and condition field during checkout.
* Fix: PHP8 compatability
* Fix: Capture now supports location header to avoid wrong response messages when capturing Klarna and Anyday payments.
* Fix: WC_Pensopay_API_Transaction::get_brand now falls back to variables.payment_methods sent from the shop if brand is empty on metadata.

= 6.3.3 =
* Compatibility test with WC 8.1

= 6.3.2 = 
* Fix: Sanitize pensopay_action in pensopay_manual_transaction_actions handler
* Fix: Remove final from private __clone() method to get rid of php >= 8 warning
* Fix: Pass currency to price_normalize for refund notice

= 6.3.1 =
* Fix: Better handling of vipps
* Fix: Issue where operations array would be null

= 6.3.0 =
* Fix: Rely on auto_capture_at instead of due_date for MPS payments
* Fix: Enhance the way auto_capture_at is calculated. It now relies on the timezone used in WordPress but can be changed with the filter woocommerce_pensopay_mps_timezone
* Feat: MobilePay Subscriptions - setting added to control status transition when a payment agreement is cancelled outside WooCommerce.
* Dev: add filter woocommerce_pensopay_mps_cancelled_from_status
* Dev: add filter woocommerce_pensopay_mps_cancel_agreement_status_options
* Fix: Bump tested with WC version to 6.6
* Fix: Bump tested with WP version to 6.0
* Feat: Anyday - hide gateway if currency is not DKK and if cart total is not within 300 - 30.000
* Fix: Remove VISA Electron card logo
* Feat: Add Google Pay as payment gateway
* Fix: Adjust SVG icons for Paypal, Apple Pay and Klarna to show properly in Safari
* Feat: Add Apple Pay gateway - works only in Safari.
* Fix: MobilePay Subscription gateway is now available when using the "Change Payment" option from the account page.
* Feat: Show a more user-friendly error message when payments fail in the callback handler.
* Dev: Add new filter woocommerce_pensopay_checkout_gateway_icon
* Dev: Add WC_PensoPay_Countries::getAlpha2FromAlpha3
* Fix: Use alpha2 country code instead of alpha3 country code in MP Checkout callbacks
* Fix: Modify force checkout logic used for MobilePay Checkout to enhance theme support.
* Fix: WC_PensoPay_API_Transaction::get_brand removes prefixed penso_ when fallback to variables.
* Fix: Refund now supports location header to avoid wrong response messages when capturing Klarna and Anyday payments.
* Fix: Capture now supports location header to avoid wrong response messages when capturing Klarna and Anyday payments.
* Dev: Add filter woocommerce_pensopay_transaction_params
* Dev: Add filter woocommerce_pensopay_transaction_params_description
* Feat: MobilePay Checkout now automatically ticks the terms and condition field during checkout.
* Fix: PHP8 compatability
* Fix: WC_PensoPay_API_Transaction::get_brand now falls back to variables.payment_methods sent from the shop if brand is empty on metadata.
* Feature: Anyday split payments as payment gateway.
* Feature: MobilePay Checkout now shows the description as copy in checkout/mobilepay-checkout.php by default which makes it easier by merchants to adjust their communication.

= 6.2.1 =
* Fix issue where settings would not be saved

= 6.2.0 =
* Remove: Bitcoin through Coinify

= 6.1.0 =
* Feature: New setting 'Cancel payments on order cancellation' allows merchants to automatically cancel payments when an order is cancelled. Disabled by default.
* Fix: Orders with multiple subscriptions didn't get the subscription transaction stored on every subscription.

= 6.0.3 =
* Fix: Danish translations not being loaded when enabled.
* Fix: Balance with decimals were incorrectly shown on "Capture Full Amount" button
* Fix: Bump 'tested with' versions

= 6.0.2 =
* Fix: Setting "Complete renewal orders" triggered on regular orders as well when enabled.

= 6.0.1 =
* Fix: Callbacks not being properly handled for non-subscription transactions

= 6.0.0 =
* Feature: MobilePay Subscriptions gateway.
* Feature: New setting 'Complete order on capture callbacks' - Completes an order when a callback regarding a captured payment is received from QuickPay.
* Feature: Add support for WCML country specific gateways added in WCML 4.10 (https://wpml.org/announcements/2020/08/wcml-4-10-currencies-and-payment-options-based-on-location/)
* Change: Recurring payments are no longer synchronized due to ?synchronization being deprecated.
* Fix: Undefined property: stdClass::$payment_method in WC_PensoPay_MobilePay_Checkout::callback_save_address
* Fix: Hide balance amount field when payment cannot be captured
* Fix: Show MobilePay logo as "Method" in the order list
* Breaking Change: Embedded / Overlay payments have been removed due to PSD2. Contact support@pensopay.com for questions regarding this decision.
* Developer: Add filter woocommerce_pensopay_create_recurring_payment_data
* Developer: Add filter woocommerce_pensopay_create_recurring_payment_data_{payment_gateway_id}
* Developer: Add filter woocommerce_pensopay_callback_payment_authorized_complete_payment
* Developer: Removed WC_PensoPay_Subscription::process_recurring_response as the logic has been refactored into hooks and callback handlers.

= 5.8.7 =
* Add Anyday split

= 5.8.6 =
* Fix as issue where paypal payments couldn't be processed

= 5.8.5 =
* Fix issue with basket lines and VAT

= 5.8.4 =
* Remove embedded payments due to PSD2 issues

= 5.8.3 =
* Re-add "Capture on complete" option

= 5.8.2 =
* Fix MobilePay Subscriptions.
* MBP Subscriptions now only show if a subscription product is involved.

= 5.8.1 =
* Fix Callback issue that doesn't check for true transaction status.
* Added a possible fix for an issue of getting the proper transaction ID when multiple exist.
* Added an opt-in option that allows people with the "Subscriptions Add-on for WooCommerce" to properly checkout.
* Initial support for MobilePay Subscriptions
* Little embedded window styling

= 5.8.0 =
* Fix pricing calculation for Klarna Payments and proper 'basket' for QP. Accounts for discounts too.

= 5.7.9 =
* Validate callback sooner

= 5.7.8 =
* Fix basket item price to be the actual item price (w/ discount) and not the product price.
* The above also fixes an issue with Klarna Payments when using a discount code.

= 5.7.7 =
* Klarna Payments gateway shipping restore

= 5.7.6 =
* Fix issue occuring when customer didn't use woocommerce subscriptions plugin

= 5.7.5 =
* Validation problems when using MobilePay Checkout due to new validation error code grouping on WC
* Added Klarna Payments
* Renamed Virtual Terminal menu item and changed its position to a lower place.
* Fixed an error where Virtual Terminal payments would not obey the language option.
* Gateway gets language from active WPML language
* Embedded window test with latest versions

= 5.7.4 =
* Tested up to 4.3.0
* Version bump

= 5.7.3 =
* Emergency fixes.
* Version bump.

= 5.7.2 =
* Fix: PayPal shipping error
* Proper version bump.

= 5.7.1 =
* Fix: WC_Subscriptions error with zero checkout amount when using a 100% discount coupon.
* Fix: Viabill double tag in some cases on product page.
* Fix: Viabill logo show on order view.
* Fix: Mobilepay now moves the phone number to the payment window.
* Feature: Virtual Terminal added. Allows payments from admin now.
* Feature: Mass Capture
* Deprecation of iframe in favor of embedded window.
* Test against latest versions.

= 5.7.0 =
* Feature: Add callback handler for recurring requests
* Fix: Stop using WC_Subscriptions_Manager::process_subscription_payment_failure_on_order as this is deprecated.
* Dev: Make synchronous recurring requests optional with the introduced filter: woocommerce_pensopay_set_synchronized_request
* Dev: Blocked callbacks for recurring requests are now optional. Can be disabled with the filter: woocommerce_pensopay_block_callback

= 5.6.2 =
* Fix: Add missing order payment box in backend for fbg1886, ideal, paypal and swish

= 5.6.1 =
* Fix: MobilePay Checkout not saving address data properly when no customer account was set on the order.

= 5.6.0 =
* Feature: Add UI setting for enabling/disabling transaction caching
* Feature: Add UI setting for setting the transaction caching expiration time
* Feature: Update a cached transaction on accepted callbacks
* Feature: Add private key validation and success indicator next to the settings field - (requires permissions to read the private key via API)
* Feature: Add button to flush the transaction cache from inside the plugin settings
* Fix: Remove "Cancel" transaction on partially captured transactions as this action is not supported
* Fix: MobilePay Checkout is now only creating users if user registration is required. The behavior can be modified via the filter woocommerce_pensopay_mobilepay_checkout_create_user
* Fix: Stop performing capture logic on order completion when the orders is not paid with PensoPay
* Fix: Add permission check on ajax endpoint for clearing logs
* Fix: WC_PensoPay_Order::get_order_id_from_callback fallback now allows both prefixed and suffixed order numbers
* Fix: Recurring payments not being cancellable
* Improvement: Do not reuse cURL instances to avoid problems with some cPanel PHP upgrades where KeepAlive is disabled by default
* Developer: Add the possibility to hide buttons for clearing logs and transaction cache via filters.

= 5.5.3 =
* Fix for embedded payments.

= 5.5.2 =
* Hotfix for capture button not working on some orders.

= 5.5.1 =
* Fix: Proper printing of validation errors returned from the API.
* Improvement: Distinguish between capture exceptions and API exception when adding runtime errors on capture requests.
* Improvement: Add order ID to API error message on capture errors not caused specifically by the PensoPay_Capture_Exception.
* Developer: Add PensoPay_Capture_Exception.

= 5.5.0 =
* Add: Separate PayPal payment instance
* Improvement: PayPal instance will, by default, strip cart items when sending data to PensoPay.

= 5.4.2 =
* Fix: Improvement of WC_PensoPay_Order::get_order_number_for_api to avoid errors if WC_PensoPay_Subscription::get_subscriptions_for_renewal_order returns no subscriptions.
* Add: MasterCard ID Check logo

= 5.4.1 =
* Fix: Unspecific CSS handle causing intermittent conflicts.

= 5.4.0 =
* Fix: MobilePay Checkout - Check for company OR full name before deciding to disable auto-receiving shipping address from MobilePay.
* Fix: Empty log entries is now fixed
* Fix: Add instance check in order completion hook to prevent multiple capture calls on each order which should result in better performance.
* Feature: Persist payment capture errors on order completion to be shown in wp-admin.
* Feature: Show error alert on manual capture failures from the order transaction box.
* Feature: Show error alert on refund failures. This also blocks WooCommerce from refunding the order items if the refund fails.
* Improvement: Pass the order object to woocommerce_pensopay_transaction_params_variables
* Improvement: Send company name (if available) with shipping_address.name if no firstname/lastname has been set on the order.
* Improvement: Remove object type casting on woocommerce_pensopay_automatic_shipping_address and woocommerce_pensopay_automatic_billing_address to allow NULL checks in the MP Checkout address saver helper methods.
* Improvement: Convert all arrays to short syntax
* Tested with WC 3.8.1

= 5.3.1 =
* Fix: Fix missing shipping information on MobilePay Checkout orders if no shipping address is specified in the MobilePay app
* Fix: Bump minimum PHP version to 5.4

= 5.3.0 =
* Fix: Make .is-loading in backend more specific.
* Feature: Trustly as separate payment method instance
* Feature: iDEAL as separate payment method instance
* Feature: Swish as separate payment method instance
* Feature: FBG1886 as separate payment method instance
* Feature: PensoPay - Extra - A flexible payment method instance which takes custom payment methods and icons from the settings panel. This can be used to offer i.e. Dankort payments through NETS if embedded payments are enabled on the main instance.
* Feature: Possibility to disable cancellation of subscription transactions programmatically through 'woocommerce_pensopay_allow_subscription_transaction_cancellation'
* Enhancement: Optimized images for Swish and Resurs.
* Enhancement: Updates helper texts on embedded window and text_on_statement on the settings page
* Enhancement: Only load the backend javascripts on relevant pages

= 5.2.0 =
* Feature: Add support for embedded payments through overlay with Clearhaus
* Developer: Add action 'woocommerce_pensopay_callback_subscription_authorized' and 'woocommerce_pensopay_callback_payment_authorized' for easier way of handling authorized callbacks for specific transaction types.
* Remove eDankort
* Fix: Minor syntax-error in backend javascript

= 5.1.7 =
Fix enabled condition for viabill, fixing warnings and currency issues (DKK, USD, NOK only)
Remove spinning animation
Remove recurs

= 5.1.6 =
* Fixes a bug that made creating a payment link from admin impossible.

= 5.1.5 =
* Fixes an undefined index bug for some viabill related variables.

= 5.1.4 =
* Add casts to ensure iframe payment works

= 5.1.3 =
* Fix: Make ViaBill pricetag toggleable for all locations

= 5.1.2 =
* Fix: Patch payments in 'process_payment' to make sure all transaction variables are up to date to avoid problems when gateway switching after cancelling a payment.
* Fix: Optimize gateway availability check on MobilePay Checkout payments in order to remove the fast checkout button when a subscription is in the cart.
* Fix: Race condition that may cause a client to miss the success page on iFrame payments.

= 5.1.1 =
* Fix: Add fallback in WC_PensoPay_Subscription::process_recurring_response to save transaction ID in case WC_Order::payment_complete fails to do so.
* Fix: Add "needs payment" check on authorized subscription callbacks before creating a recurring payment.
* Tested up to WC 3.6.5

= 5.1.0 =
* Feature: Possibility to fetch the API private key directly from the settings page. Requires an API user with permissions to perform GET requests to /accounts/private-key.
* Feature: Add iframe payment where user doesn't leave the store
* Feature: Add toggleable ViaBill pricetag
* Fix: Minor helper text update for GA tracking ID on the settings page.
* Fix: Add fallback for saving transaction IDs on orders since this seemed to randomly fail when using WC_Order::payment_complete to set it.
* Tested up to WP 5.2.2
* Dev - Add action: woocommerce_pensopay_meta_box_subscription_before_content
* Dev - Add action: woocommerce_pensopay_meta_box_subscription_after_content
* Dev - Add action: woocommerce_pensopay_meta_box_payment_before_content
* Dev - Add action: woocommerce_pensopay_meta_box_payment_after_content
* Dev - Add filter: woocommerce_pensopay_capture_on_order_completion

= 5.0.0 =
* Feature: Add Mobilepay Checkout support
* Feature: Add Vipps
* Feature: Add replaceable template file through woocommerce-pensopay/checkout/mobilepay-checkout.php
* Feature: Add Resurs
* Feature: Add Bitcoin
* Tweak: Add capture callback handler for Sofort to properly handle transactions not sending authorized callbacks.
* Tweak: Add filter: woocommerce_pensopay_callback_url
* Tweak: Add action: woocommerce_pensopay_after_checkout_validation
* Tweak: Add filter: woocommerce_pensopay_get_setting_{setting}
* Tweak: Add action: woocommerce_pensopay_accepted_callback_before_processing
* Tweak: Add action: woocommerce_pensopay_accepted_callback_before_processing_{operation}
* Tweak: Add action: woocommerce_pensopay_save_automatic_addresses_before
* Tweak: Add action: woocommerce_pensopay_save_automatic_addresses_after
* Tweak: Add filter: woocommerce_pensopay_automatic_billing_address
* Tweak: Add filter: woocommerce_pensopay_automatic_shipping_address
* Tweak: Add filter: woocommerce_pensopay_automatic_formatted_address
* Tweak: Add filter: woocommerce_pensopay_mobilepay_checkout_checkout_headline
* Tweak: Add filter: woocommerce_pensopay_mobilepay_checkout_checkout_text
* Tweak: Add filter: woocommerce_pensopay_mobilepay_checkout_button_theme
* Tweak: Add filter: woocommerce_pensopay_mobilepay_checkout_button_size
* Tweak: Updates the MobilePay logo
* Tweak: WC_PensoPay_Helper::get_callback_url now relies on home_url instead of site_url to ensure better compatibility with WPML.
* Fix: WC_PensoPay_Address::get_street_name and WC_PensoPay_Address:get_house_extension throwning a warning if no house number is found on an address.
* Remove: Remove non-CRUD data fetching for WC versions below 3.0.
* Add: Bitcoin icon
* Add: Swish icon
* Add: Trustly icon
* Add: Paysafecard icon

== Upgrade Notice ==
= 5.0.0 =
5.0.0 removes support for WC versions below 3.0. Make sure to perform tests of the plugin on a test/staging environment before upgrading.
