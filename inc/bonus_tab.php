<?php

defined('ABSPATH') || exit; // Exit if accessed directly

class BonusPlusTab
{

    public static function init(){
        add_filter('woocommerce_account_menu_items', [__CLASS__, 'bonusplus_account_links'], 10);
        add_action('init', [__CLASS__, 'bonus_plus_add_my_account_endpoint']);
        add_filter('query_vars', [__CLASS__, 'bonus_plus_query_vars']);
        add_action('woocommerce_account_bonus-plus_endpoint', array(__CLASS__, 'woocommerce_account_bonus_plus'));
    }


    public static function bonusplus_account_links($menu_links){
        $new = array(
            'bonus-plus'     => 'Бонусная программа',
        );

        // array_slice() is good when you want to add an element between the other ones
        $menu_links = array_slice($menu_links, 0, 1, true)
            + $new
            + array_slice($menu_links, 1, NULL, true);

        return $menu_links;
    }

    public static function bonus_plus_add_my_account_endpoint(){
        add_rewrite_endpoint('bonus-plus', EP_ROOT | EP_PAGES);
    }

    public static function bonus_plus_query_vars($vars){
        $vars[] = 'bonus-plus';
        return $vars;
    }

    public static function account_bonus_plus_template($current_page){
        echo $current_page;
    }
}

BonusPlusTab::init();