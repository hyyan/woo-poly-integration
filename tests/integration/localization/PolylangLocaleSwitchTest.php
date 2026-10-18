<?php
use Hyyan\WPI\Utilities;
use PHPUnit\Framework\TestCase;

final class PolylangLocaleSwitchTest extends TestCase
{
    private $originalLanguage;

    protected function setUp(): void
    {
        $this->originalLanguage = PLL()->curlang;
    }

    protected function tearDown(): void
    {
        PLL()->curlang = $this->originalLanguage;
    }

    public function testUnknownLocalePreservesAllLanguagesSelection(): void
    {
        PLL()->curlang = false;
        Utilities::switch_pll_locale('woopoly_unknown');
        self::assertFalse(PLL()->curlang);
    }

    public function testKnownLocalesSelectTheirExistingLanguageObjects(): void
    {
        foreach (array('en_US', 'de_DE') as $locale) {
            PLL()->curlang = false;
            Utilities::switch_pll_locale($locale);
            self::assertSame(PLL()->model->get_language($locale), PLL()->curlang);
            self::assertSame($locale, pll_current_language('locale'));
        }
    }

    public function testEmptyLocaleRestoresAllLanguagesSelection(): void
    {
        PLL()->curlang = PLL()->model->get_language('de');
        Utilities::switch_pll_locale(false);
        self::assertFalse(PLL()->curlang);
    }

    public function testUnknownLocaleKeepsTheExistingLegacyObjectFallback(): void
    {
        PLL()->curlang = (object) array('locale' => 'legacy');
        Utilities::switch_pll_locale('woopoly_unknown');
        self::assertSame('woopoly_unknown', PLL()->curlang->locale);
    }
}
