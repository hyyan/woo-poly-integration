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
 * Prodcut Meta
 *
 * Handle product meta sync
 *
 * @author Hyyan
 */
class Meta
{

    /**
     * Construct object
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

        // sync product meta with polylang
        add_filter('pll_copy_post_metas', array($this, 'defineProductMetaToCopy'));

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
            $ID = absint($_GET['post']);
            $disable = $ID && (pll_get_post_language($ID) != pll_default_language());
        } elseif (isset($_GET['new_lang']) || $currentScreen->base == 'edit') {
            $disable = isset($_GET['new_lang']) && (esc_attr($_GET['new_lang']) != pll_default_language()) ?
                    true : false;
            $ID = isset($_GET['from_post']) ? absint($_GET['from_post']) : false;
        }

        // disable fields edit for translation
        if ($disable) {
            $this->addFieldsLockerScript();
        }

        /* sync selected prodcut type */
        $this->syncSelectedProdcutType($ID);
    }

    /**
     * Define the meta keys that must copyied from orginal product to its
     * translation
     *
     * @param array $metas array of meta keys
     *
     * @return array extended meta keys array
     */
    public function defineProductMetaToCopy(array $metas = array())
    {

        $default = array(
            // general
            'product-type',
            '_virtual',
            '_downloadable',
            '_sku',
            '_regular_price',
            '_sale_price',
            '_sale_price_dates_from',
            '_sale_price_dates_to',
            '_downloadable_files',
            '_download_limit',
            '_download_expiry',
            '_download_type',
            // stock
            '_manage_stock',
            '_stock',
            '_backorders',
            '_stock_status',
            '_sold_individually',
            // shipping
            '_weight',
            '_length',
            '_width',
            '_height',
            'product_shipping_class',
            // advanced
            '_purchase_note',
            'menu_order',
            'comment_status',
            // extra
            '_upsell_ids',
            '_crosssell_ids',
            '_featured',
            '_thumbnail_id',
            '_price',
            '_product_image_gallery',
            'total_sales',
            '_translation_porduct_type',
            '_visibility',
        );

        return array_merge(
                $metas
                , apply_filters(HooksInterface::PRODUCT_META_SYNC_FILTER, $default)
        );
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
        add_action('save_post', function ($_ID) {
            $product = get_product($_ID);
            if ($product) {
                $type = $product->product_type;
                update_post_meta($_ID, '_translation_porduct_type', $type);
            }
        });

        /*
         * If the _translation_porduct_type meta is
         * found then we add the js script to sync the product type select
         * list
         */
        if ($ID && ($type = get_post_meta($ID, '_translation_porduct_type'))) {

            add_action('admin_print_scripts', function () use ($type) {

                $jsID = 'product-type-sync';
                $code = sprintf(
                        '// <![CDATA[ %1$s'
                        . ' addLoadEvent(function () { %1$s'
                        . '  jQuery("#product-type option")'
                        . '     .removeAttr("selected");%1$s'
                        . '  jQuery("#product-type option[value=\"%2$s\"]")'
                        . '         .attr("selected", "selected");%1$s'
                        . '})'
                        . '// ]]>'
                        , PHP_EOL
                        , $type[0]
                );

                Utilities::jsScriptWrapper($jsID, $code);
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
                    , \Hyyan\WPI\Plugin::getVersion()
                    , false
            );
        }, 100);
    }

}
