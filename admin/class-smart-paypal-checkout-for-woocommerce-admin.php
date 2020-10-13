<?php

/**
 * @package    Paypal_Checkout_For_Woocommerce
 * @subpackage Paypal_Checkout_For_Woocommerce/admin
 * @author     PayPal <paypal@mbjtechnolabs.com>
 */
class Paypal_Checkout_For_Woocommerce_Admin {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_scripts() {
        if (isset($_GET['section']) && 'paypal_smart_checkout' === $_GET['section']) {
            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/smart-paypal-checkout-for-woocommerce-admin.js', array('jquery'), time(), false);
            wp_localize_script($this->plugin_name, 'paypal_smart_checkout', array(
                'woocommerce_currency' => get_woocommerce_currency(),
                'is_advanced_cards_available' => psb_is_advanced_cards_available() ? 'yes' : 'no'
            )); 
        }
    }

}
