<?php

/**
 * @since      1.0.0
 * @package    Paypal_Checkout_For_Woocommerce_Gateway
 * @subpackage Paypal_Checkout_For_Woocommerce_Gateway/includes
 * @author     PayPal <paypal@mbjtechnolabs.com>
 */
class Paypal_Checkout_For_Woocommerce_Gateway extends WC_Payment_Gateway_CC {

    /**
     * @since    1.0.0
     */
    public $request;
    public $settings;
    public $plugin_name;
    public static $log = false;

    public function __construct() {
        $this->setup_properties();
        $this->init_form_fields();
        $this->init_settings();
        $this->get_properties();
        $this->plugin_name = 'smart-paypal-checkout-for-woocommerce';
        $this->title = __('PayPal Checkout', 'smart-paypal-checkout-for-woocommerce');
        $this->description = __('Accept PayPal, PayPal Credit and alternative payment types.', 'smart-paypal-checkout-for-woocommerce');
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('woocommerce_admin_order_totals_after_total', array($this, 'psb_display_order_fee'));
        $this->icon = 'https://www.paypalobjects.com/webstatic/mktg/Logo/pp-logo-100px.png';
        if (has_active_session()) {
            $this->order_button_text = $this->get_option('order_review_page_button_text', 'Confirm your PayPal order');
        }
    }

    public function setup_properties() {
        $this->id = 'paypal_smart_checkout';
        $this->method_title = __('PayPal Checkout', 'smart-paypal-checkout-for-woocommerce');
        $more_detais = '<b>An instant global business.</b><br><br>One integration for all your online payment needs.<br><b><br>Benefits</b>';
        $more_detais .= '<ul>'
                . '<li class="list">Enable a seamless buying experience for your customers that drives conversion and loyalty.</li>'
                . '<li class="list">Accept PayPal payments with simplified onboarding, adaptable integration and easy account setup.</li>'
                . '<li class="list">Access to 377M+ PayPal customers around the globe*, with local currency support for better money management</li>'
                . '<li class="list">Peace of mind for you and your customers with buyer and seller protection on eligible sales.</li>'
                . '</ul><br>'
                . '*PayPal Fourth Quarters 2020 Result<br><br><br>';
        if (!isset($_GET['section'])) {
            $more_detais = '';
        }
        $this->method_description = $more_detais . __('PayPal Checkout with Smart Payment Buttons gives your buyers a simplified and secure checkout experience.', 'smart-paypal-checkout-for-woocommerce');
        $this->has_fields = true;
    }

    public function get_properties() {
        $this->enabled = $this->get_option('enabled', 'no');
        $this->supports = array(
            'products',
            'refunds',
            'pay_button'
        );

        $this->pay_button_id = 'psb_cart';
        $this->sandbox = 'yes' === $this->get_option('testmode', 'no');
        $this->sandbox_client_id = $this->get_option('sandbox_client_id', '');
        $this->sandbox_secret_id = $this->get_option('sandbox_api_secret', '');
        $this->live_client_id = $this->get_option('api_client_id', '');
        $this->live_secret_id = $this->get_option('api_secret', '');
        if (!empty($this->sandbox_client_id) && !empty($this->sandbox_secret_id)) {
            $this->is_sandbox_seller_onboarding_done = 'yes';
        } else {
            $this->is_sandbox_seller_onboarding_done = 'no';
        }
        if (!empty($this->live_client_id) && !empty($this->live_secret_id)) {
            $this->is_live_seller_onboarding_done = 'yes';
        } else {
            $this->is_live_seller_onboarding_done = 'no';
        }
        if ($this->sandbox) {
            $this->client_id = $this->sandbox_client_id;
            $this->secret_id = $this->sandbox_secret_id;
        } else {
            $this->client_id = $this->live_client_id;
            $this->secret_id = $this->live_secret_id;
        }
        if (!$this->is_valid_for_use() || !$this->is_credentials_set()) {
            $this->enabled = 'no';
        }
        $this->paymentaction = $this->get_option('paymentaction', 'capture');
        if ($this->paymentaction === 'authorize' && get_woocommerce_currency() === 'INR') {
            $this->paymentaction = 'capture';
        }
        $this->advanced_card_payments = 'yes' === $this->get_option('enable_advanced_card_payments', 'no');
        if (psb_is_advanced_cards_available() === false) {
            $this->advanced_card_payments = false;
        }
        if ($this->advanced_card_payments) {
            $this->threed_secure_enabled = 'yes' === $this->get_option('threed_secure_enabled', 'no');
        } else {
            $this->threed_secure_enabled = false;
        }
    }

