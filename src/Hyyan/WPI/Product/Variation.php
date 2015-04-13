<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hyyan\WPI\Product;

use Hyyan\WPI\HooksInterface;

/**
 * Description of Variation
 *
 * Handle product variation
 *
 * @author Hyyan
 */
class Variation
{

    /**
     * Constrcut object
     */
    public function __construct()
    {
        // extend meta list to include variation meta
        add_filter(
                HooksInterface::PRODUCT_META_SYNC_FILTER
                , array($this, 'extendProductMetaList')
        );
    }

    /**
     * Extend the product meta list that must by synced
     *
     * @param array $metas current meta list
     *
     * @return array
     */
    public function extendProductMetaList(array $metas)
    {
        return array_merge($metas, array(
            '_min_variation_price',
            '_max_variation_price',
            '_min_price_variation_id',
            '_max_price_variation_id',
            '_min_variation_regular_price',
            '_max_variation_regular_price',
            '_min_regular_price_variation_id',
            '_max_regular_price_variation_id',
            '_min_variation_sale_price',
            '_max_variation_sale_price',
            '_min_sale_price_variation_id',
            '_max_sale_price_variation_id',
        ));
    }

}
