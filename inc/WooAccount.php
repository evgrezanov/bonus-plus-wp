<?php

namespace BPWP;

defined('ABSPATH') || exit; // Exit if accessed directly

class BPWPMyAccount
{
    private $billing_fields = [
        'billing_first_name',
        'billing_last_name',
        'billing_company',
        'billing_address_1',
        'billing_address_2',
        'billing_city',
        'billing_postcode',
        'billing_country',
        'billing_state',
        'billing_email',
        'billing_phone',
    ];

    /**
     *  Init
     */
    public static function init()
    {
        add_action('init', [__CLASS__, 'bpwp_add_my_account_endpoint']);
        add_action('init', [__CLASS__, 'bpwp_customer_data_shortcode_init']);
        add_filter('query_vars', [__CLASS__, 'bpwp_query_vars']);
        add_filter('woocommerce_account_menu_items', [__CLASS__, 'bpwp_account_links'], 10);
        add_action('woocommerce_account_bonus-plus_endpoint', [__CLASS__, 'bpwp_api_print_customer_card_info']);
        add_action('bpwp_veryfy_client_data', [__CLASS__, 'bpwp_api_render_customer_data']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'bpwp_qrcode_scripts']);
        add_filter('bpwp_debug_phone_verify', '__return_true');

    }

    /**
     *  Добавляем вкладку bonus-plus в аккаунт клиента WooCommerce
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
     *  Shortcode init [bpwp_api_customer_data]
     */
    public static function bpwp_customer_data_shortcode_init()
    {
        add_shortcode('bpwp_api_customer_data', [__CLASS__, 'bpwp_api_render_customer_data']);
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
        $tab_title = __('Бонус+', 'bonus-plus-wp');
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
        if ($info && is_array($info)) {

            // for debug
            if (isset($_REQUEST['bpwp-debug']) && !empty($_REQUEST['bpwp-debug'])) {
                $is_debug = sanitize_text_field($_REQUEST['bpwp-debug']);
            }

            // not debug
            if (empty($is_debug)) {
                printf('<h2>%s</h2>', 'Информация по карте лояльности');

                echo '<div id="loader" class="center-body"><div class="loader-ball-8"></div></div>';

                //do_action('bpwp_after_bonus_card_info_title');

                echo '<br><div id="qrcode"></div><br>';

                foreach ($info as $key => $value) {
                    if ($key != 'person') {
                        if ($key == 'discountCardName') {
                            printf('%s:%s<br />', esc_html(__('Тип карты', 'bonus-plus-wp')), esc_html($value));
                        }
                        if ($key == 'discountCardNumber') {
                            printf('%s:%s<br />', esc_html(__('Номер карты', 'bonus-plus-wp')), esc_html($value));
                        }
                        if ($key == 'availableBonuses') {
                            printf('%s:%s<br />', esc_html(__('Доступных бонусов', 'bonus-plus-wp')), esc_html($value));
                        }
                        if ($key == 'notActiveBonuses') {
                            printf('%s:%s<br />', esc_html(__('Неактивных бонусов', 'bonus-plus-wp')), esc_html($value));
                        }
                        if ($key == 'purchasesTotalSum') {
                            printf('%s:%s<br />', esc_html(__('Сумма покупок', 'bonus-plus-wp')), esc_html($value));
                        }
                        if ($key == 'purchasesSumToNextCard') {
                            printf('%s:%s<br />', esc_html(__('Сумма покупок для смены карты', 'bonus-plus-wp')), esc_html($value));
                        }
                        if ($key == 'lastPurchaseDate') {
                            printf('%s:%s<br />', esc_html(__('Последняя покупка', 'bonus-plus-wp')), esc_html($value));
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
                                printf('%s:%s<br />', esc_html(__('Держатель', 'bonus-plus-wp')), esc_html($owner));
                            }
                        }
                    }
                }
            } else { // debug
                foreach ($info as $key => $value) {
                    if ($key !== 'person') {
                        printf('%s:%s<br />', esc_html($key), esc_html($value));
                    } else {
                        $personalInfo = $value;
                        foreach ($personalInfo as $pkey => $pvalue) {
                            if (!is_array($pvalue)) {
                                printf('%s:%s<br />', esc_html($pkey), esc_html($pvalue));
                            }
                        }
                    }
                }
            }
        } else { // нет данных в бонус+ 
            do_action('bpwp_veryfy_client_data');
        }
    }

    /**
     * Подключение скриптов для генерации QR кода в блоке 
     * "Информация по карте лояльности" в профиле
     * 
     */
    public static function bpwp_qrcode_scripts()
    {
        if (!is_user_logged_in()) {
            return;
        }
        $customerData = bpwp_api_get_customer_data();
        $cardNumber = !empty($customerData['discountCardNumber']) ? $customerData['discountCardNumber'] : '';
        $phone = get_user_meta(get_current_user_id(), 'billing_phone', true);

        wp_enqueue_script(
            'qrcodejs',
            plugins_url('/assets/qrcodejs/qrcode.min.js', __DIR__),
            [],
            BPWP_PLUGIN_VERSION,
            'in_footer'
        );

        wp_enqueue_script(
            'customerjs',
            plugins_url('/assets/customer.js', __DIR__),
            ['qrcodejs', 'jquery'],
            BPWP_PLUGIN_VERSION,
            'in_footer'
        );

        wp_enqueue_script( 'wp-api' );

        wp_localize_script(
            'customerjs',
            'accountBonusPlusData',
            array(
                'phone'      => esc_attr($phone),
                'cardNumber' => esc_attr($cardNumber),
                'redirect'   => site_url() . '/my-account/bonus-plus/',
                'debug'      => apply_filters('bpwp_debug_phone_verify', false),
            )
        );
        wp_enqueue_style('bpwp-bonus-loader-style');
    }

    /**
     *  Render customer data in /my-profile/ or shortcode
     */
    public static function bpwp_api_render_customer_data()
    {
        $phone = !empty(get_user_meta(get_current_user_id(), 'billing_phone', true)) ? get_user_meta(get_current_user_id(), 'billing_phone', true) : '';

        $verifiedUser = !empty(get_user_meta(get_current_user_id(), 'bpwp_verified_user', true)) ? get_user_meta(get_current_user_id(), 'bpwp_verified_user', true) : '';

        $msg = '';

        if (empty($phone)) {

            $msg .= sprintf('<h3>%s</h3>', __('Пожалуйста, заполните платежный адрес и телефон', 'bonus-plus-wp'));
            $msg .= sprintf('<a href="%s">%s</a>', '/my-account/edit-address/billing/', __('Перейти к заполнению данных', 'bonus-plus-wp'));
        } else if (empty($verifiedUser)) {

            self::bpwp_render_verify_phone_form($phone);
        } else {

            printf('<h3>%s</h3>', __('text', 'bonus-plus-wp'));
        }

        echo $msg;
    }

    /**
     *  Render verify phone form
     */
    public static function bpwp_render_verify_phone_form($phone)
    {
?>
        <div id="verify-phone-dialog">

            <div id="loader" class="center-body">
                <div class="loader-ball-8"></div>
            </div>

            <div hidden id="bpmsg" class="msg" style="display:none;"></div>

            <!-- <div id="qrcode" style="display:none;"></div> -->

            <div id='bpwp-registration' style="display:none;">
                <p><?php echo __('Вы еще не зарегистрированы в программе лояльности', 'bonus-plus-wp') ?></p>
            </div>

            <div id='bpwp-verify-start' style="display:none;">
                <p><?php echo __('Подтвердите номер телефона', 'bonus-plus-wp') ?>
                    <strong><?php echo $phone ?></strong>
                </p>
                <button id="bpwpSendSms"><?php echo __('Отправить SMS c кодом подтверждения', 'bonus-plus-wp') ?></button>
            </div>

            <div id='bpwp-verify-end' style="display:none;">
                <p><?php echo __('Введите код высланый в SMS, на номер телефона:', 'bonus-plus-wp') ?>
                    <strong><?php echo $phone ?></strong>
                </p>
                <input id="bpwpOtpInput" type="number" maxLength="1" size="6" min="0" max="999999" pattern="[0-9]{6}" />
                <button id="bpwpSendOtp"><?php echo __('Подтвердить номер телефона', 'bonus-plus-wp') ?></button>
            </div>
        </div>
<?php
    }

    /**
     *  Return customer data for registration
     *  
     * @param int $user_id Client ID
     * 
     * @return array $registrationData Data for registration
     */
    public static function bpwp_get_client_registration_data($user_id)
    {
        $customer = get_user_meta($user_id);
        $firstName = !empty($customer['billing_first_name']) ? $customer['billing_first_name'] : '-';
        $lastName = !empty($customer['billing_last_name']) ? $customer['billing_last_name'] : '-';
        
        $billingEmail = !empty($customer['billing_email']) ? $customer['billing_email'] : '-';
        $billingPhone = !empty($customer['billing_phone']) ? $customer['billing_phone'] : '-';

        $registrationData = array();

        if ($firstName && $lastName && $billingEmail && $billingPhone) {
            $registrationData = array(
                'phone'      => $billingPhone[0],
                'email'      => $billingEmail[0],
                'fn'         => $firstName[0],
                'ln'         => $lastName[0],
                'desc'       => __('Регистрация на сайте', 'bonus-plus-wp'),
            );
        }

        return $registrationData;
    }

}

BPWPMyAccount::init();