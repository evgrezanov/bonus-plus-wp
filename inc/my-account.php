<?php

defined('ABSPATH') || exit; // Exit if accessed directly

class WooBonusPlus_My_Account
{
    /**
     *  Init
     */
    public static function init()
    {
        add_action('init', [__CLASS__, 'bpwp_add_my_account_endpoint']);
        add_filter('query_vars', [__CLASS__, 'bpwp_query_vars']);
        add_filter('woocommerce_account_menu_items', [__CLASS__, 'bpwp_account_links'], 10);
        add_action('woocommerce_account_bonus-plus_endpoint', [__CLASS__, 'bpwp_render_customer_info']);
        add_action('wp_login', [__CLASS__, 'bpwp_customer_login'], 10, 2);
    }

    /**
     *  Rewrite endpoint
     */
    public static function bpwp_add_my_account_endpoint()
    {
        add_rewrite_endpoint('bonus-plus', EP_ROOT | EP_PAGES);
    }

    /**
     *  Add query var
     */
    public static function bpwp_query_vars($vars)
    {
        $vars[] = 'bonus-plus';
        return $vars;
    }

    /**
     *  Add new item in my profile sidebar menu
     */
    public static function bpwp_account_links($menu_links)
    {
        $options = get_option('woobonusplus_option_name');
        $tab_title = trim($options['____3']);
        $tab_title ? '' : 'Бонусная программа';
        $new = array(
            'bonus-plus' => $tab_title,
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
    public static function bpwp_render_customer_info()
    {
        $phone = bp_api_get_customer_phone();

        if (!empty($phone)) {

            $res = bp_api_request(
                'customer',
                array(
                    'phone' => $phone
                ),
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
     *  Update bonuses data after customer login, save data to meta field
     */
    public static function bpwp_customer_login($user_login, $user)
    {

        $bonuses = get_user_meta($user->ID, 'bpw_availableBonuses', true);

        if (empty($bonuses) || $bonuses == '0') {

            $phone = bp_api_get_customer_phone($user->ID);

            if (!empty($phone)) {

                $res = bp_api_request(
                    'customer',
                    array(
                        'phone' => $phone
                    ),
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
}
WooBonusPlus_My_Account::init();