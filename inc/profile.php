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
     *  display tab template
     */
    public static function bonus_plus_account_content()
    {
        $woobonusplus_options = get_option( 'woobonusplus_option_name' );
        $profile_text = $woobonusplus_options['___4'];
        echo $profile_text;
        echo '<br>';
        echo '<a href="">редактировать данные для входа</a>';
        echo '<br>';
        $current_account_info = self::render_bonus_plus_customer_info();
        /*if ( is_admin() ){
            echo '<div class="bonus-plus-client-form">';
            self::render_bonus_plus_customer_info();
            echo '</div>';
        }
        echo '<div class="bonus-plus-client-form">';
        echo '<p class="woocommerce-form-row woocommerce-form-row--bonus-plus-card-number form-row form-row-bonus-plus-card-number">
		        <label for="account_bonus_plus_card_number">Номер карты клиента</label>
		        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_bonus_plus_card_number" id="account_bonus_plus_card_number" value="">
                <span><em>Формат EAN-8 или EAN13 (только цифры)</em></span>
	        </p>';
        echo '
            <p class="woocommerce-form-row woocommerce-form-row--bonuspassword form-row form-row-bonuspassword">
                <label for="account_bonus_plus_password">Номер телефона</label>
                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_bonus_plus_phone_number" id="account_bonus_plus_phone_number" value="">
                <span><em>Формат не имеет значение. Обязан содержать минимум 11 цифр</em></span>
            </p>';
        echo '<input type="submit" name="submit" id="submit" class="button button-primary" value="Сохранить">';
        echo '</div>';
        */
    }

    /**
     *  render Company Account info
     */
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

    /**
     *  render Customer Account info
     */
    public static function render_bonus_plus_customer_info()
    {
        $res = WooBonusPlus_API::bp_api_request('customer', array('phone'=>self::get_customer_phone()), 'GET');

        $info = json_decode($res);

        //ob_start();

        foreach ($info as $key => $value) :
            if ($key != 'person') {
                print($key . ' = ' . $value . '<br />');
            } else {
                $person_data = $value;
                foreach ($person_data as $dkey => $data){
                    print($dkey . ' = ' . $data . '<br />');
                }
            }
        endforeach;

        //return ob_get_clean();

    }

    /**
     *  return customer billing phone
     */
    public static function get_customer_phone($customer_id = ''){
        if (empty($customer_id)){
            $customer_id = get_current_user_id();
        }
        $phone = get_user_meta( $customer_id, 'billing_phone', true );
        $phone = apply_filters('bp_api_filter_user_phone', $phone);

        return $phone;
    }
}
WooBonusPlus_Profile::init();
