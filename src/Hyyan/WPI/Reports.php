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
     * Construct object
     */
    public function __construct()
    {
        add_filter(
                'woocommerce_reports_get_order_report_query'
                , array($this, 'filterByLanguage')
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
    public function filterByLanguage(array $query)
    {
        if (
                !(
                isset($_GET['tab']) &&
                esc_attr($_GET['tab']) === 'orders'
                )
        ) {
            return $query;
        }

        if (!isset($_GET['report'])) {
            return $query;
        }

        $report = esc_attr($_GET['report']);
        $type = null;
        if ($report === 'sales_by_product') {
            $type = 'post';
        } elseif ($report === 'sales_by_category') {
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

}
