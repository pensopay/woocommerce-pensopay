=== WooCommerce PensoPay ===
Contributors: PensoPay
Tags: gateway, woo commerce, pensopay, gateway, integration, woocommerce, woocommerce pensopay, payment, payment gateway, psp
Requires at least: 4.0.0
Tested up to: 6.4.2
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

== Changelog ==
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

== Changelog ==
= 4.10.1 =
* Fix bug causing white screen of death
* Add option to automatically detect language

= 4.10.0 =
* Add public admin notices infrastructure
* Add possibility to manually create payment links for orders and subscriptions as WooCommerce admin.
* Removes legacy WC_PensoPay_Order. Now only supports WC 3.x
* Removes WC_PensoPay_Order_Base
* Add filter: woocommerce_pensopay_order_action_create_payment_link_for_order
* Add action: woocommerce_pensopay_order_action_payment_link_created
* Introduce customer email sent on manual payment link creation.

= 4.9.4 =
* Tested up to WC 3.4.2
* Add arg (bool) $recurring to filter 'woocommerce_pensopay_order_number_for_api'
* Add methods to get street name used for Klarna
* Add methods to get house number used for Klarna
* Add methods to get house extension used for Klarna

= 4.9.3 =
* Add filter woocommerce_pensopay_transaction_fee_data
* Clean up WC_PensoPay_Base_Order::add_transaction_fee
* Move WC compatibility headers from README to the plugin core file

= 4.9.2 =
* Update version requirements

= 4.9.1 =
* Specify version number on static files in attempt to fix caching issues

= 4.9.0 =
* Fix: Add check if rates are not empty in WC_PensoPay_Base_Order::get_transaction_basket_params_line_helper
* Improvement: Remove shipping from the basket data and add it to the shipping data array instead
* Improvement: Add mobile phone to invoice_address and shipping_address params.
* Fix: Check transaction balance before 'capture on complete' and adjust the amount captured in case a partial capture has been performed already.
* Improvement: Add WC_PensoPay_API::patch
* Improvement: Better error explanation when refunding in-refundable transactions through the WooCommerce interface.
* Add: Verified by Visa logo
* Add: MasterCard SecureCode logo
* Add: Apple Pay logo
* Add: 'WC requires at least' and 'WC tested up to' helpers when upgrading WooCommerce
* Remove: Compatibility for WC 2.x
* Improvement: Update PHP docs
* Remove: Asynchronous loading of transaction data in the order overview to avoid hammering the backend with HTTP requests in case of large order views.
* Add: Transaction data caching. Currently only used in order list view.
* Add: Introducing filter woocommerce_pensopay_transaction_cache_enabled to enable/disable transaction caching. Defaults to true.
* Add: Introducing filter woocommerce_pensopay_transaction_cache_expiration to control how long transactions are cached for. Defaults to one week.
* Improvement: Move transaction data in the order overview from the shipping_address column to a separate payment column. Includes an updated UI.
* Add: Introducing hook woocommerce_pensopay_accepted_callback to target any accepted callback
* Remove: variables.plugin_version on payment creations.
* Add: Shopsystem data to payment creations - name + version
* Add: New filter 'woocommerce_pensopay_transaction_params_shopsystem'

= 4.8.4 =
* Add vat_rate to refund requests

= 4.8.3 =
* Add check for change_payment request in callback handler when authorizing new subscriptions to avoid subscriptions going into 'processing' limbo.
* Update ard logos to svg according to the new payment window from PensoPay
* Add iDEAL logo
* Add UnionPay logo
* Add Cirrus logo
* Add BankAxess logo
* Add filter: woocommerce_pensopay_checkout_gateway_icon_url
* Move client redirect for bulk actions inside permission check to avoid incorrect redirects for regular users.
* Add additional checks for vat rates to avoid division by zero errors.
* Update 'Test up to' to 4.9.0

= 4.8.2 =
* Add filter woocommerce_pensopay_order_number_for_api
* Change order of transaction ID meta key searches

= 4.8.1 =
* Remove SWIPP as possible payment option icon.
* Add setting: Autocompletion of successful renewal/recurring orders.
* Add payment type check in woocommerce_order_status_completed to early break out if a different gateway is used on the order.
* Fix issue where fee was not capturable from the order view with MobilePay payments.

