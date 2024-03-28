<?php

namespace BPWP;

defined('ABSPATH') || exit; // Exit if accessed directly

class BPWPApiHelper
{
    private static $maxDebitBonuses;

    /**
     *  Init
     */
    public static function init()
    {
        self::$maxDebitBonuses = 0;

        add_action('woocommerce_before_cart_totals', [__CLASS__, 'bpwp_cart_checkout_bonusplus_price']);
        add_action('woocommerce_checkout_before_order_review', [__CLASS__, 'bpwp_cart_checkout_bonusplus_price']);
        add_action('woocommerce_product_meta_end', [__CLASS__, 'bpwp_single_bonusplus_price'], 10);
        add_action('woocommerce_cart_calculate_fees', [__CLASS__, 'bpwp_add_custom_fee_on_checkout'], 20, 1);

        add_action( 'woocommerce_add_to_cart', [__CLASS__, 'bpwp_cart_updated' ] );
        add_action( 'woocommerce_update_cart_action_cart_updated', [__CLASS__, 'bpwp_cart_updated' ] );
    }
    
    // Списание бонусов в заказе
    public static function bpwp_add_custom_fee_on_checkout($cart)
    {
        if (WC()->session->__isset('bpwp_debit_bonuses')) {
            $discount = (float) WC()->session->get('bpwp_debit_bonuses');
        }
        
        if (isset($discount) && $discount > 0) {
        $cart->add_fee(__('Списание бонусов', 'bonus-plus-wp'), -$discount);
        }
    }
    
    public static function bpwp_cart_updated($cart_updated) {
        if (  WC()->session->__isset( 'bpwp_debit_bonuses' ) ) {
            WC()->session->set('bpwp_debit_bonuses', null);
        }
        delete_user_meta(get_current_user_id(), 'bpwp_debit_bonuses');
		return true;
    }
    /**
     *  Возвращает товары в формате RetailItem по ИД
     * 
     *  @param array $product_ids ИД товаров по которым необходимо расчитать чек и количество товаров
     * 
     *  @return object RetailItem для переданных товаров https://bonusplus.pro/api/Help/ResourceModel?modelName=RetailItem
     */
    public static function bpwp_product_to_retailitems($product_ids) 
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
                    "price"     => (float) $product_price
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
     */
    public static function bpwp_get_calc_bonusplus_price()
    {
        global $product;
        $items = [];

        // Если находимся на странице товара
        if (is_product() && $product) {
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
                wp_json_encode($params),
                'PUT',
            );
            
            //$response_code = wp_remote_retrieve_response_code($retailcalc);

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

        $info = bpwp_api_get_customer_data();
        
        if ($info && is_array($info)) {

            $available_bonuses = $info['availableBonuses'];
            
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
            $output .= '<p>Ваш бонусный баланс '. $available_bonuses .'.</p><p>Сумма бонусов, которые будут начислены '. $bonuses .'.</p>'; 
            $output .= '</div>';

            // На чекаут выводим поле для сипсывания бонусов
            if ( is_checkout() && $maxDebitBonuses > 0 ) {
                $output .= '<p>Доступно для списания '. $maxDebitBonuses .' бонусов</p>';
                $output .= '<div id="verify-phone-dialog">
                <div id="loader" class="center-body">
                <div class="loader-ball-8"></div>
                </div>
                <div hidden id="bpmsg" class="msg" style="display:none;"></div>
    
                    <div id="bpwp-verify-start" style="display:none;">
                        <p>'. __('Сколько бонусов списать для этой покупки?', 'bonus-plus-wp').'
                        <strong><?php echo $bonuses ?></strong>
                        </p>
                    <input id="bpwpBonusesInput" type="number" maxLength="1" size="6" min="0" max="'. esc_attr($maxDebitBonuses) .'" pattern="[0-9]*"/>
                    <button id="bpwpSendSms">'. __('Списать бонусы', 'bonus-plus-wp').'</button>
                    </div>
    
                <div id="bpwp-verify-end" style="display:none;">
                    <p>'. __('Введите код высланый в SMS, на номер телефона:', 'bonus-plus-wp').'
                        <strong><?php echo $bonuses ?></strong>
                    </p>
                    <input id="bpwpOtpInput" type="number" maxLength="1" size="6" min="0" max="999999" pattern="[0-9]{6}" />
                    <button id="bpwpSendOtp">'. __('Подтвердить номер телефона', 'bonus-plus-wp').'</button>
                </div>
                </div>';
                }
        }

        echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Renders retail items calculation based on the provided data.
     *
     * @param array $data The data for the retail items calculation.
     */
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

            echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }

    /**
     *  Выводим информацию по бонусам
     */
    public static function bpwp_single_bonusplus_price(){
        
        $content = '';
        $price_data = self::bpwp_get_calc_bonusplus_price();
        $content = self::bpwp_render_retailitems_calc($price_data);

        return $content;
    }

    /**
     * Выводим бонусы, доступные для начисления и списания
     */
    public static function bpwp_cart_checkout_bonusplus_price(){
        $content = '';

        if (is_cart() || is_checkout()) {
            $price_data = self::bpwp_get_calc_bonusplus_price();
            $content = self::bpwp_render_calc_bonusplus_price($price_data);
        }
        return $content;
    }

    /**
     * Get the maximum debit bonuses from the provided data array.
     *
     * @param array $data The input data array
     */
    public function bpwp_get_max_debit_bonuses($data) {
        if (is_array($data) && isset($data['request']) && isset($data['request']['discount'])) {
            $this->maxDebitBonuses = $data['request']['maxDebitBonuses'];
        }
    }

}

BPWPApiHelper::init();