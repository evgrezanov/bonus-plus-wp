<?php

/**
 * Plugin Name: Bonus-Plus-wp
 * Plugin URI: https://github.com/evgrezanov/bonus-plus-wp
 * Description: Интеграция WooCommerce и БонусПлюс. Для отображения данных пользователя используйте шорткод [bpwp_api_customer_bonus_card]
 * Author: redmonkey73
 * Author URI: http://evgeniyrezanov.site
 * Developer: redmonkey73
 * Developer URI: http://evgeniyrezanov.site
 * Text Domain: wp-bonus-plus
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * PHP requires at least: 7.0
 * WP requires at least: 5.0
 * Tested up to: 5.8
 * Version: 1.4
 */
namespace BPWP;

defined('ABSPATH') || exit; // Exit if accessed directly

class BPWPBonusPlus_Core
{
    /**
     *  Init
     */
    public static function init()
    {
        define('BPWP_PLUGIN_VERSION', '1.3');

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
        load_plugin_textdomain('bonus-plus-wp', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /**
     * Load components
     *
     * @return void
     */
    public static function bpwp_load_components()
    {
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            require_once __DIR__ . '/inc/WooAccount.php';
        }
        require_once __DIR__ . '/inc/MenuSettings.php';
        require_once __DIR__ . '/inc/Logger.php';
        require_once __DIR__ . '/inc/ClientProfile.php';
        require_once __DIR__ . '/inc/WooProductCatExport.php';
    }

    /**
     * Register styles for bonus card widget
     *
     * @return void
     */
    public static function bpwp_shortcode_wp_enqueue_styles()
    {
        wp_register_style(
            'bpwp-bonus-card-style', 
            plugins_url('/assets/qrcodejs/style.css', __FILE__), 
            array(),
            BPWP_PLUGIN_VERSION, 
            'all'
        );
        wp_register_style(
            'bpwp-verify-form-style',
            plugins_url('/assets/verify-form/style.css', __FILE__),
            array(),
            BPWP_PLUGIN_VERSION,
            'all'
        );
    }

    /**
     *  Action fire at plugin activation
     *
     *  @return array
     */
    public static function bpwp_plugin_activate()
    {
        flush_rewrite_rules();
        update_option('bpwp_plugin_permalinks_flushed', 0);
    }

    /**
     *  Action fire at plugin deactivation
     *
     *  @return array
     */
    public static function bpwp_plugin_deactivate()
    {
        flush_rewrite_rules();
    }
}
BPWPBonusPlus_Core::init();