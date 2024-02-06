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
            'body'        => wp_json_encode($params),
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
    
    do_action('logger', $url);
    do_action('logger', $args,'warning');
    
    $request = wp_remote_request($url, $args);

    $response_code = wp_remote_retrieve_response_code($request);
    
    if (is_wp_error($request)) {
        $response['code'] = $request->get_error_code();
        $response['message'] = $request->get_error_message();
        $response['request'] = $request;
        $response['class'] = 'notice notice-error';
    }

    $response['code'] = $response_code;
    //$response['message'] = bpwp_api_get_error_msg($response_code);

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

/* Редактируем поля на странице оформления заказа 
** Отключаем ненужные поля в форме заказа
** Меняем порядок вывода
** Редактируем текст внутри полей
*/
//add_filter('woocommerce_checkout_fields', 'custom_override_checkout_fields', 9999);
function custom_override_checkout_fields($fields)
{

	$fields['order']['order_comments']['placeholder'] = 'Расскажите о пожеланиях к заказу или как улучшить работу сайта'; // Примечеания к заказу
	$fields['billing']['billing_first_name']['class'][0] = 'form-row-wide'; // Поле Имя на всю ширину
	$fields['billing']['billing_email']['class'][0] = 'form-row-first'; // Поле email 50%
	$fields['billing']['billing_phone']['class'][0] = 'form-row-last'; // Поле Тел 50%
	unset($fields['billing']['billing_first_name']['label']);
	unset($fields['billing']['billing_email']['label']);
	unset($fields['billing']['billing_phone']['label']);

	$fields['billing']['billing_first_name']['placeholder'] = 'Имя';
	$fields['billing']['billing_email']['placeholder'] = 'Email';
    $fields['billing']['billing_phone']['placeholder'] = '+7 (999) 999-99-99'; //Телефон
	
	// Вставляем телефон пользователя в поле
    if ( is_user_logged_in() ) {
        $user_id = get_current_user_id();

        // Получаем значение поля "Телефон" пользователя
        $user_phone = get_user_meta( $user_id, 'billing_phone', true );

        // Проверяем, заполнено ли поле "Телефон" у пользователя
        if ( ! empty( $user_phone ) ) {
            // Задаем значение по умолчанию для поля "Телефон"
            $fields['billing']['billing_phone']['default'] = $user_phone;
        }
    } else {
	$fields['billing']['billing_phone']['placeholder'] = '+7 (999) 999-99-99'; //Телефон
	}
	
	unset($fields['billing']['billing_last_name']); //Фамилия

	unset($fields['billing']['billing_city']); //Населенный пункт
	unset($fields['billing']['billing_state']); //Область/Регион
	unset($fields['billing']['billing_company']); //Название компании
	unset($fields['billing']['billing_address_1']); //Адрес
	unset($fields['billing']['billing_address_2']); //Подъезд этаж и т.п.
	unset($fields['billing']['billing_postcode']); //Почтовый индекс
	unset($fields['billing']['billing_country']); //Страна

	// remove shipping fields
	unset($fields['shipping']['shipping_first_name']);
	unset($fields['shipping']['shipping_last_name']);
	unset($fields['shipping']['shipping_company']);
	unset($fields['shipping']['shipping_address_1']);
	unset($fields['shipping']['shipping_address_2']);
	unset($fields['shipping']['shipping_city']);
	unset($fields['shipping']['shipping_postcode']);
	unset($fields['shipping']['shipping_country']);
	unset($fields['shipping']['shipping_state']);

	unset($fields['order']['order_comments']); //Примечания к заказу

	// Порядок полей
	$fields["billing"]["billing_first_name"]["priority"] = 10;
	$fields["billing"]["billing_email"]["priority"] = 20;
	$fields["billing"]["billing_phone"]["priority"] = 30;

	return $fields;
}