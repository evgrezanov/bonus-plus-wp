<?php

namespace BPWP;

defined('ABSPATH') || exit; // Exit if accessed directly

class BPWPProfile
{

    /**
     *  Init
     */
    public static function init()
    {
        add_action('init', [__CLASS__, 'bpwp_api_bonus_card_shortcode_init']);
        add_action('wp_login', [__CLASS__, 'bpwp_customer_login'], 10, 2);
        add_filter('bpwp_replace_customer_card_desc', [__CLASS__, 'bpwp_replace_customer_card_desc'], 10, 2);
        //add_action('woocommerce_customer_save_address', [__CLASS__, 'bpwp_remove_user_meta_on_address_change'], 10, 2);
    }

    /**
     *  Shortcode init
     */
    public static function bpwp_api_bonus_card_shortcode_init()
    {
        add_shortcode('bpwp_api_customer_bonus_card', [__CLASS__, 'bpwp_api_render_customer_bonus_card']);
    }


    /**
     * Render client bonus card
     * 
     *  @return void
     */
    public static function bpwp_api_render_customer_bonus_card()
    {
        //todo заменить на apply_filter
        $customer_bonuses = self::bpwp_api_prepare_customer_bonuses_data();

        $title  = $customer_bonuses['title'];
        $url    = $customer_bonuses['url'];
        $desc   = $customer_bonuses['desc'];
        $class  = $customer_bonuses['class'];

        wp_enqueue_style('bpwp-bonus-card-style');

        ob_start(); ?>

        <div class="container">
            <a class="<?php echo esc_attr($class); ?>" href="<?php echo esc_url_raw($url); ?>">
                <h3 class="bp-bonuses-card-title"><?php esc_html_e($title); ?></h3>
                <p class="small bp-bonuses-card"><?php esc_html_e($desc); ?></p>
                <div class="go-corner" href="<?php echo esc_url_raw($url); ?>">
                    <div class="go-arrow">
                        →
                    </div>
                </div>
            </a>
        </div>

        <?php

        return ob_get_clean();
    }


    /**
     *  Prepare customer data for display bonus card
     * 
     *  @return array of properties of bonus cart
     */
    public static function bpwp_api_prepare_customer_bonuses_data($customer_id = '')
    {
        $data = [];
        // Если пользователь неавторизован
        if ( !is_user_logged_in() ) {
            // Описание неопознанного пользователя
            $desc = get_option('bpwp_msg_unknow_customers');

            if ( function_exists('wc_get_page_permalink') ){
                $url = wc_get_page_permalink('myaccount ');
            } else {
                $url = site_url();
            }
            $url = get_option('bpwp_uri_unknow_customers');
            $url = apply_filters('bpwp_filter_goto_register_url', $url);

            $data['title']  =   __('Войдите, на сайт', 'bonus-plus-wp');
            $data['url']    =   $url;
            $data['desc']   =   $desc;
            $data['class']  =   'card4';

        } else {

            // Если пользователь авторизован 
            $data = bpwp_api_get_customer_data();
            // Описание опознанного пользователя
            $desc = get_option('bpwp_msg_know_customers');
            $data['availableBonuses'] = isset($data['availableBonuses']) && !empty($data['availableBonuses']) ? $data['availableBonuses'] : 0;
            $data['notActiveBonuses'] = isset($data['notActiveBonuses']) && !empty($data['notActiveBonuses']) ? $data['notActiveBonuses'] : 0;            
            $allBonuses = $data['availableBonuses'] + $data['notActiveBonuses'];
            
            $availablekeys = [
                'discountCardName',
                'purchasesTotalSum',
                'purchasesSumToNextCard',
                'nextCardName',
                'availableBonuses',
                'notActiveBonuses',
                'allBonuses'
            ];

            $tavailablekeys = [
                isset($data['discountCardName']) ? $data['discountCardName'] : null,
                isset($data['purchasesTotalSum']) ? $data['purchasesTotalSum'] : null,
                isset($data['purchasesSumToNextCard']) ? $data['purchasesSumToNextCard'] : null,
                isset($data['nextCardName']) ? $data['nextCardName'] : null,
                $data['availableBonuses'],
                $data['notActiveBonuses'],
                $allBonuses
            ];

            $desc = str_replace($availablekeys, $tavailablekeys, $desc);

            if (function_exists('wc_get_page_permalink')) {
                $url = wc_get_page_permalink('shop ');
            } else {
                $url = site_url();
            }
            $url = get_option('bpwp_uri_know_customers');
            $url = apply_filters('bpwp_filter_goto_shop_url', $url);

            // Возвращаем массив для данных для виджета бонусной карты
            $data['title']  =   sprintf('%s %s', $allBonuses, __('бонусных ₽', 'bonus-plus-wp'));
            $data['url']    =   $url;
            $data['desc']   =   $desc;
            $data['class']  =   'card3';

            // Проверим заполнены ли у порльзователя дата рождения и телефон
            $user_id = get_current_user_id();
            $billing_phone = get_user_meta(get_current_user_id(), 'billing_phone', true);
            $bonus_plus = get_user_meta(get_current_user_id(), 'bonus-plus', true);
            
            if (empty($billing_phone) ) {
                $data['title']  =   __('ОШИБКА!', 'bonus-plus-wp');
                $data['url']    =   get_option('bpwp_uri_customers_lk_billing_address');
                $data['desc']   =   'Добавьте номер телефона в личном кабинете';
                $data['class']  =   'card3';
                // или данные из Бонус+ пустые
            } elseif (empty($bonus_plus)){
                $data['title']  =   __('ОШИБКА!', 'bonus-plus-wp');
                $data['url']    =   get_option('bpwp_msg_customers_not_verify_phone_number');
                $data['desc']   =   'Подтвердите номер телефона чтобы получать бонусы -->';
                $data['class']  =   'card3';
                // во всех остальных случаях
            } else {
                $data['title']  =   sprintf('%s %s', $allBonuses, __('бонусных ₽', 'bonus-plus-wp'));
                $data['url']    =   $url;
                $data['desc']   =   $desc;
                $data['class']  =   'card3';
            }

        }

        $data = apply_filters('bpwp_filter_bonus_card_data', $data);
        
        return $data;
    }

