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
     * Setup the right permalinks to work with polylang if used permalinks is the
     * default woocommerce permalinks
     * (This was getting called too often, also a situation occurred where these got set to boolean...)
     */
    public function setDefaultPermalinks()
    {
        $permalinks = get_option('woocommerce_permalinks');
        $was_set=false;
        if (! isset($permalinks['category_base']) || (is_bool($permalinks['category_base']))) {
            $permalinks['category_base'] = self::PRODUCT_CATEGORY_BASE;
            $was_set=true;
        }
        if (! isset($permalinks['tag_base']) || (is_bool($permalinks['tag_base']))) {
            $permalinks['tag_base'] =  self::PRODUCT_TAG_BASE;
            $was_set=true;
        }
        if (! isset($permalinks['product_base']) || (is_bool($permalinks['product_base']))) {
            $permalinks['product_base'] = self::PRODUCT_BASE;
            $was_set=true;
        }

        if ($was_set) {
            update_option('woocommerce_permalinks', $permalinks);
        }
    }
}
