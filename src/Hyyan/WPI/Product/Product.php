<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Product;

use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\Features;

/**
 * Product.
 *
 * Handle product translation
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Product
{
    /**
     * Construct object.
     */
    public function __construct()
    {

        // manage product translation
        add_filter(
                'pll_get_post_types', array($this, 'manageProductTranslation')
        );

        // sync post parent (good for grouped products)
        add_filter('admin_init', array($this, 'syncPostParent'));

        // get attributes in current language
        add_filter(
                'woocommerce_product_attribute_terms', array($this, 'getProductAttributesInLanguage')
        );

        new Meta();
        new Variable();
        new Duplicator();

        if ('on' === Settings::getOption('stock', Features::getID(), 'on')) {
            new Stock();
        }
    }

    /**
     * Notifty polylang about product custom post.
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
     * Tell polylang to sync the post parent.
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

    /**
     * Get product attributes in right language.
     *
     * @param array $args array of arguments for get_terms function in WooCommerce
     *                    attributes html markup
     *
     * @return array
     */
    public function getProductAttributesInLanguage($args)
    {
        global $post;
        $lang = '';

        if (isset($_GET['new_lang'])) {
            $lang = esc_attr($_GET['new_lang']);
        } elseif (!empty($post)) {
            $lang = pll_get_post_language($post->ID);
        } else {
            $lang = PLL()->pref_lang;
        }

        $args['lang'] = $lang;

        return $args;
    }
}