    public function payment_fields() {
        $description = $this->get_description();
        if ($description) {
            echo wpautop(wptexturize($description));
        }
        do_action('display_paypal_button_checkout_page');
        if ($this->advanced_card_payments) {
            parent::payment_fields();
            if ($this->threed_secure_enabled) {
                echo '<div id="payments-sdk__contingency-lightbox"></div>';
            }
        }
    }

    public function form() {
        wp_enqueue_script('wc-credit-card-form');
        $fields = array();
        $cvc_field = '<div class="form-row form-row-last">
                        <label for="' . esc_attr($this->id) . '-card-cvc">' . apply_filters('cc_form_label_card_code', __('Card code', 'smart-paypal-checkout-for-woocommerce'), $this->id) . ' </label>
                        <div id="' . esc_attr($this->id) . '-card-cvc" class="input-text wc-credit-card-form-card-cvc hosted-field-braintree"></div>
                    </div>';
        $default_fields = array(
            'card-number-field' => '<div class="form-row form-row-wide">
                        <label for="' . esc_attr($this->id) . '-card-number">' . apply_filters('cc_form_label_card_number', __('Card number', 'smart-paypal-checkout-for-woocommerce'), $this->id) . '</label>
                        <div id="' . esc_attr($this->id) . '-card-number"  class="input-text wc-credit-card-form-card-number hosted-field-braintree"></div>
                    </div>',
            'card-expiry-field' => '<div class="form-row form-row-first">
                        <label for="' . esc_attr($this->id) . '-card-expiry">' . apply_filters('cc_form_label_expiry', __('Expiry (MM/YY)', 'smart-paypal-checkout-for-woocommerce'), $this->id) . ' </label>
                        <div id="' . esc_attr($this->id) . '-card-expiry" class="input-text wc-credit-card-form-card-expiry hosted-field-braintree"></div>
                    </div>',
        );
        if (!$this->supports('credit_card_form_cvc_on_saved_method')) {
            $default_fields['card-cvc-field'] = $cvc_field;
        }
        $fields = wp_parse_args($fields, apply_filters('woocommerce_credit_card_form_fields', $default_fields, $this->id));
        ?>
        <fieldset id="wc-<?php echo esc_attr($this->id); ?>-cc-form" class='wc-credit-card-form wc-payment-form'>
            <?php do_action('woocommerce_credit_card_form_start', $this->id); ?>
            <?php
            foreach ($fields as $field) {
                echo $field;
            }
            ?>
            <?php do_action('woocommerce_credit_card_form_end', $this->id); ?>
            <div class="clear"></div>
        </fieldset>
        <?php
        if ($this->supports('credit_card_form_cvc_on_saved_method')) {
            echo '<fieldset>' . $cvc_field . '</fieldset>';
        }
    }

    public function is_valid_for_use() {
        return in_array(
                get_woocommerce_currency(), apply_filters(
                        'woocommerce_paypal_supported_currencies', array('AUD', 'BRL', 'CAD', 'MXN', 'NZD', 'HKD', 'SGD', 'USD', 'EUR', 'JPY', 'TRY', 'NOK', 'CZK', 'DKK', 'HUF', 'ILS', 'MYR', 'PHP', 'PLN', 'SEK', 'CHF', 'TWD', 'THB', 'GBP', 'RMB', 'RUB', 'INR')
                ), true
        );
    }

