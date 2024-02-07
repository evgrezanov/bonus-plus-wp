<?php

namespace BPWP;

defined('ABSPATH') || exit; // Exit if accessed directly

class BPWPRestApiEndpoints
{

    private static $ins = null;

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_endpoints'], 10);
    }

    /**
     * @return BPWPRestApiEndpoints|null
     */
    public static function get_instance()
    {
        if (null === self::$ins) {
            self::$ins = new self;
        }

        return self::$ins;
    }

    // Регистрация эндпоинтов для отправки SMS и проверки полученного кода
    public function register_endpoints()
    {
        register_rest_route('wp/v1', '/sendcode', array(
            'methods' => 'POST',
            'callback' => array($this, 'bpwp_customer_sendcode'),
            'permission_callback' => array($this, 'verify_wp_nonce'),
        ));

        register_rest_route('wp/v1', '/checkcode', array(
            'methods' => 'POST',
            'callback' => array($this, 'bpwp_customer_checkcode'),
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
            'permission_callback' => array($this, 'verify_wp_nonce'),
        ));
    }

    /**
     * @return int|false
     */
    public function verify_wp_nonce($request)
    {
        $nonce = $request->get_header('X-WP-Nonce');
        return wp_verify_nonce($nonce, 'wp_rest');
    }

    // Отправляет проверочный код на номер телефона клиента посредством смс-сообщения
    public function bpwp_customer_sendcode(\WP_REST_Request $request)
    {

        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
        }

        $phone = bpwp_api_get_customer_phone($user_id); // Получаем тот же телефон у пользователя.

        $res = bpwp_api_request(
            'customer/' . $phone . '/sendCode',
            array(),
            'PUT'
        );
        
        // 204 - success
        if ($res['code'] == 204) {
            $response = array(
                'success' => true,
                'message' => 'Код отправлен',
            );
        } else {
            $response = array(
                'success' => false,
                'message' => 'Код не отправлен!',
            );
        }

        wp_send_json($response);
        wp_die();
    }

    // Проверяет код, отправленный на номер телефона клиента
    public function bpwp_customer_checkcode(\WP_REST_Request $request)
    {
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
        }

        // ? Проверить, если нет телефона и кода, то возвращаем ошибку
        $args = array(
            'phone' => $request->get_param('phone'),
            'code' => $request->get_param('code')
        );

        // customer/$phone/checkCode/$code
        $res = bpwp_api_request(
            'customer/' . $args['phone'] . '/checkCode/' . $args['code'],
            array(),
            'PUT'
        );
        
        do_action('logger', $res);

        // Если 204 - успех, создаем клиента: запрос POST /customer, phone обязательно
        if ($res['code'] == 204) {
            $response = array(
                'success' => true,
                'message' => 'Код принят',
            );
            
            // TODO: Добавить запрос проверки существования пользвателя в б+
            $get_customer = bpwp_api_request(
                'customer',
                array(
                    'phone' => $args['phone'],
                ),
                'GET',
            );
            
            if ($get_customer['code'] == 200) {
                $response = array(
                    'success' => false,
                    'message' => 'Ошибка. Пользователь уже существует!',
                );
                wp_send_json($response);
                wp_die();
            }

            $customer = bpwp_api_request(
                'customer',
                wp_json_encode( array(
                    'phone' => $args['phone']
                )),
                'POST'
            );
            
            if ($customer['code'] == 200) {
                update_user_meta($user_id, 'bonus-plus', $customer['request']);
                
                $response = array(
                    'success' => true,
                    'message' => 'Пользователь добавлен',
                    'customer_created' => true, // проверим и редиректим на /my-account/bonus-plus/
                );
            } else {
                do_action(
                    'bpwp_logger',
                    $type = __CLASS__,
                    $title = __('Ошибка при получении данных клиента', 'bonus-plus-wp'),
                    $desc = sprintf(__('У пользователя с ИД %s, данные не получены!', 'bonus-plus-wp'), $user_id),
                ); 
            }
            
        // 412 - ошибка по разным причинам, обработать. получить message из msg
        } else {
            $response = array(
                'success' => false,
                'message' => 'Код не верный!',
            );
        }

        wp_send_json($response);
        wp_die();
    }

}

BPWPRestApiEndpoints::get_instance();