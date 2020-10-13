(function ($) {
    'use strict';
    $(function () {
        jQuery('#woocommerce_paypal_smart_checkout_testmode').change(function () {
            var production = jQuery('#woocommerce_paypal_smart_checkout_api_client_id, #woocommerce_paypal_smart_checkout_api_secret').closest('tr'),
                    sandbox = jQuery('#woocommerce_paypal_smart_checkout_sandbox_client_id, #woocommerce_paypal_smart_checkout_sandbox_api_secret').closest('tr');
            if (jQuery(this).is(':checked')) {
                sandbox.show();
                production.hide();
            } else {
                sandbox.hide();
                production.show();
            }
        }).change();
        jQuery('#woocommerce_paypal_smart_checkout_enable_advanced_card_payments').change(function () {
            if (jQuery(this).is(':checked')) {
                jQuery('#woocommerce_paypal_smart_checkout_threed_secure_enabled').closest('tr').show();
            } else {
                jQuery('#woocommerce_paypal_smart_checkout_threed_secure_enabled').closest('tr').hide();
            }
        }).change();
        if(paypal_smart_checkout.woocommerce_currency === 'INR') {
            $("#woocommerce_paypal_smart_checkout_paymentaction option[value='authorize']").attr('disabled','disabled');
            $("#woocommerce_paypal_smart_checkout_paymentaction option[value='capture']").attr('selected','selected');
        }
        var psb_available = jQuery('#woocommerce_paypal_smart_checkout_enable_advanced_card_payments, #woocommerce_paypal_smart_checkout_threed_secure_enabled').closest('tr');
        if(paypal_smart_checkout.is_advanced_cards_available === 'yes') {
            psb_available.show();
        } else {
            psb_available.hide();
        }
    });
})(jQuery);