    public function is_credentials_set() {
        if (!empty($this->client_id) && !empty($this->secret_id)) {
            return true;
        } else {
            return false;
        }
    }

    public function init_form_fields() {
        if (!class_exists('Paypal_Checkout_For_Woocommerce_Settings')) {
            include 'class-smart-paypal-checkout-for-woocommerce-settings.php';
        }
        $this->settings = Paypal_Checkout_For_Woocommerce_Settings::instance();

        $this->form_fields = $this->settings->psb_setting_fields();
    }

    public function process_admin_options() {

        delete_transient('psb_sandbox_access_token');
        delete_transient('psb_live_access_token');
        delete_transient('psb_sandbox_client_token');
        delete_transient('psb_live_client_token');
        delete_option('psb_snadbox_webhook_id');
        delete_option('psb_live_webhook_id');
        delete_option('psb_sandbox_seller_onboarding_process_done');
        delete_option('psb_live_seller_onboarding_process_done');
        parent::process_admin_options();
        if ($this->is_valid_for_use()) {
            if ('yes' !== $this->get_option('debug', 'no')) {
                if (empty(self::$log)) {
                    self::$log = wc_get_logger();
                }
                self::$log->clear('paypal');
            }
        } else {
            ?>
            <div class="inline error">
                <p>
                    <strong><?php esc_html_e('Gateway disabled', 'smart-paypal-checkout-for-woocommerce'); ?></strong>: <?php esc_html_e('PayPal does not support your store currency.', 'smart-paypal-checkout-for-woocommerce'); ?>
                </p>
            </div>
            <?php
        }
    }

    public function admin_options() {
        $this->psb_admin_notice();
        wp_enqueue_script('wc-clipboard');
        parent::admin_options();
    }

    public function process_payment($woo_order_id) {
        $is_success = false;
        if (isset($_GET['from']) && 'checkout' === $_GET['from']) {
            psb_set_session('psb_woo_order_id', $woo_order_id);
            include_once SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_DIR . '/includes/class-smart-paypal-checkout-for-woocommerce-request.php';
            $this->request = new Paypal_Checkout_For_Woocommerce_Request($this);
            $this->request->psb_create_order_request($woo_order_id);
            exit();
        } else {
            $psb_paypal_order_id = psb_get_session('psb_paypal_order_id');
            if (!empty($psb_paypal_order_id)) {
                include_once SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_DIR . '/includes/class-smart-paypal-checkout-for-woocommerce-request.php';
                $this->request = new Paypal_Checkout_For_Woocommerce_Request();
                $order = wc_get_order($woo_order_id);
                if ($this->paymentaction === 'capture') {
                    $is_success = $this->request->psb_order_capture_request($woo_order_id);
                } else {
                    $is_success = $this->request->psb_order_auth_request($woo_order_id);
                }
                psb_update_post_meta($order, '_payment_action', $this->paymentaction);
                psb_update_post_meta($order, 'enviorment', ($this->sandbox) ? 'sandbox' : 'live');
                WC()->cart->empty_cart();
                if ($is_success) {
                    unset(WC()->session->psb_session);
                    return array(
                        'result' => 'success',
                        'redirect' => $this->get_return_url($order),
                    );
                } else {
                    unset(WC()->session->psb_session);
                    return array(
                        'result' => 'failure',
                        'redirect' => wc_get_cart_url()
                    );
                }
            }
        }
    }

    public function get_transaction_url($order) {
        $enviorment = psb_get_post_meta($order, 'enviorment', true);
        if ($enviorment === 'sandbox') {
            $this->view_transaction_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_view-a-trans&id=%s';
        } else {
            $this->view_transaction_url = 'https://www.paypal.com/cgi-bin/webscr?cmd=_view-a-trans&id=%s';
        }
        return parent::get_transaction_url($order);
    }

