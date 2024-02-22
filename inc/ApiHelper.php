<?php

namespace BPWP;

use WC_Cart;

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
        add_action('woocommerce_product_meta_end', [__CLASS__, 'bpwp_single_product_bonusplus_price'], 10);
        add_action('woocommerce_cart_calculate_fees', [__CLASS__, 'add_custom_fee_on_checkout']);

        add_action( 'woocommerce_add_to_cart', [__CLASS__, 'cart_updated' ] );
        add_action( 'woocommerce_update_cart_action_cart_updated', [__CLASS__, 'cart_updated' ] );

    }

    public static function add_custom_fee_on_checkout( WC_Cart $cart)
    {
        // Получить данные для списания бонусов
        $fee_amount = -(int)$_SESSION['bpwp_debit_bonuses'];
        $fee_name = apply_filters('bpwp_order_fee_name', 'Списание бонусов');
        $taxable = true;
        $tax_class = 'bpwp-bonuses-reserved';
        
        if ( ! empty( $fee_amount ) ) {
            $cart->add_fee($fee_name, $fee_amount, $taxable, $tax_class);
        }
    }
    
    public static function cart_updated( $cart_updated) {
		$_SESSION['bpwp_debit_bonuses'] = 0;
		return true;
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
     * 
     *  TODO: дописать обработку массива
     *  ! Если товар один. смотри bpwp_product_to_retailitems - нужно сформировать массив retail item. Проверить как отрабатывает
     *  ..
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
        // TODO добавить проверку на nonce if (isset($_GET['testrequest']) && isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'nonce_action_name')) {}
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
                // var_dump($info);
                // $phone = $info['phone'];
                // $cardNumber = $info['cardnumber'];
                // wp_localize_script(
                //     'customerjs',
                //     'accountBonusPlusData',
                //     array(
                //         'phone'      => esc_attr($phone),
                //         'cardNumber' => esc_attr($cardNumber),
                //     )
                // );
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
                    <input id="bpwpBonusesInput" type="number" maxLength="1" size="6" min="0" max="'. $maxDebitBonuses .'" pattern="[0-9]*"/>
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

        echo $output;
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
     *  Выводит доступные бонусы на странице товара
     */
    public static function bpwp_single_product_bonusplus_price(){
        
        if (!is_product()) {
            return;
        }
        
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

    /**11
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