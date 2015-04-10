<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <tiribthea4hyyan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Product;

/**
 * Prodcut Meta
 *
 * Handle product meta sync
 *
 * @author Hyyan
 */
class Meta
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

        $ID = false;
        $disable = false;

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

        if (isset($_GET['post'])) {
            $ID = esc_attr($_GET['post']);
            $disable = $ID && (pll_get_post_language($ID) != pll_default_language());
        } elseif (isset($_GET['new_lang']) || $currentScreen->base == 'edit') {
            $disable = true;
            $ID = isset($_GET['from_post']) ? esc_attr($_GET['from_post']) : false;
        }

        // disable fields edit for translation
        if ($disable) {
            $this->addFieldsLockerScript();
        }

        /* sync selected prodcut type */
        $this->syncSelectedProdcutType($ID);

        // sync product meta with polylang
        if ($ID && ($product = wc_get_product($ID))) {

            // define the porduct meta that must be synced
            // polylang will handle this part
            add_filter('pll_copy_post_metas', function ($metas) use ($product) {
                return $this->defineProductMetaToCopy($metas, $product);
            });

        }
    }

    /**
     * Sync the porduct select list
     *
     * @param integer $ID product type
     */
    protected function syncSelectedProdcutType($ID = null)
    {
        /*
         * First we add save_post action to save the porduct type
         * as post meta
         *
         * This is step is important so we can get the right product type
         */
        add_action('save_post', function ($ID) {
            $product = get_product($ID);
            if ($product) {
                $type = $product->product_type;
                update_post_meta($ID, '_translation_porduct_type', $type);
            }
        });

        /*
         * If the _translation_porduct_type meta is
         * found then we add the js script to sync the product type select
         * list
         */
        if ($ID && ($type = get_post_meta($ID, '_translation_porduct_type'))) {
            add_action('admin_print_scripts', function () use ($type) {
                printf(
                        '<script type="text/javascript" id="woo-poly">'
                        . '// <![CDATA[ %1$s'
                        . ' addLoadEvent(function () { %1$s'
                        . '     jQuery("#product-type option").removeAttr("selected");%1$s'
                        . '     jQuery("#product-type option[value=\"%2$s\"]").attr("selected", "selected");%1$s'
                        . '});'
                        . '// ]]>'
                        . '</script>'
                        , PHP_EOL
                        , $type[0]
                );
            }, 11);
        }
    }

    /**
     * Add the Fields Locker script
     *
     * The script will disable editing of some porduct metas for product
     * translation
     *
     * @todo Add option to control this part
     */
    protected function addFieldsLockerScript()
    {
        add_action('admin_enqueue_scripts', function () {
            wp_enqueue_script(
                    'hyyan-wpi-fields-locker.js'
                    , plugins_url('public/js/FieldsLocker.js', Hyyan_WPI_DIR)
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
     * @param array       $metas   array of meta keys
     * @param \WC_Product $product the current product object
     *
     * @return array extended meta keys array
     */
    protected function defineProductMetaToCopy($metas, $product)
    {
        return array_merge($metas, array(
            '_stock_status',
            'total_sales',
            '_downloadable',
            '_downloadable_files',
            '_download_limit',
            '_download_expiry',
            '_download_type',
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
