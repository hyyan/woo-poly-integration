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

            if (isset($_GET[$name]) && !is_string($_GET[$name])) {
                unset($_GET[$name]);
                continue;
            }
            if (empty($_GET[$name]) || !taxonomy_exists($taxonomy)) {
                continue;
            }

            $terms = explode(',', wc_clean(wp_unslash($_GET[$name])));
            $termsTranslations = array();

            foreach ($terms as $value) {
                if (ctype_digit($value)) {
                    $ID = absint($value);
                    $translation = pll_get_term($ID);
                    $termsTranslations[] = $translation ? absint($translation) : $ID;
                } else {
                    $slug = sanitize_title($value);
                    $matches = get_terms(array('taxonomy' => $taxonomy, 'slug' => $slug, 'hide_empty' => false, 'number' => 1, 'lang' => ''));
                    $term = !is_wp_error($matches) && $matches ? reset($matches) : false;
                    $translation = $term ? pll_get_term($term->term_id) : false;
                    $translated = $translation ? get_term($translation, $taxonomy) : false;
                    $termsTranslations[] = $translated && !is_wp_error($translated) ? $translated->slug : $slug;
                }
            }

            $_GET[$name] = implode(',', $termsTranslations);
            $_chosen_attributes[$taxonomy]['terms'] = $termsTranslations;
        }
    }
}
