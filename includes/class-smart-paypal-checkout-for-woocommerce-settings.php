<?php

defined('ABSPATH') || exit;

$default_settings = array(
    'enabled' => array(
        'title' => __('Enable/Disable', 'smart-paypal-checkout-for-woocommerce'),
        'type' => 'checkbox',
        'label' => __('Enable PayPal Checkout', 'smart-paypal-checkout-for-woocommerce'),
        'description' => __('Check this box to enable the payment gateway. Leave unchecked to disable it.', 'smart-paypal-checkout-for-woocommerce'),
        'desc_tip' => true,
        'default' => 'yes',
    ),
    'title' => array(
        'title' => __('Title', 'smart-paypal-checkout-for-woocommerce'),
        'type' => 'text',
        'description' => __('This controls the label the user will see for this payment option during checkout.', 'smart-paypal-checkout-for-woocommerce'),
        'default' => __('PayPal', 'smart-paypal-checkout-for-woocommerce'),
        'desc_tip' => true,
    ),
    'description' => array(
        'title' => __('Description', 'smart-paypal-checkout-for-woocommerce'),
        'type' => 'text',
        'desc_tip' => true,
        'description' => __('This controls the description the user will see for this payment option during checkout.', 'smart-paypal-checkout-for-woocommerce'),
        'default' => __("Pay via PayPal; you can pay with your credit card if you don't have a PayPal account.", 'smart-paypal-checkout-for-woocommerce'),
    ),
    'testmode' => array(
        'title' => __('PayPal sandbox', 'smart-paypal-checkout-for-woocommerce'),
        'type' => 'checkbox',
        'label' => __('Enable PayPal sandbox', 'smart-paypal-checkout-for-woocommerce'),
        'default' => 'no',
        'description' => __('Check this box to enable test mode so that all transactions will hit PayPal’s sandbox server instead of the live server. This should only be used during development as no real transactions will occur when this is enabled.', 'smart-paypal-checkout-for-woocommerce'),
        'desc_tip' => true
    ),
    'api_details' => array(
        'title' => __('API credentials', 'smart-paypal-checkout-for-woocommerce'),
        'type' => 'title',
        'description' => __("<a target='_blank' href='https://developer.paypal.com/docs/business/get-started/#step-1-get-api-credentials'>Get API credentials</a>", 'smart-paypal-checkout-for-woocommerce'),
        'desc_tip' => true
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
        'title' => __('Smart Payment Buttons options', 'smart-paypal-checkout-for-woocommerce'),
        'type' => 'title',
        'description' => '',
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

$advanced_settings = array(
    'advanced' => array(
        'title' => __('Advanced options', 'smart-paypal-checkout-for-woocommerce'),
        'type' => 'title',
        'description' => '',
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
        'description' => __('Choose whether you wish to capture funds immediately or authorize payment only.', 'smart-paypal-checkout-for-woocommerce') .'  '. $payment_action_not_available,
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
    'debug' => array(
        'title' => __('Debug log', 'smart-paypal-checkout-for-woocommerce'),
        'type' => 'checkbox',
        'label' => __('Enable logging', 'smart-paypal-checkout-for-woocommerce'),
        'default' => 'no',
        'description' => sprintf(__('Log PayPal events, such as Webhook, Payment, Refund inside %s Note: this may log personal information. We recommend using this for debugging purposes only and deleting the logs when finished.', 'smart-paypal-checkout-for-woocommerce'), '<code>' . WC_Log_Handler_File::get_log_file_path('paypal') . '</code>'),
    )
);
if (function_exists('wc_coupons_enabled')) {
    if (wc_coupons_enabled()) {
        $order_review_page_settings['order_review_page_enable_coupons'] = array(
            'title' => __('Enable/Disable coupons', 'smart-paypal-checkout-for-woocommerce'),
            'type' => 'checkbox',
            'label' => __('Enable the use of coupon codes', 'smart-paypal-checkout-for-woocommerce'),
            'description' => __('Coupons can be applied from the order review.', 'smart-paypal-checkout-for-woocommerce'),
            'default' => 'yes',
        );
    }
}
$settings = apply_filters('psb_settings', array_merge($default_settings, $button_manager_settings, $order_review_page_settings, $advanced_settings));
return $settings;
