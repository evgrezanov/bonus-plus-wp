<?php

/**
 * Plugin Name: Woo-bonusplus
 * Plugin URI: https://github.com/evgrezanov/wooms-bonusplus
 * Description: Интеграция WooCommerce и БонусПлюс. Для отображения данных пользователя используйте шорткод [bpwp_api_customer_bonus_card]
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
 * Version: 1.0.2-dev
 */


defined('ABSPATH') || exit; // Exit if accessed directly

class WooBonusPlus_Core
{
    /**
     *  Init
     */
    public static function init()
    {
        define('BPWP_PLUGIN_VERSION', '1.0.2-dev');

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
        add_action('wp_enqueue_scripts', [__CLASS__, 'bpwp_qrcode_scripts']);
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
        require_once __DIR__ . '/inc/WooAccount.php';
        require_once __DIR__ . '/inc/profile.php';
        require_once __DIR__ . '/inc/settings.php';
        require_once __DIR__ . '/inc/MenuSettings.php';
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
     * Register scripts
     *
     * @return void
     */
    public static function bpwp_qrcode_scripts()
    {
        wp_enqueue_script(
            'bpwp-qrcodejs',
            plugins_url('/assets/qrcodejs/qrcode.min.js', __FILE__),
            array(),
            BPWP_PLUGIN_VERSION,
            'in_footer'
        );
        wp_enqueue_script(
            'bpwp-qrcodejs-action',
            plugins_url('/assets/script.js', __FILE__),
            array('bpwp-qrcodejs'),
            BPWP_PLUGIN_VERSION,
            'in_footer'
        );

        $cardNumber = '';

        if ( is_user_logged_in() ) {
            $cardNumber = get_user_meta(get_current_user_id(), 'bpwp_discountCardNumber', true);
        }

        wp_localize_script(
            'bpwp-qrcodejs-action',
            'discountCardNumber', 
            array(
                'cardNumber' => $cardNumber
            )
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
WooBonusPlus_Core::init();