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

        $type = null;
        if ($this->report === 'sales_by_product') {
            $type = 'post';
        } elseif ($this->report === 'sales_by_category') {
            $type = 'term';
        }

        if (!$type) {
            return $query;
        }

        global $polylang;
        $lang = ($current = pll_current_language()) ?
                array($current) :
                pll_languages_list();

        $query['join'].= $polylang->model->join_clause($type);
        $query['where'].= $polylang->model->where_clause($lang, $type);

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

}
