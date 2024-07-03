=== WPBonusPlus ===
Contributors: redmonkey73, mickuznetsov
Donate link: https://github.com/evgrezanov
Tags: bonus, woocommerce, sync, integration
Requires at least: 4.0
Tested up to: 6.5
Stable tag: 2.13
Requires PHP: 8.1
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

[Документация по началу работы](https://github.com/evgrezanov/bonus-plus-wp/wiki)

[Нужна доработка плагина?](https://github.com/evgrezanov/bonus-plus-wp/issues/new)


== Installation ==

This section describes how to install the plugin and get it working.

e.g.

= Automatic installation =

Automatic installation is the easiest option -- WordPress will handles the file transfer, and you won’t need to leave your web browser. To do an automatic install of bonus-plus-wp, log in to your WordPress dashboard, navigate to the Plugins menu, and click “Add New.”

In the search field type “bonus-plus-wp,” then click “Search Plugins.” Once you’ve found us,  you can view details about it such as the point release, rating, and description. Most importantly of course, you can install it by! Click “Install Now,” and WordPress will take it from there.

= Manual installation =

Manual installation method requires downloading the bonus-plus-wp plugin and uploading it to your web server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](https://wordpress.org/support/article/managing-plugins/#manual-plugin-installation).



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

WordPress 6.0
PHP Рекомендуем 8.0 и выше

= Будет работать без WooCommerce? =

Нет, текущая версия работает только с WooCommerce


== Screenshots ==

1. Страница настроек
2. Страница личного кабинета WooCommerce
3. Шорткод для отображения бонусной карты клиента
4. Экспорт товаров и категорий в БонусПлюс

== Changelog ==

= 2.13 =
- исправлена ошибка при списании бонусов https://github.com/evgrezanov/bonus-plus-wp/issues/75
- добавлена проверка номера телефона https://github.com/evgrezanov/bonus-plus-wp/issues/72

= 2.12 =
- fix bugs
- Вынести шаблон карты в лк в /templates

= 2.11 =
- возможность списания части бонусов на чекауте https://github.com/evgrezanov/bonus-plus-wp/issues/63
- bugs fixing

= 2.10 =
- version fix

= 2.9 =
- линковка существующего клиента из б+ https://github.com/evgrezanov/bonus-plus-wp/issues/42

= 2.8 =
- убрали поле "дата рождения" из личного кабинета https://github.com/evgrezanov/bonus-plus-wp/issues/58
- добавлена обработка ошибок при экспорте товаров и категорий https://github.com/evgrezanov/bonus-plus-wp/issues/56
- рефакторинг процесса авторизации и регистрации в программе лояльности https://github.com/evgrezanov/bonus-plus-wp/issues/47
- исправлена функция списания и резервирования бонусов https://github.com/evgrezanov/bonus-plus-wp/issues/40
- исправлена проблема с безопастностью при генерации QR кода в личном кабинете исправить проблему с безопастностью https://github.com/evgrezanov/bonus-plus-wp/issues/21

= 2.7 =
- устранение бага с экспортом товаров и категорий https://github.com/evgrezanov/bonus-plus-wp/issues/50

= 2.6 =
- настройки виджета перенесены на отдельную вкладку в админке https://github.com/evgrezanov/bonus-plus-wp/issues/32
- исправлены предупреджения https://github.com/evgrezanov/bonus-plus-wp/issues/31
- реализовано списание бонусов https://github.com/evgrezanov/bonus-plus-wp/issues/40
- исправлена ошибка с отображением бонусов в корзине https://github.com/evgrezanov/bonus-plus-wp/issues/37

= 2.5 =
- исправлена проблема с экспортом товаров и категорий https://github.com/evgrezanov/bonus-plus-wp/issues/28
- отображение начисленных бонусов на страннице товара, корзины
- обновление начисленных бонусов при оплате заказа

= 2.4 =
- добавлены настройки для вывода различного текста и ссылок для различных типов пользователей

= 2.1 =
- clear client meta after login
- include account.js at my-account page

= 2.0 =
- add registration interface at my-account

= 1.10 =
- fix warnings at export products page

= 1.9 =
- Добавлена возможность верификации номера телефона

= 1.8 =
- Добавлен экспорт вариаций как продуктов
- Добавлены хуки bpwp_filter_export_product_cat и bpwp_filter_export_products для фильтрации категорий и товаров перед экспортом

= 1.7 =
- Исправлены функция определения родительской категории у товара, добавлена документация https://github.com/evgrezanov/bonus-plus-wp/wiki/Export-products-and-product-cat/

= 1.6 =
- Исправлены ошибки при выводе сообщения о завершении экспорта товаров и категорий

= 1.5 =
- Удалена опция "Идентифицировать клиента по" https://github.com/evgrezanov/bonus-plus-wp/issues/11
- Добавлена опция "Действие с товаром у которого больше 1 категории"
- Добавлены опции "Ссылка для идентифицированных пользователей" и "Ссылка для неопознанных пользователей"

= 1.4 =
- Функция экспорта товаров и категорий в Бонус+
- Добавлена страница Управления плагином
- Добавлен Logger
- Добавлен шорткод [bpwp_api_customer_data] для отображения данных текущего клиента из Бонус+

= 1.3 =
- Bug fix

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
