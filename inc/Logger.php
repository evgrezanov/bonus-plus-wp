<?php

namespace BPWP;

/**
 * Logger for bonus-plus-wp
 *
 * Example: do_action('bpwp_logger', $type = '123', $title = '123', $desc = '123');
 */
final class BPWPLogger
{

  /**
   * The init
   */
  public static function init()
  {
    add_action('admin_init', array(__CLASS__, 'add_settings'));
    add_action('bpwp_logger', array(__CLASS__, 'add_log'), 10, 3);
    add_action('bpwp_logger_error', array(__CLASS__, 'add_log_error'), 10, 3);

    
    add_filter('bpwp_logger_enable', function ($is_enable) {
      return self::is_enable();
    });

    add_action('admin_menu', function () {
      if (self::is_enable()) {
        global $submenu;
        $permalink = admin_url('admin.php?page=wc-status&tab=logs');
        $submenu['bonusplus'][] = array('Журнал', 'manage_options', $permalink);
      }
    }, 111);
  }

  public static function is_enable()
  {
    if (get_option('bpwp_logger_enable')) {
      return true;
    }

    return false;
  }


  /**
   * add_log_error
   */
  public static function add_log_error($type = 'bonus-plus-wp', $title = '', $description = '')
  {
    if (!self::is_enable()) {
      return;
    }

    $data = '';

    $data .= strval($title);

    if (!empty($description)) {

      if (is_array($description)) {
        $description = wp_json_encode($description, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
      } else {
        $description = wc_print_r($description, true);
      }

      $description = wp_trim_words($description, $num_words = 300, $more = null);
      $data .= ':' . PHP_EOL . $description;
    }

    $source = $type;
    $source = str_replace('\\', '-', $source);

    $logger = wc_get_logger();
    $context = array('source' => $source);
    $logger->error($data, $context);
  }

  /**
   * add log
   */
  public static function add_log($type = 'bonus-plus-wp', $title = '', $description = '')
  {
    if (!self::is_enable()) {
      return;
    }

    $data = '';

    $data .= strval($title);

    if (!empty($description)) {
      if (is_array($description)) {
        $description = wp_json_encode($description, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
      } else {
        $description = wc_print_r($description, true);
      }

      $description = wp_trim_words($description, $num_words = 300, $more = null);
      $data .= ':' . PHP_EOL . $description;
    }

    $source = $type;
    $source = str_replace('\\', '-', $source);

    $logger = wc_get_logger();
    $context = array('source' => $source);
    $logger->info($data, $context);
  }

  /**
   * render_settings_page
   */
  public static function add_settings()
  {

    $option_name = 'bpwp_logger_enable';

    register_setting('bpwp-settings', $option_name);
    add_settings_field(
      $id = $option_name,
      $title = __('Логирование', 'bonus-plus-wp'),
      $callback = function ($args) {
        printf(
          '<input type="checkbox" name="%s" value="1" %s />',
          esc_attr($args['key']),
          checked(1, $args['value'], false)
        );
        printf('<p><small>При включении, ошибки и ключевые изменения данных будут записываться в <a href="%s">журнал WooCommerce</a></small></p>', admin_url('admin.php?page=wc-status&tab=logs')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      },
      $page = 'bpwp-settings',
      $section = 'bpwp_section_access',
      $args = [
        'key' => $option_name,
        'value' => get_option($option_name),
      ]
    );
  }
}

BPWPLogger::init();
