<?php
use Hyyan\WPI\LocaleNumbers;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class LocaleNumberContextTest extends TestCase
{
    #[DataProvider('priceRequests')]
    public function testUntrustedMarkerCannotChangeAdminFormatting(array $request, string $decimal, string $number): void
    {
        $original = $_REQUEST; $language = PLL()->curlang;
        try {
            $_REQUEST = $request; PLL()->curlang = PLL()->model->get_language('de');
            $formatter = (new ReflectionClass(LocaleNumbers::class))->newInstanceWithoutConstructor();
            self::assertSame($decimal, $formatter->getLocaleDecimalSeparator('.'));
            self::assertSame($number, $formatter->getLocalizedDecimal('1234.5',1234.5));
        } finally { $_REQUEST = $original; PLL()->curlang = $language; }
    }
    public static function priceRequests(): array
    {
        return array(array(array(),'.','1234.5'), array(array('get_product_price_by_ajax'=>1),'.','1234.5'), array(array('get_product_price_by_ajax'=>array('legacy')),'.','1234.5'));
    }
    #[DataProvider('priceHooks')]
    public function testNativePriceCallbackRetainsLocalizedDisplay(string $hook): void
    {
        $request = $_REQUEST;
        $language = PLL()->curlang;
        $results = array();
        $callback = static function () use (&$results) {
            $formatter = (new ReflectionClass(LocaleNumbers::class))->newInstanceWithoutConstructor();
            $results = array($formatter->getLocaleDecimalSeparator('.'), $formatter->getLocalizedDecimal('1234.5', 1234.5));
        };
        add_action($hook, $callback);
        try {
            $_REQUEST = array();
            PLL()->curlang = PLL()->model->get_language('de');
            do_action($hook);
            self::assertSame(array(',', '1.234,5'), $results);
        } finally {
            remove_action($hook, $callback);
            $_REQUEST = $request;
            PLL()->curlang = $language;
        }
    }

    public static function priceHooks(): array
    {
        $cases = array();
        foreach (array('woocs_get_products_price_html', 'woocs_get_variation_products_price_html', 'woocs_get_custom_price_html') as $action) {
            foreach (array('wp_ajax_', 'wp_ajax_nopriv_') as $prefix) {
                $cases[$prefix . $action] = array($prefix . $action);
            }
        }
        return $cases;
    }

    public function testOtherAjaxCallbackRetainsAdminFormattingDespiteMarker(): void
    {
        $request = $_REQUEST;
        $results = array();
        $callback = static function () use (&$results) {
            $formatter = (new ReflectionClass(LocaleNumbers::class))->newInstanceWithoutConstructor();
            $results = array($formatter->getLocaleDecimalSeparator('.'), $formatter->getLocalizedDecimal('1234.5', 1234.5));
        };
        add_action('wp_ajax_woo_poly_integration_context_fixture', $callback);
        try {
            $_REQUEST = array('get_product_price_by_ajax' => 1);
            do_action('wp_ajax_woo_poly_integration_context_fixture');
            self::assertSame(array('.', '1234.5'), $results);
        } finally {
            remove_action('wp_ajax_woo_poly_integration_context_fixture', $callback);
            $_REQUEST = $request;
        }
    }

    public function testNativeAdminFallbackKeepsDistinctDecimalAndThousandSeparators(): void
    {
        $original = $_REQUEST;
        try {
            $_REQUEST = array();
            $formatter = (new ReflectionClass(LocaleNumbers::class))->newInstanceWithoutConstructor();
            $args = $formatter->filterPriceArgs(array('currency'=>'','decimal_separator'=>'.','thousand_separator'=>','));
            self::assertSame('.', $args['decimal_separator']);
            self::assertSame(',', $args['thousand_separator']);
        } finally { $_REQUEST = $original; }
    }
}
