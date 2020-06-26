<div id="wcpp-loader" class="is-loading"></div>

<div id="wcpp-payment-modal" class="wcpp-modal" style="display: none;"
     data-link="<?php esc_attr_e($payment_link) ?>"
     data-autoopen="<?php esc_attr_e($auto_open) ?>"
     data-total="<?php esc_attr_e($payment_total) ?>"
     data-total-formatted="<?php esc_attr_e($payment_total_formatted) ?>"
     data-currency="<?php esc_attr_e($payment_currency) ?>"
     data-redirect="<?php esc_attr_e($redirect) ?>">
	<div class="wcpp-modal__dialog-wrapper">
		<div class="wcpp-modal__dialog">
			<div class="wcpp-modal-header">
				<div class="wcpp-modal-header__amount is-<?php esc_attr_e(get_option( 'woocommerce_currency_pos' )) ?>"></div>
				<div class="wcpp-modal-header__close" data-close-redirect="<?php esc_attr_e($close_redirect) ?>">
					<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64" version="1.1" height="64" viewBox="0 0 64 64" enable-background="new 0 0 64 64">
						<g>
							<path fill="#1D1D1B" d="M28.941,31.786L0.613,60.114c-0.787,0.787-0.787,2.062,0,2.849c0.393,0.394,0.909,0.59,1.424,0.59   c0.516,0,1.031-0.196,1.424-0.59l28.541-28.541l28.541,28.541c0.394,0.394,0.909,0.59,1.424,0.59c0.515,0,1.031-0.196,1.424-0.59   c0.787-0.787,0.787-2.062,0-2.849L35.064,31.786L63.41,3.438c0.787-0.787,0.787-2.062,0-2.849c-0.787-0.786-2.062-0.786-2.848,0   L32.003,29.15L3.441,0.59c-0.787-0.786-2.061-0.786-2.848,0c-0.787,0.787-0.787,2.062,0,2.849L28.941,31.786z"/>
						</g>
					</svg>
				</div>
			</div>

			<form id="wcpp-payment-form">
				<div id="wcpp-payment-form__inner">
					<div class="wcpp-card-row wcpp-flex">
						<div class="form-row wcpp-card-column is-card">
							<label class="woocommerce-form__label"><?php echo apply_filters('woocommerce_pensopay_payment_overlay_label__cardnumber', __( 'Card number', 'woo-pensopay' ) ); ?></label>
                            <input type="tel" tabindex="1" data-pensopay="cardnumber" class="input-text wcpp-input" autocomplete="off" autofocus="autofocus" placeholder="0000 0000 0000 0000" pattern="[0-9\s]*" data-mask="#" inputmode="numeric">
						</div>
						<div class="form-row wcpp-card-column is-brand">
							<div class="wcpp-card-brand">
                                <label class="wcpp-flex">&nbsp;</label>
								<svg class="wcpp-card-brand__visa" enable-background="new 0 0 24 24" id="Layer_1" version="1.1" viewBox="0 0 24 24" xml:space="preserve" xmlns="http://www.w3.org/2000/svg">
									<g>
										<path d="M22,3H2C0.8969727,3,0,3.8969727,0,5v14c0,1.1030273,0.8969727,2,2,2h20c1.1030273,0,2-0.8969727,2-2V5   C24,3.8969727,23.1030273,3,22,3z M5.0020142,10.1005249C5.0130615,10.0424194,5.0678101,10,5.1317139,10H5.131958   l1.2373047,0.0018311c0.0016479,0,0.003418,0.000061,0.005127,0.000061   c0.1810913,0.0067749,0.4310303,0.0474243,0.5099487,0.3209839c0.0006104,0.0020142,0.0010986,0.0040283,0.0015869,0.0061646   l0.2195435,0.9576416c0.0126953,0.055603-0.0180664,0.1120605-0.0740967,0.1356812   c-0.0175781,0.0074463-0.0362549,0.0109253-0.0547485,0.0109253c-0.0401611,0-0.0793457-0.0169678-0.1048584-0.0479736   c-0.413147-0.5015259-1.0302124-0.8990479-1.784668-1.1495361C5.0267944,10.2157593,4.9909668,10.1586304,5.0020142,10.1005249z    M7.9163208,13.9940186L6.854248,13.9954224H6.854126c-0.0585327,0-0.1099854-0.0355835-0.1264038-0.0874023L5.805481,11.0007935   c-0.0159912-0.0504761,0.0050049-0.1047363,0.052002-0.1343994c0.0469971-0.0297241,0.109375-0.0281982,0.1546631,0.0039673   c0.6640015,0.4708252,1.1398926,1.064209,1.3169556,1.4434204c0.0025024,0.0050659,0.0045166,0.010376,0.0060425,0.0157471   l0.1206665,0.4030151l1.1219482-2.6444092c0.0197754-0.0465698,0.0684204-0.0772705,0.1225586-0.0772705l0.9589844-0.0004272   h0.0001221c0.0443115,0,0.0856323,0.0205688,0.1099854,0.0546875s0.0285034,0.0773315,0.0109863,0.1148682l-1.7432861,3.7405396   C8.0162964,13.9650879,7.9688721,13.9940186,7.9163208,13.9940186z M10.7965088,13.8972168   c-0.0107422,0.0584106-0.0656128,0.1011353-0.1298218,0.1011353H9.7550659c-0.0388184,0-0.0756226-0.0157471-0.100647-0.0431519   c-0.0249634-0.0272827-0.0356445-0.0635376-0.0291138-0.0988159l0.6920776-3.7481079   c0.0107422-0.0583496,0.0656128-0.1010742,0.1297607-0.1010742h0.9122925c0.0388184,0,0.0756226,0.0158081,0.100647,0.0431519   c0.0250244,0.0272827,0.0356445,0.0634766,0.0291748,0.0987549L10.7965088,13.8972168z M13.8735352,11.6255493   c0.6526489,0.2723999,0.9561768,0.6044922,0.9516602,1.0435791c-0.0085449,0.7985229-0.7852173,1.3146362-1.9787598,1.3148193   h-0.0014648c-0.4162598-0.0041504-0.8504028-0.0667114-1.1613159-0.1672974   c-0.0606689-0.0195923-0.0971069-0.0767822-0.0862427-0.1352539l0.1129761-0.6088867   c0.0067749-0.036499,0.0311279-0.0681152,0.0662842-0.0858765c0.0350952-0.0178833,0.0770874-0.0200806,0.1141357-0.0058594   c0.3528442,0.135437,0.6022949,0.1998291,1.0651245,0.1998291c0.3251953,0,0.6765747-0.1174927,0.6796875-0.3756104   c0.0020142-0.1685791-0.1465454-0.2890015-0.5887451-0.4771729c-0.4311523-0.1841431-1.0031128-0.4927368-0.9960327-1.0460205   c0.0062866-0.7471924,0.7963867-1.2693481,1.9213867-1.2693481c0.3968506,0,0.7137451,0.0689087,0.909668,0.126709   c0.062439,0.0184937,0.100708,0.0764771,0.0897217,0.1361694l-0.1065674,0.578186   c-0.0066528,0.0360107-0.0303955,0.0671997-0.0646973,0.0852661s-0.0755005,0.0209351-0.1124268,0.0078735   c-0.1585693-0.0562134-0.4107666-0.1309204-0.7678223-0.1309204c-0.012085,0-0.0244141,0.0001221-0.034729,0.0003052   c-0.4299927,0-0.6538086,0.1657104-0.6538086,0.3294678C13.229248,11.3275757,13.4740601,11.4464722,13.8735352,11.6255493z    M18.9709473,13.9544678c-0.0249634,0.0286865-0.0627441,0.0454102-0.1026001,0.0454102h-0.8240356   c-0.0618896,0-0.1154785-0.0397339-0.1286621-0.0955811c-0.0274658-0.1156006-0.0939941-0.3961792-0.1207886-0.5020752   c-0.0909424,0-0.4708252-0.0004883-0.8284302-0.0008545h-0.0175781c-0.3111572-0.0004272-0.6013794-0.0007935-0.6640625-0.0007935   c-0.0245972,0.0581665-0.0991211,0.2383423-0.215271,0.5209961C16.0500488,13.96875,16.0010376,14,15.9462891,14h-0.9448853   c-0.0446167,0-0.0860596-0.020752-0.1103516-0.0551758c-0.024292-0.0344849-0.0281372-0.0778198-0.0101318-0.1155396   l1.6686401-3.4869995c0.1109009-0.2320557,0.2956543-0.3310547,0.6176147-0.3310547h0.8049927   c0.0618896,0,0.1152344,0.0396118,0.1286011,0.0952759l0.8961792,3.7457275   C19.0054932,13.8881836,18.9959106,13.9257813,18.9709473,13.9544678z" fill="#303C42" />
										<polygon fill="#303C42" points="17.2510376,11.0871582 16.6240845,12.5838013 17.6036377,12.5838013 17.3307495,11.4319458  " />
									</g>
								</svg>
								<svg class="wcpp-card-brand__mastercard" enable-background="new 0 0 24 24" id="Layer_1" version="1.1" viewBox="0 0 24 24" xml:space="preserve" xmlns="http://www.w3.org/2000/svg">
									<g>
										<path d="M12.038147,16.7518311c-0.2258301,0-0.3540039,0.1434326-0.3875732,0.3540039h0.7568359   C12.3738403,16.8800049,12.2426147,16.7518311,12.038147,16.7518311z" fill="#303C42" />
										<path d="M7.3629761,16.7670898c-0.2746582,0-0.4425049,0.2105713-0.4425049,0.4668579   c0,0.2563477,0.1678467,0.4669189,0.4425049,0.4669189c0.2623901,0,0.4393921-0.201355,0.4393921-0.4669189   C7.8023682,16.9685059,7.6253662,16.7670898,7.3629761,16.7670898z" fill="#303C42" />
										<path d="M16.3166504,16.7670898c-0.2747192,0-0.4425659,0.2105713-0.4425659,0.4668579   c0,0.2563477,0.1678467,0.4669189,0.4425659,0.4669189c0.2623901,0,0.4393921-0.201355,0.4393921-0.4669189   C16.7560425,16.9685059,16.5790405,16.7670898,16.3166504,16.7670898z" fill="#303C42" />
										<path d="M22,3H2C0.8969727,3,0,3.8969727,0,5v14c0,1.1030273,0.8969727,2,2,2h20c1.1030273,0,2-0.8969727,2-2V5   C24,3.8969727,23.1030273,3,22,3z M6.3131714,17.9633789H5.9927368v-0.8117676   c0-0.2471924-0.1037598-0.3845215-0.3203735-0.3845215c-0.2105713,0-0.3570557,0.1342773-0.3570557,0.3875732v0.8087158H4.994873   v-0.8117676c0-0.2471924-0.1068115-0.3845215-0.3173828-0.3845215c-0.2166748,0-0.3570557,0.1342773-0.3570557,0.3875732v0.8087158   H4v-1.4587402h0.3173828v0.1800537c0.1190186-0.1708984,0.2716064-0.2166748,0.4272461-0.2166748   c0.2227783,0,0.3814697,0.0976563,0.4821777,0.2593994c0.1342773-0.2044678,0.3265381-0.2624512,0.5126343-0.2593994   c0.3540039,0.0030518,0.5737305,0.2349854,0.5737305,0.579834V17.9633789z M8.1015015,17.2339478v0.7294312H7.7841187v-0.177063   C7.6834106,17.9175415,7.5308228,18,7.3233032,18c-0.4089355,0-0.7293701-0.3204956-0.7293701-0.7660522   c0-0.4454956,0.3204346-0.7659302,0.7293701-0.7659302c0.2075195,0,0.3601074,0.0823975,0.4608154,0.213623v-0.177002h0.3173828   V17.2339478z M9.0413818,18c-0.2380371,0-0.4577637-0.0610962-0.6317139-0.18927l0.1495361-0.2471924   c0.1068115,0.0824585,0.2655029,0.1526489,0.4852295,0.1526489c0.2166748,0,0.3326416-0.0641479,0.3326416-0.177063   c0-0.0823975-0.0823975-0.1281738-0.2563477-0.1525269l-0.1495361-0.0213623   c-0.3265381-0.0457764-0.503479-0.1922607-0.503479-0.4302979c0-0.289917,0.2379761-0.4669189,0.6072388-0.4669189   c0.2319336,0,0.4425049,0.0518799,0.5950928,0.1525879l-0.1373291,0.2563477   c-0.088501-0.0549316-0.2685547-0.1251221-0.4547119-0.1251221c-0.1739502,0-0.27771,0.0640869-0.27771,0.1708984   c0,0.0976563,0.1098633,0.1251221,0.2471924,0.1434326l0.1495361,0.0213623   c0.3173828,0.0457764,0.5096436,0.1800537,0.5096436,0.4364014C9.706665,17.8015747,9.4625244,18,9.0413818,18z M10.7229004,18   c-0.3753662,0-0.5065918-0.2014771-0.5065918-0.5401611v-0.6652832H9.9202881v-0.289917h0.2960205v-0.4425049h0.3204346v0.4425049   h0.5187378v0.289917h-0.5187378v0.6591797c0,0.1464233,0.0518799,0.2441406,0.2105713,0.2441406   c0.0823364,0,0.1860962-0.0274658,0.2807617-0.0823975l0.0914917,0.2716064C11.0188599,17.9572144,10.8602295,18,10.7229004,18z    M12.730896,17.3560181h-1.083374c0.0457764,0.2625122,0.2319336,0.3570557,0.4364014,0.3570557   c0.1464844,0,0.302124-0.0548706,0.4241943-0.1525269l0.1556396,0.2349243C12.4867554,17.9450073,12.2853394,18,12.0656128,18   c-0.4364014,0-0.7476807-0.302124-0.7476807-0.7660522c0-0.4546509,0.2990723-0.7659302,0.7263184-0.7659302   c0.4089355,0,0.6896973,0.3112793,0.692749,0.7659302C12.7369995,17.2767334,12.7339478,17.3164063,12.730896,17.3560181z    M13.8905029,16.8067627c-0.0670776-0.0274658-0.1342773-0.0366211-0.1983032-0.0366211   c-0.2075806,0-0.3113403,0.1342773-0.3113403,0.3753662v0.8178711h-0.3173218v-1.4587402h0.3143311v0.177002   c0.0823364-0.1281738,0.201416-0.213623,0.3844604-0.213623c0.0640869,0,0.1556396,0.012207,0.2258301,0.0396729   L13.8905029,16.8067627z M14.8518066,17.7008667c0.1373291,0,0.2624512-0.0457153,0.3814697-0.1312256l0.1525879,0.2563477   C15.2271729,17.9511719,15.0806885,18,14.8609619,18c-0.4699707,0-0.7781982-0.3234863-0.7781982-0.7660522   c0-0.4424438,0.3082275-0.7659302,0.7781982-0.7659302c0.2197266,0,0.3662109,0.0488281,0.5249023,0.1739502l-0.1525879,0.2563477   c-0.1190186-0.0854492-0.2441406-0.1312256-0.3814697-0.1312256c-0.2532959,0.0030518-0.4394531,0.1861572-0.4394531,0.4668579   C14.4123535,17.5147095,14.5985107,17.697876,14.8518066,17.7008667z M17.0551147,17.2339478v0.7294312h-0.3173828v-0.177063   C16.6370239,17.9175415,16.484436,18,16.2769165,18c-0.4088745,0-0.7293091-0.3204956-0.7293091-0.7660522   c0-0.4454956,0.3204346-0.7659302,0.7293091-0.7659302c0.2075195,0,0.3601074,0.0823975,0.4608154,0.213623v-0.177002h0.3173828   V17.2339478z M18.3002319,16.8067627c-0.0671387-0.0274658-0.1342773-0.0366211-0.1984253-0.0366211   c-0.2074585,0-0.3112183,0.1342773-0.3112183,0.3753662v0.8178711h-0.3173828v-1.4587402h0.3143311v0.177002   c0.0823975-0.1281738,0.201416-0.213623,0.3845215-0.213623c0.0640259,0,0.1556396,0.012207,0.2258301,0.0396729   L18.3002319,16.8067627z M20,17.2339478v0.7294312h-0.3173828v-0.177063C19.5819092,17.9175415,19.4293213,18,19.2218018,18   c-0.4089355,0-0.7293701-0.3204956-0.7293701-0.7660522c0-0.4454956,0.3204346-0.7659302,0.7293701-0.7659302   c0.2075195,0,0.3601074,0.0823975,0.4608154,0.213623v-0.7628784H20V17.2339478z M15.25,15.5   c-1.2597046,0-2.3989868-0.5012817-3.25-1.3043213C11.1489868,14.9987183,10.0097046,15.5,8.75,15.5   C6.1308594,15.5,4,13.3691406,4,10.75S6.1308594,6,8.75,6c1.2597046,0,2.3989868,0.5012817,3.25,1.3043213   C12.8510132,6.5012817,13.9902954,6,15.25,6C17.8691406,6,20,8.1308594,20,10.75S17.8691406,15.5,15.25,15.5z" fill="#303C42" />
										<path d="M19.2614746,16.7670898c-0.2746582,0-0.4424438,0.2105713-0.4424438,0.4668579   c0,0.2563477,0.1677856,0.4669189,0.4424438,0.4669189c0.2624512,0,0.4394531-0.201355,0.4394531-0.4669189   C19.7009277,16.9685059,19.5239258,16.7670898,19.2614746,16.7670898z" fill="#303C42" />
										<path d="M12,7.3043213C11.081543,8.1710205,10.5,9.390564,10.5,10.75s0.581543,2.5789795,1.5,3.4456787   c0.918457-0.8666992,1.5-2.0862427,1.5-3.4456787S12.918457,8.1710205,12,7.3043213z" fill="#303C42" />
									</g>
								</svg>
							</div>
						</div>

					</div>

					<div class="wcpp-card-row">
						<div class="form-row wcpp-card-column is-exp">
							<label class="woocommerce-form__label"><?php echo apply_filters('woocommerce_pensopay_payment_overlay_label__expdate', __( 'Expiration Month/Year', 'woo-pensopay' ) ); ?></label>
							<input class="input-text wcpp-input" type="tel" tabindex="2" maxlength="7" placeholder="<?php _e('MM / YY', 'woo-pensopay') ?>" autocomplete="off" data-pensopay="expiration">
						</div>
						<div class="form-row wcpp-card-column is-cvd">
							<label class="woocommerce-form__label"><?php echo apply_filters('woocommerce_pensopay_payment_overlay_label__cvd', __( 'CVV/CVD', 'woo-pensopay' ) ); ?></label>
							<input class="input-text wcpp-input" type="tel" tabindex="3" maxlength="4" autocomplete="off" data-pensopay="cvd" placeholder="CVD">
						</div>
					</div>
				</div>
				<button class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button wcpp-btn--checkout alt" type="submit" tabindex="4">
					<span><?php echo apply_filters('woocommerce_pensopay_payment_overlay__pay', __( 'Pay', 'woo-pensopay' ) ); ?></span>
				</button>
			</form>

            <div class="woocommerce-message wcpp-modal-completed" style="display: none;">
                <svg class="wcpp-modal-completed__icon wcpp-animated" xmlns="http://www.w3.org/2000/svg" width="130" height="130" viewBox="0 0 70 70">
                    <path class="wcpp-modal-completed__icon__result" fill="#D8D8D8" d="M35,60 C21.1928813,60 10,48.8071187 10,35 C10,21.1928813 21.1928813,10 35,10 C48.8071187,10 60,21.1928813 60,35 C60,48.8071187 48.8071187,60 35,60 Z M23.6332378,33.2260427 L22.3667622,34.7739573 L34.1433655,44.40936 L47.776114,27.6305926 L46.223886,26.3694074 L33.8566345,41.59064 L23.6332378,33.2260427 Z"/>
                    <circle class="wcpp-modal-completed__icon__circle" cx="35" cy="35" r="24" stroke="#979797" stroke-width="2" stroke-linecap="round" fill="transparent"/>
                    <polyline class="wcpp-modal-completed__check" stroke="transparent" stroke-width="2" points="23 34 34 43 47 27" fill="transparent"/>
                </svg>
            </div>
		</div>
		<div class="wcpp-modal-disclaimer">
			<span><?=  apply_filters('woocommerce_pensopay_payment_overlay__pay', __( 'Secure payment by', 'woo-pensopay' ) ) ?> </span> <img src="<?php esc_attr_e( WC_PensoPay_Views::asset_url( 'images/pensopay.svg' ) ); ?>" />
		</div>
	</div>
</div>
