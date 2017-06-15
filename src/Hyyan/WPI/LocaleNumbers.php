<?php
/**
 * LocaleNumbers.
 *
 * @author Jonathan Moore <jonathan.moore@bcs.org>
 */
namespace Hyyan\WPI;

class LocaleNumbers
{

    /**
     * Construct object.
     */
    public function __construct()
    {
        add_filter('wc_get_price_decimal_separator', array($this, 'getLocaleDecimalSeparator'), 10, 1);
        add_filter('wc_get_price_thousand_separator', array($this, 'getLocaleThousandSeparator'), 10, 1);

        //unreleased woocommerce checkin https://github.com/woocommerce/woocommerce/pull/15628
        add_filter('woocommerce_format_localized_decimal', array($this, 'getLocalizedDecimal'), 10, 2);
        add_filter('woocommerce_format_localized_price', array($this, 'getLocalizedPrice'), 10, 2);
    }

    /*
     * get localized getLocalizedDecimal 
     *
     * @param string    default WooCommerce formatting of value
     * @param string    $input value to be formatted
     *
     * @return string  formatted number
     */
    public function getLocalizedDecimal($wooFormattedValue, $input)
    {
        $retval = $wooFormattedValue;
        $a = new \NumberFormatter(pll_current_language('locale'), \NumberFormatter::DECIMAL);
        if ($a){
            $retval = $a->format($input, \NumberFormatter::TYPE_DOUBLE);
        }
        return $retval;
    }
    
    /*
     * get localized price string 
     *
     * @param string    default WooCommerce formatting of value
     * @param string    $input value to be formatted
     *
     * @return string  formatted number
     */
    public function getLocalizedPrice($wooFormattedValue, $input)
    {
        $retval = $wooFormattedValue;
        $a = new \NumberFormatter(pll_current_language('locale'), \NumberFormatter::CURRENCY);
        if ($a){
            $retval = $a->format($input);
        }
        return $retval;
    }
    
    /*
     * get localized decimal separator 
     *
     * @param string    $input WooCommerce configured value
     *
     * @return string  formatted number
     */
    public function getLocaleDecimalSeparator($separator)
    {
        $retval = $separator;
        $locale = pll_current_language('locale');
        $a = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
        if ($a){
            $locale_result = $a->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);
            if ($locale_result){
                $retval = $locale_result;
            }
        }
        return $retval;
    }
 
        /*
     * get localized thousand separator 
     *
     * @param string    $input WooCommerce configured value
     *
     * @return string  formatted number
     */
    public function getLocaleThousandSeparator($separator)
    {
        $retval = $separator;
        $a = new \NumberFormatter(pll_current_language('locale'), \NumberFormatter::DECIMAL);
        if ($a){
            $retval = $a->getSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL);
        }
        return $retval;
    }
    
}