    public function can_refund_order($order) {
        $has_api_creds = false;
        if (!empty($this->client_id) && !empty($this->secret_id)) {
            $has_api_creds = true;
        }
        return $order && $order->get_transaction_id() && $has_api_creds;
    }

    public function process_refund($order_id, $amount = null, $reason = '') {
        $order = wc_get_order($order_id);
        if (!$this->can_refund_order($order)) {
            return new WP_Error('error', __('Refund failed.', 'smart-paypal-checkout-for-woocommerce'));
        }
        include_once SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_DIR . '/includes/class-smart-paypal-checkout-for-woocommerce-request.php';
        $this->request = new Paypal_Checkout_For_Woocommerce_Request();
        $transaction_id = $order->get_transaction_id();
        $bool = $this->request->psb_refund_order($order_id, $amount, $reason, $transaction_id);
        return $bool;
    }

    public function psb_display_order_fee($order_id) {
        $order = wc_get_order($order_id);
        $fee = psb_get_post_meta($order, '_paypal_fee', true);
        $currency = psb_get_post_meta($order, '_paypal_fee_currency_code', true);
        if ($order->get_status() == 'refunded') {
            return true;
        }
        ?>
        <tr>
            <td class="label stripe-fee">
                <?php echo wc_help_tip(__('This represents the fee PayPal collects for the transaction.', 'smart-paypal-checkout-for-woocommerce')); ?>
                <?php esc_html_e('PayPal Fee:', 'smart-paypal-checkout-for-woocommerce'); ?>
            </td>
            <td width="1%"></td>
            <td class="total">
                -&nbsp;<?php echo wc_price($fee, array('currency' => $currency)); ?>
            </td>
        </tr>
        <?php
    }

    public function get_icon() {
        $icon = $this->icon ? '<img src="' . WC_HTTPS::force_https_url($this->icon) . '" alt="' . esc_attr($this->get_title()) . '" />' : '';
        return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
    }

    public function generate_psb_paypal_checkout_text_html($field_key, $data) {
        if (isset($data['type']) && $data['type'] === 'psb_paypal_checkout_text') {
            $field_key = $this->get_field_key($field_key);
            ob_start();
            ?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="<?php echo esc_attr($field_key); ?>"><?php echo wp_kses_post($data['title']); ?> <?php echo $this->get_tooltip_html($data); // WPCS: XSS ok.                                                                            ?></label>
                </th>
                <td class="forminp" id="<?php echo esc_attr($field_key); ?>">
                    <button type="button" class="button psb-disconnect"><?php echo __('Disconnect', ''); ?></button>
                    <p class="description"><?php echo wp_kses_post($data['description']); ?></p>
                </td>
            </tr>
            <?php
            return ob_get_clean();
        }
    }

    public function generate_psb_paypal_checkout_onboarding_html($field_key, $data) {
        if (isset($data['type']) && $data['type'] === 'psb_paypal_checkout_onboarding') {
            $field_key = $this->get_field_key($field_key);
            $testmode = ( $data['mode'] === 'live' ) ? 'no' : 'yes';
            $args = array(
                'displayMode' => 'minibrowser',
            );
            $id = ($testmode === 'no') ? 'connect-to-production' : 'connect-to-sandbox';
            $label = ($testmode === 'no') ? __('Connect to PayPal', 'paypal-for-woocommerce') : __('Connect to PayPal Sandbox', 'paypal-for-woocommerce');
            ob_start();
            ?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="<?php echo esc_attr($field_key); ?>"><?php echo wp_kses_post($data['title']); ?> <?php echo $this->get_tooltip_html($data); // WPCS: XSS ok.                                                                            ?></label>
                </th>
                <td class="forminp" id="<?php echo esc_attr($field_key); ?>">
                    <?php
                    if ($this->is_live_seller_onboarding_done === 'no' && $testmode === 'no' || $this->is_sandbox_seller_onboarding_done === 'no' && $testmode === 'yes') {
                        $signup_link = $this->psb_get_signup_link($testmode);
                        if ($signup_link) {
                            $url = add_query_arg($args, $signup_link);
                            $this->psb_display_paypal_signup_button($url, $id, $label);
                            $script_url = 'https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js';
                            ?>
                            <script type="text/javascript">
                                document.querySelectorAll('[data-paypal-onboard-complete=onboardingCallback]').forEach((element) => {
                                    element.addEventListener('click', (e) => {
                                        if ('undefined' === typeof PAYPAL) {
                                            e.preventDefault();
                                            alert('PayPal');
                                        }
                                    });
                                });</script>
                            <script id="paypal-js" src="<?php echo esc_url($script_url); ?>"></script> <?php
                        } else {
                            echo __('We could not properly connect to PayPal', '');
                            ?>
                            <a href="#" class="psb_paypal_checkout_gateway_manual_credential_input"><?php echo __('Toggle to manual credential input', ''); ?></a>
                            <?php
                        }
                    }
                    ?>
                </td>
            </tr>
            <?php
            return ob_get_clean();
        }
    }

