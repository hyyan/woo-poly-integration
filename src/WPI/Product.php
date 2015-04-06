<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
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
        // sync product meta
        add_action(
                'current_screen'
                , array($this, 'syncProductsMeta')
        );

        // sync stock
        add_action(
                'woocommerce_reduce_order_stock'
                , array($this, 'syncStock')
        );
        add_filter(
                'woocommerce_restore_order_stock_quantity'
                , array($this, 'restoreStockQuantity')
                , 10
                , 2
        );
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

        /*
         * Disable editing product meta for translation
         *
         * if the "post" is defined in $_GET then we should check if the current
         * porduct has a translation and it is the same as the default translation
         * lang defined in polylang then porduct meta editing must by enabled
         *
         * if the "new_lang" is defined or if the current page is the "edit"
         * page then porduct meta editing must by disabled
         *
         * enqueue the lock-fileds.js script if we should disable porduct
         * meta editing
         */
        $disable = false;
        if (isset($_GET['post'])) {
            $porductID = esc_attr($_GET['post']);
            $disable = $porductID && (pll_get_post_language($porductID) != pll_default_language());
        } elseif (isset($_GET['new_lang']) || $currentScreen->base == 'edit') {
            $disable = true;
        }
        if ($disable) {
            $this->addLockFiledsScript();
        }

        /* sync the prodcut type */
        $this->syncSelectedProdcutType();
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
     * Restore order stock quantity
     *
     * @param integer $change the stock change
     * @param integer $id     item id
     *
     * @return integer stock change
     */
    public function restoreStockQuantity($change, $id)
    {

        $orderId = absint($_POST['order_id']);
        $order = new \WC_Order($orderId);
        $items = $order->get_items();
        $product = $order->get_product_from_item($items[$id]);

        // get array of defined langs
        $langs = pll_languages_list();

        foreach ($items as $item) {

            $productId = $item['product_id'];
            $productObject = wc_get_product($productId);

            // product not found
            if (!$productObject)
                return $change;

            $productLang = pll_get_post_language($productId);

            // product default lang can not be found
            if (!$productLang)
                return $change;

            foreach ($langs as $name) {

                // skip the current product lang
                if ($productLang == $name)
                    continue;

                $translationID = pll_get_post($productId, $name);

                if ($translationID && ($transltedProduct = wc_get_product($translationID))) {
                    $transltedProduct->increase_stock($change);
                }
            }
        }

        return $change;
    }

    /**
     * Sync the porduct select list
     */
    protected function syncSelectedProdcutType()
    {
        /*
         * First we add save_post action to save the porduct type
         * as post meta
         *
         * This is step is important so we can get the right product type
         */
        add_action('save_post', function ($id) {
            $product = get_product($id);
            if ($product) {
                $type = $product->product_type;
                update_post_meta($id, '_translation_porduct_type', $type);
            }
        });

        /*
         * If we can get the post id and the _translation_porduct_type meta is
         * found then we add the js script to sync the product type select
         * list
         */
        $id = isset($_GET['from_post']) ?
                esc_attr($_GET['from_post']) :
                (isset($_GET['post']) ? esc_attr($_GET['post']) : false);

        if ($id && ($type = get_post_meta($id, '_translation_porduct_type'))) {
            add_action('admin_print_scripts', function () use ($type) {
                echo '<script type="text/javascript" id="woo-poly">';
                echo PHP_EOL . '// <![CDATA[' . PHP_EOL;
                echo 'addLoadEvent(function () {' . PHP_EOL;
                echo "jQuery('#product-type option').removeAttr('selected');" . PHP_EOL;
                echo "jQuery('#product-type option[value=\"" . $type[0] . "\"]').attr('selected', 'selected');" . PHP_EOL;
                echo '});' . PHP_EOL;
                echo PHP_EOL . '// ]]>' . PHP_EOL;
                echo '</script>';
            }, 11);
        }
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
            'total_sales',
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
            '_product_url',
            '_translation_porduct_type',
            '_product_attributes',
            '_default_attributes'
        ));
    }

}
