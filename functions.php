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
    /*
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
    */
    
    $args = array(
        'method'      => $type,
        'headers'     => array(
            'Authorization' => 'ApiKey ' . $token,
        ),
    );

    //$response = wp_remote_request($url, $args);
    
    $request = wp_remote_request($url, $args);
    if (is_wp_error($request)) {
        do_action(
            'bpwp_logger_error',
            $type = 'BPWP-Request',
            $title = 'Ошибка REST API WP Error',
            $desc = $request->get_error_message()
        );

        return false;
    }

    if (empty($request['body'])) {
        do_action(
            'bpwp_logger_error',
            $type = 'BPWP-Request',
            $title = 'REST API вернулся без требуемых данных'
        );

        return false;
    }

    $response = json_decode($request['body'], true);

    if (!empty($response["errors"]) and is_array($response["errors"])) {
        foreach ($response["errors"] as $error) {
            do_action(
                'bpwp_logger_error',
                $type = 'BPWP-Request',
                $title = $url,
                $response
            );
        }
    }
    //if ($response_code != 200)
    /*$result = array(
        'response'  => $response,
        'code'      => $response_code,
        'body'      => $response_body,
    );*/

    return $response;
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