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
     *  @return array
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
                $data['discountCardName'],
                $data['purchasesTotalSum'],
                $data['purchasesSumToNextCard'],
                $data['nextCardName'],
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
            $url = apply_filters('bpwp_filter_goto_shop_url', $url);

            // Возвращаем массив для бонусной карты
            $data['title']  =   sprintf('%s %s', $allBonuses, __('бонусных рублей', 'bonus-plus-wp'));
            $data['url']    =   $url;
            $data['desc']   =   $desc;
            $data['class']  =   'card3';

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
        $phone = bpwp_api_get_customer_phone($user_id);

        if (!empty($phone)) {

            $res = bpwp_api_request(
                'customer',
                array(
                    'phone' => $phone
                ),
                'GET'
            );
            update_user_meta($user_id, 'bonus-plus', $res);
        }
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
}
BPWPProfile::init();
