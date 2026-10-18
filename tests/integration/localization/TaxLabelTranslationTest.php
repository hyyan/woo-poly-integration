<?php
use Hyyan\WPI\Utilities;
use PHPUnit\Framework\TestCase;

final class TaxLabelTranslationTest extends TestCase
{
    public function testNativeRateLabelsFollowLanguageWithoutChangingStoredRates(): void
    {
        $previous = PLL()->curlang;
        $strings = $GLOBALS['l10n']['pll_string'] ?? null;
        $id = WC_Tax::_insert_tax_rate(array('tax_rate_country' => 'DE', 'tax_rate_state' => '',
            'tax_rate' => '20', 'tax_rate_name' => 'Tax label regression', 'tax_rate_priority' => 1,
            'tax_rate_compound' => 0, 'tax_rate_shipping' => 0, 'tax_rate_order' => 0, 'tax_rate_class' => ''));
        try {
            foreach (array('de' => 'Steuer Pruefung', 'en' => 'Tax label regression') as $slug => $expected) {
                Utilities::switch_pll_locale($slug === 'de' ? 'de_DE' : 'en_US');
                $mo = new PLL_MO();
                $mo->add_entry(new Translation_Entry(array('singular' => 'Tax label regression', 'translations' => array($expected))));
                $GLOBALS['l10n']['pll_string'] = $mo;
                self::assertSame($expected, WC_Tax::get_rate_label($id));
                $rates = WC_Tax::get_rates_for_tax_class('');
                self::assertSame('Tax label regression', $rates[$id]->tax_rate_name);
                self::assertSame('20.0000', $rates[$id]->tax_rate);
            }
        } finally {
            WC_Tax::_delete_tax_rate($id);
            PLL()->curlang = $previous;
            if ($strings === null) { unset($GLOBALS['l10n']['pll_string']); }
            else { $GLOBALS['l10n']['pll_string'] = $strings; }
        }
    }
}
