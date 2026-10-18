<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Hyyan\WPI\Plugin;

final class NativeCatalogLoaderTest extends TestCase
{
    #[DataProvider('legacyVersions')]
    public function testLegacyLoaderRetainsGlobalPackPriority(string $version): void
    {
        $this->assertLegacyPaths($version, true);
    }

    #[DataProvider('legacyVersions')]
    public function testLegacyLoaderRetainsBundledFallback(string $version): void
    {
        $this->assertLegacyPaths($version, false);
    }

    private function assertLegacyPaths(string $version, bool $globalFound): void
    {
        $original = $GLOBALS['wp_version'];
        $paths = array();
        $locale = static fn($value, $domain) => $domain === 'woo-poly-integration' ? 'es_ES' : $value;
        $load = static function ($loaded, $domain, $file, $language) use (&$paths, $globalFound) {
            if ($domain !== 'woo-poly-integration') { return $loaded; }
            $paths[] = array($file, $language);
            return count($paths) === 1 ? $globalFound : true;
        };
        add_filter('plugin_locale', $locale, 10, 2);
        add_filter('pre_load_textdomain', $load, 10, 4);
        $GLOBALS['wp_version'] = $version;
        try {
            (new ReflectionClass(Plugin::class))->newInstanceWithoutConstructor()->loadTextDomain();
            $expected = array(array(WP_LANG_DIR . '/plugins/woo-poly-integration-es_ES.mo', 'es_ES'));
            if (!$globalFound) {
                $expected[] = array(WP_PLUGIN_DIR . '/' . plugin_basename(dirname(Hyyan_WPI_DIR)) . '/languages/woo-poly-integration-es_ES.mo', 'es_ES');
            }
            self::assertSame($expected, $paths);
        } finally {
            $GLOBALS['wp_version'] = $original;
            remove_filter('plugin_locale', $locale, 10);
            remove_filter('pre_load_textdomain', $load, 10);
        }
    }

    public static function legacyVersions(): array
    {
        return array('minimum' => array('5.4.2'), 'registry' => array('6.1'), 'last eager' => array('6.6'));
    }

    public function testCurrentLoaderRetainsLazyRegistration(): void
    {
        $calls = 0;
        $load = static function ($loaded, $domain) use (&$calls) {
            if ($domain === 'woo-poly-integration') { ++$calls; }
            return $loaded;
        };
        add_filter('pre_load_textdomain', $load, 10, 2);
        try {
            (new ReflectionClass(Plugin::class))->newInstanceWithoutConstructor()->loadTextDomain();
            self::assertSame(0, $calls);
            self::assertSame(dirname(Hyyan_WPI_DIR) . '/languages/', $GLOBALS['wp_textdomain_registry']->get('woo-poly-integration', 'es_ES'));
        } finally { remove_filter('pre_load_textdomain', $load, 10); }
    }
}
