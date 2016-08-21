<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Widgets;

/**
 * LayeredNav.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class LayeredNav
{
    /**
     * Construct object.
     */
    public function __construct()
    {
        add_action('init', array($this, 'layeredNavInit'), 1000);
    }

    /**
     * Layered Nav Init.
     *
     * @global array $_chosen_attributes
     *
     * @return false if not layered nav filter
     */
    public function layeredNavInit()
    {
        if (
                !(is_active_widget(false, false, 'woocommerce_layered_nav', true) &&
                !is_admin())
        ) {
            return false;
        }

        global $_chosen_attributes;

        $attributes = wc_get_attribute_taxonomies();
        foreach ($attributes as $tax) {
            $attribute = wc_sanitize_taxonomy_name($tax->attribute_name);
            $taxonomy = wc_attribute_taxonomy_name($attribute);
            $name = 'filter_'.$attribute;

            if (!(!empty($_GET[$name]) && taxonomy_exists($taxonomy))) {
                continue;
            }

            $terms = explode(',', $_GET[$name]);
            $termsTranslations = array();

            foreach ($terms as $ID) {
                $translation = pll_get_term($ID);
                $termsTranslations [] = $translation ? $translation : $ID;
            }

            $_GET[$name] = implode(',', $termsTranslations);
            $_chosen_attributes[$taxonomy]['terms'] = $termsTranslations;
        }
    }
}
