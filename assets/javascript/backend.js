( function( $ ) {
	"use strict";

	PensoPay.prototype.init = function() {
		// Add event handlers
		this.actionBox.on( 'click', '[data-action]', $.proxy( this.callAction, this ) );

		var previousSelectValue = this.orderStatusSelect.val();
		this.orderStatusSelect.
		hover(function() {
			if (this.value) {
				previousSelectValue = this.value;
			}
		}).
		change(function() {
			let newValue = this.value;
			if (newValue === 'wc-refunded') {
				let answer = window.confirm(pensopayBackend.refund_warning);
				if (!answer) {
					this.value = previousSelectValue;
				}
			}
		})
	};

	PensoPay.prototype.callAction = function( e ) {
		e.preventDefault();
		var target = $( e.target );
		var action = target.attr( 'data-action' );

		if( typeof this[action] !== 'undefined' ) {
			var message = target.attr('data-confirm') || 'Are you sure you want to continue?';
			if( confirm( message ) ) {
				this[action]();	
			}
		}	
	};

	PensoPay.prototype.capture = function() {
		var request = this.request( {
			pensopay_action : 'capture'
		} );
	};

	PensoPay.prototype.captureAmount = function () {
		var request = this.request({
			pensopay_action: 'capture',
			pensopay_amount: $('#pp-balance__amount-field').val()
		} );
	};

	PensoPay.prototype.cancel = function() {
		var request = this.request( {
			pensopay_action : 'cancel'
		} );
	};

	PensoPay.prototype.refund = function() {
		var request = this.request( {
			pensopay_action : 'refund'
		} );
	};

	PensoPay.prototype.split_capture = function() {
		var request = this.request( {
			pensopay_action : 'splitcapture',
			amount : parseFloat( $('#pensopay_split_amount').val() ),
			finalize : 0
		} );
	};

	PensoPay.prototype.split_finalize = function() {
		var request = this.request( {
			pensopay_action : 'splitcapture',
			amount : parseFloat( $('#pensopay_split_amount').val() ),
			finalize : 1
		} );
	};

	PensoPay.prototype.request = function( dataObject ) {
		var that = this;
		var request = $.ajax( {
			type : 'POST',
			url : ajaxurl,
			dataType: 'json',
			data : $.extend( {}, { action : 'pensopay_manual_transaction_actions', post : this.postID.val() }, dataObject ),
			beforeSend : $.proxy( this.showLoader, this, true ),
			success : function() {
				$.get( window.location.href, function( data ) {
					var newData = $(data).find( '#' + that.actionBox.attr( 'id' ) + ' .inside' ).html();
					that.actionBox.find( '.inside' ).html( newData );
					that.showLoader( false );
				} );
			},
			error : function(jqXHR, textStatus, errorThrown) {
				alert(jqXHR.responseText);
				that.showLoader( false );
			}
		} );

		return request;
	};

	PensoPay.prototype.showLoader = function( e, show ) {
		if( show ) {
			this.actionBox.append( this.loaderBox );
		} else {
			this.actionBox.find( this.loaderBox ).remove();
		}
	};

    


    PensoPayCheckAPIStatus.prototype.init = function () {
    	if (this.apiSettingsField.length) {
			$(window).on('load', $.proxy(this.pingAPI, this));
			this.apiSettingsField.on('blur', $.proxy(this.pingAPI, this));
			this.insertIndicator();
		}
	};

	PensoPayCheckAPIStatus.prototype.insertIndicator = function () {
		this.indicator.insertAfter(this.apiSettingsField.hide().fadeIn());
	};

	PensoPayCheckAPIStatus.prototype.pingAPI = function () {
		$.post(ajaxurl, { action: 'pensopay_ping_api', api_key: this.apiSettingsField.val() }, $.proxy(function (response) {
			if (response.status === 'success') {
				this.indicator.addClass('ok').removeClass('error');
			} else {
				this.indicator.addClass('error').removeClass('ok');
			}
		}, this), "json");
	};
    
	// DOM ready
	$(function() {
		new PensoPay().init();
		new PensoPayCheckAPIStatus().init();
		new PensoPayPrivateKey().init();

		function wcppInsertAjaxResponseMessage(response) {
			if (response.hasOwnProperty('status') && response.status == 'success') {
				var message = $('<div id="message" class="updated"><p>' + response.message + '</p></div>');
				message.hide();
				message.insertBefore($('#wcpp_wiki'));
				message.fadeIn('fast', function () {
					setTimeout(function () {
						message.fadeOut('fast', function () {
							message.remove();
						});
					},5000);
				});
			}
		}

        var emptyLogsButton = $('#wcpp_logs_clear');
        emptyLogsButton.on('click', function(e) {
        	e.preventDefault();
        	emptyLogsButton.prop('disabled', true);
        	$.getJSON(ajaxurl, { action: 'pensopay_empty_logs' }, function (response) {
				wcppInsertAjaxResponseMessage(response);
				emptyLogsButton.prop('disabled', false);
        	});
        });

        var flushCacheButton = $('#wcpp_flush_cache');
		flushCacheButton.on('click', function(e) {
        	e.preventDefault();
			flushCacheButton.prop('disabled', true);
        	$.getJSON(ajaxurl, { action: 'pensopay_flush_cache' }, function (response) {
				wcppInsertAjaxResponseMessage(response);
				flushCacheButton.prop('disabled', false);
        	});
        });
	});

	function PensoPay() {
		this.actionBox 	= $( '#pensopay-payment-actions' );
		this.postID		= $( '#post_ID' );
		this.loaderBox 	= $( '<div class="loader"></div>');
		this.orderStatusSelect = $( '#order_status' );
	}

    function PensoPayCheckAPIStatus() {
    	this.apiSettingsField = $('#woocommerce_pensopay_pensopay_apikey');
		this.indicator = $('<span class="wcpp_api_indicator"></span>');
	}

	function PensoPayPrivateKey() {
		this.field = $('#woocommerce_pensopay_pensopay_privatekey');
		this.apiKeyField = $('#woocommerce_pensopay_pensopay_apikey');
		this.refresh = $('<span class="wcpp_api_indicator refresh"></span>');
	}

	PensoPayPrivateKey.prototype.init = function () {
		var self = this;
		this.field.parent().append(this.refresh.hide());

		this.refresh.on('click', function() {
			if ( ! self.refresh.hasClass('ok')) {
				self.refresh.addClass('is-loading');
				$.post(ajaxurl + '?action=pensopay_fetch_private_key', { api_key: self.apiKeyField.val() }, function(response) {
					if (response.status === 'success') {
						self.field.val(response.data.private_key);
						self.refresh.removeClass('refresh').addClass('ok');
					} else {
						self.flashError(response.message);
					}

					self.refresh.removeClass('is-loading');
				}, 'json');
			}
		});

		this.validatePrivateKey();
	}

	PensoPayPrivateKey.prototype.validatePrivateKey = function() {
		var self = this;
		$.post(ajaxurl + '?action=pensopay_fetch_private_key', { api_key: self.apiKeyField.val() }, function(response) {
			if (response.status === 'success' && self.field.val() === response.data.private_key) {
				self.refresh.removeClass('refresh').addClass('ok');
			}

			self.refresh.fadeIn();
		}, 'json');
	};

	PensoPayPrivateKey.prototype.flashError = function (message) {
		var message = $('<div style="color: red; font-style: italic;"><p style="font-size: 12px;">' + message + '</p></div>');
		message.hide().insertAfter(this.refresh).fadeIn('fast', function() {
			setTimeout(function () {
				message.fadeOut('fast', function() {
					message.remove();
				})
			}, 10000)
		});
	}

	function updateEmbeddedFieldsVisibilityState(embeddedSettingField, generalEmbeddedSettingRows) {

		if( ! embeddedSettingField.is(':checked')) {
			embeddedSettingField.closest('tr').siblings().fadeOut();
			generalEmbeddedSettingRows.fadeOut();
		} else {
			generalEmbeddedSettingRows.fadeIn();
		}
	}

	$(function() {
		var embeddedSetting = $('#woocommerce_pensopay_pensopay_embedded_payments_enabled');
		var generalEmbeddedSettingRows = $('#woocommerce_pensopay_pensopay_embedded_autojump').closest('tr');

		updateEmbeddedFieldsVisibilityState(embeddedSetting, generalEmbeddedSettingRows)

		embeddedSetting.on('change', function() {
			updateEmbeddedFieldsVisibilityState(embeddedSetting, generalEmbeddedSettingRows)
		})
	});

})(jQuery);