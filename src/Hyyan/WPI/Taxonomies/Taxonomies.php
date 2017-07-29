<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Taxonomies;

use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\Features;

/**
 * Taxonomies.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Taxonomies
{
    /**
     * Managed taxonomies.
     *
     * @var array
     */
    protected $managed = array();

    /**
     * Construct object.
     */
    public function __construct()
    {
        /* Just to prepare taxonomies  */
        $this->prepareAndGet();

        /* Manage taxonomies translation */
        add_filter(
                'pll_get_taxonomies', array($this, 'getAllTranslateableTaxonomies'), 10, 2
        );
                
        add_action('update_option_wpi-features', array($this, 'updatePolyLangFromWooPolyFeatures'), 10, 3);

        add_action('update_option_wpi-metas-list', array($this, 'updatePolyLangFromWooPolyMetas'), 10, 3);
    }

    /**
         * All this function needs to do is:
         *   if called requesting all available settings
         *			return all taxonomies enabled in woo-poly
         * This is because Polylang only saves the options which are turned on in Polylang so needs to
         * be told about the others.
     *
     * @param array $taxonomies array of cutoms taxonomies managed by polylang
          * @param bool  $is_settings true when displaying the list of custom taxonomies in Polylang settings
     *
     * @return array
     */
    public function getAllTranslateableTaxonomies($taxonomies, $is_settings)
    {
        //if not called to get all settings, simply return the input
        if (!($is_settings)) {
            return $taxonomies;
        }

        //otherwise, called by Polylang Settings, return translatable taxonomies
        $add = array();
        $tax_types = array(
            'attributes' => 'Hyyan\WPI\Taxonomies\Attributes',
            'categories' => 'Hyyan\WPI\Taxonomies\Categories',
            'tags' => 'Hyyan\WPI\Taxonomies\Tags',
            'shipping-class' => 'Hyyan\WPI\Taxonomies\ShippingCalss',
        );

        //for each type, add it
        foreach ($tax_types as $tax_type => $class) {
            $names = $class::getNames();
            if ('on' === Settings::getOption($tax_type, Features::getID(), 'on')) {
                $add = array_merge($add, $names);
            }
        }
                
        return array_merge($taxonomies, $add);
    }

        
        
    /**
         * Hook to allow some customization when WooPoly Settings are saved,
         * for example some settings should be updated in Polylang Settings
         * [we could also catch some mutually incompatible woopoly settings,
         *  by hooking pre_update_option_wpi-metas-list]
     *
     * @param array $old_value   previous WooPoly settings
     * @param array $new_value   new WooPoly settings
          * @param string $option		 option name
     *
     * @return array
     */
    public function updatePolyLangFromWooPolyMetas($old_value, $new_value, $option)
    {
        //we could update Polylang settings for Featured Image, Comment Status, Page Order
        //if the WooPoly settings have changed, but note this would also affect Posts
        return true;
    }

    /**
         * When WooPoly settings are saved, we should try to update the related Polylang Settings
     *
     * @param array $old_value   previous WooPoly settings
     * @param array $new_value   new WooPoly settings
          * @param string $option		option name
     *
     * @return array
     */
    public function updatePolyLangFromWooPolyFeatures($old_value, $new_value, $option)
    {
        $polylang_options = get_option('polylang');
        $polylang_taxs = $polylang_options['taxonomies'];
        $update=false;

        //check Polylang is in sync for Product category and tag translation
        if ((isset($new_value['categories'])) && ($new_value['categories']=='on')) {
            if (! in_array('product_cat', $polylang_taxs)) {
                $polylang_options['taxonomies'][] = 'product_cat';
                $update=true;
            }
        } else {
            $key = array_search('product_cat', $polylang_taxs);
            if ($key!==false) {  //key may be zero which is different from false
                unset($polylang_options['taxonomies'][$key]);
                $update=true;
            }
        }
        if ((isset($new_value['tags'])) && ($new_value['tags']=='on')) {
            if (! in_array('product_tag', $polylang_taxs)) {
                $polylang_options['taxonomies'][] = 'product_tag';
                $update=true;
            }
        } else {
            $key = array_search('product_tag', $polylang_taxs);
            if ($key!==false) {
                unset($polylang_options['taxonomies'][$key]);
                $update=true;
            }
        }

        //for attributes don't force on for all attributes but do force off when disabled
        if (isset($old_value['attributes']) && isset($new_value['attributes'])) {
            $old_attr_sync = $old_value['attributes'];
            $new_attr_sync = $new_value['attributes'];
            if ($old_attr_sync != $new_attr_sync) {
                //if we are just turning the attributes on, old behaviour is to force add to translation
                //now we will not force translation on, only force off, ie:
                //  remove from Polylang if disabling translation
                if ($new_attr_sync!='on') {
                    $remove = Attributes::getNames();
                    foreach ($remove as $tax) {
                        if (in_array($tax, $polylang_taxs)) {
                            $polylang_options['taxonomies'] = array_flip($polylang_options['taxonomies']);
                            unset($polylang_options['taxonomies'][$tax]);
                            $polylang_options['taxonomies'] = array_flip($polylang_options['taxonomies']);
                            $update = true;
                        } //if Product Attribute was previously translated
                    } //for each Product Attribute
                } //if wooPoly Translate Product Attributes is turned On
            } //if attributes setting has changed
        } //if attributes are set
        if ($update) {
            update_option('polylang', $polylang_options);
        }
    }
    
    
    /**
     * Get managed taxonomies.
     *
     * @return array taxonomies that must be added and removed to polylang
     */
    protected function prepareAndGet()
    {
        $add = array();
        $remove = array();
        $supported = array(
            'attributes' => 'Hyyan\WPI\Taxonomies\Attributes',
            'categories' => 'Hyyan\WPI\Taxonomies\Categories',
            'tags' => 'Hyyan\WPI\Taxonomies\Tags',
            //'shipping-class' => 'Hyyan\WPI\Taxonomies\ShippingCalss',
        );

        foreach ($supported as $option => $class) {
            $names = $class::getNames();
            if ('on' === Settings::getOption($option, Features::getID(), 'on')) {
                $add = array_merge($add, $names);
                if (!isset($this->managed[$class])) {
                    $this->managed[$class] = new $class();
                }
            } else {
                $remove = array_merge($remove, $names);
            }
        }

        return array($add, $remove);
    }
}
