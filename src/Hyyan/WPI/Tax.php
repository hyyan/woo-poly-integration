<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI;

use Hyyan\WPI\Utilities;

/**
 * Tax.
 *
 * @author Jonathan Moore <jonathan.moore@bcs.org>
 */
class Tax
{

    /**
     * Construct object.
     */
    public function __construct()
    {
        add_filter('woocommerce_get_price_suffix', array($this, 'filterPriceSuffix'), 10, 4);
        $this->registerTaxStringsForTranslation();
    }

    /**
     * Register woocommerce email subjects and headings in polylang strings
     * translations table.
     */
    public function registerTaxStringsForTranslation()
    {
        if (wc_tax_enabled() && 'taxable') {
            $suffix = get_option('woocommerce_price_display_suffix');
            if ($suffix) {
                $this->registerTaxString('woocommerce_price_display_suffix', $suffix);
            }
        }
    }

    /**
     * Register common strings for all wooCommerce taxes for translation in Polylang
     * Strings Translations table.
     *
     * @param string $setting    Option to save
     * @param string $sufix      Additional string variation, e.g. invoice paid vs invoice
     */
    public function registerTaxString($setting, $default = '')
    {
        if (function_exists('pll_register_string')) {
            $value = get_option($setting);
            if (!($value)) {
                $value = $default;
            }
            if ($value) {
                pll_register_string($setting, $value, __('Woocommerce Taxes', 'woo-poly-integration'));
            }
        }
    }

    /* translate string
     *
     * @param string   $subject Email subject in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated subject
     */

    public function translateTaxString($tax_string)
    {
        if (function_exists('pll_register_string')) {
            $lang  = pll_current_language('locale');
            $trans = pll__($tax_string);
            if ($trans) {
                return $trans;
            } else {
                return $tax_string;
            }
        }
    }

    /**
     * hook for woocommerce_get_price_suffix to get translated price string.
     * note for variable products this is skipped and instead calls get_price_html
     * which has filter woocommerce_get_price_html
     *
     * @param string $html default price html provided by wooCommerce
     * @param WC_Product $instance current product
     *
     * @return array
     */
    public function filterPriceSuffix($html, $instance, $price, $qty)
    {
        $html = '';

        if (($suffix = get_option('woocommerce_price_display_suffix')) && wc_tax_enabled() && 'taxable' === $instance->get_tax_status()) {

            //the rest of this function is the same as the wooCommerce code, here just translating the suffix
            //before expanding any suffix parameters
            $suffix = $this->translateTaxString($suffix);

            if ('' === $price) {
                $price = $instance->get_price();
            }
            $replacements = array(
                '{price_including_tax}' => wc_price(wc_get_price_including_tax($instance, array('qty' => $qty, 'price' => $price))),
                '{price_excluding_tax}' => wc_price(wc_get_price_excluding_tax($instance, array('qty' => $qty, 'price' => $price))),
            );
            $html         = str_replace(array_keys($replacements), array_values($replacements), ' <small class="woocommerce-price-suffix">' . wp_kses_post($suffix) . '</small>');
        }

        return $html;
    }
}
