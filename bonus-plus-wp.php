<?php
/**
 * Plugin Name: Bonus-Plus-wp
 * Plugin URI: https://bonuspluswp.site/
 * Description: Интеграция WooCommerce и БонусПлюс. Для отображения данных пользователя используйте шорткод [bpwp_api_customer_bonus_card]
 * Author: redmonkey73
 * Author URI: https://github.com/evgrezanov/
 * Developer: redmonkey73
 * Developer URI: https://github.com/evgrezanov/
 * Text Domain: bonus-plus-wp
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Playground: https://raw.githubusercontent.com/evgrezanov/bonus-plus-wp/main/blueprints/blueprint.json
 * PHP requires at least: 8.1
 * WP requires at least: 6.0
 * Tested up to: 6.6.1
 * Version: 2.3.2
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
        define('BPWP_PLUGIN_VERSION', '2.3.2');

        define('BPWP_PLUGIN_DIR', plugin_dir_path(__FILE__));

        // Plugin Folder Name.
        if ( ! defined( 'BPWP_NAME' ) ) {
            define( 'BPWP_NAME', trim( dirname( plugin_basename( __FILE__ ) ), '/' ) );
        }

        // Plugin Root File.
        if ( ! defined( 'BPWP_PLUGIN_FILE' ) ) {
            define( 'BPWP_PLUGIN_FILE', __FILE__ );
        }

        // Plugin Dir including the folder.
        if ( ! defined('BPWP_DIR' ) ) {
            define( 'BPWP_DIR', BPWP_PLUGIN_DIR . '/' . BPWP_NAME );
        }

        // Plugin URL including the folder.
        if ( ! defined('BPWP_URL' ) ) {
            define( 'BPWP_URL', WP_PLUGIN_URL . '/' . BPWP_NAME );
        }

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

        // Add to `admin_init` safe redirect to welcome page
	    add_action( 'admin_init', [__CLASS__, 'bpwp_safe_welcome_redirect']);
        
        add_action('bpwp_activate', [__CLASS__, 'bpwp_plugin_activate']);
        add_action('bpwp_deactivate', [__CLASS__, 'bpwp_plugin_deactivate']);

        add_action('bpwp_welcome_activate', [__CLASS__, 'bpwp_welcome_activate']);

        add_action('wp_enqueue_scripts', [__CLASS__, 'bpwp_shortcode_wp_enqueue_styles']);

        add_filter('plugin_action_links_bonus-plus-wp/bonus-plus-wp.php', [__CLASS__, 'bpwp_add_support_link']);

        add_filter('plugin_row_meta', [__CLASS__, 'bpwp_plugin_row_meta'], 10, 2);
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
            require_once __DIR__ . '/inc/RestApiEndpoints.php';
            require_once __DIR__ . '/inc/WooAccount.php';
            require_once __DIR__ . '/inc/ApiHelper.php';

            require_once __DIR__ . '/inc/MenuSettings.php';
        }
        require_once __DIR__ . '/inc/Logger.php';
        require_once __DIR__ . '/inc/ClientProfile.php';
        require_once __DIR__ . '/inc/WooProductCatExport.php';
        require_once __DIR__ . '/inc/CustomerBalance.php';
        require_once __DIR__ . '/inc/PhoneRegistration.php';
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
            [],
            BPWP_PLUGIN_VERSION, 
            'all'
        );
        wp_register_style(
            'bpwp-bonus-loader-style',
            plugins_url('/assets/loader.css', __FILE__),
            [],
            BPWP_PLUGIN_VERSION,
            'all'
        );
        wp_register_style(
            'bpwp-user-qr-card-style',
            plugins_url('/assets/qrcard.css', __FILE__),
            [],
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

        do_action('bpwp_welcome_activate');
    }

    /**
	 * Add the transient.
	 *
	 * Add the welcome page transient.
	 *
	 * @since 2.3.1
	 */
	public static function bpwp_welcome_activate() {
		// Transient max age is 60 seconds.
		set_transient( '_welcome_redirect_bpwp', true, 60 );
	}

    /**
     *  Action fire at plugin deactivation
     *
     *  @return array
     *  
     *  @since   2.3.2
     */
    public static function bpwp_plugin_deactivate()
    {
        flush_rewrite_rules();

        do_action('bpwp_welcome_deactivate');
    }

    /**
	 * Delete the Transient on plugin deactivation.
	 *
	 * Delete the welcome page transient.
	 *
	 * @since   2.3.2
	 */
	public static function bpwp_welcome_deactivate() {
        delete_transient( '_welcome_redirect_bpwp' );
    }

    /**
     * Добавляет ссылку на страницу поддержки на странице плагинов.
     *
     * @param array $links Существующие ссылки.
     * @return array Модифицированный массив ссылок.
     */
    public static function bpwp_add_support_link($links) {
        $support_link = '<a href="https://bonuspluswp.site/request/" target="_blank" style="color: green; font-weight: bold;">' . __('Нужна помошь', 'bonus-plus-wp') . '</a>';
        array_push($links, $support_link);

        return $links;
    }

    /**
     * Добавляет ссылку на страницу документации в мета-данные плагина.
     *
     * @param array $links Существующие ссылки.
     * @param string $file Путь к файлу плагина.
     * @return array Модифицированный массив ссылок.
     */
    public static function bpwp_plugin_row_meta($links, $file) {
        if ($file == 'bonus-plus-wp/bonus-plus-wp.php') {
            $support_link = '<a href="https://bonuspluswp.site/category/docs/" target="_blank" font-weight: bold;">' . __('Документация', 'bonus-plus-wp') . '</a>';
            $links[] = $support_link;
        }
        return $links;
    }

    /**
	 * Safe Welcome Page Redirect.
	 *
	 * Safe welcome page redirect which happens only
	 * once and if the site is not a network or MU.
	 *
	 * @since 	2.3.2
	 */
	public static function bpwp_safe_welcome_redirect() {
		// Bail if no activation redirect transient is present. (if ! true).
		if ( ! get_transient( '_welcome_redirect_bpwp' ) ) {
			return;
		}

		// Delete the redirect transient.
		delete_transient( '_welcome_redirect_bpwp' );

		// Bail if activating from network or bulk sites.
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		// Redirects to `your-domain.com/wp-admin/admin.php?page=bpwp-welcome-page`.
		wp_safe_redirect( 
			add_query_arg(
				array(
					'page' => 'bpwp-welcome-page'
				),
				admin_url( 'admin.php' )
			)
		);
	}
}
BPWPBonusPlus_Core::init();