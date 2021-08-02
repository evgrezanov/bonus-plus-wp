<?php

namespace BPWP;

defined('ABSPATH') || exit; // Exit if accessed directly

class BPWPWooProductCatExport
{
    public static $lastExportOption;

    public static $lastExport = [];

    public static $error;

    /**
     *  Init
     */
    public static function init()
    {
        self::$lastExportOption = 'bpwp_last_products_export_date';
        add_action('admin_init', array(__CLASS__, 'settings_general'), $priority = 10, $accepted_args = 1);

        add_action('bpwp_tool_actions_btns', [__CLASS__, 'bpwp_export_ui_btns']);
        add_action('bpwp_tool_actions_products_cats_export', [__CLASS__, 'bpwp_api_products_cats_export'], 10, 2);
        add_action('bpwp_tool_actions_message', [__CLASS__, 'bpwp_export_message_ui']);
    }

    /**
     *  Add sections to settiongs page
     * 
     *  @return mixed
     */
    public static function settings_general()
    {
        register_setting('bpwp-settings', 'bpwp_wrong_products_action');
        add_settings_field(
            $id = 'bpwp_wrong_products_action',
            $title = __('Действие при импорте с товаром у которого больше 1 категории:', 'bonus-plus-wp'),
            $callback = array(__CLASS__, 'display_wrong_products_action'),
            $page = 'bpwp-settings',
            $section = 'bpwp_section_access'
        );
    }

    /**
     * display_wrong_products_action
     * 
     *  @return mixed
     */
    public static function display_wrong_products_action()
    {

        $wrong_products_action = get_option('bpwp_wrong_products_action'); ?>
        <select class="check_prefix_postfix" name="bpwp_wrong_products_action">
            <?php
            printf(
                '<option value="%s" %s>%s</option>',
                'hide',
                selected('hide', $wrong_products_action, false),
                'Импортировать, не включая товар в файл импорта'
            );
            printf(
                '<option value="%s" %s>%s</option>',
                'stop',
                selected('stop', $wrong_products_action, false),
                'Не выполнять импорт'
            );
            ?>
        </select>
        <?php printf('<p><small>%s</small></p>', esc_html(__('1 товару в Бонус+ может соответствовать только одна категория товаров. Выберите действие, которое будет происходить с товарами, у которых больше 1 категории, при импорте товаров', 'bonus-plus-wp')));
    }

    /**
     *  Подготовка данных категорий для импорта в Б+
     * 
     *  @return $product_cats ProductTiny Массив категорий и товаров https://bonusplus.pro/api/Help/ResourceModel?modelName=ProductTiny
     */
    public static function bpwp_api_product_cat_data_prepare()
    {
        $product_cats = [];
        $productCat = get_terms(
            [
                'taxonomy'   => 'product_cat',
                'hide_empty' => false,
            ]
        );
        if ($productCat && !is_wp_error($productCat)) {
            foreach ($productCat as $term) {
                $product_cat = [
                    'id'  => $term->term_id,
                    'pid' => $term->parent == 0 ? 0 : $term->parent,
                    'n'   => $term->name,
                    'g'   => true,
                ];
                $product_cats[] = $product_cat;
            }
        }

        if (!$productCat) {
            do_action(
                'bpwp_logger',
                $type = __CLASS__,
                $title = __('Нет данных для экспорта', 'bonus-plus-wp'),
                $desc = sprintf(__('Не нашли ни одной категории с товаром', 'bonus-plus-wp')),
            );
        }

        if (is_wp_error($productCat)) {
            $errorString = $productCat->get_error_message();
            $errorCode = $productCat->get_error_code();
            do_action(
                'bpwp_logger_error',
                $type = __CLASS__,
                $title = __('Ошибка при получении категорий товаров', 'bonus-plus-wp'),
                $desc = sprintf('[%s] %s', $errorCode, $errorString),
            );
        }

        self::$lastExport['cat_count'] = count($productCat) > 0 ? count($productCat) : 0;
        self::$lastExport['cat_export'] = count($product_cats) > 0 ? count($productCat) : 0;
        self::$lastExport['cat_hide'] = count($productCat) - count($product_cats) > 0 ? count($productCat) - count($product_cats) : 0;

        return $product_cats;
    }

