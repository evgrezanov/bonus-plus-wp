<?php

/**
 * Generated by the WordPress Option Page generator
 * at http://jeremyhixon.com/wp-tools/option-page/
 */

class WooBonusPlus_Settings
{
	private $woobonusplus_options;

	public function __construct()
	{
		add_action('admin_menu', array($this, 'woobonusplus_add_plugin_page'));
		add_action('admin_init', array($this, 'woobonusplus_page_init'));
	}

	public function woobonusplus_add_plugin_page()
	{
		add_menu_page(
			'WooBonusPlus', // page_title
			'WooBonusPlus', // menu_title
			'manage_options', // capability
			'woobonusplus', // menu_slug
			array($this, 'woobonusplus_create_admin_page'), // function
			'dashicons-admin-generic', // icon_url
			2 // position
		);
	}

	public function woobonusplus_create_admin_page()
	{
		$this->woobonusplus_options = get_option('woobonusplus_option_name');
		
		?>
		
		<div class="wrap">
			<h2>WooBonusPlus</h2>
			<p>https://bonusplus.pro/lk</p>
			<?php 
				settings_errors();
				$res = WooBonusPlus_API::bp_api_request(
					'account',
					'',
					'GET'
				);

				$info = json_decode($res);
				//print_r($res);
				foreach ($info as $key => $value) :
					if ($key == 'balance') {
						print('Балланс: ' . $value . '<br />');
					}
					if ($key == 'tariff') {
						print('Тарифф: ' . $value . '<br />');
					}
					if ($key == 'smsPrice') {
						print('Стоимость СМС: ' . $value . '<br />');
					}
					if ($key == 'pushPrice') {
						print('Стоимость push: ' . $value . '<br />');
					}
				endforeach;
			
			?>
			<form method="post" action="options.php">
				<?php
				settings_fields('woobonusplus_option_group');
				do_settings_sections('woobonusplus-admin');
				submit_button();
				?>
			</form>
		</div>
		<?php 
	}

	public function woobonusplus_page_init()
	{
		register_setting(
			'woobonusplus_option_group', // option_group
			'woobonusplus_option_name', // option_name
			array($this, 'woobonusplus_sanitize') // sanitize_callback
		);

		add_settings_section(
			'woobonusplus_setting_section', // id
			'Settings', // title
			array($this, 'woobonusplus_section_info'), // callback
			'woobonusplus-admin' // page
		);

		add_settings_field(
			'_0', // id
			'Логин', // title
			array($this, '_0_callback'), // callback
			'woobonusplus-admin', // page
			'woobonusplus_setting_section' // section
		);

		add_settings_field(
			'_1', // id
			'Пароль', // title
			array($this, '_1_callback'), // callback
			'woobonusplus-admin', // page
			'woobonusplus_setting_section' // section
		);

		add_settings_field(
			'_api_2', // id
			'Ключ API', // title
			array($this, '_api_2_callback'), // callback
			'woobonusplus-admin', // page
			'woobonusplus_setting_section' // section
		);

		add_settings_field(
			'____3', // id
			'Заголовок вкладки в профиле', // title
			array($this, '____3_callback'), // callback
			'woobonusplus-admin', // page
			'woobonusplus_setting_section' // section
		);

		add_settings_field(
			'___4', // id
			'Инструкция для пользователя', // title
			array($this, '___4_callback'), // callback
			'woobonusplus-admin', // page
			'woobonusplus_setting_section' // section
		);
	}

	public function woobonusplus_sanitize($input)
	{
		$sanitary_values = array();
		if (isset($input['_0'])) {
			$sanitary_values['_0'] = sanitize_text_field($input['_0']);
		}

		if (isset($input['_1'])) {
			$sanitary_values['_1'] = sanitize_text_field($input['_1']);
		}

		if (isset($input['_api_2'])) {
			$sanitary_values['_api_2'] = sanitize_text_field($input['_api_2']);
		}

		if (isset($input['____3'])) {
			$sanitary_values['____3'] = sanitize_text_field($input['____3']);
		}

		if (isset($input['___4'])) {
			$sanitary_values['___4'] = esc_textarea($input['___4']);
		}

		return $sanitary_values;
	}

	public function woobonusplus_section_info()
	{
	}

	public function _0_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="woobonusplus_option_name[_0]" id="_0" value="%s">',
			isset($this->woobonusplus_options['_0']) ? esc_attr($this->woobonusplus_options['_0']) : ''
		);
	}

	public function _1_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="woobonusplus_option_name[_1]" id="_1" value="%s">',
			isset($this->woobonusplus_options['_1']) ? esc_attr($this->woobonusplus_options['_1']) : ''
		);
	}

	public function _api_2_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="woobonusplus_option_name[_api_2]" id="_api_2" value="%s">',
			isset($this->woobonusplus_options['_api_2']) ? esc_attr($this->woobonusplus_options['_api_2']) : ''
		);
	}

	public function ____3_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="woobonusplus_option_name[____3]" id="____3" value="%s">',
			isset($this->woobonusplus_options['____3']) ? esc_attr($this->woobonusplus_options['____3']) : ''
		);
	}

	public function ___4_callback()
	{
		printf(
			'<textarea class="large-text" rows="5" name="woobonusplus_option_name[___4]" id="___4">%s</textarea>',
			isset($this->woobonusplus_options['___4']) ? esc_attr($this->woobonusplus_options['___4']) : ''
		);
	}
}

if (is_admin())
	$woobonusplus = new WooBonusPlus_Settings();

/* 
 * Retrieve this value with:
 * $woobonusplus_options = get_option( 'woobonusplus_option_name' ); // Array of All Options
 * $_0 = $woobonusplus_options['_0']; // Логин
 * $_1 = $woobonusplus_options['_1']; // Пароль
 * $_api_2 = $woobonusplus_options['_api_2']; // Ключ API
 * $____3 = $woobonusplus_options['____3']; // Заголовок вкладки в профиле
 * $___4 = $woobonusplus_options['___4']; // Инструкция для пользователя
 */