= 4.8.0 =
* Add WooCommerce 3 compatibility release
* Add filter woocommerce_pensopay_transaction_params_variables
* Add filter woocommerce_pensopay_is_request_to_change_payment
* Add subscription status check in the subscription_cancellation hook to avoid transactions being cancelled on subscriptions that are actually active.
* Bulk action to retry failed payments and activate the subscription on successful captures.
* Add transaction metadata accessor method
* Add transaction state accessor method
* Add shipping to transaction basket items.
* Fix typo in Paypal on icon selection
* Remove SWIPP support
* Isolating meta view to separate view file.
* Fix incorrect page check for adding meta boxes.

= 4.7.0 =
* Minor settings helper text updates.
* Add support for qTranslateX in the callback handler. Added logic to prevent browser redirects resulting in callback data loss.
* WP-SpamShield - Bypass security check on PensoPay callbacks.
* Improve product switching (downgrade/upgrade)
* Fix syntax error in classes/updates/woocommerce-pensopay-update-4.6.php resulting in update not completing in case of caught exceptions.
* Remove obsolete Google Analytics Client ID setting.

= 4.6.8 =
* Fix issues with WooCommerce-check failing on network-sites.

= 4.6.7 =
* Add dependency check before loading class files to avoid site crashes in case WooCommerce is disabled.

= 4.6.6 =
* Exclude TRANSACTION_ID from being copied from subscriptions to renewal orders.
* Update translations

= 4.6.5 =
* Make WC_PensoPay_Views::get_view PHP 5.3 compatible.
* Patch cases where transaction ID was not always found on renewal orders.

= 4.6.4 =
* Fix issue with WC_PensoPay_Install not being included properly on plugin activation

= 4.6.3 =
* Remove: WC_PensoPay_Install_Helper
* Improvement: Stop relying on register_activation_hook when upgrading.
* Improvement: Show admin notice when a database upgrade is required. This action must be triggered manually and it will run in the background.
* Add views folder
* Add WC_PensoPay_Views to simplify view handling.

= 4.6.2 =
* Fix issue with older PHP version not bein able to use return value in write context in WC_PensoPay_Settings.

= 4.6.1 =
* Replaced Paii logo with Swipp

= 4.6.0 =
* Feature: Add basket content to transactions.
* Feature: Always add invoice + shipping information on transactions.
* Feature: Add Klarna as separate payment method.
* Feature: Add Swipp as separate payment method.
* Feature: Add Sofort as separate payment method
* Feature: New filters added. (woocommerce_pensopay_transaction_params_shipping, woocommerce_pensopay_transaction_params_invoice, woocommerce_pensopay_transaction_params_basket)
* Feature: Visualize required settings on the settings page.
* Feature: Add admin notice if required fields are not configured.
* Feature: Add button in the plugin settings' "Logs"-section for easy debug log access.
* Feature: Add direct link to the wiki from the settings page.
* Feature: Add live API key validator on the settings page.
* Feature: Simplifying the settings page by removing unused fields.
* Feature: Add hook 'woocommerce_pensopay_loaded'.
* Feature: Add hook 'woocommerce_pensopay_accepted_callback_status_{$state}'.
* Removed: Autocapture settings for subscriptions. Subscriptions now rely on the main autocapture settings (Physical/virtual products).
* Removed: WC_PensoPay_Order::get_callback_url - deprecated since 4.2.0.
* Bug: Remove subscription cancellation from callback handler, on 'cancel'-callbacks to avoid situations where subscriptions ends up in a faulty "Pending Cancellation" state.
* Bug: Fix bug where fees area added on top of each other.
* Bug: Clean up old payment IDs and payment links before creating a new payment link used to update a credit card. Legacy data caused problems in some cases.
* Improvement: Complete refactoring of how subscriptions are handled. The subscription transaction ID is now stored on the 'shop_subscription'-post. Now only payment transactions are stored on regular orders which should improve the renewal/capturing process and make the UI more intuitive. This should also eliminate a lot of quirks when it comes to renewal orders.


