<?php
/**
 * Welcome Page View
 *
 * Welcome page content i.e. HTML/CSS/PHP.
 *
 * @since 	2.3.0
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

	<h1><?php printf( __('Настройка интеграция с Бонус+ &nbsp;%s'), $the_version ); ?></h1>

	<div class="about-text">
		<?php printf( __("Интеграция с сервисом программы лояльности Бонус+ для WordPress и Woocommerce"), $the_version ); ?>
	</div>

	<div class="wp-badge welcome__logo"></div>

	<div class="feature-section one-col">
		<h2><?php _e( 'Начало работы' ); ?></h2>
		<ul>
			<li>
				<strong><?php _e( 'Шаг #1: ' ); ?></strong>
				<?php 
					printf(
            			'<a href="%s" target="_blank">%s</a>',
            			esc_url('https://bonusplus.pro/api/link/KEETCJEL'),
						esc_html(
							__('Зарегистрируйтесь в программе лояльности если еще не зарегистрированы', 'bonus-plus-wp')
						)
        			); 
				?>
			</li>
			<li>
				<strong><?php _e( 'Шаг #2: ' ); ?></strong> 
				<?php _e('Получите API ключ и сохраните его в настройках плагина', 'bonus-plus-wp'); ?>
			</li>
			<li>
				<strong><?php _e( 'Шаг #3: ' ); ?></strong> 
				<?php _e('Экспортируйте товары и категории в программу лояльности', 'bonus-plus-wp'); ?>
		</ul>
	 </div>

	<div class="feature-section one-col">
		<h2>Что умеет плагин??</h2>
		<div class="headline-feature feature-video">
			<div class='embed-container'>
				<iframe src='https://www.youtube.com/embed/584susZUd7g?si=lPD1aEZQ3lCOiUrR' frameborder='0' allowfullscreen></iframe>
			</div>
		</div>
	</div>

	<div class="feature-section two-col">
		<div class="col">
			<!--<img src="https://ps.w.org/bonus-plus-wp/assets/banner-1544x500.png?rev=3146154" />-->
			<h3><?php _e('Экспорт товаров и категорий в Бонус+'); ?></h3>
			<p><?php _e('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras sed sapien quam. Sed dapibus est id enim facilisis, at posuere turpis adipiscing. Quisque sit amet dui dui.' ); ?></p>
		</div>

		<div class="col">
			<!--<img src="http://placehold.it/600x180/0092F9/fff?text=WELCOME" />-->
			<h3><?php _e('Начисление и списание бонусов на сайте'); ?></h3>
			<p><?php _e( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras sed sapien quam. Sed dapibus est id enim facilisis, at posuere turpis adipiscing. Quisque sit amet dui dui.' ); ?></p>
		</div>
	</div>

	<div class="feature-section two-col">
		<div class="col">
			<!--<img src="http://placehold.it/600x180/0092F9/fff?text=WELCOME" />-->
			<h3><?php _e('Автоматическая регистрация пользователя в программе лояльности при регистрации на сайте'); ?></h3>
			<p><?php _e( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras sed sapien quam. Sed dapibus est id enim facilisis, at posuere turpis adipiscing. Quisque sit amet dui dui.' ); ?></p>
		</div>
		<div class="col">
			<!--<img src="http://placehold.it/600x180/0092F9/fff?text=WELCOME" />-->
			<h3><?php _e( 'Обязательное поле номер телефона' ); ?></h3>
			<p><?php _e( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras sed sapien quam. Sed dapibus est id enim facilisis, at posuere turpis adipiscing. Quisque sit amet dui dui.' ); ?></p>
		</div>
	</div>
</div>
<!-- HTML Ended! -->
