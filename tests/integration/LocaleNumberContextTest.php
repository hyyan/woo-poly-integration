<?php
use Hyyan\WPI\LocaleNumbers;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class LocaleNumberContextTest extends TestCase
{
    #[DataProvider('priceRequests')]
    public function testFormattingSelectorPreservesNativeAndLegacyAjaxResults(array $request, string $decimal, string $number): void
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
        return array(array(array(),'.','1234.5'), array(array('get_product_price_by_ajax'=>1),',','1.234,5'), array(array('get_product_price_by_ajax'=>array('legacy')),',','1.234,5'));
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