= 4.5.6 =
* Fix bug where certain customers are not able to manually pay a failed recurring order.
* Add convenience wrapper WC_PensoPay_Subscription::cart_contains_failed_renewal_order_payment()
* Add convenience wrapper WC_PensoPay_Subscription::get_subscription_for_renewal_order()
* Add convenience wrapper WC_PensoPay_Subscription::get_subscriptions_for_order()
* Add convenience wrapper WC_PensoPay_Subscription::cart_contains_renewal()
* Add ?synchronized query parameter to recurring requests.
* Add WC_PensoPay_Order::get_payment_method_change_count()
* Add WC_PensoPay_Order::increase_payment_method_change_count()
* Hook into woocommerce_subscription_payment_method_updated_to_*
* Use $order->update_status on failed recurring payments instead of WC_Subscriptions_Manager::process_subscription_payment_failure_on_order to get a correct count of failed payments.
* Append the payment count (or timestamp to ensure backwards compatibility) to the order numbers sent to the PensoPay API when manually paying a failed recurring order.

= 4.5.5 =
* Fix: Problem with fees being incorrectly stored when using custom decimal pointers. Rely on wp_format_decimals.

= 4.5.4 =
* Add support for subscription_payment_method_change_customer
* Add transaction state check in WC_PensoPay::subscription_cancel
* Add WC_PensoPay_Order::is_request_to_change_payment()

= 4.5.3 =
* Add possibility to disable transaction information in the order overview
* Fix bug in WC_PensoPay_Helper::price_multiply which didn't properly format prices where are not standard English format.
* Add WC_PensoPay_Helper::price_multiplied_to_float
* Add WC_PensoPay_Helper::price_custom_to_multiplied
* Add unit tests and composer.json to repository

= 4.5.2 =
* Fix problem where settings could not be saved for MobilePay and ViaBill

= 4.5.1 =
* Fix problems with some merchants experiencing failed orders after successful payments.

= 4.5.0 =
* Add WC_PensoPay_Order::has_pensopay_payment().
* Add WC_PensoPay_API_Transaction::get_brand().
* Add WC_PensoPay_API_Transaction::get_currency().
* Add WC_PensoPay_API_Transaction::get_balance().
* Add WC_PensoPay_API_Transaction::get_formatted_balance().
* Add WC_PensoPay_API_Transaction::get_remaining_balance().
* Add WC_PensoPay_API_Transaction::get_formatted_remaining_balance().
* Add WC_PensoPay_API_Transaction::is_operation_approved( $operation ).
* Add WC_PensoPay::plugins_url.
* Add WC_PensoPay_Helper::has_preorder_plugin.
* Feature: Add support for WooCommerce Pre Orders
* Feature: Add Card icons to transaction meta data. Issue #62986808298852.
* Feature: Add possibility to capture a specified amount and not only the full order amount.
* Add Translation template (woo-pensopay.pot).
* Fix: Meta-box being shown when any transactionID if mapped on the order. Issue #145750965321211.
* Fix: Avoid multiple hooks and filters. Thanks to David Tolnem for investigating and providing code example.
* Improvement: Compressed PNG card icons.
* Improvement: Update existing payment links on process payment.
* Improvement: Stop clearing the customer basket on payment processing. This step has been moved to "thank_you"-page.
* Improvement: Update translations.
* Rename WC_PensoPay_API_Transaction::create_link to WC_PensoPay_API_Transaction::patch_link.
* Remove: WC_PensoPay::prepare_extras()

= 4.4.5 =
* Add support for multiple subscriptions.

= 4.4.4 =
* Fix problem with Paii attempted to be loaded after removal.

= 4.4.3 =
* Only make transaction status checks on orders with _transaction_id AND payment methods 'pensopay', 'mobilepay' and 'viabill'
* Remove Paii gateway instance

= 4.4.2 =
* Fix I18n textdomain load bug
* Add wpml-config.xml
* Add title to wpml-config.xml
* Add description to wpml-config.xml
* Add checkout_button_text to wpml-config.xml
* Add 'order_post_id' param to callback URL on recurring payments to ensure compatability with third party software changing the order number.
* Add maxlength on text_on_statement

= 4.4.1 =
* Fix incosistent subscription check which might cause problems for some shops.

