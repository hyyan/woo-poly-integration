<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI;

use Hyyan\WPI\Tools\FlashMessages;

/**
 * Endpoints.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Endpoints
{
    /**
     * Array of current found endpoints.
     *
     * @var array
     */
    protected $endpoints = array();

    /**
     * Construct object.
     */
    public function __construct()
    {

        /* Register endpoints to translate as polulang strings */
        $this->regsiterEndpointsTranslations();

        add_action(
                'init', array($this, 'rewriteEndpoints'), 11
        );
        add_action(
                'woocommerce_update_options', array($this, 'addEndpoints')
        );
        add_filter(
                'pre_update_option_rewrite_rules', array($this, 'updateRules'), 100, 2
        );
        add_filter(
                'pll_the_language_link', array($this, 'correctPolylangSwitcherLinks'), 10, 2
        );
        add_filter(
                'wp_get_nav_menu_items', array($this, 'fixMyAccountLinkInMenus')
        );
        add_action(
                'current_screen', array($this, 'showFlashMessages')
        );
    }



    /**
     * Rewrite endpoints.
     *
     * Add all endpoints to polylang strings table
     */
    public function rewriteEndpoints()
    {
        $this->addEndpoints();
        //flush_rewrite_rules();
    }

    /**
     * Register endpoints translations.
     *
     * Find all woocomerce endpoints and register them with polylang to be
     * translated as polylang strings
     *
     * @global \Polylang $polylang
     * @global \WooCommerce $woocommerce
     *
     * @return false if missing polylang or woocommerce
     */
    public function regsiterEndpointsTranslations()
    {
        global $polylang, $woocommerce;
        if (!$polylang || !$woocommerce) {
            return false;
        }

        $vars = WC()->query->get_query_vars();
        foreach ($vars as $key => $value) {
            WC()->query->query_vars[$key] = $this->getEndpointTranslation($value);
        }
    }

    /**
     * Get endpoint translations.
     *
     * Register endpoint as polylang string if not registered and returne the
     * endpoint translation for the current langauge
     *
     * @global \Polylang $polylang
     *
     * @param string $endpoint the endpoint name
     *
     * @return string endpoint translation
     */
    public function getEndpointTranslation($endpoint)
    {
        pll_register_string(
                $endpoint, $endpoint, static::getPolylangStringSection()
        );

        $this->endpoints [] = $endpoint;

        return pll__($endpoint);
    }

    /**
     * Update Rules.
     *
     * Update the endpoint rule with new value and flush the rewrite rules
     *
     * @param string $value endpoint name
     *
     * @return string endpoint name
     */
    public function updateRules($value)
    {
        remove_filter(
                'pre_update_option_rewrite_rules', array($this, __FUNCTION__), 100, 2
        );
        $this->addEndpoints();
        flush_rewrite_rules();

        return $value;
    }

    /**
     * Add endpoints.
     *
     * Add all endpoints translation in the current langauge
     */
    public function addEndpoints()
    {
        $langs = pll_languages_list();
        foreach ($this->endpoints as $endpoint) {
            foreach ($langs as $lang) {
                add_rewrite_endpoint(pll_translate_string($endpoint, $lang), EP_ROOT | EP_PAGES);
            }
        }
    }

    /**
     * Get Endpoint Url.
     *
     * Rebuild permalink with corrent endpoint translation
     *
     * @param string $endpoint  endpoint name
     * @param string $value
     * @param string $permalink orginal permalink
     *
     * @return string final permalink
     */
    public function rebuildUrl($endpoint, $value = '', $permalink = '')
    {
        if (get_option('permalink_structure')) {
            if (strstr($permalink, '?')) {
                $query_string = '?'.parse_url($permalink, PHP_URL_QUERY);
                $permalink = current(explode('?', $permalink));
            } else {
                $query_string = '';
            }
            $url = trailingslashit($permalink)
                    .$endpoint
                    .'/'
                    .$query_string;
        } else {
            $url = add_query_arg($endpoint, $value, $permalink);
        }

        return $url;
    }

    /**
     * Correct Polylang Switcher Links.
     *
     * Add the correct endpoint translations for polylang switcher links
     *
     * @global \WP $wp
     *
     * @param string $link link
     * @param string $slug langauge
     *
     * @return string final link
     */
    public function correctPolylangSwitcherLinks($link, $slug)
    {
        global $wp;
        $endpoints = WC()->query->get_query_vars();
        foreach ($endpoints as $key => $value) {
            if (isset($wp->query_vars[$key])) {
                $link = str_replace(
                        $value, pll_translate_string($key, $slug), $link
                );
                break;
            }
        }

        return $link;
    }

    /**
     * Fix My Account Link In Menus.
     *
     * The method will remove endpoints from my account page link in wp menus
     *
     * @global \Polylang $polylang
     *
     * @param array $items menu items
     *
     * @return array menu items
     *
     * @todo Find a better solution
     */
    public function fixMyAccountLinkInMenus(array $items = array())
    {
        global $polylang;
        $translations = PLL()->model->post->get_translations(
                wc_get_page_id('myaccount')
        );

        foreach ($items as $item) {
            if (in_array($item->object_id, $translations)) {
                $vars = WC()->query->get_query_vars();
                foreach ($vars as $key => $value) {
                    if ($value && false !== ($pos = strpos($item->url, $value))) {
                        $item->url = substr($item->url, 0, $pos);
                    }
                }
            }
        }

        return $items;
    }

    /**
     * Show flash messages.
     *
     * Show endpoints flash messages in defined screens only
     */
    public function showFlashMessages()
    {
        $screen = get_current_screen();
        $allowedPages = array(
            'edit-shop_order',
            'woocommerce_page_wc-settings',
            'settings_page_mlang',
            'hyyan-wpi',
        );
        if (in_array($screen->id, $allowedPages)) {
            FlashMessages::add(
                    MessagesInterface::MSG_ENDPOINTS_TRANSLATION, Plugin::getView('Messages/endpointsTranslations')
            );
        }
    }

    /**
     * Get polylang StringSection.
     *
     * @return string section name
     */
    public static function getPolylangStringSection()
    {
        return __('Woocommerce Endpoints', 'woo-poly-integration');
    }
}
