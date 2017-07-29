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

        //Product title/description sync/translate, defaults to 0-Off for back-compatiblity
        $translate_option = Settings::getOption('new-translation-defaults', Features::getID(), 0);
        if ($translate_option) {
            add_filter('default_title', array($this, 'wpi_editor_title'));
            add_filter('default_content', array($this, 'wpi_editor_content'));
            add_filter('default_excerpt', array($this, 'wpi_editor_excerpt'));
        }
                
        //TODO: this filter appears to be unnecessary - remove
        //woocommerce_product_attribute_terms is already getting terms for a particular attribute
        //which is already the language version of the attribute ...
        // get attributes in current language
        /*
         *
        add_filter(
                'woocommerce_product_attribute_terms', array($this, 'getProductAttributesInLanguage')
        );
         */
        //show cross-sells and up-sells in correct language
        add_filter('woocommerce_product_get_upsell_ids', array($this, 'getUpsellsInLanguage'), 10, 2);
        add_filter('woocommerce_product_get_cross_sell_ids', array($this, 'getCrosssellsInLanguage'), 10, 2);
        add_filter('woocommerce_product_get_children', array($this, 'getChildrenInLanguage'), 10, 2);
        
        new Meta();
        new Variable();
        new Duplicator();

        if ('on' === Settings::getOption('stock', Features::getID(), 'on')) {
            new Stock();
        }
    }

    
    /**
     * filter child ids of Grouped Product
     *
     * @param array      $related_ids array of product ids
     * @param WC_Product $product current product
     *
     * @return array filtered result
     */
    public function getChildrenInLanguage($relatedIds, $product)
    {
        return $this->getProductIdsInLanguage($relatedIds, $product);
    }
    /**
     * filter upsells display
     *
     * @param array      $related_ids array of product ids
     * @param WC_Product $product current product
     *
     * @return array filtered result
     */
    public function getUpsellsInLanguage($relatedIds, $product)
    {
        return $this->getProductIdsInLanguage($relatedIds, $product);
    }
    /**
     * filter Cross-sells display
     *
     * @param array      $related_ids array of product ids
     * @param WC_Product $product current product
     *
     * @return array filtered result
     */
    public function getCrosssellsInLanguage($relatedIds, $product)
    {
        return $this->getProductIdsInLanguage($relatedIds, $product);
    }
    /**
     * filter product ids
     *
     * @param array      $product_ids array of product ids
     * @param WC_Product $product current product
     *
     * @return array filtered result
     */
    public function getProductIdsInLanguage($productIds, $product)
    {
        $productLang = pll_get_post_language($product->get_id());
        $mappedIds = array();
        foreach ($productIds as $productId) {
            $correctLanguageId = pll_get_post($productId, $productLang);
            if ($correctLanguageId) {
                $mappedIds[]=$correctLanguageId;
            } else {
                //what do you want to do if product not available in current display language?
                //allow the available product language to be returned
                $mappedIds[]=$productId;
            }
        }
        return $mappedIds;
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
        

    // Make sure Polylang copies the title when creating a translation
    public function wpi_editor_title($title)
    {
        // Polylang sets the 'from_post' parameter
        if (isset($_GET['from_post'])) {
            $my_post = get_post($_GET['from_post']);
            if ($my_post) {
                return $my_post->post_title;
            }
        }
        return $title;
    }

    // Make sure Polylang copies the content when creating a translation
    public function wpi_editor_content($content)
    {
        // Polylang sets the 'from_post' parameter
        if (isset($_GET['from_post'])) {
            $my_post = get_post($_GET['from_post']);
            if ($my_post) {
                return $my_post->post_content;
            }
        }
        return $content;
    }

    // Make sure Polylang copies the excerpt [woocommerce short description] when creating a translation
    public function wpi_editor_excerpt($excerpt)
    {
        // Polylang sets the 'from_post' parameter
        if (isset($_GET['from_post'])) {
            $my_post = get_post($_GET['from_post']);
            if ($my_post) {
                return $my_post->post_excerpt;
            }
        }
        return $excerpt;
    }
}
