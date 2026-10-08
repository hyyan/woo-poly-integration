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
        add_filter('query_vars', array($this, 'getQueryVars'));
        add_filter('request', array($this, 'layeredNavInit'));
    }

    /**
     * Register public attribute filters.
     *
     * @param array $variables public query variables
     *
     * @return array
     */
    public function getQueryVars(array $variables)
    {
        foreach (wc_get_attribute_taxonomies() as $tax) {
            $variables[] = 'filter_' . wc_sanitize_taxonomy_name($tax->attribute_name);
        }
        return $variables;
    }

    /**
     * Translate public attribute filters.
     *
     * @param array $query parsed public query variables
     *
     * @return array
     */
    public function layeredNavInit(array $query)
    {
        if (
                !(is_active_widget(false, false, 'woocommerce_layered_nav', true) &&
                !is_admin())
        ) {
            return $query;
        }

        $attributes = wc_get_attribute_taxonomies();
        foreach ($attributes as $tax) {
            $attribute = wc_sanitize_taxonomy_name($tax->attribute_name);
            $taxonomy = wc_attribute_taxonomy_name($attribute);
            $name = 'filter_'.$attribute;

            if (isset($query[$name]) && !is_string($query[$name])) {
                unset($query[$name], $_GET[$name]);
                continue;
            }
            if (empty($query[$name]) || !taxonomy_exists($taxonomy)) {
                continue;
            }

            $terms = explode(',', sanitize_text_field(wp_unslash($query[$name])));
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

            $query[$name] = implode(',', $termsTranslations);
            $_GET[$name] = $query[$name];
        }
        return $query;
    }
}
