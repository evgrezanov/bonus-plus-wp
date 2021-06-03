<?php
//namespace WooBonusPlus;
/**
 * Plugin Name: Woo-bonusplus
 * Plugin URI: https://github.com/evgrezanov/wooms-bonusplus
 * Description: Добавляет механизм отображения бонусов в Woocommerce. 
 * Version: 1.1.0
 */

defined('ABSPATH') || exit; // Exit if accessed directly

class WooBonusPlus
{
    public static function init()
    {
        define('WOOBPP_PLUGIN_URL', plugins_url('', __FILE__));
        define('WOOBPP_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
        define('WOOBPP_PLUGIN_VERSION', '1.1.0');

        add_action('plugins_loaded', [__CLASS__, 'inc_components']);
    }

    public static function inc_components()
    {
        require_once WOOBPP_PLUGIN_DIR_PATH . '/inc/settings.php';
        require_once WOOBPP_PLUGIN_DIR_PATH . '/inc/api.php';
        require_once WOOBPP_PLUGIN_DIR_PATH . '/inc/profile.php';
    }

}
WooBonusPlus::init();