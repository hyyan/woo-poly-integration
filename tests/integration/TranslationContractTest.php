<?php
use Hyyan\WPI\Product\Meta;
use Hyyan\WPI\Tools\TranslationsDownloader;
use Hyyan\WPI\Admin\Features;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class TranslationContractTest extends TestCase
{
    public function testPolylangSettingsHelpUsesPluginTranslationDomain(): void
    {
        $filter = static function ($translated, $text, $domain) {
            return $text === 'Polylang admin menu "Languages, Settings"' && $domain === 'woo-poly-integration' ? 'Translated settings help' : $translated;
        };
        add_filter('gettext', $filter, 10, 3);
        try {
            $groups = Meta::getProductMetaToCopy(array(), false);
            self::assertStringContainsString('Translated settings help', $groups['polylang']['desc']);
            self::assertStringContainsString('Translated settings help', $groups['Attributes']['desc']);
        } finally { remove_filter('gettext', $filter, 10); }
    }
    #[DataProvider('transportResults')]
    public function testExistingCatalogKeysSupportReorderedPlaceholders(bool $available): void
    {
        $calls = 0; $lookup = null;
        $http = static function () use (&$calls, $available) {
            $calls++;
            return $available && $calls === 1 ? array('response' => array('code' => 200), 'body' => '') : new WP_Error('failed', 'Fixture transport failure');
        };
        $gettext = static function ($translated, $text, $domain) use (&$lookup) {
            if ($domain === 'woo-poly-integration' && strpos($text, 'WooCommerce translation %s') !== false) {
                $lookup = $text;
                return '<a href="%2$s">Repository</a> for %1$s';
            }
            return $translated;
        };
        add_filter('pre_http_request', $http); add_filter('gettext', $gettext, 10, 3);
        try {
            try { TranslationsDownloader::download('zz_CONTRACT', 'Fixture language'); self::fail('Fixture transport must fail'); }
            catch (RuntimeException $error) {
                self::assertStringContainsString('Fixture language(zz_CONTRACT)', $error->getMessage());
                self::assertStringContainsString(TranslationsDownloader::getRepoUrl(), $error->getMessage());
                self::assertStringContainsString('translation %s', $lookup);
            }
        } finally { remove_filter('pre_http_request', $http); remove_filter('gettext', $gettext, 10); }
    }
    public static function transportResults(): array { return array(array(false), array(true)); }
    public function testEndpointNoticeRetainsCatalogKeyAndAllowsOrderedTranslation(): void
    {
        $source = null;
        $filter = static function ($translated, $text, $domain) use (&$source) {
            if ($domain === 'woo-poly-integration' && $text === 'You can translate woocommerce endpoints, email strings, shipping methods from polylang strings tab. <a target="_blank" href="%s">%s</a>') {
                $source = $text;
                return '%2$s: <a href="%1$s">Open translations</a>';
            }
            return $translated;
        };
        add_filter('gettext', $filter, 10, 3);
        try {
            $html = \Hyyan\WPI\Plugin::getView('Messages/endpointsTranslations');
            $dom = new DOMDocument(); @$dom->loadHTML($html);
            $link = $dom->getElementsByTagName('a')->item(0);
            self::assertStringContainsString('admin.php?page=mlang_strings', $link->getAttribute('href'));
            self::assertStringContainsString('Translate:', $html);
            self::assertStringContainsString('href="%s">%s</a>', $source);
        } finally { remove_filter('gettext', $filter, 10); }
    }
    #[DataProvider('legacyFormatKeys')]
    public function testLegacyCatalogStillTranslatesNumberedFormats(string $current, string $legacy): void
    {
        $original = $GLOBALS['l10n']['woo-poly-integration'] ?? null;
        $catalog = new MO();
        $catalog->add_entry(new Translation_Entry(array('singular' => $legacy,
            'translations' => array('%2$s before %1$s'))));
        $GLOBALS['l10n']['woo-poly-integration'] = $catalog;
        try {
            self::assertSame('second before first', sprintf(__($current, 'woo-poly-integration'), 'first', 'second'));
        } finally {
            if ($original === null) { unset($GLOBALS['l10n']['woo-poly-integration']); }
            else { $GLOBALS['l10n']['woo-poly-integration'] = $original; }
        }
    }

    #[DataProvider('legacyFormatKeys')]
    public function testCurrentCatalogFormatTakesPrecedenceOverLegacy(string $current, string $legacy): void
    {
        $original = $GLOBALS['l10n']['woo-poly-integration'] ?? null;
        $catalog = new MO();
        $catalog->add_entry(new Translation_Entry(array('singular' => $legacy,
            'translations' => array('legacy %2$s %1$s'))));
        $catalog->add_entry(new Translation_Entry(array('singular' => $current,
            'translations' => array('current %2$s %1$s'))));
        $GLOBALS['l10n']['woo-poly-integration'] = $catalog;
        try {
            self::assertSame('current second first', sprintf(__($current, 'woo-poly-integration'), 'first', 'second'));
        } finally {
            if ($original === null) { unset($GLOBALS['l10n']['woo-poly-integration']); }
            else { $GLOBALS['l10n']['woo-poly-integration'] = $original; }
        }
    }

    #[DataProvider('legacyFormatKeys')]
    public function testExplicitCurrentSourceTranslationPrecedesLegacy(string $current, string $legacy): void
    {
        $original = $GLOBALS['l10n']['woo-poly-integration'] ?? null;
        $catalog = new MO();
        $catalog->add_entry(new Translation_Entry(array('singular' => $legacy, 'translations' => array('legacy text'))));
        $catalog->add_entry(new Translation_Entry(array('singular' => $current, 'translations' => array($current))));
        $GLOBALS['l10n']['woo-poly-integration'] = $catalog;
        try { self::assertSame($current, __($current, 'woo-poly-integration')); }
        finally {
            if ($original === null) { unset($GLOBALS['l10n']['woo-poly-integration']); }
            else { $GLOBALS['l10n']['woo-poly-integration'] = $original; }
        }
    }

    #[DataProvider('legacyFormatKeys')]
    public function testLegacyDomainGettextFiltersAreRetained(string $current, string $legacy): void
    {
        $filter = static fn($translated, $text) => $text === $legacy ? 'legacy domain %2$s %1$s' : $translated;
        add_filter('gettext_woo-poly-integration', $filter, 10, 2);
        try { self::assertSame('legacy domain second first', sprintf(__($current, 'woo-poly-integration'), 'first', 'second')); }
        finally { remove_filter('gettext_woo-poly-integration', $filter, 10); }
    }

    public function testCurrentGettextFilterTakesPrecedenceOverLegacy(): void
    {
        list($current, $legacy) = self::legacyFormatKeys()[0];
        $filter = static fn($translated, $text, $domain) => $domain === 'woo-poly-integration'
            ? ($text === $current ? 'current filtered' : ($text === $legacy ? 'legacy filtered' : $translated)) : $translated;
        add_filter('gettext', $filter, 10, 3);
        try { self::assertSame('current filtered', __($current, 'woo-poly-integration')); }
        finally { remove_filter('gettext', $filter, 10); }
    }

    public function testFormatCompatibilityDoesNotChangeOtherDomains(): void
    {
        list($current) = self::legacyFormatKeys()[0];
        self::assertSame($current, __($current, 'woopoly-unrelated-fixture'));
    }

    public static function legacyFormatKeys(): array
    {
        return array(
            array('WooCommerce translation %1$s can not be found in : <a href="%2$s">%2$s</a>',
                'WooCommerce translation %s can not be found in : <a href="%2$s">%2$s</a>'),
            array('Unable to download WooCommerce translation %1$s from : <a href="%2$s">%2$s</a>',
                'Unable to download WooCommerce translation %s from : <a href="%2$s">%2$s</a>'),
            array('You can translate woocommerce endpoints, email strings, shipping methods from polylang strings tab. <a target="_blank" href="%1$s">%2$s</a>',
                'You can translate woocommerce endpoints, email strings, shipping methods from polylang strings tab. <a target="_blank" href="%s">%s</a>'),
        );
    }

    public function testFeatureDescriptionRetainsExistingCatalogKey(): void
    {
        $key = 'When creating new translations, start with blank text, copy or machine translation? (You may want to turn this off if using Polylang Pro, Lingotek or other automatic copy-or-translation solution.) ';
        $filter = static fn($translated, $text, $domain) => $domain === 'woo-poly-integration' && $text === $key ? 'Existing catalog translation' : $translated;
        add_filter('gettext', $filter, 10, 3);
        try {
            $features = (new ReflectionClass(Features::class))->newInstanceWithoutConstructor();
            $fields = $features->getFields(array())['wpi-features'];
            $field = array_values(array_filter($fields, static fn($field) => $field['name'] === 'new-translation-defaults'))[0];
            self::assertSame('Existing catalog translation', $field['desc']);
            self::assertSame(array('0','1','2'), array_map('strval',array_keys($field['options'])));
        } finally { remove_filter('gettext', $filter, 10); }
    }
}
