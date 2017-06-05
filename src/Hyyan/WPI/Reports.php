<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI;

use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\Features;
use Hyyan\WPI\Utilities;

/**
 * Reports.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Reports
{
    /**
     * Tab name.
     *
     * @var string
     */
    protected $tab;

    /**
     * Report type.
     *
     * @var string
     */
    protected $report;

    /**
     * Construct object.
     */
    public function __construct()
    {
        $this->tab = isset($_GET['tab']) ? esc_attr($_GET['tab']) : false;
        $this->report = isset($_GET['report']) ? esc_attr($_GET['report']) : false;

        if ('off' === Settings::getOption('reports', Features::getID(), 'on')) {
            return;
        }

        /* Handle products filtering and combining */
        if ('orders' == $this->tab || false === $this->report) {
            add_filter(
                    'woocommerce_reports_get_order_report_data', array($this, 'combineProductsByLanguage')
            );
            add_filter(
                    'woocommerce_reports_get_order_report_query', array($this, 'filterProductByLanguage')
            );
        }

        /* handle stock table filtering */
        add_filter(
                'woocommerce_report_most_stocked_query_from', array($this, 'filterStockByLanguage')
        );
        add_filter(
                'woocommerce_report_out_of_stock_query_from', array($this, 'filterStockByLanguage')
        );
        add_filter(
                'woocommerce_report_low_in_stock_query_from', array($this, 'filterStockByLanguage')
        );

        /* Combine product report with its translation */
        add_action('admin_init', array($this, 'translateProductIDS'));

        /* Combine product category report with its translation */
        add_action('admin_init', array($this, 'translateCategoryIDS'));
        add_filter(
                'woocommerce_report_sales_by_category_get_products_in_category', array($this, 'addProductsInCategoryTranslations'), 10, 2
        );
    }

    /**
     * Filter by language.
     *
     * Filter report data according to choosen language
     *
     * @param array $query
     *
     * @return array final report query
     */
    public function filterProductByLanguage(array $query)
    {
        $reports = array(
            'sales_by_product',
            'sales_by_category',
        );
        if (!in_array($this->report, $reports)) {
            return $query;
        }

        /* Check for product_ids */
        if (isset($_GET['product_ids'])) {
            return $query;
        }
        
        $lang = ($current = pll_current_language()) ?
                array($current) :
                pll_languages_list();

        $query['join'] .= PLL()->model->post->join_clause('posts');
        $query['where'] .= PLL()->model->post->where_clause($lang, 'post');

        return $query;
    }

    /**
     * Combine products by language.
     *
     * @param array $results
     *
     * @return array
     */
    public function combineProductsByLanguage($results)
    {
        if (!is_array($results)) {
            return $results;
        }

        if (isset($results['0']->order_item_qty)) {
            $mode = 'top_sellers';
        } elseif (is_array($results) && isset($results['0']->order_item_total)) {
            $mode = 'top_earners';
        } else {
            return $results;
        }

        $translated = array();
        $lang = pll_current_language() ?:
                get_user_meta(get_current_user_id(), 'user_lang', true);

        /* Filter data by language */
        foreach ($results as $data) {
            $translation = Utilities::getProductTranslationByID(
                            $data->product_id, $lang
            );

            if ($translation) {
                $data->from = $data->product_id;
                $data->product_id = $translation->get_id();
            }
            $translated [] = $data;
        }

        /* Unique product IDS */
        $unique = array();

        foreach ($translated as $data) {
            if (!isset($unique[$data->product_id])) {
                $unique[$data->product_id] = $data;
                continue;
            }

            $property = '';
            switch ($mode) {
                case 'top_sellers':
                    $property = 'order_item_qty';
                    break;
                case 'top_earners':
                    $property = 'order_item_total';
                    break;
                default:
                    break;
            }

            $unique[$data->product_id]->$property += $data->$property;
        }

        return array_values($unique);
    }

    /**
     * Filter stock by language.
     *
     * Filter the stock table according to choosen language
     *
     * @param string $query stock query
     *
     * @return string final stock query
     */
    public function filterStockByLanguage($query)
    {
        $lang = ($current = pll_current_language()) ?
                array($current) :
                pll_languages_list();

        $join = PLL()->model->post->join_clause('posts');
        $where = PLL()->model->post->where_clause($lang, 'post');

        return str_replace('WHERE 1=1', "{$join} WHERE 1=1 {$where}", $query);
    }

    /**
     * Translate product IDS for product report.
     *
     * @global \Polylang $polylang
     * @global \WooCommerce $woocommerce
     *
     * @return false if woocommerce or polylang not found
     */
    public function translateProductIDS()
    {
        global $polylang, $woocommerce;
        if (!$polylang || !$woocommerce) {
            return false;
        }

        /* Check for product_ids */
        if (!isset($_GET['product_ids'])) {
            return false;
        }

        $IDS = (array) $_GET['product_ids'];
        $extendedIDS = array();

        if (static::isCombine()) {
            foreach ($IDS as $ID) {
                $translations = Utilities::getProductTranslationsArrayByID($ID);
                $extendedIDS = array_merge($extendedIDS, $translations);
            }
        } elseif (
                isset($_GET['lang']) &&
                esc_attr($_GET['lang']) !== 'all'
        ) {
            $lang = esc_attr($_GET['lang']);
            foreach ($IDS as $ID) {
                $translation = Utilities::getProductTranslationByID($ID, $lang);
                $extendedIDS[] = $translation->id;
            }
        }

        /* Update with extended list */
        if (!empty($extendedIDS)) {
            $_GET['product_ids'] = $extendedIDS;
        }
    }

    /**
     * Translate Category IDS for category report.
     *
     * @global \Polylang $polylang
     * @global \WooCommerce $woocommerce
     *
     * @return false if woocommerce or polylang not found
     */
    public function translateCategoryIDS()
    {
        global $polylang, $woocommerce;
        if (!$polylang || !$woocommerce) {
            return false;
        }

        /* Check for product_ids */
        if (!isset($_GET['show_categories'])) {
            return false;
        }

        if (
                !static::isCombine() &&
                (isset($_GET['lang']) && esc_attr($_GET['lang']) !== 'all')
        ) {
            $IDS = (array) $_GET['show_categories'];
            $extendedIDS = array();
            $lang = esc_attr($_GET['lang']);

            foreach ($IDS as $ID) {
                $translation = pll_get_term($ID, $lang);
                if ($translation) {
                    $extendedIDS[] = $translation;
                }
            }

            if (!empty($extendedIDS)) {
                $_GET['show_categories'] = $extendedIDS;
            }
        }
    }

    /**
     * Collect products from category translations.
     *
     * Add all products in the given category translations
     *
     * @param array $productIDS array of products in the given category
     * @param int   $categoryID category ID
     *
     * @return array array of producs in the given category and its translations
     */
    public function addProductsInCategoryTranslations($productIDS, $categoryID)
    {
        if (static::isCombine()) {

            /* Find the category translations */
            $translations = Utilities::getTermTranslationsArrayByID($categoryID);

            foreach ($translations as $slug => $ID) {
                if ($ID === $categoryID) {
                    continue;
                }

                $termIDS = get_term_children($ID, 'product_cat');
                $termIDS[] = $ID;
                $productIDS = array_merge(
                        $productIDS, (array) get_objects_in_term($termIDS, 'product_cat')
                );
            }
        }

        return $productIDS;
    }

    /**
     * Is combine.
     *
     * Check if combine mode is requested
     *
     * @return bool true if combine mode , false otherwise
     */
    public static function isCombine()
    {
        return !pll_current_language() ||
                (isset($_GET['lang']) && esc_attr($_GET['lang']) === 'all');
    }
}
