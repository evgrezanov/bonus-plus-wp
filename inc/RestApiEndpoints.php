<?php

namespace BPWP;

defined('ABSPATH') || exit; // Exit if accessed directly

class BPWPRestApiEndpoints
{

    private static $ins = null;

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_endpoints'], 10);
        // Early enable customer WC_Session
        //add_action( 'init', [$this,'wc_session_enabler'] ); //https://rajaamanullah.com/how-to-use-woocommerce-sessions-and-cookies/
    }
    
    public static function wc_session_enabler() {
        if ( is_user_logged_in() || is_admin() )
            return;
    
        if ( isset(WC()->session) && ! WC()->session->has_session() ) {
            WC()->session->set_customer_session_cookie( true );
        }
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
                ),
                'debit' => array(
                    'type' => 'integer',
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
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

        $phone = bpwp_api_get_customer_phone($user_id); // Получаем телефон у пользователя.

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

        //$phone = bpwp_api_get_customer_phone($user_id);
        // ? Проверить, если нет телефона и кода, то возвращаем ошибку
        $args = array(
            'phone' => $request->get_param('phone'),
            'code' => $request->get_param('code'),
            'debit' => $request->get_param('debit')
        );

        // customer/$phone/checkCode/$code
        $res = bpwp_api_request(
            'customer/' . $args['phone'] . '/checkCode/' . $args['code'],
            array(),
            'PUT'
        );
        
        // Cоздаем клиента или резервируем бонусы
        if ($res['code'] == 204) {
            $response = array(
                'success' => true,
                'message' => 'Код принят',
            );
            
            // *! Передаем количество бонусов
            if ($args['debit'] > 0) {
                
                update_user_meta($user_id, 'bpwp_debit_bonuses', esc_attr($args['debit']));

                $response = array(
                    'success' => true,
                    'message' => 'Списание бонусов',
                    'debit_bonuses' => true,
                );
                
                wp_send_json($response);
                wp_die();
            }

            // Код верный. Запрос проверки существования пользвателя в б+
            $get_customer = bpwp_api_request(
                'customer',
                array(
                    'phone' => $args['phone'],
                ),
                'GET',
            );

            // Если такой номер существует, обновляем мета и редиректим
            if ($get_customer['code'] == 200) {
                
                update_user_meta($user_id, 'bonus-plus', $get_customer['request']);
                $response = array(
                    'success' => true,
                    'message' => 'Пользователь уже существует!',
                    'customer_created' => true, // проверим и редиректим на /my-account/bonus-plus/
                );
                wp_send_json($response);
                wp_die();
            }
            
            // Добавляем пользователя в б+
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

        } else {
            // 412 - ошибка по разным причинам, обработать. получить message из msg
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