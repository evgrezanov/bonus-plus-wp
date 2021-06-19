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
                    'Настройки',
                    'Настройки',
                    'manage_options',
                    'bpwp-settings',
                    array(__CLASS__, 'display_settings')
                );
            },
            30
        );

        add_action('admin_init', array(__CLASS__, 'settings_general'), $priority = 10, $accepted_args = 1);

        add_action('bpwp_settings_after_header', [__CLASS__, 'render_nav_menu']);
    }

    public static function render_nav_menu()
    {

        $nav_items = [
            'lk' => sprintf('<a href="%s" target="_blank">%s</a>', 'https://bonusplus.pro/lk', 'Вход в ЛК БонусПлюс'),
            'diagnostic' => sprintf('<a href="%s">%s</a>', admin_url('site-health.php'), 'Диагностика проблем'),
            'api-docs' => sprintf('<a href="%s" target="_blank">%s</a>', 'https://bonusplus.pro/api', 'БонусПлюс для разработчиков'),
        ];

        //$nav_items = apply_filters('wooms_settings_nav_items', $nav_items);

        echo implode(' | ', $nav_items);
    }

    public static function settings_general()
    {

        add_settings_section('bpwp_section_access', 'Данные для доступа БонусПлюс', null, 'bpwp-settings');

        register_setting('bpwp-settings', 'bpwp_api_key');
        add_settings_field(
            $id = 'bpwp_api_key',
            $title = 'API Key',
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

        add_settings_section('bpwp_section_front_msgs', 'Сообщения для пользователя', null, 'bpwp-settings');
        
        register_setting('bpwp-settings', 'bpwp_msg_not_reg');
        add_settings_field(
            $id = 'bpwp_msg_not_reg',
            $title = 'Сообщение для незарегистрированного пользователя',
            $callback = array(__CLASS__, 'display_lk_url'),
            $page = 'bpwp-settings',
            $section = 'bpwp_section_front_msgs'
        );

        register_setting('bpwp-settings', 'bpwp_msg_loggin');
        add_settings_field(
            $id = 'bpwp_msg_loggin',
            $title = 'Сообщение для авторизованного пользователя',
            $callback = array(__CLASS__, 'display_lk_url'),
            $page = 'bpwp-settings',
            $section = 'bpwp_section_front_msgs'
        );

        register_setting('bpwp-settings', 'bpwp_msg_not_loggin');
        add_settings_field(
            $id = 'bpwp_msg_not_loggin',
            $title = 'Сообщение для неавторизованного пользователя',
            $callback = array(__CLASS__, 'display_lk_url'),
            $page = 'bpwp-settings',
            $section = 'bpwp_section_front_msgs'
        );
    }

    /**
     * display_lk_url
     */
    public static function display_lk_url()
    {
        printf('<input type="url" name="bpwp_lk_url" value="%s"/>', get_option('bpwp_lk_url'));
    }

    /**
     * display_api_key
     */
    public static function display_api_key()
    {
        printf('<input type="text" name="bpwp_api_key" value="%s"/>', get_option('bpwp_api_key'));

        printf('<p>Вводить нужно только API Key здесь. На стороне БонусПлюс ничего настраивать не нужно. Получить ключ можно <a href="%s" target="_blank">здесь</a></p>', BPWP_LK_URL);
    }

    /**
     * display_settings
     */
    public static function display_settings()
    {

        ?>
        <form method="POST" action="options.php">

            <h1>Настройки интеграции БонусПлюс</h1>

            <?php do_action('bpwp_settings_after_header') ?>

            <?php

            settings_fields('bpwp-settings');
            do_settings_sections('bpwp-settings');
            submit_button();
            ?>
        </form>


<?php 

        printf('<p><a href="%s">Управление синхронизацией</a></p>', admin_url('admin.php?page=moysklad'));
        printf('<p><a href="%s" target="_blank">Расширенная версия с дополнительными возможностями</a></p>', "https://wpcraft.ru/product/wooms-extra/");
        printf('<p><a href="%s" target="_blank">Предложения по улучшению и запросы на доработку</a></p>', "https://github.com/wpcraft-ru/wooms/issues");
        printf('<p><a href="%s" target="_blank">Рекомендуемые хостинги</a></p>', "https://wpcraft.ru/wordpress/hosting/");
        printf('<p><a href="%s" target="_blank">Сопровождение магазинов и консалтинг</a></p>', "https://wpcraft.ru/wordpress-woocommerce-mentoring/");
        printf('<p><a href="%s" target="_blank">Помощь и техическая поддержка</a></p>', "https://wpcraft.ru/contacts/");
    }
}
MenuSettings::init();
