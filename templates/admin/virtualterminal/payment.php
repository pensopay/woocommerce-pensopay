<?php
    $paymentInstance = WC_PensoPay_VirtualTerminal_Payment::get_instance();
    $stateCode = $paymentInstance->get_post_data('state');
    $blockExtended = $stateCode !== null && $stateCode !== WC_PensoPay_VirtualTerminal_Payment::STATE_INITIAL;
    $blockInitial = ($stateCode !== null && $stateCode === WC_PensoPay_VirtualTerminal_Payment::STATE_INITIAL) || $blockExtended;
?>

<?php if ($paymentInstance->get_post_data('error_message')): ?>
    <div class="error message">
        <span><?= $paymentInstance->get_post_data('error_message') ?></span>
    </div>
    <?php $paymentInstance->remove_post_meta('error_message') ?>
<?php endif; ?>

<?php if ($paymentInstance->get_post_data('pay_link')): ?>
    <div class="success link">
        <a id="payLink" target="_blank" href="<?= $paymentInstance->get_post_data('pay_link') ?>"><?= sprintf(__('Your payment link is: %s', 'woo-pensopay'), $paymentInstance->get_post_data('pay_link')) ?></a>
    </div>
    <?php $paymentInstance->remove_post_meta('pay_link') ?>
<?php endif; ?>

<?= wp_nonce_field('save_post', 'pensopay_nonce') ?>
<p>
    <small>
        <label for="order_id" class="input-required"><?= __('Order ID', 'woo-pensopay') ?></label>
        <input id="order_id" name="order_id" type="text" style="width:100%" value="<?= $paymentInstance->get_post_data('order_id') ?>" <?= $blockInitial ? 'disabled="disabled"' : 'class="required min_4 max_20 alphanumeric"' ?> />
    </small>
</p>

<?php if ($paymentInstance->get_post_data('state')): ?>
<p>
    <small>
        <label><?= __('State', 'woo-pensopay') ?></label>
    </small>
    <span class="payment-status <?= $paymentInstance->get_status_color_code($paymentInstance->get_last_code()) ?>"><?= $paymentInstance->get_display_status() ?></span>
</p>
<?php endif; ?>

<p>
    <small>
        <label for="amount" class="input-required"><?= __('Amount', 'woo-pensopay') ?></label>
        <input id="amount" name="amount" type="text" style="width:100%" value="<?= $paymentInstance->get_post_data('amount') ?: '' ?>" <?= $blockExtended ? 'disabled="disabled"' : 'class="required number"' ?> />
    </small>
</p>

<p>
    <small>
        <label for="locale_code" class="input-required"><?= __('Language', 'woo-pensopay') ?></label>
        <br />
        <select id="locale_code" name="locale_code" <?= $blockExtended ? 'disabled="disabled"' : 'class="required"' ?>>
            <?php
                require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
                $translations = wp_get_available_translations();
                $selectedLanguage = $paymentInstance->get_post_data('locale_code') ?: 'da_DK';
                foreach ($translations as $code => $language): ?>
                    <option value="<?= $language['language'] ?>" <?= $code === $selectedLanguage ? 'selected="selected"' : '' ?>><?= $language['english_name'] ?></option>
                <?php endforeach; ?>
        </select>
    </small>
</p>

<p>
    <small>
        <label for="currency" class="input-required"><?= __('Currency', 'woo-pensopay') ?></label>
        <br />
        <select id="currency" name="currency" <?= $blockInitial ? 'disabled="disabled"' : 'class="required"' ?>>
            <?php
                $selectedCurrency = $paymentInstance->get_post_data('currency') ?: 'DKK';
            ?>
            <?php foreach (get_woocommerce_currencies() as $code => $title): ?>
                <option value="<?= $code ?>" <?= $code === $selectedCurrency ? 'selected="selected"' : '' ?>><?= $title ?></option>
            <?php endforeach; ?>
        </select>
    </small>
</p>

<p>
    <small>
        <label for="autocapture" class="input-required"><?= __('Autocapture', 'woo-pensopay') ?></label>
        <br />
        <select id="autocapture" name="autocapture" <?= $blockExtended ? 'disabled="disabled"' : 'class="required"' ?>>
            <?php $autocapture = $paymentInstance->get_post_data('autocapture') ?>
            <option value="0" <?= $autocapture == 0 ? 'selected="selected"' : '' ?>><?= __('No', 'woo-pensopay') ?></option>
            <option value="1" <?= $autocapture == 1 ? 'selected="selected"' : '' ?>><?= __('Yes', 'woo-pensopay') ?></option>
        </select>
    </small>
</p>

<p>
    <small>
        <label for="autofee" class="input-required"><?= __('Autofee', 'woo-pensopay') ?></label>
        <br />
        <select id="autofee" name="autofee" <?= $blockExtended ? 'disabled="disabled"' : 'class="required"' ?>>
            <?php $autofee = $paymentInstance->get_post_data('autofee') ?>
            <option value="0" <?= $autofee == 0 ? 'selected="selected"' : '' ?>><?= __('No', 'woo-pensopay') ?></option>
            <option value="1" <?= $autofee == 1 ? 'selected="selected"' : '' ?>><?= __('Yes', 'woo-pensopay') ?></option>
        </select>
    </small>
</p>

<!-- Customer Information -->
<p>
    <small>
        <label for="customer_name"><?= __('Name', 'woo-pensopay') ?></label>
        <input id="customer_name" name="customer_name" type="text" style="width:100%" value="<?= $paymentInstance->get_post_data('customer_name') ?>" <?= $blockExtended ? 'disabled="disabled"' : '' ?> />
    </small>
