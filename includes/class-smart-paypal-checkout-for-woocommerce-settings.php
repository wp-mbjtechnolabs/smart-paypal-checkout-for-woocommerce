<?php

defined('ABSPATH') || exit;


defined('ABSPATH') || exit;

if (!class_exists('Paypal_Checkout_For_Woocommerce_Settings')) {

    class Paypal_Checkout_For_Woocommerce_Settings {

        public $gateway_key;
        public $settings = array();
        protected static $_instance = null;

        public static function instance() {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        public function __construct() {
            $this->gateway_key = 'woocommerce_paypal_smart_checkout_settings';
        }

        public function get($id, $default = false) {
            if (!$this->has($id)) {
                return $default;
            }
            return $this->settings[$id];
        }

        public function get_load() {
            return get_option($this->gateway_key, array());
        }

        public function has($id) {
            $this->load();
            return array_key_exists($id, $this->settings);
        }

        public function set($id, $value) {
            $this->load();
            $this->settings[$id] = $value;
        }

        public function persist() {
            update_option($this->gateway_key, $this->settings);
        }

        public function load() {
            if ($this->settings) {
                return false;
            }
            $this->settings = get_option($this->gateway_key, array());
            
            $defaults = array(
                'title' => __('PayPal', 'smart-paypal-checkout-for-woocommerce'),
                'description' => __(
                        'Accept PayPal, PayPal Credit and alternative payment types.', 'smart-paypal-checkout-for-woocommerce'
                )
            );
            foreach ($defaults as $key => $value) {
                if (isset($this->settings[$key])) {
                    continue;
                }
                $this->settings[$key] = $value;
            }
            return true;
        }

        public function psb_setting_fields() {
            $payment_action_not_available = '';
            if (get_woocommerce_currency() === 'INR') {
                $payment_action_not_available = __('Authorize payment action is not available for INR currency.', 'smart-paypal-checkout-for-woocommerce');
            }
            $default_settings = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable PayPal Checkout', 'smart-paypal-checkout-for-woocommerce'),
                    'description' => __('Check this box to enable the payment gateway. Leave unchecked to disable it.', 'smart-paypal-checkout-for-woocommerce'),
                    'desc_tip' => true,
                    'default' => 'yes',
                ),
                'api_details' => array(
                    'title' => __('Account Settings', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'title',
                    'description' => '',
                    'class' => 'psb_separator_heading',
                ),
                'testmode' => array(
                    'title' => __('PayPal sandbox', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable PayPal sandbox', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'no',
                    'description' => __('Check this box to enable test mode so that all transactions will hit PayPal’s sandbox server instead of the live server. This should only be used during development as no real transactions will occur when this is enabled.', 'smart-paypal-checkout-for-woocommerce'),
                    'desc_tip' => true
                ),
                'live_onboarding' => array(
                    'title' => __('Connect to PayPal', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'psb_paypal_checkout_onboarding',
                    'gateway' => 'psb_paypal_checkout',
                    'mode' => 'live',
                    'description' => __('Setup or link an existing PayPal account.', 'smart-paypal-checkout-for-woocommerce'),
                    'desc_tip' => ''
                ),
                'live_disconnect' => array(
                    'title' => __('Disconnect from PayPal', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'psb_paypal_checkout_text',
                    'mode' => 'live',
                    'description' => __('Click to reset current credentials and use another account.', 'smart-paypal-checkout-for-woocommerce'),
                    'desc_tip' => '',
                ),
                'sandbox_onboarding' => array(
                    'title' => __('Connect to PayPal', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'psb_paypal_checkout_onboarding',
                    'gateway' => 'psb_paypal_checkout',
                    'mode' => 'sandbox',
                    'description' => __('Setup or link an existing PayPal account.', 'smart-paypal-checkout-for-woocommerce'),
                    'desc_tip' => ''
                ),
                'sandbox_disconnect' => array(
                    'title' => __('Disconnect from PayPal', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'psb_paypal_checkout_text',
                    'mode' => 'sandbox',
                    'description' => __('Click to reset current credentials and use another account.', 'smart-paypal-checkout-for-woocommerce'),
                    'desc_tip' => ''
                ),
                'api_client_id' => array(
                    'title' => __('PayPal Client ID', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'password',
                    'description' => __('Enter your PayPal Client ID.', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => '',
                    'desc_tip' => true
                ),
                'api_secret' => array(
                    'title' => __('PayPal Secret', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'password',
                    'description' => __('Enter your PayPal Secret.', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => '',
                    'desc_tip' => true
                ),
                'sandbox_client_id' => array(
                    'title' => __('Sandbox Client ID', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'password',
                    'description' => __('Enter your PayPal Sandbox Client ID.', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => '',
                    'desc_tip' => true
                ),
                'sandbox_api_secret' => array(
                    'title' => __('Sandbox Secret', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'password',
                    'description' => __('Enter your PayPal Sandbox Secret.', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => '',
                    'desc_tip' => true
                )
            );

            $button_manager_settings = array(
                'button_manager' => array(
                    'title' => __('Smart Payment Buttons Settings', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'title',
                    'description' => '',
                    'class' => 'psb_separator_heading',
                ),
                'style_color' => array(
                    'title' => __('Button Color', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select',
                    'description' => __('Set the color you would like to use for the PayPal button.', 'smart-paypal-checkout-for-woocommerce'),
                    'desc_tip' => true,
                    'default' => 'gold',
                    'options' => array(
                        'gold' => __('Gold (Recommended)', 'smart-paypal-checkout-for-woocommerce'),
                        'blue' => __('Blue', 'smart-paypal-checkout-for-woocommerce'),
                        'silver' => __('Silver', 'smart-paypal-checkout-for-woocommerce'),
                        'white' => __('White', 'smart-paypal-checkout-for-woocommerce'),
                        'black' => __('Black', 'smart-paypal-checkout-for-woocommerce')
                    ),
                ),
                'style_shape' => array(
                    'title' => __('Button Shape', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select',
                    'description' => __('Set the shape you would like to use for the buttons.', 'smart-paypal-checkout-for-woocommerce'),
                    'desc_tip' => true,
                    'default' => 'rect',
                    'options' => array(
                        'rect' => __('Rect (Recommended)', 'smart-paypal-checkout-for-woocommerce'),
                        'pill' => __('Pill', 'smart-paypal-checkout-for-woocommerce')
                    ),
                ),
                'style_label' => array(
                    'title' => __('Button Label', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select',
                    'description' => __('Set the label type you would like to use for the PayPal button.', 'smart-paypal-checkout-for-woocommerce'),
                    'desc_tip' => true,
                    'default' => 'paypal',
                    'options' => array(
                        'paypal' => __('PayPal (Recommended)', 'smart-paypal-checkout-for-woocommerce'),
                        'checkout' => __('Checkout', 'smart-paypal-checkout-for-woocommerce'),
                        'buynow' => __('Buynow', 'smart-paypal-checkout-for-woocommerce'),
                        'pay' => __('Pay', 'smart-paypal-checkout-for-woocommerce')
                    ),
                ),
                'disable_funding' => array(
                    'title' => __('Disable funding', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'multiselect',
                    'class' => 'wc-enhanced-select',
                    'description' => __('Funding methods selected here will be hidden from showing in the Smart Payment Buttons.', 'smart-paypal-checkout-for-woocommerce'),
                    'desc_tip' => true,
                    'options' => array(
                        'credit' => __('PayPal Credit', 'smart-paypal-checkout-for-woocommerce'),
                        'venmo' => __('Venmo', 'smart-paypal-checkout-for-woocommerce'),
                        'sepa' => __('SEPA-Lastschrift', 'smart-paypal-checkout-for-woocommerce'),
                        'bancontact' => __('Bancontact', 'smart-paypal-checkout-for-woocommerce'),
                        'eps' => __('eps', 'smart-paypal-checkout-for-woocommerce'),
                        'giropay' => __('giropay', 'smart-paypal-checkout-for-woocommerce'),
                        'ideal' => __('iDEAL', 'smart-paypal-checkout-for-woocommerce'),
                        'mybank' => __('MyBank', 'smart-paypal-checkout-for-woocommerce'),
                        'p24' => __('Przelewy24', 'smart-paypal-checkout-for-woocommerce'),
                        'sofort' => __('Sofort', 'smart-paypal-checkout-for-woocommerce'),
                    ),
                )
            );

            $order_review_page_settings = array(
                'order_review_page' => array(
                    'title' => __('Order Review Page options', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'title',
                    'description' => '',
                    'class' => 'psb_separator_heading',
                ),
                'order_review_page_title' => array(
                    'title' => __('Page Title', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'text',
                    'description' => __('Set the Page Title value you would like used on the PayPal Checkout order review page.', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => __('Confirm your PayPal order', 'smart-paypal-checkout-for-woocommerce'),
                    'desc_tip' => true,
                ),
                'order_review_page_description' => array(
                    'title' => __('Description', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'text',
                    'desc_tip' => true,
                    'description' => __('Set the Description you would like used on the PayPal Checkout order review page.', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => __("<strong>You're almost done!</strong><br>Review your information before you place your order.", 'smart-paypal-checkout-for-woocommerce'),
                ),
                'order_review_page_button_text' => array(
                    'title' => __('Button Text', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'text',
                    'description' => __('Set the Button Text you would like used on the PayPal Checkout order review page.', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => __('Confirm your PayPal order', 'smart-paypal-checkout-for-woocommerce'),
                    'desc_tip' => true,
                )
            );

            $pay_later_messaging_settings = array(
                'pay_later_messaging_settings' => array(
                    'title' => __('Pay Later Messaging Settings', 'smart-paypal-checkout-for-woocommerce'),
                    'class' => '',
                    'description' => '',
                    'type' => 'title',
                    'class' => 'ppcp_separator_heading',
                ),
                'enabled_pay_later_messaging' => array(
                    'title' => __('Enable/Disable', 'smart-paypal-checkout-for-woocommerce'),
                    'label' => __('Enable Pay Later Messaging', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'checkbox',
                    'description' => '<div style="font-size: smaller">Displays Pay Later messaging for available offers. Restrictions apply. <a target="_blank" href="https://developer.paypal.com/docs/business/pay-later/us/commerce-platforms/">See terms and learn more</a></div>',
                    'default' => 'no'
                ),
                'pay_later_messaging_page_type' => array(
                    'title' => __('Page Type', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'multiselect',
                    'css' => 'width: 100%;',
                    'class' => 'wc-enhanced-select pay_later_messaging_field',
                    'default' => array('home', 'category', 'product', 'cart', 'payment'),
                    'options' => array('home' => __('Home', 'smart-paypal-checkout-for-woocommerce'), 'category' => __('Category', 'smart-paypal-checkout-for-woocommerce'), 'product' => __('Product', 'smart-paypal-checkout-for-woocommerce'), 'cart' => __('Cart', 'smart-paypal-checkout-for-woocommerce'), 'payment' => __('Payment', 'smart-paypal-checkout-for-woocommerce')),
                    'description' => '<div style="font-size: smaller;">Set the page(s) you want to display messaging on, and then adjust that page\'s display option below.</div>',
                ),
                'pay_later_messaging_home_page_settings' => array(
                    'title' => __('Home Page', 'smart-paypal-checkout-for-woocommerce'),
                    'description' => __('Customize the appearance of <a target="_blank" href="https://www.paypal.com/us/business/buy-now-pay-later">Pay Later Messaging</a> on the Home page to promote special financing offers which help increase sales.', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'title',
                    'class' => 'pay_later_messaging_field pay_later_messaging_home_field',
                ),
                'pay_later_messaging_home_layout_type' => array(
                    'title' => __('Layout Type', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_home_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'flex',
                    'desc_tip' => true,
                    'options' => array('text' => __('Text Layout', 'smart-paypal-checkout-for-woocommerce'), 'flex' => __('Flex Layout', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_home_text_layout_logo_type' => array(
                    'title' => __('Logo Type', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_home_field pay_later_messaging_home_text_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'primary',
                    'desc_tip' => true,
                    'options' => array('primary' => __('Primary', 'smart-paypal-checkout-for-woocommerce'), 'alternative' => __('Alternative', 'smart-paypal-checkout-for-woocommerce'), 'inline' => __('Inline', 'smart-paypal-checkout-for-woocommerce'), 'none' => __('None', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_home_text_layout_logo_position' => array(
                    'title' => __('Logo Position', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_home_field pay_later_messaging_home_text_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'left',
                    'desc_tip' => true,
                    'options' => array('left' => __('Left', 'smart-paypal-checkout-for-woocommerce'), 'right' => __('Right', 'smart-paypal-checkout-for-woocommerce'), 'top' => __('Top', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_home_text_layout_text_size' => array(
                    'title' => __('Text Size', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_home_field pay_later_messaging_home_text_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => '12',
                    'desc_tip' => true,
                    'options' => array('10' => __('10 px', 'smart-paypal-checkout-for-woocommerce'), '11' => __('11 px', 'smart-paypal-checkout-for-woocommerce'), '12' => __('12 px', 'smart-paypal-checkout-for-woocommerce'), '13' => __('13 px', 'smart-paypal-checkout-for-woocommerce'), '14' => __('14 px', 'smart-paypal-checkout-for-woocommerce'), '15' => __('15 px', 'smart-paypal-checkout-for-woocommerce'), '16' => __('16 px', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_home_text_layout_text_color' => array(
                    'title' => __('Text Color', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_home_field pay_later_messaging_home_text_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'black',
                    'desc_tip' => true,
                    'options' => array('black' => __('Black', 'smart-paypal-checkout-for-woocommerce'), 'white' => __('White', 'smart-paypal-checkout-for-woocommerce'), 'monochrome' => __('Monochrome', 'smart-paypal-checkout-for-woocommerce'), 'grayscale' => __('Grayscale', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_home_flex_layout_color' => array(
                    'title' => __('Color', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_home_field pay_later_messaging_home_flex_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'blue',
                    'desc_tip' => true,
                    'options' => array('blue' => __('Blue', 'smart-paypal-checkout-for-woocommerce'), 'black' => __('Black', 'smart-paypal-checkout-for-woocommerce'), 'white' => __('White', 'smart-paypal-checkout-for-woocommerce'), 'white-no-border' => __('White (No Border)', 'smart-paypal-checkout-for-woocommerce'), 'gray' => __('Gray', 'smart-paypal-checkout-for-woocommerce'), 'monochrome' => __('Monochrome', 'smart-paypal-checkout-for-woocommerce'), 'grayscale' => __('Grayscale', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_home_flex_layout_ratio' => array(
                    'title' => __('Ratio', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_home_field pay_later_messaging_home_flex_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => '8x1',
                    'desc_tip' => true,
                    'options' => array('1x1' => __('Flexes between 120px and 300px wide', 'smart-paypal-checkout-for-woocommerce'), '1x4' => __('160px wide', 'smart-paypal-checkout-for-woocommerce'), '8x1' => __('Flexes between 250px and 768px wide', 'smart-paypal-checkout-for-woocommerce'), '20x1' => __('Flexes between 250px and 1169px wide', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_home_shortcode' => array(
                    'title' => __('Enable/Disable', 'smart-paypal-checkout-for-woocommerce'),
                    'label' => __('I need a shortcode so that I can place the message in a better spot on Home page.', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'checkbox',
                    'class' => 'pay_later_messaging_field pay_later_messaging_home_field pay_later_messaging_home_shortcode',
                    'description' => '',
                    'default' => 'no'
                ),
                'pay_later_messaging_home_preview_shortcode' => array(
                    'title' => __('Shortcode', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'copy_text',
                    'class' => 'pay_later_messaging_field pay_later_messaging_home_field pay_later_messaging_home_preview_shortcode preview_shortcode',
                    'description' => '',
                    'custom_attributes' => array('readonly' => 'readonly'),
                    'button_class' => 'home_copy_text',
                    'default' => '[aepfw_bnpl_message placement="home"]'
                ),
                'pay_later_messaging_category_page_settings' => array(
                    'title' => __('Category Page', 'smart-paypal-checkout-for-woocommerce'),
                    'class' => '',
                    'description' => __('Customize the appearance of <a target="_blank" href="https://www.paypal.com/us/business/buy-now-pay-later">Pay Later Messaging</a> on the Category page to promote special financing offers which help increase sales.', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'title',
                    'class' => 'pay_later_messaging_field pay_later_messaging_category_field',
                ),
                'pay_later_messaging_category_layout_type' => array(
                    'title' => __('Layout Type', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_category_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'flex',
                    'desc_tip' => true,
                    'options' => array('text' => __('Text Layout', 'smart-paypal-checkout-for-woocommerce'), 'flex' => __('Flex Layout', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_category_text_layout_logo_type' => array(
                    'title' => __('Logo Type', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_category_field pay_later_messaging_category_text_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'primary',
                    'desc_tip' => true,
                    'options' => array('primary' => __('Primary', 'smart-paypal-checkout-for-woocommerce'), 'alternative' => __('Alternative', 'smart-paypal-checkout-for-woocommerce'), 'inline' => __('Inline', 'smart-paypal-checkout-for-woocommerce'), 'none' => __('None', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_category_text_layout_logo_position' => array(
                    'title' => __('Logo Position', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_category_field pay_later_messaging_category_text_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'left',
                    'desc_tip' => true,
                    'options' => array('left' => __('Left', 'smart-paypal-checkout-for-woocommerce'), 'right' => __('Right', 'smart-paypal-checkout-for-woocommerce'), 'top' => __('Top', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_category_text_layout_text_size' => array(
                    'title' => __('Text Size', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_category_field pay_later_messaging_category_text_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => '12',
                    'desc_tip' => true,
                    'options' => array('10' => __('10 px', 'smart-paypal-checkout-for-woocommerce'), '11' => __('11 px', 'smart-paypal-checkout-for-woocommerce'), '12' => __('12 px', 'smart-paypal-checkout-for-woocommerce'), '13' => __('13 px', 'smart-paypal-checkout-for-woocommerce'), '14' => __('14 px', 'smart-paypal-checkout-for-woocommerce'), '15' => __('15 px', 'smart-paypal-checkout-for-woocommerce'), '16' => __('16 px', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_category_text_layout_text_color' => array(
                    'title' => __('Text Color', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_category_field pay_later_messaging_category_text_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'black',
                    'desc_tip' => true,
                    'options' => array('black' => __('Black', 'smart-paypal-checkout-for-woocommerce'), 'white' => __('White', 'smart-paypal-checkout-for-woocommerce'), 'monochrome' => __('Monochrome', 'smart-paypal-checkout-for-woocommerce'), 'grayscale' => __('Grayscale', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_category_flex_layout_color' => array(
                    'title' => __('Color', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_category_field pay_later_messaging_category_flex_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'blue',
                    'desc_tip' => true,
                    'options' => array('blue' => __('Blue', 'smart-paypal-checkout-for-woocommerce'), 'black' => __('Black', 'smart-paypal-checkout-for-woocommerce'), 'white' => __('White', 'smart-paypal-checkout-for-woocommerce'), 'white-no-border' => __('White (No Border)', 'smart-paypal-checkout-for-woocommerce'), 'gray' => __('Gray', 'smart-paypal-checkout-for-woocommerce'), 'monochrome' => __('Monochrome', 'smart-paypal-checkout-for-woocommerce'), 'grayscale' => __('Grayscale', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_category_flex_layout_ratio' => array(
                    'title' => __('Ratio', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_category_field pay_later_messaging_category_flex_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => '8x1',
                    'desc_tip' => true,
                    'options' => array('1x1' => __('Flexes between 120px and 300px wide', 'smart-paypal-checkout-for-woocommerce'), '1x4' => __('160px wide', 'smart-paypal-checkout-for-woocommerce'), '8x1' => __('Flexes between 250px and 768px wide', 'smart-paypal-checkout-for-woocommerce'), '20x1' => __('Flexes between 250px and 1169px wide', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_category_shortcode' => array(
                    'title' => __('Enable/Disable', 'smart-paypal-checkout-for-woocommerce'),
                    'label' => __('I need a shortcode so that I can place the message in a better spot on category page.', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'checkbox',
                    'class' => 'pay_later_messaging_field pay_later_messaging_category_field pay_later_messaging_category_shortcode',
                    'description' => '',
                    'default' => 'no'
                ),
                'pay_later_messaging_category_preview_shortcode' => array(
                    'title' => __('Shortcode', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'copy_text',
                    'class' => 'pay_later_messaging_field pay_later_messaging_category_field pay_later_messaging_category_preview_shortcode preview_shortcode',
                    'description' => '',
                    'button_class' => 'category_copy_text',
                    'custom_attributes' => array('readonly' => 'readonly'),
                    'default' => '[aepfw_bnpl_message placement="category"]'
                ),
                'pay_later_messaging_product_page_settings' => array(
                    'title' => __('Product Page', 'smart-paypal-checkout-for-woocommerce'),
                    'description' => __('Customize the appearance of <a target="_blank" href="https://www.paypal.com/us/business/buy-now-pay-later">Pay Later Messaging</a> on the Product page to promote special financing offers which help increase sales.', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'title',
                    'class' => 'pay_later_messaging_field pay_later_messaging_product_field',
                ),
                'pay_later_messaging_product_layout_type' => array(
                    'title' => __('Layout Type', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_product_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'text',
                    'desc_tip' => true,
                    'options' => array('text' => __('Text Layout', 'smart-paypal-checkout-for-woocommerce'), 'flex' => __('Flex Layout', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_product_text_layout_logo_type' => array(
                    'title' => __('Logo Type', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_product_field pay_later_messaging_product_text_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'primary',
                    'desc_tip' => true,
                    'options' => array('primary' => __('Primary', 'smart-paypal-checkout-for-woocommerce'), 'alternative' => __('Alternative', 'smart-paypal-checkout-for-woocommerce'), 'inline' => __('Inline', 'smart-paypal-checkout-for-woocommerce'), 'none' => __('None', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_product_text_layout_logo_position' => array(
                    'title' => __('Logo Position', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_product_field pay_later_messaging_product_text_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'left',
                    'desc_tip' => true,
                    'options' => array('left' => __('Left', 'smart-paypal-checkout-for-woocommerce'), 'right' => __('Right', 'smart-paypal-checkout-for-woocommerce'), 'top' => __('Top', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_product_text_layout_text_size' => array(
                    'title' => __('Text Size', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_product_field pay_later_messaging_product_text_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => '12',
                    'desc_tip' => true,
                    'options' => array('10' => __('10 px', 'smart-paypal-checkout-for-woocommerce'), '11' => __('11 px', 'smart-paypal-checkout-for-woocommerce'), '12' => __('12 px', 'smart-paypal-checkout-for-woocommerce'), '13' => __('13 px', 'smart-paypal-checkout-for-woocommerce'), '14' => __('14 px', 'smart-paypal-checkout-for-woocommerce'), '15' => __('15 px', 'smart-paypal-checkout-for-woocommerce'), '16' => __('16 px', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_product_text_layout_text_color' => array(
                    'title' => __('Text Color', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_product_field pay_later_messaging_product_text_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'black',
                    'desc_tip' => true,
                    'options' => array('black' => __('Black', 'smart-paypal-checkout-for-woocommerce'), 'white' => __('White', 'smart-paypal-checkout-for-woocommerce'), 'monochrome' => __('Monochrome', 'smart-paypal-checkout-for-woocommerce'), 'grayscale' => __('Grayscale', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_product_flex_layout_color' => array(
                    'title' => __('Color', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_product_field pay_later_messaging_product_flex_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'blue',
                    'desc_tip' => true,
                    'options' => array('blue' => __('Blue', 'smart-paypal-checkout-for-woocommerce'), 'black' => __('Black', 'smart-paypal-checkout-for-woocommerce'), 'white' => __('White', 'smart-paypal-checkout-for-woocommerce'), 'white-no-border' => __('White (No Border)', 'smart-paypal-checkout-for-woocommerce'), 'gray' => __('Gray', 'smart-paypal-checkout-for-woocommerce'), 'monochrome' => __('Monochrome', 'smart-paypal-checkout-for-woocommerce'), 'grayscale' => __('Grayscale', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_product_flex_layout_ratio' => array(
                    'title' => __('Ratio', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_product_field pay_later_messaging_product_flex_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => '8x1',
                    'desc_tip' => true,
                    'options' => array('1x1' => __('Flexes between 120px and 300px wide', 'smart-paypal-checkout-for-woocommerce'), '1x4' => __('160px wide', 'smart-paypal-checkout-for-woocommerce'), '8x1' => __('Flexes between 250px and 768px wide', 'smart-paypal-checkout-for-woocommerce'), '20x1' => __('Flexes between 250px and 1169px wide', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_product_shortcode' => array(
                    'title' => __('Enable/Disable', 'smart-paypal-checkout-for-woocommerce'),
                    'label' => __('I need a shortcode so that I can place the message in a better spot on product page.', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'checkbox',
                    'class' => 'pay_later_messaging_field pay_later_messaging_product_field pay_later_messaging_product_shortcode',
                    'description' => '',
                    'default' => 'no'
                ),
                'pay_later_messaging_product_preview_shortcode' => array(
                    'title' => __('Shortcode', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'copy_text',
                    'class' => 'pay_later_messaging_field pay_later_messaging_product_field pay_later_messaging_product_preview_shortcode preview_shortcode',
                    'description' => '',
                    'button_class' => 'product_copy_text',
                    'custom_attributes' => array('readonly' => 'readonly'),
                    'default' => '[aepfw_bnpl_message placement="product"]'
                ),
                'pay_later_messaging_cart_page_settings' => array(
                    'title' => __('Cart Page', 'smart-paypal-checkout-for-woocommerce'),
                    'description' => __('Customize the appearance of <a target="_blank" href="https://www.paypal.com/us/business/buy-now-pay-later">Pay Later Messaging</a> on the Cart page to promote special financing offers which help increase sales.', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'title',
                    'class' => 'pay_later_messaging_field pay_later_messaging_cart_field',
                ),
                'pay_later_messaging_cart_layout_type' => array(
                    'title' => __('Layout Type', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_cart_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'text',
                    'desc_tip' => true,
                    'options' => array('text' => __('Text Layout', 'smart-paypal-checkout-for-woocommerce'), 'flex' => __('Flex Layout', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_cart_text_layout_logo_type' => array(
                    'title' => __('Logo Type', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_cart_field pay_later_messaging_cart_text_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'primary',
                    'desc_tip' => true,
                    'options' => array('primary' => __('Primary', 'smart-paypal-checkout-for-woocommerce'), 'alternative' => __('Alternative', 'smart-paypal-checkout-for-woocommerce'), 'inline' => __('Inline', 'smart-paypal-checkout-for-woocommerce'), 'none' => __('None', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_cart_text_layout_logo_position' => array(
                    'title' => __('Logo Position', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_cart_field pay_later_messaging_cart_text_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'left',
                    'desc_tip' => true,
                    'options' => array('left' => __('Left', 'smart-paypal-checkout-for-woocommerce'), 'right' => __('Right', 'smart-paypal-checkout-for-woocommerce'), 'top' => __('Top', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_cart_text_layout_text_size' => array(
                    'title' => __('Text Size', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_cart_field pay_later_messaging_cart_text_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => '12',
                    'desc_tip' => true,
                    'options' => array('10' => __('10 px', 'smart-paypal-checkout-for-woocommerce'), '11' => __('11 px', 'smart-paypal-checkout-for-woocommerce'), '12' => __('12 px', 'smart-paypal-checkout-for-woocommerce'), '13' => __('13 px', 'smart-paypal-checkout-for-woocommerce'), '14' => __('14 px', 'smart-paypal-checkout-for-woocommerce'), '15' => __('15 px', 'smart-paypal-checkout-for-woocommerce'), '16' => __('16 px', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_cart_text_layout_text_color' => array(
                    'title' => __('Text Color', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_cart_field pay_later_messaging_cart_text_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'black',
                    'desc_tip' => true,
                    'options' => array('black' => __('Black', 'smart-paypal-checkout-for-woocommerce'), 'white' => __('White', 'smart-paypal-checkout-for-woocommerce'), 'monochrome' => __('Monochrome', 'smart-paypal-checkout-for-woocommerce'), 'grayscale' => __('Grayscale', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_cart_flex_layout_color' => array(
                    'title' => __('Color', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_cart_field pay_later_messaging_cart_flex_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'blue',
                    'desc_tip' => true,
                    'options' => array('blue' => __('Blue', 'smart-paypal-checkout-for-woocommerce'), 'black' => __('Black', 'smart-paypal-checkout-for-woocommerce'), 'white' => __('White', 'smart-paypal-checkout-for-woocommerce'), 'white-no-border' => __('White (No Border)', 'smart-paypal-checkout-for-woocommerce'), 'gray' => __('Gray', 'smart-paypal-checkout-for-woocommerce'), 'monochrome' => __('Monochrome', 'smart-paypal-checkout-for-woocommerce'), 'grayscale' => __('Grayscale', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_cart_flex_layout_ratio' => array(
                    'title' => __('Ratio', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_cart_field pay_later_messaging_cart_flex_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => '8x1',
                    'desc_tip' => true,
                    'options' => array('1x1' => __('Flexes between 120px and 300px wide', 'smart-paypal-checkout-for-woocommerce'), '1x4' => __('160px wide', 'smart-paypal-checkout-for-woocommerce'), '8x1' => __('Flexes between 250px and 768px wide', 'smart-paypal-checkout-for-woocommerce'), '20x1' => __('Flexes between 250px and 1169px wide', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_cart_shortcode' => array(
                    'title' => __('Enable/Disable', 'smart-paypal-checkout-for-woocommerce'),
                    'label' => __('I need a shortcode so that I can place the message in a better spot on cart page.', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'checkbox',
                    'class' => 'pay_later_messaging_field pay_later_messaging_cart_field pay_later_messaging_cart_shortcode',
                    'description' => '',
                    'default' => 'no'
                ),
                'pay_later_messaging_cart_preview_shortcode' => array(
                    'title' => __('Shortcode', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'copy_text',
                    'class' => 'pay_later_messaging_field pay_later_messaging_cart_field pay_later_messaging_cart_preview_shortcode preview_shortcode',
                    'description' => '',
                    'button_class' => 'cart_copy_text',
                    'custom_attributes' => array('readonly' => 'readonly'),
                    'default' => '[aepfw_bnpl_message placement="cart"]'
                ),
                'pay_later_messaging_payment_page_settings' => array(
                    'title' => __('Payment Page', 'smart-paypal-checkout-for-woocommerce'),
                    'description' => __('Customize the appearance of <a target="_blank" href="https://www.paypal.com/us/business/buy-now-pay-later">Pay Later Messaging</a> on the Payment page to promote special financing offers which help increase sales.', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'title',
                    'class' => 'pay_later_messaging_field pay_later_messaging_payment_field',
                ),
                'pay_later_messaging_payment_layout_type' => array(
                    'title' => __('Layout Type', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_payment_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'text',
                    'desc_tip' => true,
                    'options' => array('text' => __('Text Layout', 'smart-paypal-checkout-for-woocommerce'), 'flex' => __('Flex Layout', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_payment_text_layout_logo_type' => array(
                    'title' => __('Logo Type', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_payment_field pay_later_messaging_payment_text_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'primary',
                    'desc_tip' => true,
                    'options' => array('primary' => __('Primary', 'smart-paypal-checkout-for-woocommerce'), 'alternative' => __('Alternative', 'smart-paypal-checkout-for-woocommerce'), 'inline' => __('Inline', 'smart-paypal-checkout-for-woocommerce'), 'none' => __('None', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_payment_text_layout_logo_position' => array(
                    'title' => __('Logo Position', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_payment_field pay_later_messaging_payment_text_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'left',
                    'desc_tip' => true,
                    'options' => array('left' => __('Left', 'smart-paypal-checkout-for-woocommerce'), 'right' => __('Right', 'smart-paypal-checkout-for-woocommerce'), 'top' => __('Top', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_payment_text_layout_text_size' => array(
                    'title' => __('Text Size', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_payment_field pay_later_messaging_payment_text_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => '12',
                    'desc_tip' => true,
                    'options' => array('10' => __('10 px', 'smart-paypal-checkout-for-woocommerce'), '11' => __('11 px', 'smart-paypal-checkout-for-woocommerce'), '12' => __('12 px', 'smart-paypal-checkout-for-woocommerce'), '13' => __('13 px', 'smart-paypal-checkout-for-woocommerce'), '14' => __('14 px', 'smart-paypal-checkout-for-woocommerce'), '15' => __('15 px', 'smart-paypal-checkout-for-woocommerce'), '16' => __('16 px', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_payment_text_layout_text_color' => array(
                    'title' => __('Text Color', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_payment_field pay_later_messaging_payment_text_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'black',
                    'desc_tip' => true,
                    'options' => array('black' => __('Black', 'smart-paypal-checkout-for-woocommerce'), 'white' => __('White', 'smart-paypal-checkout-for-woocommerce'), 'monochrome' => __('Monochrome', 'smart-paypal-checkout-for-woocommerce'), 'grayscale' => __('Grayscale', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_payment_flex_layout_color' => array(
                    'title' => __('Color', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_payment_field pay_later_messaging_payment_flex_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'blue',
                    'desc_tip' => true,
                    'options' => array('blue' => __('Blue', 'smart-paypal-checkout-for-woocommerce'), 'black' => __('Black', 'smart-paypal-checkout-for-woocommerce'), 'white' => __('White', 'smart-paypal-checkout-for-woocommerce'), 'white-no-border' => __('White (No Border)', 'smart-paypal-checkout-for-woocommerce'), 'gray' => __('Gray', 'smart-paypal-checkout-for-woocommerce'), 'monochrome' => __('Monochrome', 'smart-paypal-checkout-for-woocommerce'), 'grayscale' => __('Grayscale', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_payment_flex_layout_ratio' => array(
                    'title' => __('Ratio', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_payment_field pay_later_messaging_payment_flex_layout_field',
                    'description' => __('', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => '8x1',
                    'desc_tip' => true,
                    'options' => array('1x1' => __('Flexes between 120px and 300px wide', 'smart-paypal-checkout-for-woocommerce'), '1x4' => __('160px wide', 'smart-paypal-checkout-for-woocommerce'), '8x1' => __('Flexes between 250px and 768px wide', 'smart-paypal-checkout-for-woocommerce'), '20x1' => __('Flexes between 250px and 1169px wide', 'smart-paypal-checkout-for-woocommerce'))
                ),
                'pay_later_messaging_payment_shortcode' => array(
                    'title' => __('Enable/Disable', 'smart-paypal-checkout-for-woocommerce'),
                    'label' => __('I need a shortcode so that I can place the message in a better spot on payment page.', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'checkbox',
                    'class' => 'pay_later_messaging_field pay_later_messaging_payment_field pay_later_messaging_payment_shortcode',
                    'description' => '',
                    'default' => 'no'
                ),
                'pay_later_messaging_payment_preview_shortcode' => array(
                    'title' => __('Shortcode', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'copy_text',
                    'class' => 'pay_later_messaging_field pay_later_messaging_payment_field pay_later_messaging_payment_preview_shortcode preview_shortcode',
                    'description' => '',
                    'button_class' => 'payment_copy_text',
                    'custom_attributes' => array('readonly' => 'readonly'),
                    'default' => '[aepfw_bnpl_message placement="payment"]'
            ));

            $advanced_settings = array(
                'advanced' => array(
                    'title' => __('Advanced Settings', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'title',
                    'description' => '',
                    'class' => 'psb_separator_heading',
                ),
                'brand_name' => array(
                    'title' => __('Brand Name', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'text',
                    'description' => __('This controls what users see as the brand / company name on PayPal review pages.', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => __(get_bloginfo('name'), 'smart-paypal-checkout-for-woocommerce'),
                    'desc_tip' => true,
                ),
                'landing_page' => array(
                    'title' => __('Landing Page', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select',
                    'description' => __('The type of landing page to show on the PayPal site for customer checkout. PayPal Account Optional must be checked for this option to be used.', 'smart-paypal-checkout-for-woocommerce'),
                    'options' => array('LOGIN' => __('Login', 'smart-paypal-checkout-for-woocommerce'),
                        'BILLING' => __('Billing', 'smart-paypal-checkout-for-woocommerce'),
                        'NO_PREFERENCE' => __('No Preference', 'smart-paypal-checkout-for-woocommerce')),
                    'default' => 'NO_PREFERENCE',
                    'desc_tip' => true,
                ),
                'enable_advanced_card_payments' => array(
                    'title' => __('Enable/Disable', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable advanced credit and debit card payments', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'no',
                    'description' => __('Currently PayPal support Unbranded payments in US, AU, UK, FR, IT and ES only. <br> <br>Advanced credit and debit cards requires that your business account be evaluated and approved by PayPal. <br><a target="_blank" href="https://www.sandbox.paypal.com/bizsignup/entry/product/ppcp">Enable for Sandbox Account</a> <span> | </span> <a target="_blank" href="https://www.paypal.com/bizsignup/entry/product/ppcp">Enable for Live Account</a><br>', 'smart-paypal-checkout-for-woocommerce'),
                ),
                'threed_secure_enabled' => array(
                    'title' => __('3D Secure', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable 3D Secure', 'smart-paypal-checkout-for-woocommerce'),
                    'description' => __('If you are based in Europe, you are subjected to PSD2. PayPal recommends this option', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'no',
                ),
                'paymentaction' => array(
                    'title' => __('Payment action', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select',
                    'description' => __('Choose whether you wish to capture funds immediately or authorize payment only.', 'smart-paypal-checkout-for-woocommerce') . '  ' . $payment_action_not_available,
                    'default' => 'capture',
                    'desc_tip' => true,
                    'options' => array(
                        'capture' => __('Capture', 'smart-paypal-checkout-for-woocommerce'),
                        'authorize' => __('Authorize', 'smart-paypal-checkout-for-woocommerce'),
                    ),
                ),
                'invoice_prefix' => array(
                    'title' => __('Invoice prefix', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'text',
                    'description' => __('Please enter a prefix for your invoice numbers. If you use your PayPal account for multiple stores ensure this prefix is unique as PayPal will not allow orders with the same invoice number.', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'WC-PSB',
                    'desc_tip' => true,
                ),
                'order_review_page_enable_coupons' => array(
                    'title' => __('Enable/Disable coupons', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable the use of coupon codes', 'smart-paypal-checkout-for-woocommerce'),
                    'description' => __('Coupons can be applied from the order review.', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'yes',
                ),
                'debug' => array(
                    'title' => __('Debug log', 'smart-paypal-checkout-for-woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable logging', 'smart-paypal-checkout-for-woocommerce'),
                    'default' => 'no',
                    'description' => sprintf(__('Log PayPal events, such as Webhook, Payment, Refund inside %s Note: this may log personal information. We recommend using this for debugging purposes only and deleting the logs when finished.', 'smart-paypal-checkout-for-woocommerce'), '<code>' . WC_Log_Handler_File::get_log_file_path('paypal_smart_checkout') . '</code>'),
                )
            );
            if (function_exists('wc_coupons_enabled')) {
                if (!wc_coupons_enabled()) {
                    unset($advanced_settings['order_review_page_enable_coupons']);
                }
            }
            $settings = apply_filters('psb_settings', array_merge($default_settings, $button_manager_settings, $pay_later_messaging_settings, $advanced_settings));
            return $settings;
        }

    }

}