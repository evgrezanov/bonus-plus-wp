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
     * URL action
     */
    public static $url;

    /**
     *  page id of Category export wiki URL
     */
    public static $product_cat_export_wiki_page_url;

    /**
     *  needHelpUrl
     */
    public static $need_help_url;

    /**
     * The Init
     */
    public static function init()
    {
        self::$bpwp_owner_url = 'https://bonusplus.pro/api/link/KEETCJEL';

        self::$bpwp_dev_doc_url = 'https://bonusplus.pro/api';

        self::$bpwp_client_url = 'https://bonusplus.pro/api/link/KEETCJEL';

        self::$url = $_SERVER['REQUEST_URI'];

        self::$product_cat_export_wiki_page_url = 'https://bonuspluswp.site/category/docs/';

        self::$need_help_url = 'https://bonuspluswp.site/request/';

        add_action(
            'admin_menu',
            function () {
                if (current_user_can('manage_options')) {
                    add_menu_page(
                        $page_title = 'Настройки подключения БонусПлюс',
                        $menu_title = 'Бонус+',
                        $capability = 'manage_options',
                        $menu_slug = 'bpwp-settings',
                        $function = array(__CLASS__, 'display_settings'),
                        $icon = 'dashicons-forms',
                        '57.5'
                    );
                }

                if (current_user_can('manage_options')) {
                    add_submenu_page(
                        'bpwp-settings',
                        'Экспорт',
                        'Экспорт каталога',
                        'manage_options',
                        'bpwp-connection',
                        array(__CLASS__, 'display_control')
                    );
                    add_submenu_page(
                        'bpwp-settings',
                        'Настройка виджета',
                        'Виджет',
                        'manage_options',
                        'bpwp-widget',
                        array(__CLASS__, 'display_widget_settings')
                    );
                }
            }
        );

        add_action('admin_init', array(__CLASS__, 'settings_general'), $priority = 10, $accepted_args = 1);

        add_action('bpwp_settings_after_header', [__CLASS__, 'render_nav_menu'], 10);

        add_action('bpwp_settings_after_header', [__CLASS__, 'display_status'], 20);

        add_action('bpwp_widget_settings_after_header', [__CLASS__, 'render_widget_nav_menu'], 10);
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
            '<a href="%s" target="_blank">%s</a> | ',
            esc_url(self::$bpwp_dev_doc_url),
            esc_html(
                __('БонусПлюс для разработчиков', 'bonus-plus-wp')
            )
        );

        printf(
            '<a href="%s" target="_blank" style="color: green; font-weight: bold;">%s</a>',
            esc_url(self::$need_help_url),
            esc_html(
                __('Нужна помошь?', 'bonus-plus-wp')
            )
        );
    }

    public static function render_widget_nav_menu()
    {
        printf(
            '<a href="%s" target="_blank">%s</a> | ',
            esc_url(self::$product_cat_export_wiki_page_url),
            esc_html(
                __('Экспорт товаров и категорий', 'bonus-plus-wp')
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

        register_setting('bpwp-settings', 'bpwp_shop_name');
        add_settings_field(
            $id = 'bpwp_shop_name',
            $title = __('Название магазина в Бонус+', 'bonus-plus-wp'),
            $callback = array(__CLASS__, 'display_shop_name'),
            $page = 'bpwp-settings',
            $section = 'bpwp_section_access'
        );

        add_settings_section('bpwp_section_front_msgs', __('Текст и ссылка виджета бонусной карты', 'bonus-plus-wp'), null, 'bpwp-widget');

        register_setting('bpwp-widget', 'bpwp_msg_know_customers');
        add_settings_field(
            $id = 'bpwp_msg_know_customers',
            $title = __('Текст для Идентифицированных пользователей', 'bonus-plus-wp'),
            $callback = array(__CLASS__, 'display_msg_know_customers'),
            $page = 'bpwp-widget',
            $section = 'bpwp_section_front_msgs'
        );

        register_setting('bpwp-widget', 'bpwp_msg_unknow_customers');
        add_settings_field(
            $id = 'bpwp_msg_unknow_customers',
            $title = __('Текст для неопознанных пользователей', 'bonus-plus-wp'),
            $callback = array(__CLASS__, 'display_msg_unknow_customers'),
            $page = 'bpwp-widget',
            $section = 'bpwp_section_front_msgs'
        );

        register_setting('bpwp-widget', 'bpwp_msg_customers_not_verify_phone_number');
        add_settings_field(
            $id = 'bpwp_msg_customers_not_verify_phone_number',
            $title = __('Ссылка для пользователей не верифицировавших номер телефона', 'bonus-plus-wp'),
            $callback = array(__CLASS__, 'display_msg_customers_not_verify_phone_number'),
            $page = 'bpwp-widget',
            $section = 'bpwp_section_front_msgs'
        );

        register_setting('bpwp-widget', 'bpwp_uri_customers_lk_billing_address');
        add_settings_field(
            $id = 'bpwp_uri_customers_lk_billing_address',
            $title = __('Ссылка для добавления телефона и даты рождения в ЛК', 'bonus-plus-wp'),
            $callback = array(__CLASS__, 'display_uri_customers_lk_billing_address'),
            $page = 'bpwp-widget',
            $section = 'bpwp_section_front_msgs'
        );        

        register_setting('bpwp-widget', 'bpwp_uri_know_customers');
        add_settings_field(
            $id = 'bpwp_uri_know_customers',
            $title = __('Ссылка для идентифицированных пользователей', 'bonus-plus-wp'),
            $callback = array(__CLASS__, 'display_uri_know_customers'),
            $page = 'bpwp-widget',
            $section = 'bpwp_section_front_msgs'
        );

        register_setting('bpwp-widget', 'bpwp_uri_unknow_customers');
        add_settings_field(
            $id = 'bpwp_uri_unknow_customers',
            $title = __('Ссылка для неопознанных пользователей', 'bonus-plus-wp'),
            $callback = array(__CLASS__, 'display_uri_unknow_customers'),
            $page = 'bpwp-widget',
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
     * display_msg_customers_not_verify_phone_number
     * 
     *  @return mixed
     */
    public static function display_msg_customers_not_verify_phone_number()
    {
        printf(
            '<input class="regular-text" type="text" name="bpwp_msg_customers_not_verify_phone_number" value="%s"/>',
            esc_attr(get_option('bpwp_msg_customers_not_verify_phone_number'))
        );

        printf(
            '<p><small>%s</small></p>',
            esc_html(__('Отобразится для пользователя который не верифицировал номер телефона в профиле', 'bonus-plus-wp')),
        );
    }

    /**
     * display_uri_customers_lk_billing_address
     * 
     * @return mixed
     */
    public static function display_uri_customers_lk_billing_address()
    {
        printf(
            '<input class="regular-text" type="text" name="bpwp_uri_customers_lk_billing_address" value="%s"/>',
            esc_attr(get_option('bpwp_uri_customers_lk_billing_address'))
        );

        printf(
            '<p><small>%s</small></p>',
            esc_html(__('Ссылка для заполнения биллинг адреса в ЛК пользователя', 'bonus-plus-wp')),
        );
    }
    
    /**
     * display_uri_know_customers
     * 
     *  @return mixed
     */
    public static function display_uri_know_customers()
    {
        printf(
            '<input class="regular-text" type="text" name="bpwp_uri_know_customers" value="%s"/>',
            esc_attr(get_option('bpwp_uri_know_customers'))
        );

        printf(
            '<p><small>%s</small></p>',
            esc_html(__('Отобразится для пользователей авторизованных на сайте и зарегистрированных в Бонус+', 'bonus-plus-wp'))
        );
    }

    /**
     * display_uri_unknow_customers
     * 
     *  @return mixed
     */
    public static function display_uri_unknow_customers()
    {
        printf(
            '<input class="regular-text" type="text" name="bpwp_uri_unknow_customers" value="%s"/>',
            esc_attr(get_option('bpwp_uri_unknow_customers'))
        );

        printf(
            '<p><small>%s</small></p>',
            esc_html(__('Отобразится для пользователей неавторизованных на сайте, либо не имеющих аккаунта в Бонус+', 'bonus-plus-wp')),
        );
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
            esc_html(__('Необходим для импорта товаров в Бонус+', 'bonus-plus-wp'))
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
            if (is_array($info) && isset($info['balance'])) {
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

    /**
     * Display Control page UI
     * 
     *  @return mixed
     */
    public static function display_control()
    { 
        ?>
        <h1><?php esc_html_e('Экспорт товаров в Бонус+', 'bonus-plus-wp'); ?></h1>

        <?php

        do_action('bpwp_widget_settings_after_header');
        
        if (empty($_GET['a'])) {

            do_action('bpwp_tool_actions_btns');

            do_action('bpwp_tool_actions_message');

        } else {

            printf('<a href="%s">Вернуться...</a>', esc_url(remove_query_arg('a', self::$url)) );
            // TODO Processing form data without nonce verification.
            // if ( isset( $_GET['a'] ) && isset( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], 'your_action_nonce' ) ) {
            //do_action( 'bpwp_tool_actions_' . $_GET['a'] );
            //}
            do_action('bpwp_tool_actions_' . $_GET['a']);

            do_action('bpwp_tool_actions_message');
            
        }

        //do_action('bpwp_tools_sections');
    }

    /**
     * Display Settings Widget page UI
     * 
     *  @return mixed
     */
    public static function display_widget_settings()
    {
        ?>
            <form method="POST" action="options.php">
                <h1><?php esc_html_e('Настройка виджета карты Бонус+', 'bonus-plus-wp'); ?></h1>

                <?php
                    settings_fields('bpwp-widget');
                    do_settings_sections('bpwp-widget', 'bpwp_section_front_msgs');
                    submit_button();
                ?>
        
            </form>
        <?php
    }
}

BPWPMenuSettings::init();
