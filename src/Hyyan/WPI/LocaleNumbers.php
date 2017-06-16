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
        //add_filter('woocommerce_format_localized_price', array($this, 'getLocalizedPrice'), 10, 2);
        
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
        //default to return unmodified wooCommerce value
        $retval = $wooFormattedValue;
        
        //don't touch values on admin screens, save as plain number using woo defaults
        if (! is_admin()){
            $a = new \NumberFormatter(pll_current_language('locale'), \NumberFormatter::DECIMAL);
            if ($a){
                $retval = $a->format($input, \NumberFormatter::TYPE_DOUBLE);
            }
        }
        return $retval;
    }
    
    /*
     * get localized price string 
     * Actually to return as a fully localized currency string we need to now the currency 
     * as well as the locale, for example:
     * 
     * $amount = '12345.67';
     * 
     * $formatter = new NumberFormatter('en_GB',  NumberFormatter::CURRENCY);
     * echo 'UK: ' . $formatter->formatCurrency($amount, 'EUR') . PHP_EOL;
     * 
     * $formatter = new NumberFormatter('de_DE',  NumberFormatter::CURRENCY);
     * echo 'DE: ' . $formatter->formatCurrency($amount, 'EUR') . PHP_EOL;
     * 
     * The output of the code above is:
     * 
     * UK: €12,345.68
     * DE: 12.345,68 €
     * and for France would be:
     * FR: 12 345,68 €
     * 
     * In this case we don't know that so we could either stick to decimal
     * or just unhook this function
     * 
     * 
     * @param string    default WooCommerce formatting of value
     * @param string    $input value to be formatted
     *
     * @return string  formatted number
     */
    public function getLocalizedPrice($wooFormattedValue, $input)
    {
        //default to return unmodified wooCommerce value
        $retval = $wooFormattedValue;
        
        //don't touch values on admin screens, save as plain number using woo defaults
        if (! is_admin()){
            //$a = new \NumberFormatter(pll_current_language('locale'), \NumberFormatter::CURRENCY);
            $a = new \NumberFormatter(pll_current_language('locale'), \NumberFormatter::DECIMAL_ALWAYS_SHOWN);
            if ($a){
                $retval = $a->format($input);
            }
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
        //don't touch values on admin screens, save as plain number using woo defaults
        if (! is_admin()){
            $locale = pll_current_language('locale');
            $a = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
            if ($a){
                $locale_result = $a->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);
                if ($locale_result){
                    $retval = $locale_result;
                }
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
        //don't touch values on admin screens, save as plain number using woo defaults
        if (! is_admin()){
            $a = new \NumberFormatter(pll_current_language('locale'), \NumberFormatter::DECIMAL);
            if ($a){
                $retval = $a->getSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL);
            }
        }
        return $retval;
    }
    
}