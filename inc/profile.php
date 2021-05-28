<?php
//namespace WooBonusPlus_Profile;
//use WooBonusPlus_API;

defined('ABSPATH') || exit; // Exit if accessed directly

class WooBonusPlus_Profile
{

    public static function init()
    {
        add_action('init', [__CLASS__, 'bonus_plus_add_my_account_endpoint']);
        add_filter('query_vars', [__CLASS__, 'bonus_plus_query_vars']);
        add_filter('woocommerce_account_menu_items', [__CLASS__, 'bonus_plus_account_links'], 10);
        add_action('woocommerce_account_bonus-plus_endpoint', array(__CLASS__, 'bonus_plus_account_content'));
        
        add_shortcode('bonusplus_login', array(__CLASS__, 'render_bonus_plus_login_info'));
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
     *  display template
     */
    public static function bonus_plus_account_content()
    {
        $woobonusplus_options = get_option( 'woobonusplus_option_name' );
        $text = $woobonusplus_options['___4'];
        echo $text;

        echo '<div>';
        echo '<input class="regular-text" type="text" name="woobonusplus_login" value=""><br/>';
        echo '<input class="regular-text" type="text" name="woobonusplus_pass" value=""><br/>';
        echo '<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">';
        echo '</div>';
    }

    public static function render_bonus_plus_login_info()
    {
        $res = WooBonusPlus_API::bp_api_get_login_curl();

        $info = json_decode($res);

        ob_start();

        foreach ($info as $key => $value):
            if ( $key != 'companies' ) {
                print($key . ' = ' . $value . '<br />');
            }
        endforeach;

        return ob_get_clean();
    }
}

WooBonusPlus_Profile::init();
