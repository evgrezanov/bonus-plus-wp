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
     * The Init
     */
    public static function init()
    {
        define('BPWP_LK_URL', 'https://bonusplus.pro/lk/Pages/Cabinet/Module/Loyalty/API_Preferences.aspx');

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
        $html = '';
        $html .= sprintf(
            '<a href="%s" target="_blank">%s</a> | ', 
            esc_url('https://bonusplus.pro/lk'),
            esc_html(__('Вход в ЛК БонусПлюс', 'bonus-plus-wp'))
        );
        $html .= sprintf(
            '<a href="%s">%s</a> | ', 
            esc_url(admin_url('site-health.php')),
            esc_html(__('Диагностика проблем', 'bonus-plus-wp'))
        );
        $html .= sprintf(
            '<a href="%s" target="_blank">%s</a>', 
            esc_url('https://bonusplus.pro/api'), 
            esc_html(__('БонусПлюс для разработчиков', 'bonus-plus-wp'))
        );
        echo $html;
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
            $title = __( 'Ключ API', 'bonus-plus-wp'),
            $callback = array(__CLASS__, 'display_api_key',),
            $page = 'bpwp-settings',
            $section = 'bpwp_section_access'
        );

        register_setting('bpwp-settings', 'bpwp_lk_url');
        add_settings_field(
            $id = 'bpwp_lk_url',
            $title = __( 'URL Личного кабинета', 'bonus-plus-wp'),
            $callback = array(__CLASS__, 'display_lk_url'),
            $page = 'bpwp-settings',
            $section = 'bpwp_section_access'
        );

        add_settings_section('bpwp_section_front_msgs', __( 'Текст виджета бонусной карты', 'bonus-plus-wp'), null, 'bpwp-settings');
        
        register_setting('bpwp-settings', 'bpwp_msg_know_customers');
        add_settings_field(
            $id = 'bpwp_msg_know_customers',
            $title = __( 'Идентифицированные пользователи', 'bonus-plus-wp'),
            $callback = array(__CLASS__, 'display_msg_know_customers'),
            $page = 'bpwp-settings',
            $section = 'bpwp_section_front_msgs'
        );

        register_setting('bpwp-settings', 'bpwp_msg_unknow_customers');
        add_settings_field(
            $id = 'bpwp_msg_unknow_customers',
            $title = __( 'Неопознанные пользователи', 'bonus-plus-wp'),
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
            esc_url(BPWP_LK_URL),
            esc_html(__('здесь', 'bonus-plus-wp'))
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

        if ( !empty($info) ) {
            $response_code = $info['code'];
            $response_code != 200 ? $class = 'notice notice-error' : $class = 'updated notice is-dismissible';
            printf('<div class="wrap"><div id="message" class="%s"><ul>', esc_attr($class) );
            foreach ($info as $key => $value) {
                if (!is_array($value)) {
                    $hkey = $fields[esc_html($key)];
                    if (!empty($hkey)){
                        printf('<li>%s : %s</li>', esc_html($hkey), esc_html($value));
                    }
                }
            }
            print('</ul></div></div>');
        }
			
    }
}
BPWPMenuSettings::init();
