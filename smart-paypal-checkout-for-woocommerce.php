<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Smart PayPal Checkout For WooCommerce
 * Plugin URI:        https://github.com/wp-mbjtechnolabs/smart-paypal-checkout-for-woocommerce
 * Description:       PayPal Checkout with Smart Payment Buttons gives your buyers a simplified and secure checkout experience. Develop by Official PayPal Partner.
 * Version:           2.0.2
 * Author:            mbjtech
 * Author URI:        https://github.com/wp-mbjtechnolabs/smart-paypal-checkout-for-woocommerce
 * License:           GPLv3
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       smart-paypal-checkout-for-woocommerce
 * Domain Path:       /languages
 * WC tested up to: 5.8.0
 * WC requires at least: 3.0.0
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_VERSION', '2.0.2');
if (!defined('PSB_SANDBOX_PARTNER_MERCHANT_ID')) {
    define('PSB_SANDBOX_PARTNER_MERCHANT_ID', 'K6QLN2LPGQRHL');
}
if (!defined('PSB_LIVE_PARTNER_MERCHANT_ID')) {
    define('PSB_LIVE_PARTNER_MERCHANT_ID', 'GT5R877JNBPLL');
}
if (!defined('PSB_ONBOARDING_URL')) {
    define('PSB_ONBOARDING_URL', 'https://mbjtechnolabs.com/ppcp-seller-onboarding/seller-onboarding.php');
}
if (!defined('SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_PATH')) {
    define('SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_PATH', untrailingslashit(plugin_dir_path(__FILE__)));
}
if (!defined('SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_URL')) {
    define('SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_URL', plugin_dir_url(__FILE__));
}
if (!defined('SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_DIR')) {
    define('SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_DIR', dirname(__FILE__));
}
if (!defined('SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_BASENAME')) {
    define('SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_BASENAME', plugin_basename(__FILE__));
}
if (!defined('SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_ASSET_URL')) {
    define('SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_ASSET_URL', plugin_dir_url(__FILE__));
}

function activate_paypal_checkout_for_woocommerce() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-smart-paypal-checkout-for-woocommerce-activator.php';
    Paypal_Checkout_For_Woocommerce_Activator::activate();
}

function deactivate_paypal_checkout_for_woocommerce() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-smart-paypal-checkout-for-woocommerce-deactivator.php';
    Paypal_Checkout_For_Woocommerce_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_paypal_checkout_for_woocommerce');
register_deactivation_hook(__FILE__, 'deactivate_paypal_checkout_for_woocommerce');
require plugin_dir_path(__FILE__) . 'includes/class-smart-paypal-checkout-for-woocommerce.php';

function run_paypal_checkout_for_woocommerce() {
    $plugin = new Paypal_Checkout_For_Woocommerce();
    $plugin->run();
}

add_action('plugins_loaded', 'load_paypal_checkout_for_woocommerce', 11);

function load_paypal_checkout_for_woocommerce() {
    if (class_exists('WC_Payment_Gateway')) {
        run_paypal_checkout_for_woocommerce();
    }
}
