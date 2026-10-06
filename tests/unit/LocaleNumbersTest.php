<?php
use Hyyan\WPI\LocaleNumbers;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
final class LocaleNumbersTest extends TestCase
{
    #[DataProvider('decimalCases')]
    public function testFrontEndFormattingPreservesEmptyValuesAndFormatsNumbers($input, $expected): void
    {
        $GLOBALS['woopoly_test_admin'] = false;
        $GLOBALS['woopoly_test_locale'] = 'de_DE';
        $_REQUEST = array();
        $formatter = (new ReflectionClass(LocaleNumbers::class))->newInstanceWithoutConstructor();
        self::assertSame($expected, $formatter->getLocalizedDecimal('fallback', $input));
    }
    public static function decimalCases(): array
    {
        return array(
            'empty dimension' => array('', 'fallback'),
            'null dimension' => array(null, 'fallback'),
            'non numeric extension value' => array('n/a', 'fallback'),
            'numeric string' => array('1234.5', '1.234,5'),
            'zero string' => array('0', '0'),
            'negative decimal' => array('-12.5', '-12,5'),
            'numeric float' => array(12.5, '12,5'),
        );
    }
    public function testAdminValueRemainsWooCommerceFormatted(): void
    {
        $GLOBALS['woopoly_test_admin'] = true;
        $_REQUEST = array();
        $formatter = (new ReflectionClass(LocaleNumbers::class))->newInstanceWithoutConstructor();
        self::assertSame('12.50', $formatter->getLocalizedDecimal('12.50', '12.5'));
    }
}