    /**
     *  Update bonuses data after customer login, store data to user meta field bonus-plus
     *  
     *  @param string $user_login
     *  @param array $user
     *
     *  @return void
     */
    public static function bpwp_customer_login($user_login, $user)
    {
        $user_id = $user->ID;
        delete_user_meta($user_id, 'bonus-plus');
        // Check billing_phone 
        $phone = bpwp_api_get_customer_phone($user_id);
        // Если у пользователя есть мета bonus-plus, значит телефон ранее верифицирован
        // if (!empty(get_user_meta($user_id, 'bonus-plus', true))){
            // Значит обновим данные
            //$phone = bpwp_api_get_customer_phone($user_id);
            //$isPhoneVerified = get_user_meta($user_id, 'bpwp_phone_verified', true);
            if (!empty($phone)) {
                $res = bpwp_api_request(
                    'customer',
                    array(
                        'phone' => $phone
                    ),
                    'GET'
                );
                if ($res['code'] == 200){
                    update_user_meta($user_id, 'bonus-plus', $res['request']);
                } else {
                    do_action(
                        'bpwp_logger',
                        $type = __CLASS__,
                        $title = __('Ошибка при получении данных клиента', 'bonus-plus-wp'),
                        $desc = sprintf(__('У пользователя с ИД %s, данные не получены!', 'bonus-plus-wp'), $user_id),
                    ); 
                }
                
            } else {  
                do_action(
                    'bpwp_logger',
                    $type = __CLASS__,
                    $title = __('Не верифицирован телефон', 'bonus-plus-wp'),
                    $desc = sprintf(__('У пользователя с ИД %s не верифицирован телефон, данные не получены!', 'bonus-plus-wp'), $user_id),
                );
            }
        //}    
    }

    /**
     *  Replace dinamic data at customer card description
     */
    public static function bpwp_replace_customer_card_desc($desc, $data){
        if (!$desc || !$data){
            return;
        }
        $availablekeys = [
            'discountCardName', 
            'purchasesTotalSum', 
            'purchasesSumToNextCard', 
            'nextCardName', 
            'availableBonuses', 
            'notActiveBonuses', 
            'allBonuses'
        ];
        foreach ($availablekeys as $key){
            if (isset($data[$key]) && !empty($data[$key])){
                $desc = str_replace($key, $data[$key], $desc);
            }
        }
    }
    
    /**
     * Обновление метаданных Бонус+ при сохранении адреса из личного кабинета пользователя
     * 
     *  @param int    $user_id      User ID being saved.
     *  @param string $address_type Type of address; 'billing' or 'shipping'.
     *
     * @return void
     * 
     */
    public static function bpwp_remove_user_meta_on_address_change( $user_id, $load_address ){
        if ( ! is_user_logged_in() ) return;

        if ( $load_address !== 'billing') return;
        // TODO: Если есть мета и тел bonus-plus['phone] и billing_phone не совпадают, то чистим мета
        $user = get_user_by_id($user_id);

        //self::bpwp_customer_login($user->user_login, $user);
    }
}
BPWPProfile::init();
