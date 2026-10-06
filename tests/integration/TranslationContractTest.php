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
            if ($domain === 'woo-poly-integration' && strpos($text, 'You can translate woocommerce endpoints,') === 0) {
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
