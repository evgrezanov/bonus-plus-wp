<?php

/**
 * General functions
 */

/**
 * Check if WooCommerce is activated
 */
if (!function_exists('bpwp_is_woocommerce_activated')) {
    function bpwp_is_woocommerce_activated()
    {
        if (class_exists('woocommerce')) {
            return true;
        } else {
            return false;
        }
    }
}
