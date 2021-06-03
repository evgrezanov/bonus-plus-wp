<?php

defined('ABSPATH') || exit; // Exit if accessed directly

class WooBonusPlus_Profile
{

    public static function init()
    {
        add_action('init', [__CLASS__, 'bonus_plus_add_my_account_endpoint']);
        add_filter('query_vars', [__CLASS__, 'bonus_plus_query_vars']);
        add_filter('woocommerce_account_menu_items', [__CLASS__, 'bonus_plus_account_links'], 10);
        add_action('woocommerce_account_bonus-plus_endpoint', array(__CLASS__, 'render_bonus_plus_customer_info'));


        add_action('wp_login', array(__CLASS__, 'bp_customer_login'),10, 2);
    }

    /**
     *  Rewrite endpoint
     */
    public static function bonus_plus_add_my_account_endpoint()
    {
        add_rewrite_endpoint('bonus-plus', EP_ROOT | EP_PAGES);
    }

    /**
     * Add query var
     */
    public static function bonus_plus_query_vars($vars)
    {
        $vars[] = 'bonus-plus';
        return $vars;
    }

    /**
     *  Add new item in my profile sidebar menu
     */
    public static function bonus_plus_account_links($menu_links)
    {
        $options = get_option('woobonusplus_option_name');
        $tab_title = trim($options['____3']);
        $tab_title ? '' : 'Бонусная программа';
        $new = array(
            'bonus-plus'     => $tab_title,
        );

        // array_slice() is good when you want to add an element between the other ones
        $menu_links = array_slice($menu_links, 0, 1, true)
            + $new
            + array_slice($menu_links, 1, NULL, true);

        return $menu_links;
    }

    /**
     *  Render Customer Account info
     */
    public static function render_bonus_plus_customer_info()
    {
        $phone = self::get_customer_phone();

        if ( !empty($phone) ){

            $res = WooBonusPlus_API::bp_api_request(
                'customer', 
                array('phone'=> $phone), 
                'GET'
            );

            $info = json_decode($res);


            foreach ($info as $key => $value) :
                if ($key == 'discountCardNumber') {
                    print('Номер карты: ' . $value . '<br />');
                }
                if ($key == 'discountCardName') {
                    print('Тип карты: ' . $value . '<br />');
                }
                if ($key == 'availableBonuses') {
                    print('Доступно бонусов: ' . $value . '<br />');
                }
                if ($key == 'purchasesSumToNextCard') {
                    print('Сумма для следующего уровня: ' . $value . '<br />');
                }
                if ($key == 'nextCardName') {
                    print('Следующий уровень: ' . $value . '<br />');
                }
            endforeach;

        } else {

            print_r('Заполните телефон в платежном адресе для доступа к бонусной программе. Ваш телефон в платежном адресе должен совпадать с телефоном в бонусной программе.');
        
        }

    }

    /**
     *  return customer billing phone
     */
    public static function get_customer_phone($customer_id = ''){
        if (empty($customer_id)){
            $customer_id = get_current_user_id();
        }

        $phone = get_user_meta($customer_id, 'billing_phone', true);
        
        $phone = apply_filters('bp_api_filter_user_phone', $phone);

        return $phone;
    }

    /**
     *  Render user info from bonusplus
     */
    public static function bp_api_user_info(){
        $phone = self::get_customer_phone();
        if (!empty($phone)) {

            $res = WooBonusPlus_API::bp_api_request(
                'customer',
                array('phone' => $phone),
                'GET'
            );

            $info = json_decode($res);


            foreach ($info as $key => $value) :
                if ($key != 'person') {
                    print($key . ' = ' . $value . '<br />');
                } else {
                    $person_data = $value;
                    foreach ($person_data as $dkey => $data) {
                        if ($dkey == 'discountCardNumber') {
                            print('Номер карты: ' . $data . '<br />');
                        }
                        if ($dkey == 'discountCardName') {
                            print('Тип карты: ' . $data . '<br />');
                        }
                        if ($dkey == 'availableBonuses') {
                            print('Доступно бонусов: ' . $data . '<br />');
                        }
                        if ($dkey == 'purchasesSumToNextCard') {
                            print('Сумма для следующего уровня: ' . $data . '<br />');
                        }
                        if ($dkey == 'nextCardName') {
                            print('Следующий уровень: ' . $data . '<br />');
                        }

                    }
                }
            endforeach;
        }
    }

    /**
     *  Save alailable bonuses count at user meta, after user login
     */
    public static function bp_customer_login($user_login, $user)
    {
        
        $bonuses = get_user_meta($user->ID, 'bpw_availableBonuses', true);

        if (empty($bonuses) || $bonuses=='0') {
        
            $phone = self::get_customer_phone($user->ID);

            if (!empty($phone)) {

                $res = WooBonusPlus_API::bp_api_request(
                    'customer',
                    array('phone' => $phone),
                    'GET'
                );

                $info = json_decode($res);


                foreach ($info as $key => $value) :
                    if ($key == 'availableBonuses') {
                        update_user_meta($user->ID, 'bpw_availableBonuses', $value);
                    }
                endforeach;
            }
        }
    }

    /**
     * Return available bonuses for customer/current user
     */
    public static function bp_customer_get_available_bonuses($customer_id = ''){
        if ($customer_id = ''){
            $customer_id = get_current_user_id();
        }
        $availableBonuses = get_user_meta($customer_id, 'bpw_availableBonuses', true);
        return $availableBonuses;
    }
}
WooBonusPlus_Profile::init();
