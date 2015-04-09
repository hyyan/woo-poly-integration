<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <tiribthea4hyyan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI;

/**
 * Attributes
 *
 * Handle attributes translation
 *
 * @author Hyyan
 */
class Attributes
{

    public function __construct()
    {
        // manage attributes translation
        add_filter(
                'pll_get_taxonomies'
                , array($this, 'makePolylangManageAttrsTranslation')
        );

        // manage attributes label translation
        add_action(
                'init'
                , array($this, 'makeAttributeLableTranslateable')
                , 10, 2
        );
        add_filter('woocommerce_attribute_label'
                , array($this, 'translateAttrsLable')
        );
    }

    /**
     * Notifty polylang about every new attributes
     *
     * @param array $types array of cutoms posts managed by polylang
     *
     * @return array
     */
    public function makePolylangManageAttrsTranslation($types)
    {
        $attrs = wc_get_attribute_taxonomy_names();
        $options = get_option('polylang');

        $taxs = $options['taxonomies'];
        $update = false;

        foreach ($attrs as $attr) {
            if (!in_array($attr, $taxs)) {
                $options['taxonomies'][] = $attr;
                $update = true;
            }
        }

        if ($update) {
            update_option('polylang', $options);
        }

        return array_merge($types, $attrs);
    }

    /**
     * Make all attributes labled managed by polylang string translation
     */
    public function makeAttributeLableTranslateable()
    {
        $attrs = wc_get_attribute_taxonomies();
        $section = __('Woocommerce Attributes', 'wpi');
        foreach ($attrs as $attr) {
            pll_register_string(
                    $attr->attribute_label
                    , $attr->attribute_label
                    , $section
            );
        }
    }

    /**
     * Translate the attribute label
     *
     * @param string $label original attribute label
     *
     * @return string translated attribute label
     */
    public function translateAttrsLable($label)
    {
        return pll__($label);
    }

}
