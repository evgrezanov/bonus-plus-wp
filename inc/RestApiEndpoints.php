<?php

namespace BPWP;

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
        // $check =  wp_verify_nonce($nonce, 'wp_rest');
        // do_action('logger', $check);
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

        $res['code'] = 204;

        // 204 - success
        if ($res['code'] == 204) {
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
    public function bpwp_customer_checkcode(\WP_REST_Request $request)
    {

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

        if ($res['code'] == 204) {
            $response = array(
                'success' => true,
                'message' => 'Код принят',
            );

            // TODO: Добавить запрос проверки существования пользвателя в б+
            // Если 204 - успех, создаем клиента: запрос POST /customer, phone обязательно
            $customer = bpwp_api_request(
                'customer',
                array(
                    'phone' => $args['phone']
                ),
                'POST'
            );

            do_action('logger', $customer);

            if ($customer['code'] == 200) {
                $response = array(
                    'success' => true,
                    'message' => 'Пользователь добавлен',
                );


                $customer_info_html = bpwp_api_get_customer_phone();
                do_action('logger', $customer_info_html, 'warning');
            }

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

}

BPWPRestApiEndpoints::get_instance();