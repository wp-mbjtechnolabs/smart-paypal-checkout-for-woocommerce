<?php

defined('ABSPATH') || exit;

class Paypal_Checkout_For_Woocommerce_Log {

    public $log_enabled = true;
    public $logger = false;
    protected static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        $this->psb_load_class();
        $this->log_enabled = 'yes' === $this->settings->get('debug', 'yes');
    }

    public function log($message, $level = 'info') {
        if ($this->log_enabled) {
            if (empty($this->logger)) {
                $this->logger = wc_get_logger();
            }
            $this->logger->log($level, $message, array('source' => 'paypal_smart_checkout'));
        }
    }

    public function webhook_log($message, $level = 'info') {
        if ($this->log_enabled) {
            if (empty($this->logger)) {
                $this->logger = wc_get_logger();
            }
            $this->logger->log($level, $message, array('source' => 'psb_webhook'));
        }
    }

    public function temp_log($message, $level = 'info') {
        if ($this->log_enabled) {
            if (empty($this->logger)) {
                $this->logger = wc_get_logger();
            }
            $this->logger->log($level, $message, array('source' => 'psb_temp'));
        }
    }

    public function psb_load_class() {
        try {
            if (!class_exists('Paypal_Checkout_For_Woocommerce_Settings')) {
                include_once SMART_PAYPAL_CHECKOUT_FOR_WOOCOMMERCE_DIR . '/includes/class-smart-paypal-checkout-for-woocommerce-settings.php';
            }
            $this->settings = Paypal_Checkout_For_Woocommerce_Settings::instance();
        } catch (Exception $ex) {
            $this->log("The exception was created on line: " . $ex->getLine(), 'error');
            $this->log($ex->getMessage(), 'error');
        }
    }

}