= 4.4.0 =
* Update translations
* Change PensoPay_Helper::get_callback_url() to use site_url instead of home_url. This ensures callbacks to always reach the Wordpress core.
* Add WC_PensoPay_Subscription as convenience wrapper
* Support for WooCommerce Subscriptions > 2.x
* Removed support for WooCommerce Subscriptions 1.x.x
* Refactor the method for checking if WC Subscriptions is enabled to support flexible folder names.
* Deprecate the TRANSACTION_ID meta tag.
* Refactor WC_PensoPay_Order::get_transaction_id - rely on the built in transaction ID if available.
* Rely on WC_PensoPay::scheduled_subscription_payment() when creating the initial subscription payment.
* Add curl_request_url to WC_PensoPay_Exception to optimize troubleshooting.
* Add possibility to clear the debug logs.

= 4.3.5 =
* Add: WC_PensoPay_API_Subscriptions::process_recurring_response().
* Fix: First autocapture on subscriptions not working.
* Fix: Problems with recurring payment references not working properly.
* Remove: recurring from callback_handler switch.

= 4.3.4 =
* Minor update to WC_PensoPay_Order::get_clean_order_number() to prevent hash-tags in order numbers, which is occasionally added by some shops.

= 4.3.3 =
* Change method descriptions.
* Disable unnecessary debug information.

= 4.3.2 =
* Fix: Short order numbers resulted in gateway errors.

= 4.3.1 =
* Feature: Add support for both fixed currency and auto-currency. Auto currency should be used when supporting multiple currencies on a web shop.

= 4.3 =
* Tweak: Refactor filter: woocommerce_order_status_completed. Now using the passed post_id.
* Feature: Add setting, checkout_button_text - button text shown when choosing payment.
* Feature: Add property WC_PensoPay::$order_button_text.
* Feature: Add WC_PensoPay_Install to handle DB updates for this and future versions.
* Feature: Add setting, pensopay_autocapture_virtual - Makes it possible for you to set a different autocapture configuration for virtual products. If the order contains both a virtual and a non-virtual product, it will default to the configuration set in "pensopay_autocapture".
* Add filter: woocommerce_pensopay_transaction_link_params.
* Fix: Paii specific settings (category, reference_title, product_id).
* Remove: WC_PensoPay_Helper::prefix_order_number().
* Feature: Support "WooCommerce Sequential Order Numbers" order number prefix/suffix.
* Remove: WC_PensoPay::find_order_by_order_number() - rely on the post ID now stored on the transaction.
* Fix: Remove currency from recurring requests
* Feature: Add support for text_on_statement for Clearhaus customers.
* Feature: Add customer_email to payment/subscription links. (Used for PayPal transactions).
* Feature: Add support for subscription_payment_method_change
* Feature: Add transaction ID, transaction order ID, payment ID and payment links to the meta content box for easy access and better debugging.
* Update translations.

= 4.2.2 =
* Fix: Payment icons not working in WooCommerce 2.4.
* Fix: JSON encode and typecast error objects in case no specific error message is set from PensoPay
* Fix: Add additional params to http_build_query to support server setups requirering param 2+3 to work properly
* Fix: Remove obosolete pensopay_paybuttontext setting from instances
* Tweak: Move woocommerce_order_complete hook outside is_admin check
* Tweak: Add post data params to API exceptions
* Tweak: Wrap process payment in try/catch and write any errors to WC system logs.

= 4.2.1 =
* Reintroduce merchant ID for support usability
* Update keys
* Update translations

= 4.2.0 =
* Deprecating WC_PensoPay::get_callback_url(). Use WC_PensoPay_Helper::get_callback_url() instead.
* Add PensoPay-Callback-Url to API request headers.
* Correct name casing in title and descriptions.
* Add method_title to instances
* Prefix subinstances with "PensoPay - %s" for usability reasons.
* Disable subscription support on MobilePay, Paii and ViaBill
* Add support for payment links. Removing old FORM method.
* Add tooltip descriptions to settings page
* Improved API error logging
* Add jQuery multiselect to 'Credit card icons'
* Change subscription description from "qp_subscription" to "woocommerce-subscription"
* Removed all settings and files related to the auto-redirect.
* Remove setting: pensopay_merchantid
* Remove setting: pensopay_redirect
* Remove setting: pensopay_redirectText
* Remove setting: pensopay_paybuttontext
* Add setting: pensopay_custom_variables
* Remove old tags before 3.0.6

