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
        // Создание заказа - Резервирование бонусов на счету клиента
        add_action( 'woocommerce_new_order', [__CLASS__, 'bpwp_balance_reserve_bonusplus'], 10, 2);

        // Заказ выполнен, запрос с начислением бонусов. Комментарий в заказ - "бонусы начисены"
        add_action('woocommerce_order_status_completed', [__CLASS__, 'bpwp_customer_balance_bonusplus']);
       
    }

    /**
     *  Резервируем бонусы
     */
    public static function bpwp_balance_reserve_bonusplus($order_id, $order)
    {
        $user_id = $order->get_user_id();
                
        // TODO Получить бонусы для списания и добавить в мета заказа
        $data = BPWPApiHelper::bpwp_get_calc_bonusplus_price();

        if (is_array($data) && isset($data['request'])) {
        $bonus_debit = $data['request']['maxDebitBonuses'];
        }

        // Запрос Резервируем бонусы
        /*
        https://bonusplus.pro/api/Help/Api/PATCH-customer-phoneNumber-balance-reserve
        */

        // Резервируем бонусы, передаем положительное число

        $order_data = array(
            'billing_phone' => bpwp_api_get_customer_phone($user_id),
            'order_id' => $order_id,
            'bonus_debit' => $bonus_debit,
        );
        
        $balance_reserve = self::bpwp_balance_reserve($order_data);

        
        $info = bpwp_api_get_customer_data();
        
        if ($info && is_array($info)) {
            $info['availableBonuses'] = $info['availableBonuses'] - $bonus_debit;
            
            update_user_meta($user_id, 'bonus-plus', $info);
        }
        
        
        //TODO В инфо заказа фильтр, начислено бонусов

        /*
        bonus-plus [
            'availableBonuses' => 404.0
        ]
        */
        // Обновление данных заказа
        // if ($balance_reserve['code'] == 200){
        //     update_order_meta($order_id, 'bonus_debit', $bonus_debit);
            
        //     //update_user_meta($order_id, 'bonus_debit', $balance_reserve['request']['customer']);
        // }

        add_post_meta( $order_id, '_bonus_debit', $bonus_debit );

        //if ($deduction == 'true') { // TODO Если пользователь подтвердил списание бонусов
            
            // Добавим Скидку
			$item_id = wc_add_order_item( $order_id, array(
				'order_item_name' => 'Списание бонусов _completed',
				'order_item_type' => 'fee'
				) );
				
				wc_add_order_item_meta( $item_id, '_line_total', wc_format_decimal( -$bonus_debit) );
			
            $order->calculate_totals();
            $order_id = $order->save();

        //} End if

    }

    /**
    *  Запрос Резервируем бонусы
    */
    public static function bpwp_balance_reserve($order_data)
    {
        if (!empty($order_data)) {

            $params = array(
                'id'=> $order_data['order_id'],
                'amount'=> $order_data['bonus_debit'],
            );

            $balance_reserve = bpwp_api_request(
                '/customer/'. $order_data['billing_phone'] .'/balance/reserve',
                wp_json_encode($params),
                'PATCH',
            );
            
            return $balance_reserve;
        }

    }
    

    /**
    *  Начисляем бонусы клиенту
    */
    public static function bpwp_customer_balance_bonusplus($order_id)
    {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();
        $bonus_debit = $order->get_meta( '_bonus_debit' );
        
        if (isset($bonus_debit) || !empty($bonus_debit)) {
            
            $order_data = array(
                'billing_phone' => bpwp_api_get_customer_phone($user_id),
                'order_id' => $order_id,
                'bonus_debit' => $bonus_debit * -1,
            );
            
            // Освобождаем из резерва, передаем отрицательное число
            $balance_reserve = self::bpwp_balance_reserve($order_data);
        }
        
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
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();

        $bonus_debit = $order->get_meta( '_bonus_debit' );
        
        if (!isset($bonus_debit) || empty($bonus_debit)) {
            $bonus_debit = 0.0;
        }
        
        $items = self::bpwp_products_to_retail($order_id);

        $store = !empty(get_option('bpwp_shop_name')) ? esc_html(get_option('bpwp_shop_name')) : '';
        
        $billingPhone = bpwp_api_get_customer_phone($user_id);

        $params = [
            'phone'         => $billingPhone,
            'bonusDebit'    => $bonus_debit, // вставить переменную со значением бонусов для списания
            'store'         => $store,
            'externalId'    => $order_id, // должно быть уникальным, это идентификатор продажи из внешней системы
        ];

        $params['items'] = $items;

        // Отправим запрос "Проведение продажи в БонусПлюс"
        if (!empty($billingPhone) && !empty($store) && count($items) >= 1){
            $retail = bpwp_api_request(
                'retail',
                wp_json_encode($params),
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

            $categories = wp_get_post_terms($product_id, 'product_cat');
            $category_id = null;

            if (!empty($categories)) {
                // Если у товара есть категории, выбираем категорию нижнего уровня
                foreach ($categories as $category) {
                    if ($category->parent == 0) {
                        $category_id = $category->term_id;
                        break;
                    }
                }

                if (!$category_id) {
                    $category_id = $categories[0]->term_id;
                }
            }

            $product_data = [
                "sum"       => (float)$total,
                "qnt"       => (float)$quantity,
                "product"   => $product_id,
                "ds"        => 0.0,
                "ext"       => $order_id.'-'.$ext, //ext - уникальный идентификатор позиции, его нужно делать либо уникальным, либо не заполнять вовсе
                "price"     => (float) $product_price
            ];

            $products_data[] = $product_data;
        }
        
        return $products_data;        
    }

}

BPWPCustomerBalance::init();