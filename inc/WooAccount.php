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
        //add_action('woocommerce_account_bonus-plus_endpoint', [__CLASS__, 'bpwp_api_print_customer_card_info']);
        add_action('woocommerce_account_bonus-plus_endpoint', [__CLASS__, 'bpwp_render_verify_phone_form']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'bpwp_qrcode_scripts']);

        add_filter('woocommerce_billing_fields', [__CLASS__, 'bpwp_add_birth_date_billing_field'], 20, 1);
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
            if (empty($is_debug)) {

                printf('<h2>%s</h2>', 'Информация по карте лояльности');

                do_action('bpwp_after_bonus_card_info_title');

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
            } else {
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
        }
    }

    /**
     * Подключение скриптов для генерации QR кода в блоке 
     * "Информация по карте лояльности" в профиле
     * 
     * @return void
     */
    public static function bpwp_qrcode_scripts()
    {
        if (!is_user_logged_in()) {
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
            plugins_url('/assets/qrcodejs/script.js', __DIR__),
            array('bpwp-qrcodejs'),
            BPWP_PLUGIN_VERSION,
            'in_footer'
        );

        wp_localize_script(
            'bpwp-qrcodejs-action',
            'discountCardNumber',
            array(
                'cardNumber' => esc_attr($cardNumber)
            )
        );
    }

    /**
     *  Render customer data
     */
    public static function bpwp_api_render_customer_data()
    {
        if (!is_user_logged_in()) {
            return;
        }

        $phone = !empty(get_user_meta(get_current_user_id(), 'billing_phone', true)) ? get_user_meta(get_current_user_id(), 'billing_phone', true) : '';

        $birth_date = !empty(get_user_meta(get_current_user_id(), 'billing_birth_date', true)) ? get_user_meta(get_current_user_id(), 'billing_birth_date', true) : '';

        $verifiedUser = !empty(get_user_meta(get_current_user_id(), 'bpwp_verified_user', true)) ? get_user_meta(get_current_user_id(), 'bpwp_verified_user', true) : '';

        ob_start();

        if (empty($phone)) {

            printf('<h2>%s</h2>', __('Для регистрации в бонусной программе пожалуйста заполнить платежный адрес и телефон', 'bonus-plus-wp'));
        } else if (empty($birth_date)) {

            printf('<h2>%s</h2>', __('Для регистрации в бонусной программе пожалуйста заполните дату рождения в платежном адресе', 'bonus-plus-wp'));
        } else if (empty($verifiedUser)) {

            self::bpwp_render_verify_phone_form();
        } else {

            //self::bpwp_api_print_customer_card_info();
            self::bpwp_render_verify_phone_form();
        }

        return ob_get_clean();
    }

    /**
     *  Render verify phone form
     */
    public static function bpwp_render_verify_phone_form()
    {
        wp_enqueue_style('bpwp-verify-form-style');
?>
        <div id="wrapper">
            <div id="dialog">
                <button class="close">×</button>
                <span><?= __('Пожалуйста, введите 6-значный код подтверждения, который мы отправили через SMS:', 'bonus-plus-wp') ?></span>
                <div id="form">
                    <input type="text" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" />
                    <input type="text" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" />
                    <input type="text" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" />
                    <input type="text" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" />
                    <input type="text" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" />
                    <input type="text" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" />
                    <button class="btn btn-primary btn-embossed"><?= __('Подтвердить', 'bonus-plus-wp') ?></button>
                </div>

                <div>
                    <?= __('Не получили SMS?', 'bonus-plus-wp') ?><br />
                    <a href="#bpwp-resend-sms"><?= __('Отправить код снова', 'bonus-plus-wp') ?></a><br />
                    <a href="/my-account/edit-address/billing/"><?= __('Изменить номер телефона', 'bonus-plus-wp') ?></a>
                </div>

            </div>
        </div>
<?php
    }

    /**
     * Подключение скриптов для формы верификации телефона
     */
    public static function bpwp_verify_form_scripts()
    {
        wp_register_script(
            'bpwp-verify-form',
            plugins_url('/assets/verify-form/script.js', __DIR__),
            array('jquery'),
            BPWP_PLUGIN_VERSION,
            'in_footer'
        );
    }

    // Adding a custom checkout date field
    public static function bpwp_add_birth_date_billing_field($billing_fields)
    {
        $billing_fields['billing_birth_date'] = array(
            'type'        => 'date',
            'label'       => __('Дата рождения', 'bonus-plus-wp'),
            'class'       => array('form-row-wide'),
            'priority'    => 25,
            'required'    => true,
            'clear'       => true,
        );

        return $billing_fields;
    }
}
BPWPMyAccount::init();
