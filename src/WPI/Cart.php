<?php

/**
 * This file is part of the hyyan/woo-poly-integration plubin.
 * (c) Hyyan Abo Fakher <tiribthea4hyyan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WPI;

/**
 * Cart
 *
 * Handle cart
 *
 * @author Hyyan
 */
class Cart
{

    /**
     * Constrcut object
     */
    public function __construct()
    {
        // handle add to cart
        add_filter(
                'woocommerce_add_to_cart_product_id'
                , array($this, 'addToCart'), 10, 1
        );

        // handle the translation of displayed porducts in cart
        add_filter(
                'woocommerce_cart_item_product'
                , array($this, 'translateCartProducts'), 10, 2
        );

        // handle the update of cart widget when lang is switched
        add_action('wp_enqueue_scripts', array($this, 'replaceCartFragmentsScript'), 100);
    }

    /**
     * Add to cart
     *
     * The function will make sure that products won't be dublicated for each
     * lang
     *
     * @global \PLL_Model $polylang
     * @global \WooCommerce $woocommerce
     *
     * @param integer $id the current product id
     *
     * @return integer the product translation id
     */
    public function addToCart($id)
    {

        global $polylang, $woocommerce;

        $result = $id;

        // get the product translations
        $IDS = $polylang->get_translations('post', $id);

        // check if any of product's translation is already in cart
        foreach ($woocommerce->cart->get_cart() as $keys => $values) {

            $product = $values['data'];

            if (in_array($product->id, $IDS)) {
                $result = $product->id;
            }
        }

        return $result;
    }

    /**
     * Translate displayed products in cart
     *
     * @param \WC_Product_Simple $cartItemData
     * @param array              $cartItem
     *
     * @return \WC_Product_Simple
     */
    public function translateCartProducts($cartItemData, $cartItem)
    {

        $result = $cartItemData;

        $productID = pll_get_post($cartItem['product_id']);
        if ($productID) {
            $translated = wc_get_product($productID);
            $result = $translated ? $translated : $cartItemData;
        }

        return $result;
    }

    /**
     * Replace woo fragments script
     *
     * To update cart widget when lang is switched
     */
    public function replaceCartFragmentsScript()
    {
        // remove the orginal wc-cart-fragments.js and register ours
        // link : remove the orginal wc-cart-fragments and register ours

        wp_deregister_script('wc-cart-fragments');
        wp_enqueue_script(
                'wc-cart-fragments'
                , plugins_url('src/assets/js/cart-fragments.js', WPI_BASE_FILE)
                , array('jquery', 'jquery-cookie')
                , '1.0'
                , true
        );
    }

}
