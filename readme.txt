=== WPBonusPlus ===
Contributors: redmonkey73
Donate link: https://github.com/evgrezanov
Tags: bonus, woocommerce, sync, integration
Requires at least: 4.0
Tested up to: 5.3
Stable tag: 4.3
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

БонусПлюс (bonusplus.pro) and WordPress/WooCommerce - sync, integration, connection

== Description ==

Integration WordPress/WooCommerce & BonusPlus http://bonusplus.pro (for Russia)

Интеграция приложения БонусПлюс (программа лояльности) и WooCommerce (WordPress)

Особенности:

*   Синхронизация данных бонусной карты пользователя
*   Шорткод для отображения данных пользователя
*   WooCommerce добавлена новая вкладка в ЛК

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload plugin to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to Settings / MoySklad and setup
1. Got to Tools / MoySklad and run sync

== Frequently Asked Questions ==

= Какие товары синхронизируются? =

По умолчанию только с артикулами. Чтобы можно было синхронизировать товары МойСклад и сайта без удаления.
Но если включить опцию UUID, то товары можно синхронизировать без артикула. В этом случае придется сначала удалить продукты с сайта.

= Что нужно чтобы синхронизация заработала? =

Нужно правильно указать реквизиты доступа на странице настроек плагина в панели управления сайтом. На стороне МойСклад ничего делать не нужно.

= Как устроен механизм синхронизации? =

Используется протокол REST API. Без протокола CommerceML. Вся логика находится на стороне сайта и сайт сам запрашиует данные из МойСклад.
В зависимости от особенностей конфигурации сервера бот синхронизации может зависать из-за таймаутов. Для этого в плагине встроен супервайзер, который следит за ботом и пинает его в случае остановки.

= Какие минимальные требования? =

WordPress 4.5
WooCommerce 3.0 - мб будет работать на Woo 2.х но не факт.
PHP 5.6


== Screenshots ==

1. Страница настроек.
2. Страница продуктов
3. Журнал обработки

== Changelog ==

= 8.2 =
- Проверка совместимости с WooCommerce 5.0 https://github.com/wpcraft-ru/wooms/issues/396
- Полное и краткое описание товара https://github.com/wpcraft-ru/wooms/issues/347
- XT: Сокрытие wooms_id из деталей Заказа видимых клиенту https://github.com/wpcraft-ru/wooms/issues/398
- XT: Загрузка изображения у модификаций Продукта https://github.com/wpcraft-ru/wooms/issues/359
- XT: При создании нового контрагента - нет email https://github.com/wpcraft-ru/wooms/issues/346

= 8.1 =
- Краткое описание товара вместо полного как опция https://github.com/wpcraft-ru/wooms/issues/347
- XT: При создании нового контрагента - нет email https://github.com/wpcraft-ru/wooms/issues/346