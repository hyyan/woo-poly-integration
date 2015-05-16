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
        add_action('admin_init', array($this, 'extendProductIDS'));
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
     * Extend product IDS for product report
     *
     * @global \Polylang $polylang
     * @global \WooCommerce $woocommerce
     *
     * @return false if woocommerce or polylang not found
     */
    public function extendProductIDS()
    {
        global $polylang, $woocommerce;
        if (!$polylang || !$woocommerce) {
            return false;
        }

        /* Check for product_ids */
        if (isset($_GET['product_ids'])) {

            add_action(
                    'admin_print_scripts'
                    , array($this, 'showProductCombineButton')
                    , 100
            );

            if (isset($_GET['combine'])) {
                $IDS = (array) $_GET['product_ids'];
                $extendedIDS = array();

                foreach ($IDS as $ID) {
                    $translations = Utilities::getProductTranslationsArrayByID($ID);
                    $extendedIDS = array_merge($extendedIDS, $translations);
                }

                /* Update with extended list */
                $_GET['product_ids'] = $extendedIDS;
            }
        }
    }

    /**
     * Show the combine buttton for product report
     */
    public function showProductCombineButton()
    {
        $isCombine = isset($_GET['combine']) ? true : false;
        $url = Utilities::getCurrentAdminUrl();

        if (!$isCombine) {
            $url = add_query_arg('combine', '', $url);
        } else {
            $url = remove_query_arg('combine', $url);
        }

        $title = __('Combine Report', 'woo-poly-integration');
        $desc = __('Combine Product Report With Its Translation', 'woo-poly-integration');
        $icon = $isCombine ?
                '<span class=\"dashicons dashicons-yes\" style=\"line-height:inherit\"></span>' :
                '<span class=\"dashicons dashicons-editor-contract\" style=\"line-height:inherit\"></span>';
        $style = $isCombine ? implode(';', $this->combineStyle) . ';color:green' :
                implode(';', $this->combineStyle);

        printf(
                '<script type="text/javascript" id="woo-poly-combine-product-report">'
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

}
