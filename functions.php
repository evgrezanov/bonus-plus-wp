<?php

/**
 * General functions
 */
function bp_api_request($endpoint, $params, $type)
{
    if (empty($endpoint) || empty($type))
        return;

    $url = 'https://bonusplus.pro/api/' . $endpoint;

    if (!empty($params) & is_array($params)) {
        foreach ($params as $key => $value) {
            $url = add_query_arg([$key => $value], $url);
        }
    }

    $option = get_option('woobonusplus_option_name');
    $token = $option['_api_2'];
    $token = base64_encode($token);

    // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);


    $headers = array();
    $headers[] = 'Authorization: ApiKey ' . $token;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        error_log('Error:' . curl_error($ch));
    }
    curl_close($ch);

    return $result;
}

/**
 *  Return customer billing phone
 */
function bp_api_get_customer_phone($customer_id = '')
{
    if (empty($customer_id)) {
        $customer_id = get_current_user_id();
    }

    $phone = get_user_meta($customer_id, 'billing_phone', true);

    $phone = apply_filters('bp_api_filter_get_customer_phone', $phone);

    return $phone;
}

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