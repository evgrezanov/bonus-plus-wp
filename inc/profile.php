<?php

namespace BPWP;

defined('ABSPATH') || exit; // Exit if accessed directly

class WooBonusPlus_Profile
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
            <a class="<?= $class ?>" href="<?= $url ?>">
                <h3 class="bp-bonuses-card-title"><?= $title ?></h3>
                <p class="small bp-bonuses-card"><?= $desc ?></p>
                <div class="go-corner" href="<?= $url ?>">
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
        $data = array();
        // Если пользователь неавторизован
        if ( !is_user_logged_in() ) {
            // Описание неопознанного пользователя
            $desc = get_option('bpwp_msg_unknow_customers');
            
            $data['title']  =   'Войдите, на сайт';
            $data['url']    =   wc_get_page_permalink('myaccount ');
            $data['desc']   =   $desc;
            $data['class']  =   'card4';

        } else {
            // Если пользователь авторизован 
            $data = bpwp_api_get_customer_data();
            // Описание опознанного пользователя
            $desc = get_option('bpwp_msg_know_customers');
            $availablekeys = array(
                'discountCardName',
                'purchasesTotalSum',
                'purchasesSumToNextCard',
                'nextCardName',
                'availableBonuses',
                'notActiveBonuses',
                'allBonuses'
            );

            $allBonuses = $data['availableBonuses'] + $data['notActiveBonuses'];
            foreach ($availablekeys as $v) {
                if ($v != 'allBonuses'){
                    if (!empty($data[$v])) {
                        $desc = str_replace($v, $data[$v], $desc);
                    }
                } else {
                    $desc = str_replace($v, $allBonuses, $desc);
                }
            }
            // Возвращаем массив для бонусной карты
            $data['title']  =   $allBonuses . ' бонусных рублей';
            $data['url']    =   wc_get_page_permalink('shop');
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

            $info = json_decode($res);

            update_user_meta($user_id, 'bonus-plus', $info);
        }
    }

    /**
     *  Replace dinamic data at customer card description
     */
    public static function bpwp_replace_customer_card_desc($desc, $data){
        if (!$desc || !$data){
            return;
        }
        $availablekeys = array(
            'discountCardName', 
            'purchasesTotalSum', 
            'purchasesSumToNextCard', 
            'nextCardName', 
            'availableBonuses', 
            'notActiveBonuses', 
            'allBonuses'
        );
        foreach ($availablekeys as $key){
            if (!empty($data[$key])){
                $desc = str_replace($key, $data[$key], $desc);
            }
        }
    }
}
WooBonusPlus_Profile::init();
