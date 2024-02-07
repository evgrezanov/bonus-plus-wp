<?php

/**
 * Обертка для вызова API Бонус+
 * 
 *  @param $endpoint variant
 *  @param $params array
 *  @param $type variant
 */
function bpwp_api_request($endpoint, $params, $type)
{
    $token = get_option('bpwp_api_key');

    if (empty($endpoint) || empty($type) || empty($token))
        return;

    $url = 'https://bonusplus.pro/api/' . $endpoint;

    $token = base64_encode($token);

    if ($type == 'GET'){
        if (!empty($params) & is_array($params)) {
            foreach ($params as $key => $value) {
                $url = add_query_arg(array($key => $value), $url);
            }
        }
        $args = array(
            'method'      => $type,
            'headers'     => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'ApiKey ' . $token,
            ),
        );
    }

    if ($type == 'POST') {
        $args = array(
            'method'      => $type,
            'headers'     => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'ApiKey ' . $token,
            ),
            'body'        => $params,
        );
    }

    if ($type == 'PUT') {
        $args = array(
            'method'      => $type,
            'headers'     => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'ApiKey ' . $token,
            ),
            'body'        => $params,
        );
        $args['headers']['Content-Length'] = strlen( $args['body'] ?: '' ); // Добавим Content-Length. Важно, если body пустой
    }

    if ($type == 'PATCH') {
        $args = array(
            'method'      => $type,
            'headers'     => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'ApiKey ' . $token,
            ),
            'body'        => $params,
        );
    }

    $request = wp_remote_request($url, $args);

    $response_code = wp_remote_retrieve_response_code($request);
    
    if (is_wp_error($request)) {
        $response['code'] = $request->get_error_code();
        $response['message'] = $request->get_error_message();
        $response['request'] = $request;
        $response['class'] = 'notice notice-error';
    }

    $response['code'] = $response_code;
    $response['message'] = bpwp_api_get_error_msg($response_code);

    if (!in_array($response_code, [200, 204])){
        $response['request'] = $request;
        $response['class'] = 'notice notice-warning';
    } else {
        $response['request'] = json_decode($request['body'], true);
        $response['class'] = 'notice notice-success';
    }

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
 *  $customer_id int ID Клиента
 *
 */
function bpwp_api_get_customer_data($customer_id = '')
{
    if (empty($customer_id) && is_user_logged_in()) {
        $customer_id = get_current_user_id();
    }

    $bonusData = get_user_meta($customer_id, 'bonus-plus', true);
    
    $data = [];
    if (!empty($bonusData) && is_array($bonusData)){
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
    }
    
    return $data;
}

function bpwp_api_get_error_msg($code)
{
    $errors = [
        400 => __('Ошибка в структуре JSON передаваемого запроса', 'bonus-plus-wp'),
        401 => __('Не удалось аутентифицировать запрос. Возможные причины: схема аутентификации или токен указаны неверно; отсутствует заголовок Authorization в запросе;', 'bonus-plus-wp'),
        403 => __('Нет прав на просмотр данного объекта', 'bonus-plus-wp'),
        404 => __('Запрошенный ресурс не существует', 'bonus-plus-wp'),
        412 => __('В процессе обработки запроса произошла ошибка связанная с: некорректными данными в параметрах запроса; невозможностью выполнить данное действие; по каким-то другим причинам', 'bonus-plus-wp'),
        500 => __('При обработке запроса возникла непредвиденная ошибка', 'bonus-plus-wp'),
        204 => __('Товары и категории успешно импортированы', 'bonus-plus-wp'),
        200 => __('ОК!', 'bonus-plus-wp'),
    ];

    return $code && key_exists($code, $errors) ? $errors[$code] : false;
}