    public function psb_display_paypal_signup_button($url, $id, $label) {
        ?><a target="_blank" class="button-primary" id="<?php echo esc_attr($id); ?>" data-paypal-onboard-complete="onboardingCallback" href="<?php echo esc_url($url); ?>" data-paypal-button="true"><?php echo esc_html($label); ?></a>
        <span class="psb_paypal_checkout_gateway_setting_sepraer"><?php echo __('OR', ''); ?></span>
        <a href="#" class="psb_paypal_checkout_gateway_manual_credential_input"><?php echo __('Toggle to manual credential input', ''); ?></a>
        <?php
    }

    public function generate_copy_text_html($key, $data) {
        $field_key = $this->get_field_key($key);
        $defaults = array(
            'title' => '',
            'disabled' => false,
            'class' => '',
            'css' => '',
            'placeholder' => '',
            'type' => 'text',
            'desc_tip' => false,
            'description' => '',
            'custom_attributes' => array(),
        );

        $data = wp_parse_args($data, $defaults);

        ob_start();
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="<?php echo esc_attr($field_key); ?>"><?php echo wp_kses_post($data['title']); ?> <?php echo $this->get_tooltip_html($data); // WPCS: XSS ok.                              ?></label>
            </th>
            <td class="forminp">
                <fieldset>
                    <legend class="screen-reader-text"><span><?php echo wp_kses_post($data['title']); ?></span></legend>
                    <input class="input-text regular-input <?php echo esc_attr($data['class']); ?>" type="text" name="<?php echo esc_attr($field_key); ?>" id="<?php echo esc_attr($field_key); ?>" style="<?php echo esc_attr($data['css']); ?>" value="<?php echo esc_attr($this->get_option($key)); ?>" placeholder="<?php echo esc_attr($data['placeholder']); ?>" <?php disabled($data['disabled'], true); ?> <?php echo $this->get_custom_attribute_html($data); // WPCS: XSS ok.                              ?> />
                    <button type="button" class="button-secondary <?php echo esc_attr($data['button_class']); ?>" data-tip="Copied!">Copy</button>
                    <?php echo $this->get_description_html($data); // WPCS: XSS ok.     ?>
                </fieldset>
            </td>
        </tr>
        <?php
        return ob_get_clean();
    }

    public function psb_get_signup_link($testmode = 'yes') {
        try {
            include_once ( SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_DIR . '/includes/class-smart-paypal-checkout-for-woocommerce-seller-onboarding.php');
            $this->seller_onboarding = Paypal_Checkout_For_Woocommerce_Seller_Onboarding::instance();
            $seller_onboarding_result = $this->seller_onboarding->psb_generate_signup_link($testmode);
            if (isset($seller_onboarding_result['result']) && 'success' === $seller_onboarding_result['result'] && !empty($seller_onboarding_result['body'])) {
                $json = json_decode($seller_onboarding_result['body']);
                if (isset($json->links)) {
                    foreach ($json->links as $link) {
                        if ('action_url' === $link->rel) {
                            return (string) $link->href;
                        }
                    }
                } else {
                    return false;
                }
            }
        } catch (Exception $ex) {
            
        }
    }

