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
    }

    /**
     *  Начисляем бонусы клиенту
     *  
     *  @return object /customer/{phoneNumber}/balance https://bonusplus.pro/api/Help/Api/PATCH-customer-phoneNumber-balance
     * 
     */
    public static function bpwp_customer_balance_bonusplus($order_id)
    {
        $order = wc_get_order($order_id);

        // Получим бонусы заказа для этого клиента.
        $amount = self::bpwp_get_order_bonuses($order_id);
        
        if (is_array($amount) && isset($amount['request']) && is_array($amount['request']['discount'])) {
            foreach ($amount['request']['discount'] as $discount) {
                if (isset($discount['cb']) && !empty($discount['cb'])){
                    $sum_bonuses = $discount['cb']; 
                }
            }
        }
        
        $billingPhone  = $order->get_billing_phone();

        // TODO: Сделать проверку, есть ли чел в системе бунус+ (?)
        // $user_bonusplus = self::bpwp_check_user_bonusplus_account($billingPhone)

        $params = [
            'amount' => $sum_bonuses,
        ];

        if (!empty($billingPhone)){
            $addbounuses = bpwp_api_request(
                'customer/'. $billingPhone .'/balance',
                json_encode($params),
                'PATCH',
            );
            $response_code = wp_remote_retrieve_response_code($addbounuses);
        }
        
    }

    public static function bpwp_get_order_bonuses($order_id)
    {
        // Переберем товары в заказе (и суммируем бонусы?)
        $order = wc_get_order($order_id);
        $products = $order->get_items();
        $items = [];

		foreach ($products as $product) {
			$items[] = $product->get_product_id();
		}

        $items = BPWPApiHelper::bpwp_product_to_retailitems($items);

        $store = !empty(get_option('bpwp_shop_name')) ? esc_html(get_option('bpwp_shop_name')) : '';

        $billingPhone = bpwp_api_get_customer_phone();
        $params = [
            'phone'         => $billingPhone,
            'bonusDebit'    => 0.0,
            'level'         => 0,
            'store'         => $store,
            'certificate'   => true
        ];

        $params['items'] = $items;

        // Отправим запрос и получим сумму бонусов
        if (!empty($billingPhone) && !empty($store) && count($items) >= 1){
            $retailcalc = bpwp_api_request(
                'retail/calc',
                json_encode($params),
                'PUT',
            );
            
            $response_code = wp_remote_retrieve_response_code($retailcalc);
            
            return $retailcalc;
        }

	}
}

BPWPCustomerBalance::init();