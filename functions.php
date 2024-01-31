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

	//$fields['order']['order_comments']['label'] = 'Комментарии';
	//$fields['order']['order_comments']['class'][0] = 'order_comments_title';
	//$fields['order']['billing_phone']['class'][0] = 'tel';
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
	
	//$fields['billing']['billing_state']['label'] = 'Регион';
	//$fields['billing']['billing_state']['placeholder'] = 'Выберите регион доставки';
	//$fields['billing']['billing_state']['priority'] = 99;
	//$fields['billing']['billing_email']['priority'] = 4;

	//unset($fields['billing']['billing_first_name']); //Имя
	unset($fields['billing']['billing_last_name']); //Фамилия
	//unset($fields['billing']['billing_phone']); //Телефон
	//unset($fields['billing']['billing_email']); //Емейл

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

	//unset($fields['account']['account_username']);
	//unset($fields['account']['account_password']);
	//unset($fields['account']['account_password-2']);

	// Порядок полей
	$fields["billing"]["billing_first_name"]["priority"] = 10;
	$fields["billing"]["billing_email"]["priority"] = 20;
	$fields["billing"]["billing_phone"]["priority"] = 30;

	return $fields;
}


// TODO: перенести в WooAccount.php
//add_action('rest_api_init', 'bpwp_register_get_customer_endpoint', 10);
add_action('rest_api_init', 'bpwp_customer_endpoints', 20);

// Регистрация эндпоинтов для отправки SMS и проверки полученного кода

function bpwp_customer_endpoints() {

    register_rest_route('wp/v1', '/sendcode', array(
        'methods' => 'POST',
        'callback' => 'bpwp_customer_sendcode',
        //'permission_callback' => '__return_true', // разрешить все
        'permission_callback' => 'verify_wp_nonce',
        
    ));

    register_rest_route('wp/v1', '/checkcode', array(
        'methods' => 'POST',
        'callback' => 'bpwp_customer_checkcode',
        'args' => array(
            'phone' => array(
                'type' => 'string',
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field'
            ),
            'code' => array(
                'type' => 'integer',
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field'
            )
        ),
        'permission_callback' => 'verify_wp_nonce',
    ));

    register_rest_route('wp/v1', '/customercreate', array(
        'methods' => 'POST',
        'callback' => 'bpwp_customer_create',
        'args' => array(
            'phone' => array(
                'type' => 'string',
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field'
            )
        ),
        'permission_callback' => 'verify_wp_nonce',
    ));
    
}

function verify_wp_nonce($request) {
		$nonce = $request->get_header('X-WP-Nonce');
        // $check =  wp_verify_nonce($nonce, 'wp_rest');
        // do_action('logger', $check);
        return wp_verify_nonce($nonce, 'wp_rest');
}

function bpwp_customer_create(WP_REST_Request $request) {

    $phone = $request->get_param( 'phone' );

    // if (is_user_logged_in()) {
    //     $user_id = get_current_user_id();
    // }

    // $phone = bpwp_api_get_customer_phone($user_id); // Получаем тот же телефон у пользователя.
    
    // // Написать правильный запрос
    $customer = bpwp_api_request(
        'customer',
        array(
            'phone' => $phone,
        ),
        'POST'
    );

    do_action('logger', $customer, 'error');

    // 204 - success
    /*
    if ($res['code'] == 200){
        $response = array(
            'success' => true,
            'message' => 'Код отправлен',
        );
    } else {
        wp_send_json(
            array(
                'success' => false,
                'message' => 'Код не отправлен!',
            )
        );
        wp_die();
    }
    */

    wp_send_json(
        array(
            'success' => true,
            'message' => 'Пользователь добавлен!',
        )
    );
    wp_die();

    //wp_send_json($response);
    //wp_die();
}

// Отправляет проверочный код на номер телефона клиента посредством смс-сообщения
function bpwp_customer_sendcode(WP_REST_Request $request) {

    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
    }

    $phone = bpwp_api_get_customer_phone($user_id); // Получаем тот же телефон у пользователя.
    
    // Написать правильный запрос
    $res = bpwp_api_request(
        'customer/'.$phone.'/sendCode',
        array(),
        'PUT'
    );

    // 204 - success
    if ($res['code'] == 204){
        $response = array(
            'success' => true,
            'message' => 'Код отправлен',
        );
    } else {
        wp_send_json(
            array(
                'success' => false,
                'message' => 'Код не отправлен!',
            )
        );
        wp_die();
    }

    wp_send_json($response);
    wp_die();
}

// Проверяет код, отправленный на номер телефона клиента
function bpwp_customer_checkcode(WP_REST_Request $request) {

    // ? Проверить, если нет телефона и кода, то возвращаем ошибку
    $args = array(
        'phone' => $request->get_param( 'phone' ),
        'code' => $request->get_param( 'code' )
    );
    
    // customer/$phone/checkCode/$code
    $res = bpwp_api_request(
        'customer/'. $args['phone'] .'/checkCode/'. $args['code'],
        array(),
        'PUT'
    );

    if ($res['code'] == 204){
        $response = array(
            'success' => true,
            'message' => 'Код принят',
        );
        
        // TODO: Добавить запрос проверки существования пользвателя в б+
        // Если 204 - успех, создаем клиента: запрос POST /customer, phone обязательно
        // $customer = bpwp_api_request(
        //     'customer',
        //     array(
        //         'phone' => $args['phone']
        //     ),
        //     'POST'
        // );

        //do_action('logger', $customer);
        
        // $customer_info_html = bpwp_api_get_customer_phone();
        // do_action('logger', $customer_info_html, 'warning');

    // 412 - ошибка по разным причинам, обработать. получить message из msg
    } else {
        wp_send_json(
            array(
                'success' => false,
                'message' => 'Код не верный!',
            )
        );
        wp_die();
    }

    wp_send_json($response);
    wp_die();
}

