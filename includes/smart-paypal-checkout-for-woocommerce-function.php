<?php

function psb_remove_empty_key($data) {
    $original = $data;
    $data = array_filter($data);
    $data = array_map(function ($e) {
        return is_array($e) ? psb_remove_empty_key($e) : $e;
    }, $data);
    return $original === $data ? $data : psb_remove_empty_key($data);
}

function psb_set_session($key, $value) {
    if (!class_exists('WooCommerce') || WC()->session == null) {
        return false;
    }
    $psb_session = WC()->session->get('psb_session');
    if (!is_array($psb_session)) {
        $psb_session = array();
    }
    $psb_session[$key] = $value;
    WC()->session->set('psb_session', $psb_session);
}

function psb_get_session($key) {
    if (!class_exists('WooCommerce') || WC()->session == null) {
        return false;
    }

    $psb_session = WC()->session->get('psb_session');
    if (!empty($psb_session[$key])) {
        return $psb_session[$key];
    }
    return false;
}

function psb_unset_session($key) {
    if (!class_exists('WooCommerce') || WC()->session == null) {
        return false;
    }
    $psb_session = WC()->session->get('psb_session');
    if (!empty($psb_session[$key])) {
        unset($psb_session[$key]);
        WC()->session->set('psb_session', $psb_session);
    }
}

function has_active_session() {
    $checkout_details = psb_get_session('psb_paypal_transaction_details');
    $psb_paypal_order_id = psb_get_session('psb_paypal_order_id');
    if (!empty($checkout_details) && !empty($psb_paypal_order_id) && isset($_GET['paypal_order_id'])) {
        return true;
    }
    return false;
}

function psb_update_post_meta($order, $key, $value) {
    if (!is_object($order)) {
        $order = wc_get_order($order);
    }
    $old_wc = version_compare(WC_VERSION, '3.0', '<');
    if ($old_wc) {
        update_post_meta($order->id, $key, $value);
    } else {
        $order->update_meta_data($key, $value);
    }
    if (!$old_wc) {
        $order->save_meta_data();
    }
}

function psb_get_post_meta($order, $key, $bool = true) {
    $order_meta_value = false;
    if (!is_object($order)) {
        $order = wc_get_order($order);
    }
    $old_wc = version_compare(WC_VERSION, '3.0', '<');
    if ($old_wc) {
        $order_meta_value = get_post_meta($order->id, $key, $bool);
    } else {
        $order_meta_value = $order->get_meta($key, $bool);
    }
    return $order_meta_value;
}

function get_button_locale_code() {
    $_supportedLocale = array(
        'en_US', 'fr_XC', 'es_XC', 'zh_XC', 'en_AU', 'de_DE', 'nl_NL',
        'fr_FR', 'pt_BR', 'fr_CA', 'zh_CN', 'ru_RU', 'en_GB', 'zh_HK',
        'he_IL', 'it_IT', 'ja_JP', 'pl_PL', 'pt_PT', 'es_ES', 'sv_SE', 'zh_TW', 'tr_TR'
    );
    $wpml_locale = psb_get_wpml_locale();
    if ($wpml_locale) {
        if (in_array($wpml_locale, $_supportedLocale)) {
            return $wpml_locale;
        }
    }
    $locale = get_locale();
    if (get_locale() != '') {
        $locale = substr(get_locale(), 0, 5);
    }
    if (!in_array($locale, $_supportedLocale)) {
        $locale = 'en_US';
    }
    return $locale;
}

function psb_get_wpml_locale() {
    $locale = false;
    if (defined('ICL_LANGUAGE_CODE') && function_exists('icl_object_id')) {
        global $sitepress;
        if (isset($sitepress)) {
            $locale = $sitepress->get_current_language();
        } else if (function_exists('pll_current_language')) {
            $locale = pll_current_language('locale');
        } else if (function_exists('pll_default_language')) {
            $locale = pll_default_language('locale');
        }
    }
    return $locale;
}

function psb_is_local_server() {
    if (!isset($_SERVER['HTTP_HOST'])) {
        return;
    }
    if ($_SERVER['HTTP_HOST'] === 'localhost' || substr($_SERVER['REMOTE_ADDR'], 0, 3) === '10.' || substr($_SERVER['REMOTE_ADDR'], 0, 7) === '192.168') {
        return true;
    }
    $live_sites = [
        'HTTP_CLIENT_IP',
        'HTTP_X_REAL_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
    ];
    foreach ($live_sites as $ip) {
        if (!empty($_SERVER[$ip])) {
            return false;
        }
    }
    if (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {
        return true;
    }
    $fragments = explode('.', site_url());
    if (in_array(end($fragments), array('dev', 'local', 'localhost', 'test'))) {
        return true;
    }
    return false;
}

function psb_readable($tex) {
    $tex = ucwords(strtolower(str_replace('_', ' ', $tex)));
    return $tex;
}

if (!function_exists('psb_is_advanced_cards_available')) {

    function psb_is_advanced_cards_available() {
        try {
            $currency = get_woocommerce_currency();
            $country_state = wc_get_base_location();
//            $available_old = array('AU' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'AT' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'BE' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'BG' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'CA' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'CY' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'CZ' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'DK' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'EE' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'FI' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'FR' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'GR' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'HU' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'IT' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'LV' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'LI' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'LT' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'LU' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'MT' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'NL' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'NO' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'PL' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'PT' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'RO' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'SK' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'SI' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'ES' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'SE' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
//                'US' => array('AUD', 'CAD', 'EUR', 'GBP', 'JPY', 'USD'),
//                'GB' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD')
//            );
            $available = array(
                'US' => array('AUD', 'CAD', 'EUR', 'GBP', 'JPY', 'USD'),
                'AU' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
                'GB' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
                'FR' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
                'IT' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
                'ES' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD')
            );
            if (isset($available[$country_state['country']]) && in_array($currency, $available[$country_state['country']])) {
                return true;
            }
            return false;
        } catch (Exception $ex) {
            return false;
        }
    }

}