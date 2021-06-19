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
    }

    /**
     *  Rewrite endpoint
     *
     *  @return array
     */
    public static function bpwp_add_my_account_endpoint()
    {
        add_rewrite_endpoint('bonus-plus', EP_ROOT | EP_PAGES);
    }

    /**
     *  Add query var
     *  
     *  @param array $vars
     * 
     *  @return array
     */
    public static function bpwp_query_vars($vars)
    {
        $vars[] = 'bonus-plus';
        return $vars;
    }

    /**
     *  Add new item in my profile sidebar menu
     *  
     *  @param array $menu_links
     *
     *  @return array
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

}
WooBonusPlus_My_Account::init();