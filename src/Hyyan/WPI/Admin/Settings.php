<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <tiribthea4hyyan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Admin;

use Hyyan\WPI\Product\Meta,
    Hyyan\WPI\HooksInterface;

/**
 * Settings
 *
 * Admin settings page
 *
 * @author Hyyan
 */
class Settings extends \WeDevs_Settings_API
{

    /**
     * Construct object
     */
    public function __construct()
    {
        parent::__construct();
        add_action('admin_init', array($this, 'init'));
        add_action('admin_menu', array($this, 'registerMenu'));
    }

    /**
     * Initialize settings
     */
    public function init()
    {
        /* Set the settings */
        $this->set_sections($this->getSections());
        $this->set_fields($this->getFields());

        /* Initialize settings */
        $this->admin_init();
    }

    /**
     * Add plugin menu
     */
    public function registerMenu()
    {
        add_options_page(
                __('Hyyan WooCommerce Polylang Integration', 'woo-poly-integration')
                , __('WooPoly', 'woo-poly-integration')
                , 'delete_posts'
                , 'hyyan-wpi'
                , array($this, 'outputPage')
        );
    }

    /**
     * Get sections
     *
     * Get setting sections array to register
     *
     * @return array
     */
    public function getSections()
    {
        return apply_filters(HooksInterface::SETTINGS_SECTIONS_FILTER, array(
            'features' => array(
                'id' => 'wpi-features',
                'title' => __('Features', 'woo-poly-integration'),
                'desc' => __(
                        ' The section will allow you to Enable/Disable
                          Plugin Features.'
                        , 'woo-poly-integration'
                )
            ),
            'meta-list' => array(
                'id' => 'wpi-metas-list',
                'title' => __('Metas List', 'woo-poly-integration'),
                'desc' => __(
                        'The section will allow controll which metas should be
                         synced between product and its translation , please ignore
                         this section if you do not understand what this is mean.
                        '
                        , 'woo-poly-integration'
                )
            )
        ));
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    public function getFields()
    {

        $fields = array();

        /* Metas list */
        $metas = Meta::getProductMetaToCopy(array(), false);
        $fields['wpi-metas-list'] = array();
        foreach ($metas as $ID => $value) {

            $fields['wpi-metas-list'][] = array(
                'name' => $ID,
                'label' => $value['name'],
                'desc' => $value['desc'],
                'type' => 'multicheck',
                'default' => array_combine($value['metas'], $value['metas']),
                'options' => array_combine(
                        $value['metas']
                        , array_map(array(__CLASS__, 'normalize'), $value['metas'])
                )
            );
        }

        /* Features */
        $fields['wpi-features'] = array(
            array(
                'name' => 'fields-locker',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Fields Locker', 'woo-poly-integration'),
                'desc' => __(
                        'Fields locker makes it easy for user to know which
                         field to translate and which to ignore '
                        , 'woo-poly-integration'
                )
            ),
            array(
                'name' => 'emails',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Emails', 'woo-poly-integration'),
                'desc' => __(
                        'Check to use order language whenever woocommerce sends
                         order emails'
                        , 'woo-poly-integration'
                )
            ),
            array(
                'name' => 'reports',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Reports', 'woo-poly-integration'),
                'desc' => __(
                        'Check to enable reports langauge filtering and
                         combining'
                        , 'woo-poly-integration'
                )
            ),
            array(
                'name' => 'coupons',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Coupons Sync', 'woo-poly-integration'),
                'desc' => __(
                        'Check to apply coupons rules for product and its
                         translations'
                        , 'woo-poly-integration'
                )
            ),
            array(
                'name' => 'stock',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Stock Sync', 'woo-poly-integration'),
                'desc' => __(
                        'Check to sync stock for product and its translations'
                        , 'woo-poly-integration'
                )
            ),
            array(
                'name' => 'categories',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Translate Categories', 'woo-poly-integration'),
                'desc' => __(
                        'Check this option to enable categories translations'
                        , 'woo-poly-integration'
                )
            ),
            array(
                'name' => 'tags',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Translate Tags', 'woo-poly-integration'),
                'desc' => __(
                        'Check this option to enable tags translations'
                        , 'woo-poly-integration'
                )
            ),
            array(
                'name' => 'attributes',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Translate Attributes', 'woo-poly-integration'),
                'desc' => __(
                        'Check this option to enable Attributes translations'
                        , 'woo-poly-integration'
                )
            ),
            array(
                'name' => 'shipping-class',
                'type' => 'checkbox',
                'default' => 'off',
                'label' => __('Translate ShippingClass', 'woo-poly-integration'),
                'desc' => __(
                        'Check this option to enable ShippingClass translations'
                        , 'woo-poly-integration'
                )
            )
        );

        return apply_filters(HooksInterface::SETTINGS_SECTIONS_FIELDS, $fields);
    }

    /**
     * Output page content
     */
    public function outputPage()
    {
        include __DIR__ . '/View.php';
    }

    /**
     * Get the value of a settings field
     *
     * @param string $option  settings field name
     * @param string $section the section name this field belongs to
     * @param string $default default text if it's not found
     *
     * @return mixed
     */
    public static function getOption($option, $section, $default = '')
    {
        $options = get_option($section);

        if (isset($options[$option])) {
            return $options[$option];
        }

        return $default;
    }

    /**
     * Normalize string by removing "_" from string
     *
     * @param string $string
     *
     * @return string
     */
    public static function normalize($string)
    {
        return ucwords(str_replace('_', ' ', $string));
    }

}
