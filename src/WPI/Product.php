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
 * Product
 *
 * Handle prodcut meta and stock sync
 *
 * @author Hyyan
 */
class Product
{

    /**
     * Constrcut object
     */
    public function __construct()
    {
        add_action('current_screen', array($this, 'syncProductsMeta'));
        add_action('woocommerce_reduce_order_stock', array($this, 'syncStock'));
    }

    /**
     * Sync porduct meta
     * 
     * @return false if the current post type is not "porduct"
     */
    public function syncProductsMeta()
    {
        $currentScreen = get_current_screen();

        if ($currentScreen->post_type !== 'product')
            return false;

        // define the porduct meta that must be synced
        // polylang will handle this
        add_filter('pll_copy_post_metas', function ($metas) {
            return $this->defineProductMetaToCopy($metas);
        });

        // disable editing product meta for translation
        $disable = false;

        /*
         * if the "post" is defined in $_GET then we should check if the current
         * porduct has a translation and it is the same as the default translation
         * lang defined in polylang then porduct meta editing must by enabled
         *
         * if the "new_lang" is defined or if the current page is the "edit"
         * page then porduct meta editing must by disabled
         */
        if ($_GET['post']) {
            $porductID = esc_attr($_GET['post']);
            $disable = $porductID && (pll_get_post_language($porductID) != pll_default_language());
        } elseif (isset($_GET['new_lang']) || $currentScreen->base == 'edit') {
            $disable = true;
        }

        /**
         * enqueue the lock-fileds.js script if we should disable porduct
         * meta editing
         */
        if ($disable) {
            $this->addLockFiledsScript();
        }
    }

    /**
     * Sync stock for product and its translation
     *
     * @param \WC_Order $order
     *
     * @return boolean false if sync failed , true otherwise
     */
    public function syncStock($order)
    {
        // get array of defined langs
        $langs = pll_languages_list();

        // get array of ordered products
        $items = $order->get_items();

        foreach ($items as $item) {

            $productId = $item['product_id'];
            $productObject = wc_get_product($productId);

            // product not found
            if (!$productObject)
                return false;

            // product does not manage the stock
            if (!$productObject->managing_stock())
                return false;

            $productLang = pll_get_post_language($productId);

            // product default lang can not be found
            if (!$productLang)
                return false;

            foreach ($langs as $name) {

                // skip the current product lang
                if ($productLang == $name)
                    continue;

                $translationID = pll_get_post($productId, $name);

                if ($translationID && ($transltedProduct = wc_get_product($translationID))) {
                    $transltedProduct->reduce_stock($item['qty']);
                }
            }
        }

        return true;
    }

    /**
     * Add the lock-fileds.js script
     *
     * The script will disable editing of some porduct metas
     */
    protected function addLockFiledsScript()
    {
        add_action('admin_enqueue_scripts', function () {
            wp_enqueue_script(
                    'wpi-ock-fields.js'
                    , plugins_url('src/assets/js/lock-fields.js', WPI_BASE_FILE)
                    , array('jquery')
                    , '1.0'
                    , true
            );
        }, 100);
    }

    /**
     * Define the meta keys that must copyied from orginal product to its
     * translation
     *
     * @param array $metas array of meta keys
     *
     * @return array extended meta keys array
     */
    protected function defineProductMetaToCopy($metas)
    {
        return array_merge($metas, array(
            '_stock_status',
            '_downloadable',
            '_virtual',
            '_regular_price',
            '_sale_price',
            '_purchase_note',
            '_featured',
            '_weight',
            '_length',
            '_width',
            '_height',
            '_sku',
            '_sale_price_dates_from',
            '_sale_price_dates_to',
            '_price',
            '_sold_individually',
            '_manage_stock',
            '_backorders',
            '_stock',
            '_upsell_ids',
            '_crosssell_ids',
            '_product_image_gallery',
            'total_sales'
        ));
    }

}
