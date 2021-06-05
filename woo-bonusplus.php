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

class WooBonusPlus_Core
{
    /**
     * $wooms_version
     */
    public static $bpwp_version;

    /**
     * $plugin_file_path
     */
    public static $bpwp_plugin_file_path;

    public static function init()
    {
        require_once __DIR__ . '/functions.php';
        require_once __DIR__ . '/inc/settings.php';

        if (!bpwp_is_woocommerce_activated()) {
            return;
        }

        require_once __DIR__ . '/inc/my-account.php';
        require_once __DIR__ . '/inc/profile.php';

        /**
         * Add hook for activate plugin
         */
        register_activation_hook(__FILE__, function () {
            do_action('bpwp_activate');
        });

        register_deactivation_hook(__FILE__, function () {
            do_action('bpwp_deactivate');
        });

        add_action('plugins_loaded', [__CLASS__, 'bpwp_true_load_plugin_textdomain']);

    }

    /**
     * Add languages
     *
     * @return void
     */
    public static function bpwp_true_load_plugin_textdomain()
    {
        load_plugin_textdomain('bonusplus-wp', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }
    
}
WooBonusPlus_Core::init();