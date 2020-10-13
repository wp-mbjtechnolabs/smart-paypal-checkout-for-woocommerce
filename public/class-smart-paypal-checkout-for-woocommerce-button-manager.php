<?php

/**
 * @package    Paypal_Checkout_For_Woocommerce_Button_Manager
 * @subpackage Paypal_Checkout_For_Woocommerce_Button_Manager/public
 * @author     PayPal <paypal@mbjtechnolabs.com>
 */
class Paypal_Checkout_For_Woocommerce_Button_Manager {

    private $plugin_name;
    private $version;
    public $request;
    public $woocommerce_paypal_smart_checkout_settings;
    public $checkout_details;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->checkout_details = '';
        if (empty($this->woocommerce_paypal_smart_checkout_settings)) {
            $this->woocommerce_paypal_smart_checkout_settings = get_option('woocommerce_paypal_smart_checkout_settings', true);
        }

        $this->get_properties();
        if ($this->is_valid_for_use() === true) {
            if (!has_action('woocommerce_api_' . strtolower('Paypal_Checkout_For_Woocommerce_Button_Manager'))) {
                add_action('woocommerce_api_' . strtolower('Paypal_Checkout_For_Woocommerce_Button_Manager'), array($this, 'handle_wc_api'));
            }
            include_once SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_PATH . '/includes/class-smart-paypal-checkout-for-woocommerce-request.php';
            $this->request = new Paypal_Checkout_For_Woocommerce_Request();
            $this->psb_add_hooks();
        }
    }

    public function get_properties() {
        $this->title = $this->psb_get_settings('title', 'PayPal');
        $this->enabled = 'yes' === $this->psb_get_settings('enabled', 'no');
        $this->testmode = 'yes' === $this->psb_get_settings('testmode', 'no');
        if ($this->testmode) {
            $this->client_id = $this->psb_get_settings('sandbox_client_id');
            $this->secret = $this->psb_get_settings('sandbox_api_secret');
            $this->webhook_id = 'snadbox_webhook_id';
            $this->access_token = get_transient('psb_sandbox_access_token');
            $this->client_token = get_transient('psb_sandbox_client_token');
        } else {
            $this->client_id = $this->psb_get_settings('api_client_id');
            $this->secret = $this->psb_get_settings('api_secret');
            $this->webhook_id = 'live_webhook_id';
            $this->access_token = get_transient('psb_access_token');
            $this->client_token = get_transient('psb_client_token');
        }
        $this->psb_currency_list = array('AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'INR', 'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'RUB', 'SGD', 'SEK', 'CHF', 'THB', 'USD');
        $this->psb_currency = in_array(get_woocommerce_currency(), $this->psb_currency_list) ? get_woocommerce_currency() : 'USD';
        $this->disable_funding = $this->psb_get_settings('disable_funding', false);
        $this->style_color = $this->psb_get_settings('style_color', 'gold');
        $this->style_shape = $this->psb_get_settings('style_shape', 'rect');
        $this->style_label = $this->psb_get_settings('style_label', 'paypal');
        $this->order_review_page_title = $this->psb_get_settings('order_review_page_title', 'Confirm your PayPal order');
        $this->order_review_page_enable_coupons = 'yes' === $this->psb_get_settings('order_review_page_enable_coupons', 'yes');
        $this->order_review_page_description = $this->psb_get_settings('order_review_page_description', false);
        $this->paymentaction = $this->psb_get_settings('paymentaction', 'capture');
        if ($this->paymentaction === 'authorize' && get_woocommerce_currency() === 'INR') {
            $this->paymentaction = 'capture';
        }
        $this->advanced_card_payments = 'yes' === $this->psb_get_settings('enable_advanced_card_payments', 'no');
        if (psb_is_advanced_cards_available() === false) {
            $this->advanced_card_payments = false;
        }
        if ($this->advanced_card_payments) {
            $this->threed_secure_enabled = 'yes' === $this->psb_get_settings('threed_secure_enabled', 'no');
        } else {
            $this->threed_secure_enabled = false;
        }
        $this->AVSCodes = array("A" => "Address Matches Only (No ZIP)",
            "B" => "Address Matches Only (No ZIP)",
            "C" => "This tranaction was declined.",
            "D" => "Address and Postal Code Match",
            "E" => "This transaction was declined.",
            "F" => "Address and Postal Code Match",
            "G" => "Global Unavailable - N/A",
            "I" => "International Unavailable - N/A",
            "N" => "None - Transaction was declined.",
            "P" => "Postal Code Match Only (No Address)",
            "R" => "Retry - N/A",
            "S" => "Service not supported - N/A",
            "U" => "Unavailable - N/A",
            "W" => "Nine-Digit ZIP Code Match (No Address)",
            "X" => "Exact Match - Address and Nine-Digit ZIP",
            "Y" => "Address and five-digit Zip match",
            "Z" => "Five-Digit ZIP Matches (No Address)");

        $this->CVV2Codes = array(
            "E" => "N/A",
            "M" => "Match",
            "N" => "No Match",
            "P" => "Not Processed - N/A",
            "S" => "Service Not Supported - N/A",
            "U" => "Service Unavailable - N/A",
            "X" => "No Response - N/A"
        );
    }

    public function psb_add_hooks() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('woocommerce_after_add_to_cart_form', array($this, 'display_paypal_button_product_page'), 1);
        add_action('woocommerce_proceed_to_checkout', array($this, 'display_paypal_button_cart_page'), 11);
        add_action('display_paypal_button_checkout_page', array($this, 'display_paypal_button_checkout_page'));
        add_action('init', array($this, 'init'));
        add_filter('clean_url', array($this, 'psb_clean_url'));
        add_action('wp_loaded', array($this, 'psb_session_manager'), 999);
        add_action('wp_head', array($this, 'psb_add_header_meta'), 0);
        add_filter('the_title', array($this, 'psb_endpoint_page_titles'));
        add_action('woocommerce_cart_emptied', array($this, 'maybe_clear_session_data'));
        add_action('woocommerce_checkout_init', array($this, 'psb_checkout_init'));
        add_action('woocommerce_available_payment_gateways', array($this, 'maybe_disable_other_gateways'));
        add_filter('woocommerce_default_address_fields', array($this, 'filter_default_address_fields'));
        add_filter('woocommerce_billing_fields', array($this, 'filter_billing_fields'));
        add_action('woocommerce_checkout_process', array($this, 'copy_checkout_details_to_post'));
        add_action('woocommerce_cart_shipping_packages', array($this, 'maybe_add_shipping_information'));
        add_filter('body_class', array($this, 'psb_add_class_order_review_page'));
        add_filter('woocommerce_coupons_enabled', array($this, 'psb_woocommerce_coupons_enabled'), 999, 1);
        add_action('woocommerce_before_checkout_form', array($this, 'psb_order_review_page_description'), 9);
        add_action('woocommerce_order_status_processing', array($this, 'psb_capture_payment'));
        add_action('woocommerce_order_status_completed', array($this, 'psb_capture_payment'));
        add_action('woocommerce_order_status_cancelled', array($this, 'psb_cancel_authorization'));
        add_action('woocommerce_order_status_refunded', array($this, 'psb_cancel_authorization'));
        add_filter('woocommerce_order_actions', array($this, 'psb_add_capture_charge_order_action'));
        add_action('woocommerce_order_action_psb_capture_charge', array($this, 'psb_maybe_capture_charge'));
        add_action('woocommerce_before_checkout_form', array($this, 'psb_update_checkout_field_details'));
        add_action('woocommerce_review_order_before_submit', array($this, 'psb_cancel_button'));
        add_action('wp_head', array($this, 'psb_create_webhooks'));
    }

    public function enqueue_scripts() {
        if (is_checkout() && $this->advanced_card_payments) {
            delete_transient('psb_sandbox_client_token');
            delete_transient('psb_client_token');
            $this->request->get_genrate_token();
            $this->get_properties();
        }
        $smart_js_arg = array();
        $smart_js_arg['client-id'] = $this->client_id;
        $smart_js_arg['currency'] = $this->psb_currency;
        if ($this->disable_funding !== false && count($this->disable_funding) > 0) {
            $smart_js_arg['disable-funding'] = implode(',', $this->disable_funding);
        }
        if ($this->testmode) {
            $smart_js_arg['buyer-country'] = WC()->countries->get_base_country();
        }
        $is_cart = is_cart() && !WC()->cart->is_empty();
        $is_product = is_product();
        $is_checkout = is_checkout();
        $page = $is_cart ? 'cart' : ( $is_product ? 'product' : ( $is_checkout ? 'checkout' : null ) );
        $smart_js_arg['commit'] = ( $page === 'checkout' ) ? 'true' : 'false';
        $smart_js_arg['intent'] = ( $this->paymentaction === 'capture' ) ? 'capture' : 'authorize';
        $smart_js_arg['locale'] = get_button_locale_code();
        if (is_checkout() && $this->advanced_card_payments) {
            $smart_js_arg['components'] = "hosted-fields,buttons";
        }
        $js_url = add_query_arg($smart_js_arg, 'https://www.paypal.com/sdk/js');
        wp_register_script('psb-checkout-js', $js_url, array(), null, false);
        wp_register_script($this->plugin_name, SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_URL . '/public/js/smart-paypal-checkout-for-woocommerce-public.js', array('jquery'), time(), false);
        $this->style_layout = is_product() ? 'horizontal' : 'vertical';
        wp_localize_script($this->plugin_name, 'psb_manager', array(
            'style_color' => $this->style_color,
            'style_shape' => $this->style_shape,
            'style_label' => $this->style_label,
            'style_layout' => $this->style_layout,
            'page' => $page,
            'checkout_url' => wc_get_checkout_url(),
            'display_order_page' => add_query_arg(array('psb_action' => 'display_order_page', 'utm_nooverride' => '1'), WC()->api_request_url('Paypal_Checkout_For_Woocommerce_Button_Manager')),
            'cc_capture' => add_query_arg(array('psb_action' => 'cc_capture', 'utm_nooverride' => '1'), WC()->api_request_url('Paypal_Checkout_For_Woocommerce_Button_Manager')),
            'create_order_url' => add_query_arg(array('psb_action' => 'create_order', 'utm_nooverride' => '1', 'from' => $page), WC()->api_request_url('Paypal_Checkout_For_Woocommerce_Button_Manager')),
            'cancel_url' => wc_get_cart_url(),
            'cart_total' => WC()->cart->total,
            'paymentaction' => $this->paymentaction,
            'advanced_card_payments' => ($this->advanced_card_payments === true) ? 'yes' : 'no',
            'threed_secure_enabled' => ($this->threed_secure_enabled === true) ? 'yes' : 'no',
            'woocommerce_process_checkout' => wp_create_nonce('woocommerce-process_checkout')
                )
        );
        if (is_checkout() && empty($this->checkout_details)) {
            wp_enqueue_script('smart-paypal-checkout-for-woocommerce-order-review', SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_URL . '/public/js/smart-paypal-checkout-for-woocommerce-order-review.js', array('jquery'), $this->version, false);
        } elseif (is_checkout() && !empty($this->checkout_details)) {
            wp_enqueue_script('smart-paypal-checkout-for-woocommerce-order-review', SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_URL . '/public/js/smart-paypal-checkout-for-woocommerce-order-capture.js', array('jquery'), $this->version, false);
        }
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/smart-paypal-checkout-for-woocommerce-public.css', array(), $this->version, 'all');
    }

    public function is_valid_for_use() {
        if (!empty($this->client_id) && !empty($this->secret) && $this->enabled) {
            return true;
        }
        return false;
    }

    public function display_paypal_button_cart_page() {
        if (get_woocommerce_currency() === 'INR') {
            return;
        }
        wp_enqueue_script('psb-checkout-js');
        wp_enqueue_script($this->plugin_name);
        echo '<div class="psb-button-container"><div id="psb_cart"></div><div class="psb-proceed-to-checkout-button-separator">&mdash; ' . __('OR', 'smart-paypal-checkout-for-woocommerce') . ' &mdash;</div></div>';
    }

    public function display_paypal_button_product_page() {
        global $product;
        if (get_woocommerce_currency() === 'INR') {
            return;
        }
        if (!is_product() || !$product->is_in_stock() || $product->is_type('external') || $product->is_type('grouped')) {
            return;
        }
        wp_enqueue_script('psb-checkout-js');
        wp_enqueue_script($this->plugin_name);
        echo '<div class="psb-button-container"><div id="psb_product"></div></div>';
    }

    public function display_paypal_button_checkout_page() {
        if (has_active_session() === false) {
            wp_enqueue_script('psb-checkout-js');
            wp_enqueue_script($this->plugin_name);
            echo '<div class="psb-button-container"><div id="psb_checkout"></div><div class="psb-proceed-to-checkout-button-separator checkout_cc_separator">&mdash;&mdash; ' . __('OR', 'smart-paypal-checkout-for-woocommerce') . ' &mdash;&mdash;</div></div>';
        }
    }

    public function psb_add_header_meta() {
        if ($this->is_valid_for_use() === true) {
            echo '<meta http-equiv="X-UA-Compatible" content="IE=edge" />';
            echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        }
    }

    public function psb_get_settings($key, $bool = false) {
        if (!empty($this->woocommerce_paypal_smart_checkout_settings)) {
            if (!empty($this->woocommerce_paypal_smart_checkout_settings[$key])) {
                return $this->woocommerce_paypal_smart_checkout_settings[$key];
            } else {
                return $bool;
            }
        } else {
            return $bool;
        }
    }

    public function psb_endpoint_page_titles($title) {
        if (!is_admin() && is_main_query() && in_the_loop() && is_page() && is_checkout() && !empty($this->checkout_details)) {
            $title = $this->order_review_page_title;
            remove_filter('the_title', array($this, 'psb_endpoint_page_titles'));
        }
        return $title;
    }

    public function handle_wc_api() {
        if (!empty($_GET['psb_action'])) {
            switch ($_GET['psb_action']) {
                case "webhook_handler":
                    $this->psb_handle_webhook_request();
                    ob_clean();
                    header('HTTP/1.1 200 OK');
                    exit();
                    break;
                case "cancel_order":
                    unset(WC()->session->psb_session);
                    wp_redirect(wc_get_cart_url());
                    exit();
                    break;
                case "create_order":
                    if (isset($_GET['from']) && 'checkout' === $_GET['from']) {
                        add_action('woocommerce_after_checkout_validation', array($this, 'maybe_start_checkout'), 10, 2);
                        WC()->checkout->process_checkout();
                    } elseif (isset($_GET['from']) && 'product' === $_GET['from']) {
                        try {
                            Paypal_Checkout_For_Woocommerce_Product::psb_add_to_cart_action();
                            $this->request->psb_create_order_request();
                            exit();
                        } catch (Exception $ex) {
                            
                        }
                    } else {
                        $this->request->psb_create_order_request();
                        exit();
                    }
                    break;
                case "display_order_page":
                    $this->psb_display_order_page();
                    break;
                case "cc_capture":
                    wc_clear_notices();
                    psb_set_session('psb_paypal_order_id', wc_clean($_GET['paypal_order_id']));
                    $this->psb_cc_capture();
                    break;
            }
        }
    }

    public function psb_checkout_init($checkout) {
        if (empty($this->checkout_details) || (isset($_GET['from']) && 'checkout' === $_GET['from'])) {
            return;
        }
        //remove_action('woocommerce_checkout_billing', array($checkout, 'checkout_form_billing'));
        //remove_action('woocommerce_checkout_shipping', array($checkout, 'checkout_form_shipping'));
        add_action('woocommerce_checkout_billing', array($this, 'paypal_billing_details'), 9);
        if (true === WC()->cart->needs_shipping_address()) {
            add_action('woocommerce_checkout_shipping', array($this, 'paypal_shipping_details'), 9);
        }
    }

    public function paypal_billing_details() {
        if (empty($this->checkout_details)) {
            return false;
        }
        ?>
        <div class="psb_billing_details">
            <?php if (wc_ship_to_billing_address_only() && WC()->cart->needs_shipping()) : ?>
                <h3><?php esc_html_e('Billing &amp; Shipping', 'smart-paypal-checkout-for-woocommerce'); ?>&nbsp;&nbsp;&nbsp;<a class="psb_edit_billing_address"><?php _e('Edit', 'smart-paypal-checkout-for-woocommerce'); ?></a></h3>
            <?php else : ?>
                <h3><?php esc_html_e('Billing details', 'smart-paypal-checkout-for-woocommerce'); ?>&nbsp;&nbsp;&nbsp;<a class="psb_edit_billing_address"><?php _e('Edit', 'smart-paypal-checkout-for-woocommerce'); ?></a></h3>
            <?php
            endif;
            echo WC()->countries->get_formatted_address($this->get_mapped_billing_address($this->checkout_details));
            ?>

        </div>
        <?php
    }

    public function paypal_shipping_details() {
        if (empty($this->checkout_details)) {
            return false;
        }
        ?>
        <div class="psb_shipping_details">
            <h3><?php _e('Shipping details', 'smart-paypal-checkout-for-woocommerce'); ?>&nbsp;&nbsp;&nbsp;<a class="psb_edit_shipping_address"><?php _e('Edit', 'smart-paypal-checkout-for-woocommerce'); ?></a></h3>
            <?php
            echo WC()->countries->get_formatted_address($this->get_mapped_shipping_address($this->checkout_details));
            echo '</div>';
        }

        public function get_mapped_billing_address($checkout_details) {
            if (empty($this->checkout_details->payer)) {
                return array();
            }
            $phone = '';
            if (!empty($checkout_details->payer->phone_number)) {
                $phone = $this->checkout_details->payer->phone_number;
            } elseif (!empty($_POST['billing_phone'])) {
                $phone = wc_clean($_POST['billing_phone']);
            }
            $billing_address = array();
            $billing_address['first_name'] = !empty($this->checkout_details->payer->name->given_name) ? $this->checkout_details->payer->name->given_name : '';
            $billing_address['last_name'] = !empty($this->checkout_details->payer->name->surname) ? $this->checkout_details->payer->name->surname : '';
            $billing_address['company'] = !empty($this->checkout_details->payer->business_name) ? $this->checkout_details->payer->business_name : '';
            if (!empty($this->checkout_details->payer->address->address_line_1) && !empty($this->checkout_details->payer->address->postal_code)) {
                $billing_address['address_1'] = !empty($this->checkout_details->payer->address->address_line_1) ? $this->checkout_details->payer->address->address_line_1 : '';
                $billing_address['address_2'] = !empty($this->checkout_details->payer->address->address_line_2) ? $this->checkout_details->payer->address->address_line_2 : '';
                $billing_address['city'] = !empty($this->checkout_details->payer->address->admin_area_2) ? $this->checkout_details->payer->address->admin_area_2 : '';
                $billing_address['state'] = !empty($this->checkout_details->payer->address->admin_area_1) ? $this->checkout_details->payer->address->admin_area_1 : '';
                $billing_address['postcode'] = !empty($this->checkout_details->payer->address->postal_code) ? $this->checkout_details->payer->address->postal_code : '';
                $billing_address['country'] = !empty($this->checkout_details->payer->address->country_code) ? $this->checkout_details->payer->address->country_code : '';
                $billing_address['phone'] = $phone;
                $billing_address['email'] = !empty($this->checkout_details->payer->email_address) ? $this->checkout_details->payer->email_address : '';
            } else {
                $billing_address['address_1'] = !empty($this->checkout_details->purchase_units[0]->shipping->address->address_line_1) ? $this->checkout_details->purchase_units[0]->shipping->address->address_line_1 : '';
                $billing_address['address_2'] = !empty($this->checkout_details->purchase_units[0]->shipping->address->address_line_2) ? $this->checkout_details->purchase_units[0]->shipping->address->address_line_2 : '';
                $billing_address['city'] = !empty($this->checkout_details->purchase_units[0]->shipping->address->admin_area_2) ? $this->checkout_details->purchase_units[0]->shipping->address->admin_area_2 : '';
                $billing_address['state'] = !empty($this->checkout_details->purchase_units[0]->shipping->address->admin_area_1) ? $this->checkout_details->purchase_units[0]->shipping->address->admin_area_1 : '';
                $billing_address['postcode'] = !empty($this->checkout_details->purchase_units[0]->shipping->address->postal_code) ? $this->checkout_details->purchase_units[0]->shipping->address->postal_code : '';
                $billing_address['country'] = !empty($this->checkout_details->purchase_units[0]->shipping->address->country_code) ? $this->checkout_details->purchase_units[0]->shipping->address->country_code : '';
                $billing_address['phone'] = $phone;
                $billing_address['email'] = !empty($this->checkout_details->payer->email_address) ? $this->checkout_details->payer->email_address : '';
            }
            return $billing_address;
        }

        public function get_mapped_shipping_address() {
            if (empty($this->checkout_details->purchase_units[0]) || empty($this->checkout_details->purchase_units[0]->shipping)) {
                return array();
            }
            if (!empty($this->checkout_details->purchase_units[0]->shipping->name->full_name)) {
                $name = explode(' ', $this->checkout_details->purchase_units[0]->shipping->name->full_name);
                $first_name = array_shift($name);
                $last_name = implode(' ', $name);
            } else {
                $first_name = '';
                $last_name = '';
            }
            $result = array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'address_1' => !empty($this->checkout_details->purchase_units[0]->shipping->address->address_line_1) ? $this->checkout_details->purchase_units[0]->shipping->address->address_line_1 : '',
                'address_2' => !empty($this->checkout_details->purchase_units[0]->shipping->address->address_line_2) ? $this->checkout_details->purchase_units[0]->shipping->address->address_line_2 : '',
                'city' => !empty($this->checkout_details->purchase_units[0]->shipping->address->admin_area_2) ? $this->checkout_details->purchase_units[0]->shipping->address->admin_area_2 : '',
                'state' => !empty($this->checkout_details->purchase_units[0]->shipping->address->admin_area_1) ? $this->checkout_details->purchase_units[0]->shipping->address->admin_area_1 : '',
                'postcode' => !empty($this->checkout_details->purchase_units[0]->shipping->address->postal_code) ? $this->checkout_details->purchase_units[0]->shipping->address->postal_code : '',
                'country' => !empty($this->checkout_details->purchase_units[0]->shipping->address->country_code) ? $this->checkout_details->purchase_units[0]->shipping->address->country_code : '',
            );
            if (!empty($this->checkout_details->payer->business_name)) {
                $result['company'] = $this->checkout_details->payer->business_name;
            }
            return $result;
        }

        public function account_registration() {
            $checkout = WC()->checkout();
            if (!is_user_logged_in() && $checkout->enable_signup) {
                if ($checkout->enable_guest_checkout) {
                    ?>
                    <p class="form-row form-row-wide create-account">
                        <input class="input-checkbox" id="createaccount" <?php checked(( true === $checkout->get_value('createaccount') || ( true === apply_filters('woocommerce_create_account_default_checked', false) )), true) ?> type="checkbox" name="createaccount" value="1" /> <label for="createaccount" class="checkbox"><?php _e('Create an account?', 'smart-paypal-checkout-for-woocommerce'); ?></label>
                    </p>
                    <?php
                }
                if (!empty($checkout->checkout_fields['account'])) {
                    ?>
                    <div class="create-account">
                        <p><?php _e('Create an account by entering the information below. If you are a returning customer please login at the top of the page.', 'smart-paypal-checkout-for-woocommerce'); ?></p>
                        <?php foreach ($checkout->checkout_fields['account'] as $key => $field) : ?>
                            <?php woocommerce_form_field($key, $field, $checkout->get_value($key)); ?>
                        <?php endforeach; ?>
                        <div class="clear"></div>
                    </div>
                    <?php
                }
            }
        }

        public function maybe_disable_other_gateways($gateways) {
            if (empty($this->checkout_details) || (isset($_GET['from']) && 'checkout' === $_GET['from'])) {
                return $gateways;
            }
            foreach ($gateways as $id => $gateway) {
                if ('paypal_smart_checkout' !== $id) {
                    unset($gateways[$id]);
                }
            }
            if (is_cart() || ( is_checkout() && !is_checkout_pay_page() )) {
                if (isset($gateways['paypal_smart_checkout']) && ( 0 >= WC()->cart->total )) {
                    unset($gateways['paypal_smart_checkout']);
                }
            }
            return $gateways;
        }

        public function filter_billing_fields($billing_fields) {
            if (empty($this->checkout_details) || (isset($_GET['from']) && 'checkout' === $_GET['from'])) {
                return $billing_fields;
            }
            if ($this->enabled === false) {
                return $billing_fields;
            }
            if (array_key_exists('billing_phone', $billing_fields)) {
                $billing_fields['billing_phone']['required'] = false;
            }
            return $billing_fields;
        }

        public function filter_default_address_fields($fields) {
            if (empty($this->checkout_details)) {
                return $fields;
            }
            if ($this->enabled === false) {
                return $fields;
            }
            if (method_exists(WC()->cart, 'needs_shipping') && !WC()->cart->needs_shipping()) {
                $not_required_fields = array('first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'postcode', 'country');
                foreach ($not_required_fields as $not_required_field) {
                    if (array_key_exists($not_required_field, $fields)) {
                        $fields[$not_required_field]['required'] = false;
                    }
                }
            }
            if (array_key_exists('state', $fields)) {
                $fields['state']['required'] = false;
            }
            return $fields;
        }

        public function copy_checkout_details_to_post() {
            if (empty($this->checkout_details)) {
                $this->checkout_details = psb_get_session('psb_paypal_transaction_details', false);

                if (empty($this->checkout_details)) {
                    if (!empty($_GET['paypal_order_id'])) {
                        $this->checkout_details = $this->request->psb_get_checkout_details($_GET['paypal_order_id']);
                    }
                }

                if (empty($this->checkout_details)) {
                    return;
                }
                psb_set_session('psb_paypal_transaction_details', $this->checkout_details);
            }
            if (!isset($_POST['payment_method']) || ( 'paypal_smart_checkout' !== $_POST['payment_method'] ) || empty($this->checkout_details)) {
                return;
            }
            $shipping_details = $this->get_mapped_shipping_address($this->checkout_details);
            $billing_details = $this->get_mapped_billing_address($this->checkout_details);
            $this->update_customer_addresses_from_paypal($shipping_details, $billing_details);
            /* if (empty($billing_details['address_1'])) {
              $_POST['ship_to_different_address'] = 0;
              $copyable_keys = array('first_name', 'last_name', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country');
              foreach ($copyable_keys as $copyable_key) {
              if (array_key_exists($copyable_key, $shipping_details)) {
              $billing_details[$copyable_key] = $shipping_details[$copyable_key];
              }
              }
              } else {
              $_POST['ship_to_different_address'] = 1;
              }
              foreach ($shipping_details as $key => $value) {
              $_POST['shipping_' . $key] = $value;
              }
              foreach ($billing_details as $key => $value) {
              $_POST['billing_' . $key] = $value;
              } */
        }

        public function maybe_add_shipping_information($packages) {
            if (empty($this->checkout_details) || (isset($_GET['from']) && 'checkout' === $_GET['from'])) {
                return $packages;
            }
            $destination = $this->get_mapped_shipping_address($this->checkout_details);
            if (!empty($destination)) {
                $packages[0]['destination']['country'] = $destination['country'];
                $packages[0]['destination']['state'] = $destination['state'];
                $packages[0]['destination']['postcode'] = $destination['postcode'];
                $packages[0]['destination']['city'] = $destination['city'];
                $packages[0]['destination']['address'] = $destination['address_1'];
                $packages[0]['destination']['address_2'] = $destination['address_2'];
            }
            return $packages;
        }

        public function init() {
            if (version_compare(WC_VERSION, '3.3', '<')) {
                add_filter('wc_checkout_params', array($this, 'filter_wc_checkout_params'), 10, 1);
            } else {
                add_filter('woocommerce_get_script_data', array($this, 'filter_wc_checkout_params'), 10, 2);
            }
        }

        public function filter_wc_checkout_params($params, $handle = '') {
            if ('wc-checkout' !== $handle && !doing_action('wc_checkout_params')) {
                return $params;
            }
            $fields = array('paypal_order_id', 'paypal_payer_id');
            $params['wc_ajax_url'] = remove_query_arg('wc-ajax', $params['wc_ajax_url']);
            foreach ($fields as $field) {
                if (!empty($_GET[$field])) {
                    $params['wc_ajax_url'] = add_query_arg($field, $_GET[$field], $params['wc_ajax_url']);
                }
            }
            $params['wc_ajax_url'] = add_query_arg('wc-ajax', '%%endpoint%%', $params['wc_ajax_url']);
            return $params;
        }

        public function psb_session_manager() {
            try {
                if (!empty($_GET['paypal_order_id']) && !empty($_GET['paypal_payer_id'])) {
                    if (isset($_GET['from']) && 'product' === $_GET['from']) {
                        if (function_exists('wc_clear_notices')) {
                            wc_clear_notices();
                        }
                    }
                    psb_set_session('psb_paypal_order_id', wc_clean($_GET['paypal_order_id']));
                    if (empty($this->checkout_details)) {
                        $this->checkout_details = psb_get_session('psb_paypal_transaction_details', false);
                        if ($this->checkout_details === false) {
                            $this->checkout_details = $this->request->psb_get_checkout_details($_GET['paypal_order_id']);
                            psb_set_session('psb_paypal_transaction_details', $this->checkout_details);
                        }
                    }
                }
            } catch (Exception $ex) {
                
            }
        }

        public function psb_display_order_page() {
            $this->checkout_details = $this->request->psb_get_checkout_details($_GET['paypal_order_id']);
            psb_set_session('psb_paypal_transaction_details', $this->checkout_details);
            if (empty($this->checkout_details)) {
                return false;
            }
            if (!empty($this->checkout_details)) {
                $shipping_details = $this->get_mapped_shipping_address($this->checkout_details);
                $billing_details = $this->get_mapped_billing_address($this->checkout_details);
                $this->update_customer_addresses_from_paypal($shipping_details, $billing_details);
            }
            $order_id = absint(psb_get_session('order_awaiting_payment'));
            if (empty($order_id)) {
                $order_id = psb_get_session('psb_woo_order_id');
            }
            $order = wc_get_order($order_id);
            $this->checkout_details = $this->checkout_details;
            if ($this->paymentaction === 'capture' && !empty($this->checkout_details->status) && $this->checkout_details->status == 'COMPLETED' && $order !== false) {
                $transaction_id = isset($this->checkout_details->purchase_units[0]->payments->captures[0]->id) ? $this->checkout_details->purchase_units['0']->payments->captures[0]->id : '';
                $seller_protection = isset($this->checkout_details->purchase_units[0]->payments->captures[0]->seller_protection->status) ? $this->checkout_details->purchase_units[0]->payments->captures[0]->seller_protection->status : '';
                $payment_source = isset($this->checkout_details->payment_source) ? $this->checkout_details->payment_source : '';
                if (!empty($payment_source->card)) {
                    $card_response_order_note = __('Card Details', 'smart-paypal-checkout-for-woocommerce');
                    $card_response_order_note .= "\n";
                    $card_response_order_note .= 'Last digits : ' . $payment_source->card->last_digits;
                    $card_response_order_note .= "\n";
                    $card_response_order_note .= 'Brand : ' . psb_readable($payment_source->card->brand);
                    $card_response_order_note .= "\n";
                    $card_response_order_note .= 'Card type : ' . psb_readable($payment_source->card->type);
                    $order->add_order_note($card_response_order_note);
                }
                $processor_response = isset($this->checkout_details->purchase_units[0]->payments->captures[0]->processor_response) ? $this->checkout_details->purchase_units[0]->payments->captures[0]->processor_response : '';
                if (!empty($processor_response->avs_code)) {
                    $avs_response_order_note = __('Address Verification Result', 'smart-paypal-checkout-for-woocommerce');
                    $avs_response_order_note .= "\n";
                    $avs_response_order_note .= $processor_response->avs_code;
                    if (isset($this->AVSCodes[$processor_response->avs_code])) {
                        $avs_response_order_note .= ' : ' . $this->AVSCodes[$processor_response->avs_code];
                    }
                    $order->add_order_note($avs_response_order_note);
                }
                if (!empty($processor_response->cvv_code)) {
                    $cvv2_response_code = __('Card Security Code Result', 'smart-paypal-checkout-for-woocommerce');
                    $cvv2_response_code .= "\n";
                    $cvv2_response_code .= $processor_response->cvv_code;
                    if (isset($this->CVV2Codes[$processor_response->cvv_code])) {
                        $cvv2_response_code .= ' : ' . $this->CVV2Codes[$processor_response->cvv_code];
                    }
                    $order->add_order_note($cvv2_response_code);
                }
                $currency_code = isset($this->checkout_details->purchase_units[0]->payments->captures[0]->seller_receivable_breakdown->paypal_fee->currency_code) ? $this->checkout_details->purchase_units[0]->payments->captures[0]->seller_receivable_breakdown->paypal_fee->currency_code : '';
                $value = isset($this->checkout_details->purchase_units[0]->payments->captures[0]->seller_receivable_breakdown->paypal_fee->value) ? $this->checkout_details->purchase_units[0]->payments->captures[0]->seller_receivable_breakdown->paypal_fee->value : '';
                psb_update_post_meta($order, '_paypal_fee', $value);
                psb_update_post_meta($order, '_paypal_fee_currency_code', $currency_code);
                $payment_status = isset($this->checkout_details->purchase_units[0]->payments->captures[0]->status) ? $this->checkout_details->purchase_units[0]->payments->captures[0]->status : '';
                if ($payment_status == 'COMPLETED') {
                    $order->payment_complete($transaction_id);
                    $order->add_order_note(sprintf(__('Payment via %s : %s .', 'smart-paypal-checkout-for-woocommerce'), $this->title, ucfirst(strtolower($payment_status))));
                } else {
                    $payment_status_reason = isset($this->checkout_details->purchase_units[0]->payments->captures[0]->status_details->reason) ? $this->checkout_details->purchase_units[0]->payments->captures[0]->status_details->reason : '';
                    $order->update_status('on-hold');
                    $order->add_order_note(sprintf(__('Payment via %s Pending. PayPal reason: %s.', 'smart-paypal-checkout-for-woocommerce'), $this->title, $payment_status_reason));
                }
                $order->add_order_note(sprintf(__('%s Transaction ID: %s', 'smart-paypal-checkout-for-woocommerce'), $this->title, $transaction_id));
                $order->add_order_note('Seller Protection Status: ' . psb_readable($seller_protection));
            } elseif ($this->paymentaction === 'authorize' && !empty($this->checkout_details->status) && $this->checkout_details->status == 'COMPLETED' && $order !== false) {
                $transaction_id = isset($this->checkout_details->purchase_units[0]->payments->authorizations[0]->id) ? $this->checkout_details->purchase_units['0']->payments->authorizations[0]->id : '';
                $seller_protection = isset($this->checkout_details->purchase_units[0]->payments->authorizations[0]->seller_protection->status) ? $this->checkout_details->purchase_units[0]->payments->authorizations[0]->seller_protection->status : '';
                $payment_status = isset($this->checkout_details->purchase_units[0]->payments->authorizations[0]->status) ? $this->checkout_details->purchase_units[0]->payments->authorizations[0]->status : '';
                $payment_status_reason = isset($this->checkout_details->purchase_units[0]->payments->authorizations[0]->status_details->reason) ? $this->checkout_details->purchase_units[0]->payments->authorizations[0]->status_details->reason : '';
                if (!empty($payment_status_reason)) {
                    $order->add_order_note(sprintf(__('Payment via %s Pending. PayPal reason: %s.', 'smart-paypal-checkout-for-woocommerce'), $this->title, $payment_status_reason));
                }
                psb_update_post_meta($order, '_transaction_id', $transaction_id);
                psb_update_post_meta($order, '_payment_status', $payment_status);
                psb_update_post_meta($order, '_auth_transaction_id', $transaction_id);
                psb_update_post_meta($order, '_payment_action', $this->paymentaction);
                $order->add_order_note(sprintf(__('%s Transaction ID: %s', 'smart-paypal-checkout-for-woocommerce'), $this->title, $transaction_id));
                $order->add_order_note('Seller Protection Status: ' . psb_readable($seller_protection));
                $order->update_status('on-hold');
                $order->add_order_note(__('Payment authorized. Change payment status to processing or complete to capture funds.', 'smart-paypal-checkout-for-woocommerce'));
            }
            WC()->cart->empty_cart();
            unset(WC()->session->psb_session);
            wp_safe_redirect($order->get_checkout_order_received_url());
            exit();
        }

        public function update_customer_addresses_from_paypal($shipping_details, $billing_details) {
            try {
                $customer = WC()->customer;
                if (!empty($billing_details['address_1'])) {
                    $customer->set_billing_address($billing_details['address_1']);
                }
                if (!empty($billing_details['address_2'])) {
                    $customer->set_billing_address_2($billing_details['address_2']);
                }
                if (!empty($billing_details['city'])) {
                    $customer->set_billing_city($billing_details['city']);
                }
                if (!empty($billing_details['postcode'])) {
                    $customer->set_billing_postcode($billing_details['postcode']);
                }
                if (!empty($billing_details['state'])) {
                    $customer->set_billing_state($billing_details['state']);
                }
                if (!empty($billing_details['country'])) {
                    $customer->set_billing_country($billing_details['country']);
                }
                if (!empty($shipping_details['address_1'])) {
                    $customer->set_shipping_address($shipping_details['address_1']);
                }
                if (!empty($shipping_details['address_2'])) {
                    $customer->set_shipping_address_2($shipping_details['address_2']);
                }
                if (!empty($shipping_details['city'])) {
                    $customer->set_shipping_city($shipping_details['city']);
                }
                if (!empty($shipping_details['postcode'])) {
                    $customer->set_shipping_postcode($shipping_details['postcode']);
                }
                if (!empty($shipping_details['state'])) {
                    $customer->set_shipping_state($shipping_details['state']);
                }
                if (!empty($shipping_details['country'])) {
                    $customer->set_shipping_country($shipping_details['country']);
                }
            } catch (Exception $ex) {
                
            }
        }

        public function maybe_start_checkout($data, $errors = null) {
            try {
                if (is_null($errors)) {
                    $error_messages = wc_get_notices('error');
                    wc_clear_notices();
                } else {
                    $error_messages = $errors->get_error_messages();
                }
                if (empty($error_messages)) {
                    $this->set_customer_data($_POST);
                } else {
                    ob_start();
                    wp_send_json_error(array('messages' => $error_messages));
                    exit;
                }
            } catch (Exception $ex) {
                
            }
        }

        function set_customer_data($data) {
            try {
                $customer = WC()->customer;
                $billing_first_name = empty($data['billing_first_name']) ? '' : wc_clean($data['billing_first_name']);
                $billing_last_name = empty($data['billing_last_name']) ? '' : wc_clean($data['billing_last_name']);
                $billing_country = empty($data['billing_country']) ? '' : wc_clean($data['billing_country']);
                $billing_address_1 = empty($data['billing_address_1']) ? '' : wc_clean($data['billing_address_1']);
                $billing_address_2 = empty($data['billing_address_2']) ? '' : wc_clean($data['billing_address_2']);
                $billing_city = empty($data['billing_city']) ? '' : wc_clean($data['billing_city']);
                $billing_state = empty($data['billing_state']) ? '' : wc_clean($data['billing_state']);
                $billing_postcode = empty($data['billing_postcode']) ? '' : wc_clean($data['billing_postcode']);
                $billing_phone = empty($data['billing_phone']) ? '' : wc_clean($data['billing_phone']);
                $billing_email = empty($data['billing_email']) ? '' : wc_clean($data['billing_email']);
                if (isset($data['ship_to_different_address'])) {
                    $shipping_first_name = empty($data['shipping_first_name']) ? '' : wc_clean($data['shipping_first_name']);
                    $shipping_last_name = empty($data['shipping_last_name']) ? '' : wc_clean($data['shipping_last_name']);
                    $shipping_country = empty($data['shipping_country']) ? '' : wc_clean($data['shipping_country']);
                    $shipping_address_1 = empty($data['shipping_address_1']) ? '' : wc_clean($data['shipping_address_1']);
                    $shipping_address_2 = empty($data['shipping_address_2']) ? '' : wc_clean($data['shipping_address_2']);
                    $shipping_city = empty($data['shipping_city']) ? '' : wc_clean($data['shipping_city']);
                    $shipping_state = empty($data['shipping_state']) ? '' : wc_clean($data['shipping_state']);
                    $shipping_postcode = empty($data['shipping_postcode']) ? '' : wc_clean($data['shipping_postcode']);
                } else {
                    $shipping_first_name = $billing_first_name;
                    $shipping_last_name = $billing_last_name;
                    $shipping_country = $billing_country;
                    $shipping_address_1 = $billing_address_1;
                    $shipping_address_2 = $billing_address_2;
                    $shipping_city = $billing_city;
                    $shipping_state = $billing_state;
                    $shipping_postcode = $billing_postcode;
                }
                $customer->set_shipping_country($shipping_country);
                $customer->set_shipping_address($shipping_address_1);
                $customer->set_shipping_address_2($shipping_address_2);
                $customer->set_shipping_city($shipping_city);
                $customer->set_shipping_state($shipping_state);
                $customer->set_shipping_postcode($shipping_postcode);
                if (version_compare(WC_VERSION, '3.0', '<')) {
                    $customer->shipping_first_name = $shipping_first_name;
                    $customer->shipping_last_name = $shipping_last_name;
                    $customer->billing_first_name = $billing_first_name;
                    $customer->billing_last_name = $billing_last_name;
                    $customer->set_country($billing_country);
                    $customer->set_address($billing_address_1);
                    $customer->set_address_2($billing_address_2);
                    $customer->set_city($billing_city);
                    $customer->set_state($billing_state);
                    $customer->set_postcode($billing_postcode);
                    $customer->billing_phone = $billing_phone;
                    $customer->billing_email = $billing_email;
                } else {
                    $customer->set_shipping_first_name($shipping_first_name);
                    $customer->set_shipping_last_name($shipping_last_name);
                    $customer->set_billing_first_name($billing_first_name);
                    $customer->set_billing_last_name($billing_last_name);
                    $customer->set_billing_country($billing_country);
                    $customer->set_billing_address_1($billing_address_1);
                    $customer->set_billing_address_2($billing_address_2);
                    $customer->set_billing_city($billing_city);
                    $customer->set_billing_state($billing_state);
                    $customer->set_billing_postcode($billing_postcode);
                    $customer->set_billing_phone($billing_phone);
                    $customer->set_billing_email($billing_email);
                }
            } catch (Exception $ex) {
                
            }
        }

        public function maybe_clear_session_data() {
            try {
                if (has_active_session()) {
                    unset(WC()->session->psb_session);
                }
            } catch (Exception $ex) {
                
            }
        }

        public function psb_add_class_order_review_page($classes) {
            try {
                if (!class_exists('WooCommerce') || WC()->session == null) {
                    return $classes;
                }
                if (has_active_session()) {
                    $classes[] = 'psb-order-review';
                }
            } catch (Exception $ex) {
                return $classes;
            }
            return $classes;
        }

        public function psb_woocommerce_coupons_enabled($bool) {
            if ($bool) {
                return $this->order_review_page_enable_coupons;
            }
            return $bool;
        }

        public function psb_order_review_page_description() {
            if ($this->order_review_page_description && has_active_session()) {
                ?>
                <div class="order_review_page_description">
                    <p>
                        <?php
                        echo wp_kses_post($this->order_review_page_description);
                        ?>
                    </p>
                </div>
                <?php
            }
        }

        public function psb_capture_payment($order_id) {
            $order = wc_get_order($order_id);
            if (!$order) {
                return;
            }
            $old_wc = version_compare(WC_VERSION, '3.0', '<');
            $payment_method = $old_wc ? $order->payment_method : $order->get_payment_method();
            $payment_action = psb_get_post_meta($order, '_payment_action');
            $auth_transaction_id = psb_get_post_meta($order, '_auth_transaction_id');
            if ('paypal_smart_checkout' === $payment_method && $payment_action === 'authorize' && !empty($auth_transaction_id)) {
                $trans_details = $this->request->psb_show_details_authorized_payment($auth_transaction_id);
                if ($this->psb_is_authorized_only($trans_details)) {
                    $this->request->psb_capture_authorized_payment($order_id);
                }
            }
        }

        public function psb_cancel_authorization($order_id) {
            $order = wc_get_order($order_id);
            if (!$order) {
                return;
            }
            $old_wc = version_compare(WC_VERSION, '3.0', '<');
            $payment_method = $old_wc ? $order->payment_method : $order->get_payment_method();
            $transaction_id = $order->get_transaction_id();
            $payment_action = psb_get_post_meta($order, '_payment_action');
            if ('paypal_smart_checkout' === $payment_method && $transaction_id && $payment_action === 'authorize') {
                // $trans_details = $this->request->psb_show_details_authorized_payment($transaction_id);
                //if ($this->psb_is_authorized_only($trans_details)) {
                //  $this->request->psb_capture_authorized_payment($order_id);
                //}
            }
        }

        public function psb_add_capture_charge_order_action($actions) {
            if (!isset($_REQUEST['post'])) {
                return $actions;
            }
            $order = wc_get_order($_REQUEST['post']);
            if (empty($order)) {
                return $actions;
            }
            $old_wc = version_compare(WC_VERSION, '3.0', '<');
            $payment_method = $old_wc ? $order->payment_method : $order->get_payment_method();
            $paypal_status = psb_get_post_meta($order, '_payment_status');
            $payment_action = psb_get_post_meta($order, '_payment_action');
            if ('paypal_smart_checkout' !== $payment_method) {
                return $actions;
            }
            if (!is_array($actions)) {
                $actions = array();
            }
            if ('CREATED' !== $paypal_status && $payment_action === 'authorize') {
                $actions['psb_capture_charge'] = esc_html__('Capture Charge', 'smart-paypal-checkout-for-woocommerce');
            }
            return $actions;
        }

        public function psb_maybe_capture_charge($order) {
            if (!is_object($order)) {
                $order = wc_get_order($order);
            }
            $order_id = version_compare(WC_VERSION, '3.0', '<') ? $order->id : $order->get_id();
            $this->psb_capture_payment($order_id);
            return true;
        }

        public function psb_is_authorized_only($trans_details = array()) {
            if (!is_wp_error($trans_details) && !empty($trans_details)) {
                $payment_status = '';
                if (isset($trans_details->status) && !empty($trans_details->status)) {
                    $payment_status = $trans_details->status;
                }
                if ('CREATED' === $payment_status) {
                    return true;
                }
            }
            return false;
        }

        public function psb_clean_url($url) {
            if (strpos($url, 'https://www.paypal.com/sdk/js') !== false) {
                $url = "{$url}' data-partner-attribution-id='MBJTechnolabs_SI_SPB";
                if (is_checkout() && $this->advanced_card_payments) {
                    $url = "{$url}' data-client-token='{$this->client_token}";
                }
            }
            return $url;
        }

        public function psb_set_address_to_checkout_field() {
            
        }

        public function validate_checkout($country, $state, $sec) {
            $state_value = '';
            $valid_states = WC()->countries->get_states(isset($country) ? $country : ( 'billing' === $sec ? WC()->customer->get_country() : WC()->customer->get_shipping_country() ));
            if (!empty($valid_states) && is_array($valid_states)) {
                $valid_state_values = array_flip(array_map('strtolower', $valid_states));
                if (isset($valid_state_values[strtolower($state)])) {
                    $state_value = $valid_state_values[strtolower($state)];
                    return $state_value;
                }
            } else {
                return $state;
            }
            if (!empty($valid_states) && is_array($valid_states) && sizeof($valid_states) > 0) {
                if (!in_array($state, array_keys($valid_states))) {
                    return false;
                } else {
                    return $state;
                }
            }

            return $state_value;
        }

        public function psb_update_checkout_field_details() {
            if (empty($this->checkout_details)) {
                $this->checkout_details = psb_get_session('psb_paypal_transaction_details', false);
                if (empty($this->checkout_details)) {
                    if (!empty($_GET['paypal_order_id'])) {
                        $this->checkout_details = $this->request->psb_get_checkout_details($_GET['paypal_order_id']);
                    }
                }
                if (empty($this->checkout_details)) {
                    return;
                }
                psb_set_session('psb_paypal_transaction_details', $this->checkout_details);
            }
            $states_list = WC()->countries->get_states();
            if (!empty($this->checkout_details)) {
                $shipping_address = $this->get_mapped_shipping_address();
                if (!empty($shipping_address)) {
                    foreach ($shipping_address as $field => $value) {
                        if (!empty($value)) {
                            if ('state' == $field) {
                                if ($this->validate_checkout($shipping_address['country'], $value, 'shipping')) {
                                    $_POST['shipping_' . $field] = $this->validate_checkout($shipping_address['country'], $value, 'shipping');
                                } else {
                                    if (isset($shipping_address['country']) && isset($states_list[$shipping_address['country']])) {
                                        $state_key = array_search($value, $states_list[$shipping_address['country']]);
                                        $_POST['shipping_' . $field] = $state_key;
                                    } else {
                                        $_POST['shipping_' . $field] = '';
                                    }
                                }
                            } else {
                                $_POST['shipping_' . $field] = wc_clean(stripslashes($value));
                            }
                        }
                    }
                }
                $billing_address = $this->get_mapped_billing_address($this->checkout_details);
                if (!empty($billing_address)) {
                    foreach ($billing_address as $field => $value) {
                        if (!empty($value)) {
                            if ('state' == $field) {
                                if ($this->validate_checkout($shipping_address['country'], $value, 'shipping')) {
                                    $_POST['billing_' . $field] = $this->validate_checkout($shipping_address['country'], $value, 'shipping');
                                } else {
                                    if (isset($shipping_address['country']) && isset($states_list[$shipping_address['country']])) {
                                        $state_key = array_search($value, $states_list[$shipping_address['country']]);
                                        $_POST['billing_' . $field] = $state_key;
                                    } else {
                                        $_POST['billing_' . $field] = '';
                                    }
                                }
                            } else {
                                $_POST['billing_' . $field] = wc_clean(stripslashes($value));
                            }
                        }
                    }
                }
            }
        }

        public function psb_cancel_button() {
            if (has_active_session()) {
                $order_button_text = __('Cancel order', 'smart-paypal-checkout-for-woocommerce');
                $cancel_order_url = add_query_arg(array('psb_action' => 'cancel_order', 'utm_nooverride' => '1', 'from' => 'checkout'), WC()->api_request_url('Paypal_Checkout_For_Woocommerce_Button_Manager'));
                echo apply_filters('psb_review_order_cance_button_html', '<a class="button alt psb_cancel" name="woocommerce_checkout_cancel_order" href="' . esc_attr($cancel_order_url) . '" >' . $order_button_text . '</a>');
            }
        }

        public function psb_create_webhooks() {
            if (psb_is_local_server() === false) {
                $webhook_id = get_option($this->webhook_id, '');
                if (empty($webhook_id)) {
                    $this->request->psb_create_webhooks_request();
                } else {
                    // check every 6 hours and validate webhook exist or not, if it's not exist we need to create new one.
                }
            }
        }

        public function psb_handle_webhook_request() {
            $this->request->psb_handle_webhook_request_handler();
        }

        public function psb_cc_capture() {
            try {

                $psb_paypal_order_id = psb_get_session('psb_paypal_order_id');
                if (!empty($psb_paypal_order_id)) {
                    include_once SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_DIR . '/includes/class-smart-paypal-checkout-for-woocommerce-request.php';
                    $this->request = new Paypal_Checkout_For_Woocommerce_Request();
                    $api_response = $this->request->psb_get_checkout_details($psb_paypal_order_id);
                    $order_id = absint(WC()->session->get('order_awaiting_payment'));
                    if (empty($order_id)) {
                        $order_id = psb_get_session('psb_woo_order_id');
                    }
                    $order = wc_get_order($order_id);
                    if ($this->psb_liability_shift($order, $api_response)) {
                        if ($this->paymentaction === 'capture') {
                            $is_success = $this->request->psb_order_capture_request($order_id, false);
                        } else {
                            $is_success = $this->request->psb_order_auth_request($order_id);
                        }
                        psb_update_post_meta($order, '_payment_action', $this->paymentaction);
                        psb_update_post_meta($order, 'enviorment', ($this->testmode) ? 'sandbox' : 'live');
                        WC()->cart->empty_cart();
                    } else {
                        $is_success = false;
                        wc_add_notice(__('We cannot process your order with the payment information that you provided. Please use an alternate payment method.', 'smart-paypal-checkout-for-woocommerce'), 'error');
                    }
                    if ($is_success) {
                        unset(WC()->session->psb_session);
                        if (ob_get_length())
                            ob_end_clean();
                        wp_send_json_success(array(
                            'result' => 'success',
                            'redirect' => apply_filters('woocommerce_get_return_url', $order->get_checkout_order_received_url(), $order),
                        ));
                        exit();
                    } else {
                        unset(WC()->session->psb_session);
                        if (ob_get_length())
                            ob_end_clean();
                        wp_send_json_success(array(
                            'result' => 'failure',
                            'redirect' => wc_get_checkout_url()
                        ));
                        exit();
                    }
                }
            } catch (Exception $ex) {
                
            }
        }

        public function psb_liability_shift($order, $response_object) {
            if ($this->threed_secure_enabled === false) {
                return true;
            }
            if (!empty($response_object)) {
                $response = json_decode(json_encode($response_object), true);
                if (!empty($response['payment_source']['card']['authentication_result']['liability_shift'])) {
                    $LiabilityShift = $response['payment_source']['card']['authentication_result']['liability_shift'];
                    $EnrollmentStatus = isset($response['payment_source']['card']['authentication_result']['three_d_secure']['enrollment_status']) ? $response['payment_source']['card']['authentication_result']['three_d_secure']['enrollment_status'] : '';
                    $AuthenticationResult = isset($response['payment_source']['card']['authentication_result']['three_d_secure']['authentication_status']) ? $response['payment_source']['card']['authentication_result']['three_d_secure']['authentication_status'] : '';
                    $liability_shift_order_note = __('3D Secure response', 'smart-paypal-checkout-for-woocommerce');
                    $liability_shift_order_note .= "\n";
                    $liability_shift_order_note .= 'Liability Shift : ' . psb_readable($LiabilityShift);
                    $liability_shift_order_note .= "\n";
                    $liability_shift_order_note .= 'Enrollment Status : ' . $EnrollmentStatus;
                    $liability_shift_order_note .= "\n";
                    $liability_shift_order_note .= 'Authentication Status : ' . $AuthenticationResult;
                    if ($order) {
                        $order->add_order_note($liability_shift_order_note);
                    }
                    if ($EnrollmentStatus === 'Y' && $AuthenticationResult === 'Y' && $LiabilityShift === 'POSSIBLE') {
                        return true;
                    } elseif ($EnrollmentStatus === 'Y' && $AuthenticationResult === 'A' && $LiabilityShift === 'POSSIBLE') {
                        return true;
                    } elseif ($EnrollmentStatus === 'N' && $LiabilityShift === 'No') {
                        return true;
                    } elseif ($EnrollmentStatus === 'U' && $LiabilityShift === 'No') {
                        return true;
                    } elseif ($EnrollmentStatus === 'B' && $LiabilityShift === 'No') {
                        return true;
                    }
                }
            }
            return false;
        }

    }
    