    public function admin_scripts() {
        if (isset($_GET['section']) && 'paypal_smart_checkout' === $_GET['section']) {
            wp_enqueue_style($this->plugin_name . '-admin', SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_ASSET_URL . 'admin/css/smart-paypal-checkout-for-woocommerce-admin.css', array(), SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_VERSION, 'all');
            wp_enqueue_script($this->plugin_name . '-admin', SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_ASSET_URL . 'admin/js/smart-paypal-checkout-for-woocommerce-admin.js', array('jquery'), time(), false);
            wp_localize_script($this->plugin_name . '-admin', 'psb_param', array(
                'woocommerce_currency' => get_woocommerce_currency(),
                'is_advanced_cards_available' => psb_is_advanced_cards_available() ? 'yes' : 'no',
                'psb_is_local_server' => ( psb_is_local_server() == true) ? 'yes' : 'no',
                'psb_onboarding_endpoint' => WC_AJAX::get_endpoint('psb_login_seller'),
                'psb_onboarding_endpoint_nonce' => wp_create_nonce('psb_login_seller'),
                'is_sandbox_seller_onboarding_done' => $this->is_sandbox_seller_onboarding_done,
                'is_live_seller_onboarding_done' => $this->is_live_seller_onboarding_done,
            ));
        }
    }

    public function psb_admin_notice() {
        $is_saller_onboarding_done = false;
        $is_saller_onboarding_failed = false;
        if (false !== get_transient('psb_primary_email_not_confirmed')) {
            echo '<div class="notice notice-error is-dismissible"><p>'
            . __('Please verify the PayPal account to receive the payments.', 'smart-paypal-checkout-for-woocommerce')
            . '</p></div>';
        }
        if (false !== get_transient('psb_sandbox_seller_onboarding_process_done')) {
            $is_saller_onboarding_done = true;
            delete_transient('psb_sandbox_seller_onboarding_process_done');
        } elseif (false !== get_transient('psb_live_seller_onboarding_process_done')) {
            $is_saller_onboarding_done = true;
            delete_transient('psb_live_seller_onboarding_process_done');
        }
        if ($is_saller_onboarding_done) {
            echo '<div class="notice notice-success is-dismissible"><p>'
            . __('PayPal onboarding process successfully completed.', 'smart-paypal-checkout-for-woocommerce')
            . '</p></div>';
        } else {
            if (false !== get_transient('psb_sandbox_seller_onboarding_process_failed')) {
                $is_saller_onboarding_failed = true;
                delete_transient('psb_sandbox_seller_onboarding_process_failed');
            } elseif (false !== get_transient('psb_live_seller_onboarding_process_failed')) {
                $is_saller_onboarding_failed = true;
                delete_transient('psb_live_seller_onboarding_process_failed');
            }
            if ($is_saller_onboarding_failed) {
                echo '<div class="notice notice-error is-dismissible">'
                . '<p>We could not properly connect to PayPal. Please reload the page to continue.</p>'
                . '</div>';
            }
        }
        if ($this->is_live_seller_onboarding_done === 'yes' || $this->is_sandbox_seller_onboarding_done === 'yes') {
            return false;
        }

        $message = sprintf(
                __(
                        'PayPal Complete Payments is almost ready. To get started, <a href="%1$s">connect your account</a>.', 'paypal-for-woocommerce'
                ), admin_url('admin.php?page=wc-settings&tab=checkout&section=paypal_smart_checkout#woocommerce_paypal_smart_checkout_api_details')
        );
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><?php echo $message; ?></p>
        </div>
        <?php
    }

}
