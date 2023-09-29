<?php

namespace BPWP;

defined('ABSPATH') || exit; // Exit if accessed directly

class BPWPCustomerBalance
{

    /**
     *  Init
     */
    public static function init()
    {
        // Заказ выполнен, запрос с начислением бонусов. Комментарий в заказ - "бонусы начисены"
        add_action('woocommerce_order_status_completed', [__CLASS__, 'bpwp_customer_balance_bonusplus']);


        // add_action('woocommerce_before_add_to_cart_quantity', [__CLASS__, 'bpwp_single__bonusplus_price']);
        // add_action('woocommerce_before_cart_totals', [__CLASS__, 'bpwp_single__bonusplus_price']);
        // add_action('woocommerce_checkout_before_order_review', [__CLASS__, 'bpwp_single__bonusplus_price']);
        // add_action('woocommerce_after_shop_loop_item', [__CLASS__, 'bpwp_single__bonusplus_price']);
    }


    /**
     *  Начисляем бонусы клиенту
     *  
     *  @return object /customer/{phoneNumber}/balance https://bonusplus.pro/api/Help/Api/PATCH-customer-phoneNumber-balance
     * 
     */
    public static function bpwp_customer_balance_bonusplus($order_id)
    {
        // Получим бонусы для этого клиента. Из мета заказа(?)
        $amount = BPWPApiHelper::bpwp_get_calc_bonusplus_price();

        $store = !empty(get_option('bpwp_shop_name')) ? esc_html(get_option('bpwp_shop_name')) : '';

        $billingPhone = bpwp_api_get_customer_phone();
        do_action('logger', $amount);
        
        $params = [
            'amount' => 22,
        ];

        if (!empty($billingPhone) && !empty($amount)){
            $retailcalc = bpwp_api_request(
                'customer/'. $billingPhone .'/balance',
                json_encode($params),
                'PATCH',
            );
            
            $response_code = wp_remote_retrieve_response_code($retailcalc);
            
            do_action('logger', $response_code);
            
        }
        
    }

}

BPWPCustomerBalance::init();