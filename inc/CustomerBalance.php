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
     */
    public static function bpwp_customer_balance_bonusplus($order_id)
    {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();

        //Проведение продажи в БонусПлюс
        $retail = self::bpwp_get_order_bonuses($order_id);

        // Обновление данных пользователя
        if ($retail['code'] == 200){
            update_user_meta($user_id, 'bonus-plus', $retail['request']['customer']);
        }
    }

    /**
     *  Проведение продажи в БонусПлюс
     *  
     *  @return object POST /retail https=>//bonusplus.pro/api/Help/Api/POST-retail
     * 
     */
    public static function bpwp_get_order_bonuses($order_id)
    {

        $items = self::bpwp_products_to_retail($order_id);

        $store = !empty(get_option('bpwp_shop_name')) ? esc_html(get_option('bpwp_shop_name')) : '';
        
        $billingPhone = bpwp_api_get_customer_phone();

        $params = [
            'phone'         => $billingPhone,
            'bonusDebit'    => 0.0,
            'store'         => $store,
            'externalId'    => $order_id, // должно быть уникальным, это идентификатор продажи из внешней системы
            'certificate'   => true
        ];

        $params['items'] = $items;

        // Отправим запрос "Проведение продажи в БонусПлюс"
        if (!empty($billingPhone) && !empty($store) && count($items) >= 1){
            $retail = bpwp_api_request(
                'retail',
                json_encode($params),
                'POST',
            );
            
            return $retail;
        }

	}

    /**
    * Собираем массив товаров в заказе
    */
    public static function bpwp_products_to_retail($order_id) 
    {
        $order = wc_get_order($order_id);
        $items = $order->get_items();
        $products_data = [];
        $ext = 0;
        
        // Переберем товары в заказе
        foreach ($items as $item_id => $item) {

            $ext++;
            $product_id = $item->get_product_id();
            $product = $item->get_product();
            
            $product_price = $product->get_price();
            $quantity = $item->get_quantity();
            $total = $item->get_total();

            $product_data = [
                "sum"       => (float)$total,
                "qnt"       => (float)$quantity,
                "product"   => $product_id,
                "ds"        => 0.0,
                "ext"       => $order_id.'-'.$ext, //ext - уникальный идентификатор позиции, его нужно делать либо уникальным, либо не заполнять вовсе
                "price"     => (float) $product_price,
            ];

            $products_data[] = $product_data;
        }
        return $products_data;        
    }
}

BPWPCustomerBalance::init();