= 4.1.0 =
* Add Google Analytics support
* Performance optimization: The order view is now making async requests to retrieve the transaction state.
* Add complete order reference in order overview
* Add version number to the plugin settings page
* Add support for multiple instances. Now it is possible to add MobilePay, Paii and viaBill as separate payment methods. Each instance is based on the core module settings to ensure a minimum amount of configuration.
* Add setting: pensopay_redirect - allows the shop owner to enable/disable the auto redirection in the checkout process.
* Remove setting: pensopay_mobilepay
* Remove setting: pensopay_viabill
* Remove setting: pensopay_labelCreditCard
* Remove setting: pensopay_labelViaBill
* Remove setting: pensopay_debug
* Fix problem with attempt of payment capture when setting order status to complete on a subscription order.
* Updated translations

= 4.0.7 =
* Add upgrade notiece for 4.0.0

= 4.0.6 =
* Activate autofee settings
* Implement upgrade notices inside the plugins section
* Update incorrect autofee key in recurring requests
* Update success response HTTP codes
* Typecasting response to string if no message object is available

= 4.0.5 =
* Add the possibility to set a custom branding ID

= 4.0.4 =
* Stop forcing HTTP on callbacks.

= 4.0.3 =
* Add WC_PensoPay_API_Subscription::is_action_allowed
* Manual AJAX actions handled for subscriptions

= 4.0.2 =
* Add mobilepay option
* Disabled viabill since the PensoPay API is not ready to support it yet.

= 4.0.1 =
* Add version parameter to the payment request

= 4.0.0 =
* Now only supports the new PensoPay gateway platform
* Introduce exception class PensoPay_Exception
* Introduce exception class PensoPay_API_Exception
* Introduce WC_PensoPay::process_refund to support "auto" gateway refunds
* Introduce WC_PensoPay_API
* Introduce WC_PensoPay_API_Payment
* Introduce WC_PensoPay_API_Subscription
* Introduce WC_PensoPay_Log - Debugging information is now added to WooCommerce system logs.
* Remove WC_PensoPay_Request
* Remove donation link

= 3.0.9 =
* Add support for important update notifications fetched from the README.txt file.

= 3.0.8 =
* Switched to WC_Order::get_total() instead of WC_Order::order_total to fix issues with WPML currencies.

= 3.0.6 =
* Added proper support for both Sequential Order Numbers FREE and Sequential Order Numbers PRO.

= 3.0.5 =
* Bugfix: 502 on checkout on shops hosted with wpengine.com.

= 3.0.4 =
* Add filter 'woocommerce_pensopay_currency' which can be used to dynamically edit the gateway currency
* Add filter 'woocommerce_pensopay_language' which can be used to dynamically edit the gateway language

= 3.0.3 =
* Added support for credit card icons in the settings.
* Re-implented auto redirect on checkout page

= 3.0.2 =
* Fixed MD5 hash problem when not in test mode

= 3.0.1 =
* Added refund support
* Update Danish i18n

= 3.0.0 =
* Completely refactored the plugin. The logic has been splitted into multiple classes, and a lot of bugs should've been eliminated with this version.
* Added ajax calls when using the API

= 2.1.6 =
* Optimized fee handling

= 2.1.5 =
* Added support for Paii

= 2.1.4 =
* Added action links to "Installed plugins" overview
* Fixed md5 checksum error caused by testmode
* Fixed problem with coupons not working properly on subscriptions
* Fixed problem with lagging the use of payment_complete() on successful payments

= 2.1.3 =
* Added i18n support, current supported languages: en_UK, da_DK
* Added possibility to add email instructions on the order confirmation. Thanks to Emil Eriksen for idea and contribution.
* Added possibility to change test mode directly in WooCommerce. Thanks to Emil Eriksen for idea and contribution.
* Added eye candy in form of SVN header banner
* Added donation link to all of you lovely fellows who might wanna donate a coin for our work.

= 2.1.2 =
* Fixed an undefined variable notices
* Switched from WC_Subscriptions_Order::get_price_per_period to WC_Subscriptions_Order::get_recurring_total
* Added payment transaction fee to orders
* Changed name to WooCommerce PensoPay