</p>

<p>
    <small>
        <label for="customer_email"><?= __('Email', 'woo-pensopay') ?></label>
        <input id="customer_email" name="customer_email" type="text" style="width:100%" value="<?= $paymentInstance->get_post_data('customer_email') ?>" <?= $blockExtended ? 'disabled="disabled"' : 'class="email"' ?> />
    </small>
</p>

<p>
    <small>
        <label for="customer_street"><?= __('Street', 'woo-pensopay') ?></label>
        <input id="customer_street" name="customer_street" type="text" style="width:100%" value="<?= $paymentInstance->get_post_data('customer_street') ?>" <?= $blockExtended ? 'disabled="disabled"' : '' ?> />
    </small>
</p>

<p>
    <small>
        <label for="customer_zipcode"><?= __('Zip Code', 'woo-pensopay') ?></label>
        <input id="customer_zipcode" name="customer_zipcode" type="text" style="width:100%" value="<?= $paymentInstance->get_post_data('customer_zipcode') ?>" <?= $blockExtended ? 'disabled="disabled"' : '' ?> />
    </small>
</p>

<p>
    <small>
        <label for="customer_city"><?= __('City', 'woo-pensopay') ?></label>
        <input id="customer_city" name="customer_city" type="text" style="width:100%" value="<?= $paymentInstance->get_post_data('customer_city') ?>" <?= $blockExtended ? 'disabled="disabled"' : '' ?> />
    </small>
</p>

<input type="hidden" id="method_type" name="method_type" value="" />
<div class="buttons-box">
<?php if (!$paymentInstance->get_post_data('order_id')): ?>
    <button type="submit" class="button button-primary" onclick="doSubmit(event, 'save_and_pay');"><?= __('Pay Now', 'woo-pensopay') ?></button>
    <button type="submit" class="button button-primary" onclick="doSubmit(event, 'save_and_send');"><?= __('Send Payment Link', 'woo-pensopay') ?></button>
<?php else: ?>
    <button type="submit" class="button button-primary button-update-status" onclick="doSubmit(event, 'update_status');"><?= __('Get Payment Status', 'woo-pensopay') ?></button>
    <?php if ($paymentInstance->can_cancel()): ?>
        <button type="submit" class="button button-primary button-update-status" onclick="doSubmit(event, 'cancel');"><?= __('Cancel', 'woo-pensopay') ?></button>
    <?php endif; ?>
    <?php if ($paymentInstance->can_capture()): ?>
        <button type="submit" class="button button-primary button-update-status" onclick="doSubmit(event, 'capture');"><?= __('Capture', 'woo-pensopay') ?></button>
    <?php endif; ?>
    <?php if ($paymentInstance->can_refund()): ?>
        <button type="submit" class="button button-primary button-update-status" onclick="doSubmit(event, 'refund');"><?= __('Refund', 'woo-pensopay') ?></button>
    <?php endif; ?>
    <?php if ($paymentInstance->get_post_data('state') === WC_PensoPay_VirtualTerminal_Payment::STATE_INITIAL): ?>
        <button type="submit" class="button button-primary" onclick="doSubmit(event, 'save_and_pay');"><?= __('Pay Now', 'woo-pensopay') ?></button>
        <button type="submit" class="button button-primary" onclick="doSubmit(event, 'save_and_send');"><?= __('Send Payment Link', 'woo-pensopay') ?></button>
    <?php endif; ?>
<?php endif; ?>
</div>

<br />
<br />
<br />

<!-- Transaction Log -->
<?php
$operations = $paymentInstance->get_post_data('operations');
if ($operations): ?>
    <table class="operations">
    <tr>
        <th><?= __('Type', 'woo-pensopay') ?></th>
        <th><?= __('Result', 'woo-pensopay') ?></th>
        <th><?= __('Time', 'woo-pensopay') ?></th>
    </tr>
    <?php foreach ($operations as $operation): ?>
        <tr class="<?= $paymentInstance->get_status_color_code($operation['qp_status_code']) ?>">
            <td><?= $operation['type'] ?></td>
            <td><?= $operation['qp_status_code'] ?>: <?= $operation['qp_status_msg'] ?></td>
            <td><?= strftime('%d-%m-%Y %H:%M:%S', strtotime($operation['created_at'])) ?></td>
        </tr>
    <?php endforeach; ?>
    </table>
<?php endif; ?>

<script>
    jQuery.fn.clearValidation = function(){var v = jQuery(this).validate();jQuery('[name]',this).each(function(){v.successList.push(this);v.showErrors();});v.resetForm();v.reset();};
    var validator;

    var doSubmit = function(e, type) {
        e = e || window.event;
        e.preventDefault();
        var target = e.target || e.srcElement;

        if (type === 'save_and_send') {
            jQuery('#customer_email').addClass('required'); //validation
        } else {
            jQuery('#customer_email').removeClass('required'); //validation
        }
        validator.resetForm();

        jQuery('#method_type').val(type);
        jQuery('#post').submit();
    };

    jQuery(function () {
        validator = jQuery('#post').validate();

        jQuery.validator.addMethod("alphanumeric", function(value, element) {
            return this.optional(element) || /^[\w.]+$/i.test(value);
        }, "Letters and numbers only please");

        // Add the rules to the classname hooks
        jQuery.validator.addClassRules({
            min_4: {minlength: 4},
            max_20: {maxlength: 20},
            number: {number: true},
            required: {required: true},
            email: {email: true},
            alphanumeric: {alphanumeric: true}
        });

        var payLink = document.getElementById('payLink');
        if (payLink) {
            payLink.click();
        }
    });
</script>