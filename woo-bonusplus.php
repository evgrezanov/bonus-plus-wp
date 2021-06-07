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
 * Version: 1.0.1
 */


defined('ABSPATH') || exit; // Exit if accessed directly

class WooBonusPlus_Core
{

    public static function init()
    {
        define('BPWP_PLUGIN_VERSION', '1.0.1');

        require_once __DIR__ . '/functions.php';

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
        add_action('plugins_loaded', [__CLASS__, 'bpwp_load_components']);
        
        add_action('bpwp_activate', [__CLASS__, 'bpwp_plugin_activate']);
        add_action('bpwp_deactivate', [__CLASS__, 'bpwp_plugin_deactivate']);

        add_action('wp_enqueue_scripts', [__CLASS__, 'bpwp_shortcode_wp_enqueue_styles']);
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

    /**
     * Load components
     *
     * @return void
     */
    public static function bpwp_load_components()
    {
        /*if (!bpwp_is_woocommerce_activated()) {
            return;
        }*/
        require_once __DIR__ . '/inc/my-account.php';
        require_once __DIR__ . '/inc/profile.php';
        require_once __DIR__ . '/inc/settings.php';
    }

    /**
     * Register styles
     *
     * @return void
     */
    public static function bpwp_shortcode_wp_enqueue_styles()
    {
        wp_register_style(
            'bpwp-bonus-card-style', 
            plugins_url('/assets/style.css', __FILE__), 
            array(),
            BPWP_PLUGIN_VERSION, 
            'all'
        );
    }

    /**
     *  Action fire at plugin activation
     */
    public static function bpwp_plugin_activate()
    {
        flush_rewrite_rules();
    }

    /**
     *  Action fire at plugin deactivation
     */
    public static function bpwp_plugin_deactivate()
    {
        flush_rewrite_rules();
    }
}
WooBonusPlus_Core::init();