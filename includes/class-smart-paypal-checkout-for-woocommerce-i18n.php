<?php

/**
 * @since      1.0.0
 * @package    Paypal_Checkout_For_Woocommerce
 * @subpackage Paypal_Checkout_For_Woocommerce/includes
 * @author     PayPal <paypal@mbjtechnolabs.com>
 */
class Paypal_Checkout_For_Woocommerce_i18n {

    /**
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain(
                'smart-paypal-checkout-for-woocommerce', false, dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }

}
