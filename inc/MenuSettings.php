<?php

namespace BPWP;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Settings
 */
class BPWPMenuSettings
{
    /**
     *  Client dashboard bonusplus URL 
     * 
     *  @var string
     */
    public static $bpwp_client_url;

    /**
     *  Developer documentation bonusplus URL 
     *  
     *  @var string
     */
    public static $bpwp_dev_doc_url;

    /**
     *  Shop owner dashboard bonusplus URL 
     * 
     *  @var string
     */
    public static $bpwp_owner_url;


    /**
     * The Init
     */
    public static function init()
    {
        self::$bpwp_owner_url = 'https://bonusplus.pro/lk/Pages/Cabinet/Module/Loyalty/API_Preferences.aspx';

        self::$bpwp_dev_doc_url = 'https://bonusplus.pro/api';

        self::$bpwp_client_url = 'https://bonusplus.pro/lk';

        add_action(
            'admin_menu',
            function () {
                if (current_user_can('manage_options')) {
                    add_menu_page(
                        $page_title = __('БонусПлюс', 'bonus-plus-wp'),
                        $menu_title = __('БонусПлюс', 'bonus-plus-wp'),
                        $capability = 'manage_options',
                        $menu_slug = 'bonus-plus',
                        $function = array(__CLASS__, 'display_settings'),
                        $icon = 'dashicons-forms',
                        '57.5'
                    );
                }
            }
        );

        add_action('admin_init', array(__CLASS__, 'settings_general'), $priority = 10, $accepted_args = 1);

        add_action('bpwp_settings_after_header', [__CLASS__, 'render_nav_menu'], 10);

        add_action('bpwp_settings_after_header', [__CLASS__, 'display_status'], 20);
    }

    /**
     * Render top menu at option page
     */
    public static function render_nav_menu()
    {
        printf(
            '<a href="%s" target="_blank">%s</a> | ',
            esc_url(self::$bpwp_client_url),
            esc_html(
                __('Вход в ЛК БонусПлюс', 'bonus-plus-wp')
            )
        );

        printf(
            '<a href="%s" target="_blank">%s</a>',
            esc_url(self::$bpwp_dev_doc_url),
            esc_html(
                __('БонусПлюс для разработчиков', 'bonus-plus-wp')
            )
        );
    }

    /**
     *  Add sections to settiongs page
     * 
     *  @return mixed
     */
    public static function settings_general()
    {

        add_settings_section('bpwp_section_access', __('Данные для доступа Бонус+', 'bonus-plus-wp'), null, 'bpwp-settings');

        register_setting('bpwp-settings', 'bpwp_api_key');
        add_settings_field(
            $id = 'bpwp_api_key',
            $title = __('Ключ API', 'bonus-plus-wp'),
            $callback = array(__CLASS__, 'display_api_key',),
            $page = 'bpwp-settings',
            $section = 'bpwp_section_access'
        );

        register_setting('bpwp-settings', 'bpwp_lk_url');
        add_settings_field(
            $id = 'bpwp_lk_url',
            $title = __('URL Личного кабинета', 'bonus-plus-wp'),
            $callback = array(__CLASS__, 'display_lk_url'),
            $page = 'bpwp-settings',
            $section = 'bpwp_section_access'
        );

        register_setting('bpwp-settings', 'bpwp_user_identification_by');
        add_settings_field(
            $id = 'bpwp_user_identification_by',
            $title = __('Идентифицировать пользователей по', 'bonus-plus-wp'),
            $callback = array(__CLASS__, 'display_user_identification_by'),
            $page = 'bpwp-settings',
            $section = 'bpwp_section_access'
        );

        register_setting('bpwp-settings', 'bpwp_shop_name');
        add_settings_field(
            $id = 'bpwp_shop_name',
            $title = __('Название магазина в Бонус+', 'bonus-plus-wp'),
            $callback = array(__CLASS__, 'display_shop_name'),
            $page = 'bpwp-settings',
            $section = 'bpwp_section_access'
        );

        add_settings_section('bpwp_section_front_msgs', __('Текст виджета бонусной карты', 'bonus-plus-wp'), null, 'bpwp-settings');

        register_setting('bpwp-settings', 'bpwp_msg_know_customers');
        add_settings_field(
            $id = 'bpwp_msg_know_customers',
            $title = __('Идентифицированные пользователи', 'bonus-plus-wp'),
            $callback = array(__CLASS__, 'display_msg_know_customers'),
            $page = 'bpwp-settings',
            $section = 'bpwp_section_front_msgs'
        );

        register_setting('bpwp-settings', 'bpwp_msg_unknow_customers');
        add_settings_field(
            $id = 'bpwp_msg_unknow_customers',
            $title = __('Неопознанные пользователи', 'bonus-plus-wp'),
            $callback = array(__CLASS__, 'display_msg_unknow_customers'),
            $page = 'bpwp-settings',
            $section = 'bpwp_section_front_msgs'
        );
    }

    /**
     * display_msg_know_customers
     * 
     *  @return mixed
     */
    public static function display_msg_know_customers()
    {
        printf(
            '<input class="regular-text" type="text" name="bpwp_msg_know_customers" value="%s"/>',
            esc_attr(get_option('bpwp_msg_know_customers'))
        );

        printf(
            '<p><small>%s <strong>%s</strong></small></p>',
            esc_html(__('Отобразится для пользователей авторизованных на сайте и зарегистрированных в Бонус+, сумма активных и неактивных бонусов у которых больше 0. В тексте можно использовать тэги:', 'bonus-plus-wp')),
            esc_html('discountCardName, purchasesTotalSum, purchasesSumToNextCard, nextCardName, availableBonuses, notActiveBonuses, allBonuses')
        );
    }

