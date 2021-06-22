<?php

/**
 * General functions
 */
function bpwp_api_request($endpoint, $params, $type)
{
    if (empty($endpoint) || empty($type))
        return;

    $url = 'https://bonusplus.pro/api/' . $endpoint;

    if (!empty($params) & is_array($params)) {
        foreach ($params as $key => $value) {
            $url = add_query_arg(array($key => $value), $url);
        }
    }

    $token = get_option('bpwp_api_key');
    $token = base64_encode($token);

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
function bpwp_api_get_customer_phone($customer_id = '')
{
    if (empty($customer_id) && is_user_logged_in()) {
        $customer_id = get_current_user_id();
    }

    $phone = get_user_meta($customer_id, 'billing_phone', true);

    $phone = apply_filters('bpwp_api_filter_get_customer_phone', $phone);

    return $phone;
}

/**
 *  Return customer bonus data
 *
 */
function bpwp_api_get_customer_data($customer_id = '')
{
    if (empty($customer_id) && is_user_logged_in()) {
        $customer_id = get_current_user_id();
    }

    $bonusData = get_user_meta($customer_id, 'bonus-plus', true);
    
    $data = array();
    foreach ($bonusData as $key => $value){
        if ($key != 'person'){
            $data[$key] = $value;
        } else {
            $_person = $value;
            foreach ($_person as $pkey=>$pvalue){
                $data[$pkey] = $pvalue;
            }
        }
    }
    
    return $data;
}

/**
 * Check if WooCommerce is activated
 */
if (!function_exists('is_woocommerce_activated')) {
    function is_woocommerce_activated()
    {
        if (class_exists('woocommerce')) {
            return true;
        } else {
            return false;
        }
    }
}

