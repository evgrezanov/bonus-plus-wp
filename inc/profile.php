<?php

defined('ABSPATH') || exit; // Exit if accessed directly

class WooBonusPlus_Profile
{

    public static function init()
    {
        //add_action('wp_login', [__CLASS__, 'bp_customer_login'], 10, 2);
        add_shortcode('wpbp_customer_bonuses', [__CLASS__, 'render_customer_bonuses']);

        add_shortcode('bp_test_test', [__CLASS__, 'bp_test_test']);
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
     * Return available bonuses for customer
     */
    public static function bp_customer_get_available_bonuses($customer_id = '')
    {
        if ($customer_id = '') {
            $customer_id = get_current_user_id();
        }
        $availableBonuses = get_user_meta($customer_id, 'bpw_availableBonuses', true);

        $availableBonuses = apply_filters('bp_api_filter_client_available_bonuses', $availableBonuses);

        return $availableBonuses;
    }

    /**
     *  Render customer bonuses info block
     */
    public static function render_customer_bonuses()
    {
        $message = self::bp_check_customer_bonuses();
        if (!$message) {
            $bonuses = self::bp_customer_get_available_bonuses();
            $bonusesM = '<p>' . $bonuses . ' Бонусных рублей</p>';
            $bonusesM .= '<p>Не забудте потратить бонусные рубли при оплате следующей покупки!</p>';
            $message = $bonusesM;
        }

        ob_start();
?>
        <div class="bp_api_customer_bonuses" role="alert">
            <?php echo $message; ?>
        </div>
    <?php
        return ob_get_clean();
    }

    /**
     *  Return $message if customer no have billing_phone, or no have availible bonus
     */
    public static function bp_check_customer_bonuses($user_id = '')
    {
        $message = '';

        if (empty($user_id)) {
            $user_id = get_current_user_id();
        }

        if (!is_user_logged_in()) {
            $message = 'Авторизуйтесь чтобы увидеть количество бонусных рублей';
        }

        $bonuses = self::bp_customer_get_available_bonuses($user_id);
        $billing_phone = bp_api_get_customer_phone($user_id);
        if (empty($bonuses) || $bonuses == 0 || $billing_phone == '') {
            $message = 'У Вас пока нет бонусных рублей. Начните накапливать бонусы, после первой покупки!';
        }

        return $message;
    }

    /**
     * Test shortcode with card
     */
    public static function bp_test_test()
    {
        $title = '27000 Бонусных рублей';
        $title = 'У Вас пока нету бонусов';
        $title = 'Войдите, на сайт';

        $url = wc_get_page_permalink('shop');;
        $url = wc_get_page_permalink('shop');
        $url = wc_get_page_permalink('myaccount');

        $desc = 'Не забудте потратить бонусные рубли при оплате следующей покупки!';
        $desc = 'Начните накапливать бонусы после первой покупки!';
        $desc = 'Чтобы увидеть баланс бонусов и расплачиватся ими за покупку';

        $class = 'card1'; 
        $class = 'card3';
        $class = 'card4';

        ob_start();
    ?>

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
}
WooBonusPlus_Profile::init();
