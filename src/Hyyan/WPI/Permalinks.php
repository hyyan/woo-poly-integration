<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI;

/**
 * Permalinks.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Permalinks
{
    const PRODUCT_BASE = '/product';
    const PRODUCT_CATEGORY_BASE = 'product-category';
    const PRODUCT_TAG_BASE = 'product-tags';

    /**
     * Construct object.
     */
    public function __construct()
    {
        add_action('init', array($this, 'setDefaultPermalinks'), 11);
    }

    /**
     * Set default permalinks.
     *
     * Setup the write permalinks to work with polylang if used permalinks is the
     * default woocommerce permalinks
     */
    public function setDefaultPermalinks()
    {
        $permalinks = get_option('woocommerce_permalinks');

        $permalinks['category_base'] = $permalinks['category_base'] ?: self::PRODUCT_CATEGORY_BASE;
        $permalinks['tag_base'] = $permalinks['tag_base'] ?: self::PRODUCT_TAG_BASE;
        $permalinks['product_base'] = $permalinks['product_base'] ?: self::PRODUCT_BASE;

        update_option('woocommerce_permalinks', $permalinks);
    }
}
