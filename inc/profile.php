<?php

defined('ABSPATH') || exit; // Exit if accessed directly

class WooBonusPlus_Profile
{

    public static function init()
    {
        add_action('init', [__CLASS__, 'bp_api_bonus_card_shortcode_init']);
    }

    public static function bp_api_bonus_card_shortcode_init(){
        add_shortcode('bp_api_customer_bonus_card', [__CLASS__, 'bp_api_render_customer_bonus_card']);
    }

    /**
     *  Print customer info from bonusplus
     */
    public static function bp_api_print_user_info()
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
     * Return available bonuses for customer
     */
    public static function bp_customer_get_available_bonuses($customer_id = '')
    {
        if ($customer_id == '') {
            $customer_id = get_current_user_id();
        }
        $availableBonuses = get_user_meta($customer_id, 'bpw_availableBonuses', true);

        $availableBonuses = apply_filters('bp_api_filter_client_available_bonuses', $availableBonuses);

        return $availableBonuses;
    }


    /**
     * Render client bonus card
     */
    public static function bp_api_render_customer_bonus_card()
    {
        $customer_bonuses = self::bp_api_prepare_customer_bonuses_data();
        /*if (!empty($customer_bonuses)){
            var_dump($customer_bonuses);
            die();
        }*/
        $title  = $customer_bonuses['title'];
        $url    = $customer_bonuses['url'];
        $desc   = $customer_bonuses['desc'];
        $class  = $customer_bonuses['class'];

        wp_enqueue_style('bpwp-bonus-card-style');

        ob_start(); ?>

        <div class="container">
            <a class="<?=$class?>" href="<?= $url ?>">
                <h3 class="bp-bonuses-card-title"><?=$title?></h3>
                <p class="small bp-bonuses-card"><?=$desc?></p>
                <div class="go-corner" href="<?=$url?>">
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
     */
    public static function bp_api_prepare_customer_bonuses_data($customer_id = '')
    {
        if (empty($customer_id) && is_user_logged_in()) {
            $customer_id = get_current_user_id();
        }

        $data = array();

        // Если пользователь неавторизован
        if (!is_user_logged_in()) {

            $data['title']  =   'Войдите, на сайт';
            $data['url']    =   wc_get_page_permalink('myaccount ');
            $data['desc']   =   'Чтобы увидеть баланс бонусов и расплачиватся ими за покупку';
            $data['class']  =   'card4';
            //var_dump(1);
            
        } elseif(is_user_logged_in()) {
            //var_dump(2);
            $bonuses = self::bp_customer_get_available_bonuses($customer_id);
            //$billing_phone = bp_api_get_customer_phone($customer_id);
            // Если у пользователя нет бонусов или = 0
            if (empty($bonuses) || $bonuses == 0) {
                //var_dump(2.1);
                $data['title']  =   'У Вас пока нет бонусов';
                $data['url']    =   wc_get_page_permalink('shop');
                $data['desc']   =   'Начните накапливать бонусы после первой покупки!';
                $data['class']  =   'card3';

            } elseif (!empty($bonuses) || $bonuses > 0) {
                //var_dump(2.2);
                // есть бонусы
                $data['title']  =   $bonuses . ' Бонусных рублей';
                $data['url']    =   wc_get_page_permalink('shop');
                $data['desc']   =   'Не забудте потратить бонусные рубли при оплате следующей покупки!';
                $data['class']  =   'card1';

            } else {
                //var_dump(2.3);
                // дефаулт
                $data['title']  =   'Оплачивайте покупки бонусными рублями';
                $data['url']    =   wc_get_page_permalink('shop');
                $data['desc']   =   'Зарегистрируйтесь и сделайте покупку чтобы начать использовать бонусные баллы.';
                $data['class']  =   'card4';

            }
        } else {
            //var_dump(3);
            // дефаулт
            $data['title']  =   'Оплачивайте покупки бонусными рублями';
            $data['url']    =   wc_get_page_permalink('shop');
            $data['desc']   =   'Зарегистрируйтесь и сделайте покупку чтобы начать использовать бонусные баллы.';
            $data['class']  =   'card4';
        }
        //var_dump($data);
        return $data;
    }

}
WooBonusPlus_Profile::init();
