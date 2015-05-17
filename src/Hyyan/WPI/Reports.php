<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <tiribthea4hyyan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI;

/**
 * Reports
 *
 * @author Hyyan
 */
class Reports
{
    /**
     * Tab name
     *
     * @var string
     */
    protected $tab;

    /**
     * Report type
     *
     * @var string
     */
    protected $report;

    /**
     * Combine button styling
     *
     * @var array
     */
    protected $combineStyle = array(
        'height:inherit',
        'float:right',
        'line-height:26px',
        'padding: 10px',
        'display: block',
        'text-decoration: none'
    );

    /**
     * Construct object
     */
    public function __construct()
    {

        $this->tab = isset($_GET['tab']) ? esc_attr($_GET['tab']) : false;
        $this->report = isset($_GET['report']) ? esc_attr($_GET['report']) : false;

        add_filter(
                'woocommerce_reports_get_order_report_query'
                , array($this, 'filterProductByLanguage')
        );

        /* handle stock table filtering */
        add_filter(
                'woocommerce_report_most_stocked_query_from'
                , array($this, 'filterStockByLangauge')
        );
        add_filter(
                'woocommerce_report_out_of_stock_query_from'
                , array($this, 'filterStockByLangauge')
        );
        add_filter(
                'woocommerce_report_low_in_stock_query_from'
                , array($this, 'filterStockByLangauge')
        );

        /* Combine product report with its translation */
        add_action('admin_init', array($this, 'translateProductIDS'));

        /* Combine product category report with its translation */
        add_action('admin_init', array($this, 'translateCategoryIDS'));
        add_filter(
                'woocommerce_report_sales_by_category_get_products_in_category'
                , array($this, 'addProductsInCategoryTranslations')
                , 10
                , 2
        );
    }

    /**
     * Filter by lanaguge
     *
     * Filter report data according to choosen lanaguge
     *
     * @global \Polylang $polylang
     * @param array $query
     *
     * @return array final report query
     */
    public function filterProductByLanguage(array $query)
    {
        if ('orders' !== $this->tab || false === $this->report) {
            return $query;
        }

        $reports = array(
            'sales_by_product',
            'sales_by_category'
        );
        if (!in_array($this->report, $reports)) {
            return $query;
        }

        /* Check for product_ids */
        if (isset($_GET['product_ids'])) {
            return $query;
        }

        global $polylang;
        $lang = ($current = pll_current_language()) ?
                array($current) :
                pll_languages_list();

        $query['join'].= $polylang->model->join_clause('post');
        $query['where'].= $polylang->model->where_clause($lang, 'post');

        return $query;
    }

    /**
     * Filter stock by langauge
     *
     * Filter the stock table according to choosen langauge
     *
     * @global \Polylang $polylang
     * @param string $query stock query
     *
     * @return string final stock query
     */
    public function filterStockByLangauge($query)
    {
        global $polylang;
        $lang = ($current = pll_current_language()) ?
                array($current) :
                pll_languages_list();

        $join = $polylang->model->join_clause('post');
        $where = $polylang->model->where_clause($lang, 'post');

        return str_replace('WHERE 1=1', "{$join} WHERE 1=1 {$where}", $query);
    }

    /**
     * Translate product IDS for product report
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

        /* Show combine button anyway */
        add_action(
                'admin_print_scripts'
                , array($this, 'showCombineButton')
                , 100
        );

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
     * Translate Category IDS for category report
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

        /* Show combine button anyway */
        add_action(
                'admin_print_scripts'
                , array($this, 'showCombineButton')
                , 100
        );

        if (
                !static::isCombine() &&
                (isset($_GET['lang']) && esc_attr($_GET['lang']) !== 'all' )
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
     * Collect products from category translations
     *
     * Add all products in the given category translations
     *
     * @param array   $productIDS array of products in the given category
     * @param integer $categoryID category ID
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
                        $productIDS
                        , (array) get_objects_in_term($termIDS, 'product_cat')
                );
            }
        }

        return $productIDS;
    }

    /**
     * Show the combine buttton for product report
     */
    public function showCombineButton()
    {
        $isCombine = static::isCombine();
        $url = Utilities::getCurrentAdminUrl();

        if (!$isCombine) {
            $url = add_query_arg(array('combine' => '', 'lang' => 'all'), $url);
        } else {
            $url = remove_query_arg('combine', $url);
        }

        $title = __('Combine Report', 'woo-poly-integration');
        $desc = __('Combine Report With Its Translation', 'woo-poly-integration');
        $icon = $isCombine ?
                '<span class=\"dashicons dashicons-yes\" style=\"line-height:inherit\"></span>' :
                '<span class=\"dashicons dashicons-editor-contract\" style=\"line-height:inherit\"></span>';
        $style = $isCombine ? implode(';', $this->combineStyle) . ';color:green' :
                implode(';', $this->combineStyle);

        printf(
                '<script type="text/javascript" id="woo-poly-combine-report">'
                . ' jQuery(document).ready(function ($) {'
                . '     $(".stats_range").prepend("<a href=\'%s\' style=\'%s\' title=\'%s\'>%s</a>")'
                . ' });'
                . '</script>'
                , $url
                , $style
                , $desc
                , $icon . $title
        );
    }

    /**
     * Is combine
     *
     * Check if combine mode is requested
     *
     * @return boolean true if combine mode , false otherwise
     */
    public static function isCombine()
    {
        return isset($_GET['combine']) &&
                (isset($_GET['lang']) && esc_attr($_GET['lang']) === 'all');
    }

}