    /**
     * display_msg_unknow_customers
     * 
     *  @return mixed
     */
    public static function display_msg_unknow_customers()
    {
        printf(
            '<input class="regular-text" type="text" name="bpwp_msg_unknow_customers" value="%s"/>',
            esc_attr(get_option('bpwp_msg_unknow_customers'))
        );

        printf(
            '<p><small>%s <strong>%s</strong></small></p>',
            esc_html(__('Отобразится для пользователей неавторизованных на сайте, либо не имеющих аккаунта в Бонус+, либо с некорректным', 'bonus-plus-wp')),
            esc_html(__('Платежным номером телефона (billing_phone)', 'bonus-plus-wp'))
        );
    }

    /**
     * display_lk_url
     * 
     *  @return mixed
     */
    public static function display_lk_url()
    {
        printf('<input class="regular-text" type="url" name="bpwp_lk_url" value="%s"/>', esc_url(get_option('bpwp_lk_url')));

        printf('<p><small>%s</small></p>', esc_html(__('Ссылка на личный кабинет Бонус+', 'bonus-plus-wp')));
    }

    /**
     * display_user_identification_by
     * 
     *  @return mixed
     */
    public static function display_user_identification_by()
    {

        $identification_by = get_option('bpwp_user_identification_by');
        ?>
        <select class="check_prefix_postfix" name="bpwp_user_identification_by">
            <?php
            printf(
                '<option value="%s" %s>%s</option>',
                'email',
                selected('email', $identification_by, false),
                'Email'
            );
            printf(
                '<option value="%s" %s>%s</option>',
                'phone',
                selected('phone', $identification_by, false),
                'Телефон'
            );
            printf(
                '<option value="%s" %s>%s</option>',
                'both',
                selected('both', $identification_by, false),
                'Сначала email, затем телефон'
            );
            ?>
        </select>
        <?php
        printf('<p><small>%s</small></p>', esc_html(__('Выберите как идентифицировать клиентов: по email, по номеру телефона или сначала по email, при неудаче по номеру телефона', 'bonus-plus-wp')));
    }

    /**
     * display_api_key
     * 
     *  @return mixed
     */
    public static function display_api_key()
    {
        printf(
            '<input class="regular-text" type="text" name="bpwp_api_key" value="%s"/>',
            esc_attr(get_option('bpwp_api_key'))
        );

        printf(
            '<p><small>%s <a href="%s" target="_blank">%s</a></small></p>',
            esc_html(__('Вводить API Key нужно только  здесь. На стороне БонусПлюс ничего настраивать не нужно. Получить ключ можно', 'bonus-plus-wp')),
            esc_url(self::$bpwp_owner_url),
            esc_html(__('здесь', 'bonus-plus-wp'))
        );
    }

    /**
     * display_shop_name
     * 
     *  @return mixed
     */
    public static function display_shop_name()
    {
        printf(
            '<input class="regular-text" type="text" name="bpwp_shop_name" value="%s"/>',
            esc_attr(get_option('bpwp_shop_name'))
        );

        printf(
            '<p><small>%s</small></p>',
            esc_html(__('Необходим для импорта товаров в Бонус+', 'bonus-plus-wp')),
        );
    }

    /**
     * display_settings
     * 
     *  @return mixed
     */
    public static function display_settings()
    {

    ?>
        <form method="POST" action="options.php">

            <h1><?php esc_html_e('Настройки интеграции Бонус+', 'bonus-plus-wp'); ?></h1>

            <?php do_action('bpwp_settings_after_header') ?>

            <?php

            settings_fields('bpwp-settings');
            do_settings_sections('bpwp-settings');
            submit_button();
            ?>
        </form>


<?php
    }

    /**
     *  Render BonusPlus shop status
     * 
     *  @return mixed
     */
    public static function display_status()
    {
        $info = bpwp_api_request(
            'account',
            '',
            'GET'
        );

        $fields = [
            'balance'   => __('Текущий балланс', 'bonus-plus-wp'),
            'tariff'    => __('Тарифный план', 'bonus-plus-wp'),
            'username'  => __('Пользователь', 'bonus-plus-wp'),
            'smsPrice'  => __('Стоимость SMS', 'bonus-plus-wp'),
            'pushPrice' => __('Стоимость push-уведомлений', 'bonus-plus-wp'),
        ];

        if (!empty($info)) {
            $response_code = $info['code'];
            if ($response_code == 200) {
                $class = 'updated notice is-dismissible';
                printf('<div class="wrap"><div id="message" class="%s"><ul>', esc_attr($class));
                foreach ($info as $key => $value) {
                    if (!is_array($value) && key_exists($key, $fields)) {
                        printf('<li>%s : %s</li>', esc_html($fields[$key]), esc_html($value));
                    }
                }
                print('</ul></div></div>');
            } else {
                $response_msg = $info['message'];
                $class = 'notice notice-error';
                printf('<div class="wrap"><div id="message" class="%s"><ul>', esc_attr($class));
                printf('<li>%s : %s</li>', esc_html($response_code), esc_html($response_msg));
                print('</ul></div></div>');
            } // todo дописать обработку для 412 ошибки, объект Error[msg, devMsg, code]

        }
    }
}

BPWPMenuSettings::init();
