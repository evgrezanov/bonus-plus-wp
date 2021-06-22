<?php

namespace BPWP;

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
                if (current_user_can('manage_options')) {
                    add_menu_page(
                        $page_title = 'БонусПлюс',
                        $menu_title = 'БонусПлюс',
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

        $nav_items = [
            'lk' => sprintf('<a href="%s" target="_blank">%s</a>', 'https://bonusplus.pro/lk', 'Вход в ЛК БонусПлюс'),
            'diagnostic' => sprintf('<a href="%s">%s</a>', admin_url('site-health.php'), 'Диагностика проблем'),
            'api-docs' => sprintf('<a href="%s" target="_blank">%s</a>', 'https://bonusplus.pro/api', 'БонусПлюс для разработчиков'),
        ];

        echo implode(' | ', $nav_items);
    }

    /**
     *  Add sections to settiongs page
     * 
     *  @return mixed
     */
    public static function settings_general()
    {

        add_settings_section('bpwp_section_access', 'Данные для доступа Бонус+', null, 'bpwp-settings');

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

        add_settings_section('bpwp_section_front_msgs', 'Текст виджета бонусной карты', null, 'bpwp-settings');
        
        register_setting('bpwp-settings', 'bpwp_msg_know_customers');
        add_settings_field(
            $id = 'bpwp_msg_know_customers',
            $title = 'Идентифицированные пользователи',
            $callback = array(__CLASS__, 'display_msg_know_customers'),
            $page = 'bpwp-settings',
            $section = 'bpwp_section_front_msgs'
        );

        register_setting('bpwp-settings', 'bpwp_msg_unknow_customers');
        add_settings_field(
            $id = 'bpwp_msg_unknow_customers',
            $title = 'Неопознанные пользователи',
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
        printf('<input class="regular-text" type="text" name="bpwp_msg_know_customers" value="%s"/>', get_option('bpwp_msg_know_customers'));
        printf('<p><small>%s</small></p>', 'Отобразится для пользователей авторизованных на сайте и зарегистрированных в Бонус+, сумма активных и неактивных бонусов у которых больше 0. В тексте можно использовать тэги: <strong>discountCardName, purchasesTotalSum, purchasesSumToNextCard, nextCardName, availableBonuses, notActiveBonuses, allBonuses</strong>');

    }

    /**
     * display_msg_unknow_customers
     * 
     *  @return mixed
     */
    public static function display_msg_unknow_customers()
    {
        printf('<input class="regular-text" type="text" name="bpwp_msg_unknow_customers" value="%s"/>', get_option('bpwp_msg_unknow_customers'));

        printf('<p><small>%s</small></p>', 'Отобразится для пользователей неавторизованных на сайте, либо не имеющих аккаунта в Бонус+, либо с некорректным <strong>Платежным номером телефона (billing_phone)</strong>');
    }

    /**
     * display_lk_url
     * 
     *  @return mixed
     */
    public static function display_lk_url()
    {
        printf('<input class="regular-text" type="url" name="bpwp_lk_url" value="%s"/>', get_option('bpwp_lk_url'));

        printf('<p><small>%s</small></p>', 'Ссылка на личный кабинет Бонус+');

    }

    /**
     * display_api_key
     * 
     *  @return mixed
     */
    public static function display_api_key()
    {
        printf('<input class="regular-text" type="text" name="bpwp_api_key" value="%s"/>', get_option('bpwp_api_key'));

        printf('<p><small>Вводить нужно только API Key здесь. На стороне БонусПлюс ничего настраивать не нужно. Получить ключ можно <a href="%s" target="_blank">здесь</a></small></p>', BPWP_LK_URL);
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
     *  Render BonusPlus shop status
     * 
     *  @return mixed
     */
    public static function display_status()
    {
		$res = bpwp_api_request(
			'account',
			'',
			'GET'
		);

		$info = json_decode($res);
        if ( !empty($info) ) {
            print('<div class="wrap">');
            print('<div id="message" class="updated notice is-dismissible">');
            print('<ul>');
            foreach ($info as $key => $value) {
                if (!is_array($value)) {
                    printf('<li>%s : %s</li>', $key, $value);
                }
            }
            print('</ul></div></div>');
        }
			
    }
}
MenuSettings::init();
