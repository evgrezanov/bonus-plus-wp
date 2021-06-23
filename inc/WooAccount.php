<?php

namespace BPWP;

defined('ABSPATH') || exit; // Exit if accessed directly

class BPWPMyAccount
{
    /**
     *  Init
     */
    public static function init()
    {
        add_action('init', [__CLASS__, 'bpwp_add_my_account_endpoint']);
        add_filter('query_vars', [__CLASS__, 'bpwp_query_vars']);
        add_filter('woocommerce_account_menu_items', [__CLASS__, 'bpwp_account_links'], 10);
        add_action('woocommerce_account_bonus-plus_endpoint', [__CLASS__, 'bpwp_api_print_customer_card_info']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'bpwp_qrcode_scripts']);
    }

    /**
     *  Rewrite endpoint
     *
     *  @return array
     */
    public static function bpwp_add_my_account_endpoint()
    {
        add_rewrite_endpoint('bonus-plus', EP_ROOT | EP_PAGES);
        if (!get_option('bpwp_plugin_permalinks_flushed')) {
            flush_rewrite_rules(false);
            update_option('bpwp_plugin_permalinks_flushed', 1);
        }
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
        $tab_title = 'Бонус+';
        $tab_title = apply_filters('bpwp_filter_woo_profile_tab_title', $tab_title);


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
     *  Выводит блок "Информация по карте лояльности" в профиле пользователя
     */
    public static function bpwp_api_print_customer_card_info()
    {
        // get data
        $info = bpwp_api_get_customer_data();

        if ( $info && is_array($info) ) {
            // for debug

            if ( isset($_REQUEST['bpwp-debug']) && !empty($_REQUEST['bpwp-debug']) ){
                $is_debug = sanitize_text_field($_REQUEST['bpwp-debug']);
            }
            if ( empty($is_debug) ) {

                printf('<h2>%s</h2>', 'Информация по карте лояльности');

                do_action('bpwp_after_bonus_card_info_title');

                echo '<br><div id="qrcode"></div><br>';
                foreach ($info as $key => $value) {
                    if ($key != 'person') {
                        if ($key == 'discountCardName') {
                            print('Тип карты: ' . $value . '<br />');
                        }
                        if ($key == 'discountCardNumber') {
                            print('Номер карты: ' . $value . '<br />');
                        }
                        if ($key == 'availableBonuses') {
                            print('Доступных бонусов: ' . $value . '<br />');
                        }
                        if ($key == 'notActiveBonuses') {
                            print('Неактивных бонусов: ' . $value . '<br />');
                        }
                        if ($key == 'purchasesTotalSum') {
                            print('Сумма покупок: ' . $value . '<br />');
                        }
                        if ($key == 'purchasesSumToNextCard') {
                            print('Сумма покупок для смены карты: ' . $value . '<br />');
                        }
                        if ($key == 'lastPurchaseDate') {
                            print('Последняя покупка: ' . $value . '<br />');
                        }
                    } else {
                        $person_data = $value;
                        $person = array();
                        foreach ($person_data as $pkey => $pvalue) {
                            if ($pkey == 'ln' || $pkey == 'fn' || $pkey == 'mn') {
                                $person[$pkey] = $pvalue;
                            }
                        }
                        if (!empty($person)) {
                            $owner = $person['ln'] . ' ' . $person['fn'] . ' ' . $person['mn'];
                            if (!empty($owner)) {
                                print('Держатель: ' . $owner . '<br />');
                            }
                        }
                    }
                }
            } else {
                foreach ($info as $key => $value) {
                    if ($key !== 'person') {
                        print($key . ' : ' . $value . '<br />');
                    } else {
                        $personalInfo = $value;
                        foreach ($personalInfo as $pkey => $pvalue) {
                            if (!is_array($pvalue)) {
                                print($pkey . ' : ' . $pvalue . '<br />');
                            }
                        }
                    }
                }
            }
        }
        ?>
            
        <?php
    }

    /**
     * Подключение скриптов для генерации QR кода в блоке "Информация по карте лояльности" в профиле
     *
     * @return void
     */
    public static function bpwp_qrcode_scripts()
    {
        if( !is_user_logged_in() ){
            return;
        }
        $customerData = bpwp_api_get_customer_data();
        $cardNumber = !empty($customerData['discountCardNumber']) ? $customerData['discountCardNumber'] : '';
        
        wp_enqueue_script(
            'bpwp-qrcodejs',
            plugins_url('/assets/qrcodejs/qrcode.min.js', __DIR__),
            array(),
            BPWP_PLUGIN_VERSION,
            'in_footer'
        );
        wp_enqueue_script(
            'bpwp-qrcodejs-action',
            plugins_url('/assets/script.js', __DIR__),
            array('bpwp-qrcodejs'),
            BPWP_PLUGIN_VERSION,
            'in_footer'
        );

        wp_localize_script(
            'bpwp-qrcodejs-action',
            'discountCardNumber',
            array(
                'cardNumber' => $cardNumber
            )
        );
    }

}
BPWPMyAccount::init();