<?php
use Hyyan\WPI\LocaleNumbers;
use PHPUnit\Framework\TestCase;

final class MissingLanguageLocaleTest extends TestCase
{
    protected function tearDown(): void
    {
        $GLOBALS['woopoly_test_locale'] = 'en_US';
        $GLOBALS['woopoly_test_wp_locale'] = 'en_US';
        $GLOBALS['woopoly_test_admin'] = false;
    }

    public function testMissingPolylangLanguageUsesWordPressLocaleForCurrencyAndNumbers(): void
    {
        $GLOBALS['woopoly_test_admin'] = false;
        $GLOBALS['woopoly_test_locale'] = false;
        $_REQUEST = array();
        $formatter = (new ReflectionClass(LocaleNumbers::class))->newInstanceWithoutConstructor();
        foreach (array('en_US' => array('.', ',', '1,234.5'), 'de_DE' => array(',', '.', '1.234,5')) as $locale => $expected) {
            $GLOBALS['woopoly_test_wp_locale'] = $locale;
            $args = $formatter->filterPriceArgs(array(
                'currency' => 'EUR', 'decimal_separator' => '.', 'thousand_separator' => ',',
                'decimals' => 2, 'price_format' => '%1$s%2$s', 'ex_tax_label' => true));
            self::assertSame($expected[0], $args['decimal_separator']);
            self::assertSame($expected[1], $args['thousand_separator']);
            self::assertSame(2, $args['decimals']);
            self::assertTrue($args['ex_tax_label']);
            self::assertSame($expected[0], $formatter->getLocaleDecimalSeparator('fallback'));
            self::assertSame($expected[1], $formatter->getLocaleThousandSeparator('fallback'));
            self::assertSame($expected[2], $formatter->getLocalizedDecimal('fallback', '1234.5'));
        }
    }
}
