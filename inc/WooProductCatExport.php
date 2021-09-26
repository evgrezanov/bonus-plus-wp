<?php

namespace BPWP;

defined('ABSPATH') || exit; // Exit if accessed directly

class BPWPWooProductCatExport
{   
    /**
     *  Название опции с данными о последнем экспорте товаров
     */
    public static $lastExportOption;

    /**
     *  Данные о последнем экспорте
     */
    public static $lastExport = [];

    /**
     *  Категории товара не имеющие дочерних категорий
     */
    public static $productCatNoChild = [];

    //public static $error = 0;

    public static $docUri;

    /**
     *  Init
     */
    public static function init()
    {
        self::$lastExportOption = 'bpwp_last_products_export_date';
        self::$docUri = 'https://github.com/evgrezanov/bonus-plus-wp/wiki/Export-products-and-product-cat';
        // set default values
        self::$lastExport['message'] = empty(self::$lastExport['message']) ? __('Экспорт еще не производился','bonus-plus-wp') : self::$lastExport['message'];
        self::$lastExport['cat_count'] = empty(self::$lastExport['cat_count']) ? 0 : self::$lastExport['message'];
        self::$lastExport['cat_export'] = empty(self::$lastExport['cat_export']) ? 0 : self::$lastExport['message'];
        self::$lastExport['cat_hide'] = empty(self::$lastExport['cat_hide']) ? 0 : self::$lastExport['message'];
        self::$lastExport['pcount'] = empty(self::$lastExport['pcount']) ? 0 : self::$lastExport['message'];
        self::$lastExport['pexport'] = empty(self::$lastExport['pexport']) ? 0 : self::$lastExport['message'];
        self::$lastExport['phide'] = empty(self::$lastExport['phide']) ? 0 : self::$lastExport['message'];
        self::$lastExport['vcount'] = empty(self::$lastExport['vcount']) ? 0 : self::$lastExport['message'];

        
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
            $title = __('Действие при экспорте с товаром у которого больше 1 категории:', 'bonus-plus-wp'),
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

        $wrongProductsAction = get_option('bpwp_wrong_products_action');
        ?>
        <select class="check_prefix_postfix" name="bpwp_wrong_products_action">
            <?php
                printf(
                    '<option value="%s" %s>%s</option>',
                    'hide',
                    selected('hide', $wrongProductsAction, false),
                    __('Импортировать, не включая товар в файл импорта', 'bonus-plus-wp')
                );
                printf(
                    '<option value="%s" %s>%s</option>',
                    'empty',
                    selected('empty', $wrongProductsAction, false),
                    __( 'Импортировать товар, без категории', 'bonus-plus-wp')
                );
            ?>
        </select>
        <?php 
            printf('<p><small>%s <a href="%s" target="_blank">%s</a></small></p>', 
                esc_html(__('В Бонус+ 1 товару, может соответствовать только одна категория товаров, подробнее', 'bonus-plus-wp')),
                esc_attr(self::$docUri),
                esc_html(__('здесь', 'bonus-plus-wp'))
            );
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
                $parent = $term->parent == 0 ? 0 : $term->parent;
                $catId = $term->term_id;
                $product_cat = [
                    'id'  => $catId,
                    'pid' => $parent,
                    'n'   => $term->name,
                    'g'   => true,
                ];
                $product_cats[] = $product_cat;
                // проверим есть ли у категории потомки
                $childCats = get_terms(
                    [
                        'taxonomy'   => 'product_cat',
                        'hide_empty' => false,
                        'parent'     => $catId,
                    ]
                );
                if (!($childCats)){
                    self::$productCatNoChild[] = $catId;
                }
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
        $simpleProducts = [];
        $variableProducts = [];
        $wrongProductsAction = get_option('bpwp_wrong_products_action');
        
        $args = array(
            'status' => 'published',
            'limit'  => -1,
            'type'   => ['simple', 'variable'],
        );

        if ($products = wc_get_products($args)) {
            
            foreach ($products as $product) {
                $productId = $product->get_id();
                
                $productName = $product->get_name();
                $productCategory = self::bpwp_get_product_child_category($productId);
                
                if (count($productCategory) !== 1) {
                    switch ($wrongProductsAction) {
                        case 'hide':
                            do_action(
                                'bpwp_logger_error',
                                $type = __CLASS__,
                                $title = __('Экспорт товаров в Бонус+, товар пропущен', 'bonus-plus-wp'),
                                $desc = sprintf(__('У товара [%s] %s более 1 дочерней категории', 'bonus-plus-wp'), $productId, $productName),
                            );
                            $productCat = -1;
                            break;
                        case 'empty':
                            $productCat = 0;
                            break;
                    }
                    $wrongProducts[$productId] = $productName;
                } else {
                    foreach ($productCategory as $productCat) {
                        $productCat = $productCat->term_id;
                    }
                }

                // простые товары
                if ($product->is_type('simple') && $productCat >= 0){
                    $productList[] = [
                        'id'  => $productId,
                        'pid' => $productCat,
                        'n'   => $productName,
                        'g'   => false,
                    ];
                    $simpleProducts[$productId] = $productName;
                } 
                
                // вариативные товары
                if ($product->is_type('variable') && $productCat >= 0){
                    foreach ($product->get_available_variations() as $variation) {
                        $variationId = $variation['variation_id'];
            
                        $var = wc_get_product($variationId);
                        $variationName = $var->get_formatted_name();
                        
                        $productList[] = [
                            'id'  => $variationId,
                            'pid' => $productCat,
                            'n'   => $variationName,
                            'g'   => false,
                        ];
                        $variableProducts[$variationId] = $variationName;
                    }
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
        self::$lastExport['vcount'] = count($variableProducts) > 0 ? count($variableProducts) : 0;

        return $productList;
    }

    /**
     *  Отправляет запрос к API Б+
     * 
     *  @param $product ProductTiny Массив категорий и товаров https://bonusplus.pro/api/Help/ResourceModel?modelName=ProductTiny
     * 
     *  @return void Результаты выполнения запроса к Б+
     */
    public static function bpwp_api_products_cats_export($product = [])
    {

        if (empty($product)) {
            $product_cat = apply_filters('bpwp_filter_export_product_cat', self::bpwp_api_product_cat_data_prepare()); 
            $products = apply_filters('bpwp_filter_export_products', self::bpwp_api_products_data_prepare());
            $product = array_merge($product_cat, $products);
            /*
            usort($product, function ($a, $b) { 
                return $a['id'] - $b['id'];
            });
            */
        }

        $store = !empty(get_option('bpwp_shop_name')) ? esc_html(get_option('bpwp_shop_name')) : '';

        if (empty($store) || empty($product)) {
            self::$lastExport['message'] =  __('Экспорт невозможен, параметры переданы неверно', 'bonus-plus-wp');
        }

    
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

        if (empty(self::$lastExport['message'])){
            $export = bpwp_api_request(
                'product/import',
                json_encode($params),
                'POST',
            );

            if ($export){
                add_option(self::$lastExportOption, date(DATE_ATOM, mktime(0, 0, 0, 7, 1, 2000)));
                self::$lastExport['message'] = $export['message'];
                self::$lastExport['class'] = $export['class'];
            }
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

    /**
     *  Отображение уведомления с результатами экспорта
     */
    public static function bpwp_export_message_ui()
    {
        $strings = [];
        $class = isset(self::$lastExport['class']) ? self::$lastExport['class'] : 'notice notice-warning';
        $lastExportDate = !empty(get_option(self::$lastExportOption)) ? get_option(self::$lastExportOption) : '';
        $wrongProductsAction = get_option('bpwp_wrong_products_action');
        switch ($wrongProductsAction) {
            case 'hide':
                $action = __('Пропущено товаров', 'bonus-plus-wp');
                break;
            case 'empty':
                $action = __('Экспортировано товаров, без категории', 'bonus-plus-wp');
                break;
        }

        $strings[] = sprintf('<strong>Результат последнего экспорта %s :</strong> %s', $lastExportDate, self::$lastExport['message']);
        $strings[] = sprintf('<strong>%s: %d</strong>', esc_html(__('Найдено категорий', 'bonus-plus-wp')), self::$lastExport['cat_count']);
        $strings[] = sprintf('<strong>%s: %d</strong>', esc_html(__('Экспортировано категорий', 'bonus-plus-wp')), self::$lastExport['cat_export']);
        $strings[] = sprintf('<strong>%s: %d</strong>', esc_html(__('Пропущено категорий', 'bonus-plus-wp')), self::$lastExport['cat_hide']);
        $strings[] = sprintf('<strong>%s: %d</strong>', esc_html(__('Найдено товаров', 'bonus-plus-wp')), self::$lastExport['pcount']);
        $strings[] = sprintf('<strong>%s: %d</strong>', esc_html(__('Экспортировано товаров', 'bonus-plus-wp')), self::$lastExport['pexport']);
        $strings[] = sprintf('<strong>%s: %d</strong>', esc_html($action), self::$lastExport['phide']);
        $strings[] = sprintf('<strong>%s: %d</strong>', esc_html(__('Экспортировано вариаций', 'bonus-plus-wp')), self::$lastExport['vcount']);


        if (defined('WC_LOG_HANDLER') && 'WC_Log_Handler_DB' == WC_LOG_HANDLER) {
            $strings[] = sprintf('Журнал обработки: <a href="%s">открыть</a>', admin_url('admin.php?page=wc-status&tab=logs&source=WooMS-ProductImage'));
        } else {
            $strings[] = sprintf('Журнал обработки: <a href="%s">открыть</a>', admin_url('admin.php?page=wc-status&tab=logs'));
        }
        
        $strings[] = sprintf('Документация по процедуре экспорта: <a href="%s" target="_blank">открыть</a>', esc_attr(self::$docUri));

        ?>
        <div class="wrap">
            <div id="message" class="<?= esc_attr($class) ?>">
                <?php
                    foreach ($strings as $string) {
                        printf('<p>%s</p>', $string);
                    }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     *  Return child category for product id https://wordpress.stackexchange.com/a/55921
     *  
     *  @param $productId int ИД товара
     * 
     *  @return array Array of child product categories
     */
    public static function bpwp_get_product_child_category($productId){
        //Get all terms associated with post in woocommerce's taxonomy 'product_cat'
        $terms = get_the_terms($productId, 'product_cat');

        //Get an array of their IDs
        $term_ids = wp_list_pluck($terms, 'term_id');

        //Get array of parents - 0 is not a parent
        $parents = array_filter(wp_list_pluck($terms, 'parent'));

        //Get array of IDs of terms which are not parents.
        $term_ids_not_parents = array_diff($term_ids,  $parents);

        //Get corresponding term objects.
        $terms_not_parents = array_intersect_key($terms,  $term_ids_not_parents);

        return $terms_not_parents;
    }
}

BPWPWooProductCatExport::init();
