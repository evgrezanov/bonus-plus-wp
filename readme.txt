=== WPBonusPlus ===
Contributors: redmonkey73
Donate link: https://github.com/evgrezanov
Tags: bonus, woocommerce, sync, integration
Requires at least: 4.0
Tested up to: 5.7.2
Stable tag: 1.2
Requires PHP: 7.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

БонусПлюс (bonusplus.pro) and WordPress/WooCommerce - sync, integration, connection

== Description ==

Integration WordPress/WooCommerce & BonusPlus http://bonusplus.pro (for Russia)

Интеграция приложения БонусПлюс (программа лояльности) и WordPress/WooCommerce

Особенности:

*   Синхронизация данных бонусной карты пользователя
*   Шорткод для отображения данных карты пользователя
*   WooCommerce добавлена новая вкладка в личном кабинете
*   Генерация QR кода, для предъявления на кассе

[Оффициальный сайт Бонус+](https://bonusplus.pro/new/#about)

[Обработка персональных данных](https://bonusplus.pro/new/data-processing/)

Документация разработчика: [https://bonusplus.pro/api](https://bonusplus.pro/api)

Примеры получаемых данных REST API Бонус+: [https://bonusplus.pro/api/Help](https://bonusplus.pro/api/Help)

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

= Automatic installation =

Automatic installation is the easiest option -- WordPress will handles the file transfer, and you won’t need to leave your web browser. To do an automatic install of WP-bonus-plus, log in to your WordPress dashboard, navigate to the Plugins menu, and click “Add New.”

In the search field type “wp-bonus-plus,” then click “Search Plugins.” Once you’ve found us,  you can view details about it such as the point release, rating, and description. Most importantly of course, you can install it by! Click “Install Now,” and WordPress will take it from there.

= Manual installation =

Manual installation method requires downloading the wp-bonus-plus plugin and uploading it to your web server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](https://wordpress.org/support/article/managing-plugins/#manual-plugin-installation).



== Frequently Asked Questions ==

= Какие данные синхронизируются? =

Синхронизируютс все данные по бонусной карте пользователя: Номер карты, Тип карты, Доступных бонусов, Неактивных бонусов, Дата последней покупки, Сумма покупок, Сумма покупок для смены карты и тд.

= Что нужно чтобы синхронизация заработала? =

Нужно правильно указать реквизиты доступа на странице настроек плагина в панели управления сайтом. На стороне БонусПлюс ннужно активировать API Ключ.

= Как устроен механизм синхронизации? =

Используется API Бонус+. Для идентификации клиента используется платежный номер телефона клиента из WooCommerce (<strong>billing_phone</strong>).

= Как изменить название вкладки в личном кабинете клиента? =

Используйте фильтр add_filter('bpwp_filter_woo_profile_tab_title', $title).

= Какие минимальные требования? =

WordPress 5.0
PHP 7.1

= Будет работать без WooCommerce? =

Да, будет, используйте хук bpwp_api_filter_get_customer_phone для фильтрации номера телефона 


== Screenshots ==

1. Страница настроек
2. Страница личного кабинета WooCommerce
3. Шорткод для отображения бонусной карты клиента

== Changelog ==

= 1.2 =
- Исправлено предупреждение https://github.com/evgrezanov/bonus-plus-wp/issues/6
- Добавлена опция "Идентифицировать клиента по" https://github.com/evgrezanov/bonus-plus-wp/issues/11
- Добавлена опция "Название магазина" https://github.com/evgrezanov/bonus-plus-wp/issues/10

= 1.1 =
- Исправлена проблема с версиями в файлах readme
- Добавлена проверка при выводе ссылок в настройках плагина
- Добавлены комментарии к функциям и переменным связанным с API Бонус+

= 1.0 =
- Страница настроек подключения
- Шорткод бонусной карты
- Интеграция с WooCommerce