// Регистрация эндпоинта для GET /customer
function bpwp_register_get_customer_endpoint() {
    
    register_rest_route('wp/v1', '/getcustomer', array(
        'methods' => 'GET',
        'callback' => 'bpwp_endpoint_get_customer',
        //'permission_callback' => '__return_true', // разрешить все
        'permission_callback' => function($request) { // Функция проверки nonce
            //$params= $request->get_params();
            $nonce = $request->get_header('X-WP-Nonce');
            return wp_verify_nonce($nonce, 'wp_rest');
        }
    ));
}

// Функция обработки запроса GET customer
function bpwp_endpoint_get_customer() {

    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
    }
    // delete_user_meta($user_id, 'bonus-plus');
    // Check billing_phone 
    $phone = bpwp_api_get_customer_phone($user_id);

    $res = bpwp_api_request(
        'customer',
        array(
            'phone' => $phone
        ),
        'GET'
    );

    if ($res['code'] == 200){
        // Обновляем мета пользователя
        update_user_meta($user_id, 'bonus-plus', $res['request']);
        $user_info = account_info();
    } else {
        $user_info = '';
        // echo 'Регистрация в системе';
        // // TODO: Отправка SMS
        // // Пишеем Ендпоинт для СМС, а функуию испльзуем сразу
        // // $user_info =
        // do_action(
        //     'bpwp_logger',
        //     $type = __CLASS__,
        //     $title = __('Ошибка при получении данных клиента', 'bonus-plus-wp'),
        //     $desc = sprintf(__('У пользователя с ИД %s, данные не получены!', 'bonus-plus-wp'), $user_id),
        // ); 
    }
    

	// Если не сущ в б+ - делаем проверку по смс. Если пройдено - создаем нового и получаем GET /customer (на основании его генерим QR код), Добавляем в мета
	// Если сущ, то делаем проверку по смс. Если пройдено - получаем GET /customer (на основании его генерим QR код), обновляем мета 
	
    
    // Возвращаем данные в формате JSON
    //return wp_json_encode($res);
    return ($user_info); // возвращаяем HTML
}

function account_info()
{
    $info = bpwp_api_get_customer_data();
    if ($info && is_array($info)) {
        
        $output = sprintf('<h2>%s</h2>', 'Информация по карте лояльности');
        
        
        foreach ($info as $key => $value) {
            if ($key != 'person') {
                if ($key == 'discountCardNumber') {
                    $output .= sprintf('<br /><div id="qrcode" data-card="%s"></div><br/>', esc_html($value));
                    $output .= sprintf('%s:%s<br />', esc_html(__('Номер карты', 'bonus-plus-wp')), esc_html($value));
                }
                if ($key == 'discountCardName') {
                    $output .= sprintf('%s:%s<br />', esc_html(__('Тип карты', 'bonus-plus-wp')), esc_html($value));
                }
                if ($key == 'availableBonuses') {
                    $output .= sprintf('%s:%s<br />', esc_html(__('Доступных бонусов', 'bonus-plus-wp')), esc_html($value));
                }
                if ($key == 'notActiveBonuses') {
                    $output .= sprintf('%s:%s<br />', esc_html(__('Неактивных бонусов', 'bonus-plus-wp')), esc_html($value));
                }
                if ($key == 'purchasesTotalSum') {
                    $output .= sprintf('%s:%s<br />', esc_html(__('Сумма покупок', 'bonus-plus-wp')), esc_html($value));
                }
                if ($key == 'purchasesSumToNextCard') {
                    $output .= sprintf('%s:%s<br />', esc_html(__('Сумма покупок для смены карты', 'bonus-plus-wp')), esc_html($value));
                }
                if ($key == 'lastPurchaseDate') {
                    $output .= sprintf('%s:%s<br />', esc_html(__('Последняя покупка', 'bonus-plus-wp')), esc_html($value));
                }
            } else {
                $person_data = $value;
                $person = array();
                foreach ($person_data as $pkey => $pvalue) {
                    if ($pkey == 'ln' || $pkey == 'fn' || $pkey == 'mn') {
                        $person[$pkey] = $pvalue;
                    }
                }
                if (!empty($person)) {
                    $owner = $person['ln'] . ' ' . $person['fn'] . ' ' . $person['mn'];
                    if (!empty($owner)) {
                        $output .= sprintf('%s:%s<br />', esc_html(__('Держатель', 'bonus-plus-wp')), esc_html($owner));
                    }
                }
            }
        }

    } else { // нет данных в бонус+ 
        do_action('logger', 'Нет данных!');
        //do_action('bpwp_veryfy_client_data');
    }

    return $output;
}
/*
Пример вызова
JS
jQuery.get('/wp-json/wp/v1/example', function(response) {
    // Обработка полученных данных
    console.log(response);
});

PHP
$response = wp_remote_get('https://example.com/wp-json/wp/v1/example');
if (is_wp_error($response)) {
    // Обработка ошибки
    $error_message = $response->get_error_message();
    echo "Ошибка: $error_message";
} else {
    $data = wp_remote_retrieve_body($response);
    $decoded_data = json_decode($data, true);
    if ($decoded_data) {
        // Обработка полученных данных
        echo "Сообщение: " . $decoded_data['message'];
        echo "Временная метка: " . $decoded_data['timestamp'];
    } else {
        // Обработка ошибки декодирования JSON
        echo "Ошибка декодирования JSON";
    }
}
*/