<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <tiribthea4hyyan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Product;

use Hyyan\WPI\HooksInterface;
use Hyyan\WPI\Utilities;

/**
 * Variable
 *
 * Handle Variable Products
 *
 * @author Hyyan
 */
class Variable
{

    /**
     * Construct object
     */
    public function __construct()
    {

        add_action('save_post', array($this, 'variations'), 10, 3);

        // extend meta list to include variation meta
        add_filter(
                HooksInterface::PRODUCT_META_SYNC_FILTER
                , array($this, 'extendProductMetaList')
        );
    }

    /**
     * Translate Variation for given variable product
     *
     * @param integer  $ID     product variable ID
     * @param \WP_Post $post   Product Post
     * @param boolean  $update true if update , false otherwise
     *
     * @return boolean
     */
    public function variations($ID, \WP_Post $post, $update)
    {

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }

        global $pagenow;
        if (!in_array($pagenow, array('post.php', 'post-new.php'))) {
            return false;
        }

        $product = wc_get_product($ID);

        if (!$product) {
            return false;
        }

        $from = null;

        if (pll_get_post_language($ID) == pll_default_language()) {
            $from = $product;
        } else {

            if (isset($_GET['from_post'])) {
                /*
                 * This check will make sure that variation , will be
                 * created for brand new products which are not saved yet by user
                 */
                $from = Utilities::getProductTranslationByID(
                        esc_attr($_GET['from_post'])
                        , pll_default_language()
                );
            } else {
                $from = Utilities::getProductTranslationByObject(
                        $product
                        , pll_default_language()
                );
            }
        }

        if (!($from instanceof \WC_Product_Variable)) {
            return false;
        }

        $langs = pll_languages_list();

        foreach ($langs as $lang) {
            $variation = new Variation(
                    $from
                    , Utilities::getProductTranslationByObject($product, $lang)
            );
            remove_action('save_post', array($this, 'variations'), 10);
            $variation->duplicate();
            add_action('save_post', array($this, 'variations'), 10, 3);
        }
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