    /**
     *  Подготовка данных товаров для импорта в Б+
     * 
     *  @return $productList ProductTiny Массив категорий и товаров https://bonusplus.pro/api/Help/ResourceModel?modelName=ProductTiny
     */
    public static function bpwp_api_products_data_prepare()
    {
        $productList = [];
        $wrongProducts = [];
        $args = array(
            'status' => 'published',
            'limit'  => -1,
            'type'   => ['simple', 'variable'],
        );
        $products = wc_get_products($args);
        if ($products) {
            foreach ($products as $product) {
                $theid = $product->get_id();
                $pCatIds = $product->category_ids;
                $categoriesCount = count($pCatIds);

                if ($categoriesCount == 1) {
                    $pCatId = $pCatIds[0];
                    $product = [
                        'id'  => $theid,
                        'pid' => $pCatId,
                        'n'   => $product->get_name(),
                        'g'   => false,
                    ];
                    $productList[] = $product;
                } else {
                    /**
                     * todo проверяем опцию, варианты:
                     *  - товар у которого больше 2х категорий пропускаем
                     *  - товар у которого больше 2х категорий записывем в лог
                     * */
                    do_action(
                        'bpwp_logger_error',
                        $type = __CLASS__,
                        $title = __('Более 1 категории у товара', 'bonus-plus-wp'),
                        $desc = sprintf(__('У товара с ID %s %s более 1 категории', 'bonus-plus-wp'), $theid, $product->name),
                    );
                    $wrongProducts[$theid] = $product->name;
                }
            }
        }
        wp_reset_postdata();
        /**
         *  Здесь мы можем проверить $wrongProducts[] и если нужно выйти
         */
        self::$lastExport['pcount'] = count($products) > 0 ? count($products) : 0;
        self::$lastExport['pexport'] = count($productList) > 0 ? count($productList) : 0;
        self::$lastExport['phide'] = count($wrongProducts) > 0 ? count($wrongProducts) : 0;

        return $productList;
    }

    /**
     *  Отправляет запрос к API Б+
     * 
     *  @param $product ProductTiny Массив категорий и товаров https://bonusplus.pro/api/Help/ResourceModel?modelName=ProductTiny
     *  @param $store string Название магазина в Б+
     * 
     *  @return void Результаты выполнения запроса к Б+
     */
    public static function bpwp_api_products_cats_export($product = [])
    {

        if (empty($product)) {
            $product_cat = self::bpwp_api_product_cat_data_prepare();
            $products = self::bpwp_api_products_data_prepare();
            $product = array_merge($product_cat, $products);
        }

        $store = !empty(get_option('bpwp_shop_name')) ? esc_html(get_option('bpwp_shop_name')) : '';

        if (empty($store) || empty($product)) {
            return [
                'result'   => false,
                'message'  => __('Экспорт невозможен, параметры переданы неверно', 'bonus-plus-wp'),
            ];
        }

        $wrongProducts = self::$lastExport['phide'];
        $wrongProductsAction = get_option('bpwp_wrong_products_action');
        
        // выполняем импорт
        if (($wrongProductsAction == 'hide')) {
            /**
             *  Описание передаваемых параметров https://bonusplus.pro/api/Help/ResourceModel?modelName=ProductImport
             *  
             *  object $product ProductTiny https://bonusplus.pro/api/Help/ResourceModel?modelName=ProductTiny
             *  string $store
             */
            $params = [
                'products' => $product,
                'store'    => esc_html($store),
            ];

            /**
             *  Описание работы хука https://bonusplus.pro/api/Help/Api/POST-product-import
             */
            $export = bpwp_api_request(
                'product/import',
                json_encode($params),
                'POST',
            );

        }

        // не выполняем импорт
        if ($wrongProductsAction == 'stop' && $wrongProducts > 0) {
            $export =  __('Импорт остановлен, тк есть товары принадлежащие больше чем 1 категории. Измените товары или настройки импорта.', 'bonus-plus-wp');
        }

        self::bpwp_prepare_product_export_result($export);
    }


