<?php

/**
 * Plugin Name: Woo-bonusplus
 * Plugin URI: https://github.com/evgrezanov/wooms-bonusplus
 * Description: Integration for WooCommerce and MoySklad (moysklad.ru, МойСклад) via REST API (wooms)
 * Author: redmonkey73
 * Author URI: http://evgeniyrezanov.site
 * Developer: redmonkey73
 * Developer URI: http://evgeniyrezanov.site
 * Text Domain: bonusplus-wp
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * WC requires at least: 4.0
 * WC tested up to: 5.2.0
 * PHP requires at least: 5.6
 * WP requires at least: 5.0
 * Tested up to: 5.7
 * Version: 8.2
 */


defined('ABSPATH') || exit; // Exit if accessed directly

class WooBonusPlus
{
    public static function init()
    {
        define('WOOBPP_PLUGIN_URL', plugins_url('', __FILE__));
        define('WOOBPP_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
        define('WOOBPP_PLUGIN_VERSION', '1.1.0');

        if (!bpwp_is_woocommerce_activated()) {
            return;
        }
        
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