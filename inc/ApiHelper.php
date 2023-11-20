<?php

namespace BPWP;

defined('ABSPATH') || exit; // Exit if accessed directly

class BPWPApiHelper
{

    /**
     *  Init
     */
    public static function init()
    {
        add_action('woocommerce_before_cart_totals', [__CLASS__, 'bpwp_cart_checkout_bonusplus_price']);
        add_action('woocommerce_checkout_before_order_review', [__CLASS__, 'bpwp_cart_checkout_bonusplus_price']);
        add_action('woocommerce_short_description', [__CLASS__, 'bpwp_single_product_bonusplus_price'], 10, 1);
        //add_action('woocommerce_checkout_order_processed', [__CLASS__, 'bpwp_woocommerce_checkout_order_processed_action'], 10, 3);
        //add_filter('woocommerce_update_cart_action_cart_updated', [__CLASS__, 'on_action_cart_updated']);
    }

    public static function on_action_cart_updated( $cart_updated ) {

        do_action('logger', $cart_updated);
        
        $new_value = 222;

        WC()->cart->set_total( $new_value );
        
        return $cart_updated;
    }

    /**
     *  Возвращает товары в формате RetailItem по ИД
     * 
     *  @param array $product_ids ИД товаров по которым необходимо расчитать чек и количество товаров
     * 
     *  @return object RetailItem для переданных товаров https://bonusplus.pro/api/Help/ResourceModel?modelName=RetailItem
     */
    public static function bpwp_product_to_retailitems ($product_ids) 
    {

        $products_data = [];
        $ext = 0;

        foreach ($product_ids as $product_item) {

            $product = wc_get_product($product_item['id']);
            $quantity = $product_item['quantity'];
            
            if ($product) {
                $ext++;
                $product_name = $product->get_name();
                $product_price = $product->get_price();

                $categories = wp_get_post_terms($product_item['id'], 'product_cat');
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
                $sum = $product_price * $quantity;

                $product_data = [
                    "sum"       => (float) $sum,
                    "qnt"       => (float) $quantity,
                    "product"   => $product_item['id'],
                    "ds"        => 0.0,
                    "ext"       => $ext,
                    "price"     => (float) $product_price,
                ];

                
                $products_data[] = $product_data;
            }
            
        }
        return $products_data;
        
    }