    /**
     *  Разбираем результаты экспорта, записывем в опцию
     * 
     *  @return mixed
     */
    public static function bpwp_prepare_product_export_result($result)
    {
        if (empty($result)) {
            add_option(self::$lastExportOption, date(DATE_ATOM, mktime(0, 0, 0, 7, 1, 2000)));
        } else {
            self::$lastExport['pcount'] = 0;
            self::$lastExport['pexport'] = 0;
            self::$lastExport['cat_count'] = 0;
            self::$lastExport['cat_export'] = 0;
            self::$lastExport['cat_hide'] = 0;
            self::$lastExport['message'] = $result;
            delete_option(self::$lastExportOption);
        }
    }

    /**
     *  Render export ui
     * 
     *  @return mixed
     */
    public static function bpwp_export_ui_btns()
    {
        printf('<h2>%s</h2>', __('Экспорт товаров и категорий', 'bonus-plus-wp'));

        printf('<a href="%s" class="button button-primary">Экспортировать</a>', add_query_arg('a', 'products_cats_export', admin_url('admin.php?page=bpwp-settings')));
    }

    public static function bpwp_export_message_ui()
    {
        $strings = [];
        $class = 'notice notice-warning';

        if (!get_option(self::$lastExportOption)) {
            $strings[] = sprintf('<strong>Статус:</strong> %s', 'Загрузка не производилась');
            if (isset(self::$lastExport['message']) && !empty(self::$lastExport['message'])){
                $strings[] = sprintf('<strong>%s</strong>', self::$lastExport['message']);
                $strings[] = sprintf('<strong>%s: %d</strong>', esc_html(__('Товаров с некорректными категориями', 'bonus-plus-wp')), self::$lastExport['phide']);
            }
        } else {
            $strings[] = sprintf('<strong>Статус:</strong> %s %s', 'Последняя загрузка', esc_html(get_option(self::$lastExportOption)));
            // todo результат загрузки
            if (!empty(self::$lastExport)) {
                $strings[] = sprintf('<strong>%s:</strong>', esc_html(__('Результат загрузки', 'bonus-plus-wp')));
                $strings[] = sprintf('<strong>%s: %d</strong>', esc_html(__('Найдено категорий', 'bonus-plus-wp')), self::$lastExport['cat_count']);
                $strings[] = sprintf('<strong>%s: %d</strong>', esc_html(__('Экспортировано категорий', 'bonus-plus-wp')), self::$lastExport['cat_export']);
                $strings[] = sprintf('<strong>%s: %d</strong>', esc_html(__('Пропущено категорий', 'bonus-plus-wp')), self::$lastExport['cat_hide']);
                $strings[] = sprintf('<strong>%s: %d</strong>', esc_html(__('Найдено товаров', 'bonus-plus-wp')), self::$lastExport['pcount']);
                $strings[] = sprintf('<strong>%s: %d</strong>', esc_html(__('Экспортировано товаров', 'bonus-plus-wp')), self::$lastExport['pexport']);
                $strings[] = sprintf('<strong>%s: %d</strong>', esc_html(__('Пропущено товаров', 'bonus-plus-wp')), self::$lastExport['phide']);
            }
        }

        if (defined('WC_LOG_HANDLER') && 'WC_Log_Handler_DB' == WC_LOG_HANDLER) {
            $strings[] = sprintf('Журнал обработки: <a href="%s">открыть</a>', admin_url('admin.php?page=wc-status&tab=logs&source=WooMS-ProductImage'));
        } else {
            $strings[] = sprintf('Журнал обработки: <a href="%s">открыть</a>', admin_url('admin.php?page=wc-status&tab=logs'));
        }

    ?>
        <div class="wrap">
            <div id="message" class="<?= esc_attr($class) ?>">
                <?php
                foreach ($strings as $string) {
                    if (!is_array($string)) {
                        printf('<p>%s</p>', $string);
                    } else {
                        foreach ($string as $s) {
                            if (!is_array($s) && !is_object($s)) {
                                printf('<p>%s</p>', $s);
                            }
                        }
                    }
                }
                ?>
            </div>
        </div>
<?php
    }
}

BPWPWooProductCatExport::init();
