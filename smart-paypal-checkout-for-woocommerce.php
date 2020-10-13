<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Smart PayPal Checkout For WooCommerce
 * Plugin URI:        https://github.com/wp-mbjtechnolabs/smart-paypal-checkout-for-woocommerce
 * Description:       PayPal Checkout with Smart Payment Buttons gives your buyers a simplified and secure checkout experience.
 * Version:           1.0.0
 * Author:            paypal@mbjtechnlabs.com
 * Author URI:        https://github.com/wp-mbjtechnolabs/smart-paypal-checkout-for-woocommerce
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       smart-paypal-checkout-for-woocommerce
 * Domain Path:       /languages
 * WC tested up to: 4.5.2
 * WC requires at least: 2.6
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_VERSION', '1.0.0');
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
