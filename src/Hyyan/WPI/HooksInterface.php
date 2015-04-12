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
 * Plugin Hooks Interface
 *
 * @author Hyyan
 */
interface HooksInterface
{

    /**
     * Product Meta Sync Filter
     *
     * The filter is fired before product meta array is passed to polylang
     * to handle sync.
     *
     * The filter recive one parameter which is the meta array
     *
     * for instance :
     * <code>
     * add_filter(Hyyan\WPI\HooksInterface::PRODUCT_META_SYNC_FILTER,function($meta=array()) {
     *
     *      // do whatever you want
     *
     *      return $meta;
     * });
     * </code>
     */
    const PRODUCT_META_SYNC_FILTER = 'woo-poly.product.metaSync';
}
