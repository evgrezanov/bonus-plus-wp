<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Settings
 */
class MenuSettings
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
                add_submenu_page(
                    'bonusplus',
                    'БонусПлюс',
                    'БонусПлюс',
                    'manage_options',
                    'bpwp-settings',
                    array(__CLASS__, 'display_settings')
                );
            },
            30
        );

        add_action('admin_init', array(__CLASS__, 'settings_general'), $priority = 10, $accepted_args = 1);

        add_action('bpwp_settings_after_header', [__CLASS__, 'render_nav_menu']);

        add_action('bpwp_settings_after_header', [__CLASS__, 'display_status']);
    }

    /**
     * Render top menu at option page
     */
    public static function render_nav_menu()
    {

        $nav_items = [
            'lk' => sprintf('<a href="%s" target="_blank">%s</a>', 'https://bonusplus.pro/lk', 'Вход в ЛК БонусПлюс'),
            'diagnostic' => sprintf('<a href="%s">%s</a>', admin_url('site-health.php'), 'Диагностика проблем'),
            'api-docs' => sprintf('<a href="%s" target="_blank">%s</a>', 'https://bonusplus.pro/api', 'БонусПлюс для разработчиков'),
        ];

        echo implode(' | ', $nav_items);
    }

    /**
     *  Add sections to settiongs page
     */
    public static function settings_general()
    {

        add_settings_section('bpwp_section_access', 'Данные для доступа БонусПлюс', null, 'bpwp-settings');

        register_setting('bpwp-settings', 'bpwp_api_key');
        add_settings_field(
            $id = 'bpwp_api_key',
            $title = 'Ключ API',
            $callback = array(__CLASS__, 'display_api_key',),
            $page = 'bpwp-settings',
            $section = 'bpwp_section_access'
        );

        register_setting('bpwp-settings', 'bpwp_lk_url');
        add_settings_field(
            $id = 'bpwp_lk_url',
            $title = 'URL Личного кабинета',
            $callback = array(__CLASS__, 'display_lk_url'),
            $page = 'bpwp-settings',
            $section = 'bpwp_section_access'
        );

        add_settings_section('bpwp_section_front_msgs', 'Текст виджета', null, 'bpwp-settings');
        
        register_setting('bpwp-settings', 'bpwp_msg_not_reg');
        add_settings_field(
            $id = 'bpwp_msg_not_signup',
            $title = 'Для незарегистрированного пользователя',
            $callback = array(__CLASS__, 'display_msg_not_signup'),
            $page = 'bpwp-settings',
            $section = 'bpwp_section_front_msgs'
        );

        register_setting('bpwp-settings', 'bpwp_msg_loggin');
        add_settings_field(
            $id = 'bpwp_msg_login',
            $title = 'Для авторизованного пользователя',
            $callback = array(__CLASS__, 'display_msg_login'),
            $page = 'bpwp-settings',
            $section = 'bpwp_section_front_msgs'
        );

        register_setting('bpwp-settings', 'bpwp_msg_not_loggin');
        add_settings_field(
            $id = 'bpwp_msg_not_login',
            $title = 'Для неавторизованного пользователя',
            $callback = array(__CLASS__, 'display_msg_not_login'),
            $page = 'bpwp-settings',
            $section = 'bpwp_section_front_msgs'
        );
    }

    /**
     * display_msg_login
     */
    public static function display_msg_not_signup()
    {
        printf('<input class="regular-text" type="url" name="bpwp_msg_not_signup" value="%s"/>', get_option('bpwp_msg_not_signup'));

        printf('<p>%s</p>', 'Отобразится для пользователей авторизовавшихся на сайте.');
    }

    /**
     * display_msg_login
     */
    public static function display_msg_login()
    {
        printf('<input class="regular-text" type="url" name="bpwp_msg_not_login" value="%s"/>', get_option('bpwp_msg_not_login'));

        printf('<p>%s</p>', 'Отобразится для пользователей авторизовавшихся на сайте.');
    }

    /**
     * display_msg_not_login
     */
    public static function display_msg_not_login()
    {
        printf('<input class="regular-text" type="url" name="bpwp_msg_not_login" value="%s"/>', get_option('bpwp_msg_not_login'));

        printf('<p>%s</p>', 'Отобразится для пользователей не вошедших на сайт.');
    }

    /**
     * display_lk_url
     */
    public static function display_lk_url()
    {
        printf('<input class="regular-text" type="url" name="bpwp_lk_url" value="%s"/>', get_option('bpwp_lk_url'));

        printf('<p>%s</p>', 'Ссылка на личный кабинет Бонус+.');

    }

    /**
     * display_api_key
     */
    public static function display_api_key()
    {
        printf('<input class="regular-text" type="text" name="bpwp_api_key" value="%s"/>', get_option('bpwp_api_key'));

        printf('<p>Вводить нужно только API Key здесь. На стороне БонусПлюс ничего настраивать не нужно. Получить ключ можно <a href="%s" target="_blank">здесь</a></p>', BPWP_LK_URL);
    }

    /**
     * display_settings
     */
    public static function display_settings()
    {

        ?>
        <form method="POST" action="options.php">

            <h1>Настройки интеграции Бонус+</h1>

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
     * 
     */
    public static function display_status()
    {
		$res = bpwp_api_request(
			'account',
			'',
			'GET'
		);

		$info = json_decode($res);
        print('<br />');
        print('<div class="wrap">');
        print('<div id="message" class="notice notice-warning">');
        
		foreach ($info as $key => $value) :
			if ($key == 'balance') {
				print('Балланс: ' . $value . '<br />');
			}
			if ($key == 'tariff') {
				print('Тарифф: ' . $value . '<br />');
			}
			if ($key == 'smsPrice') {
				print('Стоимость СМС: ' . $value . '<br />');
			}
			if ($key == 'pushPrice') {
				print('Стоимость push: ' . $value . '<br />');
			}
		endforeach;

        print('</div></div>');
			
    }
}
MenuSettings::init();
