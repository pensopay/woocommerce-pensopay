( function( $ ) {
	"use strict";

	var shippingMethodRequiredFields = [];
	var selectedShippingMethodRequiredFields = [];
	var togglableCheckoutFields = true;

	window.WCPP_MPCheckout = {
		/**
		 * Checks if the selected payment gateway is MP
		 * @returns {boolean}
		 */
		isMobilePaySelected: function () {
			return $('[name="payment_method"]:checked').val() === 'mobilepay_checkout';
		},
		/**
		 * Determine if the fields should be enabled or disabled
		 */
		toggleCheckoutFields: function (event, data) {
			var $body = $(document.body);

			// Update the required fields of all shipping rates
			if (data && data.fragments && data.fragments.hasOwnProperty('mpco_required_fields')) {
				shippingMethodRequiredFields = data.fragments.mpco_required_fields;
			}

			// Update the required fields of all shipping rates
			if (data && data.fragments && data.fragments.hasOwnProperty('mpco_toggle_checkout_fields_appearance')) {
				togglableCheckoutFields = data.fragments.mpco_toggle_checkout_fields_appearance;
			}

			// Next, possibly set the required fields of the current shipping method
			var currentShippingMethod = WCPP_MPCheckout.getCurrentShippingMethod();
			if (currentShippingMethod) {
				if (shippingMethodRequiredFields.hasOwnProperty(currentShippingMethod)){
					selectedShippingMethodRequiredFields = shippingMethodRequiredFields[currentShippingMethod];
				} else {
					selectedShippingMethodRequiredFields = [];
				}
			} else {
				selectedShippingMethodRequiredFields = [];
			}


			if (WCPP_MPCheckout.isMobilePaySelected()) {
				WCPP_MPCheckout.setBillingCountry();
				WCPP_MPCheckout.addHiddenCountryField();
				// First reset all the fields
				WCPP_MPCheckout.performActionOnFields(WCPP_MPCheckout.enableField);
				// Then disable either all or non-required fields
				WCPP_MPCheckout.performActionOnFields(WCPP_MPCheckout.disableField);
				$body.trigger('wcpp_mobilepay_fields_disabled');
				WCPP_MPCheckout.tickTerms();
			} else {
				WCPP_MPCheckout.removeHiddenCountryField();
				WCPP_MPCheckout.performActionOnFields(WCPP_MPCheckout.enableField);
				$body.trigger('wcpp_mobilepay_fields_enabled');
			}
		},
		/**
		 * Enables a field
		 * @param $field
		 */
		enableField: function($field) {
			if ($field.length) {
				$field.prop('disabled', false);
				$field.closest('.form-row').show();
			}
		},
		/**
		 * Disables a field
		 * @param $field
		 */
		disableField: function ($field) {
			if (  ! togglableCheckoutFields ) {
				return;
			}

			if ($field.length && (selectedShippingMethodRequiredFields.length === 0 || selectedShippingMethodRequiredFields.indexOf($field.selector.replace('#', '')) === -1)) {
				$field.prop('disabled', true);

				// For better UX, hide the field if required fields are set and the current field is not required.
				if (selectedShippingMethodRequiredFields.length) {
					$field.closest('.form-row').hide();
				}
			}
		},
		/**
		 * Returns sections
		 * @returns {string[]}
		 */
		getSections: function() {
			return [
				'billing',
				'shipping'
			];
		},
		/**
		 * Returns fields that are part of the billing/shipping sections
		 *
		 * @param section
		 * @returns {*}
		 */
		getSectionFields: function (section) {
			var fields = [
				'first_name',
				'last_name',
				'company',
				'state',
				'address_1',
				'address_2',
				'city',
				'postcode',
				'country',
				'phone',
				'email'
			];

			return $.map(fields, function (field, index) {
				return section + '_' + field;
			})
		},
		/**
		 * Returns fields that are not a part of the billing/shipping sections
		 *
		 * @returns {string[]}
		 */
		getMiscFields: function () {
			return [
				'ship-to-different-address-checkbox'
			];
		},
		/**
		 * Performs an action to the checkout fields
		 *
		 * @param callback
		 */
		performActionOnFields(callback) {
			// Run through section fields

			$.each( WCPP_MPCheckout.getSections(), function( index, section ) {
				$.each( WCPP_MPCheckout.getSectionFields( section ), function( index, fieldSelector ) {
					callback( $( '#' + fieldSelector ) );
				} );
			} );

			// Run through misc fields
			$.each( WCPP_MPCheckout.getMiscFields(), function( index, fieldSelector ) {
				callback($('#' + fieldSelector));
			} )
		},
		tickTerms: function() {
			$('[name="terms"]').prop('checked', true).change();
		},
		/**
		 * Sets the billing country to DK
		 */
		setBillingCountry() {
			var $billingCountry = $('[name="billing_country"]');
			if ($billingCountry.val() !== 'DK') {
				$billingCountry.val('DK').trigger('change');
			}
		},
		/**
		 * Select MobilePay as payment method and go to checkout.
		 */
		forceCheckout(e) {
			e.preventDefault();
			var $form = $('form.checkout');
			$form.find('[name="payment_method"][value="mobilepay_checkout"]').prop('checked', true);
			$form.find('[name="payment_method"]').change();
			WCPP_MPCheckout.tickTerms();
			$form.submit();
		},
		/**
		 * Add hidden placeholder to make sure the country is submitted
		 */
		addHiddenCountryField() {
			var $form = $('form.checkout');
			var hiddenCountryField = $('<input type="hidden" name="billing_country" value="DK" class="mp-billing-country"/>');
			$form.append(hiddenCountryField);
		},
		/**
		 * Remove placeholder country field
		 */
		removeHiddenCountryField() {
			$('form.checkout').find('.mp-billing-country').remove();
		},

		/**
		 * Returns the value of the selected shipping method.
		 *
		 * @returns {jQuery}
		 */
		getCurrentShippingMethod() {
			return jQuery('form.checkout [name^="shipping_method"]:checked').first().val();
		}

	};

	/**
	 * DOM ready
	 */
	$(function() {
		var $body = $(document.body);

		$body.on('change', '[name="payment_method"]', WCPP_MPCheckout.toggleCheckoutFields);
		$body.on('updated_checkout', WCPP_MPCheckout.toggleCheckoutFields);
		$body.on('click', '.mobilepay-checkout--force', WCPP_MPCheckout.forceCheckout);

		$body.trigger('wcpp_mobilepay_init');
	});

})(jQuery);