= 2.1.1 =
* Fixes FATAL ERROR bug on checkout introduced in 2.1.0
* Plugin URI in gateway-pensopay.php

= 2.1.0 =
* Bugfix: Static call to a non-static method caused strict errors.
* Added support for WooCommerce 2.1.

= 2.0.9 =
* Bug where custom meta boxes were not instantiated should be fixed in this version
* More currencies added (SEK, NOK, GBP)

= 2.0.8 =
* Fixed viabill cardtypelock

= 2.0.7 =
* Fixed bug where server complains about PensoPay SSL certificate.
* Changed iBill labels to viaBill
* Added the possibility to set a custom text on the checkout page right before the customer is redirected to the PensoPay payment window.
* Added the possibility to set a custom label to credit card and viaBill.

= 2.0.6 =
* Fixed bug where recurring payments were not being captured properly.
* Fixed undefined variable notice "params_string".

= 2.0.4 =
* Implemented a tweak to the "WooCommerce Sequential Order Numbers"-support which should fix any problems with WooCommerce PensoPay + Sequential order numbers.

= 2.0.3 =
* Fixing issues with cardtypelocks

= 2.0.2 =
* Enabling auto redirect on receipt page which accidently got disabled in 2.0.1

= 2.0.1 =
* Updated a hook causing problems with saving gateway settings.

= 2.0.0 =
* Build to work with WooCommerce 2.0.x or higher
* Refactoring the majority of existing methods to save a lot of code and implementing better API error handling.

= 1.4.0 =
* Implement WC_PensoPay::create_md5() which manually sets the order of the md5 checkpoints.
* Should fix payment integration and missing mails sent out to customers after implementation of protocol v7.

= 1.3.11 =
* Plugin now uses PensoPay version 7

= 1.3.10 =
* Feature: Allow customers to select between credit card and iBill when choosing PensoPay as pay method. Credit card is ticket as default option. 		NB: You are required to have an agreement with iBill in order to use this feature properly.

= 1.3.9 =
* 'Capture on complete' now also works on bulk actions.

= 1.3.8 =
* Short install guide added to README.txt

= 1.3.7 =
* 'Capture on complete' is implemented as an option in the gateway settings. It can be turned on/off. Default: Off
* This is a faster way to process your orders. When the order state is set to "completed", the payment will automatically be capture. This works in both the order overview and in the single order view.

= 1.3.6 =
* Bugfix: Implemented missing check for WC Subscriptions resulting in fatal error on api_action_router().


= 1.3.5 =
* Bugfix: Problem with transaction ID not being connected to an order [FIXED].

= 1.3.4 =
* Added better support for "WooCommerce Sequential Order Numbers".
* Automatically redirects after 5 seconds on "Checkout -> Pay"-page.

= 1.3.3 =
* Bugfix: Corrected bug not showing price corectly on subscriptions in payment window.

= 1.3.1 =
* Bugfix: Systems not having WooCommerce Subscriptions enabled got a fatal error on payment site.

= 1.3.0 =
* Added support for WooCommerce subscription.
* Now reduces stock when a payment is completed.

= 1.2.2 =
* Bugfix: Capturing payments from WooCommerce backend caused problems due to missing order_total param in cURL request.

= 1.2.1 =
* More minor changes to the payment cancellations from PensoPay form.

= 1.2.0 =
* Major rewriting of payments cancelled by customer.

= 1.1.3 =
* Implemented payment auto capturing.

= 1.1.2 =
* Link back to payment page after payment cancellation added.

= 1.1.1 =
* If a payment is cancelled by user, a $woocommerce->add_error will now be shown, notifying the customer about this. We also post a note to the order about cancellation.

= 1.1.0 =
* Changed plugin structure.
* core.js added to the plugin to avoid inline javascript.
* Implemented payment state and transaction id in order overview.
* Implemented payment handling in single order view.
* Added support for split payments
* If turned on in PensoPay Manager, shop owners may now split up the transactions.
* Rewritten and added a lot of the class methods.

= 1.0.1 =
*  Bugfix: Corrected a few unchecked variables that caused php notices in error logs.

== Upgrade Notice ==
= 5.0.0 =
5.0.0 removes support for WC versions below 3.0. Make sure to perform tests of the plugin on a test/staging environment before upgrading.
