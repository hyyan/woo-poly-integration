<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <tiribthea4hyyan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Product;

use Hyyan\WPI\Admin\Settings,
    Hyyan\WPI\Admin\Features;

/**
 * Product
 *
 * Handle product translation
 *
 * @author Hyyan
 */
class Product
{

    /**
     * Construct object
     */
    public function __construct()
    {

        // manage product translation
        add_filter(
                'pll_get_post_types'
                , array($this, 'manageProductTranslation')
        );

        // sync post parent (good for grouped products)
        add_filter('admin_init', array($this, 'syncPostParent'));

        new Meta();
        new Variable();
        new Duplicator();

        if ('on' === Settings::getOption('stock', Features::getID(), 'on')) {
            new Stock();
        }
    }

    /**
     * Notifty polylang about product custom post
     *
     * @param array $types array of custom post names managed by polylang
     *
     * @return array
     */
    public function manageProductTranslation(array $types)
    {

        $options = get_option('polylang');
        $postTypes = $options['post_types'];
        if (!in_array('product', $postTypes)) {
            $options['post_types'][] = 'product';
            update_option('polylang', $options);
        }

        $types [] = 'product';

        return $types;
    }

    /**
     * Tell polylang to sync the post parent
     */
    public function syncPostParent()
    {
        $options = get_option('polylang');
        $sync = $options['sync'];
        if (!in_array('post_parent', $sync)) {
            $options['sync'][] = 'post_parent';
            update_option('polylang', $options);
        }
    }

}
