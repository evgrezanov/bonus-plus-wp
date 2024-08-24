<?php

namespace BPWP;

defined('ABSPATH') || exit; // Exit if accessed directly

class BPWPPhoneRegistration
{
    /**
     * Initialize the class
     */
    public static function init()
    {
        add_action('admin_init', [__CLASS__, 'register_settings']);
        add_action('woocommerce_register_form', [__CLASS__, 'add_phone_field_to_registration']);
        add_action('woocommerce_created_customer', [__CLASS__, 'save_phone_field']);
        add_filter('woocommerce_registration_errors', [__CLASS__, 'validate_phone_field'], 10, 3);
    }

    /**
     * Register settings for the phone registration option
     */
    public static function register_settings()
    {
        register_setting('bpwp-settings', 'bpwp_require_phone_registration');
        add_settings_field(
            'bpwp_require_phone_registration',
            __('Обязательный номер телефона при регистрации', 'bonus-plus-wp'),
            [__CLASS__, 'render_require_phone_option'],
            'bpwp-settings',
            'bpwp_section_access'
        );
    }

    /**
     * Render the checkbox for requiring phone number
     */
    public static function render_require_phone_option()
    {
        $option = get_option('bpwp_require_phone_registration');
        echo '<input type="checkbox" id="bpwp_require_phone_registration" name="bpwp_require_phone_registration" value="1" ' . checked(1, $option, false) . '/>';
        echo '<label for="bpwp_require_phone_registration">' . __('Сделать номер телефона обязательным при регистрации', 'bonus-plus-wp') . '</label>';
    }

    /**
     * Add phone field to the registration form
     */
    public static function add_phone_field_to_registration()
    {
        $required = get_option('bpwp_require_phone_registration') ? 'required' : '';
        
        woocommerce_form_field(
            'billing_phone',
            array(
                'type'        => 'tel',
                'required'    => $required,
                'label'       => __('Номер телефона', 'bonus-plus-wp'),
                'placeholder' => __('Введите ваш номер телефона', 'bonus-plus-wp'),
            )
        );
    }

    /**
     * Save phone field when a new customer account is created
     *
     * @param int $customer_id The ID of the newly created customer
     */
    public static function save_phone_field($customer_id)
    {
        if (isset($_POST['billing_phone'])) {
            update_user_meta($customer_id, 'billing_phone', sanitize_text_field($_POST['billing_phone']));
        }
    }

    /**
     * Validate the phone field during registration
     *
     * @param \WP_Error $errors
     * @param string $username
     * @param string $email
     * @return \WP_Error
     */
    public static function validate_phone_field($errors, $username, $email)
    {
        if (get_option('bpwp_require_phone_registration') && empty($_POST['billing_phone'])) {
            $errors->add('billing_phone_error', __('Номер телефона обязателен для заполнения.', 'bonus-plus-wp'));
        }
        return $errors;
    }
}

BPWPPhoneRegistration::init();