    /**
     *  Возвращает информацию о скидках/бонусах которые будут применены для каждой позиции чека
     *  
     *  @return object CalcResult https://bonusplus.pro/api/Help/ResourceModel?modelName=CalcResult
     * 
     *  TODO: дописать обработку массива
     */
    public static function bpwp_get_calc_bonusplus_price()
    {
        global $product;
        $items = [];

        // Если находимся на странице товара
        if (is_product() && $product) {
            $items[] = $product->get_id();
            $quantity = 1;
            $items[] = array(
                'id'        => $product->get_id(),
                'quantity'  => $quantity
            );
        }

        // Если находимся в корзине
        if (is_cart() || is_checkout()) {
            $cart = WC()->cart;
            $cart_items = $cart->get_cart();
            foreach ($cart_items as $cart_item_key => $cart_item) {
                $items[] = array(
                    'id'        => $cart_item['product_id'],
                    'quantity'  => $cart_item['quantity']
                );
            }
        }

        $items = self::bpwp_product_to_retailitems($items);

        $store = !empty(get_option('bpwp_shop_name')) ? esc_html(get_option('bpwp_shop_name')) : '';

        $billingPhone = bpwp_api_get_customer_phone();
        $params = [
            'phone'         => $billingPhone,
            'bonusDebit'    => 0.0,
            'level'         => 0,
            'store'         => $store,
        ];
        
        $params['items'] = $items;

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

    /**
     *  Рендерит информацию о бонусах по чеку
     * 
     *  @param object $data данные о бонусах
     * 
     *  @return string HTML данные о скидках по чеку
     */
    public static function bpwp_render_calc_bonusplus_price($data) {
        
        $output = '';
        
        if (isset($_GET['testrequest'])) {
            
            $output .= '<ul>';
        
            if (is_array($data) && isset($data['request']) && isset($data['request']['discount'])) {

                foreach ($data['request']['discount'] as $discount) {
                    $output .= '<li>';
                    $output .= '<strong>ext:</strong> ' . $discount['ext'] . '<br>';

                    if (is_array($discount['messages']) && !empty($discount['messages'])) {
                        $output .= '<strong>messages:</strong> <ul>';
                        foreach ($discount['messages'] as $message) {
                            $output .= '<li>' . $message . '</li>';
                        }
                        $output .= '</ul>';
                    }
                
                    $output .= '<strong>cb:</strong> ' . $discount['cb'] . ' - Сумма бонусов, которые будут начислены на данную позицию<br>';
                    $output .= '<strong>db:</strong> ' . $discount['db'] . ' - Сумма бонусов, которые будут списаны для данной позиции<br>';
                    $output .= '<strong>ds:</strong> ' . $discount['ds'] . ' - Общая сумма скидки для позиции (без учета бонусов)<br>';
                    $output .= '<strong>dp:</strong> ' . $discount['dp'] . ' - Процент скидки для позиции (без учета бонусов)<br>';
                    $output .= '<strong>ids:</strong> ' . $discount['ids'] . ' - Сумма скидки, примененная на стороне БонусПлюс для данной позиции (внутренняя скидка)<br>';
                    $output .= '<strong>idp:</strong> ' . $discount['idp'] . ' - Процент скидки, примененный на стороне БонусПлюс для данной позиции (внутренняя скидка)<br>';
                    $output .= '<strong>dbp:</strong> ' . $discount['dbp'] . ' - Процент списания бонусов<br>';
                    $output .= '<strong>cbp:</strong> ' . $discount['cbp'] . ' - Процент начисления бонусов<br>';
                    $output .= '</li>';
                }
            } else {
                $output .= '<li>Invalid data format</li>';
            }
        
            $output .= '</ul>';
            $maxDebitBonuses = $data['request']['maxDebitBonuses'];
            $multiplicityDebitBonus = $data['request']['multiplicityDebitBonus'];
            $output .= '<strong>maxDebitBonuses:</strong> ';
            $output .= $maxDebitBonuses . '<br>';
            $output .= '<strong>multiplicityDebitBonus:</strong> ';
            $output .= $multiplicityDebitBonus . '<br>';
        
        }

        
        $info = bpwp_api_get_customer_data();
        if ($info && is_array($info)) {

            $available_bonuses = $info['availableBonuses'];
            
            $maxDebitBonuses = 0;

            if (is_array($data) && isset($data['request']) && isset($data['request']['discount'])) {
                
                $bonuses = 0;

                foreach ($data['request']['discount'] as $discount) {
                    if (isset($discount['cb']) && !empty($discount['cb'])){
                        $bonuses = $bonuses + $discount['cb'];
                    }
                }

                $maxDebitBonuses = $data['request']['maxDebitBonuses'];

            }

            $output .= '<div class="bonus-plus-price">';
            $output .= '<p>Ваш бонусный баланс '. $available_bonuses .'.</p><p>Сумма бонусов, которые будут начислены '. $bonuses .'.</p><p> На эту покупку будет списано '. $maxDebitBonuses .' бон.</p>'; 
            $output .= '</div>';
            
        }

        echo $output;
    }

    public static function bpwp_render_retailitems_calc($data)
    {
        if (is_array($data) && isset($data['request']) && is_array($data['request']['discount'])) {
            
            $output = '<div class="bonus-plus-price">';
            foreach ($data['request']['discount'] as $discount) {
                $output .= '<ul>';
                if (isset($discount['cb']) && !empty($discount['cb'])){
                    $output .= '<li> +' . $discount['cb'] . ' бонусов</li>';
                }

                if (isset($discount['db']) && !empty($discount['db'])){
                    $output .= '<li> -' . $discount['db'] . ' бонусов</li>';
                }
                $output .= '</ul>';
            }
            $output .= '</div>';

            echo $output;
        }
    }

    /**
     *  Выводим информацию по бонусам
     */
    public static function bpwp_single__bonusplus_price(){
        $price_data = self::bpwp_get_calc_bonusplus_price();
        echo self::bpwp_render_retailitems_calc($price_data);
    }

    /**
     * 
     */
    public static function bpwp_single_product_bonusplus_price($post_excerpt){
        $content = '';
        
        if (is_product()) {
            $price_data = self::bpwp_get_calc_bonusplus_price();
            $content = $post_excerpt . self::bpwp_render_retailitems_calc($price_data);
        }

        return $content;
    }

    /**
     * 
     */
    public static function bpwp_cart_checkout_bonusplus_price(){
        $content = '';
        if (is_cart()) {
            $price_data = self::bpwp_get_calc_bonusplus_price();
            $content = self::bpwp_render_retailitems_calc($price_data);
        }
        
        // Выводим бонусы, доступные для списания.
        if (is_checkout()) {
            $price_data = self::bpwp_get_calc_bonusplus_price();

            //$order = wc_get_checkout()->get_order();
            //$order_id = $order->get_id();
            //$total = $order->get_total();
            //do_action('logger', $cart);
            
            
            $content = self::bpwp_render_calc_bonusplus_price($price_data);
            
            WC()->cart->add_fee( 'sale', 34, false);
        }
        
        return $content;
    }

    public static function bpwp_woocommerce_checkout_order_processed_action($order_id, $posted_data, $order){
        do_action('logger', $order);
        do_action('logger', $order_id);
        do_action('logger', $posted_data);
        
    }


    /**
     * TODO: 
     [x]- Вывести в чекаут Бонусы для списания: пока хардкодом
     * - Поставить галочку списывать или нет
     [x] - На хук оплаты сделать запрос на удержание бонусов
     [x] - На хук Выполнен: отозвать удержание бонусов, провести подажу со списание бонусов 'retail'
     * 
     */












}

BPWPApiHelper::init();