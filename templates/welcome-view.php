<?php
/**
 * Welcome Page View
 *
 * Welcome page content i.e. HTML/CSS/PHP.
 *
 * @since 	2.3.1
 * @package BPWP
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Version.
$the_version = BPWP_PLUGIN_VERSION;

// Logo image.
$logo_img = BPWP_URL . '/assets/img/icon.svg';
$banner = BPWP_URL . '/assets/img/banner.png';

 ?>
<!-- HTML Started! -->
<div class="wrap about-wrap">

	<h1><?php printf( __('Настройка интеграция с Бонус+ &nbsp;%s' , 'bonus-plus-wp' ), $the_version ); ?></h1>

	<div class="about-text">
		<?php printf( __('Интеграция с сервисом программы лояльности Бонус+ для WordPress и Woocommerce', 'bonus-plus-wp') ); ?>
	</div>

	<div class="wp-badge welcome__logo"></div>

	<div class="feature-section one-col">
		<h2><?php _e( 'Начало работы', 'bonus-plus-wp' ); ?></h2>
		<ul>
			<li>
				<strong><?php _e( 'Шаг #1: ', 'bonus-plus-wp' ); ?></strong>
				<?php 
					printf(
            			'<a href="%s" target="_blank">%s</a>',
            			esc_url('https://bonusplus.pro/api/link/KEETCJEL'),
						esc_html(
							__('Зарегистрируйтесь в программе лояльности, если еще не зарегистрированы', 'bonus-plus-wp')
						)
        			); 
				?>
			</li>
			<li>
				<strong><?php esc_html_e( 'Шаг #2: ', 'bonus-plus-wp' ); ?></strong> 
				<?php esc_html_e('Получите API ключ и сохраните его в настройках плагина', 'bonus-plus-wp'); ?>
			</li>
			<li>
				<strong><?php esc_html_e( 'Шаг #3: ', 'bonus-plus-wp' ); ?></strong> 
				<?php esc_html_e('Экспортируйте товары и категории в программу лояльности', 'bonus-plus-wp'); ?>
		</ul>
	 </div>

	<div class="feature-section one-col">
		<h2><?php esc_html_e('Что умеет плагин?', 'bonus-plus-wp'); ?></h2>
		<div class="headline-feature feature-video">
			<div class='embed-container'>
				<iframe src='https://www.youtube.com/embed/584susZUd7g?si=lPD1aEZQ3lCOiUrR' frameborder='0' allowfullscreen></iframe>
			</div>
		</div>
	</div>

	<div class="feature-section two-col">
		<div class="col">
			<h3><?php esc_html_e('Экспорт товаров и категорий в Бонус+', 'bonus-plus-wp' ); ?></h3>
			<p><?php esc_html_e('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras sed sapien quam. Sed dapibus est id enim facilisis, at posuere turpis adipiscing. Quisque sit amet dui dui.', 'bonus-plus-wp'  ); ?></p>
		</div>

		<div class="col">
			<h3><?php esc_html_e('Начисление и списание бонусов на сайте', 'bonus-plus-wp' ); ?></h3>
			<p><?php esc_html_e( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras sed sapien quam. Sed dapibus est id enim facilisis, at posuere turpis adipiscing. Quisque sit amet dui dui.', 'bonus-plus-wp'  ); ?></p>
		</div>
	</div>

	<div class="feature-section two-col">
		<div class="col">
			<h3><?php esc_html_e('Автоматическая регистрация пользователя в программе лояльности при регистрации на сайте', 'bonus-plus-wp' ); ?></h3>
			<p><?php esc_html_e( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras sed sapien quam. Sed dapibus est id enim facilisis, at posuere turpis adipiscing. Quisque sit amet dui dui.', 'bonus-plus-wp'  ); ?></p>
		</div>
		<div class="col">
			<h3><?php esc_html_e( 'Обязательное поле номер телефона', 'bonus-plus-wp' ); ?></h3>
			<p><?php esc_html_e( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras sed sapien quam. Sed dapibus est id enim facilisis, at posuere turpis adipiscing. Quisque sit amet dui dui.', 'bonus-plus-wp'  ); ?></p>
		</div>
	</div>
</div>
<!-- HTML Ended! -->
