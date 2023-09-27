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
        add_action('woocommerce_before_single_product_summary', [__CLASS__, 'bpwp_single__bonusplus_price']);
        add_action('woocommerce_before_cart_totals', [__CLASS__, 'bpwp_single__bonusplus_price']);
        add_action('woocommerce_checkout_before_order_review', [__CLASS__, 'bpwp_single__bonusplus_price']);
        add_action('woocommerce_after_shop_loop_item', [__CLASS__, 'bpwp_single__bonusplus_price']);
    }

    /**
     *  Возвращает товары в формате RetailItem по ИД
     * 
     *  @param array $product_ids ИД товаров по которым необходимо расчитать чек
     * 
     *  @return object RetailItem для переданных товаров https://bonusplus.pro/api/Help/ResourceModel?modelName=RetailItem
     */
    public static function bpwp_product_to_retailitems ($product_ids) 
    {
        $products_data = [];
        $ext = 0;

        foreach ($product_ids as $product_id) {
            $product = wc_get_product($product_id);

            if ($product) {
                $ext++;
                $product_name = $product->get_name();
                $product_price = $product->get_price();

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
                    "sum"       => 1.0,
                    "qnt"       => 1.0,
                    "product"   => $product_id,
                    "ds"        => 0.0,
                    "cat"       => $category_id,
                    "ext"       => $ext,
                    "price"     => (float) $product_price,
                    /*"sellMode" => "sample string 7",*/
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
        $items = [];

        // Если находимся на странице товара
        if ( is_product() ) {
            global $product;
            $items[] = $product->get_id();
            $quantity = 1;
        }

        // Если находимся в корзине
        if (is_cart() || is_checkout()) {
            $cart = WC()->cart;
            $cart_items = $cart->get_cart();
            foreach ($cart_items as $cart_item_key => $cart_item) {
                $items[] = $cart_item['product_id'];
                $quantity = $cart_item['quantity'];
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
            'certificate'   => true
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
        $output = '<ul>';

        if (is_array($data) && isset($data['request']) && is_array($data['request']['discount'])) {
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
        $output .= '<strong>maxDebitBonuses:</strong> ' . $data['request']['maxDebitBonuses'] . '<br>';
        $output .= '<strong>multiplicityDebitBonus:</strong> ' . $data['request']['multiplicityDebitBonus'] . '<br>';
    
        return $output;
    }

    /**
     *  Выводим количество начисленных бонусов за покупку
     *  на странице товара, в корзине и чекауте $discount['cb']
     * 
     *  @param object $data данные о бонусах
     * 
     *  @return string HTML данные о скидках по чеку
     */
    public static function bpwp_render_retailitems_calc($data)
    {
        if (is_array($data) && isset($data['request']) && is_array($data['request']['discount'])) {
            foreach ($data['request']['discount'] as $discount) {
                if (isset($discount['cb']) && !empty($discount['cb'])){
                    $add = '+' . $discount['cb'] . ' бонусов';
                    return $add;
                }
            }
        }
    }

    /**
     *  Выводим информацию по бонусам
     */
    public static function bpwp_single__bonusplus_price(){
        $price_data = self::bpwp_get_calc_bonusplus_price();
        echo self::bpwp_render_retailitems_calc($price_data);
    }
}

BPWPApiHelper::init();