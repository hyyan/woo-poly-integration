<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Product;

use Hyyan\WPI\HooksInterface;
use Hyyan\WPI\Utilities;
use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\MetasList;

/**
 * product Meta.
 *
 * Handle product meta sync
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Meta
{
    /**
     * Construct object.
     */
    public function __construct()
    {
        // sync product meta
        add_action(
                'current_screen', array($this, 'syncProductsMeta')
        );
    }

    /**
     * Sync product meta.
     *
     * @return false if the current post type is not "product"
     */
    public function syncProductsMeta()
    {

        // sync product meta with polylang
        add_filter('pll_copy_post_metas', array(__CLASS__, 'getProductMetaToCopy'));
        // Shipping Class translation is not supported after WooCommerce 2.6 but it is
        // still implemented by WooCommerce as a taxonomy. Therefore Polylang will not
        // copy the Shipping Class meta. We need to take care of it.
        if (Utilities::woocommerceVersionCheck('2.6')) { 
            add_action('wp_insert_post', array($this, 'syncShippingClass'), 10, 3);
        }

        $currentScreen = get_current_screen();

        if ($currentScreen->post_type !== 'product') {
            return false;
        }

        $ID = false;
        $disable = false;

        /*
         * Disable editing product meta for translation
         *
         * if the "post" is defined in $_GET then we should check if the current
         * product has a translation and it is the same as the default translation
         * lang defined in polylang then product meta editing must by enabled
         *
         * if the "new_lang" is defined or if the current page is the "edit"
         * page then product meta editing must by disabled
         */

        if (isset($_GET['post'])) {
            $ID = absint($_GET['post']);
            $disable = $ID && (pll_get_post_language($ID) != pll_default_language());
        } elseif (isset($_GET['new_lang']) || $currentScreen->base == 'edit') {
            $disable = isset($_GET['new_lang']) && (esc_attr($_GET['new_lang']) != pll_default_language()) ?
                    true : false;
            $ID = isset($_GET['from_post']) ? absint($_GET['from_post']) : false;
            
            // Add the '_translation_porduct_type' meta, for the case where
            // the product was created before plugin acivation.
            $this->addProductTypeMeta($ID);
        }

        // disable fields edit for translation
        if ($disable) {
            add_action(
                    'admin_print_scripts', array($this, 'addFieldsLocker'), 100
            );
        }

        /* sync selected product type */
        $this->syncSelectedproductType($ID);
    }
    
    
    /**
     * Sync Product Shipping Class.
     * 
     * Shipping Class translation is not supported after WooCommerce 2.6
     * but it is still implemented by WooCommerce as a taxonomy. Therefore,
     * Polylang will not copy the Shipping Class meta.
     *
     * @param int       $post_id    Id of the product being created or edited
     * @param \WP_Post  $post       Post object
     * @param boolean   $update     Whether this is an existing post being updated or not
     */
    public function syncShippingClass($post_id, $post, $update)
    {
        if (in_array('product_shipping_class', $this->getProductMetaToCopy())) {
            // If adding new product translation copy shipping class, otherwise
            // sync all product translations with shipping class of this.
            $copy = isset($_GET['new_lang']) && isset($_GET['from_post']);
            
            if ($copy) {
                // New translation - copy shipping class from product source
                $ID = isset($_GET['from_post']) ? absint($_GET['from_post']) : false;
                $product = wc_get_product($ID);
            } else {
                // Product edit - update shipping class of all product translations
                $product = wc_get_product($post_id);
            }
            
            if ($product) {            
                $shipping_class = $product->get_shipping_class();
                if ($shipping_class){
                    
                    $shipping_terms = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );
                    if ($shipping_terms) {
                        
                        if ($copy) {
                            // New translation - copy shipping class from product source
                            wp_set_post_terms( $post_id, array( $shipping_terms->term_id ), 'product_shipping_class' );
                        } else {
                            // Product edit - update shipping class of all product translations
                            $langs = pll_languages_list();
                            
                            foreach ($langs as $lang) {
                                $translation_id = pll_get_post($post_id, $lang);
                                if ($translation_id != $post_id) {
                                    // Don't sync if is the same product
                                    wp_set_post_terms( $translation_id, array( $shipping_terms->term_id ), 'product_shipping_class' );
                                }
                            }
                        }
                    }
                }
            } 
        }
    }
    
    /**
     * Add product type meta to products created before plugin activation.
     *
     * @param int $ID Id of the product in the default language
     */
    public function addProductTypeMeta($ID)
    {
        if ($ID) {
            $meta = get_post_meta($ID, '_translation_porduct_type');

            if (empty($meta)) {
                $product = wc_get_product($ID);
                if ($product) {
                    update_post_meta($ID, '_translation_porduct_type', $product->product_type);
                }
            }

        }
    }

    /**
     * Define the meta keys that must copyied from orginal product to its
     * translation.
     *
     * @param array $metas array of meta keys
     * @param bool  $flat  false to return meta list with sections (default true)
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
                ),
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
                ),
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
                ),
            ),
            // attributes
            'Attributes' => array(
                'name' => __('Attributes Metas', 'woo-poly-integration'),
                'desc' => __('Attributes Metas', 'woo-poly-integration'),
                'metas' => array(
                    '_product_attributes',
                    '_default_attributes',
                ),
            ),
            // Taxes
            'Taxes' => array(
                'name' => __('Taxes Metas', 'woo-poly-integration'),
                'desc' => __('Taxes Metas', 'woo-poly-integration'),
                'metas' => array(
                    '_tax_status',
                    '_tax_class',
                ),
            ),
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
     * Add the Fields Locker script.
     *
     * The script will disable editing of some product metas for product
     * translation
     *
     * @return bool false if the fields locker feature is disabled
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
                'function hyyan_wpi_lockFields(){'
                .'  var disabled = %s;'
                .'  for (var i = 0; i < disabled.length; i++) {'
                .'      $('
                .'       %s + ","'
                .'       + "." + disabled[i] + ","'
                .'       + "#" +disabled[i] + ","'
                .'      + "*[name^=\'"+disabled[i]+"\']"'
                .'      )'
                .'      .off("click")'
                .'      .on("click", function (e) {e.preventDefault()})'
                .'      .css({'
                .'          opacity: .5,'
                .'          \'pointer-events\': \'none\','
                .'          cursor: \'not-allowed\''
                .'      });'
                .'  }'
                . '};'
                . 'hyyan_wpi_lockFields();'
                . '$(document).ajaxComplete(function(){'
                . '    hyyan_wpi_lockFields(); '
                . '});'
                , json_encode($metas), !empty($selectors) ?
                        json_encode(implode(',', $selectors)) :
                        array(rand())
        );

        Utilities::jsScriptWrapper($jsID, $code);
    }

    /**
     * Sync the product select list.
     *
     * @param int $ID product type
     */
    protected function syncSelectedproductType($ID = null)
    {
        /*
         * First we add save_post action to save the product type
         * as post meta
         *
         * This is step is important so we can get the right product type
         */
        add_action('save_post', function ($_ID) {
            $product = wc_get_product($_ID);    // get_product soft deprecated for wc_get_product
            if ($product && !isset($_GET['from_post'])) {
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
                        .' addLoadEvent(function () { %1$s'
                        .'  jQuery("#product-type option")'
                        .'     .removeAttr("selected");%1$s'
                        .'  jQuery("#product-type option[value=\"%2$s\"]")'
                        .'         .attr("selected", "selected");%1$s'
                        .'})'
                        .'// ]]>', PHP_EOL, $type[0]
                );

                Utilities::jsScriptWrapper($jsID, $code, false);
            }, 11);
        }
    }
}
