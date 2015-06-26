<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <tiribthea4hyyan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Product;

use Hyyan\WPI\HooksInterface,
    Hyyan\WPI\Utilities,
    Hyyan\WPI\Admin\Settings,
    Hyyan\WPI\Admin\MetasList;

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
        add_filter('pll_copy_post_metas', array(__CLASS__, 'getProductMetaToCopy'));

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
            add_action(
                    'admin_print_scripts'
                    , array($this, 'addFieldsLocker')
                    , 100
            );
        }

        /* sync selected prodcut type */
        $this->syncSelectedProdcutType($ID);
    }

    /**
     * Define the meta keys that must copyied from orginal product to its
     * translation
     *
     * @param array   $metas array of meta keys
     * @param boolean $flat  false to return meta list with sections (default true)
     *
     * @return array extended meta keys array
     */
    public static function getProductMetaToCopy(array $metas = array(), $flat = true)
    {

        $default = apply_filters(HooksInterface::PRODUCT_META_SYNC_FILTER, array(
            // general
            'general' => array(
                'name' => __('General Metas', 'woo-poly-integration'),
                'desc' => __('General Metas', 'woo-poly-integration'),
                'metas' => array(
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
                    'menu_order',
                    'comment_status',
                    '_upsell_ids',
                    '_crosssell_ids',
                    '_featured',
                    '_thumbnail_id',
                    '_price',
                    '_product_image_gallery',
                    'total_sales',
                    '_translation_porduct_type',
                    '_visibility',
                )
            ),
            // stock
            'stock' => array(
                'name' => __('Stock Metas', 'woo-poly-integration'),
                'desc' => __('Stock Metas', 'woo-poly-integration'),
                'metas' => array(
                    '_manage_stock',
                    '_stock',
                    '_backorders',
                    '_stock_status',
                    '_sold_individually',
                )
            ),
            // shipping
            'shipping' => array(
                'name' => __('ShippingClass Metas', 'woo-poly-integration'),
                'desc' => __('ShippingClass Metas', 'woo-poly-integration'),
                'metas' => array(
                    '_weight',
                    '_length',
                    '_width',
                    '_height',
                    'product_shipping_class',
                )
            ),
            // attributes
            'Attributes' => array(
                'name' => __('Attributes Metas', 'woo-poly-integration'),
                'desc' => __('Attributes Metas', 'woo-poly-integration'),
                'metas' => array(
                    '_product_attributes',
                    '_default_attributes',
                ),
            )
        ));

        if (false === $flat) {
            return $default;
        }

        foreach ($default as $ID => $value) {
            $metas = array_merge($metas, Settings::getOption(
                            $ID, MetasList::getID(), $value['metas']
            ));
        }

        return array_values($metas);
    }

    /**
     * Add the Fields Locker script
     *
     * The script will disable editing of some porduct metas for product
     * translation
     *
     * @return boolean false if the fields locker feature is disabled
     */
    public function addFieldsLocker()
    {

        if ('off' === Settings::getOption('fields-locker', \Hyyan\WPI\Admin\Features::getID(), 'on')) {
            return false;
        }

        $metas = static::getProductMetaToCopy();
        $selectors = apply_filters(HooksInterface::FIELDS_LOCKER_SELECTORS_FILTER, array(
            '.insert',
            in_array('_product_attributes', $metas) ? '#product_attributes :input' : rand(),
        ));

        $jsID = 'product-fields-locker';
        $code = sprintf(
                'var disabled = %s;'
                . 'for (var i = 0; i < disabled.length; i++) {'
                . ' $('
                . '     %s + ","'
                . '     + "." + disabled[i] + ","'
                . '     + "#" +disabled[i] + ","'
                . '     + "*[name^=\'"+disabled[i]+"\']"'
                . ' )'
                . '     .off("click")'
                . '     .on("click", function (e) {e.preventDefault()})'
                . '     .css({'
                . '         opacity: .5,'
                . '         \'pointer-events\': \'none\','
                . '         cursor: \'not-allowed\''
                . '     }'
                . ' );'
                . '}'
                , json_encode($metas)
                , !empty($selectors) ?
                        json_encode(implode(',', $selectors)) :
                        array(rand())
        );

        Utilities::jsScriptWrapper($jsID, $code);
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

                Utilities::jsScriptWrapper($jsID, $code, false);
            }, 11);
        }
    }

}
